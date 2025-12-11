<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class ContratoParcela extends Entity
{
    protected $dates = [
        'created_at',
        'updated_at',
        'synced_at',
    ];

    /**
     * Retorna valor formatado
     */
    public function getValorFormatado(): string
    {
        return 'R$ ' . number_format($this->valor ?? 0, 2, ',', '.');
    }

    /**
     * Retorna valor líquido formatado
     */
    public function getValorLiquidoFormatado(): string
    {
        return 'R$ ' . number_format($this->valor_liquido ?? $this->valor ?? 0, 2, ',', '.');
    }

    /**
     * Retorna data de vencimento formatada
     */
    public function getVencimentoFormatado(): string
    {
        if (empty($this->data_vencimento)) {
            return 'N/A';
        }
        return date('d/m/Y', strtotime($this->data_vencimento));
    }

    /**
     * Retorna data de pagamento formatada
     */
    public function getPagamentoFormatado(): string
    {
        if (empty($this->data_pagamento)) {
            return '-';
        }
        return date('d/m/Y', strtotime($this->data_pagamento));
    }

    /**
     * Verifica se está vencida
     */
    public function isVencida(): bool
    {
        if ($this->status_local === 'pago') {
            return false;
        }
        
        return !empty($this->data_vencimento) && strtotime($this->data_vencimento) < time();
    }

    /**
     * Verifica se está paga
     */
    public function isPaga(): bool
    {
        return $this->status_local === 'pago';
    }

    /**
     * Retorna badge de status
     */
    public function getBadgeStatus(): string
    {
        $badges = [
            'pendente' => '<span class="badge bg-warning"><i class="bx bx-time me-1"></i>Pendente</span>',
            'pago' => '<span class="badge bg-success"><i class="bx bx-check me-1"></i>Pago</span>',
            'vencido' => '<span class="badge bg-danger"><i class="bx bx-error me-1"></i>Vencido</span>',
            'cancelado' => '<span class="badge bg-secondary"><i class="bx bx-x me-1"></i>Cancelado</span>',
        ];

        // Se está pendente mas vencida, mostra como vencido
        if ($this->status_local === 'pendente' && $this->isVencida()) {
            return $badges['vencido'];
        }

        return $badges[$this->status_local] ?? $badges['pendente'];
    }

    /**
     * Retorna badge de status do Asaas
     */
    public function getBadgeStatusAsaas(): string
    {
        $badges = [
            'PENDING' => '<span class="badge bg-warning">Pendente</span>',
            'AWAITING_RISK_ANALYSIS' => '<span class="badge bg-info">Em análise</span>',
            'RECEIVED' => '<span class="badge bg-success">Recebido</span>',
            'CONFIRMED' => '<span class="badge bg-success">Confirmado</span>',
            'RECEIVED_IN_CASH' => '<span class="badge bg-success">Dinheiro</span>',
            'OVERDUE' => '<span class="badge bg-danger">Vencido</span>',
            'REFUNDED' => '<span class="badge bg-secondary">Estornado</span>',
            'REFUND_REQUESTED' => '<span class="badge bg-warning">Estorno solicitado</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">' . $this->status . '</span>';
    }
}

