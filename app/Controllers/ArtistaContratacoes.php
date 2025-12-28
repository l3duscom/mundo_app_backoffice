<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\ArtistaContratacao;
use App\Models\ArtistaModel;
use App\Models\ArtistaContratacaoModel;
use App\Models\ArtistaVooModel;
use App\Models\ArtistaHospedagemModel;
use App\Models\ArtistaTransladoModel;
use App\Models\ArtistaAlimentacaoModel;
use App\Models\ArtistaCustoExtraModel;
use App\Models\ArtistaParcelaModel;
use App\Models\ArtistaContratacaoAnexoModel;
use App\Models\LancamentoFinanceiroModel;
use App\Models\EventoModel;

class ArtistaContratacoes extends BaseController
{
    protected $contratacaoModel;
    protected $artistaModel;
    protected $eventoModel;
    protected $anexoModel;

    public function __construct()
    {
        $this->contratacaoModel = new ArtistaContratacaoModel();
        $this->artistaModel = new ArtistaModel();
        $this->eventoModel = new EventoModel();
        $this->anexoModel = new ArtistaContratacaoAnexoModel();
    }

    /**
     * Listagem de contratações
     */
    public function index()
    {
        $eventId = $this->request->getGet('evento_id') ?? evento_selecionado();
        
        if (!$eventId) {
            return redirect()->to('eventos')->with('atencao', 'Selecione um evento primeiro.');
        }
        
        $data = [
            'titulo' => 'Contratações de Artistas',
            'contratacoes' => $this->contratacaoModel->buscaPorEvento($eventId),
            'artistas' => $this->artistaModel->buscaAtivos(),
            'evento_id' => $eventId,
        ];

        return view('ArtistaContratacoes/index', $data);
    }

    /**
     * Exibir contratação
     */
    public function exibir(int $id = null)
    {
        $contratacao = $this->buscaContratacaoOu404($id);
        $contratacao = $this->contratacaoModel->buscaCompleta($id);

        $vooModel = new ArtistaVooModel();
        $hospModel = new ArtistaHospedagemModel();
        $transModel = new ArtistaTransladoModel();
        $alimModel = new ArtistaAlimentacaoModel();
        $extraModel = new ArtistaCustoExtraModel();
        $parcelaModel = new ArtistaParcelaModel();

        $data = [
            'titulo' => 'Contratação ' . $contratacao->codigo,
            'contratacao' => $contratacao,
            'artista' => $this->artistaModel->find($contratacao->artista_id),
            'voos' => $vooModel->buscaPorContratacao($id),
            'hospedagens' => $hospModel->buscaPorContratacao($id),
            'translados' => $transModel->buscaPorContratacao($id),
            'alimentacoes' => $alimModel->buscaPorContratacao($id),
            'extras' => $extraModel->buscaPorContratacao($id),
            'parcelas' => $parcelaModel->buscaPorContratacao($id),
            'anexos' => $this->anexoModel->buscarPorContratacao($id),
            'totais' => $this->contratacaoModel->calculaTotais($id),
            'tiposTranslado' => ArtistaTransladoModel::TIPOS,
            'tiposAlimentacao' => ArtistaAlimentacaoModel::TIPOS,
        ];

        return view('ArtistaContratacoes/exibir', $data);
    }

    /**
     * Criar contratação
     */
    public function criar()
    {
        $contratacao = new ArtistaContratacao();
        $contratacao->event_id = session()->get('evento_selecionado');

        $data = [
            'titulo' => 'Nova Contratação',
            'contratacao' => $contratacao,
            'artistas' => $this->artistaModel->buscaAtivos(),
        ];

        return view('ArtistaContratacoes/criar', $data);
    }

    /**
     * Cadastrar contratação
     */
    public function cadastrar()
    {
        $isAjax = $this->request->isAJAX();

        $contratacao = new ArtistaContratacao($this->request->getPost());
        $contratacao->codigo = $this->contratacaoModel->gerarCodigo();
        $contratacao->event_id = $this->request->getPost('event_id') ?: evento_selecionado();

        if (!$this->contratacaoModel->insert($contratacao)) {
            $erro = 'Erro ao cadastrar: ' . implode(', ', $this->contratacaoModel->errors());
            
            if ($isAjax) {
                return $this->response->setJSON(['token' => csrf_hash(), 'erro' => $erro]);
            }
            return redirect()->back()->withInput()->with('erro', $erro);
        }

        $contratacaoId = $this->contratacaoModel->getInsertID();

        if ($isAjax) {
            return $this->response->setJSON([
                'token' => csrf_hash(),
                'sucesso' => 'Contratação cadastrada com sucesso!',
                'redirect' => site_url("artista-contratacoes/exibir/{$contratacaoId}")
            ]);
        }
        
        return redirect()->to("artista-contratacoes/exibir/{$contratacaoId}")->with('sucesso', 'Contratação cadastrada com sucesso!');
    }

    /**
     * Confirmar contratação (gera parcelas)
     */
    public function confirmar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $id = $this->request->getPost('id');
        $contratacao = $this->buscaContratacaoOu404($id);

