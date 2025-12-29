<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\Avaliacao;
use App\Entities\Cliente;
use App\Entities\Cartao;
use App\Entities\Endereco;
use App\Entities\Inscricao;
use App\Traits\ValidacoesTrait;
use App\Services\ResendService;



class Concursos extends BaseController
{
	use ValidacoesTrait;

	private $clienteModel;
	private $usuarioModel;
	private $cartaoModel;
	private $pedidosModel;
	private $concursoModel;
	private $inscricaoModel;
	private $avaliacaoModel;
	private $enderecoModel;
	private $ingressoModel;
	private $credencialModel;
	private $grupoUsuarioModel;
	private $notifyService;
	private $resendService;




	public function __construct()
	{
		$this->clienteModel = new \App\Models\ClienteModel();
		$this->usuarioModel = new \App\Models\UsuarioModel();
		$this->cartaoModel = new \App\Models\CartaoModel();
		$this->pedidosModel = new \App\Models\PedidoModel();
		$this->concursoModel = new \App\Models\ConcursoModel();
		$this->inscricaoModel = new \App\Models\InscricaoModel();
		$this->avaliacaoModel = new \App\Models\AvaliacaoModel();
		$this->enderecoModel = new \App\Models\EnderecoModel();
		$this->ingressoModel = new \App\Models\IngressoModel();
		$this->credencialModel = new \App\Models\CredencialModel();
		$this->grupoUsuarioModel = new \App\Models\GrupoUsuarioModel();
		$this->notifyService = new \App\Services\NotifyService();
		$this->resendService = new ResendService();
	}

