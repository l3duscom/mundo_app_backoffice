<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Lead extends Entity
{
    protected $attributes = [
        'id'                     => null,
        'codigo'                 => null,
        'evento_id'              => null,
        'vendedor_id'            => null,
        'etapa'                  => 'novo',
        'nome'                   => null,
        'nome_fantasia'          => null,
        'tipo_pessoa'            => 'juridica',
        'documento'              => null,
        'email'                  => null,
        'telefone'               => null,
        'celular'                => null,
        'segmento'               => null,
        'instagram'              => null,
        'origem'                 => null,
        'valor_estimado'         => 0,
        'temperatura'            => 'frio',
        'proxima_acao'           => null,
        'proxima_acao_descricao' => null,
        'motivo_perda'           => null,
        'observacoes'            => null,
        'expositor_id'           => null,
        'contrato_id'            => null,
        'created_at'             => null,
        'updated_at'             => null,
        'deleted_at'             => null,
    ];

    protected $dates = ['proxima_acao', 'created_at', 'updated_at', 'deleted_at'];
    
    protected $casts = [
        'id'              => 'integer',
        'evento_id'       => '?integer',
        'vendedor_id'     => '?integer',
        'expositor_id'    => '?integer',
        'contrato_id'     => '?integer',
        'valor_estimado'  => 'float',
    ];

    /**
     * Retorna o valor estimado formatado em Real
     */
    public function getValorEstimadoFormatado(): string
    {
        return 'R$ ' . number_format($this->attributes['valor_estimado'] ?? 0, 2, ',', '.');
    }

    /**
     * Retorna o nome de exibição (nome fantasia se existir, senão nome)
     */
    public function getNomeExibicao(): string
    {
        return $this->attributes['nome_fantasia'] ?: $this->attributes['nome'];
    }

    /**
     * Retorna o badge HTML da temperatura
     */
    public function getBadgeTemperatura(): string
    {
        $cores = [
            'frio'   => 'bg-info',
            'morno'  => 'bg-warning',
            'quente' => 'bg-danger',
        ];

        $icones = [
            'frio'   => 'bx bx-wind',
            'morno'  => 'bx bx-sun',
            'quente' => 'bx bxs-flame',
        ];

        $temp = $this->attributes['temperatura'] ?? 'frio';
        $cor = $cores[$temp] ?? 'bg-secondary';
        $icone = $icones[$temp] ?? 'bx bx-question-mark';
        
        return '<span class="badge ' . $cor . '"><i class="' . $icone . '"></i> ' . ucfirst($temp) . '</span>';
    }

    /**
     * Retorna o badge HTML da etapa
     */
    public function getBadgeEtapa(): string
    {
        $cores = [
            'novo'             => 'bg-secondary',
            'primeiro_contato' => 'bg-info',
            'qualificado'      => 'bg-primary',
            'proposta'         => 'bg-warning text-dark',
            'negociacao'       => 'bg-orange',
            'ganho'            => 'bg-success',
            'perdido'          => 'bg-danger',
        ];

        $nomes = [
            'novo'             => 'Novo',
            'primeiro_contato' => 'Primeiro Contato',
            'qualificado'      => 'Qualificado',
            'proposta'         => 'Proposta',
            'negociacao'       => 'Negociação',
            'ganho'            => 'Ganho',
            'perdido'          => 'Perdido',
        ];

        $etapa = $this->attributes['etapa'] ?? 'novo';
        $cor = $cores[$etapa] ?? 'bg-secondary';
        $nome = $nomes[$etapa] ?? $etapa;
        
        return '<span class="badge ' . $cor . '">' . $nome . '</span>';
    }

    /**
     * Verifica se o lead foi convertido
     */
    public function isConvertido(): bool
    {
        return !empty($this->attributes['expositor_id']);
    }

    /**
     * Verifica se o lead pode ser convertido (só se estiver na etapa ganho)
     */
    public function podeConverter(): bool
    {
        return $this->attributes['etapa'] === 'ganho' && !$this->isConvertido();
    }

    /**
     * Retorna a próxima ação formatada
     */
    public function getProximaAcaoFormatada(): string
    {
        if (empty($this->attributes['proxima_acao'])) {
            return '-';
        }

        $data = $this->attributes['proxima_acao'];
        if ($data instanceof \CodeIgniter\I18n\Time) {
            return $data->format('d/m/Y');
        }
        
        return date('d/m/Y', strtotime($data));
    }

    /**
     * Verifica se a próxima ação está atrasada
     */
    public function isProximaAcaoAtrasada(): bool
    {
        if (empty($this->attributes['proxima_acao'])) {
            return false;
        }

        $data = $this->attributes['proxima_acao'];
        if ($data instanceof \CodeIgniter\I18n\Time) {
            return $data->isBefore(\CodeIgniter\I18n\Time::today());
        }
        
        return strtotime($data) < strtotime('today');
    }
}
