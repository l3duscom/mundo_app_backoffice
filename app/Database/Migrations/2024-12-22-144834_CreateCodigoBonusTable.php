<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCodigoBonusTable extends Migration
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
            'bonus_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'ID do bônus que está usando este código (preenchido quando usado)',
            ],
            'codigo' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => 'Código do bônus',
            ],
            'usado' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '0 = não usado, 1 = usado',
            ],
            'validade' => [
                'type' => 'DATE',
                'null' => true,
                'comment' => 'Data de validade do código',
            ],
            'validade_lote' => [
                'type' => 'DATE',
                'null' => true,
                'comment' => 'Data de validade do lote',
            ],
            // Timestamps
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('bonus_id');
        $this->forge->addKey('codigo');
        $this->forge->addKey('usado');
        $this->forge->addKey('validade');
        
        $this->forge->createTable('codigo_bonus');
    }

    public function down()
    {
        $this->forge->dropTable('codigo_bonus');
    }
}
