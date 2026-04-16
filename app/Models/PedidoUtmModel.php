<?php

namespace App\Models;

use CodeIgniter\Model;

class PedidoUtmModel extends Model
{
    protected $table         = 'pedido_utms';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $allowedFields = [
        'pedido_id',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * Salva os UTMs da sessão para um pedido
     */
    public function salvaUtmsDoPedido(int $pedidoId): void
    {
        helper('utmify');
        $utms = get_utm_params();

        // Só insere se houver ao menos um UTM preenchido
        $temUtm = array_filter($utms, fn($v) => !empty($v));
        if (empty($temUtm)) {
            return;
        }

        $this->insert([
            'pedido_id'    => $pedidoId,
            'utm_source'   => $utms['utm_source'],
            'utm_medium'   => $utms['utm_medium'],
            'utm_campaign' => $utms['utm_campaign'],
            'utm_content'  => $utms['utm_content'],
            'utm_term'     => $utms['utm_term'],
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Busca UTMs de um pedido
     */
    public function buscaPorPedido(int $pedidoId): ?object
    {
        return $this->where('pedido_id', $pedidoId)->first();
    }
}
