<?php

namespace App\Models;

use CodeIgniter\Model;

class PedidoModel extends Model
{
    protected $table = 'pedidos';
    protected $returnType = 'App\Entities\Pedido';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'user_id',
        'evento_id',
        'endereco_id',
        'codigo',
        'rastreio',
        'total',
        'valor_liquido',
        'data_vencimento',
        'status',
        'status_entrega',
        'forma_pagamento',
        'frete',
        'convite',
        'crm',
        'crmobs',
        'cretade_at',
        'updated_at',
        'charge_id',
        'comprovante',
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

    public function geraCodigoPedido(): string
    {
        do {
            $codigo = strtoupper(random_string('alnum', 20));

            $this->select('codigo')->where('codigo', $codigo);
        } while ($this->countAllResults() > 1);

        return $codigo;
    }

    public function recuperaRecompraPorEvento(int $event_id)
    {
        $sql = "
    SELECT 
    p11.id,
    p11.crm,
    p11.created_at,
    p11.updated_at,
    p11.crmobs,
    c.nome,
    c.cpf,
    CONCAT('https://wa.me/55', REPLACE(REPLACE(REPLACE(REPLACE(c.telefone, '(', ''), ')', ''), '-', ''), ' ', '')) AS whatsapp,
    c.email,
    p11.total as 'total',
    CASE 
        WHEN p11.status = 'CONFIRMED' THEN 'Cartão de Crédito'
        WHEN p11.status IN ('PAID', 'RECEIVED') THEN 'PIX'
        WHEN p11.status = 'RECEIVED_IN_CASH' THEN 'Dinheiro'
        ELSE 'Outro'
    END AS 'pagamento',
    CASE 
        WHEN p11.frete = 1 THEN 'Entrega em casa'
        WHEN p11.frete = 0 THEN 'Retirar no dia'
        ELSE 'Outro'
    END AS Entrega
FROM pedidos p11
LEFT JOIN pedidos p17 
    ON p11.user_id = p17.user_id 
    AND p17.evento_id in (17)
LEFT JOIN clientes c
    ON p11.user_id = c.usuario_id
WHERE p11.evento_id = 12
AND p11.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH', 'PAID')
AND p17.id IS NULL
AND p11.crm IS NULL
ORDER BY p11.total DESC;


    ";

        // Alterado de getResultArray() para getResult(), para que os dados venham como objetos
        return $this->db->query($sql, [$event_id])->getResult();
    }

    public function recuperaRecompraRevertidaPorEvento(int $event_id)
    {
        $sql = "
    SELECT 
    p11.id,
    p11.crm,
    p11.created_at,
    p11.updated_at,
    p11.crmobs,
    c.nome,
    c.cpf,
    CONCAT('https://wa.me/55', REPLACE(REPLACE(REPLACE(REPLACE(c.telefone, '(', ''), ')', ''), '-', ''), ' ', '')) AS whatsapp,
    c.email,
    p11.total as 'total',
    CASE 
        WHEN p11.status = 'CONFIRMED' THEN 'Cartão de Crédito'
        WHEN p11.status IN ('PAID', 'RECEIVED') THEN 'PIX'
        WHEN p11.status = 'RECEIVED_IN_CASH' THEN 'Dinheiro'
        ELSE 'Outro'
    END AS 'pagamento',
    CASE 
        WHEN p11.frete = 1 THEN 'Entrega em casa'
        WHEN p11.frete = 0 THEN 'Retirar no dia'
        ELSE 'Outro'
    END AS Entrega
FROM pedidos p11
LEFT JOIN pedidos p17 
    ON p11.user_id = p17.user_id 
    AND p17.evento_id in (17)
LEFT JOIN clientes c
    ON p11.user_id = c.usuario_id
WHERE p11.evento_id = 12
AND p11.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH', 'PAID')
AND p17.id IS NULL
AND p11.crm = 'REVERTIDO'
ORDER BY p11.updated_at DESC;


    ";

        // Alterado de getResultArray() para getResult(), para que os dados venham como objetos
        return $this->db->query($sql, [$event_id])->getResult();
    }

