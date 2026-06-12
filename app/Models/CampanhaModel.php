<?php

namespace App\Models;

use CodeIgniter\Model;

class CampanhaModel extends Model
{
    protected $table      = 'pedido_utms';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    private const STATUS_PAGOS_SQL = "('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')";

    /**
     * Devolve fragmento WHERE de período + binds.
     */
    private function periodoWhere(?string $dataInicial, ?string $dataFinal, array &$binds): string
    {
        $sql = '';
        if ($dataInicial) { $sql .= ' AND DATE(p.created_at) >= ? '; $binds[] = $dataInicial; }
        if ($dataFinal)   { $sql .= ' AND DATE(p.created_at) <= ? '; $binds[] = $dataFinal; }
        return $sql;
    }

    private function filtrosWhere(array $filtros, array &$binds): string
    {
        $sql = '';
        foreach (['utm_source', 'utm_medium', 'utm_campaign'] as $f) {
            if (! empty($filtros[$f])) {
                $sql .= " AND u.{$f} = ? ";
                $binds[] = $filtros[$f];
            }
        }
        return $sql;
    }

    public function metricasGerais(int $eventId, ?string $dataInicial, ?string $dataFinal): array
    {
        $binds = [$eventId];
        $periodo = $this->periodoWhere($dataInicial, $dataFinal, $binds);

        $sqlTot = "
            SELECT COUNT(DISTINCT p.id) AS qtd_pedidos, COALESCE(SUM(p.total), 0) AS receita
            FROM pedidos p
            WHERE p.evento_id = ?
              AND p.deleted_at IS NULL
              AND p.status IN " . self::STATUS_PAGOS_SQL . "
              $periodo
        ";
        $tot = $this->db->query($sqlTot, $binds)->getRow();

        $binds2 = [$eventId];
        $periodo2 = $this->periodoWhere($dataInicial, $dataFinal, $binds2);
        $sqlUtm = "
            SELECT COUNT(DISTINCT p.id) AS qtd
            FROM pedidos p
            INNER JOIN pedido_utms u ON u.pedido_id = p.id
            WHERE p.evento_id = ?
              AND p.deleted_at IS NULL
              AND p.status IN " . self::STATUS_PAGOS_SQL . "
              $periodo2
        ";
        $utm = $this->db->query($sqlUtm, $binds2)->getRow();

        $qtd = (int) ($tot->qtd_pedidos ?? 0);
        $rec = (float) ($tot->receita ?? 0);
        $qtdUtm = (int) ($utm->qtd ?? 0);

        return [
            'qtd_pedidos'   => $qtd,
            'qtd_com_utm'   => $qtdUtm,
            'receita'       => $rec,
            'ticket_medio'  => $qtd > 0 ? $rec / $qtd : 0,
            'cobertura_pct' => $qtd > 0 ? ($qtdUtm / $qtd) * 100 : 0,
        ];
    }

    public function agregarPor(string $coluna, int $eventId, ?string $dataInicial, ?string $dataFinal, array $filtros = []): array
    {
        $colunasValidas = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];
        if (! in_array($coluna, $colunasValidas, true)) {
            return [];
        }

        $binds = [$eventId];
        $periodo = $this->periodoWhere($dataInicial, $dataFinal, $binds);
        $filtroSql = $this->filtrosWhere($filtros, $binds);

        $sql = "
            SELECT COALESCE(NULLIF(u.{$coluna}, ''), '(direto)') AS origem,
                   COUNT(DISTINCT p.id) AS qtd_pedidos,
                   COALESCE(SUM(p.total), 0) AS receita
            FROM pedidos p
            LEFT JOIN pedido_utms u ON u.pedido_id = p.id
            WHERE p.evento_id = ?
              AND p.deleted_at IS NULL
              AND p.status IN " . self::STATUS_PAGOS_SQL . "
              $periodo
              $filtroSql
            GROUP BY origem
            ORDER BY receita DESC
        ";

