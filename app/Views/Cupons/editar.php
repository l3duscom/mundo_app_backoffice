<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Cupons de Desconto</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('cupons'); ?>">Cupons</a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bx bx-edit me-2"></i>Editar Cupom: <?php echo esc($cupom->codigo); ?></h6>
        <div>
            <?php echo $cupom->getBadgeStatus(); ?>
            <span class="badge bg-light text-dark ms-2">Usado: <?php echo $cupom->quantidade_usada; ?> vez(es)</span>
        </div>
    </div>
    <div class="card-body">
        <?php echo form_open('/', ['id' => 'formCupom', 'class' => 'row g-3'], ['id' => $cupom->id]); ?>
            
            <?php echo $this->include('Cupons/_form'); ?>
            
            <div class="col-12">
                <hr>
                <a href="<?php echo site_url('cupons'); ?>" class="btn btn-light">
                    <i class="bx bx-arrow-back me-1"></i>Voltar
                </a>
                <button type="submit" class="btn btn-primary" id="btn-salvar">
                    <i class="bx bx-save me-1"></i>Salvar Alterações
                </button>
            </div>
            
        <?php echo form_close(); ?>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>

<script src="<?php echo site_url('admin/assets/js/form.js'); ?>"></script>

<script>
$(document).ready(function() {
    
    $("#formCupom").on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            type: "POST",
            url: "<?php echo site_url('cupons/atualizar'); ?>",
            data: new FormData(this),
            dataType: "json",
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {
                $("#btn-salvar").prop("disabled", true).html('<span class="spinner-border spinner-border-sm"></span> Salvando...');
            },
            success: function(response) {
                $("#btn-salvar").prop("disabled", false).html('<i class="bx bx-save me-1"></i>Salvar Alterações');
                $("input[name=csrf_test_name]").val(response.token);

                if (!response.erro) {
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    }
                }

                if (response.erro) {
                    Lobibox.notify('error', {
                        pauseDelayOnHover: true,
                        continueDelayOnInactiveTab: false,
                        position: 'top right',
                        msg: response.erro
                    });

                    if (response.erros_model) {
                        $.each(response.erros_model, function(campo, mensagem) {
                            $("[name=" + campo + "]").addClass('is-invalid');
                            $("[name=" + campo + "]").next('.invalid-feedback').html(mensagem);
                        });
                    }
                }
            },
            error: function() {
                $("#btn-salvar").prop("disabled", false).html('<i class="bx bx-save me-1"></i>Salvar Alterações');
                Lobibox.notify('error', {
                    pauseDelayOnHover: true,
                    continueDelayOnInactiveTab: false,
                    position: 'top right',
                    msg: 'Erro ao processar requisição'
                });
            }
        });
    });

    // Remover classe de erro ao digitar
    $('input, select').on('input change', function() {
        $(this).removeClass('is-invalid');
    });
});
</script>

<?php echo $this->endSection() ?>
