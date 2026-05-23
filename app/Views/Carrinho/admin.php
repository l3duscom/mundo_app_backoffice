<?php echo $this->extend('Layout/externo'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>
<style>
    .ingresso-card {
        transition: box-shadow 0.2s ease;
    }
    .ingresso-card:hover {
        box-shadow: 0 0 10px rgba(108, 3, 143, 0.15);
    }
    .ingresso-qtd a {
        color: #6C038F;
    }
    .ingresso-qtd a:hover {
        color: #9C27B0;
    }
</style>
<?php echo $this->endSection() ?>


<?php echo $this->section('conteudo') ?>

<?php
if (!isset($_SESSION['carrinho']) || !is_array($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

if (isset($_GET['adicionar'])) {
    $idIngresso = (int) $_GET['adicionar'];
    $ingressoSelecionado = null;
    foreach ($ingressos as $ing) {
        if ((int) $ing->id === $idIngresso) {
            $ingressoSelecionado = $ing;
            break;
        }
    }
    if ($ingressoSelecionado) {
        if (isset($_SESSION['carrinho'][$idIngresso])) {
            $_SESSION['carrinho'][$idIngresso]['quantidade']++;
        } else {
            $preco = (float) $ingressoSelecionado->preco;
            $_SESSION['carrinho'][$idIngresso] = [
                'quantidade' => 1,
                'nome'       => $ingressoSelecionado->nome,
                'preco'      => $preco,
                'tipo'       => $ingressoSelecionado->tipo ?? 'individual',
                'taxa'       => 0,
                'unitario'   => $preco,
            ];
        }
    }
}

if (isset($_GET['excluir'])) {
    $idIngresso = (int) $_GET['excluir'];
    if (isset($_SESSION['carrinho'][$idIngresso])) {
        if ($_SESSION['carrinho'][$idIngresso]['quantidade'] > 1) {
            $_SESSION['carrinho'][$idIngresso]['quantidade']--;
        } else {
            unset($_SESSION['carrinho'][$idIngresso]);
        }
    }
}

$total_carrinho = 0;
foreach ($_SESSION['carrinho'] as $item) {
    $total_carrinho += $item['quantidade'] * $item['preco'];
}
$_SESSION['total'] = $total_carrinho;
?>

<div class="row mt-4">
    <div class="col-lg-8">
        <div class="card shadow radius-10">
            <div class="card-body">

                <div id="response"></div>

                <div class="row mb-2" style="padding: 15px;">
                    <div class="col-4" style="border-bottom: 5px solid #6C038F;">
                        <center><strong style="color: #6C038F;">MINHA SACOLA</strong></center>
                    </div>
                    <div class="col-4" style="border-bottom: 3px solid #A7A7A7;">
                        <center><strong style="color: #A7A7A7;">PAGAMENTO</strong></center>
                    </div>
                    <div class="col-4" style="border-bottom: 3px solid #A7A7A7;">
                        <center><strong style="color: #A7A7A7;">CONFIRMAÇÃO</strong></center>
                    </div>
                </div>

                <div class="mb-2 mt-3 font-24" style="color: #6C038F;">
                    Ingressos disponíveis &mdash; <?= esc($evento->nome) ?>
                </div>

                <?php if (empty($ingressos)) : ?>
                    <div class="alert alert-warning">
                        Nenhum ingresso disponível para este evento.
                    </div>
                <?php else : ?>
                    <?php foreach ($ingressos as $ingresso) : ?>
                        <?php $qtd = $_SESSION['carrinho'][$ingresso->id]['quantidade'] ?? 0; ?>
                        <div class="card border border-muted ingresso-card">
                            <div class="row align-items-center" style="padding: 15px;">
                                <div class="col-7">
                                    <span class="font-22" style="color: #6C038F;">
                                        <strong><?= esc($ingresso->nome) ?></strong>
                                    </span><br>
                                    <span class="text-muted"><?= esc($evento->nome) ?></span>
                                </div>
                                <div class="col-5 d-flex flex-row-reverse align-items-center ingresso-qtd">
                                    <strong style="font-size: 26px;">
                                        <a href="?excluir=<?= (int) $ingresso->id ?>">
                                            <i class="bx bx-minus-circle" style="padding-right: 10px;"></i>
                                        </a>
                                        <?= $qtd ?>
                                        <a href="?adicionar=<?= (int) $ingresso->id ?>">
                                            <i class="bx bx-plus-circle" style="padding-left: 10px;"></i>
                                        </a>
                                    </strong>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="mt-4" style="padding: 5px;">
                    <?php if (!empty($_SESSION['carrinho'])) : ?>
                        <table class="table mb-0 table-hover">
                            <thead>
                                <tr>
                                    <th width="60%">Ingresso</th>
                                    <th width="20%">Quantidade</th>
                                    <th width="20%">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($_SESSION['carrinho'] as $key => $value) : ?>
                                    <?php if ($value['quantidade'] != 0) : ?>
                                        <tr>
                                            <td><u><?= esc($value['nome']) ?></u></td>
                                            <td><?= (int) $value['quantidade'] ?></td>
                                            <td>R$ <?= number_format($value['quantidade'] * $value['unitario'], 2, ',', '') ?></td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <div class="alert alert-info text-center">
                            <i class="bx bx-error-circle"></i>
                            Sua sacola está vazia. Escolha um ingresso para continuar.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card shadow-none w-100 mt-3">
                    <div class="card-body shadow">
                        <div class="d-flex align-items-center">
                            <div>
                                <h4 class="mb-0">Ingressos</h4>
                                <p class="mb-0 text-muted" style="font-size: 11px;">
                                    <?= esc($evento->nome) ?>
                                </p>
                            </div>
                            <div class="ms-auto fs-3 mb-0">
                                <p class="mb-0" style="font-size: 10px;">Total a pagar:</p>
                                <strong>R$ <?= number_format($_SESSION['total'], 2, ',', '') ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="fixed-bottom bg-white shadow-lg">
                    <div class="d-grid gap-2 mb-0" style="padding: 7px;">
                        <center>
                            <span style="padding-top: 5px; margin-bottom: -5px;">
                                Resumo da compra:
                                <strong>R$ <?= number_format($_SESSION['total'], 2, ',', '') ?></strong>
                            </span>
                        </center>
                        <a href="<?= site_url('/checkout/confirmadm') ?>" class="btn btn-primary btn-lg mt-0">Continuar</a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow radius-10">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <h5 class="mb-0">Compra segura</h5>
                        <p class="mb-0">Ambiente seguro e autenticado</p>
                        <span class="text-muted" style="font-size: 10px;">Este site utiliza certificado SSL</span>
                    </div>
                    <div class="ms-auto fs-3">
                        <i class="bx bx-check-shield" style="font-size: 45px;"></i>
                    </div>
                </div>
                <div class="progress mt-3" style="height: 5px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 42%;"></div>
                </div>
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
        <?php echo $this->include('Clientes/_checkmail'); ?>
        <?php echo $this->include('Clientes/_viacep'); ?>
    });
</script>
<?php echo $this->endSection() ?>
