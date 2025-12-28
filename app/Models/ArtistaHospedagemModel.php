<?php

namespace App\Models;

use CodeIgniter\Model;

class ArtistaHospedagemModel extends Model
{
    protected $table                = 'artista_hospedagens';
    protected $returnType           = 'object';
    protected $useSoftDeletes       = false;
    protected $allowedFields        = [
        'contratacao_id',
        'hotel',
        'endereco',
        'telefone',
        'codigo_reserva',
        'data_checkin',
        'data_checkout',
        'tipo_quarto',
        'quantidade_quartos',
        'valor_diaria',
        'valor_total',
        'status',
        'observacoes',
    ];

    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';

    public function buscaPorContratacao(int $contratacaoId): array
    {
        return $this->where('contratacao_id', $contratacaoId)
            ->orderBy('data_checkin', 'ASC')
            ->findAll();
    }
}
