<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<link href="<?php echo site_url('recursos/theme/'); ?>plugins/input-mask/css/inputmask.min.css" rel="stylesheet" />
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

<script>
var contatoIndex = <?php echo count($contatos ?? []); ?>;

$(document).ready(function() {
    // Máscaras
    $('.cpf').mask('000.000.000-00');
    $('.telefone').mask('(00) 00000-0000');

    // Adicionar contato
    $('#btnAdicionarContato').click(function() {
        var html = `
        <div class="row contato-row mb-2" data-index="${contatoIndex}">
            <div class="col-md-2">
                <select name="contatos[${contatoIndex}][tipo]" class="form-select form-select-sm">
                    <option value="agente">Agente</option>
                    <option value="empresario">Empresário</option>
                    <option value="assessoria">Assessoria</option>
                    <option value="tecnico">Técnico</option>
                    <option value="outro">Outro</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="contatos[${contatoIndex}][nome]" class="form-control form-control-sm" placeholder="Nome">
            </div>
            <div class="col-md-2">
                <input type="text" name="contatos[${contatoIndex}][telefone]" class="form-control form-control-sm telefone" placeholder="Telefone">
            </div>
            <div class="col-md-3">
                <input type="email" name="contatos[${contatoIndex}][email]" class="form-control form-control-sm" placeholder="E-mail">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remover-contato"><i class="bx bx-trash"></i></button>
            </div>
        </div>`;
        $('#container-contatos').append(html);
        contatoIndex++;
        $('.telefone').mask('(00) 00000-0000');
    });

    // Remover contato
    $(document).on('click', '.btn-remover-contato', function() {
        $(this).closest('.contato-row').remove();
    });

    // Submit
    $('#formArtista').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var btn = form.find('button[type="submit"]');
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Salvando...');
        
        $.ajax({
            url: '<?php echo site_url("artistas/atualizar"); ?>',
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
