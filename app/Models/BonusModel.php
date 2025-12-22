<?php

namespace App\Models;

use CodeIgniter\Model;

class BonusModel extends Model
{
    protected $table = 'bonus';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'ingresso_id',
        'user_id',
        'tipo_bonus',
        'instrucoes',
        'codigo',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Buscar todos os bônus de um usuário
     */
    public function getBonusPorUsuario(int $user_id)
    {
        return $this->where('user_id', $user_id)->findAll();
    }

    /**
     * Buscar bônus por ingresso
     */
    public function getBonusPorIngresso(int $ingresso_id)
    {
        return $this->where('ingresso_id', $ingresso_id)->findAll();
    }

    /**
     * Buscar bônus Cinemark por ingresso
     */
    public function getCinemarkPorIngresso(int $ingresso_id)
    {
        return $this->where('ingresso_id', $ingresso_id)
                    ->where('tipo_bonus', 'cinemark')
                    ->first();
    }

    /**
     * Buscar todos os bônus de um usuário indexados por ingresso_id
     */
    public function getBonusPorUsuarioIndexado(int $user_id): array
    {
        $bonus = $this->where('user_id', $user_id)->findAll();
        $resultado = [];
        
        foreach ($bonus as $b) {
            if (!isset($resultado[$b->ingresso_id])) {
                $resultado[$b->ingresso_id] = [];
            }
            $resultado[$b->ingresso_id][] = $b;
        }
        
        return $resultado;
    }
}
