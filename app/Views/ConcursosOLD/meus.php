<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>


<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.css') ?>" />



<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>


<div class="row">
    <?php $id = usuario_logado()->id ?>
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Meus Pedidos</div>
    </div>
    <!--end breadcrumb-->


    <div class="row">

        <div class="row">
            <?php if ($pedidos != null) : ?>
                <div class="col-lg-12">
                    <div class="card shadow radius-10">
                        <div class="card-body">
                            <div class="card-body">
                                <ul class="nav nav-tabs nav-primary" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link active" data-bs-toggle="tab" href="#proximos" role="tab" aria-selected="true">
                                            <div class="d-flex align-items-center">

                                                <div class="tab-title font-18">PRÓXIMOS</div>
                                            </div>
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" data-bs-toggle="tab" href="#anteriores" role="tab" aria-selected="false">
                                            <div class="d-flex align-items-center">

                                                <div class="tab-title font-18">ANTERIORES</div>
                                            </div>
                                        </a>
                                    </li>

                                </ul>
                                <div class="tab-content py-3">
                                    <div class="tab-pane fade show active" id="dangerhome" role="tabpanel">
                                        <?php foreach ($pedidos as $p) : ?>

                                            <div class="card border shadow-none">
                                                <div class="card-body mb-0 mt-0">
                                                    <?php
                                                    if ($p->status == 'paid' || $p->status == 'CONFIRMED' || $p->status == 'RECEIVED') {
                                                        $status_pedido = 'Aprovado';
                                                    } else if ($p->status == 'waiting' || $p->status == 'PENDING' || $p->status == 'AWAITING_RISK_ANALYSIS') {
                                                        $status_pedido = 'Aguardando';
                                                    } else if ($p->status == 'canceled' || $p->status == 'REFUNDED' || $p->status == 'REFUND_REQUESTED' || $p->status == 'REFUND_IN_PROGRESS') {
                                                        $status_pedido = 'Cancelado';
                                                    } else {
                                                        $status_pedido = 'Recusado';
                                                    }
                                                    ?>
                                                    <h3 class=" mb-4"><?= $p->nome_evento ?></h3>
                                                    <p class="mb-2" style="font-size:16px">Status: <?= $status_pedido ?></p>
                                                    <p class="mb-2" style="font-size:16px">Data da compra: <?= date('d/m/Y H:i:s', strtotime($p->created_at)) ?></p>
                                                    <p class="mb-2" style="font-size:16px">Número do pedido: <?= $p->cod_pedido ?></p>
                                                    <p class="mb-2" style="font-size:16px">Valor total do pedido: R$ <?= $p->total ?></p>
                                                    <p class="mb-2" style="font-size:16px">Status da entrega: <?= $p->frete == null ? "Retirar no local" : $p->status_entrega ?></p>
                                                    <p class="mb-2" style="font-size:16px">Código de rastreio:
                                                        <?php if ($p->rastreio != null) : ?>
                                                            <a href="https://www.melhorrastreio.com.br/rastreio/<?= $p->rastreio ?>" target="_blank"><?= $p->rastreio ?></a>
                                                        <?php endif; ?>
                                                    </p>
                                                    <hr>
                                                    <p class="mb-2" style="font-size:16px"><i class="bi bi-calendar-check-fill" style="padding-right: 10px; "></i> <?= date('d/m/Y', strtotime($p->data_inicio)) . ' - ' . date('d/m/Y', strtotime($p->data_fim)) ?></p>
                                                    <p class="mb-2" style="font-size:16px"><i class="bx bx-map-pin" style="padding-right: 10px;"></i>
                                                        <?= $p->local_evento ?> - Av. Ipiranga, 6681 - Partenon, Porto Alegre - RS, 90619-900
                                                    </p>
                                                    <p class="mb-2"> <img src=" <?php echo site_url('recursos/theme/images/classificacao-livre-logo.png'); ?>" style="padding-right: 10px;" alt="" width=" 28px" height="auto">
                                                        Classificação Livre</p>
                                                    <tr>

                                                        <td><?= $p->frete == null ? "Retirar no local" : "receber em casa" ?></td>
                                                    </tr>
                                                </div>
                                            </div>

                                        <?php endforeach; ?>
                                    </div>
                                    <div class="tab-pane fade" id="dangerprofile" role="tabpanel">
                                        <p>Food truck fixie locavore, accusamus mcsweeney's marfa nulla single-origin coffee squid. Exercitation +1 labore velit, blog sartorial PBR leggings next level wes anderson artisan four loko farm-to-table craft beer twee. Qui photo booth letterpress, commodo enim craft beer mlkshk aliquip jean shorts ullamco ad vinyl cillum PBR. Homo nostrud organic, assumenda labore aesthetic magna delectus mollit. Keytar helvetica VHS salvia yr, vero magna velit sapiente labore stumptown. Vegan fanny pack odio cillum wes anderson 8-bit, sustainable jean shorts beard ut DIY ethical culpa terry richardson biodiesel. Art party scenester stumptown, tumblr butcher vero sint qui sapiente accusamus tattooed echo park.</p>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    <?php else : ?>
        <center>
            <hr>
            <p>Você ainda não fen nenhum pedido :(</p>

            <a href="<?= site_url('/carrinho') ?>" target="_blank" class="btn btn-primary">Comprar ingresso!</a>
            <hr>
        </center>
    <?php endif; ?>


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


        $(' #ajaxTable').DataTable({
            "oLanguage": DATATABLE_PTBR,
            "ajax": "<?php echo site_url('declarations/recuperaDeclaracoesPorUsuario'); ?>",
            "columns": [{
                "data": "nome"
            }, {
                "data": "month"
            }, {
                "data": "type"
            }, {
                "data": "status"
            }, {
                "data": "created_at"
            }, ],
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