<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMetasVendasMarcosTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'meta_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'data' => ['type' => 'DATE'],
            'faturamento_acumulado_esperado' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'ordem' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('meta_id');
        $this->forge->createTable('metas_vendas_marcos');
    }

    public function down()
    {
        $this->forge->dropTable('metas_vendas_marcos');
    }
}
