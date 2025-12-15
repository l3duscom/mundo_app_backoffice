<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusToCredenciamentoPessoas extends Migration
{
    public function up()
    {
        $this->forge->addColumn('credenciamento_pessoas', [
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pendente', 'aprovado', 'rejeitado'],
                'default' => 'pendente',
                'after' => 'whatsapp',
            ],
            'motivo_rejeicao' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'status',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('credenciamento_pessoas', ['status', 'motivo_rejeicao']);
    }
}
