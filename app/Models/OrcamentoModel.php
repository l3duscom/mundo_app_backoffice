<?php

namespace App\Models;

use CodeIgniter\Model;

class OrcamentoModel extends Model
{
    protected $table                = 'orcamentos';
    protected $returnType           = 'App\Entities\Orcamento';
    protected $useSoftDeletes       = true;
    protected $allowedFields        = [
        'event_id',
        'fornecedor_id',
        'codigo',
        'titulo',
        'descricao',
        'situacao',
        'valor_total',
        'valor_desconto',
        'valor_final',
        'forma_pagamento',
        'quantidade_parcelas',
        'data_validade',
        'data_aprovacao',
        'observacoes',
    ];

    // Dates
    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';
    protected $deletedField         = 'deleted_at';

    // Validation
    protected $validationRules    = [
        'fornecedor_id' => 'required|integer',
        'titulo'        => 'required|max_length[255]',
    ];

    protected $validationMessages = [
        'fornecedor_id' => [
            'required' => 'Selecione um fornecedor.',
        ],
        'titulo' => [
            'required' => 'Informe o título do orçamento.',
        ],
    ];

    /**
     * Gera código único do orçamento
     * Formato: ORC-ANO-XXXXXXXX
     */
    public function gerarCodigo(): string
    {
        $ano = date('Y');
        $tentativas = 0;
        $maxTentativas = 50;
        
        do {
            $codigoAleatorio = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            $codigo = sprintf('ORC-%s-%s', $ano, $codigoAleatorio);
            
            $existe = $this->where('codigo', $codigo)->countAllResults() > 0;
            $tentativas++;
            
            if ($tentativas >= $maxTentativas) {
                throw new \RuntimeException('Não foi possível gerar um código único');
            }
        } while ($existe);
        
        return $codigo;
    }

    /**
     * Busca orçamento completo com fornecedor e evento
     */
    public function buscaOrcamentoCompleto(int $id): ?object
    {
        return $this->select('orcamentos.*, fornecedores.razao as fornecedor_nome, fornecedores.cnpj as fornecedor_cnpj, fornecedores.telefone as fornecedor_telefone, eventos.nome as evento_nome')
            ->join('fornecedores', 'fornecedores.id = orcamentos.fornecedor_id', 'left')
            ->join('eventos', 'eventos.id = orcamentos.event_id', 'left')
            ->withDeleted(true)
            ->find($id);
    }

    /**
     * Busca orçamentos por evento
     */
    public function buscaPorEvento(int $eventId): array
    {
        return $this->where('event_id', $eventId)
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    /**
     * Busca orçamentos por fornecedor
     */
    public function buscaPorFornecedor(int $fornecedorId): array
    {
        return $this->where('fornecedor_id', $fornecedorId)
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    /**
     * Recalcula valores do orçamento baseado nos itens
     */
    public function recalcularValores(int $orcamentoId): void
    {
        $itemModel = new OrcamentoItemModel();
        $itens = $itemModel->where('orcamento_id', $orcamentoId)->findAll();
        
        $valorTotal = 0;
        foreach ($itens as $item) {
            $valorTotal += $item->valor_total;
        }
        
        $orcamento = $this->find($orcamentoId);
        $valorDesconto = $orcamento->valor_desconto ?? 0;
        $valorFinal = $valorTotal - $valorDesconto;
        
        $this->update($orcamentoId, [
            'valor_total' => $valorTotal,
            'valor_final' => max(0, $valorFinal),
        ]);
    }

    /**
     * Totais por situação
     */
    public function totaisPorSituacao(?int $eventId = null): array
    {
        $builder = $this->select('situacao, COUNT(*) as total, SUM(valor_final) as valor_total')
            ->groupBy('situacao');

        if ($eventId) {
            $builder->where('event_id', $eventId);
        }

        return $builder->findAll();
    }
}
