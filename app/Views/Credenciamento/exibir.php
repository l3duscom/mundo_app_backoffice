<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
    .pessoa-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
    }
    .pessoa-card.responsavel { border-left: 4px solid #667eea; }
    .pessoa-card.funcionario { border-left: 4px solid #28a745; }
    .pessoa-card.suplente { border-left: 4px solid #ffc107; }
    .veiculo-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        border-left: 4px solid #17a2b8;
    }
    .info-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .badge-status {
        font-size: 0.9rem;
        padding: 8px 15px;
    }
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3 text-muted">
            <a href="<?php echo site_url('/'); ?>">Dashboard</a>
        </div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?php echo site_url('credenciamento/listar'); ?>">Credenciamentos</a></li>
                    <li class="breadcrumb-item active"><?= esc($contrato->codigo ?? 'Detalhes') ?></li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a href="<?= site_url('credenciamento/listar') ?>" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i>Voltar
            </a>
        </div>
    </div>

    <!-- Cabeçalho -->
    <div class="card info-card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <?php 
                    $instagram = $expositor->instagram ?? '';
                    if (!empty($instagram) && strpos($instagram, '@') !== 0) {
                        $instagram = '@' . $instagram;
                    }
                    $nomeExpositor = $expositor->nome ?? 'Expositor';
                    $tituloCredenciamento = !empty($instagram) ? "{$instagram} - {$nomeExpositor}" : $nomeExpositor;
                    ?>
                    <h4 class="mb-1"><i class="bi bi-person-badge me-2"></i>Credenciamento de <?= esc($tituloCredenciamento) ?></h4>
                    <p class="mb-0 opacity-75">
                        <strong><?= esc($contrato->codigo ?? 'N/A') ?></strong>
                        - <?= esc($evento->titulo ?? 'Evento') ?>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <?= $credenciamento->getBadgeStatus() ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Coluna Principal -->
        <div class="col-lg-8">
            <!-- VEÍCULO -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-truck text-info me-2"></i>Veículo</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($veiculos)) : ?>
                    <?php foreach ($veiculos as $veiculo) : ?>
                    <div class="veiculo-card d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-truck text-info me-2"></i>
                            <strong><?= esc($veiculo->modelo) ?></strong> - <?= esc($veiculo->placa) ?>
                            <small class="text-muted d-block mt-1">Cor: <?= esc($veiculo->cor) ?></small>
                        </div>
                        <div>
                            <span class="badge bg-success"><i class="bi bi-check me-1"></i>Cadastrado</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else : ?>
                    <p class="text-muted mb-0"><i class="bi bi-info-circle me-1"></i>Nenhum veículo cadastrado.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- RESPONSÁVEL E FUNCIONÁRIOS -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-people text-primary me-2"></i>Responsável e Funcionários</h5>
                </div>
                <div class="card-body">
                    <!-- Responsável -->
                    <h6 class="text-muted mb-3">Responsável</h6>
                    <?php if ($responsavel) : ?>
                    <div class="pessoa-card responsavel d-flex justify-content-between align-items-center">
                        <div>
                            <?= $responsavel->getBadgeTipo() ?>
                            <strong class="ms-2"><?= esc($responsavel->nome) ?></strong>
                            <small class="text-muted d-block mt-1">
                                CPF: <?= $responsavel->getCpfFormatado() ?> | 
                                RG: <?= esc($responsavel->rg) ?> | 
                                WhatsApp: <?= $responsavel->getWhatsappFormatado() ?>
                            </small>
                        </div>
                        <div>
                            <?= $responsavel->getBadgeStatusAprovacao() ?>
                        </div>
                    </div>
                    <?php else : ?>
                    <p class="text-muted"><i class="bi bi-exclamation-triangle me-1"></i>Nenhum responsável cadastrado.</p>
                    <?php endif; ?>

                    <hr>

                    <!-- Funcionários -->
                    <h6 class="text-muted mb-3">Funcionários</h6>
                    <?php if (!empty($funcionarios)) : ?>
                    <?php foreach ($funcionarios as $func) : ?>
                    <div class="pessoa-card funcionario d-flex justify-content-between align-items-center">
                        <div>
                            <?= $func->getBadgeTipo() ?>
                            <strong class="ms-2"><?= esc($func->nome) ?></strong>
                            <small class="text-muted d-block mt-1">
                                CPF: <?= $func->getCpfFormatado() ?> | 
                                RG: <?= esc($func->rg) ?> | 
                                WhatsApp: <?= $func->getWhatsappFormatado() ?>
                            </small>
                        </div>
                        <div>
                            <?= $func->getBadgeStatusAprovacao() ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else : ?>
                    <p class="text-muted"><i class="bi bi-info-circle me-1"></i>Nenhum funcionário cadastrado.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- SUPLENTES -->
            <?php if (!empty($suplentes) || ($limites['suplentes'] ?? 0) > 0) : ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-person-plus text-warning me-2"></i>Suplentes</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($suplentes)) : ?>
                    <?php foreach ($suplentes as $sup) : ?>
                    <div class="pessoa-card suplente d-flex justify-content-between align-items-center">
                        <div>
                            <?= $sup->getBadgeTipo() ?>
                            <strong class="ms-2"><?= esc($sup->nome) ?></strong>
                            <small class="text-muted d-block mt-1">
                                CPF: <?= $sup->getCpfFormatado() ?> | 
                                RG: <?= esc($sup->rg) ?> | 
                                WhatsApp: <?= $sup->getWhatsappFormatado() ?>
                            </small>
                        </div>
                        <div>
                            <?= $sup->getBadgeStatusAprovacao() ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else : ?>
                    <p class="text-muted mb-0"><i class="bi bi-info-circle me-1"></i>Nenhum suplente cadastrado.</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Informações do Contrato -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-file-text text-primary me-2"></i>Informações</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Contrato</small>
                        <p class="mb-0 fw-bold"><?= esc($contrato->codigo) ?></p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Expositor</small>
                        <p class="mb-0"><?= esc($expositor->nome ?? 'N/A') ?></p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Evento</small>
                        <p class="mb-0"><?= esc($evento->titulo ?? 'N/A') ?></p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Status do Credenciamento</small>
                        <p class="mb-0"><?= $credenciamento->getBadgeStatus() ?></p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Limite de Funcionários</small>
                        <p class="mb-0"><?= $limites['funcionarios'] ?></p>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted">Limite de Suplentes</small>
                        <p class="mb-0"><?= $limites['suplentes'] ?></p>
                    </div>
                </div>
            </div>

            <!-- Ações -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-gear text-primary me-2"></i>Ações</h5>
                </div>
                <div class="card-body">
                    <?php if ($credenciamento->status === 'completo') : ?>
                    <form action="<?= site_url('credenciamento/aprovarTudo') ?>" method="post" class="mb-2">
                        <?= csrf_field() ?>
                        <input type="hidden" name="credenciamento_id" value="<?= $credenciamento->id ?>">
                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Confirma a aprovação de todo o credenciamento?')">
                            <i class="bi bi-check-circle me-1"></i>Aprovar Tudo
                        </button>
                    </form>
                    <?php endif; ?>

                    <?php if (in_array($credenciamento->status, ['completo', 'aprovado'])) : ?>
                    <form action="<?= site_url('credenciamento/devolver') ?>" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="credenciamento_id" value="<?= $credenciamento->id ?>">
                        <button type="submit" class="btn btn-warning w-100" onclick="return confirm('Devolver credenciamento para o expositor preencher novamente?')">
                            <i class="bi bi-arrow-return-left me-1"></i>Devolver para Edição
                        </button>
                    </form>
                    <?php endif; ?>

                    <hr>

                    <a href="<?= site_url('expositores/exibir/' . $expositor->id) ?>" class="btn btn-outline-primary w-100 mb-2">
                        <i class="bi bi-person me-1"></i>Ver Expositor
                    </a>
                    <a href="<?= site_url('contratos/exibir/' . $contrato->id) ?>" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-file-text me-1"></i>Ver Contrato
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>
