<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMelhorEnvioCredentialsTable extends Migration
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
            'access_token'  => ['type' => 'TEXT', 'null' => false],
            'refresh_token' => ['type' => 'TEXT', 'null' => false],
            'scope'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'expires_at'    => ['type' => 'DATETIME', 'null' => false],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('melhor_envio_credentials');
    }

    public function down()
    {
        $this->forge->dropTable('melhor_envio_credentials');
    }
}
