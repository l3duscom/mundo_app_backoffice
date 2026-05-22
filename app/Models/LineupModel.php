<?php

namespace App\Models;

use CodeIgniter\Model;

class LineupModel extends Model
{
    protected $table         = 'lineup';
    protected $returnType    = 'App\Entities\LineupEntity';
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'event_id',
        'nome',
        'dia',
        'tipo',
        'descricao',
        'imagem',
        'ordem',
        'ativo',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'event_id' => 'required|integer',
        'nome'     => 'required|min_length[2]|max_length[255]',
        'dia'      => 'permit_empty',
        'tipo'     => 'permit_empty|max_length[60]',
        'ativo'    => 'in_list[0,1]',
    ];

    protected $validationMessages = [
        'event_id' => [
            'required' => 'O evento é obrigatório.',
        ],
        'nome' => [
            'required'   => 'O nome é obrigatório.',
            'min_length' => 'O nome precisa ter pelo menos 2 caracteres.',
        ],
    ];

    public function getByEvento(int $eventId): array
    {
        return $this->where('event_id', $eventId)
            ->orderBy('dia', 'ASC')
            ->orderBy('ordem', 'ASC')
            ->orderBy('nome', 'ASC')
            ->findAll();
    }
}
