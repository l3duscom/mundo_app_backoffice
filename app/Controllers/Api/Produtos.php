<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ProdutoModel;
use App\Models\EventoModel;
use CodeIgniter\HTTP\ResponseInterface;

class Produtos extends BaseController
{
    protected $produtoModel;
    protected $eventoModel;

    public function __construct()
    {
        $this->produtoModel = new ProdutoModel();
        $this->eventoModel = new EventoModel();
    }

    /**
     * Lista todos os produtos
     * GET /api/produtos
     * 
     * Query params opcionais:
     * - event_id: filtrar por evento
     * - categoria: filtrar por categoria
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function index()
    {
        try {
            $eventId = $this->request->getGet('event_id');
            $categoria = $this->request->getGet('categoria');

            $builder = $this->produtoModel;

            // Aplica filtros se fornecidos
            if ($eventId) {
                $builder = $builder->where('event_id', $eventId);
            }

            if ($categoria) {
                $builder = $builder->where('categoria', $categoria);
            }

            $produtos = $builder->orderBy('categoria', 'ASC')
                               ->orderBy('nome', 'ASC')
                               ->findAll();

            $data = [];
            foreach ($produtos as $produto) {
                $data[] = [
                    'id'         => $produto->id,
                    'event_id'   => $produto->event_id,
                    'imagem'     => $produto->imagem,
                    'categoria'  => $produto->categoria,
                    'nome'       => $produto->nome,
                    'preco'      => $produto->preco,
                    'pontos'     => $produto->pontos,
                    'created_at' => $produto->created_at,
                ];
            }

            return $this->response
                ->setJSON([
                    'success' => true,
                    'data'    => $data,
                    'total'   => count($data),
                ])
                ->setStatusCode(200);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao listar produtos API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao listar produtos',
                    'error'   => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Lista produtos por evento
     * GET /api/produtos/evento/{event_id}
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

            $produtos = $this->produtoModel->getProdutosPorEvento($event_id);

            $data = [];
            foreach ($produtos as $produto) {
                $data[] = [
                    'id'         => $produto->id,
                    'event_id'   => $produto->event_id,
                    'imagem'     => $produto->imagem,
                    'categoria'  => $produto->categoria,
                    'nome'       => $produto->nome,
                    'preco'      => $produto->preco,
                    'pontos'     => $produto->pontos,
                    'created_at' => $produto->created_at,
                ];
            }

            return $this->response
                ->setJSON([
                    'success' => true,
                    'data'    => $data,
                    'total'   => count($data),
                ])
                ->setStatusCode(200);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao buscar produtos por evento API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao buscar produtos',
                    'error'   => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Lista categorias por evento
     * GET /api/produtos/categorias/{event_id}
     * 
     * @param int $event_id
     * @return \CodeIgniter\HTTP\Response
     */
    public function categorias($event_id = null)
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

            $categorias = $this->produtoModel->getCategoriasPorEvento($event_id);

            $data = [];
            foreach ($categorias as $cat) {
                $data[] = $cat->categoria;
            }

