-- DEBUG: Investigar por que a contagem está incorreta
-- Comparação: 453 (query) vs 40 (real)

SET @evento_id = 17;
SET @data_hoje = '2025-11-25';

-- 1. Contagem BÁSICA de ingressos (sem filtro de tipo)
SELECT 
    'TOTAL SEM FILTRO' as tipo_contagem,
    COUNT(*) as registros,
    COUNT(DISTINCT i.pedido_id) as pedidos_distintos
FROM ingressos i
INNER JOIN pedidos p ON p.id = i.pedido_id
WHERE p.evento_id = @evento_id
AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
AND DATE(p.created_at) = @data_hoje;

-- 2. Contagem COM FILTRO de tipo (excluindo cinemark, adicional, etc) E SEM CORTESIAS
SELECT 
    'COM FILTRO + SEM CORTESIAS' as tipo_contagem,
    COUNT(*) as registros,
    COUNT(DISTINCT i.pedido_id) as pedidos_distintos
FROM ingressos i
INNER JOIN pedidos p ON p.id = i.pedido_id
WHERE p.evento_id = @evento_id
AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
AND i.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
AND i.ticket_id != 608
AND DATE(p.created_at) = @data_hoje;

-- 3. Ver TIPOS de ingressos vendidos hoje (COM cortesias)
SELECT 
    i.tipo,
    i.ticket_id,
    COUNT(*) as quantidade_registros,
    SUM(CASE WHEN i.tipo = 'combo' THEN 2 ELSE 1 END) as contagem_ajustada,
    COUNT(DISTINCT i.pedido_id) as pedidos,
    CASE WHEN i.ticket_id = 608 THEN 'CORTESIA' ELSE 'PAGO' END as tipo_venda
FROM ingressos i
INNER JOIN pedidos p ON p.id = i.pedido_id
WHERE p.evento_id = @evento_id
AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
AND DATE(p.created_at) = @data_hoje
GROUP BY i.tipo, i.ticket_id
ORDER BY quantidade_registros DESC;

-- 4. Ver pedidos individuais de hoje (SEM CORTESIAS)
SELECT 
    p.id as pedido_id,
    p.codigo,
    p.status,
    p.created_at,
    COUNT(i.id) as qtd_ingressos,
    SUM(CASE WHEN i.tipo = 'combo' THEN 2 ELSE 1 END) as contagem_ajustada,
    GROUP_CONCAT(i.tipo) as tipos_ingressos,
    GROUP_CONCAT(i.ticket_id) as ticket_ids
FROM pedidos p
LEFT JOIN ingressos i ON i.pedido_id = p.id 
    AND i.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
    AND i.ticket_id != 608
WHERE p.evento_id = @evento_id
AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
AND DATE(p.created_at) = @data_hoje
GROUP BY p.id
ORDER BY p.created_at DESC;

-- 5. Query EXATA usada no dashboard (COM FILTRO DE CORTESIA)
SELECT 
    DATE(p.created_at) as data,
    SUM(CASE WHEN i.tipo = 'combo' THEN 2 ELSE 1 END) as ingressos,
    COUNT(i.id) as total_registros_ingressos,
    COUNT(DISTINCT p.id) as total_pedidos
FROM pedidos p
INNER JOIN ingressos i ON i.pedido_id = p.id
WHERE p.evento_id = @evento_id
AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
AND i.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
AND i.ticket_id != 608
AND DATE(p.created_at) = @data_hoje
GROUP BY DATE(p.created_at);

-- 6. Comparação: COM vs SEM cortesias
SELECT 
    'COM CORTESIAS' as tipo,
    SUM(CASE WHEN i.tipo = 'combo' THEN 2 ELSE 1 END) as total_ingressos
FROM ingressos i
INNER JOIN pedidos p ON p.id = i.pedido_id
WHERE p.evento_id = @evento_id
AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
AND i.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
AND DATE(p.created_at) = @data_hoje

UNION ALL

SELECT 
    'SEM CORTESIAS' as tipo,
    SUM(CASE WHEN i.tipo = 'combo' THEN 2 ELSE 1 END) as total_ingressos
FROM ingressos i
INNER JOIN pedidos p ON p.id = i.pedido_id
WHERE p.evento_id = @evento_id
AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
AND i.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
AND i.ticket_id != 608
AND DATE(p.created_at) = @data_hoje;

-- 7. Verificar se há duplicação de ingressos (mesmo nome no mesmo pedido)
SELECT 
    p.id as pedido_id,
    p.codigo,
    i.nome as nome_ingresso,
    i.tipo,
    COUNT(*) as vezes_repetido
FROM pedidos p
INNER JOIN ingressos i ON i.pedido_id = p.id
WHERE p.evento_id = @evento_id
AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
AND DATE(p.created_at) = @data_hoje
GROUP BY p.id, i.nome, i.tipo
HAVING COUNT(*) > 1
ORDER BY vezes_repetido DESC;

