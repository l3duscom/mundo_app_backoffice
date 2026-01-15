<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\OrderBump;
use App\Models\OrderBumpModel;
use App\Models\TicketModel;

class OrderBumps extends BaseController
{
    protected $orderBumpModel;
    protected $ticketModel;

    public function __construct()
    {
        $this->orderBumpModel = new OrderBumpModel();
        $this->ticketModel = new TicketModel();
    }

    /**
     * Listagem de order bumps
     */
    public function index()
    {
        $eventoId = evento_selecionado();

        if (!$eventoId) {
            return redirect()->to('/eventos')->with('erro', 'Selecione um evento primeiro');
        }

        $data = [
            'titulo' => 'Order Bumps',
            'orderBumps' => $this->orderBumpModel->buscaPorEvento($eventoId),
        ];

        return view('OrderBumps/index', $data);
    }

    /**
     * Formulário de criação
     */
    public function criar()
    {
        $eventoId = evento_selecionado();

        if (!$eventoId) {
            return redirect()->to('/eventos')->with('erro', 'Selecione um evento primeiro');
        }

        $data = [
            'titulo' => 'Novo Order Bump',
            'orderBump' => new OrderBump(),
            'tickets' => $this->buscaTicketsEvento($eventoId),
        ];

        return view('OrderBumps/criar', $data);
    }

    /**
     * Processa cadastro
     */
    public function cadastrar()
    {
        $isAjax = $this->request->isAJAX();
        $eventoId = evento_selecionado();

        if (!$eventoId) {
            $erro = 'Selecione um evento primeiro';
            if ($isAjax) {
                return $this->response->setJSON(['token' => csrf_hash(), 'erro' => $erro]);
            }
            return redirect()->to('/eventos')->with('erro', $erro);
        }

        $postData = $this->request->getPost();
        $postData['event_id'] = $eventoId;

        // Converte preço
        $postData['preco'] = $this->converteValorMonetario($postData['preco'] ?? '0');

        // Ticket vazio = null
        if (empty($postData['ticket_id'])) {
            $postData['ticket_id'] = null;
        }

        // Estoque vazio = ilimitado (null)
        if ($postData['estoque'] === '' || $postData['estoque'] === null) {
            $postData['estoque'] = null;
        }

        // Verifica nome duplicado
        if ($this->orderBumpModel->nomeExiste($postData['nome'], $eventoId)) {
            $erro = 'Já existe um order bump com este nome neste evento';
            if ($isAjax) {
                return $this->response->setJSON(['token' => csrf_hash(), 'erro' => $erro]);
            }
            return redirect()->back()->withInput()->with('erro', $erro);
        }

        // Processa upload de imagem
        $imagem = $this->request->getFile('imagem');
        if ($imagem !== null && $imagem->isValid() && !$imagem->hasMoved() && $imagem->getSize() > 0) {
            // Cria diretório se não existir
            $uploadPath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'order_bumps';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            $novoNome = $imagem->getRandomName();
            try {
                if ($imagem->move($uploadPath, $novoNome)) {
                    $postData['imagem'] = $novoNome;
                }
            } catch (\Exception $e) {
                log_message('error', 'Erro no upload de imagem: ' . $e->getMessage());
            }
        }


        if (!$this->orderBumpModel->insert($postData)) {
            $erro = 'Erro ao salvar order bump';
            $errosModel = $this->orderBumpModel->errors();
            if ($isAjax) {
                return $this->response->setJSON(['token' => csrf_hash(), 'erro' => $erro, 'erros_model' => $errosModel]);
            }
            return redirect()->back()->withInput()->with('erro', $erro)->with('erros_model', $errosModel);
        }

        if ($isAjax) {
            return $this->response->setJSON([
                'token' => csrf_hash(),
                'sucesso' => 'Order Bump cadastrado com sucesso!',
                'redirect' => site_url('order-bumps')
            ]);
        }

        return redirect()->to('order-bumps')->with('sucesso', 'Order Bump cadastrado com sucesso!');
    }

    /**
     * Formulário de edição
     */
    public function editar(int $id = null)
    {
        $orderBump = $this->buscaOu404($id);
        $eventoId = evento_selecionado();

        $data = [
            'titulo' => 'Editar Order Bump',
            'orderBump' => $orderBump,
            'tickets' => $this->buscaTicketsEvento($eventoId),
        ];

        return view('OrderBumps/editar', $data);
    }

