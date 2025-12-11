<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<style>
    .note-editor {
        border-radius: 8px;
    }
    .note-editable {
        background: white;
        min-height: 500px;
    }
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Editar Documento</div>
    <div class="ms-auto">
        <a href="<?php echo site_url("contratodocumentos/gerenciar/{$contrato->id}"); ?>" class="btn btn-secondary">
            <i class="bx bx-arrow-back me-2"></i>Voltar
        </a>
    </div>
</div>

<div class="card shadow radius-10">
    <div class="card-header">
        <h6 class="mb-0"><i class="bx bx-edit me-2"></i><?php echo esc($documento->titulo); ?></h6>
    </div>
    <div class="card-body">
        <form id="formEditarDocumento">
            <input type="hidden" name="id" value="<?php echo $documento->id; ?>">
            
            <div class="mb-3">
                <label class="form-label">Conteúdo do Documento:</label>
                <textarea id="conteudo_html" name="conteudo_html"><?php echo $documento->conteudo_html; ?></textarea>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="<?php echo site_url("contratodocumentos/visualizar/{$documento->id}"); ?>" class="btn btn-outline-secondary" target="_blank">
                    <i class="bx bx-show me-2"></i>Pré-visualizar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bx bx-save me-2"></i>Salvar Alterações
                </button>
            </div>
        </form>
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
        height: 500,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });

    $('#formEditarDocumento').on('submit', function(e) {
        e.preventDefault();
        
        var btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Salvando...');
        
        var formData = {
            id: $('input[name="id"]').val(),
            conteudo_html: $('#conteudo_html').summernote('code'),
            [csrfName]: csrfToken
        };
        
        $.ajax({
            type: 'POST',
            url: '<?php echo site_url('contratodocumentos/salvar'); ?>',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.token) csrfToken = response.token;
                
                if (response.sucesso) {
                    alert(response.sucesso);
                } else {
                    alert(response.erro || 'Erro ao salvar');
                }
            },
            error: function() {
                alert('Erro ao processar');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="bx bx-save me-2"></i>Salvar Alterações');
            }
        });
    });
});
</script>
<?php echo $this->endSection() ?>

