<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
    .documento-preview {
        background: white;
        padding: 40px;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        max-width: 900px;
        margin: 0 auto;
    }
    
    .documento-preview h1, 
    .documento-preview h2, 
    .documento-preview h3 {
        color: #333;
    }
    
    .documento-preview table {
        width: 100%;
        border-collapse: collapse;
        margin: 15px 0;
    }
    
    .documento-preview table th,
    .documento-preview table td {
        border: 1px solid #ddd;
        padding: 8px;
    }
    
    .documento-preview table th {
        background-color: #f5f5f5;
    }
    
    @media print {
        .no-print { display: none !important; }
        .documento-preview { 
            border: none; 
            box-shadow: none; 
            padding: 0;
        }
    }
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="no-print mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1"><?php echo esc($documento->titulo); ?></h5>
            <p class="mb-0"><?php echo $documento->getBadgeStatus(); ?></p>
        </div>
        <div>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="bx bx-printer me-2"></i>Imprimir
            </button>
            <a href="<?php echo site_url("contratodocumentos/gerenciar/{$contrato->id}"); ?>" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-2"></i>Voltar
            </a>
        </div>
    </div>
</div>

<div class="documento-preview">
    <?php echo $documento->conteudo_html; ?>
</div>

<?php if ($documento->isAssinado()): ?>
<div class="no-print mt-4">
    <div class="card">
        <div class="card-body">
            <h6 class="mb-3"><i class="bx bx-shield-quarter me-2"></i>Informações de Assinatura</h6>
            <div class="row">
                <div class="col-md-4">
                    <small class="text-muted">Assinado por:</small>
                    <p class="mb-0 fw-bold"><?php echo esc($documento->assinado_por); ?></p>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Documento:</small>
                    <p class="mb-0"><?php echo esc($documento->documento_assinante); ?></p>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Data/Hora:</small>
                    <p class="mb-0"><?php echo $documento->getDataAssinaturaFormatada(); ?></p>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <small class="text-muted">IP:</small>
                    <p class="mb-0 font-monospace"><?php echo esc($documento->ip_assinatura); ?></p>
                </div>
                <div class="col-md-6">
                    <small class="text-muted">Hash de Verificação:</small>
                    <p class="mb-0 font-monospace small"><?php echo esc($documento->hash_assinatura); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php echo $this->endSection() ?>

