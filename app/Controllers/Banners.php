<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BannerModel;
use App\Models\EventoModel;

class Banners extends BaseController
{
    protected $bannerModel;
    protected $eventoModel;

    public function __construct()
    {
        $this->bannerModel = new BannerModel();
        $this->eventoModel = new EventoModel();
    }

    public function index()
    {
        $eventId = $this->request->getGet('evento_id') ?? evento_selecionado();

        $eventos = $this->eventoModel->orderBy('data_inicio', 'DESC')->findAll();

        $evento  = null;
        $banners = [];

        if (!empty($eventId)) {
            $evento  = $this->eventoModel->find($eventId);
            $banners = $this->bannerModel->getByEvento((int) $eventId);
        }

        $data = [
            'titulo'             => 'Banners',
            'eventos'            => $eventos,
            'eventIdSelecionado' => $eventId,
            'evento'             => $evento,
            'banners'            => $banners,
        ];

        return view('Banners/index', $data);
    }

    public function salvar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno = ['token' => csrf_hash()];

        try {
            $post = $this->request->getPost();

            $dados = [
                'event_id' => $post['event_id'] ?? null,
                'link'     => trim((string) ($post['link'] ?? '')) ?: null,
                'ordem'    => (int) ($post['ordem'] ?? 0),
                'ativo'    => isset($post['ativo']) ? 1 : 0,
            ];

            $imagemAtual = null;
            if (!empty($post['id'])) {
                $atual = $this->bannerModel->find($post['id']);
                if ($atual) {
                    $imagemAtual = $atual->imagem;
                }
                $dados['id'] = $post['id'];
            }

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

                $arquivo->store('banners');
                $dados['imagem'] = $arquivo->getName();

                if ($imagemAtual && file_exists(WRITEPATH . 'uploads/banners/' . $imagemAtual)) {
                    @unlink(WRITEPATH . 'uploads/banners/' . $imagemAtual);
                }
            } elseif (empty($post['id'])) {
                $retorno['erro'] = 'A imagem é obrigatória.';
                return $this->response->setJSON($retorno);
            }

            if ($this->bannerModel->save($dados)) {
                $retorno['sucesso'] = !empty($post['id']) ? 'Banner atualizado!' : 'Banner criado!';
                $retorno['id']      = $post['id'] ?? $this->bannerModel->getInsertID();
                return $this->response->setJSON($retorno);
            }

            $retorno['erro']        = 'Erro ao salvar.';
            $retorno['erros_model'] = $this->bannerModel->errors();
            return $this->response->setJSON($retorno);

        } catch (\Throwable $e) {
            log_message('error', '[BANNERS/SALVAR] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
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

        $id   = $this->request->getPost('id');
        $item = $this->bannerModel->find($id);
        if (!$item) {
            $retorno['erro'] = 'Banner não encontrado.';
            return $this->response->setJSON($retorno);
        }

        if ($this->bannerModel->delete($id)) {
            if (!empty($item->imagem) && file_exists(WRITEPATH . 'uploads/banners/' . $item->imagem)) {
                @unlink(WRITEPATH . 'uploads/banners/' . $item->imagem);
            }
            $retorno['sucesso'] = 'Banner removido.';
            return $this->response->setJSON($retorno);
        }

        $retorno['erro'] = 'Erro ao remover.';
        return $this->response->setJSON($retorno);
    }

    public function reordenar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno = ['token' => csrf_hash()];

        $eventId = (int) $this->request->getPost('event_id');
        $ids     = $this->request->getPost('ids');

        if (!$eventId || !is_array($ids)) {
            $retorno['erro'] = 'Parâmetros inválidos.';
            return $this->response->setJSON($retorno);
        }

        $this->bannerModel->reordenar($eventId, $ids);
        $retorno['sucesso'] = 'Ordem atualizada.';
        return $this->response->setJSON($retorno);
    }

    public function imagem($imagem = null)
    {
        if ($imagem != null) {
            $this->exibeArquivo('banners', $imagem);
        }
    }
}
