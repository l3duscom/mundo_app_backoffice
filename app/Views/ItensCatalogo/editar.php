<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>
<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">
        <a href="<?php echo site_url('/itenscatalogo'); ?>">Catálogo de Itens</a>
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('/itenscatalogo'); ?>">Catálogo</a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<div class="row">
    <div class="col-xl-9 mx-auto">

        <div id="response"></div>

        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><?php echo $titulo; ?></h6>
            </div>
            <div class="card-body">
                <form id="formItem" action="<?php echo site_url("itenscatalogo/atualizar"); ?>">

                    <?php echo csrf_field(); ?>

                    <input type="hidden" name="id" value="<?php echo $item->id; ?>">

                    <?php echo $this->include('ItensCatalogo/_form'); ?>

                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bx bx-save me-1"></i>Salvar
                            </button>
                            <a href="<?php echo site_url("itenscatalogo/exibir/{$item->id}") ?>" class="btn btn-secondary ms-2">
                                <i class="bx bx-arrow-back me-1"></i>Voltar
                            </a>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>


<?php echo $this->section('scripts') ?>

<script src="<?php echo site_url('recursos/vendor/loadingoverlay/loadingoverlay.min.js') ?>"></script>
<script src="<?php echo site_url('recursos/vendor/mask/jquery.mask.min.js') ?>"></script>

<script>
$(document).ready(function() {

    $('.money').mask('#.##0,00', {reverse: true});

    $('#formItem').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            type: 'POST',
            url: $(this).attr('action'),
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {
                $('#response').html('');
                $('#formItem').LoadingOverlay("show");
            },
            success: function(response) {
                $('#formItem').LoadingOverlay("hide");
                $("input[name='csrf_mundo']").val(response.token);

                if (!response.erro && !response.info) {
                    window.location.href = "<?php echo site_url("itenscatalogo/exibir/{$item->id}"); ?>";
                }

                if (response.info) {
                    $('#response').html('<div class="alert alert-info">' + response.info + '</div>');
                }

                if (response.erro) {
                    $('#response').html('<div class="alert alert-danger">' + response.erro + '</div>');
                    if (response.erros_model) {
                        $.each(response.erros_model, function(key, value) {
                            $('#response').append('<div class="alert alert-warning">' + value + '</div>');
                        });
                    }
                }
            },
            error: function() {
                $('#formItem').LoadingOverlay("hide");
                alert('Erro ao processar a solicitação');
            }
        });
    });
});
</script>

<?php echo $this->endSection() ?>

