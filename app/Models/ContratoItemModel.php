<?php

namespace App\Models;

use CodeIgniter\Model;

class ContratoItemModel extends Model
{
    protected $table                = 'contrato_itens';
    protected $returnType           = 'App\Entities\ContratoItem';
    protected $useSoftDeletes       = true;
    protected $allowedFields        = [
        'contrato_id',
        'tipo_item',
        'descricao',
        'localizacao',
        'metragem',
        'quantidade',
        'valor_unitario',
        'valor_desconto',
        'valor_total',
        'observacoes',
    ];

    // Dates
    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';
    protected $deletedField         = 'deleted_at';

    // Validation
    protected $validationRules    = [
        'contrato_id'    => 'required|integer',
        'tipo_item'      => 'required|max_length[50]',
        'quantidade'     => 'required|integer|greater_than[0]',
        'valor_unitario' => 'required|numeric',
    ];

    protected $validationMessages = [
        'contrato_id' => [
            'required' => 'O contrato é obrigatório.',
        ],
        'tipo_item' => [
            'required' => 'Selecione o tipo do item.',
        ],
        'quantidade' => [
            'required' => 'Informe a quantidade.',
            'greater_than' => 'A quantidade deve ser maior que zero.',
        ],
        'valor_unitario' => [
            'required' => 'Informe o valor unitário.',
        ],
    ];

    /**
     * Busca itens por contrato
     *
     * @param int $contratoId
     * @return array
     */
    public function buscaPorContrato(int $contratoId): array
    {
        return $this->where('contrato_id', $contratoId)
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    /**
     * Calcula o total dos itens de um contrato
     *
     * @param int $contratoId
     * @return array
     */
    public function calculaTotaisContrato(int $contratoId): array
    {
        $resultado = $this->select('
            SUM(quantidade * valor_unitario) as subtotal,
            SUM(valor_desconto) as total_desconto,
            SUM(valor_total) as total,
            COUNT(*) as quantidade_itens
        ')
        ->where('contrato_id', $contratoId)
        ->first();

        return [
            'subtotal'         => (float)($resultado->subtotal ?? 0),
            'total_desconto'   => (float)($resultado->total_desconto ?? 0),
            'total'            => (float)($resultado->total ?? 0),
            'quantidade_itens' => (int)($resultado->quantidade_itens ?? 0),
        ];
    }

    /**
     * Atualiza os totais do contrato pai
     *
     * @param int $contratoId
     * @return bool
     */
    public function atualizaTotaisContrato(int $contratoId): bool
    {
        $totais = $this->calculaTotaisContrato($contratoId);

        $contratoModel = new \App\Models\ContratoModel();
        $contrato = $contratoModel->find($contratoId);

        if (!$contrato) {
            return false;
        }

        // Atualiza os valores do contrato baseado nos itens
        $contrato->valor_original = $totais['subtotal'];
        $contrato->valor_desconto = $totais['total_desconto'];
        
        // Calcula valor final considerando desconto adicional
        $descontoAdicional = (float)($contrato->desconto_adicional ?? 0);
        $contrato->valor_final = $totais['total'] - $descontoAdicional;
        $contrato->valor_em_aberto = $contrato->valor_final - (float)($contrato->valor_pago ?? 0);

        if ($contrato->quantidade_parcelas > 0) {
            $contrato->valor_parcela = $contrato->valor_final / $contrato->quantidade_parcelas;
        }

        return $contratoModel->save($contrato);
    }

    /**
     * Retorna os tipos de itens disponíveis
     *
     * @return array
     */
    public static function getTiposItem(): array
    {
        return [
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

