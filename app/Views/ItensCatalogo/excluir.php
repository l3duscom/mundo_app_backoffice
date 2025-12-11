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
                <li class="breadcrumb-item active" aria-current="page">Excluir</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<div class="row">
    <div class="col-xl-6 mx-auto">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h6 class="mb-0"><i class="bx bx-trash me-2"></i>Confirmar Exclusão</h6>
            </div>
            <div class="card-body text-center">
                <div class="mb-4">
                    <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                </div>
                
                <h5>Deseja realmente excluir este item?</h5>
                
                <div class="my-4 p-3 bg-light rounded">
                    <h4 class="mb-1"><?php echo esc($item->nome); ?></h4>
                    <p class="mb-1"><?php echo $item->getBadgeTipo(); ?></p>
                    <p class="mb-0 text-primary fw-bold"><?php echo $item->getValorFormatado(); ?></p>
                </div>

                <p class="text-muted">Esta ação pode ser desfeita posteriormente.</p>

                <form action="<?php echo site_url("itenscatalogo/excluir/$item->id"); ?>" method="post">
                    <?php echo csrf_field(); ?>
                    
                    <button type="submit" class="btn btn-danger px-4">
                        <i class="bx bx-trash me-1"></i>Sim, Excluir
                    </button>
                    <a href="<?php echo site_url("itenscatalogo/exibir/$item->id"); ?>" class="btn btn-secondary ms-2">
                        <i class="bx bx-x me-1"></i>Cancelar
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>


<?php echo $this->section('scripts') ?>
<?php echo $this->endSection() ?>

