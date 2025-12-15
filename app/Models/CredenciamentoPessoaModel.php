<?php

namespace App\Models;

use CodeIgniter\Model;

class CredenciamentoPessoaModel extends Model
{
    protected $table            = 'credenciamento_pessoas';
    protected $returnType       = 'App\Entities\CredenciamentoPessoa';
    protected $allowedFields    = [
        'credenciamento_id',
        'tipo',
        'nome',
        'rg',
        'cpf',
        'whatsapp',
        'status',
        'motivo_rejeicao',
    ];

    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $validationRules = [
        'credenciamento_id' => 'required|integer',
        'tipo' => 'required|in_list[responsavel,funcionario,suplente]',
        'nome' => 'required|max_length[200]',
        'rg' => 'required|max_length[20]',
        'cpf' => 'required|max_length[14]',
        'whatsapp' => 'required|max_length[20]',
    ];

    protected $validationMessages = [
        'nome' => ['required' => 'O nome é obrigatório.'],
        'rg' => ['required' => 'O RG é obrigatório.'],
        'cpf' => ['required' => 'O CPF é obrigatório.'],
        'whatsapp' => ['required' => 'O WhatsApp é obrigatório.'],
    ];

    /**
     * Busca pessoas por credenciamento
     */
    public function buscaPorCredenciamento(int $credenciamentoId): array
    {
        return $this->where('credenciamento_id', $credenciamentoId)
            ->orderBy("FIELD(tipo, 'responsavel', 'funcionario', 'suplente')")
            ->findAll();
    }

    /**
     * Busca responsável do credenciamento
     */
    public function buscaResponsavel(int $credenciamentoId)
    {
        return $this->where('credenciamento_id', $credenciamentoId)
            ->where('tipo', 'responsavel')
            ->first();
    }

    /**
     * Busca funcionários do credenciamento
     */
    public function buscaFuncionarios(int $credenciamentoId): array
    {
        return $this->where('credenciamento_id', $credenciamentoId)
            ->where('tipo', 'funcionario')
            ->findAll();
    }

    /**
     * Busca suplentes do credenciamento
     */
    public function buscaSuplentes(int $credenciamentoId): array
    {
        return $this->where('credenciamento_id', $credenciamentoId)
            ->where('tipo', 'suplente')
            ->findAll();
    }

    /**
     * Conta pessoas por tipo e credenciamento
     */
    public function contaPorTipo(int $credenciamentoId, string $tipo): int
    {
        return $this->where('credenciamento_id', $credenciamentoId)
            ->where('tipo', $tipo)
            ->countAllResults();
    }

    /**
     * Verifica se já tem responsável
     */
    public function temResponsavel(int $credenciamentoId): bool
    {
        return $this->contaPorTipo($credenciamentoId, 'responsavel') > 0;
    }

    /**
     * Verifica se pode adicionar mais pessoas do tipo
     */
    public function podeAdicionar(int $credenciamentoId, string $tipo, int $limite): bool
    {
        if ($tipo === 'responsavel') {
            return !$this->temResponsavel($credenciamentoId);
        }
        
        return $this->contaPorTipo($credenciamentoId, $tipo) < $limite;
    }

    /**
     * Aprovar pessoa
     */
    public function aprovar(int $id): bool
    {
        return $this->update($id, [
            'status' => 'aprovado',
            'motivo_rejeicao' => null,
        ]);
    }

    /**
     * Rejeitar pessoa
     */
    public function rejeitar(int $id, ?string $motivo = null): bool
    {
        return $this->update($id, [
            'status' => 'rejeitado',
            'motivo_rejeicao' => $motivo,
        ]);
    }

    /**
     * Aprovar todas as pessoas do credenciamento
     */
    public function aprovarTodas(int $credenciamentoId): bool
    {
        return $this->where('credenciamento_id', $credenciamentoId)
            ->set(['status' => 'aprovado', 'motivo_rejeicao' => null])
            ->update();
    }

    /**
     * Resetar status de todas as pessoas para pendente
     */
    public function resetarStatus(int $credenciamentoId): bool
    {
        return $this->where('credenciamento_id', $credenciamentoId)
            ->set(['status' => 'pendente', 'motivo_rejeicao' => null])
            ->update();
    }

    /**
     * Verifica se todas as pessoas estão aprovadas
     */
    public function todasAprovadas(int $credenciamentoId): bool
    {
        $total = $this->where('credenciamento_id', $credenciamentoId)->countAllResults();
        $aprovadas = $this->where('credenciamento_id', $credenciamentoId)
            ->where('status', 'aprovado')
            ->countAllResults();
        
        return $total > 0 && $total === $aprovadas;
    }

    /**
     * Conta por status
     */
    public function contaPorStatus(int $credenciamentoId, string $status): int
    {
        return $this->where('credenciamento_id', $credenciamentoId)
            ->where('status', $status)
            ->countAllResults();
    }
}
