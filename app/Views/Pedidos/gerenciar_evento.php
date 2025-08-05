<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>


<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.css') ?>" />



<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>

<?php if (isset($evento_selecionado)) : ?>
    <div class="card rounded-4 mb-3">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <?php 
                    $nome_pedidos = evento_nome();
                    $nome_display = $nome_pedidos && strlen($nome_pedidos) > 3 ? htmlspecialchars($nome_pedidos) : 'Evento Selecionado';
                    ?>
                    <h4 class="mb-0">Gerenciando Pedidos: <strong><?= $nome_display ?></strong></h4>
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

<!-- Menu de abas -->
<div class="tab-menu mb-4">
    <a href="<?= site_url('ingressos/add'); ?>" class="tab<?= (current_url() == site_url('ingressos/add')) ? ' active' : ''; ?>">Add ingressos</a>
    <a href="<?= site_url('pedidos/recompra/' . $evento); ?>" class="tab cyan<?= (current_url() == site_url('pedidos/recompra/' . $evento)) ? ' active' : ''; ?>">Recompra</a>
    <a href="<?= site_url('pedidos/entrega/' . $evento); ?>" class="tab yellow<?= (current_url() == site_url('pedidos/entrega/' . $evento)) ? ' active' : ''; ?>">Aguardando Entrega</a>
    <a href="<?= site_url('pedidos/enviados/' . $evento); ?>" class="tab gray<?= (current_url() == site_url('pedidos/enviados/' . $evento)) ? ' active' : ''; ?>">Enviados</a>
    <a href="<?= site_url('pedidos/pendentes/' . $evento); ?>" class="tab red<?= (current_url() == site_url('pedidos/pendentes/' . $evento)) ? ' active' : ''; ?>">Pendentes</a>
    <a href="<?= site_url('pedidos/reembolsados/' . $evento); ?>" class="tab blue<?= (current_url() == site_url('pedidos/reembolsados/' . $evento)) ? ' active' : ''; ?>">Reembolsados</a>
    <a href="<?= site_url('pedidos/chargeback/' . $evento); ?>" class="tab orange<?= (current_url() == site_url('pedidos/chargeback/' . $evento)) ? ' active' : ''; ?>">Chargeback</a>
    <a href="<?= site_url('pedidos/vip/' . $evento); ?>" class="tab black<?= (current_url() == site_url('pedidos/vip/' . $evento)) ? ' active' : ''; ?>">VIP - Aguardando</a>
    <a href="<?= site_url('pedidos/vipentregue/' . $evento); ?>" class="tab black<?= (current_url() == site_url('pedidos/vipentregue/' . $evento)) ? ' active' : ''; ?>">VIP - Entregue</a>
</div>
<style>
.tab-menu {
    display: flex;
    gap: 0.5rem;
    background: #181f2c;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    overflow-x: auto;
}
.tab-menu .tab {
    padding: 0.5rem 1.2rem;
    border-radius: 0.4rem;
    font-weight: 500;
    color: #bfc9db;
    background: transparent;
    border: none;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
    outline: none;
    white-space: nowrap;
    text-decoration: none;
    display: inline-block;
}
.tab-menu .tab.active,
.tab-menu .tab:focus {
    background: #2563eb;
    color: #fff;
}
.tab-menu .tab:hover:not(.active) {
    background: #232b3e;
    color: #fff;
}
.tab-menu .tab.yellow { background: #facc15; color: #222; }
.tab-menu .tab.yellow.active { background: #eab308; color: #fff; }
.tab-menu .tab.gray { background: #6b7280; color: #fff; }
.tab-menu .tab.cyan { background: #06b6d4; color: #fff; }
.tab-menu .tab.cyan.active { background: #0891b2; color: #fff; }
.tab-menu .tab.red { background: #ef4444; color: #fff; }
.tab-menu .tab.red.active { background: #dc2626; color: #fff; }
.tab-menu .tab.blue { background: #3b82f6; color: #fff; }
.tab-menu .tab.blue.active { background: #2563eb; color: #fff; }
.tab-menu .tab.orange { background: #f97316; color: #fff; }
.tab-menu .tab.orange.active { background: #ea580c; color: #fff; }
.tab-menu .tab.black { background: #181f2c; color: #fff; }
</style>

<!-- Conteúdo principal da página -->
<div class="row">
    <!-- ... tabela, cards, etc ... -->
    <div class="col-lg-12">
    
        <div class="card shadow radius-10">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="ajaxTable" class="table table-striped table-sm" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Pedido</th>
                                <th>Status</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Whatsapp</th>
                                <th>CPF</th>
                                <th>Frete</th>
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

            "ajax": "<?php echo site_url('pedidos/recuperapedidosadmin/' . $evento); ?>",
            "columns": [{
                    "data": "cod_pedido"
                },
                {
                    "data": "status"
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
                    "data": "cpf"
                },
                {
                    "data": "frete"
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