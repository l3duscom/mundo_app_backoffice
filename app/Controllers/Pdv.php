<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\AsaasService;
use App\Services\ResendService;

class Pdv extends BaseController
{
    private $eventoModel;
    private $ticketModel;
    private $pedidoModel;
    private $ingressoModel;
    private $usuarioModel;
    private $clienteModel;
    private $transactionModel;
    private $asaasService;
    private $resendService;

    public function __construct()
    {
        $this->eventoModel = new \App\Models\EventoModel();
        $this->ticketModel = new \App\Models\TicketModel();
        $this->pedidoModel = new \App\Models\PedidoModel();
        $this->ingressoModel = new \App\Models\IngressoModel();
        $this->usuarioModel = new \App\Models\UsuarioModel();
        $this->clienteModel = new \App\Models\ClienteModel();
        $this->transactionModel = new \App\Models\TransactionModel();
        $this->asaasService = new AsaasService();
        $this->resendService = new ResendService();
    }

    /**
     * Dashboard PDV - Lista eventos ativos
     */
    public function dashboard()
    {
        $usuario = $this->usuarioLogado();

        // Verifica se é PDV
        if (!$usuario->is_pdv) {
            return redirect()->to(site_url('home'));
        }

        // Busca eventos ativos e vigentes
        $eventos = $this->eventoModel
            ->where('ativo', 1)
            ->where('data_fim >=', date('Y-m-d'))
            ->orderBy('data_inicio', 'ASC')
            ->findAll();

        $data = [
            'titulo' => 'PDV - Selecione o Evento',
            'usuario' => $usuario,
            'eventos' => $eventos,
        ];

        return view('Pdv/dashboard', $data);
    }

    /**
     * Tela de vendas - Seleção de ingressos
     */
    public function vender(int $event_id = null)
    {
        $usuario = $this->usuarioLogado();

        if (!$usuario->is_pdv) {
            return redirect()->to(site_url('home'));
        }

        if (!$event_id) {
            return redirect()->to(site_url('pdv/dashboard'))->with('atencao', 'Selecione um evento.');
        }

        $evento = $this->eventoModel->find($event_id);
        if (!$evento) {
            return redirect()->to(site_url('pdv/dashboard'))->with('erro', 'Evento não encontrado.');
        }

        // Busca ingressos do evento agrupados por categoria
        $tickets = $this->ticketModel
            ->where('event_id', $event_id)
            ->where('ativo', 1)
            ->where('quantidade >= estoque')
            ->where('data_lote >=', date('Y-m-d H:i:s'))
            ->orderBy('categoria', 'ASC')
            ->orderBy('nome', 'ASC')
            ->findAll();

        // Agrupa por categoria
        $categorias = [];
        foreach ($tickets as $ticket) {
            $cat = strtolower($ticket->categoria ?? 'comum');
            if (!isset($categorias[$cat])) {
                $categorias[$cat] = [];
            }
            $categorias[$cat][] = $ticket;
        }

        // Inicializa carrinho PDV na sessão se não existir
        if (!session()->has('pdv_carrinho')) {
            session()->set('pdv_carrinho', []);
        }

        $data = [
            'titulo' => 'PDV - ' . $evento->nome,
            'usuario' => $usuario,
            'evento' => $evento,
            'tickets' => $tickets,
            'categorias' => $categorias,
            'carrinho' => session()->get('pdv_carrinho'),
        ];

        return view('Pdv/vender', $data);
    }

