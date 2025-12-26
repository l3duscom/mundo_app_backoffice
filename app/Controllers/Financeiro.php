<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\LancamentoFinanceiroModel;
use App\Models\EventoModel;

class Financeiro extends BaseController
{
    private $lancamentoModel;
    private $eventoModel;

    public function __construct()
    {
        $this->lancamentoModel = new LancamentoFinanceiroModel();
        $this->eventoModel = new EventoModel();
    }

    /**
     * Dashboard Financeiro
     */
    public function index()
    {
        if (!$this->usuarioLogado()->is_admin) {
            return redirect()->back()->with('atencao', 'Você não tem permissão para acessar esse menu.');
        }

        // Pega evento do contexto ou parâmetro
        $eventoId = $this->request->getGet('event_id') ?? evento_selecionado();
        
        // Lista de eventos para o filtro
        $eventos = $this->eventoModel->orderBy('id', 'DESC')->findAll();

        $data = [
            'titulo' => 'Financeiro',
            'eventos' => $eventos,
            'eventoSelecionado' => $eventoId,
        ];

        return view('Financeiro/index', $data);
    }

    /**
     * Recupera dados do resumo via AJAX
     */
    public function recuperaResumo()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $eventoId = $this->request->getGet('event_id');
        $dataInicio = $this->request->getGet('data_inicio');
        $dataFim = $this->request->getGet('data_fim');

        // Se event_id for 'todos', deixa null para não filtrar
        if ($eventoId === 'todos' || $eventoId === '') {
            $eventoId = null;
        }

        $resumo = $this->lancamentoModel->calculaResumo(
            $eventoId ? (int) $eventoId : null,
            $dataInicio ?: null,
            $dataFim ?: null
        );

