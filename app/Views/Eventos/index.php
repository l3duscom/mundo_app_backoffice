<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>


<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.css') ?>" />



<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>

<?php if ($evento_selecionado) : ?>
    <div class="card rounded-4 mb-3">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <?php 
                    $nome_evento_list = evento_nome();
                    $nome_show = $nome_evento_list && strlen($nome_evento_list) > 3 ? htmlspecialchars($nome_evento_list) : 'Evento Selecionado';
                    ?>
                    <h4 class="mb-0">Evento Ativo: <strong><?= $nome_show ?></strong></h4>
                    
                </div>
                <div>
                    <a href="<?= site_url('/') ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Trocar Evento
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row">

    <?php $id = usuario_logado()->id ?>
    <!--breadcrumb-->
    <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Eventos</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <?php if (usuario_logado()->is_membro) : ?>
                        <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="fadeIn animated bx bx-crown" style="color: #ffd700;" title="Membro Premium"></i> </a></li>
                    <?php else : ?>
                        <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                    <?php endif; ?>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <div class="btn-group">
                <a href="<?php echo site_url('eventos/criar'); ?>" class="btn btn-primary">Criar evento</a>

            </div>
        </div>

    </div>
    <!--end breadcrumb-->
    <div class="col-lg-12">

        <div class="card shadow radius-10">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="ajaxTable" class="table table-striped table-sm" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Data Início</th>
                                <th>Data Fim</th>
                                <th>Categoria</th>
                                <th>Responsável</th>
                                <th>Situação</th>
                            </tr>
                        </thead>
                    </table>
                </div>
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


        $('#ajaxTable').DataTable({

            "oLanguage": DATATABLE_PTBR,

            "ajax": "<?php echo site_url('eventos/recuperaeventos'); ?>",
            "columns": [{
                    "data": "nome"
                },
                {
                    "data": "data_inicio"
                },
                {
                    "data": "data_fim"
                },
                {
                    "data": "categoria"
                },
                {
                    "data": "responsavel"
                },
                {
                    "data": "situacao"
                },
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
    });
</script>

<?php echo $this->endSection() ?>