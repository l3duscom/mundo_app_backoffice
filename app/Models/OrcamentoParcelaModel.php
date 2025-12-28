<?php

namespace App\Models;

use CodeIgniter\Model;

class OrcamentoParcelaModel extends Model
{
    protected $table                = 'orcamento_parcelas';
    protected $returnType           = 'App\Entities\OrcamentoParcela';
    protected $useSoftDeletes       = false;
    protected $allowedFields        = [
        'orcamento_id',
        'numero_parcela',
        'valor',
        'data_vencimento',
        'data_pagamento',
        'status',
        'forma_pagamento',
        'comprovante_url',
        'observacoes',
    ];

    // Dates
    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';

    /**
     * Busca parcelas de um orçamento
     */
    public function buscaPorOrcamento(int $orcamentoId): array
    {
        return $this->where('orcamento_id', $orcamentoId)
            ->orderBy('numero_parcela', 'ASC')
            ->findAll();
    }

    /**
     * Busca parcelas pendentes
     */
    public function buscaPendentes(int $orcamentoId): array
    {
        return $this->where('orcamento_id', $orcamentoId)
            ->whereIn('status', ['pendente', 'vencido'])
            ->orderBy('numero_parcela', 'ASC')
            ->findAll();
    }

    /**
     * Busca parcelas pagas
     */
    public function buscaPagas(int $orcamentoId): array
    {
        return $this->where('orcamento_id', $orcamentoId)
            ->where('status', 'pago')
            ->orderBy('numero_parcela', 'ASC')
            ->findAll();
    }

    /**
     * Conta parcelas por status
     */
    public function contaPorStatus(int $orcamentoId): array
    {
        $result = $this->select('status, COUNT(*) as total')
            ->where('orcamento_id', $orcamentoId)
            ->groupBy('status')
            ->findAll();

        $contagem = [
            'pendente' => 0,
            'pago' => 0,
            'vencido' => 0,
            'cancelado' => 0,
        ];

        foreach ($result as $row) {
            $contagem[$row->status] = (int)$row->total;
        }

        return $contagem;
    }

    /**
     * Calcula totais das parcelas
     */
    public function calculaTotais(int $orcamentoId): array
    {
        $parcelas = $this->buscaPorOrcamento($orcamentoId);
        
        $totais = [
            'total' => 0,
            'pago' => 0,
            'pendente' => 0,
            'quantidade' => count($parcelas),
            'pagas' => 0,
            'pendentes' => 0,
        ];

        foreach ($parcelas as $parcela) {
            $totais['total'] += $parcela->valor;
            
            if ($parcela->status === 'pago') {
                $totais['pago'] += $parcela->valor;
                $totais['pagas']++;
            } else {
                $totais['pendente'] += $parcela->valor;
                $totais['pendentes']++;
            }
        }

        return $totais;
    }

    /**
     * Gera parcelas automaticamente para um orçamento
     */
    public function gerarParcelas(int $orcamentoId, int $quantidade, float $valorTotal, ?string $dataInicio = null): bool
    {
        // Remove parcelas existentes
        $this->where('orcamento_id', $orcamentoId)->delete();

        if ($quantidade <= 0 || $valorTotal <= 0) {
            return true;
        }

        $valorParcela = round($valorTotal / $quantidade, 2);
        $diferenca = $valorTotal - ($valorParcela * $quantidade);
        
        $dataBase = $dataInicio ? new \DateTime($dataInicio) : new \DateTime();

        for ($i = 1; $i <= $quantidade; $i++) {
            $valorAtual = $valorParcela;
            // Adiciona diferença de arredondamento na última parcela
            if ($i === $quantidade) {
                $valorAtual += $diferenca;
            }

            $dataVencimento = clone $dataBase;
            if ($i > 1) {
                $dataVencimento->modify('+' . ($i - 1) . ' month');
            }

            $this->insert([
                'orcamento_id' => $orcamentoId,
                'numero_parcela' => $i,
                'valor' => $valorAtual,
                'data_vencimento' => $dataVencimento->format('Y-m-d'),
                'status' => 'pendente',
            ]);
        }

        return true;
    }

    /**
     * Atualiza status das parcelas vencidas
     */
    public function atualizarVencidas(): int
    {
        return $this->where('status', 'pendente')
            ->where('data_vencimento <', date('Y-m-d'))
            ->set('status', 'vencido')
            ->update();
    }

    /**
     * Marca parcela como paga
     */
    public function marcarComoPaga(int $parcelaId, ?string $formaPagamento = null, ?string $observacoes = null): bool
    {
        return $this->update($parcelaId, [
            'status' => 'pago',
            'data_pagamento' => date('Y-m-d'),
            'forma_pagamento' => $formaPagamento,
            'observacoes' => $observacoes,
        ]);
    }
}
