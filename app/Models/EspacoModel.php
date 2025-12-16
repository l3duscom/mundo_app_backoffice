<?php

namespace App\Models;

use CodeIgniter\Model;

class EspacoModel extends Model
{
    protected $table                = 'espacos';
    protected $returnType           = 'App\Entities\Espaco';
    protected $useSoftDeletes       = false;
    protected $allowedFields        = [
        'event_id',
        'tipo_item',
        'nome',
        'descricao',
        'imagem',
        'status',
        'contrato_item_id',
    ];

    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';

    protected $validationRules = [
        'event_id'  => 'required|integer',
        'tipo_item' => 'required',
        'nome'      => 'required|max_length[50]',
    ];

    /**
     * Busca espaços por evento
     */
    public function buscaPorEvento(int $eventId): array
    {
        return $this->where('event_id', $eventId)
            ->orderBy('nome', 'ASC')
            ->findAll();
    }

    /**
     * Busca espaços por evento e tipo de item com JOIN para dados do expositor
     * Suporta tipo_item armazenado como JSON array ou string simples
     */
    public function buscaPorEventoETipo(int $eventId, string $tipoItem): array
    {
        $db = \Config\Database::connect();
        
        $sql = "SELECT e.*, 
                       exp.instagram
                FROM espacos e 
                LEFT JOIN contrato_itens ci ON e.contrato_item_id = ci.id 
                LEFT JOIN contratos c ON ci.contrato_id = c.id 
                LEFT JOIN expositores exp ON c.expositor_id = exp.id 
                WHERE e.event_id = ? 
                AND e.tipo_item LIKE ? 
                ORDER BY e.nome ASC";
        
        return $db->query($sql, [$eventId, '%' . $tipoItem . '%'])->getResult();
    }

    /**
     * Busca espaços livres por evento e tipo de item
     * Suporta tipo_item armazenado como JSON array ou string simples
     */
    public function buscaLivresPorEventoETipo(int $eventId, string $tipoItem): array
    {
        return $this->where('event_id', $eventId)
            ->groupStart()
                ->like('tipo_item', $tipoItem)
            ->groupEnd()
            ->where('status', 'livre')
            ->orderBy('nome', 'ASC')
            ->findAll();
    }

    /**
     * Reserva um espaço para um item de contrato de forma atômica
     * @return bool|string true se sucesso, mensagem de erro se falha
     */
    public function reservar(int $espacoId, int $contratoItemId)
    {
        $db = \Config\Database::connect();
        
        try {
            $db->transStart();
            
            // Busca o espaço com lock para evitar race condition
            $espaco = $db->query("SELECT * FROM espacos WHERE id = ? FOR UPDATE", [$espacoId])->getRow();
            
            if (!$espaco) {
                $db->transRollback();
                return 'Espaço não encontrado';
            }
            
            if ($espaco->status !== 'livre') {
                $db->transRollback();
                return 'Este espaço já está ' . $espaco->status;
            }
            
            // Reserva o espaço
            $this->update($espacoId, [
                'status' => 'reservado',
                'contrato_item_id' => $contratoItemId,
            ]);
            
            $db->transComplete();
            
            return $db->transStatus();
            
        } catch (\Exception $e) {
            $db->transRollback();
            return 'Erro ao reservar: ' . $e->getMessage();
        }
    }

    /**
     * Libera um espaço (remove reserva)
     */
    public function liberar(int $espacoId): bool
    {
        return $this->update($espacoId, [
            'status' => 'livre',
            'contrato_item_id' => null,
        ]);
    }

    /**
     * Libera espaço pelo item de contrato
     */
    public function liberarPorContratoItem(int $contratoItemId): bool
    {
        return $this->where('contrato_item_id', $contratoItemId)
            ->set(['status' => 'livre', 'contrato_item_id' => null])
            ->update();
    }

    /**
     * Busca espaço reservado por um item de contrato
     */
    public function buscaPorContratoItem(int $contratoItemId)
    {
        return $this->where('contrato_item_id', $contratoItemId)->first();
    }

    /**
     * Conta espaços por status para um evento
     */
    public function contaPorStatus(int $eventId): array
    {
        $result = $this->select('status, COUNT(*) as total')
            ->where('event_id', $eventId)
            ->groupBy('status')
            ->findAll();

        $contagem = ['livre' => 0, 'reservado' => 0, 'bloqueado' => 0];
        foreach ($result as $row) {
            $contagem[$row->status] = (int) $row->total;
        }

        return $contagem;
    }
}
