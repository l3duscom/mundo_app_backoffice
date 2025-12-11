<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>

<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.css') ?>" />

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
    <div class="ms-auto">
        <a href="<?php echo site_url('contratos/criar'); ?>" class="btn btn-primary">
            <i class="bx bx-plus me-2"></i>Novo Contrato
        </a>
    </div>
</div>
<!--end breadcrumb-->

<div class="row">

    <div class="col-lg-12">
        <div class="card shadow radius-10">
            <div class="card-body">
                
                <!-- Filtros -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold"><i class="bx bx-check-circle me-1"></i>Situação</label>
                        <select id="filtroSituacao" class="form-select">
                            <option value="">Todas as situações</option>
                            <option value="proposta">Proposta</option>
                            <option value="proposta_aceita">Proposta Aceita</option>
                            <option value="contrato_assinado">Contrato Assinado</option>
                            <option value="pagamento_aberto">Pagamento em Aberto</option>
                            <option value="pagamento_andamento">Pagamento em Andamento</option>
                            <option value="pagamento_confirmado">Pagamento Confirmado</option>
                            <option value="cancelado">Cancelado</option>
                            <option value="banido">Banido</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" id="btnLimparFiltros" class="btn btn-outline-secondary">
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
                                <th>Itens</th>
                                <th>Valor</th>
                                <th>Pago</th>
                                <th>Situação</th>
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

    // Inicializa DataTable
    var table = $('#ajaxTable').DataTable({
        "oLanguage": DATATABLE_PTBR,
        "ajax": "<?php echo site_url('contratos/recuperacontratos'); ?>",
        "columns": [
            { "data": "codigo" },
            { "data": "expositor" },
            { "data": "evento" },
            { "data": "qtd_itens" },
            { "data": "valor_final" },
            { "data": "valor_pago" },
            { "data": "situacao" },
        ],
        "order": [],
        "deferRender": true,
        "processing": true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',
        },
        "responsive": true,
        "pagingType": $(window).width() < 768 ? "simple" : "simple_numbers",
    });

    // Filtro por Situação
    $('#filtroSituacao').on('change', function() {
        var valor = $(this).val();
        table.column(6).search(valor).draw();
    });

    // Limpar Filtros
    $('#btnLimparFiltros').on('click', function() {
        $('#filtroSituacao').val('');
        table.columns().search('').draw();
    });
});
</script>

<?php echo $this->endSection() ?>

