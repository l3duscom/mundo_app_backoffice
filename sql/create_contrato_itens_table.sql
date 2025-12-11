-- ========================================
-- Script para criar a tabela de Itens do Contrato
-- MySQL 5.7+ / MariaDB 10.2+
-- ========================================

CREATE TABLE IF NOT EXISTS `contrato_itens` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `contrato_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID do contrato',
    
    -- Dados do Item
    `tipo_item` VARCHAR(50) NOT NULL COMMENT 'Tipo: Espaço Comercial, Cota, Patrocínio, Artist Alley, Food Park, etc.',
    `descricao` VARCHAR(255) NULL COMMENT 'Descrição do item/espaço contratado',
    `localizacao` VARCHAR(100) NULL COMMENT 'Localização do espaço (ex: Stand A-15, Mesa 23)',
    `metragem` VARCHAR(20) NULL COMMENT 'Tamanho do espaço (ex: 3x3m, 2x2m)',
    
    -- Valores
    `quantidade` INT(5) NOT NULL DEFAULT 1 COMMENT 'Quantidade de itens',
    `valor_unitario` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor unitário do item',
    `valor_desconto` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor de desconto no item',
    `valor_total` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor total (qtd * unitário - desconto)',
    
    -- Observações
    `observacoes` TEXT NULL,
    
    -- Controle
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    `deleted_at` DATETIME NULL,
    
    PRIMARY KEY (`id`),
    INDEX `contrato_itens_contrato_id` (`contrato_id`),
    INDEX `contrato_itens_tipo_item` (`tipo_item`),
    INDEX `contrato_itens_deleted_at_id` (`deleted_at`, `id`),
    
    CONSTRAINT `fk_contrato_itens_contrato` 
        FOREIGN KEY (`contrato_id`) 
        REFERENCES `contratos` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Exemplos de INSERT (opcional)
-- ========================================

-- Exemplo: Adicionar itens a um contrato
-- INSERT INTO `contrato_itens` (
--     `contrato_id`, `tipo_item`, `descricao`, `localizacao`, `metragem`,
--     `quantidade`, `valor_unitario`, `valor_desconto`, `valor_total`,
--     `created_at`, `updated_at`
-- ) VALUES 
-- (1, 'Espaço Comercial', 'Stand comercial área principal', 'Stand A-15', '3x3m', 1, 2500.00, 250.00, 2250.00, NOW(), NOW()),
-- (1, 'Cota', 'Cota de patrocínio Bronze', NULL, NULL, 1, 1000.00, 0.00, 1000.00, NOW(), NOW()),
-- (1, 'Energia Elétrica', 'Ponto de energia adicional', 'Stand A-15', NULL, 2, 150.00, 0.00, 300.00, NOW(), NOW());

-- ========================================
-- Para remover a tabela (CUIDADO!)
-- ========================================
-- DROP TABLE IF EXISTS `contrato_itens`;

