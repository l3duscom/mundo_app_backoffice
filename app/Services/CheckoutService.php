<?php

namespace App\Services;

use App\Models\ClienteModel;
use App\Models\PedidoModel;
use App\Models\IngressoModel;
use App\Models\TransactionModel;
use App\Models\EnderecoModel;
use App\Models\EventoModel;
use App\Services\AsaasService;
use App\Services\PagarmeService;
use App\Entities\Cliente;
use Config\Services;
use DateTime;
use DateTimeZone;

class CheckoutService
{
    protected $clienteModel;
    protected $pedidoModel;
    protected $ingressoModel;
    protected $transactionModel;
    protected $enderecoModel;
    protected $eventoModel;
    protected $ticketModel;
    protected $asaasService;
    protected $pagarmeService;

    public function __construct()
    {
        $this->clienteModel = new ClienteModel();
        $this->pedidoModel = new PedidoModel();
        $this->ingressoModel = new IngressoModel();
        $this->transactionModel = new TransactionModel();
        $this->enderecoModel = new EnderecoModel();
        $this->eventoModel = new EventoModel();
        $this->ticketModel = new \App\Models\TicketModel();
        $this->asaasService = new AsaasService();
        $this->pagarmeService = new PagarmeService();
    }

    public function processarPagamentoPix(array $post): array
    {
        helper('text');

        if (!isset($post['email'], $post['valor_total'], $_SESSION['carrinho'])) {
            return ['erro' => 'Dados incompletos'];
        }

        $cliente = $this->buscaOuCriaCliente($post);
        $user_id = $cliente->usuario_id;

        $pedido_id = $this->criaPedido($post, $user_id, 'PIX');
        $this->registraIngressos($pedido_id, $user_id);

        $customer_id = $this->obtemOuCriaCustomerIdAsaas($cliente, $post);
        $this->clienteModel->update($cliente->id, ['customer_id' => $customer_id]);

        $payment = $this->asaasService->paymentPix([
            'customer_id' => $customer_id,
            'value' => (float)$post['valor_total'],
            'description' => 'Ingressos Dreamfest 25',
            'externalReference' => 'Api ASAAS'
        ]);

        if (isset($payment['errors'][0])) {
            return ['erro' => 'Erro ao processar o pagamento'];
        }

        $transaction = $this->asaasService->obtemQrCode($payment['id']);

        $this->pedidoModel->update($pedido_id, [
            'charge_id' => $payment['id'],
            'status' => $payment['status']
        ]);

        // Atualiza os pedidos vinculados com o charge_id
        $this->atualizaPedidosVinculados($pedido_id, $payment['id']);

        $this->transactionModel->insert([
            'pedido_id' => $pedido_id,
            'charge_id' => $payment['id'],
            'installment_value' => $payment['value'],
            'expire_at' => date('Y-m-d', strtotime('+1 days')),
            'payment' => $payment['billingType'],
            'qrcode' => $transaction['payload'],
            'qrcode_image' => $transaction['encodedImage'],
            'link' => $payment['invoiceUrl']
        ]);

        Services::emailService()->enviarQrCode($cliente, $transaction, $payment);

        unset($_SESSION['carrinho']);

        return [
            'token' => csrf_hash(),
            'id' => $payment['id']
        ];
    }

