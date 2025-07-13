<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\Endereco;
use App\Traits\ValidacoesTrait;

class Pedidos extends BaseController
{
	use ValidacoesTrait;

	private $clienteModel;
	private $usuarioModel;
	private $cartaoModel;
	private $pedidosModel;
	private $enderecoModel;
	private $ingressoModel;
	private $credencialModel;
	private $eventoModel;



	public function __construct()
	{
		$this->clienteModel = new \App\Models\ClienteModel();
		$this->usuarioModel = new \App\Models\UsuarioModel();
		$this->cartaoModel = new \App\Models\CartaoModel();
		$this->pedidosModel = new \App\Models\PedidoModel();
		$this->enderecoModel = new \App\Models\EnderecoModel();
		$this->ingressoModel = new \App\Models\IngressoModel();
		$this->credencialModel = new \App\Models\CredencialModel();
		$this->eventoModel = new \App\Models\EventoModel();
	}

	public function index()
	{

		$id = $this->usuarioLogado()->id;

		$cli = $this->clienteModel->withDeleted(true)->where('usuario_id', $id)->first();

		$cliente = $this->buscaclienteOu404($cli->id);

		$pedidos = $this->pedidosModel->recuperaPedidosPorUsuario($id);


		$card = $this->cartaoModel->withDeleted(true)->where('user_id', $id)->first();
		//dd($ingressos);
		$data = [
			'titulo' => 'Dashboard de ' . esc($cliente->nome),
			'cliente' => $cliente,
			'card' => $card,
			'pedidos' => $pedidos
		];


		return view('Pedidos/meus', $data);
	}

