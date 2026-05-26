<?php

namespace App\Controllers;

use App\Models\MeetModel;
use App\Models\EventoModel;

class Meet extends BaseController
{
    protected $meetModel;
    protected $eventoModel;

    public function __construct()
    {
        $this->meetModel   = new MeetModel();
        $this->eventoModel = new EventoModel();
    }

    public function index()
    {
        if (!$this->usuarioLogado()->is_admin) {
            return redirect()->back()->with('atencao', 'Você não tem permissão para acessar esse menu.');
        }

        $evento = evento_selecionado_com_validacao();
        if (!$evento) {
            return redirect()->to(site_url('/'))->with('atencao', 'Selecione um evento primeiro.');
        }

        $meets = $this->meetModel->getByEvento((int) $evento->id);

        $dias = $this->montaDiasEvento($evento);

        $porDia = [];
        foreach ($meets as $item) {
            $chave = $item->data_meet ? date('Y-m-d', strtotime((string) $item->data_meet)) : 'sem_dia';
            $porDia[$chave][] = $item;
        }

        $data = [
            'titulo' => 'Meet & Greet — ' . esc($evento->nome),
            'evento' => $evento,
            'meets'  => $meets,
            'dias'   => $dias,
            'porDia' => $porDia,
            'tipos'  => $this->getTipos(),
        ];

        return view('Meet/index', $data);
    }

    public function salvar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno = ['token' => csrf_hash()];

        if (!$this->usuarioLogado()->is_admin) {
            $retorno['erro'] = 'Sem permissão.';
            return $this->response->setJSON($retorno);
        }

        $evento = evento_selecionado_com_validacao();
        if (!$evento) {
            $retorno['erro'] = 'Selecione um evento primeiro.';
            return $this->response->setJSON($retorno);
        }

        try {
            $post = $this->request->getPost();

            $dados = [
                'event_id'     => (int) $evento->id,
                'artista'      => trim($post['artista'] ?? ''),
                'dia'          => trim($post['dia'] ?? '') ?: null,
                'tipo'         => trim($post['tipo'] ?? '') ?: null,
                'quantidade'   => (int) ($post['quantidade'] ?? 0),
                'data_meet'    => !empty($post['data_meet']) ? $post['data_meet'] : null,
                'hora_inicial' => !empty($post['hora_inicial']) ? $post['hora_inicial'] : null,
                'hora_final'   => !empty($post['hora_final']) ? $post['hora_final'] : null,
            ];

            if (!empty($post['id'])) {
                $atual = $this->meetModel->find($post['id']);
                if (!$atual || (int) $atual->event_id !== (int) $evento->id) {
                    $retorno['erro'] = 'Meet & Greet não pertence ao evento atual.';
                    return $this->response->setJSON($retorno);
                }
                $dados['id'] = $post['id'];
            }

            if ($this->meetModel->save($dados)) {
                $retorno['sucesso'] = !empty($post['id']) ? 'Meet & Greet atualizado!' : 'Meet & Greet criado!';
                $retorno['id']      = $post['id'] ?? $this->meetModel->getInsertID();
                return $this->response->setJSON($retorno);
            }

            $retorno['erro']        = 'Erro ao salvar.';
            $retorno['erros_model'] = $this->meetModel->errors();
            return $this->response->setJSON($retorno);

        } catch (\Throwable $e) {
            log_message('error', '[MEET/SALVAR] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            $retorno['erro']  = 'Exceção: ' . $e->getMessage();
            $retorno['debug'] = [
                'tipo'    => get_class($e),
                'arquivo' => basename($e->getFile()) . ':' . $e->getLine(),
            ];
            return $this->response->setStatusCode(200)->setJSON($retorno);
        }
    }

    public function excluir()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno = ['token' => csrf_hash()];

        if (!$this->usuarioLogado()->is_admin) {
            $retorno['erro'] = 'Sem permissão.';
            return $this->response->setJSON($retorno);
        }

        $evento = evento_selecionado_com_validacao();
        if (!$evento) {
            $retorno['erro'] = 'Selecione um evento primeiro.';
            return $this->response->setJSON($retorno);
        }

        $id   = $this->request->getPost('id');
        $item = $this->meetModel->find($id);
        if (!$item || (int) $item->event_id !== (int) $evento->id) {
            $retorno['erro'] = 'Meet & Greet não encontrado para o evento atual.';
            return $this->response->setJSON($retorno);
        }

        if ($this->meetModel->delete($id)) {
            $retorno['sucesso'] = 'Meet & Greet removido.';
            return $this->response->setJSON($retorno);
        }

        $retorno['erro'] = 'Erro ao remover.';
        return $this->response->setJSON($retorno);
    }

    private function montaDiasEvento($evento): array
    {
        if (!$evento || empty($evento->data_inicio)) {
            return [];
        }
        $inicio = strtotime((string) $evento->data_inicio);
        $fim    = !empty($evento->data_fim) ? strtotime((string) $evento->data_fim) : $inicio;
        if ($fim < $inicio) {
            $fim = $inicio;
        }

        $dias = [];
        for ($t = $inicio; $t <= $fim; $t += 86400) {
            $dias[] = [
                'date'      => date('Y-m-d', $t),
                'formatado' => date('d/m/Y', $t) . ' (' . $this->diaSemana($t) . ')',
            ];
        }
        return $dias;
    }

    private function diaSemana(int $timestamp): string
    {
        $dias = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
        return $dias[(int) date('w', $timestamp)];
    }

    private function getTipos(): array
    {
        return ['Comum', 'VIP', 'Epic', 'Premium'];
    }
}
