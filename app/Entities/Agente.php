<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Agente extends Entity
{
    protected $attributes = [
        'id' => null,
        'tipo' => 'agente',
        'nome' => null,
        'nome_fantasia' => null,
        'cpf' => null,
        'cnpj' => null,
        'email' => null,
        'telefone' => null,
        'whatsapp' => null,
        'site' => null,
        'cep' => null,
        'endereco' => null,
        'numero' => null,
        'complemento' => null,
        'bairro' => null,
        'cidade' => null,
        'estado' => null,
        'banco' => null,
        'agencia' => null,
        'conta' => null,
        'tipo_conta' => null,
        'pix' => null,
        'observacoes' => null,
        'ativo' => 1,
        'created_at' => null,
        'updated_at' => null,
        'deleted_at' => null,
    ];

    protected $casts = [
        'id' => 'integer',
        'ativo' => 'boolean',
    ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    const TIPOS = [
        'agente' => 'Agente',
        'empresario' => 'Empresário',
        'agencia' => 'Agência',
        'assessoria' => 'Assessoria',
        'produtor' => 'Produtor',
        'tecnico' => 'Técnico',
        'outro' => 'Outro',
    ];

    /**
     * Retorna o tipo formatado
     */
    public function getTipoLabel(): string
    {
        return self::TIPOS[$this->attributes['tipo']] ?? $this->attributes['tipo'];
    }

    /**
     * Retorna badge de status
     */
    public function exibeStatus(): string
    {
        if ($this->attributes['ativo']) {
            return '<span class="badge bg-success">Ativo</span>';
        }
        return '<span class="badge bg-secondary">Inativo</span>';
    }

    /**
     * Retorna nome para exibição (fantasia ou nome)
     */
    public function getNomeExibicao(): string
    {
        return $this->attributes['nome_fantasia'] ?: $this->attributes['nome'];
    }

    /**
     * Retorna documento formatado (CNPJ ou CPF)
     */
    public function getDocumento(): string
    {
        return $this->attributes['cnpj'] ?: $this->attributes['cpf'] ?: '-';
    }

    /**
     * Retorna endereço completo
     */
    public function getEnderecoCompleto(): string
    {
        $partes = [];
        
        if ($this->attributes['endereco']) {
            $end = $this->attributes['endereco'];
            if ($this->attributes['numero']) {
                $end .= ', ' . $this->attributes['numero'];
            }
            if ($this->attributes['complemento']) {
                $end .= ' - ' . $this->attributes['complemento'];
            }
            $partes[] = $end;
        }
        
        if ($this->attributes['bairro']) {
            $partes[] = $this->attributes['bairro'];
        }
        
        if ($this->attributes['cidade']) {
            $cidade = $this->attributes['cidade'];
            if ($this->attributes['estado']) {
                $cidade .= '/' . $this->attributes['estado'];
            }
            $partes[] = $cidade;
        }
        
        if ($this->attributes['cep']) {
            $partes[] = 'CEP: ' . $this->attributes['cep'];
        }
        
        return implode(' - ', $partes) ?: '-';
    }
}