    public function processarPagamentoCartao(array $post): array
    {
        if (!isset($post['email'], $post['valor_total'], $_SESSION['carrinho'])) {
            return ['erro' => 'Dados incompletos'];
        }

        $cliente = $this->buscaOuCriaCliente($post);
        $user_id = $cliente->usuario_id;

        $pedido_id = $this->criaPedido($post, $user_id, 'CREDIT_CARD');
        $this->registraIngressos($pedido_id, $user_id);

        $this->clienteModel->update($cliente->id, [
            'customer_id' => $this->obtemOuCriaCustomerIdAsaas($cliente, $post)
        ]);

        $juros = 0.034;
        $installments = (int)$post['installmentCount'];
        $valor = number_format((float)$post['valor_total'], 2, '.', '');

        $valor_com_juros = $installments <= 1 ? $valor : number_format($valor + ($valor * $juros * $installments), 2, '.', '');

        $telefone = preg_replace('/[^0-9]/', '', $post['telefone']);
        preg_match('/^(\d{2})(\d{8,9})$/', $telefone, $matches);

        $evento = $this->eventoModel->find(12);
        $valorCentavos = (int) preg_replace('/\D/', '', $valor_com_juros);

        $pay = [
            'items' => [[
                'amount' => $valorCentavos,
                'description' => 'Ingresso(s) ' . $evento->nome,
                'quantity' => 1,
                'code' => 'product_code'
            ]],
            'customer' => [
                'name' => $post['nome'],
                'email' => $post['email'],
                'document' => preg_replace('/\D/', '', $post['cpf']),
                'type' => 'individual',
                'phones' => [
                    'mobile_phone' => [
                        'country_code' => '55',
                        'area_code' => $matches[1] ?? '',
                        'number' => $matches[2] ?? ''
                    ]
                ]
            ],
            'payments' => [[
                'payment_method' => 'credit_card',
                'credit_card' => [
                    'installments' => $installments,
                    'card' => [
                        'number' => $post['numero_cartao'],
                        'holder_name' => $post['holderName'],
                        'exp_month' => $post['mes_vencimento'],
                        'exp_year' => $post['ano_vencimento'],
                        'cvv' => $post['codigo_seguranca'],
                        'billing_address' => [
                            'country' => 'BR',
                            'state' => $post['estado'],
                            'city' => $post['cidade'],
                            'line_1' => $post['endereco'],
                            'zip_code' => preg_replace('/[^0-9]/', '', $post['cep'])
                        ]
                    ]
                ]
            ]],
            'options' => [
                'antifraud_enabled' => false
            ],
            'initiator_transaction_key' => uniqid()
        ];

        $payment = $this->pagarmeService->createTransaction($pay);

        if (isset($payment['errors'][0]) || empty($payment['id'])) {
            return ['erro' => 'Erro ao processar pagamento com cartão'];
        }

        $status = in_array($payment['status'], ['paid', 'CONFIRMED', 'RECEIVED']) ? 'CONFIRMED' : $payment['status'];

        $this->pedidoModel->update($pedido_id, [
            'charge_id' => $payment['id'],
            'status' => $status
        ]);

        // Atualiza os pedidos vinculados com o charge_id
        $this->atualizaPedidosVinculados($pedido_id, $payment['id']);

        $this->transactionModel->insert([
            'pedido_id' => $pedido_id,
            'charge_id' => $payment['id'],
            'installment_value' => $post['valor_total'],
            'installments' => $installments,
            'payment' => 'CREDIT_CARD'
        ]);

        $this->enderecoModel->insert([
            'pedido_id' => $pedido_id,
            'endereco' => $post['endereco'],
            'numero' => $post['numero'],
            'bairro' => $post['bairro'],
            'cep' => preg_replace('/[^0-9]/', '', $post['cep']),
            'cidade' => $post['cidade'],
            'estado' => $post['estado']
        ]);

        Services::emailService()->enviarConfirmacaoCartao($cliente);

        unset($_SESSION['carrinho']);

        return [
            'status' => $status === 'CONFIRMED' ? 'ok' : 'erro',
            'erro' => $status !== 'CONFIRMED' ? 'Pagamento não confirmado' : null
        ];
    }

    private function buscaOuCriaCliente(array $post)
    {
        $cliente = $this->clienteModel
            ->withDeleted(true)
            ->where('email', $post['email'])
            ->orderBy('id', 'DESC')
            ->first();

        if ($cliente) {
            return $cliente;
        }

        $cliente = new Cliente($post);

        if ($this->clienteModel->save($cliente)) {
            $cliente->id = $this->clienteModel->getInsertID();
            Services::auth()->criaUsuarioParaCliente($cliente);
            Services::emailService()->enviarAcessoCliente($cliente);
            return $this->clienteModel->find($cliente->id);
        }

        throw new \Exception('Erro ao salvar cliente');
    }

    private function criaPedido(array $post, int $user_id, string $forma_pagamento): int
    {
        $frete = ($post['frete'] ?? '') === 'casa' ? 1 : 0;

        $this->pedidoModel->insert([
            'evento_id' => 12,
            'user_id' => $user_id,
            'codigo' => $this->pedidoModel->geraCodigoPedido(),
            'total' => $post['valor_total'] / 100,
            'frete' => $frete,
            'convite' => $post['convite'] ?? '',
            'forma_pagamento' => $forma_pagamento
        ]);

        return $this->pedidoModel->getInsertID();
    }

