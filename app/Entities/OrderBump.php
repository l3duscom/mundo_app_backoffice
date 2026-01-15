<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class OrderBump extends Entity
{
    protected $attributes = [
        'id' => null,
        'event_id' => null,
        'ticket_id' => null,
        'nome' => null,
        'descricao' => null,
        'preco' => 0.00,
        'imagem' => null,
        'tipo' => 'produto',
        'estoque' => null,
        'max_por_pedido' => 1,
        'ordem' => 0,
        'ativo' => 1,
        'created_at' => null,
        'updated_at' => null,
        'deleted_at' => null,
    ];

    protected $casts = [
        'id' => 'integer',
        'event_id' => 'integer',
        'ticket_id' => '?integer',
        'preco' => 'float',
        'estoque' => '?integer',
        'max_por_pedido' => 'integer',
        'ordem' => 'integer',
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
     * Retorna badge do tipo
     */
    public function exibeTipo(): string
    {
        $tipos = [
            'produto' => ['label' => 'Produto', 'class' => 'bg-primary'],
            'servico' => ['label' => 'Serviço', 'class' => 'bg-info'],
            'ingresso_adicional' => ['label' => 'Ingresso Adicional', 'class' => 'bg-warning'],
        ];

        $tipo = $this->attributes['tipo'] ?? 'produto';
        $config = $tipos[$tipo] ?? $tipos['produto'];

        return '<span class="badge ' . $config['class'] . '">' . $config['label'] . '</span>';
    }

    /**
     * Verifica se tem estoque disponível
     */
    public function temEstoque(int $quantidade = 1): bool
    {
        // Estoque NULL = ilimitado
        if ($this->attributes['estoque'] === null) {
            return true;
        }

        return (int) $this->attributes['estoque'] >= $quantidade;
    }

    /**
     * Retorna texto de estoque
     */
    public function getEstoqueTexto(): string
    {
        if ($this->attributes['estoque'] === null) {
            return '<span class="text-muted">Ilimitado</span>';
        }

        $estoque = (int) $this->attributes['estoque'];
        
        if ($estoque <= 0) {
            return '<span class="text-danger">Esgotado</span>';
        }
        
        if ($estoque <= 10) {
            return '<span class="text-warning">' . $estoque . ' restantes</span>';
        }

        return '<span class="text-success">' . $estoque . ' em estoque</span>';
    }

    /**
     * Retorna URL da imagem ou placeholder
     */
    public function getImagemUrl(): string
    {
        if (!empty($this->attributes['imagem'])) {
            return site_url('uploads/order_bumps/' . $this->attributes['imagem']);
        }
        return site_url('recursos/theme/images/placeholder-product.png');
    }

    /**
     * Verifica se tem imagem
     */
    public function temImagem(): bool
    {
        return !empty($this->attributes['imagem'] ?? null);
    }
}

