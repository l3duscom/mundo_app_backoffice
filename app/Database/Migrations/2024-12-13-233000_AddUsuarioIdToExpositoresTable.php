<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUsuarioIdToExpositoresTable extends Migration
{
    public function up()
    {
        $fields = [
            'usuario_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'id',
                'comment'    => 'ID do usuário vinculado ao expositor',
            ],
        ];

        $this->forge->addColumn('expositores', $fields);

        // Adiciona índice para o campo usuario_id
        $this->forge->addKey('usuario_id');
    }

    public function down()
    {
        $this->forge->dropColumn('expositores', 'usuario_id');
    }
}

/*
-- Script MySQL equivalente:

ALTER TABLE `expositores` 
ADD COLUMN `usuario_id` INT(11) UNSIGNED NULL COMMENT 'ID do usuário vinculado ao expositor' AFTER `id`,
ADD INDEX `expositores_usuario_id` (`usuario_id`);

-- Para reverter:
-- ALTER TABLE `expositores` DROP COLUMN `usuario_id`;
*/
