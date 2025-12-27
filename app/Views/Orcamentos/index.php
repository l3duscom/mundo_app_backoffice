<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<link href="<?php echo site_url('recursos/theme/'); ?>plugins/datatable/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Orçamentos</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Orçamentos</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto d-flex gap-2">
        <select class="form-select form-select-sm" id="filtroSituacao" style="width: 200px;">
            <option value="">Todas as Situações</option>
            <option value="rascunho">Rascunho</option>
            <option value="enviado">Enviado</option>
            <option value="aprovado">Aprovado</option>
            <option value="em_andamento">Em Andamento</option>
            <option value="concluido">Concluído</option>
            <option value="cancelado">Cancelado</option>
        </select>
        <a href="<?php echo site_url('orcamentos/criar'); ?>" class="btn btn-primary">
            <i class="bx bx-plus me-2"></i>Novo Orçamento
        </a>
    </div>
</div>
<!--end breadcrumb-->

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabelaOrcamentos" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Título</th>
                        <th>Fornecedor</th>
                        <th>Valor</th>
                        <th>Situação</th>
                        <th>Data</th>
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
<script src="<?php echo site_url('recursos/theme/'); ?>plugins/datatable/js/jquery.dataTables.min.js"></script>
<script src="<?php echo site_url('recursos/theme/'); ?>plugins/datatable/js/dataTables.bootstrap5.min.js"></script>

<script>
var tabela;

$(document).ready(function() {
    tabela = $('#tabelaOrcamentos').DataTable({
        ajax: {
            url: '<?php echo site_url("orcamentos/recuperaorcamentos"); ?>',
            type: 'GET',
            data: function(d) {
                d.situacao = $('#filtroSituacao').val();
                d.evento_id = '<?php echo $evento_id ?? ''; ?>';
            }
        },
        columns: [
            { data: 'codigo' },
            { data: 'titulo' },
            { data: 'fornecedor' },
            { data: 'valor_final', className: 'text-end' },
            { data: 'situacao', className: 'text-center' },
            { data: 'data', className: 'text-center' }
        ],
        order: [[5, 'desc']],
        language: {
            url: '<?php echo site_url("recursos/theme/plugins/datatable/js/pt-BR.json"); ?>'
        }
    });

    $('#filtroSituacao').on('change', function() {
        tabela.ajax.reload();
    });
});
</script>

<?php echo $this->endSection() ?>
