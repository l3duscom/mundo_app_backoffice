<?php

namespace App\Models;

use CodeIgniter\Model;

class IngressoModel extends Model
{
    protected $table = 'ingressos';
    protected $returnType = 'App\Entities\Ingresso';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'pedido_id',
        'user_id',
        'ticket_id',
        'nome',
        'valor_unitario',
        'valor',
        'quantidade',
        'codigo',
        'tipo',
        'participante',
        'cinemark'

    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';



    protected $validationMessages = [];

    public function geraCodigoIngresso(): string
    {
        do {
            $codigo = strtoupper(random_string('alnum', 6));

            $this->select('codigo')->where('codigo', $codigo);
        } while ($this->countAllResults() > 1);

        return $codigo;
    }

    public function re3cuperaIngressosPorUsuario(int $usuario_id)
    {
        $query = $this->query("
        SELECT
         ingressos.created_at,
            ingressos.nome,
            ingressos.valor_unitario,
            ingressos.valor,
            ingressos.quantidade,
            ingressos.codigo,
            ingressos.pedido_id,
            ingressos.participante,
            pedidos.codigo as cod_pedido,
            pedidos.rastreio,
            pedidos.status,
            pedidos.status_entrega,
            pedidos.frete,
            pedidos.evento_id,
            pedidos.comprovante,
            eventos.nome as nome_evento,
            eventos.slug,
            eventos.data_inicio,
            eventos.data_fim,
            eventos.hora_inicio,
            eventos.hora_fim,
            eventos.local
        FROM
        ingressos
        INNER JOIN pedidos on pedidos.id = ingressos.pedido_id
        INNER JOIN usuarios on usuarios.id = ingressos.user_id
        INNER JOIN eventos on eventos.id = pedidos.evento_id
        WHERE usuarios.id = $usuario_id
        AND (pedidos.status = 'CONFIRMED'
        OR pedidos.status = 'RECEIVED'
        OR pedidos.status = 'paid'
        OR pedidos.status = 'RECEIVED_IN_CASH)
        ");
        return $query->getResultArray();
    }
    public function recuperaIngressosPorUsuarioEncerrados(int $usuario_id)
    {
        $atributos = [
            'ingressos.id',
            'ingressos.ticket_id',
            'ingressos.created_at',
            'ingressos.nome',
            'ingressos.valor_unitario',
            'ingressos.valor',
            'ingressos.quantidade',
            'ingressos.codigo',
            'ingressos.pedido_id',
            'ingressos.participante',
            'ingressos.tipo',
            'ingressos.cinemark',
            'pedidos.codigo as cod_pedido',
            'pedidos.rastreio',
            'pedidos.status',
            'pedidos.status_entrega',
            'pedidos.frete',
            'pedidos.evento_id',
            'pedidos.comprovante',
            'eventos.nome as nome_evento',
            'eventos.slug',
            'eventos.data_inicio',
            'eventos.data_fim',
            'eventos.hora_inicio',
            'eventos.hora_fim',
            'eventos.local'
        ];

        $retorno = $this->select($atributos)
            ->join('pedidos', 'pedidos.id = ingressos.pedido_id')
            ->join('usuarios', 'usuarios.id = ingressos.user_id')
            ->join('eventos', 'eventos.id = pedidos.evento_id')
            ->where('usuarios.id', $usuario_id)
            ->where('eventos.data_inicio < NOW()')
            ->whereIn('pedidos.status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
            ->orderBy('pedidos.id', 'DESC')
            ->findAll();

        return $retorno;
    }

    public function recuperaIngressosPorUsuario(int $usuario_id)
    {
        $atributos = [
            'ingressos.id',
            'ingressos.ticket_id',
            'ingressos.created_at',
            'ingressos.nome',
            'ingressos.valor_unitario',
            'ingressos.valor',
            'ingressos.quantidade',
            'ingressos.codigo',
            'ingressos.pedido_id',
            'ingressos.participante',
            'ingressos.tipo',
            'ingressos.cinemark',
            'pedidos.codigo as cod_pedido',
            'pedidos.rastreio',
            'pedidos.status',
            'pedidos.status_entrega',
            'pedidos.frete',
            'pedidos.evento_id',
            'pedidos.comprovante',
            'eventos.nome as nome_evento',
            'eventos.slug',
            'eventos.data_inicio',
            'eventos.data_fim',
            'eventos.hora_inicio',
            'eventos.hora_fim',
            'eventos.local'
        ];

        $retorno = $this->select($atributos)
            ->join('pedidos', 'pedidos.id = ingressos.pedido_id')
            ->join('usuarios', 'usuarios.id = ingressos.user_id')
            ->join('eventos', 'eventos.id = pedidos.evento_id')
            ->where('usuarios.id', $usuario_id)
            //->where('eventos.data_fim >= NOW()')
            ->whereIn('pedidos.status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
            ->orderBy('pedidos.id', 'DESC')
            ->findAll();

        return $retorno;
    }

    public function recuperaTotalIngressos($event_id)
    {


        $retorno = $this->select('*')
            ->join('pedidos', 'pedidos.id = ingressos.pedido_id')
            ->whereNotIn('ingressos.tipo', ['cinemark', 'adicional', '', 'produto'])
            ->whereIn('pedidos.status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
            ->where('pedidos.evento_id', $event_id)
            ->countAllResults();

        return $retorno;
    }

    public function recuperaTotalIngressosSabado($event_id)
    {


        $retorno = $this->select('*')
            ->join('pedidos', 'pedidos.id = ingressos.pedido_id')
            ->whereNotIn('ingressos.tipo', ['cinemark', 'adicional', '', 'produto'])
            ->whereIn('pedidos.status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
            ->like('ingressos.nome', 'sábado')
            ->where('pedidos.evento_id', $event_id)
            ->countAllResults();

        return $retorno;
    }
    public function recuperaTotalIngressosDomingo($event_id)
    {


        $retorno = $this->select('*')
            ->join('pedidos', 'pedidos.id = ingressos.pedido_id')
            ->whereNotIn('ingressos.tipo', ['cinemark', 'adicional', '', 'produto'])
            ->whereIn('pedidos.status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
            ->like('ingressos.nome', 'domingo')
            ->where('pedidos.evento_id', $event_id)
            ->countAllResults();

        return $retorno;
    }
    public function recuperaTotalIngressosEpic($event_id)
    {


        $retorno = $this->select('*')
            ->join('pedidos', 'pedidos.id = ingressos.pedido_id')
            ->whereNotIn('ingressos.tipo', ['cinemark', 'adicional', '', 'produto'])
            ->whereIn('pedidos.status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
            ->like('ingressos.nome', 'epic')
            ->where('pedidos.evento_id', $event_id)
            ->countAllResults();

        return $retorno;
    }
    public function recuperaTotalIngressosExp($event_id)
    {


        $retorno = $this->select('*')
            ->join('pedidos', 'pedidos.id = ingressos.pedido_id')
            ->whereNotIn('ingressos.tipo', ['cinemark', 'adicional', '', 'produto'])
            ->whereIn('pedidos.status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
            ->like('ingressos.nome', 'experience')
            ->where('pedidos.evento_id', $event_id)
            ->countAllResults();

        return $retorno;
    }
     public function recuperaTotalIngressosElite($event_id)
    {


        $retorno = $this->select('*')
            ->join('pedidos', 'pedidos.id = ingressos.pedido_id')
            ->whereNotIn('ingressos.tipo', ['cinemark', 'adicional', '', 'produto'])
            ->whereIn('pedidos.status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
            ->like('ingressos.nome', 'elite')
            ->where('pedidos.evento_id', $event_id)
            ->countAllResults();

        return $retorno;
    }
    public function recuperaTotalIngressosVip($event_id)
    {


        $retorno = $this->select('*')
            ->join('pedidos', 'pedidos.id = ingressos.pedido_id')
            ->whereNotIn('ingressos.tipo', ['cinemark', 'adicional', '', 'produto'])
            ->whereIn('pedidos.status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
            ->like('ingressos.nome', 'vip')
            ->where('pedidos.evento_id', $event_id)
            ->countAllResults();

        return $retorno;
    }

    public function recuperaTotalIngressosCombo($event_id)
    {


        $retorno = $this->select('*')
            ->join('pedidos', 'pedidos.id = ingressos.pedido_id')
            ->whereNotIn('ingressos.tipo', ['cinemark', 'adicional', 'individual', '', 'produto'])
            ->whereIn('pedidos.status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
            ->where('pedidos.evento_id', $event_id)
            ->countAllResults();

        return $retorno;
    }
    public function recuperaTotalIngressosPendentes($event_id)
    {


        $retorno = $this->select('*')
            ->join('pedidos', 'pedidos.id = ingressos.pedido_id')
            ->whereNotIn('ingressos.tipo', ['cinemark', 'adicional', '', 'produto'])
            ->whereNotIn('pedidos.status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
            ->where('pedidos.evento_id', $event_id)
            ->countAllResults();

        return $retorno;
    }

    public function recuperaTotalIngressosCortesias($event_id)
    {


        $retorno = $this->select('*')
            ->join('pedidos', 'pedidos.id = ingressos.pedido_id')
            ->like('ingressos.nome', 'cortesia')
            ->whereIn('pedidos.status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
            ->where('pedidos.evento_id', $event_id)
            ->countAllResults();

        return $retorno;
    }
    public function recuperaTotalIngressosHoje($event_id)
    {


        $retorno = $this->select('*')
            ->join('pedidos', 'pedidos.id = ingressos.pedido_id')
            ->where('DATE(ingressos.created_at) >= DATE(NOW())')
            ->where('pedidos.evento_id', $event_id)
            ->notLike('ingressos.nome', 'cortesia')
            ->whereNotIn('ingressos.tipo', ['cinemark', 'adicional', '', 'produto', 'acesso'])
            ->whereIn('pedidos.status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])

            ->countAllResults();

        return $retorno;
    }



    public function recuperaIngressosPorPedido(int $id)
    {
        $atributos = [
            'ingressos.id',
            'ingressos.created_at',
            'ingressos.nome',
            'ingressos.valor_unitario',
            'ingressos.valor',
            'ingressos.quantidade',
            'ingressos.codigo',
            'ingressos.pedido_id',
            'ingressos.participante',
            'ingressos.cinemark',
            'pedidos.codigo as cod_pedido',
            'pedidos.rastreio',
            'pedidos.status',
            'pedidos.status_entrega',
            'pedidos.frete',
            'pedidos.evento_id',
            'pedidos.comprovante',
            'eventos.nome as nome_evento',
            'eventos.slug',
            'eventos.data_inicio',
            'eventos.data_fim',
            'eventos.hora_inicio',
            'eventos.hora_fim',
            'eventos.local',
            'clientes.nome as nome_cliente'
        ];

        $retorno = $this->select($atributos)
            ->join('pedidos', 'pedidos.id = ingressos.pedido_id')
            ->join('usuarios', 'usuarios.id = ingressos.user_id')
            ->join('clientes', 'usuarios.id = clientes.usuario_id')
            ->join('eventos', 'eventos.id = pedidos.evento_id')
            ->where('pedidos.id', $id)
            ->whereIn('pedidos.status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
            ->findAll();

        return $retorno;
    }

    public function recuperaIngresso(int $id)
    {

        $atributos = [
            'ingressos.id',
            'ingressos.created_at',
            'ingressos.nome',
            'ingressos.valor_unitario',
            'ingressos.valor',
            'ingressos.quantidade',
            'ingressos.codigo',
            'ingressos.pedido_id',
            'ingressos.participante',
            'ingressos.cinemark',
            'pedidos.codigo as cod_pedido',
            'pedidos.rastreio',
            'pedidos.status',
            'pedidos.status_entrega',
            'pedidos.frete',
            'pedidos.evento_id',
            'pedidos.comprovante',
            'eventos.nome as nome_evento',
            'eventos.slug',
            'eventos.data_inicio',
            'eventos.data_fim',
            'eventos.hora_inicio',
            'eventos.hora_fim',
            'eventos.local',
            'eventos.cep',
            'eventos.endereco',
            'eventos.numero',
            'eventos.bairro',
            'eventos.cidade',
            'eventos.estado',
            'ingressos.user_id'
        ];

        $retorno = $this->select($atributos)
            ->join('pedidos', 'pedidos.id = ingressos.pedido_id')
            ->join('usuarios', 'usuarios.id = ingressos.user_id')
            ->join('eventos', 'eventos.id = pedidos.evento_id')
            ->find($id);

        return $retorno;
    }
}
