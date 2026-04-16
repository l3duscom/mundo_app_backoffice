<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> Logs do Sistema <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
    .log-card {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .log-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .log-card a {
        text-decoration: none;
        color: inherit;
    }
    .log-today {
        border-left: 4px solid #6c038f !important;
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
                <li class="breadcrumb-item active" aria-current="page">Logs do Sistema</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="mb-0"><i class="bx bx-file me-2"></i>Arquivos de Log</h5>
                    <span class="badge bg-secondary"><?= count($arquivos) ?> arquivos</span>
                </div>

                <div class="alert alert-info py-2" style="font-size: 13px;">
                    <i class="bx bx-info-circle me-1"></i>
                    Clique em um arquivo para visualizar o conteúdo com filtros. Use o filtro de texto para buscar por <strong>"UTMify"</strong>, <strong>"Webhook"</strong>, etc.
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Arquivo</th>
                                <th>Data</th>
                                <th>Tamanho</th>
                                <th>Última modificação</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($arquivos)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bx bx-folder-open" style="font-size: 40px;"></i><br>
                                        Nenhum arquivo de log encontrado.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $hoje = 'log-' . date('Y-m-d') . '.log'; ?>
                                <?php foreach ($arquivos as $arq): ?>
                                    <tr class="<?= $arq['nome'] === $hoje ? 'table-active' : '' ?>">
                                        <td>
                                            <i class="bx bx-file text-primary me-1"></i>
                                            <strong><?= esc($arq['nome']) ?></strong>
                                            <?php if ($arq['nome'] === $hoje): ?>
                                                <span class="badge bg-success ms-1">Hoje</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($arq['data']) ?></td>
                                        <td><?= esc($arq['tamanho']) ?></td>
                                        <td><?= esc($arq['modified']) ?></td>
                                        <td>
                                            <a href="<?= site_url('logs/view/' . $arq['nome']) ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bx bx-show me-1"></i>Ver
                                            </a>
                                            <a href="<?= site_url('logs/view/' . $arq['nome']) ?>?filtro=UTMify" class="btn btn-sm btn-outline-success">
                                                <i class="bx bx-target-lock me-1"></i>UTMify
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<?php echo $this->endSection() ?>
