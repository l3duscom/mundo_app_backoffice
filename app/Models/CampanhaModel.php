<?php

namespace App\Models;

use CodeIgniter\Model;

class CampanhaModel extends Model
{
    protected $table      = 'pedido_utms';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    private const STATUS_PAGOS = ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'];

    /**
     * Métricas totais do evento dentro do período.
     * Inclui % de pedidos com UTM (cobertura).
     */
    public function metricasGerais(int $eventId, ?string $dataInicial, ?string $dataFinal): array
    {
        $base = $this->db->table('pedidos p')
            ->where('p.evento_id', $eventId)
            ->where('p.deleted_at', null)
            ->whereIn('p.status', self::STATUS_PAGOS);

        if ($dataInicial) $base->where('DATE(p.created_at) >=', $dataInicial);
        if ($dataFinal)   $base->where('DATE(p.created_at) <=', $dataFinal);

        $totais = $base
            ->select('COUNT(DISTINCT p.id) AS qtd_pedidos, COALESCE(SUM(p.valor_total),0) AS receita')
            ->get()->getRow();

        $comUtm = $this->db->table('pedidos p')
            ->join('pedido_utms u', 'u.pedido_id = p.id', 'inner')
            ->where('p.evento_id', $eventId)
            ->where('p.deleted_at', null)
            ->whereIn('p.status', self::STATUS_PAGOS);

        if ($dataInicial) $comUtm->where('DATE(p.created_at) >=', $dataInicial);
        if ($dataFinal)   $comUtm->where('DATE(p.created_at) <=', $dataFinal);

        $qtdComUtm = (int) $comUtm->countAllResults();

        $qtdPedidos = (int) ($totais->qtd_pedidos ?? 0);
        $receita    = (float) ($totais->receita ?? 0);
        $ticket     = $qtdPedidos > 0 ? $receita / $qtdPedidos : 0;
        $cobertura  = $qtdPedidos > 0 ? ($qtdComUtm / $qtdPedidos) * 100 : 0;

        return [
            'qtd_pedidos'   => $qtdPedidos,
            'qtd_com_utm'   => $qtdComUtm,
            'receita'       => $receita,
            'ticket_medio'  => $ticket,
            'cobertura_pct' => $cobertura,
        ];
    }

    /**
     * Agrega por uma das colunas utm_source|utm_medium|utm_campaign.
     */
    public function agregarPor(string $coluna, int $eventId, ?string $dataInicial, ?string $dataFinal, array $filtros = []): array
    {
        $colunasValidas = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];
        if (! in_array($coluna, $colunasValidas, true)) {
            return [];
        }

        $builder = $this->db->table('pedidos p')
            ->select("COALESCE(NULLIF(u.{$coluna}, ''), '(direto)') AS origem,
                      COUNT(DISTINCT p.id) AS qtd_pedidos,
                      COALESCE(SUM(p.valor_total), 0) AS receita")
            ->join('pedido_utms u', 'u.pedido_id = p.id', 'left')
            ->where('p.evento_id', $eventId)
            ->where('p.deleted_at', null)
            ->whereIn('p.status', self::STATUS_PAGOS);

        if ($dataInicial) $builder->where('DATE(p.created_at) >=', $dataInicial);
        if ($dataFinal)   $builder->where('DATE(p.created_at) <=', $dataFinal);

        if (! empty($filtros['utm_source']))   $builder->where('u.utm_source', $filtros['utm_source']);
        if (! empty($filtros['utm_medium']))   $builder->where('u.utm_medium', $filtros['utm_medium']);
        if (! empty($filtros['utm_campaign'])) $builder->where('u.utm_campaign', $filtros['utm_campaign']);

        $rows = $builder->groupBy('origem')
            ->orderBy('receita', 'DESC')
            ->get()->getResultArray();

        foreach ($rows as &$r) {
            $r['qtd_pedidos']  = (int) $r['qtd_pedidos'];
            $r['receita']      = (float) $r['receita'];
            $r['ticket_medio'] = $r['qtd_pedidos'] > 0 ? $r['receita'] / $r['qtd_pedidos'] : 0;
        }

        return $rows;
    }

