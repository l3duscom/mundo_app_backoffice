<?php

namespace App\Controllers;

use App\Services\ResendService;

class RecuperacaoLeads extends BaseController
{
    private $recuperacaoLeadModel;
    private $eventoModel;
    private $usuarioModel;
    private $resendService;

    public function __construct()
    {
        $this->recuperacaoLeadModel = new \App\Models\RecuperacaoLeadModel();
        $this->eventoModel = new \App\Models\EventoModel();
        $this->usuarioModel = new \App\Models\UsuarioModel();
        $this->resendService = new ResendService();
    }

    public function index()
    {
        if (! $this->usuarioLogado()->is_admin) {
            return redirect()->back()->with('atencao', 'Você não tem permissão para acessar esse menu.');
        }

        $eventoDestino = evento_selecionado_com_validacao();

        if (! $eventoDestino) {
            return redirect()->to(site_url('/'))->with('atencao', 'Selecione um evento primeiro.');
        }

        $eventos = $this->eventoModel
            ->where('id !=', $eventoDestino->id)
            ->orderBy('data_inicio', 'DESC')
            ->findAll();

        $eventoOrigemId = (int) ($this->request->getGet('evento_origem_id') ?? 0);
        $tipoSelecionado = trim((string) ($this->request->getGet('tipo') ?? ''));
        $tiposDisponiveis = ['VIP', 'Epic', 'Premium', 'Comum'];

        $leads = [];
        $eventoOrigem = null;
        $estatisticas = [
            'total'         => 0,
            'sem_status'    => 0,
            'contato_feito' => 0,
            'rejeitado'     => 0,
            'revertido'     => 0,
            'valor_origem'  => 0,
        ];

        if ($eventoOrigemId > 0) {
            $eventoOrigem = $this->eventoModel->find($eventoOrigemId);

            if ($eventoOrigem) {
                $this->recuperacaoLeadModel->marcaRevertidos($eventoDestino->id);

                $leads = $this->recuperacaoLeadModel->listaLeads($eventoOrigemId, $eventoDestino->id, $tipoSelecionado !== '' ? $tipoSelecionado : null);

                foreach ($leads as $lead) {
                    $estatisticas['total']++;
                    $estatisticas['valor_origem'] += (float) $lead['valor_total_origem'];

                    $status = $lead['recuperacao_status'] ?? null;
                    if (! $status) {
                        $estatisticas['sem_status']++;
                    } elseif (isset($estatisticas[$status])) {
                        $estatisticas[$status]++;
                    }
                }
            }
        }

        $data = [
            'titulo'           => 'Recuperação de Leads',
            'eventoDestino'    => $eventoDestino,
            'eventoOrigem'     => $eventoOrigem,
            'eventoOrigemId'   => $eventoOrigemId,
            'eventos'          => $eventos,
            'leads'            => $leads,
            'estatisticas'     => $estatisticas,
            'tiposDisponiveis' => $tiposDisponiveis,
            'tipoSelecionado'  => $tipoSelecionado,
        ];

        return view('RecuperacaoLeads/index', $data);
    }

    public function salvar()
    {
        if (! $this->usuarioLogado()->is_admin) {
            return $this->response->setJSON(['erro' => 'Sem permissão.']);
        }

        $eventoDestino = evento_selecionado_com_validacao();
        if (! $eventoDestino) {
            return $this->response->setJSON(['erro' => 'Selecione um evento primeiro.']);
        }

        $userId          = (int) $this->request->getPost('user_id');
        $eventoOrigemId  = (int) $this->request->getPost('evento_origem_id');
        $status          = $this->request->getPost('status');
        $observacao      = $this->request->getPost('observacao');

        if (! $userId || ! $eventoOrigemId) {
            return $this->response->setJSON(['erro' => 'Parâmetros inválidos.']);
        }

        $statusValidos = ['contato_feito', 'rejeitado', 'revertido'];
        if ($status !== null && $status !== '' && ! in_array($status, $statusValidos, true)) {
            return $this->response->setJSON(['erro' => 'Status inválido.']);
        }

        if ($status === '') {
            $status = null;
        }

        $this->recuperacaoLeadModel->definirStatus(
            $userId,
            $eventoOrigemId,
            (int) $eventoDestino->id,
            $status,
            $observacao !== '' ? $observacao : null
        );

        return $this->response->setJSON([
            'sucesso' => 'Status atualizado.',
            'token'   => csrf_hash(),
        ]);
    }

