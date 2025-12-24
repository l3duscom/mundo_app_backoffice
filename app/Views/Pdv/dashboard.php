<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
    .evento-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
    }
    
    .evento-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        border-color: #28a745;
    }
    
    .evento-card .card-img-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
        min-height: 150px;
        background: #f5f5f5;
    }
    
    .evento-card .card-img-top {
        max-width: 200px;
        max-height: 150px;
        object-fit: contain;
        border-radius: 8px;
    }
    
    .evento-card .card-img-placeholder {
        width: 100%;
        height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 8px;
    }
    
    .evento-date {
        background: #28a745;
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
        display: inline-block;
    }
    
    .pdv-header {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border-radius: 16px;
        padding: 30px;
        margin-bottom: 30px;
    }
    
    .pdv-header h2 {
        color: white;
        margin: 0;
    }
    
    .pdv-header p {
        color: rgba(255,255,255,0.8);
        margin: 0;
    }
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="container-fluid py-4">
    
    <!-- Header PDV -->
    <div class="pdv-header">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h2><i class="bi bi-shop me-3"></i>Ponto de Venda</h2>
                <p class="mt-2 mb-0">Selecione o evento para iniciar uma venda</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= site_url('pdv/minhasVendas') ?>" class="btn btn-outline-light btn-lg">
                    <i class="bi bi-receipt me-2"></i>Minhas Vendas
                </a>
                <a href="<?= site_url('console/dashboard') ?>" class="btn btn-light btn-lg">
                    <i class="bi bi-arrow-left me-2"></i>Voltar
                </a>
            </div>
        </div>
    </div>

    <!-- Grid de Eventos -->
    <div class="row g-4">
        <?php if (empty($eventos)) : ?>
            <div class="col-12">
                <div class="alert alert-warning text-center">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Nenhum evento ativo disponível para venda.
                </div>
            </div>
        <?php else : ?>
            <?php foreach ($eventos as $evento) : ?>
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <a href="<?= site_url("pdv/vender/{$evento->id}") ?>" class="text-decoration-none">
                        <div class="card evento-card h-100">
                            <div class="card-img-wrapper">
                                <?php if (!empty($evento->avatar)) : ?>
                                    <img src="https://backoffice.mundodream.com.br/eventos/imagem/<?= $evento->avatar ?>" class="card-img-top" alt="<?= esc($evento->nome) ?>">
                                <?php else : ?>
                                    <div class="card-img-placeholder">
                                        <i class="bi bi-calendar-event text-white" style="font-size: 4rem; opacity: 0.5;"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title text-dark mb-2"><?= esc($evento->nome) ?></h5>
                                <p class="text-muted small mb-3">
                                    <i class="bi bi-geo-alt me-1"></i><?= esc($evento->local) ?>
                                </p>
                                <span class="evento-date">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    <?= date('d/m', strtotime($evento->data_inicio)) ?>
                                    <?php if ($evento->data_inicio != $evento->data_fim) : ?>
                                        - <?= date('d/m', strtotime($evento->data_fim)) ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<?php echo $this->endSection() ?>
