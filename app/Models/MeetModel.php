<?php

namespace App\Models;

use CodeIgniter\Model;

class MeetModel extends Model
{
    protected $table = 'meet';
    protected $returnType = 'App\Entities\Meet';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'event_id',
        'artista',
        'dia',
        'data_meet',
        'hora_inicial',
        'hora_final',
        'quantidade',
        'tipo',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';




    // Validation
    protected $validationRules = [
        'event_id'   => 'required|integer',
        'artista'    => 'required|min_length[2]|max_length[255]',
        'dia'        => 'permit_empty|max_length[50]',
        'tipo'       => 'permit_empty|max_length[50]',
        'quantidade' => 'permit_empty|integer',
    ];

    protected $validationMessages = [
        'event_id' => [
            'required' => 'O evento é obrigatório.',
        ],
        'artista' => [
            'required'   => 'O nome do artista é obrigatório.',
            'min_length' => 'O nome do artista precisa ter pelo menos 2 caracteres.',
        ],
    ];

    public function getByEvento(int $eventId): array
    {
        return $this->where('event_id', $eventId)
            ->orderBy('data_meet', 'ASC')
            ->orderBy('hora_inicial', 'ASC')
            ->orderBy('artista', 'ASC')
            ->findAll();
    }

    public function recuperaMeetForDay(int $event_id)
    {

        $atributos = [
            'meet.id',
            'meet.event_id',
            'artista',
            'meet.dia',
            'meet.data_meet',
            'meet.hora_inicial',
            'meet.hora_final',
            'meet.quantidade',
            'meet.tipo',
            'meet.created_at',

        ];

        $retorno = $this->select($atributos)
            ->join('eventos', 'eventos.id = meet.event_id')
            ->where('meet.event_id', $event_id)
            ->where('meet.quantidade >', 0)
            ->orderBy('meet.hora_inicial', 'ASC')
            ->findAll();

        return $retorno;
    }

    public function recuperaMeetVipForDay(int $event_id, string $tipo)
    {

        $atributos = [
            'meet.id',
            'meet.event_id',
            'artista',
            'meet.dia',
            'meet.data_meet',
            'meet.hora_inicial',
            'meet.hora_final',
            'meet.quantidade',
            'meet.tipo',
            'meet.created_at',

        ];

        $retorno = $this->select($atributos)
            ->join('eventos', 'eventos.id = meet.event_id')
            ->where('meet.event_id', $event_id)
            ->where('meet.tipo', $tipo)
            ->where('meet.quantidade >', 0)
            ->orderBy('meet.hora_inicial', 'ASC')
            ->findAll();

        return $retorno;
    }

    public function recuperaMeetEpicForDay(int $event_id, string $tipo)
    {

        $atributos = [
            'meet.id',
            'meet.event_id',
            'artista',
            'meet.dia',
            'meet.data_meet',
            'meet.hora_inicial',
            'meet.hora_final',
            'meet.quantidade',
            'meet.tipo',
            'meet.created_at',

        ];

        $retorno = $this->select($atributos)
            ->join('eventos', 'eventos.id = meet.event_id')
            ->where('meet.event_id', $event_id)
            ->where('meet.tipo', $tipo)
            ->where('meet.quantidade >', 0)
            ->orderBy('meet.hora_inicial', 'ASC')
            ->findAll();

        return $retorno;
    }

    public function checkMeetAvailable(int $meet_id)
    {

        $atributos = [
            'meet.id',
            'meet.event_id',
            'meet.dia',
            'meet.data_meet',
            'meet.hora_inicial',
            'meet.hora_final',
            'meet.quantidade',
            'meet.tipo',
            'meet.created_at',
            'eventos.name'
        ];

        $retorno = $this->select($atributos)
            ->where('meet.quantidade >= 0')
            ->where('meet.event_id', $meet_id)
            ->find();

        return $retorno;
    }
}
