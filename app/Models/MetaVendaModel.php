<?php

namespace App\Models;

use CodeIgniter\Model;

class MetaVendaModel extends Model
{
    protected $table         = 'metas_vendas';
    protected $returnType    = 'App\Entities\MetaVenda';
    protected $useSoftDeletes = true;
    protected $allowedFields = ['event_id', 'tipo', 'nome', 'meta_total', 'ativo'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function porEvento(int $eventId, string $tipo = null): array
    {
        $q = $this->where('event_id', $eventId);
        if ($tipo) {
            $q = $q->where('tipo', $tipo);
        }
        return $q->orderBy('tipo', 'ASC')->orderBy('id', 'ASC')->findAll();
    }
}
