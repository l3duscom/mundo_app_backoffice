<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
    .alert-parcela {
        border-left: 4px solid;
        border-radius: 8px;
        padding: 16px 20px;
        margin-bottom: 16px;
    }
    .alert-parcela.vencida {
        background: rgba(220, 53, 69, 0.1);
        border-color: #dc3545;
    }
    .alert-parcela.proxima {
        background: rgba(255, 193, 7, 0.1);
        border-color: #ffc107;
    }
    .contrato-card {
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 12px;
        margin-bottom: 20px;
        overflow: hidden;
    }
    .contrato-header {
        background: #f8f9fa;
        padding: 16px 20px;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    .valor-destaque {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
    }
    .valor-destaque .valor { font-size: 1.8rem; font-weight: 700; }
    .valor-destaque .label { font-size: 0.8rem; opacity: 0.85; }
    .info-box {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 12px 16px;
    }
    .info-box .label { font-size: 0.75rem; color: #6c757d; text-transform: uppercase; }
    .info-box .value { font-size: 1rem; font-weight: 600; color: #212529; }
    .item-card {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 8px;
    }
    .parcela-row { padding: 12px 0; border-bottom: 1px solid #e9ecef; }
    .parcela-row:last-child { border-bottom: none; }
    .parcela-vencida { background: rgba(220, 53, 69, 0.05); border-radius: 8px; padding: 12px; }
    .parcela-proxima { background: rgba(255, 193, 7, 0.05); border-radius: 8px; padding: 12px; }
    .btn-pagar {
        background: #28a745;
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 6px;
        font-weight: 600;
        text-decoration: none;
    }
    .btn-pagar:hover { background: #218838; color: white; }
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="container-fluid py-4">
    
    <!-- Header de boas-vindas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px;">
                <div class="card-body py-4">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-white bg-opacity-25 p-3 me-3">
                            <i class="bi bi-building text-white" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h4 class="text-white mb-1">Olá, <?= esc($expositor->nome_fantasia ?? $expositor->nome ?? 'Parceiro') ?>!</h4>
                            <p class="text-white-50 mb-0 small">Acompanhe seus contratos e pagamentos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!isset($expositor) || !$expositor) : ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Seu cadastro de expositor ainda não está vinculado à sua conta. Entre em contato com o suporte.
    </div>
    <?php else : ?>

    <?php if (empty($contratos_por_evento)) : ?>
    <div class="text-center py-5">
        <i class="bi bi-file-earmark-text text-muted" style="font-size: 3rem;"></i>
        <h5 class="mt-3 text-muted">Nenhum contrato encontrado</h5>
    </div>
    <?php else : ?>
    
    <?php foreach ($contratos_por_evento as $eventoData) : ?>
    <!-- Evento -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0"><i class="bi bi-calendar-event text-primary me-2"></i><?= esc($eventoData['evento']->nome) ?></h5>
                    <small class="text-muted"><?= date('d/m/Y', strtotime($eventoData['evento']->data_inicio)) ?> - <?= date('d/m/Y', strtotime($eventoData['evento']->data_fim)) ?></small>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <?php foreach ($eventoData['contratos'] as $contratoData) : ?>
            <?php 
            $contrato = $contratoData['contrato']; 
            $itens = $contratoData['itens']; 
            $parcelas = $contratoData['parcelas'];
            $documentos = $contratoData['documentos'] ?? [];
            $valorRestante = $contratoData['valor_restante'] ?? 0;
            $pagamentoCompleto = $contratoData['pagamento_completo'] ?? false;
            
            // Verificar parcelas em atraso ou próximas
            $parcelasVencidas = [];
            $parcelasProximas = [];
            $hoje = time();
            $em7dias = strtotime('+7 days');
            
            foreach ($parcelas as $parcela) {
                if ($parcela->status_local !== 'pago') {
                    $vencimento = strtotime($parcela->data_vencimento);
                    if ($vencimento < $hoje) {
                        $parcelasVencidas[] = $parcela;
                    } elseif ($vencimento <= $em7dias) {
                        $parcelasProximas[] = $parcela;
                    }
                }
            }
            ?>
            
            <div class="contrato-card">
                <!-- Header do Contrato -->
                <div class="contrato-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0"><?= esc($contrato->codigo) ?></h5>
                        <?php if ($contrato->descricao) : ?>
                        <small class="text-muted"><?= esc($contrato->descricao) ?></small>
                        <?php endif; ?>
                    </div>
                    <?php
                    $situacaoClass = match($contrato->situacao) {
                        'pago', 'pagamento_confirmado' => 'bg-success',
                        'pagamento_aberto', 'pagamento_andamento' => 'bg-info',
                        'proposta', 'proposta_aceita' => 'bg-warning',
                        'cancelado', 'banido' => 'bg-danger',
                        default => 'bg-secondary'
                    };
                    $situacaoLabel = match($contrato->situacao) {
                        'proposta' => 'Proposta',
                        'proposta_aceita' => 'Proposta Aceita',
                        'contrato_assinado' => 'Contrato Assinado',
                        'pagamento_aberto' => 'Aguardando Pagamento',
                        'pagamento_andamento' => 'Pagamento em Andamento',
                        'aguardando_contrato' => 'Aguardando Contrato',
                        'pagamento_confirmado' => 'Pago',
                        'cancelado' => 'Cancelado',
                        default => ucfirst($contrato->situacao)
                    };
                    ?>
                    <span class="badge <?= $situacaoClass ?>"><?= $situacaoLabel ?></span>
                </div>
                
                <div class="p-3">
                    <!-- ALERTAS DE PARCELAS -->
                    <?php if (!empty($parcelasVencidas)) : ?>
                    <div class="alert-parcela vencida">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Parcela(s) em atraso!</strong>
                                <p class="mb-0 small text-muted">
                                    <?php foreach ($parcelasVencidas as $pv) : ?>
                                    Parcela <?= $pv->numero_parcela ?> - Venceu em <?= date('d/m/Y', strtotime($pv->data_vencimento)) ?> - R$ <?= number_format($pv->valor, 2, ',', '.') ?><br>
                                    <?php endforeach; ?>
                                </p>
                            </div>
                            <?php if (count($parcelasVencidas) === 1 && $parcelasVencidas[0]->asaas_payment_id) : ?>
                            <?php $payId = str_replace('pay_', '', $parcelasVencidas[0]->asaas_payment_id); ?>
                            <a href="https://www.asaas.com/i/<?= $payId ?>" target="_blank" class="btn-pagar">Pagar Agora</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($parcelasProximas)) : ?>
                    <div class="alert-parcela proxima">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="text-warning"><i class="bi bi-clock-fill me-2"></i>Parcela(s) a vencer em breve</strong>
                                <p class="mb-0 small text-muted">
                                    <?php foreach ($parcelasProximas as $pp) : ?>
                                    Parcela <?= $pp->numero_parcela ?> - Vence em <?= date('d/m/Y', strtotime($pp->data_vencimento)) ?> - R$ <?= number_format($pp->valor, 2, ',', '.') ?><br>
                                    <?php endforeach; ?>
                                </p>
                            </div>
                            <?php if (count($parcelasProximas) === 1 && $parcelasProximas[0]->asaas_payment_id) : ?>
                            <?php $payId = str_replace('pay_', '', $parcelasProximas[0]->asaas_payment_id); ?>
                            <a href="https://www.asaas.com/i/<?= $payId ?>" target="_blank" class="btn-pagar">Pagar</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- VALOR TOTAL EM DESTAQUE -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="valor-destaque">
                                <div class="label">VALOR TOTAL DO CONTRATO</div>
                                <div class="valor">R$ <?= number_format($contrato->valor_final ?? 0, 2, ',', '.') ?></div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="info-box h-100">
                                        <div class="label">Forma de Pagamento</div>
                                        <div class="value"><?= esc($contrato->forma_pagamento ?? 'Não definido') ?></div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-box h-100">
                                        <div class="label">Parcelas</div>
                                        <div class="value"><?= $contrato->quantidade_parcelas ?>x de R$ <?= number_format(($contrato->valor_final ?? 0) / max(1, $contrato->quantidade_parcelas), 2, ',', '.') ?></div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-box h-100">
                                        <div class="label">Valor Pago</div>
                                        <div class="value text-success">R$ <?= number_format($contrato->valor_pago ?? 0, 2, ',', '.') ?></div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-box h-100">
                                        <div class="label">Valor Restante</div>
                                        <div class="value <?= $valorRestante > 0 ? 'text-danger' : 'text-success' ?>">R$ <?= number_format($valorRestante, 2, ',', '.') ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ITENS DO CONTRATO EM DESTAQUE -->
                    <?php if (!empty($itens)) : ?>
                    <div class="mb-4">
                        <h6 class="mb-3"><i class="bi bi-box-seam text-primary me-2"></i>Itens Contratados</h6>
                        <?php foreach ($itens as $item) : ?>
                        <div class="item-card d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-primary me-2"><?= esc($item->tipo_item) ?></span>
                                <?= esc($item->descricao ?? '') ?>
                                <?php if ($item->localizacao) : ?><small class="text-muted ms-2">(<?= esc($item->localizacao) ?>)</small><?php endif; ?>
                            </div>
                            <div class="text-end">
                                <span class="text-muted small"><?= $item->quantidade ?>x R$ <?= number_format($item->valor_unitario, 2, ',', '.') ?></span>
                                <strong class="ms-2">R$ <?= number_format($item->valor_total, 2, ',', '.') ?></strong>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- PARCELAS -->
                    <?php if (!empty($parcelas)) : ?>
                    <div>
                        <h6 class="mb-3"><i class="bi bi-credit-card text-primary me-2"></i>Parcelas</h6>
                        <?php foreach ($parcelas as $parcela) : ?>
                        <?php
                        $vencimento = strtotime($parcela->data_vencimento);
                        $isVencida = $parcela->status_local !== 'pago' && $vencimento < $hoje;
                        $isProxima = $parcela->status_local !== 'pago' && $vencimento >= $hoje && $vencimento <= $em7dias;
                        $rowClass = $isVencida ? 'parcela-vencida' : ($isProxima ? 'parcela-proxima' : 'parcela-row');
                        ?>
                        <div class="<?= $rowClass ?>">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Parcela <?= $parcela->numero_parcela ?></strong>
                                    <span class="text-muted mx-2">•</span>
                                    <span class="<?= $isVencida ? 'text-danger fw-bold' : '' ?>">
                                        <?= date('d/m/Y', $vencimento) ?>
                                    </span>
                                    <?php if ($parcela->status_local === 'pago' && $parcela->data_pagamento) : ?>
                                    <span class="text-success small ms-2"><i class="bi bi-check-circle-fill"></i> Pago em <?= date('d/m/Y', strtotime($parcela->data_pagamento)) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <strong>R$ <?= number_format($parcela->valor, 2, ',', '.') ?></strong>
                                    <?php if ($parcela->status_local === 'pago') : ?>
                                    <span class="badge bg-success">Pago</span>
                                    <?php elseif ($isVencida) : ?>
                                    <span class="badge bg-danger">Vencida</span>
                                    <?php if ($parcela->asaas_payment_id) : ?>
                                    <?php $payId = str_replace('pay_', '', $parcela->asaas_payment_id); ?>
                                    <a href="https://www.asaas.com/i/<?= $payId ?>" target="_blank" class="btn-pagar btn-sm">Pagar</a>
                                    <?php endif; ?>
                                    <?php elseif ($isProxima) : ?>
                                    <span class="badge bg-warning text-dark">A vencer</span>
                                    <?php if ($parcela->asaas_payment_id) : ?>
                                    <?php $payId = str_replace('pay_', '', $parcela->asaas_payment_id); ?>
                                    <a href="https://www.asaas.com/i/<?= $payId ?>" target="_blank" class="btn-pagar btn-sm">Pagar</a>
                                    <?php endif; ?>
                                    <?php else : ?>
                                    <span class="badge bg-secondary">Pendente</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- DOCUMENTOS DO CONTRATO -->
                    <div class="mt-4">
                        <h6 class="mb-3"><i class="bi bi-file-earmark-text text-primary me-2"></i>Documentos do Contrato</h6>
                        
                        <?php if (!$pagamentoCompleto) : ?>
                        <div class="alert alert-secondary py-2 small">
                            <i class="bi bi-lock-fill me-2"></i>
                            Os documentos estarão disponíveis após a confirmação do pagamento completo.
                        </div>
                        <?php endif; ?>
                        
                        <?php if (empty($documentos)) : ?>
                        <div class="text-muted small <?= !$pagamentoCompleto ? 'opacity-50' : '' ?>">
                            <i class="bi bi-file-earmark me-1"></i> Nenhum documento gerado ainda.
                        </div>
                        <?php else : ?>
                        <div class="<?= !$pagamentoCompleto ? 'opacity-50' : '' ?>" style="<?= !$pagamentoCompleto ? 'pointer-events: none;' : '' ?>">
                            <?php foreach ($documentos as $doc) : ?>
                            <?php
                            $statusClass = match($doc->status) {
                                'confirmado' => 'bg-success',
                                'assinado' => 'bg-primary',
                                'pendente', 'pendente_assinatura' => 'bg-warning text-dark',
                                'rascunho' => 'bg-secondary',
                                'cancelado' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                            $statusLabel = match($doc->status) {
                                'confirmado' => 'Confirmado',
                                'assinado' => 'Assinado',
                                'pendente', 'pendente_assinatura' => 'Pendente de Assinatura',
                                'rascunho' => 'Rascunho',
                                'cancelado' => 'Cancelado',
                                default => ucfirst($doc->status)
                            };
                            ?>
                            <div class="item-card d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                                    <strong><?= esc($doc->titulo ?? 'Contrato') ?></strong>
                                    <span class="badge <?= $statusClass ?> ms-2"><?= $statusLabel ?></span>
                                    <?php if ($doc->data_assinatura) : ?>
                                    <small class="text-muted ms-2">Assinado em <?= date('d/m/Y', strtotime($doc->data_assinatura)) ?></small>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <?php if ($doc->arquivo_url && $pagamentoCompleto) : ?>
                                    <a href="<?= $doc->arquivo_url ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-download me-1"></i>Baixar
                                    </a>
                                    <?php elseif (in_array($doc->status, ['pendente', 'pendente_assinatura']) && $pagamentoCompleto && $doc->hash_assinatura) : ?>
                                    <a href="<?= site_url('contratodocumentos/assinar/' . $doc->hash_assinatura) ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pen me-1"></i>Assinar
                                    </a>
                                    <?php else : ?>
                                    <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
    
    <?php endif; ?>
    <?php endif; ?>

</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<?php echo $this->endSection() ?>
