-- ============================================
-- Atribui Conquista para usuários com tipo específico de ingresso
-- ============================================
-- Este script:
-- 1. Cria a conquista (se não existir)
-- 2. Atribui a conquista para usuários com ingresso específico no evento
-- 3. Cria registro no extrato de pontos
-- 4. Atualiza o saldo de pontos dos usuários
-- ============================================

-- ============================================
-- CONFIGURAÇÕES - AJUSTE AQUI
-- ============================================
SET @event_id = 17;                           -- ID do evento
SET @ticket_id = 1;                           -- ID do tipo de ingresso (ex: VIP, Meia-Entrada, etc)
SET @conquista_nome = 'Ingresso VIP';         -- Nome da conquista
SET @conquista_desc = 'Adquiriu ingresso VIP para o evento';  -- Descrição
SET @conquista_pontos = 50;                   -- Pontos da conquista
SET @conquista_nivel = 'OURO';                -- Nível (BRONZE, PRATA, OURO)
-- ============================================

-- Passo 1: Criar a conquista (se não existir)
INSERT INTO `conquistas` (`event_id`, `codigo`, `nome_conquista`, `descricao`, `pontos`, `nivel`, `status`, `created_at`, `updated_at`)
SELECT 
    @event_id, 
    UPPER(SUBSTRING(MD5(CONCAT(@event_id, @ticket_id, @conquista_nome, RAND())), 1, 8)),
    @conquista_nome, 
    @conquista_desc, 
    @conquista_pontos, 
    @conquista_nivel, 
    'ATIVA', 
    NOW(), 
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `conquistas` 
    WHERE `event_id` = @event_id 
    AND `nome_conquista` = @conquista_nome
);

-- Obtém o ID da conquista criada/existente
SET @conquista_id = (
    SELECT id FROM conquistas 
    WHERE event_id = @event_id 
    AND nome_conquista = @conquista_nome 
    LIMIT 1
);

-- Exibe informações da conquista
SELECT 
    id,
    event_id,
    codigo,
    nome_conquista,
    descricao,
    pontos,
    nivel,
    status
FROM conquistas 
WHERE id = @conquista_id;

-- ============================================
-- Passo 2 a 4: Atribui conquista, cria extrato e atualiza saldo
-- Usando transação para garantir integridade
-- ============================================
START TRANSACTION;

-- Insere em usuario_conquistas para usuários com ingresso específico
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
    @event_id,
    p.user_id,
    @conquista_pontos,
    1, -- atribuição por admin/sistema
    'ATIVA',
    NULL,
    NOW(),
    NOW()
FROM pedidos p
INNER JOIN ingressos i ON i.pedido_id = p.id
INNER JOIN usuarios u ON p.user_id = u.id
WHERE p.evento_id = @event_id
AND i.event_id = @event_id
AND i.ticket_id = @ticket_id
AND p.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH') -- Status de pedidos válidos
AND p.user_id IS NOT NULL
AND p.deleted_at IS NULL
AND i.deleted_at IS NULL
AND NOT EXISTS (
    SELECT 1 FROM usuario_conquistas uc
    WHERE uc.user_id = p.user_id
    AND uc.conquista_id = @conquista_id
    AND uc.event_id = @event_id
);

-- Guarda quantidade de registros inseridos
SET @registros_inseridos = ROW_COUNT();

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
    @event_id,
    'CONQUISTA',
    @conquista_pontos,
    COALESCE(u.pontos, 0) as saldo_anterior,
    COALESCE(u.pontos, 0) + @conquista_pontos as saldo_atual,
    CONCAT('Conquista: ', @conquista_nome, ' - Atribuída automaticamente por ter ingresso do tipo ', @ticket_id),
    'usuario_conquista',
    uc.id,
    NULL,
    NOW(),
    NOW()
FROM usuario_conquistas uc
INNER JOIN usuarios u ON u.id = uc.user_id
WHERE uc.conquista_id = @conquista_id
AND uc.event_id = @event_id
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
SET u.pontos = COALESCE(u.pontos, 0) + @conquista_pontos
WHERE uc.conquista_id = @conquista_id
AND uc.event_id = @event_id
AND uc.created_at >= NOW() - INTERVAL 1 MINUTE; -- Apenas os recém-criados

COMMIT;

-- ============================================
-- Verificações
-- ============================================

-- Exibe resumo da operação
SELECT 
    @registros_inseridos as 'Usuários que Receberam',
    @conquista_nome as 'Nome da Conquista',
    @conquista_pontos as 'Pontos',
    @conquista_nivel as 'Nível',
    @event_id as 'Event ID',
    @ticket_id as 'Ticket ID';

-- 1. Verifica quantos usuários receberam a conquista
SELECT COUNT(*) as 'Total de Usuários com esta Conquista'
FROM usuario_conquistas
WHERE conquista_id = @conquista_id
AND event_id = @event_id;

-- 2. Lista os usuários que receberam
SELECT 
    uc.id as uc_id,
    u.id as user_id,
    u.nome,
    u.email,
    uc.pontos as pontos_ganhos,
    u.pontos as saldo_atual,
    uc.created_at as data_conquista,
    (SELECT COUNT(*) FROM ingressos WHERE pedido_id IN (
        SELECT id FROM pedidos WHERE user_id = u.id AND evento_id = @event_id
    ) AND ticket_id = @ticket_id) as qtd_ingressos_tipo
