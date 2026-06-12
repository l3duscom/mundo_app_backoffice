<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.css') ?>" />
<style>
    .metric-card {
        border-left: 4px solid #6c757d;
        transition: transform 0.2s;
    }
    .metric-card:hover { transform: translateY(-2px); }
    .metric-card.primary { border-left-color: #0d6efd; }
    .metric-card.success { border-left-color: #198754; }
    .metric-card.info    { border-left-color: #0dcaf0; }
    .metric-card.warning { border-left-color: #ffc107; }
    .metric-card.purple  { border-left-color: #6f42c1; }
    .metric-card h3 { font-size: 1.6rem; font-weight: 700; margin: 0; }
    .metric-card p  { margin: 0; font-size: 0.78rem; text-transform: uppercase; color: #6c757d; font-weight: 600; }
    .badge-origem   { font-size: 0.75rem; }
    table.dataTable td .badge { font-weight: 500; }
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Marketing</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Campanhas</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <span class="badge bg-primary"><i class="bx bx-calendar-event me-1"></i><?php echo esc($evento->nome); ?></span>
    </div>
</div>

<!-- Filtros -->
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-2">
                <label class="form-label small fw-bold">Data inicial</label>
                <input type="date" id="fData_inicial" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Data final</label>
                <input type="date" id="fData_final" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Source</label>
                <select id="fUtm_source" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach ($sources as $s) : ?>
                        <option value="<?php echo esc($s); ?>"><?php echo esc($s); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Medium</label>
                <select id="fUtm_medium" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach ($mediums as $m) : ?>
                        <option value="<?php echo esc($m); ?>"><?php echo esc($m); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Campaign</label>
                <select id="fUtm_campaign" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <?php foreach ($campaigns as $c) : ?>
                        <option value="<?php echo esc($c); ?>"><?php echo esc($c); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" id="btnLimpar" class="btn btn-outline-secondary btn-sm w-100" title="Limpar filtros">
                    <i class="bx bx-x"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Cards de Métricas -->
<div class="row mb-3">
    <div class="col-md-3 col-sm-6 mb-2">
        <div class="card metric-card primary shadow-sm">
            <div class="card-body py-3">
                <p>Pedidos pagos</p>
                <h3 class="text-primary" id="mQtdPedidos">-</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-2">
        <div class="card metric-card success shadow-sm">
            <div class="card-body py-3">
                <p>Receita</p>
                <h3 class="text-success" id="mReceita">-</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-2">
        <div class="card metric-card info shadow-sm">
            <div class="card-body py-3">
                <p>Ticket médio</p>
                <h3 class="text-info" id="mTicket">-</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-2">
        <div class="card metric-card purple shadow-sm">
            <div class="card-body py-3">
                <p>Cobertura UTM</p>
                <h3 style="color:#6f42c1;" id="mCobertura">-</h3>
                <small class="text-muted" id="mCoberturaDetalhe">-</small>
            </div>
        </div>
    </div>
</div>

<!-- Tabelas de agregação -->
<div class="row mb-3">
    <div class="col-lg-4 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-header py-2"><strong><i class="bx bx-link-external me-1"></i>Por Source</strong></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0" id="tabSource">
                    <thead><tr><th>Origem</th><th class="text-end">Pedidos</th><th class="text-end">Receita</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-header py-2"><strong><i class="bx bx-broadcast me-1"></i>Por Medium</strong></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0" id="tabMedium">
                    <thead><tr><th>Medium</th><th class="text-end">Pedidos</th><th class="text-end">Receita</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-header py-2"><strong><i class="bx bx-target-lock me-1"></i>Por Campaign</strong></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0" id="tabCampaign">
                    <thead><tr><th>Campaign</th><th class="text-end">Pedidos</th><th class="text-end">Receita</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Gráfico -->
<div class="card shadow-sm mb-3">
    <div class="card-header py-2"><strong><i class="bx bx-line-chart me-1"></i>Evolução diária da receita por source</strong></div>
    <div class="card-body" style="height: 360px;">
        <canvas id="chartEvolucao"></canvas>
    </div>
</div>

<!-- Lista detalhada de pedidos -->
<div class="card shadow-sm mb-3">
    <div class="card-header py-2"><strong><i class="bx bx-list-ul me-1"></i>Pedidos do período</strong></div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabPedidos" class="table table-striped table-sm" style="width:100%;">
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Data</th>
                        <th>Cliente</th>
                        <th class="text-end">Valor</th>
                        <th>Source</th>
                        <th>Medium</th>
                        <th>Campaign</th>
                        <th>Content</th>
                        <th>Term</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script src="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.js'); ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
$(document).ready(function () {
    var fmtMoney = function (v) {
        return 'R$ ' + parseFloat(v || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };
    var fmtPct = function (v) { return parseFloat(v || 0).toFixed(1) + '%'; };
    var fmtData = function (s) {
        if (!s) return '-';
        var d = s.replace(' ', 'T');
        var dt = new Date(d);
        if (isNaN(dt)) return s;
        return dt.toLocaleString('pt-BR');
    };

    var chart = null;
    var paletaCores = ['#0d6efd', '#198754', '#dc3545', '#fd7e14', '#6f42c1', '#20c997', '#0dcaf0', '#ffc107', '#6c757d', '#d63384'];

    var tabPedidos = $('#tabPedidos').DataTable({
        data: [],
        order: [[1, 'desc']],
        pageLength: 50,
        responsive: true,
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json' },
        columns: [
            { data: 'cod_pedido', render: function (v, t, row) {
                return '<a href="<?php echo site_url('pedidos/ingressos/'); ?>' + row.id + '" target="_blank">' + (v || '-') + '</a>';
            }},
            { data: 'created_at', render: fmtData },
            { data: 'cliente' },
            { data: 'valor_total', className: 'text-end', render: fmtMoney },
            { data: 'utm_source',   render: function (v) { return '<span class="badge bg-primary badge-origem">' + (v || '-') + '</span>'; } },
            { data: 'utm_medium' },
            { data: 'utm_campaign' },
            { data: 'utm_content' },
            { data: 'utm_term' }
        ]
    });

    function renderAgregacao(tbodySel, rows) {
        var $tb = $(tbodySel).empty();
        if (!rows || !rows.length) {
            $tb.append('<tr><td colspan="3" class="text-muted text-center small py-3">Sem dados</td></tr>');
            return;
        }
        rows.forEach(function (r) {
            $tb.append(
                '<tr>' +
                '<td><span class="badge bg-light text-dark badge-origem">' + (r.origem || '-') + '</span></td>' +
                '<td class="text-end">' + r.qtd_pedidos + '</td>' +
                '<td class="text-end">' + fmtMoney(r.receita) + '</td>' +
                '</tr>'
            );
        });
    }

    function renderGrafico(evolucao) {
        var ctx = document.getElementById('chartEvolucao').getContext('2d');
        if (chart) chart.destroy();

        var datasets = (evolucao.datasets || []).map(function (ds, idx) {
            var cor = paletaCores[idx % paletaCores.length];
            return {
                label: ds.source,
                data: ds.data,
                borderColor: cor,
                backgroundColor: cor + '33',
                tension: 0.3,
                fill: false,
                pointRadius: 2
            };
        });

        chart = new Chart(ctx, {
            type: 'line',
            data: { labels: evolucao.labels || [], datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { callbacks: { label: function (c) { return c.dataset.label + ': ' + fmtMoney(c.parsed.y); } } }
                },
                scales: {
                    y: { ticks: { callback: function (v) { return fmtMoney(v); } } }
                }
            }
        });
    }

    function carregar() {
        $.getJSON('<?php echo site_url('campanhas/dados'); ?>', {
            data_inicial: $('#fData_inicial').val(),
            data_final:   $('#fData_final').val(),
            utm_source:   $('#fUtm_source').val(),
            utm_medium:   $('#fUtm_medium').val(),
            utm_campaign: $('#fUtm_campaign').val()
        }, function (resp) {
            if (resp.erro) { alert(resp.erro); return; }

            $('#mQtdPedidos').text((resp.metricas.qtd_pedidos || 0).toLocaleString('pt-BR'));
            $('#mReceita').text(fmtMoney(resp.metricas.receita));
            $('#mTicket').text(fmtMoney(resp.metricas.ticket_medio));
            $('#mCobertura').text(fmtPct(resp.metricas.cobertura_pct));
            $('#mCoberturaDetalhe').text(resp.metricas.qtd_com_utm + ' de ' + resp.metricas.qtd_pedidos + ' pedidos com UTM');

            renderAgregacao('#tabSource tbody',   resp.por_source);
            renderAgregacao('#tabMedium tbody',   resp.por_medium);
            renderAgregacao('#tabCampaign tbody', resp.por_campaign);

            renderGrafico(resp.evolucao || { labels: [], datasets: [] });

            tabPedidos.clear().rows.add(resp.pedidos || []).draw();
        }).fail(function () {
            alert('Falha ao carregar dados.');
        });
    }

    $('#fData_inicial, #fData_final, #fUtm_source, #fUtm_medium, #fUtm_campaign').on('change', carregar);
    $('#btnLimpar').on('click', function () {
        $('#fData_inicial, #fData_final, #fUtm_source, #fUtm_medium, #fUtm_campaign').val('');
        carregar();
    });

    carregar();
});
</script>
<?php echo $this->endSection() ?>
