<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
    .pdv-header {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border-radius: 16px;
        padding: 30px;
        margin-bottom: 30px;
    }
    
    .pdv-header h2 {
        color: white;
        margin: 0;
    }
    
    .stat-card {
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        color: white;
        height: 100%;
    }
    
    .stat-card.confirmadas {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }
    
    .stat-card.pendentes {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
    }
    
    .stat-card.total-confirmado {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .stat-card.total-pendente {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    }
    
    .stat-card h3 {
        font-size: 1.8rem;
        margin: 0;
    }
    
    .stat-card p {
        margin: 5px 0 0 0;
        opacity: 0.9;
        font-size: 0.85rem;
    }
    
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .status-pago {
        background: #d4edda;
        color: #155724;
    }
    
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-cancelado {
        background: #f8d7da;
        color: #721c24;
    }
    
    .forma-pagamento {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="container-fluid py-4">
    
    <!-- Header PDV -->
    <div class="pdv-header">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h2><i class="bi bi-receipt me-3"></i>Minhas Vendas</h2>
                <p class="text-white-50 mt-2 mb-0">Acompanhe suas vendas realizadas</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= site_url('pdv/dashboard') ?>" class="btn btn-light">
                    <i class="bi bi-shop me-2"></i>Nova Venda
                </a>
                <a href="<?= site_url('console/dashboard') ?>" class="btn btn-outline-light">
                    <i class="bi bi-arrow-left me-2"></i>Voltar
                </a>
            </div>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card confirmadas">
                <h3><?= $vendasConfirmadas ?></h3>
                <p><i class="bi bi-check-circle me-1"></i>Vendas Confirmadas</p>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card pendentes">
                <h3><?= $vendasPendentes ?></h3>
                <p><i class="bi bi-clock me-1"></i>Vendas Pendentes</p>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card total-confirmado">
                <h3>R$ <?= number_format($totalConfirmado, 2, ',', '.') ?></h3>
                <p><i class="bi bi-cash-stack me-1"></i>Total Confirmado</p>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card total-pendente">
                <h3>R$ <?= number_format($totalPendente, 2, ',', '.') ?></h3>
                <p><i class="bi bi-hourglass-split me-1"></i>Total Pendente</p>
            </div>
        </div>
    </div>

    <!-- Tabela de Vendas -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="bi bi-list-ul me-2"></i>
                Histórico de Vendas
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($vendas)) : ?>
                <div class="alert alert-info m-4">
                    <i class="bi bi-info-circle me-2"></i>
                    Nenhuma venda realizada ainda.
                </div>
            <?php else : ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Código</th>
                                <th>Data/Hora</th>
                                <th>Evento</th>
                                <th>Cliente</th>
                                <th>Forma Pagamento</th>
                                <th class="text-end">Valor</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vendas as $venda) : ?>
                                <tr>
                                    <td>
                                        <code class="fw-bold"><?= esc($venda->codigo) ?></code>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($venda->created_at)) ?>
                                        <small class="text-muted d-block"><?= date('H:i', strtotime($venda->created_at)) ?></small>
                                    </td>
                                    <td>
                                        <small><?= esc($venda->evento_nome ?? 'N/A') ?></small>
                                    </td>
                                    <td>
                                        <?= esc($venda->cliente_nome ?? 'N/A') ?>
                                        <?php if (!empty($venda->cliente_email)) : ?>
                                            <small class="text-muted d-block"><?= esc($venda->cliente_email) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="forma-pagamento">
                                            <?php
                                            $icone = 'bi-credit-card';
                                            if (strtoupper($venda->forma_pagamento) === 'PIX') $icone = 'bi-qr-code';
                                            if (strtoupper($venda->forma_pagamento) === 'DINHEIRO') $icone = 'bi-cash';
                                            ?>
                                            <i class="bi <?= $icone ?>"></i>
                                            <?= esc($venda->forma_pagamento) ?>
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold">
                                        R$ <?= number_format($venda->total, 2, ',', '.') ?>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $statusClass = 'status-pending';
                                        $statusText = $venda->status;
                                        
                                        if (in_array(strtoupper($venda->status), ['PAGO', 'CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH'])) {
                                            $statusClass = 'status-pago';
                                            $statusText = 'PAGO';
                                        } elseif (in_array(strtoupper($venda->status), ['CANCELLED', 'REFUNDED'])) {
                                            $statusClass = 'status-cancelado';
                                        }
                                        ?>
                                        <span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<?php echo $this->endSection() ?>
