<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>
<style>
    .campos-pj {
        transition: all 0.3s ease;
    }
</style>
<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Meu Perfil</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Meu Perfil</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <input id="btn-salvar" type="submit" value="Salvar" class="btn btn-primary" form="form">
    </div>
</div>

<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card shadow radius-10">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <div class="me-3"><i class="bx bx-user fs-4 text-primary"></i></div>
                    <div>
                        <h5 class="card-title mb-0">Meus Dados</h5>
                        <small class="text-muted"><?= esc($expositor->getNomeExibicao()) ?></small>
                    </div>
                </div>
            </div>

            <div class="card-body">

                <div id="response"></div>

                <?php echo form_open('/', ['id' => 'form']) ?>

                <?php echo $this->include('Expositores/_form'); ?>

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

<script>
$(document).ready(function() {

    <?php echo $this->include('Expositores/_viacep'); ?>

    function toggleCamposPessoa() {
        var isPj = $('#tipo_pj').is(':checked');
        if (isPj) {
            $('#documento-label').text('CNPJ *');
            $('#documento').addClass('cnpj').removeClass('cpf');
            $('#nome-label').text('Razão Social *');
            $('#nome').attr('placeholder', 'Insira a razão social');
            $('#documento').attr('placeholder', 'Insira o CNPJ');
            $('#documento').mask('00.000.000/0000-00');
            $('.campos-pj').slideDown(300);
        } else {
            $('#documento-label').text('CPF *');
            $('#documento').addClass('cpf').removeClass('cnpj');
            $('#nome-label').text('Nome Completo *');
            $('#nome').attr('placeholder', 'Insira o nome completo');
            $('#documento').attr('placeholder', 'Insira o CPF');
            $('#documento').mask('000.000.000-00');
            $('.campos-pj').slideUp(300);
        }
    }

    $('input[name="tipo_pessoa"]').on('change', function() {
        toggleCamposPessoa();
    });

    $("#form").on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: '<?php echo site_url('expositores/atualizarMeuPerfil'); ?>',
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

                if (response.sucesso) {
                    $("#response").html('<div class="alert alert-success">' + response.sucesso + '</div>');
                    window.scrollTo({top: 0, behavior: 'smooth'});
                } else if (response.info) {
                    $("#response").html('<div class="alert alert-info">' + response.info + '</div>');
                } else if (response.erro) {
                    $("#response").html('<div class="alert alert-danger">' + response.erro + '</div>');
                    if (response.erros_model) {
                        $.each(response.erros_model, function(key, value) {
                            $("#response").append('<ul class="list-unstyled"><li class="text-danger">' + value + '</li></ul>');
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

    $("#form").submit(function () {
        $(this).find(":submit").attr('disabled', 'disabled');
    });
});
</script>

<?php echo $this->endSection() ?>
