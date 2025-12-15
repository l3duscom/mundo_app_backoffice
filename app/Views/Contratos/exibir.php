<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>

<link href="<?php echo site_url('recursos/theme/'); ?>plugins/select2/css/select2.min.css" rel="stylesheet" />
<link href="<?php echo site_url('recursos/theme/'); ?>plugins/select2/css/select2-bootstrap4.css" rel="stylesheet" />

<style>
    .card {
        transition: all 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 25px rgba(0, 0, 0, 0.1) !important;
    }
    
    .info-item {
        transition: all 0.2s ease;
        padding: 0.5rem;
        border-radius: 8px;
    }
    
    .info-item:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }
    
    .progress {
        height: 8px;
        border-radius: 4px;
    }
    
    .timeline-item {
        position: relative;
        padding-left: 30px;
        padding-bottom: 20px;
        border-left: 2px solid #dee2e6;
    }
    
    .timeline-item:last-child {
        border-left: 2px solid transparent;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -6px;
        top: 0;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #6c757d;
    }
    
    .timeline-item.active::before {
        background-color: #0d6efd;
    }
    
    .timeline-item.completed::before {
        background-color: #198754;
    }
    
    .btn-purple {
        background-color: #6f42c1;
        border-color: #6f42c1;
        color: white;
    }
    
    .btn-purple:hover {
        background-color: #5a32a3;
        border-color: #5a32a3;
        color: white;
    }
    
    .bg-purple {
        background-color: #6f42c1 !important;
        color: white !important;
    }
</style>

<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">
        <a href="<?php echo site_url('/contratos'); ?>">Contratos</a>
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('/contratos'); ?>">Contratos</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= esc($contrato->codigo ?? '#' . $contrato->id); ?></li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <div class="btn-group">
            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                Ações
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="<?php echo site_url("contratos/editar/$contrato->id"); ?>">
                    <i class="bx bx-edit-alt me-2"></i>Editar contrato</a></li>
                <li><hr class="dropdown-divider"></li>
                <li class="dropdown-header">Alterar Situação</li>
                <?php 
                $situacoes = [
                    'proposta' => 'Proposta',
                    'proposta_aceita' => 'Proposta Aceita',
                    'aguardando_contrato' => 'Aguardando Contrato',
                    'contrato_assinado' => 'Contrato Assinado',
                    'aguardando_credenciamento' => 'Aguardando Credenciamento',
                    'pagamento_aberto' => 'Pagamento em Aberto',
                    'pagamento_andamento' => 'Pagamento em Andamento',
                    'pagamento_confirmado' => 'Pagamento Confirmado',
                    'finalizado' => 'Finalizado',
                    'cancelado' => 'Cancelado',
                    'banido' => 'Banido',
                ];
                foreach ($situacoes as $key => $label): 
                    if ($key !== $contrato->situacao):
                ?>
                <li><a class="dropdown-item btn-alterar-situacao" href="#" data-situacao="<?= $key ?>">
                    <i class="bx bx-right-arrow-alt me-2"></i><?= $label ?></a></li>
                <?php endif; endforeach; ?>
                <li><hr class="dropdown-divider"></li>
                <?php if ($contrato->deleted_at == null) : ?>
                    <li><a class="dropdown-item text-danger" href="<?php echo site_url("contratos/excluir/$contrato->id"); ?>">
                        <i class="bx bx-trash me-2"></i>Excluir contrato</a></li>
                <?php else : ?>
                    <li><a class="dropdown-item text-success" href="<?php echo site_url("contratos/desfazerexclusao/$contrato->id"); ?>">
                        <i class="bx bx-undo me-2"></i>Restaurar contrato</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <a href="<?php echo site_url("contratos") ?>" class="btn btn-secondary ms-2">
            <i class="bx bx-arrow-back me-2"></i>Voltar
        </a>
    </div>
</div>
<!--end breadcrumb-->

<?php 
// Calcula desconto PIX (10%) apenas se forma de pagamento for PIX à vista (1 parcela)
$isPix = ($contrato->forma_pagamento === 'PIX');
$isPixAVista = $isPix && ($contrato->quantidade_parcelas == 1);
$descontoPix = $isPixAVista ? ($contrato->valor_final * 0.10) : 0;
$valorAPagar = $contrato->valor_final - $descontoPix;
$valorRestante = $valorAPagar - ($contrato->valor_pago ?? 0);
$porcentagemPaga = $valorAPagar > 0 ? round(($contrato->valor_pago / $valorAPagar) * 100, 1) : 0;
?>

