<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class ContratoDocumento extends Entity
{
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'data_envio',
        'data_assinatura',
        'data_confirmacao',
    ];

    /**
     * Retorna badge de status do documento
     */
    public function getBadgeStatus(): string
    {
        $badges = [
            'rascunho'            => '<span class="badge bg-secondary"><i class="bx bx-file me-1"></i>Rascunho</span>',
            'pendente_assinatura' => '<span class="badge bg-warning text-dark"><i class="bx bx-time me-1"></i>Pendente Assinatura</span>',
            'assinado'            => '<span class="badge bg-info"><i class="bx bx-pen me-1"></i>Assinado</span>',
            'confirmado'          => '<span class="badge bg-success"><i class="bx bx-check-circle me-1"></i>Confirmado</span>',
            'cancelado'           => '<span class="badge bg-danger"><i class="bx bx-x-circle me-1"></i>Cancelado</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">' . esc($this->status) . '</span>';
    }

    /**
     * Retorna o status formatado
     */
    public function getStatusFormatado(): string
    {
        $status = [
            'rascunho'            => 'Rascunho',
            'pendente_assinatura' => 'Pendente de Assinatura',
            'assinado'            => 'Assinado pelo Expositor',
            'confirmado'          => 'Confirmado',
            'cancelado'           => 'Cancelado',
        ];

        return $status[$this->status] ?? $this->status;
    }

    /**
     * Verifica se o documento está assinado
     */
    public function isAssinado(): bool
    {
        return in_array($this->status, ['assinado', 'confirmado']);
    }

    /**
     * Verifica se o documento está confirmado
     */
    public function isConfirmado(): bool
    {
        return $this->status === 'confirmado';
    }

    /**
     * Verifica se pode ser assinado
     */
    public function podeAssinar(): bool
    {
        return $this->status === 'pendente_assinatura';
    }

    /**
     * Verifica se pode ser confirmado
     */
    public function podeConfirmar(): bool
    {
        return $this->status === 'assinado';
    }

    /**
     * Verifica se pode ser editado
     */
    public function podeEditar(): bool
    {
        return in_array($this->status, ['rascunho', 'pendente_assinatura']);
    }

    /**
     * Gera hash único para assinatura
     */
    public function gerarHashAssinatura(): string
    {
        $this->hash_assinatura = bin2hex(random_bytes(32));
        return $this->hash_assinatura;
    }

    /**
     * Retorna URL para assinatura
     */
    public function getUrlAssinatura(): string
    {
        if (empty($this->hash_assinatura)) {
            return '';
        }
        return site_url('contratodocumentos/assinar/' . $this->hash_assinatura);
    }

    /**
     * Retorna data de assinatura formatada
     */
    public function getDataAssinaturaFormatada(): string
    {
        if (empty($this->data_assinatura)) {
            return '-';
        }
        return $this->data_assinatura->format('d/m/Y H:i');
    }

    /**
     * Retorna data de confirmação formatada
     */
    public function getDataConfirmacaoFormatada(): string
    {
        if (empty($this->data_confirmacao)) {
            return '-';
        }
        return $this->data_confirmacao->format('d/m/Y H:i');
    }

    /**
     * Retorna data de envio formatada
     */
    public function getDataEnvioFormatada(): string
    {
        if (empty($this->data_envio)) {
            return '-';
        }
        return $this->data_envio->format('d/m/Y H:i');
    }
}

