<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>
<style>
.info-label {
    font-weight: 600;
    color: #6c757d;
    font-size: 0.85rem;
    text-transform: uppercase;
}
.info-value {
    font-size: 1rem;
    margin-bottom: 1rem;
}
.status-badge-lg {
    font-size: 1.2rem;
    padding: 0.75rem 1.5rem;
}
.bg-purple {
    background-color: #6f42c1 !important;
    color: white;
}
.bg-orange {
    background-color: #fd7e14 !important;
    color: white;
}
.detail-card {
    border-left: 4px solid #0d6efd;
}
.detail-card.evento {
    border-left-color: #0dcaf0;
}
.detail-card.pedido {
    border-left-color: #ffc107;
}
.detail-card.upgrade {
    border-left-color: #6f42c1;
}

/* Estilos para exibição de dados formatados */
.ingresso-item {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 8px;
    padding: 12px 15px;
    margin-bottom: 10px;
    border-left: 3px solid #6f42c1;
}
.ingresso-item .ingresso-nome {
    font-weight: 600;
    color: #333;
    font-size: 0.95rem;
}
.ingresso-item .ingresso-codigo {
    font-family: monospace;
    font-size: 0.8rem;
    color: #6c757d;
    background: #fff;
    padding: 2px 6px;
    border-radius: 4px;
}
.ingresso-item .ingresso-valor {
    font-weight: 600;
    color: #198754;
}
.beneficio-item {
    display: flex;
    align-items: flex-start;
    padding: 8px 0;
    border-bottom: 1px solid #e9ecef;
}
.beneficio-item:last-child {
    border-bottom: none;
}
.beneficio-item i {
    color: #198754;
    margin-right: 10px;
    margin-top: 3px;
}
.beneficio-item span {
    flex: 1;
}
.timeline-progress {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 0;
}
.timeline-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    position: relative;
}
.timeline-step::after {
    content: '';
    position: absolute;
    top: 20px;
    left: 50%;
    width: 100%;
    height: 3px;
    background: #e9ecef;
    z-index: 0;
}
.timeline-step:last-child::after {
    display: none;
}
.timeline-step.active::after {
    background: linear-gradient(90deg, #198754 50%, #e9ecef 50%);
}
.timeline-step.completed::after {
    background: #198754;
}
.timeline-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    color: #6c757d;
    z-index: 1;
    transition: all 0.3s ease;
}
.timeline-step.active .timeline-circle,
.timeline-step.completed .timeline-circle {
    background: #198754;
    color: white;
}
.timeline-step.rejected .timeline-circle {
    background: #dc3545;
    color: white;
}
.timeline-label {
    margin-top: 8px;
    font-size: 0.8rem;
    color: #6c757d;
    text-align: center;
}
.timeline-step.active .timeline-label,
.timeline-step.completed .timeline-label {
    color: #198754;
    font-weight: 600;
}
</style>
<?php echo $this->endSection() ?>

<?php
// Função helper para formatar JSON de ingressos ORIGINAIS
function formatarIngressosCliente($jsonString) {
    if (empty($jsonString) || $jsonString === '-') {
        return '<span class="text-muted">Nenhum ingresso</span>';
    }
    
    $ingressos = json_decode($jsonString, true);
    if (!is_array($ingressos)) {
        return '<span class="text-muted">' . esc($jsonString) . '</span>';
    }
    
    $html = '<div class="ingressos-list">';
    foreach ($ingressos as $ingresso) {
        $nome = $ingresso['nome'] ?? $ingresso['ingresso_nome'] ?? $ingresso['tipo'] ?? 'Ingresso';
        $codigo = $ingresso['codigo'] ?? $ingresso['ingresso_codigo'] ?? '';
        $valorNumerico = $ingresso['valor'] ?? $ingresso['valor_original'] ?? null;
        $valor = $valorNumerico !== null ? 'R$ ' . number_format(floatval($valorNumerico), 2, ',', '.') : '';
        
        $html .= '<div class="ingresso-item">';
        $html .= '<div class="d-flex justify-content-between align-items-center">';
        $html .= '<span class="ingresso-nome"><i class="bx bx-ticket me-2"></i>' . esc($nome) . '</span>';
        if ($valor) {
            $html .= '<span class="ingresso-valor">' . $valor . '</span>';
        }
        $html .= '</div>';
        if ($codigo) {
            $html .= '<div class="mt-1"><span class="ingresso-codigo">' . esc($codigo) . '</span></div>';
        }
        $html .= '</div>';
    }
    $html .= '</div>';
    
    return $html;
}

