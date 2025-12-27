<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ConquistaModel;
use App\Models\EventoModel;
use CodeIgniter\HTTP\ResponseInterface;

class Conquistas extends BaseController
{
    protected $conquistaModel;
    protected $eventoModel;

    public function __construct()
    {
        $this->conquistaModel = new ConquistaModel();
        $this->eventoModel = new EventoModel();
    }

    /**
     * Lista todas as conquistas
     * GET /api/conquistas
     * 
     * Query params opcionais:
     * - event_id: filtrar por evento
     * - nivel: filtrar por nível
     * - status: filtrar por status
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function index()
    {
        try {
            $eventId = $this->request->getGet('event_id');
            $nivel = $this->request->getGet('nivel');
            $status = $this->request->getGet('status');

            $builder = $this->conquistaModel;

            // Aplica filtros se fornecidos
            if ($eventId) {
                $builder = $builder->where('event_id', $eventId);
            }

            if ($nivel) {
                $builder = $builder->where('nivel', $nivel);
            }

            if ($status) {
                $builder = $builder->where('status', $status);
            }

            $conquistas = $builder->orderBy('pontos', 'ASC')->findAll();

            $data = [];
            foreach ($conquistas as $conquista) {
                $data[] = [
                    'id' => $conquista->id,
                    'event_id' => $conquista->event_id,
                    'codigo' => $conquista->codigo,
                    'nome_conquista' => $conquista->nome_conquista,
                    'descricao' => $conquista->descricao,
                    'pontos' => $conquista->pontos,
                    'nivel' => $conquista->nivel,
                    'status' => $conquista->status,
                    'created_at' => $conquista->created_at,
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
            log_message('error', 'Erro ao listar conquistas API: ' . $e->getMessage());
            
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
     * Lista conquistas por evento
     * GET /api/conquistas/evento/{event_id}
     * 
     * @param int $event_id
     * @return \CodeIgniter\HTTP\Response
     */
    public function porEvento($event_id = null)
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
            // Valida se o evento existe
            $evento = $this->eventoModel->find($event_id);
            if (!$evento) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Evento não encontrado'
                    ])
                    ->setStatusCode(404);
            }

            $conquistas = $this->conquistaModel->getConquistasPorEvento($event_id);

            $data = [];
            foreach ($conquistas as $conquista) {
                $data[] = [
                    'id' => $conquista->id,
                    'event_id' => $conquista->event_id,
                    'codigo' => $conquista->codigo,
                    'nome_conquista' => $conquista->nome_conquista,
                    'descricao' => $conquista->descricao,
                    'pontos' => $conquista->pontos,
                    'nivel' => $conquista->nivel,
                    'status' => $conquista->status,
                    'created_at' => $conquista->created_at,
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
            log_message('error', 'Erro ao buscar conquistas por evento API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao buscar conquistas',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Busca uma conquista específica
     * GET /api/conquistas/{id}
     * 
     * @param int $id
     * @return \CodeIgniter\HTTP\Response
     */
    public function show($id = null)
    {
        if (!$id) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'ID da conquista não fornecido'
                ])
                ->setStatusCode(400);
        }

        try {
            $conquista = $this->conquistaModel->find($id);

            if (!$conquista) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Conquista não encontrada'
                    ])
                    ->setStatusCode(404);
            }

            return $this->response
                ->setJSON([
                    'success' => true,
                    'data' => [
                        'id' => $conquista->id,
                        'event_id' => $conquista->event_id,
                        'codigo' => $conquista->codigo,
                        'nome_conquista' => $conquista->nome_conquista,
                        'descricao' => $conquista->descricao,
                        'pontos' => $conquista->pontos,
                        'nivel' => $conquista->nivel,
                        'status' => $conquista->status,
                        'created_at' => $conquista->created_at,
                        'updated_at' => $conquista->updated_at,
                    ],
                ])
                ->setStatusCode(200);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao buscar conquista API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao buscar conquista',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Cria uma nova conquista
     * POST /api/conquistas
     * 
     * Body JSON:
     * {
     *   "event_id": 1,
     *   "nome_conquista": "Primeira Participação",
     *   "pontos": 10,
     *   "nivel": "BRONZE",
     *   "status": "ATIVA"
     * }
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function create()
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

            // Valida evento se fornecido
            if (!empty($json['event_id'])) {
                $evento = $this->eventoModel->find($json['event_id']);
                if (!$evento) {
                    return $this->response
                        ->setJSON([
                            'success' => false,
                            'message' => 'Evento não encontrado'
                        ])
                        ->setStatusCode(404);
                }
            }

            // Prepara dados para salvar
            // Nota: O código será gerado automaticamente pelo Model
            $data = [
                'event_id' => !empty($json['event_id']) ? $json['event_id'] : null,
                'nome_conquista' => $json['nome_conquista'] ?? '',
                'descricao' => $json['descricao'] ?? null,
                'pontos' => $json['pontos'] ?? 0,
                'nivel' => $json['nivel'] ?? 'BRONZE',
                'status' => $json['status'] ?? 'ATIVA',
            ];

            // Salva no banco
            if ($this->conquistaModel->save($data)) {
                $id = $this->conquistaModel->getInsertID();
                $conquistaCriada = $this->conquistaModel->find($id);

                // Verifica se a conquista foi encontrada
                if (!$conquistaCriada) {
                    log_message('error', 'Conquista criada mas não encontrada após inserção. ID: ' . $id);
                    return $this->response
                        ->setJSON([
                            'success' => false,
                            'message' => 'Conquista criada mas houve erro ao recuperar os dados'
                        ])
                        ->setStatusCode(500);
                }

                return $this->response
                    ->setJSON([
                        'success' => true,
                        'message' => 'Conquista criada com sucesso',
                        'data' => [
                            'id' => $conquistaCriada->id,
                            'event_id' => $conquistaCriada->event_id,
                            'codigo' => $conquistaCriada->codigo,
                            'nome_conquista' => $conquistaCriada->nome_conquista,
                            'descricao' => $conquistaCriada->descricao,
                            'pontos' => $conquistaCriada->pontos,
                            'nivel' => $conquistaCriada->nivel,
                            'status' => $conquistaCriada->status,
                            'created_at' => $conquistaCriada->created_at,
                        ]
                    ])
                    ->setStatusCode(201);
            }

            // Erro de validação
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao criar conquista',
                    'errors' => $this->conquistaModel->errors()
                ])
                ->setStatusCode(422);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao criar conquista API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao criar conquista',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Atualiza uma conquista existente
     * PUT /api/conquistas/{id}
     * PATCH /api/conquistas/{id}
     * 
     * @param int $id ID da conquista
     * @return \CodeIgniter\HTTP\Response
     */
    public function update($id = null)
    {
        // Valida método
        if (!in_array($this->request->getMethod(), ['put', 'patch'])) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Método não permitido'
                ])
                ->setStatusCode(405);
        }

        if (!$id) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'ID da conquista não fornecido'
                ])
                ->setStatusCode(400);
        }

        try {
            // Busca conquista
            $conquista = $this->conquistaModel->find($id);

            if (!$conquista) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Conquista não encontrada'
                    ])
                    ->setStatusCode(404);
            }

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

            // Se mudar o evento, valida se existe
            if (isset($json['event_id']) && $json['event_id'] != $conquista->event_id) {
                $evento = $this->eventoModel->find($json['event_id']);
                if (!$evento) {
                    return $this->response
                        ->setJSON([
                            'success' => false,
                            'message' => 'Evento não encontrado'
                        ])
                        ->setStatusCode(404);
                }
            }

            // Prepara dados para atualizar
            $data = array_merge(['id' => $id], $json);

            // Salva alterações
            if ($this->conquistaModel->save($data)) {
                $conquistaAtualizada = $this->conquistaModel->find($id);

                // Verifica se a conquista foi encontrada
                if (!$conquistaAtualizada) {
                    log_message('error', 'Conquista atualizada mas não encontrada. ID: ' . $id);
                    return $this->response
                        ->setJSON([
                            'success' => false,
                            'message' => 'Conquista atualizada mas houve erro ao recuperar os dados'
                        ])
                        ->setStatusCode(500);
                }

                return $this->response
                    ->setJSON([
                        'success' => true,
                        'message' => 'Conquista atualizada com sucesso',
                        'data' => [
                            'id' => $conquistaAtualizada->id,
                            'event_id' => $conquistaAtualizada->event_id,
                            'codigo' => $conquistaAtualizada->codigo,
                            'nome_conquista' => $conquistaAtualizada->nome_conquista,
                            'descricao' => $conquistaAtualizada->descricao,
                            'pontos' => $conquistaAtualizada->pontos,
                            'nivel' => $conquistaAtualizada->nivel,
                            'status' => $conquistaAtualizada->status,
                            'updated_at' => $conquistaAtualizada->updated_at,
                        ]
                    ])
                    ->setStatusCode(200);
            }

            // Erro de validação
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao atualizar conquista',
                    'errors' => $this->conquistaModel->errors()
                ])
                ->setStatusCode(422);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao atualizar conquista API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao atualizar conquista',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Deleta uma conquista (soft delete)
     * DELETE /api/conquistas/{id}
     * 
     * @param int $id ID da conquista
     * @return \CodeIgniter\HTTP\Response
     */
    public function delete($id = null)
    {
        if (!$id) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'ID da conquista não fornecido'
                ])
                ->setStatusCode(400);
        }

        try {
            $conquista = $this->conquistaModel->find($id);

            if (!$conquista) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Conquista não encontrada'
                    ])
                    ->setStatusCode(404);
            }

            if ($this->conquistaModel->delete($id)) {
                return $this->response
                    ->setJSON([
                        'success' => true,
                        'message' => 'Conquista deletada com sucesso'
                    ])
                    ->setStatusCode(200);
            }

            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao deletar conquista'
                ])
                ->setStatusCode(500);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao deletar conquista API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao deletar conquista',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }
}

