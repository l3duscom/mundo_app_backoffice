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
        
        return $this->db->query($sql)->getResultArray();
    }
    
    /**
     * Busca evolução diária comparativa entre dois eventos
     */
    public function getEvolucaoDiariaComparativa(int $evento1Id, int $evento2Id, array $status = ['CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH'], int $ticketCortesia = 608)
    {
        $statusStr = "'" . implode("','", $status) . "'";
        
        // Vendas diárias
        $sql = "
            WITH vendas_diarias AS (
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
            ),
            vendas_acumuladas AS (
                SELECT 
                    vd.evento_id,
                    vd.data_venda,
                    vd.pedidos_dia,
                    vd.ingressos_dia,
                    vd.receita_dia,
                    SUM(vd.pedidos_dia) OVER (PARTITION BY vd.evento_id ORDER BY vd.data_venda) AS pedidos_acumulados,
                    SUM(vd.ingressos_dia) OVER (PARTITION BY vd.evento_id ORDER BY vd.data_venda) AS ingressos_acumulados,
                    SUM(vd.receita_dia) OVER (PARTITION BY vd.evento_id ORDER BY vd.data_venda) AS receita_acumulada,
                    ROW_NUMBER() OVER (PARTITION BY vd.evento_id ORDER BY vd.data_venda) AS dia_venda
                FROM vendas_diarias vd
            )
            SELECT 
                va1.dia_venda,
                DATE_FORMAT(va1.data_venda, '%d/%m/%Y') AS data_evento1,
                va1.pedidos_dia AS pedidos_dia_ev1,
                va1.ingressos_dia AS ingressos_dia_ev1,
                va1.receita_dia AS receita_dia_ev1,
                va1.pedidos_acumulados AS pedidos_acum_ev1,
                va1.ingressos_acumulados AS ingressos_acum_ev1,
                va1.receita_acumulada AS receita_acum_ev1,
                DATE_FORMAT(va2.data_venda, '%d/%m/%Y') AS data_evento2,
                COALESCE(va2.pedidos_dia, 0) AS pedidos_dia_ev2,
                COALESCE(va2.ingressos_dia, 0) AS ingressos_dia_ev2,
                COALESCE(va2.receita_dia, 0) AS receita_dia_ev2,
                COALESCE(va2.pedidos_acumulados, 0) AS pedidos_acum_ev2,
                COALESCE(va2.ingressos_acumulados, 0) AS ingressos_acum_ev2,
                COALESCE(va2.receita_acumulada, 0) AS receita_acum_ev2,
                (va1.ingressos_acumulados - COALESCE(va2.ingressos_acumulados, 0)) AS diff_ingressos,
                (va1.receita_acumulada - COALESCE(va2.receita_acumulada, 0)) AS diff_receita,
                ROUND((va1.ingressos_acumulados / NULLIF(va2.ingressos_acumulados, 0) * 100) - 100, 2) AS perc_evolucao_ingressos,
                ROUND((va1.receita_acumulada / NULLIF(va2.receita_acumulada, 0) * 100) - 100, 2) AS perc_evolucao_receita
            FROM vendas_acumuladas va1
            LEFT JOIN vendas_acumuladas va2 ON va1.dia_venda = va2.dia_venda 
                AND va2.evento_id = {$evento2Id}
            WHERE va1.evento_id = {$evento1Id}
            ORDER BY va1.dia_venda
        ";
        
        return $this->db->query($sql)->getResultArray();
    }
    
    /**
     * Busca comparação por períodos (semanas/meses)
     */
    public function getComparacaoPorPeriodos(int $evento1Id, int $evento2Id, array $status = ['CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH'], int $ticketCortesia = 608)
    {
        $statusStr = "'" . implode("','", $status) . "'";
        
        $sql = "
            WITH primeira_venda AS (
                SELECT 
                    evento_id,
                    MIN(created_at) AS inicio
                FROM pedidos
                WHERE evento_id IN ({$evento1Id}, {$evento2Id})
                    AND status IN ({$statusStr})
                GROUP BY evento_id
            )
            SELECT 
                periodo,
                SUM(CASE WHEN evento_id = {$evento1Id} THEN pedidos ELSE 0 END) AS pedidos_ev1,
                SUM(CASE WHEN evento_id = {$evento1Id} THEN ingressos ELSE 0 END) AS ingressos_ev1,
                SUM(CASE WHEN evento_id = {$evento1Id} THEN receita ELSE 0 END) AS receita_ev1,
                SUM(CASE WHEN evento_id = {$evento2Id} THEN pedidos ELSE 0 END) AS pedidos_ev2,
                SUM(CASE WHEN evento_id = {$evento2Id} THEN ingressos ELSE 0 END) AS ingressos_ev2,
                SUM(CASE WHEN evento_id = {$evento2Id} THEN receita ELSE 0 END) AS receita_ev2,
                (SUM(CASE WHEN evento_id = {$evento1Id} THEN ingressos ELSE 0 END) - 
                 SUM(CASE WHEN evento_id = {$evento2Id} THEN ingressos ELSE 0 END)) AS diff_ingressos,
                (SUM(CASE WHEN evento_id = {$evento1Id} THEN receita ELSE 0 END) - 
                 SUM(CASE WHEN evento_id = {$evento2Id} THEN receita ELSE 0 END)) AS diff_receita
            FROM (
                SELECT 
                    p.evento_id,
                    CASE 
                        WHEN DATEDIFF(p.created_at, pv.inicio) <= 7 THEN '1. Primeira Semana'
                        WHEN DATEDIFF(p.created_at, pv.inicio) <= 14 THEN '2. Segunda Semana'
                        WHEN DATEDIFF(p.created_at, pv.inicio) <= 21 THEN '3. Terceira Semana'
                        WHEN DATEDIFF(p.created_at, pv.inicio) <= 30 THEN '4. Primeiro Mês'
                        WHEN DATEDIFF(p.created_at, pv.inicio) <= 60 THEN '5. Segundo Mês'
                        ELSE '6. Demais Períodos'
                    END AS periodo,
                    COUNT(DISTINCT p.id) AS pedidos,
                    COUNT(i.id) AS ingressos,
                    SUM(p.total) AS receita
                FROM pedidos p
                INNER JOIN primeira_venda pv ON p.evento_id = pv.evento_id
                LEFT JOIN ingressos i ON p.id = i.pedido_id 
                    AND i.ticket_id <> {$ticketCortesia}
                WHERE p.evento_id IN ({$evento1Id}, {$evento2Id})
                    AND p.status IN ({$statusStr})
                GROUP BY p.evento_id, periodo
            ) periodos
            GROUP BY periodo
            ORDER BY periodo
        ";
        
        return $this->db->query($sql)->getResultArray();
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
        
        $result = $this->db->query($sql)->getRowArray();
        return $result ?: [];
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
        
        return $this->db->query($sql)->getResultArray();
    }
}

