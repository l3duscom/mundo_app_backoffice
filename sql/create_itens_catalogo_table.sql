-- ========================================
-- Script para criar a tabela de Catálogo de Itens
-- MySQL 5.7+ / MariaDB 10.2+
-- ========================================

CREATE TABLE IF NOT EXISTS `itens_catalogo` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID do evento',
    
    -- Dados do Item
    `nome` VARCHAR(100) NOT NULL COMMENT 'Nome do item (ex: Stand Comercial 3x3)',
    `tipo` VARCHAR(50) NOT NULL COMMENT 'Tipo: Espaço Comercial, Cota, Patrocínio, etc.',
    `descricao` TEXT NULL COMMENT 'Descrição detalhada do item',
    `metragem` VARCHAR(20) NULL COMMENT 'Tamanho padrão (ex: 3x3m)',
    
    -- Valores
    `valor` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor padrão do item',
    
    -- Status
    `ativo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Ativo, 0=Inativo',
    
    -- Controle
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    `deleted_at` DATETIME NULL,
    
    PRIMARY KEY (`id`),
    INDEX `itens_catalogo_event_id` (`event_id`),
    INDEX `itens_catalogo_tipo` (`tipo`),
    INDEX `itens_catalogo_ativo` (`ativo`),
    INDEX `itens_catalogo_deleted_at_id` (`deleted_at`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Para alterar tabela existente (se já existir)
-- ========================================
-- ALTER TABLE `itens_catalogo` ADD COLUMN `event_id` INT(11) UNSIGNED NOT NULL AFTER `id`;
-- ALTER TABLE `itens_catalogo` ADD INDEX `itens_catalogo_event_id` (`event_id`);

-- ========================================
-- Exemplo de INSERT (substitua EVENT_ID pelo ID real do evento)
-- ========================================

-- INSERT INTO `itens_catalogo` (`event_id`, `nome`, `tipo`, `descricao`, `metragem`, `valor`, `ativo`, `created_at`, `updated_at`) VALUES
-- (EVENT_ID, 'Stand Comercial 2x2', 'Espaço Comercial', 'Stand comercial tamanho pequeno', '2x2m', 1500.00, 1, NOW(), NOW()),
-- (EVENT_ID, 'Stand Comercial 3x3', 'Espaço Comercial', 'Stand comercial tamanho médio', '3x3m', 2500.00, 1, NOW(), NOW()),
-- (EVENT_ID, 'Mesa Artist Alley', 'Artist Alley', 'Mesa individual para artistas', '1.5x0.8m', 350.00, 1, NOW(), NOW()),
-- (EVENT_ID, 'Food Park Pequeno', 'Food Park', 'Espaço food park pequeno', '3x3m', 3000.00, 1, NOW(), NOW()),
-- (EVENT_ID, 'Cota Bronze', 'Cota', 'Cota de patrocínio Bronze', NULL, 5000.00, 1, NOW(), NOW()),
-- (EVENT_ID, 'Cota Prata', 'Cota', 'Cota de patrocínio Prata', NULL, 10000.00, 1, NOW(), NOW()),
-- (EVENT_ID, 'Cota Ouro', 'Cota', 'Cota de patrocínio Ouro', NULL, 20000.00, 1, NOW(), NOW());

-- ========================================
-- Para remover a tabela (CUIDADO!)
-- ========================================
-- DROP TABLE IF EXISTS `itens_catalogo`;

