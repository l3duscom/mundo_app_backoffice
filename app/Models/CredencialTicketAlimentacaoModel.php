<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model da tabela auxiliar `credencial_ticket_alimentacao`.
 *
 * Uma linha aqui = ticket de alimentação retirado para aquela credencial.
 * Sem linha = ainda não retirado.
 */
class CredencialTicketAlimentacaoModel extends Model
{
    protected $table         = 'credencial_ticket_alimentacao';
    protected $primaryKey    = 'credencial_id';
    protected $returnType    = 'object';
    protected $useAutoIncrement = false;

    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'credencial_id',
        'retirado_em',
        'operador_id',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Marca a retirada — INSERT IGNORE preserva a data original quando já existe.
     */
    public function marcar(int $credencialId, ?int $operadorId = null): bool
    {
        $db = $this->db;
        $agora = date('Y-m-d H:i:s');

        $sql = 'INSERT IGNORE INTO ' . $this->table
            . ' (credencial_id, retirado_em, operador_id, created_at, updated_at)'
            . ' VALUES (?, ?, ?, ?, ?)';

        return (bool) $db->query($sql, [$credencialId, $agora, $operadorId, $agora, $agora]);
    }

    /**
     * Desmarca — remove a linha.
     */
    public function desmarcar(int $credencialId): bool
    {
        return (bool) $this->where('credencial_id', $credencialId)->delete();
    }

    /**
     * Retorna a linha atual (ou null).
     */
    public function porCredencial(int $credencialId)
    {
        return $this->where('credencial_id', $credencialId)->first();
    }
}
