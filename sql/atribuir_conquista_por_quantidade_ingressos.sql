-- ============================================
-- Atribui Conquista baseado em quantidade de ingressos específicos
-- ============================================
-- Este script:
-- 1. Busca uma conquista JÁ EXISTENTE
-- 2. Conta quantos ingressos do tipo cada usuário tem
-- 3. Multiplica os pontos pela quantidade de ingressos
-- 4. Atribui a conquista com pontos proporcionais
-- 5. Cria registro no extrato de pontos
-- 6. Atualiza o saldo de pontos dos usuários
-- ============================================

-- ============================================
-- CONFIGURAÇÕES - AJUSTE AQUI
-- ============================================
SET @event_id = 17;                           -- ID do evento
SET @ticket_id = 1;                           -- ID do tipo de ingresso
SET @conquista_id = 1;                        -- ID da conquista JÁ CRIADA
-- ============================================

-- Verifica se a conquista existe
SELECT 
    id,
    event_id,
    codigo,
    nome_conquista,
    descricao,
    pontos as pontos_base,
    nivel,
    status,
    CASE 
        WHEN status = 'ATIVA' THEN '✅ Ativa'
        ELSE '⚠️ Inativa'
    END as status_texto
FROM conquistas 
WHERE id = @conquista_id;

-- Se a query acima retornar vazio, a conquista não existe!
-- Crie primeiro via API ou SQL antes de continuar.

-- Obtém os pontos base da conquista
SET @pontos_base = (
    SELECT pontos FROM conquistas WHERE id = @conquista_id LIMIT 1
);

-- ============================================
-- Verifica quantos ingressos cada usuário tem
-- ============================================
SELECT 
    p.user_id,
    u.nome,
    u.email,
    COUNT(i.id) as qtd_ingressos,
    @pontos_base as pontos_por_ingresso,
    (COUNT(i.id) * @pontos_base) as pontos_totais,
    CASE 
        WHEN EXISTS (
            SELECT 1 FROM usuario_conquistas uc 
            WHERE uc.user_id = p.user_id 
            AND uc.conquista_id = @conquista_id 
            AND uc.event_id = @event_id
        ) THEN '❌ Já possui'
        ELSE '✅ Pode receber'
    END as status_conquista
FROM pedidos p
INNER JOIN ingressos i ON i.pedido_id = p.id
INNER JOIN usuarios u ON p.user_id = u.id
WHERE p.evento_id = @event_id
AND i.event_id = @event_id
AND i.ticket_id = @ticket_id
AND p.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH')
AND p.user_id IS NOT NULL
AND p.deleted_at IS NULL
AND i.deleted_at IS NULL
GROUP BY p.user_id, u.nome, u.email
ORDER BY qtd_ingressos DESC;

-- ============================================
-- Passo 1: Atribui conquista com pontos multiplicados
-- Usando transação para garantir integridade
-- ============================================
START TRANSACTION;

-- Insere em usuario_conquistas com pontos multiplicados pela quantidade
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
    subquery.user_id,
    subquery.pontos_totais, -- Pontos multiplicados pela quantidade
    1, -- atribuição por admin/sistema
    'ATIVA',
    NULL,
    NOW(),
    NOW()
FROM (
    SELECT 
        p.user_id,
        COUNT(i.id) as qtd_ingressos,
        (COUNT(i.id) * @pontos_base) as pontos_totais
    FROM pedidos p
    INNER JOIN ingressos i ON i.pedido_id = p.id
    WHERE p.evento_id = @event_id
    AND i.event_id = @event_id
    AND i.ticket_id = @ticket_id
    AND p.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH')
    AND p.user_id IS NOT NULL
    AND p.deleted_at IS NULL
    AND i.deleted_at IS NULL
    GROUP BY p.user_id
) AS subquery
WHERE NOT EXISTS (
    SELECT 1 FROM usuario_conquistas uc
    WHERE uc.user_id = subquery.user_id
    AND uc.conquista_id = @conquista_id
    AND uc.event_id = @event_id
);

