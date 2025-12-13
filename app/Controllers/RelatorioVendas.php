<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Dompdf\Dompdf;

class RelatorioVendas extends BaseController
{
    protected $pedidoModel;
    protected $eventoModel;
    protected $db;

    public function __construct()
    {
        $this->pedidoModel = new \App\Models\PedidoModel();
        $this->eventoModel = new \App\Models\EventoModel();
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

        // Lista todos os eventos para seleção
        $eventos = $this->eventoModel->orderBy('id', 'DESC')->findAll();

        $data = [
            'titulo' => 'Relatórios de Vendas',
            'evento' => $evento,
            'eventos' => $eventos,
            'event_id' => $event_id,
        ];

        return view('Relatorios/Vendas/index', $data);
    }

    /**
     * Relatório de vendas por período
     */
    public function vendasPorPeriodo()
    {
        if (!$this->usuarioLogado()->temPermissaoPara('listar-contratos')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $event_id = $this->request->getGet('evento_id') ?? session()->get('event_id');
        $data_inicio = $this->request->getGet('data_inicio') ?? date('Y-m-01');
        $data_fim = $this->request->getGet('data_fim') ?? date('Y-m-d');

        if (!$event_id) {
            return redirect()->to('relatorios/vendas')->with('atencao', 'Selecione um evento.');
        }

        $evento = $this->eventoModel->find($event_id);
        $eventos = $this->eventoModel->orderBy('id', 'DESC')->findAll();

        // Buscar vendas por período
        $vendasDiarias = $this->getVendasDiarias($event_id, $data_inicio, $data_fim);
        $totais = $this->getTotaisPeriodo($event_id, $data_inicio, $data_fim);
        
        // Métricas adicionais como no dashboard
        $metricas = $this->getMetricasCompletas($event_id, $data_inicio, $data_fim);
        $topIngressos = $this->getTopIngressosPeriodo($event_id, $data_inicio, $data_fim);
        $vendasPorMetodo = $this->getVendasPorMetodoPagamento($event_id, $data_inicio, $data_fim);

        $data = [
            'titulo' => 'Relatório de Vendas por Período',
            'evento' => $evento,
            'eventos' => $eventos,
            'event_id' => $event_id,
            'data_inicio' => $data_inicio,
            'data_fim' => $data_fim,
            'vendas_diarias' => $vendasDiarias,
            'totais' => $totais,
            'metricas' => $metricas,
            'top_ingressos' => $topIngressos,
            'vendas_por_metodo' => $vendasPorMetodo,
        ];

        return view('Relatorios/Vendas/vendas_periodo', $data);
    }

    /**
     * Relatório de vendas por tipo de ingresso
     */
    public function vendasPorIngresso()
    {
        if (!$this->usuarioLogado()->temPermissaoPara('listar-contratos')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $event_id = $this->request->getGet('evento_id') ?? session()->get('event_id');
        $data_inicio = $this->request->getGet('data_inicio') ?? date('Y-m-01');
        $data_fim = $this->request->getGet('data_fim') ?? date('Y-m-d');

        if (!$event_id) {
            return redirect()->to('relatorios/vendas')->with('atencao', 'Selecione um evento.');
        }

        $evento = $this->eventoModel->find($event_id);
        $eventos = $this->eventoModel->orderBy('id', 'DESC')->findAll();

        // Buscar vendas por ingresso
        $vendasPorIngresso = $this->getVendasPorIngresso($event_id, $data_inicio, $data_fim);
        $totais = $this->getTotaisPeriodo($event_id, $data_inicio, $data_fim);

        $data = [
            'titulo' => 'Relatório de Vendas por Ingresso',
            'evento' => $evento,
            'eventos' => $eventos,
            'event_id' => $event_id,
            'data_inicio' => $data_inicio,
            'data_fim' => $data_fim,
            'vendas_por_ingresso' => $vendasPorIngresso,
            'totais' => $totais,
        ];

        return view('Relatorios/Vendas/vendas_ingresso', $data);
    }

    /**
     * Relatório de vendas por método de pagamento
     */
    public function vendasPorMetodo()
    {
        if (!$this->usuarioLogado()->temPermissaoPara('listar-contratos')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $event_id = $this->request->getGet('evento_id') ?? session()->get('event_id');
        $data_inicio = $this->request->getGet('data_inicio') ?? date('Y-m-01');
        $data_fim = $this->request->getGet('data_fim') ?? date('Y-m-d');

        if (!$event_id) {
            return redirect()->to('relatorios/vendas')->with('atencao', 'Selecione um evento.');
        }

        $evento = $this->eventoModel->find($event_id);
        $eventos = $this->eventoModel->orderBy('id', 'DESC')->findAll();

        // Buscar vendas por método
        $vendasPorMetodo = $this->getVendasPorMetodoPagamento($event_id, $data_inicio, $data_fim);
        $totais = $this->getTotaisPeriodo($event_id, $data_inicio, $data_fim);

        $data = [
            'titulo' => 'Relatório de Vendas por Método de Pagamento',
            'evento' => $evento,
            'eventos' => $eventos,
            'event_id' => $event_id,
            'data_inicio' => $data_inicio,
            'data_fim' => $data_fim,
            'vendas_por_metodo' => $vendasPorMetodo,
            'totais' => $totais,
        ];

        return view('Relatorios/Vendas/vendas_metodo', $data);
    }

    /**
     * Exportar relatório para Excel (CSV)
     */
    public function exportarExcel(string $tipo)
    {
        $event_id = $this->request->getGet('evento_id') ?? session()->get('event_id');
        $data_inicio = $this->request->getGet('data_inicio') ?? date('Y-m-01');
        $data_fim = $this->request->getGet('data_fim') ?? date('Y-m-d');

        if (!$event_id) {
            return redirect()->back()->with('erro', 'Evento não selecionado.');
        }

        $evento = $this->eventoModel->find($event_id);
        $nomeEvento = preg_replace('/[^a-zA-Z0-9]/', '_', $evento->nome ?? 'evento');

        switch ($tipo) {
            case 'periodo':
                $dados = $this->getVendasDiarias($event_id, $data_inicio, $data_fim);
                $filename = "vendas_periodo_{$nomeEvento}_{$data_inicio}_{$data_fim}.csv";
                $cabecalho = ['Data', 'Quantidade', 'Valor Total'];
                break;
            case 'ingresso':
                $dados = $this->getVendasPorIngresso($event_id, $data_inicio, $data_fim);
                $filename = "vendas_ingresso_{$nomeEvento}_{$data_inicio}_{$data_fim}.csv";
                $cabecalho = ['Ingresso', 'Quantidade', 'Valor Total', 'Percentual'];
                break;
            case 'metodo':
                $dados = $this->getVendasPorMetodoPagamento($event_id, $data_inicio, $data_fim);
                $filename = "vendas_metodo_{$nomeEvento}_{$data_inicio}_{$data_fim}.csv";
                $cabecalho = ['Método', 'Quantidade', 'Valor Total', 'Percentual'];
                break;
            default:
                return redirect()->back()->with('erro', 'Tipo de relatório inválido.');
        }

        // Gera CSV
        $output = fopen('php://temp', 'w');
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Cabeçalho
        fputcsv($output, $cabecalho, ';');

        // Dados
        foreach ($dados as $linha) {
            $row = [];
            foreach ($linha as $valor) {
                $row[] = $valor;
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
        $data_inicio = $this->request->getGet('data_inicio') ?? date('Y-m-01');
        $data_fim = $this->request->getGet('data_fim') ?? date('Y-m-d');

        if (!$event_id) {
            return redirect()->back()->with('erro', 'Evento não selecionado.');
        }

        $evento = $this->eventoModel->find($event_id);
        $totais = $this->getTotaisPeriodo($event_id, $data_inicio, $data_fim);

        switch ($tipo) {
            case 'periodo':
                $dados = $this->getVendasDiarias($event_id, $data_inicio, $data_fim);
                $titulo = 'Relatório de Vendas por Período';
                $colunas = ['Data', 'Quantidade', 'Valor Total'];
                break;
            case 'ingresso':
                $dados = $this->getVendasPorIngresso($event_id, $data_inicio, $data_fim);
                $titulo = 'Relatório de Vendas por Ingresso';
                $colunas = ['Ingresso', 'Quantidade', 'Valor Total', 'Percentual'];
                break;
            case 'metodo':
                $dados = $this->getVendasPorMetodoPagamento($event_id, $data_inicio, $data_fim);
                $titulo = 'Relatório de Vendas por Método de Pagamento';
                $colunas = ['Método', 'Quantidade', 'Valor Total', 'Percentual'];
                break;
            default:
                return redirect()->back()->with('erro', 'Tipo de relatório inválido.');
        }

        $html = view('Relatorios/Vendas/pdf_template', [
            'titulo' => $titulo,
            'evento' => $evento,
            'data_inicio' => $data_inicio,
            'data_fim' => $data_fim,
            'dados' => $dados,
            'colunas' => $colunas,
            'totais' => $totais,
        ]);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nomeEvento = preg_replace('/[^a-zA-Z0-9]/', '_', $evento->nome ?? 'evento');
        $filename = "relatorio_{$tipo}_{$nomeEvento}_{$data_inicio}_{$data_fim}.pdf";

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    // ========================================
    // MÉTODOS PRIVADOS - CONSULTAS
    // ========================================

    /**
     * Busca vendas diárias
     */
    private function getVendasDiarias(int $event_id, string $data_inicio, string $data_fim): array
    {
        $result = $this->db->table('pedidos')
            ->select("DATE(created_at) as data, COUNT(*) as quantidade, SUM(total) as valor_total")
            ->where('evento_id', $event_id)
            ->whereIn('status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
            ->where("DATE(created_at) >=", $data_inicio)
            ->where("DATE(created_at) <=", $data_fim)
            ->groupBy("DATE(created_at)")
            ->orderBy("data", "ASC")
            ->get()
            ->getResultArray();

        // Formatar para exibição
        foreach ($result as &$row) {
            $row['data_formatada'] = date('d/m/Y', strtotime($row['data']));
            $row['valor_formatado'] = 'R$ ' . number_format($row['valor_total'] ?? 0, 2, ',', '.');
        }

        return $result;
    }

    /**
     * Busca vendas por tipo de ingresso
     */
    private function getVendasPorIngresso(int $event_id, string $data_inicio, string $data_fim): array
    {
        $result = $this->db->table('pedidos p')
            ->select("COALESCE(i.nome, 'Avulso') as ingresso, COUNT(DISTINCT p.id) as quantidade, SUM(p.total) as valor_total")
            ->join('ingressos i', 'i.pedido_id = p.id', 'left')
            ->where('p.evento_id', $event_id)
            ->whereIn('p.status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
            ->where("DATE(p.created_at) >=", $data_inicio)
            ->where("DATE(p.created_at) <=", $data_fim)
            ->groupBy("i.nome")
            ->orderBy("valor_total", "DESC")
            ->get()
            ->getResultArray();

        // Calcular totais para percentuais
        $totalValor = array_sum(array_column($result, 'valor_total'));

        foreach ($result as &$row) {
            $row['valor_formatado'] = 'R$ ' . number_format($row['valor_total'] ?? 0, 2, ',', '.');
            $row['percentual'] = $totalValor > 0 ? round(($row['valor_total'] / $totalValor) * 100, 1) : 0;
        }

        return $result;
    }

    /**
     * Busca vendas por método de pagamento
     */
    private function getVendasPorMetodoPagamento(int $event_id, string $data_inicio, string $data_fim): array
    {
        $result = $this->db->table('pedidos')
            ->select("COALESCE(forma_pagamento, 'Não especificado') as metodo, COUNT(*) as quantidade, SUM(total) as valor_total")
            ->where('evento_id', $event_id)
            ->whereIn('status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
            ->where("DATE(created_at) >=", $data_inicio)
            ->where("DATE(created_at) <=", $data_fim)
            ->groupBy("forma_pagamento")
            ->orderBy("valor_total", "DESC")
            ->get()
            ->getResultArray();

        // Calcular totais para percentuais
        $totalValor = array_sum(array_column($result, 'valor_total'));

        foreach ($result as &$row) {
            $row['valor_formatado'] = 'R$ ' . number_format($row['valor_total'] ?? 0, 2, ',', '.');
            $row['percentual'] = $totalValor > 0 ? round(($row['valor_total'] / $totalValor) * 100, 1) : 0;
            $row['metodo_label'] = $this->getMetodoLabel($row['metodo']);
        }

        return $result;
    }

    /**
     * Busca totais do período
     */
    private function getTotaisPeriodo(int $event_id, string $data_inicio, string $data_fim): array
    {
        $result = $this->db->table('pedidos')
            ->select("COUNT(*) as quantidade, SUM(total) as valor_total")
            ->where('evento_id', $event_id)
            ->whereIn('status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
            ->where("DATE(created_at) >=", $data_inicio)
            ->where("DATE(created_at) <=", $data_fim)
            ->get()
            ->getRowArray();

        return [
            'quantidade' => $result['quantidade'] ?? 0,
            'valor_total' => $result['valor_total'] ?? 0,
            'valor_formatado' => 'R$ ' . number_format($result['valor_total'] ?? 0, 2, ',', '.'),
            'ticket_medio' => ($result['quantidade'] ?? 0) > 0 
                ? 'R$ ' . number_format(($result['valor_total'] ?? 0) / $result['quantidade'], 2, ',', '.') 
                : 'R$ 0,00',
        ];
    }

    /**
     * Retorna label amigável para método de pagamento
     */
    private function getMetodoLabel(string $metodo): string
    {
        $labels = [
            'PIX' => 'PIX',
            'CREDIT_CARD' => 'Cartão de Crédito',
            'BOLETO' => 'Boleto',
            'credit_card' => 'Cartão de Crédito',
            'pix' => 'PIX',
            'boleto' => 'Boleto',
            'Dinheiro' => 'Dinheiro',
            'Cartão de Crédito' => 'Cartão de Crédito',
            'Cartão de Débito' => 'Cartão de Débito',
        ];

        return $labels[$metodo] ?? $metodo;
    }

    /**
     * Busca métricas completas do período (similar ao dashboard)
     */
    private function getMetricasCompletas(int $event_id, string $data_inicio, string $data_fim): array
    {
        try {
            // Total de ingressos vendidos (combo conta como 2)
            $ingressosResult = $this->db->query("
                SELECT SUM(CASE WHEN i.tipo = 'combo' THEN 2 ELSE 1 END) as total_ingressos
                FROM ingressos i
                INNER JOIN pedidos p ON p.id = i.pedido_id
                WHERE p.evento_id = ?
                AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
                AND i.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
                AND DATE(p.created_at) >= ?
                AND DATE(p.created_at) <= ?
            ", [$event_id, $data_inicio, $data_fim])->getRowArray();

            // Clientes únicos
            $clientesResult = $this->db->table('pedidos')
                ->select('COUNT(DISTINCT user_id) as clientes_unicos')
                ->where('evento_id', $event_id)
                ->whereIn('status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
                ->where("DATE(created_at) >=", $data_inicio)
                ->where("DATE(created_at) <=", $data_fim)
                ->get()
                ->getRowArray();

            // Receita total e ticket médio
            $receitaResult = $this->db->table('pedidos')
                ->select('SUM(total) as receita_total, COUNT(*) as total_pedidos, AVG(total) as ticket_medio')
                ->where('evento_id', $event_id)
                ->whereIn('status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
                ->where("DATE(created_at) >=", $data_inicio)
                ->where("DATE(created_at) <=", $data_fim)
                ->get()
                ->getRowArray();

            return [
                'total_ingressos' => (int)($ingressosResult['total_ingressos'] ?? 0),
                'clientes_unicos' => (int)($clientesResult['clientes_unicos'] ?? 0),
                'receita_total' => (float)($receitaResult['receita_total'] ?? 0),
                'receita_formatada' => 'R$ ' . number_format($receitaResult['receita_total'] ?? 0, 2, ',', '.'),
                'total_pedidos' => (int)($receitaResult['total_pedidos'] ?? 0),
                'ticket_medio' => (float)($receitaResult['ticket_medio'] ?? 0),
                'ticket_medio_formatado' => 'R$ ' . number_format($receitaResult['ticket_medio'] ?? 0, 2, ',', '.'),
            ];
        } catch (\Exception $e) {
            log_message('error', 'Erro getMetricasCompletas: ' . $e->getMessage());
            return [
                'total_ingressos' => 0,
                'clientes_unicos' => 0,
                'receita_total' => 0,
                'receita_formatada' => 'R$ 0,00',
                'total_pedidos' => 0,
                'ticket_medio' => 0,
                'ticket_medio_formatado' => 'R$ 0,00',
            ];
        }
    }

    /**
     * Busca top ingressos vendidos no período
     */
    private function getTopIngressosPeriodo(int $event_id, string $data_inicio, string $data_fim, int $limit = 10): array
    {
        try {
            $result = $this->db->query("
                SELECT 
                    t.nome as ingresso,
                    SUM(CASE WHEN i.tipo = 'combo' THEN 2 ELSE 1 END) as quantidade,
                    SUM(i.valor) as valor_total
                FROM ingressos i
                INNER JOIN pedidos p ON p.id = i.pedido_id
                INNER JOIN tickets t ON t.id = i.ticket_id
                WHERE p.evento_id = ?
                AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
                AND i.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
                AND DATE(p.created_at) >= ?
                AND DATE(p.created_at) <= ?
                GROUP BY t.id, t.nome
                ORDER BY quantidade DESC
                LIMIT 10
            ", [$event_id, $data_inicio, $data_fim])->getResultArray();

            foreach ($result as &$row) {
                $row['valor_formatado'] = 'R$ ' . number_format($row['valor_total'] ?? 0, 2, ',', '.');
            }

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Erro getTopIngressosPeriodo: ' . $e->getMessage());
            return [];
        }
    }
}
