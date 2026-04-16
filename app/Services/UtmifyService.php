<?php

namespace App\Services;

use App\Models\PedidoUtmModel;

class UtmifyService
{
    private string $apiToken = 'rTHVfZ0W4FaTkSz4cgcMw0eGXst1kq7NJCfT';
    private string $apiUrl   = 'https://api.utmify.com.br/api-credentials/orders';

    /**
     * Envia postback de venda confirmada para o UTMify
     *
     * @param object $pedido   Entidade do pedido
     * @param string $email    Email do cliente
     * @param string $nome     Nome do cliente
     * @return array           Resultado da chamada
     */
    public function notifyPurchase(object $pedido, string $email, string $nome = ''): array
    {
        // Busca UTMs do pedido
        $pedidoUtmModel = new PedidoUtmModel();
        $utms = $pedidoUtmModel->buscaPorPedido($pedido->id);

        $payload = [
            'orderId'       => (string) $pedido->id,
            'platform'      => 'mundo_dream',
            'paymentMethod'  => $pedido->forma_pagamento ?? 'PIX',
            'status'        => 'paid',
            'createdAt'     => $pedido->created_at ?? date('c'),
            'approvedDate'  => date('c'),
            'customer'      => [
                'name'  => $nome,
                'email' => $email,
            ],
            'purchase'      => [
                [
                    'productName' => 'Ingresso - Pedido #' . ($pedido->codigo ?? $pedido->id),
                    'plans'       => [
                        [
                            'planName' => $pedido->forma_pagamento ?? 'PIX',
                            'value'    => (float) ($pedido->total ?? 0),
                        ]
                    ],
                ]
            ],
            'trackingParameters' => [
                'src'             => $utms->utm_source ?? null,
                'utm_source'      => $utms->utm_source ?? null,
                'utm_medium'      => $utms->utm_medium ?? null,
                'utm_campaign'    => $utms->utm_campaign ?? null,
                'utm_content'     => $utms->utm_content ?? null,
                'utm_term'        => $utms->utm_term ?? null,
            ],
        ];

        return $this->sendRequest($payload);
    }

    /**
     * Envia requisição para a API do UTMify
     */
    private function sendRequest(array $payload): array
    {
        $headers = [
            'Content-Type: application/json',
            'x-api-token: ' . $this->apiToken,
        ];

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                log_message('error', 'UTMify cURL error: ' . $error);
                return ['success' => false, 'error' => $error];
            }

            log_message('info', 'UTMify postback enviado - Pedido #' . ($payload['orderId'] ?? '') . ' - HTTP ' . $httpCode . ' - Response: ' . $response);

            return [
                'success'   => $httpCode >= 200 && $httpCode < 300,
                'http_code' => $httpCode,
                'response'  => json_decode($response, true) ?? $response,
            ];
        } catch (\Exception $e) {
            log_message('error', 'UTMify exception: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
