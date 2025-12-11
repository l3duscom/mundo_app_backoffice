-- ========================================
-- Script para criar a tabela de Expositores
-- MySQL 5.7+ / MariaDB 10.2+
-- ========================================

-- Criar tabela de expositores
CREATE TABLE IF NOT EXISTS `expositores` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `tipo_pessoa` ENUM('pf', 'pj') NOT NULL DEFAULT 'pf' COMMENT 'pf = Pessoa Física, pj = Pessoa Jurídica',
    `nome` VARCHAR(255) NOT NULL COMMENT 'Nome completo ou Razão Social',
    `nome_fantasia` VARCHAR(255) NULL COMMENT 'Nome fantasia (apenas para PJ)',
    `documento` VARCHAR(20) NOT NULL COMMENT 'CPF ou CNPJ',
    `ie` VARCHAR(30) NULL COMMENT 'Inscrição Estadual (apenas para PJ)',
    `email` VARCHAR(255) NOT NULL,
    `telefone` VARCHAR(20) NOT NULL,
    `celular` VARCHAR(20) NULL,
    `cep` VARCHAR(10) NULL,
    `endereco` VARCHAR(255) NULL,
    `numero` VARCHAR(20) NULL,
    `complemento` VARCHAR(100) NULL,
    `bairro` VARCHAR(100) NULL,
    `cidade` VARCHAR(100) NULL,
    `estado` VARCHAR(2) NULL,
    `responsavel` VARCHAR(255) NULL COMMENT 'Nome do responsável pela empresa (apenas PJ)',
    `responsavel_telefone` VARCHAR(20) NULL COMMENT 'Telefone do responsável (apenas PJ)',
    `tipo_expositor` VARCHAR(50) NULL COMMENT 'Tipo de expositor: Stand Comercial, Artist Alley, Vila dos Artesãos, etc.',
    `segmento` VARCHAR(100) NULL COMMENT 'Segmento de atuação do expositor',
    `observacoes` TEXT NULL,
    `ativo` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    `deleted_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    INDEX `expositores_documento` (`documento`),
    INDEX `expositores_email` (`email`),
    INDEX `expositores_tipo_pessoa` (`tipo_pessoa`),
    INDEX `expositores_deleted_at_id` (`deleted_at`, `id`),
    INDEX `expositores_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Exemplos de INSERT (opcional)
-- ========================================

-- Exemplo: Pessoa Física (PF)
-- INSERT INTO `expositores` (
--     `tipo_pessoa`, `nome`, `documento`, `email`, `telefone`, `celular`,
--     `cep`, `endereco`, `numero`, `bairro`, `cidade`, `estado`,
--     `segmento`, `ativo`, `created_at`, `updated_at`
-- ) VALUES (
--     'pf', 'João da Silva', '12345678901', 'joao@email.com', '(51) 3333-4444', '(51) 99999-8888',
--     '90000-000', 'Rua Exemplo', '123', 'Centro', 'Porto Alegre', 'RS',
--     'Artesanato', 1, NOW(), NOW()
-- );

-- Exemplo: Pessoa Jurídica (PJ)
-- INSERT INTO `expositores` (
--     `tipo_pessoa`, `nome`, `nome_fantasia`, `documento`, `ie`, `email`, `telefone`, `celular`,
--     `cep`, `endereco`, `numero`, `complemento`, `bairro`, `cidade`, `estado`,
--     `responsavel`, `responsavel_telefone`, `segmento`, `ativo`, `created_at`, `updated_at`
-- ) VALUES (
--     'pj', 'Empresa Exemplo LTDA', 'Exemplo Store', '12345678000199', '123456789', 'contato@empresa.com', '(51) 3333-4444', '(51) 99999-8888',
--     '90000-000', 'Av. Comercial', '500', 'Sala 101', 'Centro', 'Porto Alegre', 'RS',
--     'Maria Souza', '(51) 98888-7777', 'Alimentação', 1, NOW(), NOW()
-- );

-- ========================================
-- Para remover a tabela (CUIDADO!)
-- ========================================
-- DROP TABLE IF EXISTS `expositores`;

