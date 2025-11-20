<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCronogramaTable extends Migration
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
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'ativo' => [
                'type'       => 'BOOLEAN',
                'default'    => true,
                'null'       => false,
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
        $this->forge->addKey(['deleted_at', 'id']);
        $this->forge->addKey('created_at');

        // Add foreign key constraint (assuming you have an 'eventos' table)
        $this->forge->addForeignKey('event_id', 'eventos', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('cronograma');
    }

    public function down()
    {
        $this->forge->dropTable('cronograma');
    }
}

/*
-- Script MySQL equivalente:

CREATE TABLE `cronograma` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_id` INT(11) UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `ativo` BOOLEAN NOT NULL DEFAULT 1,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    `deleted_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    INDEX `cronograma_event_id` (`event_id`),
    INDEX `cronograma_deleted_at_id` (`deleted_at`, `id`),
    INDEX `cronograma_created_at` (`created_at`),
    CONSTRAINT `cronograma_event_id_foreign` 
        FOREIGN KEY (`event_id`) 
        REFERENCES `eventos` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Para remover a tabela:
-- DROP TABLE IF EXISTS `cronograma`;
*/
