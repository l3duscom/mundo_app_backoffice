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
use Dompdf\Dompdf;
use Dompdf\Options;



class Concursos extends BaseController
{
	use ValidacoesTrait;

	private $clienteModel;
	private $usuarioModel;
	private $cartaoModel;
	private $pedidosModel;
	private $concursoModel;
	private $inscricaoModel;
	private $inscricaoHistoricoModel;
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
		$this->inscricaoHistoricoModel = new \App\Models\InscricaoHistoricoModel();
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

	/**
	 * Exibe formulário de criação de concurso
	 */
	public function criar($evento_id)
	{
		if (!$this->usuarioLogado()->temPermissaoPara('juri')) {
			return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
		}

		$eventoModel = new \App\Models\EventoModel();
		$evento = $eventoModel->find($evento_id);

		if (!$evento) {
			return redirect()->back()->with('erro', 'Evento não encontrado.');
		}

		$data = [
			'titulo' => 'Novo Concurso',
			'evento' => $evento,
			'concurso' => null,
		];

		return view('Concursos/form_concurso', $data);
	}

	/**
	 * Salva novo concurso
	 */
	public function salvar()
	{
		if (!$this->usuarioLogado()->temPermissaoPara('juri')) {
			return redirect()->back()->with('atencao', 'Você não tem permissão para esta ação.');
		}

		$post = $this->request->getPost();

		// Gera código automaticamente
		$codigo = $this->concursoModel->geraCodigoPedido();
		
		// Gera slug a partir do nome
		$slug = url_title($post['nome'], '-', true);

		$dados = [
			'evento_id' => $post['evento_id'],
			'codigo' => $codigo,
			'nome' => $post['nome'],
			'slug' => $slug,
			'tipo' => $post['tipo'],
			'juri' => (int) $post['juri'],
			'ativo' => isset($post['ativo']) ? 1 : 0,
		];

		if ($this->concursoModel->insert($dados)) {
			return redirect()->to(site_url("concursos/{$post['evento_id']}"))->with('sucesso', 'Concurso criado com sucesso!');
		}

		return redirect()->back()->with('erro', 'Erro ao criar concurso. Tente novamente.')->withInput();
	}

	/**
	 * Exibe formulário de edição de concurso
	 */
	public function editar($id)
	{
		if (!$this->usuarioLogado()->temPermissaoPara('juri')) {
			return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
		}

		$concurso = $this->concursoModel->find($id);

		if (!$concurso) {
			return redirect()->back()->with('erro', 'Concurso não encontrado.');
		}

		$eventoModel = new \App\Models\EventoModel();
		$evento = $eventoModel->find($concurso->evento_id);

		$data = [
			'titulo' => 'Editar Concurso',
			'evento' => $evento,
			'concurso' => $concurso,
		];

		return view('Concursos/form_concurso', $data);
	}

	/**
	 * Atualiza concurso existente
	 */
	public function atualizar()
	{
		if (!$this->usuarioLogado()->temPermissaoPara('juri')) {
			return redirect()->back()->with('atencao', 'Você não tem permissão para esta ação.');
		}

		$post = $this->request->getPost();
		$id = $post['id'];

		$concurso = $this->concursoModel->find($id);
		if (!$concurso) {
			return redirect()->back()->with('erro', 'Concurso não encontrado.');
		}

		// Gera slug a partir do nome
		$slug = url_title($post['nome'], '-', true);

		$dados = [
			'nome' => $post['nome'],
			'slug' => $slug,
			'tipo' => $post['tipo'],
			'juri' => (int) $post['juri'],
			'ativo' => isset($post['ativo']) ? 1 : 0,
		];

		if ($this->concursoModel->update($id, $dados)) {
			return redirect()->to(site_url("concursos/{$concurso->evento_id}"))->with('sucesso', 'Concurso atualizado com sucesso!');
		}

		return redirect()->back()->with('erro', 'Erro ao atualizar concurso. Tente novamente.')->withInput();
	}

	/**
	 * Exclui concurso (apenas se não tiver inscritos)
	 */
	public function excluir($id)
	{
		if (!$this->usuarioLogado()->temPermissaoPara('juri')) {
			return redirect()->back()->with('atencao', 'Você não tem permissão para esta ação.');
		}

		$concurso = $this->concursoModel->find($id);
		if (!$concurso) {
			return redirect()->back()->with('erro', 'Concurso não encontrado.');
		}

		// Verificar se tem inscritos
		$qtdInscritos = $this->inscricaoModel->where('concurso_id', $id)->countAllResults();
		if ($qtdInscritos > 0) {
			return redirect()->back()->with('atencao', "Não é possível excluir este concurso pois existem {$qtdInscritos} inscrição(ões) vinculadas.");
		}

		if ($this->concursoModel->delete($id)) {
			return redirect()->to(site_url("concursos/{$concurso->evento_id}"))->with('sucesso', 'Concurso excluído com sucesso!');
		}

		return redirect()->back()->with('erro', 'Erro ao excluir concurso. Tente novamente.');
	}

