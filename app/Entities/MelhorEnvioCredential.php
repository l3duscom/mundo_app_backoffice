<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use CodeIgniter\I18n\Time;

class MelhorEnvioCredential extends Entity
{
    protected $dates = ['expires_at', 'created_at', 'updated_at'];

    public function expiraEm(): ?int
    {
        if (! $this->expires_at) {
            return null;
        }
        $expira = $this->expires_at instanceof Time
            ? $this->expires_at
            : Time::parse((string) $this->expires_at);
        return (int) ($expira->getTimestamp() - time());
    }

    public function expiraEmHoras(): ?float
    {
        $segundos = $this->expiraEm();
        return $segundos === null ? null : round($segundos / 3600, 1);
    }

    public function precisaRefresh(int $margemHoras = 24): bool
    {
        $h = $this->expiraEmHoras();
        return $h === null || $h < $margemHoras;
    }
}