FROM usuario_conquistas uc
INNER JOIN usuarios u ON u.id = uc.user_id
WHERE uc.conquista_id = @conquista_id
AND uc.event_id = @event_id
ORDER BY uc.created_at DESC
LIMIT 50;

-- 3. Verifica o extrato de pontos gerado
SELECT 
    ep.id,
    u.nome,
    u.email,
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
    AND event_id = @event_id
)
ORDER BY ep.created_at DESC
LIMIT 50;

-- 4. Verifica quantidade de ingressos por tipo no evento
SELECT 
    i.ticket_id,
    t.nome as tipo_ingresso,
    COUNT(DISTINCT i.id) as total_ingressos,
    COUNT(DISTINCT p.user_id) as usuarios_unicos
FROM ingressos i
INNER JOIN pedidos p ON i.pedido_id = p.id
LEFT JOIN tickets t ON i.ticket_id = t.id
WHERE i.event_id = @event_id
AND p.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH')
AND i.deleted_at IS NULL
AND p.deleted_at IS NULL
GROUP BY i.ticket_id, t.nome
ORDER BY total_ingressos DESC;

-- 5. Verifica quem tem o tipo de ingresso mas ainda não recebeu a conquista
SELECT 
    DISTINCT p.user_id,
    u.nome,
    u.email,
    COUNT(i.id) as qtd_ingressos
FROM pedidos p
INNER JOIN ingressos i ON i.pedido_id = p.id
INNER JOIN usuarios u ON p.user_id = u.id
WHERE p.evento_id = @event_id
AND i.event_id = @event_id
AND i.ticket_id = @ticket_id
AND p.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH')
AND p.deleted_at IS NULL
AND i.deleted_at IS NULL
AND NOT EXISTS (
    SELECT 1 FROM usuario_conquistas uc
    WHERE uc.user_id = p.user_id
    AND uc.conquista_id = @conquista_id
    AND uc.event_id = @event_id
)
GROUP BY p.user_id, u.nome, u.email
LIMIT 20;

-- ============================================
-- QUERY AUXILIAR: Descobrir IDs dos tipos de ingresso
-- ============================================
-- Use esta query para descobrir qual ticket_id usar:
SELECT 
    t.id as ticket_id,
    t.nome as tipo_ingresso,
    t.descricao,
    t.valor,
    COUNT(DISTINCT i.id) as total_vendidos,
    COUNT(DISTINCT p.user_id) as usuarios_unicos
FROM tickets t
LEFT JOIN ingressos i ON t.id = i.ticket_id AND i.event_id = @event_id
LEFT JOIN pedidos p ON i.pedido_id = p.id AND p.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH')
WHERE t.event_id = @event_id
GROUP BY t.id, t.nome, t.descricao, t.valor
ORDER BY total_vendidos DESC;

-- ============================================
-- NOTAS IMPORTANTES:
-- ============================================
-- 1. Configure as variáveis no início do script
-- 2. @event_id: ID do evento
-- 3. @ticket_id: ID do tipo de ingresso (use a query auxiliar para descobrir)
-- 4. Ajuste nome, descrição, pontos e nível da conquista
-- 5. O script previne duplicação automaticamente
-- 6. O código da conquista é gerado automaticamente
-- 7. Se precisar rodar novamente, não criará duplicatas
-- ============================================

-- ============================================
-- EXEMPLOS DE USO:
-- ============================================

-- EXEMPLO 1: Conquista para ingresso VIP
/*
SET @event_id = 17;
SET @ticket_id = 5;
SET @conquista_nome = 'Ingresso VIP';
SET @conquista_desc = 'Adquiriu ingresso VIP para o evento';
SET @conquista_pontos = 100;
SET @conquista_nivel = 'OURO';
*/

-- EXEMPLO 2: Conquista para meia-entrada
/*
SET @event_id = 17;
SET @ticket_id = 3;
SET @conquista_nome = 'Meia-Entrada';
SET @conquista_desc = 'Adquiriu ingresso com meia-entrada';
SET @conquista_pontos = 20;
SET @conquista_nivel = 'BRONZE';
*/

-- EXEMPLO 3: Conquista para combo de ingressos
/*
SET @event_id = 17;
SET @ticket_id = 8;
SET @conquista_nome = 'Combo Família';
SET @conquista_desc = 'Adquiriu combo de ingressos família';
SET @conquista_pontos = 75;
SET @conquista_nivel = 'PRATA';
*/

-- ============================================
-- ROLLBACK (CUIDADO - só use se algo der errado):
-- ============================================
/*
-- Limpa registros criados
DELETE FROM extrato_pontos 
WHERE referencia_tipo = 'usuario_conquista' 
AND referencia_id IN (
    SELECT id FROM usuario_conquistas 
    WHERE conquista_id = @conquista_id 
    AND event_id = @event_id
);

DELETE FROM usuario_conquistas 
WHERE conquista_id = @conquista_id 
AND event_id = @event_id;

-- Reverte pontos dos usuários (antes de deletar as conquistas)
UPDATE usuarios u
INNER JOIN (
    SELECT user_id, SUM(pontos) as total_pontos
    FROM usuario_conquistas
    WHERE conquista_id = @conquista_id
    AND event_id = @event_id
    GROUP BY user_id
) uc ON u.id = uc.user_id
SET u.pontos = COALESCE(u.pontos, 0) - uc.total_pontos;

DELETE FROM conquistas 
WHERE id = @conquista_id;
*/

