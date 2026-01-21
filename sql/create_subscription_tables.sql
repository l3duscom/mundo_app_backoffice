-- ========================================
-- SQL para criar as tabelas do módulo de assinaturas
-- Execute este script no seu banco de dados
-- ========================================

-- Adicionar campos na tabela usuarios (se não existirem)
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS is_premium TINYINT(1) NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS premium_ate DATETIME NULL,
ADD COLUMN IF NOT EXISTS asaas_subscription_id VARCHAR(100) NULL;

-- Criar índice para otimizar consultas de usuários premium
CREATE INDEX IF NOT EXISTS idx_usuarios_premium ON usuarios(is_premium, premium_ate);

-- ========================================
-- Tabela de Planos
-- ========================================
CREATE TABLE IF NOT EXISTS planos (
    id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    descricao TEXT NULL,
    preco DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    ciclo ENUM('MONTHLY', 'YEARLY') NOT NULL DEFAULT 'MONTHLY',
    beneficios TEXT NULL COMMENT 'JSON com lista de benefícios',
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_planos_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Tabela de Assinaturas
-- ========================================
CREATE TABLE IF NOT EXISTS assinaturas (
    id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT(11) UNSIGNED NOT NULL,
    plano_id INT(11) UNSIGNED NOT NULL,
    asaas_subscription_id VARCHAR(100) NULL,
    asaas_customer_id VARCHAR(100) NULL,
    status ENUM('PENDING', 'ACTIVE', 'OVERDUE', 'CANCELLED', 'EXPIRED') NOT NULL DEFAULT 'PENDING',
    data_inicio DATETIME NULL,
    data_fim DATETIME NULL,
    proximo_vencimento DATE NULL,
    valor_pago DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    forma_pagamento VARCHAR(50) NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,
    PRIMARY KEY (id),
    KEY idx_assinaturas_usuario (usuario_id),
    KEY idx_assinaturas_plano (plano_id),
    KEY idx_assinaturas_status (status),
    KEY idx_assinaturas_asaas (asaas_subscription_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Tabela de Histórico de Assinaturas
-- ========================================
CREATE TABLE IF NOT EXISTS assinatura_historicos (
    id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    assinatura_id INT(11) UNSIGNED NOT NULL,
    evento ENUM('CREATED', 'PAYMENT_CONFIRMED', 'PAYMENT_FAILED', 'RENEWED', 'CANCELLED', 'EXPIRED', 'REACTIVATED') NOT NULL DEFAULT 'CREATED',
    descricao VARCHAR(255) NULL,
    dados_json TEXT NULL COMMENT 'Dados adicionais em JSON',
    created_at DATETIME NULL,
    PRIMARY KEY (id),
    KEY idx_historico_assinatura (assinatura_id),
    KEY idx_historico_evento (evento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Inserir plano padrão (Premium Mensal)
-- ========================================
INSERT INTO planos (nome, slug, descricao, preco, ciclo, beneficios, ativo, created_at) VALUES 
('Premium Mensal', 'premium-mensal', 'Acesso premium com todos os benefícios', 29.90, 'MONTHLY', '["Acesso ilimitado", "Suporte prioritário", "Recursos exclusivos"]', 1, NOW());
