<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAguardandoContratoSituacao extends Migration
{
    public function up()
    {
        // Altera o ENUM para incluir 'aguardando_contrato'
        $this->db->query("ALTER TABLE `contratos` MODIFY `situacao` ENUM(
            'proposta',
            'proposta_aceita', 
            'contrato_assinado',
            'pagamento_aberto',
            'pagamento_andamento',
            'aguardando_contrato',
            'pagamento_confirmado',
            'cancelado',
            'banido'
        ) NOT NULL DEFAULT 'proposta' COMMENT 'Situação atual do contrato'");
    }

    public function down()
    {
        // Reverte para o ENUM anterior
        $this->db->query("ALTER TABLE `contratos` MODIFY `situacao` ENUM(
            'proposta',
            'proposta_aceita', 
            'contrato_assinado',
            'pagamento_aberto',
            'pagamento_andamento',
            'pagamento_confirmado',
            'cancelado',
            'banido'
        ) NOT NULL DEFAULT 'proposta' COMMENT 'Situação atual do contrato'");
    }
}

