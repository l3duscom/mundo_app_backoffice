<?php

/**
 * Helper para captura e gerenciamento de parâmetros UTM (integração UTMify)
 */

if (!function_exists('capture_utm_params')) {
    /**
     * Captura parâmetros UTM da URL e salva na sessão PHP
     */
    function capture_utm_params(): void
    {
        $request = \Config\Services::request();
        $session = session();

        $utmKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];

        foreach ($utmKeys as $key) {
            $value = $request->getGet($key);
            if ($value !== null && $value !== '') {
                $session->set($key, $value);
            }
        }
    }
}

if (!function_exists('get_utm_params')) {
    /**
     * Retorna os parâmetros UTM armazenados na sessão
     *
     * @return array
     */
    function get_utm_params(): array
    {
        $session = session();

        return [
            'utm_source'   => $session->get('utm_source') ?? '',
            'utm_medium'   => $session->get('utm_medium') ?? '',
            'utm_campaign' => $session->get('utm_campaign') ?? '',
            'utm_content'  => $session->get('utm_content') ?? '',
            'utm_term'     => $session->get('utm_term') ?? '',
        ];
    }
}

if (!function_exists('get_utmify_purchase_script')) {
    /**
     * Gera o script de evento Purchase para o pixel UTMify
     *
     * @param string $orderId
     * @param float  $valor
     * @return string
     */
    function get_utmify_purchase_script(string $orderId, float $valor): string
    {
        $valorFormatado = number_format($valor, 2, '.', '');

        return <<<HTML
<script>
    if (typeof window.utmifyPixel !== 'undefined') {
        window.utmifyPixel.track('Purchase', {
            value: {$valorFormatado},
            currency: 'BRL',
            order_id: '{$orderId}'
        });
    }
</script>
HTML;
    }
}
