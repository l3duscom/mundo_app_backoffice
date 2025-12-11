<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class ItemCatalogo extends Entity
{

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Retorna o valor formatado em BRL
     *
     * @return string
     */
    public function getValorFormatado(): string
    {
        return 'R$ ' . number_format($this->valor, 2, ',', '.');
    }

    /**
     * Retorna o badge colorido do tipo
     *
     * @return string
     */
    public function getBadgeTipo(): string
    {
        $cores = [
            'Espaço Comercial'  => 'bg-primary',
            'Cota'              => 'bg-gold',
            'Patrocínio'        => 'bg-purple',
            'Artist Alley'      => 'bg-info',
            'Vila dos Artesãos' => 'bg-warning text-dark',
            'Espaço Medieval'   => 'bg-dark',
            'Indie'             => 'bg-success',
            'Games'             => 'bg-danger',
            'Food Park'         => 'bg-orange',
            'Espaço Temático'   => 'bg-secondary',
            'Parceiros'         => 'bg-info',
            'Patrocinadores'    => 'bg-gold',
            'Energia Elétrica'  => 'bg-warning text-dark',
            'Internet'          => 'bg-info',
            'Credenciamento'    => 'bg-secondary',
            'Outros'            => 'bg-light text-dark',
        ];

        $classe = $cores[$this->tipo] ?? 'bg-secondary';

        $style = '';
        if ($classe === 'bg-purple') {
            $classe = '';
            $style = 'style="background-color: #6f42c1; color: white;"';
        } elseif ($classe === 'bg-orange') {
            $classe = '';
            $style = 'style="background-color: #fd7e14; color: white;"';
        } elseif ($classe === 'bg-gold') {
            $classe = '';
            $style = 'style="background-color: #ffc107; color: #000;"';
        }

        return '<span class="badge ' . $classe . '" ' . $style . '>' . esc($this->tipo) . '</span>';
    }

    /**
     * Retorna o badge de status
     *
     * @return string
     */
    public function exibeStatus(): string
    {
        if ($this->ativo) {
            return '<span class="badge bg-success">Ativo</span>';
        }
        return '<span class="badge bg-danger">Inativo</span>';
    }

    /**
     * Retorna a descrição resumida para exibição
     *
     * @return string
     */
    public function getDescricaoResumida(): string
    {
        $partes = [$this->nome];
        
        if (!empty($this->metragem)) {
            $partes[] = $this->metragem;
        }
        
        return implode(' - ', $partes);
    }
}

