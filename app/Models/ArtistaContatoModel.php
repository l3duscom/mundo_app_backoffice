<?php

namespace App\Models;

use CodeIgniter\Model;

class ArtistaContatoModel extends Model
{
    protected $table                = 'artista_contatos';
    protected $returnType           = 'object';
    protected $useSoftDeletes       = false;
    protected $allowedFields        = [
        'artista_id',
        'tipo',
        'nome',
        'telefone',
        'email',
        'observacoes',
    ];

    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';

    // Tipos de contato
    public const TIPOS = [
        'agente' => 'Agente',
        'empresario' => 'Empresário',
        'assessoria' => 'Assessoria',
        'tecnico' => 'Técnico',
        'outro' => 'Outro',
    ];

    /**
     * Busca contatos de um artista
     */
    public function buscaPorArtista(int $artistaId): array
    {
        return $this->where('artista_id', $artistaId)
            ->orderBy('tipo', 'ASC')
            ->findAll();
    }
}
