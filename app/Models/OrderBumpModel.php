<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderBumpModel extends Model
{
    protected $table                = 'order_bumps';
    protected $returnType           = 'App\Entities\OrderBump';
    protected $useSoftDeletes       = true;
    protected $allowedFields        = [
        'event_id',
        'ticket_id',
        'nome',
        'descricao',
        'preco',
        'imagem',
        'tipo',
        'estoque',
        'max_por_pedido',
        'ordem',
        'ativo',
    ];

    // Dates
    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';
    protected $deletedField         = 'deleted_at';

    // Validation
    protected $validationRules = [
        'event_id' => 'required|integer',
        'nome' => 'required|min_length[3]|max_length[255]',
        'preco' => 'required|numeric|greater_than_equal_to[0]',
        'tipo' => 'required|in_list[produto,servico,ingresso_adicional]',
        'max_por_pedido' => 'integer|greater_than_equal_to[1]',
    ];

    protected $validationMessages = [
        'nome' => [
            'required' => 'O nome é obrigatório',
            'min_length' => 'O nome deve ter pelo menos 3 caracteres',
        ],
        'preco' => [
            'required' => 'O preço é obrigatório',
            'numeric' => 'O preço deve ser um valor numérico',
        ],
    ];

    /**
     * Busca order bumps por evento
     */
    public function buscaPorEvento(int $eventoId): array
    {
        return $this->select('order_bumps.*, tickets.nome as ticket_nome')
            ->join('tickets', 'tickets.id = order_bumps.ticket_id', 'left')
            ->where('order_bumps.event_id', $eventoId)
            ->orderBy('order_bumps.ordem', 'ASC')
            ->orderBy('order_bumps.nome', 'ASC')
            ->findAll();
    }

    /**
     * Busca order bumps para um ticket específico
     */
    public function buscaPorTicket(int $ticketId): array
    {
        return $this->where('ticket_id', $ticketId)
            ->where('ativo', 1)
            ->orderBy('ordem', 'ASC')
            ->findAll();
    }

    /**
     * Busca order bumps disponíveis no checkout
     * Retorna bumps do ticket específico + bumps gerais do evento
     */
    public function buscaDisponiveis(int $ticketId, int $eventoId): array
    {
        return $this->where('event_id', $eventoId)
            ->where('ativo', 1)
            ->groupStart()
                ->where('ticket_id', null)
                ->orWhere('ticket_id', $ticketId)
            ->groupEnd()
            ->orderBy('ordem', 'ASC')
            ->findAll();
    }

    /**
     * Busca order bumps ativos por evento (para checkout)
     */
    public function buscaAtivosEvento(int $eventoId): array
    {
        return $this->where('event_id', $eventoId)
            ->where('ativo', 1)
            ->orderBy('ordem', 'ASC')
            ->findAll();
    }

    /**
     * Decrementa estoque do order bump
     */
    public function decrementarEstoque(int $id, int $quantidade = 1): bool
    {
        $bump = $this->find($id);
        
        if (!$bump) {
            return false;
        }

        // Estoque ilimitado
        if ($bump->estoque === null) {
            return true;
        }

        // Verifica se tem estoque suficiente
        if ($bump->estoque < $quantidade) {
            return false;
        }

        return $this->update($id, [
            'estoque' => $bump->estoque - $quantidade
        ]);
    }

    /**
     * Incrementa estoque (para cancelamentos)
     */
    public function incrementarEstoque(int $id, int $quantidade = 1): bool
    {
        $bump = $this->find($id);
        
        if (!$bump || $bump->estoque === null) {
            return true;
        }

        return $this->update($id, [
            'estoque' => $bump->estoque + $quantidade
        ]);
    }

    /**
     * Verifica se o nome já existe no evento
     */
    public function nomeExiste(string $nome, int $eventoId, ?int $excluirId = null): bool
    {
        $builder = $this->where('nome', $nome)
            ->where('event_id', $eventoId);

        if ($excluirId) {
            $builder->where('id !=', $excluirId);
        }

        return $builder->countAllResults() > 0;
    }
}
