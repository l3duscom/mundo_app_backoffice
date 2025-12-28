<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class OrcamentoParcela extends Entity
{
    protected $attributes = [
        'id' => null,
        'orcamento_id' => null,
        'numero_parcela' => 1,
        'valor' => 0.00,
        'data_vencimento' => null,
        'data_pagamento' => null,
        'status' => 'pendente',
        'forma_pagamento' => null,
        'comprovante_url' => null,
        'observacoes' => null,
        'created_at' => null,
        'updated_at' => null,
    ];

    protected $casts = [
        'id' => 'integer',
        'orcamento_id' => 'integer',
        'numero_parcela' => 'integer',
        'valor' => 'float',
    ];

    protected $dates = ['data_vencimento', 'data_pagamento', 'created_at', 'updated_at'];

    // Status da parcela
    public const STATUS = [
        'pendente' => ['label' => 'Pendente', 'cor' => 'warning', 'icone' => 'bx-time'],
        'pago' => ['label' => 'Pago', 'cor' => 'success', 'icone' => 'bx-check-circle'],
        'vencido' => ['label' => 'Vencido', 'cor' => 'danger', 'icone' => 'bx-error-circle'],
        'cancelado' => ['label' => 'Cancelado', 'cor' => 'secondary', 'icone' => 'bx-x-circle'],
    ];

    /**
     * Retorna badge do status
     */
    public function exibeStatus(): string
    {
        $status = self::STATUS[$this->attributes['status']] ?? self::STATUS['pendente'];
        return '<span class="badge bg-' . $status['cor'] . '"><i class="bx ' . $status['icone'] . ' me-1"></i>' . $status['label'] . '</span>';
    }

    /**
     * Retorna valor formatado
     */
    public function getValorFormatado(): string
    {
        return 'R$ ' . number_format($this->attributes['valor'], 2, ',', '.');
    }

    /**
     * Retorna data de vencimento formatada
     */
    public function getDataVencimentoFormatada(): string
    {
        if (!$this->attributes['data_vencimento']) {
            return '-';
        }
        
        $data = $this->attributes['data_vencimento'];
        if (is_string($data)) {
            $data = new \DateTime($data);
        }
        
        return $data->format('d/m/Y');
    }

    /**
     * Verifica se está vencida
     */
    public function estaVencida(): bool
    {
        if ($this->attributes['status'] !== 'pendente') {
            return false;
        }
        
        $hoje = new \DateTime();
        $vencimento = $this->attributes['data_vencimento'] ?? null;
        
        return $vencimento && $vencimento < $hoje;
    }
}
