<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?><?php echo $titulo; ?><?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">
        <a href="<?php echo site_url('/assinaturas-admin'); ?>">Gerenciar Assinaturas</a>
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('/assinaturas-admin'); ?>">Assinaturas</a></li>
                <li class="breadcrumb-item active">#<?php echo $assinatura->id; ?></li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <?php if ($assinatura->status !== 'CANCELLED' && $assinatura->status !== 'ACTIVE'): ?>
        <button class="btn btn-success btn-reativar" data-id="<?php echo $assinatura->id; ?>">
            <i class="bx bx-revision me-1"></i>Reativar
        </button>
        <?php endif; ?>
        <?php if ($assinatura->status === 'ACTIVE'): ?>
        <button class="btn btn-danger btn-cancelar" data-id="<?php echo $assinatura->id; ?>">
            <i class="bx bx-x me-1"></i>Cancelar
        </button>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <!-- Informações Principais -->
    <div class="col-lg-8">
        <div class="card shadow radius-10 mb-4">
            <div class="card-header bg-transparent d-flex align-items-center">
                <h6 class="mb-0"><i class="bx bx-credit-card me-2"></i>Detalhes da Assinatura</h6>
                <div class="ms-auto"><?php echo $assinatura->exibeStatus(); ?></div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted" width="40%">ID Assinatura</td>
                                <td><strong>#<?php echo $assinatura->id; ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Plano</td>
                                <td><span class="badge bg-primary"><?php echo esc($assinatura->plano_nome); ?></span></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Ciclo</td>
                                <td><?php echo $assinatura->plano_ciclo === 'YEARLY' ? 'Anual' : 'Mensal'; ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Valor</td>
                                <td><strong class="text-success fs-5"><?php echo $assinatura->getValorFormatado(); ?></strong></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted" width="45%">Data Início</td>
                                <td><?php echo $assinatura->getDataInicioFormatada(); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Data Fim</td>
                                <td><?php echo $assinatura->getDataFimFormatada(); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Próx. Vencimento</td>
                                <td><?php echo $assinatura->getProximoVencimentoFormatado(); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Forma Pagamento</td>
                                <td><?php echo $assinatura->exibeFormaPagamento(); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <?php if ($assinatura->asaas_subscription_id): ?>
                <hr>
                <h6 class="text-muted mb-3"><i class="bx bx-link me-2"></i>Integração Asaas</h6>
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">ID Assinatura Asaas</small>
                        <p><code><?php echo esc($assinatura->asaas_subscription_id); ?></code></p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">ID Cliente Asaas</small>
                        <p><code><?php echo esc($assinatura->asaas_customer_id ?? '-'); ?></code></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Histórico de Eventos -->
        <div class="card shadow radius-10">
            <div class="card-header bg-transparent">
                <h6 class="mb-0"><i class="bx bx-history me-2"></i>Histórico de Eventos</h6>
            </div>
            <div class="card-body">
                <?php if (empty($historico)): ?>
                <div class="text-center py-4">
                    <i class="bx bx-time text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">Nenhum evento registrado</p>
                </div>
                <?php else: ?>
                <div class="timeline">
                    <?php foreach ($historico as $evento): ?>
                    <div class="timeline-item py-3 border-bottom">
                        <div class="d-flex">
                            <div class="me-3">
                                <div class="rounded-circle bg-light p-2">
                                    <i class="bx <?php echo $evento->getIcone(); ?> fs-5"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <?php echo $evento->exibeEvento(); ?>
                                        <?php if ($evento->descricao): ?>
                                        <p class="text-muted mb-0 mt-1 small"><?php echo esc($evento->descricao); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted"><?php echo $evento->getDataFormatada(); ?></small>
                                </div>
                                <?php 
                                $dados = $evento->getDados();
                                if (!empty($dados)): 
                                ?>
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-outline-secondary btn-ver-dados" type="button" data-bs-toggle="collapse" data-bs-target="#dados-<?php echo $evento->id; ?>">
                                        <i class="bx bx-code-alt me-1"></i>Ver dados
                                    </button>
                                    <div class="collapse mt-2" id="dados-<?php echo $evento->id; ?>">
                                        <pre class="bg-light p-2 rounded small mb-0"><?php echo json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Card do Usuário -->
        <div class="card shadow radius-10 mb-4">
            <div class="card-header bg-transparent">
                <h6 class="mb-0"><i class="bx bx-user me-2"></i>Assinante</h6>
            </div>
            <div class="card-body text-center">
                <div class="rounded-circle bg-primary bg-opacity-10 p-4 mx-auto mb-3" style="width: fit-content;">
                    <i class="bx bx-user text-primary" style="font-size: 3rem;"></i>
                </div>
                <h5 class="mb-1"><?php echo esc($assinatura->usuario_nome); ?></h5>
                <p class="text-muted mb-3"><?php echo esc($assinatura->usuario_email); ?></p>
                <a href="<?php echo site_url('clientes/exibir/' . $assinatura->usuario_id); ?>" class="btn btn-outline-primary btn-sm">
                    <i class="bx bx-show me-1"></i>Ver Perfil
                </a>
            </div>
        </div>

        <!-- Card do Plano -->
        <div class="card shadow radius-10 mb-4 border-primary">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bx bx-crown me-2"></i><?php echo esc($assinatura->plano_nome); ?></h6>
            </div>
            <div class="card-body text-center">
                <h2 class="text-primary mb-0">R$ <?php echo number_format($assinatura->plano_preco, 2, ',', '.'); ?></h2>
                <small class="text-muted">/<?php echo $assinatura->plano_ciclo === 'YEARLY' ? 'ano' : 'mês'; ?></small>
                
                <hr>
                
                <a href="<?php echo site_url('planos/editar/' . $assinatura->plano_id); ?>" class="btn btn-outline-primary btn-sm">
                    <i class="bx bx-cog me-1"></i>Editar Plano
                </a>
            </div>
        </div>

        <!-- Ações Rápidas -->
        <div class="card shadow radius-10">
            <div class="card-header bg-transparent">
                <h6 class="mb-0"><i class="bx bx-zap me-2"></i>Ações Rápidas</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?php echo site_url('assinaturas-admin'); ?>" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i>Voltar à Lista
                    </a>
                    <?php if ($assinatura->status === 'ACTIVE'): ?>
                    <button class="btn btn-outline-danger btn-cancelar" data-id="<?php echo $assinatura->id; ?>">
                        <i class="bx bx-x me-1"></i>Cancelar Assinatura
                    </button>
                    <?php elseif ($assinatura->status !== 'CANCELLED'): ?>
                    <button class="btn btn-outline-success btn-reativar" data-id="<?php echo $assinatura->id; ?>">
                        <i class="bx bx-revision me-1"></i>Reativar Assinatura
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Cancelamento -->
<div class="modal fade" id="modalCancelar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bx bx-x-circle me-2"></i>Cancelar Assinatura</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bx bx-warning me-2"></i>
                    Esta ação irá cancelar a assinatura e remover o status premium do usuário.
                </div>
                <div class="mb-3">
                    <label class="form-label">Motivo do cancelamento (opcional)</label>
                    <textarea class="form-control" id="motivoCancelamento" rows="3" placeholder="Informe o motivo..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
                <button type="button" class="btn btn-danger" id="confirmarCancelamento">
                    <i class="bx bx-x me-1"></i>Confirmar Cancelamento
                </button>
            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script>
