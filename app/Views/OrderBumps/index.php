<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">
        <a href="<?php echo site_url('/order-bumps'); ?>">Order Bumps</a>
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active">Order Bumps</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="<?php echo site_url('order-bumps/criar'); ?>" class="btn btn-primary">
            <i class="bx bx-plus me-2"></i>Novo Order Bump
        </a>
    </div>
</div>

<!-- Alerta explicativo -->
<div class="alert alert-info border-0 mb-4">
    <div class="d-flex align-items-center">
        <i class="bx bx-info-circle fs-4 me-2"></i>
        <div>
            <strong>Order Bumps</strong> são produtos ou serviços adicionais oferecidos durante o checkout, como camisetas, acessórios ou serviços extras.
            Diferente de Upsells, não substituem o ingresso, apenas complementam a compra.
        </div>
    </div>
</div>

<div class="card shadow radius-10">
    <div class="card-body">
        <?php if (empty($orderBumps)): ?>
        <div class="text-center py-5">
            <i class="bx bx-package" style="font-size: 4rem; color: #ccc;"></i>
            <p class="text-muted mt-3">Nenhum order bump configurado para este evento</p>
            <a href="<?php echo site_url('order-bumps/criar'); ?>" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i>Configurar primeiro order bump
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="tabelaOrderBumps">
                <thead>
                    <tr>
                        <th width="80">Imagem</th>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th class="text-end">Preço</th>
                        <th>Vinculado a</th>
                        <th class="text-center">Estoque</th>
                        <th class="text-center">Status</th>
                        <th width="150">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderBumps as $bump): ?>
                    <tr data-id="<?php echo $bump->id; ?>">
                        <td>
                            <?php if ($bump->temImagem()): ?>
                            <img src="<?php echo $bump->getImagemUrl(); ?>" alt="" class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
                            <?php else: ?>
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bx bx-image text-muted fs-4"></i>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo esc($bump->nome); ?></strong>
                            <?php if ($bump->descricao): ?>
                            <br><small class="text-muted"><?php echo esc(substr($bump->descricao, 0, 50)); ?><?php echo strlen($bump->descricao) > 50 ? '...' : ''; ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $bump->exibeTipo(); ?></td>
                        <td class="text-end">
                            <strong class="text-success"><?php echo $bump->getPrecoFormatado(); ?></strong>
                        </td>
                        <td>
                            <?php if ($bump->ticket_nome): ?>
                            <span class="badge bg-light text-dark"><?php echo esc($bump->ticket_nome); ?></span>
                            <?php else: ?>
                            <span class="text-muted">Todos os ingressos</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?php echo $bump->getEstoqueTexto(); ?></td>
                        <td class="text-center status-cell"><?php echo $bump->exibeStatus(); ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?php echo site_url("order-bumps/editar/{$bump->id}"); ?>" class="btn btn-outline-primary" title="Editar">
                                    <i class="bx bx-edit"></i>
                                </a>
                                <button class="btn btn-outline-warning btn-toggle-status" data-id="<?php echo $bump->id; ?>" title="Alterar Status">
                                    <i class="bx bx-power-off"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-excluir" data-id="<?php echo $bump->id; ?>" title="Excluir">
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
        if (!confirm('Tem certeza que deseja excluir este order bump?')) return;
        
        var id = $(this).data('id');
        var btn = $(this);
        
        $.post('<?php echo site_url("order-bumps/excluir"); ?>/' + id, {
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
        
        $.post('<?php echo site_url("order-bumps/alterarStatus"); ?>', {
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
