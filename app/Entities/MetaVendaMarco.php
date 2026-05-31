<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class MetaVendaMarco extends Entity
{
    protected $dates = ['created_at', 'updated_at'];
    protected $casts = [
        'faturamento_acumulado_esperado' => 'float',
    ];
}
