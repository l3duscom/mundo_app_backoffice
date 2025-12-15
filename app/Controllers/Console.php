<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\Cliente;
use App\Entities\Cartao;
use App\Entities\Ingresso;
use App\Traits\ValidacoesTrait;
use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QROutputInterface;
use App\Entities\Evento;
use App\Entities\Ticket;

class Console extends BaseController
{
	use ValidacoesTrait;

	private $clienteModel;
	private $usuarioModel;
	private $cartaoModel;
	private $ingressoModel;
	private $pedidoModel;
	private $meetModel;
	private $queueModel;
	private $eventoModel;
	private $ticketModel;
	



	public function __construct()
	{
		$this->clienteModel = new \App\Models\ClienteModel();
		$this->usuarioModel = new \App\Models\UsuarioModel();
		$this->cartaoModel = new \App\Models\CartaoModel();
		$this->ingressoModel = new \App\Models\IngressoModel();
		$this->pedidoModel = new \App\Models\PedidoModel();
		$this->meetModel = new \App\Models\MeetModel();
		$this->queueModel = new \App\Models\QueueModel();
		$this->eventoModel = new \App\Models\EventoModel();
		$this->ticketModel = new \App\Models\TicketModel();
	}

	public function dashboard()
	{
		unset($_SESSION['carrinho']);

		$usuario = $this->usuarioLogado();

		// ========== DASHBOARD DO PARCEIRO ==========
		if ($usuario->is_parceiro) {
			$data = [
				'titulo' => 'Dashboard de Parceiro',
			];

			$expositorModel = new \App\Models\ExpositorModel();
			$contratoModel = new \App\Models\ContratoModel();
			$contratoItemModel = new \App\Models\ContratoItemModel();
			$contratoParcelaModel = new \App\Models\ContratoParcelaModel();
			$documentoModel = new \App\Models\ContratoDocumentoModel();
			
			// Busca o expositor vinculado ao usuário
			$expositor = $expositorModel->where('usuario_id', $usuario->id)->first();
			$data['expositor'] = $expositor;
			
			if ($expositor) {
				// Busca contratos do expositor
				$contratos = $contratoModel->buscaPorExpositor($expositor->id);
				
				// Agrupa contratos por evento
				$contratosPorEvento = [];
				foreach ($contratos as $contrato) {
					$evento = $this->eventoModel->find($contrato->event_id);
					if (!$evento) continue;
					
					// Busca itens e parcelas do contrato
					$itens = $contratoItemModel->buscaPorContrato($contrato->id);
					$parcelas = $contratoParcelaModel->buscaPorContrato($contrato->id);
					$totaisParcelas = $contratoParcelaModel->calculaTotais($contrato->id);
					
					// Busca documentos do contrato
					$documentos = $documentoModel->buscaPorContrato($contrato->id);
					
					// Calcula progresso do pagamento
					$valorTotal = $contrato->valor_final ?? 0;
					$valorPago = $contrato->valor_pago ?? 0;
					$valorRestante = $valorTotal - $valorPago;
					$porcentagemPaga = $valorTotal > 0 ? round(($valorPago / $valorTotal) * 100, 1) : 0;
					$pagamentoCompleto = $valorRestante <= 0;
					
					$eventId = $contrato->event_id;
					if (!isset($contratosPorEvento[$eventId])) {
						$contratosPorEvento[$eventId] = [
							'evento' => $evento,
							'contratos' => [],
						];
					}
					$contratosPorEvento[$eventId]['contratos'][] = [
						'contrato' => $contrato,
						'itens' => $itens,
						'parcelas' => $parcelas,
						'documentos' => $documentos,
						'totais_parcelas' => $totaisParcelas,
						'valor_restante' => $valorRestante,
						'porcentagem_paga' => $porcentagemPaga,
						'pagamento_completo' => $pagamentoCompleto,
					];
				}
				
				$data['contratos_por_evento'] = array_values($contratosPorEvento);
			} else {
				$data['contratos_por_evento'] = [];
			}
			
			return view('Console/dashboard_parceiro', $data);
		}

		// ========== USUÁRIO NÃO-PARCEIRO ==========
		return redirect()->to('https://mundodream.com.br');
	}

	public function evento($event_id)
	{
		// Buscar dados do evento para o pixel

		$evento = $this->eventoModel->find($event_id);

		if ($this->usuarioLogado()) {
			$id = $this->usuarioLogado()->id;
			$cli = $this->clienteModel->withDeleted(true)->where('usuario_id', $id)->first();
			//$cliente = $this->buscaclienteOu404($cli->id);
			$card = $this->cartaoModel->withDeleted(true)->where('user_id', $id)->first();
		} else {
			$id = null;
		}

		$items = $this->ticketModel->recuperaIngressosPorEvento($event_id);

		$ingressos = array();
		foreach ($items as $item) {
			$ingressos[] = array(
				'id' => $item->id,
				'nome' => $item->nome,
				'preco' => $item->preco * 0.85,
				'descricao' => $item->descricao,
				'data_inicio' => $item->data_inicio,
				'data_fim' => $item->data_fim,
				'tipo' => $item->tipo,
				'dia' => $item->dia,
				'lote' => $item->lote,
				'categoria' => $item->categoria,
				'data_lote' => $item->data_lote,
				'estoque' => $item->estoque,
				'parent_ticket_id' => $item->parent_ticket_id
			);
		}

		$data = [
			'titulo' => 'Comprar ingressos',
			'id' => $id,
			'items' => $ingressos,
			'event_id' => $event_id,
			'evento' => $evento // Dados do evento para o pixel
		];

		return view('Carrinho/logado', $data);
	}


