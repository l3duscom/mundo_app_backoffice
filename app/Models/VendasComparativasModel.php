<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model exclusivo para Dashboard de Comparação de Vendas
 * NÃO reutilizar em outras partes do sistema
 */
class VendasComparativasModel extends Model
{
    protected $table = 'pedidos';
    
    /**
     * Busca visão geral de um ou mais eventos
     */
    public function getVisaoGeralEventos(array $eventIds, array $status = ['CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH'], int $ticketCortesia = 608)
    {
        $eventIdsStr = implode(',', $eventIds);
        $statusStr = "'" . implode("','", $status) . "'";
        
        $sql = "
            SELECT 
                e.id AS evento_id,
                e.nome AS evento_nome,
                DATE_FORMAT(e.data_inicio, '%d/%m/%Y') AS data_evento,
                COUNT(DISTINCT p.id) AS total_pedidos,
                COUNT(i.id) AS total_ingressos,
                SUM(CASE WHEN p.status IN ({$statusStr}) THEN p.total ELSE 0 END) AS receita_total,
                MIN(p.created_at) AS primeira_venda,
                MAX(p.created_at) AS ultima_venda,
                DATEDIFF(MAX(p.created_at), MIN(p.created_at)) + 1 AS dias_vendas,
                COUNT(DISTINCT DATE(p.created_at)) AS dias_com_vendas
            FROM eventos e
            LEFT JOIN pedidos p ON e.id = p.evento_id 
                AND p.status IN ({$statusStr})
            LEFT JOIN ingressos i ON p.id = i.pedido_id 
                AND i.ticket_id <> {$ticketCortesia}
            WHERE e.id IN ({$eventIdsStr})
            GROUP BY e.id, e.nome, e.data_inicio
            ORDER BY e.id
        ";
        
        $result = $this->db->query($sql);
        return $result ? $result->getResultArray() : [];
    }
    
    /**
     * Busca evolução diária comparativa entre dois eventos
     * VERSÃO OTIMIZADA: 1 query + processamento em PHP
     */
    public function getEvolucaoDiariaComparativa(int $evento1Id, int $evento2Id, array $status = ['CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH'], int $ticketCortesia = 608)
    {
        $statusStr = "'" . implode("','", $status) . "'";
        
        // UMA query simples para buscar vendas diárias
        $sql = "
            SELECT 
                p.evento_id,
                DATE(p.created_at) AS data_venda,
                COUNT(DISTINCT p.id) AS pedidos_dia,
                COUNT(i.id) AS ingressos_dia,
                SUM(p.total) AS receita_dia
            FROM pedidos p
            LEFT JOIN ingressos i ON p.id = i.pedido_id 
                AND i.ticket_id <> {$ticketCortesia}
            WHERE p.evento_id IN ({$evento1Id}, {$evento2Id})
                AND p.status IN ({$statusStr})
            GROUP BY p.evento_id, DATE(p.created_at)
            ORDER BY p.evento_id, DATE(p.created_at)
        ";
        
        $result = $this->db->query($sql);
        if (!$result) {
            return [];
        }
        
        $vendas = $result->getResultArray();
        
        // Separar por evento
        $evento1 = [];
        $evento2 = [];
        
        foreach ($vendas as $venda) {
            if ($venda['evento_id'] == $evento1Id) {
                $evento1[] = $venda;
            } else {
                $evento2[] = $venda;
            }
        }
        
        // Calcular acumulados em PHP (muito mais rápido!)
        $acumEv1 = $this->calcularAcumulados($evento1);
        $acumEv2 = $this->calcularAcumulados($evento2);
        
        // Mesclar resultados
        $resultado = [];
        $maxDias = max(count($acumEv1), count($acumEv2));
        
        for ($i = 0; $i < $maxDias; $i++) {
            $dia = $i + 1;
            $ev1 = $acumEv1[$i] ?? null;
            $ev2 = $acumEv2[$i] ?? null;
            
            $ingressosAcumEv1 = $ev1['ingressos_acumulados'] ?? 0;
            $ingressosAcumEv2 = $ev2['ingressos_acumulados'] ?? 0;
            $receitaAcumEv1 = $ev1['receita_acumulada'] ?? 0;
            $receitaAcumEv2 = $ev2['receita_acumulada'] ?? 0;
            
            $resultado[] = [
                'dia_venda' => $dia,
                'data_evento1' => $ev1 ? date('d/m/Y', strtotime($ev1['data_venda'])) : null,
                'pedidos_dia_ev1' => $ev1['pedidos_dia'] ?? 0,
                'ingressos_dia_ev1' => $ev1['ingressos_dia'] ?? 0,
                'receita_dia_ev1' => $ev1['receita_dia'] ?? 0,
                'pedidos_acum_ev1' => $ev1['pedidos_acumulados'] ?? 0,
                'ingressos_acum_ev1' => $ingressosAcumEv1,
                'receita_acum_ev1' => $receitaAcumEv1,
                'data_evento2' => $ev2 ? date('d/m/Y', strtotime($ev2['data_venda'])) : null,
                'pedidos_dia_ev2' => $ev2['pedidos_dia'] ?? 0,
                'ingressos_dia_ev2' => $ev2['ingressos_dia'] ?? 0,
                'receita_dia_ev2' => $ev2['receita_dia'] ?? 0,
                'pedidos_acum_ev2' => $ev2['pedidos_acumulados'] ?? 0,
                'ingressos_acum_ev2' => $ingressosAcumEv2,
                'receita_acum_ev2' => $receitaAcumEv2,
                'diff_ingressos' => $ingressosAcumEv1 - $ingressosAcumEv2,
                'diff_receita' => $receitaAcumEv1 - $receitaAcumEv2,
                'perc_evolucao_ingressos' => $ingressosAcumEv2 > 0 ? round((($ingressosAcumEv1 / $ingressosAcumEv2) * 100) - 100, 2) : null,
                'perc_evolucao_receita' => $receitaAcumEv2 > 0 ? round((($receitaAcumEv1 / $receitaAcumEv2) * 100) - 100, 2) : null
            ];
        }
        
        return $resultado;
    }
    
