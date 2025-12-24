<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
/* Kanban Container */
.kanban-container {
    display: flex;
    gap: 15px;
    overflow-x: auto;
    padding-bottom: 20px;
    min-height: calc(100vh - 300px);
}

/* Kanban Column */
.kanban-column {
    min-width: 280px;
    max-width: 280px;
    background: #f8f9fa;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
}

.kanban-column-header {
    padding: 12px 15px;
    border-radius: 8px 8px 0 0;
    color: white;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.kanban-column-header i {
    margin-right: 8px;
}

.kanban-column-header .count-badge {
    background: rgba(255,255,255,0.3);
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
}

.kanban-column-body {
    flex: 1;
    padding: 10px;
    overflow-y: auto;
    max-height: calc(100vh - 350px);
    min-height: 200px;
}

/* Kanban Card */
.kanban-card {
    background: white;
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    cursor: grab;
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
}

.kanban-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.kanban-card:active {
    cursor: grabbing;
}

.kanban-card .card-title {
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 8px;
    color: #333;
}

.kanban-card .card-title a {
    color: #333;
    text-decoration: none;
}

.kanban-card .card-title a:hover {
    color: #0d6efd;
}

.kanban-card .card-expositor {
    font-size: 0.85rem;
    color: #666;
    margin-bottom: 6px;
}

.kanban-card .card-valor {
    font-size: 0.9rem;
    font-weight: 600;
    color: #198754;
    margin-bottom: 8px;
}

.kanban-card .card-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}

.kanban-card .badge {
    font-size: 0.7rem;
    padding: 3px 6px;
}

/* Cores dos headers das colunas */
.kanban-header-proposta { background: #6c757d; }
.kanban-header-proposta_aceita { background: #0dcaf0; }
.kanban-header-contrato_assinado { background: #0d6efd; }
.kanban-header-aguardando_credenciamento { background: #20c997; }
.kanban-header-pagamento_aberto { background: #ffc107; color: #333; }
.kanban-header-pagamento_andamento { background: #fd7e14; }
.kanban-header-aguardando_contrato { background: #6f42c1; }
.kanban-header-pagamento_confirmado { background: #198754; }
.kanban-header-finalizado { background: #198754; }
.kanban-header-cancelado { background: #dc3545; }
.kanban-header-banido { background: #212529; }

/* Bordas dos cards por situação */
.kanban-card[data-situacao="proposta"] { border-left-color: #6c757d; }
.kanban-card[data-situacao="proposta_aceita"] { border-left-color: #0dcaf0; }
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
}

.sortable-chosen {
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

/* Responsivo */
@media (max-width: 768px) {
    .kanban-column {
        min-width: 260px;
    }
}

/* Scrollbar customizada */
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
    width: 6px;
}

.kanban-column-body::-webkit-scrollbar-track {
    background: transparent;
}

.kanban-column-body::-webkit-scrollbar-thumb {
    background: #ced4da;
    border-radius: 3px;
}

/* View Toggle */
.view-toggle .btn {
    padding: 6px 12px;
}

.view-toggle .btn.active {
    background-color: #0d6efd;
    color: white;
}

/* Loading overlay */
.kanban-loading {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.8);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.kanban-loading.show {
    display: flex;
}
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<!-- Loading Overlay -->
<div class="kanban-loading" id="kanbanLoading">
    <div class="text-center">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
        <p class="mt-2 text-muted">Atualizando situação...</p>
    </div>
</div>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Contratos</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('contratos'); ?>">Contratos</a></li>
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
            <i class="bx bx-plus me-2"></i>Novo Contrato
        </a>
    </div>
</div>
<!--end breadcrumb-->

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
    
    // Configuração das situações
    const situacoes = {
        'proposta': { nome: 'Proposta', icone: 'bx bx-file' },
        'proposta_aceita': { nome: 'Proposta Aceita', icone: 'bx bx-check' },
        'contrato_assinado': { nome: 'Contrato Assinado', icone: 'bx bx-edit' },
        'aguardando_credenciamento': { nome: 'Aguard. Credenc.', icone: 'bx bx-id-card' },
        'pagamento_aberto': { nome: 'Pgto em Aberto', icone: 'bx bx-time' },
        'pagamento_andamento': { nome: 'Pgto em Andamento', icone: 'bx bx-loader' },
        'aguardando_contrato': { nome: 'Aguard. Contrato', icone: 'bx bx-file-find' },
        'pagamento_confirmado': { nome: 'Pgto Confirmado', icone: 'bx bx-check-circle' },
        'finalizado': { nome: 'Finalizado', icone: 'bx bx-check-double' },
        'cancelado': { nome: 'Cancelado', icone: 'bx bx-x-circle' },
        'banido': { nome: 'Banido', icone: 'bx bx-block' }
    };

    // Carregar dados
    function carregarKanban() {
        var eventId = $('#filtroEvento').val();
        
        $.ajax({
            url: '<?php echo site_url("contratos/recuperaContratosKanban"); ?>',
            type: 'GET',
            data: { event_id: eventId },
            dataType: 'json',
            success: function(data) {
                renderizarKanban(data);
            },
            error: function() {
                $('#kanbanBoard').html('<div class="alert alert-danger m-3">Erro ao carregar contratos.</div>');
            }
        });
    }

    // Renderizar Kanban
    function renderizarKanban(data) {
        var html = '';
        
        for (var situacao in situacoes) {
            var config = situacoes[situacao];
            var contratos = data[situacao] || [];
            
            html += '<div class="kanban-column">';
            html += '<div class="kanban-column-header kanban-header-' + situacao + '">';
            html += '<span><i class="' + config.icone + '"></i>' + config.nome + '</span>';
            html += '<span class="count-badge">' + contratos.length + '</span>';
            html += '</div>';
            html += '<div class="kanban-column-body" data-situacao="' + situacao + '">';
            
            contratos.forEach(function(contrato) {
                html += renderizarCard(contrato);
            });
            
            html += '</div>';
            html += '</div>';
        }
        
        $('#kanbanBoard').html(html);
        inicializarSortable();
    }

    // Renderizar Card
    function renderizarCard(contrato) {
        var html = '<div class="kanban-card" data-id="' + contrato.id + '" data-situacao="' + contrato.situacao + '">';
        html += '<div class="card-title">';
        html += '<a href="<?php echo site_url("contratos/exibir"); ?>/' + contrato.id + '">';
        html += contrato.codigo || '#' + contrato.id;
        html += '</a>';
        html += '</div>';
        html += '<div class="card-expositor"><i class="bx bx-store-alt me-1"></i>' + (contrato.expositor || 'N/A') + '</div>';
        html += '<div class="card-valor">' + contrato.valor_final + '</div>';
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
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                onEnd: function(evt) {
                    var cardEl = evt.item;
                    var contratoId = cardEl.getAttribute('data-id');
                    var novaSituacao = evt.to.getAttribute('data-situacao');
                    var situacaoAntiga = cardEl.getAttribute('data-situacao');
                    
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
                    
                    // Atualizar contadores
                    atualizarContadores();
                    
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

    // Atualizar contadores das colunas
    function atualizarContadores() {
        document.querySelectorAll('.kanban-column').forEach(function(column) {
            var body = column.querySelector('.kanban-column-body');
            var badge = column.querySelector('.count-badge');
            var count = body.querySelectorAll('.kanban-card').length;
            badge.textContent = count;
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
