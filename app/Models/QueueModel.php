<?php

namespace App\Models;

use CodeIgniter\Model;

class QueueModel extends Model
{
    protected $table = 'queue_meet';
    protected $returnType = 'App\Entities\Queue';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'user_id',
        'meet_id',
        'ordem',
        'code',
        'status',
        'ingresso_id',
        'cretade_at',

    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';




    // Validation
    protected $validationRules = [
        'code' => 'required',

    ];

    protected $validationMessages = [];

    public function recuperaQueuePorUsuario(int $user_id)
    {
        $atributos = [
            'queue_meet.id',
            'queue_meet.meet_id',
            'queue_meet.ordem',
            'queue_meet.code',
            'queue_meet.status',
            'queue_meet.ingresso_id',
            'queue_meet.updated_at',
            'meet.artista',
            'meet.data_meet',
            'meet.hora_inicial',
            'meet.tipo'
        ];

        $ordem = $this->select($atributos)
            ->join('meet', 'meet.id = queue_meet.meet_id')
            ->where('queue_meet.user_id', $user_id)
            ->findAll();

        return $ordem;
    }

    public function recuperaOrdem(int $meet_id)
    {
        $atributos = [
            'queue_meet.id',
            'queue_meet.meet_id',
            'queue_meet.ordem',
            'queue_meet.updated_at'
        ];

        $ordem = $this->select($atributos)
            ->where('queue_meet.meet_id', $meet_id)
            ->orderBy('queue_meet.ordem', 'DESC')
            ->first();

        return $ordem;
    }

    public function geraCodigo(): string
    {
        do {
            $codigo = strtoupper(random_string('alnum', 10));

            $this->select('codigo')->where('codigo', $codigo);
        } while ($this->countAllResults() > 1);

        return $codigo;
    }

    public function recuperaQueueAll(int $user_id) {}

    public function recuperaPorCodigo(string $code)
    {
        $atributos = [
            'queue_meet.id',
            'queue_meet.user_id',
            'queue_meet.meet_id',
            'queue_meet.ingresso_id',
            'queue_meet.ordem',
            'queue_meet.code',
            'queue_meet.status',
            'queue_meet.created_at',
            'queue_meet.updated_at',
            'meet.artista',
            'meet.dia',
            'meet.data_meet',
            'meet.hora_inicial',
            'meet.tipo',
            'meet.event_id',
            'usuarios.nome AS usuario_nome',
            'usuarios.email AS usuario_email',
            'ingressos.nome AS ingresso_nome',
            'ingressos.codigo AS ingresso_codigo',
        ];

        return $this->select($atributos)
            ->join('meet', 'meet.id = queue_meet.meet_id')
            ->join('usuarios', 'usuarios.id = queue_meet.user_id', 'left')
            ->join('ingressos', 'ingressos.id = queue_meet.ingresso_id', 'left')
            ->where('queue_meet.code', $code)
            ->first();
    }

    public function recuperaQueueAllByUser(int $ingresso_id)
    {


        $atributos = [
            'queue_meet.meet_id',

        ];

        $retorno = $this->select($atributos)
            ->where('queue_meet.ingresso_id', $ingresso_id)
            ->findAll();



        return $retorno;
    }

    public function recuperaQueue(int $user_id, $meet_id) {}

    public function recuperaCheckinQueue(int $user_id, $meet_id) {}

    public function recuperaCheckinQueueDay(int $user_id, $meet_id, $day) {}

    public function CountQueueForDayEpic(int $event_id, string $day, int $user_id)
    {

        return $this->join('meet', 'meet.id = queue_meet.meet_id')
            ->where('meet.event_id', $event_id)
            ->where('meet.dia', $day)
            ->where('queue_meet.user_id', $user_id)
            ->countAllResults();
    }


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
            'inscricoes.nome_musica',
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
}