<div class="row">
    <!-- Coluna Esquerda - Resumo -->
    <div class="col-lg-4">
        <div class="card shadow radius-10">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #0d6efd;">
                            <i class="bx bx-file text-white fs-3"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-0"><?php echo esc($contrato->codigo ?? '#' . $contrato->id); ?></h5>
                        <p class="text-muted mb-0">Contrato</p>
                    </div>
                </div>
                
                <div class="text-center mb-3">
                    <?php echo $contrato->getBadgeSituacao(); ?>
                </div>
                
                <hr>
                
                <div class="row g-3">
                    <div class="col-12">
                        <div class="d-flex align-items-center info-item">
                            <i class="bx bx-store-alt text-primary me-2"></i>
                            <div>
                                <small class="text-muted">Expositor:</small>
                                <p class="mb-0 fw-bold">
                                    <a href="<?php echo site_url("expositores/exibir/{$expositor->id}"); ?>">
                                        <?php echo esc($expositor->getNomeExibicao()); ?>
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="d-flex align-items-center info-item">
                            <i class="bx bx-calendar-event text-primary me-2"></i>
                            <div>
                                <small class="text-muted">Evento:</small>
                                <p class="mb-0"><?php echo esc($evento->nome ?? 'N/A'); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($contrato->descricao)): ?>
                    <div class="col-12">
                        <div class="d-flex align-items-center info-item">
                            <i class="bx bx-info-circle text-primary me-2"></i>
                            <div>
                                <small class="text-muted">Descrição:</small>
                                <p class="mb-0"><?php echo esc($contrato->descricao); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <hr>

                <div class="row g-2">
                    <div class="col-6">
                        <small class="text-muted">Criado:</small>
                        <p class="mb-0"><?php echo $contrato->created_at->humanize(); ?></p>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Atualizado:</small>
                        <p class="mb-0"><?php echo $contrato->updated_at->humanize(); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Progresso Financeiro -->
        <div class="card shadow radius-10 mt-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bx bx-trending-up me-2"></i>Progresso do Pagamento</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <h2 class="mb-0 text-success"><?php echo $porcentagemPaga; ?>%</h2>
                    <small class="text-muted">do valor total</small>
                </div>
                <div class="progress mb-3">
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo min($porcentagemPaga, 100); ?>%"></div>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Pago: <strong class="text-success"><?php echo $contrato->getValorPagoFormatado(); ?></strong></span>
                    <span class="text-muted">Restante: <strong class="text-danger">R$ <?php echo number_format($valorRestante, 2, ',', '.'); ?></strong></span>
                </div>
            </div>
        </div>

        <!-- Card Resumo Financeiro Líquido (aparece quando tem parcelas sincronizadas) -->
        <?php if (!empty($totais_parcelas) && $totais_parcelas['quantidade'] > 0 && ($totais_parcelas['taxa_total'] ?? 0) > 0): ?>
        <div class="card shadow radius-10 mt-4 border-success">
            <div class="card-header bg-success bg-opacity-10">
                <h6 class="mb-0 text-success"><i class="bx bx-dollar-circle me-2"></i>Resumo Financeiro Líquido</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="text-center p-2 bg-light rounded">
                            <small class="text-muted d-block">Valor Bruto</small>
                            <span class="text-muted text-decoration-line-through">R$ <?php echo number_format($totais_parcelas['total'], 2, ',', '.'); ?></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-2 bg-success rounded">
                            <small class="text-white d-block">Valor Líquido</small>
                            <span class="fw-bold text-white fs-5">R$ <?php echo number_format($totais_parcelas['total_liquido'], 2, ',', '.'); ?></span>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row g-2 text-center">
                    <div class="col-4">
                        <small class="text-muted d-block">Taxa Asaas</small>
                        <span class="text-danger fw-bold">-R$ <?php echo number_format($totais_parcelas['taxa_total'], 2, ',', '.'); ?></span>
                    </div>
                    <div class="col-4">
                        <small class="text-muted d-block">Recebido Líq.</small>
                        <span class="text-success fw-bold">R$ <?php echo number_format($totais_parcelas['pago_liquido'] ?? 0, 2, ',', '.'); ?></span>
                    </div>
                    <div class="col-4">
                        <small class="text-muted d-block">A Receber Líq.</small>
                        <span class="text-primary fw-bold">R$ <?php echo number_format($totais_parcelas['pendente_liquido'] ?? 0, 2, ',', '.'); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Card Documento do Contrato -->
        <?php 
        $aguardandoContrato = ($contrato->situacao === 'aguardando_contrato');
        $pagamentoConfirmado = ($contrato->situacao === 'pagamento_confirmado');
        $pagamentoEmAndamento = ($contrato->situacao === 'pagamento_andamento');
        $documentoModel = new \App\Models\ContratoDocumentoModel();
        $documento = $documentoModel->buscaDocumentoAtivo($contrato->id);
        $todosDocumentos = $documentoModel->buscaPorContrato($contrato->id);
        
        // Card aparece se estiver aguardando contrato, pagamento confirmado, pagamento em andamento, ou tem algum documento
        if ($aguardandoContrato || $pagamentoConfirmado || $pagamentoEmAndamento || !empty($todosDocumentos)): 
        ?>
        <div class="card shadow radius-10 mt-4">
            <div class="card-header bg-purple text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bx bx-file-blank me-2"></i>Documento do Contrato</h6>
                <?php if ($documento): ?>
                    <?php echo $documento->getBadgeStatus(); ?>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($aguardandoContrato): ?>
                <div class="alert alert-info mb-3">
                    <i class="bx bx-info-circle me-2"></i>
                    <strong>Ação necessária!</strong> O pagamento foi confirmado. Agora é necessário gerar o documento do contrato, enviá-lo para assinatura do expositor e confirmar no sistema.
                </div>
                <?php endif; ?>
                
                <?php if (!empty($todosDocumentos)): ?>
                    <!-- Tabela de Histórico de Documentos -->
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Título</th>
                                    <th class="text-center">Status</th>
                                    <th>Criado em</th>
                                    <th>Assinatura</th>
                                    <th>Confirmação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($todosDocumentos as $doc): ?>
                                <tr class="<?php echo $doc->id === $documento->id ? 'table-primary' : ''; ?>">
                                    <td>
                                        <?php echo esc($doc->titulo); ?>
                                        <?php if ($doc->id === $documento->id): ?>
                                            <span class="badge bg-primary ms-1">Atual</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?php echo $doc->getBadgeStatus(); ?></td>
                                    <td><small><?php echo $doc->created_at->format('d/m/Y H:i'); ?></small></td>
                                    <td>
                                        <?php if ($doc->data_assinatura): ?>
                                            <small class="text-success">
                                                <i class="bx bx-check me-1"></i><?php echo $doc->getDataAssinaturaFormatada(); ?>
                                                <br><span class="text-muted">por <?php echo esc($doc->assinado_por); ?></span>
                                            </small>
                                        <?php else: ?>
                                            <small class="text-muted">-</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($doc->data_confirmacao): ?>
                                            <small class="text-success">
                                                <i class="bx bx-badge-check me-1"></i><?php echo $doc->getDataConfirmacaoFormatada(); ?>
                                            </small>
                                        <?php else: ?>
                                            <small class="text-muted">-</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-3">Nenhum documento gerado ainda.</p>
                <?php endif; ?>
                
                <div class="d-grid gap-2">
                    <a href="<?php echo site_url("contratodocumentos/gerenciar/{$contrato->id}"); ?>" class="btn btn-purple">
                        <i class="bx bx-file-find me-1"></i>Gerenciar Documento
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Coluna Direita - Detalhes -->
    <div class="col-lg-8">
        <!-- Card Valores -->
        <div class="card shadow radius-10">
            <div class="card-header">
                <h6 class="mb-0"><i class="bx bx-money me-2"></i>Valores do Contrato</h6>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-3 text-center">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block">Valor Original</small>
                            <h4 class="mb-0"><?php echo $contrato->getValorOriginalFormatado(); ?></h4>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block">Desc. Itens (<?php echo $contrato->getPorcentagemDesconto(); ?>%)</small>
                            <h4 class="mb-0 text-danger">- <?php echo $contrato->getValorDescontoFormatado(); ?></h4>
                        </div>
                    </div>
                    <?php if ($isPixAVista): ?>
                    <div class="col-md-3 text-center">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block">Desc. PIX à Vista (10%)</small>
                            <h4 class="mb-0 text-danger">- R$ <?php echo number_format($descontoPix, 2, ',', '.'); ?></h4>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="p-3 bg-success text-white rounded">
                            <small class="d-block">Valor a Pagar</small>
                            <h4 class="mb-0">R$ <?php echo number_format($valorAPagar, 2, ',', '.'); ?></h4>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="col-md-6 text-center">
                        <div class="p-3 bg-primary text-white rounded">
                            <small class="d-block">Valor Final</small>
                            <h4 class="mb-0"><?php echo $contrato->getValorFinalFormatado(); ?></h4>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <hr>

                <div class="row g-3">
                    <div class="col-md-3">
                        <small class="text-muted">Parcelas:</small>
                        <p class="mb-0 fw-bold"><?php echo $contrato->quantidade_parcelas; ?>x de <?php echo $contrato->getValorParcelaFormatado(); ?></p>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Forma de Pagamento:</small>
                        <p class="mb-0"><?php echo esc($contrato->forma_pagamento ?? 'Não definido'); ?></p>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Valor Pago:</small>
                        <p class="mb-0 text-success fw-bold"><?php echo $contrato->getValorPagoFormatado(); ?></p>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Valor em Aberto:</small>
                        <p class="mb-0 text-danger fw-bold"><?php echo $contrato->getValorEmAbertoFormatado(); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Datas -->
        <div class="card shadow radius-10 mt-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bx bx-calendar me-2"></i>Datas Importantes</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <small class="text-muted">Data da Proposta:</small>
                        <p class="mb-0"><?php echo $contrato->data_proposta ? date('d/m/Y', strtotime($contrato->data_proposta)) : 'N/A'; ?></p>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Data do Aceite:</small>
                        <p class="mb-0"><?php echo $contrato->data_aceite ? date('d/m/Y', strtotime($contrato->data_aceite)) : 'N/A'; ?></p>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Data de Assinatura:</small>
                        <p class="mb-0"><?php echo $contrato->data_assinatura ? date('d/m/Y', strtotime($contrato->data_assinatura)) : 'N/A'; ?></p>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Data de Vencimento:</small>
                        <p class="mb-0 <?php echo $contrato->data_vencimento && strtotime($contrato->data_vencimento) < time() ? 'text-danger fw-bold' : ''; ?>">
                            <?php echo $contrato->data_vencimento ? date('d/m/Y', strtotime($contrato->data_vencimento)) : 'N/A'; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Parcelas (só aparece quando status é pagamento em aberto ou posterior) -->
        <?php 
        $statusComParcelas = ['pagamento_aberto', 'pagamento_andamento', 'aguardando_contrato', 'pagamento_confirmado', 'aguardando_credenciamento', 'finalizado'];
        if ($contrato->quantidade_parcelas > 0 && in_array($contrato->situacao, $statusComParcelas)): 
        ?>
        <div class="card shadow radius-10 mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bx bx-credit-card me-2"></i>Parcelas do Contrato</h6>
                <?php if (!empty($contrato->asaas_payment_id)): ?>
                <button type="button" class="btn btn-outline-primary btn-sm" id="btnSincronizarAsaas">
                    <i class="bx bx-refresh me-1"></i>Sincronizar Asaas
                </button>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tabelaParcelasAsaas">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 80px;">Parcela</th>
                                <th>Vencimento</th>
                                <th class="text-end">Valor Bruto</th>
                                <th class="text-end">Valor Líquido</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Asaas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($parcelas)): ?>
                                <!-- Parcelas sincronizadas do banco de dados -->
                                <?php $qtdParcelas = count($parcelas); ?>
                                <?php foreach ($parcelas as $parcela): ?>
                                <tr>
                                    <td class="text-center">
                                        <span class="badge bg-secondary"><?php echo $parcela->numero_parcela; ?>/<?php echo $qtdParcelas; ?></span>
                                    </td>
                                    <td>
                                        <span class="<?php echo $parcela->isVencida() ? 'text-danger fw-bold' : ''; ?>">
                                            <?php echo $parcela->getVencimentoFormatado(); ?>
                                        </span>
                                        <?php if ($parcela->data_pagamento): ?>
                                            <br><small class="text-success">Pago: <?php echo $parcela->getPagamentoFormatado(); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end text-muted">
                                        <small><?php echo $parcela->getValorFormatado(); ?></small>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-success"><?php echo $parcela->getValorLiquidoFormatado(); ?></span>
                                        <?php 
                                        $taxa = ($parcela->valor ?? 0) - ($parcela->valor_liquido ?? $parcela->valor ?? 0);
                                        if ($taxa > 0): 
                                        ?>
                                        <br><small class="text-danger">-R$ <?php echo number_format($taxa, 2, ',', '.'); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo $parcela->getBadgeStatus(); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo $parcela->getBadgeStatusAsaas(); ?>
                                        <?php if ($parcela->comprovante_url): ?>
                                            <br><a href="<?php echo $parcela->comprovante_url; ?>" target="_blank" class="btn btn-sm btn-outline-success mt-1">
                                                <i class="bx bx-receipt"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- Parcelas calculadas localmente (antes da sincronização) -->
                                <?php 
                                $qtdParcelas = $contrato->quantidade_parcelas;
                                $valorParcelaBase = $valorAPagar / $qtdParcelas;
                                $dataVencBase = $contrato->data_vencimento ? strtotime($contrato->data_vencimento) : strtotime('+7 days');
                                $valorPagoAcumulado = $contrato->valor_pago ?? 0;
                                
                                for ($i = 1; $i <= $qtdParcelas; $i++): 
                                    $vencimentoParcela = strtotime('+' . ($i - 1) . ' months', $dataVencBase);
                                    $valorParcelaAtual = round($valorParcelaBase, 2);
                                    
                                    if ($valorPagoAcumulado >= $valorParcelaAtual) {
                                        $statusParcela = 'pago';
                                        $valorPagoAcumulado -= $valorParcelaAtual;
                                    } elseif ($valorPagoAcumulado > 0) {
                                        $statusParcela = 'parcial';
                                        $valorPagoAcumulado = 0;
                                    } elseif ($vencimentoParcela < time()) {
                                        $statusParcela = 'vencido';
                                    } else {
                                        $statusParcela = 'pendente';
                                    }
                                ?>
                                <tr>
                                    <td class="text-center">
                                        <span class="badge bg-secondary"><?php echo $i; ?>/<?php echo $qtdParcelas; ?></span>
                                    </td>
                                    <td>
                                        <span class="<?php echo $statusParcela === 'vencido' ? 'text-danger fw-bold' : ''; ?>">
                                            <?php echo date('d/m/Y', $vencimentoParcela); ?>
                                        </span>
                                    </td>
                                    <td class="text-end text-muted">
                                        <small>R$ <?php echo number_format($valorParcelaAtual, 2, ',', '.'); ?></small>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-success">R$ <?php echo number_format($valorParcelaAtual, 2, ',', '.'); ?></span>
                                        <br><small class="text-muted">sincronizar para ver</small>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($statusParcela === 'pago'): ?>
                                            <span class="badge bg-success"><i class="bx bx-check me-1"></i>Pago</span>
                                        <?php elseif ($statusParcela === 'parcial'): ?>
                                            <span class="badge bg-warning"><i class="bx bx-time me-1"></i>Parcial</span>
                                        <?php elseif ($statusParcela === 'vencido'): ?>
                                            <span class="badge bg-danger"><i class="bx bx-error me-1"></i>Vencido</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><i class="bx bx-time me-1"></i>Pendente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center text-muted">
                                        <small>Não sincronizado</small>
                                    </td>
                                </tr>
                                <?php endfor; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <?php 
                            // Calcula totais se tem parcelas sincronizadas
                            $totalBruto = $valorAPagar;
                            $totalLiquido = $valorAPagar;
                            $taxaTotal = 0;
                            
                            if (!empty($totais_parcelas) && $totais_parcelas['quantidade'] > 0) {
                                $totalBruto = $totais_parcelas['total'];
                                $totalLiquido = $totais_parcelas['total_liquido'];
                                $taxaTotal = $totais_parcelas['taxa_total'] ?? 0;
                            }
                            ?>
                            <tr>
                                <td colspan="2" class="text-end fw-bold">Total Bruto:</td>
                                <td class="text-end text-muted">R$ <?php echo number_format($totalBruto, 2, ',', '.'); ?></td>
                                <td class="text-end">
                                    <span class="fw-bold text-success fs-6">R$ <?php echo number_format($totalLiquido, 2, ',', '.'); ?></span>
                                    <?php if ($taxaTotal > 0): ?>
                                    <br><small class="text-danger">Taxa: -R$ <?php echo number_format($taxaTotal, 2, ',', '.'); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td colspan="2" class="text-center">
                                    <?php if (!empty($parcelas)): ?>
                                        <small class="text-muted">
                                            Última sync: <?php echo $parcelas[0]->synced_at ? $parcelas[0]->synced_at->format('d/m/Y H:i') : 'N/A'; ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Card Itens do Contrato -->
        <div class="card shadow radius-10 mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bx bx-list-ul me-2"></i>Itens do Contrato</h6>
                
                <?php 
                // Define as situações que permitem adicionar itens (apenas proposta_aceita)
                $situacoesPermitidas = ['proposta_aceita'];
                $podeAdicionarItens = in_array($contrato->situacao, $situacoesPermitidas);
                $propostaAguardando = ($contrato->situacao === 'proposta');
                $pagamentoAberto = in_array($contrato->situacao, ['pagamento_aberto', 'pagamento_andamento']);
                $aguardandoContratoItens = ($contrato->situacao === 'aguardando_contrato');
                $aguardandoCredenciamento = ($contrato->situacao === 'aguardando_credenciamento');
                $contratoBloqueado = in_array($contrato->situacao, ['contrato_assinado', 'aguardando_contrato', 'pagamento_confirmado', 'aguardando_credenciamento', 'finalizado', 'cancelado', 'banido']);
                ?>

                <?php if ($propostaAguardando): ?>
                    <button type="button" class="btn btn-success btn-sm btn-aceitar-proposta">
                        <i class="bx bx-check me-1"></i>Aceitar Proposta
                    </button>
                <?php elseif ($aguardandoContratoItens): ?>
                    <a href="<?php echo site_url("contratodocumentos/gerenciar/{$contrato->id}"); ?>" class="btn btn-purple btn-sm">
                        <i class="bx bx-file-find me-1"></i>Gerenciar Documento
                    </a>
                <?php elseif ($pagamentoAberto): ?>
                    <div class="btn-group">
                        <?php if (!empty($contrato->asaas_invoice_url)): ?>
                        <a href="<?php echo $contrato->asaas_invoice_url; ?>" target="_blank" class="btn btn-primary btn-sm">
                            <i class="bx bx-link-external me-1"></i>Abrir Link de Pagamento
                        </a>
                        <?php endif; ?>
                        <button type="button" class="btn btn-success btn-sm btn-confirmar-pagamento">
                            <i class="bx bx-check-circle me-1"></i>Confirmar Pagamento
                        </button>
                        <button type="button" class="btn btn-warning btn-sm btn-receber-dinheiro">
                            <i class="bx bx-money me-1"></i>Receber em Dinheiro
                        </button>
                    </div>
                <?php elseif ($podeAdicionarItens): ?>
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalItem">
                            <i class="bx bx-plus me-1"></i>Adicionar Item
                        </button>
                        <button type="button" class="btn btn-success btn-sm btn-gerar-cobranca" id="btnGerarCobranca" style="display: none;">
                            <i class="bx bx-dollar me-1"></i>Gerar Cobrança
                        </button>
                    </div>
                <?php elseif ($contratoBloqueado): ?>
                    <span class="badge bg-secondary">Edição bloqueada</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($propostaAguardando): ?>
                <div class="alert alert-warning mb-4">
                    <i class="bx bx-info-circle me-2"></i>
                    <strong>Proposta aguardando aceite.</strong> 
                    Para adicionar itens ao contrato, primeiro aceite a proposta clicando no botão acima.
                </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table id="tabelaItens" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Descrição</th>
                                <th>Localização</th>
                                <th>Metragem</th>
                                <th class="text-center">Qtd</th>
                                <th class="text-end">Valor Unit.</th>
                                <th class="text-end">Desconto</th>
                                <th class="text-end">Total</th>
                                <?php if ($podeAdicionarItens): ?>
                                <th class="text-center">Ações</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Carregado via AJAX -->
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary">
                                <td colspan="5" class="text-end fw-bold">Totais:</td>
                                <td class="text-end" id="totais-subtotal">-</td>
                                <td class="text-end text-danger" id="totais-desconto">-</td>
                                <td class="text-end fw-bold text-primary" id="totais-total">-</td>
                                <?php if ($podeAdicionarItens): ?>
                                <td></td>
                                <?php endif; ?>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Card Observações -->
        <?php if (!empty($contrato->observacoes)): ?>
        <div class="card shadow radius-10 mt-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bx bx-note me-2"></i>Observações</h6>
            </div>
            <div class="card-body">
                <p class="mb-0"><?php echo nl2br(esc($contrato->observacoes)); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Card Credenciamento -->
        <div class="card shadow radius-10 mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bx bx-id-card me-2"></i>Credenciamento</h6>
                <div>
                    <?php if (isset($credenciamento) && $credenciamento): ?>
                    <?= $credenciamento['credenciamento']->getBadgeStatus() ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?php if (!isset($credenciamento) || !$credenciamento): ?>
                <p class="text-muted mb-0">
                    <i class="bx bx-info-circle me-1"></i>
                    Nenhum credenciamento preenchido para este contrato.
                </p>
                <?php else: ?>
                
                <?php $cred = $credenciamento['credenciamento']; ?>
                
                <!-- Botões de Ação Admin -->
                <?php if ($cred->status !== 'aprovado'): ?>
                <div class="alert alert-light border mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="bx bx-check-shield me-1"></i> <strong>Ações de Aprovação:</strong></span>
                        <div>
                            <button class="btn btn-success btn-sm me-2" id="btnAprovarTudo" data-id="<?= $cred->id ?>">
                                <i class="bx bx-check-double me-1"></i>Aprovar Tudo
                            </button>
                            <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalDevolverCredenciamento">
                                <i class="bx bx-undo me-1"></i>Devolver para Correção
                            </button>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Veículo -->
                <div class="mb-4">
                    <h6 class="text-primary"><i class="bx bxs-car me-1"></i> Veículo</h6>
                    <?php if (empty($credenciamento['veiculos'])): ?>
                    <p class="text-muted small">Nenhum veículo cadastrado.</p>
                    <?php else: ?>
                    <?php foreach ($credenciamento['veiculos'] as $veiculo): ?>
                    <div class="p-2 bg-light rounded mb-2">
                        <strong><?= esc($veiculo->marca) ?> <?= esc($veiculo->modelo) ?></strong>
                        - <?= esc($veiculo->cor) ?>
                        <span class="badge bg-secondary ms-2"><?= $veiculo->getPlacaFormatada() ?></span>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Responsável -->
                <div class="mb-4">
                    <h6 class="text-primary"><i class="bx bxs-user-badge me-1"></i> Responsável</h6>
                    <?php if (!$credenciamento['responsavel']): ?>
                    <p class="text-muted small">Nenhum responsável cadastrado.</p>
                    <?php else: ?>
                    <?php $resp = $credenciamento['responsavel']; ?>
                    <div class="p-2 bg-light rounded d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?= esc($resp->nome) ?></strong>
                            <?= $resp->getBadgeStatusAprovacao() ?>
                            <small class="d-block text-muted">
                                CPF: <?= $resp->getCpfFormatado() ?> | 
                                RG: <?= esc($resp->rg) ?> | 
                                WhatsApp: <?= $resp->getWhatsappFormatado() ?>
                            </small>
                            <?php if ($resp->motivo_rejeicao): ?>
                            <small class="text-danger"><i class="bx bx-x-circle"></i> <?= esc($resp->motivo_rejeicao) ?></small>
                            <?php endif; ?>
                        </div>
                        <?php if ($cred->status !== 'aprovado'): ?>
                        <div class="btn-group btn-group-sm">
                            <?php if ($resp->status !== 'aprovado'): ?>
                            <button class="btn btn-outline-success btn-aprovar-pessoa" data-id="<?= $resp->id ?>"><i class="bx bx-check"></i></button>
                            <?php endif; ?>
                            <?php if ($resp->status !== 'rejeitado'): ?>
                            <button class="btn btn-outline-danger btn-rejeitar-pessoa" data-id="<?= $resp->id ?>" data-nome="<?= esc($resp->nome) ?>"><i class="bx bx-x"></i></button>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Funcionários -->
                <div class="mb-4">
                    <h6 class="text-primary"><i class="bx bxs-group me-1"></i> Funcionários (<?= count($credenciamento['funcionarios']) ?>)</h6>
                    <?php if (empty($credenciamento['funcionarios'])): ?>
                    <p class="text-muted small">Nenhum funcionário cadastrado.</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>CPF</th>
                                    <th>RG</th>
                                    <th>WhatsApp</th>
                                    <th class="text-center">Status</th>
                                    <?php if ($cred->status !== 'aprovado'): ?>
                                    <th class="text-center">Ações</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($credenciamento['funcionarios'] as $func): ?>
                                <tr>
                                    <td>
                                        <?= esc($func->nome) ?>
                                        <?php if ($func->motivo_rejeicao): ?>
                                        <br><small class="text-danger"><?= esc($func->motivo_rejeicao) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $func->getCpfFormatado() ?></td>
                                    <td><?= esc($func->rg) ?></td>
                                    <td><?= $func->getWhatsappFormatado() ?></td>
                                    <td class="text-center"><?= $func->getBadgeStatusAprovacao() ?></td>
                                    <?php if ($cred->status !== 'aprovado'): ?>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <?php if ($func->status !== 'aprovado'): ?>
                                            <button class="btn btn-outline-success btn-aprovar-pessoa" data-id="<?= $func->id ?>"><i class="bx bx-check"></i></button>
                                            <?php endif; ?>
                                            <?php if ($func->status !== 'rejeitado'): ?>
                                            <button class="btn btn-outline-danger btn-rejeitar-pessoa" data-id="<?= $func->id ?>" data-nome="<?= esc($func->nome) ?>"><i class="bx bx-x"></i></button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Suplentes -->
                <div>
                    <h6 class="text-primary"><i class="bx bxs-user-plus me-1"></i> Suplentes (<?= count($credenciamento['suplentes']) ?>)</h6>
                    <?php if (empty($credenciamento['suplentes'])): ?>
                    <p class="text-muted small mb-0">Nenhum suplente cadastrado.</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>CPF</th>
                                    <th>RG</th>
                                    <th>WhatsApp</th>
                                    <th class="text-center">Status</th>
                                    <?php if ($cred->status !== 'aprovado'): ?>
                                    <th class="text-center">Ações</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($credenciamento['suplentes'] as $sup): ?>
                                <tr>
                                    <td>
                                        <?= esc($sup->nome) ?>
                                        <?php if ($sup->motivo_rejeicao): ?>
                                        <br><small class="text-danger"><?= esc($sup->motivo_rejeicao) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $sup->getCpfFormatado() ?></td>
                                    <td><?= esc($sup->rg) ?></td>
                                    <td><?= $sup->getWhatsappFormatado() ?></td>
                                    <td class="text-center"><?= $sup->getBadgeStatusAprovacao() ?></td>
                                    <?php if ($cred->status !== 'aprovado'): ?>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <?php if ($sup->status !== 'aprovado'): ?>
                                            <button class="btn btn-outline-success btn-aprovar-pessoa" data-id="<?= $sup->id ?>"><i class="bx bx-check"></i></button>
                                            <?php endif; ?>
                                            <?php if ($sup->status !== 'rejeitado'): ?>
                                            <button class="btn btn-outline-danger btn-rejeitar-pessoa" data-id="<?= $sup->id ?>" data-nome="<?= esc($sup->nome) ?>"><i class="bx bx-x"></i></button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Devolver Credenciamento -->
