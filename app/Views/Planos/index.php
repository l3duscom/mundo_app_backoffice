<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?><?php echo $titulo; ?><?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">
        <a href="<?php echo site_url('/planos'); ?>">Planos de Assinatura</a>
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active">Planos</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="<?php echo site_url('planos/criar'); ?>" class="btn btn-primary">
            <i class="bx bx-plus me-2"></i>Novo Plano
        </a>
    </div>
</div>

<!-- Cards de Estatísticas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="bx bx-package text-primary fs-4"></i>
                    </div>
                    <div>
                        <h4 class="mb-0"><?php echo count($planos); ?></h4>
                        <span class="text-muted small">Total de Planos</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="bx bx-check-circle text-success fs-4"></i>
                    </div>
                    <div>
                        <h4 class="mb-0"><?php echo count(array_filter($planos, fn($p) => $p->ativo)); ?></h4>
                        <span class="text-muted small">Planos Ativos</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                        <i class="bx bx-calendar text-info fs-4"></i>
                    </div>
                    <div>
                        <h4 class="mb-0"><?php echo count(array_filter($planos, fn($p) => $p->ciclo === 'MONTHLY')); ?></h4>
                        <span class="text-muted small">Planos Mensais</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                        <i class="bx bx-calendar-check text-warning fs-4"></i>
                    </div>
                    <div>
                        <h4 class="mb-0"><?php echo count(array_filter($planos, fn($p) => $p->ciclo === 'YEARLY')); ?></h4>
                        <span class="text-muted small">Planos Anuais</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow radius-10">
    <div class="card-body">
        <?php if (empty($planos)): ?>
        <div class="text-center py-5">
            <i class="bx bx-package" style="font-size: 4rem; color: #ccc;"></i>
            <p class="text-muted mt-3">Nenhum plano de assinatura cadastrado</p>
            <a href="<?php echo site_url('planos/criar'); ?>" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i>Criar primeiro plano
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="tabelaPlanos">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Slug</th>
                        <th>Ciclo</th>
                        <th class="text-end">Preço</th>
                        <th class="text-center">Assinaturas</th>
                        <th class="text-center">Status</th>
                        <th width="150">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($planos as $plano): ?>
                    <tr data-id="<?php echo $plano->id; ?>">
                        <td>
                            <strong><?php echo esc($plano->nome); ?></strong>
                            <?php if ($plano->descricao): ?>
                            <br><small class="text-muted"><?php echo esc(substr($plano->descricao, 0, 60)); ?><?php echo strlen($plano->descricao) > 60 ? '...' : ''; ?></small>
                            <?php endif; ?>
                        </td>
                        <td><code><?php echo esc($plano->slug); ?></code></td>
                        <td><?php echo $plano->exibeCiclo(); ?></td>
                        <td class="text-end">
                            <strong class="text-success"><?php echo $plano->getPrecoFormatado(); ?></strong>
                            <?php if ($plano->ciclo === 'YEARLY'): ?>
                            <br><small class="text-muted"><?php echo 'R$ ' . number_format($plano->preco / 12, 2, ',', '.'); ?>/mês</small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark"><?php echo $plano->total_assinaturas ?? 0; ?></span>
                        </td>
                        <td class="text-center status-cell"><?php echo $plano->exibeStatus(); ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?php echo site_url("planos/editar/{$plano->id}"); ?>" class="btn btn-outline-primary" title="Editar">
                                    <i class="bx bx-edit"></i>
                                </a>
                                <button class="btn btn-outline-warning btn-toggle-status" data-id="<?php echo $plano->id; ?>" title="Alterar Status">
                                    <i class="bx bx-power-off"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-excluir" data-id="<?php echo $plano->id; ?>" title="Excluir">
                                    <i class="bx bx-trash"></i>
                                </button>
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

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Excluir
    $('.btn-excluir').click(function() {
        if (!confirm('Tem certeza que deseja excluir este plano?')) return;
        
        var id = $(this).data('id');
        var btn = $(this);
        
        $.post('<?php echo site_url("planos/excluir"); ?>/' + id, {
            '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>'
        }, function(r) {
            $('[name="<?php echo csrf_token(); ?>"]').val(r.token);
            if (r.erro) {
                alert(r.erro);
                return;
            }
            btn.closest('tr').fadeOut(300, function() { $(this).remove(); });
        }, 'json');
    });

    // Toggle status
    $('.btn-toggle-status').click(function() {
        var id = $(this).data('id');
        var row = $(this).closest('tr');
        
        $.post('<?php echo site_url("planos/alterarStatus"); ?>', {
            '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>',
            'id': id
        }, function(r) {
            $('[name="<?php echo csrf_token(); ?>"]').val(r.token);
            if (r.erro) {
                alert(r.erro);
                return;
            }
            row.find('.status-cell').html(r.badge);
        }, 'json');
    });
});
</script>
<?php echo $this->endSection() ?>
