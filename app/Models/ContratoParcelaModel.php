<?php

namespace App\Models;

use CodeIgniter\Model;

class ContratoParcelaModel extends Model
{
    protected $table                = 'contrato_parcelas';
    protected $returnType           = 'App\Entities\ContratoParcela';
    protected $useSoftDeletes       = false;
    protected $allowedFields        = [
        'contrato_id',
        'asaas_payment_id',
        'asaas_installment_id',
        'numero_parcela',
        'valor',
        'valor_liquido',
        'data_vencimento',
        'data_pagamento',
        'status',
        'status_local',
        'comprovante_url',
        'forma_pagamento',
        'observacoes',
        'synced_at',
    ];

    // Dates
    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';

    /**
     * Busca parcelas de um contrato
     */
    public function buscaPorContrato(int $contratoId): array
    {
        return $this->where('contrato_id', $contratoId)
            ->orderBy('data_vencimento', 'ASC')
            ->orderBy('numero_parcela', 'ASC')
            ->findAll();
    }

    /**
     * Busca parcela pelo ID do Asaas
     */
    public function buscaPorAsaasId(string $asaasPaymentId)
    {
        return $this->where('asaas_payment_id', $asaasPaymentId)->first();
    }

    /**
     * Busca parcelas pendentes de um contrato
     */
    public function buscaPendentes(int $contratoId): array
    {
        return $this->where('contrato_id', $contratoId)
            ->whereIn('status_local', ['pendente', 'vencido'])
            ->orderBy('numero_parcela', 'ASC')
            ->findAll();
    }

    /**
     * Busca parcelas pagas de um contrato
     */
    public function buscaPagas(int $contratoId): array
    {
        return $this->where('contrato_id', $contratoId)
            ->where('status_local', 'pago')
            ->orderBy('numero_parcela', 'ASC')
            ->findAll();
    }

    /**
     * Conta parcelas por status
     */
    public function contaPorStatus(int $contratoId): array
    {
        $result = $this->select('status_local, COUNT(*) as total')
            ->where('contrato_id', $contratoId)
            ->groupBy('status_local')
            ->findAll();

        $contagem = [
            'pendente' => 0,
            'pago' => 0,
            'vencido' => 0,
            'cancelado' => 0,
        ];

        foreach ($result as $row) {
            $contagem[$row->status_local] = (int)$row->total;
        }

        return $contagem;
    }

    /**
     * Calcula totais das parcelas
     */
    public function calculaTotais(int $contratoId): array
    {
        $parcelas = $this->buscaPorContrato($contratoId);
        
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
            
            if ($parcela->status_local === 'pago') {
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
     * Sincroniza parcelas do Asaas
     */
    public function sincronizarDoAsaas(int $contratoId, array $parcelasAsaas, ?string $installmentId = null): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            foreach ($parcelasAsaas as $index => $parcelaAsaas) {
                // Verifica se já existe
                $parcelaExistente = $this->where('asaas_payment_id', $parcelaAsaas['id'])->first();

                // Mapeia status do Asaas para status local
                $statusLocal = $this->mapearStatusAsaas($parcelaAsaas['status']);

                $dados = [
                    'contrato_id' => $contratoId,
                    'asaas_payment_id' => $parcelaAsaas['id'],
                    'asaas_installment_id' => $installmentId,
                    'numero_parcela' => $index + 1,
                    'valor' => $parcelaAsaas['value'],
                    'valor_liquido' => $parcelaAsaas['netValue'] ?? $parcelaAsaas['value'],
                    'data_vencimento' => $parcelaAsaas['dueDate'],
                    'data_pagamento' => $parcelaAsaas['paymentDate'] ?? null,
                    'status' => $parcelaAsaas['status'],
                    'status_local' => $statusLocal,
                    'comprovante_url' => $parcelaAsaas['transactionReceiptUrl'] ?? null,
                    'forma_pagamento' => $parcelaAsaas['billingType'] ?? null,
                    'synced_at' => date('Y-m-d H:i:s'),
                ];

                if ($parcelaExistente) {
                    $this->update($parcelaExistente->id, $dados);
                } else {
                    $this->insert($dados);
                }
            }

            $db->transComplete();
            return $db->transStatus();

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Erro ao sincronizar parcelas: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mapeia status do Asaas para status local
     */
    private function mapearStatusAsaas(string $statusAsaas): string
    {
        $mapa = [
            'PENDING' => 'pendente',
            'AWAITING_RISK_ANALYSIS' => 'pendente',
            'RECEIVED' => 'pago',
            'CONFIRMED' => 'pago',
            'RECEIVED_IN_CASH' => 'pago',
            'OVERDUE' => 'vencido',
            'REFUNDED' => 'cancelado',
            'REFUND_REQUESTED' => 'pendente',
            'CHARGEBACK_REQUESTED' => 'vencido',
            'CHARGEBACK_DISPUTE' => 'vencido',
            'DUNNING_REQUESTED' => 'vencido',
            'DUNNING_RECEIVED' => 'pago',
        ];

        return $mapa[$statusAsaas] ?? 'pendente';
    }

    /**
     * Atualiza status de uma parcela pelo ID do Asaas
     */
    public function atualizarPorAsaasId(string $asaasPaymentId, array $dados): bool
    {
        $parcela = $this->buscaPorAsaasId($asaasPaymentId);
        
        if (!$parcela) {
            return false;
        }

        // Mapeia status se foi passado
        if (isset($dados['status'])) {
            $dados['status_local'] = $this->mapearStatusAsaas($dados['status']);
        }

        $dados['synced_at'] = date('Y-m-d H:i:s');

        return $this->update($parcela->id, $dados);
    }
}

