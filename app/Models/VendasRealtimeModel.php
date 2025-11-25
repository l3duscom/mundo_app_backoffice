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
     */
    public function getMetricasGerais(int $evento_id): array
    {
        $sql = "
        SELECT 
            COUNT(DISTINCT p.id) as total_pedidos,
            COUNT(DISTINCT i.id) as total_ingressos,
            SUM(p.total) as receita_total,
            AVG(p.total) as ticket_medio,
            COUNT(DISTINCT p.user_id) as clientes_unicos
        FROM pedidos p
        LEFT JOIN ingressos i ON p.id = i.pedido_id AND i.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
        WHERE p.evento_id = ?
        AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
        ";
        
        $query = $this->db->query($sql, [$evento_id]);
        return $query ? $query->getRowArray() : [];
    }
    
    /**
     * Evolução de vendas por dia (últimos 30 dias)
     */
    public function getEvolucaoDiaria(int $evento_id, int $dias = 30): array
    {
        $sql = "
        SELECT 
            DATE(p.created_at) as data,
            COUNT(DISTINCT p.id) as pedidos,
            COUNT(DISTINCT i.id) as ingressos,
            SUM(p.total) as receita,
            COUNT(DISTINCT p.user_id) as clientes
        FROM pedidos p
        LEFT JOIN ingressos i ON p.id = i.pedido_id AND i.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
        WHERE p.evento_id = ?
        AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
        AND p.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
        GROUP BY DATE(p.created_at)
        ORDER BY data ASC
        ";
        
        $query = $this->db->query($sql, [$evento_id, $dias]);
        return $query ? $query->getResultArray() : [];
    }
    
    /**
     * Vendas por hora do dia (últimas 24h)
     */
    public function getVendasPorHora(int $evento_id): array
    {
        $sql = "
        SELECT 
            HOUR(p.created_at) as hora,
            COUNT(DISTINCT p.id) as pedidos,
            COUNT(DISTINCT i.id) as ingressos,
            SUM(p.total) as receita
        FROM pedidos p
        LEFT JOIN ingressos i ON p.id = i.pedido_id AND i.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
        WHERE p.evento_id = ?
        AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
        AND p.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        GROUP BY HOUR(p.created_at)
        ORDER BY hora ASC
        ";
        
        $query = $this->db->query($sql, [$evento_id]);
        return $query ? $query->getResultArray() : [];
    }
    
    /**
     * Top 10 ingressos mais vendidos
     */
    public function getTopIngressos(int $evento_id, int $limit = 10): array
    {
        $sql = "
        SELECT 
            i.nome as ingresso,
            COUNT(i.id) as quantidade,
            SUM(i.valor) as receita_total,
            AVG(i.valor) as preco_medio
        FROM ingressos i
        INNER JOIN pedidos p ON p.id = i.pedido_id
        WHERE p.evento_id = ?
        AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
        AND i.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
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
            p.payment_type as metodo,
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
        GROUP BY p.payment_type
        ORDER BY pedidos DESC
        ";
        
        $query = $this->db->query($sql, [$evento_id, $evento_id]);
        return $query ? $query->getResultArray() : [];
    }
    
    /**
     * Vendas em tempo real (últimas 50)
     */
    public function getVendasRecentes(int $evento_id, int $limit = 50): array
    {
        $sql = "
        SELECT 
            p.id,
            p.cod_pedido,
            p.created_at,
            p.total,
            p.payment_type,
            c.nome as cliente_nome,
            COUNT(i.id) as qtd_ingressos
        FROM pedidos p
        LEFT JOIN clientes c ON c.usuario_id = p.user_id
        LEFT JOIN ingressos i ON i.pedido_id = p.id AND i.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
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
     */
    public function getComparacaoPeriodo(int $evento_id, int $dias = 7): array
    {
        $sql = "
        SELECT 
            'periodo_atual' as periodo,
            COUNT(DISTINCT p.id) as pedidos,
            SUM(p.total) as receita
        FROM pedidos p
        WHERE p.evento_id = ?
        AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
        AND p.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
        
        UNION ALL
        
        SELECT 
            'periodo_anterior' as periodo,
            COUNT(DISTINCT p.id) as pedidos,
            SUM(p.total) as receita
        FROM pedidos p
        WHERE p.evento_id = ?
        AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
        AND p.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
        AND p.created_at < DATE_SUB(CURDATE(), INTERVAL ? DAY)
        ";
        
        $query = $this->db->query($sql, [$evento_id, $dias, $evento_id, $dias * 2, $dias]);
        return $query ? $query->getResultArray() : [];
    }
}

