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
                                            <strong>R$ <?= number_format($transaction->installment_value / 100, 2, ',', ''); ?></strong>
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
        <center>
            <span style="padding-top: 5px; margin-bottom: -5px; font-size: 14px">Verificando pagamento automaticamente...</span><br>
            <span style="font-size: 12px; color: #666;">Próxima verificação em: </span><strong id="timer" style="font-size: 18px; color: #6C038F;"></strong>
        </center>
        <input id="btn-salvar" type="button" value="Verificar pagamento agora" class="btn btn-success btn-lg mt-0">

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
    value: <?= ($transaction->installment_value ?? 0) / 100 ?>,
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
        // Variáveis para controle do polling
        let pollingInterval;
        let countdownInterval;
        let nextCheckSeconds = 5; // Próxima verificação em 5 segundos
        let isPaymentReceived = false;
        let totalChecks = 0;
        let maxChecks = 60; // Parar após 60 verificações (5 minutos)

        // Função para atualizar o timer da próxima verificação
        function updateTimer() {
            $('#timer').text(nextCheckSeconds + 's');
            nextCheckSeconds--;

            if (nextCheckSeconds < 0) {
                nextCheckSeconds = 4; // Reiniciar para 5 segundos (4 porque decrementará imediatamente)
            }
        }

        // Função para verificar o status da transação
        function checkTransactionStatus() {
            if (isPaymentReceived) return; // Evitar verificações desnecessárias
            
            totalChecks++;
            
            // Parar após o máximo de verificações (5 minutos)
            if (totalChecks > maxChecks) {
                clearInterval(pollingInterval);
                clearInterval(countdownInterval);
                $('#timer').text('--');
                console.log('Tempo limite de verificação atingido');
                return;
            }

            // Reiniciar o timer para próxima verificação
            nextCheckSeconds = 5;

            $.ajax({
                type: 'GET',
                url: '<?php echo site_url('checkout/check-status/' . $charge_id); ?>',
                dataType: 'json',
                success: function(response) {
                    if (response.is_paid) {
                        isPaymentReceived = true;
                        clearInterval(pollingInterval);
                        clearInterval(countdownInterval);
                        
                        // Mostrar feedback visual de sucesso
                        $("#response").html('<div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> Pagamento confirmado! Redirecionando...</div>');
                        $("#btn-salvar").val('Pagamento Confirmado!').removeClass('btn-success').addClass('btn-primary').prop('disabled', true);
                        $('#timer').text('✓').css('color', '#28a745');
                        
                        // Redirecionar após 2 segundos
                        setTimeout(function() {
                            if (response.redirect_url) {
                                window.location.href = response.redirect_url;
                            }
                        }, 2000);
                    }
                    console.log(`Verificação ${totalChecks}/${maxChecks} - Status:`, response.status);
                },
                error: function(xhr, status, error) {
                    console.error('Erro ao verificar status:', error);
                    // Em caso de erro, mostrar mensagem amigável
                    if (totalChecks === 1) {
                        $("#response").html('<div class="alert alert-warning">Verificando conexão... Tentando novamente em alguns segundos.</div>');
                    }
                }
            });
        }

        // Mostrar feedback inicial
        $("#response").html('<div class="alert alert-info"><i class="bi bi-info-circle"></i> Aguardando confirmação do pagamento PIX...</div>');

        // Inicializar o countdown timer
        updateTimer(); // Mostrar o tempo inicial
        countdownInterval = setInterval(updateTimer, 1000);

        // Inicializar o polling para verificar o status (a cada 5 segundos)
        pollingInterval = setInterval(checkTransactionStatus, 5000);

        // Verificar imediatamente ao carregar a página (após 2 segundos)
        setTimeout(checkTransactionStatus, 2000);

        // Botão de verificar pagamento manual
        $("#btn-salvar").on('click', function() {
            if (!isPaymentReceived) {
                $(this).prop('disabled', true).val('Verificando...');
                
                // Fazer uma verificação manual imediata
                $.ajax({
                    type: 'GET',
                    url: '<?php echo site_url('checkout/check-status/' . $charge_id); ?>',
                    dataType: 'json',
                    success: function(response) {
                        if (response.is_paid) {
                            isPaymentReceived = true;
                            clearInterval(pollingInterval);
                            clearInterval(countdownInterval);
                            
                            $("#response").html('<div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> Pagamento confirmado! Redirecionando...</div>');
                            $("#btn-salvar").val('Pagamento Confirmado!').removeClass('btn-success').addClass('btn-primary');
                            $('#timer').text('✓').css('color', '#28a745');
                            
                            setTimeout(function() {
                                if (response.redirect_url) {
                                    window.location.href = response.redirect_url;
                                }
                            }, 2000);
                        } else {
                            $("#btn-salvar").prop('disabled', false).val('Verificar pagamento agora');
                            $("#response").html('<div class="alert alert-warning">Pagamento ainda não foi detectado. Aguarde ou tente novamente.</div>');
                        }
                    },
                    error: function() {
                        $("#btn-salvar").prop('disabled', false).val('Verificar pagamento agora');
                        $("#response").html('<div class="alert alert-danger">Erro ao verificar pagamento. Tente novamente.</div>');
                    }
                });
            }
        });

        // Cleanup ao sair da página
        $(window).on('beforeunload', function() {
            clearInterval(pollingInterval);
            clearInterval(countdownInterval);
        });
    });
</script>


<?php echo $this->endSection() ?>