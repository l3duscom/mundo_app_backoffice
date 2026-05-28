<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class ChecklistModelos extends BaseController
{
    protected $modeloModel;
    protected $itemModel;
    protected $eventoModel;

    public function __construct()
    {
        $this->modeloModel = new \App\Models\ChecklistModeloModel();
        $this->itemModel   = new \App\Models\ChecklistModeloItemModel();
        $this->eventoModel = new \App\Models\EventoModel();
    }

    public function index()
    {
        $eventoId = $this->request->getGet('event_id');

        $eventos = $this->eventoModel->orderBy('data_inicio', 'DESC')->findAll();

        $modelos = [];
        if (!empty($eventoId)) {
            $modelos = $this->modeloModel->porEvento((int) $eventoId);
            foreach ($modelos as $m) {
                $m->total_itens = $this->itemModel->where('modelo_id', $m->id)->countAllResults();
            }
        }

        $data = [
            'titulo'  => 'Modelos de Checklist',
            'eventos' => $eventos,
            'eventoIdSelecionado' => $eventoId,
            'modelos' => $modelos,
        ];

        return view('ChecklistModelos/index', $data);
    }

    public function criar()
    {
        $eventoId = $this->request->getGet('event_id');

        $data = [
            'titulo'  => 'Novo Modelo de Checklist',
            'modelo'  => null,
            'itens'   => [],
            'eventos' => $this->eventoModel->orderBy('data_inicio', 'DESC')->findAll(),
            'eventoIdSelecionado' => $eventoId,
        ];

        return view('ChecklistModelos/form', $data);
    }

    public function editar($id)
    {
        $modelo = $this->modeloModel->find($id);
        if (!$modelo) {
            return redirect()->to(site_url('checklist-modelos'))->with('erro', 'Modelo não encontrado.');
        }

        $data = [
            'titulo'  => 'Editar Modelo de Checklist',
            'modelo'  => $modelo,
            'itens'   => $this->itemModel->porModelo($modelo->id),
            'eventos' => $this->eventoModel->orderBy('data_inicio', 'DESC')->findAll(),
            'eventoIdSelecionado' => $modelo->event_id,
        ];

        return view('ChecklistModelos/form', $data);
    }

    public function salvar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno = ['token' => csrf_hash()];

        $post = $this->request->getPost();

        $dados = [
            'event_id' => (int) ($post['event_id'] ?? 0),
            'tipo'     => $post['tipo'] ?? 'entrada',
            'nome'     => trim((string) ($post['nome'] ?? '')) ?: null,
            'ativo'    => isset($post['ativo']) ? 1 : 0,
        ];

        if (!empty($post['id'])) {
            $dados['id'] = (int) $post['id'];
        }

        if (!$this->modeloModel->save($dados)) {
            $retorno['erro']        = 'Erro ao salvar modelo.';
            $retorno['erros_model'] = $this->modeloModel->errors();
            return $this->response->setJSON($retorno);
        }

        $modeloId = !empty($post['id']) ? (int) $post['id'] : $this->modeloModel->getInsertID();

        // Substitui os itens (mais simples e seguro do que sincronizar)
        $this->itemModel->removerPorModelo($modeloId);

        $itens = $post['itens'] ?? [];
        if (is_array($itens)) {
            $ordem = 0;
            foreach ($itens as $item) {
                $titulo = trim((string) ($item['titulo'] ?? ''));
                if ($titulo === '') {
                    continue;
                }
                $this->itemModel->insert([
                    'modelo_id'  => $modeloId,
                    'titulo'     => $titulo,
                    'quantidade' => max(1, (int) ($item['quantidade'] ?? 1)),
                    'tipo'       => trim((string) ($item['tipo'] ?? '')) ?: null,
                    'categoria'  => trim((string) ($item['categoria'] ?? '')) ?: null,
                    'ordem'      => $ordem++,
                ]);
            }
        }

        $retorno['sucesso']  = !empty($post['id']) ? 'Modelo atualizado!' : 'Modelo criado!';
        $retorno['id']       = $modeloId;
        $retorno['redirect'] = site_url('checklist-modelos?event_id=' . (int) $dados['event_id']);

        return $this->response->setJSON($retorno);
    }

    public function excluir()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno = ['token' => csrf_hash()];

        $id = (int) $this->request->getPost('id');
        $modelo = $this->modeloModel->find($id);

        if (!$modelo) {
            $retorno['erro'] = 'Modelo não encontrado.';
            return $this->response->setJSON($retorno);
        }

        $this->itemModel->removerPorModelo($id);

        if ($this->modeloModel->delete($id)) {
            $retorno['sucesso'] = 'Modelo excluído.';
            return $this->response->setJSON($retorno);
        }

        $retorno['erro'] = 'Erro ao excluir modelo.';
        return $this->response->setJSON($retorno);
    }

    /**
     * Copia um modelo (e seus itens) para outro evento.
     */
    public function copiar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno = ['token' => csrf_hash()];

        $modeloId = (int) $this->request->getPost('modelo_id');
        $eventoDestinoId = (int) $this->request->getPost('event_id_destino');

        $modelo = $this->modeloModel->find($modeloId);
        if (!$modelo) {
            $retorno['erro'] = 'Modelo de origem não encontrado.';
            return $this->response->setJSON($retorno);
        }

        if ($eventoDestinoId <= 0 || !$this->eventoModel->find($eventoDestinoId)) {
            $retorno['erro'] = 'Evento destino inválido.';
            return $this->response->setJSON($retorno);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $novoId = $this->modeloModel->insert([
            'event_id' => $eventoDestinoId,
            'tipo'     => $modelo->tipo,
            'nome'     => $modelo->nome,
            'ativo'    => $modelo->ativo,
        ]);

        $itens = $this->itemModel->porModelo($modelo->id);
        foreach ($itens as $item) {
            $this->itemModel->insert([
                'modelo_id'  => $novoId,
                'titulo'     => $item->titulo,
                'quantidade' => $item->quantidade,
                'tipo'       => $item->tipo,
                'categoria'  => $item->categoria,
                'ordem'      => $item->ordem,
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            $retorno['erro'] = 'Falha ao copiar modelo.';
            return $this->response->setJSON($retorno);
        }

        $retorno['sucesso']  = 'Modelo copiado!';
        $retorno['redirect'] = site_url('checklist-modelos?event_id=' . $eventoDestinoId);
        return $this->response->setJSON($retorno);
    }
}
