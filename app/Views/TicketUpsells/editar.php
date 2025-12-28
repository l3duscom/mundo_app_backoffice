<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">
        <a href="<?php echo site_url('/ticket-upsells'); ?>">Upsells</a>
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('/ticket-upsells'); ?>">Upsells</a></li>
                <li class="breadcrumb-item active">Editar</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="<?php echo site_url('/ticket-upsells'); ?>" class="btn btn-secondary">
            <i class="bx bx-arrow-back me-2"></i>Voltar
        </a>
    </div>
</div>

<div class="card shadow radius-10">
    <div class="card-header">
        <h5 class="mb-0"><i class="bx bx-edit me-2"></i><?php echo $titulo; ?></h5>
    </div>
    <div class="card-body">
        <form id="formUpsell" method="post" action="<?php echo site_url('ticket-upsells/atualizar'); ?>">
            <input type="hidden" name="<?php echo csrf_token(); ?>" value="<?php echo csrf_hash(); ?>">
            
            <?php echo $this->include('TicketUpsells/_form'); ?>
            
            <hr class="my-4">
            <div class="d-flex justify-content-end">
                <a href="<?php echo site_url('/ticket-upsells'); ?>" class="btn btn-light me-2">Cancelar</a>
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
$(document).ready(function() {
    $('.money').mask('#.##0,00', {reverse: true});

    // Calcula diferença ao mudar tickets
    function calcularDiferenca() {
        var origemId = $('#ticketOrigem').val();
        var destinoId = $('#ticketDestino').val();

        if (!origemId || !destinoId) {
            $('#valorDiferenca').text('R$ 0,00');
            return;
        }

        $.get('<?php echo site_url("ticket-upsells/calcularDiferenca"); ?>', {
            origem: origemId,
            destino: destinoId
        }, function(r) {
            $('#valorDiferenca').text(r.formatado);
            if (r.diferenca <= 0) {
                $('#alertDiferenca').removeClass('alert-info').addClass('alert-warning');
            } else {
                $('#alertDiferenca').removeClass('alert-warning').addClass('alert-info');
            }
        }, 'json');
    }

    $('#ticketOrigem, #ticketDestino').change(calcularDiferenca);
    
    // Calcula inicial
    calcularDiferenca();

    // Submit
    $('#formUpsell').on('submit', function(e) {
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
                
                if (response.redirect) {
                    window.location.href = response.redirect;
                }
            },
            error: function() {
                alert('Erro ao processar');
                btn.prop('disabled', false).html('<i class="bx bx-save me-2"></i>Salvar Alterações');
            }
        });
    });
});
</script>
<?php echo $this->endSection() ?>
