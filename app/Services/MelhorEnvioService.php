<?php

namespace App\Services;

use App\Models\MelhorEnvioCredentialModel;
use App\Models\MelhorEnvioLogModel;
use RuntimeException;

class MelhorEnvioService
{
    private string $baseUrl;
    private string $userAgent;
    private MelhorEnvioCredentialModel $credModel;
    private MelhorEnvioLogModel $logModel;

    public function __construct()
    {
        $this->baseUrl = env('CI_ENVIRONMENT') === 'production'
            ? 'https://melhorenvio.com.br'
            : 'https://sandbox.melhorenvio.com.br';

        $contato = env('MELHOR_ENVIO_CONTATO', 'contato@mundodream.com.br');
        $this->userAgent = 'MundoDream Backoffice (' . $contato . ')';

        $this->credModel = new MelhorEnvioCredentialModel();
        $this->logModel  = new MelhorEnvioLogModel();
    }

    // ===================== OAuth =====================

    public function authorizeUrl(string $state): string
    {
        $params = [
            'client_id'     => env('MELHOR_ENVIO_CLIENT_ID'),
            'redirect_uri'  => env('MELHOR_ENVIO_REDIRECT_URI'),
            'response_type' => 'code',
            'state'         => $state,
            'scope'         => 'cart-read cart-write companies-read companies-write coupons-read coupons-write notifications-read orders-read products-read products-write purchases-read shipping-calculate shipping-cancel shipping-checkout shipping-companies shipping-generate shipping-preview shipping-print shipping-share shipping-tracking ecommerce-shipping transactions-read users-read users-write webhooks-read webhooks-write',
        ];

        return $this->baseUrl . '/oauth/authorize?' . http_build_query($params);
    }

    public function exchangeCode(string $code): void
    {
        $body = [
            'grant_type'    => 'authorization_code',
            'client_id'     => env('MELHOR_ENVIO_CLIENT_ID'),
            'client_secret' => env('MELHOR_ENVIO_CLIENT_SECRET'),
            'redirect_uri'  => env('MELHOR_ENVIO_REDIRECT_URI'),
            'code'          => $code,
        ];

        $resp = $this->tokenRequest($body);
        $this->credModel->salvarTokens(
            $resp['access_token'],
            $resp['refresh_token'],
            (int) $resp['expires_in'],
            $resp['scope'] ?? null,
        );

        log_message('info', '[ME-OAUTH] tokens obtidos via authorization_code');
    }

    public function refreshIfNeeded(): void
    {
        $cred = $this->credModel->atual();
        if (! $cred) {
            throw new RuntimeException('Melhor Envio: nenhuma credencial cadastrada. Conecte em /melhor-envio/conectar.');
        }

        if (! $cred->precisaRefresh(24)) {
            return;
        }

        $body = [
            'grant_type'    => 'refresh_token',
            'client_id'     => env('MELHOR_ENVIO_CLIENT_ID'),
            'client_secret' => env('MELHOR_ENVIO_CLIENT_SECRET'),
            'refresh_token' => $cred->refresh_token,
        ];

        $resp = $this->tokenRequest($body);
        $this->credModel->salvarTokens(
            $resp['access_token'],
            $resp['refresh_token'],
            (int) $resp['expires_in'],
            $resp['scope'] ?? null,
        );

        log_message('info', '[ME-OAUTH] refresh efetuado');
    }

    private function tokenRequest(array $body): array
    {
        $url = $this->baseUrl . '/oauth/token';
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'User-Agent: ' . $this->userAgent,
        ];

        $t0 = microtime(true);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $respBody = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $erro     = curl_errno($ch) ? curl_error($ch) : null;
        curl_close($ch);
        $duracao = (int) round((microtime(true) - $t0) * 1000);

        $sanitizado = $body;
        if (isset($sanitizado['client_secret'])) {
            $sanitizado['client_secret'] = '***';
        }
        if (isset($sanitizado['refresh_token'])) {
            $sanitizado['refresh_token'] = '***';
        }
        if (isset($sanitizado['code'])) {
            $sanitizado['code'] = '***';
        }

        $this->logModel->registrar([
            'pedido_id'     => null,
            'endpoint'      => '/oauth/token',
            'http_method'   => 'POST',
            'request_body'  => json_encode($sanitizado, JSON_UNESCAPED_UNICODE),
            'response_body' => $this->sanitizaResposta($respBody),
            'http_status'   => $httpCode,
            'duracao_ms'    => $duracao,
            'erro'          => $erro,
        ]);

        log_message('info', '[ME-OAUTH] POST /oauth/token -> ' . $httpCode . ' (' . $duracao . 'ms)');

        if ($httpCode >= 400 || $erro) {
            log_message('error', '[ME-OAUTH] falha ' . $httpCode . ' - ' . ($erro ?: $respBody));
            throw new RuntimeException('Falha OAuth Melhor Envio: HTTP ' . $httpCode);
        }

