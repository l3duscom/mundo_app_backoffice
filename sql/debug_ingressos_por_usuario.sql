-- =====================================================
-- DEBUG: Verificar Ingressos por Usuário
-- =====================================================
-- Este script ajuda a identificar problemas de vazamento
-- de dados entre usuários na API de ingressos.
-- =====================================================

-- 1. VERIFICAR INGRESSOS DE UM USUÁRIO ESPECÍFICO
-- =====================================================
-- Troque o user_id pelos IDs que estão apresentando problema

SET @usuario_a = 6;  -- Troque pelo ID do primeiro usuário
SET @usuario_b = 7;  -- Troque pelo ID do segundo usuário

-- Ingressos do Usuário A
SELECT 
    'USUARIO A' as tipo,
    i.id as ingresso_id,
    i.user_id,
    i.codigo,
    i.nome as nome_ingresso,
    i.ticket_id,
    p.id as pedido_id,
    p.codigo as cod_pedido,
    p.status,
    p.evento_id,
    u.id as usuario_confirmado,
    u.email
FROM ingressos i
INNER JOIN pedidos p ON p.id = i.pedido_id
INNER JOIN usuarios u ON u.id = i.user_id
WHERE i.user_id = @usuario_a
  AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
ORDER BY i.id DESC;

-- Ingressos do Usuário B
SELECT 
    'USUARIO B' as tipo,
    i.id as ingresso_id,
    i.user_id,
    i.codigo,
    i.nome as nome_ingresso,
    i.ticket_id,
    p.id as pedido_id,
    p.codigo as cod_pedido,
    p.status,
    p.evento_id,
    u.id as usuario_confirmado,
    u.email
FROM ingressos i
INNER JOIN pedidos p ON p.id = i.pedido_id
INNER JOIN usuarios u ON u.id = i.user_id
WHERE i.user_id = @usuario_b
  AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
ORDER BY i.id DESC;


-- 2. VERIFICAR SE HÁ INCONSISTÊNCIAS (user_id diferente entre ingressos e pedidos)
-- =====================================================
SELECT 
    'INCONSISTENCIA' as alerta,
    i.id as ingresso_id,
    i.user_id as ingresso_user_id,
    p.user_id as pedido_user_id,
    i.codigo,
    p.codigo as cod_pedido,
    p.status
FROM ingressos i
INNER JOIN pedidos p ON p.id = i.pedido_id
WHERE i.user_id != p.user_id
  AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
LIMIT 50;

-- Se este SELECT retornar resultados, há um problema de integridade de dados!


-- 3. VERIFICAR ÚLTIMAS REQUISIÇÕES DE INGRESSOS (via logs)
-- =====================================================
-- Verificar security_logs para ver quem acessou recentemente
SELECT 
    event_type,
    identifier as email,
    user_id,
    ip_address,
    details,
    created_at
FROM security_logs
WHERE user_id IN (@usuario_a, @usuario_b)
  OR identifier IN (
      SELECT email FROM usuarios WHERE id IN (@usuario_a, @usuario_b)
  )
ORDER BY created_at DESC
LIMIT 20;


-- 4. CONTAR INGRESSOS POR USUÁRIO
-- =====================================================
SELECT 
    u.id as user_id,
    u.email,
    COUNT(DISTINCT i.id) as total_ingressos,
    COUNT(DISTINCT p.id) as total_pedidos,
    GROUP_CONCAT(DISTINCT p.status) as status_pedidos
