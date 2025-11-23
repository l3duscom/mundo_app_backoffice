<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UsuarioConquistaModel;
use App\Models\ExtratoPontosModel;
use App\Models\UsuarioModel;
use App\Models\ConquistaModel;
use App\Services\ConquistaService;
use CodeIgniter\HTTP\ResponseInterface;

class UsuarioConquistas extends BaseController
{
    protected $usuarioConquistaModel;
    protected $extratoPontosModel;
    protected $usuarioModel;
    protected $conquistaModel;
    protected $conquistaService;

    public function __construct()
    {
        $this->usuarioConquistaModel = new UsuarioConquistaModel();
        $this->extratoPontosModel = new ExtratoPontosModel();
        $this->usuarioModel = new UsuarioModel();
        $this->conquistaModel = new ConquistaModel();
        $this->conquistaService = new ConquistaService();
    }

    /**
     * Lista conquistas de um usuário
     * GET /api/usuario-conquistas/usuario/{user_id}
     * 
     * Query params opcionais:
     * - event_id: filtrar por evento
     * 
     * @param int $user_id
     * @return \CodeIgniter\HTTP\Response
     */
    public function porUsuario($user_id = null)
    {
        if (!$user_id) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'ID do usuário não fornecido'
                ])
                ->setStatusCode(400);
        }

        try {
            $eventId = $this->request->getGet('event_id');

            $conquistas = $this->usuarioConquistaModel->getConquistasDoUsuario($user_id, $eventId);

            $data = [];
            $totalPontos = 0;

            foreach ($conquistas as $conquista) {
                $data[] = [
                    'id' => $conquista->id,
                    'conquista_id' => $conquista->conquista_id,
                    'nome_conquista' => $conquista->nome_conquista ?? null,
                    'nivel' => $conquista->nivel ?? null,
                    'event_id' => $conquista->event_id,
                    'pontos' => $conquista->pontos,
                    'admin' => (int)$conquista->admin,
                    'status' => $conquista->status,
                    'created_at' => $conquista->created_at,
                ];
                
                if ($conquista->status === 'ATIVA') {
                    $totalPontos += $conquista->pontos;
                }
            }

            return $this->response
                ->setJSON([
                    'success' => true,
                    'data' => $data,
                    'total' => count($data),
                    'total_pontos' => $totalPontos,
                ])
                ->setStatusCode(200);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao listar conquistas do usuário API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao listar conquistas',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Atribui uma conquista a um usuário
     * POST /api/usuario-conquistas/atribuir
     * 
     * Body JSON:
     * {
     *   "user_id": 1,
     *   "conquista_id": 1,
     *   "event_id": 1,
     *   "admin": true (opcional, default false),
     *   "atribuido_por": 2 (opcional, ID do admin)
     * }
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function atribuir()
    {
        // Valida método
        if ($this->request->getMethod() !== 'post') {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Método não permitido'
                ])
                ->setStatusCode(405);
        }

        try {
            // Recupera dados do JSON
            $json = $this->request->getJSON(true);

            if (!is_array($json) || empty($json)) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Dados não fornecidos ou JSON inválido'
                    ])
                    ->setStatusCode(400);
            }

            // Valida campos obrigatórios
            $camposObrigatorios = ['user_id', 'conquista_id', 'event_id'];
            foreach ($camposObrigatorios as $campo) {
                if (!isset($json[$campo])) {
                    return $this->response
                        ->setJSON([
                            'success' => false,
                            'message' => "Campo {$campo} é obrigatório"
                        ])
                        ->setStatusCode(400);
                }
            }

            $userId = (int) $json['user_id'];
            $conquistaId = (int) $json['conquista_id'];
            $eventId = (int) $json['event_id'];
            $isAdmin = isset($json['admin']) ? (bool) $json['admin'] : false;
            $atribuidoPor = isset($json['atribuido_por']) ? (int) $json['atribuido_por'] : null;

            // Chama o serviço para atribuir a conquista
            $result = $this->conquistaService->atribuirConquista(
                $userId,
                $conquistaId,
                $eventId,
                $isAdmin,
                $atribuidoPor
            );

            if ($result['success']) {
                return $this->response
                    ->setJSON($result)
                    ->setStatusCode(201);
            } else {
                $statusCode = 400;
                if (isset($result['errors'])) {
                    $statusCode = 422;
                }
                return $this->response
                    ->setJSON($result)
                    ->setStatusCode($statusCode);
            }

        } catch (\Exception $e) {
            log_message('error', 'Erro ao atribuir conquista API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao atribuir conquista',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Atribui uma conquista a um usuário usando o código
     * POST /api/usuario-conquistas/atribuir-por-codigo
     * 
     * Body JSON:
     * {
     *   "user_id": 1,
     *   "codigo": "A1B2C3D4",
     *   "event_id": 1,
     *   "admin": false (opcional, default false),
     *   "atribuido_por": 2 (opcional, ID do admin)
     * }
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function atribuirPorCodigo()
    {
        // Valida método
        if ($this->request->getMethod() !== 'post') {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Método não permitido'
                ])
                ->setStatusCode(405);
        }

        try {
            // Recupera dados do JSON
            $json = $this->request->getJSON(true);

            if (!is_array($json) || empty($json)) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Dados não fornecidos ou JSON inválido'
                    ])
                    ->setStatusCode(400);
            }

            // Valida campos obrigatórios
            $camposObrigatorios = ['user_id', 'codigo', 'event_id'];
            foreach ($camposObrigatorios as $campo) {
                if (!isset($json[$campo])) {
                    return $this->response
                        ->setJSON([
                            'success' => false,
                            'message' => "Campo {$campo} é obrigatório"
                        ])
                        ->setStatusCode(400);
                }
            }

            $userId = (int) $json['user_id'];
            $codigo = trim($json['codigo']);
            $eventId = (int) $json['event_id'];
            $isAdmin = isset($json['admin']) ? (bool) $json['admin'] : false;
            $atribuidoPor = isset($json['atribuido_por']) ? (int) $json['atribuido_por'] : null;

            // Busca a conquista pelo código
            $conquista = $this->conquistaModel->buscarPorCodigo($codigo);

            if (!$conquista) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Conquista não encontrada com o código fornecido'
                    ])
                    ->setStatusCode(404);
            }

            // Valida se a conquista está ativa
            if ($conquista->status !== 'ATIVA') {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Conquista não está ativa',
                        'status_conquista' => $conquista->status
                    ])
                    ->setStatusCode(400);
            }

            // Valida se a conquista pertence ao evento informado
            if ($conquista->event_id != $eventId) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Conquista não pertence ao evento informado',
                        'event_id_conquista' => $conquista->event_id,
                        'event_id_informado' => $eventId
                    ])
                    ->setStatusCode(400);
            }

            // Chama o serviço para atribuir a conquista
            $result = $this->conquistaService->atribuirConquista(
                $userId,
                $conquista->id,
                $eventId,
                $isAdmin,
                $atribuidoPor
            );

            if ($result['success']) {
                // Adiciona informações da conquista na resposta
                $result['conquista'] = [
                    'id' => $conquista->id,
                    'codigo' => $conquista->codigo,
                    'nome_conquista' => $conquista->nome_conquista,
                    'descricao' => $conquista->descricao,
                    'pontos' => $conquista->pontos,
                    'nivel' => $conquista->nivel,
                ];
                
                return $this->response
                    ->setJSON($result)
                    ->setStatusCode(201);
            } else {
                $statusCode = 400;
                if (isset($result['errors'])) {
                    $statusCode = 422;
                }
                return $this->response
                    ->setJSON($result)
                    ->setStatusCode($statusCode);
            }

        } catch (\Exception $e) {
            log_message('error', 'Erro ao atribuir conquista por código API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao atribuir conquista',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Revoga uma conquista de um usuário
     * POST /api/usuario-conquistas/{id}/revogar
     * 
     * Body JSON:
     * {
     *   "atribuido_por": 2,
     *   "motivo": "Motivo da revogação" (opcional)
     * }
     * 
     * @param int $id ID do usuario_conquista
     * @return \CodeIgniter\HTTP\Response
     */
    public function revogar($id = null)
    {
        if (!$id) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'ID não fornecido'
                ])
                ->setStatusCode(400);
        }

        // Valida método
        if ($this->request->getMethod() !== 'post') {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Método não permitido'
                ])
                ->setStatusCode(405);
        }

        try {
            // Recupera dados do JSON
            $json = $this->request->getJSON(true);

            if (!is_array($json)) {
                $json = [];
            }

            $atribuidoPor = isset($json['atribuido_por']) ? (int) $json['atribuido_por'] : null;
            $motivo = isset($json['motivo']) ? (string) $json['motivo'] : null;

            if (!$atribuidoPor) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Campo atribuido_por é obrigatório'
                    ])
                    ->setStatusCode(400);
            }

            // Chama o serviço para revogar a conquista
            $result = $this->conquistaService->revogarConquista($id, $atribuidoPor, $motivo);

            $statusCode = $result['success'] ? 200 : 400;
            return $this->response
                ->setJSON($result)
                ->setStatusCode($statusCode);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao revogar conquista API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao revogar conquista',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Busca extrato de pontos do usuário
     * GET /api/usuario-conquistas/extrato/{user_id}
     * 
     * Query params opcionais:
     * - event_id: filtrar por evento
     * - limit: limitar quantidade de registros
     * 
     * @param int $user_id
     * @return \CodeIgniter\HTTP\Response
     */
    public function extrato($user_id = null)
    {
        if (!$user_id) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'ID do usuário não fornecido'
                ])
                ->setStatusCode(400);
        }

        try {
            $eventId = $this->request->getGet('event_id');
            $limit = $this->request->getGet('limit');

            $extrato = $this->extratoPontosModel->getExtratoUsuario($user_id, $eventId, $limit);

            $data = [];
            foreach ($extrato as $item) {
                $data[] = [
                    'id' => $item->id,
                    'event_id' => $item->event_id,
                    'tipo' => $item->tipo,
                    'pontos' => $item->pontos,
                    'saldo_anterior' => $item->saldo_anterior,
                    'saldo_atual' => $item->saldo_atual,
                    'descricao' => $item->descricao,
                    'created_at' => $item->created_at,
                ];
            }

            // Busca saldo atual do usuário
            $usuario = $this->usuarioModel->find($user_id);
            $saldoAtual = $usuario ? (int) ($usuario->pontos ?? 0) : 0;

            return $this->response
                ->setJSON([
                    'success' => true,
                    'data' => $data,
                    'total' => count($data),
                    'saldo_atual' => $saldoAtual,
                ])
                ->setStatusCode(200);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao buscar extrato API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao buscar extrato',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Busca ranking de usuários por evento
     * GET /api/usuario-conquistas/ranking/{event_id}
     * 
     * Query params opcionais:
     * - limit: limitar quantidade de usuários (default 10)
     * 
     * @param int $event_id
     * @return \CodeIgniter\HTTP\Response
     */
    public function ranking($event_id = null)
    {
        if (!$event_id) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'ID do evento não fornecido'
                ])
                ->setStatusCode(400);
        }

        try {
            $limit = $this->request->getGet('limit') ?? 10;
            $limit = min((int) $limit, 100); // Máximo 100

            $ranking = $this->usuarioConquistaModel->getRankingPorEvento($event_id, $limit);

            $data = [];
            $posicao = 1;
            foreach ($ranking as $item) {
                $data[] = [
                    'posicao' => $posicao++,
                    'user_id' => $item['user_id'],
                    'nome' => $item['nome'],
                    'total_pontos' => (int) $item['total_pontos'],
                    'total_conquistas' => (int) $item['total_conquistas'],
                ];
            }

            return $this->response
                ->setJSON([
                    'success' => true,
                    'data' => $data,
                    'total' => count($data),
                ])
                ->setStatusCode(200);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao buscar ranking API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao buscar ranking',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }
}

