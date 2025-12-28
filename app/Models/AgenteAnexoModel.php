<?php

namespace App\Models;

use CodeIgniter\Model;

class AgenteAnexoModel extends Model
{
    protected $table                = 'agente_anexos';
    protected $returnType           = 'App\Entities\AgenteAnexo';
    protected $useSoftDeletes       = false;
    protected $allowedFields        = [
        'agente_id',
        'nome_arquivo',
        'arquivo',
        'tipo',
        'tamanho',
        'descricao',
    ];

    // Dates
    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = false;

    /**
     * Busca anexos de um agente
     */
    public function buscarPorAgente(int $agenteId): array
    {
        return $this->where('agente_id', $agenteId)
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    /**
     * Salva arquivo e registra no banco
     */
    public function salvarAnexo(int $agenteId, $file, ?string $descricao = null): ?int
    {
        if (!$file->isValid() || $file->hasMoved()) {
            return null;
        }

        $newName = $file->getRandomName();
        $file->move(WRITEPATH . 'uploads/agentes/anexos', $newName);

        $data = [
            'agente_id' => $agenteId,
            'nome_arquivo' => $file->getClientName(),
            'arquivo' => $newName,
            'tipo' => $file->getClientMimeType(),
            'tamanho' => $file->getSize(),
            'descricao' => $descricao,
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

        $path = WRITEPATH . 'uploads/agentes/anexos/' . $anexo->arquivo;
        if (is_file($path)) {
            unlink($path);
        }

        return $this->delete($anexoId);
    }
}
