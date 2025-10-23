<?php echo $this->extend('Layout/externo'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>

<!-- Bootstrap core CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.6.1/font/bootstrap-icons.css">



<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>
<?php
$juros = 0.034;

// Se vier valor_total via GET, usa ele como base do total (corrigindo para float)
if (isset($_GET['valor_total']) && !empty($_GET['valor_total'])) {
    // Limpa o valor e converte para float
    $valorGet = str_replace(',', '.', $_GET['valor_total']);
    $valorGet = preg_replace('/[^0-9.]/', '', $valorGet);
    $total = floatval($valorGet);
}

// Adiciona o valor do frete ao total
if (isset($_SESSION['valor_frete'])) {
    $total += floatval($_SESSION['valor_frete']);
}

$event_id = session()->get('event_id');
?>

<h5 class="mb-0 mt-3">Quase lá! Agora é só efetuar o pagamento e garantir seus ingressos!</h5>

<div class="row mt-4">
    <div class="col-lg-8">
        <div class="block">
            <div class="block-body">
                <div class="card shadow radius-10">
                    <div class="card-body">


                        <div class="d-flex align-items-center">
                            <div class="card shadow-none w-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">

                                        <div class="ms-auto fs-3 mb-0">
                                            <p class="mb-0" style="font-size: 10px;">Total a pagar:</p>
                                            <strong>R$ <?= number_format($total, 2, ',', '') ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <!-- Exibirá os retornos do backend -->
                        <div id="response">


                        </div>




                        <form method="POST" action="<?= site_url('Checkout/finalizarcartao/'. $event_id) ?>">

                            <?= csrf_field() ?>

                            <input type="hidden" name="valor_total" id="valor_total" value="<?= $total ?>" required>
                            <input type="hidden" name="frete" id="frete" value="<?= $_SESSION['frete'] ?>" required>
                            <input type="hidden" name="convite" value="<?= $_SESSION['convite'] ?>">
                            <input type="hidden" name="event_id" value="<?= $event_id ?>">
                            <div class="d-flex align-items-center mt-0">
                                <div class="card border shadow-none w-100">

                                    <div class="card-header py-3">
                                        <h6 class="mb-0">Seus dados</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-sm-8">
                                                <label for="nome" class="form-label">Nome completo</label>
                                                <input type="text" name="nome" id="nome" required class="form-control form-control-lg mb-2 shadow" style="font-size:medium; padding:13px" value="<?php if ($data_cli) echo esc($data_cli['nome']); ?>" <?php if (isset($data_cli['nome'])) : ?> <?php endif ?> required>
                                                <div class="invalid-feedback">
                                                    Este campo é obrigatório.
                                                </div>
                                            </div>

                                            <div class="col-sm-4">
                                                <?php if (isset($data_cli['cpf']) && !empty($data_cli['cpf'])) : ?>
                                                    <!-- CPF já cadastrado - campo oculto -->
                                                    <input type="hidden" name="cpf" value="<?= esc($data_cli['cpf']) ?>">
                                                    <label class="form-label">CPF</label>
                                                    <div class="alert alert-info mb-2" style="padding: 10px; font-size: small;">
                                                        <i class="bi bi-check-circle-fill"></i> CPF já cadastrado
                                                    </div>
                                                <?php else : ?>
                                                    <!-- CPF ainda não cadastrado - mostrar campo -->
                                                    <label for="cpf" class="form-label">CPF</label>
                                                    <input type="text" class="form-control form-control-lg mb-2 shadow" style="font-size:medium; padding:13px" name="cpf" id="cpf" value="" required>
                                                    <div class="invalid-feedback">
                                                        Este campo é obrigatório.
                                                    </div>
                                                <?php endif ?>
                                            </div>

                                            <div class="col-lg-8">
                                                <label for="email" class="form-label">E-mail</label>
                                                <input type="email" class="form-control form-control-lg mb-2 shadow" style="font-size:medium; padding:13px" name="email" id="email" value="<?php if ($data_cli) echo esc($data_cli['email']); ?>" <?php if (isset($data_cli['email'])) : ?> readonly <?php endif ?> required>
                                                <div class="invalid-feedback">
                                                    Este campo é obrigatório.
                                                </div>
                                            </div>

                                            <div class="col-lg-4">
                                                <?php if (isset($data_cli['telefone']) && !empty($data_cli['telefone'])) : ?>
                                                    <!-- Telefone já cadastrado - campo oculto -->
                                                    <input type="hidden" name="telefone" value="<?= esc($data_cli['telefone']) ?>">
                                                    <label class="form-label">Whatsapp</label>
                                                    <div class="alert alert-info mb-2" style="padding: 10px; font-size: small;">
                                                        <i class="bi bi-check-circle-fill"></i> Telefone já cadastrado
                                                    </div>
                                                <?php else : ?>
                                                    <!-- Telefone ainda não cadastrado - mostrar campo -->
                                                    <label for="telefone" class="form-label">Whatsapp</label>
                                                    <input type="text" class="form-control form-control-lg mb-2 shadow" style="font-size:medium; padding:13px" name="telefone" id="telefone" value="">
                                                <?php endif ?>
                                            </div>


                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="d-flex align-items-center mt-0">
                                <div class="card border shadow-none w-100">

                                    <div class="card-header py-3">
                                        <h6 class="mb-0">Dados do cartão</h6>
                                    </div>
                                    <div class="card-body">
                                        <?php if (empty($data_cli['credit_card_token'])) : ?>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="numero_cartao" class="form-label">Número do cartão</label>
                                                    <input type="text" class="form-control form-control-lg mb-2 shadow" style="font-size:medium; padding:13px" name="numero_cartao" id="numero_cartao" required>
                                                    <div class="invalid-feedback">
                                                        Este campo é obrigatório.
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="holderName" class="form-label">Nome impresso no cartão</label>
                                                    <input type="text" class="form-control form-control-lg mb-2 shadow" style="font-size:medium; padding:13px" name="holderName" id="holderName" required>
                                                    <div class="invalid-feedback">
                                                        Este campo é obrigatório.
                                                    </div>
                                                </div>


                                                <div class="col-md-3">
                                                    <label for="installmentCount" class="form-label">Parcelas</label>
                                                    <select class="form-select form-control-lg mb-2 shadow" style="font-size:medium; padding:13px" name="installmentCount" id="installmentCount" required>
                                                        <option></option>
                                                        <option value="1">1x de <?= number_format($total / 1, 2, ',', ' ') ?> </option>
                                                        <option value="2">2x de <?= number_format(($total + ($total * $juros * 2)) / 2, 2, ',', ' ') ?> </option>
                                                        <option value="3">3x de <?= number_format(($total + ($total * $juros * 3)) / 3, 2, ',', ' ') ?> </option>
                                                        <option value="4">4x de <?= number_format(($total + ($total * $juros * 4)) / 4, 2, ',', ' ') ?> </option>
                                                        <option value="5">5x de <?= number_format(($total + ($total * $juros * 5)) / 5, 2, ',', ' ') ?> </option>

                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Este campo é obrigatório.
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="mes_vencimento" class="form-label">Mês de vencimento</label>
                                                    <select class="form-select form-control-lg mb-2 shadow" style="font-size:medium; padding:13px" name="mes_vencimento" id="mes_vencimento" required>
                                                        <option></option>
                                                        <option>1</option>
                                                        <option>2</option>
                                                        <option>3</option>
                                                        <option>4</option>
                                                        <option>5</option>
                                                        <option>6</option>
                                                        <option>7</option>
                                                        <option>8</option>
                                                        <option>9</option>
                                                        <option>10</option>
                                                        <option>11</option>
                                                        <option>12</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Este campo é obrigatório.
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <label for="ano_vencimento" class="form-label">Ano de vencimento</label>
                                                    <select class="form-select form-control-lg mb-2 shadow" style="font-size:medium; padding:13px" name="ano_vencimento" id="ano_vencimento" required>
                                                        <option></option>
                                                        <option>2023</option>
                                                        <option>2024</option>
                                                        <option>2025</option>
                                                        <option>2026</option>
                                                        <option>2027</option>
                                                        <option>2028</option>
                                                        <option>2029</option>
                                                        <option>2030</option>
                                                        <option>2031</option>
                                                        <option>2032</option>
                                                        <option>2033</option>
                                                        <option>2034</option>
                                                        <option>2035</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Este campo é obrigatório.
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <label for="codigo_seguranca" class="form-label">CVV</label>
                                                    <input type="text" class="form-control form-control-lg mb-2 shadow" style="font-size:medium; padding:13px" name="codigo_seguranca" id="codigo_seguranca" required>
                                                    <div class="invalid-feedback">
                                                        Este campo é obrigatório.
                                                    </div>
                                                </div>

                                                <!-- Input do Payment Token que será gerado a partir dos dados do cartão inseridos -->
                                                <div class="col-12">
                                                    <input type="hidden" class="form-control" name="payment_token" id="payment_token" readonly>
                                                </div>

                                                <!-- Input da máscara do cartão de crédito inserido -->
                                                <div class="col-12">
                                                    <input type="hidden" class="form-control" name="mascara_cartao" id="mascara_cartao" readonly>
                                                </div>
                                            </div>
                                        <?php else : ?>
                                            <center>Sua compra será realizada com o cartão cadastrado</center>
                                        <?php endif ?>
                                    </div>
                                </div>
                            </div>




                            <div class="d-flex align-items-center mt-0">
                                <div class="card border shadow-none w-100">

                                    <div class="card-header py-3">
                                        <h6 class="mb-0">Endereço de cobrança</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-4">
                                                <label for="cep" class="form-label">CEP <a href="https://buscacepinter.correios.com.br/app/endereco/index.php" target="_blank">Não sabe o cep?</a></label>
                                                <input type="text" class="form-control form-control-lg mb-2 shadow cep" style="font-size:medium; padding:13px" name="cep" required>
                                                <div id="cep"></div>
                                            </div>
                                            <div class="col-lg-6">
                                                <label for="endereco" class="form-label">Endereco</label>
                                                <input type="text" class="form-control form-control-lg mb-2 shadow" style="font-size:medium; padding:13px" name="endereco" id="endereco" required readonly>
                                                <div class="invalid-feedback">
                                                    Este campo é obrigatório.
                                                </div>
                                            </div>

                                            <div class="col-lg-2">
                                                <label for="numero" class="form-label">Número</label>
                                                <input type="text" class="form-control form-control-lg mb-2 shadow" style="font-size:medium; padding:13px" name="numero" id="numero" required>
                                                <div class="invalid-feedback">
                                                    Este campo é obrigatório.
                                                </div>
                                            </div>

                                            <div class="col-lg-4">
                                                <label for="bairro" class="form-label">Bairro</label>
                                                <input type="text" class="form-control form-control-lg mb-2 shadow" style="font-size:medium; padding:13px" name="bairro" id="bairro" required readonly>
                                                <div class="invalid-feedback">
                                                    Este campo é obrigatório.
                                                </div>
                                            </div>



                                            <div class="col-lg-6">
                                                <label for="cidade" class="form-label">Cidade</label>
                                                <input type="text" class="form-control form-control-lg mb-2 shadow" style="font-size:medium; padding:13px" name="cidade" id="cidade" required readonly>
                                                <div class="invalid-feedback">
                                                    Este campo é obrigatório.
                                                </div>
                                            </div>

                                            <div class="col-lg-2">
                                                <label for="estado" class="form-label">Estado</label>
                                                <input type="text" class="form-control form-control-lg mb-2 shadow" style="font-size:medium; padding:13px" name="estado" id="estado" required readonly>
                                                <div class="invalid-feedback">
                                                    Este campo é obrigatório.
                                                </div>
                                            </div>


                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="d-flex align-items-center mt-0">
                                <div class="card border shadow-none w-100">

                                    <div class="card-header py-3">
                                        <h6 class="mb-0">Detalhes do pedido</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
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
                            </div>



                            <div id="areaBotoes" class="row g-3" style="padding:7px">
                                <center><span class="text-muted" style="padding-top: 5px; margin-bottom: -10px">Resumo da compra: <strong>R$ <?= number_format($total, 2, ',', '') ?> </strong></center>

                                <div class="d-grid gap-2 mb-0" style="padding:7px; margin-top: -3px">
                                    <input id="btn-salvar" type="submit" value="Comprar agora" class="btn btn-primary btn-lg mt-0" style="background-color: purple; border-color: purple; color: white;">

                                </div>
                            </div>

                        </form>





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

<div class="row" style="padding-left: 20px; padding-right: 20px; padding-bottom: 80px">
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


<script>
    $(document).ready(function() {
        $('[name=cep]').on('keyup', function() {


            var cep = $(this).val();

            if (cep.length === 9) {

                $.ajax({

                    type: 'GET',
                    url: '<?php echo site_url('checkout/consultacep'); ?>',
                    data: {
                        cep: cep
                    },
                    dataType: 'json',
                    beforeSend: function() {


                        $("#formulario_pagamento").LoadingOverlay("show");

                        $("#cep").html('');

                    },
                    success: function(response) {

                        $("#formulario_pagamento").LoadingOverlay("hide", true);


                        if (!response.erro) {

                            if (!response.endereco) {

                                $('[name=endereco]').prop('readonly', false);

                                $('[name=endereco]').focus();

                            }


                            if (!response.bairro) {

                                $('[name=bairro]').prop('readonly', false);

                            }


                            // Preenchemos os inputs com os valores do response
                            $('[name=endereco]').val(response.endereco);
                            $('[name=bairro]').val(response.bairro);
                            $('[name=cidade]').val(response.cidade);
                            $('[name=estado]').val(response.estado);

                        }

                        if (response.erro) {

                            // Exitem erros de validação

                            $("#cep").html(response.erro);
                        }

                    },
                    error: function() {

                        $("#formulario_pagamento").LoadingOverlay("hide", true);

                        alert(
                            'Não foi possível procesar a solicitação. Por favor entre em contato com o suporte técnico.'
                        );

                    }



                });



            }

        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://getbootstrap.com/docs/5.1/examples/checkout/form-validation.js"></script>
<!-- Meta Pixel InitiateCheckout Event -->
<?php if (isset($evento) && !empty($evento->meta_pixel_id)): ?>
<script>
// InitiateCheckout Event - quando o usuário inicia o processo de pagamento
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
    content_name: '<?= $evento->nome ?>',
    content_category: '<?= $evento->categoria ?? 'Evento' ?>',
    content_type: 'product',
    value: totalValue,
    currency: 'BRL',
    content_ids: cartItems,
    num_items: totalItems
});
</script>
<?php else: ?>
<script>
    fbq('track', 'InitiateCheckout');
</script>
<?php endif; ?>

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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action*="finalizarcartao"]');
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

<?php echo $this->endSection() ?>