<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CupomModel;
use App\Models\EventoModel;
use App\Traits\ValidacoesTrait;

class Cupons extends BaseController
{
    use ValidacoesTrait;

    private $cupomModel;
    private $eventoModel;

    public function __construct()
    {
        $this->cupomModel = new CupomModel();
        $this->eventoModel = new EventoModel();
    }

    /**
     * Lista cupons
     */
    public function index()
    {
        if (!$this->usuarioLogado()->temPermissaoPara('listar-cupons')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        // Pega evento da URL ou do contexto
        $eventoId = $this->request->getGet('evento_id') ?? evento_selecionado();

        $data = [
            'titulo' => 'Cupons de Desconto',
            'eventos' => $this->eventoModel->orderBy('id', 'DESC')->findAll(),
            'evento_id' => $eventoId,
        ];

        return view('Cupons/index', $data);
    }

    /**
     * Formulário de criação
     */
    public function criar()
    {
        if (!$this->usuarioLogado()->temPermissaoPara('criar-cupons')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        // Pega evento da URL ou do contexto
        $eventoId = $this->request->getGet('evento_id') ?? evento_selecionado();

        $data = [
            'titulo' => 'Novo Cupom de Desconto',
            'eventos' => $this->eventoModel->orderBy('id', 'DESC')->findAll(),
            'evento_id' => $eventoId,
        ];

        return view('Cupons/criar', $data);
    }

    /**
     * Cadastrar cupom (AJAX)
     */
    public function cadastrar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $post = $this->request->getPost();

        // Converter código para maiúsculas
        $post['codigo'] = strtoupper($post['codigo'] ?? '');

        // Verificar se código já existe
        $existente = $this->cupomModel->buscaPorCodigo($post['codigo']);
        if ($existente) {
            $retorno['erro'] = 'Este código de cupom já existe.';
            return $this->response->setJSON($retorno);
        }

        // Validar campos
        $cupom = new \App\Entities\Cupom($post);

        if (!$this->cupomModel->protect(false)->insert($cupom)) {
            $retorno['erro'] = 'Erro ao cadastrar cupom.';
            $retorno['erros_model'] = $this->cupomModel->errors();
            return $this->response->setJSON($retorno);
        }

        session()->setFlashdata('sucesso', 'Cupom cadastrado com sucesso!');
        $retorno['sucesso'] = 'Cupom cadastrado com sucesso!';
        $retorno['redirect'] = site_url('cupons');

        return $this->response->setJSON($retorno);
    }

    /**
     * Formulário de edição
     */
    public function editar($id = null)
    {
        if (!$this->usuarioLogado()->temPermissaoPara('editar-cupons')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $cupom = $this->buscaCupomOu404($id);

        $data = [
            'titulo' => 'Editar Cupom: ' . $cupom->codigo,
            'cupom' => $cupom,
            'eventos' => $this->eventoModel->orderBy('id', 'DESC')->findAll(),
        ];

        return view('Cupons/editar', $data);
    }

    /**
     * Atualizar cupom (AJAX)
     */
    public function atualizar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $post = $this->request->getPost();

        $cupom = $this->buscaCupomOu404($post['id']);

        // Converter código para maiúsculas
        $post['codigo'] = strtoupper($post['codigo'] ?? '');

        // Verificar se código já existe (exceto o próprio)
        $existente = $this->cupomModel->buscaPorCodigo($post['codigo']);
        if ($existente && $existente->id != $cupom->id) {
            $retorno['erro'] = 'Este código de cupom já existe.';
            return $this->response->setJSON($retorno);
        }

        $cupom->fill($post);

        if (!$this->cupomModel->protect(false)->save($cupom)) {
            $retorno['erro'] = 'Erro ao atualizar cupom.';
            $retorno['erros_model'] = $this->cupomModel->errors();
            return $this->response->setJSON($retorno);
        }

        session()->setFlashdata('sucesso', 'Cupom atualizado com sucesso!');
        $retorno['sucesso'] = 'Cupom atualizado com sucesso!';
        $retorno['redirect'] = site_url('cupons');

        return $this->response->setJSON($retorno);
    }

    /**
     * Excluir cupom
     */
    public function excluir($id = null)
    {
        if (!$this->usuarioLogado()->temPermissaoPara('excluir-cupons')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $cupom = $this->buscaCupomOu404($id);

        if ($this->cupomModel->delete($id)) {
            return redirect()->to(site_url('cupons'))->with('sucesso', 'Cupom excluído com sucesso!');
        }

        return redirect()->back()->with('erro', 'Erro ao excluir cupom.');
    }

    /**
     * Desfazer exclusão
     */
    public function desfazerexclusao($id = null)
    {
        $cupom = $this->buscaCupomOu404($id);

        if ($cupom->deleted_at === null) {
            return redirect()->back()->with('info', 'Cupom não está excluído.');
        }

        if ($this->cupomModel->protect(false)->update($id, ['deleted_at' => null])) {
            return redirect()->to(site_url('cupons'))->with('sucesso', 'Exclusão desfeita com sucesso!');
        }

        return redirect()->back()->with('erro', 'Erro ao desfazer exclusão.');
    }

    /**
     * Alternar status ativo/inativo (AJAX)
     */
    public function alterarStatus()
    {
        $retorno['token'] = csrf_hash();

        $id = $this->request->getPost('id');
        $cupom = $this->buscaCupomOu404($id);

        $cupom->ativo = !$cupom->ativo;

        if (!$this->cupomModel->protect(false)->save($cupom)) {
            $retorno['erro'] = 'Erro ao alterar status.';
            return $this->response->setJSON($retorno);
        }

        $status = $cupom->ativo ? 'ativado' : 'desativado';
        $retorno['sucesso'] = 'Cupom ' . $status . ' com sucesso!';
        $retorno['novo_status'] = $cupom->ativo;
        $retorno['badge'] = $cupom->getBadgeStatus();

        return $this->response->setJSON($retorno);
    }

    /**
     * Recupera cupons para DataTables (AJAX)
     */
    public function recuperaCupons()
    {

        // Pega evento da URL ou do contexto
        $eventId = $this->request->getGet('event_id') ?? $this->request->getGet('evento_id') ?? evento_selecionado();

        $builder = $this->cupomModel
            ->select('cupons.*, eventos.nome as evento_nome')
            ->join('eventos', 'eventos.id = cupons.evento_id', 'left')
            ->withDeleted(true);

        if (!empty($eventId)) {
            $builder->where('cupons.evento_id', $eventId);
        }

        $cupons = $builder->orderBy('cupons.created_at', 'DESC')->findAll();

        $data = [];

        foreach ($cupons as $cupom) {
            $data[] = [
                'codigo' => '<strong>' . esc($cupom->codigo) . '</strong>',
                'nome' => esc($cupom->nome),
                'desconto' => $cupom->getDescontoFormatado() . ' ' . $cupom->getBadgeTipo(),
                'valor_minimo' => $cupom->valor_minimo > 0 ? 'R$ ' . number_format($cupom->valor_minimo, 2, ',', '.') : '-',
                'quantidade' => $cupom->getQuantidadeFormatada(),
                'validade' => $cupom->getValidadeFormatada(),
                'situacao' => $cupom->exibeSituacao(),
                'acoes' => $this->montaBotoes($cupom),
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    /**
     * Validar cupom (API para checkout)
     */
    public function validar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $codigo = $this->request->getPost('codigo');
        $eventId = $this->request->getPost('evento_id');
        $userId = $this->request->getPost('user_id') ?? session()->get('usuario_id');
        $valorPedido = (float) $this->request->getPost('valor_pedido');

        $resultado = $this->cupomModel->validarCupom($codigo, $eventId, $userId, $valorPedido);

        if (!$resultado['valido']) {
            $retorno['erro'] = $resultado['erro'];
            return $this->response->setJSON($retorno);
        }

        $cupom = $resultado['cupom'];
        $valorDesconto = $this->cupomModel->calcularDesconto($cupom, $valorPedido);
        $valorFinal = max(0, $valorPedido - $valorDesconto);

        $retorno['sucesso'] = 'Cupom válido!';
        $retorno['cupom'] = [
            'id' => $cupom->id,
            'codigo' => $cupom->codigo,
            'nome' => $cupom->nome,
            'desconto_formatado' => $cupom->getDescontoFormatado(),
            'valor_desconto' => $valorDesconto,
            'valor_desconto_formatado' => 'R$ ' . number_format($valorDesconto, 2, ',', '.'),
            'valor_final' => $valorFinal,
            'valor_final_formatado' => 'R$ ' . number_format($valorFinal, 2, ',', '.'),
        ];

        return $this->response->setJSON($retorno);
    }

    /**
     * Monta botões de ação
     */
    private function montaBotoes($cupom): string
    {
        if ($cupom->deleted_at !== null) {
            return '';
        }

        $btns = '<div class="d-flex gap-1">';

        // Botão editar
        $btns .= '<a href="' . site_url("cupons/editar/{$cupom->id}") . '" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bx bx-edit"></i></a>';

        // Botão ativar/desativar
        if ($cupom->ativo) {
            $btns .= '<button type="button" class="btn btn-sm btn-outline-warning btn-status" data-id="' . $cupom->id . '" title="Desativar"><i class="bx bx-pause"></i></button>';
        } else {
            $btns .= '<button type="button" class="btn btn-sm btn-outline-success btn-status" data-id="' . $cupom->id . '" title="Ativar"><i class="bx bx-play"></i></button>';
        }

        // Botão excluir
        $btns .= '<a href="' . site_url("cupons/excluir/{$cupom->id}") . '" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="return confirm(\'Tem certeza que deseja excluir?\')"><i class="bx bx-trash"></i></a>';

        $btns .= '</div>';

        return $btns;
    }

    /**
     * Busca cupom ou retorna 404
     */
    private function buscaCupomOu404($id = null)
    {
        if (!$id || !$cupom = $this->cupomModel->withDeleted(true)->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Cupom não encontrado: ' . $id);
        }

        return $cupom;
    }
}
