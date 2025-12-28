<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">
        <a href="<?php echo site_url('/agentes'); ?>">Agentes</a>
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('/agentes'); ?>">Agentes</a></li>
                <li class="breadcrumb-item active">Novo</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="<?php echo site_url('/agentes'); ?>" class="btn btn-secondary">
            <i class="bx bx-arrow-back me-2"></i>Voltar
        </a>
    </div>
</div>

<div class="card shadow radius-10">
    <div class="card-header">
        <h5 class="mb-0"><i class="bx bx-plus me-2"></i><?php echo $titulo; ?></h5>
    </div>
    <div class="card-body">
        <form id="formAgente" method="post" action="<?php echo site_url('agentes/cadastrar'); ?>">
            <input type="hidden" name="<?php echo csrf_token(); ?>" value="<?php echo csrf_hash(); ?>">
            
            <?php echo $this->include('Agentes/_form'); ?>
            
            <hr class="my-4">
            <div class="d-flex justify-content-end">
                <a href="<?php echo site_url('/agentes'); ?>" class="btn btn-light me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bx bx-save me-2"></i>Salvar Agente
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
    // Máscaras
    $('.cpf').mask('000.000.000-00');
    $('.cnpj').mask('00.000.000/0000-00');
    $('.telefone').mask('(00) 00000-0000');
    $('.cep').mask('00000-000');

    // Submit
    $('#formAgente').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var btn = form.find('button[type="submit"]');
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Salvando...');
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                $('[name="<?php echo csrf_token(); ?>"]').val(response.token);
                
                if (response.erro) {
                    alert(response.erro);
                    btn.prop('disabled', false).html('<i class="bx bx-save me-2"></i>Salvar Agente');
                    return;
                }
                
                if (response.sucesso) {
                    alert(response.sucesso);
                }
                
                if (response.redirect) {
                    window.location.href = response.redirect;
                }
            },
            error: function(xhr, status, error) {
                console.log('Erro:', xhr.responseText);
                alert('Erro ao processar a requisição: ' + error);
                btn.prop('disabled', false).html('<i class="bx bx-save me-2"></i>Salvar Agente');
            }
        });
    });
});
</script>
<?php echo $this->endSection() ?>
