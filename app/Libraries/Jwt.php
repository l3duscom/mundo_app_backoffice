<?php

namespace App\Libraries;

use Exception;

/**
 * Biblioteca simples para geração e validação de JWT (JSON Web Token)
 * Implementa o padrão JWT para autenticação de API
 */
class Jwt
{
    /**
     * Gera um token JWT
     *
     * @param array $payload Dados a serem incluídos no token
     * @param int $expiration Tempo de expiração em segundos (padrão: 24 horas)
     * @return string Token JWT gerado
     */
    public static function encode(array $payload, int $expiration = 86400): string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        // Adiciona informações de tempo ao payload
        $payload['iat'] = time(); // Issued At
        $payload['exp'] = time() + $expiration; // Expiration Time

        // Codifica header e payload em base64
        $base64UrlHeader = self::base64UrlEncode(json_encode($header));
        $base64UrlPayload = self::base64UrlEncode(json_encode($payload));

        // Cria a assinatura
        $signature = hash_hmac(
            'sha256',
            $base64UrlHeader . "." . $base64UrlPayload,
            self::getSecret(),
            true
        );
        $base64UrlSignature = self::base64UrlEncode($signature);

        // Retorna o token completo
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * Decodifica e valida um token JWT
     *
     * @param string $token Token JWT a ser validado
     * @param array $options Opções adicionais de validação
     * @return array|null Payload do token se válido, null se inválido
     */
    public static function decode(string $token, array $options = []): ?array
    {
        try {
            // SEGURANÇA: Limita tamanho do token (evita DoS)
            if (strlen($token) > 2048) {
                log_message('warning', 'Token JWT muito grande rejeitado: ' . strlen($token) . ' bytes');
                return null;
            }

            // Separa o token em suas partes
            $tokenParts = explode('.', $token);

            if (count($tokenParts) !== 3) {
                log_message('warning', 'Token JWT com formato inválido');
                return null;
            }

            [$base64UrlHeader, $base64UrlPayload, $base64UrlSignature] = $tokenParts;

            // SEGURANÇA: Valida caracteres permitidos em base64url
            if (!preg_match('/^[A-Za-z0-9_-]+$/', $base64UrlHeader) ||
                !preg_match('/^[A-Za-z0-9_-]+$/', $base64UrlPayload) ||
                !preg_match('/^[A-Za-z0-9_-]+$/', $base64UrlSignature)) {
                log_message('warning', 'Token JWT contém caracteres inválidos');
                return null;
            }

            // Decodifica e valida o header
            $header = json_decode(self::base64UrlDecode($base64UrlHeader), true);
            if (!is_array($header) || !isset($header['alg']) || $header['alg'] !== 'HS256') {
                log_message('warning', 'Header JWT inválido ou algoritmo não suportado');
                return null;
            }

            // Valida a assinatura usando timing-safe comparison
            $signature = hash_hmac(
                'sha256',
                $base64UrlHeader . "." . $base64UrlPayload,
                self::getSecret(),
                true
            );
            $expectedSignature = self::base64UrlEncode($signature);

            // SEGURANÇA: Usa hash_equals para evitar timing attacks
            if (!hash_equals($base64UrlSignature, $expectedSignature)) {
                log_message('warning', 'Assinatura JWT inválida');
                return null;
            }

            // Decodifica o payload
            $payload = json_decode(self::base64UrlDecode($base64UrlPayload), true);

            if (!is_array($payload)) {
                log_message('warning', 'Payload JWT inválido');
                return null;
            }

            // SEGURANÇA: Valida campos obrigatórios
            $requiredFields = $options['required_fields'] ?? [];
            foreach ($requiredFields as $field) {
                if (!isset($payload[$field])) {
                    log_message('warning', "Campo obrigatório ausente no JWT: {$field}");
                    return null;
                }
            }

            // SEGURANÇA: Verifica tempo de expiração (exp)
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                $expiredFor = time() - $payload['exp'];
                log_message('info', "Token JWT expirado há {$expiredFor} segundos");
                return null;
            }

            // SEGURANÇA: Verifica "not before" (nbf)
            if (isset($payload['nbf']) && $payload['nbf'] > time()) {
                log_message('warning', 'Token JWT ainda não é válido (nbf)');
                return null;
            }

            // SEGURANÇA: Verifica "issued at" (iat)
            if (isset($payload['iat'])) {
                $age = time() - $payload['iat'];
                $maxAge = $options['max_age'] ?? 31536000; // 1 ano padrão
                
                if ($age > $maxAge) {
                    log_message('warning', "Token JWT muito antigo: {$age} segundos");
                    return null;
                }

                // Token não pode ser emitido no futuro
                if ($payload['iat'] > time() + 60) { // 60s de tolerância clock skew
                    log_message('warning', 'Token JWT emitido no futuro (iat)');
                    return null;
                }
            }

            // SEGURANÇA: Valida audience (aud) se fornecido
            if (isset($options['audience']) && isset($payload['aud'])) {
                if ($payload['aud'] !== $options['audience']) {
                    log_message('warning', 'Token JWT com audience inválida');
                    return null;
                }
            }

            // SEGURANÇA: Valida issuer (iss) se fornecido
            if (isset($options['issuer']) && isset($payload['iss'])) {
                if ($payload['iss'] !== $options['issuer']) {
                    log_message('warning', 'Token JWT com issuer inválido');
                    return null;
                }
            }

            return $payload;
        } catch (Exception $e) {
            log_message('error', 'Erro ao decodificar JWT: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Codifica uma string em base64 URL-safe
     *
     * @param string $data Dados a serem codificados
     * @return string String codificada
     */
    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Decodifica uma string base64 URL-safe
     *
     * @param string $data String codificada
     * @return string Dados decodificados
     */
    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * Recupera a chave secreta para assinatura do JWT
     * 
     * @return string Chave secreta
     * @throws Exception Se a chave não estiver configurada
     */
    private static function getSecret(): string
    {
        $secret = env('JWT_SECRET_KEY');

        if (empty($secret)) {
            // Fallback para uma chave baseada na chave de recuperação de senha
            // mas idealmente deve usar uma chave dedicada no .env
            $secret = env('CHAVE_RECUPERACAO_SENHA');
        }

        if (empty($secret)) {
            throw new Exception('JWT_SECRET_KEY não configurada no .env');
        }

        return $secret;
    }

    /**
     * Gera um refresh token simples
     *
     * @return string Token de refresh
     */
    public static function generateRefreshToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Extrai o token do header Authorization
     *
     * @param string|null $authHeader Header Authorization
     * @return string|null Token extraído ou null
     */
    public static function extractFromHeader(?string $authHeader): ?string
    {
        if (empty($authHeader)) {
            return null;
        }

        // Suporta formato: Bearer TOKEN ou JWT TOKEN
        if (preg_match('/(?:Bearer|JWT)\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }
}

