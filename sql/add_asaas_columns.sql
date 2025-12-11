-- =====================================================
-- Script para adicionar campos de integração com Asaas
-- Execute este script no banco de dados MySQL
-- =====================================================

-- Tabela expositores: adicionar campo para armazenar customer_id do Asaas
ALTER TABLE `expositores` ADD COLUMN `asaas_customer_id` VARCHAR(100) NULL AFTER `observacoes`;

-- Tabela contratos: adicionar campos para armazenar dados da cobrança do Asaas
ALTER TABLE `contratos` ADD COLUMN `asaas_payment_id` VARCHAR(100) NULL AFTER `arquivo_comprovante`;
ALTER TABLE `contratos` ADD COLUMN `asaas_invoice_url` VARCHAR(500) NULL AFTER `asaas_payment_id`;
ALTER TABLE `contratos` ADD COLUMN `asaas_billing_type` VARCHAR(20) NULL COMMENT 'Tipo: BOLETO, PIX, CREDIT_CARD' AFTER `asaas_invoice_url`;

-- Índices para melhor performance
CREATE INDEX idx_expositores_asaas ON `expositores` (`asaas_customer_id`);
CREATE INDEX idx_contratos_asaas ON `contratos` (`asaas_payment_id`);

