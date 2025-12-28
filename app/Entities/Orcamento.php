<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Orcamento extends Entity
{
    protected $attributes = [
        'id' => null,
        'event_id' => null,
        'fornecedor_id' => null,
        'codigo' => null,
        'titulo' => null,
        'descricao' => null,
        'situacao' => 'rascunho',
        'valor_total' => 0.00,
        'valor_desconto' => 0.00,
        'valor_final' => 0.00,
        'forma_pagamento' => null,
        'quantidade_parcelas' => 1,
        'data_validade' => null,
        'data_aprovacao' => null,
        'observacoes' => null,
        'created_at' => null,
        'updated_at' => null,
        'deleted_at' => null,
    ];

    protected $casts = [
        'id' => 'integer',
        'event_id' => 'integer',
        'fornecedor_id' => 'integer',
        'valor_total' => 'float',
        'valor_desconto' => 'float',
        'valor_final' => 'float',
        'quantidade_parcelas' => 'integer',
    ];

    protected $dates = ['data_validade', 'data_aprovacao', 'created_at', 'updated_at', 'deleted_at'];

    // Situações do orçamento
    public const SITUACOES = [
        'rascunho' => ['label' => 'Rascunho', 'cor' => 'secondary', 'icone' => 'bx-edit'],
        'enviado' => ['label' => 'Enviado', 'cor' => 'info', 'icone' => 'bx-send'],
        'aprovado' => ['label' => 'Aprovado', 'cor' => 'success', 'icone' => 'bx-check-circle'],
        'em_andamento' => ['label' => 'Em Andamento', 'cor' => 'warning', 'icone' => 'bx-loader'],
        'concluido' => ['label' => 'Concluído', 'cor' => 'primary', 'icone' => 'bx-check-double'],
        'cancelado' => ['label' => 'Cancelado', 'cor' => 'danger', 'icone' => 'bx-x-circle'],
    ];

    /**
     * Retorna badge da situação
     */
    public function exibeSituacao(): string
    {
        $situacao = self::SITUACOES[$this->attributes['situacao']] ?? self::SITUACOES['rascunho'];
        return '<span class="badge bg-' . $situacao['cor'] . '"><i class="bx ' . $situacao['icone'] . ' me-1"></i>' . $situacao['label'] . '</span>';
    }

    /**
     * Retorna label da situação
     */
    public function getSituacaoLabel(): string
    {
        return self::SITUACOES[$this->attributes['situacao']]['label'] ?? 'Rascunho';
    }

    /**
     * Valor total formatado
     */
    public function getValorTotalFormatado(): string
    {
        return 'R$ ' . number_format($this->attributes['valor_total'], 2, ',', '.');
    }

    /**
     * Valor final formatado
     */
    public function getValorFinalFormatado(): string
    {
        return 'R$ ' . number_format($this->attributes['valor_final'], 2, ',', '.');
    }

    /**
     * Valor desconto formatado
     */
    public function getValorDescontoFormatado(): string
    {
        return 'R$ ' . number_format($this->attributes['valor_desconto'], 2, ',', '.');
    }

    /**
     * Verifica se pode ser editado
     */
    public function podeEditar(): bool
    {
        return in_array($this->attributes['situacao'], ['rascunho', 'enviado']);
    }

    /**
     * Verifica se pode ser cancelado
     */
    public function podeCancelar(): bool
    {
        return !in_array($this->attributes['situacao'], ['concluido', 'cancelado']);
    }
}
