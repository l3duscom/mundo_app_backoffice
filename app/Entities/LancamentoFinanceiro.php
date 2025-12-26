<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class LancamentoFinanceiro extends Entity
{
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'data_lancamento',
        'data_pagamento',
    ];

    protected $casts = [
        'valor' => 'float',
        'event_id' => '?integer',
        'referencia_id' => '?integer',
    ];

    /**
     * Retorna badge do tipo
     */
    public function getBadgeTipo(): string
    {
        $badges = [
            'ENTRADA' => '<span class="badge bg-success"><i class="bx bx-trending-up me-1"></i>Entrada</span>',
            'SAIDA' => '<span class="badge bg-danger"><i class="bx bx-trending-down me-1"></i>Saída</span>',
        ];

        return $badges[$this->tipo] ?? '<span class="badge bg-secondary">' . $this->tipo . '</span>';
    }

    /**
     * Retorna badge do status
     */
    public function getBadgeStatus(): string
    {
        $badges = [
            'pendente' => '<span class="badge bg-warning text-dark">Pendente</span>',
            'pago' => '<span class="badge bg-success">Pago</span>',
            'cancelado' => '<span class="badge bg-secondary">Cancelado</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">' . $this->status . '</span>';
    }

    /**
     * Retorna badge da origem
     */
    public function getBadgeOrigem(): string
    {
        $badges = [
            'MANUAL' => '<span class="badge bg-secondary">Manual</span>',
            'CONTRATO' => '<span class="badge bg-info">Contrato</span>',
            'INGRESSO' => '<span class="badge bg-primary">Ingresso</span>',
            'PDV' => '<span class="badge bg-purple" style="background:#6f42c1 !important;">PDV</span>',
            'CONTA_PAGAR' => '<span class="badge bg-danger">Conta a Pagar</span>',
        ];

        return $badges[$this->origem] ?? '<span class="badge bg-secondary">' . $this->origem . '</span>';
    }

    /**
     * Retorna valor formatado
     */
    public function getValorFormatado(): string
    {
        $prefixo = $this->tipo === 'SAIDA' ? '-' : '+';
        $cor = $this->tipo === 'SAIDA' ? 'text-danger' : 'text-success';
        
        return '<span class="' . $cor . ' fw-bold">' . $prefixo . ' R$ ' . number_format($this->valor, 2, ',', '.') . '</span>';
    }

    /**
     * Retorna data formatada
     */
    public function getDataLancamentoFormatada(): string
    {
        return $this->data_lancamento ? $this->data_lancamento->format('d/m/Y') : '-';
    }

    /**
     * Retorna data de pagamento formatada
     */
    public function getDataPagamentoFormatada(): string
    {
        return $this->data_pagamento ? $this->data_pagamento->format('d/m/Y') : '-';
    }
}
