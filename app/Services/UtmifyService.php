<?php

namespace App\Services;

use App\Models\PedidoUtmModel;

class UtmifyService
{
    private string $apiToken = 'rTHVfZ0W4FaTkSz4cgcMw0eGXst1kq7NJCfT';
    private string $apiUrl   = 'https://api.utmify.com.br/api-credentials/orders';

    /**
     * Mapeia forma de pagamento do sistema para o formato aceito pelo UTMify
     */
    private function mapPaymentMethod(?string $formaPagamento): string
    {
        $map = [
            'PIX'         => 'pix',
            'CREDIT_CARD' => 'credit_card',
            'BOLETO'      => 'boleto',
            'PACK'        => 'pix',
        ];

        return $map[strtoupper($formaPagamento ?? '')] ?? 'unknown';
    }

    /**
     * Converte created_at (pode ser string ou objeto DateTime) para string ISO
     */
    private function formatDate($date): string
    {
        if ($date === null) {
            return date('Y-m-d\TH:i:s\Z');
        }

        if (is_string($date)) {
            return date('Y-m-d\TH:i:s\Z', strtotime($date));
        }

        if ($date instanceof \DateTime || $date instanceof \DateTimeInterface) {
            return $date->format('Y-m-d\TH:i:s\Z');
        }

        // Se for objeto serializado do CI4, tenta extrair a data
        if (is_object($date) && isset($date->date)) {
            return date('Y-m-d\TH:i:s\Z', strtotime($date->date));
        }

        return date('Y-m-d\TH:i:s\Z');
    }

    /**
     * Envia postback de venda confirmada para o UTMify
     *
     * @param object      $pedido   Entidade do pedido
     * @param object|null $cliente  Entidade do cliente (com nome, email, telefone, cpf)
     * @return array                Resultado da chamada
     */
    public function notifyPurchase(object $pedido, ?object $cliente = null): array
    {
        // Busca UTMs do pedido
        $pedidoUtmModel = new PedidoUtmModel();
        $utms = $pedidoUtmModel->buscaPorPedido($pedido->id);

        $valorTotal = (float) ($pedido->total ?? 0);
        $valorCentavos = (int) round($valorTotal * 100);

        $payload = [
            'orderId'       => (string) $pedido->id,
            'platform'      => 'mundo_dream',
            'paymentMethod' => $this->mapPaymentMethod($pedido->forma_pagamento ?? null),
            'status'        => 'paid',
            'createdAt'     => $this->formatDate($pedido->created_at ?? null),
            'approvedDate'  => date('Y-m-d\TH:i:s\Z'),
            'customer'      => [
                'name'     => $cliente->nome ?? '',
                'email'    => $cliente->email ?? '',
                'phone'    => $cliente->telefone ?? null,
                'document' => $cliente->cpf ?? null,
            ],
            'products'      => [
                [
                    'id'           => (string) ($pedido->id ?? ''),
                    'name'         => 'Ingresso - Pedido #' . ($pedido->codigo ?? $pedido->id),
                    'planId'       => (string) ($pedido->evento_id ?? '1'),
                    'planName'     => $pedido->forma_pagamento ?? 'PIX',
                    'quantity'     => 1,
                    'priceInCents' => $valorCentavos,
                ]
            ],
            'commission'    => [
                'totalPriceInCents'      => $valorCentavos,
                'gatewayFeeInCents'      => 0,
                'userCommissionInCents'  => $valorCentavos,
            ],
            'trackingParameters' => [
                'src'          => $utms->utm_source ?? null,
                'utm_source'   => $utms->utm_source ?? null,
                'utm_medium'   => $utms->utm_medium ?? null,
                'utm_campaign' => $utms->utm_campaign ?? null,
                'utm_content'  => $utms->utm_content ?? null,
                'utm_term'     => $utms->utm_term ?? null,
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
