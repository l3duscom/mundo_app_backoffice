<?php

namespace App\Models;

use CodeIgniter\Model;

class PlanoModel extends Model
{
    protected $table                = 'planos';
    protected $returnType           = 'App\Entities\Plano';
    protected $useSoftDeletes       = true;
    protected $allowedFields        = [
        'nome',
        'slug',
        'descricao',
        'preco',
        'ciclo',
        'beneficios',
        'ativo',
    ];

    // Dates
    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';
    protected $deletedField         = 'deleted_at';

    // Validation
    protected $validationRules = [
        'nome' => 'required|min_length[3]|max_length[100]',
        'slug' => 'required|min_length[3]|max_length[100]',
        'preco' => 'required|numeric|greater_than_equal_to[0]',
        'ciclo' => 'required|in_list[MONTHLY,YEARLY]',
    ];

    protected $validationMessages = [
        'nome' => [
            'required' => 'O nome é obrigatório',
            'min_length' => 'O nome deve ter pelo menos 3 caracteres',
        ],
        'slug' => [
            'required' => 'O slug é obrigatório',
        ],
        'preco' => [
            'required' => 'O preço é obrigatório',
            'numeric' => 'O preço deve ser um valor numérico',
        ],
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['generateSlug'];
    protected $beforeUpdate = ['generateSlug'];

    /**
     * Gera slug automaticamente a partir do nome
     */
    protected function generateSlug(array $data): array
    {
        if (isset($data['data']['nome']) && empty($data['data']['slug'])) {
            $slug = url_title($data['data']['nome'], '-', true);
            $data['data']['slug'] = $this->getUniqueSlug($slug, $data['data']['id'] ?? null);
        }
        return $data;
    }

    /**
     * Garante slug único
     */
    private function getUniqueSlug(string $slug, ?int $excludeId = null): string
    {
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExiste($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Busca planos ativos
     */
    public function buscaAtivos(): array
    {
        return $this->where('ativo', 1)
            ->orderBy('preco', 'ASC')
            ->findAll();
    }

    /**
     * Busca plano por slug
     */
    public function buscaPorSlug(string $slug)
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Verifica se o slug já existe
     */
    public function slugExiste(string $slug, ?int $excluirId = null): bool
    {
        $builder = $this->where('slug', $slug);

        if ($excluirId) {
            $builder->where('id !=', $excluirId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Busca todos os planos com contagem de assinaturas
     */
    public function buscaComContagem(): array
    {
        return $this->select('planos.*, COUNT(assinaturas.id) as total_assinaturas')
            ->join('assinaturas', 'assinaturas.plano_id = planos.id AND assinaturas.deleted_at IS NULL', 'left')
            ->groupBy('planos.id')
            ->orderBy('planos.nome', 'ASC')
            ->findAll();
    }

    /**
     * Conta assinaturas ativas de um plano
     */
    public function contaAssinaturasAtivas(int $planoId): int
    {
        $db = \Config\Database::connect();
        return $db->table('assinaturas')
            ->where('plano_id', $planoId)
            ->where('status', 'ACTIVE')
            ->where('deleted_at IS NULL')
            ->countAllResults();
    }
}
