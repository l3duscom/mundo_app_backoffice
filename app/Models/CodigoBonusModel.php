<?php

namespace App\Models;

use CodeIgniter\Model;

class CodigoBonusModel extends Model
{
    protected $table = 'codigo_bonus';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'bonus_id',
        'codigo',
        'usado',
        'validade',
        'validade_lote',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Buscar códigos disponíveis (não usados)
     */
    public function getDisponiveis()
    {
        return $this->where('usado', 0)->findAll();
    }

    /**
     * Buscar códigos em ordem de criação (mais recentes primeiro)
     */
    public function listaCodigos()
    {
        return $this->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Marcar código como usado
     */
    public function marcarUsado(int $id, int $bonus_id)
    {
        return $this->update($id, [
            'usado' => 1,
            'bonus_id' => $bonus_id
        ]);
    }

    /**
     * Contar códigos disponíveis
     */
    public function contaDisponiveis(): int
    {
        return $this->where('usado', 0)->countAllResults();
    }

    /**
     * Contar códigos usados
     */
    public function contaUsados(): int
    {
        return $this->where('usado', 1)->countAllResults();
    }

    /**
     * Buscar próximo código disponível
     */
    public function getProximoDisponivel()
    {
        return $this->where('usado', 0)
                    ->orderBy('validade', 'ASC')
                    ->first();
    }
}
