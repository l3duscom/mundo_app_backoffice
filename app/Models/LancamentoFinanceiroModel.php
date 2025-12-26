<?php

namespace App\Models;

use CodeIgniter\Model;

class LancamentoFinanceiroModel extends Model
{
    protected $table                = 'lancamentos_financeiros';
    protected $returnType           = 'App\Entities\LancamentoFinanceiro';
    protected $useSoftDeletes       = true;
    protected $allowedFields        = [
        'event_id',
        'tipo',
        'origem',
        'referencia_tipo',
        'referencia_id',
        'descricao',
        'valor',
        'valor_liquido',
        'data_lancamento',
        'data_pagamento',
        'status',
        'forma_pagamento',
        'categoria',
        'observacoes',
    ];

    // Dates
    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';
    protected $deletedField         = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'tipo'            => 'required|in_list[ENTRADA,SAIDA]',
        'descricao'       => 'required|min_length[3]',
        'valor'           => 'required|decimal',
        'data_lancamento' => 'required|valid_date',
    ];

    protected $validationMessages   = [];

    /**
     * Busca lançamentos com filtros
     */
    public function recuperaLancamentos(?int $eventId = null, ?string $tipo = null, ?string $status = null, ?string $dataInicio = null, ?string $dataFim = null)
    {
        $builder = $this->select('lancamentos_financeiros.*, eventos.nome as evento_nome')
            ->join('eventos', 'eventos.id = lancamentos_financeiros.event_id', 'left');

        if ($eventId) {
            $builder->where('lancamentos_financeiros.event_id', $eventId);
        }

        if ($tipo) {
            $builder->where('lancamentos_financeiros.tipo', $tipo);
        }

        if ($status) {
            $builder->where('lancamentos_financeiros.status', $status);
        }

        if ($dataInicio) {
            $builder->where('lancamentos_financeiros.data_lancamento >=', $dataInicio);
        }

        if ($dataFim) {
            $builder->where('lancamentos_financeiros.data_lancamento <=', $dataFim);
        }

        return $builder->orderBy('lancamentos_financeiros.data_lancamento', 'DESC')
            ->orderBy('lancamentos_financeiros.id', 'DESC')
            ->findAll();
    }

    /**
     * Calcula resumo financeiro
     */
    public function calculaResumo(?int $eventId = null, ?string $dataInicio = null, ?string $dataFim = null): array
    {
        $builder = $this->select('
            SUM(CASE WHEN tipo = "ENTRADA" AND status = "pago" THEN valor ELSE 0 END) as entradas_brutas,
            SUM(CASE WHEN tipo = "ENTRADA" AND status = "pago" THEN COALESCE(valor_liquido, valor) ELSE 0 END) as entradas_liquidas,
            SUM(CASE WHEN tipo = "SAIDA" AND status = "pago" THEN valor ELSE 0 END) as saidas_pagas,
            SUM(CASE WHEN tipo = "ENTRADA" AND status = "pendente" THEN valor ELSE 0 END) as entradas_pendentes,
            SUM(CASE WHEN tipo = "SAIDA" AND status = "pendente" THEN valor ELSE 0 END) as saidas_pendentes,
            COUNT(CASE WHEN status = "pendente" THEN 1 END) as qtd_pendentes
        ');

        if ($eventId) {
            $builder->where('event_id', $eventId);
        }

        if ($dataInicio) {
            $builder->where('data_lancamento >=', $dataInicio);
        }

        if ($dataFim) {
            $builder->where('data_lancamento <=', $dataFim);
        }

        $result = $builder->first();

        return [
            'entradas_brutas' => (float) ($result->entradas_brutas ?? 0),
            'entradas_liquidas' => (float) ($result->entradas_liquidas ?? 0),
            'saidas_pagas' => (float) ($result->saidas_pagas ?? 0),
            'entradas_pendentes' => (float) ($result->entradas_pendentes ?? 0),
            'saidas_pendentes' => (float) ($result->saidas_pendentes ?? 0),
            'saldo_bruto' => (float) (($result->entradas_brutas ?? 0) - ($result->saidas_pagas ?? 0)),
            'saldo_liquido' => (float) (($result->entradas_liquidas ?? 0) - ($result->saidas_pagas ?? 0)),
            'qtd_pendentes' => (int) ($result->qtd_pendentes ?? 0),
        ];
    }

    /**
     * Verifica se já existe lançamento para a referência
     */
    public function existeReferencia(string $referenciaTipo, int $referenciaId): bool
    {
        return $this->where('referencia_tipo', $referenciaTipo)
            ->where('referencia_id', $referenciaId)
            ->countAllResults() > 0;
    }

    /**
     * Sincroniza entradas de parcelas de contrato
     */
    public function sincronizarParcelas(): int
    {
        $db = \Config\Database::connect();
        $parcelaModel = new \App\Models\ContratoParcelaModel();
        $contratoModel = new \App\Models\ContratoModel();
        
        $parcelas = $parcelaModel->findAll();
        $count = 0;

        foreach ($parcelas as $parcela) {
            if ($this->existeReferencia('contrato_parcelas', $parcela->id)) {
                continue;
            }

            $contrato = $contratoModel->find($parcela->contrato_id);
            if (!$contrato) continue;

            $data = [
                'event_id' => $contrato->event_id,
                'tipo' => 'ENTRADA',
                'origem' => 'CONTRATO',
                'referencia_tipo' => 'contrato_parcelas',
                'referencia_id' => $parcela->id,
                'descricao' => "Parcela {$parcela->numero_parcela} - Contrato #{$parcela->contrato_id}",
                'valor' => $parcela->valor,
                'valor_liquido' => $parcela->valor_liquido ?? $parcela->valor,
                'data_lancamento' => $parcela->data_vencimento,
                'data_pagamento' => $parcela->data_pagamento,
                'status' => $parcela->status_local === 'pago' ? 'pago' : 'pendente',
                'forma_pagamento' => $parcela->forma_pagamento,
                'categoria' => 'Contratos',
            ];

            $this->insert($data);
            $count++;
        }

        return $count;
    }

    /**
     * Sincroniza entradas de pedidos (ingressos/PDV)
     */
    public function sincronizarPedidos(): int
    {
        $db = \Config\Database::connect();
        
        $query = $db->query("
            SELECT p.*, e.nome as evento_nome
            FROM pedidos p
            LEFT JOIN eventos e ON e.id = p.evento_id
            WHERE p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
            AND p.deleted_at IS NULL
        ");
        
        $pedidos = $query->getResultArray();
        $count = 0;

        foreach ($pedidos as $pedido) {
            if ($this->existeReferencia('pedidos', $pedido['id'])) {
                continue;
            }

            $origem = !empty($pedido['pdv_id']) ? 'PDV' : 'INGRESSO';
            
            $data = [
                'event_id' => $pedido['evento_id'],
                'tipo' => 'ENTRADA',
                'origem' => $origem,
                'referencia_tipo' => 'pedidos',
                'referencia_id' => $pedido['id'],
                'descricao' => "Pedido #{$pedido['codigo']} - {$pedido['evento_nome']}",
                'valor' => $pedido['total'],
                'valor_liquido' => $pedido['valor_liquido'] ?? $pedido['total'],
                'data_lancamento' => date('Y-m-d', strtotime($pedido['created_at'])),
                'data_pagamento' => date('Y-m-d', strtotime($pedido['updated_at'])),
                'status' => 'pago',
                'forma_pagamento' => $pedido['forma_pagamento'] ?? 'N/A',
                'categoria' => $origem === 'PDV' ? 'Vendas PDV' : 'Vendas Online',
            ];

            $this->insert($data);
            $count++;
        }

        return $count;
    }

    /**
     * Sincroniza saídas de contas a pagar
     */
    public function sincronizarContasPagar(): int
    {
        $contaPagarModel = new \App\Models\ContaPagarModel();
        $contas = $contaPagarModel->findAll();
        $count = 0;

        foreach ($contas as $conta) {
            if ($this->existeReferencia('contas_pagar', $conta->id)) {
                continue;
            }

            $data = [
                'event_id' => null,
                'tipo' => 'SAIDA',
                'origem' => 'CONTA_PAGAR',
                'referencia_tipo' => 'contas_pagar',
                'referencia_id' => $conta->id,
                'descricao' => $conta->descricao_conta,
                'valor' => $conta->valor_conta,
                'data_lancamento' => $conta->data_vencimento,
                'data_pagamento' => $conta->situacao == 1 ? date('Y-m-d', strtotime($conta->updated_at)) : null,
                'status' => $conta->situacao == 1 ? 'pago' : 'pendente',
                'forma_pagamento' => null,
                'categoria' => 'Fornecedores',
            ];

            $this->insert($data);
            $count++;
        }

        return $count;
    }

    /**
     * Sincroniza todos os dados
     */
    public function sincronizarTodos(): array
    {
        return [
            'parcelas' => $this->sincronizarParcelas(),
            'pedidos' => $this->sincronizarPedidos(),
            'contas_pagar' => $this->sincronizarContasPagar(),
        ];
    }
}
