<?php

namespace App\Models;

use CodeIgniter\Model;

class RefoundModel extends Model
{
    protected $table = 'refounds';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useSoftDeletes = true;
    
    protected $allowedFields = [
        'pedido_id',
        'cliente_id',
        'tipo_solicitacao',
        'aceito',
        'pedido_codigo',
        'pedido_valor_total',
        'pedido_data_compra',
        'pedido_forma_pagament',
        'pedido_status',
        'cliente_nome',
        'cliente_email',
        'evento_id',
        'evento_nome',
        'evento_data_inicio',
        'ingressos_originais',
        'tipo_upgrade',
        'oferta_titulo',
        'oferta_subtitulo',
        'oferta_vantagem_valor',
        'opcao_selecionada',
        'oferta_detalhes',
        'beneficios_apresentados',
        'ingressos_para_upgrade',
        'ip_solicitacao',
        'user_agent',
        'observacoes',
        'status',
        'processado_em',
        'processado_por',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    /**
     * Lista todos os refounds para a tabela admin
     */
    public function listaRefoundsAdmin($event_id = null)
    {
        $builder = $this->select([
            'refounds.*',
        ]);

        if ($event_id) {
            $builder->where('refounds.evento_id', $event_id);
        }

        return $builder->orderBy('refounds.created_at', 'DESC')
                       ->findAll();
    }

    /**
     * Retorna estatísticas dos refounds
     */
    public function getEstatisticas($event_id = null)
    {
        $builder = $this->select([
            'COUNT(*) as total',
            'SUM(CASE WHEN status = "pendente" THEN 1 ELSE 0 END) as pendentes',
            'SUM(CASE WHEN status = "concluido" THEN 1 ELSE 0 END) as aprovados',
            'SUM(CASE WHEN status = "cancelado" THEN 1 ELSE 0 END) as rejeitados',
            'SUM(CASE WHEN tipo_solicitacao = "upgrade" THEN 1 ELSE 0 END) as upgrades',
            'SUM(CASE WHEN tipo_solicitacao = "reembolso" THEN 1 ELSE 0 END) as reembolsos',
            'SUM(pedido_valor_total) as valor_total',
            // Valores por status
            'SUM(CASE WHEN status = "pendente" THEN pedido_valor_total ELSE 0 END) as valor_pendentes',
            'SUM(CASE WHEN status = "concluido" THEN pedido_valor_total ELSE 0 END) as valor_aprovados',
            'SUM(CASE WHEN status = "cancelado" THEN pedido_valor_total ELSE 0 END) as valor_rejeitados',
            // Valores por tipo
            'SUM(CASE WHEN tipo_solicitacao = "upgrade" THEN pedido_valor_total ELSE 0 END) as valor_upgrades',
            'SUM(CASE WHEN tipo_solicitacao = "reembolso" THEN pedido_valor_total ELSE 0 END) as valor_reembolsos',
        ]);

        if ($event_id) {
            $builder->where('evento_id', $event_id);
        }

        return $builder->first();
    }

    /**
     * Lista refunds por cliente_id (para a área do usuário)
     */
    public function listaRefoundsPorCliente($cliente_id)
    {
        return $this->select([
            'refounds.*',
        ])
        ->where('cliente_id', $cliente_id)
        ->orderBy('created_at', 'DESC')
        ->findAll();
    }

    /**
     * Conta refunds pendentes do cliente
     */
    public function contaRefoundsPendentesPorCliente($cliente_id)
    {
        return $this->where('cliente_id', $cliente_id)
                    ->where('status', 'pendente')
                    ->countAllResults();
    }
}
