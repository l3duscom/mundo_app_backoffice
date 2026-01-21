<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Plano extends Entity
{
    protected $attributes = [
        'id' => null,
        'nome' => null,
        'slug' => null,
        'descricao' => null,
        'preco' => 0.00,
        'ciclo' => 'MONTHLY',
        'beneficios' => null,
        'ativo' => 1,
        'created_at' => null,
        'updated_at' => null,
        'deleted_at' => null,
    ];

    protected $casts = [
        'id' => 'integer',
        'preco' => 'float',
        'ativo' => 'boolean',
    ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * Retorna o preço formatado em R$
     */
    public function getPrecoFormatado(): string
    {
        return 'R$ ' . number_format($this->attributes['preco'] ?? 0, 2, ',', '.');
    }

    /**
     * Retorna badge de status HTML
     */
    public function exibeStatus(): string
    {
        if ($this->attributes['ativo']) {
            return '<span class="badge bg-success">Ativo</span>';
        }
        return '<span class="badge bg-secondary">Inativo</span>';
    }

    /**
     * Retorna badge do ciclo de cobrança
     */
    public function exibeCiclo(): string
    {
        $ciclos = [
            'MONTHLY' => ['label' => 'Mensal', 'class' => 'bg-primary'],
            'YEARLY' => ['label' => 'Anual', 'class' => 'bg-info'],
        ];

        $ciclo = $this->attributes['ciclo'] ?? 'MONTHLY';
        $config = $ciclos[$ciclo] ?? $ciclos['MONTHLY'];

        return '<span class="badge ' . $config['class'] . '">' . $config['label'] . '</span>';
    }

    /**
     * Retorna texto do ciclo
     */
    public function getCicloTexto(): string
    {
        return $this->attributes['ciclo'] === 'YEARLY' ? 'Anual' : 'Mensal';
    }

    /**
     * Retorna array de benefícios
     */
    public function getBeneficios(): array
    {
        $beneficios = $this->attributes['beneficios'] ?? null;
        
        if (empty($beneficios)) {
            return [];
        }

        if (is_string($beneficios)) {
            return json_decode($beneficios, true) ?? [];
        }

        return (array) $beneficios;
    }

    /**
     * Define benefícios a partir de array
     */
    public function setBeneficios($beneficios): self
    {
        if (is_array($beneficios)) {
            $this->attributes['beneficios'] = json_encode($beneficios, JSON_UNESCAPED_UNICODE);
        } else {
            $this->attributes['beneficios'] = $beneficios;
        }
        return $this;
    }

    /**
     * Verifica se o plano está ativo
     */
    public function isAtivo(): bool
    {
        return (bool) ($this->attributes['ativo'] ?? false);
    }
}