    /**
     * AJAX - Adiciona item ao carrinho
     */
    public function adicionarItem()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['erro' => 'Acesso não permitido']);
        }

        $ticket_id = $this->request->getPost('ticket_id');
        $ticket = $this->ticketModel->find($ticket_id);

        if (!$ticket) {
            return $this->response->setJSON(['erro' => 'Ingresso não encontrado']);
        }

        $carrinho = session()->get('pdv_carrinho') ?? [];

        if (isset($carrinho[$ticket_id])) {
            $carrinho[$ticket_id]['quantidade']++;
        } else {
            $taxa = $ticket->preco * 0.07;
            $carrinho[$ticket_id] = [
                'ticket_id' => $ticket_id,
                'nome' => $ticket->nome,
                'categoria' => $ticket->categoria,
                'preco' => $ticket->preco,
                'taxa' => $taxa,
                'total' => $ticket->preco + $taxa,
                'quantidade' => 1,
            ];
        }

        session()->set('pdv_carrinho', $carrinho);

        return $this->response->setJSON([
            'sucesso' => true,
            'carrinho' => $carrinho,
            'total' => $this->calcularTotalCarrinho($carrinho),
            'token' => csrf_hash(),
        ]);
    }

    /**
     * AJAX - Remove item do carrinho
     */
    public function removerItem()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['erro' => 'Acesso não permitido']);
        }

        $ticket_id = $this->request->getPost('ticket_id');
        $carrinho = session()->get('pdv_carrinho') ?? [];

        if (isset($carrinho[$ticket_id])) {
            if ($carrinho[$ticket_id]['quantidade'] > 1) {
                $carrinho[$ticket_id]['quantidade']--;
            } else {
                unset($carrinho[$ticket_id]);
            }
        }

        session()->set('pdv_carrinho', $carrinho);

        return $this->response->setJSON([
            'sucesso' => true,
            'carrinho' => $carrinho,
            'total' => $this->calcularTotalCarrinho($carrinho),
            'token' => csrf_hash(),
        ]);
    }

    /**
     * AJAX - Limpa carrinho
     */
    public function limparCarrinho()
    {
        session()->remove('pdv_carrinho');
        session()->remove('pdv_event_id');

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['sucesso' => true]);
        }

        return redirect()->to(site_url('pdv/dashboard'));
    }

    /**
     * Checkout - Dados do cliente, entrega e pagamento
     */
    public function checkout(int $event_id = null)
    {
        $usuario = $this->usuarioLogado();

        if (!$usuario->is_pdv) {
            return redirect()->to(site_url('home'));
        }

        $carrinho = session()->get('pdv_carrinho') ?? [];

        if (empty($carrinho)) {
            return redirect()->to(site_url('pdv/dashboard'))->with('atencao', 'Carrinho vazio.');
        }

        $evento = $this->eventoModel->find($event_id);

        $data = [
            'titulo' => 'PDV - Checkout',
            'usuario' => $usuario,
            'evento' => $evento,
            'carrinho' => $carrinho,
            'total' => $this->calcularTotalCarrinho($carrinho),
        ];

        return view('Pdv/checkout', $data);
    }

    /**
     * Processa a venda
     */
    public function processarVenda()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $usuario = $this->usuarioLogado();

        if (!$usuario->is_pdv) {
            return $this->response->setJSON(['erro' => 'Acesso não permitido']);
        }

        $carrinho = session()->get('pdv_carrinho') ?? [];

        if (empty($carrinho)) {
            return $this->response->setJSON(['erro' => 'Carrinho vazio']);
        }

        $nome = $this->request->getPost('nome');
        $email = $this->request->getPost('email');
        $cpf = $this->request->getPost('cpf');
        $telefone = $this->request->getPost('telefone');
        $entrega = $this->request->getPost('entrega'); // 'local' ou 'casa'
        $formaPagamento = $this->request->getPost('forma_pagamento'); // 'pix', 'dinheiro', 'cartao'
        $event_id = $this->request->getPost('event_id');

        // Calcula total
        $total = $this->calcularTotalCarrinho($carrinho);

        // Adiciona taxa de entrega se for em casa
        $taxaEntrega = 0;
        if ($entrega === 'casa') {
            $taxaEntrega = 25.00;
            $total += $taxaEntrega;
        }

        // Busca ou cria cliente
        $cliente = $this->buscarOuCriarCliente($nome, $email, $cpf, $telefone);
        $userId = $cliente->usuario_id;

        // Gera código do pedido
        $codigoPedido = $this->pedidoModel->geraCodigoPedido();

        // Cria o pedido
        $frete = $entrega === 'casa' ? 1 : 0;
        $pedido = [
            'evento_id' => $event_id,
            'user_id' => $userId,
            'codigo' => $codigoPedido,
            'total' => $total,
            'frete' => $frete,
            'forma_pagamento' => strtoupper($formaPagamento),
            'status' => 'PENDING',
        ];

        $this->pedidoModel->skipValidation(true)->protect(false)->insert($pedido);
        $pedidoId = $this->pedidoModel->getInsertID();

        // Cria os ingressos
        foreach ($carrinho as $item) {
            for ($i = 0; $i < $item['quantidade']; $i++) {
                $ingresso = [
                    'pedido_id' => $pedidoId,
                    'user_id' => $userId,
                    'nome' => $item['nome'],
                    'quantidade' => 1,
                    'valor_unitario' => $item['preco'],
                    'valor' => $item['total'],
                    'tipo' => $item['categoria'] ?? 'comum',
                    'ticket_id' => $item['ticket_id'],
                    'codigo' => $userId . $this->ingressoModel->geraCodigoIngresso(),
                ];
                $this->ingressoModel->skipValidation(true)->protect(false)->insert($ingresso);

                // Atualiza estoque
                $this->ticketModel
                    ->where('id', $item['ticket_id'])
                    ->set('estoque', 'estoque + 1', false)
                    ->update();
            }
        }

        // Se for PIX, gera cobrança
        if ($formaPagamento === 'pix') {
            $cobrancaPix = $this->gerarCobrancaPix($pedidoId, $total, $nome, $cpf, $email, $telefone);
            
            // Verifica se houve erro
            if (isset($cobrancaPix['erro'])) {
                return $this->response->setJSON([
                    'erro' => $cobrancaPix['erro'],
                    'token' => csrf_hash(),
                ]);
            }

            return $this->response->setJSON([
                'sucesso' => true,
                'pedido_id' => $pedidoId,
                'forma_pagamento' => 'pix',
                'pix' => $cobrancaPix,
                'token' => csrf_hash(),
            ]);
        }

        // Para dinheiro/cartão, aguarda confirmação manual
        return $this->response->setJSON([
            'sucesso' => true,
            'pedido_id' => $pedidoId,
            'forma_pagamento' => $formaPagamento,
            'aguardando_confirmacao' => true,
            'token' => csrf_hash(),
        ]);
    }

    /**
     * Confirma pagamento manual (dinheiro/cartão)
     */
    public function confirmarPagamento()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $pedidoId = $this->request->getPost('pedido_id');

        $this->pedidoModel
            ->where('id', $pedidoId)
            ->set('status', 'pago')
            ->set('data_pagamento', date('Y-m-d H:i:s'))
            ->update();

        // Atualiza ingressos
        $this->ingressoModel
            ->where('pedido_id', $pedidoId)
            ->set('status', 'ativo')
            ->update();

        // Limpa carrinho
        session()->remove('pdv_carrinho');

        // Envia email
        $this->enviarEmailConfirmacao($pedidoId);

        return $this->response->setJSON([
            'sucesso' => true,
            'pedido_id' => $pedidoId,
            'redirect' => site_url("pdv/obrigado/$pedidoId"),
            'token' => csrf_hash(),
        ]);
    }

    /**
     * Tela de obrigado
     */
    public function obrigado(int $pedidoId = null)
    {
        $usuario = $this->usuarioLogado();

        if (!$usuario->is_pdv) {
            return redirect()->to(site_url('home'));
        }

        $pedido = $this->pedidoModel->find($pedidoId);

        if (!$pedido) {
            return redirect()->to(site_url('pdv/dashboard'))->with('erro', 'Pedido não encontrado.');
        }

        $cliente = $this->usuarioModel->find($pedido->user_id);
        $evento = $this->eventoModel->find($pedido->event_id);
        $ingressos = $this->ingressoModel->where('pedido_id', $pedidoId)->findAll();

        $data = [
            'titulo' => 'PDV - Venda Concluída',
            'usuario' => $usuario,
            'pedido' => $pedido,
            'cliente' => $cliente,
            'evento' => $evento,
            'ingressos' => $ingressos,
        ];

        return view('Pdv/obrigado', $data);
    }

    /**
     * Verifica status do pagamento (polling para PIX)
     */
    public function verificarPagamento()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['erro' => 'Acesso não permitido']);
        }

        $pedidoId = $this->request->getPost('pedido_id');
        $pedido = $this->pedidoModel->find($pedidoId);

        if (!$pedido) {
            return $this->response->setJSON(['erro' => 'Pedido não encontrado']);
        }

        // Status que indicam pagamento confirmado
        $statusPago = ['RECEIVED', 'RECEIVED_IN_CASH', 'CONFIRMED', 'pago'];
        $isPago = in_array(strtoupper($pedido->status), array_map('strtoupper', $statusPago));

        return $this->response->setJSON([
            'pago' => $isPago,
            'status' => $pedido->status,
            'token' => csrf_hash(),
        ]);
    }

    // ==================== MÉTODOS AUXILIARES ====================

    /**
     * Calcula total do carrinho
     */
    private function calcularTotalCarrinho(array $carrinho): float
    {
        $total = 0;
        foreach ($carrinho as $item) {
            $total += $item['total'] * $item['quantidade'];
        }
        return $total;
    }

    /**
     * Busca ou cria cliente seguindo o mesmo fluxo do Checkout
     */
    private function buscarOuCriarCliente(string $nome, string $email, string $cpf, string $telefone): object
    {
        // Busca cliente existente
        $cliente = $this->clienteModel
            ->withDeleted(true)
            ->where('email', $email)
            ->orderBy('id', 'DESC')
            ->first();

        if ($cliente) {
            return $cliente;
        }

        // Cria novo cliente
        $novoCliente = [
            'nome' => $nome,
            'email' => $email,
            'cpf' => preg_replace('/[^0-9]/', '', $cpf),
            'telefone' => preg_replace('/[^0-9]/', '', $telefone),
        ];

        $this->clienteModel->skipValidation(true)->protect(false)->insert($novoCliente);
        $clienteId = $this->clienteModel->getInsertID();

        // Cria usuário para o cliente
        $pass = $this->usuarioModel->geraCodigoUsuario();
        $usuario = [
            'nome' => $nome,
            'email' => $email,
            'password' => $pass,
            'ativo' => true,
        ];

        $this->usuarioModel->skipValidation(true)->protect(false)->insert($usuario);
        $userId = $this->usuarioModel->getInsertID();

        // Adiciona ao grupo cliente
        $grupoUsuarioModel = new \App\Models\GrupoUsuarioModel();
        $grupoUsuarioModel->protect(false)->insert([
            'grupo_id' => 2,
            'usuario_id' => $userId,
        ]);

        // Atualiza cliente com o ID do usuário
        $this->clienteModel
            ->protect(false)
            ->where('id', $clienteId)
            ->set('usuario_id', $userId)
            ->update();

        // Envia email de acesso
        $clienteObj = $this->clienteModel->find($clienteId);
        $this->enviarEmailNovoCliente($clienteObj, $pass);

        return $clienteObj;
    }

    /**
     * Gera cobrança PIX via Asaas
     */
    private function gerarCobrancaPix(int $pedidoId, float $valor, string $nome, string $cpf, string $email, string $telefone): array
    {
        try {
            // Busca ou cria cliente no Asaas
            $customerId = $this->obtemOuCriaCustomerIdAsaas($nome, $cpf, $email, $telefone);

            log_message('info', 'PDV PIX - Customer ID: ' . $customerId);

            // Cria pagamento PIX
            $payment = $this->asaasService->paymentPix([
                'customer_id' => $customerId,
                'value' => $valor * 100, // Valor em centavos (o serviço divide por 100)
                'description' => 'PDV - Venda de ingressos #' . $pedidoId,
                'externalReference' => 'PDV-' . $pedidoId,
            ]);

            log_message('info', 'PDV PIX - Payment response: ' . json_encode($payment));

            // Verifica se o retorno é válido
            if ($payment === null) {
                log_message('error', 'PDV PIX - Retorno null do Asaas');
                return ['erro' => 'Erro de conexão com gateway de pagamento'];
            }

            if (isset($payment['errors'])) {
                log_message('error', 'Erro Asaas PIX: ' . json_encode($payment['errors']));
                return ['erro' => 'Falha ao gerar PIX: ' . ($payment['errors'][0]['description'] ?? 'Erro desconhecido')];
            }

            if (!isset($payment['id'])) {
                log_message('error', 'PDV PIX - Payment ID não encontrado: ' . json_encode($payment));
                return ['erro' => 'Resposta inválida do gateway de pagamento'];
            }

            // Obtém QR Code
            $qrcode = $this->asaasService->obtemQrCode($payment['id']);

            log_message('info', 'PDV PIX - QRCode response: ' . json_encode($qrcode));

            if ($qrcode === null || !isset($qrcode['encodedImage'])) {
                log_message('error', 'PDV PIX - Erro ao obter QR Code');
                return ['erro' => 'Erro ao gerar QR Code PIX'];
            }

            // Atualiza pedido com charge_id
            $this->pedidoModel->protect(false)->update($pedidoId, [
                'charge_id' => $payment['id'],
                'status' => $payment['status'] ?? 'PENDING',
            ]);

            // Registra transação
            $this->transactionModel->protect(false)->insert([
                'pedido_id' => $pedidoId,
                'charge_id' => $payment['id'],
                'installment_value' => $valor,
                'expire_at' => date('Y-m-d', strtotime('+1 days')),
                'payment' => 'PIX',
                'qrcode' => $qrcode['payload'] ?? '',
                'qrcode_image' => $qrcode['encodedImage'] ?? '',
                'link' => $payment['invoiceUrl'] ?? '',
            ]);

            return [
                'payment_id' => $payment['id'],
                'qrcode' => 'data:image/png;base64,' . $qrcode['encodedImage'],
                'qrcode_text' => $qrcode['payload'] ?? '',
                'valor' => $valor,
                'invoice_url' => $payment['invoiceUrl'] ?? '',
            ];

        } catch (\Exception $e) {
            log_message('error', 'Erro ao gerar PIX PDV: ' . $e->getMessage());
            return ['erro' => 'Erro ao gerar PIX: ' . $e->getMessage()];
        }
    }

    /**
     * Obtém ou cria customer_id no Asaas
     */
    private function obtemOuCriaCustomerIdAsaas(string $nome, string $cpf, string $email, string $telefone): string
    {
        // Verifica se o cliente já existe
        $cliente = $this->clienteModel->where('email', $email)->first();

        if ($cliente && !empty($cliente->customer_id)) {
            return $cliente->customer_id;
        }

        // Cria no Asaas
        $cobrar = [
            'nome' => $nome,
            'cpf' => preg_replace('/[^0-9]/', '', $cpf),
            'email' => $email,
            'telefone' => preg_replace('/[^0-9]/', '', $telefone),
            'cep' => '',
            'numero' => '',
        ];

        $customer = $this->asaasService->customers($cobrar);

        if (!isset($customer['id'])) {
            throw new \Exception('Erro ao criar cliente na API ASAAS');
        }

        // Atualiza cliente se existir
        if ($cliente) {
            $this->clienteModel->protect(false)->update($cliente->id, ['customer_id' => $customer['id']]);
        }

        return $customer['id'];
    }

    /**
     * Envia email de confirmação
     */
    private function enviarEmailConfirmacao(int $pedidoId): void
    {
        $pedido = $this->pedidoModel->find($pedidoId);
        if (!$pedido) return;

        $cliente = $this->usuarioModel->find($pedido->user_id);
        $evento = $this->eventoModel->find($pedido->evento_id);

        if (!$cliente || !$evento) return;

        $data = [
            'cliente' => $cliente,
            'evento' => $evento,
            'pedido' => $pedido,
        ];

        $mensagem = view('Pedidos/email_pedido', $data);

        $this->resendService->enviarEmail(
            $cliente->email,
            'Pedido realizado com sucesso!',
            $mensagem
        );
    }

    /**
     * Envia email para novo cliente com dados de acesso
     */
    private function enviarEmailNovoCliente(object $cliente, string $senha): void
    {
        $data = [
            'cliente' => $cliente,
            'senha' => $senha,
        ];

        $mensagem = view('Clientes/email_dados_acesso', $data);

        $this->resendService->enviarEmail(
            $cliente->email,
            'Seja bem-vindo(a) ao MundoDream!',
            $mensagem
        );
    }
}
