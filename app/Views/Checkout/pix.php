<?php echo $this->extend('Layout/externo'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>


<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.css') ?>" />



<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>


<h5 class="mb-0 mt-3">Quase lá! Agora é só efetuar o pagamento e garantir seus ingressos! </h5>


<div class="row mt-4">
    <div class="col-lg-8">
        <div class="block">
            <div class="block-body">
                <div class="card shadow radius-10">
                    <div class="card-body">







                        <!-- Exibirá os retornos do backend -->
                        <div id="response">
                            <?php if (session()->getFlashdata('erro')): ?>
                                <div class="alert alert-danger">
                                    <?= session()->getFlashdata('erro') ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (session()->getFlashdata('sucesso')): ?>
                                <div class="alert alert-success">
                                    <?= session()->getFlashdata('sucesso') ?>
                                </div>
                            <?php endif; ?>
                        </div>


                        <?php echo form_open('Checkout/finalizarpix/' . $event_id, ['id' => 'form']) ?>

                        <?php 
                        $descontoPix = $total * 0.10;
                        $totalComDesconto = $total - $descontoPix;
                        ?>
                        <input type="hidden" name="valor_total" id="valor_total" value="<?= $totalComDesconto * 100 ?>" required>
                        <input type="hidden" name="frete" id="frete" value="<?= $_SESSION['frete'] ?>" required>
                        <input type="hidden" name="convite" value="<?= $_SESSION['convite'] ?>">


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



                                    <div class="card-header py-3">
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



                        <div class="d-grid gap-2 mb-0" style="padding:7px">
                            <?php 
                            $descontoPix = $total * 0.10;
                            $totalComDesconto = $total - $descontoPix;
                            ?>
                            <center>
                                <span style="padding-top: 5px; margin-bottom: -5px">
                                    Resumo da compra: <strong>R$ <?= number_format($total, 2, ',', '') ?></strong>
                                </span>
                                <div class="text-success" style="font-size: 12px;">
                                    <i class="bi bi-check-circle-fill"></i> Desconto PIX: <strong>R$ <?= number_format($descontoPix, 2, ',', '') ?></strong>
                                </div>
                                <span style="font-size: 14px; color: #6C038F;">
                                    <strong>Total com desconto: R$ <?= number_format($totalComDesconto, 2, ',', '') ?></strong>
                                </span>
                            </center>
                            <input id="btn-salvar" type="submit" value="Comprar agora" class="btn btn-lg mt-0" style="background-color: purple; border-color: purple; color: white;">

                        </div>
                        <?php echo form_close(); ?>




                        <center>
                            <span class="text-muted mb-5" style="font-size: 9px;">Processado por:</span><br>
                            <img class="mt-1" src="<?php echo site_url('recursos/front/images/asaas.png'); ?>" width="100px" height="auto">
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

<!-- Modal de Processamento -->
<div class="modal fade" id="modalProcessando" tabindex="-1" aria-labelledby="modalProcessandoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="text-align:center;">
      <div class="modal-body py-5">
        <div class="spinner-border text-primary mb-3" style="width: 4rem; height: 4rem;" role="status"></div>
        <h5 class="mb-3 mt-2">Processando pagamento...</h5>
        <p class="text-muted">Não feche ou atualize esta página.<br>Sua compra está sendo finalizada.</p>
      </div>
    </div>
  </div>
</div>

<script src="<?php echo site_url('recursos/vendor/loadingoverlay/loadingoverlay.min.js') ?>"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('form');
        const btn = document.getElementById('btn-salvar');
        if (form && btn) {
            form.addEventListener('submit', function(e) {
                btn.disabled = true;
                btn.value = "Processando...";
                btn.classList.add('disabled');
                // Mostra o modal
                var modal = new bootstrap.Modal(document.getElementById('modalProcessando'));
                modal.show();
            });
        }
    });
</script>

<!-- Meta Pixel Events -->
<?php if (isset($evento) && !empty($evento->meta_pixel_id)): ?>
<script>
// ViewContent Event - quando a página PIX é carregada
fbq('track', 'ViewContent', {
    content_name: '<?= $evento->nome ?> - PIX',
    content_category: '<?= $evento->categoria ?? 'Evento' ?>',
    content_type: 'product',
    content_ids: [<?= $evento->id ?>]
});

// InitiateCheckout Event - quando o usuário clica para finalizar o pagamento PIX
function trackInitiateCheckoutPix() {
    let totalValue = <?= $total ?? 0 ?>;
    let cartItems = [];
    let totalItems = 0;
    
    <?php if (isset($_SESSION['carrinho']) && is_array($_SESSION['carrinho'])): ?>
        <?php foreach ($_SESSION['carrinho'] as $key => $value): ?>
            <?php if ($value['quantidade'] > 0): ?>
                cartItems.push(<?= $key ?>);
                totalItems += <?= $value['quantidade'] ?>;
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
    
    fbq('track', 'InitiateCheckout', {
        content_name: '<?= $evento->nome ?> - PIX',
        content_category: '<?= $evento->categoria ?? 'Evento' ?>',
        content_type: 'product',
        value: totalValue,
        currency: 'BRL',
        content_ids: cartItems,
        num_items: totalItems
    });
}
</script>
<?php endif; ?>

<script src="<?php echo site_url('recursos/vendor/mask/jquery.mask.min.js') ?>"></script>
<script src="<?php echo site_url('recursos/vendor/mask/app.js') ?>"></script>

<script>
    $(document).ready(function() {

        // Track InitiateCheckout when user submits PIX payment
        $("#form").on('submit', function(e) {
            <?php if (isset($evento) && !empty($evento->meta_pixel_id)): ?>
            trackInitiateCheckoutPix();
            <?php endif; ?>
        });

    });
</script>





<?php echo $this->endSection() ?>