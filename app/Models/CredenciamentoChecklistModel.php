<?php

namespace App\Models;

use CodeIgniter\Model;

class CredenciamentoChecklistModel extends Model
{
    protected $table         = 'credenciamento_checklists';
    protected $returnType    = 'App\Entities\CredenciamentoChecklist';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'credenciamento_id',
        'modelo_id',
        'tipo',
        'status',
        'conferido_por',
        'conferido_em',
        'observacoes',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function buscaPorCredenciamentoTipo(int $credenciamentoId, string $tipo)
    {
        return $this->where('credenciamento_id', $credenciamentoId)
            ->where('tipo', $tipo)
            ->orderBy('id', 'DESC')
            ->first();
    }

    public function porCredenciamento(int $credenciamentoId): array
    {
        return $this->where('credenciamento_id', $credenciamentoId)->findAll();
    }
}
