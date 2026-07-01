<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTicketAlimentacaoToCredenciais extends Migration
{
    public function up()
    {
        $this->forge->addColumn('credenciais', [
            'ticket_alimentacao'    => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after'   => 'ativo',
            ],
            'ticket_alimentacao_em' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'ticket_alimentacao',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('credenciais', ['ticket_alimentacao', 'ticket_alimentacao_em']);
    }
}