        $json = json_decode((string) $respBody, true);
        if (! is_array($json) || empty($json['access_token'])) {
            throw new RuntimeException('Resposta OAuth invalida do Melhor Envio');
        }
        return $json;
    }

    private function sanitizaResposta(?string $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        $data = json_decode($raw, true);
        if (! is_array($data)) {
            return $raw;
        }
        foreach (['access_token', 'refresh_token'] as $k) {
            if (isset($data[$k])) {
                $data[$k] = '***';
            }
        }
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    // ===================== Operacoes =====================

    public function cotar(array $to, array $produtos = [], ?int $pedidoId = null): array
    {
        $payload = [
            'from'     => ['postal_code' => env('ME_FROM_CEP')],
            'to'       => ['postal_code' => $to['postal_code']],
            'products' => $produtos ?: [$this->payloadVolumePadrao() + ['quantity' => 1, 'insurance_value' => 0]],
        ];

        return $this->request('POST', '/api/v2/me/shipment/calculate', $payload, $pedidoId);
    }

    public function adicionarAoCarrinho(int $pedidoId, int $servicoId, array $to): string
    {
        $payload = [
            'service'  => $servicoId,
            'from'     => $this->payloadRemetente(),
            'to'       => $to,
            'products' => $this->declaracaoConteudo($pedidoId),
            'volumes'  => [$this->payloadVolumePadrao()],
            'options'  => [
                'insurance_value'  => 0,
                'receipt'          => false,
                'own_hand'         => false,
                'reverse'          => false,
                'non_commercial'   => true,
                'platform'         => 'MundoDream Backoffice',
            ],
        ];

        $resp = $this->request('POST', '/api/v2/me/cart', $payload, $pedidoId);
        return (string) ($resp['id'] ?? '');
    }

    public function checkout(array $orderIds, ?int $pedidoId = null): array
    {
        return $this->request('POST', '/api/v2/me/shipment/checkout', ['orders' => $orderIds], $pedidoId);
    }

    public function gerarEtiqueta(array $orderIds, ?int $pedidoId = null): array
    {
        return $this->request('POST', '/api/v2/me/shipment/generate', ['orders' => $orderIds], $pedidoId);
    }

    public function imprimirEtiqueta(array $orderIds, string $mode = 'private', ?int $pedidoId = null): string
    {
        $resp = $this->request('POST', '/api/v2/me/shipment/print', [
            'mode'   => $mode,
            'orders' => $orderIds,
        ], $pedidoId);
        return (string) ($resp['url'] ?? '');
    }

    public function rastrear(array $orderIds, ?int $pedidoId = null): array
    {
        return $this->request('POST', '/api/v2/me/shipment/tracking', ['orders' => $orderIds], $pedidoId);
    }

    // ===================== Helpers =====================

    public function payloadVolumePadrao(): array
    {
        return [
            'height' => (float) env('ME_VOL_ALTURA', 2),
            'width'  => (float) env('ME_VOL_LARGURA', 12),
            'length' => (float) env('ME_VOL_COMPRIMENTO', 17),
            'weight' => (float) env('ME_VOL_PESO', 0.5),
        ];
    }

    public function payloadRemetente(): array
    {
        return [
            'name'        => env('ME_FROM_NOME'),
            'email'       => env('ME_FROM_EMAIL'),
            'phone'       => env('ME_FROM_TELEFONE'),
            'document'    => env('ME_FROM_DOCUMENTO'),
            'address'     => env('ME_FROM_ENDERECO'),
            'number'      => env('ME_FROM_NUMERO'),
            'complement'  => env('ME_FROM_COMPLEMENTO'),
            'district'    => env('ME_FROM_BAIRRO'),
            'city'        => env('ME_FROM_CIDADE'),
            'state_abbr'  => env('ME_FROM_ESTADO'),
            'postal_code' => env('ME_FROM_CEP'),
            'country_id'  => env('ME_FROM_PAIS', 'BR'),
        ];
    }

    public function declaracaoConteudo(int $pedidoId): array
    {
        $db = \Config\Database::connect();
        $sql = "SELECT i.nome, COUNT(*) AS qtd, AVG(i.valor) AS valor_unit
                FROM ingressos i
                WHERE i.pedido_id = ?
                  AND i.tipo NOT IN ('cinemark','adicional','produto','')
                  AND i.ticket_id != 608
                GROUP BY i.nome";
        $rows = $db->query($sql, [$pedidoId])->getResultArray();

        return array_map(fn($r) => [
            'name'          => $r['nome'],
            'quantity'      => (int) $r['qtd'],
            'unitary_value' => (float) $r['valor_unit'],
        ], $rows);
    }

    public function temCredencial(): bool
    {
        return $this->credModel->atual() !== null;
    }

    public function credencialAtual()
    {
        return $this->credModel->atual();
    }

    // ===================== Request central =====================

    private function request(string $method, string $path, array $body = [], ?int $pedidoId = null): array
    {
        $this->refreshIfNeeded();
        $cred = $this->credModel->atual();

        $url = $this->baseUrl . $path;
        $headers = [
            'Authorization: Bearer ' . $cred->access_token,
            'Accept: application/json',
            'Content-Type: application/json',
            'User-Agent: ' . $this->userAgent,
        ];

        $t0 = microtime(true);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        if (! empty($body)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
        }

        $respBody = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $erro     = curl_errno($ch) ? curl_error($ch) : null;
        curl_close($ch);
        $duracao = (int) round((microtime(true) - $t0) * 1000);

        $this->logModel->registrar([
            'pedido_id'     => $pedidoId,
            'endpoint'      => $path,
            'http_method'   => $method,
            'request_body'  => json_encode($body, JSON_UNESCAPED_UNICODE),
            'response_body' => is_string($respBody) ? $respBody : null,
            'http_status'   => $httpCode,
            'duracao_ms'    => $duracao,
            'erro'          => $erro,
        ]);

        log_message('info', '[ME] ' . $method . ' ' . $path . ' -> ' . $httpCode . ' (' . $duracao . 'ms)');

        if ($httpCode >= 400 || $erro) {
            log_message('error', '[ME] erro ' . $httpCode . ' em ' . $path . ' - ' . ($erro ?: $respBody));
            throw new RuntimeException('Falha Melhor Envio: HTTP ' . $httpCode);
        }

        $json = json_decode((string) $respBody, true);
        return is_array($json) ? $json : [];
    }
}
