-- ============================================
-- Script de verificação da estrutura de conquistas
-- ============================================
-- Execute este script para verificar se todas as tabelas e colunas
-- necessárias para o sistema de conquistas estão criadas

-- 1. Verifica se a tabela conquistas existe
SELECT 
    'conquistas' as tabela,
    CASE WHEN COUNT(*) > 0 THEN 'OK' ELSE 'FALTA CRIAR' END as status
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'conquistas';

-- 2. Verifica se a tabela usuario_conquistas existe
SELECT 
    'usuario_conquistas' as tabela,
    CASE WHEN COUNT(*) > 0 THEN 'OK' ELSE 'FALTA CRIAR' END as status
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'usuario_conquistas';

-- 3. Verifica se a tabela extrato_pontos existe
SELECT 
    'extrato_pontos' as tabela,
    CASE WHEN COUNT(*) > 0 THEN 'OK' ELSE 'FALTA CRIAR' END as status
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'extrato_pontos';

-- 4. Verifica se a coluna pontos existe na tabela usuarios
SELECT 
    'usuarios.pontos' as coluna,
    CASE WHEN COUNT(*) > 0 THEN 'OK' ELSE 'FALTA CRIAR' END as status
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'usuarios'
AND COLUMN_NAME = 'pontos';

-- 5. Lista todas as colunas da tabela conquistas
SELECT 
    'Colunas da tabela conquistas:' as info;
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'conquistas'
ORDER BY ORDINAL_POSITION;

-- 6. Lista todas as colunas da tabela usuario_conquistas
SELECT 
    'Colunas da tabela usuario_conquistas:' as info;
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'usuario_conquistas'
ORDER BY ORDINAL_POSITION;

-- 7. Lista todas as colunas da tabela extrato_pontos
SELECT 
    'Colunas da tabela extrato_pontos:' as info;
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'extrato_pontos'
ORDER BY ORDINAL_POSITION;

-- 8. Verifica as foreign keys
SELECT 
    'Foreign keys verificadas:' as info;
SELECT 
    TABLE_NAME,
    CONSTRAINT_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME IN ('conquistas', 'usuario_conquistas', 'extrato_pontos')
AND REFERENCED_TABLE_NAME IS NOT NULL;

