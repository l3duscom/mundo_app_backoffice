<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>


<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.css') ?>" />



<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>


<div class="row g-4 align-items-start">
    <!-- Coluna lateral esquerda -->
    <div class="col-lg-4 col-xl-3">
        <div class="card w-100 shadow bg-dark radius-10">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-12">
                        <h5> <img src="<?php echo site_url('recursos/img/dreamcoin.png'); ?>" alt="" class="rounded-circle" width="34" height="34">
                            Saldo da conta </h5>
                    </div>
                    <div class="row ">
                        <div class="col col-5">
                            <p class="mb-0 text-muted" style="font-size: 10px;">DREAMCOIN</p>
                            <h4 class="mb-0"><?php echo usuario_logado()->pontos; ?></h4>
                        </div>
                        <div class="col col-2">
                            <i class="bi bi-plus-lg text-muted" style="margin-left: -10px"></i>
                        </div>
                        <div class="col col-5">
                            <p class="mb-0 text-muted" style="font-size: 10px;">CASHBACK</p>
                            <h4 class="mb-0"><span style="font-size: 10px; margin-left: -20px"> R$ </span> <?php echo usuario_logado()->saldo; ?></h4>
                        </div>
                    </div><!--end row-->
                </div>
            </div>
        </div>
        <div class="d-grid mb-3">
            <a href="#meus-ingressos" class="btn btn-primary btn-lg fw-bold text-white shadow rounded-pill d-flex align-items-center justify-content-center gap-2" style="letter-spacing:0.5px;">
                <i class="bi bi-ticket-fill" style="font-size:1.2em;"></i> Ver meus ingressos
            </a>
        </div>
        <!-- Card Conquistas (mover para cima) -->
        <div class="card w-100 shadow bg-dark radius-10">
            <div class="card-body">
