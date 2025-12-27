<?php

namespace App\Models;

use CodeIgniter\Model;

class OrcamentoItemModel extends Model
{
    protected $table                = 'orcamento_itens';
    protected $returnType           = 'App\Entities\OrcamentoItem';
    protected $useSoftDeletes       = false;
    protected $allowedFields        = [
        'orcamento_id',
        'descricao',
        'quantidade',
        'valor_unitario',
        'valor_total',
        'observacoes',
    ];

    // Dates
    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';

    // Validation
    protected $validationRules    = [
        'orcamento_id'   => 'required|integer',
        'descricao'      => 'required|max_length[255]',
        'quantidade'     => 'required|decimal',
        'valor_unitario' => 'required|decimal',
    ];

    /**
     * Busca itens de um orçamento
     */
    public function buscarPorOrcamento(int $orcamentoId): array
    {
        return $this->where('orcamento_id', $orcamentoId)
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    /**
     * Calcula total de um orçamento
     */
    public function calcularTotalOrcamento(int $orcamentoId): float
    {
        $result = $this->selectSum('valor_total')
            ->where('orcamento_id', $orcamentoId)
            ->first();
        
        return (float) ($result->valor_total ?? 0);
    }

    /**
     * Salva item e recalcula orçamento
     */
    public function salvarERecalcular(array $data): bool
    {
        $result = $this->save($data);
        
        if ($result && !empty($data['orcamento_id'])) {
            $orcamentoModel = new OrcamentoModel();
            $orcamentoModel->recalcularValores($data['orcamento_id']);
        }
        
        return $result;
    }

    /**
     * Remove item e recalcula orçamento
     */
    public function removerERecalcular(int $itemId): bool
    {
        $item = $this->find($itemId);
        if (!$item) {
            return false;
        }
        
        $orcamentoId = $item->orcamento_id;
        $result = $this->delete($itemId);
        
        if ($result) {
            $orcamentoModel = new OrcamentoModel();
            $orcamentoModel->recalcularValores($orcamentoId);
        }
        
        return $result;
    }
}
