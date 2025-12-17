<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>
<style>
.refund-card {
    border-left: 4px solid #6f42c1;
    transition: all 0.3s ease;
}
.refund-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.refund-card.pendente {
    border-left-color: #ffc107;
}
.refund-card.concluido {
    border-left-color: #198754;
}
.refund-card.cancelado {
    border-left-color: #dc3545;
}
.refund-card.processando {
    border-left-color: #0dcaf0;
}
.status-badge {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
}
.timeline-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}
.timeline-icon.pendente {
    background-color: #fff3cd;
    color: #856404;
}
.timeline-icon.concluido {
    background-color: #d4edda;
    color: #155724;
}
.timeline-icon.cancelado {
    background-color: #f8d7da;
    color: #721c24;
}
.timeline-icon.processando {
    background-color: #cce5ff;
    color: #004085;
}
</style>
<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>

<div class="row justify-content-center">
    <div class="col-lg-12 col-xl-10">
        
        <!-- Título -->
        <div class="d-flex align-items-center mb-4">
            <i class="bi bi-arrow-counterclockwise text-primary me-2" style="font-size:2rem;"></i>
            <h2 class="mb-0 fw-bold" style="letter-spacing:0.5px;">Minhas Solicitações</h2>
        </div>

        <!-- Info Card -->
        <?php if (empty($refounds)): ?>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox text-muted mb-3" style="font-size:3rem;"></i>
                <h5 class="text-muted">Você não tem solicitações de reembolso</h5>
                <p class="text-muted">Quando você fizer uma solicitação, ela aparecerá aqui.</p>
            </div>
        </div>
        <?php else: ?>

        <!-- Resumo -->
        <div class="row mb-4">
            <div class="col-md-3 col-6 mb-3 mb-md-0">
                <div class="card shadow-sm border-0 text-center py-3">
                    <div class="text-primary fw-bold" style="font-size:1.8rem;"><?= count($refounds) ?></div>
                    <div class="text-muted small">Total</div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3 mb-md-0">
                <div class="card shadow-sm border-0 text-center py-3">
                    <div class="text-warning fw-bold" style="font-size:1.8rem;"><?= count(array_filter($refounds, fn($r) => strtolower($r->status) === 'pendente')) ?></div>
                    <div class="text-muted small">Pendentes</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card shadow-sm border-0 text-center py-3">
                    <div class="text-success fw-bold" style="font-size:1.8rem;"><?= count(array_filter($refounds, fn($r) => strtolower($r->status) === 'concluido')) ?></div>
                    <div class="text-muted small">Aprovadas</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card shadow-sm border-0 text-center py-3">
                    <div class="text-danger fw-bold" style="font-size:1.8rem;"><?= count(array_filter($refounds, fn($r) => strtolower($r->status) === 'cancelado')) ?></div>
                    <div class="text-muted small">Canceladas</div>
                </div>
            </div>
        </div>

        <!-- Lista de Solicitações -->
        <div class="row g-4">
            <?php foreach ($refounds as $refound): ?>
            <?php 
            $statusLower = strtolower($refound->status ?? 'pendente');
            $statusBadge = match($statusLower) {
                'pendente' => ['bg-warning text-dark', 'Pendente', 'bx-time'],
                'processando' => ['bg-info', 'Processando', 'bx-loader-alt'],
                'concluido' => ['bg-success', 'Concluído', 'bx-check'],
                'cancelado' => ['bg-danger', 'Cancelado', 'bx-x'],
                'erro' => ['bg-dark', 'Erro', 'bx-error'],
                default => ['bg-secondary', ucfirst($statusLower), 'bx-question-mark']
            };
            ?>
            <div class="col-12">
                <div class="card shadow-sm border-0 refund-card <?= $statusLower ?>">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <!-- Ícone de Status -->
                            <div class="col-auto">
                                <div class="timeline-icon <?= $statusLower ?>">
                                    <i class="bx <?= $statusBadge[2] ?>"></i>
                                </div>
                            </div>
                            
                            <!-- Informações Principais -->
                            <div class="col">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    <h5 class="mb-0 fw-semibold"><?= esc($refound->evento_nome ?? 'Evento') ?></h5>
                                    <span class="badge <?= $statusBadge[0] ?> status-badge">
                                        <i class="bx <?= $statusBadge[2] ?> me-1"></i><?= $statusBadge[1] ?>
                                    </span>
                                    <?php if ($refound->tipo_solicitacao): ?>
                                    <span class="badge <?= strtolower($refound->tipo_solicitacao) === 'upgrade' ? 'bg-purple' : 'bg-orange' ?>">
                                        <?= ucfirst(esc($refound->tipo_solicitacao)) ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="row g-3">
                                    <div class="col-md-3 col-6">
                                        <small class="text-muted d-block">Pedido</small>
                                        <span class="fw-bold"><?= esc($refound->pedido_codigo ?? '-') ?></span>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <small class="text-muted d-block">Valor</small>
                                        <span class="fw-bold text-success">R$ <?= number_format($refound->pedido_valor_total ?? 0, 2, ',', '.') ?></span>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <small class="text-muted d-block">Data da Solicitação</small>
                                        <span class="fw-bold"><?= $refound->created_at ? date('d/m/Y H:i', strtotime($refound->created_at)) : '-' ?></span>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <small class="text-muted d-block">Processado em</small>
                                        <span class="fw-bold"><?= $refound->processado_em ? date('d/m/Y H:i', strtotime($refound->processado_em)) : '-' ?></span>
                                    </div>
                                </div>
                                
                                <?php if ($refound->observacoes): ?>
                                <div class="mt-3 p-2 bg-light rounded">
                                    <small class="text-muted d-block mb-1"><i class="bx bx-message-detail me-1"></i>Observações:</small>
                                    <span><?= esc($refound->observacoes) ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Botão Ver Detalhes -->
                            <div class="col-auto mt-3 mt-md-0">
                                <a href="<?= site_url('pedidos/meus-refounds/' . $refound->id) ?>" class="btn btn-outline-primary">
                                    <i class="bx bx-show me-1"></i>Ver Detalhes
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php endif; ?>
        
        <!-- Voltar -->
        <div class="mt-4">
            <a href="<?= site_url('pedidos') ?>" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i>Voltar aos Meus Pedidos
            </a>
        </div>
        
    </div>
</div>

<?php echo $this->endSection() ?>


<?php echo $this->section('scripts') ?>
<script>
// Animações suaves nos cards
$(document).ready(function() {
    $('.refund-card').each(function(index) {
        $(this).delay(100 * index).queue(function(next) {
            $(this).addClass('animated fadeInUp');
            next();
        });
    });
});
</script>
<?php echo $this->endSection() ?>
