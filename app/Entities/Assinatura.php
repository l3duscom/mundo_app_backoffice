<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Assinatura extends Entity
{
    protected $attributes = [
        'id' => null,
        'usuario_id' => null,
        'plano_id' => null,
        'asaas_subscription_id' => null,
        'asaas_customer_id' => null,
        'status' => 'PENDING',
        'data_inicio' => null,
        'data_fim' => null,
        'proximo_vencimento' => null,
        'valor_pago' => 0.00,
        'forma_pagamento' => null,
        'created_at' => null,
        'updated_at' => null,
        'deleted_at' => null,
    ];

    protected $casts = [
        'id' => 'integer',
        'usuario_id' => 'integer',
        'plano_id' => 'integer',
        'valor_pago' => 'float',
    ];

    protected $dates = ['data_inicio', 'data_fim', 'proximo_vencimento', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * Retorna badge de status HTML
     */
    public function exibeStatus(): string
    {
        $statusConfig = [
            'PENDING' => ['label' => 'Pendente', 'class' => 'bg-warning text-dark'],
            'ACTIVE' => ['label' => 'Ativa', 'class' => 'bg-success'],
            'OVERDUE' => ['label' => 'Atrasada', 'class' => 'bg-danger'],
            'CANCELLED' => ['label' => 'Cancelada', 'class' => 'bg-secondary'],
            'EXPIRED' => ['label' => 'Expirada', 'class' => 'bg-dark'],
        ];

        $status = $this->attributes['status'] ?? 'PENDING';
        $config = $statusConfig[$status] ?? $statusConfig['PENDING'];

        return '<span class="badge ' . $config['class'] . '">' . $config['label'] . '</span>';
    }

    /**
     * Retorna o texto do status
     */
    public function getStatusTexto(): string
    {
        $statusTextos = [
            'PENDING' => 'Pendente',
            'ACTIVE' => 'Ativa',
            'OVERDUE' => 'Pagamento Atrasado',
            'CANCELLED' => 'Cancelada',
            'EXPIRED' => 'Expirada',
        ];

        return $statusTextos[$this->attributes['status']] ?? 'Desconhecido';
    }

    /**
     * Retorna o valor formatado em R$
     */
    public function getValorFormatado(): string
    {
        return 'R$ ' . number_format($this->attributes['valor_pago'] ?? 0, 2, ',', '.');
    }

    /**
     * Verifica se a assinatura está ativa
     */
    public function isAtiva(): bool
    {
        return $this->attributes['status'] === 'ACTIVE';
    }

    /**
     * Verifica se a assinatura está expirada
     */
    public function isExpirada(): bool
    {
        if ($this->attributes['status'] === 'EXPIRED') {
            return true;
        }

        if (!empty($this->attributes['data_fim'])) {
            $dataFim = $this->attributes['data_fim'];
            if (is_string($dataFim)) {
                $dataFim = new \DateTime($dataFim);
            }
            return $dataFim < new \DateTime();
        }

        return false;
    }

    /**
     * Verifica se está cancelada
     */
    public function isCancelada(): bool
    {
        return $this->attributes['status'] === 'CANCELLED';
    }

    /**
     * Verifica se está pendente
     */
    public function isPendente(): bool
    {
        return $this->attributes['status'] === 'PENDING';
    }

    /**
     * Verifica se está com pagamento atrasado
     */
    public function isAtrasada(): bool
    {
        return $this->attributes['status'] === 'OVERDUE';
    }

    /**
     * Retorna data de início formatada
     */
    public function getDataInicioFormatada(): string
    {
        if (empty($this->attributes['data_inicio'])) {
            return '-';
        }
        
        $data = $this->attributes['data_inicio'];
        if (is_string($data)) {
            $data = new \DateTime($data);
        }
        
        return $data->format('d/m/Y');
    }

    /**
     * Retorna data de fim formatada
     */
    public function getDataFimFormatada(): string
    {
        if (empty($this->attributes['data_fim'])) {
            return '-';
        }
        
        $data = $this->attributes['data_fim'];
        if (is_string($data)) {
            $data = new \DateTime($data);
        }
        
        return $data->format('d/m/Y');
    }

    /**
     * Retorna próximo vencimento formatado
     */
    public function getProximoVencimentoFormatado(): string
    {
        if (empty($this->attributes['proximo_vencimento'])) {
            return '-';
        }
        
        $data = $this->attributes['proximo_vencimento'];
        if (is_string($data)) {
            $data = new \DateTime($data);
        }
        
        return $data->format('d/m/Y');
    }

    /**
     * Retorna badge da forma de pagamento
     */
    public function exibeFormaPagamento(): string
    {
        $formas = [
            'PIX' => ['label' => 'PIX', 'class' => 'bg-success', 'icon' => 'bx-qr'],
            'CREDIT_CARD' => ['label' => 'Cartão', 'class' => 'bg-primary', 'icon' => 'bx-credit-card'],
            'BOLETO' => ['label' => 'Boleto', 'class' => 'bg-info', 'icon' => 'bx-barcode'],
        ];

        $forma = $this->attributes['forma_pagamento'] ?? null;
        
        if (!$forma || !isset($formas[$forma])) {
            return '<span class="text-muted">-</span>';
        }

        $config = $formas[$forma];
        return '<span class="badge ' . $config['class'] . '"><i class="bx ' . $config['icon'] . ' me-1"></i>' . $config['label'] . '</span>';
    }
}
