<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
#imagemPreview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">
        <a href="<?php echo site_url('/order-bumps'); ?>">Order Bumps</a>
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('/order-bumps'); ?>">Order Bumps</a></li>
                <li class="breadcrumb-item active">Novo</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="<?php echo site_url('/order-bumps'); ?>" class="btn btn-secondary">
            <i class="bx bx-arrow-back me-2"></i>Voltar
        </a>
    </div>
</div>

<div class="card shadow radius-10">
    <div class="card-header">
        <h5 class="mb-0"><i class="bx bx-package me-2"></i><?php echo $titulo; ?></h5>
    </div>
    <div class="card-body">
        <form id="formOrderBump" method="post" action="<?php echo site_url('order-bumps/cadastrar'); ?>" enctype="multipart/form-data">
            <input type="hidden" name="<?php echo csrf_token(); ?>" value="<?php echo csrf_hash(); ?>">
            
            <?php echo $this->include('OrderBumps/_form'); ?>
            
            <hr class="my-4">
            <div class="d-flex justify-content-end">
                <a href="<?php echo site_url('/order-bumps'); ?>" class="btn btn-light me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bx bx-save me-2"></i>Salvar Order Bump
                </button>
            </div>
        </form>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
$(document).ready(function() {
    // Máscara de dinheiro
    $('.money').mask('#.##0,00', {reverse: true});

    // Preview de imagem
    $('#inputImagem').change(function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#imagemPreview').html('<img src="' + e.target.result + '" alt="Preview">');
            }
            reader.readAsDataURL(file);
        }
    });

    // Submit com AJAX
    $('#formOrderBump').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var btn = form.find('button[type="submit"]');
        var formData = new FormData(this);
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Salvando...');
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                $('[name="<?php echo csrf_token(); ?>"]').val(response.token);
                
                if (response.erro) {
                    alert(response.erro);
                    if (response.erros_model) {
                        var erros = Object.values(response.erros_model).join('\n');
                        alert(erros);
                    }
                    btn.prop('disabled', false).html('<i class="bx bx-save me-2"></i>Salvar Order Bump');
                    return;
                }
                
                if (response.redirect) {
                    window.location.href = response.redirect;
                }
            },
            error: function() {
                alert('Erro ao processar requisição');
                btn.prop('disabled', false).html('<i class="bx bx-save me-2"></i>Salvar Order Bump');
            }
        });
    });
});
</script>
<?php echo $this->endSection() ?>