        return $this->response->setJSON($resumo);
    }

    /**
     * Recupera lançamentos via AJAX para DataTables
     */
    public function recuperaLancamentos()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $eventoId = $this->request->getGet('event_id');
        $tipo = $this->request->getGet('tipo');
        $status = $this->request->getGet('status');
        $dataInicio = $this->request->getGet('data_inicio');
        $dataFim = $this->request->getGet('data_fim');

        // Se event_id for 'todos', deixa null
        if ($eventoId === 'todos' || $eventoId === '') {
            $eventoId = null;
        }

        $lancamentos = $this->lancamentoModel->recuperaLancamentos(
            $eventoId ? (int) $eventoId : null,
            $tipo ?: null,
            $status ?: null,
            $dataInicio ?: null,
            $dataFim ?: null
        );

        $data = [];
        foreach ($lancamentos as $lancamento) {
            $valorBruto = $lancamento->valor ?? 0;
            $valorLiquido = $lancamento->valor_liquido ?? $valorBruto;
            $cor = $lancamento->tipo === 'SAIDA' ? 'text-danger' : 'text-success';
            $prefixo = $lancamento->tipo === 'SAIDA' ? '-' : '+';
            
            $data[] = [
                'id' => $lancamento->id,
                'data' => $lancamento->getDataLancamentoFormatada(),
                'descricao' => esc($lancamento->descricao),
                'tipo' => $lancamento->getBadgeTipo(),
                'origem' => $lancamento->getBadgeOrigem(),
                'evento' => $lancamento->evento_nome ? esc($lancamento->evento_nome) : '<span class="text-muted">-</span>',
                'valor_bruto' => '<span class="' . $cor . ' fw-bold">' . $prefixo . ' R$ ' . number_format($valorBruto, 2, ',', '.') . '</span>',
                'valor_liquido' => '<span class="' . $cor . '">' . $prefixo . ' R$ ' . number_format($valorLiquido, 2, ',', '.') . '</span>',
                'status' => $lancamento->getBadgeStatus(),
                'acoes' => $this->montaBotoes($lancamento),
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    /**
     * Monta botões de ação
     */
    private function montaBotoes($lancamento): string
    {
        $btns = '';
        
        // Só permite editar/excluir lançamentos manuais
        if ($lancamento->origem === 'MANUAL') {
            $btns .= '<a href="' . site_url("financeiro/editar/{$lancamento->id}") . '" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bx bx-edit"></i></a> ';
            $btns .= '<button type="button" class="btn btn-sm btn-outline-danger" onclick="excluirLancamento(' . $lancamento->id . ')" title="Excluir"><i class="bx bx-trash"></i></button>';
        } else {
            $btns .= '<span class="text-muted">Automático</span>';
        }

        return $btns;
    }

    /**
     * Formulário de novo lançamento
     */
    public function criar()
    {
        if (!$this->usuarioLogado()->is_admin) {
            return redirect()->back()->with('atencao', 'Você não tem permissão para acessar esse menu.');
        }

        $eventos = $this->eventoModel->orderBy('id', 'DESC')->findAll();

        $data = [
            'titulo' => 'Novo Lançamento',
            'eventos' => $eventos,
            'lancamento' => new \App\Entities\LancamentoFinanceiro(),
        ];

        return view('Financeiro/criar', $data);
    }

    /**
     * Salva novo lançamento
     */
    public function cadastrar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $dados = [
            'event_id' => $this->request->getPost('event_id') ?: null,
            'tipo' => $this->request->getPost('tipo'),
            'origem' => 'MANUAL',
            'descricao' => $this->request->getPost('descricao'),
            'valor' => str_replace(['.', ','], ['', '.'], $this->request->getPost('valor')),
            'data_lancamento' => $this->request->getPost('data_lancamento'),
            'data_pagamento' => $this->request->getPost('data_pagamento') ?: null,
            'status' => $this->request->getPost('status'),
            'forma_pagamento' => $this->request->getPost('forma_pagamento') ?: null,
            'categoria' => $this->request->getPost('categoria') ?: null,
            'observacoes' => $this->request->getPost('observacoes') ?: null,
        ];

        if (!$this->lancamentoModel->insert($dados)) {
            $retorno['erro'] = 'Erro ao salvar: ' . implode(', ', $this->lancamentoModel->errors());
            return $this->response->setJSON($retorno);
        }

        $retorno['sucesso'] = 'Lançamento cadastrado com sucesso!';
        $retorno['redirect'] = site_url('financeiro');
        return $this->response->setJSON($retorno);
    }

    /**
     * Formulário de edição
     */
    public function editar(int $id)
    {
        if (!$this->usuarioLogado()->is_admin) {
            return redirect()->back()->with('atencao', 'Você não tem permissão para acessar esse menu.');
        }

        $lancamento = $this->lancamentoModel->find($id);

        if (!$lancamento || $lancamento->origem !== 'MANUAL') {
            return redirect()->to('financeiro')->with('atencao', 'Lançamento não encontrado ou não pode ser editado.');
        }

        $eventos = $this->eventoModel->orderBy('id', 'DESC')->findAll();

        $data = [
            'titulo' => 'Editar Lançamento',
            'eventos' => $eventos,
            'lancamento' => $lancamento,
        ];

        return view('Financeiro/editar', $data);
    }

    /**
     * Atualiza lançamento
     */
    public function atualizar(int $id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $lancamento = $this->lancamentoModel->find($id);

        if (!$lancamento || $lancamento->origem !== 'MANUAL') {
            $retorno['erro'] = 'Lançamento não encontrado ou não pode ser editado.';
            return $this->response->setJSON($retorno);
        }

        $dados = [
            'event_id' => $this->request->getPost('event_id') ?: null,
            'tipo' => $this->request->getPost('tipo'),
            'descricao' => $this->request->getPost('descricao'),
            'valor' => str_replace(['.', ','], ['', '.'], $this->request->getPost('valor')),
            'data_lancamento' => $this->request->getPost('data_lancamento'),
            'data_pagamento' => $this->request->getPost('data_pagamento') ?: null,
            'status' => $this->request->getPost('status'),
            'forma_pagamento' => $this->request->getPost('forma_pagamento') ?: null,
            'categoria' => $this->request->getPost('categoria') ?: null,
            'observacoes' => $this->request->getPost('observacoes') ?: null,
        ];

        if (!$this->lancamentoModel->update($id, $dados)) {
            $retorno['erro'] = 'Erro ao atualizar: ' . implode(', ', $this->lancamentoModel->errors());
            return $this->response->setJSON($retorno);
        }

        $retorno['sucesso'] = 'Lançamento atualizado com sucesso!';
        $retorno['redirect'] = site_url('financeiro');
        return $this->response->setJSON($retorno);
    }

    /**
     * Exclui lançamento
     */
    public function excluir(int $id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $lancamento = $this->lancamentoModel->find($id);

        if (!$lancamento || $lancamento->origem !== 'MANUAL') {
            $retorno['erro'] = 'Lançamento não encontrado ou não pode ser excluído.';
            return $this->response->setJSON($retorno);
        }

        $this->lancamentoModel->delete($id);

        $retorno['sucesso'] = 'Lançamento excluído com sucesso!';
        return $this->response->setJSON($retorno);
    }

    /**
     * Sincroniza dados de outras tabelas
     */
    public function sincronizar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        try {
            $resultado = $this->lancamentoModel->sincronizarTodos();

            $total = array_sum($resultado);

            if ($total > 0) {
                $retorno['sucesso'] = "Sincronização concluída! {$total} lançamentos importados.";
            } else {
                $retorno['info'] = 'Nenhum novo lançamento para sincronizar.';
            }

            $retorno['detalhes'] = $resultado;
        } catch (\Exception $e) {
            log_message('error', 'Erro ao sincronizar: ' . $e->getMessage());
            $retorno['erro'] = 'Erro ao sincronizar: ' . $e->getMessage();
        }

        return $this->response->setJSON($retorno);
    }
}
