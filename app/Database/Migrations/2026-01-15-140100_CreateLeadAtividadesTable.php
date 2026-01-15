<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLeadAtividadesTable extends Migration
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
            'lead_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'usuario_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'tipo' => [
                'type'       => 'ENUM',
                'constraint' => ['nota', 'ligacao', 'email', 'reuniao', 'whatsapp', 'mudanca_etapa', 'criacao', 'conversao'],
                'default'    => 'nota',
            ],
            'descricao' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'etapa_anterior' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'etapa_nova' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('lead_id');
        $this->forge->addKey('usuario_id');
        $this->forge->addKey('tipo');
        
        $this->forge->createTable('lead_atividades');
    }

    public function down()
    {
        $this->forge->dropTable('lead_atividades');
    }
}
