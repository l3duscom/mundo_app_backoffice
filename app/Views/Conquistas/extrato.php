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
                <li class="breadcrumb-item"><a href="<?php echo site_url('conquistas-admin'); ?>">Conquistas</a></li>
                <li class="breadcrumb-item active" aria-current="page">Extrato de Pontos</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="<?php echo site_url('conquistas-admin'); ?>" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-2"></i>Voltar
        </a>
    </div>
</div>
<!--end breadcrumb-->

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <label class="form-label">Tipo de Transação</label>
                <select id="filtroTipo" class="form-select">
                    <option value="">Todos</option>
                    <option value="CONQUISTA">Conquista</option>
                    <option value="BONUS">Bônus</option>
                    <option value="COMPRA">Compra</option>
                    <option value="REVOGACAO">Revogação</option>
                    <option value="RESGATE">Resgate</option>
                    <option value="EXPIRACAO">Expiração</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Buscar Usuário (ID)</label>
                <input type="number" id="filtroUsuario" class="form-control" placeholder="ID do usuário">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="button" id="btnFiltrar" class="btn btn-primary me-2">
                    <i class="bx bx-filter-alt me-1"></i>Filtrar
                </button>
                <button type="button" id="btnLimpar" class="btn btn-outline-secondary">
                    <i class="bx bx-x me-1"></i>Limpar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bx bx-list-ul me-2"></i>Histórico de Transações de Pontos</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabelaExtrato" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Usuário</th>
                        <th>Tipo</th>
                        <th class="text-end">Pontos</th>
                        <th class="text-end">Saldo</th>
                        <th>Descrição</th>
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
    
    var tabela = $('#tabelaExtrato').DataTable({
        ajax: {
            url: '<?php echo site_url("conquistas-admin/recuperaExtrato"); ?>',
            type: 'GET',
            data: function(d) {
                d.event_id = eventoContexto;
                d.tipo = $('#filtroTipo').val();
                d.user_id = $('#filtroUsuario').val();
            }
        },
        columns: [
            { data: 'id', visible: false },
            { data: 'data' },
            { data: 'usuario' },
            { data: 'tipo', className: 'text-center' },
            { data: 'pontos', className: 'text-end' },
            { data: 'saldo', className: 'text-end' },
            { data: 'descricao' }
        ],
        order: [[0, 'desc']],
        language: {
            url: '<?php echo site_url("recursos/theme/plugins/datatable/js/pt-BR.json"); ?>'
        }
    });
    
    // Filtrar
    $('#btnFiltrar').click(function() {
        tabela.ajax.reload();
    });
    
    // Limpar filtros
    $('#btnLimpar').click(function() {
        $('#filtroTipo').val('');
        $('#filtroUsuario').val('');
        tabela.ajax.reload();
    });
    
    // Enter para buscar
    $('#filtroUsuario').keypress(function(e) {
        if (e.which == 13) {
            tabela.ajax.reload();
        }
    });
});
</script>

<?php echo $this->endSection() ?>
