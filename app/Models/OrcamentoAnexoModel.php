<?php

namespace App\Models;

use CodeIgniter\Model;

class OrcamentoAnexoModel extends Model
{
    protected $table                = 'orcamento_anexos';
    protected $returnType           = 'App\Entities\OrcamentoAnexo';
    protected $useSoftDeletes       = false;
    protected $allowedFields        = [
        'orcamento_id',
        'nome_arquivo',
        'arquivo',
        'tipo',
        'tamanho',
    ];

    // Dates
    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = false;

    /**
     * Busca anexos de um orçamento
     */
    public function buscarPorOrcamento(int $orcamentoId): array
    {
        return $this->where('orcamento_id', $orcamentoId)
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    /**
     * Salva arquivo e registra no banco
     */
    public function salvarAnexo(int $orcamentoId, $file): ?int
    {
        if (!$file->isValid() || $file->hasMoved()) {
            return null;
        }

        $newName = $file->getRandomName();
        $file->move(WRITEPATH . 'uploads/orcamentos/anexos', $newName);

        $data = [
            'orcamento_id' => $orcamentoId,
            'nome_arquivo' => $file->getClientName(),
            'arquivo' => $newName,
            'tipo' => $file->getClientMimeType(),
            'tamanho' => $file->getSize(),
        ];

        $this->insert($data);
        return $this->getInsertID();
    }

    /**
     * Remove anexo do banco e do disco
     */
    public function removerAnexo(int $anexoId): bool
    {
        $anexo = $this->find($anexoId);
        if (!$anexo) {
            return false;
        }

        $path = WRITEPATH . 'uploads/orcamentos/anexos/' . $anexo->arquivo;
        if (is_file($path)) {
            unlink($path);
        }

        return $this->delete($anexoId);
    }
}
