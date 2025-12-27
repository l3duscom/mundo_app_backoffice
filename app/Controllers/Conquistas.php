<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ConquistaModel;
use App\Models\UsuarioConquistaModel;
use App\Models\ExtratoPontosModel;
use App\Models\EventoModel;
use App\Models\UsuarioModel;

class Conquistas extends BaseController
{
    private $conquistaModel;
    private $usuarioConquistaModel;
    private $extratoPontosModel;
    private $eventoModel;
    private $usuarioModel;

    public function __construct()
    {
        $this->conquistaModel = new ConquistaModel();
        $this->usuarioConquistaModel = new UsuarioConquistaModel();
        $this->extratoPontosModel = new ExtratoPontosModel();
        $this->eventoModel = new EventoModel();
        $this->usuarioModel = new UsuarioModel();
    }

    /**
     * Lista conquistas
     */
    public function index()
    {
        if (!$this->usuarioLogado()->is_admin) {
            return redirect()->back()->with('atencao', 'Você não tem permissão para acessar esse menu.');
        }

        $eventoId = $this->request->getGet('event_id') ?? evento_selecionado();

        $data = [
            'titulo' => 'Gerenciamento de Conquistas',
            'eventos' => $this->eventoModel->orderBy('id', 'DESC')->findAll(),
            'evento_id' => $eventoId,
        ];

        return view('Conquistas/index', $data);
    }

    /**
     * Formulário de criação
     */
    public function criar()
    {
        if (!$this->usuarioLogado()->is_admin) {
            return redirect()->back()->with('atencao', 'Você não tem permissão para acessar esse menu.');
        }

        $eventoId = $this->request->getGet('event_id') ?? evento_selecionado();

        $data = [
            'titulo' => 'Nova Conquista',
            'eventos' => $this->eventoModel->orderBy('id', 'DESC')->findAll(),
            'evento_id' => $eventoId,
        ];

        return view('Conquistas/criar', $data);
    }

    /**
     * Cadastrar conquista (AJAX)
     */
    public function cadastrar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $post = $this->request->getPost();

        // Validar campos obrigatórios
        if (empty($post['event_id'])) {
            $retorno['erro'] = 'Selecione um evento.';
            return $this->response->setJSON($retorno);
        }

        if (empty($post['nome_conquista'])) {
            $retorno['erro'] = 'Nome da conquista é obrigatório.';
            return $this->response->setJSON($retorno);
        }

        $dados = [
            'event_id' => $post['event_id'],
            'nome_conquista' => $post['nome_conquista'],
            'descricao' => $post['descricao'] ?? null,
            'pontos' => (int) ($post['pontos'] ?? 0),
            'nivel' => $post['nivel'] ?? 'BRONZE',
            'status' => $post['status'] ?? 'ATIVA',
        ];

        if (!$this->conquistaModel->insert($dados)) {
            $retorno['erro'] = 'Erro ao cadastrar conquista.';
            $retorno['erros_model'] = $this->conquistaModel->errors();
            return $this->response->setJSON($retorno);
        }

        session()->setFlashdata('sucesso', 'Conquista cadastrada com sucesso!');
        $retorno['sucesso'] = 'Conquista cadastrada com sucesso!';
        $retorno['redirect'] = site_url('conquistas-admin');

