<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class OrcamentoItem extends Entity
{
    protected $attributes = [
        'id' => null,
        'orcamento_id' => null,
        'descricao' => null,
        'quantidade' => 1.00,
        'valor_unitario' => 0.00,
        'valor_total' => 0.00,
        'observacoes' => null,
        'created_at' => null,
        'updated_at' => null,
    ];

    protected $casts = [
        'id' => 'integer',
        'orcamento_id' => 'integer',
        'quantidade' => 'float',
        'valor_unitario' => 'float',
        'valor_total' => 'float',
    ];

    protected $dates = ['created_at', 'updated_at'];

    /**
     * Calcula o valor total do item
     */
    public function calcularTotal(): float
    {
        $this->attributes['valor_total'] = $this->attributes['quantidade'] * $this->attributes['valor_unitario'];
        return $this->attributes['valor_total'];
    }

    /**
     * Valor unitário formatado
     */
    public function getValorUnitarioFormatado(): string
    {
        return 'R$ ' . number_format($this->attributes['valor_unitario'], 2, ',', '.');
    }

    /**
     * Valor total formatado
     */
    public function getValorTotalFormatado(): string
    {
        return 'R$ ' . number_format($this->attributes['valor_total'], 2, ',', '.');
    }
}
