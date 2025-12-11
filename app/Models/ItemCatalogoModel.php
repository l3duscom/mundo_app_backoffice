<?php

namespace App\Models;

use CodeIgniter\Model;

class ItemCatalogoModel extends Model
{
    protected $table                = 'itens_catalogo';
    protected $returnType           = 'App\Entities\ItemCatalogo';
    protected $useSoftDeletes       = true;
    protected $allowedFields        = [
        'event_id',
        'nome',
        'tipo',
        'descricao',
        'metragem',
        'valor',
        'ativo',
    ];

    // Dates
    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';
    protected $deletedField         = 'deleted_at';

    // Validation
    protected $validationRules    = [
        'event_id' => 'required|integer',
        'nome'     => 'required|max_length[100]',
        'tipo'     => 'required|max_length[50]',
        'valor'    => 'required|numeric',
    ];

    protected $validationMessages = [
        'event_id' => [
            'required' => 'Selecione o evento.',
        ],
        'nome' => [
            'required' => 'Informe o nome do item.',
        ],
        'tipo' => [
            'required' => 'Selecione o tipo do item.',
        ],
        'valor' => [
            'required' => 'Informe o valor do item.',
        ],
    ];

    /**
     * Busca itens ativos
     *
     * @param int|null $eventId
     * @return array
     */
    public function buscaAtivos(?int $eventId = null): array
    {
        $builder = $this->where('ativo', 1);
        
        if ($eventId) {
            $builder->where('event_id', $eventId);
        }
        
        return $builder->orderBy('tipo', 'ASC')
            ->orderBy('nome', 'ASC')
            ->findAll();
    }

    /**
     * Busca itens por evento
     *
     * @param int $eventId
     * @return array
     */
    public function buscaPorEvento(int $eventId): array
    {
        return $this->where('event_id', $eventId)
            ->where('ativo', 1)
            ->orderBy('tipo', 'ASC')
            ->orderBy('nome', 'ASC')
            ->findAll();
    }

    /**
     * Busca itens por tipo
     *
     * @param string $tipo
     * @param int|null $eventId
     * @return array
     */
    public function buscaPorTipo(string $tipo, ?int $eventId = null): array
    {
        $builder = $this->where('tipo', $tipo)
            ->where('ativo', 1);
            
        if ($eventId) {
            $builder->where('event_id', $eventId);
        }
            
        return $builder->orderBy('nome', 'ASC')
            ->findAll();
    }

    /**
     * Retorna os tipos disponíveis
     *
     * @return array
     */
    public static function getTipos(): array
    {
        return [
            'Espaço Comercial',
            'Artist Alley',
            'Vila dos Artesãos',
            'Espaço Medieval',
            'Indie',
            'Games',
            'Espaço Temático',
            'Estúdio Tattoo',
            'Food Park',
            'Cota',
            'Patrocínio',
            'Parceiros',
            'Patrocinadores',
            'Energia Elétrica',
            'Internet',
            'Credenciamento',
            'Outros',
        ];
    }

    /**
     * Busca itens para select2 (AJAX)
     *
     * @param string|null $termo
     * @param int|null $eventId
     * @return array
     */
    public function buscaParaSelect(?string $termo = null, ?int $eventId = null): array
    {
        $builder = $this->select('id, event_id, nome, tipo, metragem, valor')
            ->where('ativo', 1);

        if ($eventId) {
            $builder->where('event_id', $eventId);
        }

        if (!empty($termo)) {
            $builder->groupStart()
                ->like('nome', $termo)
                ->orLike('tipo', $termo)
                ->orLike('descricao', $termo)
                ->groupEnd();
        }

        return $builder->orderBy('tipo', 'ASC')
            ->orderBy('nome', 'ASC')
            ->findAll();
    }
}

