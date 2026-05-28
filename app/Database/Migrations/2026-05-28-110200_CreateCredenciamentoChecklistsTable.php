<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCredenciamentoChecklistsTable extends Migration
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
            'modelo_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Modelo de origem usado ao iniciar este checklist',
            ],
            'tipo' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'comment' => 'entrada | saida',
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'em_andamento',
                'comment' => 'em_andamento | concluido | reaberto',
            ],
            'conferido_por' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'conferido_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'observacoes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('credenciamento_id');
        $this->forge->addKey('tipo');
        $this->forge->createTable('credenciamento_checklists');
    }

    public function down()
    {
        $this->forge->dropTable('credenciamento_checklists');
    }
}
