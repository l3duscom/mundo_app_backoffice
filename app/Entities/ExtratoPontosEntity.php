<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class ExtratoPontosEntity extends Entity
{
    protected $datamap = [];
    
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    
    protected $casts = [
        'id'              => 'integer',
        'user_id'         => 'integer',
        'event_id'        => '?integer',
        'tipo'            => 'string',
        'pontos'          => 'integer',
        'saldo_anterior'  => 'integer',
        'saldo_atual'     => 'integer',
        'descricao'       => '?string',
        'referencia_tipo' => '?string',
        'referencia_id'   => '?integer',
        'atribuido_por'   => '?integer',
    ];

    protected $attributes = [
        'saldo_anterior' => 0,
        'saldo_atual'    => 0,
    ];
}

