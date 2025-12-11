-- =====================================================
-- Tabela de Parcelas do Contrato
-- Armazena as parcelas sincronizadas do Asaas
-- =====================================================

CREATE TABLE IF NOT EXISTS `contrato_parcelas` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `contrato_id` INT(11) UNSIGNED NOT NULL,
    
    -- Dados do Asaas
    `asaas_payment_id` VARCHAR(100) NULL COMMENT 'ID do pagamento/parcela no Asaas',
    `asaas_installment_id` VARCHAR(100) NULL COMMENT 'ID do parcelamento no Asaas',
    
    -- Dados da parcela
    `numero_parcela` INT(11) NOT NULL DEFAULT 1,
    `valor` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `valor_liquido` DECIMAL(10,2) NULL COMMENT 'Valor líquido após taxas',
    `data_vencimento` DATE NOT NULL,
    `data_pagamento` DATE NULL,
    
    -- Status
    `status` VARCHAR(50) NOT NULL DEFAULT 'PENDING' COMMENT 'Status do Asaas: PENDING, RECEIVED, CONFIRMED, OVERDUE, etc',
    `status_local` ENUM('pendente', 'pago', 'vencido', 'cancelado') NOT NULL DEFAULT 'pendente',
    
    -- Comprovante
    `comprovante_url` VARCHAR(500) NULL,
    
    -- Forma de pagamento usada
    `forma_pagamento` VARCHAR(50) NULL COMMENT 'PIX, BOLETO, CREDIT_CARD, CASH',
    
    -- Observações
    `observacoes` TEXT NULL,
    
    -- Timestamps
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `synced_at` DATETIME NULL COMMENT 'Última sincronização com Asaas',
    
    PRIMARY KEY (`id`),
    KEY `idx_contrato_id` (`contrato_id`),
    KEY `idx_asaas_payment_id` (`asaas_payment_id`),
    KEY `idx_status` (`status`),
    KEY `idx_data_vencimento` (`data_vencimento`),
    
    CONSTRAINT `fk_parcela_contrato` FOREIGN KEY (`contrato_id`) 
        REFERENCES `contratos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

