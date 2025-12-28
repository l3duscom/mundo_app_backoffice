<?php

namespace App\Models;

use CodeIgniter\Model;

class ArtistaVooModel extends Model
{
    protected $table                = 'artista_voos';
    protected $returnType           = 'object';
    protected $useSoftDeletes       = false;
    protected $allowedFields        = [
        'contratacao_id',
        'tipo',
        'companhia',
        'numero_voo',
        'localizador',
        'origem',
        'destino',
        'data_embarque',
        'horario_embarque',
        'horario_chegada',
        'classe',
        'assento',
        'bagagem_despachada',
        'peso_bagagem',
        'passageiros',
        'valor',
        'status',
        'observacoes',
    ];

    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';

    public function buscaPorContratacao(int $contratacaoId): array
    {
        return $this->where('contratacao_id', $contratacaoId)
            ->orderBy('data_embarque', 'ASC')
            ->orderBy('tipo', 'ASC')
            ->findAll();
    }
}
