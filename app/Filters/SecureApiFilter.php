<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\RateLimiter;

/**
 * Filtro de Segurança para APIs
 * Implementa HTTPS obrigatório, rate limiting e outras proteções
 */
class SecureApiFilter implements FilterInterface
{
    /**
     * Executa verificações de segurança antes da requisição
     *
     * @param RequestInterface $request
     * @param array|null $arguments
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // 1. Força HTTPS em produção
        if (ENVIRONMENT === 'production' && !$this->isHttps($request)) {
            return service('response')
                ->setJSON([
                    'success' => false,
                    'message' => 'HTTPS é obrigatório para acessar a API',
                    'error' => 'Use https:// ao invés de http://'
                ])
                ->setStatusCode(426); // 426 Upgrade Required
        }

        // 2. Rate limiting geral (60 requisições por minuto)
        $rateLimiter = new RateLimiter();
        $clientIp = RateLimiter::getClientIp();
        
        $throttle = $rateLimiter->throttle("api_{$clientIp}", 60, 60);
        
        if (!$throttle['allowed']) {
            log_message('warning', "Rate limit excedido para IP: {$clientIp}");
            
            $response = service('response')
                ->setJSON([
                    'success' => false,
                    'message' => 'Muitas requisições. Aguarde um momento.',
                    'error' => 'Rate limit excedido',
                    'retry_after' => $throttle['retry_after']
                ])
                ->setStatusCode(429); // 429 Too Many Requests
            
            $response->setHeader('Retry-After', (string)$throttle['retry_after']);
            
            return $response;
        }

        // 3. Adiciona headers de rate limit na resposta
        $request->rateLimitRemaining = $throttle['remaining'];

        // 4. Validações adicionais de segurança
        $this->validateSecurityHeaders($request);

        return $request;
    }

    /**
     * Adiciona headers de segurança após a requisição
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array|null $arguments
     * @return ResponseInterface
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Headers de segurança
        $response->setHeader('X-Content-Type-Options', 'nosniff');
        $response->setHeader('X-Frame-Options', 'DENY');
        $response->setHeader('X-XSS-Protection', '1; mode=block');
        $response->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Rate limit info
        if (isset($request->rateLimitRemaining)) {
            $response->setHeader('X-RateLimit-Remaining', (string)$request->rateLimitRemaining);
        }

        // CORS headers (ajuste conforme necessário)
        if (ENVIRONMENT !== 'production') {
            // Em desenvolvimento, permite origens locais
            $response->setHeader('Access-Control-Allow-Origin', '*');
            $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        } else {
            // Em produção, configure domínios específicos
            $allowedOrigins = env('CORS_ALLOWED_ORIGINS', '');
            if (!empty($allowedOrigins)) {
                $origins = explode(',', $allowedOrigins);
                $requestOrigin = $request->getServer('HTTP_ORIGIN');
                
                if (in_array($requestOrigin, $origins)) {
                    $response->setHeader('Access-Control-Allow-Origin', $requestOrigin);
                    $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
                    $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
                    $response->setHeader('Access-Control-Allow-Credentials', 'true');
                }
            }
        }

        return $response;
    }

    /**
     * Verifica se a requisição é HTTPS
     *
     * @param RequestInterface $request
     * @return bool
     */
    private function isHttps(RequestInterface $request): bool
    {
        // Verifica se está usando HTTPS
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            return true;
        }

        if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
            return true;
        }

        // Verifica headers de proxy (Cloudflare, nginx, etc)
        $forwardedProto = $request->getServer('HTTP_X_FORWARDED_PROTO');
        if ($forwardedProto === 'https') {
            return true;
        }

        $cloudflareVisitorScheme = $request->getServer('HTTP_CF_VISITOR');
        if ($cloudflareVisitorScheme && strpos($cloudflareVisitorScheme, 'https') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Valida headers de segurança suspeitos
     *
     * @param RequestInterface $request
     * @return void
     */
    private function validateSecurityHeaders(RequestInterface $request): void
    {
        $userAgentString = $request->getServer('HTTP_USER_AGENT') ?? '';
        $userAgent = $userAgentString;
        
        // Detecta user agents suspeitos
        $suspiciousPatterns = [
            'sqlmap',
            'nikto',
            'nmap',
            'masscan',
            'acunetix',
            'burp',
            'metasploit'
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (stripos($userAgent, $pattern) !== false) {
                $clientIp = RateLimiter::getClientIp();
                log_message('alert', "User agent suspeito detectado! IP: {$clientIp} | UA: {$userAgent}");
                
                // Bloqueia IP automaticamente
                $rateLimiter = new RateLimiter();
                $rateLimiter->block($clientIp, 'api', 3600, 'User agent suspeito detectado');
            }
        }
    }
}