	    public function gerenciar()
    {

        if (!$this->usuarioLogado()->temPermissaoPara('editar-clientes')) {

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $id = $this->usuarioLogado()->id;

        $eventos = $this->eventoModel->orderBy('id', 'DESC')->findAll();
        
        // Verificar se há evento selecionado no contexto
        $event_id = session()->get('event_id');
        $evento_selecionado = null;
        
        if ($event_id) {
            $evento_selecionado = $this->eventoModel->find($event_id);
        }

        // Se há evento selecionado, mostrar diretamente os pedidos
        if ($event_id && $evento_selecionado) {
            $data = [
                'titulo' => 'Gerenciamento de pedidos',
                'evento' => $event_id,
                'evento_selecionado' => $evento_selecionado,
            ];
            
            return view('Pedidos/gerenciar_evento', $data);
        }

        // Se não há evento selecionado, mostrar lista de eventos
        $data = [
            'titulo' => 'Gerenciamento de pedidos',
            'eventos' => $eventos,
            'event_id' => $event_id,
            'evento_selecionado' => $evento_selecionado,
        ];


        return view('Pedidos/gerenciar', $data);
    }
	public function gerenciar_evento($event_id)
	{

		if (!$this->usuarioLogado()->temPermissaoPara('editar-clientes')) {

			return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
		}

		$id = $this->usuarioLogado()->id;

		// Buscar dados do evento
		$evento_selecionado = $this->eventoModel->find($event_id);

		$data = [
			'titulo' => 'Gerenciamento de pedidos',
			'evento' => $event_id,
			'evento_selecionado' => $evento_selecionado,
		];


		return view('Pedidos/gerenciar_evento', $data);
	}

	public function entrega($event_id)
	{

		if (!$this->usuarioLogado()->temPermissaoPara('editar-clientes')) {

			return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
		}

		$id = $this->usuarioLogado()->id;



		$data = [
			'titulo' => 'Pedidos aguardando envio',
			'evento' => $event_id,
		];


		return view('Pedidos/entrega', $data);
	}

	public function vip($event_id)
	{

		if (!$this->usuarioLogado()->temPermissaoPara('editar-clientes')) {

			return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
		}

		$id = $this->usuarioLogado()->id;



		$data = [
			'titulo' => 'Pedidos aguardando envio',
			'evento' => $event_id,
		];


		return view('Pedidos/vip', $data);
	}

	public function vipentregue($event_id)
	{

		if (!$this->usuarioLogado()->temPermissaoPara('editar-clientes')) {

			return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
		}

		$id = $this->usuarioLogado()->id;



		$data = [
			'titulo' => 'Pedidos aguardando envio',
			'evento' => $event_id,
		];


		return view('Pedidos/vipentregue', $data);
	}

	public function enviados($event_id)
	{

		if (!$this->usuarioLogado()->temPermissaoPara('editar-clientes')) {

			return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
		}

		$id = $this->usuarioLogado()->id;

		// Buscar dados do evento
		$evento_selecionado = $this->eventoModel->find($event_id);

		$data = [
			'titulo' => 'Pedidos Enviados',
			'evento' => $event_id,
			'evento_selecionado' => $evento_selecionado,
		];

		return view('Pedidos/enviados', $data);
	}

	public function pendentes($event_id)
	{

		if (!$this->usuarioLogado()->temPermissaoPara('editar-clientes')) {

			return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
		}

		$id = $this->usuarioLogado()->id;

		// Buscar dados do evento
		$evento_selecionado = $this->eventoModel->find($event_id);

		$data = [
			'titulo' => 'Pedidos Pendentes',
			'evento' => $event_id,
			'evento_selecionado' => $evento_selecionado,
		];

		return view('Pedidos/pendentes', $data);
	}

	public function recompra($event_id)
	{

		if (!$this->usuarioLogado()->temPermissaoPara('editar-clientes')) {

			return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
		}

		$usuario_logado = $this->usuarioLogado()->id;


		$recompra = $this->pedidosModel->recuperaRecompraPorEvento($event_id);

		$data = [
			'titulo' => 'Recompra',
			'recompra' => $recompra,
			'usuario_logado' => $usuario_logado,
			'evento' => $event_id,
		];



		return view('Pedidos/recompra', $data);
	}

	public function recomprarevertida($event_id)
	{

		if (!$this->usuarioLogado()->temPermissaoPara('editar-clientes')) {

			return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
		}

		$usuario_logado = $this->usuarioLogado()->id;


		$recompra = $this->pedidosModel->recuperaRecompraRevertidaPorEvento($event_id);

		$data = [
			'titulo' => 'Recompra',
			'recompra' => $recompra,
			'usuario_logado' => $usuario_logado,
			'evento' => $event_id,
		];



		return view('Pedidos/recomprarevertida', $data);
	}

	public function recomprarejeitada($event_id)
	{

		if (!$this->usuarioLogado()->temPermissaoPara('editar-clientes')) {

			return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
		}

		$usuario_logado = $this->usuarioLogado()->id;


		$recompra = $this->pedidosModel->recuperaRecompraRejeitadaPorEvento($event_id);

		$data = [
			'titulo' => 'Recompra',
			'recompra' => $recompra,
			'usuario_logado' => $usuario_logado,
			'evento' => $event_id,
		];



		return view('Pedidos/recomprarejeitada', $data);
	}

	public function recomprainiciada($event_id)
	{

		if (!$this->usuarioLogado()->temPermissaoPara('editar-clientes')) {

			return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
		}

		$usuario_logado = $this->usuarioLogado()->id;


		$recompra = $this->pedidosModel->recuperaRecompraIniciadaPorEvento($event_id);

		$data = [
			'titulo' => 'Recompra',
			'recompra' => $recompra,
			'usuario_logado' => $usuario_logado,
			'evento' => $event_id,
		];



		return view('Pedidos/recomprainiciada', $data);
	}

	public function recuperaPedidosAdmin($event_id)
	{
		if (!$this->request->isAJAX()) {
			return redirect()->back();
		}

		$pedidos = $this->pedidosModel->listaPedidosAdmin($event_id);

		// Receberá o array de objetos de clientes
		$data = [];

		foreach ($pedidos as $pedido) {
			$data[] = [

				'cod_pedido' => anchor("pedidos/ingressos/" . $pedido->id, esc($pedido->cod_pedido), 'title="Exibir usuário ' . esc($pedido->cod_pedido) . ' "'),
				'nome' => esc($pedido->nome),
				'email' => esc($pedido->email),
				'telefone' => esc($pedido->telefone),
				'status' => esc($pedido->status),
				'cpf' => esc($pedido->cpf),
				'frete' => $pedido->frete,
				'status_entrega' => esc($pedido->status_entrega),
				'rastreio' => esc($pedido->rastreio),
			];
		}

		$retorno = [
			'data' => $data,
		];

		return $this->response->setJSON($retorno);
	}

	public function recuperaPedidosAdminEntrega(int $event_id)
	{
		if (!$this->request->isAJAX()) {
			return redirect()->back();
		}

		$pedidos = $this->pedidosModel->listaPedidosAdminEntrega($event_id);

		// Receberá o array de objetos de clientes
		$data = [];

		foreach ($pedidos as $pedido) {
			$data[] = [

				'cod_pedido' => anchor("pedidos/ingressos/" . $pedido->id, esc($pedido->cod_pedido), 'title="Exibir usuário ' . esc($pedido->cod_pedido) . ' "'),
				'nome' => esc($pedido->nome),
				'email' => esc($pedido->email),
				'telefone' => esc($pedido->telefone),
				'status' => esc($pedido->status),
				'cpf' => esc($pedido->cpf),
				'frete' => $pedido->frete,
				'status_entrega' => esc($pedido->status_entrega),
				'rastreio' => esc($pedido->rastreio),
			];
		}

		$retorno = [
			'data' => $data,
		];

		return $this->response->setJSON($retorno);
	}

	public function recuperaPedidosAdminVip(int $event_id)
	{
		if (!$this->request->isAJAX()) {
			return redirect()->back();
		}

		$pedidos = $this->pedidosModel->listaPedidosAdminVip($event_id);

		// Receberá o array de objetos de clientes
		$data = [];

		foreach ($pedidos as $pedido) {
			// Limpar e formatar o número de telefone para WhatsApp
			$telefone_limpo = $this->limparTelefone($pedido->telefone);
			$whatsapp_link = $telefone_limpo ? "https://wa.me/55" . $telefone_limpo : $pedido->telefone;
			
			$data[] = [

				'cod_pedido' => anchor("pedidos/ingressos/" . $pedido->id, esc($pedido->cod_pedido), 'title="Exibir usuário ' . esc($pedido->cod_pedido) . ' "'),
				'nome' => esc($pedido->nome),
				'email' => esc($pedido->email),
				'telefone' => $telefone_limpo ? anchor($whatsapp_link, esc($pedido->telefone), 'target="_blank" title="Abrir WhatsApp"') : esc($pedido->telefone),
				'status' => esc($pedido->status),
				'cinemark' => esc($pedido->cinemark),
				'frete' => $pedido->frete,
				'status_entrega' => esc($pedido->status_entrega),
				'rastreio' => esc($pedido->rastreio),
			];
		}

		$retorno = [
			'data' => $data,
		];

		return $this->response->setJSON($retorno);
	}

	public function recuperaPedidosAdminVipEntregue(int $event_id)
	{
		if (!$this->request->isAJAX()) {
			return redirect()->back();
		}

		$pedidos = $this->pedidosModel->listaPedidosAdminVipEntregue($event_id);

		// Receberá o array de objetos de clientes
		$data = [];

		foreach ($pedidos as $pedido) {
			// Limpar e formatar o número de telefone para WhatsApp
			$telefone_limpo = $this->limparTelefone($pedido->telefone);
			$whatsapp_link = $telefone_limpo ? "https://wa.me/55" . $telefone_limpo : $pedido->telefone;
			
			$data[] = [

				'cod_pedido' => anchor("pedidos/ingressos/" . $pedido->id, esc($pedido->cod_pedido), 'title="Exibir usuário ' . esc($pedido->cod_pedido) . ' "'),
				'nome' => esc($pedido->nome),
				'email' => esc($pedido->email),
				'telefone' => $telefone_limpo ? anchor($whatsapp_link, esc($pedido->telefone), 'target="_blank" title="Abrir WhatsApp"') : esc($pedido->telefone),
				'status' => esc($pedido->status),
				'cinemark' => esc($pedido->cinemark),
				'frete' => $pedido->frete,
				'status_entrega' => esc($pedido->status_entrega),
				'rastreio' => esc($pedido->rastreio),
			];
		}

		$retorno = [
			'data' => $data,
		];

		return $this->response->setJSON($retorno);
	}
	public function recuperaPedidosAdminEnviados(int $event_id)
	{
		if (!$this->request->isAJAX()) {
			return redirect()->back();
		}

		$pedidos = $this->pedidosModel->listaPedidosAdminEnviados($event_id);

		// Receberá o array de objetos de clientes
		$data = [];

		foreach ($pedidos as $pedido) {
			$data[] = [

				'cod_pedido' => anchor("pedidos/ingressos/" . $pedido->id, esc($pedido->cod_pedido), 'title="Exibir usuário ' . esc($pedido->cod_pedido) . ' "'),
				'nome' => esc($pedido->nome),
				'email' => esc($pedido->email),
				'telefone' => esc($pedido->telefone),
				'status' => esc($pedido->status),
				'cpf' => esc($pedido->cpf),
				'frete' => $pedido->frete,
				'status_entrega' => esc($pedido->status_entrega),
				'rastreio' => esc($pedido->rastreio),
			];
		}

		$retorno = [
			'data' => $data,
		];

		return $this->response->setJSON($retorno);
	}

	public function recuperaPedidosAdminPendentes(int $event_id)
	{
		if (!$this->request->isAJAX()) {
			return redirect()->back();
		}

		$pedidos = $this->pedidosModel->listaPedidosAdminPendentes($event_id);

		// Receberá o array de objetos de clientes
		$data = [];

		foreach ($pedidos as $pedido) {
			$data[] = [
				'cod_pedido' => anchor("pedidos/ingressos/" . $pedido->id, esc($pedido->cod_pedido), 'title="Exibir usuário ' . esc($pedido->cod_pedido) . ' "'),
				'nome' => esc($pedido->nome),
				'email' => esc($pedido->email),
				'telefone' => esc($pedido->telefone),
				'status' => esc($pedido->status),
				'cpf' => esc($pedido->cpf),
				'frete' => $pedido->frete,
				'status_entrega' => esc($pedido->status_entrega),
				'rastreio' => esc($pedido->rastreio),
				'data_vencimento' => $pedido->data_vencimento ? date('d/m/Y', strtotime($pedido->data_vencimento)) : '-',
			];
		}

		$retorno = [
			'data' => $data,
		];

		return $this->response->setJSON($retorno);
	}

	public function reembolsados($event_id)
	{

		if (!$this->usuarioLogado()->temPermissaoPara('editar-clientes')) {

			return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
		}

		$id = $this->usuarioLogado()->id;

		// Buscar dados do evento
		$evento_selecionado = $this->eventoModel->find($event_id);

		$data = [
			'titulo' => 'Pedidos Reembolsados',
			'evento' => $event_id,
			'evento_selecionado' => $evento_selecionado,
		];

		return view('Pedidos/reembolsados', $data);
	}

	public function recuperaPedidosAdminReembolsados(int $event_id)
	{
		if (!$this->request->isAJAX()) {
			return redirect()->back();
		}

		$pedidos = $this->pedidosModel->listaPedidosAdminReembolsados($event_id);

		// Receberá o array de objetos de clientes
		$data = [];

		foreach ($pedidos as $pedido) {
			$data[] = [
				'cod_pedido' => anchor("pedidos/ingressos/" . $pedido->id, esc($pedido->cod_pedido), 'title="Exibir usuário ' . esc($pedido->cod_pedido) . ' "'),
				'nome' => esc($pedido->nome),
				'email' => esc($pedido->email),
				'telefone' => esc($pedido->telefone),
				'status' => esc($pedido->status),
				'cpf' => esc($pedido->cpf),
				'frete' => $pedido->frete,
				'status_entrega' => esc($pedido->status_entrega),
				'rastreio' => esc($pedido->rastreio),
				'data_vencimento' => $pedido->data_vencimento ? date('d/m/Y', strtotime($pedido->data_vencimento)) : '-',
			];
		}

		$retorno = [
			'data' => $data,
		];

		return $this->response->setJSON($retorno);
	}

	public function chargeback($event_id)
	{

		if (!$this->usuarioLogado()->temPermissaoPara('editar-clientes')) {

			return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
		}

		$id = $this->usuarioLogado()->id;

		// Buscar dados do evento
		$evento_selecionado = $this->eventoModel->find($event_id);

		$data = [
			'titulo' => 'Pedidos com Chargeback',
			'evento' => $event_id,
			'evento_selecionado' => $evento_selecionado,
		];

		return view('Pedidos/chargeback', $data);
	}

	public function recuperaPedidosAdminChargeback(int $event_id)
	{
		if (!$this->request->isAJAX()) {
			return redirect()->back();
		}

		$pedidos = $this->pedidosModel->listaPedidosAdminChargeback($event_id);

		// Receberá o array de objetos de clientes
		$data = [];

		foreach ($pedidos as $pedido) {
			$data[] = [
				'cod_pedido' => anchor("pedidos/ingressos/" . $pedido->id, esc($pedido->cod_pedido), 'title="Exibir usuário ' . esc($pedido->cod_pedido) . ' "'),
				'nome' => esc($pedido->nome),
				'email' => esc($pedido->email),
				'telefone' => esc($pedido->telefone),
				'status' => esc($pedido->status),
				'cpf' => esc($pedido->cpf),
				'frete' => $pedido->frete,
				'status_entrega' => esc($pedido->status_entrega),
				'rastreio' => esc($pedido->rastreio),
				'data_vencimento' => $pedido->data_vencimento ? date('d/m/Y', strtotime($pedido->data_vencimento)) : '-',
			];
		}

		$retorno = [
			'data' => $data,
		];

		return $this->response->setJSON($retorno);
	}

	public function ingressos($id)
	{

		$pedido_id = $id;

		$pedido = $this->pedidosModel->recuperaPedido($pedido_id);




		$cli = $this->clienteModel->withDeleted(true)->where('usuario_id', $pedido->user_id)->first();

		$cliente = $this->buscaclienteOu404($cli->id);


		$endereco = $this->enderecoModel->where('pedido_id', $pedido_id)->first();


		$todos = $this->ingressoModel->recuperaIngressosPorUsuario($pedido->user_id);


		$ingressos = $this->ingressoModel->recuperaIngressosPorPedido($pedido_id);

		$credenciais = $this->credencialModel->withDeleted(true)->where('pedido_id', $pedido_id)->findAll();
		$data = [
			'titulo' => 'Ingressos do pedido' . esc($pedido->cod_pedido),
			'todos' => $todos,
			'ingressos' => $ingressos,
			'pedido' => $pedido,
			'cliente' => $cliente,
			'endereco' => $endereco,
			'credenciais' => $credenciais
		];


		return view('Pedidos/ingressos', $data);
	}

	public function rastreio($pedido_id)
	{

		// Envio o hash do token do form
		$retorno['token'] = csrf_hash();

		// Recupero o post da requisição
		$post = $this->request->getPost();


		$pedido = $this->pedidosModel->find($pedido_id);

		$pedido->fill($post);


		if ($this->pedidosModel->save($pedido)) {

			$atributos = [
				'clientes.id',
				'clientes.nome',
				'clientes.cpf',
				'clientes.email',
				'clientes.telefone',
				'clientes.deleted_at',
				'clientes.usuario_id'
			];
			$cliente = $this->clienteModel->select($atributos)
				->where('usuario_id', $pedido->user_id)
				->first();





			$this->enviaEmailRastreio($cliente);

			return redirect()->to(site_url("pedidos/ingressos/" . $pedido_id))->with('sucesso', "Participante alterado com sucesso");
		}

		return redirect()->to(site_url("pedidos/ingressos/" . $pedido_id))->with('atencao', "Erro ao alterar o participante, contate o suporte!");
	}

	private function enviaEmailRastreio(object $cliente): void
	{
		$email = service('email');

		$email->setFrom(env('email.fromEmail'), env('email.fromName'));

		$email->setTo($cliente->email);

		$email->setCC('relacionamento@mundodream.com.br');

		$email->setSubject('Olá, seus ingressos foram enviados!');

		$data = [
			'cliente' => $cliente,
		];

		$mensagem = view('Pedidos/email_rastreio', $data);

		$email->setMessage($mensagem);

		$email->send();
	}



	public function receberCartao()
	{
		$id = $this->usuarioLogado()->id;

		$cli = $this->clienteModel->withDeleted(true)->where('usuario_id', $id)->first();

		$cliente = $this->buscaclienteOu404($cli->id);

		$pedidos = $this->pedidosModel->recuperaPedidosPorUsuario($id);


		$card = $this->cartaoModel->withDeleted(true)->where('user_id', $id)->first();
		//dd($ingressos);
		$data = [
			'titulo' => 'Receber Cartão',
			'cliente' => $cliente,
			'card' => $card,
		];


		return view('Pedidos/receber_cartao', $data);
	}

	public function gerenciarEndereco($pedido_id)
	{

		$id = $this->usuarioLogado()->id;

		$cli = $this->clienteModel->withDeleted(true)->where('usuario_id', $id)->first();

		$cliente = $this->buscaclienteOu404($cli->id);

		//$pedidos = $this->pedidosModel->recuperaPedidosPorPedido($pedido_id);


		$endereco = $this->enderecoModel->where('pedido_id', $pedido_id)->first();

		//dd($endereco);
		$data = [
			'titulo' => 'Gerenciar Endereço',
			'cliente' => $cliente,
			'endereco' => $endereco,
			'pedido_id' => $pedido_id,

		];



		if (isset($endereco)) {
			return view('Pedidos/editar_endereco', $data);
		} else {
			return view('Pedidos/receber_cartao', $data);
		}
	}



	public function registrar_endereco_cartao()
	{
		if (!$this->request->isAJAX()) {
			return redirect()->back();
		}

		// Envio o hash do token do form
		$retorno['token'] = csrf_hash();

		$user_id = $this->usuarioLogado()->id;

		$cli = $this->clienteModel->withDeleted(true)->where('usuario_id', $user_id)->first();

		$cliente = $this->buscaclienteOu404($cli->id);

		// Recupero o post da requisição
		$post = $this->request->getPost();

		$endereco = new Endereco($post);

		if ($this->enderecoModel->save($endereco)) {
			session()->setFlashdata('sucesso', 'Dados salvos com sucesso!');

			$retorno['id'] = $this->enderecoModel->getInsertID();

			$this->cartaoModel
				->protect(false)
				->where('user_id', $user_id)
				->set('endereco_id', $retorno['id'])
				->set('status', 'Preparando')
				->update();

			//$this->enviaEmailEnvioCartao($cliente);

			return $this->response->setJSON($retorno);
		}

		// Retornamos os erros de validação
		$retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
		$retorno['erros_model'] = $this->enderecoModel->errors();

		// Retorno para o ajax request
		return $this->response->setJSON($retorno);
	}

	public function editar_endereco_pedido()
	{
		if (!$this->request->isAJAX()) {
			return redirect()->back();
		}

		// Envio o hash do token do form
		$retorno['token'] = csrf_hash();

		$user_id = $this->usuarioLogado()->id;

		$cli = $this->clienteModel->withDeleted(true)->where('usuario_id', $user_id)->first();

		$cliente = $this->buscaclienteOu404($cli->id);

		// Recupero o post da requisição
		$post = $this->request->getPost();
		$pedido_id = $post['pedido_id'];
		$endereco = $this->enderecoModel->where('pedido_id', $pedido_id)->first();
		$endereco->fill($post);

		if ($endereco->hasChanged() === false) {
			$retorno['info'] = 'Não há dados para atualizar';
			return $this->response->setJSON($retorno);
		}

		if ($this->enderecoModel->save($endereco)) {
			session()->setFlashdata('sucesso', 'Dados salvos com sucesso!');

			$retorno['id'] = $this->enderecoModel->getInsertID();


			//$this->enviaEmailEnvioCartao($cliente);

			return $this->response->setJSON($retorno);
		}

		// Retornamos os erros de validação
		$retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
		$retorno['erros_model'] = $this->enderecoModel->errors();

		// Retorno para o ajax request
		return $this->response->setJSON($retorno);
	}

	/**
	 * Método que recupera o cliente
	 *
	 * @param integer $id
	 * @return Exceptions|object
	 */
	private function buscaclienteOu404(int $id = null)
	{
		if (!$id || !$cliente = $this->clienteModel->recuperaCliente($id)) {
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos o cliente $id");
		}

		return $cliente;
	}

	private function enviaEmailEnvioCartao(object $cliente): void
	{
		$email = service('email');

		$email->setFrom(env('email.fromEmail'), env('email.fromName'));

		$email->setTo($cliente->email);

		$email->setCC('relacionamento@mundodream.com.br');

		$email->setSubject('Endereço atualizado com sucesso!');

		$data = [
			'cliente' => $cliente,
		];

		$mensagem = view('Pedidos/email_envio_cartao', $data);

		$email->setMessage($mensagem);

		$email->send();
	}


	public function checkContact($id)
	{
		$pedido = $this->pedidosModel->withDeleted(true)->where('id', $id)->first();

		$atributos = [
			'clientes.id',
			'clientes.nome',
			'clientes.cpf',
			'clientes.email',
			'clientes.telefone',
			'clientes.deleted_at',
			'clientes.usuario_id'
		];

		$cliente = $this->clienteModel->select($atributos)
			->withDeleted(true)
			->where('clientes.usuario_id', $pedido->user_id)
			->orderBy('id', 'DESC')
			->first();

		$this->pedidosModel
			->protect(false)
			->where('id', $id)
			->set('crm', 'INICIADO')
			->update();


		return redirect()->to(site_url("pedidos/recompra/"))->with('sucesso', "Contato iniciado!");
	}

	public function revertido($id)
	{
		$pedido = $this->pedidosModel->withDeleted(true)->where('id', $id)->first();

		$this->pedidosModel
			->protect(false)
			->where('id', $id)
			->set('crm', 'REVERTIDO')
			->update();

		return redirect()->to(site_url("pedidos/recompra/"))->with('sucesso', "Pedido revertido!");
	}

	public function rejeitado($id)
	{
		$pedido = $this->pedidosModel->withDeleted(true)->where('id', $id)->first();


		$this->pedidosModel
			->protect(false)
			->where('id', $id)
			->set('crm', 'REJEITADO')
			->update();

		return redirect()->to(site_url("pedidos/recompra/"))->with('erro', "Pedido rejeitado!");
	}

	public function motivo($id)
	{
		// Envio o hash do token do form
		$retorno['token'] = csrf_hash();

		// Recupero o post da requisição
		$post = $this->request->getPost();

		$pedido = $this->pedidosModel->withDeleted(true)->where('id', $id)->first();

		$this->pedidosModel
			->protect(false)
			->where('id', $id)
			->set('crmobs', $post['crmobs'])
			->update();

		return redirect()->to(site_url("pedidos/recompra/"))->with('sucesso', "Pedido revertido!");
	}

	/**
	 * Método para limpar e formatar número de telefone para WhatsApp
	 *
	 * @param string $telefone
	 * @return string|null
	 */
	private function limparTelefone($telefone)
	{
		if (empty($telefone)) {
			return null;
		}

		// Remove todos os caracteres não numéricos
		$telefone_limpo = preg_replace('/[^0-9]/', '', $telefone);

		// Se o número começa com 0, remove o 0
		if (strlen($telefone_limpo) > 10 && substr($telefone_limpo, 0, 1) === '0') {
			$telefone_limpo = substr($telefone_limpo, 1);
		}

		// Verifica se o número tem pelo menos 10 dígitos (DDD + número)
		if (strlen($telefone_limpo) >= 10) {
			return $telefone_limpo;
		}

		return null;
	}
}
