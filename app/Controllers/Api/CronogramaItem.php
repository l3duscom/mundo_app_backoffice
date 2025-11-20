<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Entities\CronogramaItemEntity;

/**
 * Controller API de Itens do Cronograma
 * Gerencia itens de cronogramas via API RESTful
 */
class CronogramaItem extends BaseController
{
    private $cronogramaItemModel;
    private $cronogramaModel;

    public function __construct()
    {
        $this->cronogramaItemModel = new \App\Models\CronogramaItemModel();
        $this->cronogramaModel = new \App\Models\CronogramaModel();
    }

    /**
     * Lista todos os itens ou filtra por cronograma
     * GET /api/cronograma-item
     * GET /api/cronograma-item?cronograma_id=1
     * GET /api/cronograma-item?status=AGUARDANDO
     * GET /api/cronograma-item?ativo=1
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function index()
    {
        try {
            // Recupera parâmetros de filtro
            $cronograma_id = $this->request->getGet('cronograma_id');
            $status = $this->request->getGet('status');
            $ativo = $this->request->getGet('ativo');

            $builder = $this->cronogramaItemModel
                ->select([
                    'cronograma_itens.*',
                    'cronograma.name as cronograma_nome',
                    'cronograma.event_id',
                    'eventos.nome as evento_nome'
                ])
                ->join('cronograma', 'cronograma.id = cronograma_itens.cronograma_id')
                ->join('eventos', 'eventos.id = cronograma.event_id')
                ->orderBy('cronograma_itens.data_hora_inicio', 'ASC');

            // Aplica filtros
            if ($cronograma_id) {
                $builder->where('cronograma_itens.cronograma_id', $cronograma_id);
            }

            if ($status) {
                $builder->where('cronograma_itens.status', $status);
            }

            if ($ativo !== null) {
                $builder->where('cronograma_itens.ativo', $ativo);
            }

            $itens = $builder->findAll();

            // Formata resposta
            $data = [];
            foreach ($itens as $item) {
                $data[] = [
                    'id' => $item->id,
                    'cronograma_id' => $item->cronograma_id,
                    'nome_item' => $item->nome_item,
                    'data_hora_inicio' => $item->data_hora_inicio,
                    'data_hora_fim' => $item->data_hora_fim,
                    'duracao_minutos' => $item->getDuracaoMinutos(),
                    'duracao_formatada' => $item->getDuracaoFormatada(),
                    'ativo' => (bool)$item->ativo,
                    'status' => $item->status,
                    'is_passado' => $item->isPassado(),
                    'is_agora' => $item->isAgora(),
                    'cronograma' => [
                        'id' => $item->cronograma_id,
                        'nome' => $item->cronograma_nome,
                    ],
                    'evento' => [
                        'id' => $item->event_id,
                        'nome' => $item->evento_nome,
                    ],
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
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
            log_message('error', 'Erro ao listar itens do cronograma API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao listar itens',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Retorna detalhes de um item específico
     * GET /api/cronograma-item/{id}
     * 
     * @param int $id ID do item
     * @return \CodeIgniter\HTTP\Response
     */
    public function show($id = null)
    {
        if (!$id) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'ID do item não fornecido'
                ])
                ->setStatusCode(400);
        }

        try {
            $item = $this->cronogramaItemModel
                ->select([
                    'cronograma_itens.*',
                    'cronograma.name as cronograma_nome',
                    'cronograma.event_id',
                    'eventos.nome as evento_nome',
                    'eventos.slug as evento_slug'
                ])
                ->join('cronograma', 'cronograma.id = cronograma_itens.cronograma_id')
                ->join('eventos', 'eventos.id = cronograma.event_id')
                ->find($id);

            if (!$item) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Item não encontrado'
                    ])
                    ->setStatusCode(404);
            }

            // Formata resposta
            $data = [
                'id' => $item->id,
                'cronograma_id' => $item->cronograma_id,
                'nome_item' => $item->nome_item,
                'data_hora_inicio' => $item->data_hora_inicio,
                'data_hora_fim' => $item->data_hora_fim,
                'duracao_minutos' => $item->getDuracaoMinutos(),
                'duracao_formatada' => $item->getDuracaoFormatada(),
                'ativo' => (bool)$item->ativo,
                'status' => $item->status,
                'is_passado' => $item->isPassado(),
                'is_agora' => $item->isAgora(),
                'cronograma' => [
                    'id' => $item->cronograma_id,
                    'nome' => $item->cronograma_nome,
                ],
                'evento' => [
                    'id' => $item->event_id,
                    'nome' => $item->evento_nome,
                    'slug' => $item->evento_slug,
                ],
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];

            return $this->response
                ->setJSON([
                    'success' => true,
                    'data' => $data
                ])
                ->setStatusCode(200);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao buscar item do cronograma API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao buscar item',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Cria um novo item
     * POST /api/cronograma-item
     * 
     * Body JSON:
     * {
     *   "cronograma_id": 1,
     *   "nome_item": "Nome do item",
     *   "data_hora_inicio": "2024-12-01 10:00:00",
     *   "data_hora_fim": "2024-12-01 11:00:00",
     *   "ativo": true,
     *   "status": "AGUARDANDO"
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

            // Valida cronograma
            if (!isset($json['cronograma_id'])) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Campo cronograma_id é obrigatório'
                    ])
                    ->setStatusCode(400);
            }

            $cronograma = $this->cronogramaModel->find($json['cronograma_id']);
            if (!$cronograma) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Cronograma não encontrado'
                    ])
                    ->setStatusCode(404);
            }

            // Cria entidade
            $item = new CronogramaItemEntity([
                'cronograma_id' => $json['cronograma_id'],
                'nome_item' => $json['nome_item'] ?? '',
                'data_hora_inicio' => $json['data_hora_inicio'] ?? null,
                'data_hora_fim' => $json['data_hora_fim'] ?? null,
                'ativo' => $json['ativo'] ?? true,
                'status' => $json['status'] ?? 'AGUARDANDO',
            ]);

            // Salva no banco
            if ($this->cronogramaItemModel->save($item)) {
                $id = $this->cronogramaItemModel->getInsertID();
                $itemCriado = $this->cronogramaItemModel->find($id);

                // Verifica se o item foi encontrado
                if (!$itemCriado) {
                    log_message('error', 'Item criado mas não encontrado após inserção. ID: ' . $id);
                    return $this->response
                        ->setJSON([
                            'success' => false,
                            'message' => 'Item criado mas houve erro ao recuperar os dados'
                        ])
                        ->setStatusCode(500);
                }

                return $this->response
                    ->setJSON([
                        'success' => true,
                        'message' => 'Item criado com sucesso',
                        'data' => [
                            'id' => $itemCriado->id,
                            'cronograma_id' => $itemCriado->cronograma_id,
                            'nome_item' => $itemCriado->nome_item,
                            'data_hora_inicio' => $itemCriado->data_hora_inicio,
                            'data_hora_fim' => $itemCriado->data_hora_fim,
                            'ativo' => (bool)$itemCriado->ativo,
                            'status' => $itemCriado->status,
                            'created_at' => $itemCriado->created_at,
                        ]
                    ])
                    ->setStatusCode(201);
            }

            // Erro de validação
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao criar item',
                    'errors' => $this->cronogramaItemModel->errors()
                ])
                ->setStatusCode(422);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao criar item do cronograma API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao criar item',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Atualiza um item existente
     * PUT /api/cronograma-item/{id}
     * PATCH /api/cronograma-item/{id}
     * 
     * @param int $id ID do item
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
                    'message' => 'ID do item não fornecido'
                ])
                ->setStatusCode(400);
        }

        try {
            // Busca item
            $item = $this->cronogramaItemModel->find($id);

            if (!$item) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Item não encontrado'
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

            // Se mudar o cronograma, valida se existe
            if (isset($json['cronograma_id']) && $json['cronograma_id'] != $item->cronograma_id) {
                $cronograma = $this->cronogramaModel->find($json['cronograma_id']);
                if (!$cronograma) {
                    return $this->response
                        ->setJSON([
                            'success' => false,
                            'message' => 'Cronograma não encontrado'
                        ])
                        ->setStatusCode(404);
                }
            }

            // Atualiza campos
            $item->fill($json);

            if (!$item->hasChanged()) {
                return $this->response
                    ->setJSON([
                        'success' => true,
                        'message' => 'Nenhuma alteração detectada',
                        'data' => [
                            'id' => $item->id,
                            'cronograma_id' => $item->cronograma_id,
                            'nome_item' => $item->nome_item,
                            'status' => $item->status,
                        ]
                    ])
                    ->setStatusCode(200);
            }

            // Salva alterações
            if ($this->cronogramaItemModel->save($item)) {
                $itemAtualizado = $this->cronogramaItemModel->find($id);

                // Verifica se o item foi encontrado
                if (!$itemAtualizado) {
                    log_message('error', 'Item atualizado mas não encontrado. ID: ' . $id);
                    return $this->response
                        ->setJSON([
                            'success' => false,
                            'message' => 'Item atualizado mas houve erro ao recuperar os dados'
                        ])
                        ->setStatusCode(500);
                }

                return $this->response
                    ->setJSON([
                        'success' => true,
                        'message' => 'Item atualizado com sucesso',
                        'data' => [
                            'id' => $itemAtualizado->id,
                            'cronograma_id' => $itemAtualizado->cronograma_id,
                            'nome_item' => $itemAtualizado->nome_item,
                            'data_hora_inicio' => $itemAtualizado->data_hora_inicio,
                            'data_hora_fim' => $itemAtualizado->data_hora_fim,
                            'ativo' => (bool)$itemAtualizado->ativo,
                            'status' => $itemAtualizado->status,
                            'updated_at' => $itemAtualizado->updated_at,
                        ]
                    ])
                    ->setStatusCode(200);
            }

            // Erro de validação
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao atualizar item',
                    'errors' => $this->cronogramaItemModel->errors()
                ])
                ->setStatusCode(422);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao atualizar item do cronograma API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao atualizar item',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Exclui (soft delete) um item
     * DELETE /api/cronograma-item/{id}
     * 
     * @param int $id ID do item
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
                    'message' => 'ID do item não fornecido'
                ])
                ->setStatusCode(400);
        }

        try {
            // Busca item
            $item = $this->cronogramaItemModel->find($id);

            if (!$item) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Item não encontrado'
                    ])
                    ->setStatusCode(404);
            }

            // Exclui (soft delete)
            if ($this->cronogramaItemModel->delete($id)) {
                return $this->response
                    ->setJSON([
                        'success' => true,
                        'message' => 'Item excluído com sucesso'
                    ])
                    ->setStatusCode(200);
            }

            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao excluir item'
                ])
                ->setStatusCode(500);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao excluir item do cronograma API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao excluir item',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Lista todos os itens de um cronograma específico
     * GET /api/cronograma-item/cronograma/{cronograma_id}
     * 
     * @param int $cronograma_id ID do cronograma
     * @return \CodeIgniter\HTTP\Response
     */
    public function byCronograma($cronograma_id = null)
    {
        if (!$cronograma_id) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'ID do cronograma não fornecido'
                ])
                ->setStatusCode(400);
        }

        try {
            // Valida se cronograma existe
            $cronograma = $this->cronogramaModel->find($cronograma_id);
            if (!$cronograma) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Cronograma não encontrado'
                    ])
                    ->setStatusCode(404);
            }

            // Busca itens
            $itens = $this->cronogramaItemModel->getItensComDetalhes($cronograma_id);

            // Formata resposta
            $data = [];
            foreach ($itens as $item) {
                $data[] = [
                    'id' => $item->id,
                    'nome_item' => $item->nome_item,
                    'data_hora_inicio' => $item->data_hora_inicio,
                    'data_hora_fim' => $item->data_hora_fim,
                    'duracao_minutos' => $item->getDuracaoMinutos(),
                    'duracao_formatada' => $item->getDuracaoFormatada(),
                    'ativo' => (bool)$item->ativo,
                    'status' => $item->status,
                    'is_passado' => $item->isPassado(),
                    'is_agora' => $item->isAgora(),
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            }

            return $this->response
                ->setJSON([
                    'success' => true,
                    'data' => [
                        'cronograma' => [
                            'id' => $cronograma->id,
                            'nome' => $cronograma->name,
                        ],
                        'itens' => $data,
                        'total' => count($data),
                    ]
                ])
                ->setStatusCode(200);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao listar itens por cronograma API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao listar itens',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Lista próximos itens de um cronograma
     * GET /api/cronograma-item/cronograma/{cronograma_id}/proximos
     * GET /api/cronograma-item/cronograma/{cronograma_id}/proximos?limit=5
     * 
     * @param int $cronograma_id ID do cronograma
     * @return \CodeIgniter\HTTP\Response
     */
    public function proximos($cronograma_id = null)
    {
        if (!$cronograma_id) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'ID do cronograma não fornecido'
                ])
                ->setStatusCode(400);
        }

        try {
            $limit = $this->request->getGet('limit') ?? 10;

            // Valida se cronograma existe
            $cronograma = $this->cronogramaModel->find($cronograma_id);
            if (!$cronograma) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Cronograma não encontrado'
                    ])
                    ->setStatusCode(404);
            }

            // Busca próximos itens
            $itens = $this->cronogramaItemModel->getProximosItens($cronograma_id, $limit);

            // Formata resposta
            $data = [];
            foreach ($itens as $item) {
                $data[] = [
                    'id' => $item->id,
                    'nome_item' => $item->nome_item,
                    'data_hora_inicio' => $item->data_hora_inicio,
                    'data_hora_fim' => $item->data_hora_fim,
                    'duracao_minutos' => $item->getDuracaoMinutos(),
                    'duracao_formatada' => $item->getDuracaoFormatada(),
                    'status' => $item->status,
                ];
            }

            return $this->response
                ->setJSON([
                    'success' => true,
                    'data' => [
                        'cronograma' => [
                            'id' => $cronograma->id,
                            'nome' => $cronograma->name,
                        ],
                        'proximos_itens' => $data,
                        'total' => count($data),
                    ]
                ])
                ->setStatusCode(200);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao listar próximos itens API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao listar próximos itens',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Atualiza o status de um item
     * PATCH /api/cronograma-item/{id}/status
     * 
     * Body: { "status": "EM_ANDAMENTO" }
     * 
     * @param int $id ID do item
     * @return \CodeIgniter\HTTP\Response
     */
    public function updateStatus($id = null)
    {
        if (!$id) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'ID do item não fornecido'
                ])
                ->setStatusCode(400);
        }

        try {
            $json = $this->request->getJSON(true);
            
            if (!is_array($json) || !isset($json['status'])) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'JSON inválido ou campo status é obrigatório'
                    ])
                    ->setStatusCode(400);
            }

            $item = $this->cronogramaItemModel->find($id);

            if (!$item) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Item não encontrado'
                    ])
                    ->setStatusCode(404);
            }

            $item->status = $json['status'];

            if ($this->cronogramaItemModel->save($item)) {
                return $this->response
                    ->setJSON([
                        'success' => true,
                        'message' => 'Status atualizado com sucesso',
                        'data' => [
                            'id' => $item->id,
                            'status' => $item->status,
                        ]
                    ])
                    ->setStatusCode(200);
            }

            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao atualizar status',
                    'errors' => $this->cronogramaItemModel->errors()
                ])
                ->setStatusCode(422);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao atualizar status do item API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao atualizar status',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }
}