        if ($contratacao->situacao !== 'rascunho') {
            $retorno['erro'] = 'Apenas contratações em rascunho podem ser confirmadas';
            return $this->response->setJSON($retorno);
        }

        // Gerar parcelas do cachê
        $parcelaModel = new ArtistaParcelaModel();
        $parcelaModel->gerarParcelas(
            $id,
            $contratacao->quantidade_parcelas ?? 1,
            $contratacao->valor_cache,
            date('Y-m-d')
        );

        $this->contratacaoModel->update($id, ['situacao' => 'confirmado']);

        $retorno['sucesso'] = 'Contratação confirmada e parcelas geradas!';
        return $this->response->setJSON($retorno);
    }

    /**
     * Pagar parcela de cachê
     */
    public function pagarParcela()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $parcelaId = $this->request->getPost('parcela_id');
        $formaPagamento = $this->request->getPost('forma_pagamento');

        $parcelaModel = new ArtistaParcelaModel();
        $parcela = $parcelaModel->find($parcelaId);

        if (!$parcela) {
            $retorno['erro'] = 'Parcela não encontrada';
            return $this->response->setJSON($retorno);
        }

        $parcelaModel->marcarComoPaga($parcelaId, $formaPagamento);

        // Criar lançamento financeiro
        $contratacao = $this->contratacaoModel->buscaCompleta($parcela->contratacao_id);
        $this->criarLancamentoFinanceiro(
            $contratacao,
            'cache',
            "Cachê - Parcela {$parcela->numero_parcela}",
            $parcela->valor
        );

        $retorno['sucesso'] = 'Parcela paga com sucesso!';
        return $this->response->setJSON($retorno);
    }

    /**
     * Adicionar voo
     */
    public function adicionarVoo()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $vooModel = new ArtistaVooModel();
        $dados = $this->request->getPost();
        
        if (!$vooModel->insert($dados)) {
            $retorno['erro'] = 'Erro ao adicionar voo';
            return $this->response->setJSON($retorno);
        }

        $retorno['sucesso'] = 'Voo adicionado!';
        $retorno['id'] = $vooModel->getInsertID();
        return $this->response->setJSON($retorno);
    }

    /**
     * Adicionar hospedagem
     */
    public function adicionarHospedagem()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $model = new ArtistaHospedagemModel();
        $dados = $this->request->getPost();
        
        if (!$model->insert($dados)) {
            $retorno['erro'] = 'Erro ao adicionar hospedagem';
            return $this->response->setJSON($retorno);
        }

        $retorno['sucesso'] = 'Hospedagem adicionada!';
        $retorno['id'] = $model->getInsertID();
        return $this->response->setJSON($retorno);
    }

    /**
     * Adicionar translado
     */
    public function adicionarTranslado()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $model = new ArtistaTransladoModel();
        $dados = $this->request->getPost();
        
        if (!$model->insert($dados)) {
            $retorno['erro'] = 'Erro ao adicionar translado';
            return $this->response->setJSON($retorno);
        }

        $retorno['sucesso'] = 'Translado adicionado!';
        $retorno['id'] = $model->getInsertID();
        return $this->response->setJSON($retorno);
    }

    /**
     * Adicionar alimentação
     */
    public function adicionarAlimentacao()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $model = new ArtistaAlimentacaoModel();
        $dados = $this->request->getPost();
        
        if (!$model->insert($dados)) {
            $retorno['erro'] = 'Erro ao adicionar alimentação';
            return $this->response->setJSON($retorno);
        }

        $retorno['sucesso'] = 'Alimentação adicionada!';
        $retorno['id'] = $model->getInsertID();
        return $this->response->setJSON($retorno);
    }

    /**
     * Adicionar custo extra
     */
    public function adicionarExtra()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $model = new ArtistaCustoExtraModel();
        $dados = $this->request->getPost();
        
        if (!$model->insert($dados)) {
            $retorno['erro'] = 'Erro ao adicionar custo';
            return $this->response->setJSON($retorno);
        }

        $retorno['sucesso'] = 'Custo adicionado!';
        $retorno['id'] = $model->getInsertID();
        return $this->response->setJSON($retorno);
    }

    /**
     * Pagar custo (voo, hospedagem, etc.)
     */
    public function pagarCusto()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $tipo = $this->request->getPost('tipo'); // voo, hospedagem, translado, alimentacao, extra
        $custoId = $this->request->getPost('custo_id');
        $contratacaoId = $this->request->getPost('contratacao_id');

        $model = null;
        $campoValor = 'valor';

        switch ($tipo) {
            case 'voo':
                $model = new ArtistaVooModel();
                break;
            case 'hospedagem':
                $model = new ArtistaHospedagemModel();
                $campoValor = 'valor_total';
                break;
            case 'translado':
                $model = new ArtistaTransladoModel();
                break;
            case 'alimentacao':
                $model = new ArtistaAlimentacaoModel();
                $campoValor = 'valor_total';
                break;
            case 'extra':
                $model = new ArtistaCustoExtraModel();
                break;
            default:
                $retorno['erro'] = 'Tipo de custo inválido';
                return $this->response->setJSON($retorno);
        }

        $custo = $model->find($custoId);
        if (!$custo) {
            $retorno['erro'] = 'Custo não encontrado';
            return $this->response->setJSON($retorno);
        }

        $model->update($custoId, ['status' => 'pago']);

        // Criar lançamento financeiro
        $contratacao = $this->contratacaoModel->buscaCompleta($contratacaoId);
        $descricao = $this->montarDescricaoCusto($tipo, $custo);
        $valor = $custo->$campoValor ?? 0;

        $this->criarLancamentoFinanceiro($contratacao, $tipo, $descricao, $valor);

        $retorno['sucesso'] = 'Pagamento registrado!';
        return $this->response->setJSON($retorno);
    }

    /**
     * Monta descrição para o lançamento financeiro
     */
    private function montarDescricaoCusto(string $tipo, object $custo): string
    {
        switch ($tipo) {
            case 'voo':
                return "Voo {$custo->numero_voo} ({$custo->origem} → {$custo->destino})";
            case 'hospedagem':
                return "Hospedagem - {$custo->hotel}";
            case 'translado':
                $tiposTranslado = ArtistaTransladoModel::TIPOS;
                return "Translado - " . ($tiposTranslado[$custo->tipo] ?? $custo->tipo);
            case 'alimentacao':
                $tiposAlim = ArtistaAlimentacaoModel::TIPOS;
                return "Alimentação - " . ($tiposAlim[$custo->tipo] ?? $custo->tipo);
            case 'extra':
                return "Extra - {$custo->descricao}";
            default:
                return "Custo {$tipo}";
        }
    }

    /**
     * Cria lançamento financeiro para custo de artista
     */
    private function criarLancamentoFinanceiro($contratacao, string $tipoCusto, string $descricao, float $valor): void
    {
        $lancamentoModel = new LancamentoFinanceiroModel();

        $lancamentoModel->insert([
            'event_id' => $contratacao->event_id,
            'tipo' => 'SAIDA',
            'origem' => 'ARTISTA',
            'referencia_tipo' => 'artista_contratacoes',
            'referencia_id' => $contratacao->id,
            'descricao' => $contratacao->nome_artistico . " - " . $descricao,
            'valor' => $valor,
            'data_lancamento' => date('Y-m-d'),
            'status' => 'pago',
            'categoria' => 'Artistas',
        ]);
    }

    /**
     * Busca contratação ou 404
     */
    private function buscaContratacaoOu404(int $id = null)
    {
        if (!$id || !$contratacao = $this->contratacaoModel->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Contratação não encontrada');
        }
        return $contratacao;
    }

    /**
     * Upload de anexo
     */
    public function uploadAnexo()
    {
        $retorno['token'] = csrf_hash();

        $contratacaoId = $this->request->getPost('contratacao_id');
        $arquivo = $this->request->getFile('arquivo');

        if (!$arquivo || !$arquivo->isValid()) {
            $retorno['erro'] = 'Arquivo inválido';
            return $this->response->setJSON($retorno);
        }

        // Verificar tamanho (10MB)
        if ($arquivo->getSize() > 10 * 1024 * 1024) {
            $retorno['erro'] = 'Arquivo muito grande (máx: 10MB)';
            return $this->response->setJSON($retorno);
        }

        $anexoId = $this->anexoModel->salvarAnexo($contratacaoId, $arquivo);

        if (!$anexoId) {
            $retorno['erro'] = 'Erro ao salvar anexo';
            return $this->response->setJSON($retorno);
        }

        $retorno['sucesso'] = 'Anexo enviado com sucesso!';
        $retorno['id'] = $anexoId;
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

        $path = WRITEPATH . 'uploads/artistas/contratacoes/anexos/' . $anexo->arquivo;
        if (!is_file($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Arquivo não encontrado');
        }

        return $this->response
            ->setHeader('Content-Type', $anexo->tipo)
            ->setHeader('Content-Disposition', 'attachment; filename="' . $anexo->nome_arquivo . '"')
            ->setBody(file_get_contents($path));
    }

    /**
     * Visualizar anexo (inline)
     */
    public function visualizarAnexo(int $id = null)
    {
        $anexo = $this->anexoModel->find($id);
        if (!$anexo) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Anexo não encontrado');
        }

        $path = WRITEPATH . 'uploads/artistas/contratacoes/anexos/' . $anexo->arquivo;
        if (!is_file($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Arquivo não encontrado');
        }

        return $this->response
            ->setHeader('Content-Type', $anexo->tipo)
            ->setHeader('Content-Disposition', 'inline; filename="' . $anexo->nome_arquivo . '"')
            ->setBody(file_get_contents($path));
    }

    /**
     * Remover anexo
     */
    public function removerAnexo()
    {
        $retorno['token'] = csrf_hash();

        $anexoId = $this->request->getPost('anexo_id');

        if (!$this->anexoModel->removerAnexo($anexoId)) {
            $retorno['erro'] = 'Erro ao remover anexo';
            return $this->response->setJSON($retorno);
        }

        $retorno['sucesso'] = 'Anexo removido!';
        return $this->response->setJSON($retorno);
    }
}
