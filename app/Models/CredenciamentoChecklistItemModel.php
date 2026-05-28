<?php

namespace App\Models;

use CodeIgniter\Model;

class CredenciamentoChecklistItemModel extends Model
{
    protected $table         = 'credenciamento_checklist_itens';
    protected $returnType    = 'App\Entities\CredenciamentoChecklistItem';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'checklist_id',
        'titulo',
        'checked',
        'quantidade',
        'tipo',
        'categoria',
        'ordem',
        'observacao',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function porChecklist(int $checklistId): array
    {
        return $this->where('checklist_id', $checklistId)
            ->orderBy('ordem', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }
}
