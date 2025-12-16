<?php

namespace App\Models;

use CodeIgniter\Model;

class CredenciamentoModel extends Model
{
    protected $table            = 'credenciamentos';
    protected $returnType       = 'App\Entities\Credenciamento';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'contrato_id',
        'status',
        'observacoes',
    ];

    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $deletedField     = 'deleted_at';

    protected $validationRules = [
        'contrato_id' => 'required|integer',
    ];

    /**
     * Busca credenciamento por contrato
     */
    public function buscaPorContrato(int $contratoId)
    {
        return $this->where('contrato_id', $contratoId)->first();
    }

    /**
     * Cria ou retorna credenciamento existente para o contrato
     */
    public function getOuCria(int $contratoId)
    {
        $credenciamento = $this->buscaPorContrato($contratoId);
        
        if (!$credenciamento) {
            $entity = new \App\Entities\Credenciamento([
                'contrato_id' => $contratoId,
                'status' => 'pendente',
            ]);
            
            $this->save($entity);
            $credenciamento = $this->find($this->getInsertID());
        }
        
        return $credenciamento;
    }

    /**
     * Verifica se credenciamento está liberado (contrato assinado)
     */
    public function estaLiberado(int $contratoId): bool
    {
        $documentoModel = new \App\Models\ContratoDocumentoModel();
        return $documentoModel->temDocumentoConfirmado($contratoId);
    }

    /**
     * Verifica se está dentro do prazo de edição (até 5 dias antes do evento)
     */
    public function dentroDoPrazo(int $contratoId): bool
    {
        $contratoModel = new \App\Models\ContratoModel();
        $eventoModel = new \App\Models\EventoModel();
        
        $contrato = $contratoModel->find($contratoId);
        if (!$contrato) {
            return false;
        }
        
        $evento = $eventoModel->find($contrato->event_id);
        if (!$evento || !$evento->data_inicio) {
            return true; // Sem data definida, permite edição
        }
        
        $dataEvento = strtotime($evento->data_inicio);
        $prazoLimite = strtotime('-5 days', $dataEvento);
        
        return time() < $prazoLimite;
    }

    /**
     * Retorna limite de funcionários/suplentes baseado no tipo de contrato
     */
    public function getLimitePessoas(int $contratoId): array
    {
        $itemModel = new \App\Models\ContratoItemModel();
        $itens = $itemModel->buscaPorContrato($contratoId);
        
        // Verifica se tem Artist Alley ou Vila dos Artesãos
        $tiposLimitados = ['Artist Alley', 'Vila dos Artesãos'];
        $isLimitado = false;
        
        foreach ($itens as $item) {
            if (in_array($item->tipo_item, $tiposLimitados)) {
                $isLimitado = true;
                break;
            }
        }
        
        return [
            'funcionarios' => $isLimitado ? 1 : 10, // 1 editável + responsável automático
            'suplentes' => $isLimitado ? 0 : 10,
            'veiculos' => 1,
            'auto_responsavel_funcionario' => $isLimitado, // Flag para autopreenchimento
        ];
    }

    /**
     * Atualiza status do credenciamento baseado no preenchimento
     */
    public function atualizaStatus(int $credenciamentoId): void
    {
        $pessoaModel = new \App\Models\CredenciamentoPessoaModel();
        
        $temResponsavel = $pessoaModel->where('credenciamento_id', $credenciamentoId)
            ->where('tipo', 'responsavel')
            ->countAllResults() > 0;
        
        $credenciamento = $this->find($credenciamentoId);
        
        if (!$credenciamento || $credenciamento->status === 'aprovado' || $credenciamento->status === 'bloqueado') {
            return;
        }
        
        if ($temResponsavel) {
            $this->update($credenciamentoId, ['status' => 'completo']);
        } else {
            $this->update($credenciamentoId, ['status' => 'em_andamento']);
        }
    }
}
