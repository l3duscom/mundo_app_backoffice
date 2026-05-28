<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateIngressoTrocasTable extends Migration
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
            'ingresso_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'ticket_id_anterior' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'ticket_id_novo' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'nome_anterior' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'nome_novo' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'operador_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Usuário do backoffice que executou a troca',
            ],
            'motivo' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('ingresso_id');
        $this->forge->addKey('ticket_id_novo');
        $this->forge->addKey('operador_id');

        $this->forge->createTable('ingresso_trocas');
    }

    public function down()
    {
        $this->forge->dropTable('ingresso_trocas');
    }
}
