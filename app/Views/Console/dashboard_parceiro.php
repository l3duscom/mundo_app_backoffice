<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="container-fluid py-4">
    
    <!-- Header de boas-vindas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body py-4">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-white bg-opacity-25 p-3 me-3">
                            <i class="bi bi-building text-white" style="font-size: 2rem;"></i>
                        </div>
                        <div>
                            <h3 class="text-white mb-1">Olá, <?= esc($expositor->nome_fantasia ?? $expositor->nome ?? 'Parceiro') ?>!</h3>
                            <p class="text-white-50 mb-0">Bem-vindo ao seu painel de parceiro</p>
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

    <!-- Contratos por Evento -->
    <?php if (empty($contratos_por_evento)) : ?>
    <div class="text-center py-5">
        <i class="bi bi-file-earmark-text text-muted" style="font-size: 4rem;"></i>
        <h5 class="mt-3 text-muted">Nenhum contrato encontrado</h5>
        <p class="text-muted">Seus contratos aparecerão aqui quando forem cadastrados.</p>
    </div>
    <?php else : ?>
    
    <?php foreach ($contratos_por_evento as $eventoData) : ?>
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header bg-dark border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1 text-white">
                        <i class="bi bi-calendar-event me-2"></i><?= esc($eventoData['evento']->nome) ?>
                    </h5>
                    <small class="text-muted">
                        <?= date('d/m/Y', strtotime($eventoData['evento']->data_inicio)) ?> 
                        - <?= date('d/m/Y', strtotime($eventoData['evento']->data_fim)) ?>
                    </small>
                </div>
                <span class="badge bg-primary"><?= count($eventoData['contratos']) ?> contrato(s)</span>
            </div>
        </div>
        <div class="card-body">
            <?php foreach ($eventoData['contratos'] as $contratoData) : ?>
            <?php $contrato = $contratoData['contrato']; $itens = $contratoData['itens']; $parcelas = $contratoData['parcelas']; ?>
            
            <div class="border rounded p-3 mb-3" style="background: rgba(255,255,255,0.02);">
                <!-- Cabeçalho do Contrato -->
                <div class="row align-items-center mb-3">
                    <div class="col-md-4">
                        <small class="text-muted d-block">Contrato</small>
                        <strong><?= esc($contrato->codigo) ?></strong>
                        <?php if ($contrato->descricao) : ?>
                        <p class="text-muted small mb-0"><?= esc($contrato->descricao) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-2 text-center">
                        <small class="text-muted d-block">Valor Total</small>
                        <strong class="text-success">R$ <?= number_format($contrato->valor_final, 2, ',', '.') ?></strong>
                    </div>
                    <div class="col-md-2 text-center">
                        <small class="text-muted d-block">Situação</small>
                        <?php
                        $situacaoClass = match($contrato->situacao) {
                            'pago' => 'bg-success',
                            'ativo', 'assinado' => 'bg-primary',
                            'pendente', 'proposta' => 'bg-warning text-dark',
                            'cancelado', 'banido' => 'bg-danger',
                            default => 'bg-secondary'
                        };
                        ?>
                        <span class="badge <?= $situacaoClass ?>"><?= ucfirst(esc($contrato->situacao)) ?></span>
                    </div>
                    <div class="col-md-4 text-end">
                        <button class="btn btn-sm btn-outline-primary me-1" type="button" data-bs-toggle="collapse" data-bs-target="#itens<?= $contrato->id ?>">
                            <i class="bi bi-box-seam"></i> Itens (<?= count($itens) ?>)
                        </button>
                        <button class="btn btn-sm btn-outline-success" type="button" data-bs-toggle="collapse" data-bs-target="#parcelas<?= $contrato->id ?>">
                            <i class="bi bi-cash-stack"></i> Parcelas (<?= count($parcelas) ?>)
                        </button>
                    </div>
                </div>

                <!-- Itens Contratados -->
                <div class="collapse mt-3" id="itens<?= $contrato->id ?>">
                    <h6 class="text-muted mb-2"><i class="bi bi-box-seam me-1"></i> Itens Contratados</h6>
                    <?php if (empty($itens)) : ?>
                    <p class="text-muted small">Nenhum item cadastrado.</p>
                    <?php else : ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-dark table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Descrição</th>
                                    <th>Qtd</th>
                                    <th>Valor Unit.</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($itens as $item) : ?>
                                <tr>
                                    <td><?= esc($item->tipo_item) ?></td>
                                    <td><?= esc($item->descricao ?? '-') ?></td>
                                    <td><?= $item->quantidade ?></td>
                                    <td>R$ <?= number_format($item->valor_unitario, 2, ',', '.') ?></td>
                                    <td>R$ <?= number_format($item->valor_total, 2, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Parcelas -->
                <div class="collapse mt-3" id="parcelas<?= $contrato->id ?>">
                    <h6 class="text-muted mb-2"><i class="bi bi-cash-stack me-1"></i> Parcelas</h6>
                    <?php if (empty($parcelas)) : ?>
                    <p class="text-muted small">Nenhuma parcela cadastrada.</p>
                    <?php else : ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-dark table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Vencimento</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($parcelas as $parcela) : ?>
                                <tr>
                                    <td><?= $parcela->numero_parcela ?></td>
                                    <td><?= date('d/m/Y', strtotime($parcela->data_vencimento)) ?></td>
                                    <td>R$ <?= number_format($parcela->valor, 2, ',', '.') ?></td>
                                    <td>
                                        <?php
                                        $statusClass = match($parcela->status_local) {
                                            'pago' => 'bg-success',
                                            'pendente' => 'bg-warning text-dark',
                                            'vencido' => 'bg-danger',
                                            'cancelado' => 'bg-dark',
                                            default => 'bg-secondary'
                                        };
                                        ?>
                                        <span class="badge <?= $statusClass ?>"><?= ucfirst($parcela->status_local) ?></span>
                                        <?php if ($parcela->data_pagamento) : ?>
                                        <small class="text-muted d-block">Pago em <?= date('d/m/Y', strtotime($parcela->data_pagamento)) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (in_array($parcela->status_local, ['pendente', 'vencido']) && $parcela->asaas_payment_id) : ?>
                                        <a href="https://www.asaas.com/i/<?= $parcela->asaas_payment_id ?>" target="_blank" class="btn btn-sm btn-success">
                                            <i class="bi bi-credit-card me-1"></i>Pagar
                                        </a>
                                        <?php elseif ($parcela->status_local === 'pago' && $parcela->comprovante_url) : ?>
                                        <a href="<?= $parcela->comprovante_url ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>Comprovante
                                        </a>
                                        <?php else : ?>
                                        <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
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