    /**
     * Calcula acumulados em PHP (helper privado)
     */
    private function calcularAcumulados(array $vendas): array
    {
        $acumulados = [];
        $pedidosAcum = 0;
        $ingressosAcum = 0;
        $receitaAcum = 0;
        
        foreach ($vendas as $venda) {
            $pedidosAcum += $venda['pedidos_dia'];
            $ingressosAcum += $venda['ingressos_dia'];
            $receitaAcum += $venda['receita_dia'];
            
            $acumulados[] = [
                'data_venda' => $venda['data_venda'],
                'pedidos_dia' => $venda['pedidos_dia'],
                'ingressos_dia' => $venda['ingressos_dia'],
                'receita_dia' => $venda['receita_dia'],
                'pedidos_acumulados' => $pedidosAcum,
                'ingressos_acumulados' => $ingressosAcum,
                'receita_acumulada' => $receitaAcum
            ];
        }
        
        return $acumulados;
    }
    
    /**
     * Busca comparação por períodos (semanas/meses)
     * VERSÃO OTIMIZADA: Query simples + processamento em PHP
     */
    public function getComparacaoPorPeriodos(int $evento1Id, int $evento2Id, array $status = ['CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH'], int $ticketCortesia = 608)
    {
        $statusStr = "'" . implode("','", $status) . "'";
        
        // Buscar primeira venda de cada evento
        $sqlPrimeiraVenda = "
            SELECT evento_id, MIN(created_at) AS primeira_venda
            FROM pedidos
            WHERE evento_id IN ({$evento1Id}, {$evento2Id})
                AND status IN ({$statusStr})
            GROUP BY evento_id
        ";
        
        $result = $this->db->query($sqlPrimeiraVenda);
        if (!$result) {
            return [];
        }
        
        $primeiraVenda = [];
        foreach ($result->getResultArray() as $row) {
            $primeiraVenda[$row['evento_id']] = $row['primeira_venda'];
        }
        
        // Buscar dados de vendas
        $sql = "
            SELECT 
                p.evento_id,
                DATE(p.created_at) AS created_at,
                COUNT(DISTINCT p.id) AS pedidos,
                COUNT(i.id) AS ingressos,
                SUM(p.total) AS receita
            FROM pedidos p
            LEFT JOIN ingressos i ON p.id = i.pedido_id 
                AND i.ticket_id <> {$ticketCortesia}
            WHERE p.evento_id IN ({$evento1Id}, {$evento2Id})
                AND p.status IN ({$statusStr})
            GROUP BY p.evento_id, DATE(p.created_at)
        ";
        
        $result = $this->db->query($sql);
        if (!$result) {
            return [];
        }
        
        // Agrupar por períodos em PHP
        $periodos = [
            '1. Primeira Semana' => ['pedidos_ev1' => 0, 'ingressos_ev1' => 0, 'receita_ev1' => 0, 'pedidos_ev2' => 0, 'ingressos_ev2' => 0, 'receita_ev2' => 0],
            '2. Segunda Semana' => ['pedidos_ev1' => 0, 'ingressos_ev1' => 0, 'receita_ev1' => 0, 'pedidos_ev2' => 0, 'ingressos_ev2' => 0, 'receita_ev2' => 0],
            '3. Terceira Semana' => ['pedidos_ev1' => 0, 'ingressos_ev1' => 0, 'receita_ev1' => 0, 'pedidos_ev2' => 0, 'ingressos_ev2' => 0, 'receita_ev2' => 0],
            '4. Primeiro Mês' => ['pedidos_ev1' => 0, 'ingressos_ev1' => 0, 'receita_ev1' => 0, 'pedidos_ev2' => 0, 'ingressos_ev2' => 0, 'receita_ev2' => 0],
            '5. Segundo Mês' => ['pedidos_ev1' => 0, 'ingressos_ev1' => 0, 'receita_ev1' => 0, 'pedidos_ev2' => 0, 'ingressos_ev2' => 0, 'receita_ev2' => 0],
            '6. Demais Períodos' => ['pedidos_ev1' => 0, 'ingressos_ev1' => 0, 'receita_ev1' => 0, 'pedidos_ev2' => 0, 'ingressos_ev2' => 0, 'receita_ev2' => 0]
        ];
        
        foreach ($result->getResultArray() as $row) {
            $eventoId = $row['evento_id'];
            $primeiraVendaEvento = $primeiraVenda[$eventoId] ?? null;
            
            if (!$primeiraVendaEvento) continue;
            
            $diffDias = (strtotime($row['created_at']) - strtotime($primeiraVendaEvento)) / 86400;
            
            if ($diffDias <= 7) {
                $periodo = '1. Primeira Semana';
            } elseif ($diffDias <= 14) {
                $periodo = '2. Segunda Semana';
            } elseif ($diffDias <= 21) {
                $periodo = '3. Terceira Semana';
            } elseif ($diffDias <= 30) {
                $periodo = '4. Primeiro Mês';
            } elseif ($diffDias <= 60) {
                $periodo = '5. Segundo Mês';
            } else {
                $periodo = '6. Demais Períodos';
            }
            
            $sufixo = $eventoId == $evento1Id ? '_ev1' : '_ev2';
            $periodos[$periodo]['pedidos' . $sufixo] += $row['pedidos'];
            $periodos[$periodo]['ingressos' . $sufixo] += $row['ingressos'];
            $periodos[$periodo]['receita' . $sufixo] += $row['receita'];
        }
        
        // Formatar resultado
        $resultado = [];
        foreach ($periodos as $nome => $dados) {
            $resultado[] = [
                'periodo' => $nome,
                'pedidos_ev1' => $dados['pedidos_ev1'],
                'ingressos_ev1' => $dados['ingressos_ev1'],
                'receita_ev1' => $dados['receita_ev1'],
                'pedidos_ev2' => $dados['pedidos_ev2'],
                'ingressos_ev2' => $dados['ingressos_ev2'],
                'receita_ev2' => $dados['receita_ev2'],
                'diff_ingressos' => $dados['ingressos_ev1'] - $dados['ingressos_ev2'],
                'diff_receita' => $dados['receita_ev1'] - $dados['receita_ev2']
            ];
        }
        
        return $resultado;
    }
    
