<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Expositor extends Entity
{

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Retorna o tipo de pessoa formatado
     *
     * @return string
     */
    public function getTipoPessoaFormatado(): string
    {
        return $this->tipo_pessoa === 'pj' ? 'Pessoa Jurídica' : 'Pessoa Física';
    }

    /**
     * Verifica se é pessoa jurídica
     *
     * @return bool
     */
    public function isPessoaJuridica(): bool
    {
        return $this->tipo_pessoa === 'pj';
    }

    /**
     * Retorna o documento formatado (CPF ou CNPJ)
     *
     * @return string
     */
    public function getDocumentoFormatado(): string
    {
        $documento = preg_replace('/[^0-9]/', '', $this->documento);
        
        if ($this->tipo_pessoa === 'pj') {
            // Formata CNPJ: 00.000.000/0000-00
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $documento);
        }
        
        // Formata CPF: 000.000.000-00
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $documento);
    }

    /**
     * Retorna o label do documento (CPF ou CNPJ)
     *
     * @return string
     */
    public function getLabelDocumento(): string
    {
        return $this->tipo_pessoa === 'pj' ? 'CNPJ' : 'CPF';
    }

    /**
     * Exibe a situação do expositor
     *
     * @return string
     */
    public function exibeSituacao(): string
    {
        if ($this->deleted_at != null) {
            $icone = '<span class="text-white">Excluído</span>&nbsp;<i class="fa fa-undo"></i>&nbsp;Desfazer';
            return anchor("expositores/desfazerexclusao/$this->id", $icone, ['class' => 'btn btn-outline-success btn-sm']);
        }

        if ($this->ativo == true) {
            return '<i class="fa fa-unlock text-success"></i>&nbsp;Ativo';
        }

        return '<i class="fa fa-lock text-warning"></i>&nbsp;Inativo';
    }

    /**
     * Retorna o endereço completo formatado
     *
     * @return string
     */
    public function getEnderecoCompleto(): string
    {
        $partes = [];

        if (!empty($this->endereco)) {
            $partes[] = $this->endereco;
        }

        if (!empty($this->numero)) {
            $partes[] = 'Nº ' . $this->numero;
        }

        if (!empty($this->complemento)) {
            $partes[] = $this->complemento;
        }

        if (!empty($this->bairro)) {
            $partes[] = $this->bairro;
        }

        if (!empty($this->cidade) && !empty($this->estado)) {
            $partes[] = $this->cidade . '/' . $this->estado;
        }

        if (!empty($this->cep)) {
            $partes[] = 'CEP: ' . $this->cep;
        }

        return implode(', ', $partes) ?: 'Endereço não informado';
    }

    /**
     * Retorna o nome de exibição (nome fantasia ou razão social)
     *
     * @return string
     */
    public function getNomeExibicao(): string
    {
        if ($this->tipo_pessoa === 'pj' && !empty($this->nome_fantasia)) {
            return $this->nome_fantasia;
        }

        return $this->nome;
    }
}

