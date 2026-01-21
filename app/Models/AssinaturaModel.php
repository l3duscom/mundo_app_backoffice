<?php

namespace App\Models;

use CodeIgniter\Model;

class AssinaturaModel extends Model
{
    protected $table                = 'assinaturas';
    protected $returnType           = 'App\Entities\Assinatura';
    protected $useSoftDeletes       = true;
    protected $allowedFields        = [
        'usuario_id',
        'plano_id',
        'asaas_subscription_id',
        'asaas_customer_id',
        'status',
        'data_inicio',
        'data_fim',
        'proximo_vencimento',
        'valor_pago',
        'forma_pagamento',
    ];

    // Dates
    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';
    protected $deletedField         = 'deleted_at';

    // Validation
    protected $validationRules = [
        'usuario_id' => 'required|integer',
        'plano_id' => 'required|integer',
        'status' => 'required|in_list[PENDING,ACTIVE,OVERDUE,CANCELLED,EXPIRED]',
    ];

    protected $validationMessages = [
        'usuario_id' => [
            'required' => 'O usuário é obrigatório',
        ],
        'plano_id' => [
            'required' => 'O plano é obrigatório',
        ],
    ];

    /**
     * Busca assinaturas por usuário
     */
    public function buscaPorUsuario(int $usuarioId): array
    {
        return $this->select('assinaturas.*, planos.nome as plano_nome, planos.ciclo as plano_ciclo')
            ->join('planos', 'planos.id = assinaturas.plano_id')
            ->where('assinaturas.usuario_id', $usuarioId)
            ->orderBy('assinaturas.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Busca assinaturas ativas
     */
    public function buscaAtivas(): array
    {
        return $this->select('assinaturas.*, planos.nome as plano_nome, usuarios.nome as usuario_nome, usuarios.email as usuario_email')
            ->join('planos', 'planos.id = assinaturas.plano_id')
            ->join('usuarios', 'usuarios.id = assinaturas.usuario_id')
            ->where('assinaturas.status', 'ACTIVE')
            ->orderBy('assinaturas.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Busca assinaturas por status
     */
    public function buscaPorStatus(string $status): array
    {
        return $this->select('assinaturas.*, planos.nome as plano_nome, usuarios.nome as usuario_nome, usuarios.email as usuario_email')
            ->join('planos', 'planos.id = assinaturas.plano_id')
            ->join('usuarios', 'usuarios.id = assinaturas.usuario_id')
            ->where('assinaturas.status', $status)
            ->orderBy('assinaturas.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Busca todas com dados relacionados
     */
    public function buscaTodas(?string $filtroStatus = null): array
    {
        $builder = $this->select('assinaturas.*, planos.nome as plano_nome, planos.preco as plano_preco, usuarios.nome as usuario_nome, usuarios.email as usuario_email')
            ->join('planos', 'planos.id = assinaturas.plano_id')
            ->join('usuarios', 'usuarios.id = assinaturas.usuario_id');

        if ($filtroStatus) {
            $builder->where('assinaturas.status', $filtroStatus);
        }

        return $builder->orderBy('assinaturas.created_at', 'DESC')->findAll();
    }

    /**
     * Busca assinatura com detalhes completos
     */
    public function buscaComDetalhes(int $id)
    {
        return $this->select('assinaturas.*, planos.nome as plano_nome, planos.preco as plano_preco, planos.ciclo as plano_ciclo, usuarios.nome as usuario_nome, usuarios.email as usuario_email, usuarios.id as usuario_id')
            ->join('planos', 'planos.id = assinaturas.plano_id')
            ->join('usuarios', 'usuarios.id = assinaturas.usuario_id')
            ->where('assinaturas.id', $id)
            ->first();
    }

    /**
     * Busca assinatura ativa do usuário
     */
    public function buscaAtivaDoUsuario(int $usuarioId)
    {
        return $this->where('usuario_id', $usuarioId)
            ->where('status', 'ACTIVE')
            ->first();
    }

    /**
     * Conta assinaturas por status
     */
    public function contaPorStatus(): array
    {
        $db = \Config\Database::connect();
        
        $result = $db->table('assinaturas')
            ->select('status, COUNT(*) as total')
            ->where('deleted_at IS NULL')
            ->groupBy('status')
            ->get()
            ->getResultArray();

        $contagem = [
            'PENDING' => 0,
            'ACTIVE' => 0,
            'OVERDUE' => 0,
            'CANCELLED' => 0,
            'EXPIRED' => 0,
        ];

        foreach ($result as $row) {
            $contagem[$row['status']] = (int) $row['total'];
        }

        return $contagem;
    }

    /**
     * Atualiza status da assinatura
     */
    public function atualizaStatus(int $id, string $status): bool
    {
        return $this->update($id, ['status' => $status]);
    }

    /**
     * Registra evento no histórico
     */
    public function registraEvento(int $assinaturaId, string $evento, ?string $descricao = null, ?array $dados = null): bool
    {
        $historicoModel = new AssinaturaHistoricoModel();
        
        return $historicoModel->insert([
            'assinatura_id' => $assinaturaId,
            'evento' => $evento,
            'descricao' => $descricao,
            'dados_json' => $dados ? json_encode($dados, JSON_UNESCAPED_UNICODE) : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]) !== false;
    }

    /**
     * Calcula receita mensal recorrente (MRR)
     */
    public function calculaMRR(): float
    {
        $db = \Config\Database::connect();
        
        $result = $db->table('assinaturas')
            ->select('SUM(CASE WHEN planos.ciclo = "MONTHLY" THEN planos.preco ELSE planos.preco / 12 END) as mrr')
            ->join('planos', 'planos.id = assinaturas.plano_id')
            ->where('assinaturas.status', 'ACTIVE')
            ->where('assinaturas.deleted_at IS NULL')
            ->get()
            ->getRowArray();

        return (float) ($result['mrr'] ?? 0);
    }

    /**
     * Estatísticas gerais
     */
    public function getEstatisticas(): array
    {
        $contagem = $this->contaPorStatus();
        
        return [
            'total' => array_sum($contagem),
            'ativas' => $contagem['ACTIVE'],
            'pendentes' => $contagem['PENDING'],
            'canceladas' => $contagem['CANCELLED'],
            'atrasadas' => $contagem['OVERDUE'],
            'expiradas' => $contagem['EXPIRED'],
            'mrr' => $this->calculaMRR(),
        ];
    }
}
