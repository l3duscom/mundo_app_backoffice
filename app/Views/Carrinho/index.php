<?php echo $this->extend('Layout/externo'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>


<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.css') ?>" />
<style>
    /* Container do menu de navegação com setas */
    .tab-navigation-wrapper {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 0;
        margin-bottom: 30px;
    }

    .tab-navigation-content {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }

    /* Barra de progresso customizada */
    .tab-scroll-indicator {
        width: 100%;
        height: 4px;
        background: #e8e8e8;
        border-radius: 10px;
        position: relative;
        overflow: visible;
        cursor: pointer;
        transition: opacity 0.3s ease, height 0.2s ease;
    }

    .tab-scroll-indicator:hover {
        height: 5px;
        background: #dcdcdc;
    }

    .tab-scroll-thumb {
        height: 100%;
        background: linear-gradient(90deg, #5651e5 0%, #4541d8 100%);
        border-radius: 10px;
        transition: all 0.2s ease;
        position: absolute;
        left: 0;
        box-shadow: 0 2px 6px rgba(86, 81, 229, 0.4);
        cursor: grab;
        min-width: 40px;
    }

    .tab-scroll-thumb:hover {
        background: linear-gradient(90deg, #6b67ff 0%, #5853ed 100%);
        box-shadow: 0 3px 8px rgba(86, 81, 229, 0.5);
    }

    .tab-scroll-thumb:active {
        cursor: grabbing;
        box-shadow: 0 2px 4px rgba(86, 81, 229, 0.6);
    }

    /* Instrução visual de scroll */
    .scroll-instruction {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 8px;
        padding: 8px 16px;
        background: linear-gradient(135deg, #f8f7ff 0%, #f0efff 100%);
        border: 1px solid #e0dfff;
        border-radius: 8px;
        color: #5651e5;
        font-size: 13px;
        font-weight: 600;
        animation: pulseInstruction 2s ease-in-out infinite;
        transition: all 0.3s ease;
        cursor: pointer;
        user-select: none;
    }

    .scroll-instruction i {
        font-size: 18px;
        animation: slideArrows 1.5s ease-in-out infinite;
    }

    .scroll-instruction:hover {
        background: linear-gradient(135deg, #f0efff 0%, #e8e7ff 100%);
        border-color: #5651e5;
        transform: scale(1.02);
    }

    /* Animação de pulse sutil */
    @keyframes pulseInstruction {
        0%, 100% {
            box-shadow: 0 2px 8px rgba(86, 81, 229, 0.2);
        }
        50% {
            box-shadow: 0 4px 16px rgba(86, 81, 229, 0.3);
        }
    }

    /* Animação das setas */
    @keyframes slideArrows {
        0%, 100% {
            transform: translateX(0);
        }
        50% {
            transform: translateX(3px);
        }
    }

    .scroll-instruction i:first-child {
        animation-direction: reverse;
    }

    /* Setas de navegação */
    .nav-arrow {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        width: 40px;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
        flex-shrink: 0;
        z-index: 10;
    }

    .nav-arrow:hover:not(.disabled) {
        background: #f8f8ff;
        border-color: #5651e5;
        transform: scale(1.05);
    }

    .nav-arrow i {
        font-size: 22px;
        color: #666;
        transition: color 0.3s;
    }

    .nav-arrow:hover:not(.disabled) i {
        color: #5651e5;
    }

    .nav-arrow.disabled {
        opacity: 0.2;
        cursor: not-allowed;
        pointer-events: none;
    }

    /* Container scrollável */
    .tab-container {
        flex: 1;
        overflow-x: auto;
        overflow-y: hidden;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 0;
        margin-bottom: 0;
    }

    /* Esconde scrollbar nativa em todos os dispositivos */
    .tab-container {
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE e Edge */
    }

    .tab-container::-webkit-scrollbar {
        display: none; /* Chrome, Safari, Opera */
    }

    /* Style the tab */
    .tab {
        display: flex;
        gap: 10px;
        padding: 0;
        background-color: transparent;
        font-size: 16px;
        min-width: min-content;
    }

    /* Style the buttons that are used to open the tab content */
    .tab button {
        background-color: #ffffff;
        color: #333;
        border: 1px solid #e5e5e5;
        border-radius: 12px;
        outline: none;
        cursor: pointer;
        padding: 10px 16px;
        transition: all 0.25s ease;
        font-weight: 500;
        min-width: 110px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        white-space: nowrap;
        position: relative;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .tab button .day-name {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 3px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .tab button .day-date {
        font-size: 11px;
        font-weight: 400;
        opacity: 0.65;
        letter-spacing: 0.1px;
    }

    /* Change background color of buttons on hover */
    .tab button:hover:not(.active) {
        color: #5651e5;
        background-color: #fafafa;
        border-color: #d0d0d0;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    /* Create an active/current tablink class */
    .tab button.active {
        color: #FFFFFF;
        background: linear-gradient(135deg, #5651e5 0%, #4541d8 100%);
        border-color: #5651e5;
        box-shadow: 0 6px 20px rgba(86, 81, 229, 0.4), 0 2px 8px rgba(86, 81, 229, 0.2);
        transform: translateY(-1px);
    }

    .tab button.active:hover {
        box-shadow: 0 6px 24px rgba(86, 81, 229, 0.45), 0 2px 8px rgba(86, 81, 229, 0.25);
    }

    .tab button.active .day-date {
        opacity: 0.95;
    }

    .tab button.active .day-name {
        font-weight: 700;
    }

    /* Ajuste para botões específicos com mais texto */
    .tab button:nth-child(n+4) {
        min-width: 155px;
    }

    /* Style the tab content */
    .tabcontent {
        display: none;
        padding: 6px 12px;
    }

    .tabcontent {
        animation: fadeEffect 0.5s;
        /* Fading effect takes 0.5 second */
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



    /* Estilos para dispositivos móveis */
    @media screen and (max-width: 768px) {
        .tab-navigation-wrapper {
            margin-bottom: 24px;
        }

        .tab-navigation-content {
            gap: 8px;
            margin-bottom: 10px;
        }

        .scroll-instruction {
            font-size: 12px;
            padding: 6px 12px;
        }

        .scroll-instruction i {
            font-size: 16px;
        }

        .tab-scroll-indicator {
            height: 6px;
        }

        .tab-scroll-indicator:hover {
            height: 6px;
        }

        .tab-scroll-thumb {
            min-width: 50px;
        }

        .nav-arrow {
            width: 36px;
            height: 54px;
            border-radius: 12px;
        }

        .nav-arrow i {
            font-size: 20px;
        }

        .tab {
            gap: 8px;
        }

        .tab button {
            min-width: 95px;
            padding: 10px 14px;
            border-radius: 10px;
        }

        .tab button .day-name {
            font-size: 13px;
            margin-bottom: 2px;
        }

        .tab button .day-date {
            font-size: 10px;
        }
        
        .card {
            margin-bottom: 15px;
            padding: 15px;
        }
        
        .card-body {
            padding: 15px;
        }
        
        .item-name {
            font-size: 14px !important;
            line-height: 1.3;
        }
        
        .item-price {
            font-size: 20px !important;
        }
        
        .badge {
            font-size: 10px !important;
            padding: 3px 6px !important;
            margin-bottom: 5px;
            display: inline-block;
            word-wrap: break-word;
            white-space: normal;
            line-height: 1.2;
        }
        
        .badge-container {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .badge-container .badge {
            width: 100%;
            margin-right: 0 !important;
        }
        
        .quantity-controls {
            font-size: 36px !important;
        }
        
        .quantity-controls a {
            padding: 4px 6px;
        }
        
        .quantity-controls i {
            font-size: 20px !important;
        }
        
        .service-fee {
            font-size: 10px !important;
        }
        
        .ticket-info {
            font-size: 9px !important;
            margin-bottom: 5px !important;
        }
        
        .quantity-section {
            margin-top: 10px !important;
        }
        
        .price-section {
            margin-top: 15px !important;
        }
        
        .eligibility-section {
            margin-top: 10px !important;
        }
        
        .eligibility-section strong {
            font-size: 12px !important;
        }
        
        .eligibility-section .text-muted {
            font-size: 10px !important;
        }
    }
    
    /* Estilos para dispositivos muito pequenos */
    @media screen and (max-width: 480px) {
        .tab-navigation-wrapper {
            margin-bottom: 20px;
        }

        .tab-navigation-content {
            gap: 6px;
            margin-bottom: 8px;
        }

        .tab-scroll-indicator {
            height: 6px;
        }

        .tab-scroll-indicator:hover {
            height: 6px;
        }

        .tab-scroll-thumb {
            min-width: 45px;
        }

        .nav-arrow {
            width: 32px;
            height: 50px;
        }

        .nav-arrow i {
            font-size: 18px;
        }

        .tab {
            gap: 6px;
        }

        .tab button {
            min-width: 85px;
            padding: 8px 12px;
        }

        .tab button .day-name {
            font-size: 12px;
            margin-bottom: 2px;
        }

        .tab button .day-date {
            font-size: 9px;
        }

        .tab button:nth-child(n+4) {
            min-width: 105px;
        }

        .scroll-instruction {
            font-size: 11px;
            padding: 5px 10px;
            gap: 6px;
        }

        .scroll-instruction i {
            font-size: 14px;
        }
        
        .col-7, .col-5 {
            width: 100% !important;
            margin-bottom: 10px;
        }
        
        .text-right {
            text-align: center !important;
        }
        
        .d-flex.align-items-end {
            align-items: center !important;
        }
        
        .item-price {
            font-size: 18px !important;
        }
        
        .quantity-controls {
            font-size: 32px !important;
        }
        
        .quantity-controls i {
            font-size: 18px !important;
        }
    }

    html {
        scroll-behavior: smooth;
    }

    /* Desktop - experiência limpa e moderna */
    @media screen and (min-width: 769px) {
        .tab-navigation-wrapper {
            margin-bottom: 32px;
        }

        .tab-navigation-content {
            gap: 12px;
            margin-bottom: 10px;
        }

        .tab-scroll-indicator {
            height: 4px;
        }

        .tab-scroll-indicator:hover {
            height: 5px;
        }

        .tab {
            gap: 10px;
        }

        .tab button {
            min-width: 120px;
            padding: 10px 18px;
        }

        .tab button:nth-child(n+4) {
            min-width: 140px;
        }

        /* Esconder setas quando não há overflow */
        .nav-arrow.disabled {
            opacity: 0;
            pointer-events: none;
        }

        .scroll-instruction {
            font-size: 13px;
            padding: 8px 20px;
        }

        .scroll-instruction i {
            font-size: 18px;
        }
    }

    /* Telas muito largas */
    @media screen and (min-width: 1200px) {
        .tab button {
            min-width: 130px;
            padding: 12px 20px;
        }

        .tab button:nth-child(n+4) {
            min-width: 150px;
        }
    }

    /* Comportamento das setas no mobile */
    @media screen and (max-width: 768px) {
        .nav-arrow {
            opacity: 1;
        }
        
        .nav-arrow.disabled {
            opacity: 0.2;
        }
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

// Salva o event_id na sessão ao carregar a página
if (isset($event_id)) {
    session()->set('event_id', $event_id);
} else {
    $event_id = session()->get('event_id');
}

?>



<h5 class="mb-0 mt-3">Quais ingressos você deseja?</h5>


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
                                        'preco' => $produto['preco'] + ($produto['preco'] * 0.07),
                                        'tipo' => $produto['tipo'],
                                        'taxa' => $produto['preco'] * 0.07,
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


                        <!-- Tab links -->
                        <?php
                        $tem_camping = false;
                        $tem_epic = false;
                        $tem_vip = false;
                        $tem_super_pack = false;
                        foreach ($items as $item) {
                            if (isset($item['categoria'])) {
                                if (strtolower($item['categoria']) === 'epic') $tem_epic = true;
                                if (strtolower($item['categoria']) === 'vip') $tem_vip = true;
                                if (strtolower($item['categoria']) === 'camping') $tem_camping = true;
                            }
                            if (!empty($item['parent_ticket_id'])) {
                                $tem_super_pack = true;
                            }
                        }
                        ?>
                        <!-- Tab Navigation com Setas -->
                        <div class="tab-navigation-wrapper">
                            <div class="tab-navigation-content">
                                <button class="nav-arrow left" onclick="scrollTabs('left')" id="scrollLeft" aria-label="Rolar para esquerda">
                                    <i class='bx bx-chevron-left'></i>
                                </button>
                                
                                <div class="tab-container" id="tabContainer">
                                    <div class="tab" id="tabMenu">
                                    <button class="tablinks" onclick="openCategoria(event, 'sabado')" id="defaultOpen">
                                        <span class="day-name">sáb.</span>
                                        <span class="day-date"><?php
                                            if (isset($evento)) {
                                                $data_inicio = date_create($evento->data_inicio);
                                                echo date_format($data_inicio, 'd/m');
                                            }
                                        ?></span>
                                    </button>
                                    
                                    <button class="tablinks" onclick="openCategoria(event, 'domingo')">
                                        <span class="day-name">dom.</span>
                                        <span class="day-date"><?php
                                            if (isset($evento)) {
                                                $data_fim = date_create($evento->data_fim);
                                                echo date_format($data_fim, 'd/m');
                                            }
                                        ?></span>
                                    </button>
                                    
                                    <button class="tablinks" onclick="openCategoria(event, 'passaporte')">
                                        <span class="day-name">2 dias</span>
                                        <span class="day-date"><?php
                                            if (isset($evento)) {
                                                $data_inicio = date_create($evento->data_inicio);
                                                $data_fim = date_create($evento->data_fim);
                                                echo date_format($data_inicio, 'd/m') . ' e ' . date_format($data_fim, 'd/m');
                                            }
                                        ?></span>
                                    </button>
                                    
                                    <?php if ($tem_camping): ?>
                                        <button class="tablinks" onclick="openCategoria(event, 'camping')">
                                            <span class="day-name">Florinda</span>
                                            <span class="day-date">Internacional</span>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($tem_epic): ?>
                                        <button class="tablinks" onclick="openCategoria(event, 'epic')">
                                            <span class="day-name">EPIC PASS</span>
                                            <span class="day-date">Experiência Épica</span>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($tem_vip): ?>
                                        <button class="tablinks" onclick="openCategoria(event, 'vip')">
                                            <span class="day-name">VIP FULL</span>
                                            <span class="day-date">Experiência Máxima</span>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($tem_super_pack): ?>
                                        <button class="tablinks" onclick="openCategoria(event, 'super_pack')">
                                            <span class="day-name">SUPER PACK</span>
                                            <span class="day-date">+ econômico</span>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button class="tablinks" onclick="openCategoria(event, 'cosplay')">
                                        <span class="day-name">Cosplayer</span>
                                        <span class="day-date">Promocional</span>
                                    </button>
                                    
                                    <button class="tablinks" onclick="openCategoria(event, 'after')">
                                        <span class="day-name">After Dream</span>
                                        <span class="day-date">Festa</span>
                                    </button>
                                    </div>
                                </div>
                                
                                <button class="nav-arrow right" onclick="scrollTabs('right')" id="scrollRight" aria-label="Rolar para direita">
                                    <i class='bx bx-chevron-right'></i>
                                </button>
                            </div>
                            
                            <!-- Barra de scroll customizada -->
                            <div class="tab-scroll-indicator">
                                <div class="tab-scroll-thumb" id="scrollThumb"></div>
                            </div>
                            
                            <!-- Instrução visual de scroll -->
                            <div class="scroll-instruction" id="scrollInstruction">
                                <i class='bx bx-chevrons-left'></i>
                                <span>Deslize para ver todos os ingressos</span>
                                <i class='bx bx-chevrons-right'></i>
                            </div>
                        </div>
                        <div class="d-grid gap-2 mb-0">
                            <a class="btn btn-light" href="#pagar">
                                <!-- <i class="bi bi-arrow-down-circle-fill" style="font-size: 25px; color: purple;"></i>-->
                                <strong><i class='bx bx-down-arrow-circle'></i> Ver detalhes da compra</strong>
                            </a>
                        </div>
                        <!-- Tab content -->
                        <?php
                        // SÁBADO
                        $tem_sabado = false;
                        foreach ($items as $key => $value) {
                            if ((($value['categoria'] == 'comum' || $value['categoria'] == 'premium') && $value['tipo'] == 'individual' && $value['dia'] == 'sab' && empty($value['parent_ticket_id']))) {
                                $tem_sabado = true;
                                break;
                            }
                        }
                        ?>
                        <div id="sabado" class="tabcontent">
                            <?php if (!$tem_sabado): ?>
                                <div class="alert alert-warning text-center mt-3 mb-3">LOTE ESGOTADO, aguarde novo lote</div>
                            <?php endif; ?>
                            <!-- instruções e conteúdo já existentes da aba Sábado -->
                            <p style="padding-top: 20px;">Este ingresso dá direito a participar do <?= isset($evento) ? esc($evento->nome) : 'evento' ?> <strong>somente no sábado</strong><?php
                                if (isset($evento)) {
                                    $data_inicio = date_create($evento->data_inicio);
                                    $meses = [
                                        '01' => 'janeiro', '02' => 'fevereiro', '03' => 'março', '04' => 'abril',
                                        '05' => 'maio', '06' => 'junho', '07' => 'julho', '08' => 'agosto',
                                        '09' => 'setembro', '10' => 'outubro', '11' => 'novembro', '12' => 'dezembro'
                                    ];
                                    $dia_inicio = date_format($data_inicio, 'd');
                                    $mes = $meses[date_format($data_inicio, 'm')];
                                    $ano = date_format($data_inicio, 'Y');
                                    $hora_inicio = isset($evento->hora_inicio) ? $evento->hora_inicio : '11:00';
                                    $hora_fim = isset($evento->hora_fim) ? $evento->hora_fim : '20:00';
                                    echo ", dia $dia_inicio de $mes de $ano das $hora_inicio às $hora_fim";
                                }
                            ?></p>
                            <p>Você receberá uma credencial exclusiva e colecionável que deverá ser apresentada na entrada e na saída do festival e sempre que for requisitada. Você terá direito à entrar e sair do evento sempre que quiser!</p>
                            <hr>
                            <div class="mb-0 mt-3 font-24" style="color: #333;">Selecione seu ingresso </div>
                            <p>Apenas a promoção de maior desconto será aplicada ao final do carrinho.</p>
                            <?php foreach ($items as $key => $value) : ?>
                                <?php if ((($value['categoria'] == 'comum' || $value['categoria'] == 'premium') && $value['tipo'] == 'individual' && $value['dia'] == 'sab' && empty($value['parent_ticket_id']))) : ?>
                                    <div class="card border border-muted px-3" data-item-id="<?= $key ?>">
                                        <div class="form-check mt-3 mb-3">
                                            <div class="row">
                                                <div class="col-7">
                                                    <span style="color: purple; font-size: 10px" class="ticket-info">Finaliza em: <?= date('d/m/Y', strtotime($value['data_lote'])) ?> </span><br>
                                                    <strong class="item-name" style="color: #6C038F; font-size: 16px"><?= $value['nome'] ?></strong><br>
                                                    <?php if (!empty($value['parent_ticket_id'])) : ?>
                                                        <div class="mt-1 mb-1 badge-container">
                                                            <span class="badge bg-success text-white me-2" style="font-size: 11px; padding: 4px 8px;">
                                                                <i class="bi bi-check-circle-fill me-1"></i>Válido para 2 eventos: Dream25 + Anime Dream 25
                                                            </span>
                                                            <span class="badge bg-warning text-dark" style="font-size: 11px; padding: 4px 8px;">
                                                                + Econômico
                                                            </span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <span class="text-muted ticket-info" style="font-size: 10px"><strong><?= $value['tipo'] ?> - <?= $value['lote'] ?> lote</strong></span>


                                                </div>
                                                <div class="col-5 text-right">
                                                    <?php if ($value['estoque'] > 0) : ?>
                                                        <div class="col-12 mt-3 font-20 d-flex flex-column align-items-end justify-content-center quantity-section" style="gap:0;">
                                                            <strong class="quantity-controls" style="font-size: 20px;">
                                                                <a href="?excluir=<?= $key ?>"><i class="bi bi-dash-circle-fill" style="padding-right: 4px;"></i></a>
                                                                <?= (isset($_SESSION['carrinho'][$key]['quantidade'])) ? $_SESSION['carrinho'][$key]['quantidade'] : '0' ?>
                                                                <a href="?adicionar=<?= $key ?>"><i class="bi bi-plus-circle-fill" style="padding-left: 4px"></i></a>
                                                            </strong>
                                                            <div class="d-flex flex-column align-items-end price-section" style="margin-top: 2px;">
                                                                <strong class="item-price" data-price="<?= $value['preco'] ?>" style="word-wrap: normal; font-size: 26px; line-height: 1; margin-bottom: 0;">
                                                                    <span style="font-size: 0.6em; vertical-align: middle;">R$</span> <?= number_format($value['preco'], 2, ',', ''); ?>
                                                                </strong>
                                                                <span class="text-muted service-fee" style="font-size: 11px; line-height: 1.1; margin-top: 0; margin-bottom: 0; padding-top: 0;">+ <?= (isset($_SESSION['carrinho'][$key]['taxa'])) ? 'R$ ' . number_format($_SESSION['carrinho'][$key]['taxa'], 2, ',', '') . ' taxa de serviço' : 'taxa de serviço' ?></span>
                                                            </div>
                                                        </div>
                                                    <?php else : ?>
                                                        <strong style="color: red;">ESGOTADO</strong>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-11 mt-3 eligibility-section">
                                                    <strong style="font-size: 13px;" class="mt-5"><i class='bx bx-info-circle'></i> Quem pode comprar? </strong>
                                                    <div class="text-muted mt-1" style="font-size: 11px;"><?= $value['descricao'] ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>

                        <?php
                        // DOMINGO
                        $tem_domingo = false;
                        foreach ($items as $key => $value) {
                            if ((($value['categoria'] == 'comum' || $value['categoria'] == 'premium') && $value['tipo'] == 'individual' && $value['dia'] == 'dom' && empty($value['parent_ticket_id']))) {
                                $tem_domingo = true;
                                break;
                            }
                        }
                        ?>
                        <div id="domingo" class="tabcontent">
                            <?php if (!$tem_domingo): ?>
                                <div class="alert alert-warning text-center mt-3 mb-3">LOTE ESGOTADO, aguarde novo lote</div>
                            <?php endif; ?>
                            <!-- instruções e conteúdo já existentes da aba Domingo -->
                            <p style="padding-top: 20px;">Este ingresso dá direito a participar do <?= isset($evento) ? esc($evento->nome) : 'evento' ?> <strong>somente no domingo</strong><?php
                                if (isset($evento)) {
                                    $data_fim = date_create($evento->data_fim);
                                    $meses = [
                                        '01' => 'janeiro', '02' => 'fevereiro', '03' => 'março', '04' => 'abril',
                                        '05' => 'maio', '06' => 'junho', '07' => 'julho', '08' => 'agosto',
                                        '09' => 'setembro', '10' => 'outubro', '11' => 'novembro', '12' => 'dezembro'
                                    ];
                                    $dia_fim = date_format($data_fim, 'd');
                                    $mes = $meses[date_format($data_fim, 'm')];
                                    $ano = date_format($data_fim, 'Y');
                                    $hora_inicio = isset($evento->hora_inicio) ? $evento->hora_inicio : '11:00';
                                    $hora_fim = isset($evento->hora_fim) ? $evento->hora_fim : '20:00';
                                    echo ", dia $dia_fim de $mes de $ano das $hora_inicio às $hora_fim";
                                }
                            ?></p>
                            <p>Você receberá uma credencial exclusiva e colecionável que deverá ser apresentada na entrada e na saída do festival e sempre que for requisitada. Você terá direito à entrar e sair do evento sempre que quiser!</p>
                            <hr>
                            <div class="mb-0 mt-3 font-24" style="color: #333;">Selecione seu ingresso </div>
                            <p>Apenas a promoção de maior desconto será aplicada ao final do carrinho.</p>
                            <?php foreach ($items as $key => $value) : ?>
                                <?php if ((($value['categoria'] == 'comum' || $value['categoria'] == 'premium') && $value['tipo'] == 'individual' && $value['dia'] == 'dom' && empty($value['parent_ticket_id']))) : ?>
                                    <div class="card border border-muted px-3" data-item-id="<?= $key ?>">
                                        <div class="form-check mt-3 mb-3">
                                            <div class="row">
                                                <div class="col-7">
                                                    <span style="color: purple; font-size: 10px" class="ticket-info">Finaliza em: <?= date('d/m/Y', strtotime($value['data_lote'])) ?> </span><br>
                                                    <strong class="item-name" style="color: #6C038F; font-size: 16px"><?= $value['nome'] ?></strong><br>
                                                    <?php if (!empty($value['parent_ticket_id'])) : ?>
                                                        <div class="mt-1 mb-1 badge-container">
                                                            <span class="badge bg-success text-white me-2" style="font-size: 11px; padding: 4px 8px;">
                                                                <i class="bi bi-check-circle-fill me-1"></i>Válido para 2 eventos: Dream25 + Anime Dream 25
                                                            </span>
                                                            <span class="badge bg-warning text-dark" style="font-size: 11px; padding: 4px 8px;">
                                                                + Econômico
                                                            </span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <span class="text-muted ticket-info" style="font-size: 10px"><strong><?= $value['tipo'] ?> - <?= $value['lote'] ?> lote</strong></span>


                                                </div>
                                                <div class="col-5 text-right">
                                                    <?php if ($value['estoque'] > 0) : ?>
                                                        <div class="col-12 mt-3 font-20 d-flex flex-column align-items-end justify-content-center quantity-section" style="gap:0;">
                                                            <strong class="quantity-controls" style="font-size: 20px;">
                                                                <a href="?excluir=<?= $key ?>"><i class="bi bi-dash-circle-fill" style="padding-right: 4px;"></i></a>
                                                                <?= (isset($_SESSION['carrinho'][$key]['quantidade'])) ? $_SESSION['carrinho'][$key]['quantidade'] : '0' ?>
                                                                <a href="?adicionar=<?= $key ?>"><i class="bi bi-plus-circle-fill" style="padding-left: 4px"></i></a>
                                                            </strong>
                                                            <div class="d-flex flex-column align-items-end price-section" style="margin-top: 2px;">
                                                                <strong class="item-price" data-price="<?= $value['preco'] ?>" style="word-wrap: normal; font-size: 26px; line-height: 1; margin-bottom: 0;">
                                                                    <span style="font-size: 0.6em; vertical-align: middle;">R$</span> <?= number_format($value['preco'], 2, ',', ''); ?>
                                                                </strong>
                                                                <span class="text-muted service-fee" style="font-size: 11px; line-height: 1.1; margin-top: 0; margin-bottom: 0; padding-top: 0;">+ <?= (isset($_SESSION['carrinho'][$key]['taxa'])) ? 'R$ ' . number_format($_SESSION['carrinho'][$key]['taxa'], 2, ',', '') . ' taxa de serviço' : 'taxa de serviço' ?></span>
                                                            </div>
                                                        </div>
                                                    <?php else : ?>
                                                        <strong style="color: red;">ESGOTADO</strong>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-11 mt-3 eligibility-section">
                                                    <strong style="font-size: 13px;" class="mt-5"><i class='bx bx-info-circle'></i> Quem pode comprar? </strong>
                                                    <div class="text-muted mt-1" style="font-size: 11px;"><?= $value['descricao'] ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>

                        </div>


                        <?php
                        // 2 DIAS
                        $tem_passaporte = false;
                        foreach ($items as $key => $value) {
                            if ((($value['categoria'] == 'comum' || $value['categoria'] == 'premium') && $value['tipo'] == 'combo' && empty($value['parent_ticket_id']))) {
                                $tem_passaporte = true;
                                break;
                            }
                        }
                        ?>
                        <div id="passaporte" class="tabcontent">
                            <?php if (!$tem_passaporte): ?>
                                <div class="alert alert-warning text-center mt-3 mb-3">LOTE ESGOTADO, aguarde novo lote</div>
                            <?php endif; ?>
                            <!-- instruções e conteúdo já existentes da aba 2 Dias -->
                            <p style="padding-top: 20px;">Este ingresso dá direito a participar do <?= isset($evento) ? esc($evento->nome) : 'evento' ?> <strong>nos dois dias de evento</strong><?php
                                if (isset($evento)) {
                                    $data_inicio = date_create($evento->data_inicio);
                                    $data_fim = date_create($evento->data_fim);
                                    $meses = [
                                        '01' => 'janeiro', '02' => 'fevereiro', '03' => 'março', '04' => 'abril',
                                        '05' => 'maio', '06' => 'junho', '07' => 'julho', '08' => 'agosto',
                                        '09' => 'setembro', '10' => 'outubro', '11' => 'novembro', '12' => 'dezembro'
                                    ];
                                    $dia_inicio = date_format($data_inicio, 'd');
                                    $dia_fim = date_format($data_fim, 'd');
                                    $mes = $meses[date_format($data_inicio, 'm')];
                                    $ano = date_format($data_inicio, 'Y');
                                    $hora_inicio = isset($evento->hora_inicio) ? $evento->hora_inicio : '11:00';
                                    $hora_fim = isset($evento->hora_fim) ? $evento->hora_fim : '20:00';
                                    echo ", dias $dia_inicio e $dia_fim de $mes de $ano das $hora_inicio às $hora_fim";
                                }
                            ?></p>
                            <p>Você receberá uma credencial exclusiva e colecionável que deverá ser apresentada na entrada e na saída do festival e sempre que for requisitada. Você terá direito à entrar e sair do evento sempre que quiser!</p>
                            <hr>
                            <div class="mb-0 mt-3 font-24" style="color: #333;">Selecione seu ingresso </div>
                            <p>Apenas a promoção de maior desconto será aplicada ao final do carrinho.</p>
                            <?php foreach ($items as $key => $value) : ?>
                                <?php if ((($value['categoria'] == 'comum' || $value['categoria'] == 'premium') && $value['tipo'] == 'combo' && empty($value['parent_ticket_id']))) : ?>
                                    <div class="card border border-muted px-3" data-item-id="<?= $key ?>">
                                        <div class="form-check mt-3 mb-3">
                                            <div class="row">
                                                <div class="col-7">
                                                    <span style="color: purple; font-size: 10px" class="ticket-info">Finaliza em: <?= date('d/m/Y', strtotime($value['data_lote'])) ?> </span><br>
                                                    <strong class="item-name" style="color: #6C038F; font-size: 16px"><?= $value['nome'] ?></strong><br>
                                                    <?php if (!empty($value['parent_ticket_id'])) : ?>
                                                        <div class="mt-1 mb-1 badge-container">
                                                            <span class="badge bg-success text-white me-2" style="font-size: 11px; padding: 4px 8px;">
                                                                <i class="bi bi-check-circle-fill me-1"></i>Válido para 2 eventos: Dream25 + Anime Dream 25
                                                            </span>
                                                            <span class="badge bg-warning text-dark" style="font-size: 11px; padding: 4px 8px;">
                                                                + Econômico
                                                            </span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <span class="text-muted ticket-info" style="font-size: 10px"><strong><?= $value['tipo'] ?> - <?= $value['lote'] ?> lote</strong></span>


                                                </div>
                                                <div class="col-5 text-right">
                                                    <?php if ($value['estoque'] > 0) : ?>
                                                        <div class="col-12 mt-3 font-20 d-flex flex-column align-items-end justify-content-center quantity-section" style="gap:0;">
                                                            <strong class="quantity-controls" style="font-size: 20px;">
                                                                <a href="?excluir=<?= $key ?>"><i class="bi bi-dash-circle-fill" style="padding-right: 4px;"></i></a>
                                                                <?= (isset($_SESSION['carrinho'][$key]['quantidade'])) ? $_SESSION['carrinho'][$key]['quantidade'] : '0' ?>
                                                                <a href="?adicionar=<?= $key ?>"><i class="bi bi-plus-circle-fill" style="padding-left: 4px"></i></a>
                                                            </strong>
                                                            <div class="d-flex flex-column align-items-end price-section" style="margin-top: 2px;">
                                                                <strong class="item-price" data-price="<?= $value['preco'] ?>" style="word-wrap: normal; font-size: 26px; line-height: 1; margin-bottom: 0;">
                                                                    <span style="font-size: 0.6em; vertical-align: middle;">R$</span> <?= number_format($value['preco'], 2, ',', ''); ?>
                                                                </strong>
                                                                <span class="text-muted service-fee" style="font-size: 11px; line-height: 1.1; margin-top: 0; margin-bottom: 0; padding-top: 0;">+ <?= (isset($_SESSION['carrinho'][$key]['taxa'])) ? 'R$ ' . number_format($_SESSION['carrinho'][$key]['taxa'], 2, ',', '') . ' taxa de serviço' : 'taxa de serviço' ?></span>
                                                            </div>
                                                        </div>
                                                    <?php else : ?>
                                                        <strong style="color: red;">ESGOTADO</strong>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-11 mt-3 eligibility-section">
                                                    <strong style="font-size: 13px;" class="mt-5"><i class='bx bx-info-circle'></i> Quem pode comprar? </strong>
                                                    <div class="text-muted mt-1" style="font-size: 11px;"><?= $value['descricao'] ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>

                        </div>

                        <?php
                        // DREAM PASS
                        $tem_camping_ingresso = false;
                        foreach ($items as $key => $value) {
                            if (($value['categoria'] == 'camping' && empty($value['parent_ticket_id']))) {
                                $tem_camping_ingresso = true;
                                break;
                            }
                        }
                        ?>
                        <div id="camping" class="tabcontent">
                            <?php if (!$tem_camping_ingresso): ?>
                                <div class="alert alert-warning text-center mt-3 mb-3">LOTE ESGOTADO, aguarde novo lote</div>
                            <?php endif; ?>
                            <!-- instruções e conteúdo já existentes da aba EPIC PASS -->
                            <p style="padding-top: 20px;">Este ingresso dá direito a participar do <?= isset($evento) ? esc($evento->nome) : 'evento' ?> no dia 7 de dezembro de 2025 + Apresentação da Atriz Florinda Meza
                            <!--<a href="#" data-bs-toggle="modal" data-bs-target="#vip-fanModal" class="btn btn-outline-secondary w-100 mt-0" style="margin-right: 5px;">O que está incluso nesse ingresso? </a> -->

                            <hr>
                            <div class="mb-0 mt-3 font-24" style="color: #333;">Selecione seu ingresso </div>
                            <p>Apenas a promoção de maior desconto será aplicada ao final do carrinho.</p>
                            <?php foreach ($items as $key => $value) : ?>
                                <?php if (($value['categoria'] == 'camping' && empty($value['parent_ticket_id']))) : ?>
                                    <div class="card border border-muted px-3" data-item-id="<?= $key ?>">
                                        <div class="form-check mt-3 mb-3">
                                            <div class="row">
                                                <div class="col-7">
                                                    <span style="color: purple; font-size: 10px" class="ticket-info">Finaliza em: <?= date('d/m/Y', strtotime($value['data_lote'])) ?> </span><br>
                                                    <strong class="item-name" style="color: #6C038F; font-size: 16px"><?= $value['nome'] ?></strong><br>
                                                    <?php if (!empty($value['parent_ticket_id'])) : ?>
                                                        <div class="mt-1 mb-1 badge-container">
                                                            <span class="badge bg-success text-white me-2" style="font-size: 11px; padding: 4px 8px;">
                                                                <i class="bi bi-check-circle-fill me-1"></i>EXCLUSIVO, INÉDITO
                                                            </span>
                                                            
                                                        </div>
                                                    <?php endif; ?>
                                                    <span class="text-muted ticket-info" style="font-size: 10px"><strong><?= $value['tipo'] ?> - <?= $value['lote'] ?> lote</strong></span>
                                                </div>
                                                <div class="col-5 text-right">
                                                    <?php if ($value['estoque'] > 0) : ?>
                                                        <div class="col-12 mt-3 font-20 d-flex flex-column align-items-end justify-content-center quantity-section" style="gap:0;">
                                                            <strong class="quantity-controls" style="font-size: 20px;">
                                                                <a href="?excluir=<?= $key ?>"><i class="bi bi-dash-circle-fill" style="padding-right: 4px;"></i></a>
                                                                <?= (isset($_SESSION['carrinho'][$key]['quantidade'])) ? $_SESSION['carrinho'][$key]['quantidade'] : '0' ?>
                                                                <a href="?adicionar=<?= $key ?>"><i class="bi bi-plus-circle-fill" style="padding-left: 4px"></i></a>
                                                            </strong>
                                                            <div class="d-flex flex-column align-items-end price-section" style="margin-top: 2px;">
                                                                <strong class="item-price" data-price="<?= $value['preco'] ?>" style="word-wrap: normal; font-size: 26px; line-height: 1; margin-bottom: 0;">
                                                                    <span style="font-size: 0.6em; vertical-align: middle;">R$</span> <?= number_format($value['preco'], 2, ',', ''); ?>
                                                            </strong>
                                                                <span class="text-muted service-fee" style="font-size: 11px; line-height: 1.1; margin-top: 0; margin-bottom: 0; padding-top: 0;">+ <?= (isset($_SESSION['carrinho'][$key]['taxa'])) ? 'R$ ' . number_format($_SESSION['carrinho'][$key]['taxa'], 2, ',', '') . ' taxa de serviço' : 'taxa de serviço' ?></span>
                                                            </div>
                                                        </div>
                                                    <?php else : ?>
                                                        <strong style="color: red;">ESGOTADO</strong>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-11 mt-3 eligibility-section">
                                                    <strong style="font-size: 13px;" class="mt-5"><i class='bx bx-info-circle'></i> Quem pode comprar? </strong>
                                                    <div class="text-muted mt-1" style="font-size: 11px;"><?= $value['descricao'] ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>

                        </div>


                        <?php
                        // EPIC
                        $tem_epic_ingresso = false;
                        foreach ($items as $key => $value) {
                            if (($value['categoria'] == 'epic' && empty($value['parent_ticket_id']))) {
                                $tem_epic_ingresso = true;
                                break;
                            }
                        }
                        ?>
                        <div id="epic" class="tabcontent">
                            <?php if (!$tem_epic_ingresso): ?>
                                <div class="alert alert-warning text-center mt-3 mb-3">LOTE ESGOTADO, aguarde novo lote</div>
                            <?php endif; ?>
                            <!-- instruções e conteúdo já existentes da aba EPIC PASS -->
                            <p style="padding-top: 20px;">Este ingresso dá direito a participar do <?= isset($evento) ? esc($evento->nome) : 'evento' ?> nos dias selecionados.</p>
                            <p>Você receberá uma kit colecionável com Credencial, Pulseira, Cordão, Pôster e Guia do evento! A Credencial e Pulseira deverão ser apresentados na entrada e na saída do festival e sempre que for requisitada. Você terá direito à entrar e sair do evento sempre que quiser!</p>
                            <!--<a href="#" data-bs-toggle="modal" data-bs-target="#vip-fanModal" class="btn btn-outline-secondary w-100 mt-0" style="margin-right: 5px;">O que está incluso nesse ingresso? </a> -->

                            <hr>
                            <div class="mb-0 mt-3 font-24" style="color: #333;">Selecione seu ingresso </div>
                            <p>Apenas a promoção de maior desconto será aplicada ao final do carrinho.</p>                        


                            <?php foreach ($items as $key => $value) : ?>
                                <?php if (($value['categoria'] == 'epic' && empty($value['parent_ticket_id']))) : ?>
                                    <div class="card border border-muted px-3" data-item-id="<?= $key ?>">
                                        <div class="form-check mt-3 mb-3">
                                            <div class="row">
                                                <div class="col-7">
                                                    <span style="color: purple; font-size: 10px" class="ticket-info">Finaliza em: <?= date('d/m/Y', strtotime($value['data_lote'])) ?> </span><br>
                                                    <strong class="item-name" style="color: #6C038F; font-size: 16px"><?= $value['nome'] ?></strong><br>
                                                    <?php if (!empty($value['parent_ticket_id'])) : ?>
                                                        <div class="mt-1 mb-1 badge-container">
                                                            <span class="badge bg-success text-white me-2" style="font-size: 11px; padding: 4px 8px;">
                                                                <i class="bi bi-check-circle-fill me-1"></i>Válido para 2 eventos: Dream25 + Anime Dream 25
                                                            </span>
                                                            <span class="badge bg-warning text-dark" style="font-size: 11px; padding: 4px 8px;">
                                                                + Econômico
                                                            </span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <span class="text-muted ticket-info" style="font-size: 10px"><strong><?= $value['tipo'] ?> - <?= $value['lote'] ?> lote</strong></span>
                                                </div>
                                                <div class="col-5 text-right">
                                                    <?php if ($value['estoque'] > 0) : ?>
                                                        <div class="col-12 mt-3 font-20 d-flex flex-column align-items-end justify-content-center quantity-section" style="gap:0;">
                                                            <strong class="quantity-controls" style="font-size: 20px;">
                                                                <a href="?excluir=<?= $key ?>"><i class="bi bi-dash-circle-fill" style="padding-right: 4px;"></i></a>
                                                                <?= (isset($_SESSION['carrinho'][$key]['quantidade'])) ? $_SESSION['carrinho'][$key]['quantidade'] : '0' ?>
                                                                <a href="?adicionar=<?= $key ?>"><i class="bi bi-plus-circle-fill" style="padding-left: 4px"></i></a>
                                                            </strong>
                                                            <div class="d-flex flex-column align-items-end price-section" style="margin-top: 2px;">
                                                                <strong class="item-price" data-price="<?= $value['preco'] ?>" style="word-wrap: normal; font-size: 26px; line-height: 1; margin-bottom: 0;">
                                                                    <span style="font-size: 0.6em; vertical-align: middle;">R$</span> <?= number_format($value['preco'], 2, ',', ''); ?>
                                                            </strong>
                                                                <span class="text-muted service-fee" style="font-size: 11px; line-height: 1.1; margin-top: 0; margin-bottom: 0; padding-top: 0;">+ <?= (isset($_SESSION['carrinho'][$key]['taxa'])) ? 'R$ ' . number_format($_SESSION['carrinho'][$key]['taxa'], 2, ',', '') . ' taxa de serviço' : 'taxa de serviço' ?></span>
                                                            </div>
                                                        </div>
                                                    <?php else : ?>
                                                        <strong style="color: red;">ESGOTADO</strong>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-11 mt-3 eligibility-section">
                                                    <strong style="font-size: 13px;" class="mt-5"><i class='bx bx-info-circle'></i> Quem pode comprar? </strong>
                                                    <div class="text-muted mt-1" style="font-size: 11px;"><?= $value['descricao'] ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>

                        </div>

                        <?php
                        // VIP
                        $tem_vip_ingresso = false;
                        foreach ($items as $key => $value) {
                            if (($value['categoria'] == 'vip' && empty($value['parent_ticket_id']))) {
                                $tem_vip_ingresso = true;
                                break;
                            }
                        }
                        ?>
                        <div id="vip" class="tabcontent">
                            <?php if (!$tem_vip_ingresso): ?>
                                <div class="alert alert-warning text-center mt-3 mb-3">LOTE ESGOTADO, aguarde novo lote</div>
                            <?php endif; ?>
                            <!-- instruções e conteúdo já existentes da aba VIP FULL -->
                            <p style="padding-top: 20px;">Este ingresso dá direito a participar do <?= isset($evento) ? esc($evento->nome) : 'evento' ?> nos dias selecionados.</p>
                            <p>Você receberá uma kit colecionável com Credencial, Pulseira, Cordão, Pôster, Copo, Ingresso holográfico e Guia do evento! A Credencial e Pulseira deverão ser apresentados na entrada e na saída do festival e sempre que for requisitada. Você terá direito à entrar e sair do evento sempre que quiser!</p>
                            <!--<a href="#" data-bs-toggle="modal" data-bs-target="#vip-fullModal" class="btn btn-outline-secondary w-100 mt-0" style="margin-right: 5px;">O que está incluso nesse ingresso? </a> -->

                            <hr>
                            <div class="mb-0 mt-3 font-24" style="color: #333;">Selecione seu ingresso </div>
                            <p>Apenas a promoção de maior desconto será aplicada ao final do carrinho.</p>
                            <!-- <div class="card border border-muted">
                                <div class="form-check mt-3 mb-3">
                                    <div class="row">
                                        <strong style="color: red; font-size: 14px">VIP FULL Sábado e VIP FULL Combo 2 dias ESGOTADOS</strong>
                                    </div>
                                </div>
                            </div> -->
                            <?php foreach ($items as $key => $value) : ?>
                                <?php if (($value['categoria'] == 'vip' && empty($value['parent_ticket_id']))) : ?>
                                    <div class="card border border-muted px-3" data-item-id="<?= $key ?>">
                                        <div class="form-check mt-3 mb-3">
                                            <div class="row">
                                                <div class="col-7">
                                                    <span style="color: purple; font-size: 10px" class="ticket-info">Finaliza em: <?= date('d/m/Y', strtotime($value['data_lote'])) ?> </span><br>
                                                    <strong class="item-name" style="color: #6C038F; font-size: 16px"><?= $value['nome'] ?></strong><br>
                                                    <?php if (!empty($value['parent_ticket_id'])) : ?>
                                                        <div class="mt-1 mb-1 badge-container">
                                                            <span class="badge bg-success text-white me-2" style="font-size: 11px; padding: 4px 8px;">
                                                                <i class="bi bi-check-circle-fill me-1"></i>Válido para 2 eventos: Dream25 + Anime Dream 25
                                                            </span>
                                                            <span class="badge bg-warning text-dark" style="font-size: 11px; padding: 4px 8px;">
                                                                + Econômico
                                                            </span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <span class="text-muted ticket-info" style="font-size: 10px"><strong><?= $value['tipo'] ?> - <?= $value['lote'] ?> lote</strong></span>


                                                </div>
                                                <div class="col-5 text-right">
                                                    <?php if ($value['estoque'] > 0) : ?>
                                                        <div class="col-12 mt-3 font-20 d-flex flex-column align-items-end justify-content-center quantity-section" style="gap:0;">
                                                            <strong class="quantity-controls" style="font-size: 20px;">
                                                                <a href="?excluir=<?= $key ?>"><i class="bi bi-dash-circle-fill" style="padding-right: 4px;"></i></a>
                                                                <?= (isset($_SESSION['carrinho'][$key]['quantidade'])) ? $_SESSION['carrinho'][$key]['quantidade'] : '0' ?>
                                                                <a href="?adicionar=<?= $key ?>"><i class="bi bi-plus-circle-fill" style="padding-left: 4px"></i></a>
                                                            </strong>
                                                            <div class="d-flex flex-column align-items-end price-section" style="margin-top: 2px;">
                                                                <strong class="item-price" data-price="<?= $value['preco'] ?>" style="word-wrap: normal; font-size: 26px; line-height: 1; margin-bottom: 0;">
                                                                    <span style="font-size: 0.6em; vertical-align: middle;">R$</span> <?= number_format($value['preco'], 2, ',', ''); ?>
                                                                </strong>
                                                                <span class="text-muted service-fee" style="font-size: 11px; line-height: 1.1; margin-top: 0; margin-bottom: 0; padding-top: 0;">+ <?= (isset($_SESSION['carrinho'][$key]['taxa'])) ? 'R$ ' . number_format($_SESSION['carrinho'][$key]['taxa'], 2, ',', '') . ' taxa de serviço' : 'taxa de serviço' ?></span>
                                                            </div>
                                                        </div>
                                                    <?php else : ?>
                                                        <strong style="color: red;">ESGOTADO</strong>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-11 mt-3 eligibility-section">
                                                    <strong style="font-size: 13px;" class="mt-5"><i class='bx bx-info-circle'></i> Quem pode comprar? </strong>
                                                    <div class="text-muted mt-1" style="font-size: 11px;"><?= $value['descricao'] ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>

                        </div>

                        <?php
                        // SUPER PACK
                        $tem_super_pack_ingresso = false;
                        foreach ($items as $key => $value) {
                            if (!empty($value['parent_ticket_id'])) {
                                $tem_super_pack_ingresso = true;
                                break;
                            }
                        }
                        ?>
                        <div id="super_pack" class="tabcontent">
                            <?php if (!$tem_super_pack_ingresso): ?>
                                <div class="alert alert-warning text-center mt-3 mb-3">LOTE ESGOTADO, aguarde novo lote</div>
                            <?php endif; ?>
                            <!-- instruções e conteúdo já existentes da aba Super Pack -->
                            <p style="padding-top: 20px;">Este ingresso dá direito a participar de <strong>2 eventos incríveis</strong>: <?= isset($evento) ? esc($evento->nome) : 'evento principal' ?> + Anime Dream 25!</p>
                            <p>Você receberá uma credencial exclusiva e colecionável que será válida para ambos os eventos. A credencial deverá ser apresentada na entrada e na saída dos festivais e sempre que for requisitada. Você terá direito à entrar e sair dos eventos sempre que quiser!</p>
                            <hr>
                            <div class="mb-0 mt-3 font-24" style="color: #333;">Selecione seu ingresso </div>
                            <p>Apenas a promoção de maior desconto será aplicada ao final do carrinho.</p>

                            <?php foreach ($items as $key => $value) : ?>
                                <?php if (!empty($value['parent_ticket_id'])) : ?>
                                    <div class="card border border-muted px-3" data-item-id="<?= $key ?>">
                                        <div class="form-check mt-3 mb-3">
                                            <div class="row">
                                                <div class="col-7">
                                                    <span style="color: purple; font-size: 10px" class="ticket-info">Finaliza em: <?= date('d/m/Y', strtotime($value['data_lote'])) ?> </span><br>
                                                    <strong class="item-name" style="color: #6C038F; font-size: 16px"><?= $value['nome'] ?></strong><br>
                                                    <div class="mt-1 mb-1 badge-container">
                                                        <span class="badge bg-success text-white me-2" style="font-size: 11px; padding: 4px 8px;">
                                                            <i class="bi bi-check-circle-fill me-1"></i>Válido para 2 eventos: Dream25 + Anime Dream 25
                                                        </span>
                                                        <span class="badge bg-warning text-dark" style="font-size: 11px; padding: 4px 8px;">
                                                            + Econômico
                                                        </span>
                                                    </div>
                                                    <span class="text-muted ticket-info" style="font-size: 10px"><strong><?= $value['tipo'] ?> - <?= $value['lote'] ?> lote</strong></span>
                                                </div>
                                                <div class="col-5 text-right">
                                                    <?php if ($value['estoque'] > 0) : ?>
                                                        <div class="col-12 mt-3 font-20 d-flex flex-column align-items-end justify-content-center quantity-section" style="gap:0;">
                                                            <strong class="quantity-controls" style="font-size: 20px;">
                                                                <a href="?excluir=<?= $key ?>"><i class="bi bi-dash-circle-fill" style="padding-right: 4px;"></i></a>
                                                                <?= (isset($_SESSION['carrinho'][$key]['quantidade'])) ? $_SESSION['carrinho'][$key]['quantidade'] : '0' ?>
                                                                <a href="?adicionar=<?= $key ?>"><i class="bi bi-plus-circle-fill" style="padding-left: 4px"></i></a>
                                                            </strong>
                                                            <div class="d-flex flex-column align-items-end price-section" style="margin-top: 2px;">
                                                                <strong class="item-price" data-price="<?= $value['preco'] ?>" style="word-wrap: normal; font-size: 26px; line-height: 1; margin-bottom: 0;">
                                                                    <span style="font-size: 0.6em; vertical-align: middle;">R$</span> <?= number_format($value['preco'], 2, ',', ''); ?>
                                                                </strong>
                                                                <span class="text-muted service-fee" style="font-size: 11px; line-height: 1.1; margin-top: 0; margin-bottom: 0; padding-top: 0;">+ <?= (isset($_SESSION['carrinho'][$key]['taxa'])) ? 'R$ ' . number_format($_SESSION['carrinho'][$key]['taxa'], 2, ',', '') . ' taxa de serviço' : 'taxa de serviço' ?></span>
                                                            </div>
                                                        </div>
                                                    <?php else : ?>
                                                        <strong style="color: red;">ESGOTADO</strong>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-11 mt-3 eligibility-section">
                                                    <strong style="font-size: 13px;" class="mt-5"><i class='bx bx-info-circle'></i> Quem pode comprar? </strong>
                                                    <div class="text-muted mt-1" style="font-size: 11px;"><?= $value['descricao'] ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>

                        </div>

                        <?php
                        // COSPLAY
                        $tem_cosplay = false;
                        foreach ($items as $key => $value) {
                            if (($value['categoria'] == 'cosplay' && empty($value['parent_ticket_id']))) {
                                $tem_cosplay = true;
                                break;
                            }
                        }
                        ?>
                        <div id="cosplay" class="tabcontent">
                            <?php if (!$tem_cosplay): ?>
                                <div class="alert alert-warning text-center mt-3 mb-3">LOTE ESGOTADO, aguarde novo lote</div>
                            <?php endif; ?>
                            <!-- instruções e conteúdo já existentes da aba Cosplayer -->
                            <p style="padding-top: 20px;">Este ingresso dá direito a participar do <?= isset($evento) ? esc($evento->nome) : 'evento' ?> nos dias selecionados.</p>
                            <p>Você receberá uma pulseira colecionável COSPLAYER que deverá ser apresentada na entrada e na saída do festival e sempre que for requisitada. Vvocê terá direito à entrar e sair do evento sempre que quiser!</p>
                            <!--<a href="#" data-bs-toggle="modal" data-bs-target="#cosplayerModal" class="btn btn-outline-secondary w-100 mt-0" style="margin-right: 5px;">O que está incluso nesse ingresso? </a> -->

                            <hr>
                            <div class="mb-0 mt-3 font-24" style="color: #333;">Selecione seu ingresso </div>
                            <p>Apenas a promoção de maior desconto será aplicada ao final do carrinho.</p>

                            <?php foreach ($items as $key => $value) : ?>
                                <?php if (($value['categoria'] == 'cosplay' && empty($value['parent_ticket_id']))) : ?>
                                    <div class="card border border-muted px-3" data-item-id="<?= $key ?>">
                                        <div class="form-check mt-3 mb-3">
                                            <div class="row">
                                                <div class="col-7">
                                                    <span style="color: purple; font-size: 10px" class="ticket-info">Finaliza em: <?= date('d/m/Y', strtotime($value['data_lote'])) ?> </span><br>
                                                    <strong class="item-name" style="color: #6C038F; font-size: 16px"><?= $value['nome'] ?></strong><br>
                                                    <?php if (!empty($value['parent_ticket_id'])) : ?>
                                                        <div class="mt-1 mb-1 badge-container">
                                                            <span class="badge bg-success text-white me-2" style="font-size: 11px; padding: 4px 8px;">
                                                                <i class="bi bi-check-circle-fill me-1"></i>Válido para 2 eventos: Dream25 + Anime Dream 25
                                                            </span>
                                                            <span class="badge bg-warning text-dark" style="font-size: 11px; padding: 4px 8px;">
                                                                + Econômico
                                                            </span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <span class="text-muted ticket-info" style="font-size: 10px"><strong><?= $value['tipo'] ?> - <?= $value['lote'] ?> lote</strong></span>


                                                </div>
                                                <div class="col-5 text-right">
                                                    <?php if ($value['estoque'] > 0) : ?>
                                                        <div class="col-12 mt-3 font-20 d-flex flex-column align-items-end justify-content-center quantity-section" style="gap:0;">
                                                            <strong class="quantity-controls" style="font-size: 20px;">
                                                                <a href="?excluir=<?= $key ?>"><i class="bi bi-dash-circle-fill" style="padding-right: 4px;"></i></a>
                                                                <?= (isset($_SESSION['carrinho'][$key]['quantidade'])) ? $_SESSION['carrinho'][$key]['quantidade'] : '0' ?>
                                                                <a href="?adicionar=<?= $key ?>"><i class="bi bi-plus-circle-fill" style="padding-left: 4px"></i></a>
                                                            </strong>
                                                            <div class="d-flex flex-column align-items-end price-section" style="margin-top: 2px;">
                                                                <strong class="item-price" data-price="<?= $value['preco'] ?>" style="word-wrap: normal; font-size: 26px; line-height: 1; margin-bottom: 0;">
                                                                    <span style="font-size: 0.6em; vertical-align: middle;">R$</span> <?= number_format($value['preco'], 2, ',', ''); ?>
                                                                </strong>
                                                                <span class="text-muted service-fee" style="font-size: 11px; line-height: 1.1; margin-top: 0; margin-bottom: 0; padding-top: 0;">+ <?= (isset($_SESSION['carrinho'][$key]['taxa'])) ? 'R$ ' . number_format($_SESSION['carrinho'][$key]['taxa'], 2, ',', '') . ' taxa de serviço' : 'taxa de serviço' ?></span>
                                                            </div>
                                                        </div>
                                                    <?php else : ?>
                                                        <strong style="color: red;">ESGOTADO</strong>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-11 mt-3 eligibility-section">
                                                    <strong style="font-size: 13px;" class="mt-5"><i class='bx bx-info-circle'></i> Quem pode comprar? </strong>
                                                    <div class="text-muted mt-1" style="font-size: 11px;"><?= $value['descricao'] ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>

                        </div>


                         <?php
                        // COSPLAY
                        $tem_after = false;
                        foreach ($items as $key => $value) {
                            if (($value['categoria'] == 'after' && empty($value['parent_ticket_id']))) {
                                $tem_after = true;
                                break;
                            }
                        }
                        ?>
                        <div id="after" class="tabcontent">
                            <?php if (!$tem_after): ?>
                                <div class="alert alert-warning text-center mt-3 mb-3">LOTE ESGOTADO, aguarde novo lote</div>
                            <?php endif; ?>
                            <p style="padding-top: 20px;">Este ingresso dá direito a participar do After do <?= isset($evento) ? esc($evento->nome) : 'evento' ?> nos dias selecionados.</p>
                            <p>Classificação: 18 Anos <br>Você receberá uma pulseira colecionável que deverá ser apresentada na entrada e na saída da festa e sempre que for requisitada. <br>Funcionamento: 20h às 5h <br> Game Party Inclusa + Camping se selecionado</p>

                            <hr>
                            <div class="mb-0 mt-3 font-24" style="color: #333;">Selecione seu ingresso </div>
                            <p>Apenas a promoção de maior desconto será aplicada ao final do carrinho.</p>

                            <?php foreach ($items as $key => $value) : ?>
                                <?php if (($value['categoria'] == 'after' && empty($value['parent_ticket_id']))) : ?>
                                    <div class="card border border-muted px-3" data-item-id="<?= $key ?>">
                                        <div class="form-check mt-3 mb-3">
                                            <div class="row">
                                                <div class="col-7">
                                                    <span style="color: purple; font-size: 10px" class="ticket-info">Finaliza em: <?= date('d/m/Y', strtotime($value['data_lote'])) ?> </span><br>
                                                    <strong class="item-name" style="color: #6C038F; font-size: 16px"><?= $value['nome'] ?></strong><br>
                                                    <?php if (!empty($value['parent_ticket_id'])) : ?>
                                                        <div class="mt-1 mb-1 badge-container">
                                                            <span class="badge bg-success text-white me-2" style="font-size: 11px; padding: 4px 8px;">
                                                                <i class="bi bi-check-circle-fill me-1"></i>Válido para 2 eventos: Dream25 + Anime Dream 25
                                                            </span>
                                                            <span class="badge bg-warning text-dark" style="font-size: 11px; padding: 4px 8px;">
                                                                + Econômico
                                                            </span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <span class="text-muted ticket-info" style="font-size: 10px"><strong><?= $value['tipo'] ?> - <?= $value['lote'] ?> lote</strong></span>


                                                </div>
                                                <div class="col-5 text-right">
                                                    <?php if ($value['estoque'] > 0) : ?>
                                                        <div class="col-12 mt-3 font-20 d-flex flex-column align-items-end justify-content-center quantity-section" style="gap:0;">
                                                            <strong class="quantity-controls" style="font-size: 20px;">
                                                                <a href="?excluir=<?= $key ?>"><i class="bi bi-dash-circle-fill" style="padding-right: 4px;"></i></a>
                                                                <?= (isset($_SESSION['carrinho'][$key]['quantidade'])) ? $_SESSION['carrinho'][$key]['quantidade'] : '0' ?>
                                                                <a href="?adicionar=<?= $key ?>"><i class="bi bi-plus-circle-fill" style="padding-left: 4px"></i></a>
                                                            </strong>
                                                            <div class="d-flex flex-column align-items-end price-section" style="margin-top: 2px;">
                                                                <strong class="item-price" data-price="<?= $value['preco'] ?>" style="word-wrap: normal; font-size: 26px; line-height: 1; margin-bottom: 0;">
                                                                    <span style="font-size: 0.6em; vertical-align: middle;">R$</span> <?= number_format($value['preco'], 2, ',', ''); ?>
                                                                </strong>
                                                                <span class="text-muted service-fee" style="font-size: 11px; line-height: 1.1; margin-top: 0; margin-bottom: 0; padding-top: 0;">+ <?= (isset($_SESSION['carrinho'][$key]['taxa'])) ? 'R$ ' . number_format($_SESSION['carrinho'][$key]['taxa'], 2, ',', '') . ' taxa de serviço' : 'taxa de serviço' ?></span>
                                                            </div>
                                                        </div>
                                                    <?php else : ?>
                                                        <strong style="color: red;">ESGOTADO</strong>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-11 mt-3 eligibility-section">
                                                    <strong style="font-size: 13px;" class="mt-5"><i class='bx bx-info-circle'></i> Quem pode comprar? </strong>
                                                    <div class="text-muted mt-1" style="font-size: 11px;"><?= $value['descricao'] ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>

                        </div>
                        <div id="mae" class="tabcontent">
                            <p style="padding-top: 20px;">Este ingresso dá direito a participar do <?= isset($evento) ? esc($evento->nome) : 'evento' ?> nos dias selecionados.</p>
                            <p>Você receberá uma credencial exclusiva e colecionável que deverá ser apresentada na entrada e na saída do festival e sempre que for requisitada. Você terá direito à entrar e sair do evento sempre que quiser!</p>
                            <!--<a href="#" data-bs-toggle="modal" data-bs-target="#cosplayerModal" class="btn btn-outline-secondary w-100 mt-0" style="margin-right: 5px;">O que está incluso nesse ingresso? </a> -->

                            <hr>
                            <div class="mb-0 mt-3 font-24" style="color: #333;">Selecione seu ingresso </div>
                            <p>Apenas a promoção de maior desconto será aplicada ao final do carrinho.</p>

                            <?php foreach ($items as $key => $value) : ?>
                                <?php if (($value['categoria'] == 'mae' && empty($value['parent_ticket_id']))) : ?>
                                    <div class="card border border-muted px-3" data-item-id="<?= $key ?>">
                                        <div class="form-check mt-3 mb-3">
                                            <div class="row">
                                                <div class="col-7">
                                                    <span style="color: purple; font-size: 10px" class="ticket-info">Finaliza em: <?= date('d/m/Y', strtotime($value['data_lote'])) ?> </span><br>
                                                    <strong class="item-name" style="color: #6C038F; font-size: 16px"><?= $value['nome'] ?></strong><br>
                                                    <?php if (!empty($value['parent_ticket_id'])) : ?>
                                                        <div class="mt-1 mb-1 badge-container">
                                                            <span class="badge bg-success text-white me-2" style="font-size: 11px; padding: 4px 8px;">
                                                                <i class="bi bi-check-circle-fill me-1"></i>Válido para 2 eventos: Dream25 + Anime Dream 25
                                                            </span>
                                                            <span class="badge bg-warning text-dark" style="font-size: 11px; padding: 4px 8px;">
                                                                + Econômico
                                                            </span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <span class="text-muted ticket-info" style="font-size: 10px"><strong><?= $value['tipo'] ?> - <?= $value['lote'] ?> lote</strong></span>


                                                </div>
                                                <div class="col-5 text-right">
                                                    <?php if ($value['estoque'] > 0) : ?>
                                                        <div class="col-12 mt-3 font-20 d-flex flex-column align-items-end justify-content-center quantity-section" style="gap:0;">
                                                            <strong class="quantity-controls" style="font-size: 20px;">
                                                                <a href="?excluir=<?= $key ?>"><i class="bi bi-dash-circle-fill" style="padding-right: 4px;"></i></a>
                                                                <?= (isset($_SESSION['carrinho'][$key]['quantidade'])) ? $_SESSION['carrinho'][$key]['quantidade'] : '0' ?>
                                                                <a href="?adicionar=<?= $key ?>"><i class="bi bi-plus-circle-fill" style="padding-left: 4px"></i></a>
                                                            </strong>
                                                            <div class="d-flex flex-column align-items-end price-section" style="margin-top: 2px;">
                                                                <strong class="item-price" data-price="<?= $value['preco'] ?>" style="word-wrap: normal; font-size: 26px; line-height: 1; margin-bottom: 0;">
                                                                    <span style="font-size: 0.6em; vertical-align: middle;">R$</span> <?= number_format($value['preco'], 2, ',', ''); ?>
                                                                </strong>
                                                                <span class="text-muted service-fee" style="font-size: 11px; line-height: 1.1; margin-top: 0; margin-bottom: 0; padding-top: 0;">+ <?= (isset($_SESSION['carrinho'][$key]['taxa'])) ? 'R$ ' . number_format($_SESSION['carrinho'][$key]['taxa'], 2, ',', '') . ' taxa de serviço' : 'taxa de serviço' ?></span>
                                                            </div>
                                                        </div>
                                                    <?php else : ?>
                                                        <strong style="color: red;">ESGOTADO</strong>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-11 mt-3 eligibility-section">
                                                    <strong style="font-size: 13px;" class="mt-5"><i class='bx bx-info-circle'></i> Quem pode comprar? </strong>
                                                    <div class="text-muted mt-1" style="font-size: 11px;"><?= $value['descricao'] ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>

                        </div>


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

                                <a href="<?= site_url('/evento/entrega/'. $event_id) ?>" class="btn btn-lg mt-0" style="padding:10px; background-color: purple; border-color: purple; color: white;"> Ir para o pagamento <i class='bx bx-right-arrow-circle'></i></a>

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
                                    <th scope="row">Acesso a um dos dias mágicos do Dreamfest</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Credencial Colecionável</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Cordão Colecionável</th>
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
                                    <td style="color:#ffcc00; font-size: 22px" title="Pago separadamente"><i class="fadeIn animated bx bx-dollar-circle"></i></td>
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
                                </strong> Promoção válida para cosplayers devidamente caracterizados no dia do evento!</a>
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
                                    <th scope="row">Credencial Colecionável</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Cordão Colecionável</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
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
                                    <td style="color:#ffcc00; font-size: 22px" title="Pago separadamente"><i class="fadeIn animated bx bx-dollar-circle"></i></td>
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
                                    <th scope="row">Credencial Colecionável</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Cordão Colecionável</th>
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
                                    <td style="color:#ffcc00; font-size: 22px" title="Pago separadamente"><i class="fadeIn animated bx bx-dollar-circle"></i></td>
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

<!--MODAL INGRESSO INDIVIDUAL-->
<div class="modal fade" id="ingIndividualSabModal" tabindex="-1" aria-labelledby="ingIndividualSabModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ingIndividualSabModal">Escolha uma opção:</h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

            </div>

            <div class="modal-body" style="max-height: 500px; overflow-y: auto;">



            </div>
        </div>
    </div>
</div>

<!--MODAL INGRESSO INDIVIDUAL-->
<div class="modal fade" id="ingIndividualDomModal" tabindex="-1" aria-labelledby="ingIndividualDomModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ingIndividualDomModal">Escolha uma opção:</h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

            </div>

            <div class="modal-body" style="max-height: 500px; overflow-y: auto;">

                xxxx

            </div>
        </div>
    </div>
</div>

<!--MODAL INGRESSO PASSAPORTE-->
<div class="modal fade" id="ingComboModal" tabindex="-1" aria-labelledby="ingComboModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ingComboModal">Escolha uma opção:</h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

            </div>

            <div class="modal-body" style="max-height: 500px; overflow-y: auto;">


                xxxx
            </div>
        </div>
    </div>
</div>

<!--MODAL-->

<!--MODAL INGRESSO PASSAPORTE-->
<div class="modal fade" id="ingEpicModal" tabindex="-1" aria-labelledby="ingEpicModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ingEpicModal">Escolha uma opção:</h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

            </div>

            <div class="modal-body" style="max-height: 500px; overflow-y: auto;">


                <div class="card">
                    <div class="card-body">
                        <?php foreach ($items as $key => $value) : ?>
                            <?php if ($value['categoria'] == 'epic' && empty($value['parent_ticket_id'])) : ?>
                                <div class="card border border-muted px-3" data-item-id="<?= $key ?>">
                                    <div class="form-check mt-3 mb-3">
                                        <div class="row">
                                            <div class="col-7">
                                                <span style="color: purple; font-size: 10px" class="ticket-info">Finaliza em: <?= date('d/m/Y', strtotime($value['data_lote'])) ?> </span><br>
                                                <strong class="item-name" style="color: #6C038F; font-size: 16px"><?= $value['nome'] ?></strong><br>
                                                <?php if (!empty($value['parent_ticket_id'])) : ?>
                                                    <div class="mt-1 mb-1 badge-container">
                                                        <span class="badge bg-success text-white me-2" style="font-size: 11px; padding: 4px 8px;">
                                                            <i class="bi bi-check-circle-fill me-1"></i>Válido para 2 eventos: Dream25 + Anime Dream 25
                                                        </span>
                                                        <span class="badge bg-warning text-dark" style="font-size: 11px; padding: 4px 8px;">
                                                            + Econômico
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                                <span class="text-muted ticket-info" style="font-size: 10px"><strong><?= $value['tipo'] ?> - <?= $value['lote'] ?> lote</strong></span>
                                            </div>
                                            <div class="col-5 text-right">
                                                <?php if ($value['estoque'] > 0) : ?>
                                                    <div class="col-12 mt-3 font-20 d-flex flex-column align-items-end justify-content-center quantity-section" style="gap:0;">
                                                        <strong class="quantity-controls" style="font-size: 20px;">
                                                            <a href="?excluir=<?= $key ?>"><i class="bi bi-dash-circle-fill" style="padding-right: 4px;"></i></a>
                                                            <?= (isset($_SESSION['carrinho'][$key]['quantidade'])) ? $_SESSION['carrinho'][$key]['quantidade'] : '0' ?>
                                                            <a href="?adicionar=<?= $key ?>"><i class="bi bi-plus-circle-fill" style="padding-left: 4px"></i></a>
                                                        </strong>
                                                        <div class="d-flex flex-column align-items-end price-section" style="margin-top: 2px;">
                                                            <strong class="item-price" data-price="<?= $value['preco'] ?>" style="word-wrap: normal; font-size: 26px; line-height: 1; margin-bottom: 0;">
                                                                <span style="font-size: 0.6em; vertical-align: middle;">R$</span> <?= number_format($value['preco'], 2, ',', ''); ?>
                                                        </strong>
                                                            <span class="text-muted service-fee" style="font-size: 11px; line-height: 1.1; margin-top: 0; margin-bottom: 0; padding-top: 0;">+ <?= (isset($_SESSION['carrinho'][$key]['taxa'])) ? 'R$ ' . number_format($_SESSION['carrinho'][$key]['taxa'], 2, ',', '') . ' taxa de serviço' : 'taxa de serviço' ?></span>
                                                        </div>
                                                    </div>
                                                <?php else : ?>
                                                    <strong style="color: red;">ESGOTADO</strong>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-11 mt-3 eligibility-section">
                                                <strong style="font-size: 13px;" class="mt-5"><i class='bx bx-info-circle'></i> Quem pode comprar? </strong>
                                                <div class="text-muted mt-1" style="font-size: 11px;"><?= $value['descricao'] ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!--MODAL-->


<!--MODAL INGRESSO COSPLAY-->
<div class="modal fade" id="ingCosplayModal" tabindex="-1" aria-labelledby="ingCosplayModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ingCosplayModal">Escolha uma opção:</h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

            </div>

            <div class="modal-body" style="max-height: 500px; overflow-y: auto;">


                ccc
            </div>
        </div>
    </div>
</div>

<!--MODAL-->

<!--MODAL INGRESSO COSPLAY-->
<div class="modal fade" id="ingVipModal" tabindex="-1" aria-labelledby="ingVipModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ingVipModal">Escolha uma opção:</h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

            </div>

            <div class="modal-body" style="max-height: 500px; overflow-y: auto;">


                xxxx
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
                                </strong> O KIT VIP FULL é composto por ingresso já com desconto de meia entrada (50% de desconto) + Ingresso Cinemark + Estacionamento Grátis + KIT de benefícios VIP FULL, conforme sua categoria
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
                                    <th scope="row">ESTACIONAMENTO GRÁTIS</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>

                                <tr>
                                    <th scope="row">Entrar e sair do evento quando quiser!</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>

                                <tr>
                                    <th scope="row">Pulseira Colecionável</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Credencial Colecionável</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Cordão Colecionável</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Pôster oficial EXCLUSIVO</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Copo Colecionável EXCLUSIVO</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Ingresso Holográfico EXCLUSIVO</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Meet & Greet com todos os convidados*</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Acesso ao evento nos dias escolhidos das 10 às 19</th>
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
                                    <th scope="row">Rodízio de Pizza servido exclusivamente na sala VIP das 13h às 16h​</th>
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
                                </strong> O KIT EPIC é composto por 1 passaporte 2 dias já com desconto de meia entrada (50% de desconto) + KIT de benefícios EPIC, conforme sua categoria
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
                                    <th scope="row">Cordão Colecionável</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Pôster oficial EXCLUSIVO</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>

                                <tr>
                                    <th scope="row">Meet & Greet com 1 convidado de sua escolha</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Acesso ao evento nos dias escolhidos das 10 às 19</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>

                                <tr>
                                    <th scope="row">Descontos de até 30% de desconto em lojinhas durante o evento!</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>


                                <tr>
                                    <th scope="row">HOTZONE - Espaço nas primeiras fileiras do palco principal! </th>
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

<div class="modal fade" id="premiumModal" tabindex="-1" aria-labelledby="premiumlLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="premiumLabel">Se liga nas vantagens de ser Premium!</h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

            </div>

            <div class="modal-body">


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
                                    <th scope="row">Entrar e sair do evento quando quiser!</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>

                                <tr>
                                    <th scope="row">Pulseira Colecionável</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Credencial Colecionável</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Cordão Colecionável</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Pôster oficial EXCLUSIVO</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Brindes exclusivos</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>
                                <tr>
                                    <th scope="row">Acesso ao evento nos dias escolhidos das 10 às 19</th>
                                    <td style="color:green; font-size: 22px"><i class="fadeIn animated bx bx-check-circle"></i></td>
                                </tr>

                                <tr>
                                    <th scope="row">Descontos de até 30% de desconto em lojinhas durante o evento!</th>
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
                                    <p class="mb-0 text-muted" style="font-size: 11px">O Universo Geek ao Extremo</p>
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
                <?php if ($_SESSION['total'] != 0) : ?>
                    <div id="areaBotoes" class="row g-3">
                        <div class="col-lg-12">
                            <a href="<?= site_url('/evento/entrega'. $event_id) ?>" class="w-100 btn btn-primary btn-lg ">Ir para entrega</a>
                        </div>

                    </div>
                    <hr>
                    <center>
                        <span class="text-muted mb-5" style="font-size: 12px;">Processado por:</span><br>
                                                    <img class="mt-1" src="<?php echo site_url('recursos/front/images/asaas.png'); ?>" width="150px" height="auto">
                    </center>
                <?php endif ?>



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

<!-- Meta Pixel Events -->
<?php if (isset($evento) && !empty($evento->meta_pixel_id)): ?>
<script>
// ViewContent Event - quando a página do carrinho é carregada
fbq('track', 'ViewContent', {
    content_name: '<?= $evento->nome ?>',
    content_category: '<?= $evento->categoria ?? 'Evento' ?>',
    content_type: 'product',
    content_ids: [<?= $evento->id ?>]
});

// AddToCart Event - quando um item é adicionado ao carrinho
function trackAddToCart(itemId, itemName, itemPrice, itemQuantity = 1) {
    fbq('track', 'AddToCart', {
        content_name: '<?= $evento->nome ?> - ' + itemName,
        content_category: '<?= $evento->categoria ?? 'Evento' ?>',
        content_type: 'product',
        value: itemPrice,
        currency: 'BRL',
        content_ids: [itemId],
        num_items: itemQuantity
    });
}

// InitiateCheckout Event - quando o usuário clica para ir para o pagamento
function trackInitiateCheckout() {
    let totalValue = <?= $_SESSION['total'] ?? 0 ?>;
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
}

// Track AddToCart when items are added via URL parameters
<?php if (isset($_GET['adicionar'])): ?>
    <?php 
    $idProduto = (int)$_GET['adicionar'];
    if (isset($items[$idProduto])): 
        $produto = $items[$idProduto];
    ?>
    trackAddToCart(<?= $idProduto ?>, '<?= $produto['nome'] ?>', <?= $produto['preco'] ?>);
    <?php endif; ?>
<?php endif; ?>
</script>
<?php endif; ?>

<script>
    $(document).ready(function() {

        //$("#form").LoadingOverlay("show");

        <?php echo $this->include('Clientes/_checkmail'); ?>

        <?php echo $this->include('Clientes/_viacep'); ?>

        // Track AddToCart when items are added via AJAX
        $(document).on('click', 'a[href*="adicionar="]', function(e) {
            <?php if (isset($evento) && !empty($evento->meta_pixel_id)): ?>
            let href = $(this).attr('href');
            let itemId = href.match(/adicionar=(\d+)/);
            if (itemId && itemId[1]) {
                // Get item details from the page
                let itemElement = $('[data-item-id="' + itemId[1] + '"]');
                let itemName = itemElement.find('.item-name').text() || 'Ingresso';
                let itemPrice = parseFloat(itemElement.find('.item-price').data('price')) || 0;
                
                // Track after a short delay to ensure the item is added to cart
                setTimeout(function() {
                    trackAddToCart(itemId[1], itemName, itemPrice);
                }, 500);
            }
            <?php endif; ?>
        });

        // Track InitiateCheckout when user clicks to go to payment
        $(document).on('click', 'a[href*="evento/entrega"]', function(e) {
            <?php if (isset($evento) && !empty($evento->meta_pixel_id)): ?>
            trackInitiateCheckout();
            <?php endif; ?>
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

    //$(document).ready(function() {
    //  $('#cartModal').modal('show');
    //})
</script>
<script>
    function openCategoria(evt, categoria) {
        // Esconde todas as tabcontent
        var tabcontent = document.getElementsByClassName("tabcontent");
        for (var i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        // Remove a classe 'active' de todos os botões
        var tablinks = document.getElementsByClassName("tablinks");
        for (var i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }
        // Mostra a aba selecionada
        document.getElementById(categoria).style.display = "block";
        evt.currentTarget.className += " active";
        // Salva a aba selecionada no localStorage
        localStorage.setItem('abaCarrinhoSelecionada', categoria);
    }

    // Função para controlar o scroll horizontal das tabs
    function scrollTabs(direction) {
        const container = document.querySelector('.tab-container');
        const scrollAmount = 200; // Quantidade de pixels para rolar
        
        if (direction === 'left') {
            container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        } else {
            container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        }
        
        // Atualiza o estado das setas após um pequeno delay
        setTimeout(updateArrowState, 100);
    }

    // Função para atualizar o estado (visibilidade) das setas
    function updateArrowState() {
        const container = document.querySelector('.tab-container');
        const scrollLeft = container.scrollLeft;
        const scrollWidth = container.scrollWidth;
        const clientWidth = container.clientWidth;
        
        const leftArrow = document.getElementById('scrollLeft');
        const rightArrow = document.getElementById('scrollRight');
        
        // Desabilita seta esquerda se estiver no início
        if (scrollLeft <= 0) {
            leftArrow.classList.add('disabled');
        } else {
            leftArrow.classList.remove('disabled');
        }
        
        // Desabilita seta direita se estiver no fim
        if (scrollLeft + clientWidth >= scrollWidth - 5) { // -5 para margem de erro
            rightArrow.classList.add('disabled');
        } else {
            rightArrow.classList.remove('disabled');
        }
    }

    // Função para atualizar a barra de scroll customizada
    function updateScrollBar() {
        const container = document.getElementById('tabContainer');
        const scrollThumb = document.getElementById('scrollThumb');
        const instruction = document.getElementById('scrollInstruction');
        
        if (!container || !scrollThumb) return;
        
        const scrollLeft = container.scrollLeft;
        const scrollWidth = container.scrollWidth;
        const clientWidth = container.clientWidth;
        
        // Calcula a porcentagem de scroll
        const scrollPercentage = scrollLeft / (scrollWidth - clientWidth);
        
        // Calcula o tamanho do thumb baseado na proporção visível
        const thumbWidth = (clientWidth / scrollWidth) * 100;
        
        // Calcula a posição do thumb
        const thumbPosition = scrollPercentage * (100 - thumbWidth);
        
        // Aplica os valores
        scrollThumb.style.width = thumbWidth + '%';
        scrollThumb.style.left = thumbPosition + '%';
        
        // Controla visibilidade da barra e instrução
        const indicator = document.querySelector('.tab-scroll-indicator');
        if (scrollWidth <= clientWidth) {
            indicator.style.opacity = '0.3';
            if (instruction) instruction.style.display = 'none';
        } else {
            indicator.style.opacity = '1';
            if (instruction && scrollLeft === 0) {
                instruction.style.display = 'flex';
            } else if (instruction && scrollLeft > 0) {
                instruction.style.display = 'none';
            }
        }
    }

    window.addEventListener('DOMContentLoaded', function() {
        // Inicializa o estado das setas e da barra
        updateArrowState();
        updateScrollBar();
        
        // Atualiza as setas e a barra quando o container for rolado
        const container = document.querySelector('.tab-container');
        if (container) {
            container.addEventListener('scroll', function() {
                updateArrowState();
                updateScrollBar();
            });
        }
        
        // Atualiza as setas e a barra quando a janela for redimensionada
        window.addEventListener('resize', function() {
            updateArrowState();
            updateScrollBar();
        });

        // Permite clicar na barra de scroll para navegar
        const scrollIndicator = document.querySelector('.tab-scroll-indicator');
        if (scrollIndicator) {
            scrollIndicator.addEventListener('click', function(e) {
                const rect = this.getBoundingClientRect();
                const clickX = e.clientX - rect.left;
                const percentage = clickX / rect.width;
                
                const container = document.getElementById('tabContainer');
                const scrollWidth = container.scrollWidth;
                const clientWidth = container.clientWidth;
                
                container.scrollLeft = percentage * (scrollWidth - clientWidth);
            });
        }

        // Permite arrastar o thumb da barra
        const scrollThumb = document.getElementById('scrollThumb');
        let isDragging = false;
        let startX = 0;
        let startScrollLeft = 0;

        if (scrollThumb) {
            scrollThumb.addEventListener('mousedown', function(e) {
                isDragging = true;
                startX = e.clientX;
                startScrollLeft = container.scrollLeft;
                scrollThumb.style.cursor = 'grabbing';
                e.preventDefault();
            });

            document.addEventListener('mousemove', function(e) {
                if (!isDragging) return;
                
                const indicator = document.querySelector('.tab-scroll-indicator');
                const rect = indicator.getBoundingClientRect();
                const deltaX = e.clientX - startX;
                const deltaPercentage = deltaX / rect.width;
                
                const scrollWidth = container.scrollWidth;
                const clientWidth = container.clientWidth;
                
                container.scrollLeft = startScrollLeft + (deltaPercentage * (scrollWidth - clientWidth));
            });

            document.addEventListener('mouseup', function() {
                if (isDragging) {
                    isDragging = false;
                    scrollThumb.style.cursor = 'grab';
                }
            });

            // Touch support para mobile
            scrollThumb.addEventListener('touchstart', function(e) {
                isDragging = true;
                startX = e.touches[0].clientX;
                startScrollLeft = container.scrollLeft;
                e.preventDefault();
            });

            document.addEventListener('touchmove', function(e) {
                if (!isDragging) return;
                
                const indicator = document.querySelector('.tab-scroll-indicator');
                const rect = indicator.getBoundingClientRect();
                const deltaX = e.touches[0].clientX - startX;
                const deltaPercentage = deltaX / rect.width;
                
                const scrollWidth = container.scrollWidth;
                const clientWidth = container.clientWidth;
                
                container.scrollLeft = startScrollLeft + (deltaPercentage * (scrollWidth - clientWidth));
            });

            document.addEventListener('touchend', function() {
                isDragging = false;
            });
        }

        // Esconde a instrução ao clicar nela ou após alguns segundos de inatividade
        const instruction = document.getElementById('scrollInstruction');
        if (instruction) {
            instruction.addEventListener('click', function() {
                this.style.display = 'none';
            });

            // Auto-esconde após 15 segundos se o usuário não interagir
            let instructionTimer = setTimeout(function() {
                if (instruction && container.scrollLeft === 0) {
                    instruction.style.transition = 'opacity 0.5s ease';
                    instruction.style.opacity = '0';
                    setTimeout(function() {
                        instruction.style.display = 'none';
                    }, 500);
                }
            }, 15000);

            // Reseta o timer se o usuário interagir
            container.addEventListener('scroll', function() {
                clearTimeout(instructionTimer);
            }, { once: true });
        }
        var abaSalva = localStorage.getItem('abaCarrinhoSelecionada');
        if (abaSalva) {
            var btn = document.querySelector('.tab button[onclick*="' + abaSalva + '"]');
            if (btn) {
                btn.click();
            }
        } else {
            var defaultBtn = document.getElementById('defaultOpen');
            if (defaultBtn) defaultBtn.click();
        }
    });
</script>

<?php echo $this->endSection() ?>