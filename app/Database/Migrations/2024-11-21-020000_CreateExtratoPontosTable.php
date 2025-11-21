<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExtratoPontosTable extends Migration
{
    public function up()
    {
        $fields = [
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'event_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Evento relacionado (se aplicável)',
            ],
            'tipo' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'comment'    => 'CONQUISTA, BONUS, AJUSTE, REVOGACAO, etc',
            ],
            'pontos' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
                'comment'    => 'Pode ser positivo ou negativo',
            ],
            'saldo_anterior' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'comment'    => 'Saldo antes da transação',
            ],
            'saldo_atual' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'comment'    => 'Saldo após a transação',
            ],
            'descricao' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'Descrição da transação',
            ],
            'referencia_tipo' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'Tipo da entidade relacionada: conquista, pedido, etc',
            ],
            'referencia_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID da entidade relacionada',
            ],
            'atribuido_por' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID do usuário que gerou a transação (se manual)',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ];

        $this->forge->addField($fields);
        
        // Add primary key
        $this->forge->addPrimaryKey('id');

        // Add indexes
        $this->forge->addKey('user_id');
        $this->forge->addKey('event_id');
        $this->forge->addKey('tipo');
        $this->forge->addKey(['referencia_tipo', 'referencia_id']);
        $this->forge->addKey(['deleted_at', 'id']);
        $this->forge->addKey('created_at');

        // Add foreign key constraints
        $this->forge->addForeignKey('user_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('event_id', 'eventos', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('atribuido_por', 'usuarios', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('extrato_pontos');
    }

    public function down()
    {
        $this->forge->dropTable('extrato_pontos');
    }
}

/*
-- Script MySQL equivalente:

CREATE TABLE `extrato_pontos` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) UNSIGNED NOT NULL,
    `event_id` INT(11) UNSIGNED NULL COMMENT 'Evento relacionado (se aplicável)',
    `tipo` VARCHAR(50) NOT NULL COMMENT 'CONQUISTA, BONUS, AJUSTE, REVOGACAO, etc',
    `pontos` INT(11) NOT NULL COMMENT 'Pode ser positivo ou negativo',
    `saldo_anterior` INT(11) NOT NULL DEFAULT 0 COMMENT 'Saldo antes da transação',
    `saldo_atual` INT(11) NOT NULL DEFAULT 0 COMMENT 'Saldo após a transação',
    `descricao` TEXT NULL COMMENT 'Descrição da transação',
    `referencia_tipo` VARCHAR(50) NULL COMMENT 'Tipo da entidade relacionada: conquista, pedido, etc',
    `referencia_id` INT(11) UNSIGNED NULL COMMENT 'ID da entidade relacionada',
    `atribuido_por` INT(11) UNSIGNED NULL COMMENT 'ID do usuário que gerou a transação (se manual)',
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    `deleted_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    INDEX `extrato_pontos_user_id` (`user_id`),
    INDEX `extrato_pontos_event_id` (`event_id`),
    INDEX `extrato_pontos_tipo` (`tipo`),
    INDEX `extrato_pontos_referencia` (`referencia_tipo`, `referencia_id`),
    INDEX `extrato_pontos_deleted_at_id` (`deleted_at`, `id`),
    INDEX `extrato_pontos_created_at` (`created_at`),
    CONSTRAINT `extrato_pontos_user_id_foreign` 
        FOREIGN KEY (`user_id`) 
        REFERENCES `usuarios` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    CONSTRAINT `extrato_pontos_event_id_foreign` 
        FOREIGN KEY (`event_id`) 
        REFERENCES `eventos` (`id`) 
        ON DELETE SET NULL 
        ON UPDATE CASCADE,
    CONSTRAINT `extrato_pontos_atribuido_por_foreign` 
        FOREIGN KEY (`atribuido_por`) 
        REFERENCES `usuarios` (`id`) 
        ON DELETE SET NULL 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Exemplos de INSERT:
INSERT INTO `extrato_pontos` (`user_id`, `event_id`, `tipo`, `pontos`, `saldo_anterior`, `saldo_atual`, `descricao`, `referencia_tipo`, `referencia_id`, `created_at`) VALUES
(1, 1, 'CONQUISTA', 10, 0, 10, 'Conquista: Primeira Participação', 'usuario_conquista', 1, NOW()),
(1, 1, 'CONQUISTA', 25, 10, 35, 'Conquista: Participou de 3 Painéis', 'usuario_conquista', 2, NOW()),
(1, 1, 'BONUS', 50, 35, 85, 'Bônus especial do evento', NULL, NULL, NOW()),
(1, 1, 'AJUSTE', -10, 85, 75, 'Ajuste manual', NULL, NULL, NOW());

-- Para remover a tabela:
-- DROP TABLE IF EXISTS `extrato_pontos`;
*/

