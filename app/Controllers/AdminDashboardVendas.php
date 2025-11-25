<?php

namespace App\Controllers;

use App\Models\VendasComparativasModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Controller exclusivo para Dashboard de Vendas Administrativo
 * NÃO reutilizar em outras partes do sistema
 * Requer autenticação de ADMIN
 */
class AdminDashboardVendas extends BaseController
{
    protected $vendasModel;
    
    public function __construct()
    {
        $this->vendasModel = new VendasComparativasModel();
        
        // Garante que o helper de autenticação está carregado
        helper('autenticacao');
    }
    
    /**
     * Exibe a página principal do dashboard
     */
    public function index()
    {
        // Debug: Log para verificar o acesso
        log_message('info', 'Tentativa de acesso ao Dashboard de Vendas');
        
        // Verificar se o usuário está logado
        $usuario = usuario_logado();
        
        if ($usuario === null) {
            log_message('warning', 'Dashboard de Vendas: Usuário não está logado');
            return redirect()->to(site_url('login'))->with('info', 'Por favor, faça login primeiro.');
        }
        
        // Log do status do usuário
        log_message('info', 'Dashboard de Vendas: Usuário ID ' . $usuario->id . ' - is_admin: ' . ($usuario->is_admin ? 'true' : 'false'));
        
        // Verificar se usuário é admin
        if (!$this->isAdmin()) {
            log_message('warning', 'Dashboard de Vendas: Acesso negado para usuário ID ' . $usuario->id);
            return redirect()->to('/')->with('error', 'Acesso negado. Esta área é exclusiva para administradores.');
        }
        
        // Buscar eventos disponíveis para seleção
        $eventos = $this->vendasModel->getEventosDisponiveis();
        
        log_message('info', 'Dashboard de Vendas: Acesso permitido para usuário ID ' . $usuario->id);
        
        $data = [
            'title' => 'Dashboard de Vendas - Administrativo',
            'titulo' => 'Dashboard de Vendas',
            'eventos' => $eventos
        ];
        
        return view('admin/dashboard_vendas', $data);
    }
    
