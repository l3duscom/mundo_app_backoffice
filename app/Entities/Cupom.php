<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Cupom extends Entity
{
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'evento_id' => 'integer',
        'desconto' => 'float',
        'valor_minimo' => 'float',
        'quantidade_total' => '?integer',
        'quantidade_usada' => 'integer',
        'uso_por_usuario' => 'integer',
        'ativo' => 'boolean',
    ];

    /**
     * Retorna o desconto formatado (% ou R$)
     */
    public function getDescontoFormatado(): string
    {
        if ($this->tipo === 'fixo') {
            return 'R$ ' . number_format($this->desconto, 2, ',', '.');
        }
        return number_format($this->desconto, 0) . '%';
    }

    /**
     * Retorna badge de status do cupom
     */
    public function getBadgeStatus(): string
    {
        if ($this->deleted_at !== null) {
            return '<span class="badge bg-secondary">Excluído</span>';
        }

        if (!$this->ativo) {
            return '<span class="badge bg-danger">Inativo</span>';
        }

        $hoje = date('Y-m-d');

        // Verifica se expirou
        if (!empty($this->attributes['data_fim']) && $hoje > $this->attributes['data_fim']) {
            return '<span class="badge bg-warning text-dark">Expirado</span>';
        }

        // Verifica se ainda não iniciou
        if (!empty($this->attributes['data_inicio']) && $hoje < $this->attributes['data_inicio']) {
            return '<span class="badge bg-info">Agendado</span>';
        }

        // Verifica se esgotou
        if ($this->quantidade_total !== null && $this->quantidade_usada >= $this->quantidade_total) {
            return '<span class="badge bg-secondary">Esgotado</span>';
        }

        return '<span class="badge bg-success">Ativo</span>';
    }

    /**
     * Retorna badge do tipo de desconto
     */
    public function getBadgeTipo(): string
    {
        if ($this->tipo === 'fixo') {
            return '<span class="badge bg-primary"><i class="bx bx-dollar me-1"></i>Valor Fixo</span>';
        }
        return '<span class="badge bg-info"><i class="bx bx-percent me-1"></i>Percentual</span>';
    }

    /**
     * Verifica se o cupom está válido para uso
     */
    public function isValido(): bool
    {
        if (!$this->ativo || $this->deleted_at !== null) {
            return false;
        }

        $hoje = date('Y-m-d');

        if (!empty($this->attributes['data_inicio']) && $hoje < $this->attributes['data_inicio']) {
            return false;
        }

        if (!empty($this->attributes['data_fim']) && $hoje > $this->attributes['data_fim']) {
            return false;
        }

        if ($this->quantidade_total !== null && $this->quantidade_usada >= $this->quantidade_total) {
            return false;
        }

        return true;
    }

    /**
     * Retorna a quantidade disponível
     */
    public function getQuantidadeDisponivel(): ?int
    {
        if ($this->quantidade_total === null) {
            return null; // Ilimitado
        }
        return max(0, $this->quantidade_total - $this->quantidade_usada);
    }

    /**
     * Formata a quantidade disponível para exibição
     */
    public function getQuantidadeFormatada(): string
    {
        if ($this->quantidade_total === null) {
            return '<span class="text-muted"><i class="bx bx-infinite"></i> Ilimitado</span>';
        }

        $disponivel = $this->getQuantidadeDisponivel();
        $total = $this->quantidade_total;

        if ($disponivel <= 0) {
            return '<span class="text-danger">' . $this->quantidade_usada . '/' . $total . '</span>';
        }

        $percent = ($this->quantidade_usada / $total) * 100;
        $color = $percent > 80 ? 'warning' : 'success';

        return '<span class="text-' . $color . '">' . $this->quantidade_usada . '/' . $total . '</span>';
    }

    /**
     * Formata a validade para exibição
     */
    public function getValidadeFormatada(): string
    {
        $dataInicio = $this->attributes['data_inicio'] ?? null;
        $dataFim = $this->attributes['data_fim'] ?? null;

        if (empty($dataInicio) && empty($dataFim)) {
            return '<span class="text-muted">Sem validade</span>';
        }

        $inicio = !empty($dataInicio) ? date('d/m/Y', strtotime($dataInicio)) : '-';
        $fim = !empty($dataFim) ? date('d/m/Y', strtotime($dataFim)) : '-';

        return $inicio . ' a ' . $fim;
    }

    /**
     * Retorna situação para listagem (com ação de desfazer exclusão)
     */
    public function exibeSituacao(): string
    {
        if ($this->deleted_at !== null) {
            $icone = '<span class="text-white">Excluído</span>&nbsp;<i class="fa fa-undo"></i>&nbsp;Desfazer';
            return anchor("cupons/desfazerexclusao/$this->id", $icone, ['class' => 'btn btn-outline-success btn-sm']);
        }

        return $this->getBadgeStatus();
    }
}
