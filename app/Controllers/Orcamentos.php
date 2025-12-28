<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\Orcamento;
use App\Models\OrcamentoModel;
use App\Models\OrcamentoItemModel;
use App\Models\OrcamentoAnexoModel;
use App\Models\FornecedorModel;
use App\Models\LancamentoFinanceiroModel;

class Orcamentos extends BaseController
{
    protected $orcamentoModel;
    protected $itemModel;
    protected $anexoModel;
    protected $fornecedorModel;

    public function __construct()
    {
        $this->orcamentoModel = new OrcamentoModel();
        $this->itemModel = new OrcamentoItemModel();
        $this->anexoModel = new OrcamentoAnexoModel();
        $this->fornecedorModel = new FornecedorModel();
    }

    public function index()
    {
        $eventoId = $this->request->getGet('evento_id') ?? evento_selecionado();
        
        if (!$eventoId) {
            return redirect()->to('eventos')->with('atencao', 'Selecione um evento primeiro.');
        }

        $data = [
            'titulo' => 'Orçamentos',
            'evento_id' => $eventoId,
        ];

        return view('Orcamentos/index', $data);
    }

    /**
     * Recupera orçamentos para DataTable (AJAX)
     */
    public function recuperaOrcamentos()
    {
        $situacao = $this->request->getGet('situacao');
        $fornecedor_id = $this->request->getGet('fornecedor_id');
        $evento_id = $this->request->getGet('evento_id') ?? evento_selecionado();

        $builder = $this->orcamentoModel
            ->select('orcamentos.*, fornecedores.razao as fornecedor_nome, eventos.nome as evento_nome')
            ->join('fornecedores', 'fornecedores.id = orcamentos.fornecedor_id', 'left')
            ->join('eventos', 'eventos.id = orcamentos.event_id', 'left')
            ->withDeleted(true);

        // Filtrar pelo evento selecionado
        if (!empty($evento_id)) {
            $builder->where('orcamentos.event_id', $evento_id);
        }

        if (!empty($situacao)) {
            $builder->where('orcamentos.situacao', $situacao);
        }

        if (!empty($fornecedor_id)) {
            $builder->where('orcamentos.fornecedor_id', $fornecedor_id);
        }

        $orcamentos = $builder->orderBy('orcamentos.id', 'DESC')->findAll();

        $data = [];
        foreach ($orcamentos as $orc) {
            $data[] = [
                'codigo' => anchor("orcamentos/exibir/$orc->id", esc($orc->codigo), 'title="Ver orçamento"'),
                'titulo' => esc($orc->titulo),
                'fornecedor' => esc($orc->fornecedor_nome ?? '-'),
                'valor_final' => 'R$ ' . number_format($orc->valor_final, 2, ',', '.'),
                'situacao' => $orc->exibeSituacao(),
                'data' => $orc->created_at ? $orc->created_at->toLocalizedString('dd/MM/yyyy') : '-',
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    /**
     * Formulário de criação
     */
    public function criar()
    {
        $eventoId = $this->request->getGet('evento_id') ?? evento_selecionado();
        
        if (!$eventoId) {
            return redirect()->to('eventos')->with('atencao', 'Selecione um evento primeiro.');
        }

        $orcamento = new Orcamento();
        $orcamento->event_id = $eventoId;

        $data = [
            'titulo' => 'Novo Orçamento',
            'orcamento' => $orcamento,
            'evento_id' => $eventoId,
            'fornecedores' => $this->fornecedorModel->where('ativo', 1)->orderBy('razao')->findAll(),
        ];

        return view('Orcamentos/criar', $data);
    }

    /**
     * Cadastra novo orçamento
     */
    public function cadastrar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $orcamento = new Orcamento($this->request->getPost());
        $orcamento->codigo = $this->orcamentoModel->gerarCodigo();
        $orcamento->situacao = 'rascunho';

        if (!$this->orcamentoModel->save($orcamento)) {
            $retorno['erro'] = 'Verifique os erros e tente novamente';
            $retorno['erros_model'] = $this->orcamentoModel->errors();
            return $this->response->setJSON($retorno);
        }

        $orcamentoId = $this->orcamentoModel->getInsertID();

        // Salvar itens se houver
        $itens = $this->request->getPost('itens');
        if (!empty($itens)) {
            $this->salvarItens($orcamentoId, $itens);
        }

        session()->setFlashdata('sucesso', 'Orçamento cadastrado com sucesso!');
        $retorno['id'] = $orcamentoId;
        $retorno['redirect'] = site_url("orcamentos/exibir/$orcamentoId");

        return $this->response->setJSON($retorno);
    }

    /**
     * Exibir orçamento
     */
    public function exibir(int $id = null)
    {
        $orcamento = $this->buscaOrcamentoOu404($id);
        $orcamento = $this->orcamentoModel->buscaOrcamentoCompleto($id);

        $parcelaModel = new \App\Models\OrcamentoParcelaModel();

        $data = [
            'titulo' => 'Orçamento ' . $orcamento->codigo,
            'orcamento' => $orcamento,
            'itens' => $this->itemModel->buscarPorOrcamento($id),
            'anexos' => $this->anexoModel->buscarPorOrcamento($id),
            'parcelas' => $parcelaModel->buscaPorOrcamento($id),
            'totaisParcelas' => $parcelaModel->calculaTotais($id),
        ];

        return view('Orcamentos/exibir', $data);
    }

    /**
     * Formulário de edição
     */
    public function editar(int $id = null)
    {
        $orcamento = $this->buscaOrcamentoOu404($id);

        if (!$orcamento->podeEditar()) {
            return redirect()->to("orcamentos/exibir/$id")
                ->with('atencao', 'Este orçamento não pode mais ser editado.');
        }

        $data = [
            'titulo' => 'Editar Orçamento ' . $orcamento->codigo,
            'orcamento' => $orcamento,
            'itens' => $this->itemModel->buscarPorOrcamento($id),
            'fornecedores' => $this->fornecedorModel->where('ativo', 1)->orderBy('razao')->findAll(),
        ];

        return view('Orcamentos/editar', $data);
    }

    /**
     * Atualiza orçamento
     */
    public function atualizar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $id = $this->request->getPost('id');
        $orcamento = $this->buscaOrcamentoOu404($id);

        if (!$orcamento->podeEditar()) {
            $retorno['erro'] = 'Este orçamento não pode mais ser editado';
            return $this->response->setJSON($retorno);
        }

        $orcamento->fill($this->request->getPost());

        if (!$this->orcamentoModel->save($orcamento)) {
            $retorno['erro'] = 'Verifique os erros e tente novamente';
            $retorno['erros_model'] = $this->orcamentoModel->errors();
            return $this->response->setJSON($retorno);
        }

        // Atualizar itens
        $itens = $this->request->getPost('itens');
        $this->atualizarItens($id, $itens);

        session()->setFlashdata('sucesso', 'Orçamento atualizado com sucesso!');
        $retorno['redirect'] = site_url("orcamentos/exibir/$id");

        return $this->response->setJSON($retorno);
    }

    /**
     * Altera situação do orçamento
     */
    public function alterarSituacao()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $id = $this->request->getPost('id');
        $novaSituacao = $this->request->getPost('situacao');

        $orcamento = $this->buscaOrcamentoOu404($id);
        $situacaoAnterior = $orcamento->situacao;

        // Validar transição
        $transicoesValidas = [
            'rascunho' => ['enviado', 'cancelado'],
            'enviado' => ['aprovado', 'rascunho', 'cancelado'],
            'aprovado' => ['em_andamento', 'cancelado'],
            'em_andamento' => ['concluido', 'cancelado'],
            'concluido' => [],
            'cancelado' => ['rascunho'],
        ];

        if (!in_array($novaSituacao, $transicoesValidas[$situacaoAnterior] ?? [])) {
            $retorno['erro'] = 'Transição de situação não permitida';
            return $this->response->setJSON($retorno);
        }

        $dataUpdate = ['situacao' => $novaSituacao];

        // Se aprovando, registrar data, criar lançamento financeiro e gerar parcelas
        if ($novaSituacao === 'aprovado') {
            $dataUpdate['data_aprovacao'] = date('Y-m-d');
            
            // Gerar parcelas (lançamento financeiro será criado ao pagar cada parcela)
            $parcelaModel = new \App\Models\OrcamentoParcelaModel();
            $parcelaModel->gerarParcelas(
                $orcamento->id,
                $orcamento->quantidade_parcelas ?? 1,
                $orcamento->valor_final,
                date('Y-m-d')
            );
        }

        $this->orcamentoModel->update($id, $dataUpdate);

        $retorno['sucesso'] = 'Situação alterada com sucesso!';
        return $this->response->setJSON($retorno);
    }

