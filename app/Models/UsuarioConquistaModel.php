<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\UsuarioConquistaEntity;

class UsuarioConquistaModel extends Model
{
    protected $table            = 'usuario_conquistas';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = UsuarioConquistaEntity::class;
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'conquista_id',
        'event_id',
        'user_id',
        'pontos',
        'admin',
        'status',
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
        'conquista_id' => 'required|is_natural_no_zero',
        'event_id'     => 'required|is_natural_no_zero',
        'user_id'      => 'required|is_natural_no_zero',
        'pontos'       => 'permit_empty|integer',
        'admin'        => 'permit_empty|in_list[0,1]',
        'status'       => 'permit_empty|string|max_length[50]|in_list[ATIVA,REVOGADA]',
    ];

    protected $validationMessages = [
        'conquista_id' => [
            'required'            => 'O campo conquista_id é obrigatório',
            'is_natural_no_zero'  => 'O campo conquista_id deve ser um número válido',
        ],
        'event_id' => [
            'required'            => 'O campo event_id é obrigatório',
            'is_natural_no_zero'  => 'O campo event_id deve ser um número válido',
        ],
        'user_id' => [
            'required'            => 'O campo user_id é obrigatório',
            'is_natural_no_zero'  => 'O campo user_id deve ser um número válido',
        ],
        'pontos' => [
            'integer'  => 'Os pontos devem ser um número inteiro',
        ],
        'admin' => [
            'in_list' => 'O campo admin deve ser 0 ou 1',
        ],
        'status' => [
            'in_list'  => 'O status deve ser ATIVA ou REVOGADA',
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
     * Previne edição de conquistas já atribuídas
     * 
     * @param array $data
     * @return array
     */
    protected function preventUpdate(array $data)
    {
        // Permite apenas atualização do status (para revogação)
        if (isset($data['data'])) {
            $allowedFields = ['status'];
            foreach ($data['data'] as $key => $value) {
                if (!in_array($key, $allowedFields)) {
                    unset($data['data'][$key]);
                }
            }
        }
        return $data;
    }

    /**
     * Verifica se usuário já possui a conquista
     * 
     * @param int $userId
     * @param int $conquistaId
     * @param int $eventId
     * @return bool
     */
    public function usuarioPossuiConquista(int $userId, int $conquistaId, int $eventId): bool
    {
        return $this->where([
            'user_id'      => $userId,
            'conquista_id' => $conquistaId,
            'event_id'     => $eventId,
            'status'       => 'ATIVA'
        ])->countAllResults() > 0;
    }

    /**
     * Busca conquistas do usuário
     * 
     * @param int $userId
     * @param int|null $eventId
     * @return array
     */
    public function getConquistasDoUsuario(int $userId, ?int $eventId = null): array
    {
        $builder = $this->select('usuario_conquistas.*, conquistas.nome_conquista, conquistas.nivel')
                        ->join('conquistas', 'conquistas.id = usuario_conquistas.conquista_id')
                        ->where('usuario_conquistas.user_id', $userId)
                        ->where('usuario_conquistas.status', 'ATIVA');

        if ($eventId) {
            $builder->where('usuario_conquistas.event_id', $eventId);
        }

        return $builder->orderBy('usuario_conquistas.created_at', 'DESC')
                       ->findAll();
    }

    /**
     * Busca total de pontos do usuário por evento
     * 
     * @param int $userId
     * @param int $eventId
     * @return int
     */
    public function getTotalPontosUsuarioEvento(int $userId, int $eventId): int
    {
        $result = $this->selectSum('pontos')
                       ->where('user_id', $userId)
                       ->where('event_id', $eventId)
                       ->where('status', 'ATIVA')
                       ->first();

        return (int) ($result['pontos'] ?? 0);
    }

    /**
     * Busca ranking de usuários por evento
     * 
     * @param int $eventId
     * @param int $limit
     * @return array
     */
    public function getRankingPorEvento(int $eventId, int $limit = 10): array
    {
        return $this->select('user_id, usuarios.nome, SUM(pontos) as total_pontos, COUNT(*) as total_conquistas')
                    ->join('usuarios', 'usuarios.id = usuario_conquistas.user_id')
                    ->where('usuario_conquistas.event_id', $eventId)
                    ->where('usuario_conquistas.status', 'ATIVA')
                    ->groupBy('user_id')
                    ->orderBy('total_pontos', 'DESC')
                    ->orderBy('total_conquistas', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
}

