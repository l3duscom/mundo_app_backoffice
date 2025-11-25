-- SQL para testar vendas por dia da semana

SET @evento_id = 17; -- Ajuste o ID do evento conforme necessário

-- Query usada no dashboard
SELECT 
    DAYOFWEEK(p.created_at) as dia_numero,
    CASE DAYOFWEEK(p.created_at)
        WHEN 1 THEN 'Domingo'
        WHEN 2 THEN 'Segunda'
        WHEN 3 THEN 'Terça'
        WHEN 4 THEN 'Quarta'
        WHEN 5 THEN 'Quinta'
        WHEN 6 THEN 'Sexta'
        WHEN 7 THEN 'Sábado'
    END as dia_semana,
    SUM(CASE WHEN i.tipo = 'combo' THEN 2 ELSE 1 END) as ingressos,
    SUM(i.valor) as receita,
    COUNT(DISTINCT p.id) as pedidos,
    COUNT(i.id) as total_ingressos_registros
FROM pedidos p
INNER JOIN ingressos i ON i.pedido_id = p.id
WHERE p.evento_id = @evento_id
AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
AND i.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
AND i.ticket_id != 608
GROUP BY DAYOFWEEK(p.created_at), dia_semana
ORDER BY dia_numero;

-- Ver distribuição por dia da semana com datas
SELECT 
    DATE(p.created_at) as data,
    DAYNAME(p.created_at) as dia_semana_en,
    CASE DAYOFWEEK(p.created_at)
        WHEN 1 THEN 'Domingo'
        WHEN 2 THEN 'Segunda'
        WHEN 3 THEN 'Terça'
        WHEN 4 THEN 'Quarta'
        WHEN 5 THEN 'Quinta'
        WHEN 6 THEN 'Sexta'
        WHEN 7 THEN 'Sábado'
    END as dia_semana,
    COUNT(DISTINCT p.id) as pedidos,
    SUM(CASE WHEN i.tipo = 'combo' THEN 2 ELSE 1 END) as ingressos
FROM pedidos p
INNER JOIN ingressos i ON i.pedido_id = p.id
WHERE p.evento_id = @evento_id
AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
AND i.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
AND i.ticket_id != 608
GROUP BY DATE(p.created_at), dia_semana
ORDER BY data DESC
LIMIT 30;

