<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateIngressoUpgradesTable extends Migration {
    public function up() {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'ingresso_id'       => ['type' => 'INT', 'unsigned' => true],
            'ticket_destino_id' => ['type' => 'INT', 'unsigned' => true],
            'valor_original'    => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'valor_destino'     => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'desconto_pct'      => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'valor_cobrado'     => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'forma_pagamento'   => ['type' => 'VARCHAR', 'constraint' => 30],
            'charge_id'         => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'status'            => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pendente'],
            'link_pagamento'    => ['type' => 'TEXT', 'null' => true],
            'qrcode'            => ['type' => 'TEXT', 'null' => true],
            'qrcode_image'      => ['type' => 'TEXT', 'null' => true],
            'expire_at'         => ['type' => 'DATETIME', 'null' => true],
            'efetivado_em'      => ['type' => 'DATETIME', 'null' => true],
            'efetivado_por'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('ingresso_upgrades');
    }
    public function down() {
        $this->forge->dropTable('ingresso_upgrades');
    }
}
