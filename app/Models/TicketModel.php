<?php

namespace App\Models;

use CodeIgniter\Model;

class TicketModel extends Model
{
    protected $table = 'tickets';
    protected $returnType = 'App\Entities\Ticket';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'event_id',
        'user_id',
        'parent_ticket_id',
        'nome',
        'codigo',
        'tipo',
        'categoria',
        'quantidade',
        'preco',
        'valor_unitario',
        'ativo',
        'data_inicio',
        'data_fim',
        'data_lote',
        'lote',
        'descricao',
        'estoque',
        'cretade_at',

    ];

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

    public function geraCodigoTicket(): string
    {
        do {
            $codigo = strtoupper(random_string('alnum', 20));

            $this->select('codigo')->where('codigo', $codigo);
        } while ($this->countAllResults() > 1);

        return $codigo;
    }



    public function recuperaConcursosPorEvento(int $evento_id)
    {
        $atributos = [
            'concursos.id',
            'concursos.created_at',
            'concursos.codigo',
            'concursos.tipo',
            'concursos.nome',
            'concursos.slug',
            'concursos.ativo',
            'concursos.evento_id',
            'concursos.juri',
            'eventos.nome as nome_evento',
            'eventos.slug',
            'eventos.data_inicio',
            'eventos.data_fim',
            'eventos.hora_inicio',
            'eventos.hora_fim',
            'eventos.local'
        ];



        return $this->select($atributos)
            ->join('eventos', 'eventos.id = concursos.evento_id')
            ->where('eventos.id', $evento_id)
            ->orderBy('concursos.created_at', 'DESC')
            ->findAll();
    }

    public function recuperaIngressosPorEvento(int $event_id)
    {
        $atributos = [
            '*',
        ];

        return $this->select($atributos)
            ->where('event_id', $event_id)
            ->where('data_lote >= now()')
            ->where('promo', null)
            ->where('ativo', 1)
            ->where('quantidade >= estoque')
            ->orderBy('nome', 'ASC')
            ->findAll();
    }

    public function recuperaAdicionaisPorEvento(int $event_id)
    {
        $atributos = [
            '*',
        ];

        return $this->select($atributos)
            ->where('event_id', $event_id)
            ->where('data_lote >= now()')
            ->where('promo', null)
            ->where('ativo', 1)
            ->where('quantidade >= estoque')
            ->orderBy('nome', 'ASC')
            ->findAll();
    }

    public function recuperaIngressosGirafasPorEvento(int $event_id)
    {
        $atributos = [
            '*',
        ];

        return $this->select($atributos)
            ->where('event_id', $event_id)
            ->where('data_lote >= now()')
            ->where('promo', 'girafinhas')
            ->where('ativo', 1)
            ->where('quantidade >= estoque')
            ->orderBy('nome', 'ASC')
            ->findAll();
    }
    public function recuperaIngressosOtakadaPorEvento(int $event_id)
    {
        $atributos = [
            '*',
        ];

        return $this->select($atributos)
            ->where('event_id', $event_id)
            ->where('data_lote >= now()')
            ->where('promo', 'otakada')
            ->where('ativo', 1)
            ->where('quantidade >= estoque')
            ->orderBy('nome', 'ASC')
            ->findAll();
    }


    public function recuperaIngressosPucPorEvento(int $event_id)
    {
        $atributos = [
            '*',
        ];

        return $this->select($atributos)
            ->where('event_id', $event_id)
            ->where('data_lote >= now()')
            ->where('promo', 'puc')
            ->where('ativo', 1)
            ->where('quantidade >= estoque')
            ->orderBy('nome', 'ASC')
            ->findAll();
    }

    /**
     * Busca tickets vinculados pelo parent_ticket_id
     */
    public function buscaTicketsVinculados(int $ticket_id)
    {
        return $this->select('*')
            ->where('parent_ticket_id', $ticket_id)
            ->where('ativo', 1)
            ->where('quantidade >= estoque')
            ->findAll();
    }
}
