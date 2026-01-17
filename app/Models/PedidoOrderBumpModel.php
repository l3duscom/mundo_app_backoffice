<?php

namespace App\Models;

use CodeIgniter\Model;

class PedidoOrderBumpModel extends Model
{
    protected $table                = 'pedido_order_bumps';
    protected $returnType           = 'App\Entities\PedidoOrderBump';
    protected $useSoftDeletes       = true;
    protected $allowedFields        = [
        'pedido_id',
        'order_bump_id',
        'quantidade',
        'preco_unitario',
        'usado',
        'usado_em',
    ];

    // Dates
    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';
    protected $deletedField         = 'deleted_at';

    /**
     * Busca order bumps de um pedido com informações do order bump original
     */
    public function getOrderBumpsPorPedido(int $pedidoId): array
    {
        return $this->select('pedido_order_bumps.*, order_bumps.nome, order_bumps.descricao, order_bumps.imagem, order_bumps.tipo')
            ->join('order_bumps', 'order_bumps.id = pedido_order_bumps.order_bump_id', 'left')
            ->where('pedido_order_bumps.pedido_id', $pedidoId)
            ->orderBy('pedido_order_bumps.created_at', 'ASC')
            ->findAll();
    }

    /**
     * Marca um order bump do pedido como usado
     */
    public function marcarComoUsado(int $id): bool
    {
        return $this->update($id, [
            'usado' => 1,
            'usado_em' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Desmarca um order bump do pedido (remove usado)
     */
    public function desmarcarUsado(int $id): bool
    {
        return $this->update($id, [
            'usado' => 0,
            'usado_em' => null,
        ]);
    }

    /**
     * Busca um order bump do pedido com validação de pedido
     */
    public function getOrderBumpDoPedido(int $id, int $pedidoId): ?object
    {
        return $this->where('id', $id)
            ->where('pedido_id', $pedidoId)
            ->first();
    }
}
