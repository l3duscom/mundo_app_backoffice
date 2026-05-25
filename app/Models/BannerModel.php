<?php

namespace App\Models;

use CodeIgniter\Model;

class BannerModel extends Model
{
    protected $table          = 'banners';
    protected $returnType     = 'App\Entities\BannerEntity';
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'event_id',
        'imagem',
        'link',
        'ordem',
        'ativo',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'event_id' => 'required|integer',
        'imagem'   => 'permit_empty|max_length[255]',
        'link'     => 'permit_empty',
        'ativo'    => 'in_list[0,1]',
    ];

    protected $validationMessages = [
        'event_id' => [
            'required' => 'O evento é obrigatório.',
        ],
    ];

    public function getByEvento(int $eventId): array
    {
        return $this->where('event_id', $eventId)
            ->orderBy('ordem', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function reordenar(int $eventId, array $idsEmOrdem): void
    {
        foreach ($idsEmOrdem as $posicao => $id) {
            $this->where('event_id', $eventId)
                ->where('id', (int) $id)
                ->set(['ordem' => (int) $posicao])
                ->update();
        }
    }
}
