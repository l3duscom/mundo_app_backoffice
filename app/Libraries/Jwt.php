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
     * @return array|null Payload do token se válido, null se inválido
     */
    public static function decode(string $token): ?array
    {
        try {
            // Separa o token em suas partes
            $tokenParts = explode('.', $token);

            if (count($tokenParts) !== 3) {
                return null;
            }

            [$base64UrlHeader, $base64UrlPayload, $base64UrlSignature] = $tokenParts;

            // Valida a assinatura
            $signature = hash_hmac(
                'sha256',
                $base64UrlHeader . "." . $base64UrlPayload,
                self::getSecret(),
                true
            );
            $expectedSignature = self::base64UrlEncode($signature);

            if ($base64UrlSignature !== $expectedSignature) {
                // Assinatura inválida
                return null;
            }

            // Decodifica o payload
            $payload = json_decode(self::base64UrlDecode($base64UrlPayload), true);

            if (!is_array($payload)) {
                return null;
            }

            // Verifica se o token expirou
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return null;
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

