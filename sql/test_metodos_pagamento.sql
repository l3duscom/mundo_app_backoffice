-- SQL para testar o campo forma_pagamento
-- Execute este script para ver os dados brutos

SET @evento_id = 17; -- Ajuste o ID do evento conforme necessário

-- Ver os valores únicos de forma_pagamento
SELECT DISTINCT forma_pagamento, COUNT(*) as qtd
FROM pedidos
WHERE evento_id = @evento_id
AND status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
GROUP BY forma_pagamento;

-- Query completa do model
SELECT 
    IFNULL(p.forma_pagamento, 'N/A') as metodo,
    COUNT(DISTINCT p.id) as pedidos,
    SUM(p.total) as receita,
    ROUND((COUNT(DISTINCT p.id) * 100.0 / (
        SELECT COUNT(DISTINCT id) 
        FROM pedidos 
        WHERE evento_id = @evento_id 
        AND status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
    )), 2) as percentual
FROM pedidos p
WHERE p.evento_id = @evento_id
AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
GROUP BY p.forma_pagamento
ORDER BY pedidos DESC;

