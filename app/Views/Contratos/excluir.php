<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>

<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">
        <a href="<?php echo site_url('/contratos'); ?>">Contratos</a>
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('/contratos'); ?>">Contratos</a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url("contratos/exibir/$contrato->id") ?>"><?= esc($contrato->codigo ?? '#' . $contrato->id); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Excluir</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<div class="row">

    <div class="col-lg-6 mx-auto">

        <div class="card shadow radius-10 border-danger border-start border-0 border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #dc3545;">
                            <i class="bx bx-trash text-white fs-3"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-1">Confirmar Exclusão</h5>
                        <p class="text-muted mb-0">Esta ação pode ser desfeita posteriormente</p>
                    </div>
                </div>
                
                <hr>
                
                <div class="alert alert-warning" role="alert">
                    <i class="bx bx-error me-2"></i>
                    <strong>Atenção!</strong> Você está prestes a excluir o contrato:
                </div>
                
                <div class="bg-light p-3 rounded mb-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <strong><i class="bx bx-file me-2"></i><?php echo esc($contrato->codigo ?? '#' . $contrato->id); ?></strong>
                            <span class="ms-2"><?php echo $contrato->getBadgeSituacao(); ?></span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Expositor:</small>
                            <p class="mb-0"><?php echo esc($expositor->getNomeExibicao()); ?></p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Tipo:</small>
                            <p class="mb-0"><?php echo esc($contrato->tipo_contrato); ?></p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Valor Final:</small>
                            <p class="mb-0"><?php echo $contrato->getValorFinalFormatado(); ?></p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Valor Pago:</small>
                            <p class="mb-0"><?php echo $contrato->getValorPagoFormatado(); ?></p>
                        </div>
                    </div>
                </div>

                <?php echo form_open("contratos/excluir/$contrato->id") ?>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-danger">
                        <i class="bx bx-trash me-2"></i>Sim, excluir contrato
                    </button>
                    <a href="<?php echo site_url("contratos/exibir/$contrato->id") ?>" class="btn btn-secondary">
                        <i class="bx bx-x me-2"></i>Cancelar
                    </a>
                </div>
                
                <?php echo form_close(); ?>

            </div>
        </div>

    </div>

</div>


<?php echo $this->endSection() ?>




<?php echo $this->section('scripts') ?>

<?php echo $this->endSection() ?>

