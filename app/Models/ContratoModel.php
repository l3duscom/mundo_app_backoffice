<?php

namespace App\Models;

use CodeIgniter\Model;

class ContratoModel extends Model
{
    protected $table                = 'contratos';
    protected $returnType           = 'App\Entities\Contrato';
    protected $useSoftDeletes       = true;
    protected $allowedFields        = [
        'event_id',
        'expositor_id',
        'codigo',
        'descricao',
        'situacao',
        'valor_original',
        'valor_desconto',
        'desconto_adicional',
        'valor_final',
        'quantidade_parcelas',
        'valor_parcela',
        'valor_pago',
        'valor_em_aberto',
        'data_proposta',
        'data_aceite',
        'data_assinatura',
        'data_vencimento',
        'data_pagamento',
        'forma_pagamento',
        'observacoes',
        'arquivo_contrato',
        'arquivo_comprovante',
        'asaas_payment_id',
        'asaas_invoice_url',
        'asaas_billing_type',
    ];

    // Dates
    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';
    protected $deletedField         = 'deleted_at';

    // Validation
    protected $validationRules    = [
        'event_id'       => 'required|integer',
        'expositor_id'   => 'required|integer',
    ];

    protected $validationMessages = [
        'event_id' => [
            'required' => 'Selecione um evento.',
        ],
        'expositor_id' => [
            'required' => 'Selecione um expositor.',
        ],
    ];

    /**
     * Busca contratos por expositor
     *
     * @param int $expositorId
     * @return array
     */
    public function buscaPorExpositor(int $expositorId): array
    {
        return $this->where('expositor_id', $expositorId)
            ->withDeleted(true)
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    /**
     * Busca contratos por evento
     *
     * @param int $eventId
     * @return array
     */
    public function buscaPorEvento(int $eventId): array
    {
        return $this->where('event_id', $eventId)
            ->withDeleted(true)
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    /**
     * Busca contratos por situação
     *
     * @param string $situacao
     * @return array
     */
    public function buscaPorSituacao(string $situacao): array
    {
        return $this->where('situacao', $situacao)
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    /**
     * Busca contrato com dados do expositor e evento
     *
     * @param int $id
     * @return object|null
     */
    public function buscaContratoCompleto(int $id): ?object
    {
        return $this->select('contratos.*, expositores.nome as expositor_nome, expositores.documento as expositor_documento, expositores.email as expositor_email, eventos.nome as evento_nome')
            ->join('expositores', 'expositores.id = contratos.expositor_id', 'left')
            ->join('eventos', 'eventos.id = contratos.event_id', 'left')
            ->withDeleted(true)
            ->find($id);
    }

    /**
     * Gera o próximo código de contrato
     * Formato: CTR-ANO-XXXXXXXX (8 caracteres aleatórios)
     *
     * @return string
     */
    public function gerarCodigo(): string
    {
        $ano = date('Y');
        $tentativas = 0;
        $maxTentativas = 50;
        
        do {
            // Gera código aleatório de 8 caracteres (letras maiúsculas e números)
            $codigoAleatorio = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            $codigo = sprintf('CTR-%s-%s', $ano, $codigoAleatorio);
            
            // Verifica se já existe
            $existe = $this->where('codigo', $codigo)->countAllResults() > 0;
            
            $tentativas++;
            
            if ($tentativas >= $maxTentativas) {
                throw new \RuntimeException('Não foi possível gerar um código único após ' . $maxTentativas . ' tentativas');
            }
            
        } while ($existe);
        
        return $codigo;
    }

    /**
     * Retorna o total de contratos por situação
     *
     * @param int|null $eventId
     * @return array
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

    /**
     * Retorna o resumo financeiro
     *
     * @param int|null $eventId
     * @return array
     */
    public function resumoFinanceiro(?int $eventId = null): array
    {
        $builder = $this->select('
            SUM(valor_original) as total_original,
            SUM(valor_desconto) as total_desconto,
            SUM(valor_final) as total_final,
            SUM(valor_pago) as total_pago,
            SUM(valor_em_aberto) as total_em_aberto,
            COUNT(*) as total_contratos
        ')->where('situacao !=', 'cancelado')
         ->where('situacao !=', 'banido');

        if ($eventId) {
            $builder->where('event_id', $eventId);
        }

        return $builder->first();
    }
}

