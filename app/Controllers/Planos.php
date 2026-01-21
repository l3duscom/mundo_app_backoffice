<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\Plano;
use App\Models\PlanoModel;

class Planos extends BaseController
{
    protected $planoModel;

    public function __construct()
    {
        $this->planoModel = new PlanoModel();
    }

    /**
     * Listagem de planos
     */
    public function index()
    {
        $dados = [
            'titulo' => 'Planos de Assinatura',
            'planos' => $this->planoModel->buscaComContagem(),
        ];

        return view('Planos/index', $dados);
    }

    /**
     * Formulário de criação
     */
    public function criar()
    {
        $dados = [
            'titulo' => 'Novo Plano',
            'plano' => new Plano(),
        ];

        return view('Planos/criar', $dados);
    }

    /**
     * Processa cadastro
     */
    public function cadastrar()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('planos');
        }

        $plano = new Plano($this->request->getPost());

        // Converter preço para float
        $preco = $this->request->getPost('preco');
        $plano->preco = $this->converteValorMonetario($preco);

        // Processar benefícios
        $beneficios = $this->request->getPost('beneficios');
        if ($beneficios) {
            $beneficiosArray = array_filter(array_map('trim', explode("\n", $beneficios)));
            $plano->setBeneficios($beneficiosArray);
        }

        // Gerar slug se não informado
        if (empty($plano->slug)) {
            $plano->slug = url_title($plano->nome, '-', true);
        }

        if (!$this->planoModel->insert($plano)) {
            return redirect()->back()
                ->with('errors_model', $this->planoModel->errors())
                ->withInput();
        }

        return redirect()->to('planos')
            ->with('sucesso', 'Plano criado com sucesso!');
    }

    /**
     * Formulário de edição
     */
    public function editar(int $id = null)
    {
        $plano = $this->buscaOu404($id);

        $dados = [
            'titulo' => 'Editar Plano',
            'plano' => $plano,
        ];

        return view('Planos/editar', $dados);
    }

    /**
     * Processa atualização
     */
    public function atualizar()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('planos');
        }

        $id = $this->request->getPost('id');
        $plano = $this->buscaOu404($id);

        $plano->fill($this->request->getPost());

        // Converter preço para float
        $preco = $this->request->getPost('preco');
        $plano->preco = $this->converteValorMonetario($preco);

        // Processar benefícios
        $beneficios = $this->request->getPost('beneficios');
        if ($beneficios) {
            $beneficiosArray = array_filter(array_map('trim', explode("\n", $beneficios)));
            $plano->setBeneficios($beneficiosArray);
        } else {
            $plano->setBeneficios([]);
        }

        if (!$this->planoModel->save($plano)) {
            return redirect()->back()
                ->with('errors_model', $this->planoModel->errors())
                ->withInput();
        }

        return redirect()->to('planos')
            ->with('sucesso', 'Plano atualizado com sucesso!');
    }

    /**
     * Excluir plano (soft delete)
     */
    public function excluir(int $id = null)
    {
        $plano = $this->buscaOu404($id);

        // Verificar se tem assinaturas ativas
        $assinaturasAtivas = $this->planoModel->contaAssinaturasAtivas($id);
        
        if ($assinaturasAtivas > 0) {
            return $this->response->setJSON([
                'erro' => "Não é possível excluir. Existem {$assinaturasAtivas} assinaturas ativas neste plano.",
                'token' => csrf_hash(),
            ]);
        }

        $this->planoModel->delete($id);

        return $this->response->setJSON([
            'sucesso' => true,
            'token' => csrf_hash(),
        ]);
    }

    /**
     * Alterar status (ativo/inativo) via AJAX
     */
    public function alterarStatus()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('planos');
        }

        $id = $this->request->getPost('id');
        $plano = $this->buscaOu404($id);

        $novoStatus = !$plano->ativo;
        
        $this->planoModel->protect(false)->update($id, ['ativo' => $novoStatus]);

        $plano->ativo = $novoStatus;

        return $this->response->setJSON([
            'sucesso' => true,
            'badge' => $plano->exibeStatus(),
            'token' => csrf_hash(),
        ]);
    }

    /**
     * Busca plano ou retorna 404
     */
    private function buscaOu404(int $id = null)
    {
        if (!$id || !$plano = $this->planoModel->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Plano não encontrado');
        }

        return $plano;
    }

    /**
     * Converte valor monetário brasileiro para float
     */
    private function converteValorMonetario(?string $valor): float
    {
        if (empty($valor)) {
            return 0.00;
        }

        // Remove R$ e espaços
        $valor = preg_replace('/[R$\s]/', '', $valor);
        
        // Converte formato brasileiro (1.234,56) para float (1234.56)
        $valor = str_replace('.', '', $valor); // Remove separador de milhar
        $valor = str_replace(',', '.', $valor); // Converte vírgula decimal

        return (float) $valor;
    }
}
