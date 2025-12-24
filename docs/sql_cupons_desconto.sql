-- =====================================================
-- SQL para Módulo de Cupons de Desconto
-- Execute manualmente no banco de dados
-- =====================================================

-- 1. Criar tabela de cupons (se não existir)
CREATE TABLE IF NOT EXISTS cupons (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    evento_id INT UNSIGNED NULL COMMENT 'NULL = todos os eventos',
    nome VARCHAR(100) NOT NULL COMMENT 'Nome interno do cupom',
    codigo VARCHAR(50) NOT NULL COMMENT 'Código que o usuário digita',
    desconto DECIMAL(10,2) NOT NULL COMMENT 'Valor do desconto (% ou R$)',
    tipo ENUM('percentual', 'fixo') DEFAULT 'percentual',
    valor_minimo DECIMAL(10,2) DEFAULT 0 COMMENT 'Valor mínimo do pedido',
    quantidade_total INT NULL COMMENT 'NULL = ilimitado',
    quantidade_usada INT DEFAULT 0,
    uso_por_usuario INT DEFAULT 1 COMMENT 'Limite de uso por usuário',
    data_inicio DATE NULL COMMENT 'Início da validade',
    data_fim DATE NULL COMMENT 'Fim da validade',
    ativo TINYINT(1) DEFAULT 1,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,
    UNIQUE KEY unique_codigo (codigo),
    INDEX idx_evento_id (evento_id),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Adicionar colunas na tabela pedidos (após valor_liquido)
ALTER TABLE pedidos ADD COLUMN cupom_id INT UNSIGNED NULL AFTER valor_liquido;
ALTER TABLE pedidos ADD COLUMN valor_desconto DECIMAL(10,2) DEFAULT 0 AFTER cupom_id;

-- 3. Adicionar índice para cupom_id
ALTER TABLE pedidos ADD INDEX idx_cupom_id (cupom_id);
