<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCronogramaItensTable extends Migration
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
            'cronograma_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'nome_item' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'data_hora_inicio' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'data_hora_fim' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'ativo' => [
                'type'       => 'BOOLEAN',
                'default'    => true,
                'null'       => false,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'AGUARDANDO',
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
        $this->forge->addKey('cronograma_id');
        $this->forge->addKey('status');
        $this->forge->addKey(['deleted_at', 'id']);
        $this->forge->addKey('data_hora_inicio');
        $this->forge->addKey('created_at');

        // Add foreign key constraint
        $this->forge->addForeignKey('cronograma_id', 'cronograma', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('cronograma_itens');
    }

    public function down()
    {
        $this->forge->dropTable('cronograma_itens');
    }
}

/*
-- Script MySQL equivalente:

CREATE TABLE `cronograma_itens` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `cronograma_id` INT(11) UNSIGNED NOT NULL,
    `nome_item` VARCHAR(255) NOT NULL,
    `data_hora_inicio` DATETIME NULL,
    `data_hora_fim` DATETIME NULL,
    `ativo` BOOLEAN NOT NULL DEFAULT 1,
    `status` VARCHAR(50) NOT NULL DEFAULT 'AGUARDANDO',
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    `deleted_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    INDEX `cronograma_itens_cronograma_id` (`cronograma_id`),
    INDEX `cronograma_itens_status` (`status`),
    INDEX `cronograma_itens_deleted_at_id` (`deleted_at`, `id`),
    INDEX `cronograma_itens_data_hora_inicio` (`data_hora_inicio`),
    INDEX `cronograma_itens_created_at` (`created_at`),
    CONSTRAINT `cronograma_itens_cronograma_id_foreign` 
        FOREIGN KEY (`cronograma_id`) 
        REFERENCES `cronograma` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Para remover a tabela:
-- DROP TABLE IF EXISTS `cronograma_itens`;
*/

