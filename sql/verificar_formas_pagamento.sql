-- SQL para verificar os valores únicos do campo forma_pagamento
-- usado na tabela pedidos

SELECT DISTINCT forma_pagamento, COUNT(*) as quantidade
FROM pedidos
WHERE status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
GROUP BY forma_pagamento
ORDER BY quantidade DESC;

