<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMetasVendasTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'event_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'tipo' => ['type' => 'VARCHAR', 'constraint' => 20, 'comment' => 'comercial | ingressos'],
            'nome' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'meta_total' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'ativo' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('event_id');
        $this->forge->addKey('tipo');
        $this->forge->createTable('metas_vendas');
    }

    public function down()
    {
        $this->forge->dropTable('metas_vendas');
    }
}
