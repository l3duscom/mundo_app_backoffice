<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Espaco extends Entity
{
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'event_id' => 'integer',
        'contrato_item_id' => '?integer',
    ];

    /**
     * Retorna badge de status formatado
     */
    public function getBadgeStatus(): string
    {
        $badges = [
            'livre' => '<span class="badge bg-success">Livre</span>',
            'reservado' => '<span class="badge bg-warning text-dark">Reservado</span>',
            'bloqueado' => '<span class="badge bg-secondary">Bloqueado</span>',
        ];

        return $badges[$this->status] ?? $this->status;
    }

    /**
     * Verifica se espaço está disponível para reserva
     */
    public function estaDisponivel(): bool
    {
        return $this->status === 'livre';
    }

    /**
     * Verifica se espaço está reservado
     */
    public function estaReservado(): bool
    {
        return $this->status === 'reservado';
    }
}
