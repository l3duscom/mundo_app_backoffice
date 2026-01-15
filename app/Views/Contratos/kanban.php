<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
/* Kanban Container - Pipedrive Style */
.kanban-container {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding: 0 0 20px 0;
    min-height: calc(100vh - 280px);
}

/* Kanban Column */
.kanban-column {
    min-width: 300px;
    max-width: 300px;
    background: linear-gradient(180deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid rgba(0,0,0,0.05);
}

.kanban-column-header {
    padding: 16px;
    border-radius: 12px 12px 0 0;
    color: white;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.kanban-column-header .header-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.kanban-column-header .header-title {
    font-weight: 600;
    font-size: 0.95rem;
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
    font-size: 1.1rem;
    font-weight: 700;
    opacity: 0.95;
}

.kanban-column-body {
    flex: 1;
    padding: 12px;
    overflow-y: auto;
    max-height: calc(100vh - 350px);
    min-height: 150px;
}

/* Kanban Card - Pipedrive Style */
.kanban-card {
    background: white;
    border-radius: 10px;
    padding: 14px;
    margin-bottom: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    cursor: grab;
    transition: all 0.2s ease;
    border-left: 4px solid transparent;
    position: relative;
}

.kanban-card:hover {
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
    transform: translateY(-3px);
}

.kanban-card:active {
    cursor: grabbing;
}

.kanban-card .card-header-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}

.kanban-card .card-codigo {
    font-size: 0.75rem;
    color: #6c757d;
    font-weight: 500;
    background: #f8f9fa;
    padding: 2px 8px;
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
    width: 26px;
    height: 26px;
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

.kanban-card .card-expositor {
    font-size: 0.95rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 6px;
    line-height: 1.3;
}

.kanban-card .card-expositor a {
    color: #212529;
    text-decoration: none;
}

.kanban-card .card-expositor a:hover {
    color: #0d6efd;
}

.kanban-card .card-valor-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.kanban-card .card-valor {
    font-size: 1rem;
    font-weight: 700;
    color: #198754;
}

.kanban-card .card-data {
    font-size: 0.75rem;
    color: #6c757d;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Progress Bar */
.kanban-card .progress {
    height: 5px;
    border-radius: 3px;
    background: #e9ecef;
    margin-bottom: 10px;
}

.kanban-card .progress-bar {
    border-radius: 3px;
    transition: width 0.3s ease;
}

.kanban-card .card-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}

.kanban-card .badge {
    font-size: 0.65rem;
    padding: 3px 7px;
    font-weight: 500;
}

/* Cores dos headers das colunas - Gradientes modernos */
.kanban-header-proposta { background: linear-gradient(135deg, #6c757d 0%, #495057 100%); }
.kanban-header-proposta_aceita { background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); }
.kanban-header-contrato_assinado { background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); }
.kanban-header-aguardando_credenciamento { background: linear-gradient(135deg, #20c997 0%, #1aa179 100%); }
.kanban-header-pagamento_aberto { background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: #212529; }
.kanban-header-pagamento_andamento { background: linear-gradient(135deg, #fd7e14 0%, #dc6a0b 100%); }
.kanban-header-aguardando_contrato { background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%); }
.kanban-header-pagamento_confirmado { background: linear-gradient(135deg, #198754 0%, #146c43 100%); }
.kanban-header-finalizado { background: linear-gradient(135deg, #198754 0%, #146c43 100%); }
.kanban-header-cancelado { background: linear-gradient(135deg, #dc3545 0%, #bb2d3b 100%); }
.kanban-header-banido { background: linear-gradient(135deg, #212529 0%, #000000 100%); }

/* Bordas dos cards por situação */
.kanban-card[data-situacao="proposta"] { border-left-color: #6c757d; }
.kanban-card[data-situacao="proposta_aceita"] { border-left-color: #17a2b8; }
.kanban-card[data-situacao="contrato_assinado"] { border-left-color: #0d6efd; }
.kanban-card[data-situacao="aguardando_credenciamento"] { border-left-color: #20c997; }
.kanban-card[data-situacao="pagamento_aberto"] { border-left-color: #ffc107; }
.kanban-card[data-situacao="pagamento_andamento"] { border-left-color: #fd7e14; }
.kanban-card[data-situacao="aguardando_contrato"] { border-left-color: #6f42c1; }
.kanban-card[data-situacao="pagamento_confirmado"] { border-left-color: #198754; }
.kanban-card[data-situacao="finalizado"] { border-left-color: #198754; }
.kanban-card[data-situacao="cancelado"] { border-left-color: #dc3545; }
.kanban-card[data-situacao="banido"] { border-left-color: #212529; }

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
    min-width: 180px;
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
    font-size: 1.5rem;
    font-weight: 700;
}

.summary-card .info p {
    margin: 0;
    font-size: 0.8rem;
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

@media (max-width: 768px) {
    .kanban-column {
        min-width: 280px;
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
        <p class="mt-3 text-muted fw-medium">Atualizando situação...</p>
    </div>
</div>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-dark fw-bold">Contratos</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Kanban</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto d-flex gap-2">
        <!-- View Toggle -->
        <div class="btn-group view-toggle" role="group">
            <a href="<?php echo site_url('contratos/lista'); ?>" class="btn btn-outline-secondary" title="Visão Tabela">
                <i class="bx bx-list-ul"></i>
            </a>
            <a href="<?php echo site_url('contratos/kanban'); ?>" class="btn btn-outline-secondary active" title="Visão Kanban">
                <i class="bx bx-columns"></i>
            </a>
        </div>
        <a href="<?php echo site_url('contratos/criar'); ?>" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i>Novo Contrato
        </a>
    </div>
</div>
<!--end breadcrumb-->

<!-- Summary Cards -->
<div class="summary-bar" id="summaryBar">
    <div class="summary-card">
        <div class="icon" style="background: rgba(13, 110, 253, 0.1); color: #0d6efd;">
            <i class="bx bx-file"></i>
        </div>
        <div class="info">
            <h3 id="totalContratos">-</h3>
            <p>Contratos</p>
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
            <h3 id="totalPago">-</h3>
            <p>Recebido</p>
        </div>
    </div>
    <div class="summary-card">
        <div class="icon" style="background: rgba(255, 193, 7, 0.1); color: #ffc107;">
            <i class="bx bx-time-five"></i>
        </div>
        <div class="info">
            <h3 id="totalAberto">-</h3>
            <p>A Receber</p>
        </div>
    </div>
</div>

<!-- Filtro por Evento -->
<?php $eventoIdUrl = $_GET['evento_id'] ?? ''; ?>
<input type="hidden" id="filtroEvento" value="<?php echo esc($eventoIdUrl); ?>">

<!-- Kanban Board -->
<div class="kanban-container" id="kanbanBoard">
    <!-- As colunas serão carregadas via JavaScript -->
    <div class="text-center w-100 py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
        <p class="mt-2 text-muted">Carregando contratos...</p>
    </div>
</div>

<?php echo $this->endSection() ?>


<?php echo $this->section('scripts') ?>

<!-- SortableJS CDN -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
$(document).ready(function() {
    
    // Configuração das situações (ordem das colunas do kanban)
    const situacoes = {
        'proposta': { nome: 'Proposta', icone: 'bx bx-file' },
        'proposta_aceita': { nome: 'Proposta Aceita', icone: 'bx bx-check' },
        'pagamento_aberto': { nome: 'Pgto em Aberto', icone: 'bx bx-time' },
        'pagamento_andamento': { nome: 'Pgto em Andamento', icone: 'bx bx-loader' },
        'pagamento_confirmado': { nome: 'Pgto Confirmado', icone: 'bx bx-check-circle' },
        'aguardando_contrato': { nome: 'Aguard. Contrato', icone: 'bx bx-file-find' },
        'contrato_assinado': { nome: 'Contrato Assinado', icone: 'bx bx-edit' },
        'aguardando_credenciamento': { nome: 'Aguard. Credenc.', icone: 'bx bx-id-card' },
        'finalizado': { nome: 'Finalizado', icone: 'bx bx-check-double' },
        'cancelado': { nome: 'Cancelado', icone: 'bx bx-x-circle' },
        'banido': { nome: 'Banido', icone: 'bx bx-block' }
    };

    let dadosKanban = {};

    // Carregar dados
    function carregarKanban() {
        var eventId = $('#filtroEvento').val();
        
        $.ajax({
            url: '<?php echo site_url("contratos/recuperaContratosKanban"); ?>',
            type: 'GET',
            data: { event_id: eventId },
            dataType: 'json',
            success: function(data) {
                dadosKanban = data;
                renderizarKanban(data);
                atualizarSummary(data);
            },
            error: function() {
                $('#kanbanBoard').html('<div class="alert alert-danger m-3">Erro ao carregar contratos.</div>');
            }
        });
    }

    // Atualizar cards de resumo
    function atualizarSummary(data) {
        let totalContratos = 0;
        let totalValor = 0;
        let totalPago = 0;

        for (var situacao in data) {
            if (data[situacao].count) {
                totalContratos += data[situacao].count;
                totalValor += data[situacao].total_valor || 0;
                totalPago += data[situacao].total_pago || 0;
            }
        }

        $('#totalContratos').text(totalContratos);
        $('#totalValor').text(formatMoney(totalValor));
        $('#totalPago').text(formatMoney(totalPago));
        $('#totalAberto').text(formatMoney(totalValor - totalPago));
    }

    function formatMoney(value) {
        return 'R$ ' + parseFloat(value).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    // Renderizar Kanban
    function renderizarKanban(data) {
        var html = '';
        
        for (var situacao in situacoes) {
            var config = situacoes[situacao];
            var dadosSituacao = data[situacao] || { cards: [], count: 0, total_valor_formatado: 'R$ 0,00' };
            var contratos = dadosSituacao.cards || [];
            
            html += '<div class="kanban-column">';
            html += '<div class="kanban-column-header kanban-header-' + situacao + '">';
            html += '<div class="header-top">';
            html += '<span class="header-title"><i class="' + config.icone + '"></i>' + config.nome + '</span>';
            html += '<span class="count-badge">' + (dadosSituacao.count || 0) + '</span>';
            html += '</div>';
            html += '<div class="header-total">' + (dadosSituacao.total_valor_formatado || 'R$ 0,00') + '</div>';
            html += '</div>';
            html += '<div class="kanban-column-body" data-situacao="' + situacao + '">';
            
            if (contratos.length === 0) {
                html += '<div class="kanban-empty"><i class="bx bx-inbox d-block"></i><small>Nenhum contrato</small></div>';
            } else {
                contratos.forEach(function(contrato) {
                    html += renderizarCard(contrato);
                });
            }
            
            html += '</div>';
            html += '</div>';
        }
        
        $('#kanbanBoard').html(html);
        inicializarSortable();
    }

    // Renderizar Card
    function renderizarCard(contrato) {
        var progressClass = 'bg-primary';
        if (contrato.progresso >= 100) progressClass = 'bg-success';
        else if (contrato.progresso >= 50) progressClass = 'bg-info';
        else if (contrato.progresso > 0) progressClass = 'bg-warning';

        var html = '<div class="kanban-card" data-id="' + contrato.id + '" data-situacao="' + contrato.situacao + '">';
        
        // Header row with code and actions
        html += '<div class="card-header-row">';
        html += '<span class="card-codigo">' + (contrato.codigo || '#' + contrato.id) + '</span>';
        html += '<div class="card-actions">';
        html += '<a href="<?php echo site_url("contratos/exibir"); ?>/' + contrato.id + '" title="Ver detalhes"><i class="bx bx-show"></i></a>';
        html += '<a href="<?php echo site_url("contratos/editar"); ?>/' + contrato.id + '" title="Editar"><i class="bx bx-edit"></i></a>';
        html += '</div>';
        html += '</div>';
        
        // Expositor name
        html += '<div class="card-expositor">';
        html += '<a href="<?php echo site_url("contratos/exibir"); ?>/' + contrato.id + '">' + contrato.expositor + '</a>';
        html += '</div>';
        
        // Valor and date
        html += '<div class="card-valor-row">';
        html += '<span class="card-valor">' + contrato.valor_final + '</span>';
        if (contrato.proxima_parcela) {
            html += '<span class="card-data"><i class="bx bx-calendar"></i>' + contrato.proxima_parcela + '</span>';
        }
        html += '</div>';
        
        // Progress bar
        if (contrato.progresso !== undefined && contrato.progresso < 100) {
            html += '<div class="progress">';
            html += '<div class="progress-bar ' + progressClass + '" role="progressbar" style="width: ' + contrato.progresso + '%" aria-valuenow="' + contrato.progresso + '" aria-valuemin="0" aria-valuemax="100"></div>';
            html += '</div>';
        }
        
        // Badges
        html += '<div class="card-badges">';
        if (contrato.documento_badge) {
            html += contrato.documento_badge;
        }
        if (contrato.credenciamento_badge) {
            html += contrato.credenciamento_badge;
        }
        if (contrato.parcela_badge) {
            html += contrato.parcela_badge;
        }
        html += '</div>';
        
        html += '</div>';
        
        return html;
    }

    // Inicializar Sortable (Drag & Drop)
    function inicializarSortable() {
        document.querySelectorAll('.kanban-column-body').forEach(function(column) {
            new Sortable(column, {
                group: 'kanban',
                animation: 200,
                easing: 'cubic-bezier(0.25, 1, 0.5, 1)',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                onEnd: function(evt) {
                    var cardEl = evt.item;
                    var contratoId = cardEl.getAttribute('data-id');
                    var novaSituacao = evt.to.getAttribute('data-situacao');
                    var situacaoAntiga = cardEl.getAttribute('data-situacao');
                    
                    // Remove empty state if exists
                    var emptyEl = evt.to.querySelector('.kanban-empty');
                    if (emptyEl) emptyEl.remove();
                    
                    if (novaSituacao !== situacaoAntiga) {
                        atualizarSituacao(contratoId, novaSituacao, cardEl, situacaoAntiga);
                    }
                }
            });
        });
    }

    // Atualizar situação via AJAX
    function atualizarSituacao(contratoId, novaSituacao, cardEl, situacaoAntiga) {
        $('#kanbanLoading').addClass('show');
        
        $.ajax({
            url: '<?php echo site_url("contratos/alterarSituacao"); ?>',
            type: 'POST',
            data: {
                id: contratoId,
                situacao: novaSituacao,
                '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>'
            },
            dataType: 'json',
            success: function(response) {
                $('#kanbanLoading').removeClass('show');
                
                if (response.erro) {
                    // Reverter
                    var colunaOriginal = document.querySelector('.kanban-column-body[data-situacao="' + situacaoAntiga + '"]');
                    colunaOriginal.appendChild(cardEl);
                    
                    Lobibox.notify('error', {
                        pauseDelayOnHover: true,
                        continueDelayOnInactiveTab: false,
                        position: 'top right',
                        msg: response.erro
                    });
                } else {
                    // Atualizar atributo do card
                    cardEl.setAttribute('data-situacao', novaSituacao);
                    
                    // Recarregar para atualizar totais
                    carregarKanban();
                    
                    Lobibox.notify('success', {
                        pauseDelayOnHover: true,
                        continueDelayOnInactiveTab: false,
                        position: 'top right',
                        msg: 'Situação atualizada com sucesso!'
                    });
                }
            },
            error: function() {
                $('#kanbanLoading').removeClass('show');
                
                // Reverter
                var colunaOriginal = document.querySelector('.kanban-column-body[data-situacao="' + situacaoAntiga + '"]');
                colunaOriginal.appendChild(cardEl);
                
                Lobibox.notify('error', {
                    pauseDelayOnHover: true,
                    continueDelayOnInactiveTab: false,
                    position: 'top right',
                    msg: 'Erro ao atualizar situação.'
                });
            }
        });
    }

    // Carregar ao iniciar
    carregarKanban();

    // Filtro por evento
    $('#filtroEvento').on('change', function() {
        carregarKanban();
    });
});
</script>

<?php echo $this->endSection() ?>
