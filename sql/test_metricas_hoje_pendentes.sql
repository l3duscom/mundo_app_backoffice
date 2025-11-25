-- SQL para testar as novas métricas: Hoje e Pendentes

SET @evento_id = 17; -- Ajuste o ID do evento conforme necessário

-- 1. Ingressos vendidos HOJE (sem cortesias)
SELECT 
    'Ingressos Hoje' as metrica,
    SUM(CASE WHEN i.tipo = 'combo' THEN 2 ELSE 1 END) as valor
FROM ingressos i
INNER JOIN pedidos p ON p.id = i.pedido_id
WHERE p.evento_id = @evento_id
AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
AND i.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
AND i.ticket_id != 608
AND DATE(p.created_at) = CURDATE();

-- 2. Receita HOJE
SELECT 
    'Receita Hoje' as metrica,
    SUM(p.total) as valor
FROM pedidos p
WHERE p.evento_id = @evento_id
AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
AND DATE(p.created_at) = CURDATE();

-- 3. Pedidos PENDENTES
SELECT 
    'Pedidos Pendentes' as metrica,
    COUNT(DISTINCT p.id) as valor
FROM pedidos p
WHERE p.evento_id = @evento_id
AND p.status = 'PENDING';

-- Query COMPLETA usada no dashboard
SELECT 
    (SELECT SUM(CASE WHEN i8.tipo = 'combo' THEN 2 ELSE 1 END)
     FROM ingressos i8
     INNER JOIN pedidos p8 ON p8.id = i8.pedido_id
     WHERE p8.evento_id = @evento_id
     AND p8.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
     AND i8.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
     AND i8.ticket_id != 608
     AND DATE(p8.created_at) = CURDATE()
    ) as ingressos_hoje,
    
    (SELECT SUM(p9.total)
     FROM pedidos p9
     WHERE p9.evento_id = @evento_id
     AND p9.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
     AND DATE(p9.created_at) = CURDATE()
    ) as receita_hoje,
    
    (SELECT COUNT(DISTINCT p10.id)
     FROM pedidos p10
     WHERE p10.evento_id = @evento_id
     AND p10.status = 'PENDING'
    ) as pedidos_pendentes;

-- Verificar status de pedidos
SELECT 
    p.status,
    COUNT(*) as quantidade,
    SUM(p.total) as total_valor
FROM pedidos p
WHERE p.evento_id = @evento_id
GROUP BY p.status
ORDER BY quantidade DESC;

-- Ver pedidos de hoje com detalhes
SELECT 
    p.id,
    p.codigo,
    p.status,
    p.total,
    p.created_at,
    COUNT(i.id) as qtd_ingressos
FROM pedidos p
LEFT JOIN ingressos i ON i.pedido_id = p.id 
    AND i.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
    AND i.ticket_id != 608
WHERE p.evento_id = @evento_id
AND DATE(p.created_at) = CURDATE()
GROUP BY p.id
ORDER BY p.created_at DESC;

