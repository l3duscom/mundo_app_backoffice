<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class UsuarioConquistaEntity extends Entity
{
    protected $datamap = [];
    
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    
    protected $casts = [
        'id'             => 'integer',
        'conquista_id'   => 'integer',
        'event_id'       => 'integer',
        'user_id'        => 'integer',
        'pontos'         => 'integer',
        'admin'          => 'integer',
        'status'         => 'string',
        'atribuido_por'  => '?integer',
    ];

    protected $attributes = [
        'pontos' => 0,
        'admin'  => 0,
        'status' => 'ATIVA',
    ];
}

