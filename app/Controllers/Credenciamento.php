<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Credenciamento extends BaseController
{
    protected $credenciamentoModel;
    protected $veiculoModel;
    protected $pessoaModel;
    protected $contratoModel;
    protected $checklistModeloModel;
    protected $checklistModeloItemModel;
    protected $checklistModel;
    protected $checklistItemModel;

    public function __construct()
    {
        $this->credenciamentoModel = new \App\Models\CredenciamentoModel();
        $this->veiculoModel = new \App\Models\CredenciamentoVeiculoModel();
        $this->pessoaModel = new \App\Models\CredenciamentoPessoaModel();
        $this->contratoModel = new \App\Models\ContratoModel();
        $this->checklistModeloModel    = new \App\Models\ChecklistModeloModel();
        $this->checklistModeloItemModel = new \App\Models\ChecklistModeloItemModel();
        $this->checklistModel          = new \App\Models\CredenciamentoChecklistModel();
        $this->checklistItemModel      = new \App\Models\CredenciamentoChecklistItemModel();
    }

    /**
     * Listagem de todos os credenciamentos (admin)
     */
    public function listar()
    {
        $eventoModel = new \App\Models\EventoModel();

        $data = [
            'titulo' => 'Credenciamentos',
            'eventos' => $eventoModel->orderBy('id', 'DESC')->findAll(),
        ];

        return view('Credenciamento/listar', $data);
    }

    /**
     * Recupera credenciamentos via AJAX
     */
    public function recuperaCredenciamentos()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $eventoId = $this->request->getGet('evento_id');

        // Query base
        $db = \Config\Database::connect();
        $builder = $db->table('credenciamentos c')
            ->select('c.*, ct.codigo as contrato_codigo, ct.expositor_id, ct.event_id,
                      e.nome as expositor_nome, e.nome_fantasia as expositor_fantasia,
                      ev.nome as evento_nome')
            ->join('contratos ct', 'ct.id = c.contrato_id', 'left')
            ->join('expositores e', 'e.id = ct.expositor_id', 'left')
            ->join('eventos ev', 'ev.id = ct.event_id', 'left')
            ->where('c.deleted_at', null);

        if (!empty($eventoId)) {
            $builder->where('ct.event_id', $eventoId);
        }

        $credenciamentos = $builder->orderBy('c.id', 'DESC')->get()->getResult();

        $data = [];
        foreach ($credenciamentos as $cred) {
            // Conta pessoas
            $totalPessoas = $this->pessoaModel->where('credenciamento_id', $cred->id)->countAllResults();
            $aprovadas = $this->pessoaModel->where('credenciamento_id', $cred->id)->where('status', 'aprovado')->countAllResults();
            $rejeitadas = $this->pessoaModel->where('credenciamento_id', $cred->id)->where('status', 'rejeitado')->countAllResults();
            
            // Conta veículos
            $veiculos = $this->veiculoModel->where('credenciamento_id', $cred->id)->countAllResults();

            // Badge de status
            $statusBadge = match($cred->status) {
                'pendente' => '<span class="badge bg-warning text-dark">Pendente</span>',
                'em_andamento' => '<span class="badge bg-info">Em Andamento</span>',
                'completo' => '<span class="badge bg-primary">Completo</span>',
                'aprovado' => '<span class="badge bg-success">Aprovado</span>',
                'bloqueado' => '<span class="badge bg-danger">Bloqueado</span>',
                default => '<span class="badge bg-secondary">' . ucfirst($cred->status ?? 'pendente') . '</span>',
            };

            // Nome do expositor
            $nomeExpositor = !empty($cred->expositor_fantasia) ? $cred->expositor_fantasia : $cred->expositor_nome;

            // Progresso
            $progresso = $totalPessoas > 0 ? round(($aprovadas / $totalPessoas) * 100) : 0;
            $progressoHtml = '<div class="progress" style="height: 6px;">
                <div class="progress-bar bg-success" style="width: ' . $progresso . '%;"></div>
            </div>
            <small class="text-muted">' . $aprovadas . '/' . $totalPessoas . ' aprovados</small>';

            $data[] = [
                'id' => $cred->id,
                'contrato' => anchor("credenciamento/exibir/{$cred->id}", esc($cred->contrato_codigo ?? '#' . $cred->contrato_id)),
                'expositor' => esc($nomeExpositor ?? 'N/A'),
                'evento' => esc($cred->evento_nome ?? 'N/A'),
                'veiculos' => '<span class="badge bg-secondary"><i class="bx bx-car me-1"></i>' . $veiculos . '</span>',
                'pessoas' => '<span class="badge bg-secondary"><i class="bx bx-group me-1"></i>' . $totalPessoas . '</span>',
                'progresso' => $progressoHtml,
                'status' => $statusBadge,
                'acoes' => '<a href="' . site_url("credenciamento/exibir/{$cred->id}") . '" class="btn btn-sm btn-outline-primary" title="Ver Detalhes"><i class="bx bx-show"></i></a>',
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    /**
     * Página principal do credenciamento
     */
    public function index($contratoId)
    {
        $contrato = $this->contratoModel->find($contratoId);
        
        if (!$contrato) {
            return redirect()->back()->with('error', 'Contrato não encontrado.');
        }

        // Verifica se usuário tem acesso ao contrato
        $usuario = $this->usuarioLogado();
        if ($usuario->is_parceiro) {
            $expositorModel = new \App\Models\ExpositorModel();
            $expositor = $expositorModel->where('usuario_id', $usuario->id)->first();
            
            if (!$expositor || $expositor->id !== $contrato->expositor_id) {
                return redirect()->back()->with('error', 'Você não tem acesso a este contrato.');
            }
        }

        // Verifica se credenciamento está liberado
        $liberado = $this->credenciamentoModel->estaLiberado($contratoId);
        $dentroDoPrazo = $this->credenciamentoModel->dentroDoPrazo($contratoId);
        
        // Obtém ou cria credenciamento
        $credenciamento = $this->credenciamentoModel->getOuCria($contratoId);
        
        // Busca dados
        $veiculos = $this->veiculoModel->buscaPorCredenciamento($credenciamento->id);
        $responsavel = $this->pessoaModel->buscaResponsavel($credenciamento->id);
        $funcionarios = $this->pessoaModel->buscaFuncionarios($credenciamento->id);
        $suplentes = $this->pessoaModel->buscaSuplentes($credenciamento->id);
        
        // Obtém limites
        $limites = $this->credenciamentoModel->getLimitePessoas($contratoId);

        // Busca evento para mostrar prazo
        $eventoModel = new \App\Models\EventoModel();
        $evento = $eventoModel->find($contrato->event_id);

        $data = [
            'titulo' => 'Credenciamento - ' . $contrato->codigo,
            'contrato' => $contrato,
            'credenciamento' => $credenciamento,
            'veiculos' => $veiculos,
            'responsavel' => $responsavel,
            'funcionarios' => $funcionarios,
            'suplentes' => $suplentes,
            'limites' => $limites,
            'liberado' => $liberado,
            'dentroDoPrazo' => $dentroDoPrazo,
            'evento' => $evento,
            'podeEditar' => $liberado && $dentroDoPrazo,
        ];

        return view('Credenciamento/index', $data);
    }

    /**
     * Salvar veículo
     */
    public function salvarVeiculo()
    {
        $credenciamentoId = $this->request->getPost('credenciamento_id');
        $contratoId = $this->request->getPost('contrato_id');
        $veiculoId = $this->request->getPost('id');

        $credenciamento = $this->credenciamentoModel->find($credenciamentoId);
        if (!$credenciamento) {
            return $this->response->setJSON(['success' => false, 'message' => 'Credenciamento não encontrado.']);
        }

        // Verifica permissões
        if (!$this->credenciamentoModel->dentroDoPrazo($contratoId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Prazo de edição expirado.']);
        }

        // Verifica limite se for novo veículo
        if (!$veiculoId && !$this->veiculoModel->podeAdicionar($credenciamentoId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Limite de 1 veículo atingido.']);
        }

        $dados = [
            'credenciamento_id' => $credenciamentoId,
            'marca' => $this->request->getPost('marca'),
            'modelo' => $this->request->getPost('modelo'),
            'cor' => $this->request->getPost('cor'),
            'placa' => strtoupper(str_replace(['-', ' '], '', $this->request->getPost('placa'))),
        ];

        if ($veiculoId) {
            $dados['id'] = $veiculoId;
        }

        if ($this->veiculoModel->save($dados)) {
            $this->credenciamentoModel->atualizaStatus($credenciamentoId);
            return $this->response->setJSON(['success' => true, 'message' => 'Veículo salvo com sucesso!']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Erro ao salvar veículo.', 'errors' => $this->veiculoModel->errors()]);
    }

    /**
     * Excluir veículo
     */
    public function excluirVeiculo($id)
    {
        $veiculo = $this->veiculoModel->find($id);
        if (!$veiculo) {
            return $this->response->setJSON(['success' => false, 'message' => 'Veículo não encontrado.']);
        }

        $credenciamento = $this->credenciamentoModel->find($veiculo->credenciamento_id);
        if (!$this->credenciamentoModel->dentroDoPrazo($credenciamento->contrato_id)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Prazo de edição expirado.']);
        }

        if ($this->veiculoModel->delete($id)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Veículo excluído com sucesso!']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Erro ao excluir veículo.']);
    }

    /**
     * Salvar pessoa (responsável, funcionário ou suplente)
     */
    public function salvarPessoa()
    {
        $credenciamentoId = $this->request->getPost('credenciamento_id');
        $contratoId = $this->request->getPost('contrato_id');
        $pessoaId = $this->request->getPost('id');
        $tipo = $this->request->getPost('tipo');

        $credenciamento = $this->credenciamentoModel->find($credenciamentoId);
        if (!$credenciamento) {
            return $this->response->setJSON(['success' => false, 'message' => 'Credenciamento não encontrado.']);
        }

        // Verifica permissões
        if (!$this->credenciamentoModel->dentroDoPrazo($contratoId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Prazo de edição expirado.']);
        }

        // Obtém limites
        $limites = $this->credenciamentoModel->getLimitePessoas($contratoId);
        $limite = $tipo === 'funcionario' ? $limites['funcionarios'] : $limites['suplentes'];

        // Verifica limite se for nova pessoa
        if (!$pessoaId && !$this->pessoaModel->podeAdicionar($credenciamentoId, $tipo, $limite)) {
            $msg = $tipo === 'responsavel' ? 'Já existe um responsável cadastrado.' : "Limite de {$limite} {$tipo}s atingido.";
            return $this->response->setJSON(['success' => false, 'message' => $msg]);
        }

        $dados = [
            'credenciamento_id' => $credenciamentoId,
            'tipo' => $tipo,
            'nome' => $this->request->getPost('nome'),
            'rg' => $this->request->getPost('rg'),
            'cpf' => preg_replace('/\D/', '', $this->request->getPost('cpf')),
            'whatsapp' => preg_replace('/\D/', '', $this->request->getPost('whatsapp')),
            'status' => 'pendente',
        ];

        // Log para debug
        log_message('info', 'Salvando pessoa: ' . json_encode($dados));

        if ($pessoaId) {
            $dados['id'] = $pessoaId;
        }

        if ($this->pessoaModel->save($dados)) {
            $this->credenciamentoModel->atualizaStatus($credenciamentoId);
            return $this->response->setJSON(['success' => true, 'message' => 'Dados salvos com sucesso!']);
        }

        return $this->response->setJSON([
            'success' => false, 
            'message' => 'Erro ao salvar dados: ' . implode(', ', $this->pessoaModel->errors()),
            'errors' => $this->pessoaModel->errors()
        ]);
    }

    /**
     * Excluir pessoa
     */
    public function excluirPessoa($id)
    {
        $pessoa = $this->pessoaModel->find($id);
        if (!$pessoa) {
            return $this->response->setJSON(['success' => false, 'message' => 'Pessoa não encontrada.']);
        }

        $credenciamento = $this->credenciamentoModel->find($pessoa->credenciamento_id);
        if (!$this->credenciamentoModel->dentroDoPrazo($credenciamento->contrato_id)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Prazo de edição expirado.']);
        }

        if ($this->pessoaModel->delete($id)) {
            $this->credenciamentoModel->atualizaStatus($pessoa->credenciamento_id);
            return $this->response->setJSON(['success' => true, 'message' => 'Pessoa excluída com sucesso!']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Erro ao excluir pessoa.']);
    }

    // =====================================================
    // EDIÇÃO ADMIN (sem checagem de prazo)
    // =====================================================

    /**
     * Salva pessoa pelo admin (cria ou atualiza) sem checar prazo.
     */
    public function salvarPessoaAdmin()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $credenciamentoId = $this->request->getPost('credenciamento_id');
        $pessoaId = $this->request->getPost('id');
        $tipo = $this->request->getPost('tipo');

        $credenciamento = $this->credenciamentoModel->find($credenciamentoId);
        if (!$credenciamento) {
            return $this->response->setJSON(['success' => false, 'message' => 'Credenciamento não encontrado.']);
        }

        $tiposPermitidos = ['responsavel', 'funcionario', 'suplente'];
        if (!in_array($tipo, $tiposPermitidos, true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tipo inválido.']);
        }

        // Bloqueia mais de um responsável
        if (!$pessoaId && $tipo === 'responsavel' && $this->pessoaModel->temResponsavel($credenciamentoId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Já existe um responsável cadastrado.']);
        }

        $dados = [
            'credenciamento_id' => $credenciamentoId,
            'tipo' => $tipo,
            'nome' => $this->request->getPost('nome'),
            'rg' => $this->request->getPost('rg'),
            'cpf' => preg_replace('/\D/', '', (string) $this->request->getPost('cpf')),
            'whatsapp' => preg_replace('/\D/', '', (string) $this->request->getPost('whatsapp')),
        ];

        if ($pessoaId) {
            $dados['id'] = $pessoaId;
        } else {
            $dados['status'] = 'pendente';
        }

        if ($this->pessoaModel->save($dados)) {
            $this->credenciamentoModel->atualizaStatus($credenciamentoId);
            return $this->response->setJSON(['success' => true, 'message' => 'Dados salvos com sucesso!']);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Erro ao salvar dados.',
            'errors' => $this->pessoaModel->errors(),
        ]);
    }

    /**
     * Exclui pessoa pelo admin sem checar prazo.
     */
    public function excluirPessoaAdmin($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $pessoa = $this->pessoaModel->find($id);
        if (!$pessoa) {
            return $this->response->setJSON(['success' => false, 'message' => 'Pessoa não encontrada.']);
        }

        if ($this->pessoaModel->delete($id)) {
            $this->credenciamentoModel->atualizaStatus($pessoa->credenciamento_id);
            return $this->response->setJSON(['success' => true, 'message' => 'Pessoa excluída com sucesso!']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Erro ao excluir pessoa.']);
    }

    // =====================================================
    // VISUALIZAÇÃO ADMIN
    // =====================================================

    /**
     * Exibe detalhes do credenciamento (admin)
     */
    public function exibir($id)
    {
        $credenciamento = $this->credenciamentoModel->find($id);
        
        if (!$credenciamento) {
            return redirect()->to('credenciamento/listar')->with('error', 'Credenciamento não encontrado.');
        }

        $contrato = $this->contratoModel->find($credenciamento->contrato_id);
        
        if (!$contrato) {
            return redirect()->to('credenciamento/listar')->with('error', 'Contrato não encontrado.');
        }

        // Busca dados
        $veiculos = $this->veiculoModel->buscaPorCredenciamento($credenciamento->id);
        $responsavel = $this->pessoaModel->buscaResponsavel($credenciamento->id);
        $funcionarios = $this->pessoaModel->buscaFuncionarios($credenciamento->id);
        $suplentes = $this->pessoaModel->buscaSuplentes($credenciamento->id);
        
        // Busca expositor
        $expositorModel = new \App\Models\ExpositorModel();
        $expositor = $expositorModel->find($contrato->expositor_id);

        // Busca evento
        $eventoModel = new \App\Models\EventoModel();
        $evento = $eventoModel->find($contrato->event_id);

        // Obtém limites
        $limites = $this->credenciamentoModel->getLimitePessoas($credenciamento->contrato_id);

        // Checklists (entrada e saída)
        $checklists = [];
        $checklistItens = [];
        foreach (['entrada', 'saida'] as $tipoCk) {
            $ck = $this->checklistModel->buscaPorCredenciamentoTipo($credenciamento->id, $tipoCk);
            $checklists[$tipoCk] = $ck;
            $checklistItens[$tipoCk] = $ck ? $this->checklistItemModel->porChecklist($ck->id) : [];
        }

        // Modelos disponíveis para iniciar checklist (do evento)
        $modelosDisponiveis = [];
        if ($evento) {
            $modelosDisponiveis = $this->checklistModeloModel
                ->where('event_id', $evento->id)
                ->where('ativo', 1)
                ->findAll();
        }

        $data = [
            'titulo' => 'Detalhes do Credenciamento',
            'credenciamento' => $credenciamento,
            'contrato' => $contrato,
            'expositor' => $expositor,
            'evento' => $evento,
            'veiculos' => $veiculos,
            'responsavel' => $responsavel,
            'funcionarios' => $funcionarios,
            'suplentes' => $suplentes,
            'limites' => $limites,
            'checklists' => $checklists,
            'checklistItens' => $checklistItens,
            'modelosDisponiveis' => $modelosDisponiveis,
        ];

        return view('Credenciamento/exibir', $data);
    }

    // =====================================================
    // CHECKLIST (entrada / saída)
    // =====================================================

    /**
     * Cria um novo checklist para o credenciamento a partir de um modelo.
     */
    public function iniciarChecklist()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno = ['token' => csrf_hash()];

        $credenciamentoId = (int) $this->request->getPost('credenciamento_id');
        $tipo             = $this->request->getPost('tipo') === 'saida' ? 'saida' : 'entrada';
        $modeloId         = (int) $this->request->getPost('modelo_id');

        $credenciamento = $this->credenciamentoModel->find($credenciamentoId);
        if (!$credenciamento) {
            $retorno['erro'] = 'Credenciamento não encontrado.';
            return $this->response->setJSON($retorno);
        }

        // Se já existe checklist desse tipo, bloqueia
        if ($this->checklistModel->buscaPorCredenciamentoTipo($credenciamentoId, $tipo)) {
            $retorno['erro'] = 'Já existe um checklist desse tipo para este credenciamento.';
            return $this->response->setJSON($retorno);
        }

        $modelo = $this->checklistModeloModel->find($modeloId);
        if (!$modelo) {
            $retorno['erro'] = 'Modelo não encontrado.';
            return $this->response->setJSON($retorno);
        }

        $itensModelo = $this->checklistModeloItemModel->porModelo($modeloId);

        $db = \Config\Database::connect();
        $db->transStart();

        $checklistId = $this->checklistModel->insert([
            'credenciamento_id' => $credenciamentoId,
            'modelo_id'         => $modeloId,
            'tipo'              => $tipo,
            'status'            => 'em_andamento',
        ]);

        foreach ($itensModelo as $item) {
            $this->checklistItemModel->insert([
                'checklist_id' => $checklistId,
                'titulo'       => $item->titulo,
                'checked'      => 0,
                'quantidade'   => $item->quantidade,
                'tipo'         => $item->tipo,
                'categoria'    => $item->categoria,
                'ordem'        => $item->ordem,
                'observacao'   => null,
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            $retorno['erro'] = 'Falha ao iniciar checklist.';
            return $this->response->setJSON($retorno);
        }

        $retorno['sucesso'] = 'Checklist iniciado!';
        return $this->response->setJSON($retorno);
    }

    /**
     * Salva o estado dos itens do checklist (checked/quantidade/observacao).
     */
    public function salvarItensChecklist()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno = ['token' => csrf_hash()];

        $checklistId = (int) $this->request->getPost('checklist_id');
        $checklist   = $this->checklistModel->find($checklistId);

        if (!$checklist) {
            $retorno['erro'] = 'Checklist não encontrado.';
            return $this->response->setJSON($retorno);
        }

        if ($checklist->status === 'concluido') {
            $retorno['erro'] = 'Checklist já concluído. Reabra para editar.';
            return $this->response->setJSON($retorno);
        }

        $itens = $this->request->getPost('itens') ?? [];

        foreach ($itens as $itemId => $dados) {
            $item = $this->checklistItemModel->find((int) $itemId);
            if (!$item || (int) $item->checklist_id !== $checklistId) {
                continue;
            }
            $this->checklistItemModel->update($item->id, [
                'checked'    => !empty($dados['checked']) ? 1 : 0,
                'quantidade' => max(0, (int) ($dados['quantidade'] ?? $item->quantidade)),
                'observacao' => trim((string) ($dados['observacao'] ?? '')) ?: null,
            ]);
        }

        $retorno['sucesso'] = 'Itens salvos.';
        return $this->response->setJSON($retorno);
    }

    /**
     * Conclui o checklist (admin/operador).
     */
    public function concluirChecklist()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno = ['token' => csrf_hash()];

        $checklistId = (int) $this->request->getPost('checklist_id');
        $checklist   = $this->checklistModel->find($checklistId);

        if (!$checklist) {
            $retorno['erro'] = 'Checklist não encontrado.';
            return $this->response->setJSON($retorno);
        }

        if ($checklist->status === 'concluido') {
            $retorno['erro'] = 'Checklist já está concluído.';
            return $this->response->setJSON($retorno);
        }

        $usuario = usuario_logado();

        $this->checklistModel->update($checklistId, [
            'status'        => 'concluido',
            'conferido_por' => $usuario->id ?? null,
            'conferido_em'  => date('Y-m-d H:i:s'),
            'observacoes'   => trim((string) $this->request->getPost('observacoes')) ?: null,
        ]);

        // Notifica expositor (com cópia para relacionamento)
        try {
            $this->notificarChecklistConcluido($checklistId);
        } catch (\Throwable $e) {
            log_message('error', 'Falha ao notificar checklist concluído #' . $checklistId . ': ' . $e->getMessage());
        }

        $retorno['sucesso'] = 'Checklist concluído!';
        return $this->response->setJSON($retorno);
    }

    /**
     * Envia email ao expositor (com cópia para relacionamento) quando um checklist é concluído.
     */
    private function notificarChecklistConcluido(int $checklistId): void
    {
        $checklist = $this->checklistModel->find($checklistId);
        if (!$checklist) {
            return;
        }

        $credenciamento = $this->credenciamentoModel->find($checklist->credenciamento_id);
        if (!$credenciamento) {
            return;
        }

        $contrato = $this->contratoModel->find($credenciamento->contrato_id);
        if (!$contrato) {
            return;
        }

        $expositorModel = new \App\Models\ExpositorModel();
        $expositor = $expositorModel->find($contrato->expositor_id);

        $eventoModel = new \App\Models\EventoModel();
        $evento = $eventoModel->find($contrato->event_id);

        $itens = $this->checklistItemModel->porChecklist($checklistId);

        $conferidoPorNome = null;
        if (!empty($checklist->conferido_por)) {
            $usuarioModel = new \App\Models\UsuarioModel();
            $conferidoPor = $usuarioModel->find($checklist->conferido_por);
            $conferidoPorNome = $conferidoPor->nome ?? null;
        }

        $mensagem = view('Credenciamento/email_checklist_concluido', [
            'checklist'        => $checklist,
            'credenciamento'   => $credenciamento,
            'contrato'         => $contrato,
            'expositor'        => $expositor,
            'evento'           => $evento,
            'itens'            => $itens,
            'conferidoPorNome' => $conferidoPorNome,
        ]);

        $destinatarios = array_values(array_filter([
            $expositor->email ?? null,
            'relacionamento@mundodream.com.br',
        ]));

        if (empty($destinatarios)) {
            return;
        }

        $tipoLabel = $checklist->getTipoLabel();
        $codigo    = $contrato->codigo ?? ('#' . $contrato->id);
        $assunto   = '✅ Checklist de ' . $tipoLabel . ' concluído - ' . $codigo;

        $resend = new \App\Services\ResendService();
        $resend->enviarEmailMultiplos($destinatarios, $assunto, $mensagem);
    }

    /**
     * Reabre um checklist concluído (somente admin).
     */
    public function reabrirChecklist()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno = ['token' => csrf_hash()];

        $usuario = usuario_logado();
        if (empty($usuario->is_admin)) {
            $retorno['erro'] = 'Somente admin pode reabrir o checklist.';
            return $this->response->setJSON($retorno);
        }

        $checklistId = (int) $this->request->getPost('checklist_id');
        $checklist   = $this->checklistModel->find($checklistId);

        if (!$checklist) {
            $retorno['erro'] = 'Checklist não encontrado.';
            return $this->response->setJSON($retorno);
        }

        $this->checklistModel->update($checklistId, [
            'status'        => 'reaberto',
            'conferido_por' => null,
            'conferido_em'  => null,
        ]);

        $retorno['sucesso'] = 'Checklist reaberto.';
        return $this->response->setJSON($retorno);
    }

    // =====================================================
    // MÉTODOS DE APROVAÇÃO (ADMIN)
    // =====================================================

    /**
     * Aprovar todo o credenciamento
     */
    public function aprovarTudo()
    {
        $credenciamentoId = $this->request->getPost('credenciamento_id');
        
        $credenciamento = $this->credenciamentoModel->find($credenciamentoId);
        if (!$credenciamento) {
            return redirect()->back()->with('error', 'Credenciamento não encontrado.');
        }

        // Aprova todas as pessoas
        $this->pessoaModel->aprovarTodas($credenciamentoId);
        
        // Atualiza status do credenciamento
        $this->credenciamentoModel->update($credenciamentoId, ['status' => 'aprovado']);
        
        // Atualiza situação do contrato
        $contrato = $this->contratoModel->find($credenciamento->contrato_id);
        if ($contrato && $contrato->situacao === 'aguardando_credenciamento') {
            $this->contratoModel->update($contrato->id, ['situacao' => 'finalizado']);
        }

        // Redireciona para a listagem com o filtro de evento
        $eventoId = $contrato ? $contrato->event_id : '';
        return redirect()->to("credenciamento/listar?evento_id={$eventoId}")->with('success', 'Credenciamento aprovado com sucesso!');
    }

    /**
     * Aprovar pessoa individual
     */
    public function aprovarPessoa($id)
    {
        $pessoa = $this->pessoaModel->find($id);
        if (!$pessoa) {
            return $this->response->setJSON(['success' => false, 'message' => 'Pessoa não encontrada.']);
        }

        if ($this->pessoaModel->aprovar($id)) {
            // Verifica se todas foram aprovadas
            if ($this->pessoaModel->todasAprovadas($pessoa->credenciamento_id)) {
                $this->credenciamentoModel->update($pessoa->credenciamento_id, ['status' => 'aprovado']);
                
                // Atualiza situação do contrato
                $credenciamento = $this->credenciamentoModel->find($pessoa->credenciamento_id);
                $contrato = $this->contratoModel->find($credenciamento->contrato_id);
                if ($contrato && $contrato->situacao === 'aguardando_credenciamento') {
                    $this->contratoModel->update($contrato->id, ['situacao' => 'finalizado']);
                }
            }
            
            return $this->response->setJSON(['success' => true, 'message' => 'Pessoa aprovada!']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Erro ao aprovar.']);
    }

    /**
     * Rejeitar pessoa individual
     */
    public function rejeitarPessoa($id)
    {
        $pessoa = $this->pessoaModel->find($id);
        if (!$pessoa) {
            return $this->response->setJSON(['success' => false, 'message' => 'Pessoa não encontrada.']);
        }

        $motivo = $this->request->getPost('motivo') ?? null;

        if ($this->pessoaModel->rejeitar($id, $motivo)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Pessoa rejeitada.']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Erro ao rejeitar.']);
    }

    /**
     * Devolver credenciamento para o expositor preencher novamente
     */
    public function devolver()
    {
        $credenciamentoId = $this->request->getPost('credenciamento_id');
        $observacao = $this->request->getPost('observacao') ?? null;
        
        $credenciamento = $this->credenciamentoModel->find($credenciamentoId);
        if (!$credenciamento) {
            return redirect()->back()->with('error', 'Credenciamento não encontrado.');
        }

        // Reseta status de todas as pessoas
        $this->pessoaModel->resetarStatus($credenciamentoId);
        
        // Atualiza status do credenciamento
        $this->credenciamentoModel->update($credenciamentoId, [
            'status' => 'pendente',
            'observacoes' => $observacao,
        ]);

        return redirect()->to("credenciamento/exibir/{$credenciamentoId}")->with('success', 'Credenciamento devolvido para correção.');
    }
}