	public function meets()
	{


		$id = $this->usuarioLogado()->id;

		$convite = $this->usuarioLogado()->codigo;

		$cli = $this->clienteModel->withDeleted(true)->where('usuario_id', $id)->first();

		$cliente = $this->buscaclienteOu404($cli->id);

		$meets = $this->queueModel->recuperaQueuePorUsuario($id);





		foreach ($meets as $key => $value) {
			$meets[$key]->qr = (new QRCode)->render($meets[$key]->code);
		}








		$data = [
			'titulo' => 'Dashboard de ' . esc($cliente->nome),
			'cliente' => $cliente,
			'convite' => $convite,
			'meets' => $meets,

		];




		return view('Console/meets', $data);
	}

	public function meet(int $ingresso_id, int $event_id)
	{


		$id = $this->usuarioLogado()->id;

		$convite = $this->usuarioLogado()->codigo;

		$cli = $this->clienteModel->withDeleted(true)->where('usuario_id', $id)->first();

		$cliente = $this->buscaclienteOu404($cli->id);

		$ingresso = $this->ingressoModel->find($ingresso_id);



		if (stripos($ingresso->nome, 'sábado') !== false) {
			$day = 'sab';
		} elseif (stripos($ingresso->nome, 'domingo') !== false) {
			$day = 'dom';
		} else {
			$day = 'duo';
		}


		if (stripos($ingresso->nome, 'vip') !== false) {
			$tipo = 'vip';
		} elseif (stripos($ingresso->nome, 'epic') !== false) {
			$tipo = 'epic';
		} else {
			$tipo = 'comum';
		}


		$meets_vip = $this->meetModel->recuperaMeetVipForDay($event_id, 'vip');
		$meets_epic = $this->meetModel->recuperaMeetEpicForDay($event_id, 'epic');
		$meets = $this->meetModel->recuperaMeetForDay($event_id);

		$count_queue = $this->queueModel->CountQueueForDayEpic($event_id, $day, $id);

		$queue = $this->queueModel->recuperaQueueAllByUser($ingresso_id);




		// Primeiro, vamos montar uma lista só dos meet_id que o usuário já reservou:
		$meetReservadosIds = array_map(function ($item) {
			return $item->meet_id;
		}, $queue);

		// Agora, filtramos os meets:
		$meetsDisponiveis = array_filter($meets, function ($meet) use ($meetReservadosIds) {
			return !in_array($meet->id, $meetReservadosIds);
		});
		$meetsDisponiveisVip = array_filter($meets_vip, function ($meet_vip) use ($meetReservadosIds) {
			return !in_array($meet_vip->id, $meetReservadosIds);
		});
		$meetsDisponiveisEpic = array_filter($meets_epic, function ($meet_epic) use ($meetReservadosIds) {
			return !in_array($meet_epic->id, $meetReservadosIds);
		});
		//1836




		if ($tipo == 'vip') {
			$data = [
				'titulo' => 'Dashboard de ' . esc($cliente->nome),
				'cliente' => $cliente,
				'meets' => $meetsDisponiveisVip,
				'day' => $day,
				'tipo' => $tipo,
				'count_queue' => $count_queue,
				'queue' => $queue
			];
			return view('Console/meet_vip', $data);
		} else if ($tipo == 'epic') {
			$data = [
				'titulo' => 'Dashboard de ' . esc($cliente->nome),
				'cliente' => $cliente,
				'meets' => $meetsDisponiveisEpic,
				'day' => $day,
				'tipo' => $tipo,
				'count_queue' => $count_queue,
				'queue' => $queue
			];
			return view('Console/meet_epic', $data);
		} else {
			$data = [
				'titulo' => 'Dashboard de ' . esc($cliente->nome),
				'cliente' => $cliente,
				'meets' => $meetsDisponiveis,
				'day' => $day,
				'tipo' => $tipo,
				'count_queue' => $count_queue,
				'queue' => $queue
			];
			return view('Console/meet', $data);
		}
	}

	public function queuecheck($meet_id, $ingresso_id)
	{
		$id = $this->usuarioLogado()->id;

		$queues = $this->queueModel->recuperaOrdem($meet_id);

		$meet = $this->meetModel->find($meet_id);

		$ordem = $queues->ordem;

		$this->queueModel
			->protect(false)
			->insert([
				'user_id' => $id,
				'meet_id' => $meet_id,
				'ingresso_id' => $ingresso_id,
				'code' => $this->queueModel->geraCodigo(),
				'status' => 'CHECKIN',
				'ordem'  => $ordem + 1,
			]);

		$this->meetModel
			->protect(false)
			->where('id', $meet_id)
			->set('quantidade', $meet->quantidade - 1)
			->update();

		return redirect()->to(site_url("console/dashboard"))->with('sucesso', "Meet & Greet reservado com sucesso!");
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
