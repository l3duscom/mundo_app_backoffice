-- SQL para testar a funcionalidade de retirar pontos

-- ==============================================
-- 1. VERIFICAR SALDO DO USUÁRIO ANTES
-- ==============================================
SET @usuario_id = 123; -- Ajuste conforme necessário

SELECT 
    id,
    nome,
    email,
    pontos as saldo_atual
FROM usuarios 
WHERE id = @usuario_id;

-- ==============================================
-- 2. VER ÚLTIMAS TRANSAÇÕES DO USUÁRIO
-- ==============================================
SELECT 
    ep.id,
    ep.tipo_transacao,
    ep.pontos,
    ep.saldo_anterior,
    ep.saldo_atual,
    ep.descricao,
    ep.created_at,
    u_admin.nome as admin_nome
FROM extrato_pontos ep
LEFT JOIN usuarios u_admin ON u_admin.id = ep.admin
WHERE ep.usuario_id = @usuario_id
ORDER BY ep.created_at DESC
LIMIT 10;

-- ==============================================
-- 3. SIMULAR RETIRADA (TESTE MANUAL)
-- ==============================================
-- ATENÇÃO: Use a API em vez deste SQL para garantir atomicidade!

START TRANSACTION;

-- Variáveis da operação
SET @pontos_retirar = 100;
SET @motivo = 'Teste de retirada manual';
SET @event_id = 17;
SET @admin_id = 1;

-- Buscar saldo atual
SELECT @saldo_anterior := pontos INTO @saldo_anterior
FROM usuarios
WHERE id = @usuario_id;

-- Calcular novo saldo
SET @saldo_atual = @saldo_anterior - @pontos_retirar;

-- Verificar se tem saldo suficiente
SELECT 
    CASE 
        WHEN @saldo_anterior >= @pontos_retirar THEN 'OK - Saldo suficiente'
        ELSE CONCAT('ERRO - Saldo insuficiente. Tem ', @saldo_anterior, ' pontos, precisa de ', @pontos_retirar)
    END as validacao;

-- Se quiser prosseguir com o teste manual:
-- (Descomente as linhas abaixo)

/*
-- Atualizar saldo do usuário
UPDATE usuarios 
SET pontos = @saldo_atual
WHERE id = @usuario_id;

-- Criar registro no extrato
INSERT INTO extrato_pontos (
    usuario_id,
    event_id,
    tipo_transacao,
    pontos,
    saldo_anterior,
    saldo_atual,
    descricao,
    admin,
    created_at
) VALUES (
    @usuario_id,
    @event_id,
    'DEBITO',
    @pontos_retirar,
    @saldo_anterior,
    @saldo_atual,
    @motivo,
    @admin_id,
    NOW()
);

COMMIT;
*/

-- Para cancelar o teste:
ROLLBACK;

-- ==============================================
-- 4. VERIFICAR RESULTADO APÓS RETIRADA
-- ==============================================
SELECT 
    id,
    nome,
    email,
    pontos as saldo_atual
FROM usuarios 
WHERE id = @usuario_id;

-- Verificar último registro do extrato
SELECT 
    ep.*,
    u_admin.nome as admin_nome
FROM extrato_pontos ep
LEFT JOIN usuarios u_admin ON u_admin.id = ep.admin
WHERE ep.usuario_id = @usuario_id
ORDER BY ep.created_at DESC
LIMIT 1;

-- ==============================================
-- 5. RELATÓRIO DE DÉBITOS POR PERÍODO
-- ==============================================
SELECT 
    DATE(ep.created_at) as data,
    COUNT(*) as total_retiradas,
    SUM(ep.pontos) as total_pontos_retirados,
    u_admin.nome as admin_responsavel
FROM extrato_pontos ep
LEFT JOIN usuarios u_admin ON u_admin.id = ep.admin
WHERE ep.tipo_transacao = 'DEBITO'
AND ep.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(ep.created_at), u_admin.nome
ORDER BY data DESC;

-- ==============================================
-- 6. USUÁRIOS COM MAIS RETIRADAS
-- ==============================================
SELECT 
    u.id,
    u.nome,
    u.email,
    u.pontos as saldo_atual,
    COUNT(ep.id) as total_retiradas,
    SUM(ep.pontos) as total_pontos_retirados
