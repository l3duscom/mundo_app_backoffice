-- Adiciona coluna 'codigo' na tabela conquistas
-- Este script deve ser executado ANTES de rodar a migration em produção
-- ou pode ser usado como referência se a migration já foi executada

-- Adiciona a coluna codigo
ALTER TABLE `conquistas` 
ADD COLUMN `codigo` VARCHAR(8) NOT NULL AFTER `id`;

-- Adiciona índice único
ALTER TABLE `conquistas` 
ADD UNIQUE KEY `unique_conquista_codigo` (`codigo`);

-- Gera códigos únicos para as conquistas existentes (caso existam)
-- NOTA: Execute este script manualmente se já houver conquistas cadastradas
UPDATE `conquistas` 
SET `codigo` = UPPER(SUBSTRING(MD5(CONCAT(id, RAND(), NOW())), 1, 8))
WHERE `codigo` = '' OR `codigo` IS NULL;

-- Verifica se todos os códigos foram gerados
SELECT COUNT(*) as total, 
       COUNT(DISTINCT codigo) as unicos,
       COUNT(*) - COUNT(DISTINCT codigo) as duplicados
FROM `conquistas`;

-- Se houver duplicados, execute novamente o UPDATE até que não haja mais duplicados

