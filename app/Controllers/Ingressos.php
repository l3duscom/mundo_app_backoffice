<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\Cliente;
use App\Entities\Cartao;
use App\Entities\Credencial;
use App\Entities\Ingresso;
use App\Traits\ValidacoesTrait;
use App\Services\ResendService;

use Dompdf\Dompdf;
use Picqer\Barcode\BarcodeGenerator;
use Picqer\Barcode\BarcodeGeneratorHTML;

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QROutputInterface;

class Ingressos extends BaseController
{
	use ValidacoesTrait;

	private $clienteModel;
	private $usuarioModel;
	private $cartaoModel;
	private $ingressoModel;
	private $pedidosModel;
	private $enderecoModel;
	private $credencialModel;
	private $eventolModel;
	private $resendService;
	private $bonusModel;
	private $codigoBonusModel;


	public function __construct()
	{
		$this->clienteModel = new \App\Models\ClienteModel();
		$this->usuarioModel = new \App\Models\UsuarioModel();
		$this->cartaoModel = new \App\Models\CartaoModel();
		$this->ingressoModel = new \App\Models\IngressoModel();
		$this->pedidosModel = new \App\Models\PedidoModel();
		$this->enderecoModel = new \App\Models\EnderecoModel();
		$this->credencialModel = new \App\Models\CredencialModel();
		$this->eventoModel = new \App\Models\EventoModel();
		$this->resendService = new ResendService();
		$this->bonusModel = new \App\Models\BonusModel();
		$this->codigoBonusModel = new \App\Models\CodigoBonusModel();
	}

	public function index()
	{

		$id = $this->usuarioLogado()->id;

		$cli = $this->clienteModel->withDeleted(true)->where('usuario_id', $id)->first();

		$cliente = $this->buscaclienteOu404($cli->id);

		$ingressos = $this->ingressoModel->recuperaIngressosPorUsuario($id);

		$ingressos_encerrados = $this->ingressoModel->recuperaIngressosPorUsuarioEncerrados($id);
		$card = $this->cartaoModel->withDeleted(true)->where('user_id', $id)->first();
		foreach ($ingressos as $key => $value) {
			$ingressos[$key]->qr = (new QRCode)->render($ingressos[$key]->codigo);
		}
		//dd($ingressos);
		$data = [
			'titulo' => 'Dashboard de ' . esc($cliente->nome),
			'cliente' => $cliente,
			'card' => $card,
			'ingressos' => $ingressos,
			'ingressos_encerrados' => $ingressos_encerrados
		];


		return view('Ingressos/index', $data);
	}

