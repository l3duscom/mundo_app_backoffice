<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Artista extends Entity
{
    protected $attributes = [
        'id' => null,
        'nome_artistico' => null,
        'genero_musical' => null,
        'biografia' => null,
        'rider_tecnico' => null,
        'foto' => null,
        'nome_completo' => null,
        'cpf' => null,
        'rg' => null,
        'data_nascimento' => null,
        'nacionalidade' => 'Brasileira',
        'passaporte' => null,
        'passaporte_validade' => null,
        'email' => null,
        'telefone' => null,
        'observacoes' => null,
        'ativo' => 1,
        'created_at' => null,
        'updated_at' => null,
        'deleted_at' => null,
    ];

    protected $casts = [
        'id' => 'integer',
        'ativo' => 'boolean',
    ];

    protected $dates = ['data_nascimento', 'passaporte_validade', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * Retorna CPF formatado
     */
    public function getCpfFormatado(): string
    {
        $cpf = preg_replace('/\D/', '', $this->attributes['cpf'] ?? '');
        if (strlen($cpf) !== 11) return $this->attributes['cpf'] ?? '';
        return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
    }

    /**
     * Retorna badge de status
     */
    public function exibeStatus(): string
    {
        if ($this->attributes['ativo']) {
            return '<span class="badge bg-success"><i class="bx bx-check me-1"></i>Ativo</span>';
        }
        return '<span class="badge bg-secondary"><i class="bx bx-x me-1"></i>Inativo</span>';
    }

    /**
     * Verifica se é estrangeiro
     */
    public function isEstrangeiro(): bool
    {
        return !empty($this->attributes['passaporte']) || 
               ($this->attributes['nacionalidade'] && $this->attributes['nacionalidade'] !== 'Brasileira');
    }

    /**
     * Retorna data de nascimento formatada
     */
    public function getDataNascimentoFormatada(): string
    {
        if (!$this->attributes['data_nascimento']) return '-';
        $data = $this->attributes['data_nascimento'];
        if (is_string($data)) $data = new \DateTime($data);
        return $data->format('d/m/Y');
    }

    /**
     * Retorna passaporte com validade
     */
    public function getPassaporteInfo(): string
    {
        if (empty($this->attributes['passaporte'])) return '-';
        
        $info = $this->attributes['passaporte'];
        if ($this->attributes['passaporte_validade']) {
            $validade = $this->attributes['passaporte_validade'];
            if (is_string($validade)) $validade = new \DateTime($validade);
            $info .= ' (Val: ' . $validade->format('d/m/Y') . ')';
        }
        return $info;
    }
}
