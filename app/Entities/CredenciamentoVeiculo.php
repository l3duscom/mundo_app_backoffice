<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class CredenciamentoVeiculo extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'credenciamento_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $dates = ['created_at', 'updated_at'];

    /**
     * Retorna placa formatada
     */
    public function getPlacaFormatada(): string
    {
        $placa = strtoupper(str_replace(['-', ' '], '', $this->placa));
        if (strlen($placa) === 7) {
            return substr($placa, 0, 3) . '-' . substr($placa, 3);
        }
        return strtoupper($this->placa);
    }

    /**
     * Retorna descrição completa do veículo
     */
    public function getDescricaoCompleta(): string
    {
        return "{$this->marca} {$this->modelo} - {$this->cor} ({$this->getPlacaFormatada()})";
    }
}