<?php if (isset($credenciamento) && $credenciamento): ?>
<div class="modal fade" id="modalDevolverCredenciamento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bx bx-undo me-2"></i>Devolver Credenciamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">O credenciamento será devolvido para o expositor fazer correções.</p>
                <div class="mb-3">
                    <label class="form-label">Observação <small class="text-muted">(opcional)</small></label>
                    <textarea class="form-control" id="observacaoDevolucao" rows="3" placeholder="Informe o que precisa ser corrigido..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btnConfirmarDevolucao" data-id="<?= $credenciamento['credenciamento']->id ?>">
                    <i class="bx bx-undo me-1"></i>Devolver
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Rejeitar Pessoa -->
<div class="modal fade" id="modalRejeitarPessoa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bx bx-x-circle me-2 text-danger"></i>Rejeitar Pessoa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Rejeitar <strong id="nomeRejeitar"></strong>?</p>
                <div class="mb-3">
                    <label class="form-label">Motivo da rejeição <small class="text-muted">(opcional)</small></label>
                    <input type="text" class="form-control" id="motivoRejeicao" placeholder="Ex: Documento inválido">
                    <input type="hidden" id="idRejeitar">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarRejeicao">
                    <i class="bx bx-x me-1"></i>Rejeitar
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Adicionar/Editar Item -->
<div class="modal fade" id="modalItem" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalItemTitle">Adicionar Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formItem">
                <div class="modal-body">
                    <input type="hidden" name="id" id="item_id">
                    <input type="hidden" name="contrato_id" value="<?php echo $contrato->id; ?>">
                    
                    <div class="row g-3">
                        <!-- Busca do Catálogo -->
                        <div class="col-12">
                            <label for="item_catalogo" class="form-label">Buscar no Catálogo <span class="text-danger">*</span></label>
                            <select class="form-select" name="item_catalogo" id="item_catalogo">
                                <option value="">Digite para buscar um item...</option>
                            </select>
                            <small class="text-muted">Selecione um item do catálogo ou preencha manualmente abaixo</small>
                        </div>

                        <hr class="my-2">

                        <div class="col-md-6">
                            <label for="tipo_item" class="form-label">Tipo do Item <span class="text-danger">*</span></label>
                            <select class="form-select" name="tipo_item" id="tipo_item" required>
                                <option value="">Selecione...</option>
                                <option value="Espaço Comercial">Espaço Comercial</option>
                                <option value="Artist Alley">Artist Alley</option>
                                <option value="Vila dos Artesãos">Vila dos Artesãos</option>
                                <option value="Espaço Medieval">Espaço Medieval</option>
                                <option value="Indie">Indie</option>
                                <option value="Games">Games</option>
                                <option value="Espaço Temático">Espaço Temático</option>
                                <option value="Food Park">Food Park</option>
                                <option value="Cota">Cota</option>
                                <option value="Patrocínio">Patrocínio</option>
                                <option value="Parceiros">Parceiros</option>
                                <option value="Patrocinadores">Patrocinadores</option>
                                <option value="Energia Elétrica">Energia Elétrica</option>
                                <option value="Internet">Internet</option>
                                <option value="Credenciamento">Credenciamento</option>
                                <option value="Outros">Outros</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="descricao" class="form-label">Descrição</label>
                            <input type="text" class="form-control" name="descricao" id="descricao" placeholder="Ex: Stand comercial área VIP">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="localizacao" class="form-label">Localização</label>
                            <input type="text" class="form-control" name="localizacao" id="localizacao" placeholder="Ex: Stand A-15">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="metragem" class="form-label">Metragem</label>
                            <input type="text" class="form-control" name="metragem" id="metragem" placeholder="Ex: 3x3m">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="quantidade" class="form-label">Quantidade <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="quantidade" id="quantidade" value="1" min="1" required>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="valor_unitario" class="form-label">Valor Unitário <span class="text-danger">*</span></label>
                            <input type="text" class="form-control money" name="valor_unitario" id="valor_unitario" placeholder="0,00" required>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="percentual_desconto" class="form-label">Desconto (%)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="percentual_desconto" id="percentual_desconto" placeholder="0" min="0" max="100" step="0.01" value="0">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Desconto (R$)</label>
                            <input type="text" class="form-control" id="valor_desconto_preview" readonly>
                            <input type="hidden" name="valor_desconto" id="valor_desconto" value="0">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Valor Total</label>
                            <input type="text" class="form-control bg-light fw-bold" id="valor_total_preview" readonly>
                        </div>
                        
                        <div class="col-12">
                            <label for="observacoes" class="form-label">Observações</label>
                            <textarea class="form-control" name="observacoes" id="observacoes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnSalvarItem">
                        <i class="bx bx-save me-1"></i>Salvar Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Gerar Cobrança -->
