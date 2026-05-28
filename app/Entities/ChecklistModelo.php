<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class ChecklistModelo extends Entity
{
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    public function getTipoLabel(): string
    {
        return $this->tipo === 'saida' ? 'Saída' : 'Entrada';
    }

    public function getBadgeTipo(): string
    {
        $classe = $this->tipo === 'saida' ? 'bg-warning text-dark' : 'bg-success';
        return '<span class="badge ' . $classe . '">' . esc($this->getTipoLabel()) . '</span>';
    }
}
