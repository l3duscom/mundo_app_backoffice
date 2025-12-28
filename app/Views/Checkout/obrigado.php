<?php echo $this->extend('Layout/externo'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>


<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.css') ?>" />



<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>
<?php

$a = 0;
$influencer = '';
if (!isset($_SESSION['total'])) {
    $_SESSION['total'] = 0;
};
if (isset($_SESSION['cupom'])) {
    $_SESSION['cupom'] = 0;
};
if (isset($_GET['cosplyer'])) {
    $cosplayer = 1;
} else {
    $cosplayer = 0;
}
if (isset($_GET['convite'])) {
    $_SESSION['convite'] = $_GET['convite'];
} else if (!empty($_SESSION['convite'])) {
    $_SESSION['convite'];
} else {
    $_SESSION['convite'] = 0;
};

if ($_SESSION['convite'] == 'x') {
    $influencer = 'o mago supremo';
} else if ($_SESSION['convite'] == 'ALKUQ4J') {
    $influencer = 'a Annya';
} else if ($_SESSION['convite'] == 'BSJFMRJ') {
    $influencer = 'a Dumadril';
} else if ($_SESSION['convite'] == '7DWYFOG') {
    $influencer = 'Yuri';
} else if ($_SESSION['convite'] == '6OKB9NC') {
    $influencer = 'a Val';
} else if ($_SESSION['convite'] == 'YN93AUN') {
    $influencer = 'a Duda';
} else if ($_SESSION['convite'] == 'WSDRKMI') {
    $influencer = 'a Viv Lee Cosplay';
} else if ($_SESSION['convite'] == '40WEBRK') {
    $influencer = 'a Vanessa';
} else if ($_SESSION['convite'] == '0ZOF49A') {
    $influencer = 'a Vithória Millan';
} else if ($_SESSION['convite'] == 'ELSWNKP') {
    $influencer = 'a Juniper Universe';
} else if ($_SESSION['convite'] == 'FJ3XYWZ') {
    $influencer = 'o Rafael Nunes';
} else {
    $influencer = 'o mago supremo';
}

?>

<?php
$event_id = session()->get('event_id');
?>

<!-- OVERLAY DE UPSELL -->
<?php if (!isset($upsells)) $upsells = []; ?>
<!-- DEBUG: <?php echo 'Upsells count: ' . count($upsells); ?> -->
<?php if (!empty($upsells)): ?>
<?php $upsell = $upsells[0]; // Pega o primeiro upsell disponível ?>
<div id="upsellOverlay" style="
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
">
    <div style="
        background: linear-gradient(135deg, #6c038f 0%, #9b4dca 100%);
        border-radius: 20px;
        max-width: 500px;
        width: 100%;
        padding: 30px;
        color: white;
        text-align: center;
        box-shadow: 0 10px 50px rgba(108,3,143,0.5);
        animation: slideUp 0.5s ease-out;
    ">
        <style>
            @keyframes slideUp {
                from { transform: translateY(50px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.05); }
            }
        </style>
        
        <div style="margin-bottom: 15px;">
            <i class="bx bx-star" style="font-size: 50px; color: #ffd700;"></i>
        </div>
        
        <h3 style="margin-bottom: 10px; font-weight: bold;">
            🎉 OFERTA ESPECIAL! 🎉
        </h3>
        
        <h4 style="margin-bottom: 20px;">
            <?php echo esc($upsell->titulo ?: 'Faça upgrade do seu ingresso!'); ?>
        </h4>
        
        <div style="background: rgba(255,255,255,0.2); border-radius: 10px; padding: 15px; margin-bottom: 20px;">
            <p style="margin-bottom: 10px; font-size: 14px;">
                <strong>Upgrade para:</strong><br>
                <span style="font-size: 18px;"><?php echo esc($upsell->ticket_destino_nome); ?></span>
            </p>
            
            <?php if (!empty($upsell->descricao)): ?>
            <p style="font-size: 12px; opacity: 0.9; margin-bottom: 0;">
                <?php echo esc($upsell->descricao); ?>
            </p>
            <?php endif; ?>
        </div>
        
        <div style="margin-bottom: 20px;">
            <p style="margin-bottom: 5px; font-size: 14px; opacity: 0.8;">
                Pague apenas a diferença:
            </p>
            <span style="font-size: 36px; font-weight: bold; color: #ffd700;">
                <?php echo $upsell->getValorFinalFormatado(); ?>
            </span>
            <?php if ($upsell->temDesconto()): ?>
            <br><span style="font-size: 12px; background: #ff6b6b; padding: 3px 10px; border-radius: 10px;">
                <?php echo $upsell->desconto_percentual; ?>% de desconto!
            </span>
            <?php endif; ?>
        </div>
        
        <a href="<?php echo site_url('checkout/upsell/' . $upsell->id); ?>" 
           class="btn btn-lg" 
           style="
               background: #ffd700;
               color: #333;
               font-weight: bold;
               padding: 15px 40px;
               border-radius: 30px;
               animation: pulse 2s infinite;
               display: inline-block;
               margin-bottom: 15px;
           ">
            <i class="bx bx-rocket me-2"></i>QUERO FAZER UPGRADE!
        </a>
        
        <br>
        
        <button onclick="document.getElementById('upsellOverlay').style.display='none'" 
                style="background: none; border: none; color: rgba(255,255,255,0.6); cursor: pointer; font-size: 14px; text-decoration: underline;">
            Não, obrigado. Continuar sem upgrade
        </button>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-12">
        <div class="block">
            <div class="block-body">
                <div class="card shadow radius-10">
                    <div class="card-body">




                      <!-- Exibirá os retornos do backend -->
                      <div id="response">


</div>


                        <div style="padding-left: 30px; padding-right: 30px;padding-top: 5px">
                            <center>
                                <i class="bx bx-check-circle" style="font-size: 80px; color:green"></i>
                                <p>Parabéns! Sua compra foi realizada com sucesso!, <strong>mas não feche essa página, você vai amar as novidades!</strong> <span style="font-size: 14px; color: red">Os emails de dado de acesso são enviados automaticamente, porém, notamos uma demora no envio para caixas do GMAIL. </span></p>
                                <hr>
                               
                               
                            </center>
                        </div>

                        <?php
                        if (isset($_GET['adicionar'])) {
                            $idProduto = (int)$_GET['adicionar'];
                            if (isset($items[$idProduto])) {
                                $produto = $items[$idProduto];
                                if (isset($_SESSION['carrinho'][$idProduto])) {
                                    $_SESSION['carrinho'][$idProduto]['quantidade']++;
                                } else {
                                    $_SESSION['carrinho'][$idProduto] = array(
                                        'quantidade' => 1,
                                        'nome' => $produto['nome'],
                                        'preco' => $produto['preco'],
                                        'tipo' => $produto['tipo'],
                                        'taxa' => 0,
                                        'unitario' => $produto['preco'],
                                        'ticket_id' => $produto['id']
                                    );
                                }
                            }
                        }

                        if (isset($_GET['excluir'])) {
                            $idProduto = (int)$_GET['excluir'];
                            if (isset($items[$idProduto])) {
                                $produto = $items[$idProduto];
                                if (isset($_SESSION['carrinho'][$idProduto])) {
                                    if ($_SESSION['carrinho'][$idProduto]['quantidade'] > 0) {
                                        $_SESSION['carrinho'][$idProduto]['quantidade']--;
                                    } else {
                                        unset($_SESSION['carrinho'][$idProduto]);
                                    }
                                }
                            }
                        }


                        ?>
                        <?php $total_carrinho = 0; ?>
                     

                        <?php foreach ($items as $key => $value) : ?>
                                <?php if ($value['tipo'] == 'produto') : ?>
                                    <div class="card border border-muted">
                                        <div class="form-check mt-3 mb-3">
                                            <div class="row">
                                                <div class="col-7">
                                                    <span style="color: purple; font-size: 10px">Finaliza em: <?= date('d/m/Y', strtotime($value['data_lote'])) ?> </span><br>
                                                    <strong style="color: #6C038F; font-size: 16px"><?= $value['nome'] ?> </strong><br>
                                                    <span class="text-muted" style="font-size: 10px"><strong><?= $value['tipo'] ?> - <?= $value['lote'] ?> lote</strong></span>


                                                </div>
                                                <div class="col-5 text-right">
                                                        <div class="col-12 mt-3 font-20"><strong style="font-size: 20px;"><a href="?excluir=<?= $key ?>"><i class="bi bi-dash-circle-fill" style=" padding-right: 4px;"></i></a> <?= (isset($_SESSION['carrinho'][$key]['quantidade'])) ? $_SESSION['carrinho'][$key]['quantidade'] : '0' ?> <a href="?adicionar=<?= $key ?>"><i class="bi bi-plus-circle-fill" style="padding-left: 4px"></i></a></strong></div>

                                                        <div class="col-12 text-right ol-4 mt-2"><span style=" font-size: 12px;">R$ </span><strong style="word-wrap: normal;font-size: 26px;"> <?= number_format($value['preco'], 2, ',', ''); ?> </strong>
                                                            <p class="text-muted" style="font-size: 10px">+ <?= (isset($_SESSION['carrinho'][$key]['taxa'])) ? 'R$ ' . number_format($_SESSION['carrinho'][$key]['taxa'], 2, ',', '') . ' Taxa' : ' Taxa' ?> <a href="?adicionar=<?= $key ?>"> </a></p>
                                                        </div>
                                                  
                                                </div>
                                                <div class="col-11 mt-3">
                                                    <strong style="font-size: 13px;" class="mt-5"><i class='bx bx-info-circle'></i> Descrição </strong>
                                                    <div class="text-muted mt-1" style="font-size: 11px;"><?= $value['descricao'] ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>


                            <div style="padding-left: 30px; padding-right: 30px; padding-bottom: 30px; padding-top: 5px">
                            <center>

                                <p>O Universo mágico do Dreamfest está te esperando! <strong>Sua compra foi realizada com sucesso e seus ingressos já estão disponíveis no link abaixo!</strong> O comprovante foi enviado para seu email e você deve favoritar esse email para não perder nenhum detalhe!</p>
                                <a href="<?= site_url('/console/dashboard/') ?>" class="btn btn-lg btn-primary mt-0 shadow">Ver meus ingressos</a>
                                <hr>
                                <p style="padding-top: 5px"><strong>Essa é sua primeira compra de ingressos para o Dream?</strong> Fique ligado! Você receberá <strong>automaticamente uma senha de acesso em seu email!</strong> Ao receber o email (confira no spam), favorite o mesmo para não perder nenhum detalhe da sua participação!</p>

                                <p>Use seu e-mail para acessar seu ingresso! </p>
                                <hr class="mt-5">
                                
                            </center>


                        </div>

                            <?php if (isset($_SESSION['carrinho'])) : ?>


                                <?php foreach ($_SESSION['carrinho'] as $key => $value) : ?>

                                    <?php

                                    $total_carrinho += $value['quantidade'] * $value['preco'];

                                    ?>

                                <?php endforeach; ?>

                            <?php endif; ?>

                            <?php $_SESSION['total'] = $total_carrinho ?>

                            </div>




                            <?php $total_carrinho = 0; ?>
                            <?php $total_taxa = 0; ?>


                            <div id="pagar" class="mt-2"></div>

                            <?php if ($_SESSION['total'] != 0) : ?>
                        <div class="card card-body">

                            <?php if (isset($_SESSION['carrinho'])) : ?>
                                <table class="table mb-0 table-hover">
                                    <thead>
                                        <tr>
                                            <th scope="col" width="40%">Ingresso</th>
                                            <th scope="col" width="20%" style="align-items:center">
                                                &nbsp;&nbsp;&nbsp;&nbsp;Quantidade
                                            </th>
                                            <th scope="col" width="40%">Valor </th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php foreach ($_SESSION['carrinho'] as $key => $value) : ?>
                                            <?php if ($value['quantidade'] != 0) : ?>
                                                <tr>
                                                    <td><u><?= $value['nome']; ?></u></td>
                                                    <td style="padding-left: 25px;"><a href="?excluir=<?= $key ?>"><i class="fadeIn animated bx bx-minus-circle" style="padding-right: 10px;"></i></a><?= $value['quantidade']; ?> <a href="?adicionar=<?= $key ?>"><i class="fadeIn animated bx bx-plus-circle" style="padding-left: 10px"></i></a></td>
                                                    <td>R$ <strong><?= number_format($value['quantidade'] * $value['unitario'], 2, ',', ''); ?></strong><span style="font-size: 12px;"><br> + R$ <?= number_format($value['quantidade'] * $value['taxa'], 2, ',', ''); ?> taxa de ingresso</span> </td>
                                                </tr>
                                            <?php endif; ?>
                                            <?php

                                            $total_carrinho += $value['quantidade'] * $value['preco'];
                                            $total_taxa += $value['quantidade'] * $value['taxa'];

                                            ?>

                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <center>
                                            <i class="fadeIn animated bx bx-error-circle"></i><br>Oooops, seu carrinho está vazio, escolha um ingresso e venha viver a magia no Dreamfest!
                                            <hr>
                                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Adicionar ingressos</button>
                                            <hr>
                                        </center>
                                        </hr>
                                    <?php endif; ?>

                                    <?php $_SESSION['total'] = $total_carrinho ?>
                                    </tbody>
                                </table>


                        </div>






                        <div class="fixed-bottom bg-white shadow-lg">
                            <div class="d-grid gap-2 mb-0" style="padding:10px">
                                <a class="btn btn-sm btn-light" href="#pagar">
                                    <!-- <i class="bi bi-arrow-down-circle-fill" style="font-size: 25px; color: purple;"></i>-->
                                    <strong><i class='bx bx-down-arrow-circle'></i> Ver detalhes da compra</strong>
                                </a>
                                <center><span style="padding-top: 5px; margin-bottom: -5px">Total a pagar: <strong>R$ <?= number_format($_SESSION['total'], 2, ',', '')  ?></strong></span> </center>

                                <a href="<?= site_url('/evento/entrega') ?>" class="btn btn-lg mt-0" style="padding:10px; background-color: purple; border-color: purple; color: white;"> Ir para o pagamento <i class='bx bx-right-arrow-circle'></i></a>

                            </div>
                        </div>
                        <?php echo form_close(); ?>


                    <?php endif ?>

                    <center>
                        <span class="text-muted mb-1" style="font-size: 9px;">Processado por:</span><br>
                                                    <img class="mt-1 mb-4" src="<?php echo site_url('recursos/front/images/asaas.png'); ?>" width="100px" height="auto">
                    </center>

                        

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




<?php echo $this->endSection() ?>


<?php echo $this->section('scripts') ?>

<!-- Meta Pixel Purchase Event -->
<?php if (isset($evento) && !empty($evento->meta_pixel_id)): ?>
<script>
// Purchase Event - quando a compra é finalizada
fbq('track', 'Purchase', {
    content_name: '<?= $evento->nome ?>',
    content_category: '<?= $evento->categoria ?? 'Evento' ?>',
    content_type: 'product',
    value: <?= $total ?? 0 ?>,
    currency: 'BRL',
    content_ids: [<?= $evento->id ?>],
    order_id: '<?= $order_id ?? '' ?>'
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

                            window.location.href =
                                "<?php echo site_url("checkout/qrcode/"); ?>" + response.id;

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