// Função helper para formatar JSON de ingressos PARA UPGRADE (mostra a oferta selecionada)
function formatarIngressosUpgradeCliente($jsonString) {
    if (empty($jsonString) || $jsonString === '-') {
        return '<span class="text-muted">Nenhum ingresso</span>';
    }
    
    $ingressos = json_decode($jsonString, true);
    if (!is_array($ingressos)) {
        return '<span class="text-muted">' . esc($jsonString) . '</span>';
    }
    
    $html = '<div class="ingressos-list">';
    foreach ($ingressos as $ingresso) {
        // Para upgrade, usar o tipo da oferta como nome principal
        $oferta = $ingresso['oferta'] ?? [];
        $nome = $oferta['tipo'] ?? $oferta['titulo'] ?? $ingresso['nome'] ?? $ingresso['ingresso_nome'] ?? 'Ingresso';
        $codigo = $ingresso['ingresso_codigo'] ?? $ingresso['codigo'] ?? '';
        
        // Valor do ganho da oferta
        $valorNumerico = $oferta['ganho'] ?? $ingresso['preco'] ?? $ingresso['valor'] ?? null;
        $valor = $valorNumerico !== null ? 'Ganho de R$ ' . number_format(floatval($valorNumerico), 2, ',', '.') : '';
        
        $html .= '<div class="ingresso-item" style="border-left-color: #198754;">';
        $html .= '<div class="d-flex justify-content-between align-items-center">';
        $html .= '<span class="ingresso-nome"><i class="bx bx-up-arrow-alt me-2 text-success"></i>' . esc($nome) . '</span>';
        if ($valor) {
            $html .= '<span class="ingresso-valor text-success">' . $valor . '</span>';
        }
        $html .= '</div>';
        if ($codigo) {
            $html .= '<div class="mt-1"><span class="ingresso-codigo">' . esc($codigo) . '</span></div>';
        }
        $html .= '</div>';
    }
    $html .= '</div>';
    
    return $html;
}

// Função helper para formatar benefícios
function formatarBeneficiosCliente($jsonString) {
    if (empty($jsonString) || $jsonString === '-') {
        return '<span class="text-muted">Nenhum benefício</span>';
    }
    
    $beneficios = json_decode($jsonString, true);
    if (!is_array($beneficios)) {
        $beneficios = preg_split('/[,\n]+/', $jsonString);
    }
    
    if (empty($beneficios)) {
        return '<span class="text-muted">' . esc($jsonString) . '</span>';
    }
    
    $html = '<div class="beneficios-list">';
    foreach ($beneficios as $beneficio) {
        $texto = is_string($beneficio) ? trim($beneficio) : ($beneficio['texto'] ?? $beneficio['nome'] ?? '');
        if (!empty($texto)) {
            $html .= '<div class="beneficio-item">';
            $html .= '<i class="bx bx-check-circle"></i>';
            $html .= '<span>' . esc($texto) . '</span>';
            $html .= '</div>';
        }
    }
    $html .= '</div>';
    
    return $html;
}
?>


<?php echo $this->section('conteudo') ?>

