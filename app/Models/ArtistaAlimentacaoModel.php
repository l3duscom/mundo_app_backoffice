<?php

namespace App\Models;

use CodeIgniter\Model;

class ArtistaAlimentacaoModel extends Model
{
    protected $table                = 'artista_alimentacoes';
    protected $returnType           = 'object';
    protected $useSoftDeletes       = false;
    protected $allowedFields        = [
        'contratacao_id',
        'tipo',
        'data',
        'local',
        'quantidade_pessoas',
        'valor_pessoa',
        'valor_total',
        'status',
        'observacoes',
    ];

    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';

    public const TIPOS = [
        'cafe' => 'Café da Manhã',
        'almoco' => 'Almoço',
        'jantar' => 'Jantar',
        'lanche' => 'Lanche',
        'camarim' => 'Camarim',
        'outro' => 'Outro',
    ];

    public function buscaPorContratacao(int $contratacaoId): array
    {
        return $this->where('contratacao_id', $contratacaoId)
            ->orderBy('data', 'ASC')
            ->findAll();
    }
}
