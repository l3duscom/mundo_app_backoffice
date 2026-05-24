<?php

namespace App\Controllers;

class RecuperacaoLeads extends BaseController
{
    private $recuperacaoLeadModel;
    private $eventoModel;

    public function __construct()
    {
        $this->recuperacaoLeadModel = new \App\Models\RecuperacaoLeadModel();
        $this->eventoModel = new \App\Models\EventoModel();
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

                $leads = $this->recuperacaoLeadModel->listaLeads($eventoOrigemId, $eventoDestino->id);

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
}