	/**
	 * Duplica concurso
	 */
	public function duplicar($id)
	{
		if (!$this->usuarioLogado()->temPermissaoPara('juri')) {
			return redirect()->back()->with('atencao', 'Você não tem permissão para esta ação.');
		}

		$concurso = $this->concursoModel->find($id);
		if (!$concurso) {
			return redirect()->back()->with('erro', 'Concurso não encontrado.');
		}

		// Gera novo código
		$codigo = $this->concursoModel->geraCodigoPedido();
		
		// Gera novo slug com sufixo
		$slug = url_title($concurso->nome . ' copia', '-', true);

		$dados = [
			'evento_id' => $concurso->evento_id,
			'codigo' => $codigo,
			'nome' => $concurso->nome . ' (Cópia)',
			'slug' => $slug,
			'tipo' => $concurso->tipo,
			'juri' => $concurso->juri,
			'ativo' => 0, // Inativo por padrão
		];

		if ($this->concursoModel->insert($dados)) {
			return redirect()->to(site_url("concursos/{$concurso->evento_id}"))->with('sucesso', 'Concurso duplicado com sucesso! O novo concurso está inativo.');
		}

		return redirect()->back()->with('erro', 'Erro ao duplicar concurso. Tente novamente.');
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

	/**
	 * Exibe o ranking/pódio de um concurso
	 * 
	 * @param int $id ID do concurso
	 */
	public function ranking($id)
	{
		if (!$this->usuarioLogado()->temPermissaoPara('juri')) {
			return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
		}

		$concurso = $this->concursoModel->withDeleted(true)->where('id', $id)->first();

		if (!$concurso) {
			return redirect()->back()->with('erro', 'Concurso não encontrado.');
		}

		// Pegar filtro de categoria (para K-Pop)
		$categoria = $this->request->getGet('categoria') ?? 'todos';

		// Verificar se é concurso de cosplay
		$isCosplay = in_array($concurso->tipo, ['desfile_cosplay', 'apresentacao_cosplay', 'cosplay_kids']);

		// Buscar ranking
		$ranking = $this->inscricaoModel->getRankingConcurso($id, $concurso->tipo, $categoria);

		// Calcular estatísticas
		$totalParticipantes = count($ranking);
		$mediaGeral = $totalParticipantes > 0 
			? round(array_sum(array_column($ranking, 'media_nota_total')) / $totalParticipantes, 2)
			: 0;
		$maiorNota = $totalParticipantes > 0 
			? max(array_column($ranking, 'media_nota_total'))
			: 0;

		$data = [
			'titulo' => 'Ranking - ' . esc($concurso->nome),
			'concurso' => $concurso,
			'ranking' => $ranking,
			'categoria' => $categoria,
			'isCosplay' => $isCosplay,
			'totalParticipantes' => $totalParticipantes,
			'mediaGeral' => $mediaGeral,
			'maiorNota' => $maiorNota,
		];

		return view('Concursos/ranking', $data);
	}

	/**
	 * Gera PDF da CLASSIFICAÇÃO — mesmos dados exibidos na tela de ranking.
	 *
	 * GET concursos/classificacao-pdf/{id}?categoria=solo|dupla|grupo|todos
	 */
	public function classificacaoPdf($id)
	{
		if (!$this->usuarioLogado()->temPermissaoPara('juri')) {
			return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
		}

		$concurso = $this->concursoModel->withDeleted(true)->where('id', $id)->first();
		if (!$concurso) {
			return redirect()->back()->with('erro', 'Concurso não encontrado.');
		}

		$categoria = $this->request->getGet('categoria') ?? 'todos';
		$isCosplay = in_array($concurso->tipo, ['desfile_cosplay', 'apresentacao_cosplay', 'cosplay_kids']);

		$ranking = $this->inscricaoModel->getRankingConcurso($id, $concurso->tipo, $categoria);

		$totalParticipantes = count($ranking);
		$mediaGeral = $totalParticipantes > 0
			? round(array_sum(array_column($ranking, 'media_nota_total')) / $totalParticipantes, 2)
			: 0;
		$maiorNota = $totalParticipantes > 0
			? max(array_column($ranking, 'media_nota_total'))
			: 0;

		$evento = null;
		if (!empty($concurso->evento_id)) {
			$evento = (new \App\Models\EventoModel())->find($concurso->evento_id);
		}

		$data = [
			'concurso'            => $concurso,
			'ranking'             => $ranking,
			'categoria'           => $categoria,
			'isCosplay'           => $isCosplay,
			'totalParticipantes'  => $totalParticipantes,
			'mediaGeral'          => $mediaGeral,
			'maiorNota'           => $maiorNota,
			'evento'              => $evento,
			'geradoEm'            => date('d/m/Y H:i:s'),
			'geradoPor'           => $this->usuarioLogado()->nome,
			'geradoPorId'         => (int) $this->usuarioLogado()->id,
			'hashDocumento'       => substr(hash('sha256', 'CLASSIF-' . $id . '-' . $categoria . '-' . microtime(true)), 0, 16),
		];

		return $this->renderPdf(
			view('Concursos/pdf_classificacao', $data),
			'classificacao-' . $this->slugPdf($concurso->nome) . '-' . ($categoria !== 'todos' ? $categoria . '-' : '') . date('Y-m-d-His') . '.pdf'
		);
	}

	/**
	 * Gera PDF de AUDITORIA — mesma classificação + notas por jurado + hash de integridade.
	 *
	 * GET concursos/auditoria-pdf/{id}?categoria=solo|dupla|grupo|todos
	 */
	public function auditoriaPdf($id)
	{
		if (!$this->usuarioLogado()->temPermissaoPara('juri')) {
			return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
		}

		$concurso = $this->concursoModel->withDeleted(true)->where('id', $id)->first();
		if (!$concurso) {
			return redirect()->back()->with('erro', 'Concurso não encontrado.');
		}

		$categoria = $this->request->getGet('categoria') ?? 'todos';
		$isCosplay = in_array($concurso->tipo, ['desfile_cosplay', 'apresentacao_cosplay', 'cosplay_kids']);

		$ranking = $this->inscricaoModel->getRankingConcurso($id, $concurso->tipo, $categoria);

		// Labels das colunas de notas (mesmo padrão do detalhesAvaliacao)
		if ($isCosplay) {
			$categoriasNotas = [
				'nota_1' => 'Dificuldade',
				'nota_2' => 'Fidelidade',
				'nota_3' => 'Naturalidade',
				'nota_4' => 'Itens/Acessórios',
			];
		} else {
			$categoriasNotas = [
				'nota_1' => 'Coreografia',
				'nota_2' => 'Sincronia',
				'nota_3' => 'Performance',
				'nota_4' => 'Figurino',
			];
		}

		// Nomes reais dos jurados oficiais (IDs fixos)
		$juradosOficiaisIds = [8294, 8292, 18];
		$juradosOficiaisMap = [];
		$juradosRows = $this->usuarioModel
			->select('id, nome, email')
			->whereIn('id', $juradosOficiaisIds)
			->findAll();
		foreach ($juradosRows as $j) {
			$juradosOficiaisMap[(int) $j->id] = [
				'id'    => (int) $j->id,
				'nome'  => $j->nome,
				'email' => $j->email,
			];
		}
		foreach ($juradosOficiaisIds as $jid) {
			if (!isset($juradosOficiaisMap[$jid])) {
				$juradosOficiaisMap[$jid] = ['id' => $jid, 'nome' => 'Jurado #' . $jid, 'email' => null];
			}
		}

		// Para cada inscrição, buscar avaliações detalhadas
		// - Agrupa por jurado_id: escolhe a de maior nota_total (mesmo critério do ranking com MAX())
		// - Duplicatas ficam registradas separadamente para exibir aviso no relatório
		foreach ($ranking as &$item) {
			$avaliacoes = $this->avaliacaoModel->getAvaliacoesDetalhadas((int) $item['inscricao_id']);
			$item['avaliacoes'] = $avaliacoes;

			$porJurado = [];
			foreach ($avaliacoes as $av) {
				$jid = (int) $av['jurado_id'];
				if (!isset($porJurado[$jid])) {
					$porJurado[$jid] = ['picked' => $av, 'duplicates' => []];
					continue;
				}
				$notaAtual = (float) $av['nota_total'];
				$notaMax   = (float) $porJurado[$jid]['picked']['nota_total'];
				if ($notaAtual > $notaMax) {
					// A anterior vira duplicata desconsiderada
					$porJurado[$jid]['duplicates'][] = $porJurado[$jid]['picked'];
					$porJurado[$jid]['picked'] = $av;
				} else {
					$porJurado[$jid]['duplicates'][] = $av;
				}
			}
			$item['avaliacao_por_jurado'] = $porJurado;
		}
		unset($item);

		$totalParticipantes = count($ranking);
		$mediaGeral = $totalParticipantes > 0
			? round(array_sum(array_column($ranking, 'media_nota_total')) / $totalParticipantes, 2)
			: 0;
		$maiorNota = $totalParticipantes > 0
			? max(array_column($ranking, 'media_nota_total'))
			: 0;

		$evento = null;
		if (!empty($concurso->evento_id)) {
			$evento = (new \App\Models\EventoModel())->find($concurso->evento_id);
		}

		// Hash de integridade dos dados (para conferência posterior).
		// Considera apenas a avaliação "picked" por jurado (a mesma usada no ranking via MAX).
		$hashPayload = json_encode([
			'concurso_id' => (int) $concurso->id,
			'categoria'   => $categoria,
			'ranking'     => array_map(function ($r) {
				$avaliacoesFinal = [];
				foreach (($r['avaliacao_por_jurado'] ?? []) as $jid => $rec) {
					$avaliacoesFinal[] = [
						'jurado_id'          => (int) $jid,
						'nota_total_usada'   => (float) $rec['picked']['nota_total'],
						'duplicadas_ignoradas' => count($rec['duplicates']),
					];
				}
				return [
					'inscricao_id' => (int) $r['inscricao_id'],
					'media'        => (float) $r['media_nota_total'],
					'avaliacoes'   => $avaliacoesFinal,
				];
			}, $ranking),
		]);
		$hashDocumento = hash('sha256', $hashPayload);

		$data = [
			'concurso'             => $concurso,
			'ranking'              => $ranking,
			'categoria'            => $categoria,
			'isCosplay'            => $isCosplay,
			'totalParticipantes'   => $totalParticipantes,
			'mediaGeral'           => $mediaGeral,
			'maiorNota'            => $maiorNota,
			'evento'               => $evento,
			'categoriasNotas'      => $categoriasNotas,
			'juradosOficiaisIds'   => $juradosOficiaisIds,
			'juradosOficiaisMap'   => $juradosOficiaisMap,
			'geradoEm'             => date('d/m/Y H:i:s'),
			'geradoPor'            => $this->usuarioLogado()->nome,
			'geradoPorId'          => (int) $this->usuarioLogado()->id,
			'geradoPorEmail'       => $this->usuarioLogado()->email ?? null,
			'ip'                   => $this->request->getIPAddress(),
			'userAgent'            => (string) $this->request->getUserAgent(),
			'hashDocumento'        => $hashDocumento,
			'timestampUnix'        => time(),
		];

		return $this->renderPdf(
			view('Concursos/pdf_auditoria', $data),
			'auditoria-' . $this->slugPdf($concurso->nome) . '-' . ($categoria !== 'todos' ? $categoria . '-' : '') . date('Y-m-d-His') . '.pdf',
			'A4',
			'landscape'
		);
	}

	/**
	 * Renderiza o HTML em PDF via dompdf e streams no navegador.
	 */
	private function renderPdf(string $html, string $filename, string $paper = 'A4', string $orientation = 'portrait')
	{
		$options = new Options();
		$options->set('isRemoteEnabled', true);
		$options->set('isHtml5ParserEnabled', true);
		$options->set('defaultFont', 'DejaVu Sans');

		$dompdf = new Dompdf($options);
		$dompdf->loadHtml($html, 'UTF-8');
		$dompdf->setPaper($paper, $orientation);
		$dompdf->render();
		$dompdf->stream($filename, ['Attachment' => false]);
		exit;
	}

	/**
	 * Converte texto em slug pra usar em nome de arquivo.
	 */
	private function slugPdf(?string $texto): string
	{
		$texto = (string) $texto;
		$texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto) ?: $texto;
		$texto = strtolower($texto);
		$texto = preg_replace('/[^a-z0-9]+/', '-', $texto);
		return trim($texto, '-') ?: 'concurso';
	}

