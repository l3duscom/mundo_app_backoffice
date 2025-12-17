<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>


<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.css') ?>" />

<style>
.bg-purple {
    background-color: #6f42c1 !important;
}
</style>

<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>


<div class="row justify-content-center">
    <div class="col-lg-12 col-xl-12">
    
        <!-- Card de Destaque - Minhas Solicitações -->
        <?php if (isset($refoundsTotal) && $refoundsTotal > 0): ?>
        <div class="card shadow-sm border-0 mb-4" style="border-left: 4px solid #6f42c1 !important;">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="d-flex align-items-center justify-content-center bg-purple rounded-circle" style="width:50px; height:50px;">
                            <i class="bx bx-refresh text-white" style="font-size:1.5rem;"></i>
                        </div>
                    </div>
                    <div class="col">
                        <h6 class="mb-1 fw-bold">Minhas Solicitações</h6>
                        <p class="mb-0 text-muted small">
                            Você tem 
                            <?php if ($refoundsPendentes > 0): ?>
                                <span class="badge bg-warning text-dark"><?= $refoundsPendentes ?> pendente(s)</span>
                            <?php endif; ?>
                            <?= $refoundsTotal ?> solicitação(ões) no total
                        </p>
                    </div>
                    <div class="col-auto">
                        <a href="<?= site_url('pedidos/meus-refounds') ?>" class="btn btn-outline-primary btn-sm">
                            <i class="bx bx-show me-1"></i>Ver Solicitações
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    
        <div class="d-flex align-items-center mb-4">
            <i class="bi bi-bag-check-fill text-primary me-2" style="font-size:2rem;"></i>
            <h2 class="mb-0 fw-bold" style="letter-spacing:0.5px;">Meus Pedidos</h2>
    </div>
        <div class="card shadow radius-10 border-0 mb-4">
            <div class="card-body pb-0">
                <ul class="nav nav-tabs nav-justified nav-primary mb-4" role="tablist">
                                    <li class="nav-item" role="presentation">
                        <a class="nav-link active fw-bold" data-bs-toggle="tab" href="#proximos" role="tab" aria-selected="true">
                            <i class="bi bi-calendar-event me-1"></i> PRÓXIMOS
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                        <a class="nav-link fw-bold" data-bs-toggle="tab" href="#anteriores" role="tab" aria-selected="false">
                            <i class="bi bi-clock-history me-1"></i> ANTERIORES
                                        </a>
                                    </li>
                                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="proximos" role="tabpanel">
                        <?php if (!empty($proximos)) : ?>
                            <div class="row g-4">
                                <?php foreach ($proximos as $p) : ?>
                                                    <?php
                                                    if ($p->status == 'paid' || $p->status == 'CONFIRMED' || $p->status == 'RECEIVED') {
                                                        $status_pedido = 'Aprovado';
                                                    } else if ($p->status == 'waiting' || $p->status == 'PENDING' || $p->status == 'AWAITING_RISK_ANALYSIS') {
                                        $status_pedido = 'Aguardando Pagamento';
                                                    } else if ($p->status == 'canceled' || $p->status == 'REFUNDED' || $p->status == 'REFUND_REQUESTED' || $p->status == 'REFUND_IN_PROGRESS') {
                                                        $status_pedido = 'Cancelado';
                                                    } else {
                                                        $status_pedido = 'Recusado';
                                                    }
                                    $badgeClass = 'bg-secondary';
                                    if ($status_pedido == 'Aprovado') $badgeClass = 'bg-success';
                                    elseif ($status_pedido == 'Aguardando Pagamento') $badgeClass = 'bg-warning text-dark';
                                    elseif ($status_pedido == 'Cancelado') $badgeClass = 'bg-danger';
                                    elseif ($status_pedido == 'Recusado') $badgeClass = 'bg-dark';
                                    ?>
                                    <div class="col-md-12">
                                        <div class="card border shadow-sm h-100">
                                            <div class="card-body p-4">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="bi bi-ticket-perforated-fill text-primary me-2" style="font-size:1.5rem;"></i>
                                                    <h4 class="mb-0 fw-semibold flex-grow-1" style="font-size:1.2rem;"> <?= $p->nome_evento ?> </h4>
                                                    <span class="badge <?= $badgeClass ?> ms-2" style="font-size:1em;"> <?= $status_pedido ?> </span>
                                                </div>
                                                <div class="row mb-2 g-2">
                                                    <div class="col-6">
                                                        <div class="small text-muted">Data da compra</div>
                                                        <div class="fw-bold"> <?= date('d/m/Y H:i', strtotime($p->created_at)) ?> </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="small text-muted">Nº do pedido</div>
                                                        <div class="fw-bold"> <?= $p->cod_pedido ?> </div>
                                                    </div>
                                                </div>
                                                <div class="row mb-2 g-2">
                                                    <div class="col-6">
                                                        <div class="small text-muted">Valor total</div>
                                                        <div class="fw-bold text-success" style="font-size:1.1em;">R$ <?= $p->total ?></div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="small text-muted">Entrega</div>
                                                        <div class="fw-bold"> <?= $p->frete == null ? "Retirar no local" : $p->status_entrega ?> </div>
                                                    </div>
                                                </div>
                                                <div class="mb-2">
                                                    <div class="small text-muted">Código de rastreio</div>
                                                    <div>
                                                        <?php if ($p->rastreio != null) : ?>
                                                            <a href="https://www.melhorrastreio.com.br/rastreio/<?= $p->rastreio ?>" target="_blank" class="fw-bold text-decoration-underline"> <?= $p->rastreio ?> </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">—</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row g-2 align-items-center mb-2">
                                                    <div class="col-auto">
                                                        <i class="bi bi-calendar-check-fill text-primary me-1"></i>
                                                    </div>
                                                    <div class="col-auto small text-muted">Evento</div>
                                                    <div class="col"> <?= date('d/m/Y', strtotime($p->data_inicio)) . ' - ' . date('d/m/Y', strtotime($p->data_fim)) ?> </div>
                                                </div>
                                                <div class="row g-2 align-items-center mb-2">
                                                    <div class="col-auto">
                                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                                    </div>
                                                    <div class="col-auto small text-muted">Local</div>
                                                    <div class="col"> <?= $p->local_evento ?> </div>
                                                </div>
                                                <div class="d-flex align-items-center mt-3">
                                                    <img src="<?php echo site_url('recursos/theme/images/classificacao-livre-logo.png'); ?>" alt="Classificação Livre" width="28" height="auto" class="me-2">
                                                    <span class="small text-muted">Classificação Livre</span>
                                                </div>
                                                <?php if (!empty($p->ingressos)) : ?>
                                                    <hr>
                                                    <div class="mb-2">
                                                        <div class="fw-bold text-secondary mb-2" style="font-size:1.2em;">Ingressos</div>
                                                        <div class="table-responsive">
                                                            <table class="table table-borderless align-middle mb-0" style="min-width:350px;">
                                                                <thead class="small text-muted">
                                                                    <tr>
                                                                        <th>Ingresso</th>
                                                                        <th>Tipo</th>
                                                                        <th>Quantidade</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($p->ingressos as $ingresso) : ?>
                                                                        <tr>
                                                                            <td class="fw-semibold"> <?= $ingresso->nome ?> </td>
                                                                            <td> <?= !empty($ingresso->tipo) ? ucfirst($ingresso->tipo) : '-' ?> </td>
                                                                            <td> <?= $ingresso->quantidade ?? 1 ?> </td>
                                                    </tr>
                                                                    <?php endforeach; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else : ?>
                            <div class="text-center py-5">
                                <i class="bi bi-bag-x-fill text-muted mb-3" style="font-size:2.5rem;"></i>
                                <p class="mb-2">Você não tem pedidos futuros.</p>
                                <a href="<?= site_url('/') ?>" target="_blank" class="btn btn-primary btn-lg rounded-pill fw-bold"><i class="bi bi-ticket-perforated-fill me-2"></i>Comprar ingresso!</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="tab-pane fade" id="anteriores" role="tabpanel">
                        <?php if (!empty($anteriores)) : ?>
                            <div class="row g-4">
                                <?php foreach ($anteriores as $p) : ?>
                                    <?php
                                    if ($p->status == 'paid' || $p->status == 'CONFIRMED' || $p->status == 'RECEIVED') {
                                        $status_pedido = 'Aprovado';
                                    } else if ($p->status == 'waiting' || $p->status == 'PENDING' || $p->status == 'AWAITING_RISK_ANALYSIS') {
                                        $status_pedido = 'Aguardando Pagamento';
                                    } else if ($p->status == 'canceled' || $p->status == 'REFUNDED' || $p->status == 'REFUND_REQUESTED' || $p->status == 'REFUND_IN_PROGRESS') {
                                        $status_pedido = 'Cancelado';
                                    } else {
                                        $status_pedido = 'Recusado';
                                    }
                                    $badgeClass = 'bg-secondary';
                                    if ($status_pedido == 'Aprovado') $badgeClass = 'bg-success';
                                    elseif ($status_pedido == 'Aguardando Pagamento') $badgeClass = 'bg-warning text-dark';
                                    elseif ($status_pedido == 'Cancelado') $badgeClass = 'bg-danger';
                                    elseif ($status_pedido == 'Recusado') $badgeClass = 'bg-dark';
                                    ?>
                                    <div class="col-md-12">
                                        <div class="card border shadow-sm h-100">
                                            <div class="card-body p-4">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="bi bi-ticket-perforated-fill text-primary me-2" style="font-size:1.5rem;"></i>
                                                    <h4 class="mb-0 fw-semibold flex-grow-1" style="font-size:1.2rem;"> <?= $p->nome_evento ?> </h4>
                                                    <span class="badge <?= $badgeClass ?> ms-2" style="font-size:1em;"> <?= $status_pedido ?> </span>
                                                </div>
                                                <div class="row mb-2 g-2">
                                                    <div class="col-6">
                                                        <div class="small text-muted">Data da compra</div>
                                                        <div class="fw-bold"> <?= date('d/m/Y H:i', strtotime($p->created_at)) ?> </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="small text-muted">Nº do pedido</div>
                                                        <div class="fw-bold"> <?= $p->cod_pedido ?> </div>
                                                    </div>
                                                </div>
                                                <div class="row mb-2 g-2">
                                                    <div class="col-6">
                                                        <div class="small text-muted">Valor total</div>
                                                        <div class="fw-bold text-success" style="font-size:1.1em;">R$ <?= $p->total ?></div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="small text-muted">Entrega</div>
                                                        <div class="fw-bold"> <?= $p->frete == null ? "Retirar no local" : $p->status_entrega ?> </div>
                                                    </div>
                                                </div>
                                                <div class="mb-2">
                                                    <div class="small text-muted">Código de rastreio</div>
                                                    <div>
                                                        <?php if ($p->rastreio != null) : ?>
                                                            <a href="https://www.melhorrastreio.com.br/rastreio/<?= $p->rastreio ?>" target="_blank" class="fw-bold text-decoration-underline"> <?= $p->rastreio ?> </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">—</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row g-2 align-items-center mb-2">
                                                    <div class="col-auto">
                                                        <i class="bi bi-calendar-check-fill text-primary me-1"></i>
                                                    </div>
                                                    <div class="col-auto small text-muted">Evento</div>
                                                    <div class="col"> <?= date('d/m/Y', strtotime($p->data_inicio)) . ' - ' . date('d/m/Y', strtotime($p->data_fim)) ?> </div>
                                                </div>
                                                <div class="row g-2 align-items-center mb-2">
                                                    <div class="col-auto">
                                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                                    </div>
                                                    <div class="col-auto small text-muted">Local</div>
                                                    <div class="col">
  <?php
    $local = !empty($p->local) ? $p->local : (isset($p->ingressos[0]->local) ? $p->ingressos[0]->local : '-');
    echo $local;
  ?>
