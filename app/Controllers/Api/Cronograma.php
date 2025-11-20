<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Entities\CronogramaEntity;

/**
 * Controller API de Cronograma
 * Gerencia cronogramas de eventos via API RESTful
 */
class Cronograma extends BaseController
{
    private $cronogramaModel;
    private $eventoModel;

    public function __construct()
    {
        $this->cronogramaModel = new \App\Models\CronogramaModel();
        $this->eventoModel = new \App\Models\EventoModel();
    }

    /**
     * Lista todos os cronogramas ou filtra por evento
     * GET /api/cronograma
     * GET /api/cronograma?event_id=1
     * GET /api/cronograma?ativo=1
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function index()
    {
        try {
            // Recupera parâmetros de filtro
            $event_id = $this->request->getGet('event_id');
            $ativo = $this->request->getGet('ativo');

            $builder = $this->cronogramaModel
                ->select([
                    'cronograma.id',
                    'cronograma.name',
                    'cronograma.ativo',
                    'cronograma.created_at',
                    'cronograma.updated_at',
                    'eventos.nome as evento_nome',
                    'eventos.id as evento_id'
                ])
                ->join('eventos', 'eventos.id = cronograma.event_id')
                ->orderBy('cronograma.created_at', 'DESC');

            // Aplica filtros
            if ($event_id) {
                $builder->where('cronograma.event_id', $event_id);
            }

            if ($ativo !== null) {
                $builder->where('cronograma.ativo', $ativo);
            }

            $cronogramas = $builder->findAll();

            // Formata resposta
            $data = [];
            foreach ($cronogramas as $cronograma) {
                $data[] = [
                    'id' => $cronograma->id,
                    'name' => $cronograma->name,
                    'ativo' => (bool)$cronograma->ativo,
                    'evento' => [
                        'id' => $cronograma->evento_id,
                        'nome' => $cronograma->evento_nome,
                    ],
                    'created_at' => $cronograma->created_at,
                    'updated_at' => $cronograma->updated_at,
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
            log_message('error', 'Erro ao listar cronogramas API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao listar cronogramas',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Retorna detalhes de um cronograma específico
     * GET /api/cronograma/{id}
     * 
     * @param int $id ID do cronograma
     * @return \CodeIgniter\HTTP\Response
     */
    public function show($id = null)
    {
        if (!$id) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'ID do cronograma não fornecido'
                ])
                ->setStatusCode(400);
        }

        try {
            $cronograma = $this->cronogramaModel
                ->select([
                    'cronograma.*',
                    'eventos.nome as evento_nome',
                    'eventos.slug as evento_slug',
                    'eventos.data_inicio as evento_data_inicio',
                    'eventos.data_fim as evento_data_fim',
                ])
                ->join('eventos', 'eventos.id = cronograma.event_id')
                ->find($id);

            if (!$cronograma) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Cronograma não encontrado'
                    ])
                    ->setStatusCode(404);
            }

            // Formata resposta
            $data = [
                'id' => $cronograma->id,
                'name' => $cronograma->name,
                'ativo' => (bool)$cronograma->ativo,
                'evento' => [
                    'id' => $cronograma->event_id,
                    'nome' => $cronograma->evento_nome,
                    'slug' => $cronograma->evento_slug,
                    'data_inicio' => $cronograma->evento_data_inicio,
                    'data_fim' => $cronograma->evento_data_fim,
                ],
                'created_at' => $cronograma->created_at,
                'updated_at' => $cronograma->updated_at,
            ];

            return $this->response
                ->setJSON([
                    'success' => true,
                    'data' => $data
                ])
                ->setStatusCode(200);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao buscar cronograma API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao buscar cronograma',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Cria um novo cronograma
     * POST /api/cronograma
     * 
     * Body JSON:
     * {
     *   "event_id": 1,
     *   "name": "Nome do cronograma",
     *   "ativo": true
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

            if (empty($json)) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Dados não fornecidos'
                    ])
                    ->setStatusCode(400);
            }

            // Valida evento
            if (!isset($json['event_id'])) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Campo event_id é obrigatório'
                    ])
                    ->setStatusCode(400);
            }

            $evento = $this->eventoModel->find($json['event_id']);
            if (!$evento) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Evento não encontrado'
                    ])
                    ->setStatusCode(404);
            }

            // Cria entidade
            $cronograma = new CronogramaEntity([
                'event_id' => $json['event_id'],
                'name' => $json['name'] ?? '',
                'ativo' => $json['ativo'] ?? true,
            ]);

            // Salva no banco
            if ($this->cronogramaModel->save($cronograma)) {
                $id = $this->cronogramaModel->getInsertID();
                $cronogramaCriado = $this->cronogramaModel->find($id);

                return $this->response
                    ->setJSON([
                        'success' => true,
                        'message' => 'Cronograma criado com sucesso',
                        'data' => [
                            'id' => $cronogramaCriado->id,
                            'event_id' => $cronogramaCriado->event_id,
                            'name' => $cronogramaCriado->name,
                            'ativo' => (bool)$cronogramaCriado->ativo,
                            'created_at' => $cronogramaCriado->created_at,
                        ]
                    ])
                    ->setStatusCode(201);
            }

            // Erro de validação
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao criar cronograma',
                    'errors' => $this->cronogramaModel->errors()
                ])
                ->setStatusCode(422);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao criar cronograma API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao criar cronograma',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Atualiza um cronograma existente
     * PUT /api/cronograma/{id}
     * PATCH /api/cronograma/{id}
     * 
     * Body JSON:
     * {
     *   "name": "Nome atualizado",
     *   "ativo": false
     * }
     * 
     * @param int $id ID do cronograma
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
                    'message' => 'ID do cronograma não fornecido'
                ])
                ->setStatusCode(400);
        }

        try {
            // Busca cronograma
            $cronograma = $this->cronogramaModel->find($id);

            if (!$cronograma) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Cronograma não encontrado'
                    ])
                    ->setStatusCode(404);
            }

            // Recupera dados do JSON
            $json = $this->request->getJSON(true);

            if (empty($json)) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Dados não fornecidos'
                    ])
                    ->setStatusCode(400);
            }

            // Se mudar o evento, valida se existe
            if (isset($json['event_id']) && $json['event_id'] != $cronograma->event_id) {
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

            // Atualiza campos
            $cronograma->fill($json);

            if (!$cronograma->hasChanged()) {
                return $this->response
                    ->setJSON([
                        'success' => true,
                        'message' => 'Nenhuma alteração detectada',
                        'data' => [
                            'id' => $cronograma->id,
                            'event_id' => $cronograma->event_id,
                            'name' => $cronograma->name,
                            'ativo' => (bool)$cronograma->ativo,
                        ]
                    ])
                    ->setStatusCode(200);
            }

            // Salva alterações
            if ($this->cronogramaModel->save($cronograma)) {
                $cronogramaAtualizado = $this->cronogramaModel->find($id);

                return $this->response
                    ->setJSON([
                        'success' => true,
                        'message' => 'Cronograma atualizado com sucesso',
                        'data' => [
                            'id' => $cronogramaAtualizado->id,
                            'event_id' => $cronogramaAtualizado->event_id,
                            'name' => $cronogramaAtualizado->name,
                            'ativo' => (bool)$cronogramaAtualizado->ativo,
                            'updated_at' => $cronogramaAtualizado->updated_at,
                        ]
                    ])
                    ->setStatusCode(200);
            }

            // Erro de validação
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao atualizar cronograma',
                    'errors' => $this->cronogramaModel->errors()
                ])
                ->setStatusCode(422);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao atualizar cronograma API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao atualizar cronograma',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Exclui (soft delete) um cronograma
     * DELETE /api/cronograma/{id}
     * 
     * @param int $id ID do cronograma
     * @return \CodeIgniter\HTTP\Response
     */
    public function delete($id = null)
    {
        // Valida método
        if ($this->request->getMethod() !== 'delete') {
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
                    'message' => 'ID do cronograma não fornecido'
                ])
                ->setStatusCode(400);
        }

        try {
            // Busca cronograma
            $cronograma = $this->cronogramaModel->find($id);

            if (!$cronograma) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Cronograma não encontrado'
                    ])
                    ->setStatusCode(404);
            }

            // Exclui (soft delete)
            if ($this->cronogramaModel->delete($id)) {
                return $this->response
                    ->setJSON([
                        'success' => true,
                        'message' => 'Cronograma excluído com sucesso'
                    ])
                    ->setStatusCode(200);
            }

            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao excluir cronograma'
                ])
                ->setStatusCode(500);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao excluir cronograma API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao excluir cronograma',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Restaura um cronograma excluído
     * POST /api/cronograma/{id}/restore
     * 
     * @param int $id ID do cronograma
     * @return \CodeIgniter\HTTP\Response
     */
    public function restore($id = null)
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

        if (!$id) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'ID do cronograma não fornecido'
                ])
                ->setStatusCode(400);
        }

        try {
            // Busca cronograma (incluindo excluídos)
            $cronograma = $this->cronogramaModel->withDeleted(true)->find($id);

            if (!$cronograma) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Cronograma não encontrado'
                    ])
                    ->setStatusCode(404);
            }

            if ($cronograma->deleted_at === null) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Cronograma não está excluído'
                    ])
                    ->setStatusCode(400);
            }

            // Restaura
            if ($this->cronogramaModel->protect(false)->save($cronograma->undelete())) {
                $cronogramaRestaurado = $this->cronogramaModel->find($id);

                return $this->response
                    ->setJSON([
                        'success' => true,
                        'message' => 'Cronograma restaurado com sucesso',
                        'data' => [
                            'id' => $cronogramaRestaurado->id,
                            'event_id' => $cronogramaRestaurado->event_id,
                            'name' => $cronogramaRestaurado->name,
                            'ativo' => (bool)$cronogramaRestaurado->ativo,
                        ]
                    ])
                    ->setStatusCode(200);
            }

            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao restaurar cronograma'
                ])
                ->setStatusCode(500);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao restaurar cronograma API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao restaurar cronograma',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Lista todos os cronogramas de um evento específico (atalho)
     * GET /api/cronograma/evento/{event_id}
     * 
     * @param int $event_id ID do evento
     * @return \CodeIgniter\HTTP\Response
     */
    public function byEvento($event_id = null)
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
            // Valida se evento existe
            $evento = $this->eventoModel->find($event_id);
            if (!$evento) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Evento não encontrado'
                    ])
                    ->setStatusCode(404);
            }

            // Busca cronogramas
            $cronogramas = $this->cronogramaModel->getCronogramasByEvento($event_id);

            // Formata resposta
            $data = [];
            foreach ($cronogramas as $cronograma) {
                $data[] = [
                    'id' => $cronograma->id,
                    'name' => $cronograma->name,
                    'ativo' => (bool)$cronograma->ativo,
                    'created_at' => $cronograma->created_at,
                    'updated_at' => $cronograma->updated_at,
                ];
            }

            return $this->response
                ->setJSON([
                    'success' => true,
                    'data' => [
                        'evento' => [
                            'id' => $evento->id,
                            'nome' => $evento->nome,
                        ],
                        'cronogramas' => $data,
                        'total' => count($data),
                    ]
                ])
                ->setStatusCode(200);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao listar cronogramas por evento API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao listar cronogramas',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }
}

