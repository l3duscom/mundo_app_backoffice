<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class CronogramaItemEntity extends Entity
{
    protected $dates = [
        'data_hora_inicio',
        'data_hora_fim',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Exibe a situação do item (Ativo/Inativo/Excluído)
     *
     * @return string
     */
    public function exibeSituacao()
    {
        if ($this->deleted_at != null) {
            // Item excluído
            $icone = '<span class="text-white">Excluído</span>&nbsp;<i class="fa fa-undo"></i>&nbsp;Desfazer';
            $situacao = anchor("cronogramaitem/desfazerexclusao/$this->id", $icone, ['class' => 'btn btn-outline-success btn-sm']);
            return $situacao;
        }

        if ($this->ativo == true) {
            $situacao = '<span class="text-success"><i class="fa fa-check-circle" aria-hidden="true"></i>&nbsp;Ativo</span>';
        } else {
            $situacao = '<span class="text-warning"><i class="fa fa-times-circle" aria-hidden="true"></i>&nbsp;Inativo</span>';
        }

        return $situacao;
    }

    /**
     * Retorna o status badge para exibição
     *
     * @return string
     */
    public function getBadgeStatus()
    {
        if ($this->deleted_at != null) {
            return '<span class="badge badge-danger">Excluído</span>';
        }

        $badges = [
            'AGUARDANDO'    => '<span class="badge badge-secondary">Aguardando</span>',
            'EM_ANDAMENTO'  => '<span class="badge badge-info">Em Andamento</span>',
            'CONCLUIDO'     => '<span class="badge badge-success">Concluído</span>',
            'CANCELADO'     => '<span class="badge badge-danger">Cancelado</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge badge-light">' . esc($this->status) . '</span>';
    }

    /**
     * Retorna a cor do status
     *
     * @return string
     */
    public function getStatusColor(): string
    {
        $colors = [
            'AGUARDANDO'    => 'secondary',
            'EM_ANDAMENTO'  => 'info',
            'CONCLUIDO'     => 'success',
            'CANCELADO'     => 'danger',
        ];

        return $colors[$this->status] ?? 'light';
    }

    /**
     * Verifica se o item está em andamento
     *
     * @return boolean
     */
    public function isEmAndamento(): bool
    {
        return $this->status === 'EM_ANDAMENTO';
    }

    /**
     * Verifica se o item está concluído
     *
     * @return boolean
     */
    public function isConcluido(): bool
    {
        return $this->status === 'CONCLUIDO';
    }

    /**
     * Verifica se o item está aguardando
     *
     * @return boolean
     */
    public function isAguardando(): bool
    {
        return $this->status === 'AGUARDANDO';
    }

    /**
     * Verifica se o item foi cancelado
     *
     * @return boolean
     */
    public function isCancelado(): bool
    {
        return $this->status === 'CANCELADO';
    }

    /**
     * Retorna a duração do item em minutos
     *
     * @return integer|null
     */
    public function getDuracaoMinutos(): ?int
    {
        if ($this->data_hora_inicio && $this->data_hora_fim) {
            $inicio = strtotime($this->data_hora_inicio);
            $fim = strtotime($this->data_hora_fim);
            
            if ($fim > $inicio) {
                return round(($fim - $inicio) / 60);
            }
        }

        return null;
    }

    /**
     * Retorna a duração formatada (Ex: 1h 30min)
     *
     * @return string|null
     */
    public function getDuracaoFormatada(): ?string
    {
        $minutos = $this->getDuracaoMinutos();

        if ($minutos === null) {
            return null;
        }

        $horas = floor($minutos / 60);
        $mins = $minutos % 60;

        if ($horas > 0 && $mins > 0) {
            return "{$horas}h {$mins}min";
        } elseif ($horas > 0) {
            return "{$horas}h";
        } else {
            return "{$mins}min";
        }
    }

    /**
     * Verifica se o item já passou (data/hora fim menor que agora)
     *
     * @return boolean
     */
    public function isPassado(): bool
    {
        if ($this->data_hora_fim) {
            return strtotime($this->data_hora_fim) < time();
        }

        return false;
    }

    /**
     * Verifica se o item está acontecendo agora
     *
     * @return boolean
     */
    public function isAgora(): bool
    {
        if ($this->data_hora_inicio && $this->data_hora_fim) {
            $agora = time();
            $inicio = strtotime($this->data_hora_inicio);
            $fim = strtotime($this->data_hora_fim);

            return $agora >= $inicio && $agora <= $fim;
        }

        return false;
    }
}

