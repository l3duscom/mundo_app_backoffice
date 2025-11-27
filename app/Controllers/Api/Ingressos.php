<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use chillerlan\QRCode\QRCode;

/**
 * Controller API de Ingressos
 * Gerencia ingressos do usuário autenticado via JWT
 */
class Ingressos extends BaseController
{
    private $clienteModel;
    private $ingressoModel;
    private $pedidoModel;
    private $cartaoModel;
    private $ticketModel;

    public function __construct()
    {
        $this->clienteModel = new \App\Models\ClienteModel();
        $this->ingressoModel = new \App\Models\IngressoModel();
        $this->pedidoModel = new \App\Models\PedidoModel();
        $this->cartaoModel = new \App\Models\CartaoModel();
        $this->ticketModel = new \App\Models\TicketModel();
    }

    /**
     * Lista todos os ingressos do usuário autenticado
     * GET /api/ingressos
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function index()
    {
        // Recupera dados do usuário autenticado via JWT (adicionado pelo filtro JwtAuthFilter)
        $usuarioAutenticado = $this->request->usuarioAutenticado ?? null;
        
        if (!$usuarioAutenticado) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ])
                ->setStatusCode(401);
        }
        
        $userId = $usuarioAutenticado['user_id'];
        
        // Log de quem está fazendo a requisição
        log_message('info', sprintf(
            "API Ingressos::index - Usuario %d (%s) requisitou lista completa de ingressos. IP: %s",
            $userId,
            $usuarioAutenticado['email'] ?? 'sem-email',
            $this->request->getIPAddress()
        ));

        try {
            // Busca o cliente vinculado ao usuário
            $cliente = $this->clienteModel->withDeleted(true)
                ->where('usuario_id', $userId)
                ->first();

            if (!$cliente) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Cliente não encontrado para este usuário'
                    ])
                    ->setStatusCode(404);
            }

            // Recupera ingressos do usuário
            $ingressos = $this->ingressoModel->recuperaIngressosPorUsuario($userId);
            
            // VALIDAÇÃO DE SEGURANÇA CRÍTICA: Verificar se todos os ingressos pertencem ao usuário
            foreach ($ingressos as $ingresso) {
                if (isset($ingresso->user_id) && (int)$ingresso->user_id !== (int)$userId) {
                    log_message('critical', sprintf(
                        "🚨 VAZAMENTO DE DADOS DETECTADO! Usuario %d recebeu ingresso %d que pertence ao usuario %d. IP: %s",
                        $userId,
                        $ingresso->id,
                        $ingresso->user_id,
                        $this->request->getIPAddress()
                    ));
                    
                    // Retornar erro em vez de dados de outro usuário
                    return $this->response
                        ->setJSON([
                            'success' => false,
                            'message' => 'Erro de segurança detectado. A requisição foi registrada.'
                        ])
                        ->setStatusCode(500);
                }
            }

            // Separa ingressos em atuais e anteriores
            $ingressos_atuais = [];
            $ingressos_anteriores = [];
            $hoje = date('Y-m-d');

            foreach ($ingressos as $ingresso) {
                // Busca ticket vinculado
                $ticket = $this->ticketModel->find($ingresso->ticket_id ?? null);
                
                // Gera QR Code
                $qrCodeBase64 = null;
                if ($ingresso->codigo) {
                    try {
                        $qrCodeBase64 = (new QRCode)->render($ingresso->codigo);
                    } catch (\Exception $e) {
                        log_message('warning', 'Erro ao gerar QR Code: ' . $e->getMessage());
                    }
                }
                
                // Prepara dados do ingresso
                $ingressoData = [
                    'id' => $ingresso->id,
                    'codigo' => $ingresso->codigo,
                    'nome' => $ingresso->nome ?? null,
                    'status' => $ingresso->status ?? null,
                    'ticket_id' => $ingresso->ticket_id ?? null,
                    'pedido_id' => $ingresso->pedido_id ?? null,
                    'created_at' => $ingresso->created_at ?? null,
                    'qr_code' => $qrCodeBase64,
                ];

                // Adiciona informações do ticket se existir
                if ($ticket) {
                    $ingressoData['ticket'] = [
                        'id' => $ticket->id,
                        'nome' => $ticket->nome ?? null,
                        'descricao' => $ticket->descricao ?? null,
                        'data_inicio' => $ticket->data_inicio ?? null,
                        'data_fim' => $ticket->data_fim ?? null,
                        'valor' => $ticket->valor ?? null,
                    ];

                    // Determina se é atual ou anterior
                    $data_fim = $ticket->data_fim ?? null;
                    if ($data_fim) {
                        $limite = date('Y-m-d', strtotime('-2 days', strtotime($hoje)));
                        if ($data_fim < $limite) {
                            $ingressos_anteriores[] = $ingressoData;
                        } else {
                            $ingressos_atuais[] = $ingressoData;
                        }
                    } else {
                        $ingressos_atuais[] = $ingressoData;
                    }
                } else {
                    // Sem ticket, considera como atual
                    $ingressos_atuais[] = $ingressoData;
                }
            }

            // Busca informações adicionais
            $convite = $usuarioAutenticado['codigo'] ?? null;
            $indicacoes = 0;
            
            if ($convite) {
                $indicacoes = $this->pedidoModel
                    ->where('convite', $convite)
                    ->whereIn('status', ['CONFIRMED', 'RECEIVED', 'paid'])
                    ->countAllResults();
            }

            // Busca cartão ativo
            $card = $this->cartaoModel
                ->where('user_id', $userId)
                ->where('expiration >= NOW()')
                ->first();

            $cardData = null;
            if ($card) {
                $cardData = [
                    'id' => $card->id,
                    'numero' => $card->numero ?? null,
                    'expiration' => $card->expiration ?? null,
                    'ativo' => true,
                ];
            }

            // Monta resposta
            return $this->response
                ->setJSON([
                    'success' => true,
                    'data' => [
                        'cliente' => [
                            'id' => $cliente->id,
                            'nome' => $cliente->nome ?? null,
                            'email' => $usuarioAutenticado['email'],
                            'cpf' => $cliente->cpf ?? null,
                            'telefone' => $cliente->telefone ?? null,
                        ],
                        'ingressos' => [
                            'atuais' => $ingressos_atuais,
                            'anteriores' => $ingressos_anteriores,
                            'total_atuais' => count($ingressos_atuais),
                            'total_anteriores' => count($ingressos_anteriores),
                            'total' => count($ingressos_atuais) + count($ingressos_anteriores),
                        ],
                        'card' => $cardData,
                        'indicacoes' => $indicacoes,
                        'convite' => $convite,
                    ]
                ])
                ->setStatusCode(200);

        } catch (\Exception $e) {
            log_message('error', sprintf(
                "Erro ao buscar ingressos API - Usuario %d: %s",
                $userId ?? 0,
                $e->getMessage()
            ));
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao buscar ingressos',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Retorna detalhes de um ingresso específico
     * GET /api/ingressos/{id}
     * 
     * @param int $id ID do ingresso
     * @return \CodeIgniter\HTTP\Response
     */
    public function show($id)
    {
        $usuarioAutenticado = $this->request->usuarioAutenticado ?? null;
        
        if (!$usuarioAutenticado) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ])
                ->setStatusCode(401);
        }
        
        $userId = $usuarioAutenticado['user_id'];

        try {
            // Busca o ingresso
            $ingresso = $this->ingressoModel->find($id);

            if (!$ingresso) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Ingresso não encontrado'
                    ])
                    ->setStatusCode(404);
            }

            // Verifica se o ingresso pertence ao usuário
            if ($ingresso->user_id != $userId) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Você não tem permissão para acessar este ingresso'
                    ])
                    ->setStatusCode(403);
            }

            // Busca ticket vinculado
            $ticket = $this->ticketModel->find($ingresso->ticket_id ?? null);

            // Gera QR Code
            $qrCodeBase64 = null;
            if ($ingresso->codigo) {
                try {
                    $qrCodeBase64 = (new QRCode)->render($ingresso->codigo);
                } catch (\Exception $e) {
                    log_message('warning', 'Erro ao gerar QR Code: ' . $e->getMessage());
                }
            }

            // Monta resposta
            $ingressoData = [
                'id' => $ingresso->id,
                'codigo' => $ingresso->codigo,
                'nome' => $ingresso->nome ?? null,
                'status' => $ingresso->status ?? null,
                'ticket_id' => $ingresso->ticket_id ?? null,
                'pedido_id' => $ingresso->pedido_id ?? null,
                'created_at' => $ingresso->created_at ?? null,
                'qr_code' => $qrCodeBase64,
            ];

            // Adiciona informações do ticket
            if ($ticket) {
                $ingressoData['ticket'] = [
                    'id' => $ticket->id,
                    'nome' => $ticket->nome ?? null,
                    'descricao' => $ticket->descricao ?? null,
                    'data_inicio' => $ticket->data_inicio ?? null,
                    'data_fim' => $ticket->data_fim ?? null,
                    'valor' => $ticket->valor ?? null,
                    'evento_id' => $ticket->evento_id ?? null,
                ];
            }

            return $this->response
                ->setJSON([
                    'success' => true,
                    'data' => $ingressoData
                ])
                ->setStatusCode(200);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao buscar ingresso API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao buscar ingresso',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Retorna apenas os ingressos atuais (não expirados)
     * GET /api/ingressos/atuais
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function atuais()
    {
        $usuarioAutenticado = $this->request->usuarioAutenticado ?? null;
        
        if (!$usuarioAutenticado) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ])
                ->setStatusCode(401);
        }
        
        $userId = $usuarioAutenticado['user_id'];
        
        // Log de quem está fazendo a requisição
        log_message('info', sprintf(
            "API Ingressos::atuais - Usuario %d (%s) requisitou ingressos. IP: %s, User-Agent: %s",
            $userId,
            $usuarioAutenticado['email'] ?? 'sem-email',
            $this->request->getIPAddress(),
            substr($this->request->getUserAgent()->getAgentString(), 0, 100)
        ));

        try {
            $ingressos = $this->ingressoModel->recuperaIngressosPorUsuario($userId);
            
            // VALIDAÇÃO DE SEGURANÇA CRÍTICA: Verificar se todos os ingressos pertencem ao usuário
            foreach ($ingressos as $ingresso) {
                if (isset($ingresso->user_id) && (int)$ingresso->user_id !== (int)$userId) {
                    log_message('critical', sprintf(
                        "🚨 VAZAMENTO DE DADOS DETECTADO! Usuario %d recebeu ingresso %d que pertence ao usuario %d. IP: %s",
                        $userId,
                        $ingresso->id,
                        $ingresso->user_id,
                        $this->request->getIPAddress()
                    ));
                    
                    // Retornar erro em vez de dados de outro usuário
                    return $this->response
                        ->setJSON([
                            'success' => false,
                            'message' => 'Erro de segurança detectado. A requisição foi registrada.'
                        ])
                        ->setStatusCode(500);
                }
            }
            
            $ingressos_atuais = [];
            $hoje = date('Y-m-d');

            foreach ($ingressos as $ingresso) {
                $ticket = $this->ticketModel->find($ingresso->ticket_id ?? null);
                
                $data_fim = $ticket->data_fim ?? null;
                $limite = date('Y-m-d', strtotime('-2 days', strtotime($hoje)));
                
                // Só adiciona se for atual (não expirado há mais de 2 dias)
                if (!$data_fim || $data_fim >= $limite) {
                    // Gera QR Code
                    $qrCodeBase64 = null;
                    if ($ingresso->codigo) {
                        try {
                            $qrCodeBase64 = (new QRCode)->render($ingresso->codigo);
                        } catch (\Exception $e) {
                            log_message('warning', 'Erro ao gerar QR Code: ' . $e->getMessage());
                        }
                    }
                    
                    $ingressoData = [
                        'id' => $ingresso->id,
                        'codigo' => $ingresso->codigo,
                        'nome' => $ingresso->nome ?? null,
                        'status' => $ingresso->status ?? null,
                        'qr_code' => $qrCodeBase64,
                    ];

                    if ($ticket) {
                        $ingressoData['ticket'] = [
                            'id' => $ticket->id,
                            'nome' => $ticket->nome ?? null,
                            'data_inicio' => $ticket->data_inicio ?? null,
                            'data_fim' => $ticket->data_fim ?? null,
                        ];
                    }

                    $ingressos_atuais[] = $ingressoData;
                }
            }
            
            log_message('info', sprintf(
                "API Ingressos::atuais - Usuario %d - Retornando %d ingressos atuais de %d totais",
                $userId,
                count($ingressos_atuais),
                count($ingressos)
            ));

            return $this->response
                ->setJSON([
                    'success' => true,
                    'data' => [
                        'ingressos' => $ingressos_atuais,
                        'total' => count($ingressos_atuais),
                    ]
                ])
                ->setStatusCode(200);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao buscar ingressos atuais API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao buscar ingressos',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }
}

