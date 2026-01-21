<?php

namespace App\Models;

use CodeIgniter\Model;

class AssinaturaHistoricoModel extends Model
{
    protected $table                = 'assinatura_historicos';
    protected $returnType           = 'App\Entities\AssinaturaHistorico';
    protected $useSoftDeletes       = false;
    protected $allowedFields        = [
        'assinatura_id',
        'evento',
        'descricao',
        'dados_json',
        'created_at',
    ];

    // Dates
    protected $useTimestamps        = false;
    protected $createdField         = 'created_at';

    // Validation
    protected $validationRules = [
        'assinatura_id' => 'required|integer',
        'evento' => 'required|in_list[CREATED,PAYMENT_CONFIRMED,PAYMENT_FAILED,RENEWED,CANCELLED,EXPIRED,REACTIVATED]',
    ];

    /**
     * Busca histórico por assinatura
     */
    public function buscaPorAssinatura(int $assinaturaId): array
    {
        return $this->where('assinatura_id', $assinaturaId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Registra novo evento
     */
    public function registra(int $assinaturaId, string $evento, ?string $descricao = null, ?array $dados = null): bool
    {
        $dadosInsert = [
            'assinatura_id' => $assinaturaId,
            'evento' => $evento,
            'descricao' => $descricao,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($dados) {
            $dadosInsert['dados_json'] = json_encode($dados, JSON_UNESCAPED_UNICODE);
        }

        return $this->insert($dadosInsert) !== false;
    }

    /**
     * Busca últimos eventos de uma assinatura
     */
    public function buscaUltimos(int $assinaturaId, int $limite = 10): array
    {
        return $this->where('assinatura_id', $assinaturaId)
            ->orderBy('created_at', 'DESC')
            ->limit($limite)
            ->findAll();
    }

    /**
     * Busca último evento de um tipo específico
     */
    public function buscaUltimoEvento(int $assinaturaId, string $evento)
    {
        return $this->where('assinatura_id', $assinaturaId)
            ->where('evento', $evento)
            ->orderBy('created_at', 'DESC')
            ->first();
    }

    /**
     * Conta eventos por tipo em um período
     */
    public function contaEventosPorTipo(?string $dataInicio = null, ?string $dataFim = null): array
    {
        $builder = $this->db->table($this->table)
            ->select('evento, COUNT(*) as total')
            ->groupBy('evento');

        if ($dataInicio) {
            $builder->where('created_at >=', $dataInicio);
        }

        if ($dataFim) {
            $builder->where('created_at <=', $dataFim);
        }

        $result = $builder->get()->getResultArray();

        $contagem = [];
        foreach ($result as $row) {
            $contagem[$row['evento']] = (int) $row['total'];
        }

        return $contagem;
    }
}
