<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProdutosTable extends Migration
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
                'constraint' => 5,
                'unsigned'   => true,
            ],
            'imagem' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'comment'    => 'URL ou caminho da imagem do produto',
            ],
            'categoria' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
                'comment'    => 'Categoria do produto',
            ],
            'nome' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'preco' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
                'null'       => false,
            ],
            'pontos' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'comment'    => 'Pontos necessários para resgatar o produto',
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
        $this->forge->addKey('categoria');
        $this->forge->addKey(['deleted_at', 'id']);
        $this->forge->addKey('created_at');

        $this->forge->createTable('produtos');
    }

    public function down()
    {
        $this->forge->dropTable('produtos');
    }
}

/*
-- Script MySQL equivalente:

CREATE TABLE `produtos` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_id` INT(5) UNSIGNED NOT NULL,
    `imagem` VARCHAR(500) NULL COMMENT 'URL ou caminho da imagem do produto',
    `categoria` VARCHAR(100) NOT NULL COMMENT 'Categoria do produto',
    `nome` VARCHAR(255) NOT NULL,
    `preco` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `pontos` INT NOT NULL DEFAULT 0 COMMENT 'Pontos necessários para resgatar o produto',
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    `deleted_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    INDEX `produtos_event_id` (`event_id`),
    INDEX `produtos_categoria` (`categoria`),
    INDEX `produtos_deleted_at_id` (`deleted_at`, `id`),
    INDEX `produtos_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Exemplos de INSERT:
INSERT INTO `produtos` (`event_id`, `imagem`, `categoria`, `nome`, `preco`, `pontos`, `created_at`, `updated_at`) VALUES
(1, '/uploads/produtos/camiseta.png', 'Vestuário', 'Camiseta Oficial do Evento', 79.90, 100, NOW(), NOW()),
(1, '/uploads/produtos/caneca.png', 'Acessórios', 'Caneca Personalizada', 29.90, 50, NOW(), NOW()),
(1, '/uploads/produtos/poster.png', 'Decoração', 'Poster Autografado', 49.90, 75, NOW(), NOW());

-- Para remover a tabela:
-- DROP TABLE IF EXISTS `produtos`;
*/

