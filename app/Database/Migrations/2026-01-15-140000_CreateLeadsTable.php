<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLeadsTable extends Migration
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
            'codigo' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'evento_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'vendedor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'etapa' => [
                'type'       => 'ENUM',
                'constraint' => ['novo', 'primeiro_contato', 'qualificado', 'proposta', 'negociacao', 'ganho', 'perdido'],
                'default'    => 'novo',
            ],
            'nome' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'nome_fantasia' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'tipo_pessoa' => [
                'type'       => 'ENUM',
                'constraint' => ['fisica', 'juridica'],
                'default'    => 'juridica',
            ],
            'documento' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'telefone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'celular' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'segmento' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'instagram' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'origem' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'valor_estimado' => [
                'type'    => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
            ],
            'temperatura' => [
                'type'       => 'ENUM',
                'constraint' => ['frio', 'morno', 'quente'],
                'default'    => 'frio',
            ],
            'proxima_acao' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'proxima_acao_descricao' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'motivo_perda' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'observacoes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'expositor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'contrato_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('codigo');
        $this->forge->addKey('evento_id');
        $this->forge->addKey('vendedor_id');
        $this->forge->addKey('etapa');
        $this->forge->addKey('temperatura');
        $this->forge->addKey('expositor_id');
        $this->forge->addKey('contrato_id');
        
        $this->forge->createTable('leads');
    }

    public function down()
    {
        $this->forge->dropTable('leads');
    }
}
