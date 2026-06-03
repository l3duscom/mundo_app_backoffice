<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class IngressoUpgrade extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'expire_at', 'efetivado_em'];
    protected $casts   = [];

    public function getValorCobradoFormatado(): string
    {
        return 'R$ ' . number_format((float) $this->valor_cobrado, 2, ',', '.');
    }

    public function getValorOriginalFormatado(): string
    {
        return 'R$ ' . number_format((float) $this->valor_original, 2, ',', '.');
    }

    public function getValorDestinoFormatado(): string
    {
        return 'R$ ' . number_format((float) $this->valor_destino, 2, ',', '.');
    }

    public function isPago(): bool
    {
        return $this->status === 'pago';
    }

    public function isPendente(): bool
    {
        return $this->status === 'pendente';
    }

    public function getStatusBadge(): string
    {
        switch ($this->status) {
            case 'pago':
                return '<span class="badge bg-success">Pago</span>';
            case 'pendente':
                return '<span class="badge bg-warning text-dark">Pendente</span>';
            case 'cancelado':
                return '<span class="badge bg-secondary">Cancelado</span>';
            case 'expirado':
                return '<span class="badge bg-danger">Expirado</span>';
            default:
                return '<span class="badge bg-secondary">' . esc($this->status) . '</span>';
        }
    }
}
