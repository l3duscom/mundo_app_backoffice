<?php

namespace App\Models;

use CodeIgniter\Model;

class FornecedorCategoriaModel extends Model
{
    protected $table                = 'fornecedor_categorias';
    protected $primaryKey           = 'id';
    protected $returnType           = 'object';
    protected $useSoftDeletes       = false;
    protected $allowedFields        = [
        'nome',
        'cor',
        'ativo',
    ];

    // Dates
    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';

    // Validation
    protected $validationRules    = [
        'nome' => 'required|max_length[100]|is_unique[fornecedor_categorias.nome,id,{id}]',
        'cor'  => 'permit_empty|max_length[20]',
    ];

    protected $validationMessages = [
        'nome' => [
            'required' => 'O nome da categoria é obrigatório',
            'is_unique' => 'Esta categoria já existe',
        ],
    ];

    /**
     * Retorna todas as categorias ativas ordenadas por nome
     */
    public function getCategoriasAtivas(): array
    {
        return $this->where('ativo', 1)
                    ->orderBy('nome', 'ASC')
                    ->findAll();
    }

    /**
     * Retorna categorias para dropdown
     */
    public function getCategoriasDropdown(): array
    {
        $categorias = $this->getCategoriasAtivas();
        $dropdown = [];
        
        foreach ($categorias as $cat) {
            $dropdown[$cat->id] = $cat->nome;
        }
        
        return $dropdown;
    }
}
