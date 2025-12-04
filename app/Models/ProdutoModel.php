<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\ProdutoEntity;

class ProdutoModel extends Model
{
    protected $table            = 'produtos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = ProdutoEntity::class;
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'event_id',
        'imagem',
        'categoria',
        'nome',
        'preco',
        'pontos',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'event_id'  => 'required|is_natural_no_zero',
        'imagem'    => 'permit_empty|string|max_length[500]',
        'categoria' => 'required|string|max_length[100]',
        'nome'      => 'required|string|max_length[255]',
        'preco'     => 'required|decimal',
        'pontos'    => 'required|integer',
    ];

    protected $validationMessages = [
        'event_id' => [
            'required'           => 'O campo event_id é obrigatório',
            'is_natural_no_zero' => 'O campo event_id deve ser um número válido',
        ],
        'imagem' => [
            'max_length' => 'A URL da imagem não pode ter mais de 500 caracteres',
        ],
        'categoria' => [
            'required'   => 'A categoria é obrigatória',
            'max_length' => 'A categoria não pode ter mais de 100 caracteres',
        ],
        'nome' => [
            'required'   => 'O nome do produto é obrigatório',
            'max_length' => 'O nome do produto não pode ter mais de 255 caracteres',
        ],
        'preco' => [
            'required' => 'O preço é obrigatório',
            'decimal'  => 'O preço deve ser um valor decimal válido',
        ],
        'pontos' => [
            'required' => 'Os pontos são obrigatórios',
            'integer'  => 'Os pontos devem ser um número inteiro',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Busca produtos por evento
     * 
     * @param int $eventId
     * @return array
     */
    public function getProdutosPorEvento(int $eventId): array
    {
        return $this->where('event_id', $eventId)
                    ->orderBy('categoria', 'ASC')
                    ->orderBy('nome', 'ASC')
                    ->findAll();
    }

    /**
     * Busca produtos por categoria
     * 
     * @param int $eventId
     * @param string $categoria
     * @return array
     */
    public function getProdutosPorCategoria(int $eventId, string $categoria): array
    {
        return $this->where('event_id', $eventId)
                    ->where('categoria', $categoria)
                    ->orderBy('nome', 'ASC')
                    ->findAll();
    }

    /**
     * Lista categorias disponíveis por evento
     * 
     * @param int $eventId
     * @return array
     */
    public function getCategoriasPorEvento(int $eventId): array
    {
        return $this->select('categoria')
                    ->where('event_id', $eventId)
                    ->groupBy('categoria')
                    ->orderBy('categoria', 'ASC')
                    ->findAll();
    }

    /**
     * Busca produtos com pontos mínimos
     * 
     * @param int $eventId
     * @param int $pontosMinimos
     * @return array
     */
    public function getProdutosComPontosMinimos(int $eventId, int $pontosMinimos): array
    {
        return $this->where('event_id', $eventId)
                    ->where('pontos <=', $pontosMinimos)
                    ->orderBy('pontos', 'DESC')
                    ->findAll();
    }

    /**
     * Busca produtos por faixa de preço
     * 
     * @param int $eventId
     * @param float $precoMin
     * @param float $precoMax
     * @return array
     */
    public function getProdutosPorFaixaPreco(int $eventId, float $precoMin, float $precoMax): array
    {
        return $this->where('event_id', $eventId)
                    ->where('preco >=', $precoMin)
                    ->where('preco <=', $precoMax)
                    ->orderBy('preco', 'ASC')
                    ->findAll();
    }
}

