-- =====================================================
-- Módulo de Agentes/Agências
-- Tabelas para gerenciamento centralizado de agentes
-- =====================================================

-- Tabela principal de agentes/agências
CREATE TABLE IF NOT EXISTS `agentes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Tipo
    `tipo` ENUM('agente', 'empresario', 'agencia', 'assessoria', 'produtor', 'tecnico', 'outro') NOT NULL DEFAULT 'agente',
    
    -- Dados principais
    `nome` VARCHAR(255) NOT NULL COMMENT 'Nome ou razão social',
    `nome_fantasia` VARCHAR(255) NULL COMMENT 'Nome fantasia (para empresas)',
    
    -- Documentos
    `cpf` VARCHAR(14) NULL,
    `cnpj` VARCHAR(18) NULL,
    
    -- Contato
    `email` VARCHAR(255) NULL,
    `telefone` VARCHAR(20) NULL,
    `whatsapp` VARCHAR(20) NULL,
    `site` VARCHAR(500) NULL COMMENT 'URL do site',
    
    -- Endereço
    `cep` VARCHAR(10) NULL,
    `endereco` VARCHAR(255) NULL,
    `numero` VARCHAR(20) NULL,
    `complemento` VARCHAR(100) NULL,
    `bairro` VARCHAR(100) NULL,
    `cidade` VARCHAR(100) NULL,
    `estado` VARCHAR(2) NULL,
    
    -- Dados bancários
    `banco` VARCHAR(100) NULL,
    `agencia` VARCHAR(20) NULL,
    `conta` VARCHAR(30) NULL,
    `tipo_conta` ENUM('corrente', 'poupanca') NULL,
    `pix` VARCHAR(255) NULL COMMENT 'Chave PIX',
    
    -- Controle
    `observacoes` TEXT NULL,
    `ativo` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME NULL,
    
    INDEX `idx_tipo` (`tipo`),
    INDEX `idx_nome` (`nome`),
    INDEX `idx_ativo` (`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Anexos do agente (media kit, contratos, etc.)
CREATE TABLE IF NOT EXISTS `agente_anexos` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `agente_id` INT UNSIGNED NOT NULL,
    
    `nome_arquivo` VARCHAR(255) NOT NULL COMMENT 'Nome original do arquivo',
    `arquivo` VARCHAR(255) NOT NULL COMMENT 'Nome do arquivo no disco',
    `tipo` VARCHAR(100) NOT NULL COMMENT 'MIME type',
    `tamanho` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Tamanho em bytes',
    `descricao` VARCHAR(255) NULL COMMENT 'Ex: Media Kit, Contrato, etc.',
    
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX `idx_agente` (`agente_id`),
    FOREIGN KEY (`agente_id`) REFERENCES `agentes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Relação N:N entre artistas e agentes
CREATE TABLE IF NOT EXISTS `artista_agentes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `artista_id` INT UNSIGNED NOT NULL,
    `agente_id` INT UNSIGNED NOT NULL,
    
    `funcao` ENUM('agente', 'empresario', 'assessoria', 'produtor', 'tecnico', 'outro') NOT NULL DEFAULT 'agente' COMMENT 'Função do agente para este artista',
    `principal` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Se é o contato principal',
    `observacoes` TEXT NULL,
    
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY `uk_artista_agente` (`artista_id`, `agente_id`),
    INDEX `idx_artista` (`artista_id`),
    INDEX `idx_agente` (`agente_id`),
    FOREIGN KEY (`artista_id`) REFERENCES `artistas`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`agente_id`) REFERENCES `agentes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
