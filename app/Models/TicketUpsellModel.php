<?php

namespace App\Models;

use CodeIgniter\Model;

class TicketUpsellModel extends Model
{
    protected $table                = 'ticket_upsells';
    protected $returnType           = 'App\Entities\TicketUpsell';
    protected $useSoftDeletes       = false;
    protected $allowedFields        = [
        'event_id',
        'ticket_origem_id',
        'ticket_destino_id',
        'valor_diferenca',
        'valor_customizado',
        'desconto_percentual',
        'titulo',
        'descricao',
        'ativo',
        'ordem',
    ];

    // Dates
    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';

    // Validation
    protected $validationRules = [
        'event_id' => 'required|integer',
        'ticket_origem_id' => 'required|integer',
        'ticket_destino_id' => 'required|integer',
    ];

    /**
     * Busca upsells por evento
     */
    public function buscaPorEvento(int $eventoId): array
    {
        return $this->select('ticket_upsells.*, 
                to1.nome as ticket_origem_nome, to1.preco as ticket_origem_preco,
                to2.nome as ticket_destino_nome, to2.preco as ticket_destino_preco')
            ->join('tickets to1', 'to1.id = ticket_upsells.ticket_origem_id')
            ->join('tickets to2', 'to2.id = ticket_upsells.ticket_destino_id')
            ->where('ticket_upsells.event_id', $eventoId)
            ->orderBy('ticket_upsells.ordem', 'ASC')
            ->orderBy('to1.nome', 'ASC')
            ->findAll();
    }

    /**
     * Busca upsells ativos para um ticket específico
     */
    public function buscaUpsellsDisponiveis(int $ticketOrigemId): array
    {
        return $this->select('ticket_upsells.*, 
                to2.nome as ticket_destino_nome, to2.preco as ticket_destino_preco, to2.descricao as ticket_destino_descricao')
            ->join('tickets to2', 'to2.id = ticket_upsells.ticket_destino_id')
            ->where('ticket_upsells.ticket_origem_id', $ticketOrigemId)
            ->where('ticket_upsells.ativo', 1)
            ->where('to2.ativo', 1)
            ->orderBy('ticket_upsells.ordem', 'ASC')
            ->findAll();
    }

    /**
     * Calcula a diferença de preço entre dois tickets
     */
    public function calcularDiferenca(int $ticketOrigemId, int $ticketDestinoId): float
    {
        $ticketModel = new TicketModel();
        
        $origem = $ticketModel->find($ticketOrigemId);
        $destino = $ticketModel->find($ticketDestinoId);

        if (!$origem || !$destino) {
            return 0;
        }

        $diferenca = (float) $destino->preco - (float) $origem->preco;
        return max(0, $diferenca); // Não permite valor negativo
    }

    /**
     * Verifica se já existe upsell para a combinação
     */
    public function existeUpsell(int $ticketOrigemId, int $ticketDestinoId, ?int $excluirId = null): bool
    {
        $builder = $this->where('ticket_origem_id', $ticketOrigemId)
            ->where('ticket_destino_id', $ticketDestinoId);

        if ($excluirId) {
            $builder->where('id !=', $excluirId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Salva upsell calculando a diferença automaticamente
     */
    public function salvarComCalculo(array $data): bool|int
    {
        // Calcula a diferença
        $data['valor_diferenca'] = $this->calcularDiferenca(
            (int) $data['ticket_origem_id'],
            (int) $data['ticket_destino_id']
        );

        if (isset($data['id']) && $data['id']) {
            return $this->update($data['id'], $data);
        }

        return $this->insert($data);
    }
}
