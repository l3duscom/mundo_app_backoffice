<?php

namespace App\Models;

use CodeIgniter\Model;

class VendasRealtimeModel extends Model
{
    protected $table = 'pedidos';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    
    /**
     * Busca métricas gerais de vendas para um evento
     * Ingressos tipo 'combo' contam como 2
     */
    public function getMetricasGerais(int $evento_id): array
    {
        $sql = "
        SELECT 
            (SELECT SUM(CASE WHEN i2.tipo = 'combo' THEN 2 ELSE 1 END)
             FROM ingressos i2
             INNER JOIN pedidos p2 ON p2.id = i2.pedido_id
             WHERE p2.evento_id = ?
             AND p2.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
             AND i2.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
             AND i2.ticket_id != 608
            ) as total_ingressos,
            (SELECT SUM(p3.total) 
             FROM pedidos p3 
             WHERE p3.evento_id = ? 
             AND p3.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
            ) as receita_total,
            (SELECT ROUND(SUM(p4.total) / COUNT(DISTINCT p4.id), 2)
             FROM pedidos p4 
             WHERE p4.evento_id = ? 
             AND p4.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
            ) as ticket_medio,
            (SELECT COUNT(DISTINCT p5.user_id)
             FROM pedidos p5
             WHERE p5.evento_id = ?
             AND p5.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
            ) as clientes_unicos
        ";
        
        $query = $this->db->query($sql, [$evento_id, $evento_id, $evento_id, $evento_id]);
        return $query ? $query->getRowArray() : [];
    }
    
    /**
     * Evolução de vendas por dia (últimos 30 dias)
     * Ingressos tipo 'combo' contam como 2
     */
    public function getEvolucaoDiaria(int $evento_id, int $dias = 30): array
    {
        $sql = "
        SELECT 
            DATE(p.created_at) as data,
            SUM(CASE WHEN i.tipo = 'combo' THEN 2 ELSE 1 END) as ingressos,
            SUM(i.valor) as receita,
            COUNT(DISTINCT p.user_id) as clientes
        FROM pedidos p
        INNER JOIN ingressos i ON i.pedido_id = p.id
        WHERE p.evento_id = ?
        AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
        AND i.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
        AND i.ticket_id != 608
        AND p.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
        GROUP BY DATE(p.created_at)
        ORDER BY data ASC
        ";
        
        $query = $this->db->query($sql, [$evento_id, $dias]);
        return $query ? $query->getResultArray() : [];
    }
    
    /**
     * Vendas por hora do dia (últimas 24h)
     * Ingressos tipo 'combo' contam como 2
     */
    public function getVendasPorHora(int $evento_id): array
    {
        $sql = "
        SELECT 
            HOUR(p.created_at) as hora,
            SUM(CASE WHEN i.tipo = 'combo' THEN 2 ELSE 1 END) as ingressos,
            SUM(i.valor) as receita
        FROM pedidos p
        INNER JOIN ingressos i ON i.pedido_id = p.id
        WHERE p.evento_id = ?
        AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
        AND i.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
        AND i.ticket_id != 608
        AND p.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        GROUP BY HOUR(p.created_at)
        ORDER BY hora ASC
        ";
        
        $query = $this->db->query($sql, [$evento_id]);
        return $query ? $query->getResultArray() : [];
    }
    
    /**
     * Todos os ingressos vendidos (sem limite)
     * Ingressos tipo 'combo' contam como 2
     */
    public function getTopIngressos(int $evento_id, int $limit = 999): array
    {
        $sql = "
        SELECT 
            i.nome as ingresso,
            SUM(CASE WHEN i.tipo = 'combo' THEN 2 ELSE 1 END) as quantidade,
            SUM(i.valor) as receita_total,
            AVG(i.valor) as preco_medio
        FROM ingressos i
        INNER JOIN pedidos p ON p.id = i.pedido_id
        WHERE p.evento_id = ?
        AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
        AND i.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
        AND i.ticket_id != 608
        GROUP BY i.nome
        ORDER BY quantidade DESC
        LIMIT ?
        ";
        
        $query = $this->db->query($sql, [$evento_id, $limit]);
        return $query ? $query->getResultArray() : [];
    }
    
