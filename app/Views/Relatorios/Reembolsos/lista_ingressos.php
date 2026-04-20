<?= $this->extend('Layout/principal') ?>

<?= $this->section('titulo') ?>
<?= esc($titulo) ?>
<?= $this->endSection() ?>

<?php
$q = array_filter([
    'data_inicio' => $filtros['data_inicio'],
    'data_fim' => $filtros['data_fim'],
    'evento_id' => $filtros['evento_id'],
    'tipo_solicitacao' => $filtros['tipo_solicitacao'],
    'status' => $filtros['status'],
], function ($v) {
    return $v !== null && $v !== '';
});
$queryExport = http_build_query($q);
?>

<?= $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Relatórios</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?= site_url('/') ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?= site_url('relatorios/reembolsos') ?>">Reembolsos</a></li>
                <li class="breadcrumb-item"><a href="<?= site_url('relatorios/reembolsos/lista?' . $queryExport) ?>">Resultado</a></li>
                <li class="breadcrumb-item active">Detalhe por ingresso</li>
            </ol>
        </nav>
    </div>
</div>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="mb-0"><?= esc($titulo) ?></h5>
    <div class="btn-group flex-wrap">
        <a href="<?= site_url('relatorios/reembolsos/exportar-excel-detalhe?' . $queryExport) ?>" class="btn btn-outline-success btn-sm">
            <i class="bx bx-download me-1"></i>CSV detalhe
        </a>
        <a href="<?= site_url('relatorios/reembolsos/exportar-pdf-detalhe?' . $queryExport) ?>" class="btn btn-outline-danger btn-sm" target="_blank">
            <i class="bx bx-file me-1"></i>PDF detalhe
        </a>
        <a href="<?= site_url('relatorios/reembolsos/lista?' . $queryExport) ?>" class="btn btn-outline-primary btn-sm">
            <i class="bx bx-list-ul me-1"></i>Resumo por pedido
        </a>
        <a href="<?= site_url('relatorios/reembolsos') ?>" class="btn btn-outline-secondary btn-sm">Novos filtros</a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted small mb-1">Evento</p>
                <p class="fw-bold mb-0"><?= esc($evento->nome ?? 'Todos os eventos') ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted small mb-1">Período (data da solicitação)</p>
                <p class="fw-bold mb-0"><?= date('d/m/Y', strtotime($filtros['data_inicio'])) ?> — <?= date('d/m/Y', strtotime($filtros['data_fim'])) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted small mb-1">Totais (ingressos)</p>
                <p class="fw-bold mb-0"><?= (int) ($totais_detalhe->total_linhas ?? 0) ?> linha(s) — R$ <?= number_format((float) ($totais_detalhe->valor_ingressos ?? 0), 2, ',', '.') ?></p>
            </div>
        </div>
    </div>
</div>

<p class="text-muted small mb-3">Uma linha por ingresso do pedido associado à solicitação. Pedidos sem ingressos no cadastro não aparecem aqui.</p>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0 table-sm">
                <thead class="table-dark">
                    <tr>
                        <th>Solicitação</th>
                        <th>Cliente</th>
                        <th>Pedido</th>
                        <th>Evento</th>
                        <th>Situação</th>
                        <th>Pagamento</th>
                        <th>Data sol.</th>
                        <th>Ingresso</th>
                        <th>Código</th>
                        <th>Participante</th>
                        <th class="text-end">Valor ing.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($linhas)) : ?>
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">Nenhum ingresso encontrado para as solicitações deste período (verifique se os pedidos possuem ingressos cadastrados).</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($linhas as $l) : ?>
                            <tr>
                                <td><?= (int) $l['refound_id'] ?></td>
                                <td><?= esc($l['cliente_nome']) ?></td>
                                <td><?= esc($l['pedido_codigo']) ?></td>
                                <td><?= esc($l['evento_nome']) ?></td>
                                <td><span class="badge bg-success"><?= esc($l['situacao']) ?></span></td>
                                <td><?= esc($l['pagamento']) ?></td>
                                <td><?= esc($l['data_solicitacao']) ?></td>
                                <td><?= esc($l['ingresso_nome']) ?></td>
                                <td><code class="small"><?= esc($l['ingresso_codigo']) ?></code></td>
                                <td><?= esc($l['ingresso_participante']) ?></td>
                                <td class="text-end">R$ <?= number_format($l['ingresso_valor'], 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
