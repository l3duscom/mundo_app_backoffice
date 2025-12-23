<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPdvIdToPedidos extends Migration
{
    public function up()
    {
        $this->forge->addColumn('pedidos', [
            'pdv_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'user_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('pedidos', 'pdv_id');
    }
}
