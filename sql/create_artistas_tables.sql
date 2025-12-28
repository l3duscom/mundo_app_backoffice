-- =====================================================
-- Módulo de Gerenciamento de Artistas
-- Tabelas para artistas, contatos, contratações e custos
-- =====================================================

-- Tabela principal de artistas
CREATE TABLE IF NOT EXISTS `artistas` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Dados Artísticos
    `nome_artistico` VARCHAR(255) NOT NULL COMMENT 'Nome artístico ou banda',
    `genero_musical` VARCHAR(100) NULL COMMENT 'Gênero/estilo musical',
    `biografia` TEXT NULL,
    `rider_tecnico` TEXT NULL COMMENT 'Requisitos técnicos do show',
    `foto` VARCHAR(500) NULL COMMENT 'Caminho da foto',
    
    -- Dados Pessoais
    `nome_completo` VARCHAR(255) NULL COMMENT 'Nome civil completo',
    `cpf` VARCHAR(14) NULL,
    `rg` VARCHAR(20) NULL,
    `data_nascimento` DATE NULL,
    `nacionalidade` VARCHAR(50) NULL DEFAULT 'Brasileira',
    `passaporte` VARCHAR(30) NULL COMMENT 'Número do passaporte (estrangeiros)',
    `passaporte_validade` DATE NULL COMMENT 'Validade do passaporte',
    
    -- Contato
    `email` VARCHAR(255) NULL,
    `telefone` VARCHAR(20) NULL,
    
    -- Controle
    `observacoes` TEXT NULL,
    `ativo` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME NULL,
    
    INDEX `idx_nome_artistico` (`nome_artistico`),
    INDEX `idx_ativo` (`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contatos do artista (agentes, empresários, etc.)
CREATE TABLE IF NOT EXISTS `artista_contatos` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `artista_id` INT UNSIGNED NOT NULL,
    `tipo` ENUM('agente', 'empresario', 'assessoria', 'tecnico', 'outro') NOT NULL DEFAULT 'agente',
    `nome` VARCHAR(255) NOT NULL,
    `telefone` VARCHAR(20) NULL,
    `email` VARCHAR(255) NULL,
    `observacoes` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_artista` (`artista_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contratações de artistas por evento
CREATE TABLE IF NOT EXISTS `artista_contratacoes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `artista_id` INT UNSIGNED NOT NULL,
    `event_id` INT UNSIGNED NOT NULL,
    `codigo` VARCHAR(50) NOT NULL COMMENT 'Código único ART-ANO-XXXX',
    
    -- Situação
    `situacao` ENUM('rascunho', 'confirmado', 'realizado', 'cancelado') NOT NULL DEFAULT 'rascunho',
    
    -- Dados da apresentação
    `data_apresentacao` DATE NULL,
    `horario_inicio` TIME NULL,
    `horario_fim` TIME NULL,
    `palco` VARCHAR(100) NULL COMMENT 'Nome do palco/local',
    
    -- Cachê
    `valor_cache` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `forma_pagamento` VARCHAR(50) NULL,
    `quantidade_parcelas` INT UNSIGNED NOT NULL DEFAULT 1,
    
    -- Controle
    `observacoes` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY `uk_codigo` (`codigo`),
    INDEX `idx_artista` (`artista_id`),
    INDEX `idx_evento` (`event_id`),
    INDEX `idx_situacao` (`situacao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Voos dos artistas
CREATE TABLE IF NOT EXISTS `artista_voos` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `contratacao_id` INT UNSIGNED NOT NULL,
    
    `tipo` ENUM('ida', 'volta') NOT NULL DEFAULT 'ida',
    `companhia` VARCHAR(100) NULL COMMENT 'Companhia aérea',
    `numero_voo` VARCHAR(20) NULL,
    `localizador` VARCHAR(20) NULL COMMENT 'Código de reserva',
    
    `origem` VARCHAR(100) NULL COMMENT 'Aeroporto de origem',
    `destino` VARCHAR(100) NULL COMMENT 'Aeroporto de destino',
    
    `data_embarque` DATE NULL,
    `horario_embarque` TIME NULL,
    `horario_chegada` TIME NULL,
    
    `classe` ENUM('economica', 'executiva', 'primeira') NOT NULL DEFAULT 'economica',
    `assento` VARCHAR(10) NULL,
    `bagagem_despachada` INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Quantidade de bagagens',
    `peso_bagagem` DECIMAL(5,2) NULL COMMENT 'Peso permitido em kg',
    
    `passageiros` TEXT NULL COMMENT 'Nomes dos passageiros',
    
    `valor` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('pendente', 'pago', 'cancelado') NOT NULL DEFAULT 'pendente',
    `observacoes` TEXT NULL,
    
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_contratacao` (`contratacao_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Hospedagens
CREATE TABLE IF NOT EXISTS `artista_hospedagens` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `contratacao_id` INT UNSIGNED NOT NULL,
    
    `hotel` VARCHAR(255) NOT NULL,
    `endereco` VARCHAR(500) NULL,
    `telefone` VARCHAR(20) NULL,
    `codigo_reserva` VARCHAR(50) NULL,
    
    `data_checkin` DATETIME NULL,
    `data_checkout` DATETIME NULL,
    
    `tipo_quarto` VARCHAR(100) NULL COMMENT 'Ex: Suite, Standard, Luxo',
    `quantidade_quartos` INT UNSIGNED NOT NULL DEFAULT 1,
    
    `valor_diaria` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `valor_total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    
    `status` ENUM('pendente', 'pago', 'cancelado') NOT NULL DEFAULT 'pendente',
    `observacoes` TEXT NULL,
    
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_contratacao` (`contratacao_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Translados
CREATE TABLE IF NOT EXISTS `artista_translados` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `contratacao_id` INT UNSIGNED NOT NULL,
    
    `tipo` ENUM('aeroporto_hotel', 'hotel_evento', 'evento_hotel', 'hotel_aeroporto', 'outro') NOT NULL DEFAULT 'aeroporto_hotel',
    
    `data_translado` DATETIME NULL,
    `origem` VARCHAR(255) NULL,
    `destino` VARCHAR(255) NULL,
    
    `veiculo` VARCHAR(100) NULL COMMENT 'Ex: Van, Sedan, SUV',
    `motorista` VARCHAR(255) NULL,
    `telefone_motorista` VARCHAR(20) NULL,
    
    `valor` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('pendente', 'pago', 'cancelado') NOT NULL DEFAULT 'pendente',
    `observacoes` TEXT NULL,
    
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_contratacao` (`contratacao_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Alimentação
CREATE TABLE IF NOT EXISTS `artista_alimentacoes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `contratacao_id` INT UNSIGNED NOT NULL,
    
    `tipo` ENUM('cafe', 'almoco', 'jantar', 'lanche', 'camarim', 'outro') NOT NULL DEFAULT 'almoco',
    
    `data` DATETIME NULL,
    `local` VARCHAR(255) NULL,
    
    `quantidade_pessoas` INT UNSIGNED NOT NULL DEFAULT 1,
    `valor_pessoa` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `valor_total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    
    `status` ENUM('pendente', 'pago', 'cancelado') NOT NULL DEFAULT 'pendente',
    `observacoes` TEXT NULL,
    
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_contratacao` (`contratacao_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Custos Extras Avulsos
CREATE TABLE IF NOT EXISTS `artista_custos_extras` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `contratacao_id` INT UNSIGNED NOT NULL,
    
    `descricao` VARCHAR(255) NOT NULL,
    `valor` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `data` DATE NULL,
    
    `status` ENUM('pendente', 'pago', 'cancelado') NOT NULL DEFAULT 'pendente',
    `observacoes` TEXT NULL,
    
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_contratacao` (`contratacao_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Parcelas do cachê (similar a orçamento)
CREATE TABLE IF NOT EXISTS `artista_parcelas` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `contratacao_id` INT UNSIGNED NOT NULL,
    
    `numero_parcela` INT NOT NULL DEFAULT 1,
    `valor` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `data_vencimento` DATE NOT NULL,
    `data_pagamento` DATE NULL,
    
    `status` ENUM('pendente', 'pago', 'vencido', 'cancelado') NOT NULL DEFAULT 'pendente',
    `forma_pagamento` VARCHAR(50) NULL,
    `observacoes` TEXT NULL,
    
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_contratacao` (`contratacao_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_vencimento` (`data_vencimento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Anexos da contratação (contratos, comprovantes, etc.)
CREATE TABLE IF NOT EXISTS `artista_contratacao_anexos` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `contratacao_id` INT UNSIGNED NOT NULL,
    
    `nome_arquivo` VARCHAR(255) NOT NULL COMMENT 'Nome original do arquivo',
    `arquivo` VARCHAR(255) NOT NULL COMMENT 'Nome do arquivo no disco',
    `tipo` VARCHAR(100) NOT NULL COMMENT 'MIME type',
    `tamanho` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Tamanho em bytes',
    
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX `idx_contratacao` (`contratacao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
