<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class OrcamentoAnexo extends Entity
{
    protected $attributes = [
        'id' => null,
        'orcamento_id' => null,
        'nome_arquivo' => null,
        'arquivo' => null,
        'tipo' => null,
        'tamanho' => null,
        'created_at' => null,
    ];

    protected $casts = [
        'id' => 'integer',
        'orcamento_id' => 'integer',
        'tamanho' => 'integer',
    ];

    protected $dates = ['created_at'];

    /**
     * Verifica se é imagem
     */
    public function isImagem(): bool
    {
        return in_array($this->attributes['tipo'], ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    /**
     * Verifica se é PDF
     */
    public function isPdf(): bool
    {
        return $this->attributes['tipo'] === 'application/pdf';
    }

    /**
     * Retorna tamanho formatado
     */
    public function getTamanhoFormatado(): string
    {
        $bytes = $this->attributes['tamanho'];
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    /**
     * Retorna ícone baseado no tipo
     */
    public function getIcone(): string
    {
        if ($this->isPdf()) {
            return 'bx bxs-file-pdf text-danger';
        }
        if ($this->isImagem()) {
            return 'bx bxs-image text-primary';
        }
        return 'bx bxs-file text-secondary';
    }
}
