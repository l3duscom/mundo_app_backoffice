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
    <div class="breadcrumb-title pe-3 text-muted">Cupons de Desconto</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Cupons</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="<?php echo site_url('cupons/criar'); ?>" class="btn btn-primary">
            <i class="bx bx-plus me-2"></i>Novo Cupom
        </a>
    </div>
</div>
<!--end breadcrumb-->

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabelaCupons" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nome</th>
                        <th>Desconto</th>
                        <th>Valor Mínimo</th>
                        <th>Quantidade</th>
                        <th>Validade</th>
                        <th>Situação</th>
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
    
    var tabela = $('#tabelaCupons').DataTable({
        ajax: {
            url: '<?php echo site_url("cupons/recuperaCupons"); ?>',
            type: 'GET',
            data: function(d) {
                d.evento_id = eventoContexto;
            }
        },
        columns: [
            { data: 'codigo' },
            { data: 'nome' },
            { data: 'desconto' },
            { data: 'valor_minimo' },
            { data: 'quantidade' },
            { data: 'validade' },
            { data: 'situacao' },
            { data: 'acoes', className: 'text-center', orderable: false }
        ],
        order: [[0, 'asc']],
        language: {
            url: '<?php echo site_url("recursos/theme/plugins/datatable/js/pt-BR.json"); ?>'
        }
    });


    // Alterar status
    $(document).on('click', '.btn-status', function() {
        var btn = $(this);
        var id = btn.data('id');

        $.ajax({
            url: '<?php echo site_url("cupons/alterarStatus"); ?>',
            type: 'POST',
            data: {
                id: id,
                '<?php echo csrf_token(); ?>': $('input[name="<?php echo csrf_token(); ?>"]').val() || '<?php echo csrf_hash(); ?>'
            },
            dataType: 'json',
            success: function(response) {
                console.log('Response:', response);
                
                // Atualiza o token CSRF
                if (response.token) {
                    $('input[name="<?php echo csrf_token(); ?>"]').val(response.token);
                }
                
                if (response.erro) {
                    alert('Erro: ' + response.erro);
                } else {
                    alert('Sucesso: ' + response.sucesso);
                    // Recarrega a tabela
                    tabela.ajax.reload(null, false);
                }
            },
            error: function(xhr, status, error) {
                console.log('Error:', xhr, status, error);
                alert('Erro ao processar requisição: ' + error);
            }
        });
    });
});
</script>

<?php echo $this->endSection() ?>