    private function registraIngressos(int $pedido_id, int $user_id): void
    {
        foreach ($_SESSION['carrinho'] as $item) {
            for ($i = 0; $i < $item['quantidade']; $i++) {
                // Registra o ingresso principal
                $this->ingressoModel->insert([
                    'pedido_id' => $pedido_id,
                    'user_id' => $user_id,
                    'nome' => $item['nome'],
                    'quantidade' => 1,
                    'valor_unitario' => $item['preco'],
                    'valor' => $item['preco'],
                    'tipo' => $item['tipo'],
                    'ticket_id' => $item['ticket_id'],
                    'codigo' => $user_id . $this->ingressoModel->geraCodigoIngresso()
                ]);

                // Verifica se o ticket tem tickets vinculados (parent_ticket_id)
                $ticketsVinculados = $this->ticketModel->buscaTicketsVinculados($item['ticket_id']);
                
                // Se encontrou tickets vinculados, cria pedidos separados para cada um
                foreach ($ticketsVinculados as $ticketVinculado) {
                    // Busca o pedido original para copiar os dados
                    $pedidoOriginal = $this->pedidoModel->find($pedido_id);
                    
                    // Cria um novo pedido para o evento do ticket vinculado
                    $novoPedido = [
                        'evento_id' => $ticketVinculado->event_id,
                        'user_id' => $user_id,
                        'codigo' => $this->pedidoModel->geraCodigoPedido(),
                        'total' => $ticketVinculado->preco,
                        'forma_pagamento' => 'PACK',
                        'status' => $pedidoOriginal->status,
                        'frete' => $pedidoOriginal->frete ?? 0,
                        'convite' => $pedidoOriginal->convite ?? '',
                        'charge_id' => $pedidoOriginal->charge_id, // Copia o charge_id do pedido principal
                    ];
                    
                    $this->pedidoModel->insert($novoPedido);
                    $novoPedidoId = $this->pedidoModel->getInsertID();
                    
                    // Registra o ingresso vinculado no novo pedido
                    $this->ingressoModel->insert([
                        'pedido_id' => $novoPedidoId,
                        'user_id' => $user_id,
                        'nome' => $ticketVinculado->nome,
                        'quantidade' => 1,
                        'valor_unitario' => $ticketVinculado->preco,
                        'valor' => $ticketVinculado->preco,
                        'tipo' => $ticketVinculado->tipo,
                        'ticket_id' => $ticketVinculado->id,
                        'codigo' => $user_id . $this->ingressoModel->geraCodigoIngresso()
                    ]);
                }
            }
        }
    }

    private function obtemOuCriaCustomerIdAsaas($cliente, array $post): string
    {
        if (!empty($cliente->customer_id)) {
            return $cliente->customer_id;
        }

        $dados = [
            'nome' => $post['nome'],
            'cpf' => $post['cpf'],
            'email' => $post['email'],
            'telefone' => preg_replace('/[^0-9]/', '', $post['telefone']),
            'cep' => '',
            'numero' => ''
        ];

        $customer = $this->asaasService->customers($dados);

        if (!isset($customer['id'])) {
            throw new \Exception('Erro ao criar cliente no ASAAS');
        }

        return $customer['id'];
    }

    /**
     * Atualiza os pedidos vinculados com o charge_id após o pagamento ser confirmado
     */
    private function atualizaPedidosVinculados(int $pedido_id, string $charge_id): void
    {
        // Busca todos os pedidos vinculados (forma_pagamento = 'PACK') do mesmo usuário
        $pedidoOriginal = $this->pedidoModel->find($pedido_id);
        $pedidosVinculados = $this->pedidoModel->where('user_id', $pedidoOriginal->user_id)
            ->where('forma_pagamento', 'PACK')
            ->where('charge_id IS NULL')
            ->findAll();

        // Atualiza o charge_id em todos os pedidos vinculados
        foreach ($pedidosVinculados as $pedidoVinculado) {
            $this->pedidoModel->update($pedidoVinculado->id, [
                'charge_id' => $charge_id,
                'status' => $pedidoOriginal->status
            ]);
        }
    }
}
