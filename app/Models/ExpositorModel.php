<?php

namespace App\Models;

use CodeIgniter\Model;

class ExpositorModel extends Model
{
    protected $table                = 'expositores';
    protected $returnType           = 'App\Entities\Expositor';
    protected $useSoftDeletes       = true;
    protected $allowedFields        = [
        'tipo_pessoa',
        'nome',
        'nome_fantasia',
        'documento',
        'ie',
        'email',
        'telefone',
        'celular',
        'cep',
        'endereco',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'responsavel',
        'responsavel_telefone',
        'tipo_expositor',
        'segmento',
        'observacoes',
        'ativo',
        'asaas_customer_id',
    ];

    // Dates
    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';
    protected $deletedField         = 'deleted_at';


    // Validation
    protected $validationRules    = [
        'nome'               => 'required|min_length[3]|max_length[255]',
        'documento'          => 'required|max_length[20]|is_unique[expositores.documento,id,{id}]',
        'email'              => 'required|valid_email|max_length[255]|is_unique[expositores.email,id,{id}]',
        'telefone'           => 'required|max_length[20]',
    ];

    protected $validationMessages = [
        'nome' => [
            'required' => 'O campo Nome/Razão Social é obrigatório.',
            'min_length' => 'O campo Nome/Razão Social deve ter pelo menos 3 caracteres.',
            'max_length' => 'O campo Nome/Razão Social não pode exceder 255 caracteres.',
        ],
        'documento' => [
            'required' => 'O campo CPF/CNPJ é obrigatório.',
            'is_unique' => 'Este CPF/CNPJ já está cadastrado.',
        ],
        'email' => [
            'required' => 'O campo E-mail é obrigatório.',
            'valid_email' => 'Por favor, informe um e-mail válido.',
            'is_unique' => 'Este e-mail já está cadastrado.',
        ],
        'telefone' => [
            'required' => 'O campo Telefone é obrigatório.',
        ],
    ];

    /**
     * Busca expositores com filtro de tipo de pessoa
     *
     * @param string|null $tipoPessoa
     * @return array
     */
    public function buscaExpositores(?string $tipoPessoa = null): array
    {
        $atributos = [
            'id',
            'tipo_pessoa',
            'nome',
            'nome_fantasia',
            'documento',
            'email',
            'telefone',
            'segmento',
            'ativo',
            'deleted_at'
        ];

        $builder = $this->select($atributos)->withDeleted(true);

        if ($tipoPessoa !== null) {
            $builder->where('tipo_pessoa', $tipoPessoa);
        }

        return $builder->orderBy('id', 'DESC')->findAll();
    }

    /**
     * Busca expositor por documento (CPF ou CNPJ)
     *
     * @param string $documento
     * @return object|null
     */
    public function buscaPorDocumento(string $documento): ?object
    {
        // Remove formatação do documento
        $documento = preg_replace('/[^0-9]/', '', $documento);

        return $this->where('documento', $documento)->withDeleted(true)->first();
    }

    /**
     * Busca expositor por e-mail
     *
     * @param string $email
     * @return object|null
     */
    public function buscaPorEmail(string $email): ?object
    {
        return $this->where('email', $email)->withDeleted(true)->first();
    }

    /**
     * Retorna os segmentos distintos cadastrados
     *
     * @return array
     */
    public function getSegmentosDisponiveis(): array
    {
        return $this->distinct()
            ->select('segmento')
            ->where('segmento IS NOT NULL')
            ->where('segmento !=', '')
            ->findAll();
    }
}

