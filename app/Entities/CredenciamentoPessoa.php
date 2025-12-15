<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class CredenciamentoPessoa extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'credenciamento_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $dates = ['created_at', 'updated_at'];

    /**
     * Retorna CPF formatado
     */
    public function getCpfFormatado(): string
    {
        $cpf = preg_replace('/\D/', '', $this->cpf);
        if (strlen($cpf) === 11) {
            return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
        }
        return $this->cpf;
    }

    /**
     * Retorna WhatsApp formatado
     */
    public function getWhatsappFormatado(): string
    {
        $numero = preg_replace('/\D/', '', $this->whatsapp);
        if (strlen($numero) === 11) {
            return '(' . substr($numero, 0, 2) . ') ' . substr($numero, 2, 5) . '-' . substr($numero, 7);
        }
        return $this->whatsapp;
    }

    /**
     * Retorna badge do tipo
     */
    public function getBadgeTipo(): string
    {
        $badges = [
            'responsavel' => '<span class="badge bg-primary">Responsável</span>',
            'funcionario' => '<span class="badge bg-secondary">Funcionário</span>',
            'suplente' => '<span class="badge bg-warning text-dark">Suplente</span>',
        ];

        return $badges[$this->tipo] ?? '<span class="badge bg-secondary">' . ucfirst($this->tipo) . '</span>';
    }

    /**
     * Verifica se é responsável
     */
    public function isResponsavel(): bool
    {
        return $this->tipo === 'responsavel';
    }
}
