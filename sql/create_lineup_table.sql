-- ========================================
-- Tabela de Line-up vinculado a Eventos
-- MySQL 5.7+ / MariaDB 10.2+
-- ========================================

CREATE TABLE IF NOT EXISTS `lineup` (
    `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_id`    INT(5) UNSIGNED NOT NULL COMMENT 'ID do evento',
    `nome`        VARCHAR(255) NOT NULL COMMENT 'Nome da atração / artista',
    `dia`         DATE NULL COMMENT 'Dia em que se apresenta',
    `tipo`        VARCHAR(60) NULL COMMENT 'Tipo da atração: Show, DJ, Banda, etc',
    `descricao`   TEXT NULL COMMENT 'Descrição / bio',
    `imagem`      VARCHAR(255) NULL COMMENT 'Arquivo de imagem (writable/uploads/lineup/)',
    `ordem`       INT(5) NOT NULL DEFAULT 0 COMMENT 'Ordem de exibição',
    `ativo`       TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`  DATETIME NULL,
    `updated_at`  DATETIME NULL,
    `deleted_at`  DATETIME NULL,
    PRIMARY KEY (`id`),
    KEY `idx_lineup_event` (`event_id`),
    KEY `idx_lineup_dia`   (`dia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
