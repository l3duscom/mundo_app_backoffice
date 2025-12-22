<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TicketModel;
use App\Models\EventoModel;

class Tickets extends BaseController
{
    private $ticketModel;
    private $eventoModel;

    public function __construct()
    {
        $this->ticketModel = new TicketModel();
        $this->eventoModel = new EventoModel();
    }

    /**
     * Lista tickets do evento selecionado
     */
    public function index()
    {
        $eventId = $this->request->getGet('event_id') ?? evento_selecionado();
        
        if (!$eventId) {
            return redirect()->to('/')->with('info', 'Selecione um evento primeiro.');
        }

        // Buscar evento
        $evento = $this->eventoModel->find($eventId);
        if (!$evento) {
            return redirect()->to('/eventos')->with('erro', 'Evento não encontrado.');
        }

        // Contagem de tickets
        $contagem = [
            'total' => $this->ticketModel->where('event_id', $eventId)->countAllResults(false),
            'ativos' => $this->ticketModel->where('event_id', $eventId)->where('ativo', 1)->countAllResults(false),
            'inativos' => $this->ticketModel->where('event_id', $eventId)->where('ativo', 0)->countAllResults(false),
        ];

        // Tipos de ticket disponíveis
        $tipos = ['individual', 'combo'];
        
        // Categorias disponíveis
        $categorias = ['comum', 'cosplay', 'vip', 'epic', 'cortesia', 'after', 'camping', 'premium'];
        
        // Dias da semana
        $dias = ['dom', 'seg', 'ter', 'qua', 'qui', 'sex', 'sab'];

        // Buscar lotes distintos do evento usando query builder direto
        $db = \Config\Database::connect();
        $lotes = $db->table('tickets')
            ->select('lote')
            ->where('event_id', $eventId)
            ->where('deleted_at IS NULL')
            ->where('lote IS NOT NULL')
            ->groupBy('lote')
            ->orderBy('lote', 'ASC')
            ->get()
            ->getResultArray();
        
        // Filtrar lotes vazios e converter para array
        $lotesArray = [];
        foreach ($lotes as $l) {
            $loteVal = $l['lote'] ?? '';
            if (strlen(trim((string)$loteVal)) > 0) {
                $lotesArray[] = $loteVal;
            }
        }

        // Buscar eventos ativos (para duplicação entre eventos)
        $eventoModel = new \App\Models\EventoModel();
        $eventosAtivos = $eventoModel
            ->where('ativo', 1)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $data = [
            'titulo' => 'Ingressos - ' . $evento->nome,
            'evento' => $evento,
            'eventIdSelecionado' => $eventId,
            'contagem' => $contagem,
            'tipos' => $tipos,
            'categorias' => $categorias,
            'dias' => $dias,
            'lotes' => $lotesArray,
            'eventosAtivos' => $eventosAtivos,
        ];

        return view('Tickets/index', $data);
    }

