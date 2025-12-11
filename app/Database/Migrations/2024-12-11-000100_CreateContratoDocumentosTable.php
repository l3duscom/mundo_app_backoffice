<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContratoDocumentosTable extends Migration
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
            'contrato_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'ID do contrato',
            ],
            'modelo_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID do modelo utilizado',
            ],
            'titulo' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'comment'    => 'Título do documento',
            ],
            'conteudo_html' => [
                'type'    => 'LONGTEXT',
                'comment' => 'Conteúdo HTML do documento preenchido',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['rascunho', 'pendente_assinatura', 'assinado', 'confirmado', 'cancelado'],
                'default'    => 'rascunho',
                'comment'    => 'Status do documento',
            ],
            'hash_assinatura' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Hash único para assinatura',
            ],
            'ip_assinatura' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
                'comment'    => 'IP do assinante',
            ],
            'user_agent_assinatura' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'comment'    => 'User agent do navegador na assinatura',
            ],
            'data_envio' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Data que foi enviado para assinatura',
            ],
            'data_assinatura' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Data que foi assinado',
            ],
            'data_confirmacao' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Data que foi confirmado pelo sistema',
            ],
            'assinado_por' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
                'comment'    => 'Nome de quem assinou',
            ],
            'documento_assinante' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'CPF/CNPJ de quem assinou',
            ],
            'confirmado_por' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID do usuário que confirmou',
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
        $this->forge->addKey('contrato_id');
        $this->forge->addKey('modelo_id');
        $this->forge->addKey('status');
        $this->forge->addKey('hash_assinatura');
        $this->forge->createTable('contrato_documentos');
    }

    public function down()
    {
        $this->forge->dropTable('contrato_documentos');
    }
}

