<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCredenciamentoVeiculosTable extends Migration
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
            'credenciamento_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'marca' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'modelo' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'cor' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'placa' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
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
        $this->forge->addForeignKey('credenciamento_id', 'credenciamentos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('credenciamento_veiculos');
    }

    public function down()
    {
        $this->forge->dropTable('credenciamento_veiculos');
    }
}
