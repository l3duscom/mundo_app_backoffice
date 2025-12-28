<?php

namespace App\Models;

use CodeIgniter\Model;

class ArtistaContratacaoAnexoModel extends Model
{
    protected $table                = 'artista_contratacao_anexos';
    protected $returnType           = 'App\Entities\ArtistaContratacaoAnexo';
    protected $useSoftDeletes       = false;
    protected $allowedFields        = [
        'contratacao_id',
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
     * Busca anexos de uma contratação
     */
    public function buscarPorContratacao(int $contratacaoId): array
    {
        return $this->where('contratacao_id', $contratacaoId)
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    /**
     * Salva arquivo e registra no banco
     */
    public function salvarAnexo(int $contratacaoId, $file): ?int
    {
        if (!$file->isValid() || $file->hasMoved()) {
            return null;
        }

        $newName = $file->getRandomName();
        $file->move(WRITEPATH . 'uploads/artistas/contratacoes/anexos', $newName);

        $data = [
            'contratacao_id' => $contratacaoId,
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

        $path = WRITEPATH . 'uploads/artistas/contratacoes/anexos/' . $anexo->arquivo;
        if (is_file($path)) {
            unlink($path);
        }

        return $this->delete($anexoId);
    }
}
