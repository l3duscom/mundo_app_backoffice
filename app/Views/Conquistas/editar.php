<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Conquistas</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('conquistas-admin'); ?>">Conquistas</a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bx bx-edit me-2"></i>Editar Conquista</h5>
        <span class="badge bg-secondary">Código: <?php echo esc($conquista->codigo); ?></span>
    </div>
    <div class="card-body">
        <form id="formConquista" action="<?php echo site_url('conquistas-admin/atualizar'); ?>" method="post">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="id" value="<?php echo $conquista->id; ?>">
            
            <?php echo $this->include('Conquistas/_form'); ?>
            
            <hr>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bx bx-save me-2"></i>Atualizar Conquista
                </button>
                <a href="<?php echo site_url('conquistas-admin'); ?>" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-2"></i>Voltar
                </a>
            </div>
        </form>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script src="<?php echo site_url('recursos/'); ?>js/front.js"></script>
<script>
$(document).ready(function() {
    $('#formConquista').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var btn = form.find('button[type="submit"]');
        var btnOriginal = btn.html();
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Salvando...');
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.token) {
                    $('input[name="<?php echo csrf_token(); ?>"]').val(response.token);
                }
                
                if (response.erro) {
                    alert('Erro: ' + response.erro);
                    btn.prop('disabled', false).html(btnOriginal);
                } else if (response.redirect) {
                    window.location.href = response.redirect;
                }
            },
            error: function(xhr, status, error) {
                alert('Erro ao processar requisição: ' + error);
                btn.prop('disabled', false).html(btnOriginal);
            }
        });
    });
});
</script>
<?php echo $this->endSection() ?>
