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
     * Sincroniza entradas de parcelas de contrato - otimizado
     */
    public function sincronizarParcelas(): int
    {
        $db = \Config\Database::connect();
        
        // Busca parcelas não sincronizadas com LEFT JOIN, incluindo dados do expositor e evento
        $query = $db->query("
            SELECT cp.id, cp.contrato_id, cp.numero_parcela, cp.valor, cp.valor_liquido,
                   cp.data_vencimento, cp.data_pagamento, cp.status_local, cp.forma_pagamento,
                   c.event_id, c.descricao as contrato_descricao,
                   e.nome as expositor_nome,
                   ev.nome as evento_nome
            FROM contrato_parcelas cp
            INNER JOIN contratos c ON c.id = cp.contrato_id
            LEFT JOIN expositores e ON e.id = c.expositor_id
            LEFT JOIN eventos ev ON ev.id = c.event_id
            LEFT JOIN lancamentos_financeiros lf ON lf.referencia_tipo = 'contrato_parcelas' AND lf.referencia_id = cp.id AND lf.deleted_at IS NULL
            WHERE lf.id IS NULL
            LIMIT 500
        ");
        
        $parcelas = $query->getResultArray();
        $count = 0;

        foreach ($parcelas as $parcela) {
            // Monta descrição mais completa com nome do expositor e evento
            $descricao = "Parcela {$parcela['numero_parcela']} - Contrato #{$parcela['contrato_id']}";
            if (!empty($parcela['expositor_nome'])) {
                $descricao .= " - " . $parcela['expositor_nome'];
            }
            if (!empty($parcela['evento_nome'])) {
                $descricao .= " - " . $parcela['evento_nome'];
            }
            
            $data = [
                'event_id' => $parcela['event_id'],
                'tipo' => 'ENTRADA',
                'origem' => 'CONTRATO',
                'referencia_tipo' => 'contrato_parcelas',
                'referencia_id' => $parcela['id'],
                'descricao' => $descricao,
                'valor' => $parcela['valor'],
                'valor_liquido' => $parcela['valor_liquido'] ?? $parcela['valor'],
                'data_lancamento' => $parcela['data_vencimento'],
                'data_pagamento' => $parcela['data_pagamento'],
                'status' => $parcela['status_local'] === 'pago' ? 'pago' : 'pendente',
                'forma_pagamento' => $parcela['forma_pagamento'],
                'categoria' => 'Contratos',
            ];

            $this->insert($data);
            $count++;
        }

        return $count;
    }

    /**
     * Atualiza o status dos lançamentos financeiros de parcelas existentes
     * Sincroniza quando o status da parcela muda de pendente para pago (ou vice-versa)
     * Também atualiza a descrição para incluir nome do expositor e evento
     */
    public function atualizarStatusParcelas(): int
    {
        $db = \Config\Database::connect();
        
        // Busca lançamentos de parcelas com status divergente ou descrição desatualizada
        $query = $db->query("
            SELECT lf.id as lancamento_id, lf.descricao as descricao_atual,
                   cp.status_local, cp.data_pagamento, cp.valor_liquido, cp.forma_pagamento,
                   cp.numero_parcela, cp.contrato_id,
                   e.nome as expositor_nome,
                   ev.nome as evento_nome
            FROM lancamentos_financeiros lf
            INNER JOIN contrato_parcelas cp ON lf.referencia_id = cp.id
            INNER JOIN contratos c ON c.id = cp.contrato_id
            LEFT JOIN expositores e ON e.id = c.expositor_id
            LEFT JOIN eventos ev ON ev.id = c.event_id
            WHERE lf.referencia_tipo = 'contrato_parcelas'
              AND lf.deleted_at IS NULL
              AND (
                  (cp.status_local = 'pago' AND lf.status = 'pendente')
                  OR (cp.status_local != 'pago' AND lf.status = 'pago')
                  OR (e.nome IS NOT NULL AND lf.descricao NOT LIKE CONCAT('%', e.nome, '%'))
              )
            LIMIT 500
        ");
        
        $divergentes = $query->getResultArray();
        $count = 0;

        foreach ($divergentes as $item) {
            $novoStatus = $item['status_local'] === 'pago' ? 'pago' : 'pendente';
            
            // Monta descrição mais completa com nome do expositor e evento
            $descricao = "Parcela {$item['numero_parcela']} - Contrato #{$item['contrato_id']}";
            if (!empty($item['expositor_nome'])) {
                $descricao .= " - " . $item['expositor_nome'];
            }
            if (!empty($item['evento_nome'])) {
                $descricao .= " - " . $item['evento_nome'];
            }
            
            $updateData = [
                'status' => $novoStatus,
                'data_pagamento' => $item['data_pagamento'],
                'valor_liquido' => $item['valor_liquido'],
                'forma_pagamento' => $item['forma_pagamento'],
                'descricao' => $descricao,
            ];

            $this->update($item['lancamento_id'], $updateData);
            $count++;
        }

        return $count;
    }

    /**
     * Sincroniza entradas de pedidos (ingressos/PDV) - processamento otimizado
     */
    public function sincronizarPedidos(): int
    {
        $db = \Config\Database::connect();
        
        // Busca apenas pedidos que ainda não foram sincronizados (limit de 500 por vez)
        $query = $db->query("
            SELECT p.id, p.evento_id, p.codigo, p.total, p.valor_liquido, p.forma_pagamento, 
                   p.created_at, p.updated_at, p.pdv_id, e.nome as evento_nome
            FROM pedidos p
            LEFT JOIN eventos e ON e.id = p.evento_id
            LEFT JOIN lancamentos_financeiros lf ON lf.referencia_tipo = 'pedidos' AND lf.referencia_id = p.id AND lf.deleted_at IS NULL
            WHERE p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
            AND p.deleted_at IS NULL
            AND lf.id IS NULL
            ORDER BY p.id DESC
            LIMIT 500
        ");
        
        $pedidos = $query->getResultArray();
        $count = 0;

        foreach ($pedidos as $pedido) {
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
     * Sincroniza saídas de contas a pagar - otimizado
     */
    public function sincronizarContasPagar(): int
    {
        $db = \Config\Database::connect();
        
        // Busca contas não sincronizadas
        $query = $db->query("
            SELECT cp.id, cp.descricao_conta, cp.valor_conta, cp.data_vencimento, cp.situacao, cp.updated_at
            FROM contas_pagar cp
            LEFT JOIN lancamentos_financeiros lf ON lf.referencia_tipo = 'contas_pagar' AND lf.referencia_id = cp.id AND lf.deleted_at IS NULL
            WHERE lf.id IS NULL
            AND cp.deleted_at IS NULL
            LIMIT 500
        ");
        
        $contas = $query->getResultArray();
        $count = 0;

        foreach ($contas as $conta) {
            $data = [
                'event_id' => null,
                'tipo' => 'SAIDA',
                'origem' => 'CONTA_PAGAR',
                'referencia_tipo' => 'contas_pagar',
                'referencia_id' => $conta['id'],
                'descricao' => $conta['descricao_conta'],
                'valor' => $conta['valor_conta'],
                'data_lancamento' => $conta['data_vencimento'],
                'data_pagamento' => $conta['situacao'] == 1 ? date('Y-m-d', strtotime($conta['updated_at'])) : null,
                'status' => $conta['situacao'] == 1 ? 'pago' : 'pendente',
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
            'parcelas_atualizadas' => $this->atualizarStatusParcelas(),
            'pedidos' => $this->sincronizarPedidos(),
            'contas_pagar' => $this->sincronizarContasPagar(),
            'assinaturas' => $this->sincronizarAssinaturas(),
        ];
    }

    /**
     * Sincroniza entradas de assinaturas premium - pagamentos confirmados
     */
    public function sincronizarAssinaturas(): int
    {
        $db = \Config\Database::connect();
        
        // Busca assinaturas com pagamentos confirmados que ainda não foram sincronizadas
        $query = $db->query("
            SELECT a.id, a.usuario_id, a.plano_id, a.valor_pago, a.forma_pagamento, a.data_inicio,
                   p.nome as plano_nome, p.preco as plano_preco,
                   u.nome as usuario_nome
            FROM assinaturas a
            INNER JOIN planos p ON p.id = a.plano_id
            INNER JOIN usuarios u ON u.id = a.usuario_id
            LEFT JOIN lancamentos_financeiros lf ON lf.referencia_tipo = 'assinaturas' AND lf.referencia_id = a.id AND lf.deleted_at IS NULL
            WHERE a.status = 'ACTIVE'
            AND a.valor_pago > 0
            AND a.deleted_at IS NULL
            AND lf.id IS NULL
            LIMIT 500
        ");
        
        $assinaturas = $query->getResultArray();
        $count = 0;

        foreach ($assinaturas as $assinatura) {
            $descricao = "Assinatura #{$assinatura['id']} - {$assinatura['usuario_nome']} - {$assinatura['plano_nome']}";
            
            $data = [
                'event_id' => null, // Assinaturas não são vinculadas a eventos
                'tipo' => 'ENTRADA',
                'origem' => 'ASSINATURA',
                'referencia_tipo' => 'assinaturas',
                'referencia_id' => $assinatura['id'],
                'descricao' => $descricao,
                'valor' => $assinatura['valor_pago'],
                'valor_liquido' => $assinatura['valor_pago'] * 0.97, // Estima taxa Asaas ~3%
                'data_lancamento' => date('Y-m-d', strtotime($assinatura['data_inicio'])),
                'data_pagamento' => date('Y-m-d', strtotime($assinatura['data_inicio'])),
                'status' => 'pago',
                'forma_pagamento' => $assinatura['forma_pagamento'] ?? 'PIX',
                'categoria' => 'Assinaturas Premium',
            ];

            $this->insert($data);
            $count++;
        }

        return $count;
    }
}
