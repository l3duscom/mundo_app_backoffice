<?php

namespace App\Models;

use CodeIgniter\Model;

class ClienteModel extends Model
{
    protected $table = 'clientes';
    protected $returnType = 'App\Entities\Cliente';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'usuario_id',
        'nome',
        'cpf',
        'telefone',
        'email',
        'cep',
        'endereco',
        'numero',
        'bairro',
        'cidade',
        'estado',
        'pj',
        'tipo_parceria',
        'area_atuacao',
        'observacoes'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'nome' => 'required|min_length[3]|max_length[125]',
        'email' => 'required|valid_email|max_length[230]|is_unique[clientes.email,id,{id}]', // Não pode ter espaços
        'email' => 'is_unique[usuarios.email,id,{id}]', // Também validamos se o e-mail informado não existe na tabela de usuários..... admin@admin.com

    ];

    protected $validationMessages = [];


    public function recuperaCliente(int $id)
    {

        $atributos = [
            'clientes.id',
            'clientes.usuario_id',
            'clientes.nome',
            'clientes.cpf',
            'clientes.email',
            'clientes.telefone',
            'clientes.created_at',
            'clientes.updated_at',
            'clientes.deleted_at',
            'grupos_usuarios.grupo_id',
            'clientes.cep',
            'clientes.endereco',
            'clientes.numero',
            'clientes.bairro',
            'clientes.cidade',
            'clientes.estado',
            'clientes.pj',
            'clientes.tipo_parceria',
            'clientes.area_atuacao',
            'clientes.observacoes',

        ];

        return $this->select($atributos)
            ->join('grupos_usuarios', 'grupos_usuarios.usuario_id = clientes.usuario_id')
            ->withDeleted(true)
            ->find($id);
    }

    public function recuperaClienteByEmail($email)
    {

        $atributos = [
            'clientes.id',
            'clientes.nome',
            'clientes.cpf',
            'clientes.email',
            'clientes.telefone',
            'clientes.created_at',
            'clientes.updated_at',
            'clientes.deleted_at',
            'grupos_usuarios.grupo_id'
        ];

        return $this->select($atributos)
            ->join('grupos_usuarios', 'grupos_usuarios.usuario_id = clientes.usuario_id')
            ->withDeleted(true)
            ->find($email);
    }
}
