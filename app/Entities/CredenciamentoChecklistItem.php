<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class CredenciamentoChecklistItem extends Entity
{
    protected $dates = ['created_at', 'updated_at'];
    protected $casts = [
        'checked' => 'boolean',
    ];
}
