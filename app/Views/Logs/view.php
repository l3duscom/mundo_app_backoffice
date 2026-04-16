<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?= esc($filename) ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
    .log-line {
        padding: 8px 12px;
        border-bottom: 1px solid #eee;
        font-size: 13px;
        font-family: 'Courier New', monospace;
        word-break: break-all;
    }
    .log-line:hover {
        background-color: #f8f9fa;
    }
    .log-INFO { border-left: 3px solid #0d6efd; }
    .log-ERROR { border-left: 3px solid #dc3545; background-color: #fff5f5; }
    .log-WARNING { border-left: 3px solid #ffc107; background-color: #fffdf0; }
    .log-DEBUG { border-left: 3px solid #6c757d; }
    .log-CRITICAL { border-left: 3px solid #dc3545; background-color: #ffe0e0; }

    .badge-INFO { background-color: #0d6efd; }
    .badge-ERROR { background-color: #dc3545; }
    .badge-WARNING { background-color: #ffc107; color: #333; }
    .badge-DEBUG { background-color: #6c757d; }
    .badge-CRITICAL { background-color: #dc3545; }

    .log-container {
        max-height: 700px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 6px;
    }
    .log-data {
        color: #888;
        font-size: 11px;
        white-space: nowrap;
        min-width: 140px;
    }
    .log-msg {
        flex: 1;
    }
    .highlight {
        background-color: #fff3cd;
        padding: 1px 3px;
        border-radius: 2px;
        font-weight: bold;
    }
    .filtro-rapido .btn {
        font-size: 12px;
    }
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Configurações</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?= site_url('/') ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?= site_url('/logs') ?>">Logs</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= esc($filename) ?></li>
            </ol>
        </nav>
    </div>
</div>

<!-- Contadores por nível -->
<div class="row mb-3">
    <div class="col">
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= site_url('logs/view/' . $filename) ?>" class="btn btn-sm <?= empty($nivel) ? 'btn-dark' : 'btn-outline-dark' ?>">
                Todos <span class="badge bg-white text-dark ms-1"><?= array_sum($contadores) ?></span>
            </a>
            <?php
            $nivelConfig = [
                'INFO'     => ['cor' => 'primary',  'icone' => 'bx-info-circle'],
                'ERROR'    => ['cor' => 'danger',   'icone' => 'bx-error'],
                'WARNING'  => ['cor' => 'warning',  'icone' => 'bx-error-alt'],
                'DEBUG'    => ['cor' => 'secondary', 'icone' => 'bx-bug'],
                'CRITICAL' => ['cor' => 'danger',   'icone' => 'bx-shield-x'],
            ];
            ?>
            <?php foreach ($contadores as $nv => $count): ?>
                <?php if ($count > 0): ?>
                    <?php $cfg = $nivelConfig[$nv] ?? ['cor' => 'secondary', 'icone' => 'bx-circle']; ?>
                    <a href="<?= site_url('logs/view/' . $filename) ?>?nivel=<?= $nv ?><?= $filtro ? '&filtro=' . urlencode($filtro) : '' ?>"
                       class="btn btn-sm <?= $nivel === $nv ? 'btn-' . $cfg['cor'] : 'btn-outline-' . $cfg['cor'] ?>">
                        <i class="bx <?= $cfg['icone'] ?> me-1"></i><?= $nv ?>
                        <span class="badge bg-white text-dark ms-1"><?= $count ?></span>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="get" action="<?= site_url('logs/view/' . $filename) ?>" class="row align-items-center g-2">
            <div class="col-auto">
                <label class="col-form-label"><i class="bx bx-search me-1"></i>Filtrar:</label>
            </div>
            <div class="col-md-5">
                <input type="text" name="filtro" class="form-control form-control-sm"
                       placeholder="Buscar no log... (ex: UTMify, Webhook, erro)"
                       value="<?= esc($filtro) ?>">
            </div>
            <input type="hidden" name="nivel" value="<?= esc($nivel) ?>">
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bx bx-search me-1"></i>Buscar</button>
            </div>
            <?php if ($filtro || $nivel): ?>
                <div class="col-auto">
                    <a href="<?= site_url('logs/view/' . $filename) ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="bx bx-x me-1"></i>Limpar filtros
                    </a>
                </div>
            <?php endif; ?>

            <!-- Filtros rápidos -->
            <div class="col-auto filtro-rapido">
                <span class="text-muted me-1" style="font-size: 12px;">Rápido:</span>
                <a href="<?= site_url('logs/view/' . $filename) ?>?filtro=UTMify" class="btn btn-sm btn-outline-success">UTMify</a>
                <a href="<?= site_url('logs/view/' . $filename) ?>?filtro=Webhook" class="btn btn-sm btn-outline-info">Webhook</a>
                <a href="<?= site_url('logs/view/' . $filename) ?>?filtro=Pontos" class="btn btn-sm btn-outline-warning">Pontos</a>
                <a href="<?= site_url('logs/view/' . $filename) ?>?filtro=Assinatura" class="btn btn-sm btn-outline-secondary">Assinatura</a>
            </div>
        </form>
    </div>
</div>

<!-- Resultado -->
<div class="card">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
            <strong>
                <i class="bx bx-list-ul me-1"></i>
                <?= $total ?> registro(s)
                <?= $filtro ? ' contendo "' . esc($filtro) . '"' : '' ?>
                <?= $nivel ? ' [' . esc($nivel) . ']' : '' ?>
            </strong>
            <a href="<?= site_url('logs') ?>" class="btn btn-sm btn-outline-dark">
                <i class="bx bx-arrow-back me-1"></i>Voltar
            </a>
        </div>

        <div class="log-container">
            <?php if (empty($linhas)): ?>
                <div class="text-center text-muted py-5">
                    <i class="bx bx-search-alt" style="font-size: 50px;"></i>
                    <p class="mt-2">Nenhum registro encontrado<?= $filtro ? ' para "' . esc($filtro) . '"' : '' ?>.</p>
                </div>
            <?php else: ?>
                <?php foreach ($linhas as $linha): ?>
                    <div class="log-line log-<?= esc($linha['nivel']) ?> d-flex align-items-start gap-2">
                        <span class="badge badge-<?= esc($linha['nivel']) ?> text-white" style="min-width: 70px; font-size: 10px;">
                            <?= esc($linha['nivel']) ?>
                        </span>
                        <span class="log-data"><?= esc($linha['data']) ?></span>
                        <span class="log-msg">
                            <?php
                            $msg = esc($linha['mensagem']);
                            if ($filtro) {
                                $msg = preg_replace('/(' . preg_quote(esc($filtro), '/') . ')/i', '<span class="highlight">$1</span>', $msg);
                            }
                            echo $msg;
                            ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<?php echo $this->endSection() ?>
