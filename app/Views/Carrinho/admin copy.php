<?php echo $this->extend('Layout/externo'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>


<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.css') ?>" />
<style>
    /* Style the tab */
    .tab {
        overflow: hidden;
        padding: 0;
        border-radius: 5px;
        border: 1px solid #CCCCCC;
        background-color: #ffffff;
        font-size: 20px;
    }

    /* Style the buttons that are used to open the tab content */
    .tab button {
        background-color: #ffffff;
        color: #6C038F;
        float: left;
        border: none;
        outline: none;
        cursor: pointer;
        padding: 14px 16px;
        transition: 0.3s;
    }

    /* Change background color of buttons on hover */
    .tab button:hover {
        color: #FFFFFF;
        background-color: #6C038F;
    }

    /* Create an active/current tablink class */
    .tab button.active {
        color: #FFFFFF;
        background-color: #6C038F;
    }

    /* Style the tab content */
    .tabcontent {
        display: none;
        padding: 6px 12px;


    }

    .tabcontent {
        animation: fadeEffect 1s;
        /* Fading effect takes 1 second */
    }

    /* Go from zero to full opacity */
    @keyframes fadeEffect {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @media screen and (max-width: 410px) {
        #divMobile {
            display: none;
        }
    }
    
    .evento-header {
        background: linear-gradient(135deg, #6C038F 0%, #9C27B0 100%);
        color: white;
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
    }
</style>

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
$nome_evento_banner = evento_nome();
if ($evento_selecionado && $nome_evento_banner && strlen($nome_evento_banner) > 3) : ?>
    <div class="evento-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-0">Evento Ativo: <strong><?= htmlspecialchars($nome_evento_banner) ?></strong></h4>
                <?php if ($evento_selecionado->data_inicio && $evento_selecionado->data_fim) : ?>
                    <p class="mb-0 opacity-75">
                        <?= date('d/m/Y', strtotime($evento_selecionado->data_inicio)) ?> - 
                        <?= date('d/m/Y', strtotime($evento_selecionado->data_fim)) ?>
                        <?php if ($evento_selecionado->local) : ?>
                            // <?= esc($evento_selecionado->local) ?>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
            <div>
                <a href="<?= site_url('/') ?>" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left"></i> Trocar Evento
                </a>
            </div>
        </div>
    </div>
<?php else : ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i>
        <strong>Atenção:</strong> Nenhum evento selecionado. 
        <a href="<?= site_url('/') ?>" class="alert-link">Clique aqui para selecionar um evento</a>.
    </div>
<?php endif; ?>

<h5 class="mb-0 mt-3">
    <?php 
    $nome_titulo = evento_nome();
    echo $nome_titulo && strlen($nome_titulo) > 3 ? htmlspecialchars($nome_titulo) : 'DREAMFEST 23 - MEGA FESTIVAL GEEK';
    ?>
</h5>
<strong style="color: #A7A7A7">
    <?php if ($evento_selecionado && $evento_selecionado->data_inicio && $evento_selecionado->data_fim) : ?>
        <?= date('d/m/Y', strtotime($evento_selecionado->data_inicio)) ?> - 
        <?= date('d/m/Y', strtotime($evento_selecionado->data_fim)) ?>
        <?= $evento_selecionado->local ? ' // ' . esc($evento_selecionado->local) : ' // PORTO ALEGRE' ?>
    <?php else : ?>
        21/06/2025 - 22/06/2025 // PORTO ALEGRE
    <?php endif; ?>
</strong>

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
                        // TODO: Esta lista de itens está hardcoded. No futuro, deveria ser dinamizada
                        // baseada no evento selecionado ($evento_selecionado) usando dados do banco.
                        // Exemplo: $ticketModel->recuperaIngressosPorEvento($event_id);
                        
                        $items = array(
                            ['categoria' => 'add', 'data' => '21/06/2025', 'lote' => '3', 'nome_exibicao' => 'CRIANÇA GRÁTIS', 'tipo_exibicao' => 'CORTESIA', 'tipo' => 'individual', 'modal' => 'comum', 'descricao' => 'CORTESIA - PROMOCIONAL', 'nome' => 'CORTESIA - CRIANÇA GRÁTIS', 'preco' => '0', 'taxa' => '0,05'],
                            ['categoria' => 'geral', 'data' => '21-22/06/2025', 'lote' => '3', 'nome_exibicao' => 'IMPRENSA', 'tipo_exibicao' => 'ACESSO', 'tipo' => 'acesso', 'modal' => 'vip-full', 'descricao' => 'CORTESIA', 'tipo' => 'acesso', 'modal' => 'comum', 'nome' => 'IMPRENSA', 'preco' => '0', 'taxa' => '0,05'],
                            ['categoria' => 'geral', 'data' => '21-22/06/2025', 'lote' => '3', 'nome_exibicao' => 'EMBAIXADOR', 'tipo_exibicao' => 'ACESSO', 'tipo' => 'acesso', 'modal' => 'vip-full', 'descricao' => 'CORTESIA', 'tipo' => 'acesso', 'modal' => 'comum', 'nome' => 'EMBAIXADOR', 'preco' => '0', 'taxa' => '0,05'],
                            ['categoria' => 'geral', 'data' => '21-22/06/2025', 'lote' => '3', 'nome_exibicao' => 'EMBAIXADOR - ACOMPANHANTE', 'tipo_exibicao' => 'ACESSO', 'tipo' => 'acesso', 'modal' => 'vip-full', 'descricao' => 'CORTESIA', 'tipo' => 'acesso', 'modal' => 'comum', 'nome' => 'EMBAIXADOR - ACOMPANHANTE', 'preco' => '0', 'taxa' => '0,05'],

                            ['categoria' => 'extra', 'data' => '21/06/2025', 'lote' => '0', 'nome_exibicao' => 'FAST PASS SÁBADO', 'tipo_exibicao' => 'ADICIONAL', 'tipo' => 'produto', 'modal' => 'vip-full', 'descricao' => 'FAST PASS - Acesso antecipado e fura fila', 'nome' => 'FAST PASS SÁBADO', 'preco' => '0', 'taxa' => '0,05'],
                            ['categoria' => 'extra', 'data' => '22/06/2025', 'lote' => '0', 'nome_exibicao' => 'FAST PASS DOMINGO', 'tipo_exibicao' => 'ADICIONAL', 'tipo' => 'produto', 'modal' => 'vip-full', 'descricao' => 'FAST PASS - Acesso antecipado e fura fila', 'nome' => 'FAST PASS DOMINGO', 'preco' => '0', 'taxa' => '0,05'],

                            ['categoria' => 'add', 'data' => '21/06/2025', 'lote' => '3', 'nome_exibicao' => 'PROJETO SOCIAL', 'tipo_exibicao' => 'CORTESIA', 'tipo' => 'social', 'modal' => 'comum', 'descricao' => 'CORTESIA - PROJETO SOCIAL', 'nome' => 'PROJETO SOCIAL CORTESIA', 'preco' => '0', 'taxa' => '0,05'],
                            ['categoria' => 'add', 'data' => '21/06/2025', 'lote' => '3', 'nome_exibicao' => 'CINEMARK', 'tipo_exibicao' => 'CORTESIA', 'tipo' => 'cinemark', 'modal' => 'comum', 'descricao' => 'CORTESIA', 'nome' => 'Ingresso CINEMARK by Dreamfest 23', 'preco' => '0', 'taxa' => '0,05'],
                            ['categoria' => 'vip', 'data' => '21-22/06/2025', 'lote' => '3', 'nome_exibicao' => 'CONVIDADO', 'tipo_exibicao' => 'ACESSO', 'tipo' => 'individual', 'modal' => 'vip-full', 'descricao' => 'CORTESIA', 'tipo' => 'individual', 'modal' => 'comum', 'nome' => 'CONVIDADO CORTESIA', 'preco' => '0', 'taxa' => '0,05'],
                            ['categoria' => 'vip', 'data' => '21-22/06/2025', 'lote' => '3', 'nome_exibicao' => 'ACOMPANHANTE', 'tipo_exibicao' => 'ACESSO', 'tipo' => 'individual', 'modal' => 'vip-full', 'descricao' => 'CORTESIA', 'tipo' => 'individual', 'modal' => 'comum', 'nome' => 'ACOMPANHANTE CORTESIA', 'preco' => '0', 'taxa' => '0,05'],
                            ['categoria' => 'geral', 'data' => '21-22/06/2025', 'lote' => '3', 'nome_exibicao' => 'EXPOSITOR', 'tipo_exibicao' => 'ACESSO', 'tipo' => 'combo', 'modal' => 'vip-full', 'descricao' => 'CORTESIA', 'tipo' => 'combo', 'modal' => 'comum', 'nome' => 'EXPOSITOR CORTESIA', 'preco' => '0', 'taxa' => '0,05'],

                            ['categoria' => 'sab', 'data' => '21/06/2025', 'lote' => '3', 'nome_exibicao' => 'Sábado', 'tipo_exibicao' => 'CORTESIA', 'tipo' => 'individual', 'modal' => 'comum', 'descricao' => 'CORTESIA', 'nome' => 'Ingresso Comum SÁBADO - CORTESIA', 'preco' => '0', 'taxa' => '0,05'],
                            ['categoria' => 'dom', 'data' => '22/06/2025', 'lote' => '3', 'nome_exibicao' => 'Domingo', 'tipo_exibicao' => 'CORTESIA', 'tipo' => 'individual', 'modal' => 'comum', 'descricao' => 'CORTESIA', 'nome' => 'Ingresso Comum DOMINGO - CORTESIA', 'preco' => '0', 'taxa' => '0,05'],
                            ['categoria' => 'duo', 'data' => '21-22/06/2025 ', 'lote' => '3', 'nome_exibicao' => 'Passaporte', 'tipo_exibicao' => 'CORTESIA', 'tipo' => 'combo', 'modal' => 'comum', 'descricao' => 'CORTESIA', 'nome' => 'Passaporte - CORTESIA', 'preco' => '0', 'taxa' => '0,05'],
                            ['categoria' => 'vip', 'data' => '21/06/2025 ', 'lote' => '3', 'nome_exibicao' => 'VIP FULL', 'tipo_exibicao' => 'CORTESIA', 'tipo' => 'combo', 'modal' => 'vip-full', 'descricao' => 'CORTESIA', 'nome' => 'Ingresso VIP FULL CORTESIA', 'preco' => '0', 'taxa' => '0,05'],
                            ['categoria' => 'vip', 'data' => '22/06/2025', 'lote' => '3', 'nome_exibicao' => 'VIP FULL', 'tipo_exibicao' => 'Sábado CORTESIA', 'tipo' => 'individual', 'modal' => 'vip-full', 'descricao' => 'CORTESIA', 'tipo' => 'individual', 'modal' => 'comum', 'nome' => 'Ingresso VIP FULL Sábado CORTESIA', 'preco' => '0', 'taxa' => '0,05'],
                            ['categoria' => 'vip', 'data' => '21-22/06/2025', 'lote' => '3', 'nome_exibicao' => 'VIP FULL', 'tipo_exibicao' => 'Domingo CORTESIA', 'tipo' => 'individual', 'modal' => 'vip-full', 'descricao' => 'CORTESIA', 'tipo' => 'individual', 'modal' => 'inteira', 'nome' => 'Ingresso VIP FULL Domingo CORTESIA', 'preco' => '0', 'taxa' => '0,05'],
                            ['categoria' => 'epc', 'data' => '21/06/2025 ', 'lote' => '3', 'nome_exibicao' => 'EPIC', 'tipo_exibicao' => 'CORTESIA', 'tipo' => 'combo', 'modal' => 'vip-full', 'descricao' => 'CORTESIA', 'nome' => 'Ingresso EPIC CORTESIA', 'preco' => '0', 'taxa' => '0,05'],
                            ['categoria' => 'epc', 'data' => '22/06/2025', 'lote' => '3', 'nome_exibicao' => 'EPIC', 'tipo_exibicao' => 'Sábado CORTESIA', 'tipo' => 'individual', 'modal' => 'vip-full', 'descricao' => 'CORTESIA', 'tipo' => 'individual', 'modal' => 'comum', 'nome' => 'Ingresso EPIC Sábado CORTESIA', 'preco' => '0', 'taxa' => '0,05'],
                            ['categoria' => 'epc', 'data' => '21-22/06/2025', 'lote' => '3', 'nome_exibicao' => 'EPIC', 'tipo_exibicao' => 'Domingo CORTESIA', 'tipo' => 'individual', 'modal' => 'vip-full', 'descricao' => 'CORTESIA', 'tipo' => 'individual', 'modal' => 'inteira', 'nome' => 'Ingresso EPIC Domingo CORTESIA', 'preco' => '0', 'taxa' => '0,05'],
                            ['categoria' => 'cos', 'data' => '21/06/2025', 'lote' => '3', 'nome_exibicao' => 'Cosplayer', 'tipo_exibicao' => 'Sábado CORTESIA', 'tipo' => 'individual', 'modal' => 'cosplayer', 'descricao' => 'CORTESIA', 'tipo' => 'individual', 'nome' => 'Cosplayer Paga Meia - SÁBADO CORTESIA', 'preco' => '0', 'taxa' => '0,05'],
                            ['categoria' => 'cos', 'data' => '22/06/2025', 'lote' => '3', 'nome_exibicao' => 'Cosplayer', 'tipo_exibicao' => 'Domingo CORTESIA', 'tipo' => 'individual', 'modal' => 'cosplayer', 'descricao' => 'CORTESIA', 'tipo' => 'individual', 'modal' => 'comum', 'nome' => 'Cosplayer Paga Meia - DOMINGO CORTESIA', 'preco' => '0', 'taxa' => '0,05'],
                            ['categoria' => 'cos', 'data' => '21-22/06/2025', 'lote' => '3', 'nome_exibicao' => 'Cosplayer', 'tipo_exibicao' => '2 Dias CORTESIA', 'tipo' => 'combo', 'modal' => 'cosplayer', 'descricao' => 'CORTESIA', 'tipo' => 'individual', 'modal' => 'inteira', 'nome' => 'Cosplayer Paga Meia - PASSAPORTE CORTESIA', 'preco' => '0', 'taxa' => '0,05'],
                            ['categoria' => 'aft', 'data' => '21/06/2025', 'lote' => '0', 'nome_exibicao' => 'AFTER - A Festa', 'tipo_exibicao' => 'CORTESIA', 'tipo' => 'individual', 'modal' => 'comum', 'descricao' => 'CORTESIA', 'nome' => 'AFTER - CORTESIA', 'preco' => '0', 'taxa' => '0,05'],


                        );


                        ?>

                        <?php
                        if (isset($_GET['adicionar'])) {
                            $idProduto = (int) $_GET['adicionar'];
                            if (isset($items[$idProduto])) {
                                if (isset($_SESSION['carrinho'][$idProduto])) {
                                    $_SESSION['carrinho'][$idProduto]['quantidade']++;
                                } else {
                                    $_SESSION['carrinho'][$idProduto] = array('quantidade' => 1, 'nome' => $items[$idProduto]['nome'], 'preco' => $items[$idProduto]['preco'] + ($items[$idProduto]['preco'] * 0.05), 'tipo' => $items[$idProduto]['tipo'], 'taxa' => $items[$idProduto]['preco'] * 0.05, 'unitario' => $items[$idProduto]['preco']);
                                }
                            }
                        }

                        if (isset($_GET['excluir'])) {
                            $idProduto = (int) $_GET['excluir'];
                            if (isset($items[$idProduto])) {
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

                        <div class="row mb-2" style="padding: 15px;">
                            <div class="col-4" style="border-bottom-width: 5px; border-bottom-style: solid; border-bottom-color: #6C038F">
                                <center><strong style="color: #6C038F; word-wrap: normal;">MINHA SACOLA</strong></center>
                            </div>


                            <div class="col-4" style="border-bottom-width: 3px; border-bottom-style: solid; border-bottom-color: #A7A7A7">
                                <center><strong style="color: #A7A7A7; word-wrap: normal;">PAGAMENTO</strong></center>
                            </div>
                            <div class="col-4" style="border-bottom-width: 3px; border-bottom-style: solid; border-bottom-color: #A7A7A7">
                                <center><strong style="color: #A7A7A7; word-wrap: normal;">CONFIRMAÇÃO</strong></center>
                            </div>
                        </div>



                        <div class="mb-2 mt-3 font-24" style="color: #6C038F;">Selecione a categoria </div>
                        <div class="row" style="padding: 10px;">





                            <p>Você receberá uma pulseira colecionável que deverá ser apresentada na entrada e na saída do festival e sempre que for requisitada. Caso opte pela pulsiera de tecido colecionável RFID, você terá direito à entrar e sair do evento sempre que quiser!</p>
                            <p><a href="#" data-bs-toggle="modal" data-bs-target="#comumModal"><strong class="mt-5" style="color: #530c6b; margin-right:10px"><i class="fa-solid fa-bolt"></i> Conheça todos os benefícios desse ingresso!</strong></a> </p>

                            <hr>
                            <div class="mb-2 mt-3 font-24" style="color: #6C038F;">Selecione seu ingresso </div>
                            <p>Apenas a promoção de maior desconto será aplicada ao final do carrinho.</p>


                            <?php foreach ($items as $key => $value) : ?>
                                <div class="card border border-muted">
                                    <div class="form-check mt-3 mb-3">
                                        <div class="row ">
                                            <div class="col-5 mb-2">
                                                <span style="color: #6C038F"><?= $value['data'] ?></span><br>
                                                <span class="font-22" style="color: #6C038F"><strong><?= $value['nome_exibicao'] ?></strong> <?= $value['tipo_exibicao'] ?></span><br>
                                                <span class="text-muted"><strong><?= $value['lote'] ?>º lote</strong></span>
                                            </div>
                                            <div class="col-6 mt-4 font-22 d-flex flex-row-reverse"><strong style="font-size: 26px;"><a href="?excluir=<?= $key ?>"><i class="fadeIn animated bx bx-minus-circle" style="padding-right: 10px;"></i></a> <?= (isset($_SESSION['carrinho'][$key]['quantidade'])) ? $_SESSION['carrinho'][$key]['quantidade'] : '0' ?> <a href="?adicionar=<?= $key ?>"><i class="fadeIn animated bx bx-plus-circle" style="padding-left: 10px"></i></a></strong></div>
                                            <div class="col-6 text-muted"><?= $value['descricao'] ?></div>
                                            <div class="col-4 font-20" style="padding-left: 30px; "><strong style="word-wrap: normal;">R$ <?= number_format($value['preco'], 2, ',', ''); ?> </strong>
                                                <p class="text-muted" style="font-size: 12px">Taxa <?= (isset($_SESSION['carrinho'][$key]['taxa'])) ? 'R$ ' . number_format($_SESSION['carrinho'][$key]['taxa'], 2, ',', '') : '5%' ?> <a href="?adicionar=<?= $key ?>"> </a></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>


















                        </div>






                        <!--
                        <div class=" mt-1"></div>
                        <div class="d-flex align-items-center">
                            <div class="card shadow-none w-100">
                                <div class="card-body shadow">
                                    <div class="">
                                        <h4 class="mb-0">Como você quer receber seus ingressos? </h4>
                                    </div>
                                    <div class="row" style="padding: 10px;">
                                        <div class="col-lg-6">
                                            s
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    -->





                        <div class="" style="padding: 5px;">
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
                                                    <td> <?= $value['quantidade']; ?> </td>
                                                    <td>R$ <?= number_format($value['quantidade'] * $value['unitario'], 2, ',', ''); ?></td>
                                                    <td>R$ <?= number_format($value['quantidade'] * $value['taxa'], 2, ',', ''); ?></td>
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




                        <div class="d-flex align-items-center">
                            <div class="card shadow-none w-100">
                                <div class="card-body  shadow">
                                    <div class="d-flex align-items-center ">
                                        <div class="">
                                            <h4 class="mb-0">Ingressos </h4>
                                            <p class="mb-0 text-muted" style="font-size: 11px">Dreamfest 23 - Mega Festival Geek</p>
                                            <p class="mb-0 text-muted" style="font-size: 11px">10 e 11 de Junho de 2023 - <strong></strong></p>
                                        </div>
                                        <div class="ms-auto fs-3 mb-0 text-muted">

                                        </div>

                                        <div class="ms-auto fs-3 mb-0">
                                            <p class="mb-0" style="font-size: 10px;">Total a pagar:</p>
                                            <strong>R$ <?= number_format($_SESSION['total'], 2, ',', '') ?></strong>
                                            <p style="font-size: 12px;" class="text-muted">Taxa: R$ <?= number_format($total_taxa, 2, ',', '') ?></p>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="fixed-bottom bg-white shadow-lg">

                            <div class="d-grid gap-2 mb-0" style="padding:7px">
                                <center><span style="padding-top: 5px; margin-bottom: -5px">Resumo da compra: <strong>R$ <?= number_format($_SESSION['total'], 2, ',', '')  ?></strong></span> </center>
                                <a href="<?= site_url('/checkout/confirmadm') ?>" class="btn btn-primary btn-lg mt-0">Continuar</a>

                            </div>
                            <?php echo form_close(); ?>

                        </div>
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
                                <h5 class="mb-0">Compra segura
                                    </h4>
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

<!--MODAL-->
<div class="modal fade" id="comumModal" tabindex="-1" aria-labelledby="comumModallLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="comumModalLabel">Se liga nas vantagens!</h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

            </div>

            <div class="modal-body">

                <div class="alert border-0 bg-light-dark alert-dismissible fade show py-2">
                    <div class="d-flex align-items-center">
                        <div class="fs-3 text-dark"><i class="bi bi-bell-fill"></i>
                        </div>
                        <div class="ms-3">
                            <div class="text-dark mt-2"><strong>Quem tem direito à meia solidária?
                                    <br>
                                </strong> Qualquer pessoa que leve 1kg de alimento não perecível no dia do evento, sendo 1kg por ingresso adquirido.
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>


                <hr>
                <div class="card">
                    <div class="card-body">
                        <table class="table mb-0 table-hover">
                            <thead>
                                <tr>
                                    <th scope="col" width="80%"></th>
                                    <th scope="col" width="20%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th scope="row">Acesso ao evento dia de evento escolhido</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>

                                <tr>
                                    <th scope="row">Entrar e sair do evento quando quiser!</th>
                                    <td style="color:#ffcc00; font-size: 22px" title="Pago separadamente"><i class="fadeIn animated bx bx-dollar-circle"></i></td>
                                </tr>

                                <tr>
                                    <th scope="row">Pulseira RFID Colecionável</th>
                                    <td style="color:#ffcc00; font-size: 22px" title="Pago separadamente"><i class="fadeIn animated bx bx-dollar-circle"></i></td>
                                </tr>

                                <tr>
                                    <th scope="row">Credencial Colecionável</th>
                                    <td style="color:#ffcc00; font-size: 22px" title="Pago separadamente"><i class="fadeIn animated bx bx-dollar-circle"></i></td>
                                </tr>

                                <tr>
                                    <th scope="row" style="color:grey">Descontos de até 30% em lojinhas durante o evento!</th>
                                    <td style="color:grey; font-size: 22px"><i class="fadeIn animated bx bx-x"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Fliperama Liberado</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Arena de Games</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Arena KPOP</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Food Park</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Palcos e painéis</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Espaços temáticos</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Camarins</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Guarda Volumes</th>
                                    <td style="color:#ffcc00; font-size: 22px" title="Pago separadamente"><i class="fadeIn animated bx bx-dollar-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Meet & Greet</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">1 foto grátis no estúdio fotográfico</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Entendi!</button>

            </div>
        </div>
    </div>
</div>



<!--MODAL-->
<div class="modal fade" id="clubeModal" tabindex="-1" aria-labelledby="clubeModallLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="clubeModal">Se liga nas vantagens!</h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

            </div>

            <div class="modal-body">

                <div class="alert border-0 bg-light-dark alert-dismissible fade show py-2">
                    <div class="d-flex align-items-center">
                        <div class="fs-3 text-dark"><i class="bi bi-bell-fill"></i>
                        </div>
                        <div class="ms-3">
                            <div class="text-dark mt-2">Garanta agora mesmo a sua vaga e faça parte do clube de vantagens geek exclusivo do Dreamfest!
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>

                <img src="<?php echo site_url('recursos/front/images/ingressos/clube-card.png'); ?>" alt="" width="100%" height="auto">



                <hr>
                <div class="card">
                    <div class="card-body">
                        <table class="table mb-0 table-hover">
                            <thead>
                                <tr>
                                    <th scope="col" width="80%"></th>
                                    <th scope="col" width="20%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th scope="row">Acesso ao evento (sábado) das 12 às 19</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Acesso ao evento (domingo) das 11 às 20</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>

                                <tr>
                                    <th scope="row">Entrar e sair do evento quando quiser!</th>
                                    <td style="color:#ffcc00; font-size: 22px" title="Pago separadamente"><i class="fadeIn animated bx bx-dollar-circle"></i></td>
                                </tr>

                                <tr>
                                    <th scope="row">Pulseira RFID Colecionável</th>
                                    <td style="color:#ffcc00; font-size: 22px" title="Pago separadamente"><i class="fadeIn animated bx bx-dollar-circle"></i></td>
                                </tr>

                                <tr>
                                    <th scope="row">Credencial Colecionável</th>
                                    <td style="color:#ffcc00; font-size: 22px" title="Pago separadamente"><i class="fadeIn animated bx bx-dollar-circle"></i></td>
                                </tr>

                                <tr>
                                    <th scope="row">Acesso GRÁTIS em todos os eventos da produtora, dentre eles o Dreamfest, Dreamfest Go, AnimeDream, Kdream e outros!</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Acesso GRÁTIS e/ou com desconto em eventos parceiros</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Você poderá dar dicas e participar da escolha dos artistas e temáticas dos eventos!</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Descontos em lojas parceiras, dentro e fora dos eventos, que variam de 10 a 50%.</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Descontos em cursos online de diversos tipos, tais como desenho, línguas, música…</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Acesso exclusivo em fila separada nos eventos da produtora!</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Espaço exclusivo com acesso privilegiado nos palcos do evento, utilizando a Hotzone</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Filas preferenciais nas praças de alimentação dos eventos</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Descontos, Cashback e isenções em produtos da linha Dreamfest</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Cartão exclusivo de sócio</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Sorteios exclusivos</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Fliperama Liberado</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Arena de Games</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Arena KPOP</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Food Park</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Palcos e painéis</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Espaços temáticos</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Camarins</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Guarda Volumes</th>
                                    <td style="color:#ffcc00; font-size: 22px" title="Pago separadamente"><i class="fadeIn animated bx bx-dollar-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Meet & Greet</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">1 foto grátis no estúdio fotográfico</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Entendi!</button>

            </div>
        </div>
    </div>
</div>





<!--MODAL-->
<div class="modal fade" id="cosplayerModal" tabindex="-1" aria-labelledby="cosplayerModallLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cosplayerModalLabel">Se liga nas vantagens!</h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

            </div>

            <div class="modal-body">

                <div class="alert border-0 bg-light-dark alert-dismissible fade show py-2">
                    <div class="d-flex align-items-center">
                        <div class="fs-3 text-dark"><i class="bi bi-bell-fill"></i>
                        </div>
                        <div class="ms-3">
                            <div class="text-dark mt-2"><strong>EXCLUSIVO!
                                    <br>
                                </strong> Promoção válida apenas para cosplayer cadastrados e aprovados na promoção "Cosplayer Paga Meia", disponível em <a href="https://dreamfest.com.br/promocoes" target="_blank">dreamfest.com.br/promocoes</a>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>


                <hr>
                <div class="card">
                    <div class="card-body">
                        <table class="table mb-0 table-hover">
                            <thead>
                                <tr>
                                    <th scope="col" width="80%"></th>
                                    <th scope="col" width="20%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th scope="row">Acesso ao evento dia de evento escolhido</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>

                                <tr>
                                    <th scope="row">Entrar e sair do evento quando quiser!</th>
                                    <td style="color:#ffcc00; font-size: 22px" title="Pago separadamente"><i class="fadeIn animated bx bx-dollar-circle"></i></td>
                                </tr>

                                <tr>
                                    <th scope="row">Pulseira RFID Colecionável</th>
                                    <td style="color:#ffcc00; font-size: 22px" title="Pago separadamente"><i class="fadeIn animated bx bx-dollar-circle"></i></td>
                                </tr>

                                <tr>
                                    <th scope="row">Credencial Colecionável</th>
                                    <td style="color:#ffcc00; font-size: 22px" title="Pago separadamente"><i class="fadeIn animated bx bx-dollar-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Fila preferencial</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Prioridade no Camarim (competidores)</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Pulseira personalizada</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>

                                <tr>
                                    <th scope="row" style="color:grey">Descontos de até 30% em lojinhas durante o evento!</th>
                                    <td style="color:grey; font-size: 22px"><i class="fadeIn animated bx bx-x"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Fliperama Liberado</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Arena de Games</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Arena KPOP</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Food Park</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Palcos e painéis</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Espaços temáticos</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Camarins</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Guarda Volumes</th>
                                    <td style="color:#ffcc00; font-size: 22px" title="Pago separadamente"><i class="fadeIn animated bx bx-dollar-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Meet & Greet</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">1 foto grátis no estúdio fotográfico</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Entendi!</button>

            </div>
        </div>
    </div>
</div>


<!--MODAL-->
<div class="modal fade" id="inteiraModal" tabindex="-1" aria-labelledby="inteiraModallLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="inteiraModalLabel">Se liga nas vantagens!</h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

            </div>

            <div class="modal-body">
                <div class="alert border-0 bg-light-danger alert-dismissible fade show py-2">
                    <div class="d-flex align-items-center">
                        <div class="fs-3 text-dark"><i class="bi bi-bell-fill"></i>
                        </div>
                        <div class="ms-3">
                            <div class="text-dark mt-2"><strong>Sabia que <sctrong>VOCÊ</sctrong> Pode pagar meia-entrada?
                                    <br>
                                </strong> SIM! Criamos a meia-entrada solidária, um projeto social onde qualquer pessoa que leve 1kg de alimento não perecível no dia do evento recebe o mesmo desconto da meia-entrada!.
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>



                <hr>
                <div class="card">
                    <div class="card-body">
                        <table class="table mb-0 table-hover">
                            <thead>
                                <tr>
                                    <th scope="col" width="80%"></th>
                                    <th scope="col" width="20%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th scope="row">Acesso ao evento dia de evento escolhido</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>

                                <tr>
                                    <th scope="row">Entrar e sair do evento quando quiser!</th>
                                    <td style="color:#ffcc00; font-size: 22px" title="Pago separadamente"><i class="fadeIn animated bx bx-dollar-circle"></i></td>
                                </tr>

                                <tr>
                                    <th scope="row">Pulseira RFID Colecionável</th>
                                    <td style="color:#ffcc00; font-size: 22px" title="Pago separadamente"><i class="fadeIn animated bx bx-dollar-circle"></i></td>
                                </tr>

                                <tr>
                                    <th scope="row">Credencial Colecionável</th>
                                    <td style="color:#ffcc00; font-size: 22px" title="Pago separadamente"><i class="fadeIn animated bx bx-dollar-circle"></i></td>
                                </tr>

                                <tr>
                                    <th scope="row" style="color:grey">Descontos de até 30% em lojinhas durante o evento!</th>
                                    <td style="color:grey; font-size: 22px"><i class="fadeIn animated bx bx-x"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Fliperama Liberado</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Arena de Games</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Arena KPOP</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Food Park</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Palcos e painéis</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Espaços temáticos</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Camarins</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Guarda Volumes</th>
                                    <td style="color:#ffcc00; font-size: 22px" title="Pago separadamente"><i class="fadeIn animated bx bx-dollar-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Meet & Greet</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">1 foto grátis no estúdio fotográfico</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Entendi!</button>

            </div>
        </div>
    </div>
</div>

<!--MODAL-->
<div class="modal fade" id="vip-fullModal" tabindex="-1" aria-labelledby="vip-fullModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="vip-fullModalLabel">Se liga nas vantagens de ser VIP!</h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

            </div>

            <div class="modal-body">

                <div class="alert border-0 bg-light-dark alert-dismissible fade show py-2">
                    <div class="d-flex align-items-center">
                        <div class="fs-3 text-dark"><i class="bi bi-bell-fill"></i>
                        </div>
                        <div class="ms-3">
                            <div class="text-dark mt-2">
                                </strong> O KIT VIP é composto por 1 ingresso já com desconto de meia entrada + KIT de benefícios VIP, conforme sua categoria.
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>


                <hr>
                <div class="card">
                    <div class="card-body">
                        <table class="table mb-0 table-hover">
                            <thead>
                                <tr>
                                    <th scope="col" width="80%"></th>
                                    <th scope="col" width="20%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <small class="text-muted">**** Mediante disponibilidade do convidado. Não inclui convidado internacional.</small>
                                <tr>
                                    <th scope="row">Fila preferencial (Entrada e Food Park)</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">1 INGRESSO CINEMARK CORTESIA</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>

                                <tr>
                                    <th scope="row">Entrar e sair do evento quando quiser!</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>

                                <tr>
                                    <th scope="row">Pulseira RFID Colecionável</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Credencial Colecionável</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>

                                <tr>
                                    <th scope="row">Meet & Greet Preferencial</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Acesso ao evento (sábado) das 11 às 19</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Acesso ao evento (domingo) das 10 às 20</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Descontos de até 30% de desconto em lojinhas durante o evento!</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">****Sala VIP - Acesso à sala climatizada, reservada e com a presença de convidados*</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">**Espaço diversão - Fliperamas e animes/séries liberados na sala VIP</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">HOTZONE - Espaço reservado nas primeiras fileiras do palco principal durante TODO o evento!</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">**Alimentação - Snacks, Salgados, Bebidas quentes e geladas e Guloseimas sendo servidas durante o dia na sala VIP</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Área de descanso - Espaço com puffs e sofás na sala VIP</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Fliperama Liberado</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Arena de Games</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Arena KPOP</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Food Park</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Palcos e painéis</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Espaços temáticos</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Camarins</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Guarda Volumes</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>

                                <tr>
                                    <th scope="row">1 foto grátis no estúdio fotográfico</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Entendi!</button>

            </div>
        </div>
    </div>
</div>


<!--MODAL-->
<div class="modal fade" id="vip-fanModal" tabindex="-1" aria-labelledby="vip-fanModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="vip-fanModalLabel">Se liga nas vantagens de ser VIP!</h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

            </div>

            <div class="modal-body">

                <div class="alert border-0 bg-light-dark alert-dismissible fade show py-2">
                    <div class="d-flex align-items-center">
                        <div class="fs-3 text-dark"><i class="bi bi-bell-fill"></i>
                        </div>
                        <div class="ms-3">
                            <div class="text-dark mt-2">
                                </strong> O KIT VIP é composto por 1 ingresso já com desconto de meia entrada + KIT de benefícios VIP, conforme sua categoria.
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>


                <hr>
                <div class="card">
                    <div class="card-body">
                        <table class="table mb-0 table-hover">
                            <thead>
                                <tr>
                                    <th scope="col" width="80%"></th>
                                    <th scope="col" width="20%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th scope="row">Fila preferencial (Entrada e Food Park)</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Acesso ao evento (sábado) das 11 às 19</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Acesso ao evento (domingo) das 10 às 20</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>

                                <tr>
                                    <th scope="row">Entrar e sair do evento quando quiser!</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>

                                <tr>
                                    <th scope="row">Pulseira RFID Colecionável</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Credencial Colecionável</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Meet & Greet Preferencial ²</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Descontos de até 30% de desconto em lojinhas durante o evento!</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">HOTZONE - Espaço reservado nas primeiras fileiras do palco principal durante TODO o evento!</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>

                                <tr>
                                    <th scope="row">Fliperama Liberado</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Arena de Games</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Arena KPOP</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Food Park</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Palcos e painéis</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Espaços temáticos</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Camarins</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Guarda Volumes</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>

                                <tr>
                                    <th scope="row">1 foto grátis no estúdio fotográfico</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Entendi!</button>

            </div>
        </div>
    </div>
</div>

<!--MODAL-->
<div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">


            <div class="modal-body">


                <div class=" mt-1"></div>
                <div class="d-flex align-items-center">
                    <div class="card shadow-none w-100">
                        <div class="card-body shadow">
                            <div class="d-flex align-items-center ">
                                <div class="">
                                    <h4 class="mb-0">Sacola mágica! </h4>
                                    <p class="mb-0 text-muted" style="font-size: 14px">Aqui você encontra o resumo dos ingressos que <strong style="color:blueviolet"><?= $influencer ?></strong> te ajudou a escolher!</p>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>


                <div class="d-flex align-items-center">
                    <div class="card shadow-none w-100">
                        <div class="card-body  shadow">
                            <div class="d-flex align-items-center ">
                                <div class="">
                                    <h4 class="mb-0">Ingressos </h4>
                                    <p class="mb-0 text-muted" style="font-size: 11px">Dreamfest 23 - Mega Festival Geek</p>
                                    <p class="mb-0 text-muted" style="font-size: 11px">10 e 11 de Junho de 2023 - <strong>Lote 1</strong></p>
                                </div>
                                <div class="ms-auto fs-3 mb-0 text-muted">

                                </div>

                                <div class="ms-auto fs-3 mb-0">
                                    <p class="mb-0" style="font-size: 10px;">Total a pagar:</p>
                                    <strong>R$ <?= number_format($_SESSION['total'], 2, ',', '') ?></strong>
                                    <strong>R$ <?= number_format($total_taxa, 2, ',', '') ?></strong>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>






            </div>
            <div class="modal-footer">

                <a href="" class="w-100 btn btn-outline-dark btn-block" data-bs-dismiss="modal"><i class="fa-solid fa-rotate-left"></i>Continuar comprando</a>

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

    //$(document).ready(function() {
    //  $('#cartModal').modal('show');
    //})
</script>
<script>
    function openCategoria(evt, categoria) {
        // Declare all variables
        var i, tabcontent, tablinks;

        // Get all elements with class="tabcontent" and hide them
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }

        // Get all elements with class="tablinks" and remove the class "active"
        tablinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }

        // Show the current tab, and add an "active" class to the button that opened the tab
        document.getElementById(categoria).style.display = "block";
        evt.currentTarget.className += " active";
    }

    document.getElementById("defaultOpen").click();
</script>




<?php echo $this->endSection() ?>