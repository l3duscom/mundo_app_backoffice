-- ============================================
-- Adiciona coluna de pontos na tabela usuarios
-- ============================================

-- Verifica se a coluna já existe antes de adicionar
-- Execute este script apenas se a coluna 'pontos' não existir

ALTER TABLE `usuarios` 
ADD COLUMN `pontos` INT(11) NOT NULL DEFAULT 0 COMMENT 'Total de pontos acumulados pelo usuário' 
AFTER `ativo`;

-- Adiciona índice para performance em queries de ranking
ALTER TABLE `usuarios` 
ADD INDEX `usuarios_pontos` (`pontos`);

-- ============================================
-- Para remover (se necessário):
-- ============================================
-- ALTER TABLE `usuarios` DROP COLUMN `pontos`;
-- ALTER TABLE `usuarios` DROP INDEX `usuarios_pontos`;

