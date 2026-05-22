<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class LineupEntity extends Entity
{
    protected $dates = [
        'dia',
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

    public function getDiaFormatado(): string
    {
        if (!$this->dia) {
            return '-';
        }
        return date('d/m/Y', strtotime((string) $this->dia));
    }
}
