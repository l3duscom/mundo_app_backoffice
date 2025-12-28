<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">
        <a href="<?php echo site_url('/artistas'); ?>">Artistas</a>
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('/artistas'); ?>">Artistas</a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="<?php echo site_url("artistas/exibir/{$artista->id}"); ?>" class="btn btn-secondary">
            <i class="bx bx-arrow-back me-2"></i>Voltar
        </a>
    </div>
</div>

<div class="card shadow radius-10">
    <div class="card-header">
        <h5 class="mb-0"><i class="bx bx-edit me-2"></i><?php echo $titulo; ?></h5>
    </div>
    <div class="card-body">
        <form id="formArtista" method="post" action="<?php echo site_url('artistas/atualizar'); ?>">
            <input type="hidden" name="<?php echo csrf_token(); ?>" value="<?php echo csrf_hash(); ?>">
            
            <?php echo $this->include('Artistas/_form'); ?>
            
            <hr class="my-4">
            <div class="d-flex justify-content-end">
                <a href="<?php echo site_url("artistas/exibir/{$artista->id}"); ?>" class="btn btn-light me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bx bx-save me-2"></i>Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
var agenteIndex = <?php echo count($agentesVinculados ?? []); ?>;

$(document).ready(function() {
    // Máscaras
    $('.cpf').mask('000.000.000-00');
    $('.telefone').mask('(00) 00000-0000');

    // Select2 para buscar agentes
    $('#selectAgente').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#modalAdicionarAgente'),
        placeholder: 'Digite para buscar...',
        allowClear: true,
        ajax: {
            url: '<?php echo site_url("agentes/pesquisar"); ?>',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { q: params.term };
            },
            processResults: function(data) {
                return { results: data.results };
            },
            cache: true
        },
        minimumInputLength: 1
    });

    // Abrir modal para vincular agente
    $('#btnAdicionarAgente').click(function() {
        $('#selectAgente').val(null).trigger('change');
        $('#selectFuncao').val('agente');
        $('#checkPrincipal').prop('checked', false);
        $('#modalAdicionarAgente').modal('show');
    });

    // Confirmar vinculação
    $('#btnConfirmarAgente').click(function() {
        var agenteData = $('#selectAgente').select2('data')[0];
        if (!agenteData) {
            alert('Selecione um agente');
            return;
        }

        // Verificar se já existe
        if ($('#container-agentes').find('[data-agente-id="' + agenteData.id + '"]').length) {
            alert('Este agente já está vinculado');
            return;
        }

        var funcao = $('#selectFuncao').val();
        var principal = $('#checkPrincipal').is(':checked');

        var html = `
        <div class="row agente-row mb-2 align-items-center" data-agente-id="${agenteData.id}">
            <div class="col-md-4">
                <input type="hidden" name="agentes[${agenteIndex}][agente_id]" value="${agenteData.id}">
                <strong>${agenteData.text}</strong>
            </div>
            <div class="col-md-3">
                <select name="agentes[${agenteIndex}][funcao]" class="form-select form-select-sm">
                    <option value="agente" ${funcao === 'agente' ? 'selected' : ''}>Agente</option>
                    <option value="empresario" ${funcao === 'empresario' ? 'selected' : ''}>Empresário</option>
                    <option value="assessoria" ${funcao === 'assessoria' ? 'selected' : ''}>Assessoria</option>
                    <option value="produtor" ${funcao === 'produtor' ? 'selected' : ''}>Produtor</option>
                    <option value="tecnico" ${funcao === 'tecnico' ? 'selected' : ''}>Técnico</option>
                    <option value="outro" ${funcao === 'outro' ? 'selected' : ''}>Outro</option>
                </select>
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input type="checkbox" name="agentes[${agenteIndex}][principal]" value="1" class="form-check-input" ${principal ? 'checked' : ''}>
                    <label class="form-check-label small">Principal</label>
                </div>
            </div>
            <div class="col-md-2">
                <a href="<?php echo site_url('agentes/exibir'); ?>/${agenteData.id}" class="btn btn-sm btn-outline-secondary" target="_blank" title="Ver Agente">
                    <i class="bx bx-link-external"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remover-agente"><i class="bx bx-trash"></i></button>
            </div>
        </div>`;

        $('#container-agentes').append(html);
        agenteIndex++;
        $('#modalAdicionarAgente').modal('hide');
    });

    // Remover agente
    $(document).on('click', '.btn-remover-agente', function() {
        $(this).closest('.agente-row').remove();
    });

    // Submit
    $('#formArtista').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var btn = form.find('button[type="submit"]');
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Salvando...');
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                $('[name="<?php echo csrf_token(); ?>"]').val(response.token);
                
                if (response.erro) {
                    alert(response.erro);
                    btn.prop('disabled', false).html('<i class="bx bx-save me-2"></i>Salvar Alterações');
                    return;
                }
                
                if (response.sucesso) {
                    alert(response.sucesso);
                }
                
                if (response.redirect) {
                    window.location.href = response.redirect;
                }
            },
            error: function(xhr, status, error) {
                console.log('Erro:', xhr.responseText);
                alert('Erro ao processar a requisição: ' + error);
                btn.prop('disabled', false).html('<i class="bx bx-save me-2"></i>Salvar Alterações');
            }
        });
    });
});
</script>
<?php echo $this->endSection() ?>
