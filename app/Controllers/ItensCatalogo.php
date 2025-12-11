<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\ItemCatalogo;

class ItensCatalogo extends BaseController
{

    private $itemCatalogoModel;
    private $eventoModel;

    public function __construct()
    {
        $this->itemCatalogoModel = new \App\Models\ItemCatalogoModel();
        $this->eventoModel = new \App\Models\EventoModel();
    }

    public function index()
    {
        $data = [
            'titulo' => 'Catálogo de Itens',
            'eventos' => $this->eventoModel->orderBy('id', 'DESC')->findAll(),
        ];

        return view('ItensCatalogo/index', $data);
    }

    /**
     * Recupera itens para DataTable via AJAX
     */
    public function recuperaItens()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $eventId = $this->request->getGet('event_id');

        $builder = $this->itemCatalogoModel
            ->select('itens_catalogo.*, eventos.nome as evento_nome')
            ->join('eventos', 'eventos.id = itens_catalogo.event_id', 'left')
            ->withDeleted(true);

        if (!empty($eventId)) {
            $builder->where('itens_catalogo.event_id', $eventId);
        }

        $itens = $builder->orderBy('eventos.nome', 'ASC')
            ->orderBy('itens_catalogo.tipo', 'ASC')
            ->orderBy('itens_catalogo.nome', 'ASC')
            ->findAll();

        $data = [];

        foreach ($itens as $item) {
            $data[] = [
                'evento' => esc($item->evento_nome ?? 'N/A'),
                'nome' => anchor("itenscatalogo/exibir/$item->id", esc($item->nome), 'title="Exibir item"'),
                'tipo' => $item->getBadgeTipo(),
                'metragem' => esc($item->metragem ?? '-'),
                'valor' => $item->getValorFormatado(),
                'status' => $item->exibeStatus(),
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    /**
     * Busca itens para Select2 (AJAX) - filtra por evento
     */
    public function buscaItens()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $termo = $this->request->getGet('termo') ?? '';
        $eventId = $this->request->getGet('event_id') ? (int)$this->request->getGet('event_id') : null;
        
        $itens = $this->itemCatalogoModel->buscaParaSelect($termo, $eventId);

        $resultado = [];
        foreach ($itens as $item) {
            $texto = $item->nome;
            if (!empty($item->metragem)) {
                $texto .= ' (' . $item->metragem . ')';
            }
            $texto .= ' - R$ ' . number_format($item->valor, 2, ',', '.');

            $resultado[] = [
                'id' => $item->id,
                'text' => $texto,
                'nome' => $item->nome,
                'tipo' => $item->tipo,
                'metragem' => $item->metragem,
                'valor' => $item->valor,
                'valor_formatado' => number_format($item->valor, 2, ',', '.'),
            ];
        }

        return $this->response->setJSON(['results' => $resultado]);
    }

    public function criar()
    {
        $item = new ItemCatalogo();

        $data = [
            'titulo' => "Novo item no catálogo",
            'item' => $item,
            'eventos' => $this->eventoModel->orderBy('id', 'DESC')->findAll(),
        ];

        return view('ItensCatalogo/criar', $data);
    }

    public function cadastrar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $post = $this->request->getPost();

        // Limpar valor monetário
        $post['valor'] = $this->limparValorMonetario($post['valor'] ?? '0');
        $post['ativo'] = isset($post['ativo']) ? 1 : 0;

        $item = new ItemCatalogo($post);

        if ($this->itemCatalogoModel->save($item)) {
            $btnCriar = anchor("itenscatalogo/criar", 'Cadastrar novo item', ['class' => 'btn btn-danger mt-2']);

            session()->setFlashdata('sucesso', "Item cadastrado com sucesso!<br> $btnCriar");

            $retorno['id'] = $this->itemCatalogoModel->getInsertID();

            return $this->response->setJSON($retorno);
        }

        $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
        $retorno['erros_model'] = $this->itemCatalogoModel->errors();

        return $this->response->setJSON($retorno);
    }

    public function exibir(int $id = null)
    {
        $item = $this->buscaItemOu404($id);
        $evento = $this->eventoModel->find($item->event_id);

        $data = [
            'titulo' => "Detalhes: " . esc($item->nome),
            'item' => $item,
            'evento' => $evento,
        ];

        return view('ItensCatalogo/exibir', $data);
    }

    public function editar(int $id = null)
    {
        $item = $this->buscaItemOu404($id);

        $data = [
            'titulo' => "Editando: " . esc($item->nome),
            'item' => $item,
            'eventos' => $this->eventoModel->orderBy('id', 'DESC')->findAll(),
        ];

        return view('ItensCatalogo/editar', $data);
    }

    public function atualizar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $post = $this->request->getPost();

        $item = $this->buscaItemOu404($post['id']);

        // Limpar valor monetário
        $post['valor'] = $this->limparValorMonetario($post['valor'] ?? '0');
        $post['ativo'] = isset($post['ativo']) ? 1 : 0;

        $item->fill($post);

        if ($item->hasChanged() === false) {
            $retorno['info'] = 'Não há dados para atualizar';
            return $this->response->setJSON($retorno);
        }

        if ($this->itemCatalogoModel->save($item)) {
            session()->setFlashdata('sucesso', 'Item atualizado com sucesso!');
            return $this->response->setJSON($retorno);
        }

        $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
        $retorno['erros_model'] = $this->itemCatalogoModel->errors();

        return $this->response->setJSON($retorno);
    }

    public function excluir(int $id = null)
    {
        $item = $this->buscaItemOu404($id);

        if ($item->deleted_at != null) {
            return redirect()->back()->with('info', "Esse item já foi excluído!");
        }

        if ($this->request->getMethod() === 'post') {
            $this->itemCatalogoModel->delete($item->id);
            return redirect()->to(site_url("itenscatalogo"))->with('sucesso', 'Item excluído com sucesso!');
        }

        $data = [
            'titulo' => "Excluir: " . esc($item->nome),
            'item' => $item,
        ];

        return view('ItensCatalogo/excluir', $data);
    }

    public function desfazerExclusao(int $id = null)
    {
        $item = $this->buscaItemOu404($id);

        if ($item->deleted_at == null) {
            return redirect()->back()->with('info', "Esse item não está excluído!");
        }

        $item->deleted_at = null;
        $this->itemCatalogoModel->protect(false)->save($item);

        return redirect()->back()->with('sucesso', 'Item restaurado com sucesso!');
    }

    /**
     * Limpa valor monetário
     */
    private function limparValorMonetario(string $valor): float
    {
        $valor = preg_replace('/[R$\s]/', '', $valor);
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
        
        return (float)$valor;
    }

    /**
     * Busca item ou retorna 404
     */
    private function buscaItemOu404(int $id = null)
    {
        if (!$id || !$item = $this->itemCatalogoModel->withDeleted(true)->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos o item $id");
        }

        return $item;
    }
}

