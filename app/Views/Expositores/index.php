<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>

<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.css') ?>" />

<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Expositores</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Expositores</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="<?php echo site_url('expositores/criar'); ?>" class="btn btn-primary">
            <i class="bx bx-plus me-2"></i>Novo Expositor
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
                        <label class="form-label fw-bold"><i class="bx bx-filter-alt me-1"></i>Filtrar por Tipo</label>
                        <select id="filtroTipo" class="form-select">
                            <option value="">Todos os tipos</option>
                            <option value="Stand Comercial">Stand Comercial</option>
                            <option value="Artist Alley">Artist Alley</option>
                            <option value="Vila dos Artesãos">Vila dos Artesãos</option>
                            <option value="Espaço Medieval">Espaço Medieval</option>
                            <option value="Indie">Indie</option>
                            <option value="Games">Games</option>
                            <option value="Espaço Temático">Espaço Temático</option>
                            <option value="Parceiros">Parceiros</option>
                            <option value="Food Park">Food Park</option>
                            <option value="Patrocinadores">Patrocinadores</option>
                            <option value="Outros">Outros</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold"><i class="bx bx-category me-1"></i>Filtrar por Segmento</label>
                        <select id="filtroSegmento" class="form-select">
                            <option value="">Todos os segmentos</option>
                            <option value="Alimentação">Alimentação</option>
                            <option value="Artesanato">Artesanato</option>
                            <option value="Brinquedos">Brinquedos</option>
                            <option value="Colecionáveis">Colecionáveis</option>
                            <option value="Cosplay">Cosplay</option>
                            <option value="Decoração">Decoração</option>
                            <option value="Eletrônicos">Eletrônicos</option>
                            <option value="Games">Games</option>
                            <option value="K-Pop">K-Pop</option>
                            <option value="Livros e HQs">Livros e HQs</option>
                            <option value="Mangás e Animes">Mangás e Animes</option>
                            <option value="Moda e Acessórios">Moda e Acessórios</option>
                            <option value="Papelaria">Papelaria</option>
                            <option value="Pelúcias">Pelúcias</option>
                            <option value="Serviços">Serviços</option>
                            <option value="Vestuário">Vestuário</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="button" id="btnLimparFiltros" class="btn btn-outline-secondary">
                            <i class="bx bx-x me-1"></i>Limpar Filtros
                        </button>
                    </div>
                </div>
                
                <hr class="mb-4">

                <div class="table-responsive">
                    <table id="ajaxTable" class="table table-striped table-sm" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Nome/Razão Social</th>
                                <th>CPF/CNPJ</th>
                                <th>E-mail</th>
                                <th>Telefone</th>
                                <th>Tipo</th>
                                <th>Segmento</th>
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
        "ajax": "<?php echo site_url('expositores/recuperaexpositores'); ?>",
        "columns": [
            { "data": "nome" },
            { "data": "documento" },
            { "data": "email" },
            { "data": "telefone" },
            { "data": "tipo_expositor" },
            { "data": "segmento" },
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
        "pageLength": 100,
    });

    // Filtro por Tipo de Expositor
    $('#filtroTipo').on('change', function() {
        var valor = $(this).val();
        table.column(4).search(valor).draw();
    });

    // Filtro por Segmento
    $('#filtroSegmento').on('change', function() {
        var valor = $(this).val();
        table.column(5).search(valor).draw();
    });

    // Limpar Filtros
    $('#btnLimparFiltros').on('click', function() {
        $('#filtroTipo').val('');
        $('#filtroSegmento').val('');
        table.columns().search('').draw();
    });
});
</script>

<?php echo $this->endSection() ?>

