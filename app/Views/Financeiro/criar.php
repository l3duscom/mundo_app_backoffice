<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
    .form-card {
        max-width: 800px;
        margin: 0 auto;
    }
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Financeiro</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('financeiro'); ?>">Financeiro</a></li>
                <li class="breadcrumb-item active" aria-current="page">Novo Lançamento</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<div class="form-card">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bx bx-plus-circle me-2"></i>Novo Lançamento Manual</h5>
        </div>
        <div class="card-body">
            <?php echo $this->include('Financeiro/_form'); ?>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<?php echo $this->include('Financeiro/_form_scripts'); ?>
<?php echo $this->endSection() ?>
