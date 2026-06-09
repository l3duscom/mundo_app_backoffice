<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>

<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.css') ?>" />

<style>
.resumo-card {
    border-left: 4px solid;
    transition: transform 0.2s;
}
.resumo-card:hover {
    transform: translateY(-2px);
}
.resumo-card.primary { border-left-color: #0d6efd; }
.resumo-card.success { border-left-color: #198754; }
.resumo-card.warning { border-left-color: #ffc107; }
.resumo-card.info { border-left-color: #0dcaf0; }

.tipo-item-badge {
    font-size: 0.8rem;
    margin: 2px;
}

/* Contratos bloqueados (cancelado, banido ou excluído) */
tr.contrato-bloqueado td {
    background-color: #fff5f5 !important;
    color: #6c757d !important;
}
tr.contrato-bloqueado td a {
    color: #6c757d !important;
    text-decoration: line-through;
}
tr.contrato-bloqueado td a:hover {
    color: #495057 !important;
}
tr.contrato-bloqueado td .badge {
    opacity: 0.65;
}
</style>

<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Contratos</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Contratos</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto d-flex gap-2">
        <!-- View Toggle -->
        <div class="btn-group" role="group">
            <a href="<?php echo site_url('contratos/lista'); ?>" class="btn btn-outline-secondary active" title="Visão Tabela">
                <i class="bx bx-list-ul"></i>
            </a>
            <a href="<?php echo site_url('contratos/kanban'); ?>" class="btn btn-outline-secondary" title="Visão Kanban">
                <i class="bx bx-columns"></i>
            </a>
        </div>
        <a href="<?php echo site_url('contratos/criar'); ?>" class="btn btn-primary">
            <i class="bx bx-plus me-2"></i>Novo Contrato
        </a>
    </div>
</div>
<!--end breadcrumb-->

<!-- Cards de Resumo -->
<div class="row mb-4" id="cardsResumo">
    <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0d6efd !important;">
            <div class="card-body py-3">
                <p class="text-muted mb-1 small text-uppercase fw-bold">Total de Contratos</p>
                <h2 class="mb-0 text-primary" id="totalContratos">-</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0dcaf0 !important;">
            <div class="card-body py-3">
                <p class="text-muted mb-1 small text-uppercase fw-bold">Valor Total Contratado</p>
                <h4 class="mb-0 text-info" id="valorTotal">-</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #198754 !important;">
            <div class="card-body py-3">
                <p class="text-muted mb-1 small text-uppercase fw-bold">Valor Recebido</p>
                <h4 class="mb-0 text-success" id="valorPago">-</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ffc107 !important;">
            <div class="card-body py-3">
                <p class="text-muted mb-1 small text-uppercase fw-bold">Valor a Receber</p>
                <h4 class="mb-0 text-warning" id="valorEmAberto">-</h4>
            </div>
        </div>
    </div>
</div>

<!-- Card de Totais por Tipo -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header py-2">
                <h6 class="mb-0"><i class="bx bx-bar-chart-alt-2 me-2"></i>Totais por Tipo de Espaço</h6>
            </div>
            <div class="card-body py-3">
                <div id="totaisPorTipo" class="d-flex flex-wrap gap-2">
                    <span class="text-muted">Carregando...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">

    <div class="col-lg-12">
        <div class="card shadow radius-10">
            <div class="card-body">
                
                <!-- Filtros -->
                <?php $eventoIdUrl = $_GET['evento_id'] ?? ''; ?>
                <input type="hidden" id="filtroEvento" value="<?php echo esc($eventoIdUrl); ?>">
                <input type="hidden" id="filtroStatusAba" value="ativos">

                <!-- Abas de status -->
                <ul class="nav nav-tabs mb-3" id="tabsContratos" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active tab-contratos" data-aba="ativos" href="#" role="tab">
                            <i class="bx bx-check-circle me-1"></i>Ativos
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link tab-contratos" data-aba="cancelados" href="#" role="tab">
                            <i class="bx bx-x-circle me-1"></i>Cancelados
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link tab-contratos" data-aba="excluidos" href="#" role="tab">
                            <i class="bx bx-trash me-1"></i>Excluídos
                        </a>
                    </li>
                </ul>

                <div class="d-flex flex-wrap gap-3 mb-4 align-items-end">
                    <div>
                        <label class="form-label fw-bold small mb-1"><i class="bx bx-check-circle me-1"></i>Situação</label>
                        <select id="filtroSituacao" class="form-select form-select-sm" style="min-width: 180px;">
                            <option value="">Todas</option>
                            <option value="proposta">Proposta</option>
                            <option value="proposta_aceita">Proposta Aceita</option>
                            <option value="aguardando_contrato">Aguardando Contrato</option>
                            <option value="contrato_assinado">Contrato Assinado</option>
                            <option value="aguardando_credenciamento">Aguardando Credenciamento</option>
                            <option value="pagamento_aberto">Pagamento em Aberto</option>
                            <option value="pagamento_andamento">Pagamento em Andamento</option>
                            <option value="pagamento_confirmado">Pagamento Confirmado</option>
                            <option value="finalizado">Finalizado</option>
                            <option value="cancelado">Cancelado</option>
                            <option value="banido">Banido</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label fw-bold small mb-1"><i class="bx bx-time-five me-1"></i>Parcela</label>
                        <select id="filtroParcela" class="form-select form-select-sm" style="min-width: 150px;">
                            <option value="">Todas</option>
                            <option value="Vencido">🔴 Vencidas</option>
                            <option value="Próximo">🟡 Próximas</option>
                        </select>
                    </div>
                    <div>
                        <button type="button" id="btnLimparFiltros" class="btn btn-outline-secondary btn-sm">
                            <i class="bx bx-x me-1"></i>Limpar
                        </button>
                    </div>
                </div>
                
                <hr class="mb-4">

                <div class="table-responsive">
                    <table id="ajaxTable" class="table table-striped table-sm" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Expositor</th>
                                <th>Evento</th>
                                <th>Tipo</th>
                                <th>Itens</th>
                                <th>Espaço</th>
                                <th>Valor</th>
                                <th>Pago</th>
                                <th>Situação</th>
                                <th>Documento</th>
                                <th>Credenc.</th>
                                <th>Parcela</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>



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

    // Função para formatar valores monetários
    function formatMoney(value) {
        return 'R$ ' + parseFloat(value).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    // Função para carregar totais
    function carregarTotais() {
        var eventId = $('#filtroEvento').val();
        
        $.ajax({
            url: '<?php echo site_url("contratos/recuperaTotais"); ?>',
            type: 'GET',
            data: { event_id: eventId },
            dataType: 'json',
            success: function(data) {
                $('#totalContratos').text(data.quantidade_contratos);
                $('#valorTotal').text(formatMoney(data.valor_total));
                $('#valorPago').text(formatMoney(data.valor_pago));
                $('#valorEmAberto').text(formatMoney(data.valor_em_aberto));
                
                // Totais por tipo
                var htmlTipos = '';
                if (Object.keys(data.por_tipo).length > 0) {
                    for (var tipo in data.por_tipo) {
                        var info = data.por_tipo[tipo];
                        htmlTipos += '<span class="badge bg-primary tipo-item-badge">' + 
                            tipo + ': ' + info.quantidade + ' contrato(s) - ' + formatMoney(info.valor) + 
                            '</span>';
                    }
                } else {
                    htmlTipos = '<span class="text-muted">Nenhum item cadastrado</span>';
                }
                $('#totaisPorTipo').html(htmlTipos);
            },
            error: function() {
                $('#totalContratos').text('-');
                $('#valorTotal').text('-');
                $('#valorPago').text('-');
                $('#valorEmAberto').text('-');
                $('#totaisPorTipo').html('<span class="text-danger">Erro ao carregar</span>');
            }
        });
    }

    // Inicializa DataTable
    var table = $('#ajaxTable').DataTable({
        "oLanguage": DATATABLE_PTBR,
        "ajax": {
            "url": "<?php echo site_url('contratos/recuperacontratos'); ?>",
            "data": function(d) {
                d.event_id = $('#filtroEvento').val();
                d.status_aba = $('#filtroStatusAba').val();
            }
        },
        "columns": [
            { "data": "codigo" },
            { "data": "expositor" },
            { "data": "evento" },
            { "data": "tipo" },
            { "data": "qtd_itens" },
            { "data": "espaco" },
            { "data": "valor_final" },
            { "data": "valor_pago" },
            { "data": "situacao" },
            { "data": "documento" },
            { "data": "credenciamento" },
            { "data": "parcela" },
        ],
        "order": [],
        "deferRender": true,
        "processing": true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',
        },
        "responsive": true,
        "pagingType": $(window).width() < 768 ? "simple" : "simple_numbers",
        "pageLength": 100,
    });

    // Carrega totais ao iniciar
    carregarTotais();

    // Filtro por Evento - recarrega via AJAX
    $('#filtroEvento').on('change', function() {
        table.ajax.reload();
        carregarTotais();
    });

    // Alternância de aba (Ativos / Cancelados / Excluídos)
    $('.tab-contratos').on('click', function(e) {
        e.preventDefault();
        $('.tab-contratos').removeClass('active');
        $(this).addClass('active');
        $('#filtroStatusAba').val($(this).data('aba'));
        table.ajax.reload();
    });

    // Filtro por Situação
    $('#filtroSituacao').on('change', function() {
        var valor = $(this).val();
        table.column(8).search(valor).draw();
    });

    // Filtro por Parcela
    $('#filtroParcela').on('change', function() {
        var valor = $(this).val();
        table.column(11).search(valor).draw();
    });

    // Limpar Filtros
    $('#btnLimparFiltros').on('click', function() {
        $('#filtroEvento').val('');
        $('#filtroSituacao').val('');
        $('#filtroParcela').val('');
        table.columns().search('').draw();
        carregarTotais();
    });
});
</script>

<?php echo $this->endSection() ?>
