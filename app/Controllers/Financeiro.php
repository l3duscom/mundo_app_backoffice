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
            
            // Monta link para o registro original
            $descricaoHtml = $this->montaLinkDescricao($lancamento);
            
            $data[] = [
                'id' => $lancamento->id,
                'data' => $lancamento->getDataLancamentoFormatada(),
                'descricao' => $descricaoHtml,
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
     * Monta link para o registro original
     */
    private function montaLinkDescricao($lancamento): string
    {
        $descricao = esc($lancamento->descricao);
        
        if (empty($lancamento->referencia_tipo) || empty($lancamento->referencia_id)) {
            return $descricao;
        }

        $url = null;
        $icon = '';
        
        switch ($lancamento->referencia_tipo) {
            case 'pedidos':
                // Link para o pedido
                $url = site_url("pedidos/ingressos/{$lancamento->referencia_id}");
                $icon = '<i class="bx bx-receipt me-1"></i>';
                break;
            case 'contrato_parcelas':
                // Link para o contrato (buscar contrato_id da parcela)
                $parcelaModel = new \App\Models\ContratoParcelaModel();
                $parcela = $parcelaModel->find($lancamento->referencia_id);
                if ($parcela) {
                    $url = site_url("contratos/exibir/{$parcela->contrato_id}");
                    $icon = '<i class="bx bx-file me-1"></i>';
                }
                break;
            case 'contas_pagar':
                $url = site_url("contas-pagar/editar/{$lancamento->referencia_id}");
                $icon = '<i class="bx bx-money me-1"></i>';
                break;
            case 'orcamentos':
                $url = site_url("orcamentos/exibir/{$lancamento->referencia_id}");
                $icon = '<i class="bx bx-calculator me-1"></i>';
                break;
            case 'artista_contratacoes':
                $url = site_url("artista-contratacoes/exibir/{$lancamento->referencia_id}");
                $icon = '<i class="bx bx-microphone me-1"></i>';
                break;
        }

        if ($url) {
            return '<a href="' . $url . '" class="text-decoration-none" title="Ver detalhes">' . $icon . $descricao . ' <i class="bx bx-link-external" style="font-size:0.8rem;"></i></a>';
        }

        return $descricao;
    }

    /**
     * Monta botões de ação
     */
    private function montaBotoes($lancamento): string
    {
        $btns = '';
        
        // Botão de visualizar registro original
        if (!empty($lancamento->referencia_tipo) && !empty($lancamento->referencia_id)) {
            $url = $this->getUrlReferencia($lancamento);
            if ($url) {
                $btns .= '<a href="' . $url . '" class="btn btn-sm btn-outline-info" title="Ver original"><i class="bx bx-link-external"></i></a> ';
            }
        }
        
        // Só permite editar/excluir lançamentos manuais
        if ($lancamento->origem === 'MANUAL') {
            $btns .= '<a href="' . site_url("financeiro/editar/{$lancamento->id}") . '" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bx bx-edit"></i></a> ';
            $btns .= '<button type="button" class="btn btn-sm btn-outline-danger" onclick="excluirLancamento(' . $lancamento->id . ')" title="Excluir"><i class="bx bx-trash"></i></button>';
        }

        return $btns ?: '<span class="text-muted">-</span>';
    }

    /**
     * Retorna URL do registro de referência
     */
    private function getUrlReferencia($lancamento): ?string
    {
        if (empty($lancamento->referencia_tipo) || empty($lancamento->referencia_id)) {
            return null;
        }

        switch ($lancamento->referencia_tipo) {
            case 'pedidos':
                return site_url("pedidos/ingressos/{$lancamento->referencia_id}");
            case 'contrato_parcelas':
                $parcelaModel = new \App\Models\ContratoParcelaModel();
                $parcela = $parcelaModel->find($lancamento->referencia_id);
                if ($parcela) {
                    return site_url("contratos/exibir/{$parcela->contrato_id}");
                }
                break;
            case 'contas_pagar':
                return site_url("contas-pagar/editar/{$lancamento->referencia_id}");
            case 'orcamentos':
                return site_url("orcamentos/exibir/{$lancamento->referencia_id}");
            case 'artista_contratacoes':
                return site_url("artista-contratacoes/exibir/{$lancamento->referencia_id}");
        }

        return null;
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

    /**
     * Calendário Financeiro Interativo
     */
    public function calendario()
    {
        if (!$this->usuarioLogado()->is_admin) {
            return redirect()->back()->with('atencao', 'Você não tem permissão para acessar esse menu.');
        }

        // Lista de eventos para o filtro
        $eventos = $this->eventoModel->orderBy('id', 'DESC')->findAll();

        $data = [
            'titulo' => 'Calendário Financeiro',
            'eventos' => $eventos,
        ];

        return view('Financeiro/calendario', $data);
    }

    /**
     * Recupera eventos para o calendário via AJAX (formato FullCalendar)
     */
    public function recuperaEventosCalendario()
    {

        $eventoId = $this->request->getGet('event_id');
        $start = $this->request->getGet('start'); // FullCalendar envia start e end
        $end = $this->request->getGet('end');

        // Se event_id for 'todos', deixa null
        if ($eventoId === 'todos' || $eventoId === '') {
            $eventoId = null;
        }

        // Busca lançamentos no período
        $lancamentos = $this->lancamentoModel->recuperaLancamentos(
            $eventoId ? (int) $eventoId : null,
            null, // tipo
            null, // status
            $start ? substr($start, 0, 10) : null,
            $end ? substr($end, 0, 10) : null
        );

        $events = [];
        foreach ($lancamentos as $lancamento) {
            $valor = (float) ($lancamento->valor ?? 0);
            $isEntrada = $lancamento->tipo === 'ENTRADA';
            
            // Cores por tipo
            $color = $isEntrada ? '#28a745' : '#dc3545';
            $textColor = '#fff';
            
            // Status pendente fica mais claro
            if ($lancamento->status === 'pendente') {
                $color = $isEntrada ? '#90EE90' : '#FFB6C1';
                $textColor = '#333';
            }

            $prefixo = $isEntrada ? '+' : '-';
            $valorFormatado = $prefixo . ' R$ ' . number_format($valor, 2, ',', '.');

            // Converter data para string (FullCalendar espera formato Y-m-d)
            $dataLancamento = $lancamento->data_lancamento;
            if ($dataLancamento instanceof \DateTime) {
                $dataLancamento = $dataLancamento->format('Y-m-d');
            } elseif (is_object($dataLancamento) && method_exists($dataLancamento, 'toDateString')) {
                $dataLancamento = $dataLancamento->toDateString();
            }

            $events[] = [
                'id' => $lancamento->id,
                'title' => $valorFormatado,
                'start' => $dataLancamento,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => $textColor,
                'extendedProps' => [
                    'tipo' => $lancamento->tipo,
                    'descricao' => $lancamento->descricao,
                    'status' => $lancamento->status,
                    'origem' => $lancamento->origem,
                    'valor' => $valor,
                    'valor_formatado' => 'R$ ' . number_format($valor, 2, ',', '.'),
                    'evento_nome' => $lancamento->evento_nome ?? '-',
                ]
            ];
        }

        return $this->response->setJSON($events);
    }

    /**
     * Recupera lançamentos de um dia específico (AJAX)
     */
    public function recuperaLancamentosDia()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $data = $this->request->getGet('data');
        $eventoId = $this->request->getGet('event_id');

        if (!$data) {
            return $this->response->setJSON(['erro' => 'Data não informada']);
        }

        // Se event_id for 'todos', deixa null
        if ($eventoId === 'todos' || $eventoId === '') {
            $eventoId = null;
        }

        $lancamentos = $this->lancamentoModel->recuperaLancamentos(
            $eventoId ? (int) $eventoId : null,
            null,
            null,
            $data,
            $data
        );

        $totalEntradas = 0;
        $totalSaidas = 0;
        $lista = [];

        foreach ($lancamentos as $lancamento) {
            $valor = (float) ($lancamento->valor ?? 0);
            
            if ($lancamento->tipo === 'ENTRADA') {
                $totalEntradas += $valor;
            } else {
                $totalSaidas += $valor;
            }

            $lista[] = [
                'id' => $lancamento->id,
                'descricao' => $lancamento->descricao,
                'tipo' => $lancamento->tipo,
                'origem' => $lancamento->origem,
                'status' => $lancamento->status,
                'valor' => 'R$ ' . number_format($valor, 2, ',', '.'),
                'evento_nome' => $lancamento->evento_nome ?? '-',
            ];
        }

        return $this->response->setJSON([
            'sucesso' => true,
            'data' => $data,
            'lancamentos' => $lista,
            'resumo' => [
                'total_entradas' => 'R$ ' . number_format($totalEntradas, 2, ',', '.'),
                'total_saidas' => 'R$ ' . number_format($totalSaidas, 2, ',', '.'),
                'saldo' => 'R$ ' . number_format($totalEntradas - $totalSaidas, 2, ',', '.'),
            ]
        ]);
    }
}