<div class="modal fade" id="modalCobranca" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bx bx-dollar me-2"></i>Gerar Cobrança
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center" id="cobranca-opcoes">
                    <div class="mb-4">
                        <h5 class="text-muted mb-2">Forma de Pagamento</h5>
                        <span class="badge bg-primary fs-5"><?php echo esc($contrato->forma_pagamento ?? 'Não definida'); ?></span>
                    </div>
                    
                    <?php if ($isPixAVista): ?>
                    <div class="row mb-3">
                        <div class="col-12">
                            <small class="text-muted">Valor do Contrato</small>
                            <h5 class="text-muted text-decoration-line-through">R$ <?php echo number_format($contrato->valor_final ?? 0, 2, ',', '.'); ?></h5>
                        </div>
                    </div>
                    <div class="alert alert-success mb-3">
                        <i class="bx bx-gift me-2"></i>
                        <strong>Desconto PIX à Vista: 10%</strong>
                        <span class="badge bg-success ms-2">- R$ <?php echo number_format(($contrato->valor_final ?? 0) * 0.10, 2, ',', '.'); ?></span>
                    </div>
                    <div class="row mb-4">
                        <div class="col-12">
                            <small class="text-muted">Valor a Pagar</small>
                            <h3 class="text-success fw-bold">R$ <?php echo number_format(($contrato->valor_final ?? 0) * 0.90, 2, ',', '.'); ?></h3>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <small class="text-muted">Valor a Cobrar</small>
                            <h3 class="text-success fw-bold">R$ <?php echo number_format($contrato->valor_final ?? 0, 2, ',', '.'); ?></h3>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (empty($contrato->forma_pagamento)): ?>
                    <div class="alert alert-warning mb-4">
                        <i class="bx bx-error me-2"></i>
                        <strong>Forma de pagamento não definida!</strong><br>
                        <small>Edite o contrato e defina a forma de pagamento antes de gerar a cobrança.</small>
                    </div>
                    <?php else: ?>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success btn-lg" id="btnConfirmarCobranca">
                            <i class="bx bx-check me-2"></i>Confirmar e Gerar Cobrança
                        </button>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div id="cobranca-loading" class="text-center py-4" style="display: none;">
                    <div class="spinner-border text-success mb-3" role="status">
                        <span class="visually-hidden">Gerando...</span>
                    </div>
                    <p class="text-muted">Gerando cobrança no Asaas...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Sucesso da Cobrança -->
