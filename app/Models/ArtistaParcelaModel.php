<?php

namespace App\Models;

use CodeIgniter\Model;

class ArtistaParcelaModel extends Model
{
    protected $table                = 'artista_parcelas';
    protected $returnType           = 'object';
    protected $useSoftDeletes       = false;
    protected $allowedFields        = [
        'contratacao_id',
        'numero_parcela',
        'valor',
        'data_vencimento',
        'data_pagamento',
        'status',
        'forma_pagamento',
        'observacoes',
    ];

    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';

    public function buscaPorContratacao(int $contratacaoId): array
    {
        return $this->where('contratacao_id', $contratacaoId)
            ->orderBy('numero_parcela', 'ASC')
            ->findAll();
    }

    /**
     * Gera parcelas automaticamente
     */
    public function gerarParcelas(int $contratacaoId, int $quantidade, float $valorTotal, ?string $dataInicio = null): bool
    {
        // Remove existentes
        $this->where('contratacao_id', $contratacaoId)->delete();

        if ($quantidade <= 0 || $valorTotal <= 0) return true;

        $valorParcela = round($valorTotal / $quantidade, 2);
        $diferenca = $valorTotal - ($valorParcela * $quantidade);
        
        $dataBase = $dataInicio ? new \DateTime($dataInicio) : new \DateTime();

        for ($i = 1; $i <= $quantidade; $i++) {
            $valorAtual = $valorParcela;
            if ($i === $quantidade) $valorAtual += $diferenca;

            $dataVencimento = clone $dataBase;
            if ($i > 1) $dataVencimento->modify('+' . ($i - 1) . ' month');

            $this->insert([
                'contratacao_id' => $contratacaoId,
                'numero_parcela' => $i,
                'valor' => $valorAtual,
                'data_vencimento' => $dataVencimento->format('Y-m-d'),
                'status' => 'pendente',
            ]);
        }

        return true;
    }

    /**
     * Marca parcela como paga
     */
    public function marcarComoPaga(int $parcelaId, ?string $formaPagamento = null): bool
    {
        return $this->update($parcelaId, [
            'status' => 'pago',
            'data_pagamento' => date('Y-m-d'),
            'forma_pagamento' => $formaPagamento,
        ]);
    }
}
