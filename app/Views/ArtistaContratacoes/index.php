<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Artistas</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active">Contratações</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="<?php echo site_url('artista-contratacoes/criar'); ?>" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i>Nova Contratação
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bx bx-calendar-event me-2"></i>Contratações de Artistas</h5>
    </div>
    <div class="card-body">
        <?php if (empty($contratacoes)): ?>
        <div class="alert alert-info text-center mb-0">
            <i class="bx bx-info-circle me-2"></i>Nenhuma contratação registrada para este evento.
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Artista</th>
                        <th>Data</th>
                        <th>Horário</th>
                        <th class="text-end">Cachê</th>
                        <th class="text-center">Situação</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contratacoes as $c): ?>
                    <tr>
                        <td><code><?php echo esc($c->codigo); ?></code></td>
                        <td><?php echo esc($c->nome_artistico ?? '-'); ?></td>
                        <td><?php echo $c->getDataApresentacaoFormatada(); ?></td>
                        <td><?php echo $c->getHorarioFormatado(); ?></td>
                        <td class="text-end"><?php echo $c->getValorCacheFormatado(); ?></td>
                        <td class="text-center"><?php echo $c->exibeSituacao(); ?></td>
                        <td class="text-center">
                            <div class="btn-group">
                                <a href="<?php echo site_url("artista-contratacoes/exibir/{$c->id}"); ?>" class="btn btn-sm btn-outline-info" title="Ver">
                                    <i class="bx bx-show"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php echo $this->endSection() ?>
