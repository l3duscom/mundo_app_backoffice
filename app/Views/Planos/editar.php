<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?><?php echo $titulo; ?><?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">
        <a href="<?php echo site_url('/planos'); ?>">Planos de Assinatura</a>
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('/planos'); ?>">Planos</a></li>
                <li class="breadcrumb-item active">Editar</li>
            </ol>
        </nav>
    </div>
</div>

<?php if (session()->has('errors_model')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>Erro ao salvar:</strong>
    <ul class="mb-0">
        <?php foreach (session('errors_model') as $erro): ?>
        <li><?php echo $erro; ?></li>
        <?php endforeach; ?>
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card shadow radius-10">
    <div class="card-header bg-transparent">
        <h6 class="mb-0"><i class="bx bx-edit me-2"></i>Editar Plano: <?php echo esc($plano->nome); ?></h6>
    </div>
    <div class="card-body">
        <form action="<?php echo site_url('planos/atualizar'); ?>" method="post">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="id" value="<?php echo $plano->id; ?>">
            
            <?php echo $this->include('Planos/_form'); ?>

            <hr>
            <div class="d-flex justify-content-end gap-2">
                <a href="<?php echo site_url('planos'); ?>" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i>Voltar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bx bx-save me-1"></i>Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
$(document).ready(function() {
    // Máscara para valor monetário
    $('.money').mask('#.##0,00', {reverse: true});

    // Atualizar preview em tempo real
    $('#nome').on('input', function() {
        $('#preview-nome').text($(this).val() || 'Nome do Plano');
    });

    $('#preco').on('input', function() {
        $('#preview-preco span').text($(this).val() || '0,00');
    });

    $('#ciclo').on('change', function() {
        $('#preview-ciclo').text($(this).val() === 'YEARLY' ? '/ano' : '/mês');
    });

    $('#beneficios').on('input', function() {
        var beneficios = $(this).val().split('\n').filter(b => b.trim());
        var html = '';
        beneficios.forEach(function(b) {
            html += '<li><i class="bx bx-check text-success me-1"></i>' + b.trim() + '</li>';
        });
        $('#preview-beneficios').html(html || '<li class="text-muted">Nenhum benefício adicionado</li>');
    });
});
</script>
<?php echo $this->endSection() ?>