	public function encerrados()
	{

		$id = $this->usuarioLogado()->id;

		$cli = $this->clienteModel->withDeleted(true)->where('usuario_id', $id)->first();

		$cliente = $this->buscaclienteOu404($cli->id);


		$ingressos = $this->ingressoModel->recuperaIngressosPorUsuarioEncerrados($id);
		$card = $this->cartaoModel->withDeleted(true)->where('user_id', $id)->first();
		//dd($ingressos);
		$data = [
			'titulo' => 'Dashboard de ' . esc($cliente->nome),
			'cliente' => $cliente,
			'card' => $card,
			'ingressos' => $ingressos
		];


		return view('Ingressos/encerrados', $data);
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

	/**
	 * Método para gerar QRCode corretamente
	 *
	 * @param string $codigo
	 * @return string
	 */
	private function gerarQRCode($codigo)
	{
		$qrcode = new QRCode(new QROptions([
			'outputType' => QRCode::OUTPUT_IMAGE_PNG,
			'eccLevel' => QRCode::ECC_L,
			'scale' => 5,
			'imageBase64' => false,
		]));
		return $qrcode->render($codigo);
	}

	public function vincular_cinemark()
	{

		if (!$this->request->isAJAX()) {
			return redirect()->back();
		}

		// Envio o hash do token do form
		$retorno['token'] = csrf_hash();

		// Recupero o post da requisição
		$post = $this->request->getPost();

		$codigoInformado = trim($post['cinemark']);

		// ========================================
		// VALIDAÇÃO: Verificar se código existe na tabela codigo_bonus
		// ========================================
		$codigoBonus = $this->codigoBonusModel->where('codigo', $codigoInformado)->first();

		if (!$codigoBonus) {
			$retorno['erro'] = 'Código não encontrado. Verifique se o código foi cadastrado corretamente no sistema.';
			return $this->response->setJSON($retorno);
		}

		// Verificar se já existe um bonus para este ingresso (para permitir atualização)
		$bonusExistente = $this->bonusModel
			->where('ingresso_id', $post['ingresso_id'])
			->where('tipo_bonus', 'cinemark')
			->first();

		// Se o código já foi usado E não é o mesmo que já está vinculado a este ingresso
		if ($codigoBonus->usado == 1) {
			// Se está atualizando para o mesmo código que já tinha, permitir
			if ($bonusExistente && $bonusExistente->codigo === $codigoInformado) {
				// OK - é o mesmo código, pode continuar
			} else {
				$retorno['erro'] = 'Este código já foi utilizado em outro ingresso. Por favor, informe um código disponível.';
				return $this->response->setJSON($retorno);
			}
		}

		$credencial = $this->ingressoModel->find($post['ingresso_id']);

		// Instruções padrão do Cinemark
		$instrucoes = "1 - Atualize ou baixe o APP Cinemark no Google Play ou APP Store.\n" .
			"2 - Faça seu login, selecione o cinema, filme de sua preferência.\n" .
			"3 - Selecione o horário da sessão e os assentos;\n" .
			"4 - Selecione o tipo de ingresso como Voucher e quantidade de ingressos que irá utilizar;\n" .
			"5 - Apresente seu voucher online no celular diretamente na entrada da sala do cinema.";

		$bonusData = [
			'ingresso_id' => $post['ingresso_id'],
			'user_id' => $credencial->user_id,
			'tipo_bonus' => 'cinemark',
			'codigo' => $codigoInformado,
			'instrucoes' => $instrucoes,
		];

		// Se já existe, atualiza; senão, insere
		if ($bonusExistente) {
			// Se está trocando de código, liberar o código antigo
			if ($bonusExistente->codigo !== $codigoInformado) {
				$codigoAntigo = $this->codigoBonusModel->where('codigo', $bonusExistente->codigo)->first();
				if ($codigoAntigo) {
					$this->codigoBonusModel->update($codigoAntigo->id, ['usado' => 0, 'bonus_id' => null]);
				}
			}
			$resultado = $this->bonusModel->update($bonusExistente->id, $bonusData);
			$bonusId = $bonusExistente->id;
			$mensagem = 'Cinemark atualizado com sucesso!';
		} else {
			$resultado = $this->bonusModel->insert($bonusData);
			$bonusId = $this->bonusModel->getInsertID();
			$mensagem = 'Cinemark vinculado com sucesso!';
		}

		if ($resultado) {
			// ========================================
			// MARCAR CÓDIGO COMO USADO na tabela codigo_bonus
			// ========================================
			$this->codigoBonusModel->update($codigoBonus->id, [
				'usado' => 1,
				'bonus_id' => $bonusId
			]);

			session()->setFlashdata('sucesso', $mensagem);

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
				->where('usuario_id', $credencial->user_id)
				->first();

			$this->enviaEmailCinemark($cliente);

			$retorno['id'] = $post['pedido_id'];

			return $this->response->setJSON($retorno);
		}
	}

	public function gerarIngressoPdf($id)
	{

		$user_id = $this->usuarioLogado()->id;

		$cli = $this->clienteModel->withDeleted(true)->where('usuario_id', $user_id)->first();

		$cliente = $this->buscaclienteOu404($cli->id);


		$ingresso = $this->ingressoModel->recuperaIngresso($id);

		if ($ingresso->user_id == $user_id) {
			if ($ingresso->participante != null) {
				$participante = $ingresso->participante;
			} else {
				$participante = $cliente->nome;
			}

			$generator = new BarcodeGeneratorHTML();
			$barcode = $generator->getBarcode($ingresso->codigo, $generator::TYPE_CODE_39);

			//$qrcode = $this->gerarQRCode($ingresso->codigo);
			$qrcode = (new QRCode)->render($ingresso->codigo);




			$data = [
				'titulo' => 'Meu ingresso: ' . $ingresso->codigo . ' - ' . date('d/m/Y H:i'),
				'ingresso' => $ingresso,
				'participante' => $participante,
				'barcode' => $barcode,
				'qrcode' => $qrcode
			];

			$nomeArquivo = 'Ingresso-' . $ingresso->slug . '-' . $ingresso->codigo . '.pdf';

			$dompdf = new Dompdf();

			$dompdf->loadHtml(view('Ingressos/pdf', $data));
			$dompdf->setPaper('A4');
			$dompdf->render();
			$dompdf->stream($nomeArquivo, ['Attachment' => false]);

			unset($dompdf);
			unset($dompdf);

			exit();
		} else {
			echo "Ingresso inválido";
		}
		//$participante = '';


	}

	public function gerarEtickett($id)
	{


		$ingresso = $id;

		$pedido = $this->pedidosModel->recuperaIngressoPedido($ingresso);




		$cli = $this->clienteModel->withDeleted(true)->where('usuario_id', $pedido->user_id)->first();


		$cliente = $this->buscaclienteOu404($cli->id);




		$todos = $this->ingressoModel->recuperaIngressosPorUsuario($pedido->user_id);



		$participante = '';


		$qrcode = $this->gerarQRCode($pedido->codigo);

		$data = [
			'titulo' => 'E-ticket: ' . $pedido->cod_pedido . ' - ' . date('d/m/Y H:i'),
			'ingresso' => $pedido,
			'cliente' => $cliente,
			'qrcode' => $qrcode
		];

		$nomeArquivo = 'E-ticket do pedido -' . $pedido->cod_pedido . '.pdf';

		$dompdf = new Dompdf();

		$dompdf->loadHtml(view('Ingressos/eticket', $data));
		$dompdf->setPaper('A4');
		$dompdf->render();
		$dompdf->stream($nomeArquivo, ['Attachment' => false]);

		unset($dompdf);
		unset($dompdf);

		exit();
	}

	public function gerarEtiqueta($pedido_id)
	{
		$pedido = $this->pedidosModel->recuperaPedido($pedido_id);
		$cliente = $this->clienteModel->withDeleted(true)->where('usuario_id', $pedido->user_id)->first();

		$ingressos = $this->ingressoModel->recuperaIngressosPorPedido($pedido_id);

		$data = [
			'titulo' => 'Etiqueta: ' . $pedido->cod_pedido . ' - ' . date('d/m/Y H:i'),
			'pedido' => $pedido,
			'cliente' => $cliente,
			'ingressos' => [],
		];


		//dd($cliente->nome);
		foreach ($ingressos as $ingresso) {
			$qrcode = (new QRCode)->render($ingresso->codigo);
			$participante = (isset($ingresso->participante) && trim($ingresso->participante) !== '') ? $ingresso->participante : $cliente->nome;

			$data['ingressos'][] = [
				'ingresso' => $ingresso,
				'qrcode' => $qrcode,
				'participante' => $participante,
			];
		}


		// Obter o renderer de view
		$renderer = \Config\Services::renderer();

		// Carregar a view como uma string
		$html = $renderer->setData($data)->render('Ingressos/etiqueta');

		// Configuração do mPDF
		$mpdf = new \Mpdf\Mpdf([
			'mode' => 'utf-8',
			'format' => [58, 28],
			'margin_left' => 0,
			'margin_right' => 0,
			'margin_top' => 0,
			'margin_bottom' => 0,
			'margin_header' => 0,
			'margin_footer' => 0


		]);

		// Gerar o PDF
		$mpdf->WriteHTML($html);
		$nomeArquivo = 'Etiqueta do pedido -' . $pedido->cod_pedido . '.pdf';
		$mpdf->Output($nomeArquivo, 'I');

		exit();
	}

	public function gerarEticket($pedido_id)
	{
		$pedido = $this->pedidosModel->recuperaPedido($pedido_id);
		$evento = $this->eventoModel->where('id', $pedido->evento_id)->first();
		
		$cliente = $this->clienteModel->withDeleted(true)->where('usuario_id', $pedido->user_id)->first();

		$ingressos = $this->ingressoModel->recuperaIngressosPorPedido($pedido_id);

		$data = [
			'titulo' => 'E-ticket: ' . $pedido->cod_pedido . ' - ' . date('d/m/Y H:i'),
			'pedido' => $pedido,
			'cliente' => $cliente,
			'evento' => $evento,
			'ingressos' => [],
		];

		


		//dd($cliente->nome);
		foreach ($ingressos as $ingresso) {
			$qrcode = (new QRCode)->render($ingresso->codigo);
			$participante = (isset($ingresso->participante) && trim($ingresso->participante) !== '') ? $ingresso->participante : $cliente->nome;

			$data['ingressos'][] = [
				'ingresso' => $ingresso,
				'qrcode' => $qrcode,
				'participante' => $participante,
			];
		}


		// Obter o renderer de view
		$renderer = \Config\Services::renderer();

		// Carregar a view como uma string
		$html = $renderer->setData($data)->render('Ingressos/eticket');

		// Configuração do mPDF
		$mpdf = new \Mpdf\Mpdf([
			'mode' => 'utf-8',
			'format' => [58, 150],
			'mode' => 'utf-8',
			'margin_left' => 4,
			'margin_right' => 7,
			'margin_top' => 5,
			'margin_bottom' => 1,
			'margin_header' => 0,
			'margin_footer' => 0
		]);

		// Gerar o PDF
		$mpdf->WriteHTML($html);
		$nomeArquivo = 'E-ticket do pedido -' . $pedido->cod_pedido . '.pdf';
		$mpdf->Output($nomeArquivo, 'I');

		exit();
	}

	public function gerarEticketGratis($pedido_id)
	{
		$pedido = $this->pedidosModel->recuperaPedido($pedido_id);
		$evento = $this->eventoModel->where('id', $pedido->evento_id)->first();
		$cliente = $this->clienteModel->withDeleted(true)->where('usuario_id', $pedido->user_id)->first();

		$ingressos = $this->ingressoModel->recuperaIngressosPorPedido($pedido_id);

		$data = [
			'titulo' => 'E-ticket: ' . $pedido->cod_pedido . ' - ' . date('d/m/Y H:i'),
			'pedido' => $pedido,
			'cliente' => $cliente,
			'evento' => $evento,
			'ingressos' => [],
		];



		foreach ($ingressos as $ingresso) {
			$qrcode = (new QRCode)->render($ingresso->codigo);
			$participante = $ingresso->participante ?? $cliente->nome;

			$data['ingressos'][] = [
				'ingresso' => $ingresso,
				'qrcode' => $qrcode,
				'participante' => $participante,
			];
		}


		// Obter o renderer de view
		$renderer = \Config\Services::renderer();

		// Carregar a view como uma string
		$html = $renderer->setData($data)->render('Ingressos/eticketgratis');

		// Configuração do mPDF
		$mpdf = new \Mpdf\Mpdf([
			'mode' => 'utf-8',
			'format' => [58, 150],
			'mode' => 'utf-8',
			'margin_left' => 4,
			'margin_right' => 7,
			'margin_top' => 5,
			'margin_bottom' => 1,
			'margin_header' => 0,
			'margin_footer' => 0
		]);

		// Gerar o PDF
		$mpdf->WriteHTML($html);
		$nomeArquivo = 'E-ticket do pedido -' . $pedido->cod_pedido . '.pdf';
		$mpdf->Output($nomeArquivo, 'I');

		exit();
	}

	public function gerarEticketPromo($pedido_id)
	{
		$pedido = $this->pedidosModel->recuperaPedido($pedido_id);
		$evento = $this->eventoModel->where('id', $pedido->evento_id)->first();
		$cliente = $this->clienteModel->withDeleted(true)->where('usuario_id', $pedido->user_id)->first();

		$ingressos = $this->ingressoModel->recuperaIngressosPorPedido($pedido_id);

		$data = [
			'titulo' => 'E-ticket: ' . $pedido->cod_pedido . ' - ' . date('d/m/Y H:i'),
			'pedido' => $pedido,
			'cliente' => $cliente,
			'evento' => $evento,
			'ingressos' => [],
		];



		foreach ($ingressos as $ingresso) {
			$qrcode = (new QRCode)->render($ingresso->codigo);
			$participante = $ingresso->participante ?? $cliente->nome;

			$data['ingressos'][] = [
				'ingresso' => $ingresso,
				'qrcode' => $qrcode,
				'participante' => $participante,
			];
		}


		// Obter o renderer de view
		$renderer = \Config\Services::renderer();

		// Carregar a view como uma string
		$html = $renderer->setData($data)->render('Ingressos/eticketpromo');

		// Configuração do mPDF
		$mpdf = new \Mpdf\Mpdf([
			'mode' => 'utf-8',
			'format' => [58, 150],
			'mode' => 'utf-8',
			'margin_left' => 4,
			'margin_right' => 7,
			'margin_top' => 5,
			'margin_bottom' => 1,
			'margin_header' => 0,
			'margin_footer' => 0
		]);

		// Gerar o PDF
		$mpdf->WriteHTML($html);
		$nomeArquivo = 'E-ticket do pedido -' . $pedido->cod_pedido . '.pdf';
		$mpdf->Output($nomeArquivo, 'I');

		exit();
	}



	public function vincular($id)
	{




		//$pedidos = $this->pedidosModel->recuperaPedidosPorPedido($pedido_id);
		$ingresso = $this->ingressoModel->recuperaIngresso($id);


		$credencial = $this->credencialModel->where('ingresso_id', $id)->first();

		//dd($endereco);
		$data = [
			'titulo' => 'Vincular credencial',
			'credencial' => $credencial,
			'id' => $id,
			'pedido' => $ingresso->pedido_id


		];




		return view('Ingressos/vincular', $data);
	}

	public function cinemark($id)
	{
		$ingresso = $this->ingressoModel->recuperaIngresso($id);
		$credencial = $this->ingressoModel->where('id', $id)->first();

		// Buscar bonus cinemark existente para este ingresso
		$bonus_cinemark = $this->bonusModel
			->where('ingresso_id', $id)
			->where('tipo_bonus', 'cinemark')
			->first();

		$data = [
			'titulo' => 'Vincular credencial',
			'credencial' => $credencial,
			'id' => $id,
			'pedido' => $ingresso->pedido_id,
			'bonus_cinemark' => $bonus_cinemark,
		];

		return view('Ingressos/cinemark', $data);
	}

	private function enviaEmailCinemark(object $cliente): void
	{
		$data = [
			'cliente' => $cliente,
		];

		$mensagem = view('Pedidos/email_cortesia', $data);

		// Enviar via Resend
		$this->resendService->enviarEmail(
			$cliente->email,
			'Olá, seu ingresso Cinemark foi enviado com sucesso!!!',
			$mensagem
		);
	}

	public function vincular_credencial()
	{
		if (!$this->request->isAJAX()) {
			return redirect()->back();
		}

		// Envio o hash do token do form
		$retorno['token'] = csrf_hash();




		// Recupero o post da requisição
		$post = $this->request->getPost();

		$credencial = new Credencial($post);

		if ($this->credencialModel->save($credencial)) {
			session()->setFlashdata('sucesso', 'Credencial vinculada com sucesso!');

			$retorno['id'] = $post['pedido_id'];


			return $this->response->setJSON($retorno);
		}

		// Retornamos os erros de validação
		$retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
		$retorno['erros_model'] = $this->enderecoModel->errors();

		// Retorno para o ajax request
		return $this->response->setJSON($retorno);
	}

	public function atualizar($id)
	{

		// Envio o hash do token do form
		$retorno['token'] = csrf_hash();

		// Recupero o post da requisição
		$post = $this->request->getPost();


		$ingresso = $this->ingressoModel->recuperaIngresso($id);

		$ingresso->fill($post);


		if ($this->ingressoModel->save($ingresso)) {

			return redirect()->to(site_url("ingressos"))->with('sucesso', "Participante alterado com sucesso");
		}

		return redirect()->to(site_url("ingressos"))->with('atencao', "Erro ao alterar o participante, contate o suporte!");
	}

	public function add()
	{
		if (!$this->usuarioLogado()->temPermissaoPara('editar-clientes')) {
			return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
		}

		// Verificar se há evento selecionado no contexto usando helper
		//$evento_selecionado = evento_selecionado_com_validacao();
		//$event_id = $evento_selecionado ? $evento_selecionado->id : null;

		// Se não há evento selecionado válido, redirecionar para seleção
		//if (!$evento_selecionado) {
			//return redirect()->to(site_url('/'))->with('atencao', 'Selecione um evento primeiro para adicionar ingressos.');
		//}

		$event_id = 17;
		$data = [
			//'titulo' => 'Add Ingressos ADMIN - ' . esc($evento_selecionado->nome),
			'event_id' => $event_id,
			'titulo' => 'Add Ingressos ADMIN' ,

		];

		return view('Carrinho/admin', $data);
	}

	public function pdv()
	{


		if (!$this->usuarioLogado()->temPermissaoPara('editar-clientes')) {

			return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
		}




		$data = [
			'titulo' => 'Add Ingressos ADMIN',


		];


		return view('Carrinho/pdv', $data);
	}
}