    public function recuperaRecompraRejeitadaPorEvento(int $event_id)
    {
        $sql = "
    SELECT 
    p11.id,
    p11.crm,
    p11.created_at,
    p11.updated_at,
    p11.crmobs,
    c.nome,
    c.cpf,
    CONCAT('https://wa.me/55', REPLACE(REPLACE(REPLACE(REPLACE(c.telefone, '(', ''), ')', ''), '-', ''), ' ', '')) AS whatsapp,
    c.email,
    p11.total as 'total',
    CASE 
        WHEN p11.status = 'CONFIRMED' THEN 'Cartão de Crédito'
        WHEN p11.status IN ('PAID', 'RECEIVED') THEN 'PIX'
        WHEN p11.status = 'RECEIVED_IN_CASH' THEN 'Dinheiro'
        ELSE 'Outro'
    END AS 'pagamento',
    CASE 
        WHEN p11.frete = 1 THEN 'Entrega em casa'
        WHEN p11.frete = 0 THEN 'Retirar no dia'
        ELSE 'Outro'
    END AS Entrega
FROM pedidos p11
LEFT JOIN pedidos p17 
    ON p11.user_id = p17.user_id 
    AND p17.evento_id in (17)
LEFT JOIN clientes c
    ON p11.user_id = c.usuario_id
WHERE p11.evento_id = 12
AND p11.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH', 'PAID')
AND p17.id IS NULL
AND p11.crm = 'REJEITADO'
ORDER BY p11.updated_at DESC;


    ";

        // Alterado de getResultArray() para getResult(), para que os dados venham como objetos
        return $this->db->query($sql, [$event_id])->getResult();
    }

    public function recuperaRecompraIniciadaPorEvento(int $event_id)
    {
        $sql = "
    SELECT 
    p11.id,
    p11.crm,
    p11.created_at,
    p11.updated_at,
    p11.crmobs,
    c.nome,
    c.cpf,
    CONCAT('https://wa.me/55', REPLACE(REPLACE(REPLACE(REPLACE(c.telefone, '(', ''), ')', ''), '-', ''), ' ', '')) AS whatsapp,
    c.email,
    p11.total as 'total',
    CASE 
        WHEN p11.status = 'CONFIRMED' THEN 'Cartão de Crédito'
        WHEN p11.status IN ('PAID', 'RECEIVED') THEN 'PIX'
        WHEN p11.status = 'RECEIVED_IN_CASH' THEN 'Dinheiro'
        ELSE 'Outro'
    END AS 'pagamento',
    CASE 
        WHEN p11.frete = 1 THEN 'Entrega em casa'
        WHEN p11.frete = 0 THEN 'Retirar no dia'
        ELSE 'Outro'
    END AS Entrega
FROM pedidos p11
LEFT JOIN pedidos p17 
    ON p11.user_id = p17.user_id 
    AND p17.evento_id in(17)
LEFT JOIN clientes c
    ON p11.user_id = c.usuario_id
WHERE p11.evento_id = 12
AND p11.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH', 'PAID')
AND p17.id IS NULL
AND p11.crm = 'INICIADO'
ORDER BY p11.updated_at DESC;


    ";

        // Alterado de getResultArray() para getResult(), para que os dados venham como objetos
        return $this->db->query($sql, [$event_id])->getResult();
    }


    public function recuperaPedidosPorUsuario(int $usuario_id)
    {
        $atributos = [
            'pedidos.id',
            'pedidos.created_at',
            'pedidos.codigo as cod_pedido',
            'pedidos.rastreio',
            'pedidos.status',
            'pedidos.status_entrega',
            'pedidos.frete',
            'pedidos.total',
            'pedidos.evento_id',
            'eventos.nome as nome_evento',
            'eventos.slug',
            'eventos.data_inicio',
            'eventos.data_fim',
            'eventos.hora_inicio',
            'eventos.hora_fim',
            'eventos.local'
        ];



        return $this->select($atributos)
            ->join('usuarios', 'usuarios.id = pedidos.user_id')
            ->join('eventos', 'eventos.id = pedidos.evento_id')
            ->where('usuarios.id', $usuario_id)
            ->orderBy('pedidos.created_at', 'DESC')
            ->findAll();
    }