<div class="row justify-content-center">
    <div class="col-lg-12 col-xl-10">
        
        <!--breadcrumb-->
        <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3 text-muted">Minhas Solicitações</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="<?php echo site_url('pedidos'); ?>"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item"><a href="<?php echo site_url('pedidos/meus-refounds'); ?>">Solicitações</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Detalhes #<?php echo $refound->id; ?></li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a href="<?php echo site_url('pedidos/meus-refounds'); ?>" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-2"></i>Voltar
                </a>
            </div>
        </div>
        <!--end breadcrumb-->

        <!-- Status em Destaque com Timeline -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-4">
                <h5 class="text-center text-muted mb-4">Status da Solicitação</h5>
                
                <?php 
                $statusLower = strtolower(trim($refound->status ?? ''));
                
                $statusClass = match($statusLower) {
                    'pendente' => 'bg-warning text-dark',
                    'processando' => 'bg-info',
                    'concluido' => 'bg-success',
                    'cancelado' => 'bg-danger',
                    'erro' => 'bg-dark',
                    default => 'bg-secondary'
                };
                
                $statusDisplay = match($statusLower) {
                    'pendente' => 'PENDENTE',
                    'processando' => 'PROCESSANDO',
                    'concluido' => 'CONCLUÍDO',
                    'cancelado' => 'CANCELADO',
                    'erro' => 'ERRO',
                    default => strtoupper($refound->status ?? 'N/A')
                };
                ?>
                
                <div class="text-center mb-4">
                    <span class="badge <?php echo $statusClass; ?> status-badge-lg">
                        <?php echo esc($statusDisplay); ?>
                    </span>
                </div>
                
                <!-- Timeline de Progresso -->
                <div class="timeline-progress px-4">
                    <div class="timeline-step <?= $statusLower !== '' ? 'completed' : 'active' ?>">
                        <div class="timeline-circle"><i class="bx bx-send"></i></div>
                        <span class="timeline-label">Enviada</span>
                    </div>
                    <div class="timeline-step <?= in_array($statusLower, ['processando', 'concluido', 'cancelado']) ? 'completed' : ($statusLower === 'pendente' ? 'active' : '') ?>">
                        <div class="timeline-circle"><i class="bx bx-search"></i></div>
                        <span class="timeline-label">Em Análise</span>
                    </div>
                    <div class="timeline-step <?= $statusLower === 'cancelado' ? 'rejected' : ($statusLower === 'concluido' ? 'completed' : '') ?>">
                        <div class="timeline-circle">
                            <i class="bx <?= $statusLower === 'cancelado' ? 'bx-x' : 'bx-check' ?>"></i>
                        </div>
                        <span class="timeline-label"><?= $statusLower === 'cancelado' ? 'Cancelada' : 'Concluída' ?></span>
                    </div>
                </div>
                
                <?php if ($refound->processado_em): ?>
                <p class="text-center mt-3 mb-0 text-muted">
                    Processado em: <?php echo date('d/m/Y H:i', strtotime($refound->processado_em)); ?>
                </p>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <!-- Dados do Evento -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100 detail-card evento">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="bx bx-calendar-event me-2"></i>Dados do Evento</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <p class="info-label mb-1">Evento</p>
                                <p class="info-value fw-bold"><?php echo esc($refound->evento_nome ?? '-'); ?></p>
                            </div>
                            <div class="col-12">
                                <p class="info-label mb-1">Data do Evento</p>
                                <p class="info-value">
                                    <?php echo $refound->evento_data_inicio ? date('d/m/Y', strtotime($refound->evento_data_inicio)) : '-'; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dados do Pedido -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100 detail-card pedido">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="bx bx-cart me-2"></i>Dados do Pedido</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <p class="info-label mb-1">Código</p>
                                <p class="info-value fw-bold"><?php echo esc($refound->pedido_codigo ?? '-'); ?></p>
                            </div>
                            <div class="col-6">
                                <p class="info-label mb-1">Valor</p>
                                <p class="info-value fw-bold text-success">
                                    R$ <?php echo number_format($refound->pedido_valor_total ?? 0, 2, ',', '.'); ?>
                                </p>
                            </div>
                            <div class="col-6">
                                <p class="info-label mb-1">Data da Compra</p>
                                <p class="info-value">
                                    <?php echo $refound->pedido_data_compra ? date('d/m/Y', strtotime($refound->pedido_data_compra)) : '-'; ?>
                                </p>
                            </div>
                            <div class="col-6">
                                <p class="info-label mb-1">Data da Solicitação</p>
                                <p class="info-value">
                                    <?php echo $refound->created_at ? date('d/m/Y H:i', strtotime($refound->created_at)) : '-'; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($refound->tipo_solicitacao === 'upgrade' || $refound->tipo_upgrade): ?>
        <!-- Dados do Upgrade -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card shadow-sm detail-card upgrade">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="bx bx-up-arrow-alt me-2"></i>Detalhes do Upgrade</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <p class="info-label mb-1">Tipo de Upgrade</p>
                                <p class="info-value"><?php echo esc($refound->tipo_upgrade ?? '-'); ?></p>
                            </div>
                            <div class="col-md-4">
                                <p class="info-label mb-1">Título da Oferta</p>
                                <p class="info-value"><?php echo esc($refound->oferta_titulo ?? '-'); ?></p>
                            </div>
                            <div class="col-md-4">
                                <p class="info-label mb-1">Valor da Vantagem</p>
                                <p class="info-value fw-bold text-success">
                                    R$ <?php echo number_format($refound->oferta_vantagem_valor ?? 0, 2, ',', '.'); ?>
                                </p>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- Ingressos -->
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <p class="info-label mb-2"><i class="bx bx-ticket me-1"></i>Ingressos Originais</p>
                                <div class="info-value">
                                    <?php echo formatarIngressosCliente($refound->ingressos_originais ?? ''); ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <p class="info-label mb-2"><i class="bx bx-up-arrow-alt me-1"></i>Ingressos para Upgrade</p>
                                <div class="info-value">
                                    <?php echo formatarIngressosUpgradeCliente($refound->ingressos_para_upgrade ?? ''); ?>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- Benefícios -->
                        <div class="row">
                            <div class="col-12">
                                <p class="info-label mb-2"><i class="bx bx-gift me-1"></i>Benefícios Apresentados</p>
                                <div class="info-value">
                                    <?php echo formatarBeneficiosCliente($refound->beneficios_apresentados ?? ''); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($refound->observacoes): ?>
        <!-- Observações -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="bx bx-message-detail me-2"></i>Observações</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0"><?php echo nl2br(esc($refound->observacoes)); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Voltar -->
        <div class="mt-2 mb-4">
            <a href="<?= site_url('pedidos/meus-refounds') ?>" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i>Voltar às Solicitações
            </a>
        </div>
        
    </div>
</div>

<?php echo $this->endSection() ?>


<?php echo $this->section('scripts') ?>
<?php echo $this->endSection() ?>
