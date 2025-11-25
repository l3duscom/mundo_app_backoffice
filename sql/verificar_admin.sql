-- ============================================================
-- VERIFICAR SE USUÁRIO É ADMIN
-- ============================================================
-- Use este script para verificar e corrigir permissões de admin
-- ============================================================

-- SUBSTITUA ESTE EMAIL PELO SEU EMAIL DE LOGIN
SET @seu_email = 'seu-email@exemplo.com';

-- ============================================================
-- 1. VERIFICAR DADOS DO USUÁRIO
-- ============================================================
SELECT 
    u.id AS usuario_id,
    u.nome,
    u.email,
    u.ativo,
    CASE 
        WHEN gu.grupo_id IS NOT NULL THEN 'SIM'
        ELSE 'NÃO'
    END AS eh_admin,
    g.nome AS grupo_nome
FROM usuarios u
LEFT JOIN grupos_usuarios gu ON u.id = gu.usuario_id AND gu.grupo_id = 1
LEFT JOIN grupos g ON gu.grupo_id = g.id
WHERE u.email = @seu_email;

-- ============================================================
-- 2. VERIFICAR TODOS OS GRUPOS DO USUÁRIO
-- ============================================================
SELECT 
    u.id AS usuario_id,
    u.nome AS usuario_nome,
    u.email,
    g.id AS grupo_id,
    g.nome AS grupo_nome,
    g.descricao AS grupo_descricao
FROM usuarios u
INNER JOIN grupos_usuarios gu ON u.id = gu.usuario_id
INNER JOIN grupos g ON gu.grupo_id = g.id
WHERE u.email = @seu_email;

-- ============================================================
-- 3. VERIFICAR ESTRUTURA DA TABELA GRUPOS
-- ============================================================
SELECT * FROM grupos ORDER BY id;

-- ============================================================
-- 4. ADICIONAR USUÁRIO AO GRUPO ADMIN (SE NECESSÁRIO)
-- ============================================================
-- DESCOMENTE AS LINHAS ABAIXO E EXECUTE SE PRECISAR ADICIONAR ADMIN

-- Primeiro, pegue o ID do seu usuário
-- SELECT id FROM usuarios WHERE email = @seu_email;

-- Substitua 999 pelo ID do seu usuário encontrado acima
-- SET @meu_usuario_id = 999;

-- Verifica se já está no grupo admin (grupo_id = 1)
-- SELECT * FROM grupos_usuarios WHERE usuario_id = @meu_usuario_id AND grupo_id = 1;

-- Se não estiver, adicione:
-- INSERT INTO grupos_usuarios (grupo_id, usuario_id, created_at, updated_at)
-- VALUES (1, @meu_usuario_id, NOW(), NOW());

-- Verifique novamente:
-- SELECT 
--     u.id AS usuario_id,
--     u.nome,
--     u.email,
--     g.nome AS grupo_nome
-- FROM usuarios u
-- INNER JOIN grupos_usuarios gu ON u.id = gu.usuario_id
-- INNER JOIN grupos g ON gu.grupo_id = g.id
-- WHERE u.id = @meu_usuario_id;

-- ============================================================
-- 5. ALTERNATIVA: ADICIONAR ADMIN POR ID DIRETO
-- ============================================================
-- Se você souber seu ID de usuário, use este método mais direto:

-- SUBSTITUA 999 PELO SEU ID DE USUÁRIO
-- INSERT INTO grupos_usuarios (grupo_id, usuario_id, created_at, updated_at)
-- SELECT 1, 999, NOW(), NOW()
-- WHERE NOT EXISTS (
--     SELECT 1 FROM grupos_usuarios 
--     WHERE grupo_id = 1 AND usuario_id = 999
-- );

-- ============================================================
-- 6. VERIFICAR SESSÃO ATIVA (Opcional)
-- ============================================================
-- Se você estiver usando sessões de banco de dados:
-- SELECT * FROM ci_sessions 
-- WHERE data LIKE '%usuario_id%' 
-- ORDER BY timestamp DESC 
-- LIMIT 5;

-- ============================================================
-- NOTAS IMPORTANTES:
-- ============================================================
-- 1. O grupo_id = 1 é SEMPRE o grupo de administradores
-- 2. Não altere o ID do grupo admin no banco
-- 3. Após adicionar ao grupo admin, faça logout e login novamente
-- 4. Limpe o cache/sessão se necessário
-- ============================================================

