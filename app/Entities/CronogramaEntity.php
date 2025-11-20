<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class CronogramaEntity extends Entity
{
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Exibe a situação do cronograma (Ativo/Inativo/Excluído)
     *
     * @return string
     */
    public function exibeSituacao()
    {
        if ($this->deleted_at != null) {
            // Cronograma excluído
            $icone = '<span class="text-white">Excluído</span>&nbsp;<i class="fa fa-undo"></i>&nbsp;Desfazer';
            $situacao = anchor("cronograma/desfazerexclusao/$this->id", $icone, ['class' => 'btn btn-outline-success btn-sm']);
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

        if ($this->ativo == true) {
            return '<span class="badge badge-success">Ativo</span>';
        }

        return '<span class="badge badge-warning">Inativo</span>';
    }
}

