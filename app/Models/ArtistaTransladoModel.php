<?php

namespace App\Models;

use CodeIgniter\Model;

class ArtistaTransladoModel extends Model
{
    protected $table                = 'artista_translados';
    protected $returnType           = 'object';
    protected $useSoftDeletes       = false;
    protected $allowedFields        = [
        'contratacao_id',
        'tipo',
        'data_translado',
        'origem',
        'destino',
        'veiculo',
        'motorista',
        'telefone_motorista',
        'valor',
        'status',
        'observacoes',
    ];

    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';

    public const TIPOS = [
        'aeroporto_hotel' => 'Aeroporto → Hotel',
        'hotel_evento' => 'Hotel → Evento',
        'evento_hotel' => 'Evento → Hotel',
        'hotel_aeroporto' => 'Hotel → Aeroporto',
        'outro' => 'Outro',
    ];

    public function buscaPorContratacao(int $contratacaoId): array
    {
        return $this->where('contratacao_id', $contratacaoId)
            ->orderBy('data_translado', 'ASC')
            ->findAll();
    }
}
