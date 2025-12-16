<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class ContratoDocumentoModelo extends Entity
{
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    /**
     * Retorna as variáveis do modelo como array
     */
    public function getVariaveisArray(): array
    {
        if (empty($this->variaveis)) {
            return [];
        }
        return json_decode($this->variaveis, true) ?? [];
    }

    /**
     * Define as variáveis do modelo a partir de um array
     */
    public function setVariaveisArray(array $variaveis): void
    {
        $this->variaveis = json_encode($variaveis);
    }

    /**
     * Retorna badge de status do modelo
     */
    public function getBadgeAtivo(): string
    {
        if ($this->ativo) {
            return '<span class="badge bg-success">Ativo</span>';
        }
        return '<span class="badge bg-secondary">Inativo</span>';
    }

    /**
     * Retorna badge do tipo de item
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
            'Geral'             => 'bg-secondary',
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
     * Substitui as variáveis do modelo pelos dados reais
     */
    public function preencherConteudo(array $dados): string
    {
        $conteudo = $this->conteudo_html;
        
        // Variáveis que contêm HTML e não devem ser escapadas
        $variaveisHtml = ['itens_lista'];
        
        foreach ($dados as $chave => $valor) {
            // Não escapa variáveis que contêm HTML
            if (in_array($chave, $variaveisHtml)) {
                $conteudo = str_replace('{{' . $chave . '}}', $valor, $conteudo);
            } else {
                $conteudo = str_replace('{{' . $chave . '}}', esc($valor), $conteudo);
            }
        }
        
        return $conteudo;
    }

    /**
     * Lista de variáveis disponíveis padrão
     */
    public static function getVariaveisPadrao(): array
    {
        return [
            // Contrato
            'contrato_codigo' => 'Código do Contrato',
            'contrato_data_proposta' => 'Data da Proposta',
            'contrato_data_aceite' => 'Data do Aceite',
            'contrato_valor_original' => 'Valor Original',
            'contrato_valor_desconto' => 'Valor de Desconto',
            'contrato_valor_final' => 'Valor Final',
            'contrato_valor_pago' => 'Valor Total Pago',
            'contrato_valor_em_aberto' => 'Valor em Aberto',
            'contrato_parcelas' => 'Quantidade de Parcelas',
            'contrato_valor_parcela' => 'Valor da Parcela',
            'contrato_forma_pagamento' => 'Forma de Pagamento',
            
            // Expositor
            'expositor_nome' => 'Nome/Razão Social do Expositor',
            'expositor_nome_fantasia' => 'Nome Fantasia',
            'expositor_documento' => 'CPF/CNPJ do Expositor',
            'expositor_tipo_pessoa' => 'Tipo de Pessoa (PF/PJ)',
            'expositor_endereco' => 'Endereço Completo',
            'expositor_email' => 'E-mail do Expositor',
            'expositor_telefone' => 'Telefone do Expositor',
            
            // Evento
            'evento_nome' => 'Nome do Evento',
            'evento_data_inicio' => 'Data de Início do Evento',
            'evento_data_fim' => 'Data de Fim do Evento',
            'evento_local' => 'Local do Evento',
            
            // Itens do Contrato
            'itens_lista' => 'Lista de Itens com descrição, valor, desconto e total',
            'itens_total' => 'Total dos Itens',
            
            // Data atual
            'data_atual' => 'Data Atual',
            'data_atual_extenso' => 'Data por Extenso',
        ];
    }
}

