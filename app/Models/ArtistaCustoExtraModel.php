<?php

namespace App\Models;

use CodeIgniter\Model;

class ArtistaCustoExtraModel extends Model
{
    protected $table                = 'artista_custos_extras';
    protected $returnType           = 'object';
    protected $useSoftDeletes       = false;
    protected $allowedFields        = [
        'contratacao_id',
        'descricao',
        'valor',
        'data',
        'status',
        'observacoes',
    ];

    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';

    public function buscaPorContratacao(int $contratacaoId): array
    {
        return $this->where('contratacao_id', $contratacaoId)
            ->orderBy('data', 'ASC')
            ->findAll();
    }
}
