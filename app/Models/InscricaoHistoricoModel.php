<?php

namespace App\Models;

use CodeIgniter\Model;

class InscricaoHistoricoModel extends Model
{
    protected $table = 'inscricoes_historico';
    protected $returnType = 'App\Entities\InscricaoHistorico';
    protected $allowedFields = [
        'inscricao_id',
        'user_id',
        'dados_anteriores',
        'dados_novos',
        'campos_alterados',
        'ip_address',
        'user_agent',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';
    protected $deletedField = '';

    /**
     * Recupera histórico de edições de uma inscrição
     */
    public function recuperaHistoricoPorInscricao(int $inscricao_id)
    {
        return $this->where('inscricao_id', $inscricao_id)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Conta quantas edições uma inscrição teve
     */
    public function contaEdicoes(int $inscricao_id): int
    {
        return $this->where('inscricao_id', $inscricao_id)->countAllResults();
    }
}
