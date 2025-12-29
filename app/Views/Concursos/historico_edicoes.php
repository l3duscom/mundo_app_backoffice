<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="row">
    <div class="col-lg-12">
        <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Histórico de Edições</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Inscrição: <?= $inscricao->codigo ?></li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a href="javascript:history.back()" class="btn btn-outline-secondary"><i class="bx bx-arrow-back"></i> Voltar</a>
            </div>
        </div>

        <div class="card shadow radius-10">
            <div class="card-header">
                <h6 class="mb-0">Inscrição: <strong><?= $inscricao->codigo ?></strong></h6>
            </div>
            <div class="card-body">
                <?php if (empty($historico)): ?>
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        Esta inscrição ainda não possui histórico de edições.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Campos Alterados</th>
                                    <th>IP</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historico as $item): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i:s', strtotime($item->created_at)) ?></td>
                                        <td>
                                            <span class="badge bg-secondary"><?= esc($item->campos_alterados ?: 'N/A') ?></span>
                                        </td>
                                        <td><?= esc($item->ip_address) ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalDetalhes<?= $item->id ?>">
                                                <i class="bx bx-search"></i> Detalhes
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Modal de Detalhes -->
                                    <div class="modal fade" id="modalDetalhes<?= $item->id ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Detalhes da Edição - <?= date('d/m/Y H:i:s', strtotime($item->created_at)) ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h6 class="text-danger"><i class="bx bx-minus-circle"></i> Dados Anteriores</h6>
                                                            <pre class="bg-light p-3 rounded" style="font-size: 11px; max-height: 300px; overflow: auto;"><?= is_string($item->dados_anteriores) ? json_encode(json_decode($item->dados_anteriores), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : json_encode($item->dados_anteriores, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6 class="text-success"><i class="bx bx-plus-circle"></i> Dados Novos</h6>
                                                            <pre class="bg-light p-3 rounded" style="font-size: 11px; max-height: 300px; overflow: auto;"><?= is_string($item->dados_novos) ? json_encode(json_decode($item->dados_novos), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : json_encode($item->dados_novos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <p class="mb-0 text-muted small">
                                                        <strong>User Agent:</strong> <?= esc($item->user_agent) ?>
                                                    </p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<?php echo $this->endSection() ?>
