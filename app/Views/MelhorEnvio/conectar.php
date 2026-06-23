<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg,#0f6b3d 0%,#1c9f5a 100%); border-radius: 12px;">
                <div class="card-body py-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-white bg-opacity-25 p-3 me-3">
                            <i class="bx bx-package text-white" style="font-size: 1.6rem;"></i>
                        </div>
                        <div>
                            <h4 class="text-white mb-1">Melhor Envio</h4>
                            <p class="text-white-50 mb-0 small">Integração para cotação, compra e geração de etiquetas de envio.</p>
                        </div>
                    </div>
                    <a href="<?= site_url('melhor-envio/status') ?>" class="btn btn-light btn-sm">
                        <i class="bx bx-list-ul me-1"></i>Ver Status / Logs
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php if (session()->getFlashdata('erro')) : ?>
        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= esc(session()->getFlashdata('erro')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('sucesso')) : ?>
        <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= esc(session()->getFlashdata('sucesso')) ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <div class="card-body">
                    <h6 class="text-muted mb-3"><i class="bx bx-link me-1"></i>Status da conexão</h6>

                    <?php if ($cred) : ?>
                        <?php
                        $horas = $cred->expiraEmHoras();
                        $statusClasse = ($horas !== null && $horas > 24) ? 'success' : (($horas !== null && $horas > 0) ? 'warning' : 'danger');
                        ?>
                        <p class="mb-2">
                            <span class="badge bg-<?= $statusClasse ?>">CONECTADO</span>
                            <small class="text-muted ms-2">
                                Token expira em
                                <strong><?= $horas !== null ? number_format($horas, 1, ',', '.') . ' h' : '?' ?></strong>
                                (<?= esc((string)$cred->expires_at) ?>)
                            </small>
                        </p>
                        <p class="mb-3 small text-muted">
                            Escopo: <code><?= esc($cred->scope ?? '-') ?></code>
                        </p>
                        <a href="<?= esc($authUrl) ?>" class="btn btn-outline-primary">
                            <i class="bx bx-refresh me-1"></i>Reconectar conta
                        </a>
                    <?php else : ?>
                        <p class="mb-3">
                            <span class="badge bg-secondary">NÃO CONECTADO</span>
                        </p>
                        <p class="text-muted mb-3 small">
                            Conecte a conta do Melhor Envio uma única vez. Os tokens serão armazenados e renovados automaticamente.
                        </p>
                        <a href="<?= esc($authUrl) ?>" class="btn btn-success">
                            <i class="bx bx-link-external me-1"></i>Conectar com Melhor Envio
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body">
                    <h6 class="text-muted mb-3"><i class="bx bx-info-circle me-1"></i>Ambiente</h6>
                    <p class="mb-1 small">
                        <strong>Modo:</strong>
                        <code><?= env('CI_ENVIRONMENT') === 'production' ? 'PRODUÇÃO' : 'SANDBOX' ?></code>
                    </p>
                    <p class="mb-1 small">
                        <strong>Redirect URI:</strong><br>
                        <code class="small"><?= esc(env('MELHOR_ENVIO_REDIRECT_URI', '-')) ?></code>
                    </p>
                    <p class="mb-0 small text-muted">
                        Volume padrão fixo: A<?= env('ME_VOL_ALTURA', 2) ?> · L<?= env('ME_VOL_LARGURA', 12) ?> · C<?= env('ME_VOL_COMPRIMENTO', 17) ?> cm · <?= env('ME_VOL_PESO', 0.5) ?> kg
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>
