<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<!-- DataTables -->
<link href="<?php echo site_url('recursos/theme/'); ?>plugins/datatable/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<!-- Evento do contexto -->
<input type="hidden" id="eventoContexto" value="<?php echo esc($evento_id ?? ''); ?>">
<!-- CSRF Token -->
<input type="hidden" name="<?php echo csrf_token(); ?>" value="<?php echo csrf_hash(); ?>">

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Conquistas</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Conquistas</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto d-flex gap-2">
        <a href="<?php echo site_url('conquistas-admin/extrato'); ?>" class="btn btn-outline-info">
            <i class="bx bx-list-ul me-2"></i>Extrato de Pontos
        </a>
        <a href="<?php echo site_url('conquistas-admin/ranking'); ?>" class="btn btn-outline-success">
            <i class="bx bx-bar-chart-alt-2 me-2"></i>Ranking
        </a>
        <a href="<?php echo site_url('conquistas-admin/criar'); ?>" class="btn btn-primary">
            <i class="bx bx-plus me-2"></i>Nova Conquista
        </a>
    </div>
</div>
<!--end breadcrumb-->

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabelaConquistas" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Pontos</th>
                        <th>Nível</th>
                        <th>Usuários</th>
                        <th>Status</th>
                        <th class="text-center" style="width: 120px;">Ações</th>
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
$(document).ready(function() {
    
    var eventoContexto = $('#eventoContexto').val();
    
    var tabela = $('#tabelaConquistas').DataTable({
        ajax: {
            url: '<?php echo site_url("conquistas-admin/recupera"); ?>',
            type: 'GET',
            data: function(d) {
                d.event_id = eventoContexto;
            }
        },
        columns: [
            { data: 'codigo' },
            { data: 'nome' },
            { data: 'descricao' },
            { data: 'pontos', className: 'text-center' },
            { data: 'nivel', className: 'text-center' },
            { data: 'usuarios', className: 'text-center' },
            { data: 'status', className: 'text-center' },
            { data: 'acoes', className: 'text-center', orderable: false }
        ],
        order: [[0, 'asc']],
        language: {
            url: '<?php echo site_url("recursos/theme/plugins/datatable/js/pt-BR.json"); ?>'
        }
    });
});
</script>

<?php echo $this->endSection() ?>
