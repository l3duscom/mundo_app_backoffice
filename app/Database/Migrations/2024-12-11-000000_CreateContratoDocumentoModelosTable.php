<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContratoDocumentoModelosTable extends Migration
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
            'nome' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'comment'    => 'Nome do modelo (ex: Contrato Espaço Comercial)',
            ],
            'tipo_item' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'comment'    => 'Tipo de item vinculado (ex: Espaço Comercial)',
            ],
            'descricao' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Descrição do modelo',
            ],
            'conteudo_html' => [
                'type'    => 'LONGTEXT',
                'comment' => 'Conteúdo HTML do modelo com placeholders',
            ],
            'variaveis' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'JSON com lista de variáveis disponíveis',
            ],
            'ativo' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
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
        $this->forge->addKey('tipo_item');
        $this->forge->addKey('ativo');
        $this->forge->createTable('contrato_documento_modelos');
    }

    public function down()
    {
        $this->forge->dropTable('contrato_documento_modelos');
    }
}

