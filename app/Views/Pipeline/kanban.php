<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
/* Kanban Container - Pipedrive Style */
.kanban-container {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    overflow-y: hidden;
    padding: 0 0 12px 0;
    height: calc(100vh - 280px);
    max-height: calc(100vh - 280px);
}

/* Kanban Column */
.kanban-column {
    min-width: 280px;
    max-width: 280px;
    background: linear-gradient(180deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid rgba(0,0,0,0.05);
}

.kanban-column-header {
    padding: 14px;
    border-radius: 12px 12px 0 0;
    color: white;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.kanban-column-header .header-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.kanban-column-header .header-title {
    font-weight: 600;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.kanban-column-header .count-badge {
    background: rgba(255,255,255,0.25);
    padding: 2px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.kanban-column-header .header-total {
    font-size: 1rem;
    font-weight: 700;
    opacity: 0.95;
}

.kanban-column-body {
    flex: 1;
    padding: 10px;
    overflow-y: auto;
    max-height: calc(100vh - 380px);
    min-height: 150px;
}

/* Kanban Card */
.kanban-card {
    background: white;
    border-radius: 10px;
    padding: 12px;
    margin-bottom: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    cursor: grab;
    transition: all 0.2s ease;
    border-left: 4px solid transparent;
    position: relative;
}

.kanban-card:hover {
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}

.kanban-card:active {
    cursor: grabbing;
}

.kanban-card .card-header-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 6px;
}

.kanban-card .card-codigo {
    font-size: 0.7rem;
    color: #6c757d;
    font-weight: 500;
    background: #f8f9fa;
    padding: 2px 6px;
    border-radius: 4px;
}

.kanban-card .card-actions {
    display: flex;
    gap: 4px;
    opacity: 0;
    transition: opacity 0.2s;
}

.kanban-card:hover .card-actions {
    opacity: 1;
}

.kanban-card .card-actions a {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    color: #6c757d;
    background: #f8f9fa;
    text-decoration: none;
    font-size: 0.85rem;
    transition: all 0.15s;
}

.kanban-card .card-actions a:hover {
    background: #e9ecef;
    color: #0d6efd;
}

.kanban-card .card-nome {
    font-size: 0.9rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 6px;
    line-height: 1.3;
}

.kanban-card .card-nome a {
    color: #212529;
    text-decoration: none;
}

.kanban-card .card-nome a:hover {
    color: #0d6efd;
}

.kanban-card .card-info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}

.kanban-card .card-valor {
    font-size: 0.95rem;
    font-weight: 700;
    color: #198754;
}

.kanban-card .card-proxima-acao {
    font-size: 0.7rem;
    color: #6c757d;
    display: flex;
    align-items: center;
    gap: 4px;
}

.kanban-card .card-proxima-acao.atrasada {
    color: #dc3545;
    font-weight: 600;
}

.kanban-card .card-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin-top: 6px;
}

.kanban-card .badge {
    font-size: 0.65rem;
    padding: 2px 6px;
    font-weight: 500;
}

.kanban-card .card-vendedor {
    font-size: 0.7rem;
    color: #6c757d;
    margin-top: 6px;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Cores dos headers das colunas */
.kanban-header-novo { background: linear-gradient(135deg, #6c757d 0%, #495057 100%); }
.kanban-header-primeiro_contato { background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); }
.kanban-header-qualificado { background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); }
.kanban-header-proposta { background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%); }
.kanban-header-negociacao { background: linear-gradient(135deg, #fd7e14 0%, #dc6a0b 100%); }
.kanban-header-ganho { background: linear-gradient(135deg, #198754 0%, #146c43 100%); }
.kanban-header-perdido { background: linear-gradient(135deg, #dc3545 0%, #bb2d3b 100%); }

/* Bordas dos cards por temperatura */
.kanban-card[data-temperatura="frio"] { border-left-color: #17a2b8; }
.kanban-card[data-temperatura="morno"] { border-left-color: #ffc107; }
.kanban-card[data-temperatura="quente"] { border-left-color: #dc3545; }

/* Drag & Drop States */
.sortable-ghost {
    opacity: 0.4;
    background: #e3f2fd;
    border: 2px dashed #90caf9;
}

.sortable-chosen {
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    transform: rotate(2deg);
}

/* Summary Cards */
.summary-bar {
    display: flex;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.summary-card {
    background: white;
    border-radius: 12px;
    padding: 16px 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    min-width: 160px;
}

.summary-card .icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.summary-card .info h3 {
    margin: 0;
    font-size: 1.4rem;
    font-weight: 700;
}

.summary-card .info p {
    margin: 0;
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* View Toggle */
.view-toggle .btn {
    padding: 8px 14px;
    border-radius: 8px;
}

.view-toggle .btn.active {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
}

/* Loading overlay */
.kanban-loading {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.9);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    backdrop-filter: blur(4px);
}

.kanban-loading.show {
    display: flex;
}

/* Scrollbars */
.kanban-container::-webkit-scrollbar {
    height: 8px;
}

.kanban-container::-webkit-scrollbar-track {
    background: #e9ecef;
    border-radius: 4px;
}

.kanban-container::-webkit-scrollbar-thumb {
    background: #adb5bd;
    border-radius: 4px;
}

.kanban-container::-webkit-scrollbar-thumb:hover {
    background: #6c757d;
}

.kanban-column-body::-webkit-scrollbar {
    width: 5px;
}

.kanban-column-body::-webkit-scrollbar-track {
    background: transparent;
}

.kanban-column-body::-webkit-scrollbar-thumb {
    background: #dee2e6;
    border-radius: 3px;
}

/* Empty column state */
.kanban-empty {
    text-align: center;
    padding: 30px 15px;
    color: #adb5bd;
}

.kanban-empty i {
    font-size: 2rem;
    margin-bottom: 10px;
}

/* Filtros */
.filtros-bar {
    display: flex;
    gap: 12px;
    margin-bottom: 16px;
    flex-wrap: wrap;
    align-items: center;
}

.filtros-bar .form-select {
    max-width: 200px;
}

@media (max-width: 768px) {
    .kanban-column {
        min-width: 260px;
    }
    .summary-bar {
        flex-direction: column;
    }
}
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<!-- Loading Overlay -->
<div class="kanban-loading" id="kanbanLoading">
    <div class="text-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
        <p class="mt-3 text-muted fw-medium">Atualizando pipeline...</p>
    </div>
</div>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-dark fw-bold">Pipeline de Vendas</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Kanban</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="<?php echo site_url('pipeline/criar'); ?>" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i>Novo Lead
        </a>
    </div>
</div>
<!--end breadcrumb-->

<!-- Filtros -->
<div class="filtros-bar">
    <select class="form-select form-select-sm" id="filtroEvento">
        <option value="">Todos os eventos</option>
        <?php foreach ($eventos as $evento): ?>
            <option value="<?php echo $evento['id']; ?>"><?php echo esc($evento['nome']); ?></option>
        <?php endforeach; ?>
    </select>
</div>

<!-- Summary Cards -->
<div class="summary-bar" id="summaryBar">
    <div class="summary-card">
        <div class="icon" style="background: rgba(13, 110, 253, 0.1); color: #0d6efd;">
            <i class="bx bx-user-plus"></i>
        </div>
        <div class="info">
            <h3 id="totalLeads">-</h3>
            <p>Leads</p>
        </div>
    </div>
    <div class="summary-card">
        <div class="icon" style="background: rgba(13, 202, 240, 0.1); color: #0dcaf0;">
            <i class="bx bx-dollar-circle"></i>
        </div>
        <div class="info">
            <h3 id="totalValor">-</h3>
            <p>Valor Total</p>
        </div>
    </div>
    <div class="summary-card">
        <div class="icon" style="background: rgba(25, 135, 84, 0.1); color: #198754;">
            <i class="bx bx-check-circle"></i>
        </div>
        <div class="info">
            <h3 id="totalGanhos">-</h3>
            <p>Ganhos</p>
        </div>
    </div>
    <div class="summary-card">
        <div class="icon" style="background: rgba(220, 53, 69, 0.1); color: #dc3545;">
            <i class="bx bx-x-circle"></i>
        </div>
        <div class="info">
            <h3 id="taxaConversao">-</h3>
            <p>Taxa Conversão</p>
        </div>
    </div>
</div>

<!-- Kanban Board -->
<div class="kanban-container" id="kanbanBoard">
    <!-- As colunas serão carregadas via JavaScript -->
    <div class="text-center w-100 py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
        <p class="mt-2 text-muted">Carregando leads...</p>
    </div>
</div>

<!-- Modal Motivo Perda -->
<div class="modal fade" id="modalMotivoPerda" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Motivo da Perda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="leadIdPerda">
                <input type="hidden" id="etapaAnteriorPerda">
                <div class="mb-3">
                    <label class="form-label">Por que este lead foi perdido?</label>
                    <select class="form-select" id="motivoPerdaSelect">
                        <option value="">Selecione...</option>
                        <option value="Preço alto">Preço alto</option>
                        <option value="Optou pela concorrência">Optou pela concorrência</option>
                        <option value="Sem orçamento">Sem orçamento</option>
                        <option value="Não respondeu">Não respondeu</option>
                        <option value="Desistiu do evento">Desistiu do evento</option>
                        <option value="Outro">Outro</option>
                    </select>
                </div>
                <div class="mb-3" id="motivoOutroContainer" style="display: none;">
                    <label class="form-label">Qual motivo?</label>
                    <input type="text" class="form-control" id="motivoOutro">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarPerda">Confirmar Perda</button>
            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>


<?php echo $this->section('scripts') ?>

<!-- SortableJS CDN -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
$(document).ready(function() {
    
    // Configuração das etapas
    const etapas = {
        'novo': { nome: 'Novo', icone: 'bx bx-user-plus' },
        'primeiro_contato': { nome: 'Primeiro Contato', icone: 'bx bx-phone' },
        'qualificado': { nome: 'Qualificado', icone: 'bx bx-check' },
        'proposta': { nome: 'Proposta', icone: 'bx bx-file' },
        'negociacao': { nome: 'Negociação', icone: 'bx bx-conversation' },
        'ganho': { nome: 'Ganho', icone: 'bx bx-trophy' },
        'perdido': { nome: 'Perdido', icone: 'bx bx-x-circle' }
    };

    let dadosKanban = {};

    // Carregar dados
    function carregarKanban() {
        var eventoId = $('#filtroEvento').val();
        
        $.ajax({
            url: '<?php echo site_url("pipeline/recuperaLeadsKanban"); ?>',
            type: 'GET',
            data: { evento_id: eventoId },
            dataType: 'json',
            success: function(data) {
                dadosKanban = data.etapas;
                renderizarKanban(data.etapas);
                atualizarSummary(data.estatisticas);
            },
            error: function() {
                $('#kanbanBoard').html('<div class="alert alert-danger m-3">Erro ao carregar leads.</div>');
            }
        });
    }

    // Atualizar cards de resumo
    function atualizarSummary(stats) {
        $('#totalLeads').text(stats.total);
        $('#totalValor').text(formatMoney(stats.valor_total));
        $('#totalGanhos').text(stats.ganhos);
        $('#taxaConversao').text(stats.taxa_conversao + '%');
    }

    function formatMoney(value) {
        return 'R$ ' + parseFloat(value).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    // Renderizar Kanban
    function renderizarKanban(data) {
        var html = '';
        
        for (var etapa in etapas) {
            var config = etapas[etapa];
            var dadosEtapa = data[etapa] || { cards: [], count: 0, total_valor_formatado: 'R$ 0,00' };
            var leads = dadosEtapa.cards || [];
            
            html += '<div class="kanban-column">';
            html += '<div class="kanban-column-header kanban-header-' + etapa + '">';
            html += '<div class="header-top">';
            html += '<span class="header-title"><i class="' + config.icone + '"></i>' + config.nome + '</span>';
            html += '<span class="count-badge">' + (dadosEtapa.count || 0) + '</span>';
            html += '</div>';
            html += '<div class="header-total">' + (dadosEtapa.total_valor_formatado || 'R$ 0,00') + '</div>';
            html += '</div>';
            html += '<div class="kanban-column-body" data-etapa="' + etapa + '">';
            
            if (leads.length === 0) {
                html += '<div class="kanban-empty"><i class="bx bx-inbox d-block"></i><small>Nenhum lead</small></div>';
            } else {
                leads.forEach(function(lead) {
                    html += renderizarCard(lead);
                });
            }
            
            html += '</div>';
            html += '</div>';
        }
        
        $('#kanbanBoard').html(html);
        inicializarSortable();
    }

    // Renderizar Card
    function renderizarCard(lead) {
        var html = '<div class="kanban-card" data-id="' + lead.id + '" data-etapa="' + lead.etapa + '" data-temperatura="' + lead.temperatura + '">';
        
        // Header row with code and actions
        html += '<div class="card-header-row">';
        html += '<span class="card-codigo">' + (lead.codigo || '#' + lead.id) + '</span>';
        html += '<div class="card-actions">';
        html += '<a href="<?php echo site_url("pipeline/exibir"); ?>/' + lead.id + '" title="Ver detalhes"><i class="bx bx-show"></i></a>';
        html += '<a href="<?php echo site_url("pipeline/editar"); ?>/' + lead.id + '" title="Editar"><i class="bx bx-edit"></i></a>';
        html += '</div>';
        html += '</div>';
        
        // Nome
        html += '<div class="card-nome">';
        html += '<a href="<?php echo site_url("pipeline/exibir"); ?>/' + lead.id + '">' + lead.nome + '</a>';
        html += '</div>';
        
        // Valor e próxima ação
        html += '<div class="card-info-row">';
        html += '<span class="card-valor">' + lead.valor_estimado + '</span>';
        if (lead.proxima_acao && lead.proxima_acao !== '-') {
            var atrasadaClass = lead.proxima_acao_atrasada ? 'atrasada' : '';
            html += '<span class="card-proxima-acao ' + atrasadaClass + '"><i class="bx bx-calendar"></i>' + lead.proxima_acao + '</span>';
        }
        html += '</div>';
        
        // Badges
        html += '<div class="card-badges">';
        html += lead.temperatura_badge;
        if (lead.segmento) {
            html += '<span class="badge bg-light text-dark">' + lead.segmento + '</span>';
        }
        if (lead.convertido) {
            html += '<span class="badge bg-success"><i class="bx bx-check"></i> Convertido</span>';
        }
        html += '</div>';
        
        // Vendedor
        if (lead.vendedor && lead.vendedor !== '-') {
            html += '<div class="card-vendedor"><i class="bx bx-user"></i>' + lead.vendedor + '</div>';
        }
        
        html += '</div>';
        
        return html;
    }

    // Inicializar Sortable (Drag & Drop)
    function inicializarSortable() {
        document.querySelectorAll('.kanban-column-body').forEach(function(column) {
            new Sortable(column, {
                group: 'pipeline',
                animation: 200,
                easing: 'cubic-bezier(0.25, 1, 0.5, 1)',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                onEnd: function(evt) {
                    var cardEl = evt.item;
                    var leadId = cardEl.getAttribute('data-id');
                    var novaEtapa = evt.to.getAttribute('data-etapa');
                    var etapaAnterior = cardEl.getAttribute('data-etapa');
                    
                    // Remove empty state if exists
                    var emptyEl = evt.to.querySelector('.kanban-empty');
                    if (emptyEl) emptyEl.remove();
                    
                    if (novaEtapa !== etapaAnterior) {
                        // Se movendo para "perdido", mostra modal de motivo
                        if (novaEtapa === 'perdido') {
                            $('#leadIdPerda').val(leadId);
                            $('#etapaAnteriorPerda').val(etapaAnterior);
                            $('#modalMotivoPerda').modal('show');
                        } else {
                            atualizarEtapa(leadId, novaEtapa, cardEl, etapaAnterior);
                        }
                    }
                }
            });
        });
    }

    // Mostrar/ocultar campo "outro" no modal
    $('#motivoPerdaSelect').on('change', function() {
        if ($(this).val() === 'Outro') {
            $('#motivoOutroContainer').show();
        } else {
            $('#motivoOutroContainer').hide();
        }
    });

    // Confirmar perda
    $('#btnConfirmarPerda').on('click', function() {
        var leadId = $('#leadIdPerda').val();
        var etapaAnterior = $('#etapaAnteriorPerda').val();
        var motivo = $('#motivoPerdaSelect').val();
        
        if (motivo === 'Outro') {
            motivo = $('#motivoOutro').val();
        }
        
        if (!motivo) {
            alert('Por favor, selecione um motivo.');
            return;
        }
        
        var cardEl = document.querySelector('.kanban-card[data-id="' + leadId + '"]');
        atualizarEtapa(leadId, 'perdido', cardEl, etapaAnterior, motivo);
        $('#modalMotivoPerda').modal('hide');
    });

    // Atualizar etapa via AJAX
    function atualizarEtapa(leadId, novaEtapa, cardEl, etapaAnterior, motivoPerda = null) {
        $('#kanbanLoading').addClass('show');
        
        var data = {
            id: leadId,
            etapa: novaEtapa,
            '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>'
        };
        
        if (motivoPerda) {
            data.motivo_perda = motivoPerda;
        }
        
        $.ajax({
            url: '<?php echo site_url("pipeline/alterarEtapa"); ?>',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                $('#kanbanLoading').removeClass('show');
                
                if (response.erro) {
                    // Reverter
                    var colunaOriginal = document.querySelector('.kanban-column-body[data-etapa="' + etapaAnterior + '"]');
                    colunaOriginal.appendChild(cardEl);
                    
                    if (response.requer_motivo) {
                        $('#leadIdPerda').val(leadId);
                        $('#etapaAnteriorPerda').val(etapaAnterior);
                        $('#modalMotivoPerda').modal('show');
                    } else {
                        Lobibox.notify('error', {
                            pauseDelayOnHover: true,
                            continueDelayOnInactiveTab: false,
                            position: 'top right',
                            msg: response.erro
                        });
                    }
                } else {
                    // Atualizar atributo do card
                    cardEl.setAttribute('data-etapa', novaEtapa);
                    
                    // Recarregar para atualizar totais
                    carregarKanban();
                    
                    Lobibox.notify('success', {
                        pauseDelayOnHover: true,
                        continueDelayOnInactiveTab: false,
                        position: 'top right',
                        msg: 'Etapa atualizada com sucesso!'
                    });
                }
            },
            error: function() {
                $('#kanbanLoading').removeClass('show');
                
                // Reverter
                var colunaOriginal = document.querySelector('.kanban-column-body[data-etapa="' + etapaAnterior + '"]');
                colunaOriginal.appendChild(cardEl);
                
                Lobibox.notify('error', {
                    pauseDelayOnHover: true,
                    continueDelayOnInactiveTab: false,
                    position: 'top right',
                    msg: 'Erro ao atualizar etapa.'
                });
            }
        });
    }

    // Carregar ao iniciar
    carregarKanban();

    // Filtros
    $('#filtroEvento').on('change', function() {
        carregarKanban();
    });
});
</script>

<?php echo $this->endSection() ?>
