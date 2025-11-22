-- ============================================
-- Atribui Conquista "Comprou Ingresso" para usuários com pedidos no evento 17
-- ============================================
-- Este script:
-- 1. Cria a conquista (se não existir)
-- 2. Atribui a conquista para usuários com pedidos no evento 17
-- 3. Cria registro no extrato de pontos
-- 4. Atualiza o saldo de pontos dos usuários
-- ============================================

-- Passo 1: Criar a conquista (ajuste os valores conforme necessário)
INSERT INTO `conquistas` (`event_id`, `nome_conquista`, `descricao`, `pontos`, `nivel`, `status`, `created_at`, `updated_at`)
SELECT 17, 'Comprou Ingresso', 'Adquiriu ingresso para o evento', 15, 'BRONZE', 'ATIVA', NOW(), NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `conquistas` 
    WHERE `event_id` = 17 
    AND `nome_conquista` = 'Comprou Ingresso'
);

-- Obtém o ID da conquista criada/existente
SET @conquista_id = (
    SELECT id FROM conquistas 
    WHERE event_id = 17 
    AND nome_conquista = 'Comprou Ingresso' 
    LIMIT 1
);

-- Obtém os pontos da conquista
SET @pontos = (
    SELECT pontos FROM conquistas WHERE id = @conquista_id
);

-- Passo 2 a 4: Atribui conquista, cria extrato e atualiza saldo (tudo junto)
-- Usando transação para garantir integridade
START TRANSACTION;

-- Insere em usuario_conquistas para usuários com pedidos no evento 17
-- que ainda não possuem esta conquista
INSERT INTO `usuario_conquistas` (
    `conquista_id`, 
    `event_id`, 
    `user_id`, 
    `pontos`, 
    `admin`, 
    `status`, 
    `atribuido_por`, 
    `created_at`, 
    `updated_at`
)
SELECT DISTINCT
    @conquista_id,
    17,
    p.user_id,
    @pontos,
    1, -- atribuição por admin/sistema
    'ATIVA',
    NULL,
    NOW(),
    NOW()
FROM pedidos p
INNER JOIN usuarios u ON p.user_id = u.id
WHERE p.evento_id = 17
AND p.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH') -- Ajuste os status conforme seu sistema
AND p.user_id IS NOT NULL
AND NOT EXISTS (
    SELECT 1 FROM usuario_conquistas uc
    WHERE uc.user_id = p.user_id
    AND uc.conquista_id = @conquista_id
    AND uc.event_id = 17
);

-- Cria registros no extrato_pontos para os usuários que acabaram de ganhar a conquista
INSERT INTO `extrato_pontos` (
    `user_id`,
    `event_id`,
    `tipo`,
    `pontos`,
    `saldo_anterior`,
    `saldo_atual`,
    `descricao`,
    `referencia_tipo`,
    `referencia_id`,
    `atribuido_por`,
    `created_at`,
    `updated_at`
)
SELECT 
    uc.user_id,
    17,
    'CONQUISTA',
    @pontos,
    COALESCE(u.pontos, 0) as saldo_anterior,
    COALESCE(u.pontos, 0) + @pontos as saldo_atual,
    CONCAT('Conquista: Comprou Ingresso - Atribuída automaticamente por ter pedido no evento'),
    'usuario_conquista',
    uc.id,
    NULL,
    NOW(),
    NOW()
FROM usuario_conquistas uc
INNER JOIN usuarios u ON u.id = uc.user_id
WHERE uc.conquista_id = @conquista_id
AND uc.event_id = 17
AND uc.created_at >= NOW() - INTERVAL 1 MINUTE -- Apenas os recém-criados
AND NOT EXISTS (
    SELECT 1 FROM extrato_pontos ep
    WHERE ep.user_id = uc.user_id
    AND ep.referencia_tipo = 'usuario_conquista'
    AND ep.referencia_id = uc.id
);

-- Atualiza o saldo de pontos dos usuários
UPDATE usuarios u
INNER JOIN usuario_conquistas uc ON uc.user_id = u.id
SET u.pontos = COALESCE(u.pontos, 0) + @pontos
WHERE uc.conquista_id = @conquista_id
AND uc.event_id = 17
AND uc.created_at >= NOW() - INTERVAL 1 MINUTE; -- Apenas os recém-criados

COMMIT;

-- ============================================
-- Verificações
-- ============================================

-- 1. Verifica quantos usuários receberam a conquista
SELECT COUNT(*) as 'Total de Usuários que Receberam a Conquista'
FROM usuario_conquistas
WHERE conquista_id = @conquista_id
AND event_id = 17;

-- 2. Lista os usuários que receberam
SELECT 
    uc.id,
    u.id as user_id,
    u.nome,
    u.email,
    uc.pontos as pontos_ganhos,
    u.pontos as saldo_atual,
    uc.created_at as data_conquista
FROM usuario_conquistas uc
INNER JOIN usuarios u ON u.id = uc.user_id
WHERE uc.conquista_id = @conquista_id
AND uc.event_id = 17
ORDER BY uc.created_at DESC;

-- 3. Verifica o extrato de pontos gerado
SELECT 
    ep.id,
    u.nome,
    ep.tipo,
    ep.pontos,
    ep.saldo_anterior,
    ep.saldo_atual,
    ep.descricao,
    ep.created_at
FROM extrato_pontos ep
INNER JOIN usuarios u ON u.id = ep.user_id
WHERE ep.referencia_tipo = 'usuario_conquista'
AND ep.referencia_id IN (
    SELECT id FROM usuario_conquistas 
    WHERE conquista_id = @conquista_id 
    AND event_id = 17
)
ORDER BY ep.created_at DESC;

-- ============================================
-- NOTAS IMPORTANTES:
-- ============================================
-- 1. Ajuste o nome da conquista conforme necessário
-- 2. Ajuste os pontos (atualmente 15)
-- 3. Ajuste o nível (atualmente BRONZE)
-- 4. Ajuste os status de pedido considerados válidos (linha 61)
-- 5. O script previne duplicação automaticamente
-- 6. Se precisar rodar novamente, não criará duplicatas
-- ============================================

-- Para desfazer (CUIDADO - só use se algo der errado):
/*
-- Limpa registros criados (ajuste o timestamp conforme necessário)
DELETE FROM extrato_pontos 
WHERE referencia_tipo = 'usuario_conquista' 
AND referencia_id IN (
    SELECT id FROM usuario_conquistas 
    WHERE conquista_id = @conquista_id 
    AND event_id = 17
);

DELETE FROM usuario_conquistas 
WHERE conquista_id = @conquista_id 
AND event_id = 17;

DELETE FROM conquistas 
WHERE id = @conquista_id;
*/

