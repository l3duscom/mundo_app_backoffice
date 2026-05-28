<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class CredenciamentoChecklist extends Entity
{
    protected $dates = ['conferido_em', 'created_at', 'updated_at'];

    public function getTipoLabel(): string
    {
        return $this->tipo === 'saida' ? 'Saída' : 'Entrada';
    }

    public function getBadgeStatus(): string
    {
        $map = [
            'em_andamento' => ['bg-info', 'Em andamento'],
            'concluido'    => ['bg-success', 'Concluído'],
            'reaberto'     => ['bg-warning text-dark', 'Reaberto'],
        ];
        [$classe, $label] = $map[$this->status] ?? ['bg-secondary', ucfirst((string) $this->status)];
        return '<span class="badge ' . $classe . '">' . esc($label) . '</span>';
    }

    public function isConcluido(): bool
    {
        return $this->status === 'concluido';
    }
}
