<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-dark">
        <a href="<?php echo site_url('/email-templates'); ?>">Templates de Email</a>
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active">Templates de Email</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <span class="badge bg-primary fs-6">
            <i class="bx bx-envelope me-1"></i><?php echo $totalTemplates; ?> Templates
        </span>
    </div>
</div>

<!-- Resumo -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow radius-10 bg-gradient-cosmic">
            <div class="card-body py-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h4 class="text-white mb-1">Gerenciador de Templates de Email</h4>
                        <p class="text-white-50 mb-0">
                            Visualize e gerencie todos os modelos de email do sistema
                        </p>
                    </div>
                    <div class="text-white">
                        <i class="bx bx-mail-send" style="font-size: 4rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grid de Categorias -->
<div class="row">
    <?php foreach ($categorias as $categoriaNome => $categoria): ?>
    <div class="col-xl-4 col-lg-6 mb-4">
        <div class="card shadow radius-10 h-100">
            <div class="card-header bg-transparent border-bottom">
                <div class="d-flex align-items-center">
                    <div class="widget-icon bg-<?php echo $categoria['cor']; ?> text-white rounded-circle me-3">
                        <i class="bx <?php echo $categoria['icone']; ?>"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0"><?php echo esc($categoriaNome); ?></h6>
                        <small class="text-muted"><?php echo count($categoria['templates']); ?> templates</small>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach ($categoria['templates'] as $templateKey => $templateNome): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bx bx-file text-<?php echo $categoria['cor']; ?> me-2"></i>
                            <span><?php echo esc($templateNome); ?></span>
                        </div>
                        <a href="<?php echo site_url('email-templates/exibir/' . $categoria['pasta'] . '/' . $templateKey); ?>" 
                           class="btn btn-sm btn-outline-<?php echo $categoria['cor']; ?>"
                           title="Visualizar template">
                            <i class="bx bx-show"></i>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
.bg-gradient-cosmic {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.widget-icon {
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}
.list-group-item:hover {
    background-color: rgba(0,0,0,0.02);
}
</style>
<?php echo $this->endSection() ?>
