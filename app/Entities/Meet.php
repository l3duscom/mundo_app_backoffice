<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Meet extends Entity
{

    protected $dates = [
        'data_meet',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function getDataMeetFormatada(): string
    {
        if (!$this->data_meet) {
            return '-';
        }
        return date('d/m/Y', strtotime((string) $this->data_meet));
    }

    public function getHorarioFormatado(): string
    {
        $ini = $this->hora_inicial ? substr((string) $this->hora_inicial, 0, 5) : null;
        $fim = $this->hora_final ? substr((string) $this->hora_final, 0, 5) : null;

        if ($ini && $fim) {
            return $ini . ' às ' . $fim;
        }
        return $ini ?? $fim ?? '-';
    }

    public function exibeSituacao()
    {
        if ($this->deleted_at != null) {

            // Cliente excluído

            $icone = '<span class="text-white">Excluído</span>&nbsp;<i class="fa fa-undo"></i>&nbsp;Desfazer';

            $situacao = anchor("clientes/desfazerexclusao/$this->id", $icone, ['class' => 'btn btn-outline-succes btn-sm']);

            return $situacao;
        }

        $situacao = '<span class="text-white"><i class="fa fa-thumbs-up" aria-hidden="true"></i>&nbsp;Disponível';

        return $situacao;
    }
}