	/**
	 * Retorna detalhes das avaliações de uma inscrição (AJAX)
	 * 
	 * @param int $inscricao_id ID da inscrição
	 */
	public function detalhesAvaliacao($inscricao_id)
	{
		if (!$this->request->isAJAX()) {
			return redirect()->back();
		}

		if (!$this->usuarioLogado()->temPermissaoPara('juri')) {
			return $this->response->setJSON([
				'sucesso' => false,
				'mensagem' => 'Sem permissão para acessar.'
			]);
		}

		$avaliacoes = $this->avaliacaoModel->getAvaliacoesDetalhadas($inscricao_id);

		// Buscar informações da inscrição para saber o tipo de concurso
		$inscricao = $this->inscricaoModel->select('inscricoes.*, concursos.tipo')
			->join('concursos', 'concursos.id = inscricoes.concurso_id')
			->where('inscricoes.id', $inscricao_id)
			->first();

		$isCosplay = in_array($inscricao->tipo ?? '', ['desfile_cosplay', 'apresentacao_cosplay', 'cosplay_kids']);

		// Labels das categorias de notas
		if ($isCosplay) {
			$categorias = [
				'nota_1' => 'Dificuldade',
				'nota_2' => 'Fidelidade',
				'nota_3' => 'Naturalidade',
				'nota_4' => 'Itens/Acessórios',
			];
		} else {
			$categorias = [
				'nota_1' => 'Coreografia',
				'nota_2' => 'Sincronia',
				'nota_3' => 'Performance',
				'nota_4' => 'Figurino',
			];
		}

		return $this->response->setJSON([
			'sucesso' => true,
			'avaliacoes' => $avaliacoes,
			'categorias' => $categorias,
			'isCosplay' => $isCosplay
		]);
	}