<div class="row">
                    <div class="col-lg-8">
                        <h5>Conquistas </h5>
                    </div>
                    <div class="col-lg-4">
                        <a href="javascript:;" class="btn btn-sm btn-outline-dark mb-3"></i>Todos</a>
                    </div>
                    <div class="row" style="margin: 5px;">
                    <?php if (usuario_logado()->is_membro) : ?>
                            <div class="col-3 font-35 shadow"> <i class=" bx bx-mouse-alt" style="color: #ffd700" title="Cadastro realizado"></i></div>
                            <div class="col-3 font-35 shadow"> <i class="bx bx-face" style="color: #ffd700" title="Pioneiro"></i></div>
                            <div class="col-3 font-35 shadow"> <i class="bx bx-crown" style="color: #ffd700" title="Premium"></i></div>
                    <?php else : ?>
                            <div class="col-3 font-35 shadow"> <i class=" bx bx-mouse-alt" style="color: #ffd700" title="Cadastro realizado"></i></div>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card w-100 shadow bg-dark radius-10 mb-3">
            <div class="card-body">
                <h5 class="mb-3 d-flex align-items-center">Endereços cadastrados
                    <a href="https://dreamfest.com.br/central-de-ajuda/como-alterar-o-endereco-de-entrega-da-minha-credencial/" target="_blank" rel="noopener noreferrer" class="ms-2 small fw-normal text-muted saiba-mais-link" style="text-decoration:none;">Saiba mais</a>
                </h5>
                <style>
                .saiba-mais-link:hover { text-decoration: underline; color:rgb(185, 13, 253) !important; }
                </style>
                <?php foreach ($enderecos_lista as $idx => $end): ?>
                    <div class="p-3 mb-2 rounded" style="background:<?= $idx === 0 ? '#232b3b' : 'transparent' ?>; border:1px solid #232b3b;">
                        <div class="d-flex align-items-center mb-1">
                            <span class="me-2" style="font-size:1.2rem;color:#0d6efd;"><i class="bi bi-geo-alt-fill"></i></span>
                            <span class="fw-semibold" style="font-size:1.05rem;">
                                <?= esc($end['endereco']) ?>
                                <?php if (!empty($end['numero'])): ?>, <?= esc($end['numero']) ?><?php endif; ?>
                                <?php if (!empty($end['bairro'])): ?> - <?= esc($end['bairro']) ?><?php endif; ?>
                                <?php if (!empty($end['cidade'])): ?>, <?= ucfirst(esc($end['cidade'])) ?><?php endif; ?>
                                <?php if (!empty($end['estado'])): ?>/<?= strtoupper(esc($end['estado'])) ?><?php endif; ?>
                                <?php if (!empty($end['cep'])): ?> - <?= esc($end['cep']) ?><?php endif; ?>
                            </span>
                            <?php if (!empty($end['default'])): ?>
                                <span class="badge bg-primary ms-2"><i class="bi bi-star-fill me-1"></i>Padrão</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($idx === 0): ?>
                            <div class="text-muted small mt-1 ms-4">
                                <i class="bi bi-info-circle"></i>
                                Se nenhum endereço for vinculado ao pedido, ele será enviado para o endereço padrão.
                                <a href="<?= site_url('usuarios/perfil') ?>" class="ms-1">Editar perfil</a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div id="meus-ingressos"></div>
        <?php if ($card == null) : ?>
            <div class="card w-100 shadow bg-dark radius-10">
                <div class="card-body">
                    <h5>Seu DreamCard </h5>
                    Você ainda não solicitou o seu cartão de membro!
                </div>
                <div class="d-grid" style="padding: 10px;">
                    <a href="#" target="_blank" class="btn btn-primary disabled">Solicitar cartão</a>
                </div>
            </div>
        <?php else : ?>
            <div class="card w-100 shadow bg-purple radius-10">
                <div class="card-body">
                    <h5>Seu DreamCard <span class="badge bg-success"><?= $card->status ?></span></h5>
                    <div class="d-flex align-items-center gap-3">
                        <div class="fs-1">
                            <i class="bi bi-credit-card-2-back-fill"></i>
                        </div>
                        <div class="">
                            <p class="mb-0 fs-6"><strong><?= $card->matricula ?></strong> </p>
                        </div>
                    </div>
                    <?php echo esc(usuario_logado()->nome); ?><br>
                    Expira em: <?= date("d/m/Y", strtotime($card->expiration)) ?>
                </div>
            </div>
            <?php if ($card->status == 'Confecção') : ?>
                <div class="col-lg-12">
                    <div class="row" style="padding-left: 20px; padding-right: 20px; padding-bottom: 20px; margin-top: -16px">
                        <a href="<?= site_url('/pedidos/recebercartao') ?>" class="btn btn-outline-danger bt-sm btn-block">Receber meu cartão em casa!</a>
                    </div>
                </div>
            <?php elseif ($card->status == 'Enviado') : ?>
                <div class="col-lg-12">
                    <div class="row" style="padding-left: 20px; padding-right: 20px; padding-bottom: 20px; margin-top: -16px">
                        <a href="https://melhorrastreio.com.br/rastreio/<?= $card->rastreio ?>" target="_blank" class="btn btn-outline-success bt-sm btn-block">Acompanhe a entrega</a>
                    </div>
                </div>
            <?php elseif ($card->status == 'Preparando') : ?>
                <div class="col-lg-12">
                    <div class="row" style="padding-left: 20px; padding-right: 20px; padding-bottom: 20px; margin-top: -16px">
                        <a href="<?= site_url('/pedidos/recebercartao') ?>" class="btn btn-outline-success bt-sm btn-block disabled">Aguardando rastreio</a>
                    </div>
    </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>
    
    <!-- Conteúdo principal à direita -->
    <div class="col-lg-8 col-xl-9">
        <!-- Todo o restante da dashboard (ingressos, check-in, etc.) -->
        
        <!-- Coloque a ancora aqui, antes das abas -->
        



   
            
                <div class="row">
                    <?php if(usuario_logado()->temPermissaoPara('access-controll')): ?>
                        <div class="col-lg-6">
                            <div class="card shadow radius-10">
                                <div class="card-body">
                                    <a href="<?= site_url('/acessos/bilheteria') ?>" class="btn btn-success mt-2 shadow w-100"> Bilheteria</a>
                                </div>
                            </div>
                        </div>    
                    <?php endif ?>  
                    <?php if(usuario_logado()->temPermissaoPara('juri')): ?>
                        <div class="col-lg-6">
                            <div class="card shadow radius-10">
                                <div class="card-body">
                                    <a href="<?= site_url('/concursos') ?>" class="btn btn-success mt-2 shadow w-100"> Gerenciar concursos</a>
                                </div>
                            </div>
                        </div>    
                    <?php endif ?>           
                </div>
            
        
            <div class="d-flex align-items-center">
                <div class="flex-grow-1" style="padding-right: 20px;">
                <div class="col-lg-9">
            
        <!-- Nav tabs -->
        <ul class="nav nav-tabs mb-3" id="ingressosTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="atuais-tab" data-bs-toggle="tab" data-bs-target="#atuais" type="button" role="tab" aria-controls="atuais" aria-selected="true">Ingressos Atuais</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="anteriores-tab" data-bs-toggle="tab" data-bs-target="#anteriores" type="button" role="tab" aria-controls="anteriores" aria-selected="false">Ingressos Anteriores</button>
            </li>
        </ul>
        <div class="tab-content" id="ingressosTabsContent">
            <div class="tab-pane fade show active" id="atuais" role="tabpanel" aria-labelledby="atuais-tab">
                <?php if (!empty($ingressos_atuais)) : ?>
                    <?php foreach ($ingressos_atuais as $i) : ?>
                        <?php /* Card de ingresso (código já existente) */ ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card shadow radius-10">
                                        <div class="card-body">
                                            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                                                <div>
                                                    <?php if ($i->tipo == 'produto') : ?>
                                                        <strong class="font-20">Produto - Não válido para acesso</strong>
                                                        <hr>
                                                    <?php endif ?>
                                                    <strong class="font-20"><?= $i->nome_evento ?></strong><br>
                                                    <span class="text-muted font-13 mt-0"> Data do Evento: <?= date('d/m/Y', strtotime($i->data_inicio)) ?> - <?= date('d/m/Y', strtotime($i->data_fim)) ?> das 10h às 19h
                                                    </span>
                                                </div>
                                                <?php if ($i->tipo != 'produto') : ?>
                                                <div class="ms-auto">
                                                    <div class="btn-group mt-2 w-100">
                                                        <a href="<?= site_url('/ingressos/gerarIngressoPdf/' . $i->id) ?>" target="_blank" class="btn btn-sm btn-warning mt-0 shadow"><i class="bx bx-printer"></i></a>
                                                        <a href="#" class="btn btn-sm btn-primary mt-0 shadow w-100" data-bs-toggle="modal" data-bs-target="#participante<?= $i->id; ?>Modal"><i class="bx bx-user" style="padding-right: 5px;"></i> Editar participante</a>
                                                    </div>
                                                <?php if (strpos($i->nome, 'VIP') !== false || strpos($i->nome, 'EPIC') !== false || true) : ?>
                                                    <?php if (!isset($perfil_incompleto) || !$perfil_incompleto): ?>
                                                        <a href="<?= site_url('/console/meet/' . $i->id) ?>" class="btn btn-success mt-2 shadow w-100"><i class='bx bx-camera'></i> Check in Meet & Greet</a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                </div>
                                                <?php endif ?>
                                            </div>
                                            <div class="row">
                                                <hr class="mt-2">
                                                <div class="col-lg-9">
                                                        <div class="col-lg-8">
                                                            <?php if ($i->tipo == 'produto') : ?>
                                                                <p class="mb-0 text-muted" style="font-size: 10px;">Produto</p>
                                                            <?php else : ?>
                                                                <p class="mb-0 text-muted" style="font-size: 10px;">Ingresso</p>
                                                            <?php endif ?>
                                                            <strong><?= $i->nome ?></strong>
                                                        </div>
                                                        <div class="col-lg-3">
                                                            <?php if ($i->tipo == 'produto') : ?>
                                                                <p class="mb-0 text-muted" style="font-size: 10px;">Código do produto</p>
                                                            <?php else : ?>
                                                                <p class="mb-0 text-muted" style="font-size: 10px;">Código do ingresso</p>
                                                            <?php endif ?>
                                                            <strong><?= $i->codigo ?></strong>
                                                        </div>
                                                        <div class="col-lg-12 mt-3"></div>
                                                        <div class="col-lg-3">
                                                            <p class="mb-0 text-muted" style="font-size: 10px;">Nome</p>
                                                            <?php if ($i->participante == null) : ?>
                                                                <strong><?= $cliente->nome ?></strong>
                                                            <?php else : ?>
                                                                <strong><?= $i->participante ?></strong>
                                                            <?php endif ?>
                                                        </div>
                                                        <div class="col-lg-3">
                                                            <p class="mb-0 text-muted" style="font-size: 10px;">Acesso</p>
                                                            <strong><?= $i->frete == null || $i->frete == 0 ? "Retirar no local" : "Receber em casa" ?></strong>
                                                        </div>
                                                        <div class="col-lg-3">
                                                            <p class="mb-0 text-muted" style="font-size: 10px;">Comprovante</p>
                                                            <strong>
                                                                <?php if ($i->comprovante != null) : ?>
                                                                    <strong><a href="<?= $i->comprovante ?>" target="_blank">Baixar comprovante</a></strong>
                                                                <?php else : ?>
                                                                    <strong>Indiponível</strong>
                                                                <?php endif ?>
                                                            </strong>
                                                        </div>
                                                        <hr class="mt-2">
                                                        <div class="col-lg-12">
                                                            <?php if ($i->cinemark != null) : ?>
                                                                <strong style="color: #ffcc00">Seu ingresso CINEMARK já está disponível!</strong><br>
                                                                Como usar o Cinemark Voucher:<br>
                                                                1 - Atualize ou baixe o APP Cinemark no Google Play ou APP Store.<br>
                                                                2 - Faça seu login, selecione o cinema, filme de sua preferência.<br>
                                                                3 - Selecione o horário da sessão e os assentos;<br>
                                                                4 - Selecione o tipo de ingresso como Voucher e quantidade de ingressos que irá utilizar;<br>
                                                                5 - Após isso, digite o código <strong style="font-size: 16px; color: #ffcc00"> <?= $i->cinemark ?></strong> do voucher que irá utilizar.<br>
                                                                6 - Apresente seu voucher online no celular diretamente na entrada da sala do cinema.<br>
                                                                <strong>Validade de 2 meses</strong>
                                                                <hr class="mt-2">
                                                            <?php endif ?>
                                                            <?php if ($i->frete == 1) : ?>
                                                                <p class="mb-1">Incrível, você optou por receber seus ingressos em casa! Acompanhe a entrega:</p>
                                                                <div class="btn-group mt-2">
                                                                    <?php if ($i->rastreio != null) : ?>
                                                                        <a href="https://rastreamento.correios.com.br/app/index.php?objetos=<?= $i->rastreio ?>" target="_blank" class="btn bt-sm btn btn-sm btn-outline-success mt-0 shadow ">Acompanhe a entrega</a>
                                                                    <?php else : ?>
                                                                        <a href="https://rastreamento.correios.com.br/app/index.php?objetos=<?= $i->rastreio ?>" target="_blank" class="btn bt-sm btn btn-sm btn-outline-white mt-0 shadow disabled">Seu pedido está sendo preparado!</a>
                                                                    <?php endif ?>
                                                                    <a href="<?= site_url('/pedidos/gerenciarendereco/' . $i->pedido_id) ?>" class="btn btn-sm btn-primary mt-0 shadow">Gerenciar endereços</a>
                                                                </div>
                                                                <?php if ($i->rastreio == null) : ?>
                                                                    <p class="mb-0 text-muted" style="font-size: 10px; padding-left:10px; padding-top: 2px"><a href="https://dreamfest.com.br/central-de-ajuda/quando-eu-vou-receber-minhas-credenciais/" target="_blank">Quando vou receber minhas credenciais?</a></p>
                                                                <?php endif ?>
                                                    <?php endif ?>
                                                </div>
                                                </div>
                                                <div class="col-lg-3 mt-3">
                                                    <?php if ($i->tipo == 'produto') : ?>
                                                        <center><strong class="font-20" style="color:red;">Não válido para acesso</strong></center>
                                                    <?php endif ?>
                                                    <img src="<?= $i->qr ?>" style="background-color:#fff; padding:0px" width="100%">
                                                </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal participante -->
                            <div class="modal fade bd-example-modal-lg" id="participante<?= $i->id; ?>Modal" tabindex="-1" role="dialog" aria-labelledby="participante<?= $i->id; ?>Modal" aria-hidden="true">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="participante<?= $i->id; ?>Modal">Gerenciamento de ingresso</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <?php echo form_open("ingressos/atualizar/$i->id") ?>
                                            <?= csrf_field() ?>
                                            <div class="form-group col-md-12">
                                                <label class="form-control-label">Informe o nome do novo participante</label>
                                                <input type="text" name="participante" class="form-control">
                                            </div>
                                            <p class="text-muted font-13"> Este é o nome que aparecerá no seu ingresso!</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                            <input id="btn-salvar" type="submit" value="Alterar" class="btn btn-primary btn-sm">
                                        </div>
                                        <?php echo form_close(); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <center>
                            <hr>
                        <p>Nenhum ingresso atual encontrado</p>
                        <a href="<?= site_url('/') ?>" target="_blank" class="btn btn-primary">Comprar ingresso!</a>
                            <hr>
                        </center>
                    <?php endif; ?>
            </div>
            <div class="tab-pane fade" id="anteriores" role="tabpanel" aria-labelledby="anteriores-tab">
                <?php if (!empty($ingressos_anteriores)) : ?>
                    <?php foreach ($ingressos_anteriores as $i) : ?>
                        <!-- Card de ingresso igual ao de atuais -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card shadow radius-10">
                                    <div class="card-body">
                                        <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                                            <div>
                                                <?php if ($i->tipo == 'produto') : ?>
                                                    <strong class="font-20">Produto - Não válido para acesso</strong>
                                                    <hr>
                                                <?php endif ?>
                                                <strong class="font-20"><?= $i->nome_evento ?></strong><br>
                                                <span class="text-muted font-13 mt-0"> Data do Evento: <?= date('d/m/Y', strtotime($i->data_inicio)) ?> - <?= date('d/m/Y', strtotime($i->data_fim)) ?> das 10h às 19h
                                                </span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <hr class="mt-2">
                                            <div class="col-lg-9">
                                                <div class="col-lg-8">
                                                    <?php if ($i->tipo == 'produto') : ?>
                                                        <p class="mb-0 text-muted" style="font-size: 10px;">Produto</p>
                                                    <?php else : ?>
                                                        <p class="mb-0 text-muted" style="font-size: 10px;">Ingresso</p>
                                                    <?php endif ?>
                                                    <strong><?= $i->nome ?></strong>
        </div>
        <div class="col-lg-3">
                                                    <?php if ($i->tipo == 'produto') : ?>
                                                        <p class="mb-0 text-muted" style="font-size: 10px;">Código do produto</p>
                                                    <?php else : ?>
                                                        <p class="mb-0 text-muted" style="font-size: 10px;">Código do ingresso</p>
                                                    <?php endif ?>
                                                    <strong><?= $i->codigo ?></strong>
                        </div>
                                                <div class="col-lg-12 mt-3"></div>
                                                <div class="col-lg-3">
                                                    <p class="mb-0 text-muted" style="font-size: 10px;">Nome</p>
                                                    <?php if ($i->participante == null) : ?>
                                                        <strong><?= $cliente->nome ?></strong>
                                                    <?php else : ?>
                                                        <strong><?= $i->participante ?></strong>
                                                    <?php endif ?>
                            </div>
                                                <div class="col-lg-3">
                                                    <p class="mb-0 text-muted" style="font-size: 10px;">Acesso</p>
                                                    <strong><?= $i->frete == null || $i->frete == 0 ? "Retirar no local" : "Receber em casa" ?></strong>
                            </div>
                                                <div class="col-lg-3">
                                                    <p class="mb-0 text-muted" style="font-size: 10px;">Comprovante</p>
                                                    <strong>
                                                        <?php if ($i->comprovante != null) : ?>
                                                            <strong><a href="<?= $i->comprovante ?>" target="_blank">Baixar comprovante</a></strong>
                                                        <?php else : ?>
                                                            <strong>Indiponível</strong>
                                                        <?php endif ?>
                                                    </strong>
                            </div>
                                                <hr class="mt-2">
                                                <div class="col-lg-12">
                                                    <?php if ($i->cinemark != null) : ?>
                                                        <strong style="color: #ffcc00">Seu ingresso CINEMARK já está disponível!</strong><br>
                                                        Como usar o Cinemark Voucher:<br>
                                                        1 - Atualize ou baixe o APP Cinemark no Google Play ou APP Store.<br>
                                                        2 - Faça seu login, selecione o cinema, filme de sua preferência.<br>
                                                        3 - Selecione o horário da sessão e os assentos;<br>
                                                        4 - Selecione o tipo de ingresso como Voucher e quantidade de ingressos que irá utilizar;<br>
                                                        5 - Após isso, digite o código <strong style="font-size: 16px; color: #ffcc00"> <?= $i->cinemark ?></strong> do voucher que irá utilizar.<br>
                                                        6 - Apresente seu voucher online no celular diretamente na entrada da sala do cinema.<br>
                                                        <strong>Validade de 2 meses</strong>
                                                        <hr class="mt-2">
                                                    <?php endif ?>
                                                    <?php if ($i->frete == 1) : ?>
                                                        <p class="mb-1">Incrível, você optou por receber seus ingressos em casa! Acompanhe a entrega:</p>
                                                        <div class="btn-group mt-2">
                                                            <?php if ($i->rastreio != null) : ?>
                                                                <a href="https://rastreamento.correios.com.br/app/index.php?objetos=<?= $i->rastreio ?>" target="_blank" class="btn bt-sm btn btn-sm btn-outline-success mt-0 shadow ">Acompanhe a entrega</a>
                                                            <?php else : ?>
                                                                <a href="https://rastreamento.correios.com.br/app/index.php?objetos=<?= $i->rastreio ?>" target="_blank" class="btn bt-sm btn btn-sm btn-outline-white mt-0 shadow disabled">Seu pedido está sendo preparado!</a>
                                                            <?php endif ?>
                                                            <a href="<?= site_url('/pedidos/gerenciarendereco/' . $i->pedido_id) ?>" class="btn btn-sm btn-primary mt-0 shadow">Gerenciar endereços</a>
                    </div>
                                                        <?php if ($i->rastreio == null) : ?>
                                                            <p class="mb-0 text-muted" style="font-size: 10px; padding-left:10px; padding-top: 2px"><a href="https://dreamfest.com.br/central-de-ajuda/quando-eu-vou-receber-minhas-credenciais/" target="_blank">Quando vou receber minhas credenciais?</a></p>
                                                        <?php endif ?>
                                                    <?php endif ?>
                </div>
            </div>
                                            <div class="col-lg-3 mt-3">
                                                <?php if ($i->tipo == 'produto') : ?>
                                                    <center><strong class="font-20" style="color:red;">Não válido para acesso</strong></center>
                                                <?php endif ?>
                                                <img src="<?= $i->qr ?>" style="background-color:#fff; padding:0px" width="100%">
                    </div>
                    </div>
                </div>
                            </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <center>
                        <hr>
                        <p>Nenhum ingresso anterior encontrado</p>
                        <a href="<?= site_url('/') ?>" target="_blank" class="btn btn-primary">Comprar ingresso!</a>
                        <hr>
                    </center>
                <?php endif; ?>
            </div>
        </div>
        <a href="<?= site_url('/ingressos') ?>" class="btn btn-sm btn-dark w-100 mt-0 mb-3 shadow">Acessar todos os meus ingressos</a>
    </div>
