<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
    .contract-card {
        background: linear-gradient(145deg, #1a1d21 0%, #2d3238 100%);
        border-radius: 16px;
        border: 1px solid rgba(255,255,255,0.08);
        transition: all 0.3s ease;
    }
    .contract-card:hover {
        border-color: rgba(102, 126, 234, 0.3);
        transform: translateY(-2px);
    }
    .stat-card {
        background: rgba(255,255,255,0.03);
        border-radius: 12px;
        padding: 16px;
        text-align: center;
        border: 1px solid rgba(255,255,255,0.05);
    }
    .stat-card.total { border-left: 3px solid #667eea; }
    .stat-card.paid { border-left: 3px solid #10b981; }
    .stat-card.remaining { border-left: 3px solid #ef4444; }
    .stat-card.progress { border-left: 3px solid #f59e0b; }
    .stat-value { font-size: 1.25rem; font-weight: 700; }
    .stat-label { font-size: 0.75rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5px; }
    .progress-bar-wrapper {
        height: 6px;
        background: rgba(255,255,255,0.1);
        border-radius: 3px;
        overflow: hidden;
    }
    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #10b981 0%, #34d399 100%);
        border-radius: 3px;
        transition: width 0.5s ease;
    }
    .action-btn {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        color: #e5e7eb;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }
    .action-btn:hover {
        background: rgba(255,255,255,0.1);
        border-color: rgba(255,255,255,0.2);
        color: #fff;
    }
    .action-btn.active { background: rgba(102, 126, 234, 0.2); border-color: #667eea; color: #667eea; }
    .section-collapse {
        background: rgba(0,0,0,0.2);
        border-radius: 12px;
        padding: 16px;
        margin-top: 16px;
    }
    .table-modern {
        border-collapse: separate;
        border-spacing: 0;
    }
    .table-modern thead th {
        background: rgba(255,255,255,0.05);
        border: none;
        padding: 12px 16px;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #9ca3af;
    }
    .table-modern tbody td {
        padding: 12px 16px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        vertical-align: middle;
    }
    .table-modern tbody tr:hover { background: rgba(255,255,255,0.02); }
    .badge-status {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .event-header {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        border-radius: 12px 12px 0 0;
        padding: 20px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .detail-row {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        padding: 12px 0;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .detail-item {
        flex: 1;
        min-width: 150px;
    }
    .detail-label { font-size: 0.75rem; color: #6b7280; margin-bottom: 4px; }
    .detail-value { font-size: 0.95rem; color: #e5e7eb; }
    .btn-pay {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border: none;
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .btn-pay:hover { transform: scale(1.02); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); color: white; }
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="container-fluid py-4">
    
    <!-- Header de boas-vindas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px;">
                <div class="card-body py-4 px-4">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px; background: rgba(255,255,255,0.2);">
                            <i class="bi bi-building text-white" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h3 class="text-white mb-1 fw-bold">Olá, <?= esc($expositor->nome_fantasia ?? $expositor->nome ?? 'Parceiro') ?>!</h3>
                            <p class="text-white-50 mb-0">Acompanhe seus contratos e pagamentos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!isset($expositor) || !$expositor) : ?>
    <div class="alert alert-warning border-0 shadow-sm" style="border-radius: 12px;">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Seu cadastro de expositor ainda não está vinculado à sua conta. Entre em contato com o suporte.
    </div>
    <?php else : ?>

    <!-- Contratos por Evento -->
    <?php if (empty($contratos_por_evento)) : ?>
    <div class="text-center py-5">
        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; background: rgba(255,255,255,0.05);">
            <i class="bi bi-file-earmark-text" style="font-size: 2rem; color: #6b7280;"></i>
        </div>
        <h5 class="text-muted mb-2">Nenhum contrato encontrado</h5>
        <p class="text-muted small">Seus contratos aparecerão aqui quando forem cadastrados.</p>
    </div>
    <?php else : ?>
    
    <?php foreach ($contratos_por_evento as $eventoData) : ?>
    <div class="card mb-4 border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
        <!-- Header do Evento -->
        <div class="event-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-1 text-white fw-bold">
                        <i class="bi bi-calendar-event me-2 text-primary"></i><?= esc($eventoData['evento']->nome) ?>
                    </h5>
                    <span class="text-muted small">
                        <i class="bi bi-clock me-1"></i>
                        <?= date('d/m/Y', strtotime($eventoData['evento']->data_inicio)) ?> 
                        até <?= date('d/m/Y', strtotime($eventoData['evento']->data_fim)) ?>
                    </span>
                </div>
                <span class="badge bg-primary bg-opacity-25 text-primary px-3 py-2" style="border-radius: 20px;">
                    <?= count($eventoData['contratos']) ?> contrato(s)
                </span>
            </div>
        </div>
        
        <div class="card-body p-0">
            <?php foreach ($eventoData['contratos'] as $contratoData) : ?>
            <?php 
            $contrato = $contratoData['contrato']; 
            $itens = $contratoData['itens']; 
            $parcelas = $contratoData['parcelas']; 
            $valorRestante = $contratoData['valor_restante'] ?? 0;
            $porcentagemPaga = $contratoData['porcentagem_paga'] ?? 0;
            
            $situacaoClass = match($contrato->situacao) {
                'pago', 'pagamento_confirmado' => 'bg-success',
                'ativo', 'assinado', 'contrato_assinado' => 'bg-primary',
                'pendente', 'proposta', 'proposta_aceita' => 'bg-warning text-dark',
                'pagamento_aberto', 'pagamento_andamento' => 'bg-info',
                'aguardando_contrato' => 'bg-secondary',
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
                'banido' => 'Banido',
                default => ucfirst($contrato->situacao)
            };
            ?>
            
            <div class="contract-card m-3 p-4">
                <!-- Contrato Header -->
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                    <div>
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <h4 class="mb-0 text-white fw-bold"><?= esc($contrato->codigo) ?></h4>
                            <span class="badge-status <?= $situacaoClass ?>"><?= $situacaoLabel ?></span>
                        </div>
                        <?php if ($contrato->descricao) : ?>
                        <p class="text-muted small mb-0"><?= esc($contrato->descricao) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="stat-card total">
                            <div class="stat-label">Valor Total</div>
                            <div class="stat-value text-white">R$ <?= number_format($contrato->valor_final ?? 0, 2, ',', '.') ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card paid">
                            <div class="stat-label">Valor Pago</div>
                            <div class="stat-value text-success">R$ <?= number_format($contrato->valor_pago ?? 0, 2, ',', '.') ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card remaining">
                            <div class="stat-label">Restante</div>
                            <div class="stat-value <?= $valorRestante > 0 ? 'text-danger' : 'text-success' ?>">R$ <?= number_format($valorRestante, 2, ',', '.') ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card progress">
                            <div class="stat-label">Progresso</div>
                            <div class="stat-value <?= $porcentagemPaga >= 100 ? 'text-success' : 'text-warning' ?>"><?= $porcentagemPaga ?>%</div>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="progress-bar-wrapper mb-4">
                    <div class="progress-bar-fill" style="width: <?= min($porcentagemPaga, 100) ?>%"></div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <button class="action-btn" type="button" data-bs-toggle="collapse" data-bs-target="#detalhes<?= $contrato->id ?>">
                        <i class="bi bi-info-circle me-1"></i> Detalhes
                    </button>
                    <?php if (!empty($itens)) : ?>
                    <button class="action-btn" type="button" data-bs-toggle="collapse" data-bs-target="#itens<?= $contrato->id ?>">
                        <i class="bi bi-box-seam me-1"></i> Itens (<?= count($itens) ?>)
                    </button>
                    <?php endif; ?>
                    <?php if (!empty($parcelas)) : ?>
                    <button class="action-btn" type="button" data-bs-toggle="collapse" data-bs-target="#parcelas<?= $contrato->id ?>">
                        <i class="bi bi-credit-card me-1"></i> Parcelas (<?= count($parcelas) ?>)
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Detalhes Collapse -->
                <div class="collapse" id="detalhes<?= $contrato->id ?>">
                    <div class="section-collapse">
                        <h6 class="text-muted mb-3"><i class="bi bi-file-text me-2"></i>Informações do Contrato</h6>
                        <div class="row g-3">
                            <div class="col-md-3 col-6">
                                <div class="detail-label">Valor Original</div>
                                <div class="detail-value">R$ <?= number_format($contrato->valor_original ?? $contrato->valor_final ?? 0, 2, ',', '.') ?></div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="detail-label">Desconto</div>
                                <div class="detail-value text-danger">- R$ <?= number_format($contrato->valor_desconto ?? 0, 2, ',', '.') ?></div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="detail-label">Parcelas</div>
                                <div class="detail-value"><?= $contrato->quantidade_parcelas ?>x de R$ <?= number_format(($contrato->valor_final ?? 0) / max(1, $contrato->quantidade_parcelas), 2, ',', '.') ?></div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="detail-label">Forma de Pagamento</div>
                                <div class="detail-value"><?= esc($contrato->forma_pagamento ?? 'Não definido') ?></div>
                            </div>
                        </div>
                        <hr class="my-3 opacity-10">
                        <div class="row g-3">
                            <div class="col-md-3 col-6">
                                <div class="detail-label">Data da Proposta</div>
                                <div class="detail-value"><?= $contrato->data_proposta ? date('d/m/Y', strtotime($contrato->data_proposta)) : '-' ?></div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="detail-label">Data do Aceite</div>
                                <div class="detail-value"><?= $contrato->data_aceite ? date('d/m/Y', strtotime($contrato->data_aceite)) : '-' ?></div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="detail-label">Data de Assinatura</div>
                                <div class="detail-value"><?= $contrato->data_assinatura ? date('d/m/Y', strtotime($contrato->data_assinatura)) : '-' ?></div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="detail-label">Vencimento</div>
                                <?php $vencido = $contrato->data_vencimento && strtotime($contrato->data_vencimento) < time() && $valorRestante > 0; ?>
                                <div class="detail-value <?= $vencido ? 'text-danger fw-bold' : '' ?>"><?= $contrato->data_vencimento ? date('d/m/Y', strtotime($contrato->data_vencimento)) : '-' ?></div>
                            </div>
                        </div>
                        <?php if (!empty($contrato->observacoes)) : ?>
                        <hr class="my-3 opacity-10">
                        <div class="detail-label">Observações</div>
                        <div class="detail-value"><?= nl2br(esc($contrato->observacoes)) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Itens Collapse -->
                <?php if (!empty($itens)) : ?>
                <div class="collapse" id="itens<?= $contrato->id ?>">
                    <div class="section-collapse">
                        <h6 class="text-muted mb-3"><i class="bi bi-box-seam me-2"></i>Itens Contratados</h6>
                        <div class="table-responsive">
                            <table class="table table-modern mb-0">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Descrição</th>
                                        <th class="text-center">Qtd</th>
                                        <th class="text-end">Valor Unit.</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($itens as $item) : ?>
                                    <tr>
                                        <td><span class="badge bg-secondary bg-opacity-25 text-secondary"><?= esc($item->tipo_item) ?></span></td>
                                        <td><?= esc($item->descricao ?? '-') ?></td>
                                        <td class="text-center"><?= $item->quantidade ?></td>
                                        <td class="text-end">R$ <?= number_format($item->valor_unitario, 2, ',', '.') ?></td>
                                        <td class="text-end fw-bold">R$ <?= number_format($item->valor_total, 2, ',', '.') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Parcelas Collapse -->
                <?php if (!empty($parcelas)) : ?>
                <div class="collapse" id="parcelas<?= $contrato->id ?>">
                    <div class="section-collapse">
                        <h6 class="text-muted mb-3"><i class="bi bi-credit-card me-2"></i>Parcelas</h6>
                        <div class="table-responsive">
                            <table class="table table-modern mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th>Vencimento</th>
                                        <th class="text-end">Valor</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($parcelas as $parcela) : ?>
                                    <?php
                                    $statusClass = match($parcela->status_local) {
                                        'pago' => 'bg-success',
                                        'pendente' => 'bg-warning text-dark',
                                        'vencido' => 'bg-danger',
                                        'cancelado' => 'bg-dark',
                                        default => 'bg-secondary'
                                    };
                                    $parcelaVencida = $parcela->data_vencimento && strtotime($parcela->data_vencimento) < time() && $parcela->status_local !== 'pago';
                                    ?>
                                    <tr>
                                        <td class="text-center">
                                            <span class="badge bg-secondary bg-opacity-50"><?= $parcela->numero_parcela ?></span>
                                        </td>
                                        <td>
                                            <span class="<?= $parcelaVencida ? 'text-danger fw-bold' : '' ?>"><?= date('d/m/Y', strtotime($parcela->data_vencimento)) ?></span>
                                            <?php if ($parcela->data_pagamento) : ?>
                                            <small class="d-block text-success"><i class="bi bi-check2 me-1"></i>Pago em <?= date('d/m/Y', strtotime($parcela->data_pagamento)) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end fw-bold">R$ <?= number_format($parcela->valor, 2, ',', '.') ?></td>
                                        <td class="text-center">
                                            <span class="badge-status <?= $statusClass ?>"><?= ucfirst($parcela->status_local) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if (in_array($parcela->status_local, ['pendente', 'vencido']) && $parcela->asaas_payment_id) : ?>
                                            <?php $paymentId = str_replace('pay_', '', $parcela->asaas_payment_id); ?>
                                            <a href="https://www.asaas.com/i/<?= $paymentId ?>" target="_blank" class="btn-pay">
                                                <i class="bi bi-credit-card me-1"></i>Pagar
                                            </a>
                                            <?php elseif ($parcela->status_local === 'pago') : ?>
                                            <span class="text-success"><i class="bi bi-check-circle-fill"></i></span>
                                            <?php else : ?>
                                            <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
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
<script>
// Toggle active state on buttons
document.querySelectorAll('.action-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        this.classList.toggle('active');
    });
});
</script>
<?php echo $this->endSection() ?>
