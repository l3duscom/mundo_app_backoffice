-- ============================================
-- Script Genérico para Atribuir Conquista a Usuários Específicos
-- ============================================
-- INSTRUÇÕES:
-- 1. Substitua os valores entre {CHAVES} pelos valores reais
-- 2. Execute linha por linha ou tudo de uma vez
-- 3. O script é seguro e previne duplicatas
-- ============================================

-- CONFIGURAÇÃO - Substitua estes valores:
SET @event_id = {EVENT_ID};                     -- Ex: 17
SET @conquista_nome = '{NOME_CONQUISTA}';       -- Ex: 'Comprou Ingresso'
SET @conquista_desc = '{DESCRICAO}';            -- Ex: 'Adquiriu ingresso para o evento'
SET @conquista_pontos = {PONTOS};               -- Ex: 15
SET @conquista_nivel = '{NIVEL}';               -- Ex: 'BRONZE'

-- ============================================
-- 1. Criar ou Obter Conquista
-- ============================================

-- Cria a conquista se não existir
INSERT INTO `conquistas` (
    `event_id`, 
    `nome_conquista`, 
    `descricao`, 
    `pontos`, 
    `nivel`, 
    `status`, 
    `created_at`, 
    `updated_at`
)
SELECT 
    @event_id, 
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

-- Obtém o ID da conquista
SET @conquista_id = (
    SELECT id FROM conquistas 
    WHERE event_id = @event_id 
    AND nome_conquista = @conquista_nome 
    LIMIT 1
);

-- Obtém os pontos (caso já existisse e queira usar o valor atual)
SET @pontos = (SELECT pontos FROM conquistas WHERE id = @conquista_id);

-- ============================================
-- 2. Selecione um dos cenários abaixo:
-- ============================================

-- CENÁRIO 1: Usuários com PEDIDOS no evento
-- ------------------------------------------
/*
CREATE TEMPORARY TABLE temp_usuarios_conquista AS
SELECT DISTINCT c.usuario_id
FROM pedidos p
INNER JOIN clientes c ON p.cliente_id = c.id
WHERE p.evento_id = @event_id
AND p.situacao IN ('aprovado', 'autorizado', 'paga')
AND c.usuario_id IS NOT NULL;
*/

-- CENÁRIO 2: Usuários com INGRESSOS no evento
-- ------------------------------------------
/*
CREATE TEMPORARY TABLE temp_usuarios_conquista AS
SELECT DISTINCT i.usuario_id
FROM ingressos i
WHERE i.evento_id = @event_id
AND i.situacao = 'ativo'
AND i.usuario_id IS NOT NULL;
*/

-- CENÁRIO 3: Usuários com INSCRIÇÕES em concursos do evento
-- ----------------------------------------------------------
/*
CREATE TEMPORARY TABLE temp_usuarios_conquista AS
SELECT DISTINCT i.user_id as usuario_id
FROM inscricoes i
INNER JOIN concursos con ON i.concurso_id = con.id
WHERE con.evento_id = @event_id
AND i.status IN ('INICIADA', 'APROVADA')
AND i.user_id IS NOT NULL;
*/

-- CENÁRIO 4: Lista específica de usuários (IDs manualmente)
-- ----------------------------------------------------------
/*
CREATE TEMPORARY TABLE temp_usuarios_conquista AS
SELECT usuario_id FROM (
    SELECT 1 as usuario_id
    UNION ALL SELECT 5
    UNION ALL SELECT 10
    UNION ALL SELECT 15
    -- Adicione mais IDs conforme necessário
) as user_list;
*/

-- CENÁRIO 5: TODOS os usuários ativos
-- ------------------------------------
/*
CREATE TEMPORARY TABLE temp_usuarios_conquista AS
SELECT id as usuario_id
FROM usuarios
WHERE ativo = 1
AND deleted_at IS NULL;
*/

-- ============================================
-- 3. Atribuir Conquista (Funciona para todos os cenários acima)
-- ============================================

START TRANSACTION;

-- Insere em usuario_conquistas
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
SELECT 
    @conquista_id,
    @event_id,
    t.usuario_id,
    @pontos,
    1,
    'ATIVA',
    NULL,
    NOW(),
    NOW()
FROM temp_usuarios_conquista t
WHERE NOT EXISTS (
    SELECT 1 FROM usuario_conquistas uc
    WHERE uc.user_id = t.usuario_id
    AND uc.conquista_id = @conquista_id
    AND uc.event_id = @event_id
);

-- Cria registros no extrato_pontos
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
    @pontos,
    COALESCE(u.pontos, 0),
    COALESCE(u.pontos, 0) + @pontos,
    CONCAT('Conquista: ', @conquista_nome),
    'usuario_conquista',
    uc.id,
    NULL,
    NOW(),
    NOW()
FROM usuario_conquistas uc
INNER JOIN usuarios u ON u.id = uc.user_id
WHERE uc.conquista_id = @conquista_id
AND uc.event_id = @event_id
AND uc.created_at >= NOW() - INTERVAL 1 MINUTE
AND NOT EXISTS (
    SELECT 1 FROM extrato_pontos ep
    WHERE ep.user_id = uc.user_id
    AND ep.referencia_tipo = 'usuario_conquista'
    AND ep.referencia_id = uc.id
);

-- Atualiza saldo dos usuários
UPDATE usuarios u
INNER JOIN usuario_conquistas uc ON uc.user_id = u.id
SET u.pontos = COALESCE(u.pontos, 0) + @pontos
WHERE uc.conquista_id = @conquista_id
AND uc.event_id = @event_id
AND uc.created_at >= NOW() - INTERVAL 1 MINUTE;

COMMIT;

-- Limpa tabela temporária
DROP TEMPORARY TABLE IF EXISTS temp_usuarios_conquista;

-- ============================================
-- 4. Verificações
-- ============================================

SELECT 
    CONCAT('Conquista ID: ', @conquista_id, ' | Pontos: ', @pontos) as 'Informações da Conquista';

SELECT 
    COUNT(*) as 'Total de Usuários que Receberam'
FROM usuario_conquistas
WHERE conquista_id = @conquista_id
AND event_id = @event_id;

-- Lista detalhada
SELECT 
    u.id,
    u.nome,
    u.email,
    uc.pontos as pontos_ganhos,
    u.pontos as saldo_total,
    uc.created_at
FROM usuario_conquistas uc
INNER JOIN usuarios u ON u.id = uc.user_id
WHERE uc.conquista_id = @conquista_id
AND uc.event_id = @event_id
ORDER BY uc.created_at DESC
LIMIT 50;

-- ============================================
-- FIM DO SCRIPT
-- ============================================

