<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<style>
    .variaveis-list {
        max-height: 400px;
        overflow-y: auto;
    }
    .var-item {
        cursor: pointer;
        padding: 5px 10px;
        border-radius: 5px;
        margin-bottom: 5px;
        background: #f8f9fa;
        font-family: monospace;
        font-size: 0.85rem;
    }
    .var-item:hover {
        background: #e9ecef;
    }
    .note-editable {
        min-height: 400px;
    }
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted"><?php echo $titulo; ?></div>
    <div class="ms-auto">
        <a href="<?php echo site_url('contratodocumentos/modelos'); ?>" class="btn btn-secondary">
            <i class="bx bx-arrow-back me-2"></i>Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-9">
        <div class="card shadow radius-10">
            <div class="card-body">
                <form id="formModelo">
                    <?php if (!empty($modelo->id)): ?>
                    <input type="hidden" name="id" value="<?php echo $modelo->id; ?>">
                    <?php endif; ?>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Nome do Modelo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nome" value="<?php echo esc($modelo->nome ?? ''); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tipo de Item <span class="text-danger">*</span></label>
                            <select class="form-select" name="tipo_item" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($tiposItem as $tipo): ?>
                                <option value="<?php echo esc($tipo); ?>" <?php echo ($modelo->tipo_item ?? '') === $tipo ? 'selected' : ''; ?>>
                                    <?php echo esc($tipo); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Ativo</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="ativo" value="1" 
                                       <?php echo ($modelo->ativo ?? true) ? 'checked' : ''; ?>>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição</label>
                            <input type="text" class="form-control" name="descricao" value="<?php echo esc($modelo->descricao ?? ''); ?>" 
                                   placeholder="Breve descrição do modelo">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Conteúdo do Modelo <span class="text-danger">*</span></label>
                        <p class="text-muted small">Use as variáveis da lista ao lado clicando nelas para inserir no documento.</p>
                        <textarea id="conteudo_html" name="conteudo_html"><?php echo $modelo->conteudo_html ?? ''; ?></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-2"></i>Salvar Modelo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3">
        <div class="card shadow radius-10">
            <div class="card-header">
                <h6 class="mb-0"><i class="bx bx-code-alt me-2"></i>Variáveis Disponíveis</h6>
            </div>
            <div class="card-body variaveis-list">
                <p class="text-muted small">Clique para inserir no editor:</p>
                <?php foreach ($variaveis as $chave => $descricao): ?>
                <div class="var-item" data-var="{{<?php echo $chave; ?>}}" title="<?php echo esc($descricao); ?>">
                    <strong>{{<?php echo $chave; ?>}}</strong>
                    <br><small class="text-muted"><?php echo esc($descricao); ?></small>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/lang/summernote-pt-BR.min.js"></script>
<script>
$(document).ready(function() {
    var csrfToken = '<?php echo csrf_hash(); ?>';
    var csrfName = '<?php echo csrf_token(); ?>';

    $('#conteudo_html').summernote({
        lang: 'pt-BR',
        height: 400,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'italic', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'hr']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });

    // Inserir variável no editor
    $('.var-item').on('click', function() {
        var variavel = $(this).data('var');
        $('#conteudo_html').summernote('editor.insertText', variavel);
    });

    // Salvar modelo
    $('#formModelo').on('submit', function(e) {
        e.preventDefault();
        
        var btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Salvando...');
        
        var formData = $(this).serializeArray();
        formData.push({ name: 'conteudo_html', value: $('#conteudo_html').summernote('code') });
        formData.push({ name: csrfName, value: csrfToken });
        
        $.ajax({
            type: 'POST',
            url: '<?php echo site_url('contratodocumentos/salvarmodelo'); ?>',
            data: $.param(formData),
            dataType: 'json',
            success: function(response) {
                if (response.token) csrfToken = response.token;
                
                if (response.sucesso) {
                    alert(response.sucesso);
                    window.location.href = '<?php echo site_url('contratodocumentos/modelos'); ?>';
                } else {
                    alert(response.erro || 'Erro ao salvar');
                    if (response.erros_model) {
                        console.log(response.erros_model);
                    }
                }
            },
            error: function() {
                alert('Erro ao processar');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="bx bx-save me-2"></i>Salvar Modelo');
            }
        });
    });
});
</script>
<?php echo $this->endSection() ?>

