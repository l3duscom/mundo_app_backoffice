<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class BannerEntity extends Entity
{
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function getBadgeStatus(): string
    {
        if ($this->deleted_at != null) {
            return '<span class="badge bg-danger">Excluído</span>';
        }
        return $this->ativo
            ? '<span class="badge bg-success">Ativo</span>'
            : '<span class="badge bg-warning text-dark">Inativo</span>';
    }
}
