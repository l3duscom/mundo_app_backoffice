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
        'nome_musica',
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

    /**
     * Verifica se o usuário já possui inscrição ativa no concurso
     * 
     * @param int $user_id ID do usuário
     * @param int $concurso_id ID do concurso
     * @return object|null Retorna a inscrição existente ou null
     */
    public function verificaInscricaoDuplicada(int $user_id, int $concurso_id)
    {
        return $this->where('user_id', $user_id)
            ->where('concurso_id', $concurso_id)
            ->whereNotIn('status', ['CANCELADA', 'REJEITADA'])
            ->first();
    }

    /**
     * Recupera inscrição para edição com validação de propriedade
     * 
     * @param int $inscricao_id ID da inscrição
     * @param int $user_id ID do usuário
     * @return object|null Retorna a inscrição ou null se não pertencer ao usuário
     */
    public function recuperaInscricaoParaEdicao(int $inscricao_id, int $user_id)
    {
        $atributos = [
            'inscricoes.*',
            'concursos.nome as nome_concurso',
            'concursos.tipo',
            'eventos.data_inicio as evento_data_inicio',
        ];

        return $this->select($atributos)
            ->join('concursos', 'concursos.id = inscricoes.concurso_id')
            ->join('eventos', 'eventos.id = concursos.evento_id')
            ->where('inscricoes.id', $inscricao_id)
            ->where('inscricoes.user_id', $user_id)
            ->first();
    }

    /**
     * Verifica se a inscrição pode ser editada (status e prazo)
     * 
     * @param int $inscricao_id ID da inscrição
     * @return array ['pode_editar' => bool, 'motivo' => string]
     */
    public function podeEditar(int $inscricao_id): array
    {
        $atributos = [
            'inscricoes.id',
            'inscricoes.status',
            'eventos.data_inicio as evento_data_inicio',
        ];

        $inscricao = $this->select($atributos)
            ->join('concursos', 'concursos.id = inscricoes.concurso_id')
            ->join('eventos', 'eventos.id = concursos.evento_id')
            ->where('inscricoes.id', $inscricao_id)
            ->first();

        if (!$inscricao) {
            return [
                'pode_editar' => false,
                'motivo' => 'Inscrição não encontrada.'
            ];
        }

        // Status permitidos para edição
        $statusPermitidos = ['INICIADA', 'APROVADA', 'CHECKIN-ONLINE', 'EDITADA'];

        if (!in_array($inscricao->status, $statusPermitidos)) {
            return [
                'pode_editar' => false,
                'motivo' => "Não é possível editar esta inscrição. Apenas inscrições com status 'Recebida', 'Aprovada', 'Check-in Online' ou 'Editada' podem ser editadas. Status atual: {$inscricao->status}"
            ];
        }

        // Verificar prazo (7 dias antes do evento)
        $dataEvento = strtotime($inscricao->evento_data_inicio);
        $prazoLimite = strtotime('-7 days', $dataEvento);
        $hoje = time();

        if ($hoje > $prazoLimite) {
            $dataLimite = date('d/m/Y', $prazoLimite);
            return [
                'pode_editar' => false,
                'motivo' => "O prazo para edição desta inscrição encerrou em {$dataLimite}. Edições são permitidas apenas até 7 dias antes da data do evento."
            ];
        }

        return [
            'pode_editar' => true,
            'motivo' => ''
        ];
    }

    /**
     * Conta quantos concursos diferentes o usuário se inscreveu
     * 
     * @param string $email Email do usuário
     * @return int Número de concursos diferentes
     */
    public function contaInscricoesPorEmail(string $email): int
    {
        $result = $this->select('COUNT(DISTINCT concurso_id) as total')
            ->where('email', $email)
            ->whereNotIn('status', ['CANCELADA', 'REJEITADA'])
            ->first();
        
        return $result ? (int) $result->total : 0;
    }
}
