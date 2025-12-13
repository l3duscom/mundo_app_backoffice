<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddValorLiquidoToPedidosTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('pedidos', [
            'valor_liquido' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
                'after'      => 'total'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('pedidos', 'valor_liquido');
    }
}
