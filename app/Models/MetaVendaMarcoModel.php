<?php

namespace App\Models;

use CodeIgniter\Model;

class MetaVendaMarcoModel extends Model
{
    protected $table         = 'metas_vendas_marcos';
    protected $returnType    = 'App\Entities\MetaVendaMarco';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['meta_id', 'data', 'faturamento_acumulado_esperado', 'ordem'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function porMeta(int $metaId): array
    {
        return $this->where('meta_id', $metaId)
            ->orderBy('ordem', 'ASC')
            ->orderBy('data', 'ASC')
            ->findAll();
    }

    public function removerPorMeta(int $metaId): bool
    {
        return $this->where('meta_id', $metaId)->delete();
    }
}
