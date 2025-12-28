<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class TicketUpsell extends Entity
{
    protected $attributes = [
        'id' => null,
        'event_id' => null,
        'ticket_origem_id' => null,
        'ticket_destino_id' => null,
        'valor_diferenca' => 0.00,
        'valor_customizado' => null,
        'desconto_percentual' => null,
        'titulo' => null,
        'descricao' => null,
        'ativo' => 1,
        'ordem' => 0,
        'created_at' => null,
        'updated_at' => null,
    ];

    protected $casts = [
        'id' => 'integer',
        'event_id' => 'integer',
        'ticket_origem_id' => 'integer',
        'ticket_destino_id' => 'integer',
        'valor_diferenca' => 'float',
        'valor_customizado' => '?float',
        'desconto_percentual' => '?float',
        'ativo' => 'boolean',
        'ordem' => 'integer',
    ];

    protected $dates = ['created_at', 'updated_at'];

    /**
     * Retorna o valor final a pagar
     */
    public function getValorFinal(): float
    {
        // Se tem valor customizado, usa ele
        if ($this->attributes['valor_customizado'] !== null) {
            return (float) $this->attributes['valor_customizado'];
        }

        $valor = (float) $this->attributes['valor_diferenca'];

        // Aplica desconto se houver
        if ($this->attributes['desconto_percentual'] !== null && $this->attributes['desconto_percentual'] > 0) {
            $desconto = $valor * ($this->attributes['desconto_percentual'] / 100);
            $valor -= $desconto;
        }

        return max(0, $valor);
    }

    /**
     * Retorna valor final formatado
     */
    public function getValorFinalFormatado(): string
    {
        return 'R$ ' . number_format($this->getValorFinal(), 2, ',', '.');
    }

    /**
     * Retorna valor da diferença formatado
     */
    public function getValorDiferencaFormatado(): string
    {
        return 'R$ ' . number_format($this->attributes['valor_diferenca'] ?? 0, 2, ',', '.');
    }

    /**
     * Retorna badge de status
     */
    public function exibeStatus(): string
    {
        if ($this->attributes['ativo']) {
            return '<span class="badge bg-success">Ativo</span>';
        }
        return '<span class="badge bg-secondary">Inativo</span>';
    }

    /**
     * Verifica se tem desconto
     */
    public function temDesconto(): bool
    {
        return $this->attributes['desconto_percentual'] !== null && $this->attributes['desconto_percentual'] > 0;
    }

    /**
     * Verifica se tem valor customizado
     */
    public function temValorCustomizado(): bool
    {
        return $this->attributes['valor_customizado'] !== null;
    }
}
