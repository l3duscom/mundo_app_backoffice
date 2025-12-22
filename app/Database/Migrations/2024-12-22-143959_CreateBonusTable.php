<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBonusTable extends Migration
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
            'ingresso_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'tipo_bonus' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'comment' => 'Tipo do bônus (ex: cinemark, estacionamento, etc)',
            ],
            'instrucoes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Instruções de uso do bônus',
            ],
            'codigo' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Código do bônus (ex: código Cinemark)',
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
        $this->forge->addKey('ingresso_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('tipo_bonus');
        
        $this->forge->createTable('bonus');
    }

    public function down()
    {
        $this->forge->dropTable('bonus');
    }
}
