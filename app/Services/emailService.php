<?php

namespace App\Services;

use CodeIgniter\Email\Email;
use App\Entities\Cliente;

class EmailService
{
    protected $email;

    public function __construct()
    {
        $this->email = \Config\Services::email();
    }

    public function enviarAcessoCliente(Cliente $cliente, string $senha = null): void
    {
        $mensagem = "Saudações {$cliente->nome},\n\n" .
            "Sua conta no Mundo Dream foi criada com sucesso!\n" .
            "Você já pode adquirir seus ingressos para o Dreamfest.\n\n" .
            "*Acesse:* " . site_url("/") . "\n" .
            "*E-mail:* {$cliente->email}\n" .
            ($senha ? "*Senha:* {$senha}\n\n" : "") .
            "Seja bem-vindo(a)!";

        $this->enviar($cliente->email, 'Bem-vindo ao Mundo Dream 🎉', $mensagem);
    }

    public function enviarQrCode(Cliente $cliente, array $qrData, array $payment): void
    {
        $mensagem = "Olá {$cliente->nome},\n\n" .
            "Seu pedido foi realizado com sucesso!\n\n" .
            "Abaixo está o link e QR Code para pagamento via PIX:\n" .
            site_url("checkout/qrcode/{$payment['id']}") . "\n\n" .
            "Pix copia e cola: {$qrData['payload']}\n\n" .
            "Valor: R$ " . number_format($payment['value'], 2, ',', '.') . "\n" .
            "Vencimento: " . date('d/m/Y', strtotime($payment['dueDate'] ?? '+1 day')) . "\n\n" .
            "Obrigado por comprar com a gente!";

        $this->enviar($cliente->email, 'Pagamento via PIX - Dreamfest 🎫', $mensagem);
    }

    public function enviarConfirmacaoCartao(Cliente $cliente): void
    {
        $mensagem = "Olá {$cliente->nome},\n\n" .
            "Recebemos a confirmação do seu pagamento via cartão.\n" .
            "Seus ingressos estarão disponíveis na sua área do usuário.\n\n" .
            "Acesse: " . site_url("/") . "\n\n" .
            "Obrigado por participar do Dreamfest!";

        $this->enviar($cliente->email, 'Confirmação de Pagamento - Dreamfest 💳', $mensagem);
    }

    protected function enviar(string $para, string $assunto, string $mensagem): void
    {
        try {
            $this->email->clear();
            $this->email->setTo($para);
            $this->email->setSubject($assunto);
            $this->email->setMessage(nl2br($mensagem));
            $this->email->send();
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao enviar e-mail: ' . $e->getMessage());
        }
    }
}
