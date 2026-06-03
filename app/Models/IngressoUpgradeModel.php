<?php

namespace App\Models;

use CodeIgniter\Model;

class IngressoUpgradeModel extends Model
{
    protected $table            = 'ingresso_upgrades';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\IngressoUpgrade::class;
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields = [
        'ingresso_id',
        'ticket_destino_id',
        'valor_original',
        'valor_destino',
        'desconto_pct',
        'valor_cobrado',
        'forma_pagamento',
        'charge_id',
        'status',
        'link_pagamento',
        'qrcode',
        'qrcode_image',
        'expire_at',
        'efetivado_em',
        'efetivado_por',
    ];

    public function porIngresso(int $ingressoId): array
    {
        return $this->where('ingresso_id', $ingressoId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    public function porChargeId(string $chargeId): ?object
    {
        return $this->where('charge_id', $chargeId)->first();
    }

    public function pendentePorIngresso(int $ingressoId): ?object
    {
        return $this->where('ingresso_id', $ingressoId)
                    ->where('status', 'pendente')
                    ->first();
    }
}
