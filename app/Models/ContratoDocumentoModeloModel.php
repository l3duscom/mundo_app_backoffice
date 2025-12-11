<?php

namespace App\Models;

use CodeIgniter\Model;

class ContratoDocumentoModeloModel extends Model
{
    protected $table                = 'contrato_documento_modelos';
    protected $returnType           = 'App\Entities\ContratoDocumentoModelo';
    protected $useSoftDeletes       = true;
    protected $allowedFields        = [
        'nome',
        'tipo_item',
        'descricao',
        'conteudo_html',
        'variaveis',
        'ativo',
    ];

    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';
    protected $deletedField         = 'deleted_at';

    protected $validationRules    = [
        'nome'          => 'required|max_length[100]',
        'tipo_item'     => 'required|max_length[50]',
        'conteudo_html' => 'required',
    ];

    protected $validationMessages = [
        'nome' => [
            'required' => 'O nome do modelo é obrigatório.',
        ],
        'tipo_item' => [
            'required' => 'Selecione o tipo de item.',
        ],
        'conteudo_html' => [
            'required' => 'O conteúdo do modelo é obrigatório.',
        ],
    ];

    /**
     * Busca modelo por tipo de item
     */
    public function buscaPorTipoItem(string $tipoItem)
    {
        return $this->where('tipo_item', $tipoItem)
            ->where('ativo', 1)
            ->first();
    }

    /**
     * Busca modelo geral (fallback)
     */
    public function buscaModeloGeral()
    {
        return $this->where('tipo_item', 'Geral')
            ->where('ativo', 1)
            ->first();
    }

    /**
     * Busca modelo adequado para um contrato com base nos itens
     */
    public function buscaModeloParaContrato(int $contratoId): ?object
    {
        // Busca tipos de itens do contrato
        $itemModel = new \App\Models\ContratoItemModel();
        $itens = $itemModel->where('contrato_id', $contratoId)->findAll();

        if (empty($itens)) {
            return $this->buscaModeloGeral();
        }

        // Pega o tipo do primeiro item (ou o mais relevante)
        $tipoItem = $itens[0]->tipo_item;

        // Tenta encontrar modelo específico
        $modelo = $this->buscaPorTipoItem($tipoItem);

        // Fallback para modelo geral
        if (!$modelo) {
            $modelo = $this->buscaModeloGeral();
        }

        return $modelo;
    }

    /**
     * Lista todos os modelos ativos
     */
    public function listaAtivos(): array
    {
        return $this->where('ativo', 1)
            ->orderBy('nome', 'ASC')
            ->findAll();
    }

    /**
     * Retorna lista de tipos de itens disponíveis
     */
    public static function getTiposItem(): array
    {
        return [
            'Geral',
            'Espaço Comercial',
            'Artist Alley',
            'Vila dos Artesãos',
            'Espaço Medieval',
            'Indie',
            'Games',
            'Espaço Temático',
            'Food Park',
            'Cota',
            'Patrocínio',
            'Parceiros',
            'Patrocinadores',
            'Energia Elétrica',
            'Internet',
            'Credenciamento',
            'Outros',
        ];
    }
}

