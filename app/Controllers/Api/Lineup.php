<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

/**
 * Controller API de Line-up
 * Disponibiliza o line-up filtrado por evento via API RESTful
 */
class Lineup extends BaseController
{
    private $lineupModel;
    private $eventoModel;

    public function __construct()
    {
        $this->lineupModel = new \App\Models\LineupModel();
        $this->eventoModel = new \App\Models\EventoModel();
    }

    /**
     * Lista o line-up, filtrando por evento e demais parâmetros
     * GET /api/lineup?event_id=1
     * GET /api/lineup?event_id=1&ativo=1
     * GET /api/lineup?event_id=1&dia=2026-06-15
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function index()
    {
        try {
            $event_id = $this->request->getGet('event_id');
            $ativo    = $this->request->getGet('ativo');
            $dia      = $this->request->getGet('dia');

            if (!$event_id) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Parâmetro event_id é obrigatório',
                    ])
                    ->setStatusCode(400);
            }

            $evento = $this->eventoModel->find($event_id);
            if (!$evento) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Evento não encontrado',
                    ])
                    ->setStatusCode(404);
            }

            $builder = $this->lineupModel
                ->where('event_id', $event_id)
                ->orderBy('dia', 'ASC')
                ->orderBy('ordem', 'ASC')
                ->orderBy('nome', 'ASC');

            if ($ativo !== null) {
                $builder->where('ativo', $ativo);
            }

            if (!empty($dia)) {
                $builder->where('dia', $dia);
            }

            $itens = $builder->findAll();

            $data = [];
            foreach ($itens as $item) {
                $data[] = $this->formatItem($item);
            }

            return $this->response
                ->setJSON([
                    'success' => true,
                    'data'    => [
                        'evento' => [
                            'id'   => (int) $evento->id,
                            'nome' => $evento->nome,
                        ],
                        'lineup' => $data,
                        'total'  => count($data),
                    ],
                ])
                ->setStatusCode(200);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao listar line-up API: ' . $e->getMessage());

            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao listar line-up',
                    'error'   => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno',
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Retorna detalhes de uma atração específica
     * GET /api/lineup/{id}
     *
     * @param int|null $id
     * @return \CodeIgniter\HTTP\Response
     */
    public function show($id = null)
    {
        if (!$id) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'ID da atração não fornecido',
                ])
                ->setStatusCode(400);
        }

        try {
            $item = $this->lineupModel->find($id);

            if (!$item) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Atração não encontrada',
                    ])
                    ->setStatusCode(404);
            }

            return $this->response
                ->setJSON([
                    'success' => true,
                    'data'    => $this->formatItem($item),
                ])
                ->setStatusCode(200);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao buscar atração line-up API: ' . $e->getMessage());

            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao buscar atração',
                    'error'   => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno',
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Lista o line-up de um evento específico (atalho)
     * GET /api/lineup/evento/{event_id}
     *
     * @param int|null $event_id
     * @return \CodeIgniter\HTTP\Response
     */
    public function byEvento($event_id = null)
    {
        if (!$event_id) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'ID do evento não fornecido',
                ])
                ->setStatusCode(400);
        }

        try {
            $evento = $this->eventoModel->find($event_id);
            if (!$evento) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Evento não encontrado',
                    ])
                    ->setStatusCode(404);
            }

            $itens = $this->lineupModel->getByEvento((int) $event_id);

            $data = [];
            foreach ($itens as $item) {
                $data[] = $this->formatItem($item);
            }

            return $this->response
                ->setJSON([
                    'success' => true,
                    'data'    => [
                        'evento' => [
                            'id'   => (int) $evento->id,
                            'nome' => $evento->nome,
                        ],
                        'lineup' => $data,
                        'total'  => count($data),
                    ],
                ])
                ->setStatusCode(200);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao listar line-up por evento API: ' . $e->getMessage());

            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao listar line-up',
                    'error'   => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno',
                ])
                ->setStatusCode(500);
        }
    }

    /**
     * Formata um item do line-up para a resposta da API
     */
    private function formatItem($item): array
    {
        $imagemUrl = !empty($item->imagem)
            ? site_url('lineup/imagem/' . $item->imagem)
            : null;

        return [
            'id'         => (int) $item->id,
            'event_id'   => (int) $item->event_id,
            'nome'       => $item->nome,
            'dia'        => $item->dia ? date('Y-m-d', strtotime((string) $item->dia)) : null,
            'tipo'       => $item->tipo,
            'descricao'  => $item->descricao,
            'imagem'     => $item->imagem,
            'imagem_url' => $imagemUrl,
            'ordem'      => (int) $item->ordem,
            'ativo'      => (int) $item->ativo,
            'created_at' => $item->created_at ? (string) $item->created_at : null,
            'updated_at' => $item->updated_at ? (string) $item->updated_at : null,
        ];
    }
}
