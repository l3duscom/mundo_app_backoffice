<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\ConquistaEntity;

class ConquistaModel extends Model
{
    protected $table            = 'conquistas';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = ConquistaEntity::class;
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'event_id',
        'codigo',
        'nome_conquista',
        'descricao',
        'pontos',
        'nivel',
        'status',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'event_id'       => 'required|is_natural_no_zero',
        'codigo'         => 'permit_empty|string|exact_length[8]|is_unique[conquistas.codigo,id,{id}]',
        'nome_conquista' => 'required|string|max_length[255]',
        'descricao'      => 'permit_empty|string',
        'pontos'         => 'required|integer',
        'nivel'          => 'required|string|max_length[50]',
        'status'         => 'required|string|max_length[50]|in_list[ATIVA,INATIVA,BLOQUEADA]',
    ];

    protected $validationMessages = [
        'event_id' => [
            'required'            => 'O campo event_id é obrigatório',
            'is_natural_no_zero'  => 'O campo event_id deve ser um número válido',
        ],
        'codigo' => [
            'exact_length' => 'O código deve ter exatamente 8 caracteres',
            'is_unique'    => 'Este código já está em uso',
        ],
        'nome_conquista' => [
            'required'   => 'O nome da conquista é obrigatório',
            'max_length' => 'O nome da conquista não pode ter mais de 255 caracteres',
        ],
        'pontos' => [
            'required' => 'Os pontos são obrigatórios',
            'integer'  => 'Os pontos devem ser um número inteiro',
        ],
        'nivel' => [
            'required'   => 'O nível é obrigatório',
            'max_length' => 'O nível não pode ter mais de 50 caracteres',
        ],
        'status' => [
            'required' => 'O status é obrigatório',
            'in_list'  => 'O status deve ser ATIVA, INATIVA ou BLOQUEADA',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['gerarCodigoAntesDeInserir'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Callback: Gera código automaticamente antes de inserir
     * 
     * @param array $data
     * @return array
     */
    protected function gerarCodigoAntesDeInserir(array $data)
    {
        // Se o código não foi fornecido, gera automaticamente
        if (empty($data['data']['codigo'])) {
            $data['data']['codigo'] = $this->gerarCodigoUnico();
        }
        
        return $data;
    }

    /**
     * Gera um código único de 8 caracteres
     * 
     * @return string
     */
    public function gerarCodigoUnico(): string
    {
        $tentativas = 0;
        $maxTentativas = 50;
        
        do {
            // Gera código aleatório de 8 caracteres (letras maiúsculas e números)
            $codigo = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            
            // Verifica se já existe
            $existe = $this->where('codigo', $codigo)->countAllResults() > 0;
            
            $tentativas++;
            
            if ($tentativas >= $maxTentativas) {
                throw new \RuntimeException('Não foi possível gerar um código único após ' . $maxTentativas . ' tentativas');
            }
            
        } while ($existe);
        
        return $codigo;
    }

    /**
     * Busca conquistas por evento
     * 
     * @param int $eventId
     * @return array
     */
    public function getConquistasPorEvento(int $eventId): array
    {
        return $this->where('event_id', $eventId)
                    ->where('status', 'ATIVA')
                    ->orderBy('pontos', 'ASC')
                    ->findAll();
    }

    /**
     * Busca conquistas por nível
     * 
     * @param string $nivel
     * @return array
     */
    public function getConquistasPorNivel(string $nivel): array
    {
        return $this->where('nivel', $nivel)
                    ->where('status', 'ATIVA')
                    ->findAll();
    }

    /**
     * Busca conquistas com pontos mínimos
     * 
     * @param int $pontosMinimos
     * @return array
     */
    public function getConquistasComPontosMinimos(int $pontosMinimos): array
    {
        return $this->where('pontos >=', $pontosMinimos)
                    ->where('status', 'ATIVA')
                    ->orderBy('pontos', 'DESC')
                    ->findAll();
    }
}

