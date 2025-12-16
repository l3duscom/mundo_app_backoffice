<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEspacosTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'event_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'tipo_item' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'comment' => 'Categoria do espaço (mesmos valores de contrato_itens.tipo_item)',
            ],
            'nome' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'comment' => 'Nome/código do espaço (ex: A1, B2, Stand 05)',
            ],
            'descricao' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['livre', 'reservado', 'bloqueado'],
                'default' => 'livre',
            ],
            'contrato_item_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Item de contrato que reservou este espaço',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('event_id');
        $this->forge->addKey('tipo_item');
        $this->forge->addKey('status');
        $this->forge->addKey('contrato_item_id');
        
        // Índice único para evitar duplicatas de nome por evento e tipo
        $this->forge->addUniqueKey(['event_id', 'tipo_item', 'nome'], 'unique_espaco');
        
        $this->forge->createTable('espacos', true);
    }

    public function down()
    {
        $this->forge->dropTable('espacos', true);
    }
}
