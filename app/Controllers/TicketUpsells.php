<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\TicketUpsell;
use App\Models\TicketUpsellModel;
use App\Models\TicketModel;

class TicketUpsells extends BaseController
{
    protected $upsellModel;
    protected $ticketModel;

    public function __construct()
    {
        $this->upsellModel = new TicketUpsellModel();
        $this->ticketModel = new TicketModel();
    }

    /**
     * Listagem de upsells
     */
    public function index()
    {
        $eventoId = evento_selecionado();

        if (!$eventoId) {
            return redirect()->to('/eventos')->with('erro', 'Selecione um evento primeiro');
        }

        $data = [
            'titulo' => 'Upsells de Ingressos',
            'upsells' => $this->upsellModel->buscaPorEvento($eventoId),
        ];

        return view('TicketUpsells/index', $data);
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
            'titulo' => 'Novo Upsell',
            'upsell' => new TicketUpsell(),
            'tickets' => $this->buscaTicketsEvento($eventoId),
        ];

        return view('TicketUpsells/criar', $data);
    }

    /**
     * Processa cadastro
     */
    public function cadastrar()
    {
        $isAjax = $this->request->isAJAX();
        $eventoId = evento_selecionado();

        // Valida se origem != destino
        if ($this->request->getPost('ticket_origem_id') == $this->request->getPost('ticket_destino_id')) {
            $erro = 'O ticket de origem e destino não podem ser iguais';
            if ($isAjax) {
                return $this->response->setJSON(['token' => csrf_hash(), 'erro' => $erro]);
            }
            return redirect()->back()->withInput()->with('erro', $erro);
        }

        // Verifica se já existe
        if ($this->upsellModel->existeUpsell(
            (int) $this->request->getPost('ticket_origem_id'),
            (int) $this->request->getPost('ticket_destino_id')
        )) {
            $erro = 'Já existe um upsell para esta combinação de tickets';
            if ($isAjax) {
                return $this->response->setJSON(['token' => csrf_hash(), 'erro' => $erro]);
            }
            return redirect()->back()->withInput()->with('erro', $erro);
        }

        $postData = $this->request->getPost();
        $postData['event_id'] = $eventoId;

        // Converte valor customizado
        if (!empty($postData['valor_customizado'])) {
            $postData['valor_customizado'] = $this->converteValorMonetario($postData['valor_customizado']);
        } else {
            $postData['valor_customizado'] = null;
        }

        $result = $this->upsellModel->salvarComCalculo($postData);

        if (!$result) {
            $erro = 'Erro ao salvar upsell';
            if ($isAjax) {
                return $this->response->setJSON(['token' => csrf_hash(), 'erro' => $erro]);
            }
            return redirect()->back()->withInput()->with('erro', $erro);
        }

        if ($isAjax) {
            return $this->response->setJSON([
                'token' => csrf_hash(),
                'sucesso' => 'Upsell cadastrado com sucesso!',
                'redirect' => site_url('ticket-upsells')
            ]);
        }

        return redirect()->to('ticket-upsells')->with('sucesso', 'Upsell cadastrado com sucesso!');
    }

    /**
     * Formulário de edição
     */
    public function editar(int $id = null)
    {
        $upsell = $this->buscaOu404($id);
        $eventoId = evento_selecionado();

        $data = [
            'titulo' => 'Editar Upsell',
            'upsell' => $upsell,
            'tickets' => $this->buscaTicketsEvento($eventoId),
        ];

        return view('TicketUpsells/editar', $data);
    }

    /**
     * Processa atualização
     */
    public function atualizar()
    {
        $isAjax = $this->request->isAJAX();
        $id = $this->request->getPost('id');
        $upsell = $this->buscaOu404($id);

        // Valida se origem != destino
        if ($this->request->getPost('ticket_origem_id') == $this->request->getPost('ticket_destino_id')) {
            $erro = 'O ticket de origem e destino não podem ser iguais';
            if ($isAjax) {
                return $this->response->setJSON(['token' => csrf_hash(), 'erro' => $erro]);
            }
            return redirect()->back()->withInput()->with('erro', $erro);
        }

        // Verifica duplicidade
        if ($this->upsellModel->existeUpsell(
            (int) $this->request->getPost('ticket_origem_id'),
            (int) $this->request->getPost('ticket_destino_id'),
            $id
        )) {
            $erro = 'Já existe um upsell para esta combinação de tickets';
            if ($isAjax) {
                return $this->response->setJSON(['token' => csrf_hash(), 'erro' => $erro]);
            }
            return redirect()->back()->withInput()->with('erro', $erro);
        }

        $postData = $this->request->getPost();

        // Converte valor customizado
        if (!empty($postData['valor_customizado'])) {
            $postData['valor_customizado'] = $this->converteValorMonetario($postData['valor_customizado']);
        } else {
            $postData['valor_customizado'] = null;
        }

        $result = $this->upsellModel->salvarComCalculo($postData);

        if (!$result) {
            $erro = 'Erro ao atualizar upsell';
            if ($isAjax) {
                return $this->response->setJSON(['token' => csrf_hash(), 'erro' => $erro]);
            }
            return redirect()->back()->withInput()->with('erro', $erro);
        }

        if ($isAjax) {
            return $this->response->setJSON([
                'token' => csrf_hash(),
                'sucesso' => 'Upsell atualizado com sucesso!',
                'redirect' => site_url('ticket-upsells')
            ]);
        }

        return redirect()->to('ticket-upsells')->with('sucesso', 'Upsell atualizado com sucesso!');
    }

    /**
     * Excluir upsell
     */
    public function excluir(int $id = null)
    {
        $upsell = $this->buscaOu404($id);

        if (!$this->upsellModel->delete($id)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['token' => csrf_hash(), 'erro' => 'Erro ao excluir']);
            }
            return redirect()->back()->with('erro', 'Erro ao excluir upsell');
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'token' => csrf_hash(),
                'sucesso' => 'Upsell excluído com sucesso!'
            ]);
        }

        return redirect()->to('ticket-upsells')->with('sucesso', 'Upsell excluído com sucesso!');
    }

    /**
     * API: Calcula diferença entre dois tickets (AJAX)
     */
    public function calcularDiferenca()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['erro' => 'Acesso inválido']);
        }

        $origemId = (int) $this->request->getGet('origem');
        $destinoId = (int) $this->request->getGet('destino');

        if (!$origemId || !$destinoId) {
            return $this->response->setJSON(['token' => csrf_hash(), 'diferenca' => 0, 'formatado' => 'R$ 0,00']);
        }

        $diferenca = $this->upsellModel->calcularDiferenca($origemId, $destinoId);

        return $this->response->setJSON([
            'token' => csrf_hash(),
            'diferenca' => $diferenca,
            'formatado' => 'R$ ' . number_format($diferenca, 2, ',', '.')
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
        if (!$id || !$upsell = $this->upsellModel->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Upsell não encontrado');
        }
        return $upsell;
    }

    /**
     * Converte valor monetário brasileiro para float
     */
    private function converteValorMonetario(?string $valor): ?float
    {
        if (empty($valor)) return null;
        
        $valor = preg_replace('/[^\d,.]/', '', $valor);
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
        
        return (float) $valor;
    }
}
