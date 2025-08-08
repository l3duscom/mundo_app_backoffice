<?php

namespace App\Services;

use Exception;

class ResendService
{
    /**
     * Envia email via API do Resend
     *
     * @param string $destinatario Email do destinatário
     * @param string $assunto Assunto do email
     * @param string $conteudoHtml Conteúdo HTML do email
     * @param string|null $remetenteEmail Email do remetente (opcional, usa configuração padrão)
     * @param string|null $remetenteNome Nome do remetente (opcional, usa configuração padrão)
     * @return bool
     * @throws Exception
     */
    public function enviarEmail(
        string $destinatario, 
        string $assunto, 
        string $conteudoHtml,
        string $remetenteEmail = null,
        string $remetenteNome = null
    ): bool {
        // Configurações do Resend
        $apiKey = env('RESEND_API_KEY');
        $fromEmail = $remetenteEmail ?? env('email.fromEmail');
        $fromName = $remetenteNome ?? env('email.fromName');

        // Validar configurações antes de enviar
        $this->validarConfiguracoes($apiKey, $fromEmail, $fromName);

        // Dados do email
        $emailData = [
            'from' => $fromName . ' <' . $fromEmail . '>',
            'to' => [$destinatario],
            'subject' => $assunto,
            'html' => $conteudoHtml
        ];

        // Enviar via API REST do Resend
        return $this->enviarViaApi($emailData, $apiKey);
    }

    /**
     * Envia email para múltiplos destinatários
     *
     * @param array $destinatarios Array de emails dos destinatários
     * @param string $assunto Assunto do email
     * @param string $conteudoHtml Conteúdo HTML do email
     * @param string|null $remetenteEmail Email do remetente (opcional)
     * @param string|null $remetenteNome Nome do remetente (opcional)
     * @return bool
     * @throws Exception
     */
    public function enviarEmailMultiplos(
        array $destinatarios, 
        string $assunto, 
        string $conteudoHtml,
        string $remetenteEmail = null,
        string $remetenteNome = null
    ): bool {
        // Configurações do Resend
        $apiKey = env('RESEND_API_KEY');
        $fromEmail = $remetenteEmail ?? env('email.fromEmail');
        $fromName = $remetenteNome ?? env('email.fromName');

        // Validar configurações antes de enviar
        $this->validarConfiguracoes($apiKey, $fromEmail, $fromName);

        // Dados do email
        $emailData = [
            'from' => $fromName . ' <' . $fromEmail . '>',
            'to' => $destinatarios,
            'subject' => $assunto,
            'html' => $conteudoHtml
        ];

        // Enviar via API REST do Resend
        return $this->enviarViaApi($emailData, $apiKey);
    }

    /**
     * Valida as configurações necessárias para o envio
     *
     * @param string|null $apiKey
     * @param string|null $fromEmail
     * @param string|null $fromName
     * @throws Exception
     */
    private function validarConfiguracoes(?string $apiKey, ?string $fromEmail, ?string $fromName): void
    {
        if (empty($apiKey) || $apiKey === 're_xxxxxxxxx') {
            log_message('error', 'RESEND_API_KEY não configurada ou usando valor padrão');
            throw new Exception('RESEND_API_KEY não configurada corretamente no arquivo .env');
        }

        if (empty($fromEmail) || empty($fromName)) {
            log_message('error', 'Configurações de email não definidas: fromEmail=' . $fromEmail . ', fromName=' . $fromName);
            throw new Exception('Configurações de email (email.fromEmail ou email.fromName) não definidas no arquivo .env');
        }
    }

    /**
     * Envia o email via API do Resend usando cURL
     *
     * @param array $emailData Dados do email
     * @param string $apiKey Chave da API
     * @return bool
     * @throws Exception
     */
    private function enviarViaApi(array $emailData, string $apiKey): bool
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.resend.com/emails');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]);
        
        // Configurações SSL para Windows
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Log da resposta para debugging
        log_message('info', 'Resend API Response - HTTP Code: ' . $httpCode . '. Response: ' . $response . '. cURL Error: ' . $curlError);

        if ($httpCode !== 200) {
            // Log do erro detalhado
            log_message('error', 'Erro ao enviar email via Resend. HTTP Code: ' . $httpCode . '. Response: ' . $response . '. cURL Error: ' . $curlError);
            
            // Lançar exceção com mais detalhes
            $errorMsg = 'Falha no envio do email via Resend. HTTP Code: ' . $httpCode;
            if (!empty($response)) {
                $errorMsg .= '. Response: ' . $response;
            }
            if (!empty($curlError)) {
                $errorMsg .= '. cURL Error: ' . $curlError;
            }
            throw new Exception($errorMsg);
        }

        return true;
    }

    /**
     * Testa a conexão com a API do Resend
     *
     * @return bool
     */
    public function testarConexao(): bool
    {
        try {
            $this->enviarEmail(
                'delivered@resend.dev', // Email de teste do Resend
                'Teste de Conexão - ' . date('Y-m-d H:i:s'),
                '<h1>Teste de Conexão</h1><p>Este é um teste de conexão com a API do Resend.</p>'
            );
            return true;
        } catch (Exception $e) {
            log_message('error', 'Teste de conexão Resend falhou: ' . $e->getMessage());
            return false;
        }
    }
}
