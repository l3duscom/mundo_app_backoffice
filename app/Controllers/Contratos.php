<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\Contrato;

class Contratos extends BaseController
{

    private $contratoModel;
    private $expositorModel;
    private $eventoModel;
    private $parcelaModel;

    public function __construct()
    {
        $this->contratoModel = new \App\Models\ContratoModel();
        $this->expositorModel = new \App\Models\ExpositorModel();
        $this->eventoModel = new \App\Models\EventoModel();
        $this->parcelaModel = new \App\Models\ContratoParcelaModel();
    }

    public function index()
    {
        if (!$this->usuarioLogado()->temPermissaoPara('listar-contratos')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $data = [
            'titulo' => 'Listando os contratos',
            'eventos' => $this->eventoModel->orderBy('id', 'DESC')->findAll(),
        ];

        return view('Contratos/index', $data);
    }

    public function recuperaContratos()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $contratos = $this->contratoModel
            ->select('contratos.*, expositores.nome as expositor_nome, expositores.nome_fantasia as expositor_fantasia, eventos.nome as evento_nome')
            ->join('expositores', 'expositores.id = contratos.expositor_id', 'left')
            ->join('eventos', 'eventos.id = contratos.event_id', 'left')
            ->withDeleted(true)
            ->orderBy('contratos.id', 'DESC')
            ->findAll();

        $data = [];

        // Carregar modelo de itens para contar
        $contratoItemModel = new \App\Models\ContratoItemModel();

        foreach ($contratos as $contrato) {
            $nomeExpositor = !empty($contrato->expositor_fantasia) 
                ? $contrato->expositor_fantasia 
                : $contrato->expositor_nome;

            // Conta itens do contrato
            $qtdItens = $contratoItemModel->where('contrato_id', $contrato->id)->countAllResults();

            $data[] = [
                'codigo' => anchor("contratos/exibir/$contrato->id", esc($contrato->codigo ?? '#' . $contrato->id), 'title="Exibir contrato"'),
                'expositor' => esc($nomeExpositor ?? 'N/A'),
                'evento' => esc($contrato->evento_nome ?? 'N/A'),
                'qtd_itens' => '<span class="badge bg-secondary">' . $qtdItens . ' ' . ($qtdItens == 1 ? 'item' : 'itens') . '</span>',
                'valor_final' => $contrato->getValorFinalFormatado(),
                'valor_pago' => $contrato->getValorPagoFormatado(),
                'situacao' => $contrato->exibeSituacao() . '<span class="d-none">' . esc($contrato->situacao) . '</span>',
            ];
        }

        $retorno = [
            'data' => $data,
        ];

        return $this->response->setJSON($retorno);
    }
    /**
     * Recupera totais para o dashboard de contratos (AJAX)
     */
    public function recuperaTotais()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $eventId = $this->request->getGet('event_id');

        // Query base para contratos
        $builder = $this->contratoModel
            ->select('contratos.id, contratos.valor_final, contratos.valor_pago, contratos.valor_em_aberto, contratos.situacao')
            ->where('contratos.deleted_at', null);

        if (!empty($eventId)) {
            $builder->where('contratos.event_id', $eventId);
        }

        $contratos = $builder->findAll();

        // Inicializar totais
        $totais = [
            'quantidade_contratos' => count($contratos),
            'valor_total' => 0,
            'valor_pago' => 0,
            'valor_em_aberto' => 0,
            'por_tipo' => [],
            'por_situacao' => [],
        ];

        // Calcular totais gerais
        foreach ($contratos as $contrato) {
            $totais['valor_total'] += (float)($contrato->valor_final ?? 0);
            $totais['valor_pago'] += (float)($contrato->valor_pago ?? 0);
            $totais['valor_em_aberto'] += (float)($contrato->valor_em_aberto ?? 0);

            // Por situação
            $situacao = $contrato->situacao ?? 'proposta';
            if (!isset($totais['por_situacao'][$situacao])) {
                $totais['por_situacao'][$situacao] = [
                    'quantidade' => 0,
                    'valor' => 0,
                ];
            }
            $totais['por_situacao'][$situacao]['quantidade']++;
            $totais['por_situacao'][$situacao]['valor'] += (float)($contrato->valor_final ?? 0);
        }

        // Buscar valores por tipo (baseado nos itens dos contratos)
        $db = \Config\Database::connect();
        $sql = "SELECT ci.tipo_item as tipo, 
                       SUM(ci.valor_total) as valor_total, 
                       COUNT(DISTINCT c.id) as quantidade
                FROM contratos c
                INNER JOIN contrato_itens ci ON ci.contrato_id = c.id
                WHERE c.deleted_at IS NULL AND ci.deleted_at IS NULL";
        
        if (!empty($eventId)) {
            $sql .= " AND c.event_id = " . (int)$eventId;
        }
        
