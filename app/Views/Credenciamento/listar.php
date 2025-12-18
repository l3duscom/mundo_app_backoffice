<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<link href="<?php echo site_url('recursos/theme/'); ?>plugins/datatable/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Credenciamentos</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Credenciamentos</li>
            </ol>
        </nav>
    </div>
</div>

<?php if (session()->getFlashdata('success')) : ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bx bx-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')) : ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bx bx-error-circle me-2"></i><?= session()->getFlashdata('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="card shadow radius-10">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bx bx-id-card me-2"></i>Credenciamentos de Expositores</h6>
                <?php $eventoIdUrl = $_GET['evento_id'] ?? ''; ?>
                <input type="hidden" id="filtroEvento" value="<?php echo esc($eventoIdUrl); ?>">
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tabelaCredenciamentos" class="table table-striped table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>Contrato</th>
                                <th>Expositor</th>
                                <th>Evento</th>
                                <th class="text-center">Veículos</th>
                                <th class="text-center">Pessoas</th>
                                <th>Progresso</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Carregado via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script src="<?php echo site_url('recursos/theme/'); ?>plugins/datatable/js/jquery.dataTables.min.js"></script>
<script src="<?php echo site_url('recursos/theme/'); ?>plugins/datatable/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    var tabela = $('#tabelaCredenciamentos').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '<?= site_url('credenciamento/recuperaCredenciamentos') ?>',
            type: 'GET',
            data: function(d) {
                d.evento_id = $('#filtroEvento').val();
            }
        },
        columns: [
            { data: 'contrato' },
            { data: 'expositor' },
            { data: 'evento' },
            { data: 'veiculos', className: 'text-center' },
            { data: 'pessoas', className: 'text-center' },
            { data: 'progresso' },
            { data: 'status', className: 'text-center' },
            { data: 'acoes', className: 'text-center', orderable: false }
        ],
        order: [[0, 'desc']],
        language: {
            url: '<?= site_url('recursos/datatable-pt-BR.json'); ?>'
        },
        pageLength: 100,
        responsive: true
    });

    // Filtro por evento
    $('#filtroEvento').on('change', function() {
        tabela.ajax.reload();
    });
});
</script>
<?php echo $this->endSection() ?>
