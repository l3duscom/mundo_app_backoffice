<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDescricaoToConquistasTable extends Migration
{
    public function up()
    {
        $fields = [
            'descricao' => [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'nome_conquista',
                'comment'    => 'Descrição detalhada da conquista',
            ],
        ];

        $this->forge->addColumn('conquistas', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('conquistas', 'descricao');
    }
}

/*
-- Script MySQL equivalente:

ALTER TABLE `conquistas` 
ADD COLUMN `descricao` TEXT NULL COMMENT 'Descrição detalhada da conquista' 
AFTER `nome_conquista`;

-- Para remover:
-- ALTER TABLE `conquistas` DROP COLUMN `descricao`;

-- Exemplos de UPDATE para adicionar descrições:
UPDATE `conquistas` SET `descricao` = 'Participou do evento pela primeira vez' WHERE `nome_conquista` = 'Primeira Participação';
UPDATE `conquistas` SET `descricao` = 'Assistiu 3 painéis durante o evento' WHERE `nome_conquista` = 'Participou de 3 Painéis';
UPDATE `conquistas` SET `descricao` = 'Participou de Meet & Greet com 5 convidados' WHERE `nome_conquista` = 'Conheceu 5 Convidados';
*/

