<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">
        <a href="<?php echo site_url('/orcamentos'); ?>">Orçamentos</a>
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('/orcamentos'); ?>">Orçamentos</a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar <?php echo esc($orcamento->codigo); ?></li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<div class="card">
    <div class="card-body">
        <h5 class="card-title mb-4"><i class="bx bx-edit me-2"></i><?php echo $titulo; ?></h5>
        
        <?php echo form_open('/', ['id' => 'formOrcamento', 'class' => 'row'], ['id' => $orcamento->id]); ?>
            
            <?php echo $this->include('Orcamentos/_form'); ?>

            <div class="col-12 mt-4">
                <hr>
                <button type="submit" class="btn btn-primary">
                    <i class="bx bx-save me-2"></i>Salvar Alterações
                </button>
                <a href="<?php echo site_url("orcamentos/exibir/{$orcamento->id}"); ?>" class="btn btn-secondary ms-2">
                    <i class="bx bx-arrow-back me-2"></i>Voltar
                </a>
            </div>

        <?php echo form_close(); ?>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script src="<?php echo site_url('recursos/theme/'); ?>plugins/input-mask/jquery.mask.min.js"></script>

<script>
$(document).ready(function() {
    $('#formOrcamento').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var btn = form.find('button[type="submit"]');
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Salvando...');
        
        $.ajax({
            url: '<?php echo site_url("orcamentos/atualizar"); ?>',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                $('[name="csrf_test_name"]').val(response.token);
                
                if (response.erro) {
                    alert(response.erro);
                    btn.prop('disabled', false).html('<i class="bx bx-save me-2"></i>Salvar Alterações');
                    return;
                }
                
                if (response.redirect) {
                    window.location.href = response.redirect;
                }
            },
            error: function() {
                alert('Erro ao processar a requisição');
                btn.prop('disabled', false).html('<i class="bx bx-save me-2"></i>Salvar Alterações');
            }
        });
    });
});
</script>

<?php echo $this->endSection() ?>
