<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Modelos de Documento</div>
    <div class="ms-auto">
        <a href="<?php echo site_url('contratodocumentos/criarmodelo'); ?>" class="btn btn-primary">
            <i class="bx bx-plus me-2"></i>Novo Modelo
        </a>
    </div>
</div>

<div class="card shadow radius-10">
    <div class="card-body">
        <?php if (empty($modelos)): ?>
        <div class="text-center py-5">
            <i class="bx bx-folder-open text-muted" style="font-size: 4rem;"></i>
            <p class="text-muted mt-3">Nenhum modelo cadastrado.</p>
            <a href="<?php echo site_url('contratodocumentos/criarmodelo'); ?>" class="btn btn-primary">
                <i class="bx bx-plus me-2"></i>Criar Primeiro Modelo
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Tipo de Item</th>
                        <th>Status</th>
                        <th>Criado em</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($modelos as $modelo): ?>
                    <tr>
                        <td>
                            <strong><?php echo esc($modelo->nome); ?></strong>
                            <?php if ($modelo->descricao): ?>
                            <br><small class="text-muted"><?php echo esc($modelo->descricao); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $modelo->getBadgeTipoItem(); ?></td>
                        <td><?php echo $modelo->getBadgeAtivo(); ?></td>
                        <td><?php echo $modelo->created_at ? $modelo->created_at->format('d/m/Y') : '-'; ?></td>
                        <td class="text-center">
                            <a href="<?php echo site_url("contratodocumentos/editarmodelo/{$modelo->id}"); ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-edit"></i>
                            </a>
                            <a href="<?php echo site_url("contratodocumentos/excluirmodelo/{$modelo->id}"); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Excluir este modelo?')">
                                <i class="bx bx-trash"></i>
                            </a>
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

