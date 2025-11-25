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
     * VERSÃO COMPATÍVEL COM MYSQL 5.7 (SEM CTEs)
     */
    public function getEvolucaoDiariaComparativa(int $evento1Id, int $evento2Id, array $status = ['CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH'], int $ticketCortesia = 608)
    {
        $statusStr = "'" . implode("','", $status) . "'";
        
        // Criar tabelas temporárias (compatível com MySQL 5.7)
        $this->db->query("DROP TEMPORARY TABLE IF EXISTS vendas_diarias_temp");
        $this->db->query("DROP TEMPORARY TABLE IF EXISTS vendas_numeradas_ev1");
        $this->db->query("DROP TEMPORARY TABLE IF EXISTS vendas_numeradas_ev2");
        $this->db->query("DROP TEMPORARY TABLE IF EXISTS vendas_acum_ev1");
        $this->db->query("DROP TEMPORARY TABLE IF EXISTS vendas_acum_ev2");
        
        // 1. Criar tabela com vendas diárias
        $this->db->query("
            CREATE TEMPORARY TABLE vendas_diarias_temp AS
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
        ");
        
        // 2. Numerar dias para evento 1
        $this->db->query("SET @row_ev1 = 0");
        $this->db->query("
            CREATE TEMPORARY TABLE vendas_numeradas_ev1 AS
            SELECT 
                (@row_ev1 := @row_ev1 + 1) AS dia_venda,
                data_venda,
                pedidos_dia,
                ingressos_dia,
                receita_dia
            FROM vendas_diarias_temp
            WHERE evento_id = {$evento1Id}
            ORDER BY data_venda
        ");
        
        // 3. Numerar dias para evento 2
        $this->db->query("SET @row_ev2 = 0");
        $this->db->query("
            CREATE TEMPORARY TABLE vendas_numeradas_ev2 AS
            SELECT 
                (@row_ev2 := @row_ev2 + 1) AS dia_venda,
                data_venda,
                pedidos_dia,
                ingressos_dia,
                receita_dia
            FROM vendas_diarias_temp
            WHERE evento_id = {$evento2Id}
            ORDER BY data_venda
        ");
        
        // 4. Calcular acumulados para evento 1
        $this->db->query("
            CREATE TEMPORARY TABLE vendas_acum_ev1 AS
            SELECT 
                v1.dia_venda,
                v1.data_venda,
                v1.pedidos_dia,
                v1.ingressos_dia,
                v1.receita_dia,
                (SELECT SUM(v2.pedidos_dia) 
                 FROM vendas_numeradas_ev1 v2 
                 WHERE v2.dia_venda <= v1.dia_venda) AS pedidos_acumulados,
                (SELECT SUM(v2.ingressos_dia) 
                 FROM vendas_numeradas_ev1 v2 
                 WHERE v2.dia_venda <= v1.dia_venda) AS ingressos_acumulados,
                (SELECT SUM(v2.receita_dia) 
                 FROM vendas_numeradas_ev1 v2 
                 WHERE v2.dia_venda <= v1.dia_venda) AS receita_acumulada
            FROM vendas_numeradas_ev1 v1
        ");
        
        // 5. Calcular acumulados para evento 2
        $this->db->query("
            CREATE TEMPORARY TABLE vendas_acum_ev2 AS
            SELECT 
                v1.dia_venda,
                v1.data_venda,
                v1.pedidos_dia,
                v1.ingressos_dia,
                v1.receita_dia,
                (SELECT SUM(v2.pedidos_dia) 
                 FROM vendas_numeradas_ev2 v2 
                 WHERE v2.dia_venda <= v1.dia_venda) AS pedidos_acumulados,
                (SELECT SUM(v2.ingressos_dia) 
                 FROM vendas_numeradas_ev2 v2 
                 WHERE v2.dia_venda <= v1.dia_venda) AS ingressos_acumulados,
                (SELECT SUM(v2.receita_dia) 
                 FROM vendas_numeradas_ev2 v2 
                 WHERE v2.dia_venda <= v1.dia_venda) AS receita_acumulada
            FROM vendas_numeradas_ev2 v1
        ");
        
        // 6. Query final
        $result = $this->db->query("
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
            FROM vendas_acum_ev1 va1
            LEFT JOIN vendas_acum_ev2 va2 ON va1.dia_venda = va2.dia_venda
            ORDER BY va1.dia_venda
        ");
        
        $data = $result ? $result->getResultArray() : [];
        
        // Limpar tabelas temporárias
        $this->db->query("DROP TEMPORARY TABLE IF EXISTS vendas_diarias_temp");
        $this->db->query("DROP TEMPORARY TABLE IF EXISTS vendas_numeradas_ev1");
        $this->db->query("DROP TEMPORARY TABLE IF EXISTS vendas_numeradas_ev2");
        $this->db->query("DROP TEMPORARY TABLE IF EXISTS vendas_acum_ev1");
        $this->db->query("DROP TEMPORARY TABLE IF EXISTS vendas_acum_ev2");
        
        return $data;
    }
    
    /**
     * Busca comparação por períodos (semanas/meses)
     * VERSÃO COMPATÍVEL COM MYSQL 5.7 (SEM CTEs)
     */
    public function getComparacaoPorPeriodos(int $evento1Id, int $evento2Id, array $status = ['CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH'], int $ticketCortesia = 608)
    {
        $statusStr = "'" . implode("','", $status) . "'";
        
        // Sem CTEs - usar subquery
        $sql = "
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
                        WHEN DATEDIFF(p.created_at, (
                            SELECT MIN(created_at) 
                            FROM pedidos 
                            WHERE evento_id = p.evento_id 
                                AND status IN ({$statusStr})
                        )) <= 7 THEN '1. Primeira Semana'
                        WHEN DATEDIFF(p.created_at, (
                            SELECT MIN(created_at) 
                            FROM pedidos 
                            WHERE evento_id = p.evento_id 
                                AND status IN ({$statusStr})
                        )) <= 14 THEN '2. Segunda Semana'
                        WHEN DATEDIFF(p.created_at, (
                            SELECT MIN(created_at) 
                            FROM pedidos 
                            WHERE evento_id = p.evento_id 
                                AND status IN ({$statusStr})
                        )) <= 21 THEN '3. Terceira Semana'
                        WHEN DATEDIFF(p.created_at, (
                            SELECT MIN(created_at) 
                            FROM pedidos 
                            WHERE evento_id = p.evento_id 
                                AND status IN ({$statusStr})
                        )) <= 30 THEN '4. Primeiro Mês'
                        WHEN DATEDIFF(p.created_at, (
                            SELECT MIN(created_at) 
                            FROM pedidos 
                            WHERE evento_id = p.evento_id 
                                AND status IN ({$statusStr})
                        )) <= 60 THEN '5. Segundo Mês'
                        ELSE '6. Demais Períodos'
                    END AS periodo,
                    COUNT(DISTINCT p.id) AS pedidos,
                    COUNT(i.id) AS ingressos,
                    SUM(p.total) AS receita
                FROM pedidos p
                LEFT JOIN ingressos i ON p.id = i.pedido_id 
                    AND i.ticket_id <> {$ticketCortesia}
                WHERE p.evento_id IN ({$evento1Id}, {$evento2Id})
                    AND p.status IN ({$statusStr})
                GROUP BY p.evento_id, periodo
            ) periodos
            GROUP BY periodo
            ORDER BY periodo
        ";
        
        $result = $this->db->query($sql);
        return $result ? $result->getResultArray() : [];
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

