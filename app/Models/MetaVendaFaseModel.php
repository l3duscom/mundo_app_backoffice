<?php

namespace App\Models;

use CodeIgniter\Model;

class MetaVendaFaseModel extends Model
{
    protected $table         = 'metas_vendas_fases';
    protected $returnType    = 'App\Entities\MetaVendaFase';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['meta_id', 'nome', 'data_inicio', 'data_fim', 'meta_fase', 'ordem'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function porMeta(int $metaId): array
    {
        return $this->where('meta_id', $metaId)
            ->orderBy('ordem', 'ASC')
            ->orderBy('data_inicio', 'ASC')
            ->findAll();
    }

    public function removerPorMeta(int $metaId): bool
    {
        return $this->where('meta_id', $metaId)->delete();
    }
}
