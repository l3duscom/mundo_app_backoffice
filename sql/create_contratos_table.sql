-- ========================================
-- Script para criar a tabela de Contratos de Expositores
-- MySQL 5.7+ / MariaDB 10.2+
-- ========================================

CREATE TABLE IF NOT EXISTS `contratos` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID do evento',
    `expositor_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID do expositor',
    
    -- Dados do Contrato
    `codigo` VARCHAR(20) NULL COMMENT 'Código único do contrato (ex: CTR-2024-0001)',
    `descricao` VARCHAR(255) NULL COMMENT 'Descrição geral do contrato',
    
    -- Situação/Etapas do Contrato
    `situacao` ENUM(
        'proposta',
        'proposta_aceita', 
        'contrato_assinado',
        'pagamento_aberto',
        'pagamento_andamento',
        'pagamento_confirmado',
        'cancelado',
        'banido'
    ) NOT NULL DEFAULT 'proposta' COMMENT 'Situação atual do contrato',
    
    -- Valores Financeiros (calculados automaticamente dos itens)
    `valor_original` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor original (soma dos itens)',
    `valor_desconto` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor de desconto dos itens',
    `desconto_adicional` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Desconto adicional no contrato',
    `valor_final` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor final (original - descontos)',
    `quantidade_parcelas` INT(3) NOT NULL DEFAULT 1 COMMENT 'Quantidade de parcelas',
    `valor_parcela` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor de cada parcela',
    `valor_pago` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor já pago',
    `valor_em_aberto` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor ainda em aberto',
    
    -- Datas importantes
    `data_proposta` DATE NULL COMMENT 'Data da proposta',
    `data_aceite` DATE NULL COMMENT 'Data do aceite da proposta',
    `data_assinatura` DATE NULL COMMENT 'Data de assinatura do contrato',
    `data_vencimento` DATE NULL COMMENT 'Data de vencimento do pagamento',
    `data_pagamento` DATE NULL COMMENT 'Data do último pagamento',
    
    -- Forma de Pagamento
    `forma_pagamento` VARCHAR(50) NULL COMMENT 'Forma de pagamento (PIX, Cartão, Boleto, etc.)',
    
    -- Observações e Arquivos
    `observacoes` TEXT NULL,
    `arquivo_contrato` VARCHAR(255) NULL COMMENT 'Caminho do arquivo do contrato assinado',
    `arquivo_comprovante` VARCHAR(255) NULL COMMENT 'Caminho do comprovante de pagamento',
    
    -- Integração Asaas
    `asaas_payment_id` VARCHAR(100) NULL COMMENT 'ID do pagamento no Asaas',
    `asaas_invoice_url` VARCHAR(500) NULL COMMENT 'URL da fatura/boleto no Asaas',
    `asaas_billing_type` VARCHAR(20) NULL COMMENT 'Tipo: BOLETO, PIX, CREDIT_CARD',
    
    -- Controle
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    `deleted_at` DATETIME NULL,
    
    PRIMARY KEY (`id`),
    INDEX `contratos_event_id` (`event_id`),
    INDEX `contratos_expositor_id` (`expositor_id`),
    INDEX `contratos_situacao` (`situacao`),
    INDEX `contratos_codigo` (`codigo`),
    INDEX `contratos_deleted_at_id` (`deleted_at`, `id`),
    INDEX `contratos_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- IMPORTANTE: Se a tabela já existe, execute os comandos abaixo
-- ========================================

-- Adicionar coluna desconto_adicional (OBRIGATÓRIO para tabelas existentes)
ALTER TABLE `contratos` ADD COLUMN IF NOT EXISTS `desconto_adicional` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `valor_desconto`;

-- Remover colunas antigas (se existirem) - Opcional
-- ALTER TABLE `contratos` DROP COLUMN IF EXISTS `tipo_contrato`;
-- ALTER TABLE `contratos` DROP COLUMN IF EXISTS `localizacao`;
-- ALTER TABLE `contratos` DROP COLUMN IF EXISTS `metragem`;

-- ========================================
-- Para remover a tabela (CUIDADO!)
-- ========================================
-- DROP TABLE IF EXISTS `contratos`;
