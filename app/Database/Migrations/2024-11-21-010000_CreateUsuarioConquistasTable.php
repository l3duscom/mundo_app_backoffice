<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsuarioConquistasTable extends Migration
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
            'conquista_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'event_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'pontos' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'comment'    => 'Pontos ganhos nesta conquista',
            ],
            'admin' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'comment'    => '0=automático, 1=atribuído por admin',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'ATIVA',
                'null'       => false,
                'comment'    => 'ATIVA, REVOGADA',
            ],
            'atribuido_por' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID do usuário admin que atribuiu (se manual)',
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
        $this->forge->addKey('conquista_id');
        $this->forge->addKey('event_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('status');
        $this->forge->addKey(['deleted_at', 'id']);
        $this->forge->addKey('created_at');
        
        // Índice único composto para garantir uma conquista por usuário
        $this->forge->addUniqueKey(['user_id', 'conquista_id', 'event_id'], 'unique_user_conquista');

        // Add foreign key constraints
        $this->forge->addForeignKey('conquista_id', 'conquistas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('event_id', 'eventos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('atribuido_por', 'usuarios', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('usuario_conquistas');
    }

    public function down()
    {
        $this->forge->dropTable('usuario_conquistas');
    }
}

/*
-- Script MySQL equivalente:

CREATE TABLE `usuario_conquistas` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `conquista_id` INT(11) UNSIGNED NOT NULL,
    `event_id` INT(11) UNSIGNED NOT NULL,
    `user_id` INT(11) UNSIGNED NOT NULL,
    `pontos` INT(11) NOT NULL DEFAULT 0 COMMENT 'Pontos ganhos nesta conquista',
    `admin` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=automático, 1=atribuído por admin',
    `status` VARCHAR(50) NOT NULL DEFAULT 'ATIVA' COMMENT 'ATIVA, REVOGADA',
    `atribuido_por` INT(11) UNSIGNED NULL COMMENT 'ID do usuário admin que atribuiu (se manual)',
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    `deleted_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    INDEX `usuario_conquistas_conquista_id` (`conquista_id`),
    INDEX `usuario_conquistas_event_id` (`event_id`),
    INDEX `usuario_conquistas_user_id` (`user_id`),
    INDEX `usuario_conquistas_status` (`status`),
    INDEX `usuario_conquistas_deleted_at_id` (`deleted_at`, `id`),
    INDEX `usuario_conquistas_created_at` (`created_at`),
    UNIQUE KEY `unique_user_conquista` (`user_id`, `conquista_id`, `event_id`),
    CONSTRAINT `usuario_conquistas_conquista_id_foreign` 
        FOREIGN KEY (`conquista_id`) 
        REFERENCES `conquistas` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    CONSTRAINT `usuario_conquistas_event_id_foreign` 
        FOREIGN KEY (`event_id`) 
        REFERENCES `eventos` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    CONSTRAINT `usuario_conquistas_user_id_foreign` 
        FOREIGN KEY (`user_id`) 
        REFERENCES `usuarios` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    CONSTRAINT `usuario_conquistas_atribuido_por_foreign` 
        FOREIGN KEY (`atribuido_por`) 
        REFERENCES `usuarios` (`id`) 
        ON DELETE SET NULL 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Para remover a tabela:
-- DROP TABLE IF EXISTS `usuario_conquistas`;
*/

