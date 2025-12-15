<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Credenciamento extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'contrato_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * Retorna badge de status formatado
     */
    public function getBadgeStatus(): string
    {
        $badges = [
            'pendente' => '<span class="badge bg-secondary">Pendente</span>',
            'em_andamento' => '<span class="badge bg-warning text-dark">Em Andamento</span>',
            'completo' => '<span class="badge bg-info">Completo</span>',
            'aprovado' => '<span class="badge bg-success">Aprovado</span>',
            'bloqueado' => '<span class="badge bg-danger">Bloqueado</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">' . ucfirst($this->status) . '</span>';
    }

    /**
     * Retorna label do status
     */
    public function getStatusLabel(): string
    {
        $labels = [
            'pendente' => 'Pendente',
            'em_andamento' => 'Em Andamento',
            'completo' => 'Completo',
            'aprovado' => 'Aprovado',
            'bloqueado' => 'Bloqueado',
        ];

        return $labels[$this->status] ?? ucfirst($this->status);
    }
}