    /**
     * API: Retorna dados de comparação entre dois eventos
     */
    public function getDadosComparativos()
    {
        // Verificar se usuário é admin
        if (!$this->isAdmin()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Acesso negado'
            ])->setStatusCode(ResponseInterface::HTTP_FORBIDDEN);
        }
        
        $evento1Id = (int) $this->request->getGet('evento1_id');
        $evento2Id = (int) $this->request->getGet('evento2_id');
        
        if (!$evento1Id || !$evento2Id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'IDs dos eventos são obrigatórios'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }
        
        try {
            // Buscar todos os dados
            $visaoGeral = $this->vendasModel->getVisaoGeralEventos([$evento1Id, $evento2Id]);
            $evolucaoDiaria = $this->vendasModel->getEvolucaoDiariaComparativa($evento1Id, $evento2Id);
            $comparacaoPeriodos = $this->vendasModel->getComparacaoPorPeriodos($evento1Id, $evento2Id);
            $resumoExecutivo = $this->vendasModel->getResumoExecutivo($evento1Id, $evento2Id);
            
            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'visao_geral' => $visaoGeral,
                    'evolucao_diaria' => $evolucaoDiaria,
                    'comparacao_periodos' => $comparacaoPeriodos,
                    'resumo_executivo' => $resumoExecutivo
                ]
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Erro ao buscar dados comparativos: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erro ao buscar dados: ' . $e->getMessage()
            ])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * API: Exporta dados em CSV
     */
    public function exportarCSV()
    {
        // Verificar se usuário é admin
        if (!$this->isAdmin()) {
            return redirect()->to('/')->with('error', 'Acesso negado');
        }
        
        $evento1Id = (int) $this->request->getGet('evento1_id');
        $evento2Id = (int) $this->request->getGet('evento2_id');
        
        if (!$evento1Id || !$evento2Id) {
            return redirect()->back()->with('error', 'IDs dos eventos são obrigatórios');
        }
        
        try {
            $evolucaoDiaria = $this->vendasModel->getEvolucaoDiariaComparativa($evento1Id, $evento2Id);
            
            // Gerar CSV
            $filename = 'comparacao_vendas_' . $evento1Id . '_vs_' . $evento2Id . '_' . date('Y-m-d_His') . '.csv';
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $filename);
            
            $output = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Cabeçalho
            fputcsv($output, [
                'Dia',
                'Data Evento 1',
                'Ingressos Dia Ev1',
                'Receita Dia Ev1',
                'Ingressos Acum Ev1',
                'Receita Acum Ev1',
                'Data Evento 2',
                'Ingressos Dia Ev2',
                'Receita Dia Ev2',
                'Ingressos Acum Ev2',
                'Receita Acum Ev2',
                'Diff Ingressos',
                'Diff Receita',
                '% Evolução Ingressos',
                '% Evolução Receita'
            ]);
            
            // Dados
            foreach ($evolucaoDiaria as $row) {
                fputcsv($output, [
                    $row['dia_venda'],
                    $row['data_evento1'],
                    $row['ingressos_dia_ev1'],
                    number_format($row['receita_dia_ev1'], 2, ',', '.'),
                    $row['ingressos_acum_ev1'],
                    number_format($row['receita_acum_ev1'], 2, ',', '.'),
                    $row['data_evento2'] ?: '-',
                    $row['ingressos_dia_ev2'],
                    number_format($row['receita_dia_ev2'], 2, ',', '.'),
                    $row['ingressos_acum_ev2'],
                    number_format($row['receita_acum_ev2'], 2, ',', '.'),
                    $row['diff_ingressos'],
                    number_format($row['diff_receita'], 2, ',', '.'),
                    $row['perc_evolucao_ingressos'] ? $row['perc_evolucao_ingressos'] . '%' : '-',
                    $row['perc_evolucao_receita'] ? $row['perc_evolucao_receita'] . '%' : '-'
                ]);
            }
            
            fclose($output);
            exit;
            
        } catch (\Exception $e) {
            log_message('error', 'Erro ao exportar CSV: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao exportar dados');
        }
    }
    
    /**
     * Verifica se o usuário logado é admin
     */
    private function isAdmin(): bool
    {
        // Verificar se usuário está logado
        if (!function_exists('usuario_logado')) {
            return false;
        }
        
        $usuario = usuario_logado();
        
        // Verificar se o usuário existe e se é admin
        if ($usuario === null) {
            return false;
        }
        
        // O atributo is_admin é definido pela biblioteca Autenticacao
        // verificando se o usuário está no grupo_id = 1 (admin)
        return isset($usuario->is_admin) && $usuario->is_admin === true;
    }
    
    /**
     * MÉTODO DE DEBUG - REMOVER EM PRODUÇÃO
     * Exibe informações do usuário logado para diagnóstico
     */
    public function debugUsuario()
    {
        $usuario = usuario_logado();
        
        if ($usuario === null) {
            echo "<h1>Nenhum usuário logado</h1>";
            echo "<p>Faça login primeiro em: <a href='" . site_url('login') . "'>Login</a></p>";
            exit;
        }
        
        echo "<h1>Informações do Usuário Logado</h1>";
        echo "<pre>";
        echo "ID: " . $usuario->id . "\n";
        echo "Nome: " . $usuario->nome . "\n";
        echo "Email: " . $usuario->email . "\n";
        echo "Ativo: " . ($usuario->ativo ? 'Sim' : 'Não') . "\n";
        echo "is_admin: " . (isset($usuario->is_admin) ? ($usuario->is_admin ? 'true' : 'false') : 'não definido') . "\n";
        echo "is_cliente: " . (isset($usuario->is_cliente) ? ($usuario->is_cliente ? 'true' : 'false') : 'não definido') . "\n";
        echo "\n--- OBJETO COMPLETO ---\n";
        print_r($usuario);
        echo "</pre>";
        
        echo "<h2>Verificação no Banco de Dados</h2>";
        
        // Verificar grupo do usuário
        $db = \Config\Database::connect();
        $query = $db->query("
            SELECT 
                u.id AS usuario_id,
                u.nome,
                u.email,
                g.id AS grupo_id,
                g.nome AS grupo_nome
            FROM usuarios u
            LEFT JOIN grupos_usuarios gu ON u.id = gu.usuario_id
            LEFT JOIN grupos g ON gu.grupo_id = g.id
            WHERE u.id = ?
        ", [$usuario->id]);
        
        $grupos = $query->getResultArray();
        
        echo "<pre>";
        echo "Grupos do usuário:\n";
        print_r($grupos);
        echo "</pre>";
        
        echo "<p><strong>Para ser admin, você deve estar no grupo_id = 1</strong></p>";
        
        if (empty($grupos)) {
            echo "<p style='color: red;'>❌ PROBLEMA: Usuário não está em nenhum grupo!</p>";
            echo "<p>Execute o SQL: <code>INSERT INTO grupos_usuarios (grupo_id, usuario_id, created_at, updated_at) VALUES (1, {$usuario->id}, NOW(), NOW());</code></p>";
        } else {
            $eAdmin = false;
            foreach ($grupos as $grupo) {
                if ($grupo['grupo_id'] == 1) {
                    $eAdmin = true;
                    break;
                }
            }
            
            if ($eAdmin) {
                echo "<p style='color: green;'>✅ Usuário ESTÁ no grupo admin (grupo_id = 1)</p>";
                echo "<p><strong>Se ainda está dando erro, faça LOGOUT e LOGIN novamente!</strong></p>";
            } else {
                echo "<p style='color: red;'>❌ Usuário NÃO está no grupo admin (grupo_id = 1)</p>";
                echo "<p>Execute o SQL: <code>INSERT INTO grupos_usuarios (grupo_id, usuario_id, created_at, updated_at) VALUES (1, {$usuario->id}, NOW(), NOW());</code></p>";
            }
        }
        
        echo "<hr>";
        echo "<p><a href='" . site_url('admin-dashboard-vendas') . "'>Tentar acessar Dashboard de Vendas</a></p>";
        echo "<p><a href='" . site_url('logout') . "'>Fazer Logout</a></p>";
    }
}