            return $this->response
                ->setJSON([
                    'success' => true,
                    'data'    => $data,
                    'total'   => count($data),
                ])
                ->setStatusCode(200);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao buscar categorias API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao buscar categorias',
                    'error'   => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Busca um produto específico
     * GET /api/produtos/{id}
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
                    'message' => 'ID do produto não fornecido'
                ])
                ->setStatusCode(400);
        }

        try {
            $produto = $this->produtoModel->find($id);

            if (!$produto) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Produto não encontrado'
                    ])
                    ->setStatusCode(404);
            }

            return $this->response
                ->setJSON([
                    'success' => true,
                    'data'    => [
                        'id'         => $produto->id,
                        'event_id'   => $produto->event_id,
                        'imagem'     => $produto->imagem,
                        'categoria'  => $produto->categoria,
                        'nome'       => $produto->nome,
                        'preco'      => $produto->preco,
                        'pontos'     => $produto->pontos,
                        'created_at' => $produto->created_at,
                        'updated_at' => $produto->updated_at,
                    ],
                ])
                ->setStatusCode(200);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao buscar produto API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao buscar produto',
                    'error'   => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Cria um novo produto
     * POST /api/produtos
     * 
     * Body JSON:
     * {
     *   "event_id": 1,
     *   "imagem": "/uploads/produtos/camiseta.png",
     *   "categoria": "Vestuário",
     *   "nome": "Camiseta Oficial",
     *   "preco": 79.90,
     *   "pontos": 100
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

            // Prepara dados para salvar
            $data = [
                'event_id'  => $json['event_id'],
                'imagem'    => $json['imagem'] ?? null,
                'categoria' => $json['categoria'] ?? '',
                'nome'      => $json['nome'] ?? '',
                'preco'     => $json['preco'] ?? 0.00,
                'pontos'    => $json['pontos'] ?? 0,
            ];

            // Salva no banco
            if ($this->produtoModel->save($data)) {
                $id = $this->produtoModel->getInsertID();
                $produtoCriado = $this->produtoModel->find($id);

                // Verifica se o produto foi encontrado
                if (!$produtoCriado) {
                    log_message('error', 'Produto criado mas não encontrado após inserção. ID: ' . $id);
                    return $this->response
                        ->setJSON([
                            'success' => false,
                            'message' => 'Produto criado mas houve erro ao recuperar os dados'
                        ])
                        ->setStatusCode(500);
                }

                return $this->response
                    ->setJSON([
                        'success' => true,
                        'message' => 'Produto criado com sucesso',
                        'data'    => [
                            'id'         => $produtoCriado->id,
                            'event_id'   => $produtoCriado->event_id,
                            'imagem'     => $produtoCriado->imagem,
                            'categoria'  => $produtoCriado->categoria,
                            'nome'       => $produtoCriado->nome,
                            'preco'      => $produtoCriado->preco,
                            'pontos'     => $produtoCriado->pontos,
                            'created_at' => $produtoCriado->created_at,
                        ]
                    ])
                    ->setStatusCode(201);
            }

            // Erro de validação
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao criar produto',
                    'errors'  => $this->produtoModel->errors()
                ])
                ->setStatusCode(422);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao criar produto API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao criar produto',
                    'error'   => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Atualiza um produto existente
     * PUT /api/produtos/{id}
     * PATCH /api/produtos/{id}
     * 
     * @param int $id ID do produto
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
                    'message' => 'ID do produto não fornecido'
                ])
                ->setStatusCode(400);
        }

        try {
            // Busca produto
            $produto = $this->produtoModel->find($id);

            if (!$produto) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Produto não encontrado'
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
            if (isset($json['event_id']) && $json['event_id'] != $produto->event_id) {
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
            if ($this->produtoModel->save($data)) {
                $produtoAtualizado = $this->produtoModel->find($id);

                // Verifica se o produto foi encontrado
                if (!$produtoAtualizado) {
                    log_message('error', 'Produto atualizado mas não encontrado. ID: ' . $id);
                    return $this->response
                        ->setJSON([
                            'success' => false,
                            'message' => 'Produto atualizado mas houve erro ao recuperar os dados'
                        ])
                        ->setStatusCode(500);
                }

                return $this->response
                    ->setJSON([
                        'success' => true,
                        'message' => 'Produto atualizado com sucesso',
                        'data'    => [
                            'id'         => $produtoAtualizado->id,
                            'event_id'   => $produtoAtualizado->event_id,
                            'imagem'     => $produtoAtualizado->imagem,
                            'categoria'  => $produtoAtualizado->categoria,
                            'nome'       => $produtoAtualizado->nome,
                            'preco'      => $produtoAtualizado->preco,
                            'pontos'     => $produtoAtualizado->pontos,
                            'updated_at' => $produtoAtualizado->updated_at,
                        ]
                    ])
                    ->setStatusCode(200);
            }

            // Erro de validação
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao atualizar produto',
                    'errors'  => $this->produtoModel->errors()
                ])
                ->setStatusCode(422);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao atualizar produto API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao atualizar produto',
                    'error'   => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Deleta um produto (soft delete)
     * DELETE /api/produtos/{id}
     * 
     * @param int $id ID do produto
     * @return \CodeIgniter\HTTP\Response
     */
    public function delete($id = null)
    {
        if (!$id) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'ID do produto não fornecido'
                ])
                ->setStatusCode(400);
        }

        try {
            $produto = $this->produtoModel->find($id);

            if (!$produto) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Produto não encontrado'
                    ])
                    ->setStatusCode(404);
            }

            if ($this->produtoModel->delete($id)) {
                return $this->response
                    ->setJSON([
                        'success' => true,
                        'message' => 'Produto deletado com sucesso'
                    ])
                    ->setStatusCode(200);
            }

            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao deletar produto'
                ])
                ->setStatusCode(500);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao deletar produto API: ' . $e->getMessage());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao deletar produto',
                    'error'   => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
                ])
                ->setStatusCode(500);
        }
    }
}

