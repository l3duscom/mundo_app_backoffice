<?php echo $this->extend('Layout/externo'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>


<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.css') ?>" />



<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>
<?php
$total_parcial = $_SESSION['total'];
$desconto = 10;
$valor_desconto = $total_parcial / 100 * $desconto;
$total = $total_parcial - ($total_parcial / 100 * $desconto);
?>

<h5 class="mb-0 mt-3">DREAMFEST 23 - MEGA FESTIVAL GEEK</h5>
<strong style="color: #A7A7A7"> 10/06/2023 - 11/06/2023 // PORTO ALEGRE</strong>

<div class="row mt-4">
    <div class="col-lg-8">
        <div class="block">
            <div class="block-body">
                <div class="card shadow radius-10">
                    <div class="card-body">

                        <div class="row mb-2" style="padding: 15px;">
                            <div class="col-4" style="border-bottom-width: 3px; border-bottom-style: solid; border-bottom-color: #A7A7A7">
                                <center><strong style="color: #A7A7A7; word-wrap: normal;">MINHA SACOLA</strong></center>
                            </div>


                            <div class="col-4" style="border-bottom-width: 5px; border-bottom-style: solid; border-bottom-color: #6C038F">
                                <center><strong style="color: #6C038F; word-wrap: normal;">PAGAMENTO</strong></center>
                            </div>
                            <div class="col-4" style="border-bottom-width: 3px; border-bottom-style: solid; border-bottom-color: #A7A7A7">
                                <center><strong style="color: #A7A7A7; word-wrap: normal;">CONFIRMAÇÃO</strong></center>
                            </div>
                        </div>

                        <div class="d-flex align-items-center">
                            <div class="card shadow-none w-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="">
                                            <h4 class="mb-0">Finalizar compra</h4>
                                            <p class="mb-0 text-muted" style="font-size: 11px">Dreamfest 23 - Mega Festival Geek</p>
                                            <p class="mb-0 text-muted" style="font-size: 11px">Ingressos - <strong>Acesso para a magia!</strong></p>
                                        </div>
                                        <div class="ms-auto fs-3 mb-0">
                                            <p class="mb-0" style="font-size: 10px;">Total a pagar:</p>
                                            <strong class="mb-0">R$ <?= number_format($total, 2, ',', '')  ?></strong>
                                            <p class="mt-0" style="font-size: 11px;">Desconto de: <strong>R$ <?= number_format($valor_desconto, 2, ',', '')  ?></strong></p>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <!-- Exibirá os retornos do backend -->
                        <div id="response">


                        </div>



                        <form method="POST" action="<?= site_url('Checkout/finalizaradm') ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="valor_total" id="valor_total" value="<?= $total * 100 ?>" required>
                            <input type="hidden" name="convite" value="<?= $_SESSION['convite'] ?? '' ?>">


                            <div class="d-flex align-items-center mt-0">
                                <div class="card border shadow-none w-100">

                                    <div class="card-header py-3">
                                        <h6 class="mb-0">Seus dados </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="form-group col-md-12 ">
                                                <label class="form-control-label">Nome completo</label>
                                                <input type="text" name="nome" placeholder="Digite seu nome completo" class="form-control form-control-lg mb-2 shadow" style="font-size:medium; padding:13px" required>
                                            </div>
                                            <div class="form-group col-md-12">
                                                <label class="form-control-label">Seu email</label>
                                                <input type="email" name="email" placeholder="Digite seu email para receber seu ingresso" class="form-control form-control-lg mb-2 shadow" style="font-size:medium; padding:13px" required>
                                                <div id="email"></div>
                                            </div>
                                            <div class="form-group col-md-12">
                                                <label class="form-control-label">Celular</label>
                                                <input type="text" name="telefone" placeholder="Insira o telefone" class="form-control sp_celphones mb-2 shadow" style="font-size:medium; padding:13px">
                                            </div>
                                        </div>


                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center mt-0">
                                <div class="card border shadow-none w-100">

                                    <div class="card-header py-3">
                                        <h6 class="mb-0">Pagamento</h6>
                                    </div>
                                    <div class="card-body">

                                        <div class="form-group col-md-12">
                                            <label class="form-control-label">CPF</label>
                                            <input type="text" name="cpf" placeholder="Digite o número do  seu CPF" class="form-control form-control-lg mb-2 shadow cpf" style="font-size:medium; padding:13px" required>
                                            <span class="text-muted" style="font-size: 11px; padding-left: 5px">Por exigência do Banco Central, o boleto precisa ter seu CPF.</span>
                                        </div>
                                        <hr>
                                        <div class="d-flex align-items-center">
                                            <div class="card border shadow-none">
                                                <div class="card-body mb-0 mt-0">

                                                    <div class="row">
                                                        <div class="col-lg-4">
                                                            <div class="d-flex align-items-center mt-0">
                                                                <div class="card border shadow-none w-100">
                                                                    <div class="card-body mb-4 mt-0">
                                                                        <i class="bx bx-calendar-event me-2 font-24"></i>
                                                                        <h5>Pague até a data de vencimento</h5>
                                                                        <span>Faça o pagamento até a data de vencimento e garanta seu ingresso.</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-4">
                                                            <div class="d-flex align-items-center mt-0">
                                                                <div class="card border shadow-none w-100">
                                                                    <div class="card-body mb-4 mt-0">
                                                                        <i class="bx bx-time me-2 font-24"></i>
                                                                        <h5>Aguarde a aprovação do pagamento</h5>
                                                                        <span>Pagamentos com boleto levam até 3 dias úteis para serem compensados.</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-4">
                                                            <div class="d-flex align-items-center mt-0 mb-0">
                                                                <div class="card border shadow-none w-100 bg-gradient-purple">
                                                                    <div class="card-body mb-4 mt-0 text-light">
                                                                        <img class="mb-2" src="<?php echo site_url('recursos/front/images/pix.svg'); ?>" width="24px" style="color:white">
                                                                        <h5>Pague com Pix e tenha acesso imediato ao produto</h5>
                                                                        <span>Pague o boleto via QRCODE e receba seu ingresso em segundos.</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>


                                        <div class="card-header py-3" style="margin-top: -35px !important">
                                            <h6 class="mb-0">Detalhes da compra</h6>
                                        </div>
                                        <div class="" style="padding: 5px;">
                                            <?php if (isset($_SESSION['carrinho'])) : ?>
                                                <table class="table mb-0 table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col" width="40%">Ingresso</th>
                                                            <th scope="col" width="20%" style="align-items:center">
                                                                &nbsp;&nbsp;&nbsp;&nbsp;Quantidade
                                                            </th>
                                                            <th scope="col" width="20%">Valor</th>
                                                            <th scope="col" width="20%">Taxa</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>

                                                        <?php foreach ($_SESSION['carrinho'] as $key => $value) : ?>
                                                            <?php if ($value['quantidade'] != 0) : ?>
                                                                <tr>
                                                                    <td><u><?= $value['nome']; ?></u></td>
                                                                    <td><?= $value['quantidade']; ?> </a></td>
                                                                    <td>R$ <?= number_format($value['quantidade'] * $value['unitario'], 2, ',', ''); ?></td>
                                                                    <td>R$ <?= number_format($value['quantidade'] * $value['taxa'], 2, ',', ''); ?></td>
                                                                </tr>
                                                            <?php endif; ?>


                                                        <?php endforeach; ?>
                                                    <?php else : ?>
                                                        <center>
                                                            <i class="fadeIn animated bx bx-error-circle"></i><br>Oooops, seu carrinho está vazio, escolha um ingresso e venha viver a magia no Dreamfest!
                                                        </center>
                                                        </hr>
                                                    <?php endif; ?>


                                                    </tbody>
                                                </table>
                                        </div>


                                    </div>

                                </div>


                            </div>




                            <center>
                                <p class="mt-2 text-muted"><a href="<?= site_url('/carrinho') ?>" style="color:#999999">Ou adicione mais ingressos</a></p>
                                <span class="text-muted mb-5" style="font-size: 12px;">Processado por:</span><br>
                                <img class="mt-1" src="https://asaas.com/assets/home3/header-logo-blue.svg">
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
            <p class="mt-0 mb-0">* O valor parcelado (acima de 3 parcelas) possui acréscimo.</p>
            <p class="mt-0 mb-0"><strong>Meia entrada solidária </strong> (50% de desconto) disponível para qualquer pessoa que levar 1kg de alimento não perecível no dia do evento.</p>
            <p class="mt-0 mb-0">Ao clicar em 'Comprar agora', eu concordo (i) com os termos de uso e regras do evento denominado Dreamfest 23 - Mega Festivalk Geek e estou ciente da Política de Privacidade e que sou maior de idade ou autorizado e acompanhado por um tutor legal.</p>

            <hr>
            <p class="mt-0 mb-0">L & M SOLUCOES EM EVENTOS E CONVENCOES DE CULTURA POP LTDA © 2023 - Todos os direitos reservados</p>
            <p class="mt-0 mb-0">21.812.142/0001-23</p>
        </div>
    </div>
</div>


<div class="fixed-bottom bg-white shadow-lg">

    <div class="d-grid gap-2 mb-0" style="padding:7px">
        <center><span style="padding-top: 5px; margin-bottom: -5px">Resumo da compra: <strong>R$ <?= number_format($total, 2, ',', '')  ?></strong></span> com <strong>R$ <?= number_format($valor_desconto, 2, ',', '')  ?></strong> de desconto</center>
        <input id="btn-salvar" type="submit" value="Comprar agora" class="btn btn-primary btn-lg mt-0">

    </div>
    </form>

</div>

<?php echo $this->endSection() ?>


<?php echo $this->section('scripts') ?>

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
                url: '<?php echo site_url('Checkout/finalizaradm'); ?>',
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
                                "<?php echo site_url("clientes/"); ?>";

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