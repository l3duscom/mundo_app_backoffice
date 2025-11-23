<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class ConquistaEntity extends Entity
{
    protected $datamap = [];
    
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    
    protected $casts = [
        'id'              => 'integer',
        'event_id'        => 'integer',
        'codigo'          => 'string',
        'nome_conquista'  => 'string',
        'descricao'       => '?string',
        'pontos'          => 'integer',
        'nivel'           => 'string',
        'status'          => 'string',
    ];

    protected $attributes = [
        'pontos' => 0,
        'status' => 'ATIVA',
    ];
}

