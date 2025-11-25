-- SQL para testar a contagem de ingressos na evolução diária
-- Este script valida se a contagem está correta considerando:
-- 1. Ingressos tipo 'combo' contam como 2
-- 2. Tipos ignorados: 'cinemark', 'adicional', '', 'produto'
-- 3. Apenas pedidos confirmados

SET @evento_id = 17; -- Ajuste o ID do evento conforme necessário
SET @dias = 30;

-- Query usada no dashboard (SEM CORTESIAS - ticket_id = 608)
SELECT 
    DATE(p.created_at) as data,
    SUM(CASE WHEN i.tipo = 'combo' THEN 2 ELSE 1 END) as ingressos,
    SUM(i.valor) as receita,
    COUNT(DISTINCT p.user_id) as clientes,
    COUNT(i.id) as total_registros_ingressos
FROM pedidos p
INNER JOIN ingressos i ON i.pedido_id = p.id
WHERE p.evento_id = @evento_id
AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
AND i.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
AND i.ticket_id != 608
AND p.created_at >= DATE_SUB(CURDATE(), INTERVAL @dias DAY)
GROUP BY DATE(p.created_at)
ORDER BY data DESC;

-- Verificar tipos de ingressos no evento (SEM CORTESIAS)
SELECT 
    i.tipo,
    COUNT(*) as quantidade,
    SUM(CASE WHEN i.tipo = 'combo' THEN 2 ELSE 1 END) as contagem_ajustada
FROM ingressos i
INNER JOIN pedidos p ON p.id = i.pedido_id
WHERE p.evento_id = @evento_id
AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
AND i.ticket_id != 608
GROUP BY i.tipo
ORDER BY quantidade DESC;

-- Total geral de ingressos do evento (deve bater com o card "Total de Ingressos")
-- SEM CORTESIAS (ticket_id = 608)
SELECT 
    SUM(CASE WHEN i.tipo = 'combo' THEN 2 ELSE 1 END) as total_ingressos,
    COUNT(i.id) as total_registros,
    COUNT(DISTINCT p.id) as total_pedidos
FROM pedidos p
INNER JOIN ingressos i ON i.pedido_id = p.id
WHERE p.evento_id = @evento_id
AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
AND i.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
AND i.ticket_id != 608;

