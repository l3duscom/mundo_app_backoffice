<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EspacoModel;
use App\Models\ContratoItemModel;

class Espacos extends BaseController
{
    protected $espacoModel;

    public function __construct()
    {
        $this->espacoModel = new EspacoModel();
    }

    /**
     * Lista espaços
     */
    public function index()
    {
        if (!$this->usuarioLogado()->temPermissaoPara('listar-contratos')) {
            return redirect()->back()->with('atencao', 'Sem permissão para acessar este recurso.');
        }

        $eventId = $this->request->getGet('event_id') ?? session()->get('evento_selecionado');
        
        $eventoModel = new \App\Models\EventoModel();
        $eventos = $eventoModel->orderBy('data_inicio', 'DESC')->findAll();

        $espacos = [];
        $contagem = ['livre' => 0, 'reservado' => 0, 'bloqueado' => 0];
        
        if ($eventId) {
            $espacos = $this->espacoModel->buscaPorEvento($eventId);
            $contagem = $this->espacoModel->contaPorStatus($eventId);
        }

        // Tipos de item disponíveis
        $tiposItem = ContratoItemModel::getTiposItem();

        $data = [
            'titulo' => 'Espaços Disponíveis',
            'espacos' => $espacos,
            'eventos' => $eventos,
            'eventIdSelecionado' => $eventId,
            'tiposItem' => $tiposItem,
            'contagem' => $contagem,
        ];

        return view('Espacos/index', $data);
    }

    /**
     * Salvar espaço (AJAX)
     */
    public function salvar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $post = $this->request->getPost();

        $espaco = new \App\Entities\Espaco();
        $espaco->fill($post);

        // Se é edição, busca o espaço existente
        if (!empty($post['id'])) {
            $espacoExistente = $this->espacoModel->find($post['id']);
            if (!$espacoExistente) {
                $retorno['erro'] = 'Espaço não encontrado';
                return $this->response->setJSON($retorno);
            }
            $espaco->id = $post['id'];
        }

        if ($this->espacoModel->save($espaco)) {
            $retorno['sucesso'] = 'Espaço salvo com sucesso!';
            $retorno['id'] = $espaco->id ?? $this->espacoModel->getInsertID();
            return $this->response->setJSON($retorno);
        }

