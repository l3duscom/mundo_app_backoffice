<?php

namespace App\Libraries;

use CodeIgniter\Database\ConnectionInterface;

/**
 * Biblioteca de Rate Limiting
 * Controla tentativas de login e requisições por IP
 */
class RateLimiter
{
    protected $db;
    protected $cache;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->cache = \Config\Services::cache();
    }

    /**
     * Verifica se o IP está bloqueado
     *
     * @param string $ip Endereço IP
     * @param string $action Ação sendo realizada (ex: 'login', 'api')
     * @return array ['blocked' => bool, 'retry_after' => int|null, 'reason' => string|null]
     */
    public function isBlocked(string $ip, string $action = 'login'): array
    {
        $safeIp = $this->sanitizeIpForCache($ip);
        $cacheKey = "blocked_{$action}_{$safeIp}";
        $blockData = $this->cache->get($cacheKey);

        if ($blockData !== null) {
            $remainingTime = $blockData['until'] - time();
            
            if ($remainingTime > 0) {
                return [
                    'blocked' => true,
                    'retry_after' => $remainingTime,
                    'reason' => $blockData['reason'] ?? 'Muitas tentativas falhas'
                ];
            } else {
                // Bloqueio expirou, limpar cache
                $this->cache->delete($cacheKey);
            }
        }

        return [
            'blocked' => false,
            'retry_after' => null,
            'reason' => null
        ];
    }

    /**
     * Verifica e incrementa tentativas de uma ação
     *
     * @param string $ip Endereço IP
     * @param string $action Ação sendo realizada
     * @param int $maxAttempts Número máximo de tentativas
     * @param int $windowSeconds Janela de tempo em segundos
     * @return array ['allowed' => bool, 'attempts' => int, 'remaining' => int]
     */
    public function attempt(string $ip, string $action = 'login', int $maxAttempts = 5, int $windowSeconds = 300): array
    {
        // Verifica se já está bloqueado
        $blockStatus = $this->isBlocked($ip, $action);
        if ($blockStatus['blocked']) {
            return [
                'allowed' => false,
                'attempts' => $maxAttempts,
                'remaining' => 0,
                'retry_after' => $blockStatus['retry_after'],
                'reason' => $blockStatus['reason']
            ];
        }

        $safeIp = $this->sanitizeIpForCache($ip);
        $cacheKey = "attempts_{$action}_{$safeIp}";
        $attempts = $this->cache->get($cacheKey) ?? 0;
        $attempts++;

        // Armazena tentativas
        $this->cache->save($cacheKey, $attempts, $windowSeconds);

        $remaining = max(0, $maxAttempts - $attempts);
        $allowed = $attempts <= $maxAttempts;

        // Se excedeu o limite, bloqueia
        if (!$allowed) {
            $this->block($ip, $action, 900, 'Limite de tentativas excedido'); // 15 minutos
        }

        return [
            'allowed' => $allowed,
            'attempts' => $attempts,
            'remaining' => $remaining,
            'retry_after' => $allowed ? null : 900
        ];
    }

    /**
     * Bloqueia um IP por determinado tempo
     *
     * @param string $ip Endereço IP
     * @param string $action Ação bloqueada
     * @param int $duration Duração do bloqueio em segundos
     * @param string $reason Motivo do bloqueio
     * @return void
     */
    public function block(string $ip, string $action = 'login', int $duration = 900, string $reason = 'Bloqueado'): void
    {
        $safeIp = $this->sanitizeIpForCache($ip);
        $cacheKey = "blocked_{$action}_{$safeIp}";
        $blockData = [
            'ip' => $ip,
            'action' => $action,
            'until' => time() + $duration,
            'reason' => $reason,
            'blocked_at' => date('Y-m-d H:i:s')
        ];

        $this->cache->save($cacheKey, $blockData, $duration);

        // Log do bloqueio
        log_message('warning', "IP bloqueado: {$ip} | Ação: {$action} | Motivo: {$reason} | Duração: {$duration}s");

        // Registra no banco de dados para auditoria
        $this->logBlock($ip, $action, $duration, $reason);
    }

    /**
     * Limpa tentativas de um IP (usado após login bem-sucedido)
     *
     * @param string $ip Endereço IP
     * @param string $action Ação a limpar
     * @return void
     */
    public function clear(string $ip, string $action = 'login'): void
    {
        $safeIp = $this->sanitizeIpForCache($ip);
        $cacheKey = "attempts_{$action}_{$safeIp}";
        $this->cache->delete($cacheKey);
    }

    /**
     * Throttling geral para requisições
     * Limita número de requisições por minuto
     *
     * @param string $identifier Identificador único (IP, user_id, etc)
     * @param int $maxRequests Número máximo de requisições
     * @param int $perSeconds Por quantos segundos
     * @return array ['allowed' => bool, 'remaining' => int, 'retry_after' => int|null]
     */
    public function throttle(string $identifier, int $maxRequests = 60, int $perSeconds = 60): array
    {
        $safeIdentifier = $this->sanitizeIpForCache($identifier);
        $cacheKey = "throttle_{$safeIdentifier}";
        $requests = $this->cache->get($cacheKey) ?? [];
        
        $now = time();
        $cutoff = $now - $perSeconds;

        // Remove requisições antigas
        $requests = array_filter($requests, function($timestamp) use ($cutoff) {
            return $timestamp > $cutoff;
        });

        // Verifica se excedeu o limite
        if (count($requests) >= $maxRequests) {
            $oldestRequest = min($requests);
            $retryAfter = $perSeconds - ($now - $oldestRequest);

            return [
                'allowed' => false,
                'remaining' => 0,
                'retry_after' => max(1, $retryAfter)
            ];
        }

        // Adiciona nova requisição
        $requests[] = $now;
        $this->cache->save($cacheKey, $requests, $perSeconds + 10);

        return [
            'allowed' => true,
            'remaining' => $maxRequests - count($requests),
            'retry_after' => null
        ];
    }

    /**
     * Registra bloqueio no banco de dados para auditoria
     *
     * @param string $ip
     * @param string $action
     * @param int $duration
     * @param string $reason
     * @return void
     */
    private function logBlock(string $ip, string $action, int $duration, string $reason): void
    {
        try {
            // Cria tabela se não existir
            $this->createLogTableIfNotExists();

            $this->db->table('security_blocks')->insert([
                'ip_address' => $ip,
                'action' => $action,
                'duration' => $duration,
                'reason' => $reason,
                'blocked_at' => date('Y-m-d H:i:s'),
                'expires_at' => date('Y-m-d H:i:s', time() + $duration)
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Erro ao registrar bloqueio: ' . $e->getMessage());
        }
    }

    /**
     * Cria tabela de logs de segurança se não existir
     *
     * @return void
     */
    private function createLogTableIfNotExists(): void
    {
        $tableExists = $this->db->tableExists('security_blocks');
        
        if (!$tableExists) {
            $forge = \Config\Database::forge();
            
            $forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'ip_address' => [
                    'type' => 'VARCHAR',
                    'constraint' => 45,
                ],
                'action' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                ],
                'duration' => [
                    'type' => 'INT',
                    'constraint' => 11,
                ],
                'reason' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ],
                'blocked_at' => [
                    'type' => 'DATETIME',
                ],
                'expires_at' => [
                    'type' => 'DATETIME',
                ],
            ]);

            $forge->addKey('id', true);
            $forge->addKey('ip_address');
            $forge->addKey('blocked_at');
            
            try {
                $forge->createTable('security_blocks', true);
                log_message('info', 'Tabela security_blocks criada com sucesso');
            } catch (\Exception $e) {
                log_message('error', 'Erro ao criar tabela security_blocks: ' . $e->getMessage());
            }
        }
    }

    /**
     * Obtém o IP real do cliente (considerando proxies)
     *
     * @return string
     */
    public static function getClientIp(): string
    {
        $request = \Config\Services::request();
        
        // Verifica headers comuns de proxy
        $ipHeaders = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];

        foreach ($ipHeaders as $header) {
            $ip = $request->getServer($header);
            if (!empty($ip)) {
                // Se houver múltiplos IPs (proxy chain), pega o primeiro
                if (strpos($ip, ',') !== false) {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }
                
                // Valida se é um IP válido
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        // Fallback para REMOTE_ADDR
        return $request->getIPAddress();
    }

    /**
     * Sanitiza IP para uso como chave de cache
     * Remove caracteres reservados: {}()/\@:
     *
     * @param string $identifier IP ou identificador
     * @return string Identificador sanitizado
     */
    private function sanitizeIpForCache(string $identifier): string
    {
        // Substitui caracteres reservados por underscore
        // Caracteres reservados no CodeIgniter Cache: {}()/\@:
        $safe = str_replace(
            [':', '/', '\\', '@', '{', '}', '(', ')'],
            ['_', '_', '_', '_', '_', '_', '_', '_'],
            $identifier
        );

        // Remove espaços
        $safe = str_replace(' ', '_', $safe);

        // Limita tamanho (máximo 250 caracteres para segurança)
        if (strlen($safe) > 250) {
            $safe = substr($safe, 0, 250);
        }

        return $safe;
    }
}

