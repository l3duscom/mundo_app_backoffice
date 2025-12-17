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
.detail-card.cliente {
    border-left-color: #198754;
}
.detail-card.pedido {
    border-left-color: #ffc107;
}
.detail-card.evento {
    border-left-color: #0dcaf0;
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
.oferta-resumo {
    background: linear-gradient(135deg, #6f42c1 0%, #9b59b6 100%);
    color: white;
    border-radius: 10px;
    padding: 15px 20px;
    margin-bottom: 15px;
}
.oferta-resumo .oferta-titulo {
    font-size: 1.2rem;
    font-weight: 700;
    margin-bottom: 5px;
}
.oferta-resumo .oferta-ganho {
    font-size: 1.5rem;
    font-weight: 700;
}
</style>
<?php echo $this->endSection() ?>

<?php
// Função helper para formatar JSON de ingressos ORIGINAIS
function formatarIngressos($jsonString) {
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
function formatarIngressosUpgrade($jsonString) {
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
function formatarBeneficios($jsonString) {
    if (empty($jsonString) || $jsonString === '-') {
        return '<span class="text-muted">Nenhum benefício</span>';
    }
    
    $beneficios = json_decode($jsonString, true);
    if (!is_array($beneficios)) {
        // Tenta separar por vírgula ou quebra de linha
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

// Função helper para formatar detalhes da oferta
function formatarOfertaDetalhes($jsonString) {
    if (empty($jsonString) || $jsonString === '-') {
        return '<span class="text-muted">Nenhum detalhe</span>';
    }
    
    $dados = json_decode($jsonString, true);
    if (!is_array($dados)) {
        return '<span class="text-muted">' . nl2br(esc($jsonString)) . '</span>';
    }
    
    $html = '<div class="oferta-detalhes">';
    
    if (isset($dados['tipo_principal'])) {
        $html .= '<div class="mb-2"><strong>Tipo:</strong> ' . esc($dados['tipo_principal']) . '</div>';
    }
    if (isset($dados['ganho_total'])) {
        $html .= '<div class="mb-2"><strong>Ganho Total:</strong> <span class="text-success fw-bold">R$ ' . number_format($dados['ganho_total'], 2, ',', '.') . '</span></div>';
    }
    if (isset($dados['resumo_ofertas'])) {
        // resumo_ofertas pode ser um array ou string
        if (is_array($dados['resumo_ofertas'])) {
            $html .= '<div class="mb-2"><strong>Resumo:</strong> ' . esc(implode(', ', $dados['resumo_ofertas'])) . '</div>';
        } else {
            $html .= '<div class="mb-2"><strong>Resumo:</strong> ' . esc($dados['resumo_ofertas']) . '</div>';
        }
    }
    
    $html .= '</div>';
    
    return $html;
}
?>




<?php echo $this->section('conteudo') ?>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Reembolsos</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('refounds'); ?>">Solicitações</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detalhes #<?php echo $refound->id; ?></li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="<?php echo site_url('refounds'); ?>" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-2"></i>Voltar
        </a>
    </div>
</div>
<!--end breadcrumb-->

<!-- Status em Destaque -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body text-center py-4">
                <h5 class="text-muted mb-3">Status da Solicitação</h5>
                <?php 
                $statusLower = strtolower(trim($refound->status ?? ''));
                $statusTexto = $refound->status ?? '';
                
                // Tratamento para status vazio
                if (empty($statusLower)) {
                    $statusClass = 'bg-secondary';
                    $statusDisplay = 'SEM STATUS';
                } else {
                    $statusClass = match($statusLower) {
                        'pendente' => 'bg-warning text-dark',
                        'processando' => 'bg-info',
                        'concluido' => 'bg-success',
                        'cancelado' => 'bg-danger',
                        'erro' => 'bg-dark',
                        default => 'bg-secondary'
                    };
                    
                    // Se o status está mapeado, usar texto formatado
                    $statusDisplay = match($statusLower) {
                        'pendente' => 'PENDENTE',
                        'processando' => 'PROCESSANDO',
                        'concluido' => 'CONCLUÍDO',
                        'cancelado' => 'CANCELADO',
                        'erro' => 'ERRO',
                        default => strtoupper($statusTexto)
                    };
                }
                ?>
                <span class="badge <?php echo $statusClass; ?> status-badge-lg">
                    <?php echo esc($statusDisplay); ?>
                </span>
                
                <?php if ($refound->processado_em): ?>
                <p class="mt-3 mb-0 text-muted">
                    Processado em: <?php echo date('d/m/Y H:i', strtotime($refound->processado_em)); ?>
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Dados do Cliente -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100 detail-card cliente">
            <div class="card-header bg-transparent">
                <h6 class="mb-0"><i class="bx bx-user me-2"></i>Dados do Cliente</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <p class="info-label mb-1">Nome</p>
                        <p class="info-value"><?php echo esc($refound->cliente_nome ?? '-'); ?></p>
                    </div>
                    <div class="col-12">
                        <p class="info-label mb-1">E-mail</p>
                        <p class="info-value"><?php echo esc($refound->cliente_email ?? '-'); ?></p>
                    </div>
                    <div class="col-12">
                        <p class="info-label mb-1">ID do Cliente</p>
                        <p class="info-value"><?php echo esc($refound->cliente_id ?? '-'); ?></p>
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
                        <p class="info-value">
                            <a href="<?php echo site_url('pedidos/ingressos/' . $refound->pedido_id); ?>">
                                <?php echo esc($refound->pedido_codigo ?? '-'); ?>
                            </a>
                        </p>
                    </div>
                    <div class="col-6">
                        <p class="info-label mb-1">Valor Total</p>
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
                        <p class="info-label mb-1">Forma de Pagamento</p>
                        <p class="info-value"><?php echo esc($refound->pedido_forma_pagament ?? '-'); ?></p>
                    </div>
                    <div class="col-12">
                        <p class="info-label mb-1">Status do Pedido</p>
                        <p class="info-value"><?php echo esc($refound->pedido_status ?? '-'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                        <p class="info-value"><?php echo esc($refound->evento_nome ?? '-'); ?></p>
                    </div>
                    <div class="col-12">
                        <p class="info-label mb-1">Data de Início</p>
                        <p class="info-value">
                            <?php echo $refound->evento_data_inicio ? date('d/m/Y', strtotime($refound->evento_data_inicio)) : '-'; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tipo de Solicitação -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100 detail-card">
            <div class="card-header bg-transparent">
                <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Tipo de Solicitação</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <p class="info-label mb-1">Tipo</p>
                        <p class="info-value">
                            <?php 
                            $tipoClass = strtolower($refound->tipo_solicitacao ?? '') === 'upgrade' ? 'bg-purple' : 'bg-orange';
                            ?>
                            <span class="badge <?php echo $tipoClass; ?>"><?php echo esc($refound->tipo_solicitacao ?? '-'); ?></span>
                        </p>
                    </div>
                    <div class="col-6">
                        <p class="info-label mb-1">Aceito</p>
                        <p class="info-value">
                            <?php if ($refound->aceito): ?>
                                <span class="badge bg-success">Sim</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Não</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-12">
                        <p class="info-label mb-1">Data da Solicitação</p>
                        <p class="info-value">
                            <?php echo $refound->created_at ? date('d/m/Y H:i:s', strtotime($refound->created_at)) : '-'; ?>
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
                        <p class="info-label mb-1">Subtítulo</p>
                        <p class="info-value"><?php echo esc($refound->oferta_subtitulo ?? '-'); ?></p>
                    </div>
                    <div class="col-md-4">
                        <p class="info-label mb-1">Valor da Vantagem</p>
                        <p class="info-value fw-bold text-success">
                            R$ <?php echo number_format($refound->oferta_vantagem_valor ?? 0, 2, ',', '.'); ?>
                        </p>
                    </div>
                    <div class="col-md-4">
                        <p class="info-label mb-1">Opção Selecionada</p>
                        <p class="info-value"><?php echo esc($refound->opcao_selecionada ?? '-'); ?></p>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <!-- Ingressos Originais - Formatado -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <p class="info-label mb-2"><i class="bx bx-ticket me-1"></i>Ingressos Originais</p>
                        <div class="info-value">
                            <?php echo formatarIngressos($refound->ingressos_originais ?? ''); ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <p class="info-label mb-2"><i class="bx bx-up-arrow-alt me-1"></i>Ingressos para Upgrade</p>
                        <div class="info-value">
                            <?php echo formatarIngressosUpgrade($refound->ingressos_para_upgrade ?? ''); ?>
                        </div>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <!-- Detalhes da Oferta - Formatado -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <p class="info-label mb-2"><i class="bx bx-detail me-1"></i>Detalhes da Oferta</p>
                        <div class="info-value">
                            <?php echo formatarOfertaDetalhes($refound->oferta_detalhes ?? ''); ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <p class="info-label mb-2"><i class="bx bx-gift me-1"></i>Benefícios Apresentados</p>
                        <div class="info-value">
                            <?php echo formatarBeneficios($refound->beneficios_apresentados ?? ''); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Informações Técnicas e Observações -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-transparent">
                <h6 class="mb-0"><i class="bx bx-code-alt me-2"></i>Informações Técnicas</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <p class="info-label mb-1">IP da Solicitação</p>
                        <p class="info-value"><code><?php echo esc($refound->ip_solicitacao ?? '-'); ?></code></p>
                    </div>
                    <div class="col-12">
                        <p class="info-label mb-1">User Agent</p>
                        <p class="info-value small text-muted"><?php echo esc($refound->user_agent ?? '-'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-transparent">
                <h6 class="mb-0"><i class="bx bx-notepad me-2"></i>Observações</h6>
            </div>
            <div class="card-body">
                <p class="info-value"><?php echo nl2br(esc($refound->observacoes ?? 'Nenhuma observação registrada.')); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Ações -->
<?php if (strtolower($refound->status ?? '') === 'pendente'): ?>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex gap-3 justify-content-center">
                    <button type="button" class="btn btn-success btn-lg" id="btnAprovar">
                        <i class="bx bx-check me-2"></i>Aprovar Solicitação
                    </button>
                    <button type="button" class="btn btn-danger btn-lg" id="btnRejeitar">
                        <i class="bx bx-x me-2"></i>Rejeitar Solicitação
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php echo $this->endSection() ?>


<?php echo $this->section('scripts') ?>
<script>
$(document).ready(function() {
    $('#btnAprovar').on('click', function() {
        if (confirm('Deseja aprovar esta solicitação?')) {
            atualizarStatus('concluido');
        }
    });

    $('#btnRejeitar').on('click', function() {
        var observacao = prompt('Motivo da rejeição (opcional):');
        atualizarStatus('cancelado', observacao);
    });

    function atualizarStatus(status, observacoes) {
        console.log('Enviando status:', status); // Debug
        console.log('ID:', <?php echo $refound->id; ?>); // Debug
        
        $.ajax({
            url: '<?php echo site_url("refounds/atualizarStatus"); ?>',
            type: 'POST',
            data: {
                id: <?php echo $refound->id; ?>,
                status: status,
                observacoes: observacoes || '',
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(response) {
                console.log('Resposta:', response); // Debug
                if (response.sucesso) {
                    alert(response.sucesso);
                    location.reload();
                } else {
                    alert(response.erro || 'Erro ao processar solicitação');
                }
            },
            error: function(xhr, status, error) {
                console.log('Erro:', xhr.responseText); // Debug
                alert('Erro ao processar solicitação');
            }
        });
    }
});
</script>
<?php echo $this->endSection() ?>
