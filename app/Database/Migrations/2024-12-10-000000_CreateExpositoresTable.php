<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExpositoresTable extends Migration
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
            'tipo_pessoa' => [
                'type'       => 'ENUM',
                'constraint' => ['pf', 'pj'],
                'default'    => 'pf',
                'null'       => false,
                'comment'    => 'pf = Pessoa Física, pj = Pessoa Jurídica',
            ],
            'nome' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
                'comment'    => 'Nome completo ou Razão Social',
            ],
            'nome_fantasia' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Nome fantasia (apenas para PJ)',
            ],
            'documento' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'comment'    => 'CPF ou CNPJ',
            ],
            'ie' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
                'comment'    => 'Inscrição Estadual (apenas para PJ)',
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'telefone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'celular' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'cep' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'endereco' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'numero' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'complemento' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'bairro' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'cidade' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'estado' => [
                'type'       => 'VARCHAR',
                'constraint' => 2,
                'null'       => true,
            ],
            'responsavel' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Nome do responsável pela empresa (apenas PJ)',
            ],
            'responsavel_telefone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'Telefone do responsável (apenas PJ)',
            ],
            'tipo_expositor' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'Tipo de expositor: Stand Comercial, Artist Alley, Vila dos Artesãos, etc.',
            ],
            'segmento' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Segmento de atuação do expositor',
            ],
            'observacoes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'ativo' => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
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
        $this->forge->addKey('documento');
        $this->forge->addKey('email');
        $this->forge->addKey('tipo_pessoa');
        $this->forge->addKey(['deleted_at', 'id']);
        $this->forge->addKey('created_at');

        $this->forge->createTable('expositores');
    }

    public function down()
    {
        $this->forge->dropTable('expositores');
    }
}

/*
-- Script MySQL equivalente:

CREATE TABLE `expositores` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Para remover a tabela:
-- DROP TABLE IF EXISTS `expositores`;
*/

