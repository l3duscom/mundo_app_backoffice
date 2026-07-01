<?php

namespace App\Models;

use CodeIgniter\Model;

class CredencialModel extends Model
{
    protected $table = 'credenciais';
    protected $returnType = 'App\Entities\Credencial';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'ingresso_id',
        'pedido_id',
        'codigo',
        'acessos',
        'ativo',
        'ticket_alimentacao',
        'ticket_alimentacao_em',
    ];

    /**
     * Marca a retirada do ticket de alimentação para a credencial.
     * Se $usado === false, desmarca (útil para desfazer entrega errada).
     */
    public function marcarTicketAlimentacao(int $id, bool $usado = true): bool
    {
        return $this->update($id, [
            'ticket_alimentacao'    => $usado ? 1 : 0,
            'ticket_alimentacao_em' => $usado ? date('Y-m-d H:i:s') : null,
        ]);
    }

    // Dates
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'codigo' => 'required',

    ];

    protected $validationMessages = [];
}
