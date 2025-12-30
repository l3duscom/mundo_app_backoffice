<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
<style>
    #calendar {
        max-width: 1200px;
        margin: 0 auto;
    }
    .fc-event {
        cursor: pointer;
        font-size: 0.75rem;
        padding: 2px 4px;
    }
    .fc-daygrid-day-number {
        font-size: 1.1rem;
        font-weight: 500;
    }
    .fc-toolbar-title {
        font-size: 1.4rem !important;
        text-transform: capitalize;
    }
    .stat-card {
        padding: 1.2rem;
        border-radius: 12px;
        color: white;
        text-align: center;
        transition: transform 0.2s;
    }
    .stat-card:hover { transform: translateY(-2px); }
    .stat-card.green { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
    .stat-card.red { background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%); }
    .stat-card.blue { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .stat-card h4 { font-size: 1.5rem; font-weight: bold; margin: 0; }
    .stat-card p { margin: 0.3rem 0 0; opacity: 0.9; font-size: 0.85rem; }
    
    .legenda-item {
        display: inline-flex;
        align-items: center;
        margin-right: 15px;
        font-size: 0.85rem;
    }
    .legenda-cor {
        width: 16px;
        height: 16px;
        border-radius: 3px;
        margin-right: 6px;
    }
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Financeiro</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('financeiro'); ?>">Financeiro</a></li>
                <li class="breadcrumb-item active" aria-current="page">Calendário</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto d-flex gap-2 align-items-center">
        <select class="form-select form-select-sm" id="filtro_evento" style="width: 200px;" onchange="atualizarCalendario()">
            <option value="todos">Todos os Eventos</option>
            <?php foreach ($eventos as $evento): ?>
                <option value="<?php echo $evento->id; ?>"><?php echo esc($evento->nome); ?></option>
            <?php endforeach; ?>
        </select>
        <a href="<?php echo site_url('financeiro'); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-list-ul me-1"></i>Ver Lista
        </a>
    </div>
</div>
<!--end breadcrumb-->

<!-- Legenda -->
<div class="card mb-3">
    <div class="card-body py-2">
        <span class="legenda-item"><span class="legenda-cor" style="background: #28a745;"></span> Entrada (Pago)</span>
        <span class="legenda-item"><span class="legenda-cor" style="background: #90EE90;"></span> Entrada (Pendente)</span>
        <span class="legenda-item"><span class="legenda-cor" style="background: #dc3545;"></span> Saída (Pago)</span>
        <span class="legenda-item"><span class="legenda-cor" style="background: #FFB6C1;"></span> Saída (Pendente)</span>
    </div>
</div>

<!-- Calendário -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bx bx-calendar me-2"></i>Calendário de Lançamentos</h5>
    </div>
    <div class="card-body">
        <div id="calendar"></div>
    </div>
</div>

<!-- Modal de Detalhes do Dia -->
<div class="modal fade" id="modalDia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="modalDiaLabel">
                    <i class="bx bx-calendar-check me-2"></i>Lançamentos do Dia
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Resumo do Dia -->
                <div class="row mb-3" id="resumoDia">
                    <div class="col-4">
                        <div class="stat-card green">
                            <h4 id="resumoEntradas">R$ 0,00</h4>
                            <p><i class="bx bx-trending-up"></i> Entradas</p>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-card red">
                            <h4 id="resumoSaidas">R$ 0,00</h4>
                            <p><i class="bx bx-trending-down"></i> Saídas</p>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-card blue">
                            <h4 id="resumoSaldo">R$ 0,00</h4>
                            <p><i class="bx bx-wallet"></i> Saldo</p>
                        </div>
                    </div>
                </div>
                
                <!-- Lista de Lançamentos -->
                <div id="listaLancamentosDia">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2">Carregando...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<!-- FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales/pt-br.global.min.js'></script>

<script>
var calendar;

document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'pt-br',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,dayGridWeek'
        },
        buttonText: {
            today: 'Hoje',
            month: 'Mês',
            week: 'Semana'
        },
        events: {
            url: '<?= site_url("financeiro/recuperaEventosCalendario") ?>',
            method: 'GET',
            extraParams: function() {
                return {
                    event_id: document.getElementById('filtro_evento').value
                };
            },
            failure: function() {
                alert('Erro ao carregar eventos do calendário');
            }
        },
        eventClick: function(info) {
            // Clicou em um evento específico, mostra modal do dia
            var data = info.event.startStr;
            abrirModalDia(data);
        },
        dateClick: function(info) {
            // Clicou em um dia
            abrirModalDia(info.dateStr);
        },
        eventDidMount: function(info) {
            // Tooltip com descrição
            info.el.title = info.event.extendedProps.descricao;
        }
    });
    
    calendar.render();
});

