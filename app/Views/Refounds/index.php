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
.bg-purple {
    background-color: #6f42c1 !important;
    color: white;
}
.bg-orange {
    background-color: #fd7e14 !important;
    color: white;
}
.status-highlight {
    font-size: 1.1rem;
    padding: 0.5rem 1rem;
}
.table td {
    vertical-align: middle;
}
</style>

<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Reembolsos</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Solicitações de Reembolso</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<!-- Cards de Resumo -->
<div class="row mb-4" id="cardsResumo">
    <div class="col-md-2 col-sm-6 mb-3 mb-md-0">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0d6efd !important;">
            <div class="card-body py-3">
                <p class="text-muted mb-1 small text-uppercase fw-bold">Total</p>
                <h2 class="mb-0 text-primary" id="totalRefounds">-</h2>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-6 mb-3 mb-md-0">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ffc107 !important;">
            <div class="card-body py-3">
                <p class="text-muted mb-1 small text-uppercase fw-bold">Pendentes</p>
                <h2 class="mb-0 text-warning" id="pendentes">-</h2>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-6 mb-3 mb-md-0">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #198754 !important;">
            <div class="card-body py-3">
                <p class="text-muted mb-1 small text-uppercase fw-bold">Aprovados</p>
                <h2 class="mb-0 text-success" id="aprovados">-</h2>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-6 mb-3 mb-md-0">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
            <div class="card-body py-3">
                <p class="text-muted mb-1 small text-uppercase fw-bold">Rejeitados</p>
                <h2 class="mb-0 text-danger" id="rejeitados">-</h2>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-6 mb-3 mb-md-0">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #6f42c1 !important;">
            <div class="card-body py-3">
                <p class="text-muted mb-1 small text-uppercase fw-bold">Upgrades</p>
                <h2 class="mb-0" style="color: #6f42c1;" id="upgrades">-</h2>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-6">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #fd7e14 !important;">
            <div class="card-body py-3">
                <p class="text-muted mb-1 small text-uppercase fw-bold">Reembolsos</p>
                <h2 class="mb-0" style="color: #fd7e14;" id="reembolsos">-</h2>
            </div>
        </div>
    </div>
</div>

<div class="row">

    <div class="col-lg-12">
        <div class="card shadow radius-10">
            <div class="card-body">
                
                <!-- Filtros -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-bold"><i class="bx bx-calendar me-1"></i>Evento</label>
                        <select id="filtroEvento" class="form-select">
                            <option value="">Todos os eventos</option>
                            <?php foreach ($eventos as $evento): ?>
                                <option value="<?php echo $evento->id; ?>"><?php echo esc($evento->nome); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold"><i class="bx bx-check-circle me-1"></i>Status</label>
                        <select id="filtroStatus" class="form-select">
                            <option value="">Todos</option>
                            <option value="pendente">Pendente</option>
                            <option value="processando">Processando</option>
                            <option value="concluido">Concluído</option>
                            <option value="cancelado">Cancelado</option>
                            <option value="erro">Erro</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold"><i class="bx bx-category me-1"></i>Tipo</label>
                        <select id="filtroTipo" class="form-select">
                            <option value="">Todos</option>
                            <option value="upgrade">Upgrade</option>
                            <option value="reembolso">Reembolso</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" id="btnLimparFiltros" class="btn btn-outline-secondary">
                            <i class="bx bx-x me-1"></i>Limpar
                        </button>
                    </div>
                </div>
                
                <hr class="mb-4">

                <div class="table-responsive">
                    <table id="ajaxTable" class="table table-striped table-hover" style="width: 100%;">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Pedido</th>
                                <th>Valor</th>
                                <th>Evento</th>
                                <th>Tipo</th>
                                <th>Status</th>
                                <th>Data Solicitação</th>
                                <th>Processado Em</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Modal de Confirmação -->
