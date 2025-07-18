<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>

<style>
    .partner-fields {
        transition: all 0.3s ease;
    }
    
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    
    .form-group {
        margin-bottom: 1rem;
    }
    
    .custom-control-label {
        font-weight: 500;
    }
    
    .partner-info {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-top: 10px;
    }
    
         .partner-info h6 {
         color: #495057;
         margin-bottom: 15px;
     }
     
     .alert-info {
         border-left: 4px solid #0dcaf0;
     }
     
     .card-header {
         border-bottom: 2px solid #e9ecef;
     }
     
     .card-header .fs-4 {
         font-size: 1.5rem;
     }
     
     .border-left-info {
         border-left: 4px solid #0dcaf0;
         padding-left: 15px;
     }
</style>

<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>


<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">
        <a href="<?php echo site_url('/clientes'); ?>">Clientes</a>
        <?php if ($cliente->pj == 1) : ?>
            / <a href="<?php echo site_url('/clientes/parceiros'); ?>">Parceiros</a>
        <?php endif; ?>
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <?php if ($cliente->pj == 1) : ?>
                    <li class="breadcrumb-item"><a href="<?php echo site_url('/clientes/parceiros'); ?>">Parceiros</a></li>
                <?php endif; ?>
                <li class="breadcrumb-item"><a href="<?php echo site_url("clientes/exibir/$cliente->id") ?>"><?= esc($cliente->nome); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <div class="btn-group">
            <input id="btn-salvar" type="submit" value="Salvar" class="btn btn-primary" form="form">
            <a href="<?php echo site_url("clientes/exibir/$cliente->id") ?>" class="btn btn-secondary">
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
                        <h5 class="card-title mb-0">Editando <?= $cliente->pj == 1 ? 'Parceiro' : 'Cliente' ?></h5>
                        <small class="text-muted"><?= esc($cliente->nome) ?></small>
                    </div>
                </div>
            </div>
            
            <div class="card-body">

                <!-- Exibirá os retornos do backend -->
                <div id="response"></div>

                <?php echo form_open('/', ['id' => 'form'], ['id' => "$cliente->id"]) ?>

                <?php echo $this->include('Clientes/_form_edit'); ?>

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

    //$("#form").LoadingOverlay("show");

    <?php echo $this->include('Clientes/_checkmail'); ?>

    <?php echo $this->include('Clientes/_viacep'); ?>

    // Função para alterar campos baseado no tipo de pessoa
    function toggleCamposPessoa() {
        var isPj = $('#pj').is(':checked');
        
        if (isPj) {
            $('#cpf-label').text('CNPJ');
            $('#cpf').addClass('cnpj').removeClass('cpf');
            $('#nome-label').text('Razão Social');
            $('#nome').attr('placeholder', 'Insira a razão social');
            $('#cpf').attr('placeholder', 'Insira o CNPJ');
            $('#cpf').mask('00.000.000/0000-00');
            $('.partner-fields').show();
        } else {
            $('#cpf-label').text('CPF');
            $('#cpf').addClass('cpf').removeClass('cnpj');
            $('#nome-label').text('Nome completo');
            $('#nome').attr('placeholder', 'Insira o nome completo');
            $('#cpf').attr('placeholder', 'Insira o CPF');
            $('#cpf').mask('000.000.000-00');
            $('.partner-fields').hide();
        }
    }

    // Executar no carregamento da página
    toggleCamposPessoa();

         // Executar quando checkbox mudar
     $('#pj').on('change', function() {
         var isPj = $(this).is(':checked');
         
         // Atualizar campos e labels
         toggleCamposPessoa();
         
         // Animar mostrar/ocultar campos de parceiro
         if (isPj) {
             $('.partner-fields').slideDown(300);
         } else {
             $('.partner-fields').slideUp(300);
         }
     });

     // Se é parceiro (grupo_id = 4), sempre mostrar campos
     <?php if ($cliente->grupo_id == 4) : ?>
         $('.partner-fields').show();
         // Adicionar classe visual para indicar campos de parceiro
         $('.partner-fields').addClass('border-left-info');
     <?php endif; ?>


    $("#form").on('submit', function(e) {


        e.preventDefault();


        $.ajax({

            type: 'POST',
            url: '<?php echo site_url('clientes/atualizar'); ?>',
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

                        $("#response").html('<div class="alert alert-info">' + response
                            .info + '</div>');

                    } else {

                        // Tudo certo com a atualização do usuário
                        // Podemos agora redirecioná-lo tranquilamente

                        window.location.href =
                            "<?php echo site_url("clientes/exibir/$cliente->id"); ?>";

                    }

                }

                if (response.erro) {

                    // Exitem erros de validação


                    $("#response").html('<div class="alert alert-danger">' + response.erro +
                        '</div>');


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

                alert(
                    'Não foi possível procesar a solicitação. Por favor entre em contato com o suporte técnico.'
                    );
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