<?php

namespace App\Models;

use CodeIgniter\Model;

class IngressoTrocaModel extends Model
{
    protected $table         = 'ingresso_trocas';
    protected $returnType    = 'App\Entities\IngressoTroca';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'ingresso_id',
        'ticket_id_anterior',
        'ticket_id_novo',
        'nome_anterior',
        'nome_novo',
        'operador_id',
        'motivo',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    /**
     * Retorna o histórico de trocas de um ingresso, com nome do operador.
     */
    public function historicoPorIngresso(int $ingresso_id)
    {
        return $this->select('ingresso_trocas.*, usuarios.nome as operador_nome')
            ->join('usuarios', 'usuarios.id = ingresso_trocas.operador_id', 'left')
            ->where('ingresso_trocas.ingresso_id', $ingresso_id)
            ->orderBy('ingresso_trocas.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Retorna o histórico de trocas de vários ingressos indexado por ingresso_id.
     *
     * @param int[] $ingressoIds
     * @return array<int, array>
     */
    public function historicoPorIngressos(array $ingressoIds): array
    {
        if (empty($ingressoIds)) {
            return [];
        }

        $rows = $this->select('ingresso_trocas.*, usuarios.nome as operador_nome')
            ->join('usuarios', 'usuarios.id = ingresso_trocas.operador_id', 'left')
            ->whereIn('ingresso_trocas.ingresso_id', $ingressoIds)
            ->orderBy('ingresso_trocas.created_at', 'DESC')
            ->findAll();

        $agrupado = [];
        foreach ($rows as $row) {
            $agrupado[$row->ingresso_id][] = $row;
        }

        return $agrupado;
    }
}
