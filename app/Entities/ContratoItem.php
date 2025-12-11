<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class ContratoItem extends Entity
{

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Retorna o valor unitário formatado em BRL
     *
     * @return string
     */
    public function getValorUnitarioFormatado(): string
    {
        return 'R$ ' . number_format($this->valor_unitario, 2, ',', '.');
    }

    /**
     * Retorna o valor do desconto formatado em BRL
     *
     * @return string
     */
    public function getValorDescontoFormatado(): string
    {
        return 'R$ ' . number_format($this->valor_desconto, 2, ',', '.');
    }

    /**
     * Retorna o valor total formatado em BRL
     *
     * @return string
     */
    public function getValorTotalFormatado(): string
    {
        return 'R$ ' . number_format($this->valor_total, 2, ',', '.');
    }

    /**
     * Retorna o subtotal (quantidade * valor unitário) formatado
     *
     * @return string
     */
    public function getSubtotalFormatado(): string
    {
        $subtotal = $this->quantidade * $this->valor_unitario;
        return 'R$ ' . number_format($subtotal, 2, ',', '.');
    }

    /**
     * Calcula o valor total do item
     *
     * @return void
     */
    public function calcularValorTotal(): void
    {
        $subtotal = $this->quantidade * $this->valor_unitario;
        $this->valor_total = $subtotal - $this->valor_desconto;
    }

    /**
     * Retorna o badge colorido do tipo de item
     *
     * @return string
     */
    public function getBadgeTipoItem(): string
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

        $classe = $cores[$this->tipo_item] ?? 'bg-secondary';

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

        return '<span class="badge ' . $classe . '" ' . $style . '>' . esc($this->tipo_item) . '</span>';
    }

    /**
     * Retorna a descrição resumida do item
     *
     * @return string
     */
    public function getDescricaoResumida(): string
    {
        $partes = [];
        
        $partes[] = $this->tipo_item;
        
        if (!empty($this->localizacao)) {
            $partes[] = $this->localizacao;
        }
        
        if (!empty($this->metragem)) {
            $partes[] = $this->metragem;
        }

        return implode(' - ', $partes);
    }
}

