<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class DashboardVendas extends BaseController
{
    private $vendasModel;
    private $eventoModel;
    protected $db;
    
    public function __construct()
    {
        $this->vendasModel = new \App\Models\VendasRealtimeModel();
        $this->eventoModel = new \App\Models\EventoModel();
        $this->db = \Config\Database::connect();
    }
    
    /**
     * Página principal do dashboard
     */
    public function index()
    {
        if (!$this->usuarioLogado()->temPermissaoPara('editar-clientes')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }
        
        // Verificar se há evento selecionado no contexto
        $event_id = session()->get('event_id');
        
        if (!$event_id) {
            return redirect()->to('/')->with('atencao', 'Selecione um evento primeiro.');
        }
        
        $evento_selecionado = $this->eventoModel->find($event_id);
        
        if (!$evento_selecionado) {
            return redirect()->to('/')->with('erro', 'Evento não encontrado.');
        }
        
        $data = [
            'titulo' => 'Dashboard de Vendas em Tempo Real',
            'evento_id' => $event_id,
            'evento' => $evento_selecionado,
        ];
        
        return view('Dashboard/vendas_realtime', $data);
    }
    
    /**
     * Debug - Ver dados brutos
     */
    public function debugDados()
    {
        $event_id = $this->request->getGet('evento_id') ?? session()->get('event_id');
        
        try {
            $vendas_recentes = $this->vendasModel->getVendasRecentes($event_id, 20);
            $vendas_por_metodo = $this->vendasModel->getVendasPorMetodo($event_id);
            
            // Log detalhado
            log_message('debug', 'Vendas por método: ' . json_encode($vendas_por_metodo));
            log_message('debug', 'Vendas recentes: ' . json_encode($vendas_recentes));
            
            return $this->response->setJSON([
                'success' => true,
                'event_id' => $event_id,
                'vendas_recentes' => $vendas_recentes,
                'vendas_recentes_count' => count($vendas_recentes),
                'vendas_por_metodo' => $vendas_por_metodo,
                'vendas_por_metodo_count' => count($vendas_por_metodo),
                'vendas_por_metodo_raw' => $vendas_por_metodo
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Teste simples - apenas métricas gerais
     */
    public function testeSimples()
    {
        $event_id = $this->request->getGet('evento_id') ?? session()->get('event_id');
        
        try {
            $result = $this->db->table('pedidos')
                ->select('COUNT(*) as total')
                ->where('evento_id', $event_id)
                ->whereIn('status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
                ->get()
                ->getRowArray();
                
            return $this->response->setJSON([
                'success' => true,
                'teste' => 'OK',
                'event_id' => $event_id,
                'result' => $result
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * API: Retorna dados para o dashboard
     */
    public function getDados()
    {
        // Limpar qualquer output anterior
        if (ob_get_level()) {
            ob_clean();
        }
        
        if (!$this->usuarioLogado()->temPermissaoPara('editar-clientes')) {
            return $this->response->setJSON(['error' => 'Sem permissão'])->setStatusCode(403);
        }
        
        $event_id = $this->request->getGet('evento_id');
        $periodo = $this->request->getGet('periodo') ?? 30; // dias
        
        if (!$event_id) {
            return $this->response->setJSON(['error' => 'evento_id é obrigatório'])->setStatusCode(400);
        }
        
        try {
            $dados = [];
            
            try {
                log_message('info', 'Buscando métricas gerais...');
                $dados['metricas_gerais'] = $this->vendasModel->getMetricasGerais($event_id);
            } catch (\Exception $e) {
                log_message('error', 'Erro em getMetricasGerais: ' . $e->getMessage());
                $dados['metricas_gerais'] = [];
            }
            
            try {
                log_message('info', 'Buscando evolução diária...');
                $dados['evolucao_diaria'] = $this->vendasModel->getEvolucaoDiaria($event_id, $periodo);
            } catch (\Exception $e) {
                log_message('error', 'Erro em getEvolucaoDiaria: ' . $e->getMessage());
                $dados['evolucao_diaria'] = [];
            }
            
            try {
                log_message('info', 'Buscando vendas por hora...');
                $dados['vendas_por_hora'] = $this->vendasModel->getVendasPorHora($event_id);
            } catch (\Exception $e) {
                log_message('error', 'Erro em getVendasPorHora: ' . $e->getMessage());
                $dados['vendas_por_hora'] = [];
            }
            
            try {
                log_message('info', 'Buscando top ingressos...');
                $dados['top_ingressos'] = $this->vendasModel->getTopIngressos($event_id, 999);
            } catch (\Exception $e) {
                log_message('error', 'Erro em getTopIngressos: ' . $e->getMessage());
                $dados['top_ingressos'] = [];
            }
            
            try {
                log_message('info', 'Buscando vendas por método...');
                $dados['vendas_por_metodo'] = $this->vendasModel->getVendasPorMetodo($event_id);
            } catch (\Exception $e) {
                log_message('error', 'Erro em getVendasPorMetodo: ' . $e->getMessage());
                $dados['vendas_por_metodo'] = [];
            }
            
            try {
                log_message('info', 'Buscando vendas recentes...');
                $dados['vendas_recentes'] = $this->vendasModel->getVendasRecentes($event_id, 20);
            } catch (\Exception $e) {
                log_message('error', 'Erro em getVendasRecentes: ' . $e->getMessage());
                $dados['vendas_recentes'] = [];
            }
            
            try {
                log_message('info', 'Buscando taxa de conversão...');
                $dados['taxa_conversao'] = $this->vendasModel->getTaxaConversao($event_id);
            } catch (\Exception $e) {
                log_message('error', 'Erro em getTaxaConversao: ' . $e->getMessage());
                $dados['taxa_conversao'] = [];
            }
            
            try {
                log_message('info', 'Buscando comparação de período...');
                $dados['comparacao_periodo'] = $this->vendasModel->getComparacaoPeriodo($event_id, 7);
            } catch (\Exception $e) {
                log_message('error', 'Erro em getComparacaoPeriodo: ' . $e->getMessage());
                $dados['comparacao_periodo'] = [];
            }
            
            try {
                log_message('info', 'Buscando vendas por dia da semana...');
                $dados['vendas_dia_semana'] = $this->vendasModel->getVendasPorDiaSemana($event_id);
            } catch (\Exception $e) {
                log_message('error', 'Erro em getVendasPorDiaSemana: ' . $e->getMessage());
                $dados['vendas_dia_semana'] = [];
            }
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $dados,
                'timestamp' => time()
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Erro geral ao buscar dados do dashboard: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ])->setStatusCode(500);
        }
    }
}

