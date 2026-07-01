<?php

namespace App\Models;

use CodeIgniter\Model;

class CredencialModel extends Model
{
    protected $table = 'credenciais';
    protected $returnType = 'App\Entities\Credencial';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'ingresso_id',
        'pedido_id',
        'codigo',
        'acessos',
        'ativo',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'codigo' => 'required',

    ];

    protected $validationMessages = [];
}
