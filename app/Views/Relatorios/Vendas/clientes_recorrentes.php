<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<!-- DataTables -->
<link href="<?php echo site_url('recursos/theme/'); ?>plugins/datatable/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
<style>
    .stat-card {
        padding: 1.5rem;
        border-radius: 12px;
        color: white;
        text-align: center;
        height: 100%;
    }
    .stat-card.purple {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .stat-card.green {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    .stat-card.orange {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    .stat-card.blue {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    .stat-card h3 {
        font-size: 2.2rem;
        font-weight: bold;
        margin: 0;
    }
    .stat-card p {
        margin: 0.5rem 0 0;
        opacity: 0.9;
        font-size: 0.9rem;
    }
    .top-3 {
        background: linear-gradient(135deg, #ffd700 0%, #ffb347 100%);
    }
    .distribuicao-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 12px;
        border-bottom: 1px solid #eee;
    }
    .distribuicao-item:last-child {
        border-bottom: none;
    }
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Relatórios</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Clientes Recorrentes</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<!-- Cards estatísticos -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card purple">
            <h3><?php echo number_format($estatisticas['total_clientes_recorrentes'], 0, ',', '.'); ?></h3>
            <p><i class="bx bx-group"></i> Clientes Recorrentes</p>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card green">
            <h3><?php echo $estatisticas['media_eventos_por_cliente']; ?></h3>
            <p><i class="bx bx-calendar-event"></i> Média Eventos/Cliente</p>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card orange">
            <h3><?php echo number_format($estatisticas['total_ingressos_recorrentes'], 0, ',', '.'); ?></h3>
            <p><i class="bx bx-ticket"></i> Total Ingressos</p>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card blue">
            <h3>R$ <?php echo number_format($estatisticas['valor_total_recorrentes'], 0, ',', '.'); ?></h3>
            <p><i class="bx bx-money"></i> Valor Total</p>
        </div>
    </div>
</div>

<div class="row">
    <!-- Tabela Principal -->
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bx bx-trophy me-2 text-warning"></i>Ranking de Clientes - Múltiplos Eventos</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tabelaClientes" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 60px;">#</th>
                                <th>Cliente</th>
                                <th class="text-center">Eventos</th>
                                <th class="text-center">Ingressos</th>
                                <th class="text-end">Valor Total</th>
                                <th class="text-center">Primeira Compra</th>
                                <th class="text-center">Última Compra</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $posicao = 1; ?>
                            <?php foreach ($clientes as $cliente): ?>
                                <tr class="<?php echo $posicao <= 3 ? 'table-warning' : ''; ?>">
                                    <td class="text-center fw-bold"><?php echo $posicao; ?>º</td>
                                    <td>
                                        <?php if ($posicao <= 3): ?>
                                            <i class="bx bx-medal text-warning"></i>
                                        <?php endif; ?>
                                        <strong><?php echo esc($cliente['nome']); ?></strong><br>
                                        <small class="text-muted"><?php echo esc($cliente['email']); ?></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary fs-6"><?php echo $cliente['total_eventos']; ?></span>
                                    </td>
                                    <td class="text-center"><?php echo number_format($cliente['total_ingressos'], 0, ',', '.'); ?></td>
                                    <td class="text-end text-success fw-bold">R$ <?php echo number_format($cliente['valor_total'], 2, ',', '.'); ?></td>
                                    <td class="text-center"><?php echo date('d/m/Y', strtotime($cliente['primeira_compra'])); ?></td>
                                    <td class="text-center"><?php echo date('d/m/Y', strtotime($cliente['ultima_compra'])); ?></td>
                                </tr>
                                <?php $posicao++; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sidebar com distribuição -->
    <div class="col-lg-3">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bx bx-pie-chart-alt me-2"></i>Distribuição por Eventos</h6>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($distribuicao)): ?>
                    <?php foreach ($distribuicao as $qtdEventos => $qtdClientes): ?>
                        <div class="distribuicao-item">
                            <span>
                                <span class="badge bg-primary"><?php echo $qtdEventos; ?></span> 
                                <?php echo $qtdEventos == 1 ? 'evento' : 'eventos'; ?>
                            </span>
                            <span class="fw-bold"><?php echo $qtdClientes; ?> <?php echo $qtdClientes == 1 ? 'cliente' : 'clientes'; ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-3 text-center text-muted">Nenhum dado</div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bx bx-star me-2"></i>Recordista</h6>
            </div>
            <div class="card-body text-center">
                <?php if (!empty($clientes)): ?>
                    <?php $recordista = $clientes[0]; ?>
                    <i class="bx bx-trophy text-warning" style="font-size: 3rem;"></i>
                    <h5 class="mt-2 mb-1"><?php echo esc($recordista['nome']); ?></h5>
                    <p class="text-muted mb-2"><?php echo esc($recordista['email']); ?></p>
                    <span class="badge bg-primary fs-5"><?php echo $recordista['total_eventos']; ?> eventos</span>
                    <p class="mt-2 mb-0 text-success fw-bold">R$ <?php echo number_format($recordista['valor_total'], 2, ',', '.'); ?></p>
                <?php else: ?>
                    <p class="text-muted">Nenhum cliente recorrente</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Eventos do recordista -->
        <?php if (!empty($clientes)): ?>
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bx bx-calendar me-2"></i>Eventos do Recordista</h6>
            </div>
            <div class="card-body">
                <?php 
                $eventos = explode(', ', $recordista['eventos_participados']);
                foreach ($eventos as $evento): 
                ?>
                    <span class="badge bg-light text-dark mb-1 d-inline-block"><?php echo esc($evento); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<!-- DataTables -->
<script src="<?php echo site_url('recursos/theme/'); ?>plugins/datatable/js/jquery.dataTables.min.js"></script>
<script src="<?php echo site_url('recursos/theme/'); ?>plugins/datatable/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#tabelaClientes').DataTable({
        ordering: false,
        language: {
            url: '<?php echo site_url("recursos/theme/plugins/datatable/js/pt-BR.json"); ?>'
        },
        pageLength: 25
    });
});
</script>
<?php echo $this->endSection() ?>