        $sql .= " GROUP BY ci.tipo_item ORDER BY ci.tipo_item";
        
        $valoresPorTipo = $db->query($sql)->getResult();

        // Monta array por tipo
        foreach ($valoresPorTipo as $v) {
            $tipo = $v->tipo ?? 'Outros';
            $totais['por_tipo'][$tipo] = [
                'quantidade' => (int)$v->quantidade,
                'valor' => (float)$v->valor_total,
            ];
        }

        return $this->response->setJSON($totais);
    }

    public function criar()
    {
        if (!$this->usuarioLogado()->temPermissaoPara('criar-contratos')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $contrato = new Contrato();
        $contrato->codigo = $this->contratoModel->gerarCodigo();
        $contrato->data_proposta = date('Y-m-d');

        $data = [
            'titulo' => "Criar novo contrato",
            'contrato' => $contrato,
            'expositores' => $this->expositorModel->where('ativo', 1)->orderBy('nome', 'ASC')->findAll(),
            'eventos' => $this->eventoModel->orderBy('id', 'DESC')->findAll(),
        ];

        return view('Contratos/criar', $data);
    }

    public function cadastrar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $post = $this->request->getPost();

        // Limpar valores monetários
        $post['valor_pago'] = $this->limparValorMonetario($post['valor_pago'] ?? '0');
        $post['desconto_adicional'] = $this->limparValorMonetario($post['desconto_adicional'] ?? '0');

        // Valores iniciais (serão recalculados quando itens forem adicionados)
        $post['valor_original'] = 0;
        $post['valor_desconto'] = 0;
        $post['valor_final'] = 0;
        $post['valor_em_aberto'] = 0; // Será calculado pelo ContratoItemModel ao adicionar itens
        
        $quantidadeParcelas = (int)($post['quantidade_parcelas'] ?? 1);
        $post['quantidade_parcelas'] = $quantidadeParcelas > 0 ? $quantidadeParcelas : 1;
        $post['valor_parcela'] = 0;

        $contrato = new Contrato($post);

        if ($this->contratoModel->save($contrato)) {
            $contratoId = $this->contratoModel->getInsertID();
            $btnAddItens = anchor("contratos/exibir/$contratoId", 'Adicionar itens ao contrato', ['class' => 'btn btn-success mt-2']);

            session()->setFlashdata('sucesso', "Contrato criado com sucesso!<br>Agora adicione os itens ao contrato.<br> $btnAddItens");

            $retorno['id'] = $contratoId;

            return $this->response->setJSON($retorno);
        }

        $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
        $retorno['erros_model'] = $this->contratoModel->errors();

        return $this->response->setJSON($retorno);
    }

    public function exibir(int $id = null)
    {
        if (!$this->usuarioLogado()->temPermissaoPara('listar-contratos')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $contrato = $this->buscaContratoOu404($id);

        // Busca dados relacionados
        $expositor = $this->expositorModel->find($contrato->expositor_id);
        $evento = $this->eventoModel->find($contrato->event_id);
        
        // Busca parcelas do banco (sincronizadas do Asaas)
        $parcelasBanco = $this->parcelaModel->buscaPorContrato($id);
        $totaisParcelas = $this->parcelaModel->calculaTotais($id);

        $data = [
            'titulo' => "Detalhando o contrato " . esc($contrato->codigo ?? '#' . $contrato->id),
            'contrato' => $contrato,
            'expositor' => $expositor,
            'evento' => $evento,
            'parcelas' => $parcelasBanco,
            'totais_parcelas' => $totaisParcelas,
        ];

        return view('Contratos/exibir', $data);
    }

    public function editar(int $id = null)
    {
        if (!$this->usuarioLogado()->temPermissaoPara('editar-contratos')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $contrato = $this->buscaContratoOu404($id);

        $data = [
            'titulo' => "Editando o contrato " . esc($contrato->codigo ?? '#' . $contrato->id),
            'contrato' => $contrato,
            'expositores' => $this->expositorModel->where('ativo', 1)->orderBy('nome', 'ASC')->findAll(),
            'eventos' => $this->eventoModel->orderBy('id', 'DESC')->findAll(),
        ];

        return view('Contratos/editar', $data);
    }

    public function atualizar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $post = $this->request->getPost();

        $contrato = $this->buscaContratoOu404($post['id']);

        // Limpar valores monetários
        $post['valor_pago'] = $this->limparValorMonetario($post['valor_pago'] ?? '0');
        $post['desconto_adicional'] = $this->limparValorMonetario($post['desconto_adicional'] ?? '0');
        
        $quantidadeParcelas = (int)($post['quantidade_parcelas'] ?? 1);
        $post['quantidade_parcelas'] = $quantidadeParcelas > 0 ? $quantidadeParcelas : 1;

        $contrato->fill($post);

        if ($contrato->hasChanged() === false) {
            $retorno['info'] = 'Não há dados para atualizar';
            return $this->response->setJSON($retorno);
        }

        if ($this->contratoModel->save($contrato)) {
            // Recalcula totais baseado nos itens
            $contratoItemModel = new \App\Models\ContratoItemModel();
            $contratoItemModel->atualizaTotaisContrato($contrato->id);

            session()->setFlashdata('sucesso', 'Contrato atualizado com sucesso!');
            return $this->response->setJSON($retorno);
        }

        $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
        $retorno['erros_model'] = $this->contratoModel->errors();

        return $this->response->setJSON($retorno);
    }

    public function alterarSituacao()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        $post = $this->request->getPost();

        $contrato = $this->buscaContratoOu404($post['id']);

        $situacaoAnterior = $contrato->situacao;
        $contrato->situacao = $post['situacao'];

        // Atualiza datas automaticamente conforme a situação
        $hoje = date('Y-m-d');
        switch ($post['situacao']) {
            case 'proposta_aceita':
                if (empty($contrato->data_aceite)) {
                    $contrato->data_aceite = $hoje;
                }
                break;
            case 'contrato_assinado':
                if (empty($contrato->data_assinatura)) {
                    $contrato->data_assinatura = $hoje;
                }
                break;
            case 'aguardando_contrato':
                if (empty($contrato->data_pagamento)) {
                    $contrato->data_pagamento = $hoje;
                }
                break;
            case 'pagamento_confirmado':
                // Só permite ir para pagamento_confirmado se tiver documento confirmado
                $documentoModel = new \App\Models\ContratoDocumentoModel();
                if (!$documentoModel->temDocumentoConfirmado($contrato->id)) {
                    $retorno['erro'] = 'Para finalizar o contrato, é necessário que o documento seja assinado pelo expositor e confirmado no sistema.';
                    return $this->response->setJSON($retorno);
                }
                
                if (empty($contrato->data_pagamento)) {
                    $contrato->data_pagamento = $hoje;
                }
                
                // Se veio forma de pagamento, atualiza
                if (!empty($post['forma_pagamento'])) {
                    $contrato->forma_pagamento = $post['forma_pagamento'];
                }
                
                // Calcula valor com desconto PIX se aplicável
                $valorAPagar = $contrato->valor_final;
                if ($contrato->forma_pagamento === 'PIX') {
                    $valorAPagar = $contrato->valor_final * 0.90; // 10% desconto
                }
                
                // Atualiza valores para pagamento completo
                $contrato->valor_pago = $valorAPagar;
                $contrato->valor_em_aberto = 0;
                break;
        }

        if ($this->contratoModel->save($contrato)) {
            $retorno['sucesso'] = 'Situação alterada com sucesso!';
            $retorno['situacao_anterior'] = $situacaoAnterior;
            $retorno['situacao_nova'] = $post['situacao'];
            return $this->response->setJSON($retorno);
        }

        $retorno['erro'] = 'Erro ao alterar situação';
        return $this->response->setJSON($retorno);
    }

    /**
     * Receber pagamento em dinheiro (integrado com Asaas)
     * Aceita pagamentos parciais que diluem nas parcelas
     */
    public function receberDinheiro()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        try {
            $post = $this->request->getPost();
            $contratoId = $post['id'];
            $valorRecebido = isset($post['valor']) ? (float)$post['valor'] : null;

            $contrato = $this->buscaContratoOu404($contratoId);

            // Calcula valor total com desconto PIX se aplicável (arredondado)
            $valorTotal = round($contrato->valor_final, 2);
            if ($contrato->forma_pagamento === 'PIX') {
                $valorTotal = round($contrato->valor_final * 0.90, 2);
            }

            // Calcula valor em aberto (arredondado)
            $valorJaPago = round($contrato->valor_pago ?? 0, 2);
            $valorEmAberto = round($valorTotal - $valorJaPago, 2);

            // Se não foi informado valor, usa o valor em aberto (pagamento total)
            if ($valorRecebido === null || $valorRecebido <= 0) {
                $valorRecebido = $valorEmAberto;
            }

            // Limita ao valor em aberto
            if ($valorRecebido > $valorEmAberto) {
                $valorRecebido = $valorEmAberto;
            }
            
            // Garante arredondamento
            $valorRecebido = round($valorRecebido, 2);

            // Novo valor pago acumulado
            $novoValorPago = $valorJaPago + $valorRecebido;
            $novoValorEmAberto = $valorTotal - $novoValorPago;

            // Se tem cobrança no Asaas, dá baixa parcela por parcela
            if (!empty($contrato->asaas_payment_id)) {
                $asaasService = new \App\Services\AsaasService();
                
                // Busca detalhes da cobrança para ver se é parcelado
                $cobranca = $asaasService->buscarCobranca($contrato->asaas_payment_id);
                
                if (isset($cobranca['installment'])) {
                    // É parcelado - busca parcelas pendentes
                    $parcelas = $asaasService->buscarParcelas($cobranca['installment']);
                    
                    log_message('info', "Parcelas do Asaas (installment {$cobranca['installment']}): " . json_encode($parcelas));
                    
                    if (isset($parcelas['data']) && is_array($parcelas['data'])) {
                        // Arredonda para evitar problemas de ponto flutuante
                        $valorRestanteParaBaixar = round($valorRecebido, 2);
                        
                        // Ordena parcelas por vencimento (mais antigas primeiro)
                        usort($parcelas['data'], function($a, $b) {
                            return strtotime($a['dueDate']) - strtotime($b['dueDate']);
                        });
                        
                        log_message('info', "Valor a baixar: R$ {$valorRestanteParaBaixar}");
                        
                        foreach ($parcelas['data'] as $parcela) {
                            // Só dá baixa em parcelas pendentes/vencidas
                            if (in_array($parcela['status'], ['PENDING', 'OVERDUE']) && $valorRestanteParaBaixar > 0.01) {
                                $valorParcela = round($parcela['value'], 2);
                                
                                log_message('info', "Processando parcela {$parcela['id']}: status={$parcela['status']}, valor={$valorParcela}, restante={$valorRestanteParaBaixar}");
                                
                                // Usa tolerância de 1 centavo para comparação
                                if ($valorRestanteParaBaixar >= ($valorParcela - 0.01)) {
                                    // Dá baixa na parcela inteira no Asaas
                                    $response = $asaasService->receberEmDinheiro(
                                        $parcela['id'],
                                        $valorParcela,
                                        date('Y-m-d')
                                    );
                                    
                                    log_message('info', "Baixa na parcela {$parcela['id']}: R$ {$valorParcela} - Resposta: " . json_encode($response));
                                    
                                    // Verifica se houve erro
                                    if (isset($response['errors'])) {
                                        log_message('error', "ERRO ao dar baixa na parcela {$parcela['id']}: " . json_encode($response['errors']));
                                    } else {
                                        // Atualiza parcela no banco local
                                        $this->parcelaModel->atualizarPorAsaasId($parcela['id'], [
                                            'status' => 'RECEIVED_IN_CASH',
                                            'data_pagamento' => date('Y-m-d'),
                                            'forma_pagamento' => 'Dinheiro',
                                        ]);
                                    }
                                    
                                    $valorRestanteParaBaixar = round($valorRestanteParaBaixar - $valorParcela, 2);
                                    log_message('info', "Valor restante a baixar: R$ {$valorRestanteParaBaixar}");
                                } else {
                                    // Valor parcial - registra apenas localmente
                                    log_message('info', "Valor parcial R$ {$valorRestanteParaBaixar} insuficiente para parcela {$parcela['id']} de R$ {$valorParcela}");
                                    $valorRestanteParaBaixar = 0;
                                }
                            } else {
                                log_message('info', "Parcela {$parcela['id']} ignorada: status={$parcela['status']}, valorRestante={$valorRestanteParaBaixar}");
                            }
                        }
                    }
                } else {
                    // Cobrança única - dá baixa direto
                    $response = $asaasService->receberEmDinheiro(
                        $contrato->asaas_payment_id,
                        $valorRecebido,
                        date('Y-m-d')
                    );

                    log_message('info', 'Asaas receberEmDinheiro para contrato ' . $contratoId . ': ' . json_encode($response));

                    if (isset($response['errors'])) {
                        log_message('warning', 'Erro ao dar baixa no Asaas, continuando com baixa manual: ' . json_encode($response['errors']));
                    }
                    
                    // Atualiza parcela única no banco local
                    $this->parcelaModel->atualizarPorAsaasId($contrato->asaas_payment_id, [
                        'status' => 'RECEIVED_IN_CASH',
                        'data_pagamento' => date('Y-m-d'),
                        'forma_pagamento' => 'Dinheiro',
                    ]);
                }
            }
            
            // Atualiza totais do contrato baseado nas parcelas do banco
            $this->atualizarValoresContratoPorParcelas($contrato);

            // Atualiza o contrato
            $contrato->valor_pago = $novoValorPago;
            $contrato->valor_em_aberto = $novoValorEmAberto;
            $contrato->data_pagamento = date('Y-m-d');
            
            // Se não tinha forma de pagamento definida, define como Dinheiro
            if (empty($contrato->forma_pagamento) || $contrato->forma_pagamento === 'Não definido') {
                $contrato->forma_pagamento = 'Dinheiro';
            }
            
            // Atualiza situação baseado no valor pago
            if ($novoValorEmAberto <= 0) {
                // Pagamento completo - vai para aguardando_contrato (precisa assinar documento)
                $documentoModel = new \App\Models\ContratoDocumentoModel();
                if ($documentoModel->temDocumentoConfirmado($contrato->id)) {
                    $contrato->situacao = 'pagamento_confirmado';
                } else {
                    $contrato->situacao = 'aguardando_contrato';
                }
                $contrato->valor_em_aberto = 0;
            } else {
                // Pagamento parcial - mantém em andamento
                $contrato->situacao = 'pagamento_andamento';
            }

            if ($this->contratoModel->save($contrato)) {
                $mensagem = $novoValorEmAberto <= 0 
                    ? 'Pagamento completo confirmado! Agora é necessário gerar e assinar o documento do contrato.' 
                    : 'Pagamento parcial registrado! Restam R$ ' . number_format($novoValorEmAberto, 2, ',', '.');
                
                $retorno['sucesso'] = $mensagem;
                $retorno['valor_recebido'] = 'R$ ' . number_format($valorRecebido, 2, ',', '.');
                $retorno['valor_pago_total'] = 'R$ ' . number_format($novoValorPago, 2, ',', '.');
                $retorno['valor_em_aberto'] = 'R$ ' . number_format($novoValorEmAberto, 2, ',', '.');
                $retorno['pagamento_completo'] = ($novoValorEmAberto <= 0);
                return $this->response->setJSON($retorno);
            }

            $retorno['erro'] = 'Erro ao salvar contrato';
            return $this->response->setJSON($retorno);

        } catch (\Exception $e) {
            $retorno['erro'] = 'Exceção: ' . $e->getMessage();
            return $this->response->setJSON($retorno);
        }
    }

    /**
     * Busca parcelas do Asaas para um contrato
     */
    public function buscarParcelasAsaas(int $id = null)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        try {
            $contrato = $this->buscaContratoOu404($id);

            if (empty($contrato->asaas_payment_id)) {
                $retorno['erro'] = 'Contrato não possui cobrança no Asaas';
                return $this->response->setJSON($retorno);
            }

            $asaasService = new \App\Services\AsaasService();

            // Busca detalhes da cobrança
            $cobranca = $asaasService->buscarCobranca($contrato->asaas_payment_id);

            if (isset($cobranca['errors'])) {
                $retorno['erro'] = 'Erro ao buscar cobrança: ' . ($cobranca['errors'][0]['description'] ?? 'Erro desconhecido');
                return $this->response->setJSON($retorno);
            }

            $parcelas = [];

            $parcelasParaSalvar = [];
            $installmentId = null;

            // Se é parcelamento, busca todas as parcelas
            if (isset($cobranca['installment'])) {
                $installmentId = $cobranca['installment'];
                $parcelasAsaas = $asaasService->buscarParcelas($installmentId);
                
                if (isset($parcelasAsaas['data']) && is_array($parcelasAsaas['data'])) {
                    foreach ($parcelasAsaas['data'] as $index => $parcela) {
                        $parcelasParaSalvar[] = $parcela;
                        $parcelas[] = [
                            'numero' => $index + 1,
                            'id' => $parcela['id'],
                            'valor' => $parcela['value'],
                            'valor_liquido' => $parcela['netValue'] ?? $parcela['value'],
                            'vencimento' => $parcela['dueDate'],
                            'status' => $parcela['status'],
                            'status_label' => $this->getStatusLabel($parcela['status']),
                            'data_pagamento' => $parcela['paymentDate'] ?? null,
                            'comprovante' => $parcela['transactionReceiptUrl'] ?? null,
                        ];
                    }
                }
            } else {
                // Cobrança única
                $parcelasParaSalvar[] = $cobranca;
                $parcelas[] = [
                    'numero' => 1,
                    'id' => $cobranca['id'],
                    'valor' => $cobranca['value'],
                    'valor_liquido' => $cobranca['netValue'] ?? $cobranca['value'],
                    'vencimento' => $cobranca['dueDate'],
                    'status' => $cobranca['status'],
                    'status_label' => $this->getStatusLabel($cobranca['status']),
                    'data_pagamento' => $cobranca['paymentDate'] ?? null,
                    'comprovante' => $cobranca['transactionReceiptUrl'] ?? null,
                ];
            }

            // Salva parcelas no banco de dados
            $salvou = $this->parcelaModel->sincronizarDoAsaas($id, $parcelasParaSalvar, $installmentId);
            
            // Atualiza valores do contrato baseado nas parcelas sincronizadas
            $this->atualizarValoresContratoPorParcelas($contrato);

            $retorno['sucesso'] = true;
            $retorno['parcelas'] = $parcelas;
            $retorno['salvo_no_banco'] = $salvou;
            $retorno['cobranca'] = [
                'id' => $cobranca['id'],
                'tipo' => $cobranca['billingType'],
                'status' => $cobranca['status'],
                'valor_total' => $cobranca['value'],
            ];

            return $this->response->setJSON($retorno);

        } catch (\Exception $e) {
            $retorno['erro'] = 'Exceção: ' . $e->getMessage();
            return $this->response->setJSON($retorno);
        }
    }

    /**
     * Atualiza valores do contrato baseado nas parcelas sincronizadas
     */
    private function atualizarValoresContratoPorParcelas($contrato): void
    {
        $totais = $this->parcelaModel->calculaTotais($contrato->id);
        
        if ($totais['quantidade'] > 0) {
            $contrato->valor_pago = $totais['pago'];
            $contrato->valor_em_aberto = $totais['pendente'];
            
            // Atualiza situação
            if ($totais['pendentes'] === 0 && $totais['pagas'] > 0) {
                // Verifica se já tem documento confirmado
                $documentoModel = new \App\Models\ContratoDocumentoModel();
                if ($documentoModel->temDocumentoConfirmado($contrato->id)) {
                    $contrato->situacao = 'pagamento_confirmado';
                } else {
                    $contrato->situacao = 'aguardando_contrato';
                }
            } elseif ($totais['pagas'] > 0 && $totais['pendentes'] > 0) {
                $contrato->situacao = 'pagamento_andamento';
            }
            
            $this->contratoModel->save($contrato);
        }
    }

    /**
     * Retorna label amigável para status do Asaas
     */
    private function getStatusLabel(string $status): array
    {
        $labels = [
            'PENDING' => ['texto' => 'Pendente', 'cor' => 'warning'],
            'AWAITING_RISK_ANALYSIS' => ['texto' => 'Em análise', 'cor' => 'info'],
            'RECEIVED' => ['texto' => 'Recebido', 'cor' => 'success'],
            'CONFIRMED' => ['texto' => 'Confirmado', 'cor' => 'success'],
            'RECEIVED_IN_CASH' => ['texto' => 'Recebido em dinheiro', 'cor' => 'success'],
            'OVERDUE' => ['texto' => 'Vencido', 'cor' => 'danger'],
            'REFUNDED' => ['texto' => 'Estornado', 'cor' => 'secondary'],
            'REFUND_REQUESTED' => ['texto' => 'Estorno solicitado', 'cor' => 'warning'],
            'CHARGEBACK_REQUESTED' => ['texto' => 'Chargeback', 'cor' => 'danger'],
            'CHARGEBACK_DISPUTE' => ['texto' => 'Disputa', 'cor' => 'danger'],
            'DUNNING_REQUESTED' => ['texto' => 'Negativação', 'cor' => 'danger'],
            'DUNNING_RECEIVED' => ['texto' => 'Recuperado', 'cor' => 'success'],
        ];

        return $labels[$status] ?? ['texto' => $status, 'cor' => 'secondary'];
    }

    public function excluir(int $id = null)
    {
        if (!$this->usuarioLogado()->temPermissaoPara('excluir-contratos')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $contrato = $this->buscaContratoOu404($id);

        if ($contrato->deleted_at != null) {
            return redirect()->back()->with('info', "Contrato já encontra-se excluído");
        }

        if ($this->request->getMethod() === 'post') {
            $this->contratoModel->delete($id);

            return redirect()->to(site_url("contratos"))->with('sucesso', "Contrato excluído com sucesso!");
        }

        // Busca dados relacionados
        $expositor = $this->expositorModel->find($contrato->expositor_id);

        $data = [
            'titulo' => "Excluindo o contrato " . esc($contrato->codigo ?? '#' . $contrato->id),
            'contrato' => $contrato,
            'expositor' => $expositor,
        ];

        return view('Contratos/excluir', $data);
    }

    public function desfazerExclusao(int $id = null)
    {
        if (!$this->usuarioLogado()->temPermissaoPara('editar-contratos')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $contrato = $this->buscaContratoOu404($id);

        if ($contrato->deleted_at === null) {
            return redirect()->back()->with('info', "Apenas contratos excluídos podem ser recuperados");
        }

        $contrato->deleted_at = null;
        $this->contratoModel->protect(false)->save($contrato);

        return redirect()->back()->with('sucesso', "Contrato recuperado com sucesso!");
    }

    /**
     * Busca expositores via AJAX
     */
    public function buscaExpositores()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $termo = $this->request->getGet('termo');

        $expositores = $this->expositorModel
            ->select('id, nome, nome_fantasia, documento')
            ->where('ativo', 1)
            ->groupStart()
                ->like('nome', $termo)
                ->orLike('nome_fantasia', $termo)
                ->orLike('documento', $termo)
            ->groupEnd()
            ->orderBy('nome', 'ASC')
            ->findAll(20);

        $resultado = [];
        foreach ($expositores as $exp) {
            $resultado[] = [
                'id' => $exp->id,
                'text' => $exp->nome_fantasia ?: $exp->nome,
                'documento' => $exp->documento,
            ];
        }

        return $this->response->setJSON($resultado);
    }

    /**
     * Método privado para limpar valor monetário
     */
    private function limparValorMonetario(string $valor): float
    {
        // Remove R$, espaços e pontos de milhar, substitui vírgula por ponto
        $valor = preg_replace('/[R$\s]/', '', $valor);
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
        
        return (float)$valor;
    }

    /**
     * Gera cobrança no Asaas
     */
    public function gerarCobranca()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $retorno['token'] = csrf_hash();

        try {
            $post = $this->request->getPost();
            $contratoId = $post['id'];

            $contrato = $this->buscaContratoOu404($contratoId);
            $expositor = $this->expositorModel->find($contrato->expositor_id);
            $evento = $this->eventoModel->find($contrato->event_id);

            if (!$expositor) {
                $retorno['erro'] = 'Expositor não encontrado';
                return $this->response->setJSON($retorno);
            }

            if ($contrato->valor_final <= 0) {
                $retorno['erro'] = 'O contrato não possui valor para cobrança. Adicione itens primeiro.';
                return $this->response->setJSON($retorno);
            }

            // Verifica se tem forma de pagamento definida
            if (empty($contrato->forma_pagamento)) {
                $retorno['erro'] = 'Defina a forma de pagamento no contrato antes de gerar a cobrança.';
                return $this->response->setJSON($retorno);
            }

            // Mapeia forma de pagamento do contrato para billing_type do Asaas
            $mapeamentoFormas = [
                'PIX' => 'PIX',
                'Boleto' => 'BOLETO',
                'Cartão de Crédito' => 'CREDIT_CARD',
                'Cartão de Débito' => 'CREDIT_CARD', // Asaas não tem débito separado
                'Transferência' => 'BOLETO', // Usa boleto como fallback
            ];

            $billingType = $mapeamentoFormas[$contrato->forma_pagamento] ?? null;

            // Formas que não geram cobrança no Asaas
            $formasExternas = ['Dinheiro', 'Permuta', 'Cortesia'];
            if (in_array($contrato->forma_pagamento, $formasExternas)) {
                // Para essas formas, apenas muda o status
                $contrato->situacao = 'pagamento_aberto';
                $this->contratoModel->save($contrato);
                
                $retorno['sucesso'] = 'Contrato atualizado para pagamento externo (' . $contrato->forma_pagamento . ')';
                $retorno['externo'] = true;
                return $this->response->setJSON($retorno);
            }

            if (!$billingType) {
                $retorno['erro'] = 'Forma de pagamento "' . $contrato->forma_pagamento . '" não suportada pelo Asaas.';
                return $this->response->setJSON($retorno);
            }

            // Calcula valor com desconto de 10% para PIX
            $valorCobranca = (float) $contrato->valor_final;
            $descontoPix = 0;
            
            if ($billingType === 'PIX') {
                $descontoPix = $valorCobranca * 0.10; // 10% de desconto
                $valorCobranca = $valorCobranca - $descontoPix;
            }

            // Instancia o serviço do Asaas
            $asaasService = new \App\Services\AsaasService();

            // Verifica se o expositor já tem customer_id no Asaas
            $customerId = $expositor->asaas_customer_id;

            if (empty($customerId)) {
                // Prepara telefone (obrigatório no Asaas)
                $telefone = preg_replace('/[^0-9]/', '', $expositor->telefone ?? '');
                if (empty($telefone)) {
                    $telefone = preg_replace('/[^0-9]/', '', $expositor->celular ?? '');
                }
                if (empty($telefone)) {
                    $retorno['erro'] = 'O expositor precisa ter um telefone cadastrado para gerar cobrança.';
                    return $this->response->setJSON($retorno);
                }

                // Prepara documento (obrigatório no Asaas)
                $documento = preg_replace('/[^0-9]/', '', $expositor->documento);
                if (empty($documento)) {
                    $retorno['erro'] = 'O expositor precisa ter um CPF/CNPJ cadastrado.';
                    return $this->response->setJSON($retorno);
                }

                // Prepara CEP (opcional mas recomendado)
                $cep = preg_replace('/[^0-9]/', '', $expositor->cep ?? '');
                
                // Cria o customer no Asaas
                $customerData = [
                    'nome' => $expositor->nome,
                    'email' => $expositor->email ?? '',
                    'telefone' => $telefone,
                    'cpf' => $documento,
                    'cep' => $cep ?: '00000000', // CEP padrão se vazio
                    'numero' => $expositor->numero ?: 'S/N',
                ];

                // Log para debug
                log_message('debug', 'Asaas - Dados do customer: ' . json_encode($customerData));

                $customerResponse = $asaasService->customers($customerData);

                // Log da resposta
                log_message('debug', 'Asaas - Resposta customer: ' . json_encode($customerResponse));

                if (isset($customerResponse['id'])) {
                    $customerId = $customerResponse['id'];
                    
                    // Salva o customer_id no expositor
                    $expositor->asaas_customer_id = $customerId;
                    $this->expositorModel->save($expositor);
                } else {
                    // Retorna erro detalhado
                    $erroDetalhado = '';
                    if (isset($customerResponse['errors']) && is_array($customerResponse['errors'])) {
                        foreach ($customerResponse['errors'] as $err) {
                            $erroDetalhado .= ($err['description'] ?? 'Erro') . '. ';
                        }
                    } else {
                        $erroDetalhado = json_encode($customerResponse);
                    }
                    
                    $retorno['erro'] = 'Erro ao criar cliente no Asaas: ' . $erroDetalhado;
                    $retorno['debug'] = $customerData;
                    return $this->response->setJSON($retorno);
                }
            }

            // Prepara os dados da cobrança
            $descricao = "Contrato {$contrato->codigo} - " . ($evento->nome ?? 'Evento');
            if ($descontoPix > 0) {
                $descricao .= " (10% desc. PIX)";
            }
            
            $vencimento = date('Y-m-d', strtotime('+7 days'));
            
            if (!empty($contrato->data_vencimento)) {
                $vencimento = $contrato->data_vencimento;
            }

            // Prepara dados da cobrança (com valor já com desconto PIX se aplicável)
            $cobrancaData = [
                'customer_id' => $customerId,
                'billing_type' => $billingType,
                'value' => $valorCobranca, // Já com desconto PIX se aplicável
                'due_date' => $vencimento,
                'description' => $descricao,
                'external_reference' => $contrato->codigo,
            ];

            // Se for parcelado (boleto ou PIX)
            if ($contrato->quantidade_parcelas > 1) {
                $valorParcela = $valorCobranca / $contrato->quantidade_parcelas;
                $cobrancaData['installment_count'] = (int) $contrato->quantidade_parcelas;
                $cobrancaData['installment_value'] = round($valorParcela, 2);
            }

            // Log para debug
            log_message('debug', 'Asaas - Dados da cobrança: ' . json_encode($cobrancaData));

            // Cria a cobrança no Asaas usando o serviço
            $paymentData = $asaasService->criarCobranca($cobrancaData);

            // Log da resposta
            log_message('debug', 'Asaas - Resposta cobrança: ' . json_encode($paymentData));

            if (isset($paymentData['id'])) {
                // Atualiza o contrato com os dados do Asaas
                $contrato->asaas_payment_id = $paymentData['id'];
                $contrato->asaas_invoice_url = $paymentData['invoiceUrl'] ?? $paymentData['bankSlipUrl'] ?? null;
                $contrato->asaas_billing_type = $billingType;
                $contrato->situacao = 'pagamento_aberto';
                
                // Atualiza valor em aberto com o valor da cobrança (já com desconto PIX se aplicável)
                $contrato->valor_em_aberto = $valorCobranca;

                $this->contratoModel->save($contrato);

                $retorno['sucesso'] = 'Cobrança gerada com sucesso!';
                $retorno['payment_id'] = $paymentData['id'];
                $retorno['invoice_url'] = $paymentData['invoiceUrl'] ?? $paymentData['bankSlipUrl'] ?? null;
                $retorno['billing_type'] = $billingType;
                $retorno['forma_pagamento'] = $contrato->forma_pagamento;
                $retorno['valor_original'] = 'R$ ' . number_format($contrato->valor_final, 2, ',', '.');
                $retorno['valor_cobranca'] = 'R$ ' . number_format($valorCobranca, 2, ',', '.');
                $retorno['desconto_pix'] = $descontoPix > 0 ? 'R$ ' . number_format($descontoPix, 2, ',', '.') : null;

                return $this->response->setJSON($retorno);
            } else {
                // Retorna erro detalhado
                $erroDetalhado = '';
                if (isset($paymentData['errors']) && is_array($paymentData['errors'])) {
                    foreach ($paymentData['errors'] as $err) {
                        $erroDetalhado .= ($err['description'] ?? 'Erro') . '. ';
                    }
                } else {
                    $erroDetalhado = json_encode($paymentData);
                }
                
                $retorno['erro'] = 'Erro ao criar cobrança no Asaas: ' . $erroDetalhado;
                return $this->response->setJSON($retorno);
            }

        } catch (\Exception $e) {
            $retorno['erro'] = 'Exceção: ' . $e->getMessage();
            return $this->response->setJSON($retorno);
        }
    }

    /**
     * Método que recupera o contrato
     */
    private function buscaContratoOu404(int $id = null)
    {
        if (!$id || !$contrato = $this->contratoModel->withDeleted(true)->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos o contrato $id");
        }

        return $contrato;
    }
}