<div class="modal fade" id="modalCobrancaSucesso" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bx bx-check-circle me-2"></i>Cobrança Gerada com Sucesso!
                </h5>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-4">
                    <i class="bx bx-check-circle text-success" style="font-size: 5rem;"></i>
                </div>
                <h4 class="mb-3">Cobrança criada!</h4>
                <p class="text-muted mb-4">
                    Tipo: <span class="badge bg-primary" id="sucesso-tipo"></span><br>
                    Valor: <span class="fw-bold" id="sucesso-valor"></span>
                </p>
                
                <a href="#" id="sucesso-link" target="_blank" class="btn btn-success btn-lg mb-3 d-block">
                    <i class="bx bx-link-external me-2"></i>Acessar Link de Pagamento
                </a>
                
                <button type="button" class="btn btn-outline-secondary" id="btn-copiar-link">
                    <i class="bx bx-copy me-2"></i>Copiar Link
                </button>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" onclick="location.reload()">Fechar e Atualizar</button>
            </div>
        </div>
    </div>
</div>


<?php echo $this->endSection() ?>




<?php echo $this->section('scripts') ?>

<script src="<?php echo site_url('recursos/vendor/mask/jquery.mask.min.js') ?>"></script>
<script src="<?php echo site_url('recursos/theme/'); ?>plugins/select2/js/select2.min.js"></script>
<script src="<?php echo site_url('recursos/theme/'); ?>plugins/select2/js/i18n/pt-BR.js"></script>

