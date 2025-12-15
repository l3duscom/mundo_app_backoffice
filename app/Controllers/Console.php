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

		$id = $this->usuarioLogado()->id;

		$convite = $this->usuarioLogado()->codigo;

		$cli = $this->clienteModel->withDeleted(true)->where('usuario_id', $id)->first();

		$cliente = $this->buscaclienteOu404($cli->id);

		$indicacoes = $this->pedidoModel->where('convite', $convite)->whereIn('status', ['CONFIRMED', 'RECEIVED', 'paid'])->countAllResults();

		$card = $this->cartaoModel->where('user_id', $id)->where('expiration >= NOW()')->first();

		$ingressos = $this->ingressoModel->recuperaIngressosPorUsuario($id);

		// Separar ingressos em atuais e anteriores
		//$ticketModel = new \App\Models\TicketModel();
		$ingressos_atuais = [];
		$ingressos_anteriores = [];
		$hoje = date('Y-m-d');
		foreach ($ingressos as $key => $ingresso) {
			// Usa data_fim do EVENTO (vem do JOIN em recuperaIngressosPorUsuario)
			$data_fim = $ingresso->data_fim ?? null;
			if ($data_fim) {
				// Se data_fim passou de 2 dias atrás, é anterior
				$limite = date('Y-m-d', strtotime('-2 days', strtotime($hoje)));
				if ($data_fim < $limite) {
					$ingressos_anteriores[] = $ingresso;
				} else {
					$ingressos_atuais[] = $ingresso;
				}
			} else {
				// Se não tem data_fim do evento, considerar como atual
				$ingressos_atuais[] = $ingresso;
			}
			$ingressos[$key]->qr = (new QRCode)->render($ingressos[$key]->codigo);
		}


		$ingresso = $this->ingressoModel->select('id')
			->where('user_id', $id)
			->first();
		if (isset($ingresso)) {
			$temingresso = true;
		} else {
			$temingresso = false;
		}




		$data = [
			'titulo' => 'Dashboard de ' . esc($cliente->nome),
			'cliente' => $cliente,
			'card' => $card,
			'temingresso' => $temingresso,
			'convite' => $convite,
			'indicacoes' => $indicacoes,
			'ingressos_atuais' => $ingressos_atuais,
			'ingressos_anteriores' => $ingressos_anteriores,
		];

		$usuario = $this->usuarioLogado();
		$campos_obrigatorios = [
			'nome' => $usuario->nome,
			'email' => $usuario->email,
			'cpf' => $cliente->cpf,
			'telefone' => $cliente->telefone,
			'cep' => $cliente->cep,
			'endereco' => $cliente->endereco,
			'numero' => $cliente->numero,
			'bairro' => $cliente->bairro,
			'cidade' => $cliente->cidade,
			'estado' => $cliente->estado,
		];
		$campos_faltando = [];
		foreach ($campos_obrigatorios as $campo => $valor) {
			if (empty($valor)) {
				$campos_faltando[] = $campo;
			}
		}
		$perfil_incompleto = !empty($campos_faltando);
		$data['perfil_incompleto'] = $perfil_incompleto;
		$data['campos_faltando'] = $campos_faltando;

        $enderecosModel = new \App\Models\EnderecoModel();
        $enderecos = $enderecosModel->where('user_id', $usuario->id)->orderBy('id', 'DESC')->findAll();

        $endereco_default = [
            'endereco' => $cliente->endereco,
            'numero' => $cliente->numero,
            'bairro' => $cliente->bairro,
            'cidade' => $cliente->cidade,
            'estado' => $cliente->estado,
            'cep' => $cliente->cep,
            'default' => true,
        ];

        $enderecos_lista = array_merge([$endereco_default], array_map(function($e) {
            return [
                'endereco' => $e->endereco,
                'numero' => $e->numero,
                'bairro' => $e->bairro,
                'cidade' => $e->cidade,
                'estado' => $e->estado,
                'cep' => $e->cep,
                'default' => false,
            ];
        }, $enderecos));

        $data['enderecos_lista'] = $enderecos_lista;

        // ========== DADOS DO PARCEIRO ==========
        if ($usuario->is_parceiro) {
            $expositorModel = new \App\Models\ExpositorModel();
            $contratoModel = new \App\Models\ContratoModel();
            $contratoParcelaModel = new \App\Models\ContratoParcelaModel();
            
            // Busca o expositor vinculado ao usuário logado
            $expositor = $expositorModel->where('usuario_id', $id)->first();
            
            if ($expositor) {
                $data['expositor'] = $expositor;
                
                // Busca todos os contratos do expositor
                $contratos = $contratoModel->buscaPorExpositor($expositor->id);
                
                // Agrupa contratos por evento e separa em ativos/anteriores
                $eventos_ativos = [];
                $eventos_anteriores = [];
                $hoje = date('Y-m-d');
                
                foreach ($contratos as $contrato) {
                    // Busca dados do evento
                    $evento = $this->eventoModel->find($contrato->event_id);
                    if (!$evento) continue;
                    
                    $eventId = $contrato->event_id;
                    
                    // Busca parcelas do contrato
                    $parcelas = $contratoParcelaModel->buscaPorContrato($contrato->id);
                    $totaisParcelas = $contratoParcelaModel->calculaTotais($contrato->id);
                    
                    // Prepara dados do contrato
                    $contratoData = [
                        'contrato' => $contrato,
                        'parcelas' => $parcelas,
                        'totais' => $totaisParcelas,
                    ];
                    
                    // Determina se evento é ativo ou anterior
                    $data_fim_evento = $evento->data_fim ?? null;
                    $isAtivo = true;
                    
                    if ($data_fim_evento) {
                        $limite = date('Y-m-d', strtotime('-2 days', strtotime($hoje)));
                        $isAtivo = $data_fim_evento >= $limite;
                    }
                    
                    // Agrupa por evento
                    if ($isAtivo) {
                        if (!isset($eventos_ativos[$eventId])) {
                            $eventos_ativos[$eventId] = [
                                'evento' => $evento,
                                'contratos' => [],
                            ];
                        }
                        $eventos_ativos[$eventId]['contratos'][] = $contratoData;
                    } else {
                        if (!isset($eventos_anteriores[$eventId])) {
                            $eventos_anteriores[$eventId] = [
                                'evento' => $evento,
                                'contratos' => [],
                            ];
                        }
                        $eventos_anteriores[$eventId]['contratos'][] = $contratoData;
                    }
                }
                
                $data['eventos_ativos'] = array_values($eventos_ativos);
                $data['eventos_anteriores'] = array_values($eventos_anteriores);
            } else {
                $data['expositor'] = null;
                $data['eventos_ativos'] = [];
                $data['eventos_anteriores'] = [];
            }
        }


		return view('Console/dashboard', $data);
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