<div class="modal fade" id="modalConfirmacao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitulo">Confirmar Ação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="modalMensagem">Deseja confirmar esta ação?</p>
                <div class="mb-3">
                    <label class="form-label">Observações (opcional)</label>
                    <textarea id="modalObservacoes" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarAcao">Confirmar</button>
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
        }
    }

    var acaoAtual = null;
    var idAtual = null;

    // Função para carregar estatísticas
    function carregarEstatisticas() {
        var eventId = $('#filtroEvento').val();
        
        $.ajax({
            url: '<?php echo site_url("refounds/recuperaEstatisticas"); ?>',
            type: 'GET',
            data: { event_id: eventId },
            dataType: 'json',
            success: function(data) {
                $('#totalRefounds').text(data.total || 0);
                $('#pendentes').text(data.pendentes || 0);
                $('#aprovados').text(data.aprovados || 0);
                $('#rejeitados').text(data.rejeitados || 0);
                $('#upgrades').text(data.upgrades || 0);
                $('#reembolsos').text(data.reembolsos || 0);
            },
            error: function() {
                $('#totalRefounds, #pendentes, #aprovados, #rejeitados, #upgrades, #reembolsos').text('-');
            }
        });
    }

    // Inicializa DataTable
    var table = $('#ajaxTable').DataTable({
        "oLanguage": DATATABLE_PTBR,
        "ajax": {
            "url": "<?php echo site_url('refounds/recuperaRefounds'); ?>",
            "data": function(d) {
                d.event_id = $('#filtroEvento').val();
            }
        },
        "columns": [
            { "data": "id" },
            { "data": "cliente_nome" },
            { "data": "pedido_codigo" },
            { "data": "valor" },
            { "data": "evento_nome" },
            { "data": "tipo_solicitacao" },
            { "data": "status" },
            { "data": "data" },
            { "data": "processado_em" },
            { "data": "acoes", "orderable": false },
        ],
        "order": [[7, 'desc']],
        "deferRender": true,
        "processing": true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',
        },
        "responsive": true,
        "pagingType": $(window).width() < 768 ? "simple" : "simple_numbers",
        "pageLength": 50,
    });

    // Carrega estatísticas ao iniciar
    carregarEstatisticas();

    // Filtro por Evento
    $('#filtroEvento').on('change', function() {
        table.ajax.reload();
        carregarEstatisticas();
    });

    // Filtro por Status
    $('#filtroStatus').on('change', function() {
        var valor = $(this).val();
        table.column(6).search(valor).draw();
    });

    // Filtro por Tipo
    $('#filtroTipo').on('change', function() {
        var valor = $(this).val();
        table.column(5).search(valor).draw();
    });

    // Limpar Filtros
    $('#btnLimparFiltros').on('click', function() {
        $('#filtroEvento').val('');
        $('#filtroStatus').val('');
        $('#filtroTipo').val('');
        table.columns().search('').draw();
        table.ajax.reload();
        carregarEstatisticas();
    });

    // Botão Aprovar
    $(document).on('click', '.btn-aprovar', function() {
        idAtual = $(this).data('id');
        acaoAtual = 'concluido';
        $('#modalTitulo').text('Aprovar Solicitação');
        $('#modalMensagem').text('Deseja aprovar esta solicitação de reembolso?');
        $('#btnConfirmarAcao').removeClass('btn-danger').addClass('btn-success').text('Aprovar');
        $('#modalObservacoes').val('');
        $('#modalConfirmacao').modal('show');
    });

    // Botão Rejeitar
    $(document).on('click', '.btn-rejeitar', function() {
        idAtual = $(this).data('id');
        acaoAtual = 'cancelado';
        $('#modalTitulo').text('Rejeitar Solicitação');
        $('#modalMensagem').text('Deseja rejeitar esta solicitação de reembolso?');
        $('#btnConfirmarAcao').removeClass('btn-success').addClass('btn-danger').text('Rejeitar');
        $('#modalObservacoes').val('');
        $('#modalConfirmacao').modal('show');
    });

    // Confirmar ação
    $('#btnConfirmarAcao').on('click', function() {
        if (!idAtual || !acaoAtual) return;

        $.ajax({
            url: '<?php echo site_url("refounds/atualizarStatus"); ?>',
            type: 'POST',
            data: {
                id: idAtual,
                status: acaoAtual,
                observacoes: $('#modalObservacoes').val(),
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(response) {
                $('#modalConfirmacao').modal('hide');
                if (response.sucesso) {
                    table.ajax.reload();
                    carregarEstatisticas();
                    // Mostrar toast de sucesso
                    alert(response.sucesso);
                } else {
                    alert(response.erro || 'Erro ao processar solicitação');
                }
            },
            error: function() {
                alert('Erro ao processar solicitação');
            }
        });
    });
});
</script>

<?php echo $this->endSection() ?>