</div>

<?php if (isset($perfil_incompleto) && $perfil_incompleto): ?>
<!-- Modal Bootstrap -->
<div class="modal fade" id="modalPerfilIncompleto" tabindex="-1" aria-labelledby="modalPerfilIncompletoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-white border-0 justify-content-center">
        <div class="w-100 text-center">
          <span style="font-size:2.5rem;color:#ffc107;"><i class="bi bi-exclamation-triangle-fill"></i></span>
          <h5 class="modal-title mt-2 mb-0 fw-bold" id="modalPerfilIncompletoLabel" style="color:#333;">Complete seu perfil</h5>
        </div>
      </div>
      <div class="modal-body text-center pb-4 pt-2" style="background:#fff;">
          <p class="mb-3" style="color:#333; font-size:1.1rem;">
              Para acessar todos os recursos, por favor complete seu perfil:
          </p>
          <ul class="list-unstyled mb-4" style="color:#222; font-size:1rem;">
              <?php foreach (
                  $campos_faltando as $campo): ?>
                  <li class="mb-2">
                      <i class="bi bi-x-circle text-danger" style="font-size:1.2rem;vertical-align:middle;"></i>
                      <span style="margin-left:6px;"><?= ucfirst($campo) ?> em branco</span>
                  </li>
              <?php endforeach; ?>
          </ul>
          <a href="<?= site_url('usuarios/perfil') ?>" class="btn btn-primary btn-lg px-4 mt-2">Editar perfil</a>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php echo $this->endSection() ?>


