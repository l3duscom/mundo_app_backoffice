<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class PedidoOrderBump extends Entity
{
    protected $attributes = [
        'id' => null,
        'pedido_id' => null,
        'order_bump_id' => null,
        'quantidade' => 1,
        'preco_unitario' => 0.00,
        'usado' => 0,
        'usado_em' => null,
        'created_at' => null,
        'updated_at' => null,
        'deleted_at' => null,
    ];

    protected $casts = [
        'id' => 'integer',
        'pedido_id' => 'integer',
        'order_bump_id' => 'integer',
        'quantidade' => 'integer',
        'preco_unitario' => 'float',
        'usado' => 'boolean',
    ];

    protected $dates = ['usado_em', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * Retorna o preço unitário formatado em R$
     */
    public function getPrecoFormatado(): string
    {
        return 'R$ ' . number_format($this->attributes['preco_unitario'] ?? 0, 2, ',', '.');
    }

    /**
     * Retorna o preço total (quantidade * preço unitário) formatado
     */
    public function getTotalFormatado(): string
    {
        $total = ($this->attributes['quantidade'] ?? 1) * ($this->attributes['preco_unitario'] ?? 0);
        return 'R$ ' . number_format($total, 2, ',', '.');
    }

    /**
     * Retorna badge de status usado/não usado
     */
    public function exibeStatusUsado(): string
    {
        if ($this->attributes['usado']) {
            $dataUsado = $this->attributes['usado_em'] ? date('d/m/Y H:i', strtotime($this->attributes['usado_em'])) : '';
            return '<span class="badge bg-success"><i class="bx bx-check-circle me-1"></i>Usado' . ($dataUsado ? ' em ' . $dataUsado : '') . '</span>';
        }
        return '<span class="badge bg-warning"><i class="bx bx-time me-1"></i>Não utilizado</span>';
    }

    /**
     * Verifica se já foi usado
     */
    public function foiUsado(): bool
    {
        return (bool) ($this->attributes['usado'] ?? false);
    }
}
