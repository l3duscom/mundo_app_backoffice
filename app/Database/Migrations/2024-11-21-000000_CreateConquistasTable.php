<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConquistasTable extends Migration
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
            'event_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'nome_conquista' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'pontos' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
            ],
            'nivel' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'comment'    => 'Ex: BRONZE, PRATA, OURO, PLATINA, etc',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'ATIVA',
                'null'       => false,
                'comment'    => 'Ex: ATIVA, INATIVA, BLOQUEADA',
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
        $this->forge->addKey('event_id');
        $this->forge->addKey('nivel');
        $this->forge->addKey('status');
        $this->forge->addKey(['deleted_at', 'id']);
        $this->forge->addKey('created_at');

        // Add foreign key constraint
        $this->forge->addForeignKey('event_id', 'eventos', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('conquistas');
    }

    public function down()
    {
        $this->forge->dropTable('conquistas');
    }
}

/*
-- Script MySQL equivalente:

CREATE TABLE `conquistas` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_id` INT(11) UNSIGNED NOT NULL,
    `nome_conquista` VARCHAR(255) NOT NULL,
    `pontos` INT(11) NOT NULL DEFAULT 0,
    `nivel` VARCHAR(50) NOT NULL COMMENT 'Ex: BRONZE, PRATA, OURO, PLATINA, etc',
    `status` VARCHAR(50) NOT NULL DEFAULT 'ATIVA' COMMENT 'Ex: ATIVA, INATIVA, BLOQUEADA',
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    `deleted_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    INDEX `conquistas_event_id` (`event_id`),
    INDEX `conquistas_nivel` (`nivel`),
    INDEX `conquistas_status` (`status`),
    INDEX `conquistas_deleted_at_id` (`deleted_at`, `id`),
    INDEX `conquistas_create_at` (`created_at`),
    CONSTRAINT `conquistas_event_id_foreign` 
        FOREIGN KEY (`event_id`) 
        REFERENCES `eventos` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Exemplos de INSERT:
INSERT INTO `conquistas` (`event_id`, `nome_conquista`, `pontos`, `nivel`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Primeira Participação', 10, 'BRONZE', 'ATIVA', NOW(), NOW()),
(1, 'Participou de 3 Painéis', 25, 'PRATA', 'ATIVA', NOW(), NOW()),
(1, 'Conheceu 5 Convidados', 50, 'OURO', 'ATIVA', NOW(), NOW()),
(1, 'Mestre Cosplayer', 100, 'PLATINA', 'ATIVA', NOW(), NOW()),
(1, 'Comprou no Meet & Greet', 15, 'BRONZE', 'ATIVA', NOW(), NOW()),
(1, 'Completou Todo o Cronograma', 200, 'DIAMANTE', 'ATIVA', NOW(), NOW());

-- Para remover a tabela:
-- DROP TABLE IF EXISTS `conquistas`;
*/

