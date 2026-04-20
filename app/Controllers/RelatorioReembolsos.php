<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Dompdf\Dompdf;

class RelatorioReembolsos extends BaseController
{
    protected $refoundModel;
    protected $eventoModel;

    public function __construct()
    {
        $this->refoundModel = new \App\Models\RefoundModel();
        $this->eventoModel = new \App\Models\EventoModel();
    }

    private function podeVisualizar(): bool
    {
        return $this->usuarioLogado()->temPermissaoPara('visualizar-relatorios');
    }

    /**
     * Filtros e link para gerar o relatório
     */
    public function index()
    {
        if (! $this->podeVisualizar()) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $event_id = session()->get('event_id');
        $evento = $event_id ? $this->eventoModel->find($event_id) : null;
        $eventos = $this->eventoModel->orderBy('id', 'DESC')->findAll();

        return view('Relatorios/Reembolsos/index', [
            'titulo' => 'Relatório de Reembolsos',
            'evento' => $evento,
            'eventos' => $eventos,
            'event_id' => $event_id,
            'data_inicio' => $this->request->getGet('data_inicio') ?: date('Y-m-01'),
            'data_fim' => $this->request->getGet('data_fim') ?: date('Y-m-d'),
        ]);
    }

    /**
     * Listagem HTML com totais e exportações
     */
    public function lista()
    {
        if (! $this->podeVisualizar()) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $filtros = $this->montarFiltrosRequest();
        if ($filtros === null) {
            return redirect()->to('relatorios/reembolsos')->with('atencao', 'Informe um período válido (data inicial menor ou igual à final).');
        }

        $totais = $this->refoundModel->totaisParaRelatorio($filtros);
        $registros = $this->refoundModel->listarParaRelatorio($filtros);

        $evento = null;
        if (! empty($filtros['evento_id'])) {
            $evento = $this->eventoModel->find($filtros['evento_id']);
        }

        $linhas = [];
        foreach ($registros as $r) {
            $linhas[] = [
                'id' => $r->id,
                'cliente_nome' => $r->cliente_nome ?? '',
                'cliente_email' => $r->cliente_email ?? '',
                'pedido_codigo' => $r->pedido_codigo ?? '',
                'valor' => (float) ($r->pedido_valor_total ?? 0),
                'evento_nome' => $r->evento_nome ?? '',
                'tipo_solicitacao' => $this->rotuloTipo($r->tipo_solicitacao ?? ''),
                'situacao' => 'Aprovado',
                'pagamento' => $this->textoPagamento($r->status ?? ''),
                'data_solicitacao' => $r->created_at ? date('d/m/Y H:i', strtotime($r->created_at)) : '',
                'processado_em' => $r->processado_em ? date('d/m/Y H:i', strtotime($r->processado_em)) : '-',
            ];
        }

        return view('Relatorios/Reembolsos/lista', [
            'titulo' => 'Relatório de Reembolsos',
            'filtros' => $filtros,
            'evento' => $evento,
            'totais' => $totais,
            'linhas' => $linhas,
            'eventos' => $this->eventoModel->orderBy('id', 'DESC')->findAll(),
        ]);
    }

    /**
     * Relatório detalhado: uma linha por ingresso do pedido (mesmos filtros).
     */
    public function listaDetalheIngressos()
    {
        if (! $this->podeVisualizar()) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $filtros = $this->montarFiltrosRequest();
        if ($filtros === null) {
            return redirect()->to('relatorios/reembolsos')->with('atencao', 'Informe um período válido (data inicial menor ou igual à final).');
        }

        $totaisDet = $this->refoundModel->totaisParaRelatorioDetalheIngressos($filtros);
        $registros = $this->refoundModel->listarParaRelatorioDetalheIngressos($filtros);

        $evento = null;
        if (! empty($filtros['evento_id'])) {
            $evento = $this->eventoModel->find($filtros['evento_id']);
        }

        $linhas = [];
        foreach ($registros as $row) {
            $linhas[] = $this->montarLinhaDetalheIngresso($row);
        }

        return view('Relatorios/Reembolsos/lista_ingressos', [
            'titulo' => 'Relatório de Reembolsos — Detalhe por ingresso',
            'filtros' => $filtros,
            'evento' => $evento,
            'totais_detalhe' => $totaisDet,
            'linhas' => $linhas,
        ]);
    }

