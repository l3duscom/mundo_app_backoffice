<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\Cliente;
use App\Entities\Cartao;
use App\Entities\CartaoCredito;
use App\Entities\Pedido;
use App\Entities\Transaction;
use App\Entities\Endereco;
use App\Entities\Evento;
use App\Traits\ValidacoesTrait;
use App\Services\PagarMeService;

class ApiCheckout extends BaseController
{
    use ValidacoesTrait;

    private $clienteModel;
    private $usuarioModel;
    private $cartaoModel;
    private $cartaoCreditoModel;
    private $pedidoModel;
    private $ingressoModel;
    private $grupoUsuarioModel;
    private $transactionModel;
    private $enderecoModel;
    private $asaasService;
    private $notifyService;
    private $pagarmeService;
    private $eventoModel;
    private $ticketModel;

    public function __construct()
    {
        $this->clienteModel = new \App\Models\ClienteModel();
        $this->usuarioModel = new \App\Models\UsuarioModel();
        $this->cartaoModel = new \App\Models\CartaoModel();
        $this->cartaoCreditoModel = new \App\Models\CartaoCreditoModel();
        $this->pedidoModel = new \App\Models\PedidoModel();
        $this->ingressoModel = new \App\Models\IngressoModel();
        $this->grupoUsuarioModel = new \App\Models\GrupoUsuarioModel();
        $this->transactionModel = new \App\Models\TransactionModel();
        $this->enderecoModel = new \App\Models\EnderecoModel();
        $this->asaasService = new \App\Services\AsaasService();
        $this->notifyService = new \App\Services\NotifyService();
        $this->pagarmeService = new \App\Services\PagarmeService();
        $this->eventoModel = new \App\Models\EventoModel();
        $this->ticketModel = new \App\Models\TicketModel();
    }

