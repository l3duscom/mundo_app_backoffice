<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\ContratoItem;

class ContratoItens extends BaseController
{

    private $contratoItemModel;
    private $contratoModel;

    public function __construct()
    {
        $this->contratoItemModel = new \App\Models\ContratoItemModel();
        $this->contratoModel = new \App\Models\ContratoModel();
    }

    /**
     * Lista itens do contrato (AJAX)
     */
    public function itens(int $contratoId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $itens = $this->contratoItemModel->buscaPorContrato($contratoId);

        // Busca o contrato para pegar o event_id
        $contrato = $this->contratoModel->find($contratoId);
        $eventId = $contrato ? $contrato->event_id : null;

        // Inicializa model de espaços
        $espacoModel = new \App\Models\EspacoModel();

        $data = [];

        foreach ($itens as $item) {
            // Busca espaço reservado por este item
            $espacoReservado = $espacoModel->buscaPorContratoItem($item->id);
            
            $data[] = [
                'id' => $item->id,
                'tipo_item' => $item->getBadgeTipoItem(),
                'tipo_item_raw' => $item->tipo_item,
                'descricao' => esc($item->descricao ?? '-'),
                'localizacao' => esc($item->localizacao ?? '-'),
                'localizacao_raw' => $item->localizacao ?? '',
                'espaco_id' => $espacoReservado ? $espacoReservado->id : null,
                'event_id' => $eventId,
                'metragem' => esc($item->metragem ?? '-'),
                'quantidade' => $item->quantidade,
                'valor_unitario' => $item->getValorUnitarioFormatado(),
                'valor_desconto' => $item->getValorDescontoFormatado(),
                'valor_total' => $item->getValorTotalFormatado(),
                'acoes' => $this->getBotoesAcao($item->id, $contratoId),
            ];
        }

        // Calcula totais
        $totais = $this->contratoItemModel->calculaTotaisContrato($contratoId);

        $retorno = [
            'data' => $data,
            'totais' => [
                'subtotal' => 'R$ ' . number_format($totais['subtotal'], 2, ',', '.'),
                'total_desconto' => 'R$ ' . number_format($totais['total_desconto'], 2, ',', '.'),
                'total' => 'R$ ' . number_format($totais['total'], 2, ',', '.'),
                'quantidade_itens' => $totais['quantidade_itens'],
            ],
        ];

        return $this->response->setJSON($retorno);
    }

