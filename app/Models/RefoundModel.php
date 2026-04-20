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

    /**
     * Lista refounds para relatório (filtro por data da solicitação: created_at).
     *
     * @param array $filtros data_inicio, data_fim (Y-m-d), evento_id?, tipo_solicitacao?, status?
     */
    public function listarParaRelatorio(array $filtros): array
    {
        $q = $this->where('DATE(created_at) >=', $filtros['data_inicio'])
            ->where('DATE(created_at) <=', $filtros['data_fim']);

        if (! empty($filtros['evento_id'])) {
            $q = $q->where('evento_id', (int) $filtros['evento_id']);
        }

        if (! empty($filtros['tipo_solicitacao'])) {
            $q = $q->where('tipo_solicitacao', $filtros['tipo_solicitacao']);
        }

        if (! empty($filtros['status'])) {
            $q = $q->where('status', $filtros['status']);
        }

        return $q->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Totais agregados para o mesmo conjunto de filtros do relatório.
     *
     * @return object{total_registros: int|string, valor_total: float|string}
     */
    public function totaisParaRelatorio(array $filtros)
    {
        $builder = $this->builder();
        $builder->select('COUNT(*) AS total_registros, COALESCE(SUM(pedido_valor_total), 0) AS valor_total', false);
        $builder->where('DATE(created_at) >=', $filtros['data_inicio']);
        $builder->where('DATE(created_at) <=', $filtros['data_fim']);

        if (! empty($filtros['evento_id'])) {
            $builder->where('evento_id', (int) $filtros['evento_id']);
        }

        if (! empty($filtros['tipo_solicitacao'])) {
            $builder->where('tipo_solicitacao', $filtros['tipo_solicitacao']);
        }

        if (! empty($filtros['status'])) {
            $builder->where('status', $filtros['status']);
        }

        return $builder->get()->getRow();
    }

    /**
     * Mesmos filtros do relatório, com uma linha por ingresso do pedido vinculado à solicitação.
     *
     * @return array<int, object>
     */
    public function listarParaRelatorioDetalheIngressos(array $filtros): array
    {
        $db = \Config\Database::connect();
        $b = $db->table('refounds');
        $b->select(
            'refounds.id AS refound_id, refounds.pedido_id, refounds.cliente_nome, refounds.cliente_email, '
            . 'refounds.pedido_codigo, refounds.evento_nome, refounds.tipo_solicitacao, refounds.status, '
            . 'refounds.created_at AS refound_created_at, refounds.processado_em, '
            . 'ingressos.id AS ingresso_id, ingressos.nome AS ingresso_nome, ingressos.codigo AS ingresso_codigo, '
            . 'ingressos.valor AS ingresso_valor, ingressos.tipo AS ingresso_tipo, ingressos.participante AS ingresso_participante',
            false
        );
        $b->join('ingressos', 'ingressos.pedido_id = refounds.pedido_id', 'inner');
        $b->where('refounds.deleted_at', null);
        $b->where('ingressos.deleted_at', null);
        $b->where('DATE(refounds.created_at) >=', $filtros['data_inicio']);
        $b->where('DATE(refounds.created_at) <=', $filtros['data_fim']);

        if (! empty($filtros['evento_id'])) {
            $b->where('refounds.evento_id', (int) $filtros['evento_id']);
        }

        if (! empty($filtros['tipo_solicitacao'])) {
            $b->where('refounds.tipo_solicitacao', $filtros['tipo_solicitacao']);
        }

        if (! empty($filtros['status'])) {
            $b->where('refounds.status', $filtros['status']);
        }

        $b->orderBy('refounds.created_at', 'DESC');
        $b->orderBy('ingressos.id', 'ASC');

        return $b->get()->getResult();
    }

    /**
     * Totais na granularidade ingresso (quantidade de linhas e soma dos valores dos ingressos).
     *
     * @return object{total_linhas: int|string, valor_ingressos: float|string}|null
     */
    public function totaisParaRelatorioDetalheIngressos(array $filtros)
    {
        $db = \Config\Database::connect();
        $b = $db->table('refounds');
        $b->select('COUNT(ingressos.id) AS total_linhas, COALESCE(SUM(ingressos.valor), 0) AS valor_ingressos', false);
        $b->join('ingressos', 'ingressos.pedido_id = refounds.pedido_id', 'inner');
        $b->where('refounds.deleted_at', null);
        $b->where('ingressos.deleted_at', null);
        $b->where('DATE(refounds.created_at) >=', $filtros['data_inicio']);
        $b->where('DATE(refounds.created_at) <=', $filtros['data_fim']);

        if (! empty($filtros['evento_id'])) {
            $b->where('refounds.evento_id', (int) $filtros['evento_id']);
        }

        if (! empty($filtros['tipo_solicitacao'])) {
            $b->where('refounds.tipo_solicitacao', $filtros['tipo_solicitacao']);
        }

        if (! empty($filtros['status'])) {
            $b->where('refounds.status', $filtros['status']);
        }

        return $b->get()->getRow();
    }
}
