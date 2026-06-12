<?php

namespace App\Controllers;

class Campanhas extends BaseController
{
    private $campanhaModel;
    private $eventoModel;

    public function __construct()
    {
        $this->campanhaModel = new \App\Models\CampanhaModel();
        $this->eventoModel   = new \App\Models\EventoModel();
    }

    public function index()
    {
        if (! $this->usuarioLogado()->is_admin) {
            return redirect()->back()->with('atencao', 'Você não tem permissão para acessar esse menu.');
        }

        $evento = evento_selecionado_com_validacao();
        if (! $evento) {
            return redirect()->to(site_url('/'))->with('atencao', 'Selecione um evento primeiro.');
        }

        $eventId = (int) $evento->id;

        $data = [
            'titulo'    => 'Análise de Campanhas',
            'evento'    => $evento,
            'sources'   => $this->campanhaModel->valoresDistintos('utm_source', $eventId),
            'mediums'   => $this->campanhaModel->valoresDistintos('utm_medium', $eventId),
            'campaigns' => $this->campanhaModel->valoresDistintos('utm_campaign', $eventId),
        ];

        return view('Campanhas/index', $data);
    }

    /**
     * Endpoint AJAX: retorna todos os dados (cards, agregações, gráfico, lista).
     */
    public function dados()
    {
        if (! $this->usuarioLogado()->is_admin) {
            return $this->response->setJSON(['erro' => 'Sem permissão.']);
        }

        $evento = evento_selecionado_com_validacao();
        if (! $evento) {
            return $this->response->setJSON(['erro' => 'Selecione um evento primeiro.']);
        }

        $eventId      = (int) $evento->id;
        $dataInicial  = $this->request->getGet('data_inicial') ?: null;
        $dataFinal    = $this->request->getGet('data_final') ?: null;

        $filtros = [
            'utm_source'   => trim((string) $this->request->getGet('utm_source')),
            'utm_medium'   => trim((string) $this->request->getGet('utm_medium')),
            'utm_campaign' => trim((string) $this->request->getGet('utm_campaign')),
        ];
        $filtros = array_filter($filtros, fn ($v) => $v !== '');

        $metricas    = $this->campanhaModel->metricasGerais($eventId, $dataInicial, $dataFinal);
        $porSource   = $this->campanhaModel->agregarPor('utm_source',   $eventId, $dataInicial, $dataFinal, $filtros);
        $porMedium   = $this->campanhaModel->agregarPor('utm_medium',   $eventId, $dataInicial, $dataFinal, $filtros);
        $porCampaign = $this->campanhaModel->agregarPor('utm_campaign', $eventId, $dataInicial, $dataFinal, $filtros);
        $evolucao    = $this->campanhaModel->evolucaoDiariaPorSource($eventId, $dataInicial, $dataFinal, $filtros);
        $pedidos     = $this->campanhaModel->listaPedidos($eventId, $dataInicial, $dataFinal, $filtros);

        return $this->response->setJSON([
            'metricas'     => $metricas,
            'por_source'   => $porSource,
            'por_medium'   => $porMedium,
            'por_campaign' => $porCampaign,
            'evolucao'     => $evolucao,
            'pedidos'      => array_map(function ($p) {
                return [
                    'id'           => (int) $p['id'],
                    'cod_pedido'   => $p['cod_pedido'],
                    'cliente'      => $p['cliente'],
                    'valor_total'  => (float) $p['valor_total'],
                    'created_at'   => $p['created_at'],
                    'utm_source'   => $p['utm_source'] ?: '(direto)',
                    'utm_medium'   => $p['utm_medium']   ?: '-',
                    'utm_campaign' => $p['utm_campaign'] ?: '-',
                    'utm_content'  => $p['utm_content']  ?: '-',
                    'utm_term'     => $p['utm_term']     ?: '-',
                ];
            }, $pedidos),
        ]);
    }
}
