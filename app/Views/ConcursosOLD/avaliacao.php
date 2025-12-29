<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>


<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.css') ?>" />



<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>


<div class="row">


    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Avaliação de <?= $inscricao->nome_social ?></strong></div>

    </div>

    <!--end breadcrumb-->
    <div class="ms-auto">

    </div>


    <div class="row">
        <div class="col-lg-12">

            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow radius-10">
                        <div class="card-body">


                            <div class="row">


                                <div class="col-lg-4">
                                    <?php if ($inscricao->apoio != null) : ?>
                                        <audio controls class="w-100">
                                            <source src="<?php echo site_url("concursos/imagem/$inscricao->apoio"); ?>" type="audio/mpeg">
                                            Your browser does not support the audio element.
                                        </audio>
                                    <?php else : ?>
                                        <audio controls class="w-100">
                                            <source src="<?php echo site_url("concursos/imagem/free.mp3"); ?>" type="audio/mpeg">
                                            Your browser does not support the audio element.
                                        </audio>
                                    <?php endif; ?>


                                    <hr class="mt-2">

                                    <p class="mb-0 text-muted" style="font-size: 10px;">Personagem</p>
                                    <strong style="font-size: 14px; color:antiquewhite"><?= $inscricao->personagem ?></strong>
                                    <p class="mb-0 text-muted" style="font-size: 10px;">Obra</p>
                                    <strong style="font-size: 14px; color:antiquewhite"><?= $inscricao->obra ?></strong>
                                    <p class="mb-0 text-muted" style="font-size: 10px;">Gênero</p>
                                    <strong style="font-size: 14px; color:antiquewhite"><?= $inscricao->genero ?></strong>
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#comumModal"><strong class="mt-5 btn w-100 btn-primary">Imagem de referência</strong></a>
                                    <hr class="mt-2">

                                    <a href="<?php echo site_url("concursos/imagem/$inscricao->apoio"); ?>">Baixar material de apoio</a>
                                </div>
                                <div class="col-lg-8">
                                    <?php echo form_open('/', ['id' => 'form']) ?>
                                    <?php echo $this->include('Concursos/_form_avaliacao_cosplay'); ?>
                                    <div class="col-lg-12">
                                        <hr>
                                        <input id="btn-salvar" type="submit" value="Finalizar avaliação" class="btn btn-lg btn-success w-100">
                                        <?php echo form_close(); ?>
                                    </div>
                                </div>

                            </div>




                        </div>
                    </div>
                </div>




            </div>





            <hr>



        </div>


    </div>
</div>




<div class="modal fade" id="comumModal" tabindex="-1" aria-labelledby="comumModallLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="comumModalLabel">Imagem de referência</h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

            </div>

            <div class="modal-body">

                <div class="card">
                    <div class="card-body">
                        <img src="<?php echo site_url("concursos/imagem/$inscricao->referencia"); ?>" width="100%" class="mb-2">

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Entendi!</button>

            </div>
        </div>
    </div>
</div>



<?php echo $this->endSection() ?>


<?php echo $this->section('scripts') ?>


<script type="text/javascript" src="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.js') ?>"></script>


<script>
    $(document).ready(function() {

        //$("#form").LoadingOverlay("show");




        $("#form").on('submit', function(e) {


            e.preventDefault();


            $.ajax({

                type: 'POST',
                url: '<?php echo site_url('Concursos/finaliza_avaliacao'); ?>',
                data: new FormData(this),
                dataType: 'json',
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function() {

                    $("#response").html('');
                    $("#btn-salvar").val('Processando pagamento...');
                    $("#btn-salvar").attr('disabled', 'disabled');

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
                                "<?php echo site_url("Concursos/gerenciar/"); ?>" + <?php echo $inscricao->concurso_id; ?>;

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