        $retorno['erro'] = 'Erro ao salvar espaço';
        $retorno['erros_model'] = $this->espacoModel->errors();
        return $this->response->setJSON($retorno);
    }

    /**
     * Salvar múltiplos espaços em lote (AJAX)
     * Um espaço pode servir para múltiplos tipos de item
     */
    public function salvarLote()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $post = $this->request->getPost();
        $eventId = $post['event_id'] ?? null;
        $tiposItem = $post['tipos_item'] ?? [];
        $prefixo = $post['prefixo'] ?? '';
        $inicio = (int)($post['inicio'] ?? 1);
        $fim = (int)($post['fim'] ?? 10);

        // Garante que é array
        if (!is_array($tiposItem)) {
            $tiposItem = [$tiposItem];
        }

        if (!$eventId || empty($tiposItem) || $inicio > $fim || ($fim - $inicio) > 100) {
            $retorno['erro'] = 'Dados inválidos para criação em lote. Selecione pelo menos um tipo.';
            return $this->response->setJSON($retorno);
        }

        $criados = 0;
        $erros = [];

        // Armazena os tipos como JSON se houver mais de um, senão como string simples
        $tipoItemValue = count($tiposItem) > 1 ? json_encode($tiposItem) : $tiposItem[0];

        // Cria os espaços (um espaço pode servir para múltiplos tipos)
        for ($i = $inicio; $i <= $fim; $i++) {
            $nome = $prefixo . $i;
            
            // Verifica se já existe um espaço com esse nome para esse evento
            $existe = $this->espacoModel
                ->where('event_id', $eventId)
                ->where('nome', $nome)
                ->first();
            
            if (!$existe) {
                $espaco = new \App\Entities\Espaco([
                    'event_id' => $eventId,
                    'tipo_item' => $tipoItemValue,
                    'nome' => $nome,
                    'status' => 'livre',
                ]);
                
                if ($this->espacoModel->save($espaco)) {
                    $criados++;
                } else {
                    $erros[] = $nome;
                }
            }
        }

        if ($criados > 0) {
            $tiposStr = count($tiposItem) > 1 ? implode(', ', $tiposItem) : $tiposItem[0];
            $retorno['sucesso'] = "{$criados} espaço(s) criado(s) para: {$tiposStr}";
        } else {
            $retorno['erro'] = 'Nenhum espaço foi criado. Verifique se já existem.';
        }

        return $this->response->setJSON($retorno);
    }

    /**
     * Excluir espaço (AJAX)
     */
    public function excluir()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $id = $this->request->getPost('id');
        $espaco = $this->espacoModel->find($id);

        if (!$espaco) {
            $retorno['erro'] = 'Espaço não encontrado';
            return $this->response->setJSON($retorno);
        }

        if ($espaco->status === 'reservado') {
            $retorno['erro'] = 'Não é possível excluir um espaço reservado';
            return $this->response->setJSON($retorno);
        }

        if ($this->espacoModel->delete($id)) {
            $retorno['sucesso'] = 'Espaço excluído com sucesso!';
            return $this->response->setJSON($retorno);
        }

        $retorno['erro'] = 'Erro ao excluir espaço';
        return $this->response->setJSON($retorno);
    }

    /**
     * Alterar status do espaço (AJAX)
     */
    public function alterarStatus()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $id = $this->request->getPost('id');
        $novoStatus = $this->request->getPost('status');

        $espaco = $this->espacoModel->find($id);

        if (!$espaco) {
            $retorno['erro'] = 'Espaço não encontrado';
            return $this->response->setJSON($retorno);
        }

        // Não permite alterar de reservado para outro status diretamente
        if ($espaco->status === 'reservado' && $novoStatus !== 'reservado') {
            // Precisa liberar o espaço primeiro
            $this->espacoModel->liberar($id);
        }

        $this->espacoModel->update($id, ['status' => $novoStatus]);

        $retorno['sucesso'] = 'Status alterado com sucesso!';
        return $this->response->setJSON($retorno);
    }

    /**
     * Busca espaços livres por evento e tipo (AJAX)
     * Usado pelos combobox de escolha de espaço
     */
    public function buscarLivres()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $eventId = $this->request->getGet('event_id');
        $tipoItem = $this->request->getGet('tipo_item');
        $contratoItemId = $this->request->getGet('contrato_item_id');

        // Log para debug
        log_message('debug', "buscarLivres: event_id={$eventId}, tipo_item={$tipoItem}, contrato_item_id={$contratoItemId}");

        $espacos = $this->espacoModel->buscaLivresPorEventoETipo($eventId, $tipoItem);

        // Inclui também o espaço já reservado por este item (se houver)
        if ($contratoItemId) {
            $espacoReservado = $this->espacoModel->buscaPorContratoItem($contratoItemId);
            if ($espacoReservado) {
                // Adiciona no início da lista
                array_unshift($espacos, $espacoReservado);
            }
        }

        $data = [];
        foreach ($espacos as $espaco) {
            $data[] = [
                'id' => $espaco->id,
                'nome' => $espaco->nome,
                'status' => $espaco->status,
                'selecionado' => ($espacoReservado ?? null) && $espaco->id === $espacoReservado->id,
            ];
        }

        return $this->response->setJSON([
            'data' => $data,
            'debug' => [
                'event_id' => $eventId,
                'tipo_item' => $tipoItem,
                'total_encontrados' => count($espacos),
            ]
        ]);
    }

    /**
     * Recupera espaços via AJAX para DataTables
     */
    public function recuperaEspacos()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $eventId = $this->request->getGet('event_id');
        
        if (!$eventId) {
            return $this->response->setJSON(['data' => []]);
        }

        $espacos = $this->espacoModel->buscaPorEvento($eventId);

        $data = [];
        foreach ($espacos as $espaco) {
            // Busca info do contrato se reservado
            $contratoInfo = '-';
            if ($espaco->contrato_item_id) {
                $itemModel = new ContratoItemModel();
                $item = $itemModel->find($espaco->contrato_item_id);
                if ($item) {
                    $contratoModel = new \App\Models\ContratoModel();
                    $contrato = $contratoModel->find($item->contrato_id);
                    if ($contrato) {
                        $contratoInfo = '<a href="' . site_url('contratos/exibir/' . $contrato->id) . '">' . esc($contrato->codigo) . '</a>';
                    }
                }
            }

            // Formata os tipos (pode ser JSON array ou string simples)
            $tipoItemDisplay = '';
            $tiposArray = json_decode($espaco->tipo_item, true);
            if (is_array($tiposArray)) {
                foreach ($tiposArray as $tipo) {
                    $tipoItemDisplay .= '<span class="badge bg-primary me-1">' . esc($tipo) . '</span>';
                }
            } else {
                $tipoItemDisplay = '<span class="badge bg-primary">' . esc($espaco->tipo_item) . '</span>';
            }

            $data[] = [
                'id' => $espaco->id,
                'tipo_item' => $tipoItemDisplay,
                'nome' => esc($espaco->nome),
                'descricao' => esc($espaco->descricao ?? '-'),
                'status' => $espaco->getBadgeStatus(),
                'contrato' => $contratoInfo,
                'acoes' => $this->getBotoesAcao($espaco),
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    /**
     * Monta botões de ação para a tabela
     */
    private function getBotoesAcao($espaco): string
    {
        $btns = '<div class="btn-group btn-group-sm">';
        
        // Botão editar
        $btns .= '<button type="button" class="btn btn-outline-primary btn-editar" 
            data-id="' . $espaco->id . '" 
            data-nome="' . esc($espaco->nome) . '"
            data-descricao="' . esc($espaco->descricao ?? '') . '"
            data-tipo-item="' . esc($espaco->tipo_item) . '"
            data-status="' . $espaco->status . '"
            title="Editar"><i class="bx bx-edit"></i></button>';

        // Botões de status
        if ($espaco->status === 'livre') {
            $btns .= '<button type="button" class="btn btn-outline-secondary btn-bloquear" 
                data-id="' . $espaco->id . '" title="Bloquear"><i class="bx bx-block"></i></button>';
        } elseif ($espaco->status === 'bloqueado') {
            $btns .= '<button type="button" class="btn btn-outline-success btn-liberar" 
                data-id="' . $espaco->id . '" title="Liberar"><i class="bx bx-check"></i></button>';
        }

        // Botão excluir (apenas se não reservado)
        if ($espaco->status !== 'reservado') {
            $btns .= '<button type="button" class="btn btn-outline-danger btn-excluir" 
                data-id="' . $espaco->id . '" title="Excluir"><i class="bx bx-trash"></i></button>';
        }

        $btns .= '</div>';
        return $btns;
    }
}