	public function exportarCsvCompleto($id)
	{
		if (!$this->usuarioLogado()->temPermissaoPara('juri')) {
			return redirect()->back()->with('atencao', 'Sem permissão.');
		}

		$concurso = $this->concursoModel->withDeleted(true)->where('id', $id)->first();
		if (!$concurso) {
			return redirect()->back()->with('erro', 'Concurso não encontrado.');
		}

		$isCosplay = in_array($concurso->tipo, ['desfile_cosplay', 'apresentacao_cosplay', 'cosplay_kids']);
		if ($isCosplay) {
			$inscricoes = $this->inscricaoModel->recuperarecuperaInscricoesCosplayPorConcurso($id);
		} else {
			$inscricoes = $this->inscricaoModel->recuperarecuperaInscricoesKpopPorConcurso($id);
		}

		$limitePorArquivo = 1950;
		$chunks = array_chunk($inscricoes, $limitePorArquivo);
		$timestamp = date('Y-m-d-His');
		$slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($concurso->nome ?? 'concurso'));

		if (count($chunks) <= 1) {
			$this->response->setHeader('Content-Type', 'text/csv; charset=UTF-8');
			$this->response->setHeader('Content-Disposition', 'attachment; filename="inscricoes-' . $slug . '-' . $timestamp . '.csv"');
			$this->response->setHeader('Cache-Control', 'no-store, no-cache');
			return $this->response->setBody($this->gerarCsvCompleto($chunks[0] ?? [], $isCosplay));
		}