-- Guarda quantidade de registros inseridos
SET @registros_inseridos = ROW_COUNT();

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
    uc.pontos, -- Usa os pontos já calculados
    COALESCE(u.pontos, 0) as saldo_anterior,
    COALESCE(u.pontos, 0) + uc.pontos as saldo_atual,
    CONCAT(
        'Conquista: ', c.nome_conquista, 
        ' - ', (uc.pontos / @pontos_base), ' ingresso(s) tipo ', @ticket_id,
        ' (', @pontos_base, ' pts cada)'
    ),
    'usuario_conquista',
    uc.id,
    NULL,
    NOW(),
    NOW()
FROM usuario_conquistas uc
INNER JOIN usuarios u ON u.id = uc.user_id
INNER JOIN conquistas c ON uc.conquista_id = c.id
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
SET u.pontos = COALESCE(u.pontos, 0) + uc.pontos
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
    (SELECT nome_conquista FROM conquistas WHERE id = @conquista_id) as 'Nome da Conquista',
    @pontos_base as 'Pontos Base (por ingresso)',
    @event_id as 'Event ID',
    @ticket_id as 'Ticket ID';

-- 1. Lista os usuários que receberam com detalhes
SELECT 
    uc.id as uc_id,
    u.id as user_id,
    u.nome,
    u.email,
    (uc.pontos / @pontos_base) as qtd_ingressos,
    @pontos_base as pontos_por_ingresso,
    uc.pontos as pontos_totais_ganhos,
    u.pontos as saldo_atual,
    uc.created_at as data_conquista
FROM usuario_conquistas uc
INNER JOIN usuarios u ON u.id = uc.user_id
WHERE uc.conquista_id = @conquista_id
AND uc.event_id = @event_id
ORDER BY uc.pontos DESC, uc.created_at DESC
LIMIT 100;

-- 2. Verifica o extrato de pontos gerado
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
ORDER BY ep.pontos DESC, ep.created_at DESC
LIMIT 100;

-- 3. Estatísticas por quantidade de ingressos
SELECT 
    (uc.pontos / @pontos_base) as qtd_ingressos,
    COUNT(*) as usuarios,
    SUM(uc.pontos) as pontos_totais_distribuidos,
    AVG(uc.pontos) as media_pontos,
    MIN(u.nome) as exemplo_usuario
FROM usuario_conquistas uc
INNER JOIN usuarios u ON u.id = uc.user_id
WHERE uc.conquista_id = @conquista_id
AND uc.event_id = @event_id
GROUP BY (uc.pontos / @pontos_base)
ORDER BY qtd_ingressos DESC;

-- 4. Total geral
SELECT 
    COUNT(DISTINCT uc.user_id) as total_usuarios,
    SUM(uc.pontos) as total_pontos_distribuidos,
    AVG(uc.pontos) as media_pontos_por_usuario,
    MAX(uc.pontos) as maior_pontuacao,
    MIN(uc.pontos) as menor_pontuacao
FROM usuario_conquistas uc
WHERE uc.conquista_id = @conquista_id
AND uc.event_id = @event_id;

-- 5. Verifica quem tem ingressos mas ainda não recebeu
SELECT 
    p.user_id,
    u.nome,
    u.email,
    COUNT(i.id) as qtd_ingressos,
    (COUNT(i.id) * @pontos_base) as pontos_que_receberia
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
ORDER BY qtd_ingressos DESC
LIMIT 50;

-- ============================================
-- QUERY AUXILIAR: Descobrir conquistas disponíveis
-- ============================================
SELECT 
    c.id as conquista_id,
    c.event_id,
    c.codigo,
    c.nome_conquista,
    c.descricao,
    c.pontos as pontos_base,
    c.nivel,
    c.status,
    COUNT(uc.id) as vezes_atribuida,
    SUM(uc.pontos) as pontos_distribuidos
FROM conquistas c
LEFT JOIN usuario_conquistas uc ON c.id = uc.conquista_id
WHERE c.event_id = @event_id
AND c.status = 'ATIVA'
GROUP BY c.id
ORDER BY c.created_at DESC;

-- ============================================
-- QUERY AUXILIAR: Descobrir tipos de ingresso
-- ============================================
SELECT 
    t.id as ticket_id,
    t.nome as tipo_ingresso,
    t.descricao,
    t.valor,
    COUNT(DISTINCT i.id) as total_ingressos_vendidos,
    COUNT(DISTINCT p.user_id) as usuarios_unicos,
    COUNT(DISTINCT p.id) as pedidos_distintos