    /**
     * Processa atualização
     */
    public function atualizar()
    {
        $isAjax = $this->request->isAJAX();
        $id = $this->request->getPost('id');
        $orderBump = $this->buscaOu404($id);
        $eventoId = evento_selecionado();

        $postData = $this->request->getPost();

        // Converte preço
        $postData['preco'] = $this->converteValorMonetario($postData['preco'] ?? '0');

        // Ticket vazio = null
        if (empty($postData['ticket_id'])) {
            $postData['ticket_id'] = null;
        }

        // Estoque vazio = ilimitado (null)
        if ($postData['estoque'] === '' || $postData['estoque'] === null) {
            $postData['estoque'] = null;
        }

        // Verifica nome duplicado (exceto o atual)
        if ($this->orderBumpModel->nomeExiste($postData['nome'], $eventoId, $id)) {
            $erro = 'Já existe um order bump com este nome neste evento';
            if ($isAjax) {
                return $this->response->setJSON(['token' => csrf_hash(), 'erro' => $erro]);
            }
            return redirect()->back()->withInput()->with('erro', $erro);
        }

        // Processa upload de imagem
        $imagem = $this->request->getFile('imagem');
        if ($imagem && $imagem->isValid() && !$imagem->hasMoved()) {
            // Remove imagem antiga
            if ($orderBump->imagem && file_exists(FCPATH . 'uploads/order_bumps/' . $orderBump->imagem)) {
                unlink(FCPATH . 'uploads/order_bumps/' . $orderBump->imagem);
            }
            
            $novoNome = $imagem->getRandomName();
            $imagem->move(FCPATH . 'uploads/order_bumps', $novoNome);
            $postData['imagem'] = $novoNome;
        }

        if (!$this->orderBumpModel->update($id, $postData)) {
            $erro = 'Erro ao atualizar order bump';
            $errosModel = $this->orderBumpModel->errors();
            if ($isAjax) {
                return $this->response->setJSON(['token' => csrf_hash(), 'erro' => $erro, 'erros_model' => $errosModel]);
            }
            return redirect()->back()->withInput()->with('erro', $erro)->with('erros_model', $errosModel);
        }

        if ($isAjax) {
            return $this->response->setJSON([
                'token' => csrf_hash(),
                'sucesso' => 'Order Bump atualizado com sucesso!',
                'redirect' => site_url('order-bumps')
            ]);
        }

        return redirect()->to('order-bumps')->with('sucesso', 'Order Bump atualizado com sucesso!');
    }

    /**
     * Excluir order bump (soft delete)
     */
    public function excluir(int $id = null)
    {
        $orderBump = $this->buscaOu404($id);

        if (!$this->orderBumpModel->delete($id)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['token' => csrf_hash(), 'erro' => 'Erro ao excluir']);
            }
            return redirect()->back()->with('erro', 'Erro ao excluir order bump');
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'token' => csrf_hash(),
                'sucesso' => 'Order Bump excluído com sucesso!'
            ]);
        }

        return redirect()->to('order-bumps')->with('sucesso', 'Order Bump excluído com sucesso!');
    }

    /**
     * Alterar status (ativo/inativo) via AJAX
     */
    public function alterarStatus()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['erro' => 'Acesso inválido']);
        }

        $id = $this->request->getPost('id');
        $orderBump = $this->buscaOu404($id);

        $novoStatus = $orderBump->ativo ? 0 : 1;

        if (!$this->orderBumpModel->update($id, ['ativo' => $novoStatus])) {
            return $this->response->setJSON(['token' => csrf_hash(), 'erro' => 'Erro ao alterar status']);
        }

        return $this->response->setJSON([
            'token' => csrf_hash(),
            'sucesso' => 'Status alterado com sucesso!',
            'novo_status' => $novoStatus,
            'badge' => $novoStatus ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-secondary">Inativo</span>'
        ]);
    }

    /**
     * Upload de imagem via AJAX
     */
    public function uploadImagem()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['erro' => 'Acesso inválido']);
        }

        $imagem = $this->request->getFile('imagem');
        
        if (!$imagem || !$imagem->isValid()) {
            return $this->response->setJSON(['token' => csrf_hash(), 'erro' => 'Arquivo inválido']);
        }

        // Valida tipo
        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($imagem->getMimeType(), $tiposPermitidos)) {
            return $this->response->setJSON(['token' => csrf_hash(), 'erro' => 'Tipo de arquivo não permitido. Use JPG, PNG, GIF ou WebP.']);
        }

        // Valida tamanho (máx 5MB)
        if ($imagem->getSize() > 5 * 1024 * 1024) {
            return $this->response->setJSON(['token' => csrf_hash(), 'erro' => 'Arquivo muito grande. Máximo 5MB.']);
        }

        $novoNome = $imagem->getRandomName();
        
        // Cria diretório se não existir
        if (!is_dir(FCPATH . 'uploads/order_bumps')) {
            mkdir(FCPATH . 'uploads/order_bumps', 0755, true);
        }

        $imagem->move(FCPATH . 'uploads/order_bumps', $novoNome);

        return $this->response->setJSON([
            'token' => csrf_hash(),
            'sucesso' => 'Imagem enviada com sucesso!',
            'arquivo' => $novoNome,
            'url' => site_url('uploads/order_bumps/' . $novoNome)
        ]);
    }

    /**
     * Remove imagem via AJAX
     */
    public function removerImagem()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['erro' => 'Acesso inválido']);
        }

        $id = $this->request->getPost('id');
        $orderBump = $this->buscaOu404($id);

        if ($orderBump->imagem && file_exists(FCPATH . 'uploads/order_bumps/' . $orderBump->imagem)) {
            unlink(FCPATH . 'uploads/order_bumps/' . $orderBump->imagem);
        }

        $this->orderBumpModel->update($id, ['imagem' => null]);

        return $this->response->setJSON([
            'token' => csrf_hash(),
            'sucesso' => 'Imagem removida com sucesso!'
        ]);
    }

    /**
     * Busca tickets do evento
     */
    private function buscaTicketsEvento(int $eventoId): array
    {
        return $this->ticketModel
            ->where('event_id', $eventoId)
            ->where('ativo', 1)
            ->orderBy('nome', 'ASC')
            ->findAll();
    }

    /**
     * Busca ou 404
     */
    private function buscaOu404(int $id = null)
    {
        if (!$id || !$orderBump = $this->orderBumpModel->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Order Bump não encontrado');
        }
        return $orderBump;
    }

    /**
     * Converte valor monetário brasileiro para float
     */
    private function converteValorMonetario(?string $valor): ?float
    {
        if (empty($valor)) return 0;
        
        $valor = preg_replace('/[^\d,.]/', '', $valor);
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
        
        return (float) $valor;
    }
}
