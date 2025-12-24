<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ExpandCuponsAndPedidos extends Migration
{
    public function up()
    {
        // Expandir tabela cupons
        $this->forge->addColumn('cupons', [
            'codigo' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'nome',
            ],
            'tipo' => [
                'type' => 'ENUM',
                'constraint' => ['percentual', 'fixo'],
                'default' => 'percentual',
                'after' => 'desconto',
            ],
            'valor_minimo' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
                'after' => 'tipo',
            ],
            'quantidade_total' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'after' => 'valor_minimo',
                'comment' => 'NULL = ilimitado',
            ],
            'quantidade_usada' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'after' => 'quantidade_total',
            ],
            'uso_por_usuario' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 1,
                'after' => 'quantidade_usada',
                'comment' => 'Limite de uso por usuário',
            ],
            'data_inicio' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'uso_por_usuario',
            ],
            'data_fim' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'data_inicio',
            ],
            'ativo' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'after' => 'data_fim',
            ],
        ]);

        // Adicionar índice único para código
        $this->forge->addKey('codigo', false, true, 'unique_codigo');

        // Adicionar colunas na tabela pedidos
        $this->forge->addColumn('pedidos', [
            'cupom_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'valor_liquido',
            ],
            'valor_desconto' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
                'after' => 'cupom_id',
            ],
        ]);

        // Adicionar índice para cupom_id
        $this->db->query('ALTER TABLE pedidos ADD INDEX idx_cupom_id (cupom_id)');
    }

    public function down()
    {
        // Remover colunas de pedidos
        $this->forge->dropColumn('pedidos', ['cupom_id', 'valor_desconto']);

        // Remover novas colunas de cupons
        $this->forge->dropColumn('cupons', [
            'codigo',
            'tipo',
            'valor_minimo',
            'quantidade_total',
            'quantidade_usada',
            'uso_por_usuario',
            'data_inicio',
            'data_fim',
            'ativo',
        ]);
    }
}
