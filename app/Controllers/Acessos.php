<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\Cliente;
use App\Entities\Cartao;
use App\Entities\Credencial;
use App\Entities\Check;
use App\Traits\ValidacoesTrait;

class Acessos extends BaseController
{
	use ValidacoesTrait;

	private $clienteModel;
	private $usuarioModel;
	private $cartaoModel;
	private $ingressoModel;
	private $pedidosModel;
	private $eventoModel;
	private $enderecoModel;
	private $credencialModel;
	private $checkModel;


	public function __construct()
	{
		$this->clienteModel = new \App\Models\ClienteModel();
		$this->usuarioModel = new \App\Models\UsuarioModel();
		$this->cartaoModel = new \App\Models\CartaoModel();
		$this->ingressoModel = new \App\Models\IngressoModel();
		$this->pedidosModel = new \App\Models\PedidoModel();
		$this->enderecoModel = new \App\Models\EnderecoModel();
		$this->eventoModel = new \App\Models\EventoModel();
		$this->credencialModel = new \App\Models\CredencialModel();
		$this->checkModel = new \App\Models\CheckModel();
	}

	public function index()
	{


		if ($this->usuarioLogado()) {
			$id = $this->usuarioLogado()->id;
			$cli = $this->clienteModel->withDeleted(true)->where('usuario_id', $id)->first();
			//$cliente = $this->buscaclienteOu404($cli->id);
			$card = $this->cartaoModel->withDeleted(true)->where('user_id', $id)->first();
		} else {
			$id = null;
		}





		$data = [
			'titulo' => 'Comprar ingressos',
			'id' => $id,

		];


		return view('Carrinho/index', $data);
	}


