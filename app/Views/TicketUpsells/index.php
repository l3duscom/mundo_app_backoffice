<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">
        <a href="<?php echo site_url('/ticket-upsells'); ?>">Upsells</a>
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active">Upsells de Ingressos</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="<?php echo site_url('ticket-upsells/criar'); ?>" class="btn btn-primary">
            <i class="bx bx-plus me-2"></i>Novo Upsell
        </a>
    </div>
</div>

<div class="card shadow radius-10">
    <div class="card-body">
        <?php if (empty($upsells)): ?>
        <div class="text-center py-5">
            <i class="bx bx-trending-up" style="font-size: 4rem; color: #ccc;"></i>
            <p class="text-muted mt-3">Nenhum upsell configurado para este evento</p>
            <a href="<?php echo site_url('ticket-upsells/criar'); ?>" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i>Configurar primeiro upsell
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover" id="tabelaUpsells">
                <thead>
                    <tr>
                        <th>Ingresso Atual</th>
                        <th><i class="bx bx-right-arrow-alt"></i></th>
                        <th>Upgrade Para</th>
                        <th class="text-end">Diferença</th>
                        <th class="text-end">Valor Final</th>
                        <th class="text-center">Status</th>
                        <th width="120">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($upsells as $u): ?>
                    <tr>
                        <td>
                            <strong><?php echo esc($u->ticket_origem_nome); ?></strong>
                            <br><small class="text-muted">R$ <?php echo number_format($u->ticket_origem_preco, 2, ',', '.'); ?></small>
                        </td>
                        <td class="text-center text-primary"><i class="bx bx-right-arrow-alt fs-4"></i></td>
                        <td>
                            <strong><?php echo esc($u->ticket_destino_nome); ?></strong>
                            <br><small class="text-muted">R$ <?php echo number_format($u->ticket_destino_preco, 2, ',', '.'); ?></small>
                        </td>
                        <td class="text-end"><?php echo $u->getValorDiferencaFormatado(); ?></td>
                        <td class="text-end">
                            <strong class="text-success"><?php echo $u->getValorFinalFormatado(); ?></strong>
                            <?php if ($u->temDesconto()): ?>
                            <br><small class="text-muted"><?php echo $u->desconto_percentual; ?>% desc.</small>
                            <?php elseif ($u->temValorCustomizado()): ?>
                            <br><small class="text-warning">Valor fixo</small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?php echo $u->exibeStatus(); ?></td>
                        <td>
                            <a href="<?php echo site_url("ticket-upsells/editar/{$u->id}"); ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                <i class="bx bx-edit"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-danger btn-excluir" data-id="<?php echo $u->id; ?>" title="Excluir">
                                <i class="bx bx-trash"></i>
                            </button>
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
        if (!confirm('Excluir este upsell?')) return;
        
        var id = $(this).data('id');
        var btn = $(this);
        
        $.post('<?php echo site_url("ticket-upsells/excluir"); ?>/' + id, {
            '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>'
        }, function(r) {
            if (r.erro) {
                alert(r.erro);
                return;
            }
            btn.closest('tr').fadeOut(300, function() { $(this).remove(); });
        }, 'json');
    });
});
</script>
<?php echo $this->endSection() ?>
