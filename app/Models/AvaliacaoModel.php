<?php

namespace App\Models;

use CodeIgniter\Model;

class AvaliacaoModel extends Model
{
    protected $table = 'avaliacoes';
    protected $returnType = 'App\Entities\Avaliacao';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'inscricao_id',
        'jurado_id',
        'nota_total',
        'nota_1',
        'nota_2',
        'nota_3',
        'nota_4',
        'nota_5',
        'checkin',
        'aprovado',
        'cretade_at',

    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';




    // Validation
    protected $validationRules = [
        'nota_1' => 'required',

    ];

    protected $validationMessages = [];




    public function recuperarecuperaInscricoesCosplayPorConcurso(int $concurso_id)
    {
        $atributos = [
            'inscricoes.id',
            'inscricoes.concurso_id',
            'inscricoes.codigo',
            'inscricoes.email',
            'inscricoes.motivacao',
            'inscricoes.tempo',
            'inscricoes.nome',
            'inscricoes.nome_social',
            'inscricoes.personagem',
            'inscricoes.obra',
            'inscricoes.genero',
            'inscricoes.referencia',
            'inscricoes.apoio',
            'inscricoes.video_led',
            'inscricoes.observacoes',
        ];



        return $this->select($atributos)
            ->where('inscricoes.concurso_id', $concurso_id)
            ->orderBy('inscricoes.updated_at', 'ASC')
            ->findAll();
    }

    public function recuperarecuperaInscricoesKpopPorConcurso(int $concurso_id)
    {
        $atributos = [
            'inscricoes.id',
            'inscricoes.concurso_id',
            'inscricoes.codigo',
            'inscricoes.email',
            'inscricoes.motivacao',
            'inscricoes.nome',
            'inscricoes.nome_social',
            'inscricoes.marca',
            'inscricoes.musica',
            'inscricoes.grupo',
            'inscricoes.referencia',
            'inscricoes.video_led',
            'inscricoes.integrantes',
        ];



        return $this->select($atributos)
            ->where('inscricoes.concurso_id', $concurso_id)
            ->orderBy('inscricoes.updated_at', 'ASC')
            ->findAll();
    }

    protected function generateCodigo(array $data): array
    {
        if (isset($data['data']['nome'])) {
            $data['data']['codigo'] = strtoupper(random_string('alnum', 20));
        }
        return $data;
    }

    /**
     * Recupera avaliações detalhadas de uma inscrição com nome do jurado
     * 
     * @param int $inscricao_id ID da inscrição
     * @return array Avaliações com detalhes por jurado
     */
    public function getAvaliacoesDetalhadas(int $inscricao_id): array
    {
        $db = \Config\Database::connect();
        
        $sql = "
            SELECT 
                a.id,
                a.inscricao_id,
                a.jurado_id,
                u.nome AS jurado_nome,
                a.nota_1,
                a.nota_2,
                a.nota_3,
                a.nota_4,
                a.nota_total,
                a.created_at
            FROM avaliacoes a
            JOIN usuarios u ON a.jurado_id = u.id
            WHERE a.inscricao_id = ?
              AND a.deleted_at IS NULL
            ORDER BY a.created_at ASC
        ";
        
        $query = $db->query($sql, [$inscricao_id]);
        
        return $query->getResultArray();
    }
}

