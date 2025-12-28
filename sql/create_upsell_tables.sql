-- =====================================================
-- Módulo de Upsell de Ingressos
-- Configuração de upgrades de tickets
-- =====================================================

-- Tabela de configuração de upsells
CREATE TABLE IF NOT EXISTS `ticket_upsells` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `event_id` INT UNSIGNED NOT NULL COMMENT 'Evento',
    
    `ticket_origem_id` INT UNSIGNED NOT NULL COMMENT 'Ticket que pode ser atualizado',
    `ticket_destino_id` INT UNSIGNED NOT NULL COMMENT 'Ticket de destino (upgrade)',
    
    `valor_diferenca` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor calculado da diferença',
    `valor_customizado` DECIMAL(10,2) NULL COMMENT 'Valor customizado (se diferente da diferença)',
    `desconto_percentual` DECIMAL(5,2) NULL COMMENT 'Desconto em % sobre a diferença',
    
    `titulo` VARCHAR(255) NULL COMMENT 'Título para exibição (ex: Upgrade para VIP)',
    `descricao` TEXT NULL COMMENT 'Descrição do benefício',
    
    `ativo` TINYINT(1) NOT NULL DEFAULT 1,
    `ordem` INT NOT NULL DEFAULT 0 COMMENT 'Ordem de exibição',
    
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY `uk_origem_destino` (`ticket_origem_id`, `ticket_destino_id`),
    INDEX `idx_evento` (`event_id`),
    INDEX `idx_origem` (`ticket_origem_id`),
    INDEX `idx_destino` (`ticket_destino_id`),
    INDEX `idx_ativo` (`ativo`),
    FOREIGN KEY (`event_id`) REFERENCES `eventos`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`ticket_origem_id`) REFERENCES `tickets`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`ticket_destino_id`) REFERENCES `tickets`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
