<?php

namespace App\Models;

use CodeIgniter\Model;

class InscricaoModel extends Model
{
    protected $table = 'inscricoes';
    protected $returnType = 'App\Entities\Inscricao';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'concurso_id',
        'user_id',
        'codigo',
        'email',
        'motivacao',
        'tempo',
        'nome',
        'nome_social',
        'personagem',
        'obra',
        'genero',
        'referencia',
        'apoio',
        'observacoes',
        'video_apresentacao',
        'categoria',
        'marca',
        'video_led',
        'musica',
        'integrantes',
        'grupo',
        'status',
        'ordem',
        'cretade_at',

    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';




    // Validation
    protected $validationRules = [
        'nome' => 'required',

    ];

    protected $validationMessages = [];




    public function recuperarecuperaInscricoesCosplayPorConcurso(int $concurso_id)
    {
        $atributos = [
            'inscricoes.id',
            'inscricoes.concurso_id',
            'inscricoes.codigo',
            'inscricoes.email',
            'inscricoes.telefone',
            'inscricoes.cpf',
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
            'inscricoes.status',
            'inscricoes.ordem',
            'inscricoes.updated_at',
            'inscricoes.created_at'
        ];



        return $this->select($atributos)
            ->where('inscricoes.concurso_id', $concurso_id)
            ->whereNotIn('inscricoes.status', ['REJEITADA'])
            ->orderBy('inscricoes.updated_at', 'DESC')
            ->findAll();
    }

    public function recuperarecuperaInscricoesKpopPorConcurso(int $concurso_id)
    {
        $atributos = [
            'inscricoes.id',
            'inscricoes.concurso_id',
            'inscricoes.codigo',
            'inscricoes.email',
            'inscricoes.telefone',
            'inscricoes.cpf',
            'inscricoes.motivacao',
            'inscricoes.nome',
            'inscricoes.nome_social',
            'inscricoes.marca',
            'inscricoes.musica',
            'inscricoes.grupo',
            'inscricoes.referencia',
            'inscricoes.video_led',
            'inscricoes.integrantes',
            'inscricoes.video_apresentacao',
            'inscricoes.status',
            'inscricoes.ordem',
            'inscricoes.updated_at',
            'inscricoes.created_at',
            'inscricoes.categoria'
        ];



        return $this->select($atributos)
            ->where('inscricoes.concurso_id', $concurso_id)
            ->whereNotIn('inscricoes.status', ['REJEITADA'])
            ->orderBy('inscricoes.updated_at', 'DESC')
            ->findAll();
    }

    public function recuperaInscricoesPorUsuario(int $user_id)
    {
        $atributos = [
            'inscricoes.id',
            'inscricoes.concurso_id',
            'inscricoes.codigo',
            'inscricoes.email',
            'inscricoes.telefone',
            'inscricoes.cpf',
            'inscricoes.motivacao',
            'inscricoes.nome',
            'inscricoes.nome_social',
            'inscricoes.marca',
            'inscricoes.musica',
            'inscricoes.grupo',
            'inscricoes.referencia',
            'inscricoes.video_led',
            'inscricoes.integrantes',
            'inscricoes.video_apresentacao',
            'inscricoes.status',
            'inscricoes.ordem',
            'concursos.tipo',
            'inscricoes.categoria',
            'inscricoes.personagem',
            'inscricoes.obra',
            'inscricoes.genero',
            'concursos.nome as nome_concurso',
            'inscricoes.updated_at',
            'inscricoes.created_at'
        ];



        return $this->select($atributos)
            ->join('concursos', 'concursos.id = inscricoes.concurso_id')
            ->where('inscricoes.user_id', $user_id)
            ->orderBy('inscricoes.id', 'DESC')
            ->findAll();
    }

    public function recuperaOrdem(int $inscricao_id, int $concurso_id)
    {
        $atributos = [
            'inscricoes.id',
            'inscricoes.concurso_id',
            'inscricoes.ordem',
            'inscricoes.updated_at'
        ];



        return $this->select($atributos)
            ->where('inscricoes.concurso_id', $concurso_id)
            ->orderBy('inscricoes.ordem', 'DESC')
            ->first();
    }

    public function recuperarecuperaInscricoesCosplayPorConcursoComNota(int $concurso_id)
    {
        $atributos = [
            'inscricoes.id',
            'inscricoes.concurso_id',
            'inscricoes.codigo',
            'inscricoes.email',
            'inscricoes.telefone',
            'inscricoes.cpf',
            'inscricoes.motivacao',
            'inscricoes.nome',
            'inscricoes.nome_social',
            'inscricoes.marca',
            'inscricoes.musica',
            'inscricoes.grupo',
            'inscricoes.referencia',
            'inscricoes.video_led',
            'inscricoes.integrantes',
            'avaliacoes.jurado_id',
            'inscricoes.status',
            'inscricoes.ordem',
            'inscricoes.updated_at'

        ];



        return $this->select($atributos)
            ->join('avaliacoes', 'avaliacoes.inscricao_id = inscricoes.id')
            ->where('inscricoes.concurso_id', $concurso_id)
            ->findAll();
    }


    public function geraCodigo(): string
    {
        do {
            $codigo = strtoupper(random_string('alnum', 10));

            $this->select('codigo')->where('codigo', $codigo);
        } while ($this->countAllResults() > 1);

        return $codigo;
    }
}
