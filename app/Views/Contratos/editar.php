<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>

<link href="<?php echo site_url('recursos/theme/'); ?>plugins/select2/css/select2.min.css" rel="stylesheet" />
<link href="<?php echo site_url('recursos/theme/'); ?>plugins/select2/css/select2-bootstrap4.css" rel="stylesheet" />

<style>
    .select2-container--bootstrap4 .select2-selection--single {
        height: calc(1.5em + 0.75rem + 2px) !important;
    }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        line-height: calc(1.5em + 0.75rem) !important;
    }
</style>

<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">
        <a href="<?php echo site_url('/contratos'); ?>">Contratos</a>
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('/contratos'); ?>">Contratos</a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url("contratos/exibir/$contrato->id") ?>"><?= esc($contrato->codigo ?? '#' . $contrato->id); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <div class="btn-group">
            <input id="btn-salvar" type="submit" value="Salvar" class="btn btn-primary" form="form">
            <a href="<?php echo site_url("contratos/exibir/$contrato->id") ?>" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-2"></i>Cancelar
            </a>
        </div>
    </div>
</div>
<!--end breadcrumb-->

<div class="row">

    <div class="col-lg-10 mx-auto">

        <div class="card shadow radius-10">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bx bx-edit-alt fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-0">Editando Contrato</h5>
                        <small class="text-muted"><?= esc($contrato->codigo ?? '#' . $contrato->id) ?></small>
                    </div>
                </div>
            </div>
            
            <div class="card-body">

                <!-- Exibirá os retornos do backend -->
                <div id="response"></div>

                <?php echo form_open('/', ['id' => 'form'], ['id' => "$contrato->id"]) ?>

                <?php echo $this->include('Contratos/_form'); ?>

                <?php echo form_close(); ?>

            </div>

        </div>

    </div>

</div>


<?php echo $this->endSection() ?>




<?php echo $this->section('scripts') ?>


<script src="<?php echo site_url('recursos/vendor/loadingoverlay/loadingoverlay.min.js') ?>"></script>
<script src="<?php echo site_url('recursos/vendor/mask/jquery.mask.min.js') ?>"></script>
<script src="<?php echo site_url('recursos/vendor/mask/app.js') ?>"></script>
<script src="<?php echo site_url('recursos/theme/'); ?>plugins/select2/js/select2.min.js"></script>
<script src="<?php echo site_url('recursos/theme/'); ?>plugins/select2/js/i18n/pt-BR.js"></script>


<script>
$(document).ready(function() {

    // Máscara para valores monetários
    $('.money').mask('#.##0,00', {reverse: true});

    // Select2 para Expositor
    $('select[name="expositor_id"]').select2({
        theme: 'bootstrap4',
        placeholder: 'Pesquise pelo nome ou documento...',
        allowClear: true,
        language: 'pt-BR',
        width: '100%'
    });

    // Select2 para Evento
    $('select[name="event_id"]').select2({
        theme: 'bootstrap4',
        placeholder: 'Pesquise pelo nome do evento...',
        allowClear: true,
        language: 'pt-BR',
        width: '100%'
    });

    $("#form").on('submit', function(e) {

        e.preventDefault();

        $.ajax({

            type: 'POST',
            url: '<?php echo site_url('contratos/atualizar'); ?>',
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {

                $("#response").html('');
                $("#btn-salvar").val('Por favor aguarde...');

            },
            success: function(response) {

                $("#btn-salvar").val('Salvar');
                $("#btn-salvar").removeAttr("disabled");

                $('[name=csrf_ordem]').val(response.token);


                if (!response.erro) {

                    if (response.info) {

                        $("#response").html('<div class="alert alert-info">' + response.info + '</div>');

                    } else {

                        window.location.href = "<?php echo site_url("contratos/exibir/$contrato->id"); ?>";

                    }

                }

                if (response.erro) {

                    $("#response").html('<div class="alert alert-danger">' + response.erro + '</div>');

                    if (response.erros_model) {

                        $.each(response.erros_model, function(key, value) {

                            $("#response").append(
                                '<ul class="list-unstyled"><li class="text-danger">' +
                                value + '</li></ul>');

                        });

                    }

                }

            },
            error: function() {

                alert('Não foi possível processar a solicitação. Por favor entre em contato com o suporte técnico.');
                $("#btn-salvar").val('Salvar');
                $("#btn-salvar").removeAttr("disabled");

            }

        });

    });


    $("#form").submit(function() {

        $(this).find(":submit").attr('disabled', 'disabled');

    });


});
</script>


<?php echo $this->endSection() ?>