    public function recuperaTotalIngressosHojeValor($event_id)
    {


        $retorno = $this->select('sum(total) as total')
            //->join('ingressos', 'ingressos.pedido_id = pedidos.id')
            ->where('DATE(pedidos.created_at) >= DATE(NOW())')
            ->where('pedidos.evento_id', $event_id)
            ->whereIn('pedidos.status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
            //->whereNotIn('ingressos.tipo', ['cinemark', 'adicional', 'produto'])
            ->find();

        return $retorno;
    }

    public function recuperaTotalIngressosValor($event_id)
    {


        $retorno = $this->select('sum(total) as total')
            ->where('evento_id', $event_id)
            ->whereIn('status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
            ->find();

        return $retorno;
    }




    public function listaPedidosAdmin($event_id)
    {
        $atributos = [
            'pedidos.id',
            'pedidos.created_at',
            'pedidos.codigo as cod_pedido',
            'pedidos.rastreio',
            'pedidos.status',
            'pedidos.status_entrega',
            'pedidos.frete',
            'pedidos.total',
            'pedidos.evento_id',
            'clientes.email',
            'clientes.telefone',
            'clientes.nome',
            'clientes.cpf'

        ];



        return $this->select($atributos)
            ->join('usuarios', 'usuarios.id = pedidos.user_id')
            ->join('clientes', 'clientes.usuario_id = usuarios.id')
            ->where('pedidos.evento_id', $event_id)
            ->whereIn('pedidos.status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
            ->orderBy('pedidos.created_at', 'DESC')
            ->findAll();
    }

    public function listaPedidosAdminVip($event_id)
    {
        $atributos = [
            'pedidos.id',
            'pedidos.created_at',
            'pedidos.codigo as cod_pedido',
            'pedidos.rastreio',
            'pedidos.status',
            'pedidos.status_entrega',
            'pedidos.frete',
            'pedidos.total',
            'pedidos.evento_id',
            'clientes.email',
            'clientes.telefone',
            'clientes.nome',
            'clientes.cpf',
            'ingressos.cinemark'

        ];



        return $this->select($atributos)
            ->join('usuarios', 'usuarios.id = pedidos.user_id')
            ->join('ingressos', 'ingressos.pedido_id = pedidos.id')
            ->join('clientes', 'clientes.usuario_id = usuarios.id')
            ->whereIn('pedidos.status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
            ->where('ingressos.cinemark is null')
            ->where('pedidos.evento_id', $event_id)
            ->like('ingressos.nome', 'vip')
            ->orderBy('pedidos.created_at', 'ASC')
            ->findAll();
    }

    public function listaPedidosAdminVipEntregue($event_id)
    {
        $atributos = [
            'pedidos.id',
            'pedidos.created_at',
            'pedidos.codigo as cod_pedido',
            'pedidos.rastreio',
            'pedidos.status',
            'pedidos.status_entrega',
            'pedidos.frete',
            'pedidos.total',
            'pedidos.evento_id',
            'clientes.email',
            'clientes.telefone',
            'clientes.nome',
            'clientes.cpf',
            'ingressos.cinemark'

        ];



        return $this->select($atributos)
            ->join('usuarios', 'usuarios.id = pedidos.user_id')
            ->join('ingressos', 'ingressos.pedido_id = pedidos.id')
            ->join('clientes', 'clientes.usuario_id = usuarios.id')
            ->whereIn('pedidos.status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
            ->where('ingressos.cinemark is not null')
            ->like('ingressos.nome', 'vip')
            ->where('pedidos.evento_id', $event_id)
            ->orderBy('pedidos.created_at', 'DESC')
            ->findAll();
    }

    public function listaPedidosAdminEntrega($event_id)
    {
        $atributos = [
            'pedidos.id',
            'pedidos.created_at',
            'pedidos.codigo as cod_pedido',
            'pedidos.rastreio',
            'pedidos.status',
            'pedidos.status_entrega',
            'pedidos.frete',
            'pedidos.total',
            'pedidos.evento_id',
            'clientes.email',
            'clientes.telefone',
            'clientes.nome',
            'clientes.cpf'

        ];



        return $this->select($atributos)
            ->join('usuarios', 'usuarios.id = pedidos.user_id')
            ->join('clientes', 'clientes.usuario_id = usuarios.id')
            ->whereIn('pedidos.status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
            ->where('pedidos.evento_id', $event_id)
            ->where('pedidos.frete', 1)
            ->where('pedidos.rastreio', null)
            ->orderBy('pedidos.created_at', 'ASC')
            ->findAll();
    }

    public function listaPedidosAdminEnviados($event_id)
    {
        $atributos = [
            'pedidos.id',
            'pedidos.created_at',
            'pedidos.codigo as cod_pedido',
            'pedidos.rastreio',
            'pedidos.status',
            'pedidos.status_entrega',
            'pedidos.frete',
            'pedidos.total',
            'pedidos.evento_id',
            'clientes.email',
            'clientes.telefone',
            'clientes.nome',
            'clientes.cpf'

        ];



        return $this->select($atributos)
            ->join('usuarios', 'usuarios.id = pedidos.user_id')
            ->join('clientes', 'clientes.usuario_id = usuarios.id')
            ->whereIn('pedidos.status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
            ->where('pedidos.evento_id', $event_id)
            ->where('pedidos.frete', 1)
            ->where('pedidos.rastreio is not null')
            ->orderBy('pedidos.created_at', 'ASC')
            ->findAll();
    }

    public function recuperaPedido(int $id)
    {
        $atributos = [
            'pedidos.id',
            'pedidos.created_at',
            'pedidos.codigo as cod_pedido',
            'pedidos.rastreio',
            'pedidos.status',
            'pedidos.status_entrega',
            'pedidos.frete',
            'pedidos.total',
            'pedidos.evento_id',
            'pedidos.user_id'

        ];



        return $this->select($atributos)
            ->find($id);
    }

    public function recuperaIngressoPedido(int $id)
    {
        $atributos = [
            'pedidos.id',
            'pedidos.created_at',
            'pedidos.codigo as cod_pedido',
            'pedidos.rastreio',
            'pedidos.status',
            'pedidos.status_entrega',
            'pedidos.frete',
            'pedidos.total',
            'pedidos.evento_id',
            'pedidos.user_id',
            'ingressos.codigo',
            'ingressos.nome',
            'ingressos.valor_unitario'

        ];



        return $this->select($atributos)
            ->join('ingressos', 'ingressos.pedido_id = pedidos.id')

            ->where('ingressos.id', $id)
            ->first();
    }

    public function listaPedidosAdminPendentes($event_id)
    {
        $atributos = [
            'pedidos.id',
            'pedidos.created_at',
            'pedidos.codigo as cod_pedido',
            'pedidos.rastreio',
            'pedidos.status',
            'pedidos.status_entrega',
            'pedidos.frete',
            'pedidos.total',
            'pedidos.evento_id',
            'pedidos.data_vencimento',
            'clientes.email',
            'clientes.telefone',
            'clientes.nome',
            'clientes.cpf'

        ];

        return $this->select($atributos)
            ->join('usuarios', 'usuarios.id = pedidos.user_id')
            ->join('clientes', 'clientes.usuario_id = usuarios.id')
            ->where('pedidos.evento_id', $event_id)
            ->whereIn('pedidos.status', ['PENDING', 'OVERDUE'])
            ->orderBy('pedidos.created_at', 'DESC')
            ->findAll();
    }

    public function listaPedidosAdminReembolsados($event_id)
    {
        $atributos = [
            'pedidos.id',
            'pedidos.created_at',
            'pedidos.codigo as cod_pedido',
            'pedidos.rastreio',
            'pedidos.status',
            'pedidos.status_entrega',
            'pedidos.frete',
            'pedidos.total',
            'pedidos.evento_id',
            'pedidos.data_vencimento',
            'clientes.email',
            'clientes.telefone',
            'clientes.nome',
            'clientes.cpf'

        ];

        return $this->select($atributos)
            ->join('usuarios', 'usuarios.id = pedidos.user_id')
            ->join('clientes', 'clientes.usuario_id = usuarios.id')
            ->where('pedidos.evento_id', $event_id)
            ->whereIn('pedidos.status', ['REFUNDED'])
            ->orderBy('pedidos.created_at', 'DESC')
            ->findAll();
    }

    public function listaPedidosAdminChargeback($event_id)
    {
        $atributos = [
            'pedidos.id',
            'pedidos.created_at',
            'pedidos.codigo as cod_pedido',
            'pedidos.rastreio',
            'pedidos.status',
            'pedidos.status_entrega',
            'pedidos.frete',
            'pedidos.total',
            'pedidos.evento_id',
            'pedidos.data_vencimento',
            'clientes.email',
            'clientes.telefone',
            'clientes.nome',
            'clientes.cpf'

        ];

        return $this->select($atributos)
            ->join('usuarios', 'usuarios.id = pedidos.user_id')
            ->join('clientes', 'clientes.usuario_id = usuarios.id')
            ->where('pedidos.evento_id', $event_id)
            ->like('pedidos.status', 'CHARGEBACK')
            ->orderBy('pedidos.created_at', 'DESC')
            ->findAll();
    }
}
