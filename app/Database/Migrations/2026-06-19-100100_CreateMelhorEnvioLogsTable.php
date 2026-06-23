<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMelhorEnvioLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'pedido_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'endpoint'      => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'http_method'   => ['type' => 'VARCHAR', 'constraint' => 8, 'null' => false],
            'request_body'  => ['type' => 'JSON', 'null' => true],
            'response_body' => ['type' => 'JSON', 'null' => true],
            'http_status'   => ['type' => 'SMALLINT', 'null' => true],
            'duracao_ms'    => ['type' => 'INT', 'null' => true],
            'erro'          => ['type' => 'TEXT', 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => false],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('pedido_id');
        $this->forge->addKey('endpoint');
        $this->forge->addKey('created_at');
        $this->forge->createTable('melhor_envio_logs');
    }

    public function down()
    {
        $this->forge->dropTable('melhor_envio_logs');
    }
}
