<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CronogramaModel;
use App\Models\CronogramaItemModel;
use App\Models\EventoModel;

class Cronograma extends BaseController
{
    protected $cronogramaModel;
    protected $itemModel;
    protected $eventoModel;

    public function __construct()
    {
        $this->cronogramaModel = new CronogramaModel();
        $this->itemModel       = new CronogramaItemModel();
        $this->eventoModel     = new EventoModel();
    }

    /**
     * Página principal: gerenciar cronogramas e itens do evento selecionado
     */
    public function index()
    {
        $eventId = $this->request->getGet('evento_id') ?? evento_selecionado();

        $eventos = $this->eventoModel->orderBy('data_inicio', 'DESC')->findAll();

        $cronogramas = [];
        if (!empty($eventId)) {
            $cronogramas = $this->cronogramaModel->getCronogramasByEvento((int) $eventId);
        }

        $evento = $eventId ? $this->eventoModel->find($eventId) : null;

        $data = [
            'titulo'             => 'Cronogramas',
            'eventos'            => $eventos,
            'eventIdSelecionado' => $eventId,
            'evento'             => $evento,
            'cronogramas'        => $cronogramas,
        ];

        return view('Cronograma/index', $data);
    }

    /**
     * Salvar cronograma (AJAX) - cria ou atualiza
     */
    public function salvar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $post = $this->request->getPost();

        $dados = [
            'event_id' => $post['event_id'] ?? null,
            'name'     => trim($post['name'] ?? ''),
            'ativo'    => isset($post['ativo']) ? 1 : 0,
        ];

        if (!empty($post['id'])) {
            $dados['id'] = $post['id'];
        }

        if ($this->cronogramaModel->save($dados)) {
            $retorno['sucesso'] = !empty($post['id']) ? 'Cronograma atualizado!' : 'Cronograma criado!';
            $retorno['id']      = $post['id'] ?? $this->cronogramaModel->getInsertID();
            return $this->response->setJSON($retorno);
        }

        $retorno['erro']         = 'Erro ao salvar cronograma.';
        $retorno['erros_model']  = $this->cronogramaModel->errors();
        return $this->response->setJSON($retorno);
    }

    /**
     * Excluir cronograma (AJAX)
     */
    public function excluir()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $id = $this->request->getPost('id');
        $cronograma = $this->cronogramaModel->find($id);

        if (!$cronograma) {
            $retorno['erro'] = 'Cronograma não encontrado.';
            return $this->response->setJSON($retorno);
        }

        // Soft delete cascateando para itens
        $this->itemModel->where('cronograma_id', $id)->delete();

        if ($this->cronogramaModel->delete($id)) {
            $retorno['sucesso'] = 'Cronograma excluído com sucesso!';
            return $this->response->setJSON($retorno);
        }

        $retorno['erro'] = 'Erro ao excluir cronograma.';
        return $this->response->setJSON($retorno);
    }

    /**
     * Retorna os itens de um cronograma (AJAX) - usado para recarregar a lista
     */
    public function itens($cronogramaId = null)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        if (!$cronogramaId) {
            return $this->response->setJSON(['data' => []]);
        }

        $itens = $this->itemModel->getItensByCronograma((int) $cronogramaId);

        $data = [];
        foreach ($itens as $item) {
            $data[] = [
                'id'               => $item->id,
                'nome_item'        => esc($item->nome_item),
                'data_hora_inicio' => $item->data_hora_inicio ? date('d/m/Y H:i', strtotime($item->data_hora_inicio)) : '-',
                'data_hora_fim'    => $item->data_hora_fim ? date('d/m/Y H:i', strtotime($item->data_hora_fim)) : '-',
                'duracao'          => $item->getDuracaoFormatada() ?? '-',
                'status'           => $item->getBadgeStatus(),
                'status_raw'       => $item->status,
                'ativo'            => $item->ativo,
                'inicio_raw'       => $item->data_hora_inicio ? date('Y-m-d\TH:i', strtotime($item->data_hora_inicio)) : '',
                'fim_raw'          => $item->data_hora_fim ? date('Y-m-d\TH:i', strtotime($item->data_hora_fim)) : '',
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    /**
     * Salvar item do cronograma (AJAX) - cria ou atualiza
     */
    public function salvarItem()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $post = $this->request->getPost();

        $inicio = !empty($post['data_hora_inicio']) ? str_replace('T', ' ', $post['data_hora_inicio']) : null;
        $fim    = !empty($post['data_hora_fim']) ? str_replace('T', ' ', $post['data_hora_fim']) : null;

        if ($inicio && strlen($inicio) === 16) {
            $inicio .= ':00';
        }
        if ($fim && strlen($fim) === 16) {
            $fim .= ':00';
        }

        if ($inicio && $fim && strtotime($fim) < strtotime($inicio)) {
            $retorno['erro'] = 'A data/hora de fim não pode ser anterior à de início.';
            return $this->response->setJSON($retorno);
        }

        $dados = [
            'cronograma_id'    => $post['cronograma_id'] ?? null,
            'nome_item'        => trim($post['nome_item'] ?? ''),
            'data_hora_inicio' => $inicio,
            'data_hora_fim'    => $fim,
            'status'           => $post['status'] ?? 'AGUARDANDO',
            'ativo'            => isset($post['ativo']) ? 1 : 0,
        ];

        if (!empty($post['id'])) {
            $dados['id'] = $post['id'];
        }

        if ($this->itemModel->save($dados)) {
            $retorno['sucesso'] = !empty($post['id']) ? 'Item atualizado!' : 'Item adicionado!';
            $retorno['id']      = $post['id'] ?? $this->itemModel->getInsertID();
            return $this->response->setJSON($retorno);
        }

        $retorno['erro']        = 'Erro ao salvar item.';
        $retorno['erros_model'] = $this->itemModel->errors();
        return $this->response->setJSON($retorno);
    }

    /**
     * Excluir item do cronograma (AJAX)
     */
    public function excluirItem()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $id = $this->request->getPost('id');
        $item = $this->itemModel->find($id);

        if (!$item) {
            $retorno['erro'] = 'Item não encontrado.';
            return $this->response->setJSON($retorno);
        }

        if ($this->itemModel->delete($id)) {
            $retorno['sucesso'] = 'Item excluído com sucesso!';
            return $this->response->setJSON($retorno);
        }

        $retorno['erro'] = 'Erro ao excluir item.';
        return $this->response->setJSON($retorno);
    }

    /**
     * Altera apenas o status de um item (AJAX)
     */
    public function alterarStatusItem()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $id     = $this->request->getPost('id');
        $status = $this->request->getPost('status');

        $permitidos = ['AGUARDANDO', 'EM_ANDAMENTO', 'CONCLUIDO', 'CANCELADO'];
        if (!in_array($status, $permitidos)) {
            $retorno['erro'] = 'Status inválido.';
            return $this->response->setJSON($retorno);
        }

        $item = $this->itemModel->find($id);
        if (!$item) {
            $retorno['erro'] = 'Item não encontrado.';
            return $this->response->setJSON($retorno);
        }

        $this->itemModel->update($id, ['status' => $status]);

        $retorno['sucesso'] = 'Status atualizado!';
        return $this->response->setJSON($retorno);
    }
}
