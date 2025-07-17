<?php echo $this->extend('Layout/externo'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>


<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.css') ?>" />


<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>



<?php
if (!isset($_SESSION['frete'])) {
    $_SESSION['frete'] = 1;
    $_SESSION['valor_frete'] = 0;
};
if (!isset($_SESSION['casa'])) {
    $_SESSION['casa'] = '';
    $_SESSION['btn_texto_casa'] = 'Selecionar';
    $_SESSION['btn_color_casa'] = 'muted';
};
if (!isset($_SESSION['impressao'])) {
    $_SESSION['impressao'] = '';
    $_SESSION['btn_texto_impressao'] = 'Selecionar';
    $_SESSION['btn_color_impressao'] = 'muted';
};

?>


<h5 class="mb-0 mt-3">Como você quer receber os seus ingressos?</h5>

<div class="row mt-4">
    <div class="col-lg-8">
        <div class="block">
            <div class="block-body">
                <div class="card shadow radius-10">
                    <div class="card-body">



                        <!-- Exibirá os retornos do backend -->
                        <div id="response">


                        </div>



                        <?php
                        $entregas = array(

                            ['tipo' => 'casa', 'valor' => 25, 'descricao' => 'Receba seu kit com ingresso, credencial + cordão colecionável, pulseiras e guia no conforto da sua casa (máximo de 4 ingressos por pacote)', 'titulo' => 'Disponível para entrega', 'badge' => '+ R$ 25,00', 'icone' => '<i class="fa-solid fa-truck-fast"></i>', 'classe' => 'badge bg-warning text-dark font-13'],
                            ['tipo' => 'impressao', 'valor' => 0, 'descricao' => 'Seu ingresso vai estar disponível na sua área de membros.', 'titulo' => 'Disponível no formato digital', 'badge' => 'GRÁTIS', 'classe' => 'badge bg-success  font-13']

                        );


                        ?>


                        <?php
                        if (isset($_GET['escolher'])) {
                            $entrega = (int) $_GET['escolher'];

                            $_SESSION['frete'] =  $entregas[$entrega]['tipo'];
                            if ($_SESSION['valor_frete'] == 0) {
                                $_SESSION['valor_frete'] =  $entregas[$entrega]['valor'];
                            } else {
                                $_SESSION['valor_frete'] =  0;
                            }

                            if ($entregas[$entrega]['tipo'] == 'casa') {
                                $_SESSION['casa'] = 'disabled';
                                $_SESSION['impressao'] = '';
                                $_SESSION['btn_texto_casa'] = 'selecionado';
                                $_SESSION['btn_color_casa'] = 'success';
                                $_SESSION['btn_texto_impressao'] = 'selecionar';
                                $_SESSION['btn_color_impressao'] = 'muted';
                            } else {
                                $_SESSION['casa'] = '';
                                $_SESSION['impressao'] = 'disabled';
                                $_SESSION['btn_texto_casa'] = 'Selecionar';
                                $_SESSION['btn_color_casa'] = 'muted';
                                $_SESSION['btn_texto_impressao'] = 'Selecionado';
                                $_SESSION['btn_color_impressao'] = 'success';
                            }
                        }




                        ?>



                        <div class=" mt-1"></div>
                        <div class="d-flex align-items-center">
                            <div class="card shadow-none w-100">
                                <div class="card-body ">
                                    <div class="d-flex align-items-center ">
                                        <div class="">
                                            <h4 class="mb-0"><i class='bx bxs-package'></i> Selecione uma forma de entrega </h4>
                                        </div>


                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-lg-12">

                                            <div class="card-body ">

                                                <?php foreach ($entregas as $key => $value) : ?>


                                                    <div class="card border border-<?= $_SESSION['btn_color_' . $value['tipo']] ?>">
                                                        <div class="form-check mt-3 mb-3">

                                                            <a href="?escolher=<?= $key ?>" id="<?= $value['tipo'] ?>" class=" btn btn-sm btn-primary mb-3 <?= $_SESSION[$value['tipo']] ?>" style="background-color: purple; border-color: purple; color: white;"><?= $_SESSION['btn_texto_' . $value['tipo']] ?></a>
                                                            <label class="form-check-label ml-5 font-20" for="flexRadioDefault1" style="padding-left: 10px;"><?= $value['titulo'] ?> <span class="<?= $value['classe'] ?>" style="margin-left: 7px;"> <?= $value['badge'] ?></span>
                                                                <br>
                                                                <span class="font-14 mt-0"> <?= $value['descricao'] ?></span>
                                                            </label>

                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>

                                                <div class="d-flex align-items-center ">


                                                    <div class="ms-auto fs-3 mb-0">
                                                        <p class="mb-0" style="font-size: 10px;">Total a pagar:</p>
                                                        <strong class="valor-total">R$ <?= number_format($_SESSION['total'] + $_SESSION['valor_frete'], 2, ',', '') ?></strong>
                                                    </div>

                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <hr>
                                    <center>
                                        <span class="text-muted mb-5" style="font-size: 12px;">Processado por:</span><br>
                                        <img class="mt-1" src="<?php echo site_url('recursos/front/images/asaas.png'); ?>" width="150px" height="auto">
                                    </center>


                                </div>
                            </div>
                        </div>











                        <?php if ($_SESSION['total'] != 0) : ?>
                            <!--  <div class="ms-auto fs-3 mb-0">
                                    <p class="mb-0" style="font-size: 10px;">Total a pagar:</p>
                                    <strong>R$ <?= number_format($_SESSION['total'] + $_SESSION['valor_frete'], 2, ',', '') ?></strong>
                                </div> -->
                            <div id="areaBotoes" class="row g-1">
                                <div class="col-lg-6">
                                    <a href="<?= site_url('/checkout/cartao/' . $event_id) ?>" class="w-100 btn btn-lg " style="background-color: purple; border-color: purple; color: white;"><span class="text-white" style="font-size: 12px;">Pagar com:</span><i class="bi bi-credit-card-fill me-2 font-16"></i>Cartão</a>
                                </div>
                                <div class="col-lg-6">
                                    <a href="<?= site_url('/checkout/pix/' . $event_id) ?>" class="w-100 btn btn-lg " style="background-color: purple; border-color: purple; color: white;"><span class="text-white" style="font-size: 12px;">Pagar com:</span><i class="fa-brands fa-pix"></i> PIX <span class="badge bg-warning text-dark font-13" style="margin-left: 7px;">10% OFF</span></a>
                                </div>
                                <!--
                                <div class="col-lg-4">
                                    <a href="<?= site_url('/checkout/boleto/'. $event_id) ?>" class="w-100 btn btn-primary btn-lg disabled"><span class="text-white" style="font-size: 12px;">Pagar com:</span><i class="bx bx-barcode-reader me-2 font-24"></i>Boleto</a>
                                </div>
                            -->

                            </div>

                        <?php endif ?>
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
            <p class="mt-0 mb-0"><strong>Meia entrada solidária </strong> (40% de desconto) disponível para qualquer pessoa que levar 1kg de alimento não perecível no dia do evento.</p>
            <p class="mt-0 mb-0">Ao clicar em 'Comprar agora', eu concordo (i) com os termos de uso e regras do evento denominado Dreamfest 25 - Mega Festivalk Geek e estou ciente da Política de Privacidade e que sou maior de idade ou autorizado e acompanhado por um tutor legal.</p>

            <hr>
            <p class="mt-0 mb-0">MUNDO DREAM EVENTOS E PRODUCOES LTDA © 2024 - Todos os direitos reservados</p>
            <p class="mt-0 mb-0">21.812.142/0001-23</p>
        </div>
    </div>
</div>





















<?php echo $this->endSection() ?>


<?php echo $this->section('scripts') ?>


<script src="<?php echo site_url('recursos/vendor/loadingoverlay/loadingoverlay.min.js') ?>"></script>


<script src="<?php echo site_url('recursos/vendor/mask/jquery.mask.min.js') ?>"></script>
<script src="<?php echo site_url('recursos/vendor/mask/app.js') ?>"></script>

<!-- Meta Pixel ViewContent Event -->
<?php if (isset($evento) && !empty($evento->meta_pixel_id)): ?>
<script>
// ViewContent Event - quando a página de entrega é carregada
fbq('track', 'ViewContent', {
    content_name: '<?= $evento->nome ?> - Entrega',
    content_category: '<?= $evento->categoria ?? 'Evento' ?>',
    content_type: 'product',
    content_ids: [<?= $evento->id ?>],
    value: <?= $total ?? 0 ?>,
    currency: 'BRL'
});
</script>
<?php endif; ?>

<script>
    $(document).ready(function() {

        //$("#form").LoadingOverlay("show");

        <?php echo $this->include('Clientes/_checkmail'); ?>

        <?php echo $this->include('Clientes/_viacep'); ?>

        // Função para calcular desconto PIX
        function calcularDescontoPix() {
            var valorOriginal = <?= $_SESSION['total'] ?? 0 ?>;
            var desconto = valorOriginal * 0.10; // 10% de desconto
            var valorComDesconto = valorOriginal - desconto;
            
            // Atualizar o valor total exibido
            $('.valor-total').text('R$ ' + valorComDesconto.toFixed(2).replace('.', ','));
            
            // Adicionar informação do desconto
            if (!$('.desconto-pix-info').length) {
                $('.valor-total').after('<div class="desconto-pix-info text-success" style="font-size: 12px;"><i class="bi bi-check-circle-fill"></i> Desconto de 10% aplicado no PIX</div>');
            }
            
            return valorComDesconto;
        }

        // Função para remover desconto PIX
        function removerDescontoPix() {
            var valorOriginal = <?= $_SESSION['total'] ?? 0 ?>;
            $('.valor-total').text('R$ ' + valorOriginal.toFixed(2).replace('.', ','));
            $('.desconto-pix-info').remove();
        }

        // Aplicar desconto quando clicar no botão PIX
        $('a[href*="checkout/pix"]').click(function(e) {
            e.preventDefault();
            var href = $(this).attr('href');
            var valorComDesconto = calcularDescontoPix();
            
            // Adicionar o valor com desconto como parâmetro na URL
            var separator = href.includes('?') ? '&' : '?';
            href += separator + 'desconto_pix=' + valorComDesconto;
            
            window.location.href = href;
        });

        // Remover desconto quando clicar no botão Cartão
        $('a[href*="checkout/cartao"]').click(function() {
            removerDescontoPix();
        });


        $("#form").on('submit', function(e) {


            e.preventDefault();


            $.ajax({

                type: 'POST',
                url: '<?php echo site_url('carrinho/cupom'); ?>',
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
                                "<?php echo site_url("carrinho"); ?>";

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