<script>
$(document).ready(function() {

    var csrfToken = '<?php echo csrf_hash(); ?>';
    var csrfName = '<?php echo csrf_token(); ?>';
    var contratoId = <?php echo $contrato->id; ?>;
    var podeAdicionarItens = <?php echo $podeAdicionarItens ? 'true' : 'false'; ?>;

    // Máscara para valores monetários
    $('.money').mask('#.##0,00', {reverse: true});

    // ================================
    // SINCRONIZAR PARCELAS DO ASAAS
    // ================================
    $('#btnSincronizarAsaas').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Sincronizando...');
        
        $.ajax({
            type: 'GET',
            url: '<?php echo site_url('contratos/buscarparcelasasaas/' . $contrato->id); ?>',
            dataType: 'json',
            success: function(response) {
                if (response.token) csrfToken = response.token;
                
                if (response.sucesso) {
                    // Dados foram salvos no banco - recarrega a página para mostrar atualizados
                    alert('Parcelas sincronizadas e salvas no banco!\n\nStatus da cobrança: ' + response.cobranca.status);
                    location.reload();
                } else {
                    alert(response.erro || 'Erro ao sincronizar parcelas');
                    btn.prop('disabled', false).html('<i class="bx bx-refresh me-1"></i>Sincronizar Asaas');
                }
            },
            error: function() {
                alert('Erro ao comunicar com o servidor');
                btn.prop('disabled', false).html('<i class="bx bx-refresh me-1"></i>Sincronizar Asaas');
            }
        });
    });

    // ================================
    // BOTÃO ACEITAR PROPOSTA
    // ================================
    $('.btn-aceitar-proposta').on('click', function() {
        if (!confirm('Deseja aceitar esta proposta? Após o aceite, você poderá adicionar os itens do contrato.')) {
            return;
        }
        
        $.ajax({
            type: 'POST',
            url: '<?php echo site_url('contratos/alterarsituacao'); ?>',
            data: {
                id: contratoId,
                situacao: 'proposta_aceita',
                [csrfName]: csrfToken
            },
            dataType: 'json',
            success: function(response) {
                if (response.token) csrfToken = response.token;
                if (response.sucesso) {
                    location.reload();
                } else {
                    alert(response.erro || 'Erro ao aceitar proposta');
                }
            },
            error: function() {
                alert('Erro ao processar a solicitação');
            }
        });
    });

    // ================================
    // BOTÃO CONFIRMAR PAGAMENTO
    // ================================
    $('.btn-confirmar-pagamento').on('click', function() {
        if (!confirm('Deseja confirmar o pagamento deste contrato?\n\nEsta ação irá marcar o contrato como PAGO.')) {
            return;
        }
        
        var btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Confirmando...');
        
        $.ajax({
            type: 'POST',
            url: '<?php echo site_url('contratos/alterarsituacao'); ?>',
            data: {
                id: contratoId,
                situacao: 'pagamento_confirmado',
                [csrfName]: csrfToken
            },
            dataType: 'json',
            success: function(response) {
                if (response.token) csrfToken = response.token;
                if (response.sucesso) {
                    alert('Pagamento confirmado com sucesso!');
                    location.reload();
                } else {
                    alert(response.erro || 'Erro ao confirmar pagamento');
                    btn.prop('disabled', false).html('<i class="bx bx-check-circle me-1"></i>Confirmar Pagamento');
                }
            },
            error: function() {
                alert('Erro ao processar a solicitação');
                btn.prop('disabled', false).html('<i class="bx bx-check-circle me-1"></i>Confirmar Pagamento');
            }
        });
    });

    // ================================
    // BOTÃO RECEBER EM DINHEIRO (Integrado com Asaas)
    // ================================
    $('.btn-receber-dinheiro').on('click', function() {
        var valorTotal = <?php echo $valorAPagar; ?>;
        var valorEmAberto = <?php echo $valorRestante; ?>;
        var qtdParcelas = <?php echo $contrato->quantidade_parcelas; ?>;
        var valorParcela = valorTotal / qtdParcelas;
        
        // Modal de opções de recebimento
        var opcao = prompt(
            'RECEBIMENTO EM DINHEIRO\n\n' +
            'Valor total: R$ ' + valorTotal.toFixed(2).replace('.', ',') + '\n' +
            'Valor em aberto: R$ ' + valorEmAberto.toFixed(2).replace('.', ',') + '\n' +
            'Parcelas: ' + qtdParcelas + 'x de R$ ' + valorParcela.toFixed(2).replace('.', ',') + '\n\n' +
            'Digite:\n' +
            '1 - Receber valor TOTAL (R$ ' + valorEmAberto.toFixed(2).replace('.', ',') + ')\n' +
            '2 - Receber 1 PARCELA (R$ ' + valorParcela.toFixed(2).replace('.', ',') + ')\n' +
            '3 - Receber valor PERSONALIZADO\n\n' +
            'Escolha (1, 2 ou 3):'
        );
        
        if (!opcao) return;
        
        var valorReceber = 0;
        
        if (opcao === '1') {
            valorReceber = valorEmAberto;
        } else if (opcao === '2') {
            valorReceber = valorParcela;
        } else if (opcao === '3') {
            var valorPersonalizado = prompt('Digite o valor a receber (use ponto para decimais):\n\nEx: 500.00');
            if (!valorPersonalizado) return;
            valorReceber = parseFloat(valorPersonalizado.replace(',', '.'));
            if (isNaN(valorReceber) || valorReceber <= 0) {
                alert('Valor inválido!');
                return;
            }
        } else {
            alert('Opção inválida!');
            return;
        }
        
        if (!confirm('Confirma o recebimento em DINHEIRO?\n\nValor: R$ ' + valorReceber.toFixed(2).replace('.', ',') + '\n\nEsta ação irá registrar o pagamento.')) {
            return;
        }
        
        var btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Processando...');
        
        $.ajax({
            type: 'POST',
            url: '<?php echo site_url('contratos/receberdinheiro'); ?>',
            data: {
                id: contratoId,
                valor: valorReceber,
                [csrfName]: csrfToken
            },
            dataType: 'json',
            success: function(response) {
                if (response.token) csrfToken = response.token;
                if (response.sucesso) {
                    alert(response.sucesso + '\n\nValor recebido: ' + response.valor_recebido);
                    location.reload();
                } else {
                    alert(response.erro || 'Erro ao confirmar recebimento');
                    btn.prop('disabled', false).html('<i class="bx bx-money me-1"></i>Receber em Dinheiro');
                }
            },
            error: function() {
                alert('Erro ao processar a solicitação');
                btn.prop('disabled', false).html('<i class="bx bx-money me-1"></i>Receber em Dinheiro');
            }
        });
    });

    // ================================
    // BOTÃO GERAR COBRANÇA - Abre modal de confirmação
    // ================================
    $('.btn-gerar-cobranca').on('click', function() {
        $('#cobranca-opcoes').show();
        $('#cobranca-loading').hide();
        $('#modalCobranca').modal('show');
    });

    // Clique no botão de confirmar cobrança
    $('#btnConfirmarCobranca').on('click', function() {
        $('#cobranca-opcoes').hide();
        $('#cobranca-loading').show();
        
        $.ajax({
            type: 'POST',
            url: '<?php echo site_url('contratos/gerarcobranca'); ?>',
            data: {
                id: contratoId,
                [csrfName]: csrfToken
            },
            dataType: 'json',
            success: function(response) {
                if (response.token) csrfToken = response.token;
                
                // Pagamento externo (Dinheiro, Permuta, Cortesia)
                if (response.externo) {
                    $('#modalCobranca').modal('hide');
                    alert(response.sucesso);
                    location.reload();
                    return;
                }
                
                if (response.sucesso && response.invoice_url) {
                    // Fecha modal de cobrança
                    $('#modalCobranca').modal('hide');
                    
                    // Preenche e abre modal de sucesso
                    $('#sucesso-tipo').text(response.forma_pagamento);
                    
                    // Mostra desconto PIX se houver
                    if (response.desconto_pix) {
                        $('#sucesso-valor').html(
                            '<span class="text-muted text-decoration-line-through">' + response.valor_original + '</span><br>' +
                            '<span class="text-success">' + response.valor_cobranca + '</span>' +
                            '<br><small class="text-success">(Desconto PIX: ' + response.desconto_pix + ')</small>'
                        );
                    } else {
                        $('#sucesso-valor').text(response.valor_cobranca);
                    }
                    
                    $('#sucesso-link').attr('href', response.invoice_url);
                    
                    $('#modalCobrancaSucesso').modal('show');
                } else {
                    $('#modalCobranca').modal('hide');
                    alert(response.erro || 'Erro ao gerar cobrança no Asaas');
                }
            },
            error: function(xhr) {
                $('#modalCobranca').modal('hide');
                console.error('Erro:', xhr.responseText);
                alert('Erro ao processar a solicitação');
            }
        });
    });

    // Botão de copiar link
    $('#btn-copiar-link').on('click', function() {
        var link = $('#sucesso-link').attr('href');
        navigator.clipboard.writeText(link).then(function() {
            alert('Link copiado para a área de transferência!');
        }).catch(function() {
            // Fallback para navegadores antigos
            var tempInput = $('<input>');
            $('body').append(tempInput);
            tempInput.val(link).select();
            document.execCommand('copy');
            tempInput.remove();
            alert('Link copiado para a área de transferência!');
        });
    });

    // ================================
    // SELECT2 PARA BUSCAR DO CATÁLOGO (filtrado pelo evento do contrato)
    // ================================
    var eventIdContrato = <?php echo $contrato->event_id; ?>;

    $('#item_catalogo').select2({
        theme: 'bootstrap4',
        placeholder: 'Digite para buscar um item do catálogo...',
        allowClear: true,
        language: 'pt-BR',
        width: '100%',
        dropdownParent: $('#modalItem'),
        ajax: {
            url: '<?php echo site_url('itenscatalogo/buscaitens'); ?>',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { 
                    termo: params.term,
                    event_id: eventIdContrato
                };
            },
            processResults: function(data) {
                return { results: data.results };
            },
            cache: true
        },
        minimumInputLength: 0
    });

    // Ao selecionar um item do catálogo, preencher os campos
    $('#item_catalogo').on('select2:select', function(e) {
        var data = e.params.data;
        
        $('#tipo_item').val(data.tipo);
        $('#descricao').val(data.nome);
        $('#metragem').val(data.metragem || '');
        $('#valor_unitario').val(data.valor_formatado);
        $('#percentual_desconto').val(0);
        $('#quantidade').val(1);
        
        // Reaplica a máscara
        $('.money').mask('#.##0,00', {reverse: true});
        
        // Recalcula o total
        calcularTotalPreview();
    });

    // Limpar seleção do catálogo ao limpar
    $('#item_catalogo').on('select2:clear', function() {
        $('#tipo_item').val('');
        $('#descricao').val('');
        $('#metragem').val('');
        $('#valor_unitario').val('');
        $('#percentual_desconto').val(0);
        $('#valor_desconto').val('0');
        $('#valor_desconto_preview').val('');
        $('#valor_total_preview').val('');
    });
    
    // Alterar situação via AJAX
    $('.btn-alterar-situacao').on('click', function(e) {
        e.preventDefault();
        
        var novaSituacao = $(this).data('situacao');
        var confirmar = confirm('Deseja realmente alterar a situação do contrato?');
        
        if (!confirmar) return;
        
        $.ajax({
            type: 'POST',
            url: '<?php echo site_url('contratos/alterarsituacao'); ?>',
            data: {
                id: contratoId,
                situacao: novaSituacao,
                [csrfName]: csrfToken
            },
            dataType: 'json',
            success: function(response) {
                if (response.token) csrfToken = response.token;
                if (response.sucesso) {
                    location.reload();
                } else {
                    alert(response.erro || 'Erro ao alterar situação');
                }
            },
            error: function() {
                alert('Erro ao processar a solicitação');
            }
        });
    });

    // ================================
    // GERENCIAMENTO DE ITENS
    // ================================
    
    // Carrega itens na inicialização
    carregarItens();

    function carregarItens() {
        $.ajax({
            type: 'GET',
            url: '<?php echo site_url('contratoitens/itens/'); ?>' + contratoId,
            dataType: 'json',
            success: function(response) {
                var tbody = $('#tabelaItens tbody');
                tbody.empty();

                var colspan = podeAdicionarItens ? 9 : 8;

                if (response.data.length === 0) {
                    tbody.append('<tr><td colspan="' + colspan + '" class="text-center text-muted py-4"><i class="bx bx-inbox fs-1 d-block mb-2"></i>Nenhum item adicionado</td></tr>');
                } else {
                    $.each(response.data, function(index, item) {
                        var acoes = podeAdicionarItens ? '<td class="text-center">' + item.acoes + '</td>' : '';
                        tbody.append(
                            '<tr>' +
                                '<td>' + item.tipo_item + '</td>' +
                                '<td>' + item.descricao + '</td>' +
                                '<td>' + item.localizacao + '</td>' +
                                '<td>' + item.metragem + '</td>' +
                                '<td class="text-center">' + item.quantidade + '</td>' +
                                '<td class="text-end">' + item.valor_unitario + '</td>' +
                                '<td class="text-end text-danger">' + item.valor_desconto + '</td>' +
                                '<td class="text-end fw-bold">' + item.valor_total + '</td>' +
                                acoes +
                            '</tr>'
                        );
                    });
                }

                // Atualiza totais
                $('#totais-subtotal').text(response.totais.subtotal);
                $('#totais-desconto').text(response.totais.total_desconto);
                $('#totais-total').text(response.totais.total);

                // Mostra/esconde botão "Gerar Cobrança" baseado na quantidade de itens
                if (response.totais.quantidade_itens > 0 && podeAdicionarItens) {
                    $('#btnGerarCobranca').show();
                } else {
                    $('#btnGerarCobranca').hide();
                }
            }
        });
    }

    // Limpa modal ao abrir para novo item
    $('#modalItem').on('show.bs.modal', function(e) {
        if (!$(e.relatedTarget).hasClass('btn-editar-item')) {
            $('#formItem')[0].reset();
            $('#item_id').val('');
            $('#modalItemTitle').text('Adicionar Item');
            $('#percentual_desconto').val(0);
            $('#valor_desconto').val('0');
            $('#valor_desconto_preview').val('');
            $('#valor_total_preview').val('');
            $('#item_catalogo').val(null).trigger('change');
            $('.money').mask('#.##0,00', {reverse: true});
        }
    });

    // Calcular valor total em tempo real (com desconto em %)
    function calcularTotalPreview() {
        var quantidade = parseInt($('#quantidade').val()) || 0;
        var valorUnitario = parseFloat($('#valor_unitario').val().replace(/\./g, '').replace(',', '.')) || 0;
        var percentualDesconto = parseFloat($('#percentual_desconto').val()) || 0;
        
        var subtotal = quantidade * valorUnitario;
        var valorDesconto = (subtotal * percentualDesconto) / 100;
        var total = subtotal - valorDesconto;
        
        // Atualiza o campo hidden com o valor do desconto
        $('#valor_desconto').val(valorDesconto.toFixed(2));
        
        // Mostra o valor do desconto formatado
        $('#valor_desconto_preview').val('R$ ' + valorDesconto.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, "."));
        
        // Mostra o valor total formatado
        $('#valor_total_preview').val('R$ ' + total.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, "."));
    }

    $('#quantidade, #valor_unitario, #percentual_desconto').on('input change keyup', calcularTotalPreview);

    // Salvar item (adicionar ou atualizar)
    $('#formItem').on('submit', function(e) {
        e.preventDefault();

        var itemId = $('#item_id').val();
        var url = itemId ? '<?php echo site_url('contratoitens/atualizar'); ?>' : '<?php echo site_url('contratoitens/adicionar'); ?>';

        var formData = $(this).serialize() + '&' + csrfName + '=' + csrfToken;

        $.ajax({
            type: 'POST',
            url: url,
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                $('#btnSalvarItem').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Salvando...');
            },
            success: function(response) {
                if (response.token) csrfToken = response.token;
                
                if (response.sucesso) {
                    $('#modalItem').modal('hide');
                    carregarItens();
                    
                    // Recarrega página para atualizar totais do contrato
                    setTimeout(function() {
                        location.reload();
                    }, 500);
                } else {
                    alert(response.erro || 'Erro ao salvar item');
                }
            },
            error: function(xhr, status, error) {
                console.log('Erro AJAX:', xhr.responseText);
                alert('Erro: ' + (xhr.responseJSON?.erro || xhr.responseText || error || 'Erro desconhecido'));
            },
            complete: function() {
                $('#btnSalvarItem').prop('disabled', false).html('<i class="bx bx-save me-1"></i>Salvar Item');
            }
        });
    });

    // Editar item
    $(document).on('click', '.btn-editar-item', function() {
        var itemId = $(this).data('id');

        $.ajax({
            type: 'GET',
            url: '<?php echo site_url('contratoitens/buscar/'); ?>' + itemId,
            dataType: 'json',
            success: function(response) {
                if (response.erro) {
                    alert(response.erro);
                    return;
                }

                $('#item_id').val(response.id);
                $('#tipo_item').val(response.tipo_item);
                $('#descricao').val(response.descricao);
                $('#localizacao').val(response.localizacao);
                $('#metragem').val(response.metragem);
                $('#quantidade').val(response.quantidade);
                $('#valor_unitario').val(response.valor_unitario);
                $('#observacoes').val(response.observacoes);
                $('#modalItemTitle').text('Editar Item');

                // Calcular percentual de desconto baseado nos valores salvos
                var valorUnitario = parseFloat(response.valor_unitario.replace(/\./g, '').replace(',', '.')) || 0;
                var valorDesconto = parseFloat(response.valor_desconto.replace(/\./g, '').replace(',', '.')) || 0;
                var subtotal = response.quantidade * valorUnitario;
                var percentual = subtotal > 0 ? (valorDesconto / subtotal) * 100 : 0;
                $('#percentual_desconto').val(percentual.toFixed(2));

                $('.money').mask('#.##0,00', {reverse: true});
                calcularTotalPreview();

                $('#modalItem').modal('show');
            }
        });
    });

    // Remover item
    $(document).on('click', '.btn-remover-item', function() {
        var itemId = $(this).data('id');

        if (!confirm('Deseja realmente remover este item?')) return;

        $.ajax({
            type: 'POST',
            url: '<?php echo site_url('contratoitens/remover/'); ?>' + itemId,
            data: {
                [csrfName]: csrfToken
            },
            dataType: 'json',
            success: function(response) {
                if (response.token) csrfToken = response.token;
                
                if (response.sucesso) {
                    carregarItens();
                    
                    // Recarrega página para atualizar totais do contrato
                    setTimeout(function() {
                        location.reload();
                    }, 500);
                } else {
                    alert(response.erro || 'Erro ao remover item');
                }
            },
            error: function() {
                alert('Erro ao processar a solicitação');
            }
        });
    });
    
    // =====================================================
    // CREDENCIAMENTO - AÇÕES DE APROVAÇÃO
    // =====================================================
    
    // Aprovar todo credenciamento
    $('#btnAprovarTudo').on('click', function() {
        var credId = $(this).data('id');
        if (!confirm('Aprovar todo o credenciamento?')) return;
        
        $.ajax({
            type: 'POST',
            url: '<?= site_url('credenciamento/aprovarTudo') ?>',
            data: { credenciamento_id: credId, [csrfName]: csrfToken },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert(response.message || 'Erro ao aprovar');
                }
            },
            error: function() { alert('Erro ao processar'); }
        });
    });
    
    // Aprovar pessoa individual
    $(document).on('click', '.btn-aprovar-pessoa', function() {
        var id = $(this).data('id');
        
        $.ajax({
            type: 'POST',
            url: '<?= site_url('credenciamento/aprovarPessoa/') ?>' + id,
            data: { [csrfName]: csrfToken },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message || 'Erro ao aprovar');
                }
            },
            error: function() { alert('Erro ao processar'); }
        });
    });
    
    // Abrir modal de rejeição
    $(document).on('click', '.btn-rejeitar-pessoa', function() {
        var id = $(this).data('id');
        var nome = $(this).data('nome');
        $('#idRejeitar').val(id);
        $('#nomeRejeitar').text(nome);
        $('#motivoRejeicao').val('');
        $('#modalRejeitarPessoa').modal('show');
    });
    
    // Confirmar rejeição
    $('#btnConfirmarRejeicao').on('click', function() {
        var id = $('#idRejeitar').val();
        var motivo = $('#motivoRejeicao').val();
        
        $.ajax({
            type: 'POST',
            url: '<?= site_url('credenciamento/rejeitarPessoa/') ?>' + id,
            data: { motivo: motivo, [csrfName]: csrfToken },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#modalRejeitarPessoa').modal('hide');
                    location.reload();
                } else {
                    alert(response.message || 'Erro ao rejeitar');
                }
            },
            error: function() { alert('Erro ao processar'); }
        });
    });
    
    // Confirmar devolução
    $('#btnConfirmarDevolucao').on('click', function() {
        var credId = $(this).data('id');
        var obs = $('#observacaoDevolucao').val();
        
        $.ajax({
            type: 'POST',
            url: '<?= site_url('credenciamento/devolver') ?>',
            data: { credenciamento_id: credId, observacao: obs, [csrfName]: csrfToken },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    $('#modalDevolverCredenciamento').modal('hide');
                    location.reload();
                } else {
                    alert(response.message || 'Erro ao devolver');
                }
            },
            error: function() { alert('Erro ao processar'); }
        });
    });
    
});
</script>

<?php echo $this->endSection() ?>

