<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class MetaVenda extends Entity
{
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts = [
        'meta_total' => 'float',
        'ativo'      => 'boolean',
    ];

    public function getTipoLabel(): string
    {
        return $this->tipo === 'comercial' ? 'Comercial' : 'Ingressos';
    }

    public function getBadgeTipo(): string
    {
        $classe = $this->tipo === 'comercial' ? 'bg-primary' : 'bg-success';
        return '<span class="badge ' . $classe . '">' . esc($this->getTipoLabel()) . '</span>';
    }
}