    /**
     * Vendas por método de pagamento
     */
    public function getVendasPorMetodo(int $evento_id): array
    {
        $sql = "
        SELECT 
            IFNULL(p.forma_pagamento, 'N/A') as metodo,
            COUNT(DISTINCT p.id) as pedidos,
            SUM(p.total) as receita,
            ROUND((COUNT(DISTINCT p.id) * 100.0 / (
                SELECT COUNT(DISTINCT id) 
                FROM pedidos 
                WHERE evento_id = ? 
                AND status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
            )), 2) as percentual
        FROM pedidos p
        WHERE p.evento_id = ?
        AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
        GROUP BY p.forma_pagamento
        ORDER BY pedidos DESC
        ";
        
        log_message('debug', 'SQL getVendasPorMetodo: ' . $sql);
        log_message('debug', 'Evento ID: ' . $evento_id);
        
        $query = $this->db->query($sql, [$evento_id, $evento_id]);
        
        if (!$query) {
            log_message('error', 'Erro na query getVendasPorMetodo: ' . json_encode($this->db->error()));
            return [];
        }
        
        $result = $query->getResultArray();
        log_message('debug', 'Resultado getVendasPorMetodo: ' . json_encode($result));
        
        return $result;
    }
    
    /**
     * Vendas em tempo real (últimas 50)
     */
    public function getVendasRecentes(int $evento_id, int $limit = 50): array
    {
        $sql = "
        SELECT 
            p.id,
            p.codigo,
            p.created_at,
            p.total,
            p.forma_pagamento,
            c.nome as cliente_nome,
            COUNT(i.id) as qtd_ingressos
        FROM pedidos p
        LEFT JOIN clientes c ON c.usuario_id = p.user_id
        LEFT JOIN ingressos i ON i.pedido_id = p.id 
            AND i.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
            AND i.ticket_id != 608
        WHERE p.evento_id = ?
        AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
        GROUP BY p.id
        ORDER BY p.created_at DESC
        LIMIT ?
        ";
        
        $query = $this->db->query($sql, [$evento_id, $limit]);
        return $query ? $query->getResultArray() : [];
    }
    
    /**
     * Taxa de conversão (pedidos confirmados / total de pedidos)
     */
    public function getTaxaConversao(int $evento_id): array
    {
        $sql = "
        SELECT 
            COUNT(CASE WHEN status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH') THEN 1 END) as confirmados,
            COUNT(CASE WHEN status = 'PENDING' THEN 1 END) as pendentes,
            COUNT(CASE WHEN status = 'CANCELED' THEN 1 END) as cancelados,
            COUNT(*) as total,
            ROUND(
                (COUNT(CASE WHEN status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH') THEN 1 END) * 100.0 / COUNT(*)),
                2
            ) as taxa_conversao
        FROM pedidos
        WHERE evento_id = ?
        ";
        
        $query = $this->db->query($sql, [$evento_id]);
        return $query ? $query->getRowArray() : [];
    }
    
    /**
     * Comparação com período anterior
     * Ingressos tipo 'combo' contam como 2
     */
    public function getComparacaoPeriodo(int $evento_id, int $dias = 7): array
    {
        $sql = "
        SELECT 
            'periodo_atual' as periodo,
            SUM(CASE WHEN i.tipo = 'combo' THEN 2 ELSE 1 END) as ingressos,
            SUM(i.valor) as receita
        FROM pedidos p
        INNER JOIN ingressos i ON i.pedido_id = p.id
        WHERE p.evento_id = ?
        AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
        AND i.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
        AND i.ticket_id != 608
        AND p.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
        
        UNION ALL
        
        SELECT 
            'periodo_anterior' as periodo,
            SUM(CASE WHEN i.tipo = 'combo' THEN 2 ELSE 1 END) as ingressos,
            SUM(i.valor) as receita
        FROM pedidos p
        INNER JOIN ingressos i ON i.pedido_id = p.id
        WHERE p.evento_id = ?
        AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
        AND i.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
        AND i.ticket_id != 608
        AND p.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
        AND p.created_at < DATE_SUB(CURDATE(), INTERVAL ? DAY)
        ";
        
        $query = $this->db->query($sql, [$evento_id, $dias, $evento_id, $dias * 2, $dias]);
        return $query ? $query->getResultArray() : [];
    }
}

