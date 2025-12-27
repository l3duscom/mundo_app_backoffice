<?php

namespace App\Models;

use CodeIgniter\Model;

class FornecedorModel extends Model
{
	protected $table                = 'fornecedores';
	protected $returnType           = 'App\Entities\Fornecedor';
	protected $useSoftDeletes       = true;
	protected $allowedFields        = [
		'razao',
		'cnpj',
		'ie',
		'telefone',
		'email',
		'categoria_id',
		'nome_contato',
		'telefone_contato',
		'cep',
		'endereco',
		'numero',
		'bairro',
		'cidade',
		'estado',
		'banco',
		'agencia',
		'conta',
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
	protected $validationRules    = [
		'razao'              => 'required|max_length[230]|is_unique[fornecedores.razao,id,{id}]',
		'cnpj'               => 'required|validaCNPJ|max_length[25]|is_unique[fornecedores.cnpj,id,{id}]',
		'ie'           	     => 'permit_empty|max_length[25]',
		'telefone'           => 'required|max_length[18]',
		'email'              => 'permit_empty|valid_email|max_length[255]',
		'categoria_id'       => 'permit_empty|is_natural',
		'nome_contato'       => 'permit_empty|max_length[100]',
		'telefone_contato'   => 'permit_empty|max_length[20]',
		'cep'                => 'permit_empty|max_length[10]',
		'endereco'           => 'permit_empty|max_length[255]',
		'numero'             => 'permit_empty|max_length[45]',
		'bairro'             => 'permit_empty|max_length[100]',
		'cidade'             => 'permit_empty|max_length[100]',
		'estado'             => 'permit_empty|max_length[2]',
		'banco'              => 'permit_empty|max_length[50]',
		'agencia'            => 'permit_empty|max_length[20]',
		'conta'              => 'permit_empty|max_length[30]',
		'pix'                => 'permit_empty|max_length[100]',
		'observacoes'        => 'permit_empty',
	];

	protected $validationMessages = [];
}