    /**
     * CSV detalhado por ingresso
     */
    public function exportarExcelDetalhe()
    {
        if (! $this->podeVisualizar()) {
            return redirect()->back()->with('atencao', 'Sem permissão.');
        }

        $filtros = $this->montarFiltrosRequest();
        if ($filtros === null) {
            return redirect()->to('relatorios/reembolsos')->with('atencao', 'Período inválido.');
        }

        $registros = $this->refoundModel->listarParaRelatorioDetalheIngressos($filtros);
        $totaisDet = $this->refoundModel->totaisParaRelatorioDetalheIngressos($filtros);

        $evento = ! empty($filtros['evento_id']) ? $this->eventoModel->find($filtros['evento_id']) : null;
        $nomeEventoSlug = preg_replace('/[^a-zA-Z0-9]/', '_', $evento->nome ?? 'todos_eventos');
        $nomeArquivo = "reembolsos_detalhe_ingressos_{$nomeEventoSlug}_{$filtros['data_inicio']}_{$filtros['data_fim']}.csv";

        $output = fopen('php://temp', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, ['Relatório de reembolsos — detalhe por ingresso'], ';');
        fputcsv($output, ['Evento', $evento->nome ?? 'Todos os eventos'], ';');
        fputcsv($output, ['Período (data da solicitação)', $filtros['data_inicio'] . ' a ' . $filtros['data_fim']], ';');
        fputcsv($output, ['Linhas (ingressos)', (int) ($totaisDet->total_linhas ?? 0)], ';');
        fputcsv($output, ['Soma valores ingressos', 'R$ ' . number_format((float) ($totaisDet->valor_ingressos ?? 0), 2, ',', '.')], ';');
        fputcsv($output, [], ';');

        $cabecalho = [
            'ID solicitação',
            'Cliente',
            'E-mail',
            'Pedido',
            'Evento',
            'Tipo solicitação',
            'Situação',
            'Pagamento',
            'Data solicitação',
            'Processado em',
            'ID ingresso',
            'Ingresso',
            'Código ingresso',
            'Participante',
            'Tipo ingresso',
            'Valor ingresso',
        ];
        fputcsv($output, $cabecalho, ';');

        foreach ($registros as $row) {
            $l = $this->montarLinhaDetalheIngresso($row);
            fputcsv($output, [
                $l['refound_id'],
                $l['cliente_nome'],
                $l['cliente_email'],
                $l['pedido_codigo'],
                $l['evento_nome'],
                $l['tipo_solicitacao'],
                $l['situacao'],
                $l['pagamento'],
                $l['data_solicitacao'],
                $l['processado_em'],
                $l['ingresso_id'],
                $l['ingresso_nome'],
                $l['ingresso_codigo'],
                $l['ingresso_participante'],
                $l['ingresso_tipo'],
                number_format($l['ingresso_valor'], 2, ',', '.'),
            ], ';');
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $nomeArquivo . '"')
            ->setBody($csv);
    }

