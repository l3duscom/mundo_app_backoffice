<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class MetasVendas extends BaseController
{
    protected $metaModel;
    protected $faseModel;
    protected $marcoModel;
    protected $eventoModel;

    public function __construct()
    {
        $this->metaModel  = new \App\Models\MetaVendaModel();
        $this->faseModel  = new \App\Models\MetaVendaFaseModel();
        $this->marcoModel = new \App\Models\MetaVendaMarcoModel();
        $this->eventoModel = new \App\Models\EventoModel();
    }

    public function index()
    {
        $eventId = (int) $this->request->getGet('event_id');
        $eventos = $this->eventoModel->orderBy('data_inicio', 'DESC')->findAll();

        $metasComercial = [];
        $metasIngressos = [];

        if ($eventId) {
            $metas = $this->metaModel->porEvento($eventId);
            foreach ($metas as $m) {
                $m->total_fases = count($this->faseModel->porMeta($m->id));
                if ($m->tipo === 'comercial') {
                    $metasComercial[] = $m;
                } else {
                    $metasIngressos[] = $m;
                }
            }
        }

        return view('MetasVendas/index', [
            'titulo'          => 'Metas de Vendas',
            'eventos'         => $eventos,
            'eventIdSelecionado' => $eventId,
            'metasComercial'  => $metasComercial,
            'metasIngressos'  => $metasIngressos,
        ]);
    }

    public function criar()
    {
        $eventId = (int) $this->request->getGet('event_id');
        $tipo    = $this->request->getGet('tipo') === 'comercial' ? 'comercial' : 'ingressos';

        return view('MetasVendas/form', [
            'titulo'   => 'Nova Meta de Vendas',
            'meta'     => null,
            'fases'    => [],
            'marcos'   => [],
            'eventos'  => $this->eventoModel->orderBy('data_inicio', 'DESC')->findAll(),
            'eventIdSelecionado' => $eventId,
            'tipoSelecionado'    => $tipo,
        ]);
    }

    public function editar($id)
    {
        $meta = $this->metaModel->find($id);
        if (!$meta) {
            return redirect()->to(site_url('metas-vendas'))->with('erro', 'Meta não encontrada.');
        }

        return view('MetasVendas/form', [
            'titulo'   => 'Editar Meta de Vendas',
            'meta'     => $meta,
            'fases'    => $this->faseModel->porMeta($meta->id),
            'marcos'   => $this->marcoModel->porMeta($meta->id),
            'eventos'  => $this->eventoModel->orderBy('data_inicio', 'DESC')->findAll(),
            'eventIdSelecionado' => $meta->event_id,
            'tipoSelecionado'    => $meta->tipo,
        ]);
    }

    public function salvar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno = ['token' => csrf_hash()];
        $post    = $this->request->getPost();

        $metaTotal = (float) str_replace(['.', ','], ['', '.'], $post['meta_total'] ?? '0');

        $dados = [
            'event_id'   => (int) ($post['event_id'] ?? 0),
            'tipo'       => in_array($post['tipo'] ?? '', ['comercial', 'ingressos']) ? $post['tipo'] : 'ingressos',
            'nome'       => trim((string) ($post['nome'] ?? '')) ?: null,
            'meta_total' => $metaTotal,
            'ativo'      => isset($post['ativo']) ? 1 : 0,
        ];

        if (!empty($post['id'])) {
            $dados['id'] = (int) $post['id'];
        }

        if (!$this->metaModel->save($dados)) {
            $retorno['erro'] = 'Erro ao salvar meta.';
            return $this->response->setJSON($retorno);
        }

        $metaId = !empty($post['id']) ? (int) $post['id'] : $this->metaModel->getInsertID();

        // Substitui fases
        $this->faseModel->removerPorMeta($metaId);
        $fases = $post['fases'] ?? [];
        if (is_array($fases)) {
            $ordem = 0;
            foreach ($fases as $f) {
                $ini = trim((string) ($f['data_inicio'] ?? ''));
                $fim = trim((string) ($f['data_fim'] ?? ''));
                $mf  = (float) str_replace(['.', ','], ['', '.'], $f['meta_fase'] ?? '0');
                if (!$ini || !$fim) {
                    continue;
                }
                $this->faseModel->insert([
                    'meta_id'     => $metaId,
                    'nome'        => trim((string) ($f['nome'] ?? '')) ?: null,
                    'data_inicio' => $ini,
                    'data_fim'    => $fim,
                    'meta_fase'   => $mf,
                    'ordem'       => $ordem++,
                ]);
            }
        }

        // Substitui marcos
        $this->marcoModel->removerPorMeta($metaId);
        $marcos = $post['marcos'] ?? [];
        if (is_array($marcos)) {
            $ordem = 0;
            foreach ($marcos as $m) {
                $data = trim((string) ($m['data'] ?? ''));
                $fat  = (float) str_replace(['.', ','], ['', '.'], $m['faturamento_acumulado_esperado'] ?? '0');
                if (!$data) {
                    continue;
                }
                $this->marcoModel->insert([
                    'meta_id'                        => $metaId,
                    'data'                           => $data,
                    'faturamento_acumulado_esperado' => $fat,
                    'ordem'                          => $ordem++,
                ]);
            }
        }

        $retorno['sucesso']  = !empty($post['id']) ? 'Meta atualizada!' : 'Meta criada!';
        $retorno['redirect'] = site_url('metas-vendas?event_id=' . (int) $dados['event_id']);
        return $this->response->setJSON($retorno);
    }

    public function excluir()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno = ['token' => csrf_hash()];
        $id      = (int) $this->request->getPost('id');
        $meta    = $this->metaModel->find($id);

        if (!$meta) {
            $retorno['erro'] = 'Meta não encontrada.';
            return $this->response->setJSON($retorno);
        }

        $this->faseModel->removerPorMeta($id);
        $this->marcoModel->removerPorMeta($id);
        $this->metaModel->delete($id);

        $retorno['sucesso'] = 'Meta excluída.';
        return $this->response->setJSON($retorno);
    }

    public function acompanhar($id)
    {
        $meta = $this->metaModel->find($id);
        if (!$meta) {
            return redirect()->to(site_url('metas-vendas'))->with('erro', 'Meta não encontrada.');
        }

        $evento = $this->eventoModel->find($meta->event_id);
        $fases  = $this->faseModel->porMeta($meta->id);
        $marcos = $this->marcoModel->porMeta($meta->id);

        return view('MetasVendas/acompanhar', [
            'titulo'  => 'Acompanhamento — ' . ($meta->nome ?: $meta->getTipoLabel()),
            'meta'    => $meta,
            'evento'  => $evento,
            'fases'   => $fases,
            'marcos'  => $marcos,
        ]);
    }

    /**
     * Retorna JSON com dados de acompanhamento (usado pelo gráfico/indicadores via AJAX).
     */
    public function dadosAcompanhamento($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $meta = $this->metaModel->find($id);
        if (!$meta) {
            return $this->response->setJSON(['erro' => 'Meta não encontrada.']);
        }

        $fases  = $this->faseModel->porMeta($meta->id);
        $marcos = $this->marcoModel->porMeta($meta->id);

        $db = \Config\Database::connect();

        // ---- Receita realizada ----
        $realizadoTotal = 0.0;
        $realizadoDiario = []; // ['Y-m-d' => float]

        if ($meta->tipo === 'ingressos') {
            $vendasModel    = new \App\Models\VendasRealtimeModel();
            $metricas       = $vendasModel->getMetricasGerais($meta->event_id);
            $realizadoTotal = (float) ($metricas['receita_total'] ?? 0);

            $diario = $vendasModel->getEvolucaoDiaria($meta->event_id, 365);
            foreach ($diario as $row) {
                $realizadoDiario[$row['data']] = (float) ($row['receita'] ?? 0);
            }
        } else {
            // Comercial: soma valor_pago de contratos ativos
            $row = $db->query(
                "SELECT COALESCE(SUM(valor_pago), 0) as realizado
                 FROM contratos
                 WHERE event_id = ? AND deleted_at IS NULL
                   AND situacao NOT IN ('cancelado','banido')",
                [$meta->event_id]
            )->getRow();
            $realizadoTotal = (float) ($row->realizado ?? 0);

            // Breakdown diário por data de pagamento das parcelas
            $rows = $db->query(
                "SELECT DATE(cp.data_pagamento) as data, SUM(cp.valor_liquido) as valor
                 FROM contrato_parcelas cp
                 JOIN contratos c ON c.id = cp.contrato_id
                 WHERE c.event_id = ? AND c.deleted_at IS NULL
                   AND c.situacao NOT IN ('cancelado','banido')
                   AND cp.status_local = 'pago'
                   AND cp.data_pagamento IS NOT NULL
                 GROUP BY DATE(cp.data_pagamento)",
                [$meta->event_id]
            )->getResult();
            foreach ($rows as $r) {
                $realizadoDiario[$r->data] = (float) $r->valor;
            }
        }

        // ---- Determina intervalo de datas a exibir ----
        $dataMin = null;
        $dataMax = date('Y-m-d');
        foreach ($fases as $f) {
            if (!$dataMin || $f->data_inicio < $dataMin) {
                $dataMin = $f->data_inicio;
            }
            if ($f->data_fim > $dataMax) {
                $dataMax = $f->data_fim;
            }
        }
        if (!$dataMin) {
            $dataMin = date('Y-m-d');
        }

        // ---- Gera série diária ----
        $labels     = [];
        $serRealiz  = []; // acumulado realizado
        $serPlan    = []; // acumulado planejado (interpolado das fases)
        $acumReal   = 0.0;

        // Constrói mapa de planejado cumulativo por data a partir das fases
        $planPorDia = [];
        foreach ($fases as $f) {
            $numDias   = $f->getNumDias();
            $mediaDia  = $f->getMediaDia();
            $cursor    = $f->data_inicio;
            for ($d = 0; $d < $numDias; $d++) {
                $planPorDia[$cursor] = ($planPorDia[$cursor] ?? 0) + $mediaDia;
                $cursor = date('Y-m-d', strtotime($cursor . ' +1 day'));
            }
        }

        $cursor   = $dataMin;
        $acumPlan = 0.0;
        while ($cursor <= $dataMax) {
            $labels[]    = date('d/m', strtotime($cursor));
            $acumReal   += $realizadoDiario[$cursor] ?? 0;
            $acumPlan   += $planPorDia[$cursor] ?? 0;
            $serRealiz[] = round($acumReal, 2);
            $serPlan[]   = round($acumPlan, 2);
            $cursor = date('Y-m-d', strtotime($cursor . ' +1 day'));
        }

        // ---- Fase atual ----
        $hoje      = date('Y-m-d');
        $faseAtual = null;
        foreach ($fases as $f) {
            if ($hoje >= $f->data_inicio && $hoje <= $f->data_fim) {
                $faseAtual = $f;
                break;
            }
        }
        if (!$faseAtual && count($fases)) {
            $faseAtual = end($fases);
        }

        // Realizado por fase
        $fasesData = [];
        foreach ($fases as $f) {
            $realizadoFase = 0.0;
            foreach ($realizadoDiario as $data => $val) {
                if ($data >= $f->data_inicio && $data <= $f->data_fim) {
                    $realizadoFase += $val;
                }
            }
            $pct = $f->meta_fase > 0 ? min(100, round(($realizadoFase / $f->meta_fase) * 100, 1)) : 0;
            $fasesData[] = [
                'nome'          => $f->nome,
                'periodo'       => $f->getLabelPeriodo(),
                'meta_fase'     => $f->meta_fase,
                'media_dia'     => round($f->getMediaDia(), 2),
                'media_semana'  => round($f->getMediaSemana(), 2),
                'realizado'     => round($realizadoFase, 2),
                'percentual'    => $pct,
                'status'        => $hoje < $f->data_inicio ? 'futuro' : ($hoje > $f->data_fim ? 'encerrado' : 'atual'),
            ];
        }

        // Marcos com realizado acumulado
        $marcosData = [];
        foreach ($marcos as $m) {
            $acum = 0.0;
            foreach ($realizadoDiario as $data => $val) {
                if ($data <= $m->data) {
                    $acum += $val;
                }
            }
            $dif = $acum - $m->faturamento_acumulado_esperado;
            $marcosData[] = [
                'data'       => date('d/m/Y', strtotime($m->data)),
                'esperado'   => $m->faturamento_acumulado_esperado,
                'realizado'  => round($acum, 2),
                'diferenca'  => round($dif, 2),
                'status'     => $m->data > $hoje ? 'futuro' : ($dif >= 0 ? 'ok' : 'atrasado'),
            ];
        }

        // Média diária necessária a partir de hoje para bater a meta total
        $diasRestantes = 0;
        foreach ($fases as $f) {
            if ($f->data_fim >= $hoje) {
                $fim = $f->data_fim;
                $ini = $f->data_inicio > $hoje ? $f->data_inicio : $hoje;
                $diasRestantes += (int) round((strtotime($fim) - strtotime($ini)) / 86400) + 1;
            }
        }
        $restante          = max(0, $meta->meta_total - $realizadoTotal);
        $mediaNecessaria   = $diasRestantes > 0 ? round($restante / $diasRestantes, 2) : 0;

        return $this->response->setJSON([
            'meta_total'        => $meta->meta_total,
            'realizado_total'   => round($realizadoTotal, 2),
            'percentual'        => $meta->meta_total > 0 ? round(($realizadoTotal / $meta->meta_total) * 100, 1) : 0,
            'media_necessaria'  => $mediaNecessaria,
            'fase_atual'        => $faseAtual ? [
                'nome'      => $faseAtual->nome,
                'periodo'   => $faseAtual->getLabelPeriodo(),
                'meta_fase' => $faseAtual->meta_fase,
                'media_dia' => round($faseAtual->getMediaDia(), 2),
            ] : null,
            'fases'  => $fasesData,
            'marcos' => $marcosData,
            'grafico' => [
                'labels'    => $labels,
                'realizado' => $serRealiz,
                'planejado' => $serPlan,
            ],
        ]);
    }

    /**
     * Retorna a meta do dia para o evento (usada no card Receita Hoje do dashboard).
     * GET /metas-vendas/meta-hoje?event_id=X&tipo=ingressos
     */
    public function metaHoje()
    {
        $eventId = (int) $this->request->getGet('event_id');
        $tipo    = $this->request->getGet('tipo') ?: 'ingressos';
        $hoje    = date('Y-m-d');

        $meta = $this->metaModel
            ->where('event_id', $eventId)
            ->where('tipo', $tipo)
            ->where('ativo', 1)
            ->orderBy('id', 'DESC')
            ->first();

        if (!$meta) {
            return $this->response->setJSON(['tem_meta' => false]);
        }

        $fases = $this->faseModel->porMeta($meta->id);
        $faseAtual = null;
        foreach ($fases as $f) {
            if ($hoje >= $f->data_inicio && $hoje <= $f->data_fim) {
                $faseAtual = $f;
                break;
            }
        }

        if (!$faseAtual) {
            return $this->response->setJSON(['tem_meta' => false]);
        }

        return $this->response->setJSON([
            'tem_meta'   => true,
            'meta_dia'   => round($faseAtual->getMediaDia(), 2),
            'meta_total' => $meta->meta_total,
            'fase_nome'  => $faseAtual->nome ?: $faseAtual->getLabelPeriodo(),
        ]);
    }
}
