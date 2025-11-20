<?php

namespace App\Models;

use CodeIgniter\Model;

class CronogramaItemModel extends Model
{
    protected $table                = 'cronograma_itens';
    protected $returnType           = 'App\Entities\CronogramaItemEntity';
    protected $useSoftDeletes       = true;
    protected $allowedFields        = [
        'cronograma_id',
        'nome_item',
        'data_hora_inicio',
        'data_hora_fim',
        'ativo',
        'status',
    ];

    // Dates
    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';
    protected $deletedField         = 'deleted_at';

    // Callbacks
    protected $allowCallbacks       = true;

    // Validation
    protected $validationRules      = [
        'cronograma_id'     => 'required|integer',
        'nome_item'         => 'required|min_length[3]|max_length[255]',
        'data_hora_inicio'  => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'data_hora_fim'     => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'ativo'             => 'in_list[0,1]',
        'status'            => 'in_list[AGUARDANDO,EM_ANDAMENTO,CONCLUIDO,CANCELADO]',
    ];

    protected $validationMessages   = [
        'cronograma_id' => [
            'required' => 'O campo Cronograma é obrigatório.',
            'integer'  => 'O campo Cronograma deve ser um número inteiro válido.',
        ],
        'nome_item' => [
            'required'   => 'O campo Nome do Item é obrigatório.',
            'min_length' => 'O campo Nome do Item precisa ter pelo menos 3 caracteres.',
            'max_length' => 'O campo Nome do Item não pode ser maior que 255 caracteres.',
        ],
        'data_hora_inicio' => [
            'valid_date' => 'O campo Data/Hora de Início deve ser uma data válida.',
        ],
        'data_hora_fim' => [
            'valid_date' => 'O campo Data/Hora de Fim deve ser uma data válida.',
        ],
        'ativo' => [
            'in_list' => 'O campo Ativo deve ser 0 ou 1.',
        ],
        'status' => [
            'in_list' => 'O campo Status deve ser AGUARDANDO, EM_ANDAMENTO, CONCLUIDO ou CANCELADO.',
        ],
    ];

    /**
     * Recupera todos os itens de um cronograma específico
     *
     * @param integer $cronograma_id
     * @return array|null
     */
    public function getItensByCronograma(int $cronograma_id)
    {
        return $this->where('cronograma_id', $cronograma_id)
                    ->orderBy('data_hora_inicio', 'ASC')
                    ->findAll();
    }

    /**
     * Recupera apenas os itens ativos de um cronograma
     *
     * @param integer $cronograma_id
     * @return array|null
     */
    public function getItensAtivosByCronograma(int $cronograma_id)
    {
        return $this->where('cronograma_id', $cronograma_id)
                    ->where('ativo', true)
                    ->orderBy('data_hora_inicio', 'ASC')
                    ->findAll();
    }

    /**
     * Recupera itens por status
     *
     * @param integer $cronograma_id
     * @param string $status
     * @return array|null
     */
    public function getItensByStatus(int $cronograma_id, string $status)
    {
        return $this->where('cronograma_id', $cronograma_id)
                    ->where('status', $status)
                    ->orderBy('data_hora_inicio', 'ASC')
                    ->findAll();
    }

    /**
     * Verifica se existe item para um cronograma
     *
     * @param integer $cronograma_id
     * @return boolean
     */
    public function existeItemParaCronograma(int $cronograma_id): bool
    {
        return $this->where('cronograma_id', $cronograma_id)->countAllResults() > 0;
    }

    /**
     * Recupera itens com informações do cronograma e evento
     *
     * @param integer $cronograma_id
     * @return array|null
     */
    public function getItensComDetalhes(int $cronograma_id)
    {
        return $this->select([
                'cronograma_itens.*',
                'cronograma.name as cronograma_nome',
                'cronograma.event_id',
                'eventos.nome as evento_nome'
            ])
            ->join('cronograma', 'cronograma.id = cronograma_itens.cronograma_id')
            ->join('eventos', 'eventos.id = cronograma.event_id')
            ->where('cronograma_itens.cronograma_id', $cronograma_id)
            ->orderBy('cronograma_itens.data_hora_inicio', 'ASC')
            ->findAll();
    }

    /**
     * Recupera próximos itens (a partir da data/hora atual)
     *
     * @param integer $cronograma_id
     * @param integer $limit
     * @return array|null
     */
    public function getProximosItens(int $cronograma_id, int $limit = 10)
    {
        $now = date('Y-m-d H:i:s');
        
        return $this->where('cronograma_id', $cronograma_id)
                    ->where('data_hora_inicio >=', $now)
                    ->where('ativo', true)
                    ->orderBy('data_hora_inicio', 'ASC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Atualiza status de múltiplos itens
     *
     * @param array $ids
     * @param string $status
     * @return boolean
     */
    public function updateStatusEmLote(array $ids, string $status): bool
    {
        if (empty($ids)) {
            return false;
        }

        return $this->whereIn('id', $ids)
                    ->set(['status' => $status])
                    ->update();
    }
}