    /**
     * Adiciona item ao contrato
     */
    public function adicionar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        try {
            $post = $this->request->getPost();

            // Valida contrato
            $contrato = $this->contratoModel->find($post['contrato_id']);
            if (!$contrato) {
                $retorno['erro'] = 'Contrato não encontrado';
                return $this->response->setJSON($retorno);
            }

            // Limpar valores monetários
            $valorUnitario = $this->limparValorMonetario($post['valor_unitario'] ?? '0');
            $quantidade = (int)($post['quantidade'] ?? 1);
            
            // O valor_desconto já vem calculado do JavaScript (não precisa limpar formatação)
            $valorDesconto = (float)($post['valor_desconto'] ?? 0);

            // Calcular valor total
            $valorTotal = ($quantidade * $valorUnitario) - $valorDesconto;

            $item = new ContratoItem([
                'contrato_id'    => $post['contrato_id'],
                'tipo_item'      => $post['tipo_item'],
                'descricao'      => $post['descricao'] ?? null,
                'localizacao'    => $post['localizacao'] ?? null,
                'metragem'       => $post['metragem'] ?? null,
                'quantidade'     => $quantidade,
                'valor_unitario' => $valorUnitario,
                'valor_desconto' => $valorDesconto,
                'valor_total'    => $valorTotal,
                'observacoes'    => $post['observacoes'] ?? null,
            ]);

            if ($this->contratoItemModel->save($item)) {
                // Atualiza totais do contrato
                $this->contratoItemModel->atualizaTotaisContrato($post['contrato_id']);

                $retorno['sucesso'] = 'Item adicionado com sucesso!';
                $retorno['id'] = $this->contratoItemModel->getInsertID();

                return $this->response->setJSON($retorno);
            }

            $retorno['erro'] = 'Erro ao adicionar item';
            $retorno['erros_model'] = $this->contratoItemModel->errors();

            return $this->response->setJSON($retorno);

        } catch (\Exception $e) {
            $retorno['erro'] = 'Exceção: ' . $e->getMessage();
            $retorno['trace'] = $e->getTraceAsString();
            return $this->response->setJSON($retorno);
        }
    }

    /**
     * Atualiza item do contrato
     */
    public function atualizar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $post = $this->request->getPost();

        $item = $this->contratoItemModel->find($post['id']);
        if (!$item) {
            $retorno['erro'] = 'Item não encontrado';
            return $this->response->setJSON($retorno);
        }

        // Limpar valores monetários
        $valorUnitario = $this->limparValorMonetario($post['valor_unitario'] ?? '0');
        $quantidade = (int)($post['quantidade'] ?? 1);
        
        // O valor_desconto já vem calculado do JavaScript (não precisa limpar formatação)
        $valorDesconto = (float)($post['valor_desconto'] ?? 0);

        // Calcular valor total
        $valorTotal = ($quantidade * $valorUnitario) - $valorDesconto;

        $item->fill([
            'tipo_item'      => $post['tipo_item'],
            'descricao'      => $post['descricao'] ?? null,
            'localizacao'    => $post['localizacao'] ?? null,
            'metragem'       => $post['metragem'] ?? null,
            'quantidade'     => $quantidade,
            'valor_unitario' => $valorUnitario,
            'valor_desconto' => $valorDesconto,
            'valor_total'    => $valorTotal,
            'observacoes'    => $post['observacoes'] ?? null,
        ]);

        if ($this->contratoItemModel->save($item)) {
            // Atualiza totais do contrato
            $this->contratoItemModel->atualizaTotaisContrato($item->contrato_id);

            $retorno['sucesso'] = 'Item atualizado com sucesso!';

            return $this->response->setJSON($retorno);
        }

        $retorno['erro'] = 'Erro ao atualizar item';
        $retorno['erros_model'] = $this->contratoItemModel->errors();

        return $this->response->setJSON($retorno);
    }

    /**
     * Remove item do contrato
     */
    public function remover(int $id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $item = $this->contratoItemModel->find($id);
        if (!$item) {
            $retorno['erro'] = 'Item não encontrado';
            return $this->response->setJSON($retorno);
        }

        $contratoId = $item->contrato_id;

        if ($this->contratoItemModel->delete($id)) {
            // Atualiza totais do contrato
            $this->contratoItemModel->atualizaTotaisContrato($contratoId);

            $retorno['sucesso'] = 'Item removido com sucesso!';

            return $this->response->setJSON($retorno);
        }

        $retorno['erro'] = 'Erro ao remover item';

        return $this->response->setJSON($retorno);
    }

    /**
     * Busca dados de um item para edição
     */
    public function buscar(int $id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $item = $this->contratoItemModel->find($id);
        if (!$item) {
            return $this->response->setJSON(['erro' => 'Item não encontrado']);
        }

        return $this->response->setJSON([
            'id'             => $item->id,
            'contrato_id'    => $item->contrato_id,
            'tipo_item'      => $item->tipo_item,
            'descricao'      => $item->descricao,
            'localizacao'    => $item->localizacao,
            'metragem'       => $item->metragem,
            'quantidade'     => $item->quantidade,
            'valor_unitario' => number_format($item->valor_unitario, 2, ',', '.'),
            'valor_desconto' => number_format($item->valor_desconto, 2, ',', '.'),
            'observacoes'    => $item->observacoes,
        ]);
    }

    /**
     * Retorna os tipos de itens disponíveis
     */
    public function tiposItem()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        return $this->response->setJSON(\App\Models\ContratoItemModel::getTiposItem());
    }

    /**
     * Gera os botões de ação para cada item
     */
    private function getBotoesAcao(int $itemId, int $contratoId): string
    {
        return '
            <button type="button" class="btn btn-sm btn-outline-primary btn-editar-item" data-id="' . $itemId . '" title="Editar">
                <i class="bx bx-edit-alt"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger btn-remover-item" data-id="' . $itemId . '" title="Remover">
                <i class="bx bx-trash"></i>
            </button>
        ';
    }

    /**
     * Limpa valor monetário
     */
    private function limparValorMonetario(string $valor): float
    {
        $valor = preg_replace('/[R$\s]/', '', $valor);
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
        
        return (float)$valor;
    }
}

