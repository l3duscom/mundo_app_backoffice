<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.css') ?>" />
<style>
.export-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 1rem;
    margin-bottom: 2rem;
}
.stats-card {
    background: white;
    border-radius: 0.8rem;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}
.stats-card:hover {
    transform: translateY(-5px);
}
.stats-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: #667eea;
}
.btn-export {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    border: none;
    padding: 1rem 2rem;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 0.5rem;
    transition: all 0.3s;
}
.btn-export:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 15px rgba(245, 87, 108, 0.3);
}
.table-export {
    font-size: 0.9rem;
}
.table-export th {
    background: #667eea;
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}
.badge-status {
    padding: 0.5rem 1rem;
    border-radius: 2rem;
    font-weight: 600;
}
</style>
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
    <a href="<?= site_url('ingressos/add'); ?>" class="tab">Add ingressos</a>
    <a href="<?= site_url('pedidos/recompra/' . $evento); ?>" class="tab cyan">Recompra</a>
    <a href="<?= site_url('pedidos/entrega/' . $evento); ?>" class="tab yellow">Aguardando Entrega</a>
    <a href="<?= site_url('pedidos/enviados/' . $evento); ?>" class="tab gray">Enviados</a>
    <a href="<?= site_url('pedidos/pendentes/' . $evento); ?>" class="tab red">Pendentes</a>
    <a href="<?= site_url('pedidos/reembolsados/' . $evento); ?>" class="tab blue">Reembolsados</a>
    <a href="<?= site_url('pedidos/chargeback/' . $evento); ?>" class="tab orange">Chargeback</a>
    <a href="<?= site_url('pedidos/vip/' . $evento); ?>" class="tab black">VIP - Aguardando</a>
    <a href="<?= site_url('pedidos/vipentregue/' . $evento); ?>" class="tab black">VIP - Entregue</a>
    <a href="<?= site_url('pedidos/dados-envio/' . $evento); ?>" class="tab active purple">📦 Exportar Envios</a>
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
.tab-menu .tab.purple { background: #a855f7; color: #fff; }
.tab-menu .tab.purple.active { background: #9333ea; color: #fff; }
</style>

<!-- Header com estatísticas -->
<div class="export-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2 class="mb-2"><i class="bi bi-box-seam"></i> Dados para Exportação de Envios</h2>
            <p class="mb-0 opacity-90">
                Visualize e exporte os dados de endereço dos pedidos que precisam ser enviados
            </p>
        </div>
        <div class="col-md-4 text-end">
            <div class="stats-number"><?= $totalPedidos ?></div>
            <div>pedidos aguardando envio</div>
        </div>
    </div>
</div>

<!-- Cards de Informações -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Total de Pedidos</h6>
                    <div class="stats-number" style="font-size: 2rem;"><?= count($dadosEnvio) ?></div>
                </div>
                <i class="bi bi-box-seam" style="font-size: 3rem; color: #667eea; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Evento</h6>
                    <h5 class="mb-0" style="color: #667eea;"><?= esc($evento_selecionado->nome) ?></h5>
                </div>
                <i class="bi bi-calendar-event" style="font-size: 3rem; color: #667eea; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Status</h6>
                    <span class="badge badge-status bg-warning text-dark">
                        <i class="bi bi-clock"></i> Aguardando Envio
                    </span>
                </div>
                <i class="bi bi-hourglass-split" style="font-size: 3rem; color: #667eea; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</div>

<!-- Botão de Exportação -->
<div class="card rounded-4 mb-3">
    <div class="card-body text-center py-4">
        <?php if (!empty($dadosEnvio)) : ?>
            <a href="<?= site_url('pedidos/exportar-envios/' . $evento) ?>" class="btn btn-export btn-lg">
                <i class="bi bi-file-earmark-arrow-down me-2"></i>
                Exportar CSV para Envio
            </a>
            <p class="text-muted mt-3 mb-0">
                <small><i class="bi bi-info-circle"></i> O arquivo CSV será baixado automaticamente e pode ser usado diretamente com sua transportadora</small>
            </p>
        <?php else : ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                Não há pedidos aguardando envio para este evento no momento.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Tabela de Visualização -->
<?php if (!empty($dadosEnvio)) : ?>
<div class="card rounded-4">
    <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <h5 class="mb-0 text-white">
            <i class="bi bi-table"></i> Pré-visualização dos Dados
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabelaEnvios" class="table table-hover table-export" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>CEP</th>
                        <th>Endereço</th>
                        <th>Número</th>
                        <th>Complemento</th>
                        <th>Bairro</th>
                        <th>Cidade</th>
                        <th>UF</th>
                        <th>Email</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dadosEnvio as $item) : ?>
                    <tr>
                        <td>
                            <a href="<?= site_url('pedidos/ingressos/' . $item['pedido_id']) ?>" target="_blank" style="text-decoration: none;">
                                <strong><?= esc($item['nome']) ?></strong>
                                <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 0.8rem;"></i>
                            </a>
                        </td>
                        <td><small><?= esc($item['cpf']) ?></small></td>
                        <td><?= esc($item['cep']) ?></td>
                        <td><?= esc($item['endereco']) ?></td>
                        <td><?= esc($item['numero']) ?></td>
                        <td><?= esc($item['complemento'] ?? '-') ?></td>
                        <td><?= esc($item['bairro']) ?></td>
                        <td><?= esc($item['cidade']) ?></td>
                        <td><span class="badge bg-primary"><?= esc($item['uf']) ?></span></td>
                        <td><small><?= esc($item['email']) ?></small></td>
                        <td><strong>R$ <?= number_format($item['valor_declarado'], 2, ',', '.') ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

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
        }
    };

    $('#tabelaEnvios').DataTable({
        "oLanguage": DATATABLE_PTBR,
        "order": [[0, 'asc']],
        "pageLength": 25,
        "responsive": true,
        "pagingType": $(window).width() < 768 ? "simple" : "simple_numbers",
    });
});
</script>

<?php echo $this->endSection() ?>

