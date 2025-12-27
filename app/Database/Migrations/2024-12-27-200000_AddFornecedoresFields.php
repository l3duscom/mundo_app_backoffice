<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFornecedoresFields extends Migration
{
    public function up()
    {
        // Adiciona novos campos na tabela fornecedores
        $this->forge->addColumn('fornecedores', [
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'telefone'
            ],
            'categoria' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'email'
            ],
            'nome_contato' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'categoria'
            ],
            'telefone_contato' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'nome_contato'
            ],
            'banco' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'estado'
            ],
            'agencia' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'banco'
            ],
            'conta' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => true,
                'after' => 'agencia'
            ],
            'pix' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'conta'
            ],
            'observacoes' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'pix'
            ],
        ]);

        // Adiciona índice para categoria
        $this->db->query('ALTER TABLE fornecedores ADD INDEX idx_categoria (categoria)');
    }

    public function down()
    {
        // Remove os campos adicionados
        $this->forge->dropColumn('fornecedores', [
            'email',
            'categoria',
            'nome_contato',
            'telefone_contato',
            'banco',
            'agencia',
            'conta',
            'pix',
            'observacoes'
        ]);
    }
}
