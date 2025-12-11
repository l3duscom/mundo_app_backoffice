<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>

<link rel="stylesheet" href="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.css') ?>" />

<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Catálogo de Itens</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Catálogo de Itens</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="<?php echo site_url("itenscatalogo/criar") ?>" class="btn btn-primary">
            <i class="bx bx-plus"></i> Novo Item
        </a>
    </div>
</div>
<!--end breadcrumb-->

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <!-- Filtro por Evento -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold"><i class="bx bx-calendar-event me-1"></i>Filtrar por Evento</label>
                        <select id="filtroEvento" class="form-select">
                            <option value="">Todos os eventos</option>
                            <?php foreach ($eventos as $ev): ?>
                                <option value="<?php echo $ev->id; ?>"><?php echo esc($ev->nome); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <hr class="mb-4">

                <div class="table-responsive">
                    <table id="ajaxTable" class="table table-striped table-sm" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Evento</th>
                                <th>Nome</th>
                                <th>Tipo</th>
                                <th>Metragem</th>
                                <th>Valor</th>
                                <th>Status</th>
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
        "sLengthMenu": "_MENU_ resultados por página",
        "sLoadingRecords": "Carregando...",
        "sProcessing": "Processando...",
        "sZeroRecords": "Nenhum registro encontrado",
        "sSearch": "Pesquisar",
        "oPaginate": {
            "sNext": "Próximo",
            "sPrevious": "Anterior"
        }
    };

    var table = $('#ajaxTable').DataTable({
        "oLanguage": DATATABLE_PTBR,
        "ajax": {
            "url": "<?php echo site_url('itenscatalogo/recuperaitens'); ?>",
            "data": function(d) {
                d.event_id = $('#filtroEvento').val();
            }
        },
        "columns": [
            { "data": "evento" },
            { "data": "nome" },
            { "data": "tipo" },
            { "data": "metragem" },
            { "data": "valor" },
            { "data": "status" },
        ],
        "order": [],
        "processing": true,
        "responsive": true,
    });

    // Filtro por evento
    $('#filtroEvento').on('change', function() {
        table.ajax.reload();
    });
});
</script>

<?php echo $this->endSection() ?>