    /**
     * Evolução diária de receita agrupada por utm_source.
     * Retorna labels (dias) + datasets (uma série por source).
     */
    public function evolucaoDiariaPorSource(int $eventId, ?string $dataInicial, ?string $dataFinal, array $filtros = []): array
    {
        $builder = $this->db->table('pedidos p')
            ->select("DATE(p.created_at) AS dia,
                      COALESCE(NULLIF(u.utm_source, ''), '(direto)') AS source,
                      COALESCE(SUM(p.valor_total), 0) AS receita")
            ->join('pedido_utms u', 'u.pedido_id = p.id', 'left')
            ->where('p.evento_id', $eventId)
            ->where('p.deleted_at', null)
            ->whereIn('p.status', self::STATUS_PAGOS);

        if ($dataInicial) $builder->where('DATE(p.created_at) >=', $dataInicial);
        if ($dataFinal)   $builder->where('DATE(p.created_at) <=', $dataFinal);

        if (! empty($filtros['utm_source']))   $builder->where('u.utm_source', $filtros['utm_source']);
        if (! empty($filtros['utm_medium']))   $builder->where('u.utm_medium', $filtros['utm_medium']);
        if (! empty($filtros['utm_campaign'])) $builder->where('u.utm_campaign', $filtros['utm_campaign']);

        $rows = $builder->groupBy(['dia', 'source'])
            ->orderBy('dia', 'ASC')
            ->get()->getResultArray();

        $dias = [];
        $sources = [];
        $matriz  = [];

        foreach ($rows as $r) {
            $dia = $r['dia'];
            $src = $r['source'];
            $dias[$dia] = true;
            $sources[$src] = true;
            $matriz[$src][$dia] = (float) $r['receita'];
        }

        $diasOrdenados = array_keys($dias);
        sort($diasOrdenados);

        $datasets = [];
        foreach (array_keys($sources) as $src) {
            $serie = [];
            foreach ($diasOrdenados as $dia) {
                $serie[] = $matriz[$src][$dia] ?? 0;
            }
            $datasets[] = ['source' => $src, 'data' => $serie];
        }

        return [
            'labels'   => $diasOrdenados,
            'datasets' => $datasets,
        ];
    }

    /**
     * Lista detalhada de pedidos com UTM, paginada via offset/limit.
     */
    public function listaPedidos(int $eventId, ?string $dataInicial, ?string $dataFinal, array $filtros = [], int $limit = 500): array
    {
        $builder = $this->db->table('pedidos p')
            ->select('p.id, p.cod_pedido, p.valor_total, p.created_at, p.status,
                      usu.nome AS cliente,
                      u.utm_source, u.utm_medium, u.utm_campaign, u.utm_content, u.utm_term')
            ->join('pedido_utms u', 'u.pedido_id = p.id', 'left')
            ->join('usuarios usu', 'usu.id = p.user_id', 'left')
            ->where('p.evento_id', $eventId)
            ->where('p.deleted_at', null)
            ->whereIn('p.status', self::STATUS_PAGOS);

        if ($dataInicial) $builder->where('DATE(p.created_at) >=', $dataInicial);
        if ($dataFinal)   $builder->where('DATE(p.created_at) <=', $dataFinal);

        if (! empty($filtros['utm_source']))   $builder->where('u.utm_source', $filtros['utm_source']);
        if (! empty($filtros['utm_medium']))   $builder->where('u.utm_medium', $filtros['utm_medium']);
        if (! empty($filtros['utm_campaign'])) $builder->where('u.utm_campaign', $filtros['utm_campaign']);

        return $builder->orderBy('p.created_at', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    /**
     * Retorna valores distintos de uma coluna UTM (para popular selects).
     */
    public function valoresDistintos(string $coluna, int $eventId): array
    {
        $colunasValidas = ['utm_source', 'utm_medium', 'utm_campaign'];
        if (! in_array($coluna, $colunasValidas, true)) {
            return [];
        }

        $rows = $this->db->table('pedido_utms u')
            ->select("DISTINCT u.{$coluna} AS valor")
            ->join('pedidos p', 'p.id = u.pedido_id', 'inner')
            ->where('p.evento_id', $eventId)
            ->where('p.deleted_at', null)
            ->where("u.{$coluna} IS NOT NULL")
            ->where("u.{$coluna} !=", '')
            ->orderBy('valor', 'ASC')
            ->get()->getResultArray();

        return array_column($rows, 'valor');
    }
}
