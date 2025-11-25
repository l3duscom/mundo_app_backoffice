-- =====================================================
-- SCRIPT DE TESTE: Dashboard de Vendas em Tempo Real
-- =====================================================
-- Use este script para testar as queries do dashboard
-- =====================================================

SET @evento_id = 17; -- ALTERE PARA O ID DO SEU EVENTO

-- =====================================================
-- 1. MÉTRICAS GERAIS
-- =====================================================

SELECT 
    '===== MÉTRICAS GERAIS =====' as teste;

SELECT 
    COUNT(DISTINCT p.id) as total_pedidos,
    COUNT(DISTINCT i.id) as total_ingressos,
    SUM(p.total) as receita_total,
    AVG(p.total) as ticket_medio,
    COUNT(DISTINCT p.user_id) as clientes_unicos
FROM pedidos p
LEFT JOIN ingressos i ON p.id = i.pedido_id
WHERE p.evento_id = @evento_id
AND p.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH');

-- =====================================================
-- 2. EVOLUÇÃO DIÁRIA (ÚLTIMOS 30 DIAS)
-- =====================================================

SELECT 
    '===== EVOLUÇÃO DIÁRIA =====' as teste;

SELECT 
    DATE(p.created_at) as data,
    COUNT(DISTINCT p.id) as pedidos,
    COUNT(DISTINCT i.id) as ingressos,
    SUM(p.total) as receita,
    COUNT(DISTINCT p.user_id) as clientes
FROM pedidos p
LEFT JOIN ingressos i ON p.id = i.pedido_id
WHERE p.evento_id = @evento_id
AND p.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH')
AND p.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY DATE(p.created_at)
ORDER BY data DESC
LIMIT 10;

-- =====================================================
-- 3. VENDAS POR HORA (ÚLTIMAS 24H)
-- =====================================================

SELECT 
    '===== VENDAS POR HORA =====' as teste;

SELECT 
    HOUR(p.created_at) as hora,
    COUNT(DISTINCT p.id) as pedidos,
    COUNT(DISTINCT i.id) as ingressos,
    SUM(p.total) as receita
FROM pedidos p
LEFT JOIN ingressos i ON p.id = i.pedido_id
WHERE p.evento_id = @evento_id
AND p.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH')
AND p.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY HOUR(p.created_at)
ORDER BY hora ASC;

-- =====================================================
-- 4. TOP 10 INGRESSOS MAIS VENDIDOS
-- =====================================================

SELECT 
    '===== TOP 10 INGRESSOS =====' as teste;

SELECT 
    t.nome as ingresso,
    COUNT(i.id) as quantidade,
    SUM(i.valor) as receita_total,
    AVG(i.valor) as preco_medio
FROM ingressos i
INNER JOIN pedidos p ON p.id = i.pedido_id
INNER JOIN tickets t ON t.id = i.ticket_id
WHERE p.evento_id = @evento_id
AND p.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH')
GROUP BY t.id, t.nome
ORDER BY quantidade DESC
LIMIT 10;

-- =====================================================
-- 5. VENDAS POR MÉTODO DE PAGAMENTO
-- =====================================================

SELECT 
    '===== VENDAS POR MÉTODO =====' as teste;

SELECT 
    p.payment_type as metodo,
    COUNT(DISTINCT p.id) as pedidos,
    SUM(p.total) as receita,
    ROUND((COUNT(DISTINCT p.id) * 100.0 / (
        SELECT COUNT(DISTINCT id) 
        FROM pedidos 
        WHERE evento_id = @evento_id
        AND status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH')
    )), 2) as percentual
FROM pedidos p
WHERE p.evento_id = @evento_id
AND p.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH')
GROUP BY p.payment_type
ORDER BY pedidos DESC;

-- =====================================================
-- 6. VENDAS RECENTES (ÚLTIMAS 20)
-- =====================================================

SELECT 
    '===== VENDAS RECENTES =====' as teste;

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
LEFT JOIN ingressos i ON i.pedido_id = p.id
WHERE p.evento_id = @evento_id
AND p.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH')
GROUP BY p.id
ORDER BY p.created_at DESC
LIMIT 20;

-- =====================================================
-- 7. TAXA DE CONVERSÃO
-- =====================================================

SELECT 
    '===== TAXA DE CONVERSÃO =====' as teste;

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
WHERE evento_id = @evento_id;

-- =====================================================
-- 8. COMPARAÇÃO COM PERÍODO ANTERIOR (7 DIAS)
-- =====================================================

SELECT 
    '===== COMPARAÇÃO 7 DIAS =====' as teste;

SELECT 
    'periodo_atual' as periodo,
    COUNT(DISTINCT p.id) as pedidos,
    SUM(p.total) as receita
FROM pedidos p
WHERE p.evento_id = @evento_id
AND p.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH')
AND p.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)

UNION ALL

SELECT 
    'periodo_anterior' as periodo,
    COUNT(DISTINCT p.id) as pedidos,
    SUM(p.total) as receita
FROM pedidos p
WHERE p.evento_id = @evento_id
AND p.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH')
AND p.created_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
AND p.created_at < DATE_SUB(CURDATE(), INTERVAL 7 DAY);

-- =====================================================
-- FIM DO SCRIPT DE TESTE
-- =====================================================

