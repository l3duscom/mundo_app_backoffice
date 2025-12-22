<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>


<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.css') ?>" />



<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>


<div class="row">

    <?php $id = usuario_logado()->id ?>
    <!--breadcrumb-->
    <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Aguardando envio CINEMARK</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">

                    <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>

                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <div class="btn-group">
                <a href="<?php echo site_url('ingressos/add'); ?>" class="btn btn-primary">Add ingressos</a>
                <a href="<?php echo site_url('pedidos/gerenciar_evento/' . $evento); ?>" class="btn btn-dark">Todos</a>
                <a href="<?php echo site_url('pedidos/entrega/' . $evento); ?>" class="btn btn-warning">Aguardando Entrega</a>
                <a href="<?php echo site_url('pedidos/enviados/' . $evento); ?>" class="btn btn-secondary">Enviados</a>
                <a href="<?php echo site_url('pedidos/pendentes/' . $evento); ?>" class="btn btn-danger">Pendentes</a>
                <a href="<?php echo site_url('pedidos/reembolsados/' . $evento); ?>" class="btn btn-info">Reembolsados</a>
                <a href="<?php echo site_url('pedidos/chargeback/' . $evento); ?>" class="btn btn-warning">Chargeback</a>
                <a href="<?php echo site_url('pedidos/vipentregue/' . $evento); ?>" class="btn btn-dark">VIP - Entregue</a>

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
                                <th>Pedido</th>
                                <th></th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Whatsapp</th>
                                <th>Bonus</th>
                                <th>Rastreio</th>
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

            "ajax": "<?php echo site_url('pedidos/recuperaPedidosAdminVip/' . $evento); ?>",
            "columns": [{
                    "data": "cod_pedido"
                },
                {
                    "data": "frete"
                },

                {
                    "data": "nome"
                },
                {
                    "data": "email"
                },
                {
                    "data": "telefone"
                },
                {
                    "data": "bonus"
                },
                {
                    "data": "rastreio"
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