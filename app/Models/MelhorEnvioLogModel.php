<?php

namespace App\Models;

use CodeIgniter\Model;

class MelhorEnvioLogModel extends Model
{
    protected $table         = 'melhor_envio_logs';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'pedido_id',
        'endpoint',
        'http_method',
        'request_body',
        'response_body',
        'http_status',
        'duracao_ms',
        'erro',
        'created_at',
    ];

    protected $useTimestamps = false;

    public function registrar(array $dados): int
    {
        $dados['created_at'] = $dados['created_at'] ?? date('Y-m-d H:i:s');
        return (int) $this->insert($dados, true);
    }

    public function ultimos(int $limite = 100, ?int $pedidoId = null): array
    {
        $q = $this->orderBy('id', 'DESC')->limit($limite);
        if ($pedidoId !== null) {
            $q = $q->where('pedido_id', $pedidoId);
        }
        return $q->findAll();
    }
}
