<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContratoItensTable extends Migration
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
            'contrato_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'ID do contrato',
            ],
            'tipo_item' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'comment'    => 'Tipo: Espaço Comercial, Cota, Patrocínio, Artist Alley, Food Park, etc.',
            ],
            'descricao' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Descrição do item/espaço contratado',
            ],
            'localizacao' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Localização do espaço (ex: Stand A-15, Mesa 23)',
            ],
            'metragem' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'Tamanho do espaço (ex: 3x3m, 2x2m)',
            ],
            'quantidade' => [
                'type'       => 'INT',
                'constraint' => 5,
                'default'    => 1,
                'null'       => false,
            ],
            'valor_unitario' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
                'null'       => false,
            ],
            'valor_desconto' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
                'null'       => false,
            ],
            'valor_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
                'null'       => false,
            ],
            'observacoes' => [
                'type' => 'TEXT',
                'null' => true,
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
        
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('contrato_id');
        $this->forge->addKey('tipo_item');
        $this->forge->addKey(['deleted_at', 'id']);

        $this->forge->createTable('contrato_itens');
    }

    public function down()
    {
        $this->forge->dropTable('contrato_itens');
    }
}