    /**
     * Upload de anexo
     */
    public function uploadAnexo()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $orcamentoId = $this->request->getPost('orcamento_id');
        $file = $this->request->getFile('arquivo');

        if (!$file || !$file->isValid()) {
            $retorno['erro'] = 'Arquivo inválido';
            return $this->response->setJSON($retorno);
        }

        // Validar tipo
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getClientMimeType(), $allowedTypes)) {
            $retorno['erro'] = 'Tipo de arquivo não permitido. Use PDF ou imagens.';
            return $this->response->setJSON($retorno);
        }

        // Validar tamanho (10MB)
        if ($file->getSize() > 10485760) {
            $retorno['erro'] = 'Arquivo muito grande. Máximo 10MB.';
            return $this->response->setJSON($retorno);
        }

        $anexoId = $this->anexoModel->salvarAnexo($orcamentoId, $file);

        if (!$anexoId) {
            $retorno['erro'] = 'Erro ao salvar anexo';
            return $this->response->setJSON($retorno);
        }

        $retorno['sucesso'] = 'Anexo enviado com sucesso!';
        $retorno['anexo_id'] = $anexoId;
        return $this->response->setJSON($retorno);
    }

    /**
     * Remove anexo
     */
    public function removerAnexo()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $anexoId = $this->request->getPost('anexo_id');

        if (!$this->anexoModel->removerAnexo($anexoId)) {
            $retorno['erro'] = 'Erro ao remover anexo';
            return $this->response->setJSON($retorno);
        }

        $retorno['sucesso'] = 'Anexo removido com sucesso!';
        return $this->response->setJSON($retorno);
    }

    /**
     * Download de anexo
     */
    public function downloadAnexo(int $id = null)
    {
        $anexo = $this->anexoModel->find($id);

        if (!$anexo) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Anexo não encontrado');
        }

        $path = WRITEPATH . 'uploads/orcamentos/anexos/' . $anexo->arquivo;

        if (!is_file($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Arquivo não encontrado');
        }

        return $this->response->download($path, null)->setFileName($anexo->nome_arquivo);
    }

    /**
     * Visualizar anexo (abrir no navegador)
     */
    public function visualizarAnexo(int $id = null)
    {
        $anexo = $this->anexoModel->find($id);

        if (!$anexo) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Anexo não encontrado');
        }

        $path = WRITEPATH . 'uploads/orcamentos/anexos/' . $anexo->arquivo;

        if (!is_file($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Arquivo não encontrado');
        }

        $mime = $anexo->tipo ?? mime_content_type($path);
        
        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . $anexo->nome_arquivo . '"')
            ->setBody(file_get_contents($path));
    }

    /**
     * Busca fornecedores (AJAX)
     */
    public function buscaFornecedores()
    {
        $termo = $this->request->getGet('q');

        $fornecedores = $this->fornecedorModel
            ->like('razao', $termo)
            ->orLike('cnpj', $termo)
            ->where('ativo', 1)
            ->orderBy('razao')
            ->findAll(20);

        $result = [];
        foreach ($fornecedores as $f) {
            $result[] = [
                'id' => $f->id,
                'text' => $f->razao . ' - ' . $f->cnpj,
            ];
        }

        return $this->response->setJSON(['results' => $result]);
    }

    /**
     * Excluir orçamento
     */
    public function excluir(int $id = null)
    {
        $orcamento = $this->buscaOrcamentoOu404($id);

        if ($orcamento->situacao === 'concluido') {
            return redirect()->back()->with('atencao', 'Orçamentos concluídos não podem ser excluídos.');
        }

        $this->orcamentoModel->delete($id);

        return redirect()->to('orcamentos')->with('sucesso', 'Orçamento excluído com sucesso!');
    }

    // ========== MÉTODOS PRIVADOS ==========

    /**
     * Salva itens do orçamento
     */
    private function salvarItens(int $orcamentoId, array $itens): void
    {
        foreach ($itens as $item) {
            if (empty($item['descricao'])) continue;

            $quantidade = (float) str_replace(',', '.', $item['quantidade'] ?? 1);
            $valorUnitario = $this->limparValor($item['valor_unitario'] ?? 0);

            $this->itemModel->insert([
                'orcamento_id' => $orcamentoId,
                'descricao' => $item['descricao'],
                'quantidade' => $quantidade,
                'valor_unitario' => $valorUnitario,
                'valor_total' => $quantidade * $valorUnitario,
                'observacoes' => $item['observacoes'] ?? null,
            ]);
        }

        $this->orcamentoModel->recalcularValores($orcamentoId);
    }

    /**
     * Atualiza itens do orçamento
     */
    private function atualizarItens(int $orcamentoId, ?array $itens): void
    {
        // Remove itens existentes
        $this->itemModel->where('orcamento_id', $orcamentoId)->delete();

        // Adiciona novos
        if (!empty($itens)) {
            $this->salvarItens($orcamentoId, $itens);
        }

        $this->orcamentoModel->recalcularValores($orcamentoId);
    }

    /**
     * Cria lançamento financeiro ao aprovar orçamento
     */
    private function criarLancamentoFinanceiro(Orcamento $orcamento): void
    {
        $lancamentoModel = new LancamentoFinanceiroModel();

        // Verifica se já existe
        if ($lancamentoModel->existeReferencia('orcamentos', $orcamento->id)) {
            return;
        }

        $lancamentoModel->insert([
            'event_id' => $orcamento->event_id,
            'tipo' => 'SAIDA',
            'origem' => 'ORCAMENTO',
            'referencia_tipo' => 'orcamentos',
            'referencia_id' => $orcamento->id,
            'descricao' => "Orçamento #{$orcamento->codigo} - {$orcamento->titulo}",
            'valor' => $orcamento->valor_final,
            'data_lancamento' => date('Y-m-d'),
            'status' => 'pendente',
            'categoria' => 'Fornecedores',
        ]);
    }

    /**
     * Cria lançamento financeiro ao pagar parcela do orçamento
     */
    private function criarLancamentoFinanceiroParcela($orcamento, $parcela): void
    {
        $lancamentoModel = new LancamentoFinanceiroModel();

        // Verifica se já existe lançamento para esta parcela
        if ($lancamentoModel->existeReferencia('orcamento_parcelas', $parcela->id)) {
            return;
        }

        $lancamentoModel->insert([
            'event_id' => $orcamento->event_id,
            'tipo' => 'SAIDA',
            'origem' => 'ORCAMENTO',
            'referencia_tipo' => 'orcamentos',
            'referencia_id' => $orcamento->id,
            'descricao' => "Orçamento #{$orcamento->codigo} - Parcela {$parcela->numero_parcela}/{$orcamento->quantidade_parcelas}",
            'valor' => $parcela->valor,
            'data_lancamento' => date('Y-m-d'),
            'status' => 'pago',
            'categoria' => 'Fornecedores',
        ]);
    }

    /**
     * Limpa valor monetário
     */
    private function limparValor($valor): float
    {
        if (is_numeric($valor)) return (float) $valor;
        $valor = preg_replace('/[^\d,.-]/', '', $valor);
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
        return (float) $valor;
    }

    /**
     * Busca orçamento ou 404
     */
    private function buscaOrcamentoOu404(int $id = null)
    {
        if (!$id || !$orcamento = $this->orcamentoModel->withDeleted(true)->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Orçamento não encontrado');
        }
        return $orcamento;
    }

    /**
     * Marcar parcela como paga (AJAX)
     */
    public function pagarParcela()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $parcelaId = $this->request->getPost('parcela_id');
        $formaPagamento = $this->request->getPost('forma_pagamento');

        $parcelaModel = new \App\Models\OrcamentoParcelaModel();
        $parcela = $parcelaModel->find($parcelaId);

        if (!$parcela) {
            $retorno['erro'] = 'Parcela não encontrada';
            return $this->response->setJSON($retorno);
        }

        if (!$parcelaModel->marcarComoPaga($parcelaId, $formaPagamento)) {
            $retorno['erro'] = 'Erro ao registrar pagamento';
            return $this->response->setJSON($retorno);
        }

        // Buscar orçamento e criar lançamento financeiro para esta parcela
        $orcamento = $this->orcamentoModel->buscaOrcamentoCompleto($parcela->orcamento_id);
        if ($orcamento) {
            $this->criarLancamentoFinanceiroParcela($orcamento, $parcela);
        }

        $retorno['sucesso'] = 'Parcela marcada como paga!';
        return $this->response->setJSON($retorno);
    }
}
