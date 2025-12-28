<?php

namespace App\Models;

use CodeIgniter\Model;

class AgenteModel extends Model
{
    protected $table                = 'agentes';
    protected $returnType           = 'App\Entities\Agente';
    protected $useSoftDeletes       = true;
    protected $allowedFields        = [
        'tipo',
        'nome',
        'nome_fantasia',
        'cpf',
        'cnpj',
        'email',
        'telefone',
        'whatsapp',
        'site',
        'cep',
        'endereco',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'banco',
        'agencia',
        'conta',
        'tipo_conta',
        'pix',
        'observacoes',
        'ativo',
    ];

    // Dates
    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';
    protected $deletedField         = 'deleted_at';

    // Validation
    protected $validationRules = [
        'nome' => 'required|min_length[2]|max_length[255]',
        'tipo' => 'required|in_list[agente,empresario,agencia,assessoria,produtor,tecnico,outro]',
    ];

    protected $validationMessages = [
        'nome' => [
            'required' => 'O nome é obrigatório',
            'min_length' => 'O nome deve ter pelo menos 2 caracteres',
        ],
    ];

    /**
     * Busca agentes ativos
     */
    public function buscaAtivos(): array
    {
        return $this->where('ativo', 1)
            ->orderBy('nome', 'ASC')
            ->findAll();
    }

    /**
     * Busca por tipo
     */
    public function buscaPorTipo(string $tipo): array
    {
        return $this->where('tipo', $tipo)
            ->where('ativo', 1)
            ->orderBy('nome', 'ASC')
            ->findAll();
    }

    /**
     * Busca agentes de um artista
     */
    public function buscaPorArtista(int $artistaId): array
    {
        return $this->select('agentes.*, artista_agentes.funcao, artista_agentes.principal')
            ->join('artista_agentes', 'artista_agentes.agente_id = agentes.id')
            ->where('artista_agentes.artista_id', $artistaId)
            ->orderBy('artista_agentes.principal', 'DESC')
            ->orderBy('agentes.nome', 'ASC')
            ->findAll();
    }

    /**
     * Busca artistas de um agente
     */
    public function buscaArtistasDoAgente(int $agenteId): array
    {
        $db = \Config\Database::connect();
        return $db->table('artistas')
            ->select('artistas.*, artista_agentes.funcao, artista_agentes.principal')
            ->join('artista_agentes', 'artista_agentes.artista_id = artistas.id')
            ->where('artista_agentes.agente_id', $agenteId)
            ->where('artistas.deleted_at IS NULL')
            ->orderBy('artistas.nome_artistico', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Pesquisa por nome
     */
    public function pesquisar(string $termo): array
    {
        return $this->like('nome', $termo)
            ->orLike('nome_fantasia', $termo)
            ->orLike('email', $termo)
            ->where('ativo', 1)
            ->orderBy('nome', 'ASC')
            ->findAll();
    }
}
