<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<!-- DataTables -->
<link href="<?php echo site_url('recursos/theme/'); ?>plugins/datatable/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
<style>
    .stat-card {
        padding: 1.5rem;
        border-radius: 12px;
        color: white;
        text-align: center;
        height: 100%;
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-3px);
    }
    .stat-card.green { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
    .stat-card.red { background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%); }
    .stat-card.blue { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .stat-card.orange { background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); }
    .stat-card h3 { font-size: 1.8rem; font-weight: bold; margin: 0; }
    .stat-card p { margin: 0.5rem 0 0; opacity: 0.9; font-size: 0.85rem; }
    .filter-card {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1rem;
    }
    /* Loading Overlay */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        flex-direction: column;
    }
    .loading-overlay.show {
        display: flex;
    }
    .loading-spinner {
        width: 60px;
        height: 60px;
        border: 5px solid rgba(255,255,255,0.3);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    .loading-text {
        color: white;
        margin-top: 20px;
        font-size: 1.2rem;
        font-weight: 500;
    }
    .loading-progress {
        color: rgba(255,255,255,0.8);
        margin-top: 10px;
        font-size: 0.9rem;
    }
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner"></div>
    <div class="loading-text">Sincronizando dados...</div>
    <div class="loading-progress" id="loadingProgress">Aguarde...</div>
</div>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Gestão</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Financeiro</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="<?php echo site_url('financeiro/criar'); ?>" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i>Novo Lançamento
        </a>
        <button type="button" class="btn btn-outline-secondary" onclick="sincronizarDados()">
            <i class="bx bx-sync me-1"></i>Sincronizar
        </button>
    </div>
</div>
<!--end breadcrumb-->

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <!-- Filtros rápidos de período -->
        <div class="mb-3">
            <label class="form-label me-2">Período Rápido:</label>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="filtrarPeriodo(30)">30 dias</button>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="filtrarPeriodo(60)">60 dias</button>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="filtrarPeriodo(90)">90 dias</button>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="filtrarPeriodo(365)">1 ano</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="filtrarTodoPeriodo()">Todo Período</button>
            </div>
        </div>
        <hr class="my-3">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Evento</label>
                <select class="form-select" id="filtro_evento">
                    <option value="todos">Todos os Eventos</option>
                    <?php foreach ($eventos as $evento): ?>
                        <option value="<?php echo $evento->id; ?>" <?php echo $eventoSelecionado == $evento->id ? 'selected' : ''; ?>>
                            <?php echo esc($evento->nome); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Data Início</label>
                <input type="date" class="form-control" id="filtro_data_inicio" value="<?php echo date('Y-m-01'); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Data Fim</label>
                <input type="date" class="form-control" id="filtro_data_fim" value="<?php echo date('Y-m-t'); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Tipo</label>
                <select class="form-select" id="filtro_tipo">
                    <option value="">Todos</option>
                    <option value="ENTRADA">Entradas</option>
                    <option value="SAIDA">Saídas</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select class="form-select" id="filtro_status">
                    <option value="">Todos</option>
                    <option value="pago">Pago</option>
                    <option value="pendente">Pendente</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-primary w-100" onclick="aplicarFiltros()">
                    <i class="bx bx-filter-alt"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Cards de Resumo -->
<div class="row mb-4" id="cardsResumo">
    <div class="col-lg-2 col-md-4 mb-3">
        <div class="stat-card green">
            <h3 id="entradas_brutas">R$ 0,00</h3>
            <p><i class="bx bx-trending-up"></i> Entradas Bruto</p>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 mb-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #0ba360 0%, #3cba92 100%);">
            <h3 id="entradas_liquidas">R$ 0,00</h3>
            <p><i class="bx bx-check-circle"></i> Entradas Líquido</p>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 mb-3">
        <div class="stat-card red">
            <h3 id="total_saidas">R$ 0,00</h3>
            <p><i class="bx bx-trending-down"></i> Saídas</p>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 mb-3">
        <div class="stat-card blue">
            <h3 id="saldo_bruto">R$ 0,00</h3>
            <p><i class="bx bx-wallet"></i> Saldo Bruto</p>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 mb-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <h3 id="saldo_liquido">R$ 0,00</h3>
            <p><i class="bx bx-money"></i> Saldo Líquido</p>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 mb-3">
        <div class="stat-card orange">
            <h3 id="pendentes">0</h3>
            <p><i class="bx bx-time"></i> Pendentes</p>
        </div>
    </div>
</div>

<!-- Tabela de Lançamentos -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bx bx-list-ul me-2"></i>Lançamentos</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabelaLancamentos" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Descrição</th>
                        <th class="text-center">Tipo</th>
                        <th class="text-center">Origem</th>
                        <th>Evento</th>
                        <th class="text-end">Bruto</th>
                        <th class="text-end">Líquido</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width: 100px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<!-- DataTables -->
<script src="<?php echo site_url('recursos/theme/'); ?>plugins/datatable/js/jquery.dataTables.min.js"></script>
<script src="<?php echo site_url('recursos/theme/'); ?>plugins/datatable/js/dataTables.bootstrap5.min.js"></script>

<script>
var tabela;
var csrfToken = '<?php echo csrf_hash(); ?>';

$(document).ready(function() {
    carregarResumo();
    inicializarTabela();
});

function formatarMoeda(valor) {
    return 'R$ ' + valor.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function carregarResumo() {
    $.ajax({
        url: '<?php echo site_url("financeiro/recuperaResumo"); ?>',
        type: 'GET',
        data: {
            event_id: $('#filtro_evento').val(),
            data_inicio: $('#filtro_data_inicio').val(),
            data_fim: $('#filtro_data_fim').val()
        },
        dataType: 'json',
        success: function(data) {
            $('#entradas_brutas').text(formatarMoeda(data.entradas_brutas));
            $('#entradas_liquidas').text(formatarMoeda(data.entradas_liquidas));
            $('#total_saidas').text(formatarMoeda(data.saidas_pagas));
            $('#saldo_bruto').text(formatarMoeda(data.saldo_bruto));
            $('#saldo_liquido').text(formatarMoeda(data.saldo_liquido));
            $('#pendentes').text(data.qtd_pendentes);
        }
    });
}

function inicializarTabela() {
    tabela = $('#tabelaLancamentos').DataTable({
        ajax: {
            url: '<?php echo site_url("financeiro/recuperaLancamentos"); ?>',
            data: function(d) {
                d.event_id = $('#filtro_evento').val();
                d.tipo = $('#filtro_tipo').val();
                d.status = $('#filtro_status').val();
                d.data_inicio = $('#filtro_data_inicio').val();
                d.data_fim = $('#filtro_data_fim').val();
            },
            beforeSend: function() {
                showLoading('Carregando lançamentos...');
            },
            complete: function() {
                hideLoading();
            }
        },
        columns: [
            { data: 'data' },
            { data: 'descricao' },
            { data: 'tipo', className: 'text-center' },
            { data: 'origem', className: 'text-center' },
            { data: 'evento' },
            { data: 'valor_bruto', className: 'text-end' },
            { data: 'valor_liquido', className: 'text-end' },
            { data: 'status', className: 'text-center' },
            { data: 'acoes', className: 'text-center', orderable: false }
        ],
        order: [[0, 'desc']],
        language: {
            url: '<?php echo site_url("recursos/theme/plugins/datatable/js/pt-BR.json"); ?>'
        },
        pageLength: 25
    });
}

function showLoading(msg) {
    $('#loadingProgress').text(msg || 'Processando...');
    $('#loadingOverlay').addClass('show');
}

function hideLoading() {
    $('#loadingOverlay').removeClass('show');
}

function filtrarPeriodo(dias) {
    var dataFim = new Date();
    var dataInicio = new Date();
    dataInicio.setDate(dataInicio.getDate() - dias);
    
    $('#filtro_data_inicio').val(dataInicio.toISOString().split('T')[0]);
    $('#filtro_data_fim').val(dataFim.toISOString().split('T')[0]);
    
    aplicarFiltros();
}

function filtrarTodoPeriodo() {
    $('#filtro_data_inicio').val('2020-01-01');
    $('#filtro_data_fim').val(new Date().toISOString().split('T')[0]);
    
    aplicarFiltros();
}

function aplicarFiltros() {
    showLoading('Atualizando dados...');
    carregarResumo();
    tabela.ajax.reload(function() {
        hideLoading();
    });
}

function excluirLancamento(id) {
    if (!confirm('Deseja realmente excluir este lançamento?')) return;
    
    showLoading('Excluindo lançamento...');
    
    $.ajax({
        url: '<?php echo site_url("financeiro/excluir/"); ?>' + id,
        type: 'POST',
        data: { '<?php echo csrf_token(); ?>': csrfToken },
        dataType: 'json',
        success: function(response) {
            hideLoading();
            if (response.token) csrfToken = response.token;
            
            if (response.sucesso) {
                alert(response.sucesso);
                aplicarFiltros();
            } else if (response.erro) {
                alert('Erro: ' + response.erro);
            }
        },
        error: function() {
            hideLoading();
            alert('Erro ao excluir. Tente novamente.');
        }
    });
}

function sincronizarDados() {
    if (!confirm('Deseja sincronizar dados de contratos, pedidos e contas a pagar?')) return;
    
    // Mostra loading overlay
    $('#loadingOverlay').addClass('show');
    $('#loadingProgress').text('Processando contratos, pedidos e contas...');
    
    $.ajax({
        url: '<?php echo site_url("financeiro/sincronizar"); ?>',
        type: 'POST',
        data: { '<?php echo csrf_token(); ?>': csrfToken },
        dataType: 'json',
        success: function(response) {
            if (response.token) csrfToken = response.token;
            
            // Esconde loading overlay
            $('#loadingOverlay').removeClass('show');
            
            if (response.sucesso) {
                var msg = response.sucesso;
                if (response.detalhes) {
                    msg += '\n\nDetalhes:\n';
                    msg += '• Parcelas: ' + response.detalhes.parcelas + '\n';
                    msg += '• Pedidos: ' + response.detalhes.pedidos + '\n';
                    msg += '• Contas a Pagar: ' + response.detalhes.contas_pagar;
                }
                alert(msg);
                aplicarFiltros();
            } else if (response.info) {
                alert(response.info);
            } else if (response.erro) {
                alert('Erro: ' + response.erro);
            }
        },
        error: function(xhr, status, error) {
            $('#loadingOverlay').removeClass('show');
            var errorMsg = 'Erro ao sincronizar. ';
            try {
                var resp = JSON.parse(xhr.responseText);
                if (resp.erro) {
                    errorMsg += resp.erro;
                } else {
                    errorMsg += error || 'Tente novamente.';
                }
            } catch(e) {
                errorMsg += xhr.responseText || error || 'Tente novamente.';
            }
            alert(errorMsg);
        }
    });
}
</script>
<?php echo $this->endSection() ?>