    /**
     * Busca resumo executivo comparativo
     */
    public function getResumoExecutivo(int $evento1Id, int $evento2Id, array $status = ['CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH'], int $ticketCortesia = 608)
    {
        $statusStr = "'" . implode("','", $status) . "'";
        
        $sql = "
            SELECT 
                'RESUMO COMPARATIVO' AS tipo,
                {$evento1Id} AS evento1,
                {$evento2Id} AS evento2,
                (SELECT COUNT(*) 
                 FROM ingressos i 
                 INNER JOIN pedidos p ON i.pedido_id = p.id 
                 WHERE p.evento_id = {$evento1Id} 
                     AND p.status IN ({$statusStr})
                     AND i.ticket_id <> {$ticketCortesia}) AS total_ingressos_ev1,
                (SELECT COUNT(*) 
                 FROM ingressos i 
                 INNER JOIN pedidos p ON i.pedido_id = p.id 
                 WHERE p.evento_id = {$evento2Id} 
                     AND p.status IN ({$statusStr})
                     AND i.ticket_id <> {$ticketCortesia}) AS total_ingressos_ev2,
                ((SELECT COUNT(*) 
                  FROM ingressos i 
                  INNER JOIN pedidos p ON i.pedido_id = p.id 
                  WHERE p.evento_id = {$evento1Id} 
                      AND p.status IN ({$statusStr})
                      AND i.ticket_id <> {$ticketCortesia}) -
                 (SELECT COUNT(*) 
                  FROM ingressos i 
                  INNER JOIN pedidos p ON i.pedido_id = p.id 
                  WHERE p.evento_id = {$evento2Id} 
                      AND p.status IN ({$statusStr})
                      AND i.ticket_id <> {$ticketCortesia})) AS diff_ingressos,
                ROUND((
                    (SELECT COUNT(*) 
                     FROM ingressos i 
                     INNER JOIN pedidos p ON i.pedido_id = p.id 
                     WHERE p.evento_id = {$evento1Id} 
                         AND p.status IN ({$statusStr})
                         AND i.ticket_id <> {$ticketCortesia}) /
                    NULLIF((SELECT COUNT(*) 
                            FROM ingressos i 
                            INNER JOIN pedidos p ON i.pedido_id = p.id 
                            WHERE p.evento_id = {$evento2Id} 
                                AND p.status IN ({$statusStr})
                                AND i.ticket_id <> {$ticketCortesia}), 0)
                    * 100
                ) - 100, 2) AS perc_evolucao_ingressos,
                (SELECT SUM(p.total) 
                 FROM pedidos p 
                 WHERE p.evento_id = {$evento1Id} 
                     AND p.status IN ({$statusStr})) AS receita_ev1,
                (SELECT SUM(p.total) 
                 FROM pedidos p 
                 WHERE p.evento_id = {$evento2Id} 
                     AND p.status IN ({$statusStr})) AS receita_ev2,
                ((SELECT SUM(p.total) 
                  FROM pedidos p 
                  WHERE p.evento_id = {$evento1Id} 
                      AND p.status IN ({$statusStr})) -
                 (SELECT SUM(p.total) 
                  FROM pedidos p 
                  WHERE p.evento_id = {$evento2Id} 
                      AND p.status IN ({$statusStr}))) AS diff_receita,
                ROUND((
                    (SELECT SUM(p.total) 
                     FROM pedidos p 
                     WHERE p.evento_id = {$evento1Id} 
                         AND p.status IN ({$statusStr})) /
                    NULLIF((SELECT SUM(p.total) 
                            FROM pedidos p 
                            WHERE p.evento_id = {$evento2Id} 
                                AND p.status IN ({$statusStr})), 0)
                    * 100
                ) - 100, 2) AS perc_evolucao_receita
        ";
        
        $result = $this->db->query($sql);
        return $result ? $result->getRowArray() : [];
    }
    
    /**
     * Lista todos os eventos disponíveis para seleção
     */
    public function getEventosDisponiveis()
    {
        $sql = "
            SELECT 
                e.id,
                e.nome,
                DATE_FORMAT(e.data_inicio, '%d/%m/%Y') AS data_inicio,
                COUNT(DISTINCT p.id) AS total_pedidos
            FROM eventos e
            LEFT JOIN pedidos p ON e.id = p.evento_id
            GROUP BY e.id, e.nome, e.data_inicio
            HAVING total_pedidos > 0
            ORDER BY e.data_inicio DESC
        ";
        
        $result = $this->db->query($sql);
        return $result ? $result->getResultArray() : [];
    }
}

