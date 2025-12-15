<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCredenciamentoPessoasTable extends Migration
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
            'tipo' => [
                'type' => 'ENUM',
                'constraint' => ['responsavel', 'funcionario', 'suplente'],
            ],
            'nome' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
            ],
            'rg' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'cpf' => [
                'type' => 'VARCHAR',
                'constraint' => 14,
            ],
            'whatsapp' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
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
        $this->forge->createTable('credenciamento_pessoas');
    }

    public function down()
    {
        $this->forge->dropTable('credenciamento_pessoas');
    }
}