	    public function index(int $event_id)
    {

        if (!$this->usuarioLogado()->temPermissaoPara('juri')) {

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        // Se não foi passado event_id, usar o da sessão
        if (!$event_id) {
            $event_id = session()->get('event_id');
        }

        if (!$event_id) {
            return redirect()->to(site_url('/'))->with('atencao', 'Selecione um evento primeiro.');
        }

        $concursos = $this->concursoModel->recuperaConcursosPorEvento($event_id);
        
        // Buscar dados do evento
        $eventoModel = new \App\Models\EventoModel();
        $evento = $eventoModel->find($event_id);

        $data = [
            'titulo' => 'Concursos',
            'concursos' => $concursos,
            'event_id' => $event_id,
            'evento' => $evento
        ];


        return view('Concursos/index', $data);
    }

	public function gerenciarold($id)
	{


		if (!$this->usuarioLogado()->temPermissaoPara('juri')) {

			return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
		}

		$concurso = $this->concursoModel->withDeleted(true)->where('id', $id)->first();


		if ($concurso->tipo == 'desfile_cosplay' || $concurso->tipo == 'apresentacao_cosplay' || $concurso->tipo == 'cosplay_kids') {
			$inscricoes = $this->inscricaoModel->recuperarecuperaInscricoesCosplayPorConcurso($id);
		} else {
			$inscricoes = $this->inscricaoModel->recuperarecuperaInscricoesKpopPorConcurso($id);
		}


		$usuario_logado = $this->usuarioLogado()->id;





		$data = [
			'titulo' => 'Gerenciamento de inscrições',
			'inscricoes' => $inscricoes,
			'concurso' => $concurso,
			'usuario_logado' => $usuario_logado,
		];

		if ($concurso->tipo == 'desfile_cosplay' || $concurso->tipo == 'apresentacao_cosplay' || $concurso->tipo == 'cosplay_kids') {
			return view('Concursos/gerenciar', $data);
		} else {
			return view('Concursos/gerenciar_kpop', $data);
		}
	}

	public function gerenciar($id)
	{


		if (!$this->usuarioLogado()->temPermissaoPara('juri')) {

			return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
		}

		$concurso = $this->concursoModel->withDeleted(true)->where('id', $id)->first();



		if ($concurso->tipo == 'desfile_cosplay' || $concurso->tipo == 'apresentacao_cosplay' || $concurso->tipo == 'cosplay_kids') {
			$inscricoes = $this->inscricaoModel->recuperarecuperaInscricoesCosplayPorConcurso($id);
		} else {
			$inscricoes = $this->inscricaoModel->recuperarecuperaInscricoesKpopPorConcurso($id);
		}


		$usuario_logado = $this->usuarioLogado()->id;





		$data = [
			'titulo' => 'Gerenciamento de inscrições',
			'inscricoes' => $inscricoes,
			'concurso' => $concurso,
			'usuario_logado' => $usuario_logado,

		];

		if ($concurso->tipo == 'desfile_cosplay' || $concurso->tipo == 'apresentacao_cosplay' || $concurso->tipo == 'cosplay_kids') {
			return view('Concursos/gerenciar', $data);
		} else {
			return view('Concursos/gerenciar_kpop', $data);
		}
	}

	public function recuperaconcursoskpop($id)
	{
		if (!$this->request->isAJAX()) {
			return redirect()->back();
		}
		$concurso = $this->concursoModel->withDeleted(true)->where('id', $id)->first();

		$inscricoes = $this->inscricaoModel->recuperarecuperaInscricoesKpopPorConcurso($id);

		// Inicializa a variável $data
		$data = [];

		foreach ($inscricoes as $inscricao) {
			if ($inscricao->categoria != 'solo') {
				$nome = $inscricao->grupo;
			} else {
				$nome = $inscricao->nome_social;
			}
			if ($inscricao->status == 'INICIADA' || $inscricao->status == 'CANCELADA') {
				$link1 = anchor("concursos/aprovaInscricao/" . $inscricao->id, "Aprovar", 'style="color:#7FFF00; font-weight: bold;"');
				$link2 = anchor("concursos/rejeitaInscricao/" . $inscricao->id, "Rejeitar ", 'style="color: #FF4500; font-weight: bold;"');
			} else if ($inscricao->status == 'APROVADA') {
				$link1 = anchor("concursos/checkinonline/" . $inscricao->id, "Realizar checkin online", 'style="color: #40E0D0; font-weight: bold;"');
				$link2 = anchor("concursos/cancelaInscricao/" . $inscricao->id, "Cancelar", 'style="color: #CD853F; font-weight: bold;"');
			} else if ($inscricao->status == 'CHECKIN-ONLINE') {
				$link1 = anchor("concursos/checkin/" . $inscricao->id, "Realizar checkin", 'style="color: #FF00FF; font-weight: bold;"');
				$link2 = anchor("concursos/cancelaInscricao/" . $inscricao->id, "Cancelar", 'style="color: #CD853F; font-weight: bold;"');
			} else if ($inscricao->status == 'CHECKIN') {
				$link2 = "Checkin: " . date('d/m/Y H:i:s', strtotime($inscricao->updated_at));
				$link1 = anchor("concursos/avaliacao_kpop/" . $inscricao->id, "Avaliar", 'style="color: #FFD700; font-weight: bold;"');
			} else if ($inscricao->status == 'REJEITADA') {
				$link1 = anchor("concursos/aprovaInscricao/" . $inscricao->id, "Aprovar", 'style="color:#7FFF00; font-weight: bold;"');
				$link2 = '';
			}
			$data[] = [
				'nome_social' => esc($nome),
				'codigo' => esc($inscricao->codigo),
				'created_at' => esc(date('d/m/Y H:i:s', strtotime($inscricao->created_at))),
				'categoria' => esc($inscricao->categoria),
				'status' => esc($inscricao->status),
				'email' => esc($inscricao->email),
				'telefone' => anchor(
					'https://wa.me/55' . str_replace(array("(", ")", " ", "-"), "", $inscricao->telefone),
					$inscricao->telefone,
					['target' => '_blank']
				),
				'video_apresentacao' => anchor($inscricao->video_apresentacao, "Abrir", array('target' => '_blank')),
				'referencia' => anchor("concursos/imagem/" . $inscricao->referencia, "Abrir", array('target' => '_blank')),
				'musica' => anchor("concursos/imagem/" . $inscricao->musica, "Abrir", array('target' => '_blank')),
				'video_led' => anchor("concursos/imagem/" . $inscricao->video_led, "Abrir", array('target' => '_blank')),
				'acao' => $link1 . ' || ' . $link2,
			];
		}

		$retorno = [
			'data' => $data,
		];

		return $this->response->setJSON($retorno);
	}

	public function my()
	{


		$usuario_logado = $this->usuarioLogado()->id;


		$inscricoes = $this->inscricaoModel->recuperaInscricoesPorUsuario($usuario_logado);


		$data = [
			'titulo' => 'Minhas Inscrições',
			'inscricoes' => $inscricoes,
		];

		return view('Concursos/my', $data);
	}

	public function gerenciar_adm($id)
	{


		if (!$this->usuarioLogado()->temPermissaoPara('editar-clientes')) {

			return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
		}

		$concurso = $this->concursoModel->withDeleted(true)->where('id', $id)->first();


		if ($concurso->tipo == 'desfile_cosplay' || $concurso->tipo == 'apresentacao_cosplay' || $concurso->tipo == 'cosplay_kids') {
			$data = [];
			$inscricoes = $this->inscricaoModel->recuperarecuperaInscricoesCosplayPorConcursoComNota($id);
			$novas = array();
			$nota = 0;
			foreach ($inscricoes as $item) {
				$nota = $item->notal_total;
				if ($nota == $item->notal_total) {
					$novas[$item->id] = $nota + $item->notal_total;
				}
			}
			dd($novas);
		} else {
			$inscricoes = $this->inscricaoModel->recuperarecuperaInscricoesKpopPorConcurso($id);
		}








		$data = [
			'titulo' => 'Gerenciamento de inscrições',
			'inscricoes' => $inscricoes,
			'concurso' => $concurso
		];

		if ($concurso->tipo == 'desfile_cosplay' || $concurso->tipo == 'apresentacao_cosplay' || $concurso->tipo == 'cosplay_kids') {
			return view('Concursos/gerenciar_adm', $data);
		} else {
			return view('Concursos/gerenciar_kpop_adm', $data);
		}
	}

	public function avaliacao($id)
	{


		if (!$this->usuarioLogado()->temPermissaoPara('juri')) {

			return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
		}

		$inscricao = $this->inscricaoModel->withDeleted(true)->where('id', $id)->first();

		$user_id = $this->usuarioLogado()->id;

		$data = [
			'titulo' => 'Gerenciamento de inscrições',
			'inscricao' => $inscricao,
			'user_id' => $user_id
		];


		return view('Concursos/avaliacao', $data);
	}


	public function avaliacao_kpop($id)
	{


		if (!$this->usuarioLogado()->temPermissaoPara('juri')) {

			return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
		}

		$inscricao = $this->inscricaoModel->withDeleted(true)->where('id', $id)->first();

		$user_id = $this->usuarioLogado()->id;

		$data = [
			'titulo' => 'Gerenciamento de inscrições',
			'inscricao' => $inscricao,
			'user_id' => $user_id
		];


		return view('Concursos/avaliacao_kpop', $data);
	}

	public function finaliza_avaliacao(int $id = null)
	{
		if (!$this->request->isAJAX()) {
			return redirect()->back();
		}

		// Envio o hash do token do form
		$retorno['token'] = csrf_hash();

		// Recupero o post da requisição
		$post = $this->request->getPost();

		$rate = new Avaliacao($post);

		if ($this->avaliacaoModel->protect(false)->insert($rate)) {

			session()->setFlashdata('sucesso', 'Dados salvos com sucesso!');
			$total = $post['nota_1'] + $post['nota_2'] + $post['nota_3'] + $post['nota_4'];
			$media = $total / 4;
			$retorno['id'] = $this->avaliacaoModel->getInsertID();
			$avaliacao = $this->avaliacaoModel->withDeleted(true)->where('id', $this->avaliacaoModel->getInsertID())->first();
			$avaliacao->nota_total = $total;
			$avaliacao->nota_5 = $media;
			$this->avaliacaoModel->protect(false)->save($avaliacao);
			return $this->response->setJSON($retorno);
		}

		// Retornamos os erros de validação
		$retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
		$retorno['erros_model'] = $this->avaliacaoModel->errors();

		// Retorno para o ajax request
		return $this->response->setJSON($retorno);
	}



	public function add($id)
	{
		//$id = $this->usuarioLogado()->id;

		//$cli = $this->clienteModel->withDeleted(true)->where('usuario_id', $id)->first();

		//$cliente = $this->buscaclienteOu404($cli->id);

		$concurso = $this->concursoModel->withDeleted(true)->where('id', $id)->first();


		$data = [
			'titulo' => 'Realizar inscrição no concurso' . esc($concurso->nome),
			'concurso' => $concurso,
		];


		return view('Concursos/add', $data);
	}

	public function inscricao_kpop($id)
	{

		//$id = $this->usuarioLogado()->id;

		//$cli = $this->clienteModel->withDeleted(true)->where('usuario_id', $id)->first();

		//$cliente = $this->buscaclienteOu404($cli->id);

		$concurso = $this->concursoModel->withDeleted(true)->where('id', $id)->first();


		$data = [
			'titulo' => 'Realizar inscrição no concurso' . esc($concurso->nome),
			'concurso' => $concurso,
		];


		return view('Concursos/inscricao_kpop', $data);
	}

	public function inscricao_cosplay($id)
	{

		//$id = $this->usuarioLogado()->id;

		//$cli = $this->clienteModel->withDeleted(true)->where('usuario_id', $id)->first();

		//$cliente = $this->buscaclienteOu404($cli->id);

		$concurso = $this->concursoModel->withDeleted(true)->where('id', $id)->first();


		$data = [
			'titulo' => 'Realizar inscrição no concurso' . esc($concurso->nome),
			'concurso' => $concurso,
		];


		return view('Concursos/inscricao_cosplay', $data);
	}

	public function inscricao_cosplay_apresentacao($id)
	{

		//$id = $this->usuarioLogado()->id;

		//$cli = $this->clienteModel->withDeleted(true)->where('usuario_id', $id)->first();

		//$cliente = $this->buscaclienteOu404($cli->id);

		$concurso = $this->concursoModel->withDeleted(true)->where('id', $id)->first();


		$data = [
			'titulo' => 'Realizar inscrição no concurso' . esc($concurso->nome),
			'concurso' => $concurso,
		];


		return view('Concursos/inscricao_cosplay_apresentacao', $data);
	}

	public function inscricao_cosplay_kids($id)
	{

		//$id = $this->usuarioLogado()->id;

		//$cli = $this->clienteModel->withDeleted(true)->where('usuario_id', $id)->first();

		//$cliente = $this->buscaclienteOu404($cli->id);

		$concurso = $this->concursoModel->withDeleted(true)->where('id', $id)->first();


		$data = [
			'titulo' => 'Realizar inscrição no concurso' . esc($concurso->nome),
			'concurso' => $concurso,
		];


		return view('Concursos/inscricao_cosplay_kids', $data);
	}

	public function add_kpop($id)
	{
		//$id = $this->usuarioLogado()->id;

		//$cli = $this->clienteModel->withDeleted(true)->where('usuario_id', $id)->first();

		//$cliente = $this->buscaclienteOu404($cli->id);

		$concurso = $this->concursoModel->withDeleted(true)->where('id', $id)->first();


		$data = [
			'titulo' => 'Realizar inscrição no concurso' . esc($concurso->nome),
			'concurso' => $concurso,
		];


		return view('Concursos/add_kpop', $data);
	}




	public function registrar_inscricao()
	{

		// Envio o hash do token do form
		$retorno['token'] = csrf_hash();





		// Recupero o post da requisição
		$post = $this->request->getPost();


		$imagem = $this->request->getFile('referencia');
		$apoio = $this->request->getFile('apoio');



		list($largura, $altura) = getimagesize($imagem->getPathName());

		if ($largura < "100" || $altura < "100") {
			$retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
			$retorno['erros_model'] = ['dimensao' => 'A imagem não pode ser menor do que 300 x 300 pixels'];

			// Retorno para o ajax request
			return redirect()->to(site_url("concursos/gerenciar/" . $post['concurso_id']))->with('atencao', "Erro ao realizar inscrição, contate o suporte!");
		}


		$caminhoImagem = $imagem->store('concursos');
		$caminhoApoio = $apoio->store('concursos');


		// C:\xampp\htdocs\ordem\writable\uploads/usuarios/1625800273_8dc568f411ea409f3e16.jpg
		$caminhoImagem = WRITEPATH . "uploads/$caminhoImagem";
		$caminhoApoio = WRITEPATH . "uploads/$caminhoApoio";


		$inscricao = new Inscricao($post);
		$inscricao->referencia = $imagem->getName();
		$inscricao->apoio = $apoio->getName();
		$inscricao->codigo = $this->inscricaoModel->geraCodigo();
		$inscricao->status = 'INICIADA';

		if ($this->inscricaoModel->skipvalidation(true)->protect(false)->save($inscricao)) {


			//$this->enviaEmailInscricao($post['email']);

			return redirect()->to(site_url("concursos/gerenciar/" . $post['concurso_id']))->with('sucesso', "Inscrição realizada com sucesso!");
		}

		return redirect()->to(site_url("concursos/gerenciar/" . $post['concurso_id']))->with('atencao', "Erro ao realizar inscrição, contate o suporte!");
	}

	public function registrar_inscricao_kpop()
	{

		// Envio o hash do token do form
		$retorno['token'] = csrf_hash();





		// Recupero o post da requisição
		$post = $this->request->getPost();


		$imagem = $this->request->getFile('referencia');
		$musica = $this->request->getFile('musica');
		$video = $this->request->getFile('video_led');



		list($largura, $altura) = getimagesize($imagem->getPathName());

		if ($largura < "100" || $altura < "100") {
			$retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
			$retorno['erros_model'] = ['dimensao' => 'A imagem não pode ser menor do que 300 x 300 pixels'];

			// Retorno para o ajax request
			return redirect()->to(site_url("concursos/gerenciar/" . $post['concurso_id']))->with('atencao', "Erro ao realizar inscrição, contate o suporte!");
		}


		$caminhoImagem = $imagem->store('concursos');
		$caminhoMusica = $musica->store('concursos');
		$caminhoVideo = $video->store('concursos');


		// C:\xampp\htdocs\ordem\writable\uploads/usuarios/1625800273_8dc568f411ea409f3e16.jpg
		$caminhoImagem = WRITEPATH . "uploads/$caminhoImagem";
		$caminhoMusica = WRITEPATH . "uploads/$caminhoMusica";
		$caminhoVideo = WRITEPATH . "uploads/$caminhoVideo";


		$inscricao = new Inscricao($post);
		$inscricao->referencia = $imagem->getName();
		$inscricao->musica = $musica->getName();
		$inscricao->video_led = $video->getName();
		$inscricao->codigo = $this->inscricaoModel->geraCodigo();
		$inscricao->status = 'INICIADA';


		if ($this->inscricaoModel->skipvalidation(true)->protect(false)->save($inscricao)) {


			//$this->enviaEmailInscricao($post['email']);

			return redirect()->to(site_url("concursos/gerenciar/" . $post['concurso_id']))->with('sucesso', "Inscrição realizada com sucesso!");
		}

		return redirect()->to(site_url("concursos/gerenciar/" . $post['concurso_id']))->with('atencao', "Erro ao realizar inscrição, contate o suporte!");
	}

	public function registrar_inscricao_kpop_open()
	{

		// Envio o hash do token do form
		$retorno['token'] = csrf_hash();


		// Recupero o post da requisição
		$post = $this->request->getPost();

		$email = $post['email'];



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
			->where('clientes.email', $email)
			->orderBy('id', 'DESC')
			->first();


		if ($cliente != null) {
			$user_id = $cliente->usuario_id;

			if ($cliente->telefone == null || $cliente->telefone == '') {
				$this->clienteModel
					->protect(false)
					->where(
						'usuario_id',
						$user_id
					)
					->set('telefone', $post['telefone'])
					->update();
			}

			if ($cliente->nome == null || $cliente->nome == '') {
				$this->clienteModel
					->protect(false)
					->where(
						'usuario_id',
						$user_id
					)
					->set('nome', $post['nome'])
					->update();
			}
		} else {
			//criqar usuario e pegar o ID
			//$user_id = $this->usuarioModel->getInsertID();

			$cliente = new Cliente($post);
			if ($this->clienteModel->save($cliente)) {
				// Cria usuario do cliente
				$newuser = $this->criaUsuarioParaCliente($cliente);


				// Envia dados de acesso ao clente
				$this->enviaEmailCriacaoEmailAcesso($cliente);

				if ($cliente->telefone) {
					$mensagem = "Saudações " . $cliente->nome . "\n\nSua conta no Mundo Dream foi criada com sucesso e você já pode adquirir seus ingressos para o Dreamfest e desfrutar de tudo o que temos a te oferecer! \n\nSeja muito bem vindo(a), seus dados de acesso são: \n*link:* " . esc(site_url("/")) . "\n*E-mail de acesso:* " . $cliente->email . "\n*Senha:*  " . $newuser . "\n\nAtenciosamente, \nDepartamento de Relacionamento";

					$telefone = str_replace([' ', '-', '(', ')'], '', $cliente->telefone);
					if (strlen($telefone) == 10 || strlen($telefone) == 11) {
						// Verificar se o número começa com 9 (para números de celular no Brasil)
						if (
							strlen($telefone) == 11 && substr($telefone, 2, 1) != '9'
						) {
							return false;
						}
						$api = $this->notifyService->notificawpp($cliente, $mensagem);
					}
				}

				$cliente_id = $this->clienteModel->getInsertID();

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
					->where('clientes.id', $cliente_id)
					->orderBy('id', 'DESC')
					->first();

				$user_id = $cliente->usuario_id;
			}
		}


		$imagem = $this->request->getFile('referencia');
		$musica = $this->request->getFile('musica');
		$video = $this->request->getFile('video_led');



		list($largura, $altura) = getimagesize($imagem->getPathName());

		if ($largura < "100" || $altura < "100") {
			$retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
			$retorno['erros_model'] = ['dimensao' => 'A imagem não pode ser menor do que 300 x 300 pixels'];

			// Retorno para o ajax request
			return redirect()->to(site_url("concursos/inscricao_kpop/" . $post['concurso_id']))->with('atencao', "Erro ao realizar inscrição, contate o suporte!");
		}


		$caminhoImagem = $imagem->store('concursos');
		$caminhoMusica = $musica->store('concursos');
		$caminhoVideo = $video->store('concursos');


		// C:\xampp\htdocs\ordem\writable\uploads/usuarios/1625800273_8dc568f411ea409f3e16.jpg
		$caminhoImagem = WRITEPATH . "uploads/$caminhoImagem";
		$caminhoMusica = WRITEPATH . "uploads/$caminhoMusica";
		$caminhoVideo = WRITEPATH . "uploads/$caminhoVideo";


		$inscricao = new Inscricao($post);
		$inscricao->referencia = $imagem->getName();
		$inscricao->musica = $musica->getName();
		$inscricao->video_led = $video->getName();
		$inscricao->codigo = $this->inscricaoModel->geraCodigo();
		$inscricao->status = 'INICIADA';

		if ($this->inscricaoModel->skipvalidation(true)->protect(false)->save($inscricao)) {
			$inscricao_id = $this->inscricaoModel->getInsertID();
			$this->inscricaoModel
				->protect(false)
				->where('id', $inscricao_id)
				->set('user_id', $user_id)
				->update();


			//$this->enviaEmailInscricao($post['email']);

			return redirect()->to(site_url("concursos/inscricao_kpop/" . $post['concurso_id']))->with('sucesso', "Inscrição realizada com sucesso! Confirma os dados em seu e-mail!");
		}

		return redirect()->to(site_url("concursos/inscricao_kpop/" . $post['concurso_id']))->with('atencao', "Erro ao realizar inscrição, contate o suporte!");
	}

	public function registrar_inscricao_cosplay_open()
	{

		// Envio o hash do token do form
		$retorno['token'] = csrf_hash();


		// Recupero o post da requisição
		$post = $this->request->getPost();

		$email = $post['email'];



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
			->where('clientes.email', $email)
			->orderBy('id', 'DESC')
			->first();


		if ($cliente != null) {
			$user_id = $cliente->usuario_id;
			if ($cliente->telefone == null || $cliente->telefone == '') {
				$this->clienteModel
					->protect(false)
					->where(
						'usuario_id',
						$user_id
					)
					->set('telefone', $post['telefone'])
					->update();
			}

			if ($cliente->nome == null || $cliente->nome == '') {
				$this->clienteModel
					->protect(false)
					->where(
						'usuario_id',
						$user_id
					)
					->set('nome', $post['nome'])
					->update();
			}
		} else {
			//criqar usuario e pegar o ID
			//$user_id = $this->usuarioModel->getInsertID();

			$cliente = new Cliente($post);
			if ($this->clienteModel->save($cliente)) {
				// Cria usuario do cliente
				$newuser = $this->criaUsuarioParaCliente($cliente);


				// Envia dados de acesso ao clente
				$this->enviaEmailCriacaoEmailAcesso($cliente);

				if ($cliente->telefone) {
					$mensagem = "Saudações " . $cliente->nome . "\n\nSua conta no Mundo Dream foi criada com sucesso e você já pode adquirir seus ingressos para o Dreamfest e desfrutar de tudo o que temos a te oferecer! \n\nSeja muito bem vindo(a), seus dados de acesso são: \n*link:* " . esc(site_url("/")) . "\n*E-mail de acesso:* " . $cliente->email . "\n*Senha:*  " . $newuser . "\n\nAtenciosamente, \nDepartamento de Relacionamento";

					$telefone = str_replace([' ', '-', '(', ')'], '', $cliente->telefone);
					if (strlen($telefone) == 10 || strlen($telefone) == 11) {
						// Verificar se o número começa com 9 (para números de celular no Brasil)
						if (
							strlen($telefone) == 11 && substr($telefone, 2, 1) != '9'
						) {
							return false;
						}
						$api = $this->notifyService->notificawpp($cliente, $mensagem);
					}
				}

				$cliente_id = $this->clienteModel->getInsertID();

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
					->where('clientes.id', $cliente_id)
					->orderBy('id', 'DESC')
					->first();

				$user_id = $cliente->usuario_id;
			}
		}


		$imagem = $this->request->getFile('referencia');
		//$video = $this->request->getFile('video_led');




		list($largura, $altura) = getimagesize($imagem->getPathName());

		if ($largura < "100" || $altura < "100") {
			$retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
			$retorno['erros_model'] = ['dimensao' => 'A imagem não pode ser menor do que 300 x 300 pixels'];

			// Retorno para o ajax request
			return redirect()->to(site_url("concursos/inscricao_cosplay/" . $post['concurso_id']))->with('atencao', "Erro ao realizar inscrição, contate o suporte!");
		}


		$caminhoImagem = $imagem->store('concursos');
		//$caminhoVideo = $video->store('concursos');



		// C:\xampp\htdocs\ordem\writable\uploads/usuarios/1625800273_8dc568f411ea409f3e16.jpg
		$caminhoImagem = WRITEPATH . "uploads/$caminhoImagem";
		//$caminhoVideo = WRITEPATH . "uploads/$caminhoVideo";



		$inscricao = new Inscricao($post);
		$inscricao->referencia = $imagem->getName();
		$inscricao->codigo = $this->inscricaoModel->geraCodigo();
		$inscricao->status = 'INICIADA';
		//$inscricao->video_led = $video->getName();

		if ($this->inscricaoModel->skipvalidation(true)->protect(false)->save($inscricao)) {
			$inscricao_id = $this->inscricaoModel->getInsertID();
			$this->inscricaoModel
				->protect(false)
				->where('id', $inscricao_id)
				->set('user_id', $user_id)
				->update();


			//$this->enviaEmailInscricao($post['email']);

			return redirect()->to(site_url("concursos/inscricao_cosplay/" . $post['concurso_id']))->with('sucesso', "Inscrição realizada com sucesso! Confirma os dados em seu e-mail!");
		}

		return redirect()->to(site_url("concursos/inscricao_cosplay/" . $post['concurso_id']))->with('atencao', "Erro ao realizar inscrição, contate o suporte!");
	}

	public function registrar_inscricao_cosplay_open_apresentacao()
	{

		// Envio o hash do token do form
		$retorno['token'] = csrf_hash();


		// Recupero o post da requisição
		$post = $this->request->getPost();

		$email = $post['email'];



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
			->where('clientes.email', $email)
			->orderBy('id', 'DESC')
			->first();


		if ($cliente != null) {
			$user_id = $cliente->usuario_id;
			if ($cliente->telefone == null || $cliente->telefone == '') {
				$this->clienteModel
					->protect(false)
					->where(
						'usuario_id',
						$user_id
					)
					->set('telefone', $post['telefone'])
					->update();
			}

			if ($cliente->nome == null || $cliente->nome == '') {
				$this->clienteModel
					->protect(false)
					->where(
						'usuario_id',
						$user_id
					)
					->set('nome', $post['nome'])
					->update();
			}
		} else {
			//criqar usuario e pegar o ID
			//$user_id = $this->usuarioModel->getInsertID();

			$cliente = new Cliente($post);
			if ($this->clienteModel->save($cliente)) {
				// Cria usuario do cliente
				$newuser = $this->criaUsuarioParaCliente($cliente);


				// Envia dados de acesso ao clente
				$this->enviaEmailCriacaoEmailAcesso($cliente);

				if ($cliente->telefone) {
					$mensagem = "Saudações " . $cliente->nome . "\n\nSua conta no Mundo Dream foi criada com sucesso e você já pode adquirir seus ingressos para o Dreamfest e desfrutar de tudo o que temos a te oferecer! \n\nSeja muito bem vindo(a), seus dados de acesso são: \n*link:* " . esc(site_url("/")) . "\n*E-mail de acesso:* " . $cliente->email . "\n*Senha:*  " . $newuser . "\n\nAtenciosamente, \nDepartamento de Relacionamento";

					$telefone = str_replace([' ', '-', '(', ')'], '', $cliente->telefone);
					if (strlen($telefone) == 10 || strlen($telefone) == 11) {
						// Verificar se o número começa com 9 (para números de celular no Brasil)
						if (
							strlen($telefone) == 11 && substr($telefone, 2, 1) != '9'
						) {
							return false;
						}
						$api = $this->notifyService->notificawpp($cliente, $mensagem);
					}
				}

				$cliente_id = $this->clienteModel->getInsertID();

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
					->where('clientes.id', $cliente_id)
					->orderBy('id', 'DESC')
					->first();

				$user_id = $cliente->usuario_id;
			}
		}


		$imagem = $this->request->getFile('referencia');
		$video = $this->request->getFile('video_led');




		list($largura, $altura) = getimagesize($imagem->getPathName());

		if ($largura < "100" || $altura < "100") {
			$retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
			$retorno['erros_model'] = ['dimensao' => 'A imagem não pode ser menor do que 300 x 300 pixels'];

			// Retorno para o ajax request
			return redirect()->to(site_url("concursos/inscricao_cosplay/" . $post['concurso_id']))->with('atencao', "Erro ao realizar inscrição, contate o suporte!");
		}


		$caminhoImagem = $imagem->store('concursos');
		$caminhoVideo = $video->store('concursos');



		// C:\xampp\htdocs\ordem\writable\uploads/usuarios/1625800273_8dc568f411ea409f3e16.jpg
		$caminhoImagem = WRITEPATH . "uploads/$caminhoImagem";
		$caminhoVideo = WRITEPATH . "uploads/$caminhoVideo";



		$inscricao = new Inscricao($post);
		$inscricao->referencia = $imagem->getName();
		$inscricao->codigo = $this->inscricaoModel->geraCodigo();
		$inscricao->status = 'INICIADA';
		$inscricao->video_led = $video->getName();

		if ($this->inscricaoModel->skipvalidation(true)->protect(false)->save($inscricao)) {
			$inscricao_id = $this->inscricaoModel->getInsertID();
			$this->inscricaoModel
				->protect(false)
				->where('id', $inscricao_id)
				->set('user_id', $user_id)
				->update();


			//$this->enviaEmailInscricao($post['email']);

			return redirect()->to(site_url("concursos/inscricao_cosplay/" . $post['concurso_id']))->with('sucesso', "Inscrição realizada com sucesso! Confirma os dados em seu e-mail!");
		}

		return redirect()->to(site_url("concursos/inscricao_cosplay/" . $post['concurso_id']))->with('atencao', "Erro ao realizar inscrição, contate o suporte!");
	}



	public function aprovaInscricao($id)
	{
		$inscricao = $this->inscricaoModel->withDeleted(true)->where('id', $id)->first();

		$concurso = $this->concursoModel->withDeleted(true)->where('id', $inscricao->concurso_id)->first();


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
			->where('clientes.usuario_id', $inscricao->user_id)
			->orderBy('id', 'DESC')
			->first();

		$this->inscricaoModel
			->protect(false)
			->where('id', $id)
			->set('status', 'APROVADA')
			->update();

		$inscricao = $this->inscricaoModel->withDeleted(true)->where('id', $id)->first();

		$this->enviaEmailInscricaoAprovada($cliente, $inscricao, $concurso);

		if ($cliente->telefone) {
			$mensagem = "Saudações " . $cliente->nome . "\n\nSua inscrição " . $inscricao->codigo . " para o " . $concurso->nome . " foi aprovada 🤩 \nEstamos muito felizes em contar com você no evento geek mais mágico do sul do Brasil! \n\n Acesse agora mesmo " . esc(site_url("/")) . " com seu email " . $cliente->email . " e senha para fazer o checkin e confirmar a sua inscrição!\n\nDetalhes do evento: \n*Dreamfest 24 - Mega Festival Geek* \n8 e 9 de junho das 10h às 19h \nCentro de eventos da PUCRS - Porto Alegre RS \n\nGeek que é geek não 😴 no ponto!";

			$telefone = str_replace([' ', '-', '(', ')'], '', $cliente->telefone);
			if (strlen($telefone) == 10 || strlen($telefone) == 11) {
				// Verificar se o número começa com 9 (para números de celular no Brasil)
				if (
					strlen($telefone) == 11 && substr($telefone, 2, 1) != '9'
				) {
					return false;
				}
				$api = $this->notifyService->notificawpp($cliente, $mensagem);
			}
		}


		//$this->enviaEmailInscricao($post['email']);

		return redirect()->to(site_url("concursos/gerenciar/" . $inscricao->concurso_id))->with('sucesso', "Inscrição Aprovada com sucesso!");
	}

	private function enviaEmailInscricaoAprovada(object $cliente, object $inscricao, object $concurso): void
	{
		// Buscar dados do evento se disponível
		$evento = null;
		if ($concurso->evento_id) {
			$eventoModel = new \App\Models\EventoModel();
			$evento = $eventoModel->find($concurso->evento_id);
		}

		// Preparar dados para o template
		$data = [
			'cliente' => $cliente,
			'inscricao' => $inscricao,
			'concurso' => $concurso,
			'evento' => $evento,
		];

		// Gerar o conteúdo HTML do email usando o template existente
		$mensagem = view('Concursos/email_aprovado', $data);

		// Enviar via Resend
		$this->resendService->enviarEmail(
			$cliente->email,
			'Sua Inscrição para o ' . $concurso->nome . ' foi aprovada!',
			$mensagem
		);
	}



	public function cancelaInscricao($id)
	{
		$inscricao = $this->inscricaoModel->withDeleted(true)->where('id', $id)->first();

		$concurso = $this->concursoModel->withDeleted(true)->where('id', $inscricao->concurso_id)->first();


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
			->where('clientes.usuario_id', $inscricao->user_id)
			->orderBy('id', 'DESC')
			->first();

		$this->inscricaoModel
			->protect(false)
			->where('id', $id)
			->set('status', 'CANCELADA')
			->update();

		$inscricao = $this->inscricaoModel->withDeleted(true)->where('id', $id)->first();

		$this->enviaEmailInscricaoCancelada($cliente, $inscricao, $concurso);

		if ($cliente->telefone) {
			$mensagem = "Saudações " . $cliente->nome . "\n\nSua inscrição " . $inscricao->codigo . " para o " . $concurso->nome . " foi *CANCELADA* 😥! \n Infelizmente a sua inscrição foi rejeiatda. Provavelmente algum dos itens obrigatórios foi preenchido incorretamente. Refaça sua inscrição ou entre em contato conosco respondendo essa mensagem!\n\n Acesse agora mesmo " . esc(site_url("/")) . " com seu email " . $cliente->email . " para acompanhar a sua inscrição!\n\nDetalhes do evento: \n*Dreamfest 24 - Mega Festival Geek* \n8 e 9 de junho das 10h às 19h \nCentro de eventos da PUCRS - Porto Alegre RS \n\nGeek que é geek não 😴 no ponto!";

			$telefone = str_replace([' ', '-', '(', ')'], '', $cliente->telefone);
			if (strlen($telefone) == 10 || strlen($telefone) == 11) {
				// Verificar se o número começa com 9 (para números de celular no Brasil)
				if (
					strlen($telefone) == 11 && substr($telefone, 2, 1) != '9'
				) {
					return false;
				}
				$api = $this->notifyService->notificawpp($cliente, $mensagem);
			}
		}


		//$this->enviaEmailInscricao($post['email']);

		return redirect()->to(site_url("concursos/gerenciar/" . $inscricao->concurso_id))->with('sucesso', "Inscrição Rejeitada com sucesso!");
	}

	public function RejeitaInscricao($id)
	{
		$inscricao = $this->inscricaoModel->withDeleted(true)->where('id', $id)->first();

		$concurso = $this->concursoModel->withDeleted(true)->where('id', $inscricao->concurso_id)->first();


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
			->where('clientes.usuario_id', $inscricao->user_id)
			->orderBy('id', 'DESC')
			->first();

		$this->inscricaoModel
			->protect(false)
			->where('id', $id)
			->set('status', 'REJEITADA')
			->update();

		$inscricao = $this->inscricaoModel->withDeleted(true)->where('id', $id)->first();

		$this->enviaEmailInscricaoRejeitada($cliente, $inscricao, $concurso);

		if ($cliente->telefone) {
			$mensagem = "Saudações " . $cliente->nome . "\n\nSua inscrição " . $inscricao->codigo . " para o " . $concurso->nome . " foi rejeitada 😞 \nInfelizmente a sua inscrição foi rejeiatda. Provavelmente algum dos itens obrigatórios foi preenchido incorretamente. Refaça sua inscrição ou entre em contato conosco através do whatsapp! \n\n Acesse agora mesmo " . esc(site_url("/")) . " com seu email " . $cliente->email . " para visualizar a sua inscrição!\n\nDetalhes do evento: \n*Dreamfest 24 - Mega Festival Geek* \n8 e 9 de junho das 10h às 19h \nCentro de eventos da PUCRS - Porto Alegre RS \n\nGeek que é geek não 😴 no ponto!";

			$telefone = str_replace([' ', '-', '(', ')'], '', $cliente->telefone);
			if (strlen($telefone) == 10 || strlen($telefone) == 11) {
				// Verificar se o número começa com 9 (para números de celular no Brasil)
				if (
					strlen($telefone) == 11 && substr($telefone, 2, 1) != '9'
				) {
					return false;
				}
				$api = $this->notifyService->notificawpp($cliente, $mensagem);
			}
		}


		//$this->enviaEmailInscricao($post['email']);

		return redirect()->to(site_url("concursos/gerenciar/" . $inscricao->concurso_id))->with('sucesso', "Inscrição Rejeitada com sucesso!");
	}

	private function enviaEmailInscricaoRejeitada(object $cliente, object $inscricao, object $concurso): void
	{
		// Buscar dados do evento se disponível
		$evento = null;
		if ($concurso->evento_id) {
			$eventoModel = new \App\Models\EventoModel();
			$evento = $eventoModel->find($concurso->evento_id);
		}

		$data = [
			'cliente' => $cliente,
			'inscricao' => $inscricao,
			'concurso' => $concurso,
			'evento' => $evento,
		];

		$mensagem = view('Concursos/email_rejeitado', $data);

		// Enviar via Resend
		$this->resendService->enviarEmail(
			$cliente->email,
			'Sua Inscrição para o ' . $concurso->nome . ' foi rejeitada!',
			$mensagem
		);
	}

	private function enviaEmailInscricaoCancelada(object $cliente, object $inscricao, object $concurso): void
	{
		// Buscar dados do evento se disponível
		$evento = null;
		if ($concurso->evento_id) {
			$eventoModel = new \App\Models\EventoModel();
			$evento = $eventoModel->find($concurso->evento_id);
		}

		$data = [
			'cliente' => $cliente,
			'inscricao' => $inscricao,
			'concurso' => $concurso,
			'evento' => $evento,
		];

		$mensagem = view('Concursos/email_cancelada', $data);

		// Enviar via Resend
		$this->resendService->enviarEmail(
			$cliente->email,
			'Sua Inscrição para o ' . $concurso->nome . ' foi CANCELADA!',
			$mensagem
		);
	}



	public function checkin($id)
	{
		$inscricao = $this->inscricaoModel->withDeleted(true)->where('id', $id)->first();

		$concurso = $this->concursoModel->withDeleted(true)->where('id', $inscricao->concurso_id)->first();

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
			->where('clientes.usuario_id', $inscricao->user_id)
			->orderBy('id', 'DESC')
			->first();


		$inscricoes = $this->inscricaoModel->recuperaOrdem($id, $inscricao->concurso_id);


		//dd($inscricoes);

		$ordem = $inscricoes->ordem;
		//dd($ordem);

		$this->inscricaoModel
			->protect(false)
			->where('id', $id)
			->set('status', 'CHECKIN')
			->set('ordem', $ordem + 1)
			->update();

		$inscricao = $this->inscricaoModel->withDeleted(true)->where('id', $id)->first();

		$this->enviaEmailInscricaoCheckin($cliente, $inscricao, $concurso);
		if ($cliente->telefone) {
			$mensagem = "Saudações " . $cliente->nome . "\n\nO checkin da sua inscrição " . $inscricao->codigo . " para o " . $concurso->nome . " foi realziado com sucesso e sua apresentação está liberada!!! \n Sua ordem de apresentação no Palco Mundo é: " . $inscricao->ordem . "º\n\n Acesse agora mesmo " . esc(site_url("/")) . " com seu email " . $cliente->email . " para acompanhar a sua inscrição!\n\nDetalhes do evento: \n*Dreamfest 24 - Mega Festival Geek* \n8 e 9 de junho das 10h às 19h \nCentro de eventos da PUCRS - Porto Alegre RS \n\nGeek que é geek não 😴 no ponto!";

			$telefone = str_replace([' ', '-', '(', ')'], '', $cliente->telefone);
			if (strlen($telefone) == 10 || strlen($telefone) == 11) {
				// Verificar se o número começa com 9 (para números de celular no Brasil)
				if (
					strlen($telefone) == 11 && substr($telefone, 2, 1) != '9'
				) {
					return false;
				}
				$api = $this->notifyService->notificawpp($cliente, $mensagem);
			}
		}

		//$this->enviaEmailInscricao($post['email']);

		return redirect()->to(site_url("concursos/gerenciar/" . $inscricao->concurso_id))->with('sucesso', "Checkin realizado com sucesso!");
	}

	public function checkinonline($id)
	{
		$inscricao = $this->inscricaoModel->withDeleted(true)->where('id', $id)->first();

		$concurso = $this->concursoModel->withDeleted(true)->where('id', $inscricao->concurso_id)->first();

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
			->where('clientes.usuario_id', $inscricao->user_id)
			->orderBy('id', 'DESC')
			->first();

		$this->inscricaoModel
			->protect(false)
			->where('id', $id)
			->set('status', 'CHECKIN-ONLINE')
			->update();

		$inscricao = $this->inscricaoModel->withDeleted(true)->where('id', $id)->first();


		$this->enviaEmailInscricaoCheckinOnline($cliente, $inscricao, $concurso);

		if ($cliente->telefone) {
			$mensagem = "Saudações " . $cliente->nome . "\n\nSua inscrição " . $inscricao->codigo . " para o " . $concurso->nome . " foi confirmada e seu ckeckin online realizado com sucesso! \n\n Acesse agora mesmo " . esc(site_url("/")) . " com seu email " . $cliente->email . " para acompanhar a sua inscrição!\n\nDetalhes do evento: \n*Dreamfest 24 - Mega Festival Geek* \n8 e 9 de junho das 10h às 19h \nCentro de eventos da PUCRS - Porto Alegre RS \n\nGeek que é geek não 😴 no ponto!";

			$telefone = str_replace([' ', '-', '(', ')'], '', $cliente->telefone);
			if (strlen($telefone) == 10 || strlen($telefone) == 11) {
				// Verificar se o número começa com 9 (para números de celular no Brasil)
				if (
					strlen($telefone) == 11 && substr($telefone, 2, 1) != '9'
				) {
					return false;
				}
				$api = $this->notifyService->notificawpp($cliente, $mensagem);
			}
		}


		//$this->enviaEmailInscricao($post['email']);

		return redirect()->to(site_url("concursos/gerenciar/" . $inscricao->concurso_id))->with('sucesso', "Checkin realizado com sucesso!");
	}

	private function enviaEmailInscricaoCheckin(object $cliente, object $inscricao, object $concurso): void
	{
		// Buscar dados do evento se disponível
		$evento = null;
		if ($concurso->evento_id) {
			$eventoModel = new \App\Models\EventoModel();
			$evento = $eventoModel->find($concurso->evento_id);
		}

		$data = [
			'cliente' => $cliente,
			'inscricao' => $inscricao,
			'concurso' => $concurso,
			'evento' => $evento,
		];

		$mensagem = view('Concursos/email_checkin', $data);

		// Enviar via Resend
		$this->resendService->enviarEmail(
			$cliente->email,
			'Checkin para o ' . $concurso->nome . ' realizado com sucesso!',
			$mensagem
		);
	}

	private function enviaEmailInscricaoCheckinOnline(object $cliente, object $inscricao, object $concurso): void
	{
		// Buscar dados do evento se disponível
		$evento = null;
		if ($concurso->evento_id) {
			$eventoModel = new \App\Models\EventoModel();
			$evento = $eventoModel->find($concurso->evento_id);
		}

		$data = [
			'cliente' => $cliente,
			'inscricao' => $inscricao,
			'concurso' => $concurso,
			'evento' => $evento,
		];

		$mensagem = view('Concursos/email_checkin_online', $data);

		// Enviar via Resend
		$this->resendService->enviarEmail(
			$cliente->email,
			'Checkin para o ' . $concurso->nome . ' realizado com sucesso!',
			$mensagem
		);
	}

	private function enviaEmailInscricao($cliente): void
	{
		$mensagem = view('Concursos/email_inscricao');

		// Enviar via Resend
		$this->resendService->enviarEmail(
			$cliente,
			'Olá, Sua inscrição está pronta!',
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
		$data = [
			'cliente' => $cliente,
		];

		$mensagem = view('Pedidos/email_envio_cartao', $data);

		// Enviar via Resend
		$this->resendService->enviarEmail(
			$cliente->email,
			'Endereço atualizado com sucesso!',
			$mensagem
		);
	}

	private function manipulaImagem(string $caminhoImagem, int $usuario_id)
	{
		service('image')
			->withFile($caminhoImagem)
			->fit(300, 300, 'center')
			->save($caminhoImagem);


		$anoAtual = date('Y');

		// Adicionar uma marca d'água de texto
		\Config\Services::image('imagick')
			->withFile($caminhoImagem)
			->text("Ordem $anoAtual - User-ID $usuario_id", [
				'color'      => '#fff',
				'opacity'    => 0.5,
				'withShadow' => false,
				'hAlign'     => 'center',
				'vAlign'     => 'bottom',
				'fontSize'   => 10
			])
			->save($caminhoImagem);
	}

	private function removeImagemDoFileSystem(string $imagem)
	{
		$caminhoImagem = WRITEPATH . "uploads/usuarios/$imagem";

		if (is_file($caminhoImagem)) {
			unlink($caminhoImagem);
		}
	}

	public function imagem(string $imagem = null)
	{
		if ($imagem != null) {
			$this->exibeArquivo('concursos', $imagem);
		}
	}

	private function criaUsuarioParaCliente(object $cliente): void
	{

		// Montamos os dados do usuário do cliente
		$usuario = [
			'nome' => $cliente->nome,
			'email' => $cliente->email,
			'password' => '123456',
			'ativo' => true,
		];

		// Criamos o usuário do cliente
		$this->usuarioModel->skipValidation(true)->protect(false)->insert($usuario);

		// Montamos os dados do grupo que o usuário fará parte
		$grupoUsuario = [
			'grupo_id' => 2, // Grupo de clientes.... lembrem que esse ID jamais deverá ser alterado ou removido.
			'usuario_id' => $this->usuarioModel->getInsertID(),
		];

		// Inserimos o usuário no grupo de clientes
		$this->grupoUsuarioModel->protect(false)->insert($grupoUsuario);

		// Atualizamos a tabela de clientes com o ID do usuário criado
		$this->clienteModel
			->protect(false)
			->where('id', $this->clienteModel->getInsertID())
			->set('usuario_id', $this->usuarioModel->getInsertID())
			->update();
	}

	/**
	 * Método que envia o e-mail para o cliente informando a alteração no e-mail de acesso.
	 *
	 * @param object $usuario
	 * @return void
	 */
	private function enviaEmailCriacaoEmailAcesso(object $cliente): void
	{
		$data = [
			'cliente' => $cliente,
		];

		$mensagem = view('Clientes/email_dados_acesso', $data);

		// Enviar via Resend
		$this->resendService->enviarEmail(
			$cliente->email,
			'Dados de acesso ao sistema',
			$mensagem
		);
	}
}