    /**
     * PDF detalhado por ingresso
     */
    public function exportarPdfDetalhe()
    {
        if (! $this->podeVisualizar()) {
            return redirect()->back()->with('atencao', 'Sem permissão.');
        }

        $filtros = $this->montarFiltrosRequest();
        if ($filtros === null) {
            return redirect()->to('relatorios/reembolsos')->with('atencao', 'Período inválido.');
        }

        $registros = $this->refoundModel->listarParaRelatorioDetalheIngressos($filtros);
        $totaisDet = $this->refoundModel->totaisParaRelatorioDetalheIngressos($filtros);
        $evento = ! empty($filtros['evento_id']) ? $this->eventoModel->find($filtros['evento_id']) : null;

        $linhas = [];
        foreach ($registros as $row) {
            $linhas[] = $this->montarLinhaDetalheIngresso($row);
        }

        $html = view('Relatorios/Reembolsos/pdf_ingressos', [
            'titulo' => 'Relatório de reembolsos — detalhe por ingresso',
            'evento' => $evento,
            'filtros' => $filtros,
            'totais_detalhe' => $totaisDet,
            'linhas' => $linhas,
        ]);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $slug = preg_replace('/[^a-zA-Z0-9]/', '_', $evento->nome ?? 'todos');
        $fn = "relatorio_reembolsos_ingressos_{$slug}_{$filtros['data_inicio']}_{$filtros['data_fim']}.pdf";

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $fn . '"')
            ->setBody($dompdf->output());
    }

    /**
     * Exporta CSV (Excel)
     */
    public function exportarExcel()
    {
        if (! $this->podeVisualizar()) {
            return redirect()->back()->with('atencao', 'Sem permissão.');
        }

        $filtros = $this->montarFiltrosRequest();
        if ($filtros === null) {
            return redirect()->to('relatorios/reembolsos')->with('atencao', 'Período inválido.');
        }

        $registros = $this->refoundModel->listarParaRelatorio($filtros);
        $totais = $this->refoundModel->totaisParaRelatorio($filtros);

        $evento = ! empty($filtros['evento_id']) ? $this->eventoModel->find($filtros['evento_id']) : null;
        $nomeEventoSlug = preg_replace('/[^a-zA-Z0-9]/', '_', $evento->nome ?? 'todos_eventos');
        $nomeArquivo = "reembolsos_{$nomeEventoSlug}_{$filtros['data_inicio']}_{$filtros['data_fim']}.csv";

        $output = fopen('php://temp', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, ['Relatório de reembolsos e solicitações'], ';');
        fputcsv($output, ['Evento', $evento->nome ?? 'Todos os eventos'], ';');
        fputcsv($output, ['Período (data da solicitação)', $filtros['data_inicio'] . ' a ' . $filtros['data_fim']], ';');
        fputcsv($output, ['Quantidade', (int) ($totais->total_registros ?? 0)], ';');
        fputcsv($output, ['Valor total (pedido)', 'R$ ' . number_format((float) ($totais->valor_total ?? 0), 2, ',', '.')], ';');
        fputcsv($output, [], ';');

        $cabecalho = ['ID', 'Cliente', 'E-mail', 'Pedido', 'Valor', 'Evento', 'Tipo', 'Situação', 'Pagamento', 'Data solicitação', 'Processado em'];
        fputcsv($output, $cabecalho, ';');

        foreach ($registros as $r) {
            fputcsv($output, [
                $r->id,
                $r->cliente_nome ?? '',
                $r->cliente_email ?? '',
                $r->pedido_codigo ?? '',
                number_format((float) ($r->pedido_valor_total ?? 0), 2, ',', '.'),
                $r->evento_nome ?? '',
                $this->rotuloTipo($r->tipo_solicitacao ?? ''),
                'Aprovado',
                $this->textoPagamento($r->status ?? ''),
                $r->created_at ? date('d/m/Y H:i', strtotime($r->created_at)) : '',
                $r->processado_em ? date('d/m/Y H:i', strtotime($r->processado_em)) : '-',
            ], ';');
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $nomeArquivo . '"')
            ->setBody($csv);
    }

    /**
     * Exporta PDF
     */
    public function exportarPdf()
    {
        if (! $this->podeVisualizar()) {
            return redirect()->back()->with('atencao', 'Sem permissão.');
        }

        $filtros = $this->montarFiltrosRequest();
        if ($filtros === null) {
            return redirect()->to('relatorios/reembolsos')->with('atencao', 'Período inválido.');
        }

        $registros = $this->refoundModel->listarParaRelatorio($filtros);
        $totaisRow = $this->refoundModel->totaisParaRelatorio($filtros);
        $evento = ! empty($filtros['evento_id']) ? $this->eventoModel->find($filtros['evento_id']) : null;

        $linhas = [];
        foreach ($registros as $r) {
            $linhas[] = [
                'id' => $r->id,
                'cliente_nome' => $r->cliente_nome ?? '-',
                'pedido_codigo' => $r->pedido_codigo ?? '-',
                'valor' => (float) ($r->pedido_valor_total ?? 0),
                'evento_nome' => $r->evento_nome ?? '-',
                'tipo_solicitacao' => $this->rotuloTipo($r->tipo_solicitacao ?? ''),
                'situacao' => 'Aprovado',
                'pagamento' => $this->textoPagamento($r->status ?? ''),
                'data_solicitacao' => $r->created_at ? date('d/m/Y H:i', strtotime($r->created_at)) : '-',
                'processado_em' => $r->processado_em ? date('d/m/Y H:i', strtotime($r->processado_em)) : '-',
            ];
        }

        $html = view('Relatorios/Reembolsos/pdf', [
            'titulo' => 'Relatório de reembolsos e solicitações',
            'evento' => $evento,
            'filtros' => $filtros,
            'totais' => $totaisRow,
            'linhas' => $linhas,
        ]);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $slug = preg_replace('/[^a-zA-Z0-9]/', '_', $evento->nome ?? 'todos');
        $fn = "relatorio_reembolsos_{$slug}_{$filtros['data_inicio']}_{$filtros['data_fim']}.pdf";

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $fn . '"')
            ->setBody($dompdf->output());
    }

    private function montarFiltrosRequest(): ?array
    {
        $data_inicio = $this->request->getGet('data_inicio') ?: date('Y-m-01');
        $data_fim = $this->request->getGet('data_fim') ?: date('Y-m-d');
        $evento_id = $this->request->getGet('evento_id');
        $tipo = $this->request->getGet('tipo_solicitacao');
        $status = $this->request->getGet('status');

        if (strtotime($data_inicio) > strtotime($data_fim)) {
            return null;
        }

        return [
            'data_inicio' => $data_inicio,
            'data_fim' => $data_fim,
            'evento_id' => $evento_id !== null && $evento_id !== '' ? (int) $evento_id : null,
            'tipo_solicitacao' => $tipo !== null && $tipo !== '' ? $tipo : null,
            'status' => $status !== null && $status !== '' ? $status : null,
        ];
    }

    private function textoPagamento(?string $status): string
    {
        $s = strtolower(trim($status ?? ''));
        switch ($s) {
            case 'pendente':
                return 'Pendente de pagamento';
            case 'processando':
                return 'Processando';
            case 'concluido':
                return 'Concluído';
            case 'cancelado':
                return 'Concluído';
            case 'erro':
                return 'Erro';
            default:
                return $status !== null && $status !== '' ? $status : 'N/A';
        }
    }

    private function rotuloTipo(string $tipo): string
    {
        $t = strtolower($tipo);
        switch ($t) {
            case 'reembolso':
                return 'Reembolso';
            case 'upgrade':
                return 'Upgrade';
            default:
                return $tipo !== '' ? ucfirst($tipo) : '-';
        }
    }

    /**
     * @param object $row resultado do join refounds + ingressos
     *
     * @return array<string, mixed>
     */
    private function montarLinhaDetalheIngresso(object $row): array
    {
        return [
            'refound_id' => (int) ($row->refound_id ?? 0),
            'cliente_nome' => $row->cliente_nome ?? '',
            'cliente_email' => $row->cliente_email ?? '',
            'pedido_codigo' => $row->pedido_codigo ?? '',
            'evento_nome' => $row->evento_nome ?? '',
            'tipo_solicitacao' => $this->rotuloTipo($row->tipo_solicitacao ?? ''),
            'situacao' => 'Aprovado',
            'pagamento' => $this->textoPagamento($row->status ?? ''),
            'data_solicitacao' => ! empty($row->refound_created_at)
                ? date('d/m/Y H:i', strtotime($row->refound_created_at))
                : '',
            'processado_em' => ! empty($row->processado_em)
                ? date('d/m/Y H:i', strtotime($row->processado_em))
                : '-',
            'ingresso_id' => (int) ($row->ingresso_id ?? 0),
            'ingresso_nome' => $row->ingresso_nome ?? '',
            'ingresso_codigo' => $row->ingresso_codigo ?? '',
            'ingresso_participante' => $row->ingresso_participante ?? '',
            'ingresso_tipo' => $row->ingresso_tipo ?? '',
            'ingresso_valor' => (float) ($row->ingresso_valor ?? 0),
        ];
    }
}
