<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCodigoToConquistas extends Migration
{
    public function up()
    {
        $fields = [
            'codigo' => [
                'type'       => 'VARCHAR',
                'constraint' => '8',
                'null'       => false,
                'after'      => 'id',
            ],
        ];
        
        $this->forge->addColumn('conquistas', $fields);
        
        // Adiciona índice único para garantir que não haja códigos duplicados
        $this->forge->addUniqueKey('codigo', 'unique_conquista_codigo');
    }

    public function down()
    {
        // Remove o índice único
        $this->db->query('ALTER TABLE conquistas DROP INDEX unique_conquista_codigo');
        
        // Remove a coluna
        $this->forge->dropColumn('conquistas', 'codigo');
    }
}