		$tempZip = tempnam(sys_get_temp_dir(), 'conc_') . '.zip';
		$zip = new \ZipArchive();
		if ($zip->open($tempZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
			return redirect()->back()->with('atencao', 'Falha ao gerar ZIP.');
		}
		foreach ($chunks as $i => $chunk) {
			$parte = str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);
			$zip->addFromString("inscricoes-{$slug}-{$timestamp}-parte-{$parte}.csv", $this->gerarCsvCompleto($chunk, $isCosplay));
		}
		$zip->close();
		$conteudo = file_get_contents($tempZip);
		@unlink($tempZip);
		$this->response->setHeader('Content-Type', 'application/zip');
		$this->response->setHeader('Content-Disposition', 'attachment; filename="inscricoes-' . $slug . '-' . $timestamp . '.zip"');
		$this->response->setHeader('Cache-Control', 'no-store, no-cache');
		return $this->response->setBody($conteudo);
	}

	public function exportarCsvContatos($id)
	{
		if (!$this->usuarioLogado()->temPermissaoPara('juri')) {
			return redirect()->back()->with('atencao', 'Sem permissão.');
		}

		$concurso = $this->concursoModel->withDeleted(true)->where('id', $id)->first();
		if (!$concurso) {
			return redirect()->back()->with('erro', 'Concurso não encontrado.');
		}

		$isCosplay = in_array($concurso->tipo, ['desfile_cosplay', 'apresentacao_cosplay', 'cosplay_kids']);
		if ($isCosplay) {
			$inscricoes = $this->inscricaoModel->recuperarecuperaInscricoesCosplayPorConcurso($id);
		} else {
			$inscricoes = $this->inscricaoModel->recuperarecuperaInscricoesKpopPorConcurso($id);
		}

		$limitePorArquivo = 1950;
		$chunks = array_chunk($inscricoes, $limitePorArquivo);
		$timestamp = date('Y-m-d-His');
		$slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($concurso->nome ?? 'concurso'));

		if (count($chunks) <= 1) {
			$this->response->setHeader('Content-Type', 'text/csv; charset=UTF-8');
			$this->response->setHeader('Content-Disposition', 'attachment; filename="contatos-' . $slug . '-' . $timestamp . '.csv"');
			$this->response->setHeader('Cache-Control', 'no-store, no-cache');
			return $this->response->setBody($this->gerarCsvContatos($chunks[0] ?? []));
		}

		$tempZip = tempnam(sys_get_temp_dir(), 'conc_') . '.zip';
		$zip = new \ZipArchive();
		if ($zip->open($tempZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
			return redirect()->back()->with('atencao', 'Falha ao gerar ZIP.');
		}
		foreach ($chunks as $i => $chunk) {
			$parte = str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);
			$zip->addFromString("contatos-{$slug}-{$timestamp}-parte-{$parte}.csv", $this->gerarCsvContatos($chunk));
		}
		$zip->close();
		$conteudo = file_get_contents($tempZip);
		@unlink($tempZip);
		$this->response->setHeader('Content-Type', 'application/zip');
		$this->response->setHeader('Content-Disposition', 'attachment; filename="contatos-' . $slug . '-' . $timestamp . '.zip"');
		$this->response->setHeader('Cache-Control', 'no-store, no-cache');
		return $this->response->setBody($conteudo);
	}

	private function gerarCsvCompleto(array $inscricoes, bool $isCosplay): string
	{
		$output = fopen('php://temp', 'r+');
		fwrite($output, "\xEF\xBB\xBF");

		if ($isCosplay) {
			fputcsv($output, ['Código', 'Nome', 'Nome Social', 'E-mail', 'Telefone', 'CPF', 'Personagem', 'Obra', 'Gênero', 'Apoio', 'Ref. Visual', 'Vídeo LED', 'Motivação', 'Tempo', 'Observações', 'Status', 'Criado em'], ',');
			foreach ($inscricoes as $i) {
				fputcsv($output, [
					$i->codigo ?? '',
					$i->nome ?? '',
					$i->nome_social ?? '',
					$i->email ?? '',
					$i->telefone ?? '',
					$i->cpf ?? '',
					$i->personagem ?? '',
					$i->obra ?? '',
					$i->genero ?? '',
					$i->apoio ?? '',
					$i->referencia ?? '',
					$i->video_led ?? '',
					$i->motivacao ?? '',
					$i->tempo ?? '',
					$i->observacoes ?? '',
					$i->status ?? '',
					$i->created_at ?? '',
				], ',');
			}
		} else {
			fputcsv($output, ['Código', 'Nome', 'Nome Social', 'E-mail', 'Telefone', 'CPF', 'Música', 'Grupo', 'Categoria', 'Marca', 'Integrantes', 'Ref. Visual', 'Vídeo LED', 'Vídeo Apresentação', 'Motivação', 'Status', 'Criado em'], ',');
			foreach ($inscricoes as $i) {
				fputcsv($output, [
					$i->codigo ?? '',
					$i->nome ?? '',
					$i->nome_social ?? '',
					$i->email ?? '',
					$i->telefone ?? '',
					$i->cpf ?? '',
					$i->musica ?? '',
					$i->grupo ?? '',
					$i->categoria ?? '',
					$i->marca ?? '',
					$i->integrantes ?? '',
					$i->referencia ?? '',
					$i->video_led ?? '',
					$i->video_apresentacao ?? '',
					$i->motivacao ?? '',
					$i->status ?? '',
					$i->created_at ?? '',
				], ',');
			}
		}

		rewind($output);
		$csv = stream_get_contents($output);
		fclose($output);
		return $csv;
	}

	private function gerarCsvContatos(array $inscricoes): string
	{
		$output = fopen('php://temp', 'r+');
		fwrite($output, "\xEF\xBB\xBF");
		fputcsv($output, ['Nome completo', 'Telefone'], ',');
		foreach ($inscricoes as $i) {
			fputcsv($output, [
				$i->nome_social ?: ($i->nome ?? ''),
				$i->telefone ?? '',
			], ',');
		}
		rewind($output);
		$csv = stream_get_contents($output);
		fclose($output);
		return $csv;
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
			
			$link1 = '';
			$link2 = '';
			
			if ($inscricao->status == 'INICIADA' || $inscricao->status == 'CANCELADA' || $inscricao->status == 'EDITADA') {
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
			
			// Buscar quantidade de edições
			$qtdEdicoes = $this->inscricaoHistoricoModel->contaEdicoes($inscricao->id);
			$historicoHtml = $qtdEdicoes > 0 
				? '<a href="' . site_url("concursos/historico_edicoes/{$inscricao->id}") . '" class="badge bg-info">' . $qtdEdicoes . ' edição(ões)</a>'
				: '<span class="badge bg-secondary">Sem edições</span>';
			
			// Formatar status com badge
			$statusBadge = $inscricao->status;
			if ($inscricao->status == 'EDITADA') {
				$statusBadge = '<span class="badge bg-info">EDITADA</span>';
			}
			
			$data[] = [
				'nome_social' => esc($nome),
				'codigo' => esc($inscricao->codigo),
				'created_at' => esc(date('d/m/Y H:i:s', strtotime($inscricao->created_at))),
				'categoria' => esc($inscricao->categoria),
				'status' => $statusBadge,
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
				'historico' => $historicoHtml,
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

			// Verificar se o usuário já tem inscrição neste concurso
			$inscricaoExistente = $this->inscricaoModel->verificaInscricaoDuplicada($user_id, $post['concurso_id']);
			if ($inscricaoExistente) {
				return redirect()->to(site_url("concursos/inscricao_kpop/" . $post['concurso_id']))
					->with('atencao', "Você já possui uma inscrição ativa neste concurso (Código: {$inscricaoExistente->codigo}). Cada participante pode ter apenas uma inscrição por concurso. Para fazer alterações, utilize a opção de edição na área 'Minhas Inscrições'.");
			}

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

			// Verificar se o usuário já tem inscrição neste concurso
			$inscricaoExistente = $this->inscricaoModel->verificaInscricaoDuplicada($user_id, $post['concurso_id']);
			if ($inscricaoExistente) {
				return redirect()->to(site_url("concursos/inscricao_cosplay/" . $post['concurso_id']))
					->with('atencao', "Você já possui uma inscrição ativa neste concurso (Código: {$inscricaoExistente->codigo}). Cada participante pode ter apenas uma inscrição por concurso. Para fazer alterações, utilize a opção de edição na área 'Minhas Inscrições'.");
			}

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

			// Verificar se o usuário já tem inscrição neste concurso
			$inscricaoExistente = $this->inscricaoModel->verificaInscricaoDuplicada($user_id, $post['concurso_id']);
			if ($inscricaoExistente) {
				return redirect()->to(site_url("concursos/inscricao_cosplay_apresentacao/" . $post['concurso_id']))
					->with('atencao', "Você já possui uma inscrição ativa neste concurso (Código: {$inscricaoExistente->codigo}). Cada participante pode ter apenas uma inscrição por concurso. Para fazer alterações, utilize a opção de edição na área 'Minhas Inscrições'.");
			}

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

	private function enviaEmailEnvioCartao(object $cliente, ?int $pedido_id = null): void
	{
		// Buscar order bumps do pedido (se houver)
		$orderBumps = [];
		if ($pedido_id) {
			try {
				$pedidoOrderBumpModel = new \App\Models\PedidoOrderBumpModel();
				$orderBumps = $pedidoOrderBumpModel->getOrderBumpsPorPedido($pedido_id);
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

	/**
	 * Exibe formulário de edição de inscrição K-Pop
	 */
	public function editar_inscricao_kpop($id)
	{
		$usuario_logado = $this->usuarioLogado()->id;

		// Verificar se a inscrição pertence ao usuário
		$inscricao = $this->inscricaoModel->recuperaInscricaoParaEdicao($id, $usuario_logado);

		if (!$inscricao) {
			return redirect()->to(site_url('concursos/my'))->with('erro', 'Inscrição não encontrada ou você não tem permissão para editá-la.');
		}

		// Verificar se pode editar
		$verificacao = $this->inscricaoModel->podeEditar($id);
		if (!$verificacao['pode_editar']) {
			return redirect()->to(site_url('concursos/my'))->with('erro', $verificacao['motivo']);
		}

		$concurso = $this->concursoModel->find($inscricao->concurso_id);

		$data = [
			'titulo' => 'Editar Inscrição - ' . esc($concurso->nome),
			'inscricao' => $inscricao,
			'concurso' => $concurso,
		];

		return view('Concursos/editar_inscricao_kpop', $data);
	}

	/**
	 * Exibe formulário de edição de inscrição Cosplay (Desfile)
	 */
	public function editar_inscricao_cosplay($id)
	{
		$usuario_logado = $this->usuarioLogado()->id;

		$inscricao = $this->inscricaoModel->recuperaInscricaoParaEdicao($id, $usuario_logado);

		if (!$inscricao) {
			return redirect()->to(site_url('concursos/my'))->with('erro', 'Inscrição não encontrada ou você não tem permissão para editá-la.');
		}

		$verificacao = $this->inscricaoModel->podeEditar($id);
		if (!$verificacao['pode_editar']) {
			return redirect()->to(site_url('concursos/my'))->with('erro', $verificacao['motivo']);
		}

		$concurso = $this->concursoModel->find($inscricao->concurso_id);

		$data = [
			'titulo' => 'Editar Inscrição - ' . esc($concurso->nome),
			'inscricao' => $inscricao,
			'concurso' => $concurso,
		];

		return view('Concursos/editar_inscricao_cosplay', $data);
	}

	/**
	 * Exibe formulário de edição de inscrição Cosplay (Apresentação)
	 */
	public function editar_inscricao_cosplay_apresentacao($id)
	{
		$usuario_logado = $this->usuarioLogado()->id;

		$inscricao = $this->inscricaoModel->recuperaInscricaoParaEdicao($id, $usuario_logado);

		if (!$inscricao) {
			return redirect()->to(site_url('concursos/my'))->with('erro', 'Inscrição não encontrada ou você não tem permissão para editá-la.');
		}

		$verificacao = $this->inscricaoModel->podeEditar($id);
		if (!$verificacao['pode_editar']) {
			return redirect()->to(site_url('concursos/my'))->with('erro', $verificacao['motivo']);
		}

		$concurso = $this->concursoModel->find($inscricao->concurso_id);

		$data = [
			'titulo' => 'Editar Inscrição - ' . esc($concurso->nome),
			'inscricao' => $inscricao,
			'concurso' => $concurso,
		];

		return view('Concursos/editar_inscricao_cosplay_apresentacao', $data);
	}

	/**
	 * Processa a edição de inscrição K-Pop
	 */
	public function atualizar_inscricao_kpop()
	{
		$post = $this->request->getPost();
		$usuario_logado = $this->usuarioLogado()->id;
		$inscricao_id = $post['inscricao_id'];

		// Buscar inscrição atual
		$inscricaoAtual = $this->inscricaoModel->recuperaInscricaoParaEdicao($inscricao_id, $usuario_logado);

		if (!$inscricaoAtual) {
			return redirect()->to(site_url('concursos/my'))->with('erro', 'Inscrição não encontrada ou você não tem permissão para editá-la.');
		}

		// Verificar se pode editar
		$verificacao = $this->inscricaoModel->podeEditar($inscricao_id);
		if (!$verificacao['pode_editar']) {
			return redirect()->to(site_url('concursos/my'))->with('erro', $verificacao['motivo']);
		}

		// Preparar dados anteriores para histórico
		$dadosAnteriores = [
			'nome' => $inscricaoAtual->nome,
			'nome_social' => $inscricaoAtual->nome_social,
			'email' => $inscricaoAtual->email,
			'telefone' => $inscricaoAtual->telefone,
			'cpf' => $inscricaoAtual->cpf,
			'video_apresentacao' => $inscricaoAtual->video_apresentacao,
			'grupo' => $inscricaoAtual->grupo,
			'integrantes' => $inscricaoAtual->integrantes,
			'categoria' => $inscricaoAtual->categoria,
			'referencia' => $inscricaoAtual->referencia,
			'musica' => $inscricaoAtual->musica,
			'video_led' => $inscricaoAtual->video_led,
			'status' => $inscricaoAtual->status,
		];

		// Atualizar dados
		$dadosAtualizar = [
			'nome' => $post['nome'],
			'nome_social' => $post['nome_social'],
			'telefone' => $post['telefone'],
			'video_apresentacao' => $post['video_apresentacao'],
			'grupo' => $post['grupo'] ?? null,
			'integrantes' => $post['integrantes'] ?? null,
			'categoria' => $post['categoria'],
			'status' => 'EDITADA',
		];

		// Processar arquivos se enviados
		$imagem = $this->request->getFile('referencia');
		if ($imagem && $imagem->isValid() && !$imagem->hasMoved()) {
			$imagem->store('concursos');
			$dadosAtualizar['referencia'] = $imagem->getName();
		}

		$musica = $this->request->getFile('musica');
		if ($musica && $musica->isValid() && !$musica->hasMoved()) {
			$musica->store('concursos');
			$dadosAtualizar['musica'] = $musica->getName();
		}

		$video = $this->request->getFile('video_led');
		if ($video && $video->isValid() && !$video->hasMoved()) {
			$video->store('concursos');
			$dadosAtualizar['video_led'] = $video->getName();
		}

		// Identificar campos alterados
		$camposAlterados = [];
		foreach ($dadosAtualizar as $campo => $valor) {
			if (isset($dadosAnteriores[$campo]) && $dadosAnteriores[$campo] != $valor) {
				$camposAlterados[] = $campo;
			}
		}

		// Salvar alterações
		if ($this->inscricaoModel->update($inscricao_id, $dadosAtualizar)) {
			// Salvar histórico
			$this->salvarHistoricoEdicao($inscricao_id, $usuario_logado, $dadosAnteriores, $dadosAtualizar, $camposAlterados);

			return redirect()->to(site_url('concursos/my'))->with('sucesso', 'Sua inscrição foi atualizada com sucesso! O status foi alterado para "Inscrição Editada" e será reavaliada pela nossa equipe.');
		}

		return redirect()->back()->with('erro', 'Erro ao atualizar inscrição. Por favor, tente novamente.');
	}

	/**
	 * Processa a edição de inscrição Cosplay (Desfile)
	 */
	public function atualizar_inscricao_cosplay()
	{
		$post = $this->request->getPost();
		$usuario_logado = $this->usuarioLogado()->id;
		$inscricao_id = $post['inscricao_id'];

		$inscricaoAtual = $this->inscricaoModel->recuperaInscricaoParaEdicao($inscricao_id, $usuario_logado);

		if (!$inscricaoAtual) {
			return redirect()->to(site_url('concursos/my'))->with('erro', 'Inscrição não encontrada ou você não tem permissão para editá-la.');
		}

		$verificacao = $this->inscricaoModel->podeEditar($inscricao_id);
		if (!$verificacao['pode_editar']) {
			return redirect()->to(site_url('concursos/my'))->with('erro', $verificacao['motivo']);
		}

		$dadosAnteriores = [
			'nome' => $inscricaoAtual->nome,
			'nome_social' => $inscricaoAtual->nome_social,
			'email' => $inscricaoAtual->email,
			'telefone' => $inscricaoAtual->telefone,
			'cpf' => $inscricaoAtual->cpf,
			'motivacao' => $inscricaoAtual->motivacao,
			'personagem' => $inscricaoAtual->personagem,
			'obra' => $inscricaoAtual->obra,
			'genero' => $inscricaoAtual->genero,
			'observacoes' => $inscricaoAtual->observacoes,
			'referencia' => $inscricaoAtual->referencia,
			'status' => $inscricaoAtual->status,
		];

		$dadosAtualizar = [
			'nome' => $post['nome'],
			'nome_social' => $post['nome_social'],
			'telefone' => $post['telefone'],
			'motivacao' => $post['motivacao'],
			'personagem' => $post['personagem'],
			'obra' => $post['obra'],
			'genero' => $post['genero'],
			'observacoes' => $post['observacoes'],
			'status' => 'EDITADA',
		];

		$imagem = $this->request->getFile('referencia');
		if ($imagem && $imagem->isValid() && !$imagem->hasMoved()) {
			$imagem->store('concursos');
			$dadosAtualizar['referencia'] = $imagem->getName();
		}

		$camposAlterados = [];
		foreach ($dadosAtualizar as $campo => $valor) {
			if (isset($dadosAnteriores[$campo]) && $dadosAnteriores[$campo] != $valor) {
				$camposAlterados[] = $campo;
			}
		}

		if ($this->inscricaoModel->update($inscricao_id, $dadosAtualizar)) {
			$this->salvarHistoricoEdicao($inscricao_id, $usuario_logado, $dadosAnteriores, $dadosAtualizar, $camposAlterados);

			return redirect()->to(site_url('concursos/my'))->with('sucesso', 'Sua inscrição foi atualizada com sucesso! O status foi alterado para "Inscrição Editada" e será reavaliada pela nossa equipe.');
		}

		return redirect()->back()->with('erro', 'Erro ao atualizar inscrição. Por favor, tente novamente.');
	}

	/**
	 * Processa a edição de inscrição Cosplay (Apresentação)
	 */
	public function atualizar_inscricao_cosplay_apresentacao()
	{
		$post = $this->request->getPost();
		$usuario_logado = $this->usuarioLogado()->id;
		$inscricao_id = $post['inscricao_id'];

		$inscricaoAtual = $this->inscricaoModel->recuperaInscricaoParaEdicao($inscricao_id, $usuario_logado);

		if (!$inscricaoAtual) {
			return redirect()->to(site_url('concursos/my'))->with('erro', 'Inscrição não encontrada ou você não tem permissão para editá-la.');
		}

		$verificacao = $this->inscricaoModel->podeEditar($inscricao_id);
		if (!$verificacao['pode_editar']) {
			return redirect()->to(site_url('concursos/my'))->with('erro', $verificacao['motivo']);
		}

		$dadosAnteriores = [
			'nome' => $inscricaoAtual->nome,
			'nome_social' => $inscricaoAtual->nome_social,
			'email' => $inscricaoAtual->email,
			'telefone' => $inscricaoAtual->telefone,
			'cpf' => $inscricaoAtual->cpf,
			'motivacao' => $inscricaoAtual->motivacao,
			'personagem' => $inscricaoAtual->personagem,
			'obra' => $inscricaoAtual->obra,
			'genero' => $inscricaoAtual->genero,
			'observacoes' => $inscricaoAtual->observacoes,
			'referencia' => $inscricaoAtual->referencia,
			'video_led' => $inscricaoAtual->video_led,
			'status' => $inscricaoAtual->status,
		];

		$dadosAtualizar = [
			'nome' => $post['nome'],
			'nome_social' => $post['nome_social'],
			'telefone' => $post['telefone'],
			'motivacao' => $post['motivacao'],
			'personagem' => $post['personagem'],
			'obra' => $post['obra'],
			'genero' => $post['genero'],
			'observacoes' => $post['observacoes'],
			'status' => 'EDITADA',
		];

		$imagem = $this->request->getFile('referencia');
		if ($imagem && $imagem->isValid() && !$imagem->hasMoved()) {
			$imagem->store('concursos');
			$dadosAtualizar['referencia'] = $imagem->getName();
		}

		$video = $this->request->getFile('video_led');
		if ($video && $video->isValid() && !$video->hasMoved()) {
			$video->store('concursos');
			$dadosAtualizar['video_led'] = $video->getName();
		}

		$camposAlterados = [];
		foreach ($dadosAtualizar as $campo => $valor) {
			if (isset($dadosAnteriores[$campo]) && $dadosAnteriores[$campo] != $valor) {
				$camposAlterados[] = $campo;
			}
		}

		if ($this->inscricaoModel->update($inscricao_id, $dadosAtualizar)) {
			$this->salvarHistoricoEdicao($inscricao_id, $usuario_logado, $dadosAnteriores, $dadosAtualizar, $camposAlterados);

			return redirect()->to(site_url('concursos/my'))->with('sucesso', 'Sua inscrição foi atualizada com sucesso! O status foi alterado para "Inscrição Editada" e será reavaliada pela nossa equipe.');
		}

		return redirect()->back()->with('erro', 'Erro ao atualizar inscrição. Por favor, tente novamente.');
	}

	/**
	 * Salva o histórico de edição da inscrição
	 */
	private function salvarHistoricoEdicao(int $inscricao_id, int $user_id, array $dadosAnteriores, array $dadosNovos, array $camposAlterados): void
	{
		$historico = [
			'inscricao_id' => $inscricao_id,
			'user_id' => $user_id,
			'dados_anteriores' => json_encode($dadosAnteriores),
			'dados_novos' => json_encode($dadosNovos),
			'campos_alterados' => implode(', ', $camposAlterados),
			'ip_address' => $this->request->getIPAddress(),
			'user_agent' => $this->request->getUserAgent()->getAgentString(),
		];

		$this->inscricaoHistoricoModel->insert($historico);
	}

	/**
	 * Exibe o histórico de edições de uma inscrição
	 */
	public function historico_edicoes($id)
	{
		if (!$this->usuarioLogado()->temPermissaoPara('juri')) {
			return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
		}

		$inscricao = $this->inscricaoModel->find($id);

		if (!$inscricao) {
			return redirect()->back()->with('erro', 'Inscrição não encontrada.');
		}

		$historico = $this->inscricaoHistoricoModel->recuperaHistoricoPorInscricao($id);

		$data = [
			'titulo' => 'Histórico de Edições',
			'inscricao' => $inscricao,
			'historico' => $historico,
		];

		return view('Concursos/historico_edicoes', $data);
	}
}
