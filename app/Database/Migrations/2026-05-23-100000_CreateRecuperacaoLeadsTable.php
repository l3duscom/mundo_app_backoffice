<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRecuperacaoLeadsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'evento_origem_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'evento_destino_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['contato_feito', 'rejeitado', 'revertido'],
                'null'       => true,
            ],
            'observacao' => [
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
        $this->forge->addUniqueKey(['user_id', 'evento_origem_id', 'evento_destino_id'], 'uk_recuperacao_leads_user_eventos');
        $this->forge->addKey('evento_destino_id');
        $this->forge->addKey('status');

        $this->forge->createTable('recuperacao_leads');
    }

    public function down()
    {
        $this->forge->dropTable('recuperacao_leads');
    }
}
