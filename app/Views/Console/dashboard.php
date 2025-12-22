<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>


<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.css') ?>" />



<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>

<?php if (usuario_logado()->is_parceiro) : ?>
<div class="container-fluid py-4">

    <?php if (!isset($expositor) || !$expositor) : ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Seu cadastro de expositor ainda não está vinculado à sua conta. Entre em contato com o suporte.
    </div>
    <?php else : ?>

    <!-- Header de boas-vindas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body py-4">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-white bg-opacity-25 p-3 me-3">
                            <i class="bi bi-building text-white" style="font-size: 2rem;"></i>
                        </div>
                        <div>
                            <h3 class="text-white mb-1">Olá, <?= esc($expositor->nome_fantasia ?? $expositor->nome ?? 'Parceiro') ?>!</h3>
                            <p class="text-white-50 mb-0">Bem-vindo ao seu painel de parceiro</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Abas de Eventos -->
    <ul class="nav nav-tabs mb-4" id="eventosTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="ativos-tab" data-bs-toggle="tab" data-bs-target="#ativos" type="button" role="tab" aria-controls="ativos" aria-selected="true">
                <i class="bi bi-calendar-check me-2"></i>Eventos Ativos
                <?php if (isset($eventos_ativos) && !empty($eventos_ativos)) : ?>
                <span class="badge bg-primary ms-1"><?= count($eventos_ativos) ?></span>
                <?php endif; ?>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="anteriores-tab" data-bs-toggle="tab" data-bs-target="#anteriores-eventos" type="button" role="tab" aria-controls="anteriores-eventos" aria-selected="false">
                <i class="bi bi-calendar-x me-2"></i>Eventos Anteriores
                <?php if (isset($eventos_anteriores) && !empty($eventos_anteriores)) : ?>
                <span class="badge bg-secondary ms-1"><?= count($eventos_anteriores) ?></span>
                <?php endif; ?>
            </button>
        </li>
    </ul>

    <div class="tab-content" id="eventosTabContent">
        <!-- Eventos Ativos -->
        <div class="tab-pane fade show active" id="ativos" role="tabpanel" aria-labelledby="ativos-tab">
            <?php if (!isset($eventos_ativos) || empty($eventos_ativos)) : ?>
            <div class="text-center py-5">
                <i class="bi bi-calendar-plus text-muted" style="font-size: 4rem;"></i>
                <h5 class="mt-3 text-muted">Nenhum evento ativo no momento</h5>
                <p class="text-muted">Quando você tiver contratos para eventos futuros, eles aparecerão aqui.</p>
            </div>
            <?php else : ?>
            <?php foreach ($eventos_ativos as $eventoData) : ?>
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-dark border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1 text-white">
                                <i class="bi bi-calendar-event me-2"></i><?= esc($eventoData['evento']->nome) ?>
                            </h5>
                            <small class="text-muted">
                                <?= date('d/m/Y', strtotime($eventoData['evento']->data_inicio)) ?> 
                                - <?= date('d/m/Y', strtotime($eventoData['evento']->data_fim)) ?>
                            </small>
                        </div>
                        <span class="badge bg-success">Ativo</span>
                    </div>
                </div>
                <div class="card-body">
                    <?php foreach ($eventoData['contratos'] as $contratoData) : ?>
                    <?php $contrato = $contratoData['contrato']; $parcelas = $contratoData['parcelas']; $totais = $contratoData['totais']; ?>
                    <div class="border rounded p-3 mb-3" style="background: rgba(255,255,255,0.02);">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <small class="text-muted d-block">Contrato</small>
                                <strong><?= esc($contrato->codigo) ?></strong>
                                <?php if ($contrato->descricao) : ?>
                                <p class="text-muted small mb-0"><?= esc($contrato->descricao) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-2 text-center">
                                <small class="text-muted d-block">Valor Total</small>
                                <strong class="text-success">R$ <?= number_format($contrato->valor_final, 2, ',', '.') ?></strong>
                            </div>
                            <div class="col-md-2 text-center">
                                <small class="text-muted d-block">Parcelas</small>
                                <strong><?= $totais['pagas'] ?>/<?= $totais['quantidade'] ?> pagas</strong>
                            </div>
                            <div class="col-md-2 text-center">
                                <small class="text-muted d-block">Situação</small>
                                <?php
                                $situacaoClass = match($contrato->situacao) {
                                    'pago' => 'bg-success',
                                    'ativo', 'assinado' => 'bg-primary',
                                    'pendente', 'proposta' => 'bg-warning',
                                    'cancelado', 'banido' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?= $situacaoClass ?>"><?= ucfirst(esc($contrato->situacao)) ?></span>
                            </div>
                            <div class="col-md-2 text-end">
                                <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#parcelas<?= $contrato->id ?>" aria-expanded="false">
                                    <i class="bi bi-list-ul"></i> Ver Parcelas
                                </button>
                            </div>
                        </div>
                        
                        <!-- Accordion de Parcelas -->
                        <div class="collapse mt-3" id="parcelas<?= $contrato->id ?>">
                            <div class="table-responsive">
                                <table class="table table-sm table-dark table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Vencimento</th>
                                            <th>Valor</th>
                                            <th>Status</th>
                                            <th>Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($parcelas as $parcela) : ?>
                                        <tr>
                                            <td><?= $parcela->numero_parcela ?></td>
                                            <td><?= date('d/m/Y', strtotime($parcela->data_vencimento)) ?></td>
                                            <td>R$ <?= number_format($parcela->valor, 2, ',', '.') ?></td>
                                            <td>
                                                <?php
                                                $statusClass = match($parcela->status_local) {
                                                    'pago' => 'bg-success',
                                                    'pendente' => 'bg-warning text-dark',
                                                    'vencido' => 'bg-danger',
                                                    'cancelado' => 'bg-dark',
                                                    default => 'bg-secondary'
                                                };
                                                ?>
                                                <span class="badge <?= $statusClass ?>"><?= ucfirst($parcela->status_local) ?></span>
                                                <?php if ($parcela->data_pagamento) : ?>
                                                <small class="text-muted d-block">Pago em <?= date('d/m/Y', strtotime($parcela->data_pagamento)) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (in_array($parcela->status_local, ['pendente', 'vencido']) && $parcela->asaas_payment_id) : ?>
                                                <a href="https://www.asaas.com/i/<?= $parcela->asaas_payment_id ?>" target="_blank" class="btn btn-sm btn-success">
                                                    <i class="bi bi-credit-card me-1"></i>Pagar
                                                </a>
                                                <?php elseif ($parcela->status_local === 'pago' && $parcela->comprovante_url) : ?>
                                                <a href="<?= $parcela->comprovante_url ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-file-earmark-pdf me-1"></i>Comprovante
                                                </a>
                                                <?php else : ?>
                                                <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="table-secondary">
                                        <tr>
                                            <td colspan="2"><strong>Totais</strong></td>
                                            <td><strong>R$ <?= number_format($totais['total'], 2, ',', '.') ?></strong></td>
                                            <td colspan="2">
                                                <span class="badge bg-success me-1">Pago: R$ <?= number_format($totais['pago'], 2, ',', '.') ?></span>
                                                <?php if ($totais['pendente'] > 0) : ?>
                                                <span class="badge bg-warning text-dark">Pendente: R$ <?= number_format($totais['pendente'], 2, ',', '.') ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Eventos Anteriores -->
        <div class="tab-pane fade" id="anteriores-eventos" role="tabpanel" aria-labelledby="anteriores-tab">
            <?php if (!isset($eventos_anteriores) || empty($eventos_anteriores)) : ?>
            <div class="text-center py-5">
                <i class="bi bi-archive text-muted" style="font-size: 4rem;"></i>
                <h5 class="mt-3 text-muted">Nenhum evento anterior encontrado</h5>
                <p class="text-muted">O histórico de eventos passados aparecerá aqui.</p>
            </div>
            <?php else : ?>
            <?php foreach ($eventos_anteriores as $eventoData) : ?>
            <div class="card mb-4 shadow-sm border-0" style="opacity: 0.85;">
                <div class="card-header bg-secondary border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1 text-white">
                                <i class="bi bi-calendar-event me-2"></i><?= esc($eventoData['evento']->nome) ?>
                            </h5>
                            <small class="text-white-50">
                                <?= date('d/m/Y', strtotime($eventoData['evento']->data_inicio)) ?> 
                                - <?= date('d/m/Y', strtotime($eventoData['evento']->data_fim)) ?>
                            </small>
                        </div>
                        <span class="badge bg-dark">Encerrado</span>
                    </div>
                </div>
                <div class="card-body">
                    <?php foreach ($eventoData['contratos'] as $contratoData) : ?>
                    <?php $contrato = $contratoData['contrato']; $parcelas = $contratoData['parcelas']; $totais = $contratoData['totais']; ?>
                    <div class="border rounded p-3 mb-3" style="background: rgba(255,255,255,0.02);">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <small class="text-muted d-block">Contrato</small>
                                <strong><?= esc($contrato->codigo) ?></strong>
                            </div>
                            <div class="col-md-2 text-center">
                                <small class="text-muted d-block">Valor Total</small>
                                <strong>R$ <?= number_format($contrato->valor_final, 2, ',', '.') ?></strong>
                            </div>
                            <div class="col-md-2 text-center">
                                <small class="text-muted d-block">Parcelas</small>
                                <strong><?= $totais['pagas'] ?>/<?= $totais['quantidade'] ?> pagas</strong>
                            </div>
                            <div class="col-md-2 text-center">
                                <small class="text-muted d-block">Situação</small>
                                <?php
                                $situacaoClass = match($contrato->situacao) {
                                    'pago' => 'bg-success',
                                    'ativo', 'assinado' => 'bg-primary',
                                    'pendente', 'proposta' => 'bg-warning',
                                    'cancelado', 'banido' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?= $situacaoClass ?>"><?= ucfirst(esc($contrato->situacao)) ?></span>
                            </div>
                            <div class="col-md-2 text-end">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#parcelasAnt<?= $contrato->id ?>" aria-expanded="false">
                                    <i class="bi bi-list-ul"></i> Ver Histórico
                                </button>
                            </div>
                        </div>
                        
                        <!-- Accordion de Parcelas -->
                        <div class="collapse mt-3" id="parcelasAnt<?= $contrato->id ?>">
                            <div class="table-responsive">
                                <table class="table table-sm table-dark table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Vencimento</th>
                                            <th>Valor</th>
                                            <th>Status</th>
                                            <th>Data Pgto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($parcelas as $parcela) : ?>
                                        <tr>
                                            <td><?= $parcela->numero_parcela ?></td>
                                            <td><?= date('d/m/Y', strtotime($parcela->data_vencimento)) ?></td>
                                            <td>R$ <?= number_format($parcela->valor, 2, ',', '.') ?></td>
                                            <td>
                                                <?php
                                                $statusClass = match($parcela->status_local) {
                                                    'pago' => 'bg-success',
                                                    'pendente' => 'bg-warning text-dark',
                                                    'vencido' => 'bg-danger',
                                                    default => 'bg-secondary'
                                                };
                                                ?>
                                                <span class="badge <?= $statusClass ?>"><?= ucfirst($parcela->status_local) ?></span>
                                            </td>
                                            <td><?= $parcela->data_pagamento ? date('d/m/Y', strtotime($parcela->data_pagamento)) : '-' ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php endif; ?>
</div>
<?php else : ?>

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
            <a href="#meus-ingressos" class="btn btn-primary btn-lg fw-bold text-white shadow rounded-pill d-flex align-items-center justify-content-center gap-2 mb-2" style="letter-spacing:0.5px; font">
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
                                                    <span class="text-muted font-13 mt-0"> Data do Evento: <?= ($i->data_inicio instanceof DateTimeInterface)
    ? $i->data_inicio->format('d/m/Y')
    : (($dt = DateTime::createFromFormat('!Y-m-d', (string)$i->data_inicio)) ? $dt->format('d/m/Y') : '') ?>
 - <?= ($i->data_inicio instanceof DateTimeInterface)
    ? $i->data_inicio->format('d/m/Y')
    : (($dt = DateTime::createFromFormat('!Y-m-d', (string)$i->data_fim)) ? $dt->format('d/m/Y') : '') ?>
 das 11h às 20h
                                                    </span>
                                                </div>
                                                <?php if ($i->tipo != 'produto') : ?>
                                                <div class="ms-auto">
                                                    <div class="btn-group mt-2 w-100">
                                                        <a href="<?= site_url('/ingressos/gerarIngressoPdf/' . $i->id) ?>" target="_blank" class="btn btn-sm btn-warning mt-0 shadow"><i class="bx bx-printer"></i></a>
                                                        <a href="#" class="btn btn-sm btn-primary mt-0 shadow w-100" data-bs-toggle="modal" data-bs-target="#participante<?= $i->id; ?>Modal"><i class="bx bx-user" style="padding-right: 5px;"></i> Editar participante</a>
                                                    </div>
                                                
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
                                                            <?php 
                                                            $bonusModel = new \App\Models\BonusModel();
                                                            $bonus_cinemark = $bonusModel->getCinemarkPorIngresso($i->id);
                                                            ?>
                                                            <?php if ($bonus_cinemark != null) : ?>
                                                                <strong style="color: #ffcc00">Seu ingresso CINEMARK já está disponível!</strong><br>
                                                                <small class="text-muted">Como usar o Cinemark Voucher:</small><br>
                                                                <?= nl2br(esc($bonus_cinemark->instrucoes)) ?><br>
                                                                - Após isso, digite o código <strong style="font-size: 16px; color: #ffcc00"><?= esc($bonus_cinemark->codigo) ?></strong> do voucher que irá utilizar.<br>
                                                                <span class="badge bg-success mt-2" style="font-size: 13px;"><i class="bi bi-check-circle-fill me-1"></i>Validade de 20 dias</span>
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
                                                    <?php 
                                                    $bonusModel = new \App\Models\BonusModel();
                                                    $bonus_cinemark = $bonusModel->getCinemarkPorIngresso($i->id);
                                                    ?>
                                                    <?php if ($bonus_cinemark != null) : ?>
                                                        <strong style="color: #ffcc00">Seu ingresso CINEMARK já está disponível!</strong><br>
                                                        <small class="text-muted">Como usar o Cinemark Voucher:</small><br>
                                                        <?= nl2br(esc($bonus_cinemark->instrucoes)) ?><br>
                                                        - Após isso, digite o código <strong style="font-size: 16px; color: #ffcc00"><?= esc($bonus_cinemark->codigo) ?></strong> do voucher que irá utilizar.<br>
                                                        <span class="badge bg-success mt-2" style="font-size: 13px;"><i class="bi bi-check-circle-fill me-1"></i>Validade de 20 dias</span>
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
    </div>
</div>

<?php if (isset($perfil_incompleto) && $perfil_incompleto && !usuario_logado()->is_parceiro): ?>
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