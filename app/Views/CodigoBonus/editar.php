<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="row">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3"><?= $titulo ?></div>
        <div class="ms-auto">
            <a href="<?= site_url('codigo-bonus') ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card radius-10">
            <div class="card-body">
                <div id="response"></div>
                <?php echo form_open('/', ['id' => 'form']) ?>
                
                <input type="hidden" name="id" value="<?= $codigo->id ?>">
                
                <?php echo $this->include('CodigoBonus/_form'); ?>

                <hr>
                <button type="submit" class="btn btn-primary" id="btnSalvar">Atualizar</button>
                
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script>
$(document).ready(function() {
    $("#form").on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: '<?php echo site_url('codigo-bonus/atualizar'); ?>',
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {
                $("#response").html('');
                $("#btnSalvar").val('Aguarde...').attr('disabled', true);
            },
            success: function(response) {
                $("#btnSalvar").val('Atualizar').removeAttr('disabled');
                if (response.sucesso) {
                    window.location.href = '<?= site_url('codigo-bonus') ?>';
                }
                if (response.erro) {
                    $("#response").html('<div class="alert alert-danger">' + response.erro + '</div>');
                }
            }
        });
    });
});
</script>
<?php echo $this->endSection() ?>