$(document).ready(function() {
    var modalCancelar = new bootstrap.Modal(document.getElementById('modalCancelar'));
    var assinaturaId = <?php echo $assinatura->id; ?>;

    // Abrir modal de cancelamento
    $('.btn-cancelar').click(function() {
        $('#motivoCancelamento').val('');
        modalCancelar.show();
    });

    // Confirmar cancelamento
    $('#confirmarCancelamento').click(function() {
        var btn = $(this);
        var motivo = $('#motivoCancelamento').val();

        btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin me-1"></i>Cancelando...');

        $.post('<?php echo site_url("assinaturas-admin/cancelar"); ?>', {
            '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>',
            'id': assinaturaId,
            'motivo': motivo
        }, function(r) {
            if (r.erro) {
                alert(r.erro);
                btn.prop('disabled', false).html('<i class="bx bx-x me-1"></i>Confirmar Cancelamento');
                return;
            }
            location.reload();
        }, 'json');
    });

    // Reativar
    $('.btn-reativar').click(function() {
        if (!confirm('Tem certeza que deseja reativar esta assinatura?')) return;

        var btn = $(this);
        btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin me-1"></i>Reativando...');

        $.post('<?php echo site_url("assinaturas-admin/reativar"); ?>', {
            '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>',
            'id': assinaturaId
        }, function(r) {
            if (r.erro) {
                alert(r.erro);
                btn.prop('disabled', false).html('<i class="bx bx-revision me-1"></i>Reativar');
                return;
            }
            location.reload();
        }, 'json');
    });
});
</script>
<?php echo $this->endSection() ?>
