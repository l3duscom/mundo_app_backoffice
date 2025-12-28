<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class ArtistaContratacao extends Entity
{
    protected $attributes = [
        'id' => null,
        'artista_id' => null,
        'event_id' => null,
        'codigo' => null,
        'situacao' => 'rascunho',
        'data_apresentacao' => null,
        'horario_inicio' => null,
        'horario_fim' => null,
        'palco' => null,
        'valor_cache' => 0.00,
        'forma_pagamento' => null,
        'quantidade_parcelas' => 1,
        'observacoes' => null,
        'created_at' => null,
        'updated_at' => null,
    ];

    protected $casts = [
        'id' => 'integer',
        'artista_id' => 'integer',
        'event_id' => 'integer',
        'valor_cache' => 'float',
        'quantidade_parcelas' => 'integer',
    ];

    protected $dates = ['data_apresentacao', 'created_at', 'updated_at'];

    // Situações da contratação
    public const SITUACOES = [
        'rascunho' => ['label' => 'Rascunho', 'cor' => 'secondary', 'icone' => 'bx-edit'],
        'confirmado' => ['label' => 'Confirmado', 'cor' => 'success', 'icone' => 'bx-check-circle'],
        'realizado' => ['label' => 'Realizado', 'cor' => 'primary', 'icone' => 'bx-check-double'],
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
     * Retorna valor do cachê formatado
     */
    public function getValorCacheFormatado(): string
    {
        return 'R$ ' . number_format($this->attributes['valor_cache'], 2, ',', '.');
    }

    /**
     * Retorna data da apresentação formatada
     */
    public function getDataApresentacaoFormatada(): string
    {
        if (!$this->attributes['data_apresentacao']) return '-';
        $data = $this->attributes['data_apresentacao'];
        if (is_string($data)) $data = new \DateTime($data);
        return $data->format('d/m/Y');
    }

    /**
     * Retorna horário formatado
     */
    public function getHorarioFormatado(): string
    {
        $inicio = $this->attributes['horario_inicio'] ?? '';
        $fim = $this->attributes['horario_fim'] ?? '';
        
        if (!$inicio) return '-';
        
        $texto = substr($inicio, 0, 5);
        if ($fim) $texto .= ' - ' . substr($fim, 0, 5);
        
        return $texto;
    }

    /**
     * Verifica se pode editar
     */
    public function podeEditar(): bool
    {
        return in_array($this->attributes['situacao'], ['rascunho']);
    }

    /**
     * Verifica se pode cancelar
     */
    public function podeCancelar(): bool
    {
        return !in_array($this->attributes['situacao'], ['realizado', 'cancelado']);
    }
}
