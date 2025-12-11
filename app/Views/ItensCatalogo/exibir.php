<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>
<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">
        <a href="<?php echo site_url('/itenscatalogo'); ?>">Catálogo de Itens</a>
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('/itenscatalogo'); ?>">Catálogo</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= esc($item->nome); ?></li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="<?php echo site_url("itenscatalogo/editar/$item->id"); ?>" class="btn btn-primary">
            <i class="bx bx-edit-alt me-1"></i>Editar
        </a>
        <?php if ($item->deleted_at == null) : ?>
            <a href="<?php echo site_url("itenscatalogo/excluir/$item->id"); ?>" class="btn btn-danger ms-2">
                <i class="bx bx-trash me-1"></i>Excluir
            </a>
        <?php else : ?>
            <a href="<?php echo site_url("itenscatalogo/desfazerexclusao/$item->id"); ?>" class="btn btn-success ms-2">
                <i class="bx bx-undo me-1"></i>Restaurar
            </a>
        <?php endif; ?>
        <a href="<?php echo site_url("itenscatalogo") ?>" class="btn btn-secondary ms-2">
            <i class="bx bx-arrow-back me-1"></i>Voltar
        </a>
    </div>
</div>
<!--end breadcrumb-->

<div class="row">
    <div class="col-xl-8 mx-auto">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-4">
                    <div class="me-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #0d6efd;">
                            <i class="bx bx-box text-white fs-3"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-1"><?php echo esc($item->nome); ?></h5>
                        <div><?php echo $item->getBadgeTipo(); ?> <?php echo $item->exibeStatus(); ?></div>
                    </div>
                    <div class="text-end">
                        <h3 class="text-primary mb-0"><?php echo $item->getValorFormatado(); ?></h3>
                    </div>
                </div>

                <hr>

                <div class="row g-3">
                    <div class="col-md-4">
                        <small class="text-muted">Evento:</small>
                        <p class="mb-0 fw-bold"><?php echo esc($evento->nome ?? 'N/A'); ?></p>
                    </div>

                    <?php if (!empty($item->metragem)): ?>
                    <div class="col-md-4">
                        <small class="text-muted">Metragem:</small>
                        <p class="mb-0 fw-bold"><?php echo esc($item->metragem); ?></p>
                    </div>
                    <?php endif; ?>

                    <div class="col-md-4">
                        <small class="text-muted">Criado em:</small>
                        <p class="mb-0"><?php echo $item->created_at->humanize(); ?></p>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted">Atualizado:</small>
                        <p class="mb-0"><?php echo $item->updated_at->humanize(); ?></p>
                    </div>
                </div>

                <?php if (!empty($item->descricao)): ?>
                <hr>
                <div>
                    <small class="text-muted">Descrição:</small>
                    <p class="mb-0"><?php echo nl2br(esc($item->descricao)); ?></p>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>


<?php echo $this->section('scripts') ?>
<?php echo $this->endSection() ?>

