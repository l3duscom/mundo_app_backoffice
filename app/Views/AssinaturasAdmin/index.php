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
                <li class="breadcrumb-item active">Assinaturas</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="<?php echo site_url('assinaturas-admin/exportar'); ?><?php echo $filtroStatus ? '?status=' . $filtroStatus : ''; ?>" class="btn btn-outline-success">
            <i class="bx bx-download me-2"></i>Exportar CSV
        </a>
    </div>
</div>

<!-- Cards de Estatísticas -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 mx-auto mb-2" style="width: fit-content;">
                    <i class="bx bx-user-check text-primary fs-4"></i>
                </div>
                <h3 class="mb-0"><?php echo $estatisticas['total']; ?></h3>
                <small class="text-muted">Total</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100 <?php echo $filtroStatus === 'ACTIVE' ? 'border-success border-2' : ''; ?>">
            <a href="<?php echo site_url('assinaturas-admin?status=ACTIVE'); ?>" class="text-decoration-none">
                <div class="card-body text-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 mx-auto mb-2" style="width: fit-content;">
                        <i class="bx bx-check-circle text-success fs-4"></i>
                    </div>
                    <h3 class="mb-0 text-success"><?php echo $estatisticas['ativas']; ?></h3>
                    <small class="text-muted">Ativas</small>
                </div>
            </a>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100 <?php echo $filtroStatus === 'PENDING' ? 'border-warning border-2' : ''; ?>">
            <a href="<?php echo site_url('assinaturas-admin?status=PENDING'); ?>" class="text-decoration-none">
                <div class="card-body text-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 mx-auto mb-2" style="width: fit-content;">
                        <i class="bx bx-time text-warning fs-4"></i>
                    </div>
                    <h3 class="mb-0 text-warning"><?php echo $estatisticas['pendentes']; ?></h3>
                    <small class="text-muted">Pendentes</small>
                </div>
            </a>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100 <?php echo $filtroStatus === 'OVERDUE' ? 'border-danger border-2' : ''; ?>">
            <a href="<?php echo site_url('assinaturas-admin?status=OVERDUE'); ?>" class="text-decoration-none">
                <div class="card-body text-center">
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3 mx-auto mb-2" style="width: fit-content;">
                        <i class="bx bx-error text-danger fs-4"></i>
                    </div>
                    <h3 class="mb-0 text-danger"><?php echo $estatisticas['atrasadas']; ?></h3>
                    <small class="text-muted">Atrasadas</small>
                </div>
            </a>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100 <?php echo $filtroStatus === 'CANCELLED' ? 'border-secondary border-2' : ''; ?>">
            <a href="<?php echo site_url('assinaturas-admin?status=CANCELLED'); ?>" class="text-decoration-none">
                <div class="card-body text-center">
                    <div class="rounded-circle bg-secondary bg-opacity-10 p-3 mx-auto mb-2" style="width: fit-content;">
                        <i class="bx bx-x-circle text-secondary fs-4"></i>
                    </div>
                    <h3 class="mb-0 text-secondary"><?php echo $estatisticas['canceladas']; ?></h3>
                    <small class="text-muted">Canceladas</small>
                </div>
            </a>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100 bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-center text-white">
                <i class="bx bx-trending-up fs-4 mb-2"></i>
                <h3 class="mb-0">R$ <?php echo number_format($estatisticas['mrr'], 2, ',', '.'); ?></h3>
                <small class="opacity-75">MRR</small>
            </div>
        </div>
    </div>
</div>

<!-- Filtro Ativo -->
<?php if ($filtroStatus): ?>
<div class="alert alert-info d-flex align-items-center mb-4">
    <i class="bx bx-filter me-2"></i>
    Filtrando por status: <strong class="ms-1"><?php echo $filtroStatus; ?></strong>
    <a href="<?php echo site_url('assinaturas-admin'); ?>" class="btn btn-sm btn-outline-info ms-auto">
        <i class="bx bx-x me-1"></i>Limpar Filtro
    </a>
</div>
<?php endif; ?>

