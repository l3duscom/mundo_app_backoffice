<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="container-fluid py-4">

    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-1">
                    <i class="bx bx-list-ul me-2"></i>Melhor Envio — Logs &amp; Status
                    <?php $ambProd = env('CI_ENVIRONMENT') === 'production'; ?>
                    <span class="badge bg-<?= $ambProd ? 'danger' : 'warning text-dark' ?> ms-2"><?= $ambProd ? 'PRODUÇÃO' : 'SANDBOX' ?></span>
                </h4>
                <p class="text-muted mb-0 small">Trilha completa de chamadas à API e webhooks recebidos.</p>
            </div>
            <a href="<?= site_url('melhor-envio/conectar') ?>" class="btn btn-outline-primary btn-sm">
                <i class="bx bx-link me-1"></i>Conexão
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('sucesso')) : ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('sucesso')) ?></div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body">
                    <h6 class="text-muted mb-3"><i class="bx bx-key me-1"></i>Credencial atual</h6>
                    <?php if ($cred) : ?>
                        <?php $horas = $cred->expiraEmHoras(); ?>
                        <p class="mb-1 small"><strong>Expira em:</strong> <?= esc((string)$cred->expires_at) ?> (<?= $horas !== null ? number_format($horas, 1, ',', '.') . 'h' : '-' ?>)</p>
                        <p class="mb-1 small"><strong>Atualizada em:</strong> <?= esc((string)$cred->updated_at) ?></p>
                        <p class="mb-0 small text-muted"><strong>Escopo:</strong> <code class="small"><?= esc($cred->scope ?? '-') ?></code></p>
                    <?php else : ?>
                        <p class="mb-0 text-danger small">
                            <i class="bx bx-error-circle me-1"></i>Nenhuma credencial cadastrada.
                            <a href="<?= site_url('melhor-envio/conectar') ?>">Conectar</a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body">
                    <h6 class="text-muted mb-3"><i class="bx bx-filter-alt me-1"></i>Filtrar logs</h6>
                    <form method="get" action="<?= site_url('melhor-envio/status') ?>" class="row g-2">
                        <div class="col-8">
                            <input type="number" class="form-control form-control-sm" name="pedido_id"
                                   value="<?= esc($pedidoIdFiltro ?? '') ?>" placeholder="ID do pedido">
                        </div>
                        <div class="col-4">
                            <button class="btn btn-sm btn-primary w-100">Filtrar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0" style="border-radius: 12px;">
        <div class="card-body">
            <h6 class="text-muted mb-3"><i class="bx bx-history me-1"></i>Últimas 100 chamadas</h6>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Data</th>
                            <th>Pedido</th>
                            <th>Endpoint</th>
                            <th>Método</th>
                            <th class="text-end">HTTP</th>
                            <th class="text-end">ms</th>
                            <th>Erro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)) : ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">Sem registros.</td></tr>
                        <?php else : ?>
                            <?php foreach ($logs as $l) : ?>
                                <?php
                                $http = (int)($l['http_status'] ?? 0);
                                $cls  = $http === 0 ? 'secondary' : ($http >= 400 ? 'danger' : ($http >= 300 ? 'warning' : 'success'));
                                ?>
                                <tr>
                                    <td class="small text-nowrap"><?= esc($l['created_at']) ?></td>
                                    <td class="small"><?= $l['pedido_id'] ? esc($l['pedido_id']) : '-' ?></td>
                                    <td class="small"><code><?= esc($l['endpoint']) ?></code></td>
                                    <td class="small"><?= esc($l['http_method']) ?></td>
                                    <td class="text-end"><span class="badge bg-<?= $cls ?>"><?= $http ?: '-' ?></span></td>
                                    <td class="text-end small"><?= esc((string)($l['duracao_ms'] ?? '-')) ?></td>
                                    <td class="small text-danger"><?= esc((string)($l['erro'] ?? '')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php echo $this->endSection() ?>
