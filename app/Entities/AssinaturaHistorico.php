<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class AssinaturaHistorico extends Entity
{
    protected $attributes = [
        'id' => null,
        'assinatura_id' => null,
        'evento' => 'CREATED',
        'descricao' => null,
        'dados_json' => null,
        'created_at' => null,
    ];

    protected $casts = [
        'id' => 'integer',
        'assinatura_id' => 'integer',
    ];

    protected $dates = ['created_at'];

    /**
     * Retorna badge do evento
     */
    public function exibeEvento(): string
    {
        $eventos = [
            'CREATED' => ['label' => 'Criada', 'class' => 'bg-primary', 'icon' => 'bx-plus-circle'],
            'PAYMENT_CONFIRMED' => ['label' => 'Pagamento Confirmado', 'class' => 'bg-success', 'icon' => 'bx-check-circle'],
            'PAYMENT_FAILED' => ['label' => 'Pagamento Falhou', 'class' => 'bg-danger', 'icon' => 'bx-x-circle'],
            'RENEWED' => ['label' => 'Renovada', 'class' => 'bg-info', 'icon' => 'bx-refresh'],
            'CANCELLED' => ['label' => 'Cancelada', 'class' => 'bg-secondary', 'icon' => 'bx-block'],
            'EXPIRED' => ['label' => 'Expirada', 'class' => 'bg-dark', 'icon' => 'bx-time-five'],
            'REACTIVATED' => ['label' => 'Reativada', 'class' => 'bg-success', 'icon' => 'bx-revision'],
        ];

        $evento = $this->attributes['evento'] ?? 'CREATED';
        $config = $eventos[$evento] ?? $eventos['CREATED'];

        return '<span class="badge ' . $config['class'] . '"><i class="bx ' . $config['icon'] . ' me-1"></i>' . $config['label'] . '</span>';
    }

    /**
     * Retorna o texto do evento
     */
    public function getEventoTexto(): string
    {
        $eventosTextos = [
            'CREATED' => 'Assinatura criada',
            'PAYMENT_CONFIRMED' => 'Pagamento confirmado',
            'PAYMENT_FAILED' => 'Falha no pagamento',
            'RENEWED' => 'Assinatura renovada',
            'CANCELLED' => 'Assinatura cancelada',
            'EXPIRED' => 'Assinatura expirada',
            'REACTIVATED' => 'Assinatura reativada',
        ];

        return $eventosTextos[$this->attributes['evento']] ?? 'Evento desconhecido';
    }

    /**
     * Retorna ícone do evento
     */
    public function getIcone(): string
    {
        $icones = [
            'CREATED' => 'bx-plus-circle text-primary',
            'PAYMENT_CONFIRMED' => 'bx-check-circle text-success',
            'PAYMENT_FAILED' => 'bx-x-circle text-danger',
            'RENEWED' => 'bx-refresh text-info',
            'CANCELLED' => 'bx-block text-secondary',
            'EXPIRED' => 'bx-time-five text-dark',
            'REACTIVATED' => 'bx-revision text-success',
        ];

        return $icones[$this->attributes['evento']] ?? 'bx-info-circle text-muted';
    }

    /**
     * Retorna dados adicionais como array
     */
    public function getDados(): array
    {
        $dados = $this->attributes['dados_json'] ?? null;
        
        if (empty($dados)) {
            return [];
        }

        if (is_string($dados)) {
            return json_decode($dados, true) ?? [];
        }

        return (array) $dados;
    }

    /**
     * Define dados JSON
     */
    public function setDadosJson($dados): self
    {
        if (is_array($dados)) {
            $this->attributes['dados_json'] = json_encode($dados, JSON_UNESCAPED_UNICODE);
        } else {
            $this->attributes['dados_json'] = $dados;
        }
        return $this;
    }

    /**
     * Retorna data formatada
     */
    public function getDataFormatada(): string
    {
        if (empty($this->attributes['created_at'])) {
            return '-';
        }
        
        $data = $this->attributes['created_at'];
        if (is_string($data)) {
            $data = new \DateTime($data);
        }
        
        return $data->format('d/m/Y H:i');
    }
}
