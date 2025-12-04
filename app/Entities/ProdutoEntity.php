<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class ProdutoEntity extends Entity
{
    protected $datamap = [];
    
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    
    protected $casts = [
        'id'        => 'integer',
        'event_id'  => 'integer',
        'imagem'    => '?string',
        'categoria' => 'string',
        'nome'      => 'string',
        'preco'     => 'float',
        'pontos'    => 'integer',
    ];

    protected $attributes = [
        'preco'  => 0.00,
        'pontos' => 0,
    ];
}

