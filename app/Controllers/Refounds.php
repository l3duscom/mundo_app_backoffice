<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Refounds extends BaseController
{
    private $refoundModel;
    private $eventoModel;

    public function __construct()
    {
        $this->refoundModel = new \App\Models\RefoundModel();
        $this->eventoModel = new \App\Models\EventoModel();
    }

    /**
     * Página principal de listagem de refounds
     */
    public function index()
    {
        if (!$this->usuarioLogado()->temPermissaoPara('editar-clientes')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $eventos = $this->eventoModel->orderBy('id', 'DESC')->findAll();

        $data = [
            'titulo' => 'Gerenciamento de Reembolsos',
            'eventos' => $eventos,
        ];

        return view('Refounds/index', $data);
    }

    /**
     * Recupera refounds via AJAX para DataTable
     */
    public function recuperaRefounds()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $eventId = $this->request->getGet('event_id');
        $refounds = $this->refoundModel->listaRefoundsAdmin($eventId);

        $data = [];

        foreach ($refounds as $refound) {
            // Badge de status
            $statusBadge = $this->getStatusBadge($refound->status);
            
            // Badge de tipo
            $tipoBadge = $this->getTipoBadge($refound->tipo_solicitacao);
            
            // Formata valor
            $valorFormatado = 'R$ ' . number_format($refound->pedido_valor_total ?? 0, 2, ',', '.');
            
            // Formata data
            $dataFormatada = $refound->created_at ? date('d/m/Y H:i', strtotime($refound->created_at)) : '-';
            
            // Formata data de processamento
            $processadoEm = $refound->processado_em ? date('d/m/Y H:i', strtotime($refound->processado_em)) : '-';

            $data[] = [
                'id' => $refound->id,
                'cliente_nome' => esc($refound->cliente_nome ?? '-'),
                'cliente_email' => esc($refound->cliente_email ?? '-'),
                'pedido_codigo' => anchor("pedidos/ingressos/" . $refound->pedido_id, esc($refound->pedido_codigo ?? '-'), 'title="Ver pedido"'),
                'valor' => $valorFormatado,
                'evento_nome' => esc($refound->evento_nome ?? '-'),
                'tipo_solicitacao' => $tipoBadge,
                'status' => $statusBadge,
                'data' => $dataFormatada,
                'processado_em' => $processadoEm,
                'aceito' => $refound->aceito ? '<span class="badge bg-success">Sim</span>' : '<span class="badge bg-secondary">Não</span>',
                'acoes' => $this->getBotoesAcao($refound),
            ];
        }

        $retorno = [
            'data' => $data,
        ];

        return $this->response->setJSON($retorno);
    }

    /**
     * Recupera estatísticas via AJAX
     */
    public function recuperaEstatisticas()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $eventId = $this->request->getGet('event_id');
        $stats = $this->refoundModel->getEstatisticas($eventId);

        return $this->response->setJSON($stats);
    }

    /**
     * Exibe detalhes de um refound
     */
    public function exibir(int $id = null)
    {
        if (!$this->usuarioLogado()->temPermissaoPara('editar-clientes')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $refound = $this->refoundModel->find($id);

        if (!$refound) {
            return redirect()->to(site_url('refounds'))->with('atencao', 'Solicitação não encontrada.');
        }

        $data = [
            'titulo' => 'Detalhes da Solicitação #' . $id,
            'refound' => $refound,
        ];

        return view('Refounds/exibir', $data);
    }

    /**
     * Atualiza o status de um refound
     */
    public function atualizarStatus()
    {
        // Aceita requisição POST normal ou AJAX
        if ($this->request->getMethod() !== 'post') {
            return redirect()->back();
        }

        $id = $this->request->getPost('id');
        $novoStatus = $this->request->getPost('status');
        $observacoes = $this->request->getPost('observacoes');

        // Log para debug
        log_message('info', 'atualizarStatus - ID: ' . $id . ', Status: ' . $novoStatus);

        // Validação do status
        if (empty($novoStatus)) {
            return $this->response->setJSON(['erro' => 'Status não informado.']);
        }

        $refound = $this->refoundModel->find($id);

        if (!$refound) {
            return $this->response->setJSON(['erro' => 'Solicitação não encontrada.']);
        }

        $dadosAtualizar = [
            'status' => $novoStatus,
            'processado_em' => date('Y-m-d H:i:s'),
            'processado_por' => $this->usuarioLogado()->id,
        ];

        if ($observacoes) {
            $dadosAtualizar['observacoes'] = $observacoes;
        }

        if ($this->refoundModel->update($id, $dadosAtualizar)) {
            return $this->response->setJSON(['sucesso' => 'Status atualizado com sucesso.']);
        }

        return $this->response->setJSON(['erro' => 'Erro ao atualizar status.']);
    }

    /**
     * Retorna badge HTML para o status
     */
    private function getStatusBadge($status)
    {
        $badges = [
            'pendente' => '<span class="badge bg-warning text-dark fs-6"><i class="bx bx-time me-1"></i>Pendente</span>',
            'processando' => '<span class="badge bg-info fs-6"><i class="bx bx-loader-alt me-1"></i>Processando</span>',
            'concluido' => '<span class="badge bg-success fs-6"><i class="bx bx-check me-1"></i>Concluído</span>',
            'cancelado' => '<span class="badge bg-danger fs-6"><i class="bx bx-x me-1"></i>Cancelado</span>',
            'erro' => '<span class="badge bg-dark fs-6"><i class="bx bx-error me-1"></i>Erro</span>',
        ];

        return $badges[strtolower($status ?? '')] ?? '<span class="badge bg-secondary fs-6">' . esc($status ?? 'N/A') . '</span>';
    }

    /**
     * Retorna badge HTML para o tipo de solicitação
     */
    private function getTipoBadge($tipo)
    {
        $badges = [
            'upgrade' => '<span class="badge bg-purple"><i class="bx bx-up-arrow-alt me-1"></i>Upgrade</span>',
            'reembolso' => '<span class="badge bg-orange"><i class="bx bx-money me-1"></i>Reembolso</span>',
        ];

        return $badges[strtolower($tipo ?? '')] ?? '<span class="badge bg-secondary">' . esc($tipo ?? 'N/A') . '</span>';
    }

    /**
     * Retorna botões de ação
     */
    private function getBotoesAcao($refound)
    {
        $html = '<div class="btn-group btn-group-sm" role="group">';
        $html .= '<a href="' . site_url('refounds/exibir/' . $refound->id) . '" class="btn btn-outline-primary" title="Ver detalhes"><i class="bx bx-show"></i></a>';
        
        if (strtolower($refound->status ?? '') === 'pendente') {
            $html .= '<button type="button" class="btn btn-outline-success btn-aprovar" data-id="' . $refound->id . '" title="Aprovar"><i class="bx bx-check"></i></button>';
            $html .= '<button type="button" class="btn btn-outline-danger btn-rejeitar" data-id="' . $refound->id . '" title="Rejeitar"><i class="bx bx-x"></i></button>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
}