    private function buscatransactionOu404(string $id = null)
    {
        if (!$id || !$transaction = $this->transactionModel->recuperaTransaction($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos a transação $id");
        }

        return $transaction;
    }

    public function pix($event_id)
    {
        $evento = $this->eventoModel->find($event_id);
        $total = 0;
        $valor_desconto = 0;
        if (isset($_SESSION['carrinho']) && is_array($_SESSION['carrinho'])) {
            foreach ($_SESSION['carrinho'] as $item) {
                if ($item['quantidade'] > 0) {
                    $total += ($item['quantidade'] * $item['unitario']) + ($item['quantidade'] * $item['taxa']);
                }
            }
        }
        return $this->response->setJSON([
            'titulo' => 'Comprar ingressos',
            'total' => $total,
            'valor_desconto' => $valor_desconto,
            'event_id' => $event_id,
            'evento' => $evento
        ]);
    }

    public function cartao($event_id)
    {
        $data = [
            'titulo' => 'Comprar ingressos',
            'event_id' => $event_id
        ];
        return $this->response->setJSON($data);
    }

    public function loja()
    {
        return $this->response->setJSON([
            'titulo' => 'Loja Oficial'
        ]);
    }

    public function obrigado()
    {
        $event_id = 17;
        $evento = $this->eventoModel->find($event_id);
        $items = $this->ticketModel->recuperaAdicionaisPorEvento($event_id);
        $ingressos = array();
        foreach ($items as $item) {
            $ingressos[] = array(
                'id' => $item->id,
                'nome' => $item->nome,
                'preco' => $item->preco,
                'descricao' => $item->descricao,
                'data_inicio' => $item->data_inicio,
                'data_fim' => $item->data_fim,
                'tipo' => $item->tipo,
                'dia' => $item->dia,
                'lote' => $item->lote,
                'categoria' => $item->categoria,
                'data_lote' => $item->data_lote,
                'estoque' => $item->estoque
            );
        }
        $total = $_SESSION['total'] ?? 0;
        return $this->response->setJSON([
            'titulo' => 'Comprar ingressos',
            'items' => $ingressos,
            'evento' => $evento,
            'total' => $total,
            'order_id' => session()->get('order_id') ?? ''
        ]);
    }

    public function finalizarpix($event_id)
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        try {
            if (!isset($data['email'], $data['valor_total'])) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Dados incompletos'])->setStatusCode(400);
            }
            // Simula sessão de carrinho para API
            $carrinho = $data['carrinho'] ?? [];
            if (empty($carrinho) || !is_array($carrinho)) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Carrinho vazio'])->setStatusCode(400);
            }
            // Busca ou cria cliente
            $cliente = $this->clienteModel->withDeleted(true)->where('email', $data['email'])->orderBy('id', 'DESC')->first();
            if (!$cliente) {
                $cliente = new \App\Entities\Cliente($data);
                $this->clienteModel->save($cliente);
                $cliente->id = $this->clienteModel->getInsertID();
            }
            $user_id = $cliente->usuario_id;
            // Cria pedido
            $pedidoData = [
                'evento_id' => $event_id,
                'user_id' => $user_id,
                'codigo' => $this->pedidoModel->geraCodigoPedido(),
                'total' => $data['valor_total'] / 100,
                'frete' => ($data['frete'] ?? '') === 'casa' ? 1 : 0,
                'convite' => $data['convite'] ?? '',
                'forma_pagamento' => 'PIX',
            ];
            $this->pedidoModel->skipValidation(true)->protect(false)->insert($pedidoData);
            $pedido_id = $this->pedidoModel->getInsertID();
            // Registra ingressos
            foreach ($carrinho as $item) {
                for ($i = 0; $i < $item['quantidade']; $i++) {
                    $this->ingressoModel->skipValidation(true)->protect(false)->insert([
                        'pedido_id' => $pedido_id,
                        'user_id' => $user_id,
                        'nome' => $item['nome'],
                        'quantidade' => 1,
                        'valor_unitario' => $item['preco'],
                        'valor' => $item['preco'],
                        'tipo' => $item['tipo'],
                        'ticket_id' => $item['ticket_id'],
                        'codigo' => $user_id . $this->ingressoModel->geraCodigoIngresso(),
                    ]);
                }
            }
            // Cria customer no Asaas
            $customer_id = $cliente->customer_id;
            if (empty($customer_id)) {
                $cobrar = [
                    'nome' => $data['nome'],
                    'cpf' => $data['cpf'],
                    'email' => $data['email'],
                    'telefone' => preg_replace('/[^0-9]/', '', $data['telefone']),
                    'cep' => '',
                    'numero' => '',
                ];
                $customer = $this->asaasService->customers($cobrar);
                if (!isset($customer['id'])) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Erro ao criar cliente na API ASAAS'])->setStatusCode(500);
                }
                $customer_id = $customer['id'];
                $this->clienteModel->protect(false)->update($cliente->id, ['customer_id' => $customer_id]);
            }
            // Cria pagamento PIX
            $payment = $this->asaasService->paymentPix([
                'customer_id' => $customer_id,
                'value' => (float) $data['valor_total'],
                'description' => evento_descricao_pagamento('pix'),
                'externalReference' => 'Api ASAAS'
            ]);
            if (isset($payment['errors'][0])) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Falha ao processar compra'])->setStatusCode(400);
            }
            $transaction = $this->asaasService->obtemQrCode($payment['id']);
            $this->pedidoModel->protect(false)->update($pedido_id, [
                'charge_id' => $payment['id'],
                'status' => $payment['status']
            ]);
            $this->transactionModel->protect(false)->insert([
                'pedido_id' => $pedido_id,
                'charge_id' => $payment['id'],
                'installment_value' => $payment['value'],
                'expire_at' => date('Y-m-d', strtotime('+1 days')),
                'payment' => $payment['billingType'],
                'qrcode' => $transaction['payload'],
                'qrcode_image' => $transaction['encodedImage'],
                'link' => $payment['invoiceUrl']
            ]);
            return $this->response->setJSON([
                'status' => 'ok',
                'pedido_id' => $pedido_id,
                'charge_id' => $payment['id'],
                'qrcode' => $transaction['payload'],
                'qrcode_image' => $transaction['encodedImage'],
                'link' => $payment['invoiceUrl'],
                'valor' => $payment['value'],
                'expire_at' => $transaction['expirationDate'],
                'asaas_status' => $payment['status']
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()])->setStatusCode(500);
        }
    }

    public function finalizarcartao($event_id)
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        try {
            if (!isset($data['email'], $data['valor_total'])) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Dados incompletos'])->setStatusCode(400);
            }
            $carrinho = $data['carrinho'] ?? [];
            if (empty($carrinho) || !is_array($carrinho)) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Carrinho vazio'])->setStatusCode(400);
            }
            $cliente = $this->clienteModel->withDeleted(true)->where('email', $data['email'])->orderBy('id', 'DESC')->first();
            if (!$cliente) {
                $cliente = new \App\Entities\Cliente($data);
                $this->clienteModel->save($cliente);
                $cliente->id = $this->clienteModel->getInsertID();
            }
            $user_id = $cliente->usuario_id;
            $pedidoData = [
                'evento_id' => $event_id,
                'user_id' => $user_id,
                'codigo' => $this->pedidoModel->geraCodigoPedido(),
                'total' => $data['valor_total'],
                'frete' => ($data['frete'] ?? '') === 'casa' ? 1 : 0,
                'convite' => $data['convite'] ?? '',
                'forma_pagamento' => 'CREDIT_CARD',
            ];
            $this->pedidoModel->skipValidation(true)->protect(false)->insert($pedidoData);
            $pedido_id = $this->pedidoModel->getInsertID();
            foreach ($carrinho as $item) {
                for ($i = 0; $i < $item['quantidade']; $i++) {
                    $this->ingressoModel->skipValidation(true)->protect(false)->insert([
                        'pedido_id' => $pedido_id,
                        'user_id' => $user_id,
                        'nome' => $item['nome'],
                        'quantidade' => 1,
                        'valor_unitario' => $item['preco'],
                        'valor' => $item['preco'],
                        'tipo' => $item['tipo'],
                        'ticket_id' => $item['ticket_id'],
                        'codigo' => $user_id . $this->ingressoModel->geraCodigoIngresso(),
                    ]);
                }
            }
            $customer_id = $cliente->customer_id;
            if (empty($customer_id)) {
                $cobrar = [
                    'nome' => $data['nome'],
                    'cpf' => $data['cpf'],
                    'email' => $data['email'],
                    'telefone' => preg_replace('/[^0-9]/', '', $data['telefone']),
                    'cep' => '',
                    'numero' => '',
                ];
                $customer = $this->asaasService->customers($cobrar);
                if (!isset($customer['id'])) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Erro ao criar cliente na API ASAAS'])->setStatusCode(500);
                }
                $customer_id = $customer['id'];
                $this->clienteModel->protect(false)->update($cliente->id, ['customer_id' => $customer_id]);
            }
            $juros = 0.034;
            $installmentCount = $data['installmentCount'] ?? 1;
            $valorFormatado = number_format((float) $data['valor_total'], 2, '.', '');
            if ($installmentCount <= 1) {
                $installmentValue = $valorFormatado;
            } else {
                $installmentValue = ($valorFormatado + ($valorFormatado * $juros * $installmentCount)) / $installmentCount;
            }
            $pay = [
                'customer_id' => $customer_id,
                'installmentCount' => $installmentCount,
                'installmentValue' => (float) $installmentValue,
                'description' => evento_descricao_pagamento(),
                'postalCode' => preg_replace('/[^0-9]/', '', $data['cep'] ?? ''),
                'observations' => 'Api ASAAS',
                'holderName' => $data['holderName'] ?? '',
                'number' => $data['numero_cartao'] ?? '',
                'expiryMonth' => $data['mes_vencimento'] ?? '',
                'expiryYear' => $data['ano_vencimento'] ?? '',
                'ccv' => $data['codigo_seguranca'] ?? '',
                'nome' => $data['nome'],
                'email' => $data['email'],
                'cpf' => $data['cpf'],
                'cep' => $data['cep'] ?? '',
                'numero' => $data['numero'] ?? '',
                'telefone' => preg_replace('/[^0-9]/', '', $data['telefone']),
            ];
            $payment = $this->asaasService->payments($pay);
            if (isset($payment['errors'][0])) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Falha ao processar compra'])->setStatusCode(400);
            }
            $this->pedidoModel->protect(false)->update($pedido_id, [
                'charge_id' => $payment['id'],
                'status' => $payment['status']
            ]);
            $this->transactionModel->protect(false)->insert([
                'pedido_id' => $pedido_id,
                'charge_id' => $payment['id'],
                'installment_value' => $payment['value'],
                'installments' => $installmentCount,
                'payment' => $payment['billingType'],
            ]);
            return $this->response->setJSON([
                'status' => 'ok',
                'pedido_id' => $pedido_id,
                'charge_id' => $payment['id'],
                'asaas_status' => $payment['status'],
                'valor' => $payment['value'],
                'installments' => $installmentCount
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()])->setStatusCode(500);
        }
    }

    public function qrcode($event_id, $id)
    {
        try {
            $transaction = $this->buscatransactionOu404($id);
            $evento = $this->eventoModel->find($event_id);
            
            $convite = $this->usuarioModel->select('usuarios.codigo')
                ->join('pedidos', 'pedidos.user_id = usuarios.id')
                ->where('pedidos.charge_id', $id)
                ->first();
            
            $payment = $this->asaasService->listaCobranca($id);
            $status = $payment['status'];
            
            $indicacoes = $this->pedidoModel->where('convite', $convite->codigo)->where('status', 'paid')->countAllResults();
            
            return $this->response->setJSON([
                'status' => 'success',
                'data' => [
                    'charge_id' => $id,
                    'transaction' => $transaction,
                    'convite' => $convite->codigo,
                    'indicacoes' => $indicacoes,
                    'status' => $status,
                    'event_id' => $event_id,
                    'evento' => $evento,
                    'qrcode' => $transaction->qrcode,
                    'qrcode_image' => $transaction->qrcode_image,
                    'valor' => $transaction->installment_value,
                    'expire_at' => $transaction->expire_at
                ]
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ])->setStatusCode(404);
        }
    }

    // Adicione aqui outros métodos públicos do Checkout, retornando JSON conforme necessário
} 