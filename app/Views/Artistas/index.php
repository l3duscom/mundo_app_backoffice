<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<link href="<?php echo site_url('recursos/theme/'); ?>plugins/datatable/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Gestão</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Artistas</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="<?php echo site_url('artistas/criar'); ?>" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i>Novo Artista
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0"><i class="bx bx-microphone me-2"></i>Artistas Cadastrados</h5>
            </div>
            <div class="col-auto">
                <select class="form-select form-select-sm" id="filtroAtivo" style="width: 150px;">
                    <option value="">Todos</option>
                    <option value="1" selected>Ativos</option>
                    <option value="0">Inativos</option>
                </select>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabelaArtistas" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>Nome Artístico</th>
                        <th>Gênero</th>
                        <th>Telefone</th>
                        <th>E-mail</th>
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
<script src="<?php echo site_url('recursos/theme/'); ?>plugins/datatable/js/jquery.dataTables.min.js"></script>
<script src="<?php echo site_url('recursos/theme/'); ?>plugins/datatable/js/dataTables.bootstrap5.min.js"></script>

<script>
var tabela;

$(document).ready(function() {
    tabela = $('#tabelaArtistas').DataTable({
        ajax: {
            url: '<?php echo site_url("artistas/recuperaartistas"); ?>',
            type: 'GET',
            data: function(d) {
                d.ativo = $('#filtroAtivo').val();
            }
        },
        columns: [
            { data: 'nome_artistico' },
            { data: 'genero_musical' },
            { data: 'telefone' },
            { data: 'email' },
            { data: 'status', className: 'text-center' },
            { data: 'acoes', className: 'text-center', orderable: false }
        ],
        order: [[0, 'asc']],
        language: {
            url: '<?php echo site_url("recursos/theme/plugins/datatable/js/pt-BR.json"); ?>'
        }
    });

    $('#filtroAtivo').change(function() {
        tabela.ajax.reload();
    });
});
</script>
<?php echo $this->endSection() ?>