FROM tickets t
LEFT JOIN ingressos i ON t.id = i.ticket_id AND i.event_id = @event_id
LEFT JOIN pedidos p ON i.pedido_id = p.id 
    AND p.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH')
    AND p.deleted_at IS NULL
WHERE t.event_id = @event_id
GROUP BY t.id, t.nome, t.descricao, t.valor
ORDER BY total_ingressos_vendidos DESC;

-- ============================================
-- NOTAS IMPORTANTES:
-- ============================================
-- 1. A conquista DEVE já estar criada antes de rodar este script
-- 2. Use @conquista_id para especificar qual conquista atribuir
-- 3. Os pontos da conquista são multiplicados pela quantidade de ingressos
-- 4. Exemplo: 3 ingressos VIP x 50 pontos = 150 pontos para o usuário
-- 5. Um usuário só pode receber a conquista UMA VEZ (mas com pontos multiplicados)
-- 6. O script previne duplicação automaticamente
-- 7. A descrição no extrato mostra quantos ingressos o usuário tinha

-- ============================================
-- EXEMPLOS DE USO:
-- ============================================

-- EXEMPLO 1: Conquista VIP (já criada com ID 5)
/*
SET @event_id = 17;
SET @ticket_id = 5;          -- ID do ingresso VIP
SET @conquista_id = 5;       -- ID da conquista VIP já criada

Resultado esperado:
- Usuário com 1 ingresso VIP: recebe 50 pontos (1 x 50)
- Usuário com 3 ingressos VIP: recebe 150 pontos (3 x 50)
- Usuário com 5 ingressos VIP: recebe 250 pontos (5 x 50)
*/

-- EXEMPLO 2: Múltiplas conquistas para diferentes tipos
/*
-- Primeiro: VIP
SET @event_id = 17;
SET @ticket_id = 5;
SET @conquista_id = 10;
-- Execute o script

-- Depois: Premium
SET @event_id = 17;
SET @ticket_id = 6;
SET @conquista_id = 11;
-- Execute o script novamente

-- Por fim: Básico
SET @event_id = 17;
SET @ticket_id = 3;
SET @conquista_id = 12;
-- Execute o script novamente
*/

-- ============================================
-- ROLLBACK (CUIDADO - só use se algo der errado):
-- ============================================
/*
-- Reverte pontos ANTES de deletar
UPDATE usuarios u
INNER JOIN usuario_conquistas uc ON u.id = uc.user_id
SET u.pontos = COALESCE(u.pontos, 0) - uc.pontos
WHERE uc.conquista_id = @conquista_id
AND uc.event_id = @event_id;

-- Limpa extrato
DELETE FROM extrato_pontos 
WHERE referencia_tipo = 'usuario_conquista' 
AND referencia_id IN (
    SELECT id FROM usuario_conquistas 
    WHERE conquista_id = @conquista_id 
    AND event_id = @event_id
);

-- Remove atribuições
DELETE FROM usuario_conquistas 
WHERE conquista_id = @conquista_id 
AND event_id = @event_id;
*/

-- ============================================
-- CASOS ESPECIAIS
-- ============================================

-- CASO 1: Usuário comprou ingressos em pedidos separados
/*
Pedido A: 2 ingressos VIP
Pedido B: 1 ingresso VIP
Total: 3 ingressos VIP

Este script conta TODOS os ingressos do usuário, 
independente de estarem em pedidos diferentes.
Resultado: 3 x 50 = 150 pontos
*/

-- CASO 2: Usuário tem ingressos de tipos diferentes
/*
Pedido: 2 VIP + 1 Premium + 1 Básico

Se rodar para ticket_id VIP (id=5):
Conta apenas os 2 VIP = 2 x 50 = 100 pontos

Se rodar para ticket_id Premium (id=6):
Conta apenas 1 Premium = 1 x 40 = 40 pontos

Cada tipo de ingresso pode ter sua própria conquista!
*/

-- CASO 3: Prevenção de duplicação
/*
Se tentar rodar o script duas vezes:
- 1ª execução: Atribui conquista normalmente
- 2ª execução: Verifica que já existe, não insere nada
- Pontos NÃO são duplicados

Seguro para executar múltiplas vezes!
*/