FROM usuarios u
INNER JOIN extrato_pontos ep ON ep.usuario_id = u.id
WHERE ep.tipo_transacao = 'DEBITO'
GROUP BY u.id, u.nome, u.email, u.pontos
ORDER BY total_pontos_retirados DESC
LIMIT 20;

-- ==============================================
-- 7. VERIFICAR INTEGRIDADE DOS DADOS
-- ==============================================
-- Verificar se saldo_atual do extrato bate com saldo do usuário
SELECT 
    u.id,
    u.nome,
    u.pontos as saldo_usuario,
    ep.saldo_atual as saldo_ultimo_extrato,
    (u.pontos - ep.saldo_atual) as diferenca,
    ep.created_at as data_ultimo_extrato
FROM usuarios u
LEFT JOIN (
    SELECT 
        usuario_id,
        saldo_atual,
        created_at,
        ROW_NUMBER() OVER (PARTITION BY usuario_id ORDER BY created_at DESC) as rn
    FROM extrato_pontos
) ep ON ep.usuario_id = u.id AND ep.rn = 1
WHERE u.pontos != COALESCE(ep.saldo_atual, u.pontos)
LIMIT 20;

-- ==============================================
-- 8. AUDITORIA - RETIRADAS POR ADMIN
-- ==============================================
SELECT 
    u_admin.id as admin_id,
    u_admin.nome as admin_nome,
    COUNT(DISTINCT ep.usuario_id) as usuarios_afetados,
    COUNT(ep.id) as total_operacoes,
    SUM(ep.pontos) as total_pontos_retirados,
    MIN(ep.created_at) as primeira_operacao,
    MAX(ep.created_at) as ultima_operacao
FROM extrato_pontos ep
INNER JOIN usuarios u_admin ON u_admin.id = ep.admin
WHERE ep.tipo_transacao = 'DEBITO'
GROUP BY u_admin.id, u_admin.nome
ORDER BY total_pontos_retirados DESC;

-- ==============================================
-- 9. HISTÓRICO COMPLETO DE UM USUÁRIO
-- ==============================================
SELECT 
    ep.id,
    ep.created_at as data_hora,
    ep.tipo_transacao,
    ep.pontos,
    ep.saldo_anterior,
    ep.saldo_atual,
    ep.descricao,
    CASE 
        WHEN ep.tipo_transacao = 'CREDITO' THEN CONCAT('+', ep.pontos)
        WHEN ep.tipo_transacao = 'DEBITO' THEN CONCAT('-', ep.pontos)
        ELSE ep.pontos
    END as movimentacao,
    u_admin.nome as responsavel,
    e.nome as evento
FROM extrato_pontos ep
LEFT JOIN usuarios u_admin ON u_admin.id = ep.admin
LEFT JOIN eventos e ON e.id = ep.event_id
WHERE ep.usuario_id = @usuario_id
ORDER BY ep.created_at DESC;

-- ==============================================
-- 10. ESTATÍSTICAS GERAIS
-- ==============================================
SELECT 
    'Total de Usuários' as metrica,
    COUNT(*) as valor
FROM usuarios
UNION ALL
SELECT 
    'Usuários com Pontos' as metrica,
    COUNT(*) as valor
FROM usuarios
WHERE pontos > 0
UNION ALL
SELECT 
    'Total de Pontos no Sistema' as metrica,
    SUM(pontos) as valor
FROM usuarios
UNION ALL
SELECT 
    'Média de Pontos por Usuário' as metrica,
    ROUND(AVG(pontos), 2) as valor
FROM usuarios
WHERE pontos > 0
UNION ALL
SELECT 
    'Total de Transações de Débito' as metrica,
    COUNT(*) as valor
FROM extrato_pontos
WHERE tipo_transacao = 'DEBITO'
UNION ALL
SELECT 
    'Total de Pontos Retirados' as metrica,
    SUM(pontos) as valor
FROM extrato_pontos
WHERE tipo_transacao = 'DEBITO';

