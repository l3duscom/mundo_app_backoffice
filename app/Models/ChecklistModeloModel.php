<?php

namespace App\Models;

use CodeIgniter\Model;

class ChecklistModeloModel extends Model
{
    protected $table         = 'checklist_modelos';
    protected $returnType    = 'App\Entities\ChecklistModelo';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'event_id',
        'tipo',
        'nome',
        'ativo',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'event_id' => 'required|integer',
        'tipo'     => 'required|in_list[entrada,saida]',
        'nome'     => 'permit_empty|max_length[255]',
    ];

    protected $validationMessages = [
        'event_id' => ['required' => 'O evento é obrigatório.'],
        'tipo'     => [
            'required' => 'O tipo é obrigatório.',
            'in_list'  => 'O tipo deve ser entrada ou saída.',
        ],
    ];

    public function porEvento(int $eventId): array
    {
        return $this->where('event_id', $eventId)
            ->orderBy('tipo', 'ASC')
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    public function buscaAtivoPorEventoTipo(int $eventId, string $tipo)
    {
        return $this->where('event_id', $eventId)
            ->where('tipo', $tipo)
            ->where('ativo', 1)
            ->orderBy('id', 'DESC')
            ->first();
    }
}
