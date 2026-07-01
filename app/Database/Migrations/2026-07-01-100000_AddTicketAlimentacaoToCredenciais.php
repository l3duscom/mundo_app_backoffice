<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Cria a tabela auxiliar `credencial_ticket_alimentacao` para registrar
 * a retirada do ticket de alimentação por credencial (sala VIP).
 *
 * Optamos por uma tabela nova em vez de ALTER TABLE na `credenciais`
 * porque a tabela original é muito acessada em produção e o ALTER
 * fica preso em metadata lock indefinidamente.
 *
 * Semântica:
 *  - Presença da linha  = ticket retirado.
 *  - Ausência da linha  = ticket ainda não retirado.
 *  - Para "desmarcar"   → DELETE.
 *  - Para "marcar"      → INSERT IGNORE (preserva a data da primeira marcação).
 */
class AddTicketAlimentacaoToCredenciais extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'credencial_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'retirado_em' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'operador_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('credencial_id');
        $this->forge->createTable('credencial_ticket_alimentacao');
    }

    public function down()
    {
        $this->forge->dropTable('credencial_ticket_alimentacao');
    }
}