<div class="card shadow radius-10">
    <div class="card-body">
        <?php if (empty($assinaturas)): ?>
        <div class="text-center py-5">
            <i class="bx bx-user-x" style="font-size: 4rem; color: #ccc;"></i>
            <p class="text-muted mt-3">
                <?php echo $filtroStatus ? "Nenhuma assinatura com status '{$filtroStatus}'" : 'Nenhuma assinatura encontrada'; ?>
            </p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="tabelaAssinaturas">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuário</th>
                        <th>Plano</th>
                        <th>Status</th>
                        <th class="text-end">Valor</th>
                        <th>Pagamento</th>
                        <th>Próx. Vencimento</th>
                        <th>Criada em</th>
                        <th width="100">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assinaturas as $assinatura): ?>
                    <tr>
                        <td><strong>#<?php echo $assinatura->id; ?></strong></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                    <i class="bx bx-user text-primary"></i>
                                </div>
                                <div>
                                    <strong><?php echo esc($assinatura->usuario_nome); ?></strong>
                                    <br><small class="text-muted"><?php echo esc($assinatura->usuario_email); ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark"><?php echo esc($assinatura->plano_nome); ?></span>
                        </td>
                        <td><?php echo $assinatura->exibeStatus(); ?></td>
                        <td class="text-end">
                            <strong><?php echo $assinatura->getValorFormatado(); ?></strong>
                        </td>
                        <td><?php echo $assinatura->exibeFormaPagamento(); ?></td>
                        <td>
                            <?php if ($assinatura->proximo_vencimento): ?>
                                <?php 
                                $vencimento = new DateTime($assinatura->proximo_vencimento);
                                $hoje = new DateTime();
                                $diff = $hoje->diff($vencimento);
                                $vencido = $vencimento < $hoje;
                                ?>
                                <span class="<?php echo $vencido ? 'text-danger' : ''; ?>">
                                    <?php echo $assinatura->getProximoVencimentoFormatado(); ?>
                                </span>
                                <?php if ($vencido): ?>
                                    <br><small class="text-danger">Vencido há <?php echo $diff->days; ?> dias</small>
                                <?php elseif ($diff->days <= 7): ?>
                                    <br><small class="text-warning">Vence em <?php echo $diff->days; ?> dias</small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small class="text-muted">
                                <?php echo $assinatura->created_at ? date('d/m/Y', strtotime($assinatura->created_at)) : '-'; ?>
                            </small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?php echo site_url("assinaturas-admin/exibir/{$assinatura->id}"); ?>" class="btn btn-outline-primary" title="Ver Detalhes">
                                    <i class="bx bx-show"></i>
                                </a>
                                <?php if ($assinatura->status === 'ACTIVE'): ?>
                                <button class="btn btn-outline-danger btn-cancelar" data-id="<?php echo $assinatura->id; ?>" title="Cancelar">
                                    <i class="bx bx-x"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
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
                <input type="hidden" id="idCancelar" value="">
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
    var modal = new bootstrap.Modal(document.getElementById('modalCancelar'));

    // Abrir modal de cancelamento
    $('.btn-cancelar').click(function() {
        $('#idCancelar').val($(this).data('id'));
        $('#motivoCancelamento').val('');
        modal.show();
    });

    // Confirmar cancelamento
    $('#confirmarCancelamento').click(function() {
        var btn = $(this);
        var id = $('#idCancelar').val();
        var motivo = $('#motivoCancelamento').val();

        btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin me-1"></i>Cancelando...');

        $.post('<?php echo site_url("assinaturas-admin/cancelar"); ?>', {
            '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>',
            'id': id,
            'motivo': motivo
        }, function(r) {
            $('[name="<?php echo csrf_token(); ?>"]').val(r.token);
            if (r.erro) {
                alert(r.erro);
                btn.prop('disabled', false).html('<i class="bx bx-x me-1"></i>Confirmar Cancelamento');
                return;
            }
            modal.hide();
            location.reload();
        }, 'json').fail(function() {
            alert('Erro ao cancelar assinatura. Tente novamente.');
            btn.prop('disabled', false).html('<i class="bx bx-x me-1"></i>Confirmar Cancelamento');
        });
    });
});
</script>
<?php echo $this->endSection() ?>