FROM usuarios u
LEFT JOIN ingressos i ON i.user_id = u.id
LEFT JOIN pedidos p ON p.id = i.pedido_id
WHERE u.id IN (@usuario_a, @usuario_b)
  AND (p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH') OR p.status IS NULL)
GROUP BY u.id, u.email;


-- 5. VERIFICAR SE HÁ INGRESSOS SEM USUÁRIO VÁLIDO
-- =====================================================
SELECT 
    i.id as ingresso_id,
    i.user_id,
    i.codigo,
    i.nome,
    p.id as pedido_id,
    p.codigo as cod_pedido,
    'Ingresso sem usuario valido' as problema
FROM ingressos i
INNER JOIN pedidos p ON p.id = i.pedido_id
LEFT JOIN usuarios u ON u.id = i.user_id
WHERE u.id IS NULL
  AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
LIMIT 20;


-- 6. TESTE: SIMULAR A QUERY DO MODEL
-- =====================================================
-- Esta é exatamente a query que o IngressoModel executa
-- Execute para um usuário específico e verifique os resultados

SELECT
    i.id,
    i.user_id,
    i.ticket_id,
    i.created_at,
    i.nome,
    i.email,
    i.cpf,
    i.valor_unitario,
    i.valor,
    i.quantidade,
    i.codigo,
    i.pedido_id,
    i.participante,
    i.tipo,
    i.cinemark,
    p.codigo as cod_pedido,
    p.rastreio,
    p.status,
    p.status_entrega,
    p.frete,
    p.evento_id,
    p.comprovante,
    e.nome as nome_evento,
    e.slug,
    e.data_inicio,
    e.data_fim,
    e.hora_inicio,
    e.hora_fim,
    e.local
FROM ingressos i
INNER JOIN pedidos p ON p.id = i.pedido_id
INNER JOIN usuarios u ON u.id = i.user_id
INNER JOIN eventos e ON e.id = p.evento_id
WHERE u.id = @usuario_a  -- Troque pelo user_id que está testando
  AND i.user_id = @usuario_a  -- Filtro duplo de segurança
  AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
ORDER BY p.id DESC;

-- Verifique se TODOS os ingressos retornados pertencem ao usuário correto


-- 7. VERIFICAR INGRESSOS DUPLICADOS OU COMPARTILHADOS
-- =====================================================
SELECT 
    i.id,
    i.codigo,
    COUNT(DISTINCT i.user_id) as usuarios_diferentes,
    GROUP_CONCAT(DISTINCT i.user_id) as user_ids,
    'Codigo duplicado entre usuarios' as problema
FROM ingressos i
INNER JOIN pedidos p ON p.id = i.pedido_id
WHERE p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
GROUP BY i.codigo
HAVING COUNT(DISTINCT i.user_id) > 1
LIMIT 20;


-- 8. VERIFICAR TIMESTAMPS DOS INGRESSOS
-- =====================================================
-- Para identificar se há problema de timing/concorrência
SELECT 
    i.user_id,
    i.id as ingresso_id,
    i.codigo,
    i.created_at,
    TIMESTAMPDIFF(SECOND, LAG(i.created_at) OVER (ORDER BY i.created_at), i.created_at) as segundos_desde_anterior
FROM ingressos i
INNER JOIN pedidos p ON p.id = i.pedido_id
WHERE i.user_id IN (@usuario_a, @usuario_b)
  AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
ORDER BY i.created_at DESC
LIMIT 50;


-- =====================================================
-- CHECKLIST DE VERIFICAÇÃO
-- =====================================================
/*
[ ] Query 1: Ingressos do Usuário A retorna apenas ingressos dele?
[ ] Query 1: Ingressos do Usuário B retorna apenas ingressos dele?
[ ] Query 2: Há inconsistências entre ingressos.user_id e pedidos.user_id?
[ ] Query 3: Logs mostram acessos corretos?
[ ] Query 4: Contagem de ingressos bate com a API?
[ ] Query 5: Há ingressos órfãos (sem usuário válido)?
[ ] Query 6: Query simulada retorna apenas ingressos do usuário testado?
[ ] Query 7: Há códigos duplicados entre usuários?
[ ] Query 8: Há padrão temporal suspeito?
*/


-- =====================================================
-- SE ENCONTRAR PROBLEMAS DE INTEGRIDADE
-- =====================================================
/*
PROBLEMA: ingressos.user_id != pedidos.user_id

CORREÇÃO:
UPDATE ingressos i
INNER JOIN pedidos p ON p.id = i.pedido_id
SET i.user_id = p.user_id
WHERE i.user_id != p.user_id;

⚠️ CUIDADO: Execute esta correção APENAS se confirmar que há inconsistência!
⚠️ Faça backup antes: mysqldump database_name ingressos > backup_ingressos.sql
*/


-- =====================================================
-- MONITORAMENTO CONTÍNUO
-- =====================================================
-- Execute esta query periodicamente para detectar vazamentos

CREATE OR REPLACE VIEW vw_ingressos_seguranca AS
SELECT 
    i.id as ingresso_id,
    i.user_id as ingresso_user_id,
    p.user_id as pedido_user_id,
    i.codigo,
    p.codigo as cod_pedido,
    i.created_at,
    CASE 
        WHEN i.user_id != p.user_id THEN '🚨 INCONSISTENCIA'
        ELSE '✅ OK'
    END as status_integridade
FROM ingressos i
INNER JOIN pedidos p ON p.id = i.pedido_id
WHERE p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH');

-- Uso:
-- SELECT * FROM vw_ingressos_seguranca WHERE status_integridade LIKE '%INCONSISTENCIA%';


-- =====================================================
-- TESTE PRÁTICO: COMPARAR API COM BANCO
-- =====================================================
/*
PASSO 1: Fazer login como Usuário A e chamar API
GET /api/ingressos/atuais
Authorization: Bearer TOKEN_USUARIO_A

PASSO 2: Anotar os IDs retornados
Ex: [123, 456, 789]

PASSO 3: Verificar no banco se esses IDs pertencem ao Usuário A
*/

SELECT 
    i.id,
    i.user_id,
    i.codigo,
    CASE 
        WHEN i.id IN (123, 456, 789) THEN '✅ Retornado pela API'
        ELSE 'Não retornado'
    END as status_api,
    CASE 
        WHEN i.user_id = @usuario_a THEN '✅ Pertence ao usuario'
        ELSE '🚨 VAZAMENTO! Pertence a outro usuario'
    END as status_propriedade
FROM ingressos i
INNER JOIN pedidos p ON p.id = i.pedido_id
WHERE i.id IN (123, 456, 789)  -- Substitua pelos IDs retornados pela API
  AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH');


-- =====================================================
-- REFERÊNCIAS
-- =====================================================
/*
📄 Arquivos Relacionados:
• app/Models/IngressoModel.php - Query do banco
• app/Controllers/Api/Ingressos.php - Lógica da API
• PROBLEMA_INGRESSOS_MISTURADOS.md - Documentação do problema

🔍 Próximos Passos:
1. Execute as queries acima
2. Anote os resultados
3. Verifique os logs: tail -f writable/logs/log-*.log
4. Compare com resultados da API
5. Reporte qualquer inconsistência encontrada
*/