    public function exportarCsv()
    {
        if (! $this->usuarioLogado()->is_admin) {
            return redirect()->back()->with('atencao', 'Sem permissão.');
        }

        $eventoDestino = evento_selecionado_com_validacao();
        if (! $eventoDestino) {
            return redirect()->to(site_url('/'))->with('atencao', 'Selecione um evento primeiro.');
        }

        $eventoOrigemId = (int) $this->request->getGet('evento_origem_id');
        if (! $eventoOrigemId) {
            return redirect()->to(site_url('/recuperacao-leads'))->with('atencao', 'Escolha um evento de origem.');
        }

        $tipo = trim((string) $this->request->getGet('tipo'));
        $tipoFilter = $tipo !== '' ? $tipo : null;

        $this->recuperacaoLeadModel->marcaRevertidos($eventoDestino->id);
        $leads = $this->recuperacaoLeadModel->listaLeads($eventoOrigemId, (int) $eventoDestino->id, $tipoFilter);

        $limitePorArquivo = 1950;
        $chunks = array_chunk($leads, $limitePorArquivo);
        $timestamp = date('Y-m-d-His');
        $sufixoTipo = $tipoFilter ? '-' . preg_replace('/[^a-z0-9]+/i', '', $tipoFilter) : '';

        if (count($chunks) <= 1) {
            $nomeArquivo = 'recuperacao-leads' . $sufixoTipo . '-' . $timestamp . '.csv';

            $this->response->setHeader('Content-Type', 'text/csv; charset=UTF-8');
            $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $nomeArquivo . '"');
            $this->response->setHeader('Cache-Control', 'no-store, no-cache');

            return $this->response->setBody($this->gerarCsvLeads($chunks[0] ?? []));
        }

        $tempZip = tempnam(sys_get_temp_dir(), 'leads_') . '.zip';
        $zip = new \ZipArchive();
        if ($zip->open($tempZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->with('atencao', 'Falha ao gerar o ZIP. Tente novamente.');
        }

        foreach ($chunks as $i => $chunk) {
            $parte = str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);
            $zip->addFromString("recuperacao-leads{$sufixoTipo}-{$timestamp}-parte-{$parte}.csv", $this->gerarCsvLeads($chunk));
        }
        $zip->close();

        $nomeArquivoZip = 'recuperacao-leads' . $sufixoTipo . '-' . $timestamp . '.zip';
        $conteudo = file_get_contents($tempZip);
        @unlink($tempZip);

        $this->response->setHeader('Content-Type', 'application/zip');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $nomeArquivoZip . '"');
        $this->response->setHeader('Cache-Control', 'no-store, no-cache');

        return $this->response->setBody($conteudo);
    }

    private function gerarCsvLeads(array $leads): string
    {
        $output = fopen('php://temp', 'r+');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['Nome completo', 'Telefone'], ',');

        foreach ($leads as $lead) {
            fputcsv($output, [
                $lead['nome'] ?? '',
                $lead['telefone'] ?? '',
            ], ',');
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    public function enviarEmail()
    {
        if (! $this->usuarioLogado()->is_admin) {
            return $this->response->setJSON(['erro' => 'Sem permissão.', 'token' => csrf_hash()]);
        }

        $eventoDestino = evento_selecionado_com_validacao();
        if (! $eventoDestino) {
            return $this->response->setJSON(['erro' => 'Selecione um evento primeiro.', 'token' => csrf_hash()]);
        }

        $userId         = (int) $this->request->getPost('user_id');
        $eventoOrigemId = (int) $this->request->getPost('evento_origem_id');
        $assunto        = trim((string) $this->request->getPost('assunto'));
        $mensagem       = trim((string) $this->request->getPost('mensagem'));

        if (! $userId || ! $eventoOrigemId || $assunto === '' || $mensagem === '') {
            return $this->response->setJSON(['erro' => 'Preencha assunto e mensagem.', 'token' => csrf_hash()]);
        }

        $usuario = $this->usuarioModel->find($userId);
        if (! $usuario || empty($usuario->email)) {
            return $this->response->setJSON(['erro' => 'Usuário sem e-mail cadastrado.', 'token' => csrf_hash()]);
        }

        $html = '<div style="font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6; color: #333;">'
              . nl2br(esc($mensagem))
              . '</div>';

        try {
            $this->resendService->enviarEmail($usuario->email, $assunto, $html);
        } catch (\Throwable $e) {
            log_message('error', 'Falha ao enviar email de recuperação: ' . $e->getMessage());
            return $this->response->setJSON(['erro' => 'Falha ao enviar e-mail. Tente novamente.', 'token' => csrf_hash()]);
        }

        $this->recuperacaoLeadModel->definirStatus(
            $userId,
            $eventoOrigemId,
            (int) $eventoDestino->id,
            'contato_feito',
            'E-mail enviado em ' . date('d/m/Y H:i') . ': ' . $assunto
        );

        return $this->response->setJSON([
            'sucesso' => 'E-mail enviado para ' . $usuario->email . '.',
            'token'   => csrf_hash(),
        ]);
    }
}
