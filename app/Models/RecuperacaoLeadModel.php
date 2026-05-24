<?php

namespace App\Models;

use CodeIgniter\Model;

class RecuperacaoLeadModel extends Model
{
    protected $table = 'recuperacao_leads';
    protected $primaryKey = 'id';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $allowedFields = [
        'user_id',
        'evento_origem_id',
        'evento_destino_id',
        'status',
        'observacao',
    ];

    /**
     * Lista clientes que compraram no evento de origem e ainda não
     * compraram no evento de destino (com status de recuperação se houver).
     */
    public function listaLeads(int $eventoOrigemId, int $eventoDestinoId): array
    {
        $sql = "
            SELECT
                u.id AS user_id,
                u.nome,
                u.email,
                c.telefone,
                o.total_pedidos_origem,
                o.total_ingressos_origem,
                o.valor_total_origem,
                o.primeira_compra_origem,
                o.ultima_compra_origem
            FROM (
                SELECT
                    i.user_id,
                    COUNT(DISTINCT p.id) AS total_pedidos_origem,
                    SUM(i.quantidade)    AS total_ingressos_origem,
                    SUM(i.valor)         AS valor_total_origem,
                    MIN(p.created_at)    AS primeira_compra_origem,
                    MAX(p.created_at)    AS ultima_compra_origem
                FROM pedidos p
                INNER JOIN ingressos i ON i.pedido_id = p.id
                WHERE p.evento_id = ?
                  AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
                  AND p.deleted_at IS NULL
                  AND i.deleted_at IS NULL
                  AND i.tipo NOT IN ('cinemark', 'adicional', 'produto', 'acesso')
                  AND i.nome NOT LIKE '%cortesia%'
                GROUP BY i.user_id
            ) o
            INNER JOIN usuarios u ON u.id = o.user_id
            LEFT JOIN clientes  c ON c.usuario_id = u.id
            LEFT JOIN (
                SELECT DISTINCT i2.user_id
                FROM pedidos p2
                INNER JOIN ingressos i2 ON i2.pedido_id = p2.id
                WHERE p2.evento_id = ?
                  AND p2.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
                  AND p2.deleted_at IS NULL
                  AND i2.deleted_at IS NULL
                  AND i2.tipo NOT IN ('cinemark', 'adicional', 'produto', 'acesso')
                  AND i2.nome NOT LIKE '%cortesia%'
            ) destino ON destino.user_id = o.user_id
            WHERE destino.user_id IS NULL
            ORDER BY o.valor_total_origem DESC
        ";

        $leads = $this->db->query($sql, [$eventoOrigemId, $eventoDestinoId])->getResultArray();

        if (empty($leads)) {
            return [];
        }

        $userIds = array_map(static fn ($l) => (int) $l['user_id'], $leads);
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));

        $sqlRec = "
            SELECT user_id, id AS recuperacao_id, status AS recuperacao_status,
                   observacao AS recuperacao_observacao, updated_at AS recuperacao_atualizado_em
            FROM recuperacao_leads
            WHERE evento_origem_id = ?
              AND evento_destino_id = ?
              AND deleted_at IS NULL
              AND user_id IN ($placeholders)
        ";

        $bindings = array_merge([$eventoOrigemId, $eventoDestinoId], $userIds);
        $recs = $this->db->query($sqlRec, $bindings)->getResultArray();

        $recByUser = [];
        foreach ($recs as $r) {
            $recByUser[(int) $r['user_id']] = $r;
        }

        foreach ($leads as &$lead) {
            $rec = $recByUser[(int) $lead['user_id']] ?? null;
            $lead['recuperacao_id']            = $rec['recuperacao_id'] ?? null;
            $lead['recuperacao_status']        = $rec['recuperacao_status'] ?? null;
            $lead['recuperacao_observacao']    = $rec['recuperacao_observacao'] ?? null;
            $lead['recuperacao_atualizado_em'] = $rec['recuperacao_atualizado_em'] ?? null;
        }
        unset($lead);

        return $leads;
    }

    /**
     * Marca como 'revertido' os leads cujo usuário já efetuou compra
     * confirmada no evento destino.
     */
    public function marcaRevertidos(int $eventoDestinoId): int
    {
        $sql = "
            UPDATE recuperacao_leads rl
            INNER JOIN ingressos i ON i.user_id = rl.user_id
            INNER JOIN pedidos p ON p.id = i.pedido_id
            SET rl.status = 'revertido',
                rl.updated_at = NOW()
            WHERE rl.evento_destino_id = ?
              AND rl.deleted_at IS NULL
              AND rl.status IS NOT NULL
              AND rl.status <> 'revertido'
              AND p.evento_id = rl.evento_destino_id
              AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
              AND i.tipo NOT IN ('cinemark', 'adicional', 'produto', 'acesso')
              AND i.deleted_at IS NULL
              AND p.deleted_at IS NULL
        ";

        $this->db->query($sql, [$eventoDestinoId]);

        return $this->db->affectedRows();
    }

    /**
     * Cria ou atualiza um registro de recuperação. Retorna o id final.
     */
    public function definirStatus(int $userId, int $eventoOrigemId, int $eventoDestinoId, ?string $status, ?string $observacao = null): int
    {
        $existente = $this->where([
            'user_id'           => $userId,
            'evento_origem_id'  => $eventoOrigemId,
            'evento_destino_id' => $eventoDestinoId,
        ])->first();

        $payload = [
            'status'     => $status,
            'observacao' => $observacao,
        ];

        if ($existente) {
            $this->update($existente['id'], $payload);
            return (int) $existente['id'];
        }

        $payload['user_id']           = $userId;
        $payload['evento_origem_id']  = $eventoOrigemId;
        $payload['evento_destino_id'] = $eventoDestinoId;

        return (int) $this->insert($payload, true);
    }
}
