<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNovasSituacoesContratos extends Migration
{
    public function up()
    {
        // Altera o ENUM para incluir aguardando_credenciamento e finalizado
        $this->db->query("ALTER TABLE contratos MODIFY COLUMN situacao ENUM(
            'proposta',
            'proposta_aceita',
            'contrato_assinado',
            'aguardando_credenciamento',
            'pagamento_aberto',
            'pagamento_andamento',
            'aguardando_contrato',
            'pagamento_confirmado',
            'finalizado',
            'cancelado',
            'banido'
        ) NOT NULL DEFAULT 'proposta'");
    }

    public function down()
    {
        // Reverte para o ENUM original (cuidado: pode perder dados)
        $this->db->query("ALTER TABLE contratos MODIFY COLUMN situacao ENUM(
            'proposta',
            'proposta_aceita',
            'contrato_assinado',
            'pagamento_aberto',
            'pagamento_andamento',
            'aguardando_contrato',
            'pagamento_confirmado',
            'cancelado',
            'banido'
        ) NOT NULL DEFAULT 'proposta'");
    }
}
