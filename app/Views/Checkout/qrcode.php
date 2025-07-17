<?php echo $this->extend('Layout/externo'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>


<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.css') ?>" />



<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>



<div class="row mt-4">
    <div class="col-lg-8">
        <div class="block">
            <div class="block-body">
                <div class="card shadow radius-10">
                    <div class="card-body">

                        <div class="row mb-2" style="padding: 15px;">




                            <div class="col-4" style="border-bottom-width: 4px; border-bottom-style: solid; border-bottom-color: #6C038F">
                                <center><strong style="color: #6C038F; word-wrap: normal;">CONFIRMAÇÃO</strong></center>
                            </div>
                        </div>

                        <div class="d-flex align-items-center">
                            <div class="card shadow-none w-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="">
                                            <h3 class="mb-0 mt-3"><i class="fa-brands fa-pix"></i>PIX</h3>
                                        </div>
                                        <div class="ms-auto fs-3 mb-0">
                                            <p class="mb-0" style="font-size: 10px;">Total a pagar:</p>
                                            <strong>R$ <?= number_format($transaction->installment_value, 2, ',', ''); ?></strong>
                                            <div class="text-success" style="font-size: 11px;"><i class="bi bi-check-circle-fill"></i> Desconto de 10% aplicado</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <!-- Exibirá os retornos do backend -->
                        <div id="response">


                        </div>

                        <div class="card-body">


                            <?php

                            if ($status == 'RECEIVED') {
                                $url = site_url('checkout/obrigado/');
                                header('Location: ' . $url);
                                exit;

                                echo '<script>window . location . replace(' . $url . ')</script>';
                            }
                            ?>


                            <div class="d-flex align-items-center" style="margin-top: -30px;">
                                <div class="card border shadow-none w-100">
                                    <div class="card-body mb-0 mt-0">
                                        <div class="row" style="padding: 10px;">
                                            <div class="card shadow radius-10">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-grow-1">
                                                            <h5 class="mb-1">Agora falta muito pouco para você viver a magia do Dreamfest! </h5>
                                                            <p class="mb-0 text-muted" style="font-size: 13px">Efetue o pagamento do QRCODE abaixo e garanta sua presença no evento geek mais mágico do Sul do Brasil!</p>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-4 mb-3">
                                                <img src="data:image/png+xml;base64,<?= $transaction->qrcode_image; ?>" width="100%">
                                            </div>
                                            <div class="col-lg-8" style="padding: 20px;">
                                                <img src="https://download.gerencianet.com.br/img/logo-pix.svg">
                                                <p class="mt-2">Com o QR Code Pix, você paga e recebe, com segurança, em segundos, a qualquer dia e hora.</p>
                                                <div class="row">
                                                    <div class="col-8">
                                                        <button id="execCopy" class="btn btn-primary btn-block"><i class="fadeIn animated bx bx-copy w-100"></i> Copiar QR Code pix</button>
                                                    </div>
                                                    <input id="input" type="text" class="text-muted" style="border:none" value="<?= trim($transaction->qrcode); ?>" style="margin: 5px; padding:10px">
                                                    <script>
                                                        // Type 1
                                                        document.getElementById('execCopy').addEventListener('click', execCopy);

                                                        function execCopy() {
                                                            document.querySelector("#input").select();
                                                            document.execCommand("copy");
                                                        }
                                                    </script>
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                </div>
                            </div>



                        </div>

                        <center>
                            <span class="text-muted mb-5" style="font-size: 12px;">Processado por:</span><br>
                            <img class="mt-1" src="<?php echo site_url('recursos/front/images/asaas.png'); ?>" width="150px" height="auto">
                        </center>
                    </div>


                </div>

            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow radius-10">
            <div class="card-body">
                <div class="d-flex align-items-center">


                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="">
                                <h5 class="mb-0">Compra segura</h4>
                                    <p class="mb-0">Ambiente seguro e autenticado</p>
                                    <span class="text-muted" style="font-size: 10px;">Este site utiliza certificado SSL</span>
                            </div>
                            <div class="ms-auto fs-3 ">
                                <i class="fadeIn animated bx bx-check-shield" style="font-size: 45px;"></i>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height: 5px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 42%"></div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>

</div>

<div class="row" style="padding-left: 20px; padding-right: 20px">
    <div class="col-8">
        <div class="text-muted" style="font-size: 11px; ">
            <p class="mb-0"><strong>Precisa de ajuda? </strong><a href="#" target="_blank">Entre em contato</a></p>
            <p class="mt-0 mb-0">* O valor parcelado possui acréscimo.</p>
            <p class="mt-0 mb-0"><strong>Meia entrada solidária </strong> (50% de desconto) disponível para qualquer pessoa que levar 1kg de alimento não perecível no dia do evento.</p>
            <p class="mt-0 mb-0">Ao clicar em 'Comprar agora', eu concordo (i) com os termos de uso e regras do evento denominado Dreamfest 25 - Mega Festivalk Geek e estou ciente da Política de Privacidade e que sou maior de idade ou autorizado e acompanhado por um tutor legal.</p>

            <hr>
            <p class="mt-0 mb-0">L & M SOLUCOES EM EVENTOS E CONVENCOES DE CULTURA POP LTDA © 2023 - Todos os direitos reservados</p>
            <p class="mt-0 mb-0">21.812.142/0001-23</p>
        </div>
    </div>
</div>


<div class="fixed-bottom bg-white shadow-lg">

    <div class="d-grid gap-2 mb-0" style="padding:7px">
        <center><span style="padding-top: 5px; margin-bottom: -5px; font-size: 16px">Já efetuou o pagamento? </span><strong id="timer" style="font-size: 22px;"></strong></center>
        <input id="btn-salvar" type="submit" value="Confirmar pagamento" onClick="window.location.reload()" class="btn btn-success btn-lg mt-0">

    </div>

</div>

<?php echo $this->endSection() ?>


<?php echo $this->section('scripts') ?>

<!-- Meta Pixel Purchase Event -->
<?php if (isset($evento) && !empty($evento->meta_pixel_id)): ?>
<script>
// Purchase Event - quando o pagamento PIX é finalizado na página de QR Code
fbq('track', 'Purchase', {
    content_name: '<?= $evento->nome ?> - PIX',
    content_category: '<?= $evento->categoria ?? 'Evento' ?>',
    content_type: 'product',
    value: <?= $transaction->installment_value ?? 0 ?>,
    currency: 'BRL',
    content_ids: [<?= $evento->id ?>],
    order_id: '<?= $charge_id ?? '' ?>'
});
</script>
<?php endif; ?>

<script src="<?php echo site_url('recursos/vendor/loadingoverlay/loadingoverlay.min.js') ?>"></script>


<script src="<?php echo site_url('recursos/vendor/mask/jquery.mask.min.js') ?>"></script>
<script src="<?php echo site_url('recursos/vendor/mask/app.js') ?>"></script>


<script>
    $(document).ready(function() {

        //$("#form").LoadingOverlay("show");




        $("#form").on('submit', function(e) {


            e.preventDefault();


            $.ajax({

                type: 'POST',
                url: '<?php echo site_url('checkout/finalizar'); ?>',
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

                            window.location.href = "<?php echo site_url('checkout/qrcode/' . $event_id . '/'); ?>" + response.id;

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