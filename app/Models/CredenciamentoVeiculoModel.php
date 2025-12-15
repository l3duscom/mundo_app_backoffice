<?php

namespace App\Models;

use CodeIgniter\Model;

class CredenciamentoVeiculoModel extends Model
{
    protected $table            = 'credenciamento_veiculos';
    protected $returnType       = 'App\Entities\CredenciamentoVeiculo';
    protected $allowedFields    = [
        'credenciamento_id',
        'marca',
        'modelo',
        'cor',
        'placa',
    ];

    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $validationRules = [
        'credenciamento_id' => 'required|integer',
        'marca' => 'required|max_length[100]',
        'modelo' => 'required|max_length[100]',
        'cor' => 'required|max_length[50]',
        'placa' => 'required|max_length[10]',
    ];

    protected $validationMessages = [
        'marca' => ['required' => 'A marca do veículo é obrigatória.'],
        'modelo' => ['required' => 'O modelo do veículo é obrigatório.'],
        'cor' => ['required' => 'A cor do veículo é obrigatória.'],
        'placa' => ['required' => 'A placa do veículo é obrigatória.'],
    ];

    /**
     * Busca veículos por credenciamento
     */
    public function buscaPorCredenciamento(int $credenciamentoId): array
    {
        return $this->where('credenciamento_id', $credenciamentoId)->findAll();
    }

    /**
     * Conta veículos por credenciamento
     */
    public function contaPorCredenciamento(int $credenciamentoId): int
    {
        return $this->where('credenciamento_id', $credenciamentoId)->countAllResults();
    }

    /**
     * Verifica se pode adicionar mais veículos (limite: 1)
     */
    public function podeAdicionar(int $credenciamentoId): bool
    {
        return $this->contaPorCredenciamento($credenciamentoId) < 1;
    }
}
