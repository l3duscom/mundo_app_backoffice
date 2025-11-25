-- =====================================================
-- VERIFICAR SE OS CAMPOS EXISTEM
-- =====================================================

-- 1. Verificar campos da tabela PEDIDOS
DESCRIBE pedidos;

-- 2. Verificar campos da tabela INGRESSOS
DESCRIBE ingressos;

-- 3. Verificar campos da tabela CLIENTES
DESCRIBE clientes;

-- 4. Verificar campos da tabela TICKETS
DESCRIBE tickets;

-- 5. Teste rápido da query mais simples
SELECT COUNT(*) as total
FROM pedidos
WHERE evento_id = 17
AND status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH');