        $rows = $this->db->query($sql, $binds)->getResultArray();
        foreach ($rows as &$r) {
            $r['qtd_pedidos']  = (int) $r['qtd_pedidos'];
            $r['receita']      = (float) $r['receita'];
            $r['ticket_medio'] = $r['qtd_pedidos'] > 0 ? $r['receita'] / $r['qtd_pedidos'] : 0;
        }
        return $rows;
    }

    public function evolucaoDiariaPorSource(int $eventId, ?string $dataInicial, ?string $dataFinal, array $filtros = []): array
    {
        $binds = [$eventId];
        $periodo = $this->periodoWhere($dataInicial, $dataFinal, $binds);
        $filtroSql = $this->filtrosWhere($filtros, $binds);

        $sql = "
            SELECT DATE(p.created_at) AS dia,
                   COALESCE(NULLIF(u.utm_source, ''), '(direto)') AS source,
                   COALESCE(SUM(p.total), 0) AS receita
            FROM pedidos p
            LEFT JOIN pedido_utms u ON u.pedido_id = p.id
            WHERE p.evento_id = ?
              AND p.deleted_at IS NULL
              AND p.status IN " . self::STATUS_PAGOS_SQL . "
              $periodo
              $filtroSql
            GROUP BY dia, source
            ORDER BY dia ASC
        ";
        $rows = $this->db->query($sql, $binds)->getResultArray();

        $dias = $sources = $matriz = [];
        foreach ($rows as $r) {
            $dias[$r['dia']]      = true;
            $sources[$r['source']] = true;
            $matriz[$r['source']][$r['dia']] = (float) $r['receita'];
        }
        $diasOrdenados = array_keys($dias);
        sort($diasOrdenados);

        $datasets = [];
        foreach (array_keys($sources) as $src) {
            $serie = [];
            foreach ($diasOrdenados as $d) {
                $serie[] = $matriz[$src][$d] ?? 0;
            }
            $datasets[] = ['source' => $src, 'data' => $serie];
        }

        return ['labels' => $diasOrdenados, 'datasets' => $datasets];
    }

    public function listaPedidos(int $eventId, ?string $dataInicial, ?string $dataFinal, array $filtros = [], int $limit = 500): array
    {
        $binds = [$eventId];
        $periodo = $this->periodoWhere($dataInicial, $dataFinal, $binds);
        $filtroSql = $this->filtrosWhere($filtros, $binds);
        $binds[] = $limit;

        $sql = "
            SELECT p.id, p.codigo AS cod_pedido, p.total AS valor_total, p.created_at, p.status,
                   usu.nome AS cliente,
                   u.utm_source, u.utm_medium, u.utm_campaign, u.utm_content, u.utm_term
            FROM pedidos p
            LEFT JOIN pedido_utms u ON u.pedido_id = p.id
            LEFT JOIN usuarios usu ON usu.id = p.user_id
            WHERE p.evento_id = ?
              AND p.deleted_at IS NULL
              AND p.status IN " . self::STATUS_PAGOS_SQL . "
              $periodo
              $filtroSql
            ORDER BY p.created_at DESC
            LIMIT ?
        ";

        return $this->db->query($sql, $binds)->getResultArray();
    }

    public function valoresDistintos(string $coluna, int $eventId): array
    {
        $colunasValidas = ['utm_source', 'utm_medium', 'utm_campaign'];
        if (! in_array($coluna, $colunasValidas, true)) {
            return [];
        }

        $sql = "
            SELECT DISTINCT u.{$coluna} AS valor
            FROM pedido_utms u
            INNER JOIN pedidos p ON p.id = u.pedido_id
            WHERE p.evento_id = ?
              AND p.deleted_at IS NULL
              AND u.{$coluna} IS NOT NULL
              AND u.{$coluna} != ''
            ORDER BY valor ASC
        ";
        $rows = $this->db->query($sql, [$eventId])->getResultArray();
        return array_column($rows, 'valor');
    }
}
