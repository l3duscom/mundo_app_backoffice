<?php
/**
 * TESTE DIRETO DA API - SEM CODEIGNITER
 * Este arquivo testa diretamente se conseguimos nos conectar ao banco
 * e executar as queries do dashboard.
 * 
 * REMOVER APÓS DEBUG!
 */

// Forçar JSON sempre
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Não mostrar erros nativos, só nosso JSON

// Capturar todos os erros
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

try {
    // Incluir configuração do banco de dados
    $dbConfig = require __DIR__ . '/../app/Config/Database.php';
    
    // Pegar configuração do grupo default
    $config = $dbConfig->default;
    
    // Conectar ao banco
    $mysqli = new mysqli(
        $config['hostname'],
        $config['username'],
        $config['password'],
        $config['database']
    );
    
    if ($mysqli->connect_error) {
        throw new Exception('Erro de conexão: ' . $mysqli->connect_error);
    }
    
    $mysqli->set_charset('utf8mb4');
    
    // Pegar IDs dos eventos
    $evento1_id = isset($_GET['evento1_id']) ? (int)$_GET['evento1_id'] : 17;
    $evento2_id = isset($_GET['evento2_id']) ? (int)$_GET['evento2_id'] : 19;
    
    $results = [
        'success' => true,
        'message' => 'Teste direto da API',
        'config' => [
            'hostname' => $config['hostname'],
            'database' => $config['database'],
            'evento1_id' => $evento1_id,
            'evento2_id' => $evento2_id
        ],
        'tests' => []
    ];
    
    // TESTE 1: Verificar se eventos existem
    $sql = "SELECT id, nome FROM eventos WHERE id IN (?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ii', $evento1_id, $evento2_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $eventos = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    $results['tests']['eventos_existem'] = [
        'status' => count($eventos) > 0 ? 'OK' : 'ERRO',
        'count' => count($eventos),
        'data' => $eventos
    ];
    
    // TESTE 2: Verificar pedidos
    $sql = "SELECT COUNT(*) as total FROM pedidos WHERE evento_id IN (?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ii', $evento1_id, $evento2_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $pedidos = $result->fetch_assoc();
    $stmt->close();
    
    $results['tests']['pedidos_existem'] = [
        'status' => $pedidos['total'] > 0 ? 'OK' : 'AVISO',
        'total' => $pedidos['total']
    ];
    
    // TESTE 3: Testar query de visão geral (simplificada)
    $sql = "
        SELECT 
            e.id AS evento_id,
            e.nome AS evento_nome,
            COUNT(DISTINCT p.id) AS total_pedidos
        FROM eventos e
        LEFT JOIN pedidos p ON e.id = p.evento_id 
        WHERE e.id IN (?, ?)
        GROUP BY e.id, e.nome
    ";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ii', $evento1_id, $evento2_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $visao = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    $results['tests']['query_visao_geral'] = [
        'status' => count($visao) > 0 ? 'OK' : 'ERRO',
        'data' => $visao
    ];
    
    // TESTE 4: Verificar se MySQL suporta CTEs
    try {
        $sql = "WITH teste AS (SELECT 1 as num) SELECT * FROM teste";
        $result = $mysqli->query($sql);
        if ($result) {
            $result->close();
            $results['tests']['mysql_suporta_cte'] = [
                'status' => 'OK',
                'message' => 'MySQL suporta CTEs (WITH)'
            ];
        }
    } catch (Exception $e) {
        $results['tests']['mysql_suporta_cte'] = [
            'status' => 'ERRO',
            'message' => 'MySQL NÃO suporta CTEs. Precisa MySQL 8.0+',
            'error' => $e->getMessage()
        ];
    }
    
    // TESTE 5: Versão do MySQL
    $version = $mysqli->get_server_info();
    $results['mysql_version'] = $version;
    
    $mysqli->close();
    
    echo json_encode($results, JSON_PRETTY_PRINT);
    
} catch (Throwable $e) {
    $error = [
        'success' => false,
        'error' => 'Erro fatal',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => array_slice(explode("\n", $e->getTraceAsString()), 0, 5)
    ];
    
    http_response_code(500);
    echo json_encode($error, JSON_PRETTY_PRINT);
}
?>

