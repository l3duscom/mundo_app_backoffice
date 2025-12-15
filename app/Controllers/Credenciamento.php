<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Credenciamento extends BaseController
{
    protected $credenciamentoModel;
    protected $veiculoModel;
    protected $pessoaModel;
    protected $contratoModel;

    public function __construct()
    {
        $this->credenciamentoModel = new \App\Models\CredenciamentoModel();
        $this->veiculoModel = new \App\Models\CredenciamentoVeiculoModel();
        $this->pessoaModel = new \App\Models\CredenciamentoPessoaModel();
        $this->contratoModel = new \App\Models\ContratoModel();
    }

    /**
     * Página principal do credenciamento
     */
    public function index($contratoId)
    {
        $contrato = $this->contratoModel->find($contratoId);
        
        if (!$contrato) {
            return redirect()->back()->with('error', 'Contrato não encontrado.');
        }

        // Verifica se usuário tem acesso ao contrato
        $usuario = $this->usuarioLogado();
        if ($usuario->is_parceiro) {
            $expositorModel = new \App\Models\ExpositorModel();
            $expositor = $expositorModel->where('usuario_id', $usuario->id)->first();
            
            if (!$expositor || $expositor->id !== $contrato->expositor_id) {
                return redirect()->back()->with('error', 'Você não tem acesso a este contrato.');
            }
        }

        // Verifica se credenciamento está liberado
        $liberado = $this->credenciamentoModel->estaLiberado($contratoId);
        $dentroDoPrazo = $this->credenciamentoModel->dentroDoPrazo($contratoId);
        
        // Obtém ou cria credenciamento
        $credenciamento = $this->credenciamentoModel->getOuCria($contratoId);
        
        // Busca dados
        $veiculos = $this->veiculoModel->buscaPorCredenciamento($credenciamento->id);
        $responsavel = $this->pessoaModel->buscaResponsavel($credenciamento->id);
        $funcionarios = $this->pessoaModel->buscaFuncionarios($credenciamento->id);
        $suplentes = $this->pessoaModel->buscaSuplentes($credenciamento->id);
        
        // Obtém limites
        $limites = $this->credenciamentoModel->getLimitePessoas($contratoId);

        // Busca evento para mostrar prazo
        $eventoModel = new \App\Models\EventoModel();
        $evento = $eventoModel->find($contrato->event_id);

        $data = [
            'titulo' => 'Credenciamento - ' . $contrato->codigo,
            'contrato' => $contrato,
            'credenciamento' => $credenciamento,
            'veiculos' => $veiculos,
            'responsavel' => $responsavel,
            'funcionarios' => $funcionarios,
            'suplentes' => $suplentes,
            'limites' => $limites,
            'liberado' => $liberado,
            'dentroDoPrazo' => $dentroDoPrazo,
            'evento' => $evento,
            'podeEditar' => $liberado && $dentroDoPrazo,
        ];

        return view('Credenciamento/index', $data);
    }

    /**
     * Salvar veículo
     */
    public function salvarVeiculo()
    {
        $credenciamentoId = $this->request->getPost('credenciamento_id');
        $contratoId = $this->request->getPost('contrato_id');
        $veiculoId = $this->request->getPost('id');

        $credenciamento = $this->credenciamentoModel->find($credenciamentoId);
        if (!$credenciamento) {
            return $this->response->setJSON(['success' => false, 'message' => 'Credenciamento não encontrado.']);
        }

        // Verifica permissões
        if (!$this->credenciamentoModel->dentroDoPrazo($contratoId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Prazo de edição expirado.']);
        }

        // Verifica limite se for novo veículo
        if (!$veiculoId && !$this->veiculoModel->podeAdicionar($credenciamentoId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Limite de 1 veículo atingido.']);
        }

        $dados = [
            'credenciamento_id' => $credenciamentoId,
            'marca' => $this->request->getPost('marca'),
            'modelo' => $this->request->getPost('modelo'),
            'cor' => $this->request->getPost('cor'),
            'placa' => strtoupper(str_replace(['-', ' '], '', $this->request->getPost('placa'))),
        ];

        if ($veiculoId) {
            $dados['id'] = $veiculoId;
        }

        if ($this->veiculoModel->save($dados)) {
            $this->credenciamentoModel->atualizaStatus($credenciamentoId);
            return $this->response->setJSON(['success' => true, 'message' => 'Veículo salvo com sucesso!']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Erro ao salvar veículo.', 'errors' => $this->veiculoModel->errors()]);
    }

    /**
     * Excluir veículo
     */
    public function excluirVeiculo($id)
    {
        $veiculo = $this->veiculoModel->find($id);
        if (!$veiculo) {
            return $this->response->setJSON(['success' => false, 'message' => 'Veículo não encontrado.']);
        }

        $credenciamento = $this->credenciamentoModel->find($veiculo->credenciamento_id);
        if (!$this->credenciamentoModel->dentroDoPrazo($credenciamento->contrato_id)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Prazo de edição expirado.']);
        }

        if ($this->veiculoModel->delete($id)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Veículo excluído com sucesso!']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Erro ao excluir veículo.']);
    }

    /**
     * Salvar pessoa (responsável, funcionário ou suplente)
     */
    public function salvarPessoa()
    {
        $credenciamentoId = $this->request->getPost('credenciamento_id');
        $contratoId = $this->request->getPost('contrato_id');
        $pessoaId = $this->request->getPost('id');
        $tipo = $this->request->getPost('tipo');

        $credenciamento = $this->credenciamentoModel->find($credenciamentoId);
        if (!$credenciamento) {
            return $this->response->setJSON(['success' => false, 'message' => 'Credenciamento não encontrado.']);
        }

        // Verifica permissões
        if (!$this->credenciamentoModel->dentroDoPrazo($contratoId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Prazo de edição expirado.']);
        }

        // Obtém limites
        $limites = $this->credenciamentoModel->getLimitePessoas($contratoId);
        $limite = $tipo === 'funcionario' ? $limites['funcionarios'] : $limites['suplentes'];

        // Verifica limite se for nova pessoa
        if (!$pessoaId && !$this->pessoaModel->podeAdicionar($credenciamentoId, $tipo, $limite)) {
            $msg = $tipo === 'responsavel' ? 'Já existe um responsável cadastrado.' : "Limite de {$limite} {$tipo}s atingido.";
            return $this->response->setJSON(['success' => false, 'message' => $msg]);
        }

        $dados = [
            'credenciamento_id' => $credenciamentoId,
            'tipo' => $tipo,
            'nome' => $this->request->getPost('nome'),
            'rg' => $this->request->getPost('rg'),
            'cpf' => preg_replace('/\D/', '', $this->request->getPost('cpf')),
            'whatsapp' => preg_replace('/\D/', '', $this->request->getPost('whatsapp')),
        ];

        if ($pessoaId) {
            $dados['id'] = $pessoaId;
        }

        if ($this->pessoaModel->save($dados)) {
            $this->credenciamentoModel->atualizaStatus($credenciamentoId);
            return $this->response->setJSON(['success' => true, 'message' => 'Dados salvos com sucesso!']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Erro ao salvar dados.', 'errors' => $this->pessoaModel->errors()]);
    }

    /**
     * Excluir pessoa
     */
    public function excluirPessoa($id)
    {
        $pessoa = $this->pessoaModel->find($id);
        if (!$pessoa) {
            return $this->response->setJSON(['success' => false, 'message' => 'Pessoa não encontrada.']);
        }

        $credenciamento = $this->credenciamentoModel->find($pessoa->credenciamento_id);
        if (!$this->credenciamentoModel->dentroDoPrazo($credenciamento->contrato_id)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Prazo de edição expirado.']);
        }

        if ($this->pessoaModel->delete($id)) {
            $this->credenciamentoModel->atualizaStatus($pessoa->credenciamento_id);
            return $this->response->setJSON(['success' => true, 'message' => 'Pessoa excluída com sucesso!']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Erro ao excluir pessoa.']);
    }
}