        return $this->response->setJSON($retorno);
    }

    /**
     * Formulário de edição
     */
    public function editar($id = null)
    {
        if (!$this->usuarioLogado()->is_admin) {
            return redirect()->back()->with('atencao', 'Você não tem permissão para acessar esse menu.');
        }

        $conquista = $this->buscaConquistaOu404($id);

        $data = [
            'titulo' => 'Editar Conquista: ' . $conquista->nome_conquista,
            'conquista' => $conquista,
            'eventos' => $this->eventoModel->orderBy('id', 'DESC')->findAll(),
        ];

        return view('Conquistas/editar', $data);
    }

    /**
     * Atualizar conquista (AJAX)
     */
    public function atualizar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $post = $this->request->getPost();

        $conquista = $this->buscaConquistaOu404($post['id']);

        $dados = [
            'event_id' => $post['event_id'],
            'nome_conquista' => $post['nome_conquista'],
            'descricao' => $post['descricao'] ?? null,
            'pontos' => (int) ($post['pontos'] ?? 0),
            'nivel' => $post['nivel'] ?? 'BRONZE',
            'status' => $post['status'] ?? 'ATIVA',
        ];

        if (!$this->conquistaModel->update($conquista->id, $dados)) {
            $retorno['erro'] = 'Erro ao atualizar conquista.';
            $retorno['erros_model'] = $this->conquistaModel->errors();
            return $this->response->setJSON($retorno);
        }

        session()->setFlashdata('sucesso', 'Conquista atualizada com sucesso!');
        $retorno['sucesso'] = 'Conquista atualizada com sucesso!';
        $retorno['redirect'] = site_url('conquistas-admin');

        return $this->response->setJSON($retorno);
    }

    /**
     * Excluir conquista
     */
    public function excluir($id = null)
    {
        if (!$this->usuarioLogado()->is_admin) {
            return redirect()->back()->with('atencao', 'Você não tem permissão para acessar esse menu.');
        }

        $conquista = $this->buscaConquistaOu404($id);

        // Verificar se há usuários com esta conquista
        $qtdUsuarios = $this->usuarioConquistaModel
            ->where('conquista_id', $id)
            ->where('status', 'ATIVA')
            ->countAllResults();

        if ($qtdUsuarios > 0) {
            return redirect()->back()->with('atencao', "Esta conquista não pode ser excluída pois está atribuída a {$qtdUsuarios} usuário(s).");
        }

        if ($this->conquistaModel->delete($id)) {
            return redirect()->to(site_url('conquistas-admin'))->with('sucesso', 'Conquista excluída com sucesso!');
        }

        return redirect()->back()->with('erro', 'Erro ao excluir conquista.');
    }

    /**
     * Duplicar conquista individual (AJAX)
     */
    public function duplicar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $id = $this->request->getPost('id');
        $eventoDestinoId = $this->request->getPost('event_id');

        $conquista = $this->conquistaModel->find($id);

        if (!$conquista) {
            $retorno['erro'] = 'Conquista não encontrada.';
            return $this->response->setJSON($retorno);
        }

        // Criar cópia
        $dados = [
            'event_id' => $eventoDestinoId ?: $conquista->event_id,
            'nome_conquista' => $conquista->nome_conquista . ' (Cópia)',
            'descricao' => $conquista->descricao,
            'pontos' => $conquista->pontos,
            'nivel' => $conquista->nivel,
            'status' => 'ATIVA',
        ];

        if ($this->conquistaModel->insert($dados)) {
            $retorno['sucesso'] = 'Conquista duplicada com sucesso!';
        } else {
            $retorno['erro'] = 'Erro ao duplicar conquista.';
        }

        return $this->response->setJSON($retorno);
    }

    /**
     * Duplicar conquistas em massa (AJAX)
     */
    public function duplicarEmMassa()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $ids = $this->request->getPost('ids');
        $eventoDestinoId = $this->request->getPost('event_id');

        if (empty($ids) || !is_array($ids)) {
            $retorno['erro'] = 'Selecione pelo menos uma conquista.';
            return $this->response->setJSON($retorno);
        }

        if (empty($eventoDestinoId)) {
            $retorno['erro'] = 'Selecione o evento de destino.';
            return $this->response->setJSON($retorno);
        }

        $count = 0;
        foreach ($ids as $id) {
            $conquista = $this->conquistaModel->find($id);
            if (!$conquista) continue;

            $dados = [
                'event_id' => $eventoDestinoId,
                'nome_conquista' => $conquista->nome_conquista,
                'descricao' => $conquista->descricao,
                'pontos' => $conquista->pontos,
                'nivel' => $conquista->nivel,
                'status' => 'ATIVA',
            ];

            if ($this->conquistaModel->insert($dados)) {
                $count++;
            }
        }

        if ($count > 0) {
            $retorno['sucesso'] = "{$count} conquista(s) duplicada(s) com sucesso!";
        } else {
            $retorno['erro'] = 'Nenhuma conquista foi duplicada.';
        }

        return $this->response->setJSON($retorno);
    }

    /**
     * Recupera conquistas para DataTables (AJAX)
     */
    public function recuperaConquistas()
    {
        $eventId = $this->request->getGet('event_id') ?? evento_selecionado();

        $builder = $this->conquistaModel
            ->select('conquistas.*, eventos.nome as evento_nome')
            ->join('eventos', 'eventos.id = conquistas.event_id', 'left');

        if (!empty($eventId)) {
            $builder->where('conquistas.event_id', $eventId);
        }

        $conquistas = $builder->orderBy('conquistas.created_at', 'DESC')->findAll();

        $data = [];

        foreach ($conquistas as $conquista) {
            // Contar usuários com esta conquista
            $qtdUsuarios = $this->usuarioConquistaModel
                ->where('conquista_id', $conquista->id)
                ->where('status', 'ATIVA')
                ->countAllResults();

            $data[] = [
                'id' => $conquista->id,
                'codigo' => '<code>' . esc($conquista->codigo) . '</code>',
                'nome' => '<strong>' . esc($conquista->nome_conquista) . '</strong>',
                'descricao' => esc(mb_substr($conquista->descricao ?? '', 0, 50)) . (strlen($conquista->descricao ?? '') > 50 ? '...' : ''),
                'pontos' => '<span class="badge bg-primary">' . number_format($conquista->pontos, 0, ',', '.') . ' pts</span>',
                'nivel' => $this->getBadgeNivel($conquista->nivel),
                'usuarios' => '<a href="' . site_url("conquistas-admin/top-usuarios/{$conquista->id}") . '" class="badge bg-info text-decoration-none">' . $qtdUsuarios . ' usuários</a>',
                'status' => $this->getBadgeStatus($conquista->status),
                'acoes' => $this->montaBotoes($conquista),
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    /**
     * Buscar conquistas para dropdown (AJAX)
     */
    public function buscarConquistas()
    {
        $conquistas = $this->conquistaModel
            ->where('status', 'ATIVA')
            ->orderBy('nome_conquista', 'ASC')
            ->findAll();
        
        $data = [];
        foreach ($conquistas as $conquista) {
            $data[] = [
                'id' => $conquista->id,
                'nome_conquista' => $conquista->nome_conquista,
                'pontos' => $conquista->pontos,
            ];
        }
        
        return $this->response->setJSON($data);
    }

    /**
     * Ranking das conquistas mais usadas
     */
    public function rankingConquistas()
    {
        if (!$this->usuarioLogado()->is_admin) {
            return redirect()->back()->with('atencao', 'Você não tem permissão para acessar esse menu.');
        }

        $eventoId = $this->request->getGet('event_id') ?? evento_selecionado();

        $data = [
            'titulo' => 'Ranking de Conquistas',
            'eventos' => $this->eventoModel->orderBy('id', 'DESC')->findAll(),
            'evento_id' => $eventoId,
        ];

        return view('Conquistas/ranking', $data);
    }

    /**
     * Dados do ranking para gráficos (AJAX)
     */
    public function dadosRanking()
    {
        $eventId = $this->request->getGet('event_id') ?? evento_selecionado();

        // Top conquistas mais atribuídas
        $builder = $this->usuarioConquistaModel
            ->select('conquista_id, conquistas.nome_conquista, conquistas.nivel, conquistas.pontos as pontos_conquista, COUNT(*) as total, SUM(usuario_conquistas.pontos) as total_pontos')
            ->join('conquistas', 'conquistas.id = usuario_conquistas.conquista_id')
            ->where('usuario_conquistas.status', 'ATIVA')
            ->groupBy('conquista_id')
            ->orderBy('total', 'DESC')
            ->limit(10);

        if (!empty($eventId)) {
            $builder->where('usuario_conquistas.event_id', $eventId);
        }

        $topConquistas = $builder->findAll();

        // Distribuição por nível
        $builderNivel = $this->usuarioConquistaModel
            ->select('conquistas.nivel, COUNT(*) as total')
            ->join('conquistas', 'conquistas.id = usuario_conquistas.conquista_id')
            ->where('usuario_conquistas.status', 'ATIVA')
            ->groupBy('conquistas.nivel')
            ->orderBy('total', 'DESC');

        if (!empty($eventId)) {
            $builderNivel->where('usuario_conquistas.event_id', $eventId);
        }

        $porNivel = $builderNivel->findAll();

        // Totalizadores
        $builderTotal = $this->usuarioConquistaModel
            ->where('status', 'ATIVA');
        
        if (!empty($eventId)) {
            $builderTotal->where('event_id', $eventId);
        }
        
        $totalAtribuicoes = $builderTotal->countAllResults();

        $builderConquistas = $this->conquistaModel
            ->where('status', 'ATIVA');
        
        if (!empty($eventId)) {
            $builderConquistas->where('event_id', $eventId);
        }
        
        $totalConquistas = $builderConquistas->countAllResults();

        // Total de pontos distribuídos
        $db = \Config\Database::connect();
        $builderPontos = $db->table('usuario_conquistas')
            ->selectSum('pontos')
            ->where('status', 'ATIVA')
            ->where('deleted_at IS NULL');
        
        if (!empty($eventId)) {
            $builderPontos->where('event_id', $eventId);
        }
        
        $resultPontos = $builderPontos->get()->getRowArray();
        $totalPontos = (int) ($resultPontos['pontos'] ?? 0);

        return $this->response->setJSON([
            'topConquistas' => $topConquistas,
            'porNivel' => $porNivel,
            'totalAtribuicoes' => $totalAtribuicoes,
            'totalConquistas' => $totalConquistas,
            'totalPontos' => $totalPontos,
        ]);
    }

    /**
     * Top usuários de uma conquista
     */
    public function topUsuarios($conquistaId = null)
    {
        if (!$this->usuarioLogado()->is_admin) {
            return redirect()->back()->with('atencao', 'Você não tem permissão para acessar esse menu.');
        }

        $conquista = $this->buscaConquistaOu404($conquistaId);

        // Buscar usuários com esta conquista
        $usuarios = $this->usuarioConquistaModel
            ->select('usuario_conquistas.*, usuarios.nome, usuarios.email')
            ->join('usuarios', 'usuarios.id = usuario_conquistas.user_id')
            ->where('usuario_conquistas.conquista_id', $conquistaId)
            ->where('usuario_conquistas.status', 'ATIVA')
            ->orderBy('usuario_conquistas.created_at', 'ASC')
            ->findAll();

        $data = [
            'titulo' => 'Usuários: ' . $conquista->nome_conquista,
            'conquista' => $conquista,
            'usuarios' => $usuarios,
        ];

        return view('Conquistas/top_usuarios', $data);
    }

    /**
     * Listagem do extrato de pontos
     */
    public function extratoPontos()
    {
        if (!$this->usuarioLogado()->is_admin) {
            return redirect()->back()->with('atencao', 'Você não tem permissão para acessar esse menu.');
        }

        $eventoId = $this->request->getGet('event_id') ?? evento_selecionado();

        $data = [
            'titulo' => 'Extrato de Pontos',
            'eventos' => $this->eventoModel->orderBy('id', 'DESC')->findAll(),
            'evento_id' => $eventoId,
        ];

        return view('Conquistas/extrato', $data);
    }

    /**
     * Recupera extrato para DataTables (AJAX)
     */
    public function recuperaExtrato()
    {
        $eventId = $this->request->getGet('event_id') ?? evento_selecionado();
        $tipo = $this->request->getGet('tipo');
        $userId = $this->request->getGet('user_id');

        $builder = $this->extratoPontosModel
            ->select('extrato_pontos.*, usuarios.nome as usuario_nome, usuarios.email as usuario_email')
            ->join('usuarios', 'usuarios.id = extrato_pontos.user_id');

        if (!empty($eventId)) {
            $builder->where('extrato_pontos.event_id', $eventId);
        }

        if (!empty($tipo)) {
            $builder->where('extrato_pontos.tipo', $tipo);
        }

        if (!empty($userId)) {
            $builder->where('extrato_pontos.user_id', $userId);
        }

        $extrato = $builder->orderBy('extrato_pontos.id', 'DESC')
            ->limit(500)
            ->findAll();

        $data = [];

        foreach ($extrato as $item) {
            $pontosClass = $item->pontos >= 0 ? 'text-success' : 'text-danger';
            $pontosPrefix = $item->pontos >= 0 ? '+' : '';

            $data[] = [
                'id' => $item->id,
                'data' => date('d/m/Y H:i', strtotime($item->created_at)),
                'usuario' => '<strong>' . esc($item->usuario_nome) . '</strong><br><small class="text-muted">' . esc($item->usuario_email) . '</small>',
                'tipo' => $this->getBadgeTipo($item->tipo),
                'pontos' => '<span class="' . $pontosClass . ' fw-bold">' . $pontosPrefix . number_format($item->pontos, 0, ',', '.') . '</span>',
                'saldo' => number_format($item->saldo_atual ?? 0, 0, ',', '.') . ' pts',
                'descricao' => esc(mb_substr($item->descricao ?? '', 0, 60)) . (strlen($item->descricao ?? '') > 60 ? '...' : ''),
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    /**
     * Busca conquista ou retorna 404
     */
    private function buscaConquistaOu404($id = null)
    {
        if (!$id || !$conquista = $this->conquistaModel->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Conquista não encontrada: ' . $id);
        }

        return $conquista;
    }

    /**
     * Monta botões de ação
     */
    private function montaBotoes($conquista): string
    {
        $btns = '<div class="d-flex gap-1">';

        // Botão editar
        $btns .= '<a href="' . site_url("conquistas-admin/editar/{$conquista->id}") . '" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bx bx-edit"></i></a>';

        // Botão duplicar
        $btns .= '<button type="button" class="btn btn-sm btn-outline-success" onclick="duplicarConquista(' . $conquista->id . ')" title="Duplicar"><i class="bx bx-copy"></i></button>';

        // Botão ver usuários
        $btns .= '<a href="' . site_url("conquistas-admin/top-usuarios/{$conquista->id}") . '" class="btn btn-sm btn-outline-info" title="Ver Usuários"><i class="bx bx-user"></i></a>';

        // Botão excluir
        $btns .= '<a href="' . site_url("conquistas-admin/excluir/{$conquista->id}") . '" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="return confirm(\'Tem certeza que deseja excluir esta conquista?\')"><i class="bx bx-trash"></i></a>';

        $btns .= '</div>';

        return $btns;
    }

    /**
     * Badge de nível
     */
    private function getBadgeNivel(string $nivel): string
    {
        $cores = [
            'BRONZE' => 'background: linear-gradient(135deg, #CD7F32, #8B4513); color: white;',
            'PRATA' => 'background: linear-gradient(135deg, #C0C0C0, #808080); color: white;',
            'OURO' => 'background: linear-gradient(135deg, #FFD700, #DAA520); color: #333;',
            'PLATINA' => 'background: linear-gradient(135deg, #E5E4E2, #BCC6CC); color: #333;',
            'DIAMANTE' => 'background: linear-gradient(135deg, #B9F2FF, #7DF9FF); color: #333;',
        ];

        $estilo = $cores[$nivel] ?? 'background: #6c757d; color: white;';

        return '<span class="badge" style="' . $estilo . '">' . esc($nivel) . '</span>';
    }

    /**
     * Badge de status
     */
    private function getBadgeStatus(string $status): string
    {
        $badges = [
            'ATIVA' => '<span class="badge bg-success">Ativa</span>',
            'INATIVA' => '<span class="badge bg-secondary">Inativa</span>',
            'BLOQUEADA' => '<span class="badge bg-danger">Bloqueada</span>',
        ];

        return $badges[$status] ?? '<span class="badge bg-secondary">' . esc($status) . '</span>';
    }

    /**
     * Badge de tipo de extrato
     */
    private function getBadgeTipo(string $tipo): string
    {
        $badges = [
            'CONQUISTA' => '<span class="badge bg-success">Conquista</span>',
            'BONUS' => '<span class="badge bg-info">Bônus</span>',
            'COMPRA' => '<span class="badge bg-primary">Compra</span>',
            'REVOGACAO' => '<span class="badge bg-danger">Revogação</span>',
            'RESGATE' => '<span class="badge bg-warning text-dark">Resgate</span>',
            'EXPIRACAO' => '<span class="badge bg-secondary">Expiração</span>',
        ];

        return $badges[$tipo] ?? '<span class="badge bg-secondary">' . esc($tipo) . '</span>';
    }

    /**
     * Atribuir conquista a um usuário (via AJAX)
     */
    public function atribuirConquista()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $userId = $this->request->getPost('user_id');
        $conquistaId = $this->request->getPost('conquista_id');

        if (!$userId || !$conquistaId) {
            $retorno['erro'] = 'Dados inválidos.';
            return $this->response->setJSON($retorno);
        }

        // Buscar conquista para obter o event_id
        $conquista = $this->conquistaModel->find($conquistaId);

        if (!$conquista) {
            $retorno['erro'] = 'Conquista não encontrada.';
            return $this->response->setJSON($retorno);
        }

        // Usar o ConquistaService para atribuir
        $conquistaService = new \App\Services\ConquistaService();
        $result = $conquistaService->atribuirConquista(
            (int) $userId,
            (int) $conquistaId,
            (int) $conquista->event_id,
            true, // isAdmin
            $this->usuarioLogado()->id // atribuidoPor
        );

        if ($result['success']) {
            $retorno['sucesso'] = $result['message'] . ' (' . $result['data']['pontos'] . ' pontos)';
        } else {
            $retorno['erro'] = $result['message'];
        }

        return $this->response->setJSON($retorno);
    }
}
