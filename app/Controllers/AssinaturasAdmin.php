<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AssinaturaModel;
use App\Models\AssinaturaHistoricoModel;
use App\Models\PlanoModel;
use App\Models\UsuarioModel;

class AssinaturasAdmin extends BaseController
{
    protected $assinaturaModel;
    protected $historicoModel;
    protected $planoModel;

    public function __construct()
    {
        $this->assinaturaModel = new AssinaturaModel();
        $this->historicoModel = new AssinaturaHistoricoModel();
        $this->planoModel = new PlanoModel();
    }

    /**
     * Listagem de todas as assinaturas
     */
    public function index()
    {
        $filtroStatus = $this->request->getGet('status');
        
        $dados = [
            'titulo' => 'Gerenciar Assinaturas',
            'assinaturas' => $this->assinaturaModel->buscaTodas($filtroStatus),
            'estatisticas' => $this->assinaturaModel->getEstatisticas(),
            'filtroStatus' => $filtroStatus,
        ];

        return view('AssinaturasAdmin/index', $dados);
    }

    /**
     * Detalhes da assinatura
     */
    public function exibir(int $id = null)
    {
        $assinatura = $this->buscaOu404($id);

        $dados = [
            'titulo' => 'Detalhes da Assinatura #' . $id,
            'assinatura' => $assinatura,
            'historico' => $this->historicoModel->buscaPorAssinatura($id),
        ];

        return view('AssinaturasAdmin/exibir', $dados);
    }

    /**
     * Cancelar assinatura manualmente
     */
    public function cancelar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('assinaturas-admin');
        }

        $id = $this->request->getPost('id');
        $motivo = $this->request->getPost('motivo');
        
        $assinatura = $this->buscaOu404($id);

        if ($assinatura->status === 'CANCELLED') {
            return $this->response->setJSON([
                'erro' => 'Esta assinatura já está cancelada.',
                'token' => csrf_hash(),
            ]);
        }

        // Atualizar status
        $this->assinaturaModel->update($id, [
            'status' => 'CANCELLED',
            'data_fim' => date('Y-m-d H:i:s'),
        ]);

        // Registrar evento no histórico
        $this->historicoModel->registra($id, 'CANCELLED', $motivo ?? 'Cancelamento manual pelo administrador', [
            'cancelado_por' => 'admin',
            'data_cancelamento' => date('Y-m-d H:i:s'),
        ]);

        // Atualizar usuário (remover premium)
        $usuarioModel = new UsuarioModel();
        $usuarioModel->protect(false)->update($assinatura->usuario_id, [
            'is_premium' => 0,
            'premium_ate' => null,
        ]);

        return $this->response->setJSON([
            'sucesso' => true,
            'mensagem' => 'Assinatura cancelada com sucesso.',
            'token' => csrf_hash(),
        ]);
    }

    /**
     * Reativar assinatura
     */
    public function reativar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('assinaturas-admin');
        }

        $id = $this->request->getPost('id');
        $assinatura = $this->buscaOu404($id);

        if ($assinatura->status === 'ACTIVE') {
            return $this->response->setJSON([
                'erro' => 'Esta assinatura já está ativa.',
                'token' => csrf_hash(),
            ]);
        }

        // Calcular nova data de fim baseado no plano
        $plano = $this->planoModel->find($assinatura->plano_id);
        $dataFim = new \DateTime();
        
        if ($plano->ciclo === 'YEARLY') {
            $dataFim->add(new \DateInterval('P1Y'));
        } else {
            $dataFim->add(new \DateInterval('P1M'));
        }

        // Atualizar status
        $this->assinaturaModel->update($id, [
            'status' => 'ACTIVE',
            'data_inicio' => date('Y-m-d H:i:s'),
            'data_fim' => $dataFim->format('Y-m-d H:i:s'),
        ]);

        // Registrar evento no histórico
        $this->historicoModel->registra($id, 'REACTIVATED', 'Reativação manual pelo administrador', [
            'reativado_por' => 'admin',
            'data_reativacao' => date('Y-m-d H:i:s'),
        ]);

        // Atualizar usuário (marcar como premium)
        $usuarioModel = new UsuarioModel();
        $usuarioModel->protect(false)->update($assinatura->usuario_id, [
            'is_premium' => 1,
            'premium_ate' => $dataFim->format('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'sucesso' => true,
            'mensagem' => 'Assinatura reativada com sucesso.',
            'token' => csrf_hash(),
        ]);
    }

    /**
     * Buscar histórico via AJAX
     */
    public function historico(int $id = null)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('assinaturas-admin');
        }

        $historico = $this->historicoModel->buscaPorAssinatura($id);

        $html = '';
        foreach ($historico as $evento) {
            $html .= '<div class="timeline-item">';
            $html .= '<div class="d-flex align-items-start">';
            $html .= '<div class="me-3"><i class="bx ' . $evento->getIcone() . ' fs-4"></i></div>';
            $html .= '<div class="flex-grow-1">';
            $html .= '<div class="d-flex justify-content-between">';
            $html .= '<strong>' . $evento->getEventoTexto() . '</strong>';
            $html .= '<small class="text-muted">' . $evento->getDataFormatada() . '</small>';
            $html .= '</div>';
            if ($evento->descricao) {
                $html .= '<p class="text-muted mb-0 small">' . esc($evento->descricao) . '</p>';
            }
            $html .= '</div></div></div>';
        }

        return $this->response->setJSON([
            'html' => $html ?: '<p class="text-muted">Nenhum evento registrado.</p>',
            'token' => csrf_hash(),
        ]);
    }

    /**
     * Exportar assinaturas para CSV
     */
    public function exportar()
    {
        $filtroStatus = $this->request->getGet('status');
        $assinaturas = $this->assinaturaModel->buscaTodas($filtroStatus);

        $filename = 'assinaturas_' . date('Y-m-d_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        
        // BOM para Excel reconhecer UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Cabeçalho
        fputcsv($output, [
            'ID',
            'Usuário',
            'Email',
            'Plano',
            'Status',
            'Valor',
            'Data Início',
            'Data Fim',
            'Próximo Vencimento',
            'Forma Pagamento',
            'Criado em',
        ], ';');

        // Dados
        foreach ($assinaturas as $a) {
            fputcsv($output, [
                $a->id,
                $a->usuario_nome,
                $a->usuario_email,
                $a->plano_nome,
                $a->getStatusTexto(),
                $a->getValorFormatado(),
                $a->getDataInicioFormatada(),
                $a->getDataFimFormatada(),
                $a->getProximoVencimentoFormatado(),
                $a->forma_pagamento ?? '-',
                $a->created_at ? date('d/m/Y H:i', strtotime($a->created_at)) : '-',
            ], ';');
        }

        fclose($output);
        exit;
    }

    /**
     * Busca assinatura ou retorna 404
     */
    private function buscaOu404(int $id = null)
    {
        if (!$id || !$assinatura = $this->assinaturaModel->buscaComDetalhes($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Assinatura não encontrada');
        }

        return $assinatura;
    }
}
