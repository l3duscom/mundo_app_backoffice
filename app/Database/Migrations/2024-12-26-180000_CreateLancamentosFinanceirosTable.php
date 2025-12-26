<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLancamentosFinanceirosTable extends Migration
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
            'event_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'tipo' => [
                'type'       => 'ENUM',
                'constraint' => ['ENTRADA', 'SAIDA'],
                'default'    => 'ENTRADA',
            ],
            'origem' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'MANUAL',
                'comment'    => 'MANUAL, CONTRATO, INGRESSO, PDV, CONTA_PAGAR',
            ],
            'referencia_tipo' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'Nome da tabela de origem',
            ],
            'referencia_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'descricao' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'valor' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
            ],
            'data_lancamento' => [
                'type' => 'DATE',
            ],
            'data_pagamento' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pendente', 'pago', 'cancelado'],
                'default'    => 'pendente',
            ],
            'forma_pagamento' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'categoria' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'observacoes' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addKey('event_id');
        $this->forge->addKey('tipo');
        $this->forge->addKey('origem');
        $this->forge->addKey('status');
        $this->forge->addKey('data_lancamento');
        $this->forge->addKey(['referencia_tipo', 'referencia_id']);

        $this->forge->createTable('lancamentos_financeiros', true);
    }

    public function down()
    {
        $this->forge->dropTable('lancamentos_financeiros', true);
    }
}