    /**
     * Recupera tickets via AJAX para DataTables
     */
    public function recuperaTickets()
    {
        $eventId = $this->request->getGet('event_id');
        
        if (!$eventId) {
            return $this->response->setJSON(['data' => []]);
        }

        $tickets = $this->ticketModel
            ->where('event_id', $eventId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $data = [];
        foreach ($tickets as $ticket) {
            $data[] = [
                'id' => $ticket->id,
                'nome' => esc($ticket->nome),
                'codigo' => esc($ticket->codigo),
                'tipo' => $this->getBadgeTipo($ticket->tipo),
                'categoria' => esc($ticket->categoria ?? '-'),
                'preco' => 'R$ ' . number_format($ticket->preco ?? 0, 2, ',', '.'),
                'quantidade' => (int)$ticket->quantidade,
                'estoque' => (int)$ticket->estoque,
                'vendidos' => (int)$ticket->quantidade - (int)$ticket->estoque,
                'lote' => esc($ticket->lote ?? '-'),
                'data_lote' => $ticket->data_lote ? date('d/m/Y', strtotime($ticket->data_lote)) : '-',
                'status' => $this->getBadgeStatus($ticket->ativo),
                'acoes' => $this->getBotoesAcao($ticket),
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    /**
     * Salvar ticket (criar ou atualizar)
     */
    public function salvar()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['erro' => 'Acesso negado']);
        }

        $id = $this->request->getPost('id');
        $eventId = $this->request->getPost('event_id');

        if (!$eventId) {
            return $this->response->setJSON([
                'erro' => 'Evento não informado',
                'token' => csrf_hash()
            ]);
        }

        $dados = [
            'event_id' => $eventId,
            'nome' => $this->request->getPost('nome'),
            'codigo' => $this->request->getPost('codigo') ?: $this->ticketModel->geraCodigoTicket(),
            'tipo' => $this->request->getPost('tipo'),
            'categoria' => $this->request->getPost('categoria'),
            'dia' => $this->request->getPost('dia'),
            'preco' => $this->limpaValor($this->request->getPost('preco')),
            'valor_unitario' => $this->limpaValor($this->request->getPost('valor_unitario')),
            'quantidade' => (int)$this->request->getPost('quantidade'),
            'estoque' => (int)$this->request->getPost('estoque'),
            'lote' => $this->request->getPost('lote'),
            'data_inicio' => $this->request->getPost('data_inicio') ?: null,
            'data_fim' => $this->request->getPost('data_fim') ?: null,
            'data_lote' => $this->request->getPost('data_lote') ?: null,
            'descricao' => $this->request->getPost('descricao'),
            'promo' => $this->request->getPost('promo') ?: null,
            'ativo' => $this->request->getPost('ativo') ? 1 : 0,
        ];

        try {
            if ($id) {
                // Atualizar
                $this->ticketModel->update($id, $dados);
                $mensagem = 'Ingresso atualizado com sucesso!';
            } else {
                // Criar
                $this->ticketModel->insert($dados);
                $mensagem = 'Ingresso criado com sucesso!';
            }

            return $this->response->setJSON([
                'sucesso' => $mensagem,
                'token' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'erro' => 'Erro ao salvar: ' . $e->getMessage(),
                'token' => csrf_hash()
            ]);
        }
    }

    /**
     * Salvar múltiplos tickets (duplicação em massa)
     */
    public function salvarLote()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['erro' => 'Acesso negado']);
        }

        $eventId = $this->request->getPost('event_id');
        $tickets = $this->request->getPost('tickets');

        if (!$eventId) {
            return $this->response->setJSON([
                'erro' => 'Evento não informado',
                'token' => csrf_hash()
            ]);
        }

        if (empty($tickets) || !is_array($tickets)) {
            return $this->response->setJSON([
                'erro' => 'Nenhum ingresso para criar',
                'token' => csrf_hash()
            ]);
        }

        try {
            $criados = 0;
            foreach ($tickets as $ticket) {
                $dados = [
                    'event_id' => $eventId,
                    'nome' => $ticket['nome'] ?? '',
                    'codigo' => $this->ticketModel->geraCodigoTicket(),
                    'tipo' => $ticket['tipo'] ?? '',
                    'categoria' => $ticket['categoria'] ?? '',
                    'dia' => $ticket['dia'] ?? null,
                    'preco' => $this->limpaValor($ticket['preco'] ?? '0'),
                    'valor_unitario' => $this->limpaValor($ticket['valor_unitario'] ?? '0'),
                    'quantidade' => (int)($ticket['quantidade'] ?? 0),
                    'estoque' => (int)($ticket['estoque'] ?? 0),
                    'lote' => $ticket['lote'] ?? '',
                    'data_inicio' => !empty($ticket['data_inicio']) ? $ticket['data_inicio'] : null,
                    'data_fim' => !empty($ticket['data_fim']) ? $ticket['data_fim'] : null,
                    'data_lote' => !empty($ticket['data_lote']) ? $ticket['data_lote'] : null,
                    'descricao' => !empty($ticket['descricao_base64']) ? base64_decode($ticket['descricao_base64']) : ($ticket['descricao'] ?? ''),
                    'promo' => !empty($ticket['promo']) ? $ticket['promo'] : null,
                    'ativo' => isset($ticket['ativo']) ? 1 : 0,
                ];

                $this->ticketModel->insert($dados);
                $criados++;
            }

            return $this->response->setJSON([
                'sucesso' => $criados . ' ingresso(s) criado(s) com sucesso!',
                'token' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'erro' => 'Erro ao salvar: ' . $e->getMessage(),
                'token' => csrf_hash()
            ]);
        }
    }

    /**
     * Atualizar múltiplos tickets (edição em massa)
     */
    public function atualizarLote()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['erro' => 'Acesso negado']);
        }

        $tickets = $this->request->getPost('tickets');

        if (empty($tickets) || !is_array($tickets)) {
            return $this->response->setJSON([
                'erro' => 'Nenhum ingresso para atualizar',
                'token' => csrf_hash()
            ]);
        }

        try {
            $atualizados = 0;
            foreach ($tickets as $ticket) {
                $id = $ticket['id'] ?? null;
                if (!$id) continue;

                $dados = [
                    'nome' => $ticket['nome'] ?? '',
                    'preco' => $this->limpaValor($ticket['preco'] ?? '0'),
                    'quantidade' => (int)($ticket['quantidade'] ?? 0),
                    'estoque' => (int)($ticket['estoque'] ?? 0),
                    'lote' => $ticket['lote'] ?? '',
                    'data_lote' => !empty($ticket['data_lote']) ? $ticket['data_lote'] : null,
                    'promo' => !empty($ticket['promo']) ? $ticket['promo'] : null,
                    'ativo' => isset($ticket['ativo']) ? 1 : 0,
                ];

                $this->ticketModel->update($id, $dados);
                $atualizados++;
            }

            return $this->response->setJSON([
                'sucesso' => $atualizados . ' ingresso(s) atualizado(s) com sucesso!',
                'token' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'erro' => 'Erro ao atualizar: ' . $e->getMessage(),
                'token' => csrf_hash()
            ]);
        }
    }

    /**
     * Alterar status do ticket (AJAX)
     */
    public function alterarStatus()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['erro' => 'Acesso negado']);
        }

        $id = $this->request->getPost('id');
        $ativo = $this->request->getPost('ativo');

        try {
            $this->ticketModel->update($id, ['ativo' => (int)$ativo]);

            return $this->response->setJSON([
                'sucesso' => $ativo ? 'Ingresso ativado!' : 'Ingresso desativado!',
                'token' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'erro' => 'Erro ao alterar status',
                'token' => csrf_hash()
            ]);
        }
    }

    /**
     * Excluir ticket (soft delete via AJAX)
     */
    public function excluir()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['erro' => 'Acesso negado']);
        }

        $id = $this->request->getPost('id');

        try {
            $this->ticketModel->delete($id);

            return $this->response->setJSON([
                'sucesso' => 'Ingresso excluído com sucesso!',
                'token' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'erro' => 'Erro ao excluir ingresso',
                'token' => csrf_hash()
            ]);
        }
    }

    /**
     * Badge para o tipo de ticket
     */
    private function getBadgeTipo($tipo)
    {
        $cores = [
            'individual' => 'primary',
            'combo' => 'info',
        ];

        $cor = $cores[$tipo] ?? 'secondary';
        return '<span class="badge bg-' . $cor . '">' . esc($tipo ?? 'N/A') . '</span>';
    }

    /**
     * Badge para status ativo/inativo
     */
    private function getBadgeStatus($ativo)
    {
        if ($ativo) {
            return '<span class="badge bg-success">Ativo</span>';
        }
        return '<span class="badge bg-secondary">Inativo</span>';
    }

    /**
     * Monta botões de ação para a tabela
     */
    private function getBotoesAcao($ticket)
    {
        $btns = '<div class="btn-group btn-group-sm">';
        
        // Editar
        $btns .= '<button type="button" class="btn btn-outline-primary btn-editar" 
                    data-id="' . $ticket->id . '"
                    data-nome="' . esc($ticket->nome) . '"
                    data-codigo="' . esc($ticket->codigo) . '"
                    data-tipo="' . esc($ticket->tipo) . '"
                    data-categoria="' . esc($ticket->categoria) . '"
                    data-dia="' . strtolower(esc($ticket->dia ?? '')) . '"
                    data-preco="' . number_format($ticket->preco ?? 0, 2, ',', '.') . '"
                    data-valor-unitario="' . number_format($ticket->valor_unitario ?? 0, 2, ',', '.') . '"
                    data-quantidade="' . (int)$ticket->quantidade . '"
                    data-estoque="' . (int)$ticket->estoque . '"
                    data-lote="' . esc($ticket->lote) . '"
                    data-data-inicio="' . ($ticket->data_inicio ? date('Y-m-d', strtotime($ticket->data_inicio)) : '') . '"
                    data-data-fim="' . ($ticket->data_fim ? date('Y-m-d', strtotime($ticket->data_fim)) : '') . '"
                    data-data-lote="' . ($ticket->data_lote ? date('Y-m-d', strtotime($ticket->data_lote)) : '') . '"
                    data-descricao="' . base64_encode($ticket->descricao ?? '') . '"
                    data-promo="' . esc($ticket->promo) . '"
                    data-ativo="' . $ticket->ativo . '"
                    title="Editar"><i class="bx bx-edit"></i></button>';

        // Duplicar
        $novoLote = ((int)$ticket->lote) + 1;
        $btns .= '<button type="button" class="btn btn-outline-info btn-duplicar" 
                    data-nome="' . esc($ticket->nome) . '"
                    data-tipo="' . esc($ticket->tipo) . '"
                    data-categoria="' . esc($ticket->categoria) . '"
                    data-dia="' . strtolower(esc($ticket->dia ?? '')) . '"
                    data-preco="' . number_format($ticket->preco ?? 0, 2, ',', '.') . '"
                    data-valor-unitario="' . number_format($ticket->valor_unitario ?? 0, 2, ',', '.') . '"
                    data-quantidade="' . (int)$ticket->quantidade . '"
                    data-lote="' . $novoLote . '"
                    data-data-inicio="' . ($ticket->data_inicio ? date('Y-m-d', strtotime($ticket->data_inicio)) : '') . '"
                    data-data-fim="' . ($ticket->data_fim ? date('Y-m-d', strtotime($ticket->data_fim)) : '') . '"
                    data-data-lote="' . ($ticket->data_lote ? date('Y-m-d', strtotime($ticket->data_lote)) : '') . '"
                    data-descricao="' . base64_encode($ticket->descricao ?? '') . '"
                    data-promo="' . esc($ticket->promo) . '"
                    title="Duplicar"><i class="bx bx-copy"></i></button>';

        // Status toggle
        if ($ticket->ativo) {
            $btns .= '<button type="button" class="btn btn-outline-warning btn-desativar" 
                        data-id="' . $ticket->id . '" title="Desativar">
                        <i class="bx bx-pause"></i></button>';
        } else {
            $btns .= '<button type="button" class="btn btn-outline-success btn-ativar" 
                        data-id="' . $ticket->id . '" title="Ativar">
                        <i class="bx bx-play"></i></button>';
        }

        // Excluir
        $btns .= '<button type="button" class="btn btn-outline-danger btn-excluir" 
                    data-id="' . $ticket->id . '" title="Excluir">
                    <i class="bx bx-trash"></i></button>';

        $btns .= '</div>';
        return $btns;
    }

    /**
     * Limpa valor monetário para salvar no banco
     */
    private function limpaValor($valor)
    {
        if (empty($valor)) return 0;
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
        return (float)$valor;
    }
}
