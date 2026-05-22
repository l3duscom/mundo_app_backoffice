<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\LineupModel;
use App\Models\EventoModel;

class Lineup extends BaseController
{
    protected $lineupModel;
    protected $eventoModel;

    public function __construct()
    {
        $this->lineupModel = new LineupModel();
        $this->eventoModel = new EventoModel();
    }

    /**
     * Tela principal: gerencia line-up do evento selecionado
     */
    public function index()
    {
        $eventId = $this->request->getGet('evento_id') ?? evento_selecionado();

        $eventos = $this->eventoModel->orderBy('data_inicio', 'DESC')->findAll();

        $evento     = null;
        $lineup     = [];
        $dias       = [];
        $porDia     = [];

        if (!empty($eventId)) {
            $evento = $this->eventoModel->find($eventId);
            $lineup = $this->lineupModel->getByEvento((int) $eventId);
            $dias   = $this->montaDiasEvento($evento);

            foreach ($lineup as $item) {
                $chave = $item->dia ? date('Y-m-d', strtotime((string) $item->dia)) : 'sem_dia';
                $porDia[$chave][] = $item;
            }
        }

        $data = [
            'titulo'             => 'Line-up',
            'eventos'            => $eventos,
            'eventIdSelecionado' => $eventId,
            'evento'             => $evento,
            'lineup'             => $lineup,
            'dias'               => $dias,
            'porDia'             => $porDia,
            'tipos'              => $this->getTipos(),
        ];

        return view('Lineup/index', $data);
    }

    /**
     * Salvar atração (cria ou atualiza). Aceita upload de imagem.
     */
    public function salvar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $post = $this->request->getPost();

        $dados = [
            'event_id'  => $post['event_id'] ?? null,
            'nome'      => trim($post['nome'] ?? ''),
            'dia'       => !empty($post['dia']) ? $post['dia'] : null,
            'tipo'      => trim($post['tipo'] ?? ''),
            'descricao' => $post['descricao'] ?? null,
            'ordem'     => (int) ($post['ordem'] ?? 0),
            'ativo'     => isset($post['ativo']) ? 1 : 0,
        ];

        // Mantém imagem atual em edição (a menos que envie nova)
        $imagemAtual = null;
        if (!empty($post['id'])) {
            $atual = $this->lineupModel->find($post['id']);
            if ($atual) {
                $imagemAtual = $atual->imagem;
            }
            $dados['id'] = $post['id'];
        }

        // Processa upload
        $arquivo = $this->request->getFile('imagem');
        if ($arquivo && $arquivo->isValid() && !$arquivo->hasMoved()) {
            $validos = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!in_array($arquivo->getMimeType(), $validos)) {
                $retorno['erro'] = 'Formato de imagem inválido. Use JPG, PNG ou WEBP.';
                return $this->response->setJSON($retorno);
            }
            if ($arquivo->getSizeByUnit('mb') > 5) {
                $retorno['erro'] = 'Imagem muito grande. Máximo 5MB.';
                return $this->response->setJSON($retorno);
            }

            $arquivo->store('lineup');
            $dados['imagem'] = $arquivo->getName();

            // Apaga imagem antiga se houver
            if ($imagemAtual && file_exists(WRITEPATH . 'uploads/lineup/' . $imagemAtual)) {
                @unlink(WRITEPATH . 'uploads/lineup/' . $imagemAtual);
            }
        }

        if ($this->lineupModel->save($dados)) {
            $retorno['sucesso'] = !empty($post['id']) ? 'Line-up atualizado!' : 'Line-up criado!';
            $retorno['id']      = $post['id'] ?? $this->lineupModel->getInsertID();
            return $this->response->setJSON($retorno);
        }

        $retorno['erro']        = 'Erro ao salvar.';
        $retorno['erros_model'] = $this->lineupModel->errors();
        return $this->response->setJSON($retorno);
    }

    /**
     * Excluir atração (AJAX)
     */
    public function excluir()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $id = $this->request->getPost('id');
        $item = $this->lineupModel->find($id);
        if (!$item) {
            $retorno['erro'] = 'Atração não encontrada.';
            return $this->response->setJSON($retorno);
        }

        if ($this->lineupModel->delete($id)) {
            $retorno['sucesso'] = 'Atração removida.';
            return $this->response->setJSON($retorno);
        }

        $retorno['erro'] = 'Erro ao remover.';
        return $this->response->setJSON($retorno);
    }

    /**
     * Exibe imagem da atração
     */
    public function imagem($imagem = null)
    {
        if ($imagem != null) {
            $this->exibeArquivo('lineup', $imagem);
        }
    }

    /**
     * Gera lista de dias entre data_inicio e data_fim do evento
     */
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
        return ['Show', 'DJ', 'Banda', 'Atração', 'Palestrante', 'Workshop', 'Outros'];
    }
}
