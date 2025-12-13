<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Dompdf\Dompdf;

class RelatorioContratos extends BaseController
{
    protected $contratoModel;
    protected $eventoModel;
    protected $expositorModel;
    protected $db;

    public function __construct()
    {
        $this->contratoModel = new \App\Models\ContratoModel();
        $this->eventoModel = new \App\Models\EventoModel();
        $this->expositorModel = new \App\Models\ExpositorModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * Página principal com menu de relatórios
     */
    public function index()
    {
        if (!$this->usuarioLogado()->temPermissaoPara('listar-contratos')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $event_id = session()->get('event_id');
        $evento = null;
        
        if ($event_id) {
            $evento = $this->eventoModel->find($event_id);
        }

        $eventos = $this->eventoModel->orderBy('id', 'DESC')->findAll();

        $data = [
            'titulo' => 'Relatórios de Contratos',
            'evento' => $evento,
            'eventos' => $eventos,
            'event_id' => $event_id,
        ];

        return view('Relatorios/Contratos/index', $data);
    }

    /**
     * Relatório de contratos por situação
     */
    public function contratosPorSituacao()
    {
        if (!$this->usuarioLogado()->temPermissaoPara('listar-contratos')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $event_id = $this->request->getGet('evento_id') ?? session()->get('event_id');
        $situacao = $this->request->getGet('situacao') ?? '';

        if (!$event_id) {
            return redirect()->to('relatorios/contratos')->with('atencao', 'Selecione um evento.');
        }

        $evento = $this->eventoModel->find($event_id);
        $eventos = $this->eventoModel->orderBy('id', 'DESC')->findAll();

        $contratosPorSituacao = $this->getContratosPorSituacao($event_id, $situacao);
        $totais = $this->getTotaisContratos($event_id, $situacao);

        $data = [
            'titulo' => 'Relatório de Contratos por Situação',
            'evento' => $evento,
            'eventos' => $eventos,
            'event_id' => $event_id,
            'situacao' => $situacao,
            'contratos_por_situacao' => $contratosPorSituacao,
            'totais' => $totais,
            'situacoes' => $this->getSituacoes(),
        ];

        return view('Relatorios/Contratos/contratos_situacao', $data);
    }

    /**
     * Relatório financeiro de contratos
     */
    public function financeiroContratos()
    {
        if (!$this->usuarioLogado()->temPermissaoPara('listar-contratos')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $event_id = $this->request->getGet('evento_id') ?? session()->get('event_id');

        if (!$event_id) {
            return redirect()->to('relatorios/contratos')->with('atencao', 'Selecione um evento.');
        }

        $evento = $this->eventoModel->find($event_id);
        $eventos = $this->eventoModel->orderBy('id', 'DESC')->findAll();

        $resumoFinanceiro = $this->getResumoFinanceiro($event_id);
        $recebimentosMensais = $this->getRecebimentosMensais($event_id);

        $data = [
            'titulo' => 'Relatório Financeiro de Contratos',
            'evento' => $evento,
            'eventos' => $eventos,
            'event_id' => $event_id,
            'resumo' => $resumoFinanceiro,
            'recebimentos_mensais' => $recebimentosMensais,
        ];

        return view('Relatorios/Contratos/financeiro', $data);
    }

    /**
     * Relatório de contratos por expositor
     */
    public function contratosPorExpositor()
    {
        if (!$this->usuarioLogado()->temPermissaoPara('listar-contratos')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $event_id = $this->request->getGet('evento_id') ?? session()->get('event_id');

        if (!$event_id) {
            return redirect()->to('relatorios/contratos')->with('atencao', 'Selecione um evento.');
        }

        $evento = $this->eventoModel->find($event_id);
        $eventos = $this->eventoModel->orderBy('id', 'DESC')->findAll();

        $contratosPorExpositor = $this->getContratosPorExpositor($event_id);
        $totais = $this->getTotaisContratos($event_id);

        $data = [
            'titulo' => 'Relatório de Contratos por Expositor',
            'evento' => $evento,
            'eventos' => $eventos,
            'event_id' => $event_id,
            'contratos_por_expositor' => $contratosPorExpositor,
            'totais' => $totais,
        ];

        return view('Relatorios/Contratos/contratos_expositor', $data);
    }

    /**
     * Exportar relatório para Excel (CSV)
     */
    public function exportarExcel(string $tipo)
    {
        $event_id = $this->request->getGet('evento_id') ?? session()->get('event_id');
        $situacao = $this->request->getGet('situacao') ?? '';

        if (!$event_id) {
            return redirect()->back()->with('erro', 'Evento não selecionado.');
        }

        $evento = $this->eventoModel->find($event_id);
        $nomeEvento = preg_replace('/[^a-zA-Z0-9]/', '_', $evento->nome ?? 'evento');

        switch ($tipo) {
            case 'situacao':
                $dados = $this->getContratosPorSituacao($event_id, $situacao);
                $filename = "contratos_situacao_{$nomeEvento}.csv";
                $cabecalho = ['Situação', 'Quantidade', 'Valor Total', 'Valor Pago', 'Valor em Aberto'];
                break;
            case 'expositor':
                $dados = $this->getContratosPorExpositor($event_id);
                $filename = "contratos_expositor_{$nomeEvento}.csv";
                $cabecalho = ['Expositor', 'Quantidade', 'Valor Total', 'Valor Pago', 'Valor em Aberto'];
                break;
            case 'financeiro':
                $dados = $this->getRecebimentosMensais($event_id);
                $filename = "contratos_financeiro_{$nomeEvento}.csv";
                $cabecalho = ['Mês', 'Valor Pago', 'Quantidade Pagamentos'];
                break;
            default:
                return redirect()->back()->with('erro', 'Tipo de relatório inválido.');
        }

        $output = fopen('php://temp', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, $cabecalho, ';');

        foreach ($dados as $linha) {
            $row = [];
            foreach ($linha as $key => $valor) {
                if (!str_contains($key, 'formatado')) {
                    $row[] = $valor;
                }
            }
            fputcsv($output, $row, ';');
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csv);
    }

    /**
     * Exportar relatório para PDF
     */
    public function exportarPdf(string $tipo)
    {
        $event_id = $this->request->getGet('evento_id') ?? session()->get('event_id');
        $situacao = $this->request->getGet('situacao') ?? '';

        if (!$event_id) {
            return redirect()->back()->with('erro', 'Evento não selecionado.');
        }

        $evento = $this->eventoModel->find($event_id);
        $totais = $this->getTotaisContratos($event_id);

        switch ($tipo) {
            case 'situacao':
                $dados = $this->getContratosPorSituacao($event_id, $situacao);
                $titulo = 'Relatório de Contratos por Situação';
                $colunas = ['Situação', 'Quantidade', 'Valor Total', 'Valor Pago', 'Em Aberto'];
                break;
            case 'expositor':
                $dados = $this->getContratosPorExpositor($event_id);
                $titulo = 'Relatório de Contratos por Expositor';
                $colunas = ['Expositor', 'Quantidade', 'Valor Total', 'Valor Pago', 'Em Aberto'];
                break;
            default:
                return redirect()->back()->with('erro', 'Tipo de relatório inválido.');
        }

        $html = view('Relatorios/Contratos/pdf_template', [
            'titulo' => $titulo,
            'evento' => $evento,
            'dados' => $dados,
            'colunas' => $colunas,
            'totais' => $totais,
        ]);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nomeEvento = preg_replace('/[^a-zA-Z0-9]/', '_', $evento->nome ?? 'evento');
        $filename = "relatorio_contratos_{$tipo}_{$nomeEvento}.pdf";

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    // ========================================
    // MÉTODOS PRIVADOS
    // ========================================

    private function getContratosPorSituacao(int $event_id, string $situacao = ''): array
    {
        $builder = $this->db->table('contratos c')
            ->select("
                c.situacao, 
                COUNT(DISTINCT c.id) as quantidade, 
                SUM(c.valor_final) as valor_total, 
                SUM(c.valor_pago) as valor_pago, 
                SUM(c.valor_em_aberto) as valor_em_aberto
            ")
            ->where('c.event_id', $event_id)
            ->where('c.deleted_at IS NULL')
            ->groupBy("c.situacao")
            ->orderBy("valor_total", "DESC");

        if ($situacao) {
            $builder->where('c.situacao', $situacao);
        }

        $result = $builder->get()->getResultArray();

        // Buscar valores líquidos por situação
        foreach ($result as &$row) {
            $liquidoResult = $this->db->table('contrato_parcelas cp')
                ->select("
                    SUM(CASE WHEN cp.status_local = 'pago' THEN cp.valor_liquido ELSE 0 END) as pago_liquido,
                    SUM(CASE WHEN cp.status_local != 'pago' THEN cp.valor_liquido ELSE 0 END) as aberto_liquido
                ")
                ->join('contratos ct', 'ct.id = cp.contrato_id')
                ->where('ct.event_id', $event_id)
                ->where('ct.situacao', $row['situacao'])
                ->where('ct.deleted_at IS NULL')
                ->get()
                ->getRowArray();

            $row['situacao_label'] = $this->getSituacaoLabel($row['situacao']);
            $row['valor_total_formatado'] = 'R$ ' . number_format($row['valor_total'] ?? 0, 2, ',', '.');
            $row['valor_pago_formatado'] = 'R$ ' . number_format($row['valor_pago'] ?? 0, 2, ',', '.');
            $row['valor_em_aberto_formatado'] = 'R$ ' . number_format($row['valor_em_aberto'] ?? 0, 2, ',', '.');
            $row['valor_pago_liquido'] = $liquidoResult['pago_liquido'] ?? 0;
            $row['valor_pago_liquido_formatado'] = 'R$ ' . number_format($liquidoResult['pago_liquido'] ?? 0, 2, ',', '.');
            $row['valor_aberto_liquido'] = $liquidoResult['aberto_liquido'] ?? 0;
            $row['valor_aberto_liquido_formatado'] = 'R$ ' . number_format($liquidoResult['aberto_liquido'] ?? 0, 2, ',', '.');
        }

        return $result;
    }

    private function getContratosPorExpositor(int $event_id): array
    {
        $result = $this->db->table('contratos c')
            ->select("e.nome as expositor, COUNT(c.id) as quantidade, SUM(c.valor_final) as valor_total, SUM(c.valor_pago) as valor_pago, SUM(c.valor_em_aberto) as valor_em_aberto")
            ->join('expositores e', 'e.id = c.expositor_id', 'left')
            ->where('c.event_id', $event_id)
            ->where('c.deleted_at IS NULL')
            ->groupBy("c.expositor_id")
            ->orderBy("valor_total", "DESC")
            ->get()
            ->getResultArray();

        foreach ($result as &$row) {
            $row['valor_total_formatado'] = 'R$ ' . number_format($row['valor_total'] ?? 0, 2, ',', '.');
            $row['valor_pago_formatado'] = 'R$ ' . number_format($row['valor_pago'] ?? 0, 2, ',', '.');
            $row['valor_em_aberto_formatado'] = 'R$ ' . number_format($row['valor_em_aberto'] ?? 0, 2, ',', '.');
        }

        return $result;
    }

    private function getResumoFinanceiro(int $event_id): array
    {
        // Resumo bruto dos contratos
        $result = $this->db->table('contratos')
            ->select("
                COUNT(*) as total_contratos,
                SUM(valor_original) as valor_original,
                SUM(valor_desconto) as valor_desconto,
                SUM(valor_final) as valor_final,
                SUM(valor_pago) as valor_pago,
                SUM(valor_em_aberto) as valor_em_aberto
            ")
            ->where('event_id', $event_id)
            ->where('deleted_at IS NULL')
            ->whereNotIn('situacao', ['cancelado', 'banido'])
            ->get()
            ->getRowArray();

        // Buscar valores líquidos das parcelas
        $parcelasResult = $this->db->table('contrato_parcelas cp')
            ->select("
                SUM(cp.valor) as total_bruto_parcelas,
                SUM(cp.valor_liquido) as total_liquido_parcelas,
                SUM(CASE WHEN cp.status_local = 'pago' THEN cp.valor ELSE 0 END) as pago_bruto,
                SUM(CASE WHEN cp.status_local = 'pago' THEN cp.valor_liquido ELSE 0 END) as pago_liquido,
                SUM(CASE WHEN cp.status_local != 'pago' THEN cp.valor ELSE 0 END) as aberto_bruto,
                SUM(CASE WHEN cp.status_local != 'pago' THEN cp.valor_liquido ELSE 0 END) as aberto_liquido
            ")
            ->join('contratos c', 'c.id = cp.contrato_id')
            ->where('c.event_id', $event_id)
            ->where('c.deleted_at IS NULL')
            ->whereNotIn('c.situacao', ['cancelado', 'banido'])
            ->get()
            ->getRowArray();

        $valorPago = $result['valor_pago'] ?? 0;
        $valorFinal = $result['valor_final'] ?? 0;
        $taxaTotal = ($parcelasResult['total_bruto_parcelas'] ?? 0) - ($parcelasResult['total_liquido_parcelas'] ?? 0);

        $percentualPago = $valorFinal > 0 ? round($valorPago / $valorFinal * 100, 1) : 0;

        return [
            'total_contratos' => $result['total_contratos'] ?? 0,
            'valor_original' => $result['valor_original'] ?? 0,
            'valor_original_formatado' => 'R$ ' . number_format($result['valor_original'] ?? 0, 2, ',', '.'),
            'valor_desconto' => $result['valor_desconto'] ?? 0,
            'valor_desconto_formatado' => 'R$ ' . number_format($result['valor_desconto'] ?? 0, 2, ',', '.'),
            'valor_final' => $valorFinal,
            'valor_final_formatado' => 'R$ ' . number_format($valorFinal, 2, ',', '.'),
            'valor_pago' => $valorPago,
            'valor_pago_formatado' => 'R$ ' . number_format($valorPago, 2, ',', '.'),
            'valor_em_aberto' => $result['valor_em_aberto'] ?? 0,
            'valor_em_aberto_formatado' => 'R$ ' . number_format($result['valor_em_aberto'] ?? 0, 2, ',', '.'),
            'percentual_pago' => $percentualPago,
            // Valores líquidos
            'valor_pago_liquido' => $parcelasResult['pago_liquido'] ?? 0,
            'valor_pago_liquido_formatado' => 'R$ ' . number_format($parcelasResult['pago_liquido'] ?? 0, 2, ',', '.'),
            'valor_aberto_liquido' => $parcelasResult['aberto_liquido'] ?? 0,
            'valor_aberto_liquido_formatado' => 'R$ ' . number_format($parcelasResult['aberto_liquido'] ?? 0, 2, ',', '.'),
            'taxa_total' => $taxaTotal,
            'taxa_total_formatado' => 'R$ ' . number_format($taxaTotal, 2, ',', '.'),
        ];
    }

    private function getRecebimentosMensais(int $event_id): array
    {
        try {
            $result = $this->db->table('contrato_parcelas cp')
                ->select("DATE_FORMAT(COALESCE(cp.data_pagamento, cp.updated_at), '%Y-%m') as mes, SUM(cp.valor) as valor_pago, COUNT(*) as quantidade")
                ->join('contratos c', 'c.id = cp.contrato_id')
                ->where('c.event_id', $event_id)
                ->where('cp.status_local', 'pago')
                ->where("COALESCE(cp.data_pagamento, cp.updated_at) IS NOT NULL")
                ->groupBy("mes")
                ->orderBy("mes", "ASC")
                ->get()
                ->getResultArray();

            foreach ($result as &$row) {
                if (!empty($row['mes'])) {
                    $row['mes_formatado'] = date('M/Y', strtotime($row['mes'] . '-01'));
                } else {
                    $row['mes_formatado'] = 'N/A';
                }
                $row['valor_pago_formatado'] = 'R$ ' . number_format($row['valor_pago'] ?? 0, 2, ',', '.');
            }

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Erro em getRecebimentosMensais: ' . $e->getMessage());
            return [];
        }
    }

    private function getTotaisContratos(int $event_id, string $situacao = ''): array
    {
        $builder = $this->db->table('contratos')
            ->select("COUNT(*) as quantidade, SUM(valor_final) as valor_total, SUM(valor_pago) as valor_pago, SUM(valor_em_aberto) as valor_em_aberto")
            ->where('event_id', $event_id)
            ->where('deleted_at IS NULL');

        if ($situacao) {
            $builder->where('situacao', $situacao);
        }

        $result = $builder->get()->getRowArray();

        // Buscar valores líquidos
        $liquidoBuilder = $this->db->table('contrato_parcelas cp')
            ->select("
                SUM(CASE WHEN cp.status_local = 'pago' THEN cp.valor_liquido ELSE 0 END) as pago_liquido,
                SUM(CASE WHEN cp.status_local != 'pago' THEN cp.valor_liquido ELSE 0 END) as aberto_liquido
            ")
            ->join('contratos c', 'c.id = cp.contrato_id')
            ->where('c.event_id', $event_id)
            ->where('c.deleted_at IS NULL');

        if ($situacao) {
            $liquidoBuilder->where('c.situacao', $situacao);
        }

        $liquidoResult = $liquidoBuilder->get()->getRowArray();

        return [
            'quantidade' => $result['quantidade'] ?? 0,
            'valor_total' => $result['valor_total'] ?? 0,
            'valor_total_formatado' => 'R$ ' . number_format($result['valor_total'] ?? 0, 2, ',', '.'),
            'valor_pago' => $result['valor_pago'] ?? 0,
            'valor_pago_formatado' => 'R$ ' . number_format($result['valor_pago'] ?? 0, 2, ',', '.'),
            'valor_em_aberto' => $result['valor_em_aberto'] ?? 0,
            'valor_em_aberto_formatado' => 'R$ ' . number_format($result['valor_em_aberto'] ?? 0, 2, ',', '.'),
            'valor_pago_liquido' => $liquidoResult['pago_liquido'] ?? 0,
            'valor_pago_liquido_formatado' => 'R$ ' . number_format($liquidoResult['pago_liquido'] ?? 0, 2, ',', '.'),
            'valor_aberto_liquido' => $liquidoResult['aberto_liquido'] ?? 0,
            'valor_aberto_liquido_formatado' => 'R$ ' . number_format($liquidoResult['aberto_liquido'] ?? 0, 2, ',', '.'),
        ];
    }

    private function getSituacoes(): array
    {
        return [
            'rascunho' => 'Rascunho',
            'proposta_enviada' => 'Proposta Enviada',
            'aguardando_pagamento' => 'Aguardando Pagamento',
            'pagamento_confirmado' => 'Pagamento Confirmado',
            'aguardando_contrato' => 'Aguardando Contrato',
            'contrato_assinado' => 'Contrato Assinado',
            'finalizado' => 'Finalizado',
            'cancelado' => 'Cancelado',
            'banido' => 'Banido',
        ];
    }

    private function getSituacaoLabel(string $situacao): string
    {
        $labels = $this->getSituacoes();
        return $labels[$situacao] ?? ucfirst(str_replace('_', ' ', $situacao));
    }
}
