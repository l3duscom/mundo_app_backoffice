<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\Endereco;
use App\Traits\ValidacoesTrait;
use App\Services\ResendService;

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
	private $resendService;
	private $dadosEnvioModel;
	private $bonusModel;
	private $pedidoOrderBumpModel;
	private $ticketModel;
	private $ingressoTrocaModel;
	private $ingressoUpgradeModel;



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
		$this->resendService = new ResendService();
		$this->dadosEnvioModel = new \App\Models\DadosEnvioModel();
		$this->bonusModel = new \App\Models\BonusModel();
		$this->pedidoOrderBumpModel = new \App\Models\PedidoOrderBumpModel();
		$this->ticketModel = new \App\Models\TicketModel();
		$this->ingressoTrocaModel = new \App\Models\IngressoTrocaModel();
		$this->ingressoUpgradeModel = new \App\Models\IngressoUpgradeModel();
	}

	public function index()
	{

		$id = $this->usuarioLogado()->id;

		$cli = $this->clienteModel->withDeleted(true)->where('usuario_id', $id)->first();

		$cliente = $this->buscaclienteOu404($cli->id);

		$pedidos = $this->pedidosModel->recuperaPedidosPorUsuario($id);

		// Separar próximos e anteriores
		$proximos = [];
		$anteriores = [];
		$hoje = date('Y-m-d');
		$ingressoModel = new \App\Models\IngressoModel();
		foreach ($pedidos as $pedido) {
			$data_fim = isset($pedido->data_fim) ? date('Y-m-d', strtotime($pedido->data_fim)) : null;
			// Buscar ingressos do pedido
			$pedido->ingressos = $ingressoModel->recuperaIngressosPorPedido($pedido->id);
			if ($data_fim && $data_fim >= $hoje) {
				$proximos[] = $pedido;
			} else {
				$anteriores[] = $pedido;
			}
		}

		$card = $this->cartaoModel->withDeleted(true)->where('user_id', $id)->first();
		
		// Buscar informações de refunds
		$refoundModel = new \App\Models\RefoundModel();
		$refoundsPendentes = $refoundModel->contaRefoundsPendentesPorCliente($cli->id);
		$refoundsTotal = count($refoundModel->listaRefoundsPorCliente($cli->id) ?? []);
		
		$data = [
			'titulo' => 'Dashboard de ' . esc($cliente->nome),
			'cliente' => $cliente,
			'card' => $card,
			'proximos' => $proximos,
			'anteriores' => $anteriores,
			'refoundsPendentes' => $refoundsPendentes,
			'refoundsTotal' => $refoundsTotal,
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
			$cupomBadge = '<span class="text-muted">&mdash;</span>';
			if (!empty($pedido->cupom_codigo)) {
				$cupomBadge = '<span class="badge bg-success" title="' . esc($pedido->cupom_nome ?? '') . '">'
					. esc($pedido->cupom_codigo) . '</span>';
			}

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
				'cupom' => $cupomBadge,
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
			
			// Buscar bônus cinemark para este pedido (via tabela bonus)
			$bonus_info = '';
			$bonus_cinemark = $this->bonusModel->where('user_id', $pedido->user_id)
				->where('tipo_bonus', 'cinemark')
				->first();
			if ($bonus_cinemark) {
				$bonus_info = '<span class="badge bg-success">Entregue</span>';
			} else {
				$bonus_info = '<span class="badge bg-warning">Aguardando</span>';
			}
			
			$data[] = [

				'cod_pedido' => anchor("pedidos/ingressos/" . $pedido->id, esc($pedido->cod_pedido), 'title="Exibir usuário ' . esc($pedido->cod_pedido) . ' "'),
				'nome' => esc($pedido->nome),
				'email' => esc($pedido->email),
				'telefone' => $telefone_limpo ? anchor($whatsapp_link, esc($pedido->telefone), 'target="_blank" title="Abrir WhatsApp"') : esc($pedido->telefone),
				'status' => esc($pedido->status),
				'bonus' => $bonus_info,
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
			
			// Bonus já foi entregue nesta listagem
			$bonus_info = '<span class="badge bg-success">Entregue</span>';
			
			$data[] = [

				'cod_pedido' => anchor("pedidos/ingressos/" . $pedido->id, esc($pedido->cod_pedido), 'title="Exibir usuário ' . esc($pedido->cod_pedido) . ' "'),
				'nome' => esc($pedido->nome),
				'email' => esc($pedido->email),
				'telefone' => $telefone_limpo ? anchor($whatsapp_link, esc($pedido->telefone), 'target="_blank" title="Abrir WhatsApp"') : esc($pedido->telefone),
				'status' => esc($pedido->status),
				'bonus' => $bonus_info,
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

		// Buscar acessos de cada ingresso
		$checkModel = new \App\Models\CheckModel();
		$acessosPorIngresso = [];
		$ultimoAcessoPorIngresso = [];
		
		foreach ($ingressos as $ingresso) {
			// Todos os acessos do ingresso
			$acessos = $checkModel
				->select('acessos.*, usuarios.nome as operador_nome')
				->join('usuarios', 'usuarios.id = acessos.operador_id', 'left')
				->where('ingresso_id', $ingresso->id)
				->orderBy('created_at', 'DESC')
				->findAll();
			
			$acessosPorIngresso[$ingresso->id] = $acessos;
			
			// Último acesso
			$ultimoAcessoPorIngresso[$ingresso->id] = !empty($acessos) ? $acessos[0] : null;
		}

		// Buscar bônus do usuário indexados por ingresso_id
		$bonus_por_ingresso = $this->bonusModel->getBonusPorUsuarioIndexado($pedido->user_id);

		// Buscar order bumps do pedido
		$orderBumps = $this->pedidoOrderBumpModel->getOrderBumpsPorPedido($pedido_id);

		// Histórico de trocas por ingresso
		$ingressoIds = array_map(fn($i) => (int) $i->id, $ingressos);
		$trocasPorIngresso = $this->ingressoTrocaModel->historicoPorIngressos($ingressoIds);

		// Upgrades por ingresso
		$upgradesPorIngresso = [];
		foreach ($ingressos as $ingresso) {
			$upgradesPorIngresso[$ingresso->id] = $this->ingressoUpgradeModel->porIngresso($ingresso->id);
		}

		// Origem da venda (UTM)
		$pedidoUtmModel = new \App\Models\PedidoUtmModel();
		$utmPedido = $pedidoUtmModel->buscaPorPedido((int) $pedido_id);

		$data = [
			'titulo' => 'Ingressos do pedido' . esc($pedido->cod_pedido),
			'todos' => $todos,
			'ingressos' => $ingressos,
			'pedido' => $pedido,
			'cliente' => $cliente,
			'endereco' => $endereco,
			'credenciais' => $credenciais,
			'acessosPorIngresso' => $acessosPorIngresso,
			'ultimoAcessoPorIngresso' => $ultimoAcessoPorIngresso,
			'bonus_por_ingresso' => $bonus_por_ingresso,
			'orderBumps' => $orderBumps,
			'trocasPorIngresso' => $trocasPorIngresso,
			'upgradesPorIngresso' => $upgradesPorIngresso,
			'utmPedido' => $utmPedido,
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





			$this->enviaEmailRastreio($cliente, $pedido_id, (int) $pedido->evento_id);

			return redirect()->to(site_url("pedidos/ingressos/" . $pedido_id))->with('sucesso', "Participante alterado com sucesso");
		}

		return redirect()->to(site_url("pedidos/ingressos/" . $pedido_id))->with('atencao', "Erro ao alterar o participante, contate o suporte!");
	}

	private function enviaEmailRastreio(object $cliente, ?int $pedido_id = null, ?int $event_id = null): void
	{
		// Buscar dados do evento se o event_id foi fornecido
		$evento = null;
		if ($event_id) {
			$evento = $this->eventoModel->find($event_id);
		}

		// Buscar order bumps do pedido (se houver)
		$orderBumps = [];
		if ($pedido_id) {
			try {
				$orderBumps = $this->pedidoOrderBumpModel->getOrderBumpsPorPedido($pedido_id);
			} catch (\Throwable $e) {
				log_message('error', 'Falha ao buscar order bumps para email rastreio: ' . $e->getMessage());
			}
		}

		$data = [
			'cliente' => $cliente,
			'evento' => $evento,
			'orderBumps' => $orderBumps,
		];

		$mensagem = view('Pedidos/email_rastreio', $data);

		// Enviar via Resend
		$this->resendService->enviarEmail(
			$cliente->email,
			'Olá, seus ingressos foram enviados!',
			$mensagem
		);
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

	/**
	 * Exibe dados de envio para exportação
	 */
	public function dadosEnvio($event_id)
	{
		if (!$this->usuarioLogado()->temPermissaoPara('editar-clientes')) {
			return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
		}

		// Buscar dados do evento
		$evento_selecionado = $this->eventoModel->find($event_id);

		if (!$evento_selecionado) {
			return redirect()->back()->with('erro', 'Evento não encontrado.');
		}

		// Buscar dados de envio
		$dadosEnvio = $this->dadosEnvioModel->buscarDadosEnvio($event_id);
		$totalPedidos = $this->dadosEnvioModel->contarPedidosParaEnvio($event_id);

		$data = [
			'titulo' => 'Exportar Dados de Envio',
			'evento' => $event_id,
			'evento_selecionado' => $evento_selecionado,
			'dadosEnvio' => $dadosEnvio,
			'totalPedidos' => $totalPedidos,
		];

		return view('Pedidos/dados_envio', $data);
	}

	/**
	 * Exporta CSV com dados de envio
	 */
	public function exportarEnvios($event_id)
	{
		if (!$this->usuarioLogado()->temPermissaoPara('editar-clientes')) {
			return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
		}

		// Buscar dados de envio
		$dadosEnvio = $this->dadosEnvioModel->buscarDadosEnvio($event_id);

		if (empty($dadosEnvio)) {
			return redirect()->back()->with('atencao', 'Não há pedidos para envio neste evento.');
		}

		// Nome do arquivo
		$evento = $this->eventoModel->find($event_id);
		$nomeEvento = $evento ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $evento->nome) : 'evento_' . $event_id;
		$filename = 'envios_' . $nomeEvento . '_' . date('Y-m-d_His') . '.csv';

		// Headers para download
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Pragma: no-cache');
		header('Expires: 0');

		// Abrir output stream
		$output = fopen('php://output', 'w');

		// BOM para UTF-8 (para Excel reconhecer acentos)
		fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

		// Cabeçalhos do CSV (excluindo campos internos)
		$headers = [
			'Nome',
			'Empresa',
			'CPF',
			'CEP',
			'Endereco',
			'Numero',
			'Complemento',
			'Bairro',
			'Cidade',
			'UF',
			'Aos Cuidados',
			'Nota Fiscal',
			'Servico',
			'Serv. Adicionais',
			'Valor Declarado',
			'Observacoes',
			'Conteudo',
			'DDD',
			'Telefone',
			'Email',
			'Chave',
			'Peso',
			'Altura',
			'Largura',
			'Comprimento',
			'Entrega Vizinho',
			'RFID'
		];
		
		fputcsv($output, $headers, ';');

		// Escrever dados
		foreach ($dadosEnvio as $row) {
			$linha = [
				$row['nome'],
				$row['empresa'],
				$row['cpf'],
				$row['cep'],
				$row['endereco'],
				$row['numero'],
				$row['complemento'] ?? '',
				$row['bairro'],
				$row['cidade'],
				$row['uf'],
				$row['aos_cuidados'],
				$row['nota_fiscal'],
				$row['servico'],
				$row['serv_adicionais'],
				number_format($row['valor_declarado'], 2, ',', '.'),
				$row['observacoes'],
				$row['conteudo'],
				$row['ddd'],
				$row['telefone'],
				$row['email'],
				$row['chave'],
				$row['peso'],
				$row['altura'],
				$row['largura'],
				$row['comprimento'],
				$row['entrega_vizinho'],
				$row['rfid']
			];
			
			fputcsv($output, $linha, ';');
		}

		fclose($output);
		exit;
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

	private function enviaEmailEnvioCartao(object $cliente, ?int $pedido_id = null): void
	{
		// Buscar order bumps do pedido (se houver)
		$orderBumps = [];
		if ($pedido_id) {
			try {
				$orderBumps = $this->pedidoOrderBumpModel->getOrderBumpsPorPedido($pedido_id);
			} catch (\Throwable $e) {
				log_message('error', 'Falha ao buscar order bumps para email envio cartao: ' . $e->getMessage());
			}
		}

		$data = [
			'cliente' => $cliente,
			'orderBumps' => $orderBumps,
		];

		$mensagem = view('Pedidos/email_envio_cartao', $data);

		// Enviar via Resend
		$this->resendService->enviarEmail(
			$cliente->email,
			'Endereço atualizado com sucesso!',
			$mensagem
		);
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


		return redirect()->to(site_url("pedidos/recompra/19"))->with('sucesso', "Contato iniciado!");
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

	/**
	 * Lista todas as solicitações de reembolso do usuário logado
	 */
	public function meusRefounds()
	{
		$id = $this->usuarioLogado()->id;

		$cli = $this->clienteModel->withDeleted(true)->where('usuario_id', $id)->first();

		if (!$cli) {
			return redirect()->back()->with('erro', 'Cliente não encontrado.');
		}

		$refoundModel = new \App\Models\RefoundModel();
		$refounds = $refoundModel->listaRefoundsPorCliente($cli->id);

		$data = [
			'titulo' => 'Minhas Solicitações de Reembolso',
			'refounds' => $refounds,
		];

		return view('Pedidos/meus_refounds', $data);
	}

	/**
	 * Exibe detalhes de uma solicitação de reembolso específica
	 */
	public function meuRefoundDetalhe(int $id = null)
	{
		if (!$id) {
			return redirect()->to(site_url('pedidos/meus-refounds'))->with('erro', 'ID inválido.');
		}

		$userId = $this->usuarioLogado()->id;
		$cli = $this->clienteModel->withDeleted(true)->where('usuario_id', $userId)->first();

		if (!$cli) {
			return redirect()->back()->with('erro', 'Cliente não encontrado.');
		}

		$refoundModel = new \App\Models\RefoundModel();
		$refound = $refoundModel->find($id);

		// Verifica se o refund pertence ao cliente logado
		if (!$refound || $refound->cliente_id != $cli->id) {
			return redirect()->to(site_url('pedidos/meus-refounds'))->with('erro', 'Solicitação não encontrada.');
		}

		$data = [
			'titulo' => 'Detalhes da Solicitação #' . $id,
			'refound' => $refound,
		];

		return view('Pedidos/meu_refound_detalhe', $data);
	}

	/**
	 * Marca um order bump do pedido como usado via AJAX
	 */
	public function marcarOrderBumpUsado(int $id = null)
	{
		if (!$this->request->isAJAX()) {
			return $this->response->setJSON(['erro' => true, 'mensagem' => 'Requisição inválida']);
		}

		if (!$id) {
			return $this->response->setJSON(['erro' => true, 'mensagem' => 'ID inválido']);
		}

		$pedidoId = $this->request->getPost('pedido_id');
		
		if (!$pedidoId) {
			return $this->response->setJSON(['erro' => true, 'mensagem' => 'Pedido não informado']);
		}

		// Verificar se o order bump pertence ao pedido
		$orderBump = $this->pedidoOrderBumpModel->getOrderBumpDoPedido($id, $pedidoId);
		
		if (!$orderBump) {
			return $this->response->setJSON(['erro' => true, 'mensagem' => 'Order bump não encontrado']);
		}

		// Marcar como usado
		if ($this->pedidoOrderBumpModel->marcarComoUsado($id)) {
			return $this->response->setJSON([
				'erro' => false,
				'mensagem' => 'Order bump marcado como usado com sucesso',
				'usado_em' => date('d/m/Y H:i'),
			]);
		}

		return $this->response->setJSON(['erro' => true, 'mensagem' => 'Erro ao marcar como usado']);
	}

	public function upgradeOpcoes($ingressoId)
	{
		if (!$this->request->isAJAX()) return redirect()->back();

		// Load ingresso with ticket_id and event_id
		$ingresso = $this->ingressoModel
			->select('ingressos.id, ingressos.ticket_id, ingressos.nome, ingressos.valor_unitario, pedidos.evento_id')
			->join('pedidos', 'pedidos.id = ingressos.pedido_id')
			->find((int)$ingressoId);

		if (!$ingresso) {
			return $this->response->setJSON(['erro' => 'Ingresso não encontrado.', 'token' => csrf_hash()]);
		}

		$ticket = $this->ticketModel->find($ingresso->ticket_id);
		if (!$ticket) {
			return $this->response->setJSON(['erro' => 'Tipo de ingresso não identificado.', 'token' => csrf_hash()]);
		}

		// All tickets from same event+lote with higher price
		$opcoes = $this->ticketModel
			->where('event_id', $ingresso->evento_id)
			->where('lote', $ticket->lote)
			->where('preco >', $ticket->preco)
			->where('ativo', 1)
			->where('deleted_at IS NULL')
			->orderBy('preco', 'ASC')
			->findAll();

		$opcoesFormatadas = array_map(function($t) use ($ticket) {
			return [
				'id'        => $t->id,
				'nome'      => $t->nome,
				'preco'     => (float)$t->preco,
				'diferenca' => round((float)$t->preco - (float)$ticket->preco, 2),
				'preco_fmt' => 'R$ ' . number_format($t->preco, 2, ',', '.'),
				'dif_fmt'   => 'R$ ' . number_format($t->preco - $ticket->preco, 2, ',', '.'),
			];
		}, $opcoes);

		return $this->response->setJSON([
			'ingresso'      => ['id' => $ingresso->id, 'nome' => $ingresso->nome, 'valor' => (float)$ingresso->valor_unitario],
			'ticket_atual'  => ['id' => $ticket->id, 'nome' => $ticket->nome, 'preco' => (float)$ticket->preco],
			'opcoes'        => $opcoesFormatadas,
			'token'         => csrf_hash(),
		]);
	}

	public function upgradeGerarLink($ingressoId)
	{
		if (!$this->request->isAJAX()) return redirect()->back();

		$post = $this->request->getPost();
		$ticketDestinoId = (int)($post['ticket_destino_id'] ?? 0);
		$descontoPct     = max(0, min(100, (float)($post['desconto_pct'] ?? 0)));

		// Load ingresso
		$ingresso = $this->ingressoModel
			->select('ingressos.id, ingressos.ticket_id, ingressos.nome, ingressos.valor_unitario, ingressos.user_id, pedidos.evento_id')
			->join('pedidos', 'pedidos.id = ingressos.pedido_id')
			->find((int)$ingressoId);

		if (!$ingresso) {
			return $this->response->setJSON(['erro' => 'Ingresso não encontrado.', 'token' => csrf_hash()]);
		}

		$ticketAtual = $this->ticketModel->find($ingresso->ticket_id);
		$ticketDestino = $this->ticketModel->find($ticketDestinoId);

		if (!$ticketAtual || !$ticketDestino) {
			return $this->response->setJSON(['erro' => 'Ticket inválido.', 'token' => csrf_hash()]);
		}

		// Validate same event+lote and higher price
		if ($ticketDestino->event_id != $ingresso->evento_id
			|| $ticketDestino->lote != $ticketAtual->lote
			|| $ticketDestino->preco <= $ticketAtual->preco) {
			return $this->response->setJSON(['erro' => 'Ticket destino inválido para upgrade.', 'token' => csrf_hash()]);
		}

		$diferenca    = round((float)$ticketDestino->preco - (float)$ticketAtual->preco, 2);
		$valorCobrado = round($diferenca * (1 - $descontoPct / 100), 2);

		// If free upgrade, apply directly
		if ($valorCobrado <= 0) {
			$upgradeId = $this->ingressoUpgradeModel->insert([
				'ingresso_id'       => $ingresso->id,
				'ticket_destino_id' => $ticketDestinoId,
				'valor_original'    => $ticketAtual->preco,
				'valor_destino'     => $ticketDestino->preco,
				'desconto_pct'      => $descontoPct,
				'valor_cobrado'     => 0,
				'forma_pagamento'   => 'gratuito',
				'status'            => 'pendente',
			]);
			$this->_efetivarUpgrade((int)$upgradeId);
			return $this->response->setJSON(['gratuito' => true, 'token' => csrf_hash()]);
		}

		// Get or create Asaas customer
		$cliente = $this->clienteModel->withDeleted(true)->where('usuario_id', $ingresso->user_id)->first();
		if (!$cliente) {
			return $this->response->setJSON(['erro' => 'Cliente não encontrado.', 'token' => csrf_hash()]);
		}

		$customerId = $cliente->customer_id;
		if (empty($customerId)) {
			// Create customer in Asaas
			$usuario = $this->usuarioModel->find($ingresso->user_id);
			$asaasService = new \App\Services\AsaasService();
			$customer = $asaasService->customers([
				'nome'     => $usuario->nome ?? $cliente->nome,
				'cpf'      => preg_replace('/[^0-9]/', '', $cliente->cpf ?? ''),
				'email'    => $usuario->email ?? '',
				'telefone' => preg_replace('/[^0-9]/', '', $cliente->telefone ?? ''),
				'cep'      => '',
				'numero'   => '',
			]);
			$customerId = $customer['id'] ?? null;
			if ($customerId) {
				$this->clienteModel->protect(false)->where('id', $cliente->id)->set('customer_id', $customerId)->update();
			}
		}

		if (!$customerId) {
			return $this->response->setJSON(['erro' => 'Falha ao identificar cliente no gateway de pagamento.', 'token' => csrf_hash()]);
		}

		// Insert pending upgrade record first (to get ID for externalReference)
		$upgradeId = $this->ingressoUpgradeModel->insert([
			'ingresso_id'       => $ingresso->id,
			'ticket_destino_id' => $ticketDestinoId,
			'valor_original'    => $ticketAtual->preco,
			'valor_destino'     => $ticketDestino->preco,
			'desconto_pct'      => $descontoPct,
			'valor_cobrado'     => $valorCobrado,
			'forma_pagamento'   => 'UNDEFINED',
			'status'            => 'pendente',
			'expire_at'         => date('Y-m-d H:i:s', strtotime('+3 days')),
		]);

		$asaasService = new \App\Services\AsaasService();
		$cobranca = $asaasService->criarCobranca([
			'customer_id'        => $customerId,
			'billing_type'       => 'UNDEFINED',
			'value'              => $valorCobrado,
			'due_date'           => date('Y-m-d', strtotime('+3 days')),
			'description'        => 'Upgrade: ' . $ingresso->nome . ' → ' . $ticketDestino->nome,
			'external_reference' => 'upgrade:' . $upgradeId,
		]);

		if (empty($cobranca['id'])) {
			return $this->response->setJSON(['erro' => 'Falha ao gerar cobrança no gateway. Tente novamente.', 'token' => csrf_hash()]);
		}

		// Update upgrade with charge info
		$this->ingressoUpgradeModel->update($upgradeId, [
			'charge_id'      => $cobranca['id'],
			'link_pagamento' => $cobranca['invoiceUrl'] ?? null,
			'status'         => 'pendente',
		]);

		return $this->response->setJSON([
			'link_pagamento' => $cobranca['invoiceUrl'] ?? null,
			'upgrade_id'     => $upgradeId,
			'valor_cobrado'  => 'R$ ' . number_format($valorCobrado, 2, ',', '.'),
			'token'          => csrf_hash(),
		]);
	}

	public function upgradeEfetivar($upgradeId)
	{
		if (!$this->request->isAJAX()) return redirect()->back();
		if (!$this->usuarioLogado()->is_admin) {
			return $this->response->setJSON(['erro' => 'Sem permissão.', 'token' => csrf_hash()]);
		}

		$upgrade = $this->ingressoUpgradeModel->find((int)$upgradeId);
		if (!$upgrade) {
			return $this->response->setJSON(['erro' => 'Upgrade não encontrado.', 'token' => csrf_hash()]);
		}
		if ($upgrade->status === 'pago') {
			return $this->response->setJSON(['erro' => 'Upgrade já efetivado.', 'token' => csrf_hash()]);
		}

		$this->_efetivarUpgrade((int)$upgradeId);

		return $this->response->setJSON(['sucesso' => true, 'token' => csrf_hash()]);
	}

	private function _efetivarUpgrade(int $upgradeId): bool
	{
		$upgrade = $this->ingressoUpgradeModel->find($upgradeId);
		if (!$upgrade || $upgrade->status === 'pago') return false;

		$ticketDestino = $this->ticketModel->find($upgrade->ticket_destino_id);
		if (!$ticketDestino) return false;

		// Update the ingresso
		$this->ingressoModel->skipValidation(true)->protect(false)->update($upgrade->ingresso_id, [
			'ticket_id'      => $ticketDestino->id,
			'nome'           => $ticketDestino->nome,
			'valor_unitario' => $ticketDestino->preco,
			'updated_at'     => date('Y-m-d H:i:s'),
		]);

		// Mark upgrade as done
		$adminId = $this->usuarioLogado() ? $this->usuarioLogado()->id : null;
		$this->ingressoUpgradeModel->update($upgradeId, [
			'status'         => 'pago',
			'efetivado_em'   => date('Y-m-d H:i:s'),
			'efetivado_por'  => $adminId,
		]);

		return true;
	}

	/**
	 * Lista tickets disponíveis para troca de dia de um ingresso (AJAX).
	 * Filtra pelo mesmo evento, mesmo tipo e mesma categoria do ticket original.
	 */
	public function trocaListar($ingresso_id = null)
	{
		if (!$this->request->isAJAX()) {
			return redirect()->back();
		}

		$retorno = ['token' => csrf_hash()];

		$ingresso_id = (int) $ingresso_id;
		$ingresso = $this->ingressoModel->find($ingresso_id);
		if (!$ingresso) {
			$retorno['erro'] = 'Ingresso não encontrado.';
			return $this->response->setJSON($retorno);
		}

		$ticketAtual = $this->ticketModel->find((int) $ingresso->ticket_id);
		if (!$ticketAtual) {
			$retorno['erro'] = 'Ticket original do ingresso não encontrado. Não é possível filtrar por tipo/categoria.';
			return $this->response->setJSON($retorno);
		}

		$disponiveis = $this->ticketModel
			->where('event_id', $ticketAtual->event_id)
			->where('tipo', $ticketAtual->tipo)
			->where('categoria', $ticketAtual->categoria)
			->where('id !=', $ticketAtual->id)
			->where('ativo', 1)
			->orderBy('dia', 'ASC')
			->orderBy('nome', 'ASC')
			->findAll();

		$data = [];
		foreach ($disponiveis as $t) {
			$data[] = [
				'id'        => (int) $t->id,
				'nome'      => esc($t->nome),
				'dia'       => $t->dia,
				'tipo'      => $t->tipo,
				'categoria' => $t->categoria,
				'lote'      => $t->lote,
			];
		}

		$retorno['ticket_atual'] = [
			'id'        => (int) $ticketAtual->id,
			'nome'      => esc($ticketAtual->nome),
			'dia'       => $ticketAtual->dia,
			'tipo'      => $ticketAtual->tipo,
			'categoria' => $ticketAtual->categoria,
		];
		$retorno['ingresso_nome_atual'] = esc($ingresso->nome);
		$retorno['disponiveis'] = $data;

		return $this->response->setJSON($retorno);
	}

	/**
	 * Executa a troca de dia de um ingresso (AJAX).
	 * Valida que o ticket destino é do mesmo tipo/categoria do ticket original.
	 * Atualiza ingressos.ticket_id e ingressos.nome e registra no histórico.
	 */
	public function trocaExecutar()
	{
		if (!$this->request->isAJAX()) {
			return redirect()->back();
		}

		$retorno = ['token' => csrf_hash()];

		$ingresso_id    = (int) $this->request->getPost('ingresso_id');
		$ticket_id_novo = (int) $this->request->getPost('ticket_id_novo');
		$motivo         = trim((string) $this->request->getPost('motivo'));

		if ($ingresso_id <= 0 || $ticket_id_novo <= 0) {
			$retorno['erro'] = 'Parâmetros inválidos.';
			return $this->response->setJSON($retorno);
		}

		$ingresso = $this->ingressoModel->find($ingresso_id);
		if (!$ingresso) {
			$retorno['erro'] = 'Ingresso não encontrado.';
			return $this->response->setJSON($retorno);
		}

		$ticketAtual = $this->ticketModel->find((int) $ingresso->ticket_id);
		if (!$ticketAtual) {
			$retorno['erro'] = 'Ticket original do ingresso não encontrado.';
			return $this->response->setJSON($retorno);
		}

		$ticketNovo = $this->ticketModel->find($ticket_id_novo);
		if (!$ticketNovo) {
			$retorno['erro'] = 'Ticket de destino não encontrado.';
			return $this->response->setJSON($retorno);
		}

		if ((int) $ticketNovo->id === (int) $ticketAtual->id) {
			$retorno['erro'] = 'O ticket de destino é igual ao atual.';
			return $this->response->setJSON($retorno);
		}

		if ($ticketNovo->event_id != $ticketAtual->event_id
			|| $ticketNovo->tipo !== $ticketAtual->tipo
			|| $ticketNovo->categoria !== $ticketAtual->categoria) {
			$retorno['erro'] = 'A troca só é permitida entre ingressos do mesmo evento, tipo e categoria.';
			return $this->response->setJSON($retorno);
		}

		$db = \Config\Database::connect();
		$db->transStart();

		$this->ingressoModel->update($ingresso_id, [
			'ticket_id' => (int) $ticketNovo->id,
			'nome'      => $ticketNovo->nome,
		]);

		$operadorId = $this->usuarioLogado()->id ?? null;

		$this->ingressoTrocaModel->insert([
			'ingresso_id'        => $ingresso_id,
			'ticket_id_anterior' => (int) $ticketAtual->id,
			'ticket_id_novo'     => (int) $ticketNovo->id,
			'nome_anterior'      => $ingresso->nome,
			'nome_novo'          => $ticketNovo->nome,
			'operador_id'        => $operadorId,
			'motivo'             => $motivo !== '' ? $motivo : null,
		]);

		$db->transComplete();

		if ($db->transStatus() === false) {
			$retorno['erro'] = 'Falha ao registrar a troca.';
			return $this->response->setJSON($retorno);
		}

		$retorno['sucesso'] = 'Ingresso trocado com sucesso!';
		return $this->response->setJSON($retorno);
	}
}

