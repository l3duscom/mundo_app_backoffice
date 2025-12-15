<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCredenciamentosTable extends Migration
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
            'contrato_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pendente', 'em_andamento', 'completo', 'aprovado', 'bloqueado'],
                'default' => 'pendente',
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
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('contrato_id', 'contratos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('credenciamentos');
    }

    public function down()
    {
        $this->forge->dropTable('credenciamentos');
    }
}
