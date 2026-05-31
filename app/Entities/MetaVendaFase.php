<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class MetaVendaFase extends Entity
{
    protected $dates = ['created_at', 'updated_at'];
    protected $casts = [
        'meta_fase' => 'float',
    ];

    public function getNumDias(): int
    {
        if (empty($this->data_inicio) || empty($this->data_fim)) {
            return 1;
        }
        $dias = (int) round((strtotime($this->data_fim) - strtotime($this->data_inicio)) / 86400) + 1;
        return max(1, $dias);
    }

    public function getMediaDia(): float
    {
        return (float) $this->meta_fase / $this->getNumDias();
    }

    public function getMediaSemana(): float
    {
        return $this->getMediaDia() * 7;
    }

    public function getLabelPeriodo(): string
    {
        if (empty($this->data_inicio) || empty($this->data_fim)) {
            return '-';
        }
        $ini  = date('d/m', strtotime($this->data_inicio));
        $fim  = date('d/m', strtotime($this->data_fim));
        $dias = $this->getNumDias();
        return "{$ini} a {$fim} ({$dias} dias)";
    }
}
