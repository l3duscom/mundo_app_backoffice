<?php

namespace App\Models;

use CodeIgniter\Model;

class CronogramaModel extends Model
{
    protected $table                = 'cronograma';
    protected $returnType           = 'App\Entities\CronogramaEntity';
    protected $useSoftDeletes       = true;
    protected $allowedFields        = [
        'event_id',
        'name',
        'ativo',
    ];

    // Dates
    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';
    protected $deletedField         = 'deleted_at';

    // Callbacks
    protected $allowCallbacks       = true;

    // Validation
    protected $validationRules      = [
        'event_id'  => 'required|integer',
        'name'      => 'required|min_length[3]|max_length[255]',
        'ativo'     => 'in_list[0,1]',
    ];

    protected $validationMessages   = [
        'event_id' => [
            'required' => 'O campo Evento é obrigatório.',
            'integer'  => 'O campo Evento deve ser um número inteiro válido.',
        ],
        'name' => [
            'required'   => 'O campo Nome é obrigatório.',
            'min_length' => 'O campo Nome precisa ter pelo menos 3 caracteres.',
            'max_length' => 'O campo Nome não pode ser maior que 255 caracteres.',
        ],
        'ativo' => [
            'in_list' => 'O campo Ativo deve ser 0 ou 1.',
        ],
    ];

    /**
     * Recupera todos os cronogramas de um evento específico
     *
     * @param integer $event_id
     * @return array|null
     */
    public function getCronogramasByEvento(int $event_id)
    {
        return $this->where('event_id', $event_id)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Recupera apenas os cronogramas ativos de um evento
     *
     * @param integer $event_id
     * @return array|null
     */
    public function getCronogramasAtivosByEvento(int $event_id)
    {
        return $this->where('event_id', $event_id)
                    ->where('ativo', true)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Verifica se existe cronograma para um evento
     *
     * @param integer $event_id
     * @return boolean
     */
    public function existeCronogramaParaEvento(int $event_id): bool
    {
        return $this->where('event_id', $event_id)->countAllResults() > 0;
    }
}