<?php echo $this->section('scripts') ?>


<script type="text/javascript" src="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.js') ?>"></script>



<script>
    $(document).ready(function() {


        const DATATABLE_PTBR = {
            "sEmptyTable": "Nenhum registro encontrado",
            "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
            "sInfoFiltered": "(Filtrados de _MAX_ registros)",
            "sInfoPostFix": "",
            "sInfoThousands": ".",
            "sLengthMenu": "_MENU_ resultados por página",
            "sLoadingRecords": "Carregando...",
            "sProcessing": "Processando...",
            "sZeroRecords": "Nenhum registro encontrado",
            "sSearch": "Pesquisar",
            "oPaginate": {
                "sNext": "Próximo",
                "sPrevious": "Anterior",
                "sFirst": "Primeiro",
                "sLast": "Último"
            },
            "oAria": {
                "sSortAscending": ": Ordenar colunas de forma ascendente",
                "sSortDescending": ": Ordenar colunas de forma descendente"
            },
            "select": {
                "rows": {
                    "_": "Selecionado %d linhas",
                    "0": "Nenhuma linha selecionada",
                    "1": "Selecionado 1 linha"
                }
            }
        }


        $(' #ajaxTable').DataTable({
            "oLanguage": DATATABLE_PTBR,
            "ajax": "<?php echo site_url('declarations/recuperaDeclaracoesPorUsuario'); ?>",
            "columns": [{
                "data": "nome"
            }, {
                "data": "month"
            }, {
                "data": "type"
            }, {
                "data": "status"
            }, {
                "data": "created_at"
            }, ],
            "order": [],
            "deferRender": true,
            "processing": true,
            "language": {
                processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',
            },
            "responsive": true,
            "pagingType": $(window).width() < 768 ? "simple" : "simple_numbers",
        });
    });
</script>

<?php if (isset($perfil_incompleto) && $perfil_incompleto): ?>
<script>
$(function(){
    $('#modalPerfilIncompleto').modal('show');
});
</script>
<?php endif; ?>

<?php echo $this->endSection() ?>