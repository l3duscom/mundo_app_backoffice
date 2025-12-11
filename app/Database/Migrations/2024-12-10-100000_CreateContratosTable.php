<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContratosTable extends Migration
{
    public function up()
    {
        $fields = [
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
                'null'       => false,
                'comment'    => 'ID do evento',
            ],
            'expositor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'ID do expositor',
            ],
            'codigo' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'Código único do contrato (ex: CTR-2024-0001)',
            ],
            'tipo_contrato' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'comment'    => 'Tipo: Cota, Patrocínio, Espaço Comercial, Artist Alley, Food Park, etc.',
            ],
            'descricao' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Descrição do contrato/espaço contratado',
            ],
            'localizacao' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Localização do espaço (ex: Stand A-15, Mesa 23)',
            ],
            'metragem' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'Tamanho do espaço (ex: 3x3m, 2x2m)',
            ],
            'situacao' => [
                'type'       => 'ENUM',
                'constraint' => ['proposta', 'proposta_aceita', 'contrato_assinado', 'pagamento_aberto', 'pagamento_andamento', 'pagamento_confirmado', 'cancelado', 'banido'],
                'default'    => 'proposta',
                'null'       => false,
                'comment'    => 'Situação atual do contrato',
            ],
            'valor_original' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
                'null'       => false,
            ],
            'valor_desconto' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
                'null'       => false,
            ],
            'valor_final' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
                'null'       => false,
            ],
            'quantidade_parcelas' => [
                'type'       => 'INT',
                'constraint' => 3,
                'default'    => 1,
                'null'       => false,
            ],
            'valor_parcela' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
                'null'       => false,
            ],
            'valor_pago' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
                'null'       => false,
            ],
            'valor_em_aberto' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
                'null'       => false,
            ],
            'data_proposta' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'data_aceite' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'data_assinatura' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'data_vencimento' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'data_pagamento' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'forma_pagamento' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'observacoes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'arquivo_contrato' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'arquivo_comprovante' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
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
        ];

        $this->forge->addField($fields);
        
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('event_id');
        $this->forge->addKey('expositor_id');
        $this->forge->addKey('situacao');
        $this->forge->addKey('tipo_contrato');
        $this->forge->addKey('codigo');
        $this->forge->addKey(['deleted_at', 'id']);
        $this->forge->addKey('created_at');

        $this->forge->createTable('contratos');
    }

    public function down()
    {
        $this->forge->dropTable('contratos');
    }
}

