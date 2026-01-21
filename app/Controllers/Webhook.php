<?php

namespace App\Controllers;

use App\Models\AuditoriaModel;
use App\Models\PedidoModel;
use App\Models\ContratoModel;
use App\Models\ContratoParcelaModel;
use App\Models\AssinaturaModel;
use App\Models\AssinaturaHistoricoModel;
use App\Models\UsuarioModel;
use App\Models\PlanoModel;
use App\Services\PontosCompraService;

class Webhook extends BaseController
{
    public function backoffice()
    {
        // Log do início da notificação
        log_message('info', 'Webhook ASAAS recebido: ' . $this->request->getBody());
        
        // Verifica se é uma requisição POST
        if ($this->request->getMethod() !== 'post') {
            log_message('error', 'Método não permitido: ' . $this->request->getMethod());
            return $this->response->setJSON(['error' => 'Método não permitido'])->setStatusCode(405);
        }
        
        $json = $this->request->getBody();
        $data = json_decode($json, true);

        // Log dos dados recebidos
        log_message('info', 'Dados decodificados: ' . json_encode($data));

        if (!$data || !isset($data['payment'])) {
            log_message('error', 'Dados de pagamento ausentes ou inválidos: ' . $json);
            return $this->response->setJSON(['error' => 'Dados de pagamento ausentes ou inválidos'])->setStatusCode(400);
        }

        $payment_id = $data['payment']['id'] ?? null;
        $payment_status = $data['payment']['status'] ?? null;
        $payment_value = $data['payment']['value'] ?? 0;
        $payment_netValue = $data['payment']['netValue'] ?? 0;
        $payment_transactionReceiptUrl = $data['payment']['transactionReceiptUrl'] ?? null;
        $payment_externalReference = $data['payment']['externalReference'] ?? null;
        $payment_billingType = $data['payment']['billingType'] ?? null;

        // Log dos valores extraídos
        log_message('info', 'Payment ID: ' . $payment_id);
        log_message('info', 'Payment Status: ' . $payment_status);
        log_message('info', 'Payment Value: ' . $payment_value);
        log_message('info', 'External Reference: ' . $payment_externalReference);

        // Validações básicas
        if (!$payment_id || !$payment_status) {
            log_message('error', 'Dados obrigatórios não encontrados');
            return $this->response->setJSON(['error' => 'Dados obrigatórios não encontrados'])->setStatusCode(400);
        }

        try {
            // Auditoria simples
            $auditoriaModel = new AuditoriaModel();
            $auditoriaModel->insert([
                'acao' => 'Webhook ASAAS',
                'descricao' => 'Notificação Asaas pay - ID: ' . $payment_id . ' - Status: ' . $payment_status . ' - Valor: ' . $payment_value . ' - Líquido: ' . $payment_netValue,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Monta dados para atualização do pedido
            $dadosAtualizacao = [
                'status' => $payment_status,
                'comprovante' => $payment_transactionReceiptUrl,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Só salva valor_liquido em pagamentos confirmados e se netValue > 0
            if (in_array($payment_status, ['CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH']) && $payment_netValue > 0) {
                $dadosAtualizacao['valor_liquido'] = $payment_netValue;
                log_message('info', 'Valor líquido salvo: R$ ' . $payment_netValue);
            }

            // Tenta atualizar pedido
            $pedidosModel = new PedidoModel();
            $pedidoAtualizado = $pedidosModel
                ->where('charge_id', $payment_id)
                ->set($dadosAtualizacao)
                ->update();

            if ($pedidoAtualizado) {
                log_message('info', 'Pedido atualizado com sucesso: ' . $payment_id);
                
                // Atribui pontos se o pagamento foi confirmado
                $statusConfirmados = ['RECEIVED', 'CONFIRMED', 'paid', 'RECEIVED_IN_CASH'];
                if (in_array($payment_status, $statusConfirmados)) {
                    // Busca o pedido para obter o ID
                    $pedido = $pedidosModel->where('charge_id', $payment_id)->first();
                    if ($pedido) {
                        $pontosService = new PontosCompraService();
                        $pontosResult = $pontosService->atribuirPontosDoPedido($pedido->id);
                        
                        if ($pontosResult['success']) {
                            log_message('info', 'Pontos atribuídos para pedido #' . $pedido->id . ': ' . ($pontosResult['data']['pontos'] ?? 0) . ' pontos');
                        } else {
                            log_message('info', 'Pontos: ' . ($pontosResult['message'] ?? 'Já atribuídos ou valor zero'));
                        }
                    }
                }
            }

            // Tenta atualizar contrato (pelo payment_id ou external_reference)
            $contratoModel = new ContratoModel();
            
            // Busca contrato pelo asaas_payment_id ou pelo código (external_reference)
            $contrato = $contratoModel->where('asaas_payment_id', $payment_id)->first();
            
            if (!$contrato && $payment_externalReference) {
                // Tenta buscar pelo código do contrato (CTR-2025-0001)
                $contrato = $contratoModel->where('codigo', $payment_externalReference)->first();
            }
            
            // Se ainda não encontrou, tenta buscar pela parcela
            if (!$contrato) {
                $parcelaModel = new ContratoParcelaModel();
                $parcela = $parcelaModel->buscaPorAsaasId($payment_id);
                if ($parcela) {
                    $contrato = $contratoModel->find($parcela->contrato_id);
                }
            }

            if ($contrato) {
                // Sincroniza todas as parcelas do Asaas antes de processar
                $contratosController = new \App\Controllers\Contratos();
                $contratosController->sincronizarParcelasAsaas($contrato);
                
                // Recarrega o contrato após sincronização
                $contrato = $contratoModel->find($contrato->id);
                
                $this->processarPagamentoContrato($contrato, $payment_status, $payment_value, $payment_transactionReceiptUrl, $payment_id);
                log_message('info', 'Contrato atualizado com sucesso: ' . $contrato->codigo);
            }

            // ========================================
            // PROCESSAMENTO DE ASSINATURAS
            // ========================================
            $payment_subscription = $data['payment']['subscription'] ?? null;
            
            if ($payment_subscription) {
                log_message('info', 'Pagamento de assinatura detectado: ' . $payment_subscription);
                $this->processarPagamentoAssinatura(
                    $payment_subscription,
                    $payment_status,
                    $payment_value,
                    $payment_billingType,
                    $payment_id
                );
            }

            return $this->response->setJSON(['status' => 'success', 'message' => 'Webhook processado com sucesso']);

        } catch (\Exception $e) {
            log_message('error', 'Exceção no webhook: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Erro interno: ' . $e->getMessage()])->setStatusCode(500);
        }
    }

    /**
     * Processa pagamento de contrato baseado no status do Asaas
     */
    private function processarPagamentoContrato($contrato, string $status, float $valor, ?string $comprovante, ?string $paymentId = null)
    {
        $contratoModel = new ContratoModel();
        $parcelaModel = new ContratoParcelaModel();
        
        // Atualiza a parcela específica no banco se tiver paymentId
        if ($paymentId) {
            $parcelaModel->atualizarPorAsaasId($paymentId, [
                'status' => $status,
                'data_pagamento' => in_array($status, ['CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH']) ? date('Y-m-d') : null,
                'comprovante_url' => $comprovante,
            ]);
        }
        
        // Recalcula totais baseado nas parcelas do banco
        $totaisParcelas = $parcelaModel->calculaTotais($contrato->id);
        
        // Se tem parcelas no banco, usa os totais das parcelas
        if ($totaisParcelas['quantidade'] > 0) {
            $novoValorPago = $totaisParcelas['pago'];
            $novoValorEmAberto = $totaisParcelas['pendente'];
            
            // Determina nova situação
            // Quando pagamento total é confirmado, vai para aguardando_contrato (precisa assinar documento)
            if ($totaisParcelas['pendentes'] === 0 && $totaisParcelas['pagas'] > 0) {
                // Verifica se já tem documento confirmado
                $documentoModel = new \App\Models\ContratoDocumentoModel();
                if ($documentoModel->temDocumentoConfirmado($contrato->id)) {
                    $novaSituacao = 'pagamento_confirmado';
                } else {
                    $novaSituacao = 'aguardando_contrato';
                }
            } elseif ($totaisParcelas['pagas'] > 0 && $totaisParcelas['pendentes'] > 0) {
                $novaSituacao = 'pagamento_andamento';
            } else {
                $novaSituacao = $contrato->situacao;
            }
            
            $contratoModel->update($contrato->id, [
                'valor_pago' => $novoValorPago,
                'valor_em_aberto' => $novoValorEmAberto,
                'situacao' => $novaSituacao,
                'data_pagamento' => $novaSituacao === 'pagamento_confirmado' ? date('Y-m-d') : $contrato->data_pagamento,
            ]);
            
            log_message('info', "Contrato {$contrato->codigo}: Atualizado via webhook. Pago: R$ {$novoValorPago}, Em aberto: R$ {$novoValorEmAberto}, Status: {$novaSituacao}");
            return;
        }
        
        // Fallback: Mapeia status do Asaas para ações (quando não tem parcelas no banco)
        switch ($status) {
            case 'CONFIRMED':
            case 'RECEIVED':
            case 'RECEIVED_IN_CASH':
                // Pagamento confirmado - acumula valor pago
                $novoValorPago = ($contrato->valor_pago ?? 0) + $valor;
                
                // Calcula valor total com desconto PIX se aplicável
                $valorTotal = $contrato->valor_final;
                if ($contrato->forma_pagamento === 'PIX') {
                    $valorTotal = $contrato->valor_final * 0.90;
                }
                
                $novoValorEmAberto = $valorTotal - $novoValorPago;
                
                // Determina nova situação
                // Quando pagamento total é confirmado, vai para aguardando_contrato (precisa assinar documento)
                if ($novoValorEmAberto <= 0) {
                    $documentoModel = new \App\Models\ContratoDocumentoModel();
                    if ($documentoModel->temDocumentoConfirmado($contrato->id)) {
                        $novaSituacao = 'pagamento_confirmado';
                    } else {
                        $novaSituacao = 'aguardando_contrato';
                    }
                    $novoValorEmAberto = 0;
                } else {
                    $novaSituacao = 'pagamento_andamento';
                }
                
                $contratoModel->update($contrato->id, [
                    'valor_pago' => $novoValorPago,
                    'valor_em_aberto' => $novoValorEmAberto,
                    'situacao' => $novaSituacao,
                    'data_pagamento' => date('Y-m-d'),
                    'arquivo_comprovante' => $comprovante,
                ]);
                
                log_message('info', "Contrato {$contrato->codigo}: Pagamento de R$ {$valor} confirmado. Total pago: R$ {$novoValorPago}. Status: {$novaSituacao}");
                break;
                
            case 'PENDING':
            case 'AWAITING_RISK_ANALYSIS':
                // Pagamento pendente
                if ($contrato->situacao === 'proposta_aceita' || $contrato->situacao === 'contrato_assinado') {
                    $contratoModel->update($contrato->id, [
                        'situacao' => 'pagamento_aberto',
                    ]);
                }
                break;
                
            case 'OVERDUE':
                // Pagamento vencido - mantém status mas pode adicionar flag
                log_message('warning', "Contrato {$contrato->codigo}: Pagamento vencido!");
                break;
                
            case 'REFUNDED':
            case 'REFUND_REQUESTED':
                // Estorno - reverte valor pago
                $novoValorPago = max(0, ($contrato->valor_pago ?? 0) - $valor);
                $valorTotal = $contrato->valor_final;
                if ($contrato->forma_pagamento === 'PIX') {
                    $valorTotal = $contrato->valor_final * 0.90;
                }
                
                $contratoModel->update($contrato->id, [
                    'valor_pago' => $novoValorPago,
                    'valor_em_aberto' => $valorTotal - $novoValorPago,
                    'situacao' => 'pagamento_aberto',
                ]);
                
                log_message('info', "Contrato {$contrato->codigo}: Estorno de R$ {$valor}. Novo valor pago: R$ {$novoValorPago}");
                break;
        }
    }

    /**
     * Processa pagamento de assinatura baseado no status do Asaas
     * 
     * @param string $subscriptionId ID da assinatura no Asaas
     * @param string $status Status do pagamento
     * @param float $valor Valor do pagamento
     * @param string|null $formaPagamento Forma de pagamento (PIX, CREDIT_CARD, BOLETO)
     * @param string|null $paymentId ID do pagamento no Asaas
     */
    private function processarPagamentoAssinatura(
        string $subscriptionId,
        string $status,
        float $valor,
        ?string $formaPagamento = null,
        ?string $paymentId = null
    ): void {
        $assinaturaModel = new AssinaturaModel();
        $historicoModel = new AssinaturaHistoricoModel();
        $usuarioModel = new UsuarioModel();
        $planoModel = new PlanoModel();

        // Busca assinatura pelo ID do Asaas
        $assinatura = $assinaturaModel->where('asaas_subscription_id', $subscriptionId)->first();

        if (!$assinatura) {
            log_message('warning', "Assinatura não encontrada para subscription_id: {$subscriptionId}");
            return;
        }

        log_message('info', "Processando assinatura #{$assinatura->id} - Status: {$status}");

        // Mapeia status do Asaas para ações
        switch ($status) {
            case 'CONFIRMED':
            case 'RECEIVED':
            case 'RECEIVED_IN_CASH':
                // Pagamento confirmado - ativa assinatura
                $plano = $planoModel->find($assinatura->plano_id);
                
                // Calcula próxima data de vencimento baseada no ciclo do plano
                $dataFim = new \DateTime();
                if ($plano && $plano->ciclo === 'YEARLY') {
                    $dataFim->add(new \DateInterval('P1Y'));
                } else {
                    $dataFim->add(new \DateInterval('P1M'));
                }

                // Atualiza assinatura
                $assinaturaModel->update($assinatura->id, [
                    'status' => 'ACTIVE',
                    'data_inicio' => $assinatura->data_inicio ?? date('Y-m-d H:i:s'),
                    'data_fim' => $dataFim->format('Y-m-d H:i:s'),
                    'proximo_vencimento' => $dataFim->format('Y-m-d'),
                    'valor_pago' => ($assinatura->valor_pago ?? 0) + $valor,
                    'forma_pagamento' => $formaPagamento,
                ]);

                // Atualiza usuário como premium
                $usuarioModel->protect(false)->update($assinatura->usuario_id, [
                    'is_premium' => 1,
                    'premium_ate' => $dataFim->format('Y-m-d H:i:s'),
                    'asaas_subscription_id' => $subscriptionId,
                ]);

                // Registra histórico
                $historicoModel->registra($assinatura->id, 'PAYMENT_CONFIRMED', 
                    "Pagamento de R$ " . number_format($valor, 2, ',', '.') . " confirmado via webhook", 
                    ['payment_id' => $paymentId, 'valor' => $valor, 'forma_pagamento' => $formaPagamento]
                );

                // ==== REGISTRA NO MÓDULO FINANCEIRO ====
                $this->registrarLancamentoAssinatura($assinatura, $valor, $formaPagamento, $paymentId, $plano);

                log_message('info', "Assinatura #{$assinatura->id}: Ativada. Usuário #{$assinatura->usuario_id} marcado como premium até {$dataFim->format('d/m/Y')}");
                break;

            case 'OVERDUE':
                // Pagamento atrasado
                $assinaturaModel->update($assinatura->id, [
                    'status' => 'OVERDUE',
                ]);

                $historicoModel->registra($assinatura->id, 'PAYMENT_FAILED', 
                    "Pagamento atrasado detectado via webhook"
                );

                log_message('warning', "Assinatura #{$assinatura->id}: Pagamento atrasado!");
                break;

            case 'REFUNDED':
            case 'REFUND_REQUESTED':
                // Estorno - pode precisar ajustar premium
                $historicoModel->registra($assinatura->id, 'PAYMENT_FAILED', 
                    "Pagamento estornado: R$ " . number_format($valor, 2, ',', '.'),
                    ['payment_id' => $paymentId, 'valor' => $valor]
                );

                log_message('info', "Assinatura #{$assinatura->id}: Pagamento estornado.");
                break;

            case 'DELETED':
            case 'CANCELLED':
                // Assinatura cancelada
                $assinaturaModel->update($assinatura->id, [
                    'status' => 'CANCELLED',
                    'data_fim' => date('Y-m-d H:i:s'),
                ]);

                // Remove premium do usuário
                $usuarioModel->protect(false)->update($assinatura->usuario_id, [
                    'is_premium' => 0,
                    'premium_ate' => null,
                ]);

                $historicoModel->registra($assinatura->id, 'CANCELLED', 
                    "Assinatura cancelada via webhook"
                );

                log_message('info', "Assinatura #{$assinatura->id}: Cancelada. Premium removido do usuário #{$assinatura->usuario_id}");
                break;

            case 'PENDING':
            case 'AWAITING_RISK_ANALYSIS':
                // Pagamento pendente - apenas log
                log_message('info', "Assinatura #{$assinatura->id}: Pagamento pendente.");
                break;

            default:
                log_message('info', "Assinatura #{$assinatura->id}: Status {$status} não requer ação especial.");
                break;
        }
    }

    /**
     * Registra lançamento financeiro para pagamento de assinatura
     * 
     * @param object $assinatura Entidade da assinatura
     * @param float $valor Valor do pagamento
     * @param string|null $formaPagamento Forma de pagamento
     * @param string|null $paymentId ID do pagamento no Asaas
     * @param object|null $plano Entidade do plano
     */
    private function registrarLancamentoAssinatura(
        $assinatura,
        float $valor,
        ?string $formaPagamento = null,
        ?string $paymentId = null,
        $plano = null
    ): void {
        $lancamentoModel = new \App\Models\LancamentoFinanceiroModel();
        
        // Verifica se já existe lançamento para este payment_id (evita duplicação)
        if ($paymentId && $lancamentoModel->where('observacoes', 'LIKE', "%{$paymentId}%")->countAllResults() > 0) {
            log_message('info', "Lançamento financeiro já existe para payment_id: {$paymentId}");
            return;
        }

        // Busca dados do usuário para descrição
        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel->find($assinatura->usuario_id);
        
        $descricao = "Assinatura #{$assinatura->id}";
        if ($usuario) {
            $descricao .= " - " . $usuario->nome;
        }
        if ($plano) {
            $descricao .= " - " . $plano->nome;
        }

        $data = [
            'event_id' => null, // Assinaturas não são vinculadas a eventos
            'tipo' => 'ENTRADA',
            'origem' => 'ASSINATURA',
            'referencia_tipo' => 'assinaturas',
            'referencia_id' => $assinatura->id,
            'descricao' => $descricao,
            'valor' => $valor,
            'valor_liquido' => $valor * 0.97, // Estima taxa Asaas ~3%
            'data_lancamento' => date('Y-m-d'),
            'data_pagamento' => date('Y-m-d'),
            'status' => 'pago',
            'forma_pagamento' => $formaPagamento ?? 'PIX',
            'categoria' => 'Assinaturas Premium',
            'observacoes' => "Payment ID: {$paymentId}",
        ];

        $lancamentoModel->insert($data);
        log_message('info', "Lançamento financeiro registrado para assinatura #{$assinatura->id}");
    }
} 