<?php

namespace App\Models;

use CodeIgniter\Model;

class ArtistaAgenteModel extends Model
{
    protected $table                = 'artista_agentes';
    protected $returnType           = 'object';
    protected $useSoftDeletes       = false;
    protected $allowedFields        = [
        'artista_id',
        'agente_id',
        'funcao',
        'principal',
        'observacoes',
    ];

    // Dates
    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = false;

    const FUNCOES = [
        'agente' => 'Agente',
        'empresario' => 'Empresário',
        'assessoria' => 'Assessoria',
        'produtor' => 'Produtor',
        'tecnico' => 'Técnico',
        'outro' => 'Outro',
    ];

    /**
     * Vincula agente ao artista
     */
    public function vincular(int $artistaId, int $agenteId, string $funcao = 'agente', bool $principal = false): bool
    {
        // Verifica se já existe
        $existe = $this->where('artista_id', $artistaId)
            ->where('agente_id', $agenteId)
            ->first();

        if ($existe) {
            return $this->update($existe->id, [
                'funcao' => $funcao,
                'principal' => $principal ? 1 : 0,
            ]);
        }

        // Se é principal, remove principal dos outros
        if ($principal) {
            $this->where('artista_id', $artistaId)
                ->set(['principal' => 0])
                ->update();
        }

        return (bool) $this->insert([
            'artista_id' => $artistaId,
            'agente_id' => $agenteId,
            'funcao' => $funcao,
            'principal' => $principal ? 1 : 0,
        ]);
    }

    /**
     * Desvincula agente do artista
     */
    public function desvincular(int $artistaId, int $agenteId): bool
    {
        return $this->where('artista_id', $artistaId)
            ->where('agente_id', $agenteId)
            ->delete();
    }

    /**
     * Busca vínculos de um artista
     */
    public function buscarPorArtista(int $artistaId): array
    {
        return $this->select('artista_agentes.*, agentes.nome, agentes.nome_fantasia, agentes.email, agentes.telefone, agentes.whatsapp, agentes.tipo as tipo_agente')
            ->join('agentes', 'agentes.id = artista_agentes.agente_id')
            ->where('artista_agentes.artista_id', $artistaId)
            ->orderBy('artista_agentes.principal', 'DESC')
            ->orderBy('agentes.nome', 'ASC')
            ->findAll();
    }
}
