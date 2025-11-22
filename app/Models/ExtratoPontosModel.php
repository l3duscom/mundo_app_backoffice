<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\ExtratoPontosEntity;

class ExtratoPontosModel extends Model
{
    protected $table            = 'extrato_pontos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = ExtratoPontosEntity::class;
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'event_id',
        'tipo',
        'pontos',
        'saldo_anterior',
        'saldo_atual',
        'descricao',
        'referencia_tipo',
        'referencia_id',
        'atribuido_por',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'user_id'        => 'required|is_natural_no_zero',
        'event_id'       => 'permit_empty|is_natural_no_zero',
        'tipo'           => 'required|string|max_length[50]',
        'pontos'         => 'required|integer',
        'saldo_anterior' => 'permit_empty|integer',
        'saldo_atual'    => 'permit_empty|integer',
    ];

    protected $validationMessages = [
        'user_id' => [
            'required'            => 'O campo user_id é obrigatório',
            'is_natural_no_zero'  => 'O campo user_id deve ser um número válido',
        ],
        'tipo' => [
            'required'   => 'O tipo é obrigatório',
            'max_length' => 'O tipo não pode ter mais de 50 caracteres',
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
    protected $beforeUpdate   = ['preventUpdate'];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Previne edição do extrato
     * 
     * @param array $data
     * @return array
     */
    protected function preventUpdate(array $data)
    {
        // Extrato é imutável - não permite updates
        $data['data'] = [];
        return $data;
    }

    /**
     * Busca extrato do usuário
     * 
     * @param int $userId
     * @param int|null $eventId
     * @param int|null $limit
     * @return array
     */
    public function getExtratoUsuario(int $userId, ?int $eventId = null, ?int $limit = null): array
    {
        $builder = $this->where('user_id', $userId);

        if ($eventId) {
            $builder->where('event_id', $eventId);
        }

        if ($limit) {
            $builder->limit($limit);
        }

        return $builder->orderBy('created_at', 'DESC')
                       ->findAll();
    }

    /**
     * Busca total de pontos por tipo
     * 
     * @param int $userId
     * @param string $tipo
     * @param int|null $eventId
     * @return int
     */
    public function getTotalPontosPorTipo(int $userId, string $tipo, ?int $eventId = null): int
    {
        $builder = $this->selectSum('pontos')
                        ->where('user_id', $userId)
                        ->where('tipo', $tipo);

        if ($eventId) {
            $builder->where('event_id', $eventId);
        }

        $result = $builder->first();
        return (int) ($result['pontos'] ?? 0);
    }

    /**
     * Busca último saldo do usuário
     * 
     * @param int $userId
     * @return int
     */
    public function getUltimoSaldo(int $userId): int
    {
        $ultimoRegistro = $this->where('user_id', $userId)
                               ->orderBy('created_at', 'DESC')
                               ->first();

        return $ultimoRegistro ? (int) $ultimoRegistro->saldo_atual : 0;
    }
}