</div>
                                                </div>
                                                <div class="d-flex align-items-center mt-3">
                                                    <img src="<?php echo site_url('recursos/theme/images/classificacao-livre-logo.png'); ?>" alt="Classificação Livre" width="28" height="auto" class="me-2">
                                                    <span class="small text-muted">Classificação Livre</span>
                                                </div>
                                                <?php if (!empty($p->ingressos)) : ?>
                                                    <hr>
                                                    <div class="mb-2">
                                                        <div class="fw-bold text-secondary mb-2" style="font-size:1.2em;">Ingressos</div>
                                                        <div class="table-responsive">
                                                            <table class="table table-borderless align-middle mb-0" style="min-width:350px;">
                                                                <thead class="small text-muted">
                                                                    <tr>
                                                                        <th>Ingresso</th>
                                                                    
                                                                        <th>Quantidade</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($p->ingressos as $ingresso) : ?>
                                                                        <tr>
                                                                            <td class="fw-semibold"> <?= $ingresso->nome ?> </td>
                                                                            <td> <?= $ingresso->quantidade ?? 1 ?> </td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                    </div>
                                    </div>
                                <?php endforeach; ?>
                                </div>
                        <?php else : ?>
                            <div class="text-center py-5">
                                <i class="bi bi-bag-x-fill text-muted mb-3" style="font-size:2.5rem;"></i>
                                <p class="mb-2">Você não tem pedidos anteriores.</p>
                                <a href="<?= site_url('/') ?>" target="_blank" class="btn btn-primary btn-lg rounded-pill fw-bold"><i class="bi bi-ticket-perforated-fill me-2"></i>Comprar ingresso!</a>
                            </div>
                        <?php endif; ?>
                    </div>
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