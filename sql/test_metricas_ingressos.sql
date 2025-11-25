-- SQL para testar as 3 métricas de ingressos
-- 1. Total SEM cortesias (ingressos vendidos)
-- 2. Total DE cortesias
-- 3. Total COM cortesias (vendidos + cortesias)

SET @evento_id = 17; -- Ajuste o ID do evento conforme necessário

-- Query EXATA usada no dashboard
SELECT 
    (SELECT SUM(CASE WHEN i2.tipo = 'combo' THEN 2 ELSE 1 END)
     FROM ingressos i2
     INNER JOIN pedidos p2 ON p2.id = i2.pedido_id
     WHERE p2.evento_id = @evento_id
     AND p2.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
     AND i2.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
     AND i2.ticket_id != 608
    ) as total_ingressos_vendidos,
    
    (SELECT SUM(CASE WHEN i6.tipo = 'combo' THEN 2 ELSE 1 END)
     FROM ingressos i6
     INNER JOIN pedidos p6 ON p6.id = i6.pedido_id
     WHERE p6.evento_id = @evento_id
     AND p6.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
     AND i6.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
     AND i6.ticket_id = 608
    ) as total_cortesias,
    
    (SELECT SUM(CASE WHEN i7.tipo = 'combo' THEN 2 ELSE 1 END)
     FROM ingressos i7
     INNER JOIN pedidos p7 ON p7.id = i7.pedido_id
     WHERE p7.evento_id = @evento_id
     AND p7.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
     AND i7.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
    ) as total_com_cortesias;

-- Verificação: total_ingressos_vendidos + total_cortesias = total_com_cortesias
-- Esta query deve retornar TRUE
SELECT 
    (@vendidos + @cortesias = @total) as soma_correta
FROM (
    SELECT 
        (SELECT IFNULL(SUM(CASE WHEN i2.tipo = 'combo' THEN 2 ELSE 1 END), 0)
         FROM ingressos i2
         INNER JOIN pedidos p2 ON p2.id = i2.pedido_id
         WHERE p2.evento_id = @evento_id
         AND p2.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
         AND i2.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
         AND i2.ticket_id != 608
        ) as vendidos,
        
        (SELECT IFNULL(SUM(CASE WHEN i6.tipo = 'combo' THEN 2 ELSE 1 END), 0)
         FROM ingressos i6
         INNER JOIN pedidos p6 ON p6.id = i6.pedido_id
         WHERE p6.evento_id = @evento_id
         AND p6.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
         AND i6.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
         AND i6.ticket_id = 608
        ) as cortesias,
        
        (SELECT IFNULL(SUM(CASE WHEN i7.tipo = 'combo' THEN 2 ELSE 1 END), 0)
         FROM ingressos i7
         INNER JOIN pedidos p7 ON p7.id = i7.pedido_id
         WHERE p7.evento_id = @evento_id
         AND p7.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
         AND i7.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
        ) as total
) t
INTO @vendidos, @cortesias, @total;

SELECT 
    @vendidos as ingressos_vendidos,
    @cortesias as cortesias,
    @total as total_geral,
    (@vendidos + @cortesias) as soma,
    (@vendidos + @cortesias = @total) as validacao_ok;

