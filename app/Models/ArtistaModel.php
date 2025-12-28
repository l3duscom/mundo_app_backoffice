<?php

namespace App\Models;

use CodeIgniter\Model;

class ArtistaModel extends Model
{
    protected $table                = 'artistas';
    protected $returnType           = 'App\Entities\Artista';
    protected $useSoftDeletes       = true;
    protected $allowedFields        = [
        'nome_artistico',
        'genero_musical',
        'biografia',
        'rider_tecnico',
        'foto',
        'nome_completo',
        'cpf',
        'rg',
        'data_nascimento',
        'nacionalidade',
        'passaporte',
        'passaporte_validade',
        'email',
        'telefone',
        'observacoes',
        'ativo',
    ];

    // Dates
    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';
    protected $deletedField         = 'deleted_at';

    // Validation
    protected $validationRules    = [
        'nome_artistico' => 'required|max_length[255]',
    ];

    protected $validationMessages = [
        'nome_artistico' => [
            'required' => 'O nome artístico é obrigatório.',
        ],
    ];

    /**
     * Busca artistas ativos
     */
    public function buscaAtivos(): array
    {
        return $this->where('ativo', 1)
            ->orderBy('nome_artistico', 'ASC')
            ->findAll();
    }

    /**
     * Busca com contatos
     */
    public function buscaComContatos(int $id)
    {
        $artista = $this->find($id);
        if ($artista) {
            $contatoModel = new ArtistaContatoModel();
            $artista->contatos = $contatoModel->buscaPorArtista($id);
        }
        return $artista;
    }
}
