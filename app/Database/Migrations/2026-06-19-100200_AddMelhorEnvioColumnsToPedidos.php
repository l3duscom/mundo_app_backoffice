<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMelhorEnvioColumnsToPedidos extends Migration
{
    public function up()
    {
        $this->forge->addColumn('pedidos', [
            'me_order_id'     => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true, 'comment' => 'UUID do envio no ME'],
            'me_protocol'     => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true, 'comment' => 'ex: ORD-202304125603'],
            'me_servico_id'   => ['type' => 'INT', 'null' => true, 'comment' => 'ID do servico (PAC, SEDEX, etc.)'],
            'me_servico_nome' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'me_etiqueta_url' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'me_status'       => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true, 'comment' => 'pending|paid|generated|posted|delivered|cancelled'],
            'me_valor_frete'  => ['type' => 'DECIMAL', 'constraint' => '8,2', 'null' => true],
            'me_postado_em'   => ['type' => 'DATETIME', 'null' => true],
            'me_entregue_em'  => ['type' => 'DATETIME', 'null' => true],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('pedidos', [
            'me_order_id',
            'me_protocol',
            'me_servico_id',
            'me_servico_nome',
            'me_etiqueta_url',
            'me_status',
            'me_valor_frete',
            'me_postado_em',
            'me_entregue_em',
        ]);
    }
}