	public function salavip()
	{
		if (!$this->usuarioLogado()->temPermissaoPara('access-controll')) {

            $this->registraAcaoDoUsuario('tentou listar as suas declarações');

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

		$event_id = 12;
		$evento = $this->eventoModel->find($event_id);

		$data = [
			'titulo' => 'Acesso sala vip',

		];


		return view('Acessos/vip', $data);
	}

	public function bilheteria()
	{
		if (!$this->usuarioLogado()->temPermissaoPara('access-controll')) {

            $this->registraAcaoDoUsuario('tentou listar as suas declarações');

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

		$event_id = 12;
		$evento = $this->eventoModel->find($event_id);

		$data = [
			'titulo' => 'Acesso ao evento',

		];


		return view('Acessos/bilheteria', $data);
	}

	public function check()
	{
		if (!$this->request->isAJAX()) {
			return redirect()->back();
		}

		// Envio o hash do token do form
		$retorno['token'] = csrf_hash();




		// Recupero o post da requisição
		$post = $this->request->getPost();


		$atributos = [
			'ingressos.id',
			'ingressos.codigo',
			'ingressos.nome',
			'pedidos.evento_id',
			'pedidos.status',
			'pedidos.user_id',
			'credenciais.id AS credencial_id',
			'credencial_ticket_alimentacao.retirado_em AS ticket_alimentacao_em'
		];

		$ingresso = $this->ingressoModel->select($atributos)
			->withDeleted(true)
			->join('pedidos', 'pedidos.id = ingressos.pedido_id')
			->join('credenciais', 'credenciais.ingresso_id = ingressos.id')
			->join(
				'credencial_ticket_alimentacao',
				'credencial_ticket_alimentacao.credencial_id = credenciais.id',
				'left'
			)
			->where('pedidos.evento_id', $post['evento_id'])
			->where('credenciais.codigo', $post['codigo'])
			->whereIn('pedidos.status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
			->orderBy('id', 'DESC')
			->first();



		if ($ingresso) {

			if (stripos($ingresso->nome, 'VIP') !== false) {
				// Conta o total de acessos já realizados para este ingresso VIP
				$totalAcessos = $this->checkModel->where('ingresso_id', $ingresso->id)->where('tipo_acesso', 'ACESSO')->countAllResults();

				$acesso = new Check($post);
				$acesso->usuario_id = $ingresso->user_id;
				$acesso->ingresso_id = $ingresso->id;
				$acesso->operador_id = $post['operador'];
				$acesso->tipo_acesso = $post['tipo'];

				if ($this->checkModel->save($acesso)) {
					// Estado do ticket de alimentação (para a view exibir o checkbox)
					$ticketRetirado  = !empty($ingresso->ticket_alimentacao_em);
					$primeiraLeitura = $totalAcessos === 0;

					$retorno['ingresso_id']            = (int) $ingresso->id;
					$retorno['credencial_id']          = (int) $ingresso->credencial_id;
					$retorno['nome']                   = $ingresso->nome;
					$retorno['primeira_leitura']       = $primeiraLeitura;
					$retorno['ticket_alimentacao']     = [
						'retirado'         => $ticketRetirado,
						'retirado_em'      => $ingresso->ticket_alimentacao_em ?? null,
						'mostrar_checkbox' => $primeiraLeitura && !$ticketRetirado,
					];

					session()->setFlashdata('atencao', '<span style="font-size: 36px; padding:20px">ATENÇÃO! </span><br><span style="padding:20px">Entrada permitida apenas com pulseira inviolada</span><br><span style="padding:20px">Este ingresso já foi utilizado <strong style="color:purple">' . ($totalAcessos + 1) . '</strong> vezes</span><br><strong>' . $ingresso->nome . '</strong>');
					return $this->response->setJSON($retorno);
				}
			} else {
				// Se a palavra "vip" não estiver presente no campo 'nome'
				//$retorno['erro'] = '<span style="font-size: 36px; padding:20px">Acesso negado! </span><br><span style="padding:20px">O ingresso não possui privilégios VIP</span>';
				session()->setFlashdata('erro', '<span style="font-size: 36px; padding:20px">Acesso negado! </span><br><span style="padding:20px">O ingresso não possui privilégios VIP</span>');

				return $this->response->setJSON($retorno);
			}
		} else {
			session()->setFlashdata('erro', '<span style="font-size: 36px; padding:20px">Acesso negado! </span><br><span style="padding:20px">O ingresso não foi localizado</span>');
			//$retorno['erro'] = '<span style="font-size: 36px; padding:20px">Acesso negado! </span><br><span style="padding:20px">O ingresso não foi localizado</span>';

			// Retorno para o ajax request
			return $this->response->setJSON($retorno);
		}



		// Retornamos os erros de validação
		$retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
		$retorno['erros_model'] = $this->enderecoModel->errors();

		// Retorno para o ajax request
		return $this->response->setJSON($retorno);
	}

	public function checkaccess()
	{
		if (!$this->request->isAJAX()) {
			return redirect()->back();
		}
		

		// Envio o hash do token do form
		$retorno['token'] = csrf_hash();

		// Recupero o post da requisição
		$post = $this->request->getPost();


		$atributos = [
			'ingressos.id',
			'ingressos.codigo',
			'ingressos.nome',
			'tickets.tipo',
			'tickets.dia',
			'tickets.data_inicio',
			'tickets.data_fim',
			'pedidos.evento_id',
			'pedidos.status',
			'pedidos.user_id',
			'pedidos.frete',
			'pedidos.rastreio'
		];

		$ingresso = $this->ingressoModel->select($atributos)
			->withDeleted(true)
			->join('pedidos', 'pedidos.id = ingressos.pedido_id')
			->join('tickets', 'tickets.id = ingressos.ticket_id')
			->where('pedidos.evento_id', $post['evento_id'])
			->where('ingressos.codigo', $post['codigo'])
			->whereIn('pedidos.status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
			->groupStart()
				->where('tickets.tipo', 'combo')
				->orGroupStart()
					->where('tickets.tipo !=', 'combo')
					->where('tickets.data_inicio <=', date('Y-m-d'))
					->where('tickets.data_fim >=', date('Y-m-d'))
				->groupEnd()
			->groupEnd()
			->orderBy('id', 'DESC')
			->first();



		if ($ingresso) {

			$acesso = $this->checkModel->where('ingresso_id', $ingresso->id)->where('tipo_acesso', 'ACESSO')->first();
			$totalAcessos = $this->checkModel->where('ingresso_id', $ingresso->id)->where('tipo_acesso', 'ACESSO')->countAllResults();

			if ($acesso) {
				$acesso = new Check($post);
				$acesso->usuario_id = $ingresso->user_id;
				$acesso->ingresso_id = $ingresso->id;
				$acesso->operador_id = $post['operador'];
				$acesso->tipo_acesso = $post['tipo'];

				if ($this->checkModel->save($acesso)) {
					session()->setFlashdata('atencao', '<span style="font-size: 36px; padding:20px">ATENÇÃO! </span><br><span style="padding:20px">Entrada permitida apenas com pulseira inviolada</span><br><span style="padding:20px">Este ingresso já foi utilizado <strong style="color:purple">' . $totalAcessos . '</strong> vezes</span><br><strong>' . $ingresso->nome . '</strong>');
					return $this->response->setJSON($retorno);
				}
			}

				$acesso = new Check($post);
				$acesso->usuario_id = $ingresso->user_id;
				$acesso->ingresso_id = $ingresso->id;
				$acesso->operador_id = $post['operador'];
				$acesso->tipo_acesso = $post['tipo'];

				if ($this->checkModel->save($acesso)) {
		
					if (stripos($ingresso->nome, 'VIP') !== false) {
						if(!empty($ingresso->rastreio)){
							session()->setFlashdata('sucesso', '<p style="font-size: 36px; padding:20px">Acesso <strong>VIP FULL </strong> Liberado a partir das 09h00</p>
							<p><strong>' . $ingresso->nome . '</strong></p>
							<p>MATERIAL ENTREGUE VIA SEDEX</p>');
						} else {
							session()->setFlashdata('sucesso', '<p style="font-size: 36px; padding:20px">Acesso <strong>VIP </strong> Liberado a partir das 09h00</p>
							<p><strong>' . $ingresso->nome . '</strong></p>
							<p><strong>Material disponível:</strong></p>
							<p>1 - Credencial + Cordão colecionável</p>
							<p>2 - Pôster Colecinável</p>
							<p>3 - Ingresso Holográfico</p>
							<p>4 - Copo Colecionável</p>
							<p>5 - Pulseira RFID (Favor vincular pulseira)</p>');
						}
					} else if (stripos($ingresso->nome, 'PREMIUM') !== false) {
						if(!empty($ingresso->rastreio)){
							session()->setFlashdata('sucesso', '<p style="font-size: 36px; padding:20px">Acesso <strong>PREMIUM  </strong> Liberado a partir das 09h00</p>
							<p><strong>' . $ingresso->nome . '</strong></p>
							<p>MATERIAL ENTREGUE VIA SEDEX</p>');
						} else {
							session()->setFlashdata('sucesso', '<p style="font-size: 36px; padding:20px">Acesso <strong>PREMIUM </strong> Liberado a partir das 09h00</p>
							<p><strong>' . $ingresso->nome . '</strong></p>
							<p><strong>Material disponível:</strong></p>
							<p>1 - Credencial + Cordão colecionável</p>
							<p>2 - Pôster Colecinável</p>
							<p>3 - Pulseira</p>');
						}
					} else if (stripos($ingresso->nome, 'EPIC') !== false) {
						if(!empty($ingresso->rastreio)){
							session()->setFlashdata('sucesso', '<p style="font-size: 36px; padding:20px">Acesso <strong>EPIC PASS  </strong> Liberado a partir das 09h00</p>
							<p><strong>' . $ingresso->nome . '</strong></p>
							<p>MATERIAL ENTREGUE VIA SEDEX</p>');
						} else {
							session()->setFlashdata('sucesso', '<p style="font-size: 36px; padding:20px">Acesso <strong>EPIC PASS </strong> Liberado a partir das 09h00</p>
							<p><strong>' . $ingresso->nome . '</strong></p>
							<p><strong>Material disponível:</strong></p>
							<p>1 - Credencial + Cordão colecionável</p>
							<p>2 - Pôster Colecinável</p>
							<p>3 - Pulseira de tecido</p>');
						}
					} else if (stripos($ingresso->nome, 'COSPLAY') !== false) {
						if(!empty($ingresso->rastreio)){
							session()->setFlashdata('sucesso', '<p style="font-size: 36px; padding:20px">Acesso <strong>COSPLAY  </strong>Liberado a partir das 10h00</p>
							<p><strong>' . $ingresso->nome . '</strong></p>
							<p>MATERIAL ENTREGUE VIA SEDEX</p>');
						} else {
							session()->setFlashdata('sucesso', '<p style="font-size: 36px; padding:20px">Acesso <strong>COSPLAY </strong>Liberado a partir das 10h00</p>
							<p><strong>' . $ingresso->nome . '</strong></p>
							<p><strong>Material disponível:</strong></p>
							<p>1 - Credencial + Cordão colecionável</p>
							<p>2 - Pulseira</p>');
						}
					} else {
						if(!empty($ingresso->rastreio)){
							session()->setFlashdata('sucesso', '<p style="font-size: 36px; padding:20px">Acesso <strong>BASIC  </strong>Liberado a partir das 10h00</p>
							<p><strong>' . $ingresso->nome . '</strong></p>
							<p>MATERIAL ENTREGUE VIA SEDEX</p>');
						} else {
							session()->setFlashdata('sucesso', '<p style="font-size: 36px; padding:20px">Acesso <strong>BASIC </strong>Liberado a partir das 10h00</p>
							<p><strong>' . $ingresso->nome . '</strong></p>
							<p><strong>Material disponível:</strong></p>
							<p>1 - Credencial + Cordão colecionável</p>
							<p>2 - Pulseira</p>');
						}
					}
					return $this->response->setJSON($retorno);
				}
			
		} else {
			session()->setFlashdata('erro', '<span style="font-size: 36px; padding:20px">Acesso negado! </span><br><span style="padding:20px">O ingresso não foi localizado</span>');
			//$retorno['erro'] = '<span style="font-size: 36px; padding:20px">Acesso negado! </span><br><span style="padding:20px">O ingresso não foi localizado</span>';

			// Retorno para o ajax request
			return $this->response->setJSON($retorno);
		}



		// Retornamos os erros de validação
		$retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
		$retorno['erros_model'] = $this->enderecoModel->errors();

		// Retorno para o ajax request
		return $this->response->setJSON($retorno);
	}

	public function girafinhas()
	{


		if ($this->usuarioLogado()) {
			$id = $this->usuarioLogado()->id;
			$cli = $this->clienteModel->withDeleted(true)->where('usuario_id', $id)->first();
			//$cliente = $this->buscaclienteOu404($cli->id);
			$card = $this->cartaoModel->withDeleted(true)->where('user_id', $id)->first();
		} else {
			$id = null;
		}





		$data = [
			'titulo' => 'Comprar ingressos',
			'id' => $id,

		];


		return view('Carrinho/girafinhas', $data);
	}


	/**
	 * Marca a retirada do ticket de alimentação da credencial (sala VIP).
	 * POST acessos/marcarTicketAlimentacao (AJAX)
	 * Body: credencial_id, retirado (0|1, opcional — se omitido, alterna)
	 */
	public function marcarTicketAlimentacao()
	{
		if (!$this->request->isAJAX()) {
			return redirect()->back();
		}

		if (!$this->usuarioLogado()->temPermissaoPara('access-controll')) {
			return $this->response->setStatusCode(403)->setJSON([
				'sucesso' => false,
				'erro'    => 'Sem permissão.',
			]);
		}

		$retorno['token'] = csrf_hash();

		$credencialId = (int) ($this->request->getPost('credencial_id') ?? 0);
		if ($credencialId <= 0) {
			$retorno['erro'] = 'ID de credencial inválido.';
			return $this->response->setJSON($retorno);
		}

		$credencialModel = new \App\Models\CredencialModel();
		$ticketModel     = new \App\Models\CredencialTicketAlimentacaoModel();

		$credencial = $credencialModel->find($credencialId);
		if (!$credencial) {
			$retorno['erro'] = 'Credencial não encontrada.';
			return $this->response->setJSON($retorno);
		}

		$post      = $this->request->getPost();
		$atual     = $ticketModel->porCredencial($credencialId);
		$jaMarcado = !empty($atual);

		if (array_key_exists('retirado', $post)) {
			$novoEstado = (bool) $post['retirado'];
		} else {
			$novoEstado = !$jaMarcado;
		}

		$operadorId = (int) ($this->usuarioLogado()->id ?? 0) ?: null;

		$ok = $novoEstado
			? $ticketModel->marcar($credencialId, $operadorId)
			: $ticketModel->desmarcar($credencialId);

		if (!$ok) {
			$retorno['erro'] = 'Não foi possível atualizar o ticket de alimentação.';
			return $this->response->setJSON($retorno);
		}

		$registro                 = $ticketModel->porCredencial($credencialId);
		$retorno['sucesso']       = true;
		$retorno['retirado']      = $registro !== null;
		$retorno['retirado_em']   = $registro->retirado_em ?? null;
		$retorno['credencial_id'] = (int) $credencial->id;

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
}
