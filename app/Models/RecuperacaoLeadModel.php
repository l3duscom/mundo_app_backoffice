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
                COUNT(DISTINCT p.id) AS total_pedidos_origem,
                SUM(i.quantidade) AS total_ingressos_origem,
                SUM(i.valor) AS valor_total_origem,
                MIN(p.created_at) AS primeira_compra_origem,
                MAX(p.created_at) AS ultima_compra_origem,
                rl.id AS recuperacao_id,
                rl.status AS recuperacao_status,
                rl.observacao AS recuperacao_observacao,
                rl.updated_at AS recuperacao_atualizado_em
            FROM ingressos i
            INNER JOIN pedidos p ON p.id = i.pedido_id
            INNER JOIN usuarios u ON u.id = i.user_id
            LEFT JOIN clientes c ON c.usuario_id = u.id
            LEFT JOIN recuperacao_leads rl
                   ON rl.user_id = u.id
                  AND rl.evento_origem_id = ?
                  AND rl.evento_destino_id = ?
                  AND rl.deleted_at IS NULL
            WHERE p.evento_id = ?
              AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
              AND i.tipo NOT IN ('cinemark', 'adicional', 'produto', 'acesso')
              AND i.deleted_at IS NULL
              AND p.deleted_at IS NULL
              AND NOT EXISTS (
                  SELECT 1
                  FROM ingressos i2
                  INNER JOIN pedidos p2 ON p2.id = i2.pedido_id
                  WHERE i2.user_id = u.id
                    AND p2.evento_id = ?
                    AND p2.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
                    AND i2.tipo NOT IN ('cinemark', 'adicional', 'produto', 'acesso')
                    AND i2.deleted_at IS NULL
                    AND p2.deleted_at IS NULL
              )
            GROUP BY u.id, u.nome, u.email, c.telefone, rl.id, rl.status, rl.observacao, rl.updated_at
            ORDER BY valor_total_origem DESC
        ";

        return $this->db->query($sql, [
            $eventoOrigemId,
            $eventoDestinoId,
            $eventoOrigemId,
            $eventoDestinoId,
        ])->getResultArray();
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
