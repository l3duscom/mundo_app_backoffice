<?php

namespace App\Controllers;

use App\Models\AuditoriaModel;
use App\Models\PedidoModel;
use App\Models\ContratoModel;
use App\Models\ContratoParcelaModel;

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
                'descricao' => 'Notificação Asaas pay - ID: ' . $payment_id . ' - Status: ' . $payment_status . ' - Valor: ' . $payment_value,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Tenta atualizar pedido
            $pedidosModel = new PedidoModel();
            $pedidoAtualizado = $pedidosModel
                ->where('charge_id', $payment_id)
                ->set([
                    'status' => $payment_status,
                    'comprovante' => $payment_transactionReceiptUrl,
                    'updated_at' => date('Y-m-d H:i:s')
                ])
                ->update();

            if ($pedidoAtualizado) {
                log_message('info', 'Pedido atualizado com sucesso: ' . $payment_id);
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
                $this->processarPagamentoContrato($contrato, $payment_status, $payment_value, $payment_transactionReceiptUrl, $payment_id);
                log_message('info', 'Contrato atualizado com sucesso: ' . $contrato->codigo);
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
} 