function atualizarCalendario() {
    calendar.refetchEvents();
}

function abrirModalDia(data) {
    // Formatar data para exibição
    var dataObj = new Date(data + 'T12:00:00');
    var dataFormatada = dataObj.toLocaleDateString('pt-BR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    
    document.getElementById('modalDiaLabel').innerHTML = '<i class="bx bx-calendar-check me-2"></i>' + dataFormatada;
    
    // Mostrar loading
    document.getElementById('listaLancamentosDia').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="text-muted mt-2">Carregando lançamentos...</p>
        </div>
    `;
    
    // Abrir modal
    var modal = new bootstrap.Modal(document.getElementById('modalDia'));
    modal.show();
    
    // Buscar dados
    var eventId = document.getElementById('filtro_evento').value;
    
    fetch('<?= site_url("financeiro/recuperaLancamentosDia") ?>?data=' + data + '&event_id=' + eventId, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(result => {
        if (result.sucesso) {
            // Atualizar resumo
            document.getElementById('resumoEntradas').textContent = result.resumo.total_entradas;
            document.getElementById('resumoSaidas').textContent = result.resumo.total_saidas;
            document.getElementById('resumoSaldo').textContent = result.resumo.saldo;
            
            // Renderizar lista
            renderizarListaLancamentos(result.lancamentos);
        } else {
            document.getElementById('listaLancamentosDia').innerHTML = `
                <div class="alert alert-warning">Erro ao carregar lançamentos.</div>
            `;
        }
    })
    .catch(error => {
        document.getElementById('listaLancamentosDia').innerHTML = `
            <div class="alert alert-danger">Erro de conexão.</div>
        `;
    });
}

function renderizarListaLancamentos(lancamentos) {
    if (lancamentos.length === 0) {
        document.getElementById('listaLancamentosDia').innerHTML = `
            <div class="alert alert-info text-center">
                <i class="bx bx-info-circle me-2"></i>Nenhum lançamento neste dia.
            </div>
        `;
        return;
    }
    
    var html = `<div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Descrição</th>
                    <th class="text-center">Tipo</th>
                    <th class="text-center">Origem</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Valor</th>
                </tr>
            </thead>
            <tbody>`;
    
    lancamentos.forEach(function(l) {
        var tipoClass = l.tipo === 'ENTRADA' ? 'bg-success' : 'bg-danger';
        var valorClass = l.tipo === 'ENTRADA' ? 'text-success' : 'text-danger';
        var prefixo = l.tipo === 'ENTRADA' ? '+' : '-';
        var statusClass = l.status === 'pago' ? 'bg-success' : 'bg-warning';
        
        html += `
            <tr>
                <td>${l.descricao}</td>
                <td class="text-center"><span class="badge ${tipoClass}">${l.tipo}</span></td>
                <td class="text-center"><span class="badge bg-secondary">${l.origem}</span></td>
                <td class="text-center"><span class="badge ${statusClass}">${l.status}</span></td>
                <td class="text-end fw-bold ${valorClass}">${prefixo} ${l.valor}</td>
            </tr>
        `;
    });
    
    html += `</tbody></table></div>`;
    
    document.getElementById('listaLancamentosDia').innerHTML = html;
}
</script>
<?php echo $this->endSection() ?>
