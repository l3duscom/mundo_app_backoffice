<?php

namespace App\Models;

use CodeIgniter\Model;

class ChecklistModeloItemModel extends Model
{
    protected $table         = 'checklist_modelo_itens';
    protected $returnType    = 'App\Entities\ChecklistModeloItem';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'modelo_id',
        'titulo',
        'quantidade',
        'tipo',
        'categoria',
        'ordem',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function porModelo(int $modeloId): array
    {
        return $this->where('modelo_id', $modeloId)
            ->orderBy('ordem', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function removerPorModelo(int $modeloId): bool
    {
        return $this->where('modelo_id', $modeloId)->delete();
    }
}
