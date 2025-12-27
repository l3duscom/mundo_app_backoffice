<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">
        <a href="<?php echo site_url('/orcamentos'); ?>">Orçamentos</a>
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('/orcamentos'); ?>">Orçamentos</a></li>
                <li class="breadcrumb-item active" aria-current="page">Novo</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<div class="card">
    <div class="card-body">
        <h5 class="card-title mb-4"><i class="bx bx-file-blank me-2"></i>Novo Orçamento</h5>
        
        <?php echo form_open('/', ['id' => 'formOrcamento', 'class' => 'row'], ['id' => $orcamento->id]); ?>
            
            <?php echo $this->include('Orcamentos/_form'); ?>

            <div class="col-12 mt-4">
                <hr>
                <button type="submit" class="btn btn-primary">
                    <i class="bx bx-save me-2"></i>Salvar Orçamento
                </button>
                <a href="<?php echo site_url('orcamentos'); ?>" class="btn btn-secondary ms-2">
                    <i class="bx bx-arrow-back me-2"></i>Voltar
                </a>
            </div>

        <?php echo form_close(); ?>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script src="<?php echo site_url('recursos/theme/'); ?>plugins/input-mask/jquery.mask.min.js"></script>

<script>
var itemIndex = <?php echo isset($itens) ? count($itens) : 1; ?>;

$(document).ready(function() {
    // Aplicar máscaras
    function aplicarMascaras() {
        $('.money').mask('#.##0,00', {reverse: true});
    }

    // Calcular total geral
    function calcularTotal() {
        var total = 0;
        $('.item-row').each(function() {
            var qtd = parseFloat($(this).find('.item-qtd').val()) || 0;
            var valorStr = $(this).find('.item-valor').val() || '0';
            var valor = parseFloat(valorStr.replace(/\./g, '').replace(',', '.')) || 0;
            total += qtd * valor;
        });
        $('#valorTotal').text('R$ ' + total.toLocaleString('pt-BR', {minimumFractionDigits: 2}));
    }

    // Calcular total do item
    function calcularTotalItem(row) {
        var qtd = parseFloat(row.find('.item-qtd').val()) || 0;
        var valorStr = row.find('.item-valor').val() || '0';
        var valor = parseFloat(valorStr.replace(/\./g, '').replace(',', '.')) || 0;
        var total = qtd * valor;
        row.find('.item-total').val(total.toLocaleString('pt-BR', {minimumFractionDigits: 2}));
    }

    // Adicionar item
    $('#btnAdicionarItem').click(function() {
        var html = `
        <div class="row item-row mb-2" data-index="${itemIndex}">
            <div class="col-md-5">
                <input type="text" name="itens[${itemIndex}][descricao]" class="form-control form-control-sm" placeholder="Descrição do item">
            </div>
            <div class="col-md-2">
                <input type="number" name="itens[${itemIndex}][quantidade]" class="form-control form-control-sm item-qtd" placeholder="Qtd" step="0.01" value="1">
            </div>
            <div class="col-md-2">
                <input type="text" name="itens[${itemIndex}][valor_unitario]" class="form-control form-control-sm money item-valor" placeholder="0,00">
            </div>
            <div class="col-md-2">
                <input type="text" class="form-control form-control-sm item-total" readonly placeholder="0,00">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-sm btn-danger btn-remover-item"><i class="bx bx-trash"></i></button>
            </div>
        </div>`;
        $('#container-itens').append(html);
        itemIndex++;
        aplicarMascaras();
    });

    // Remover item
    $(document).on('click', '.btn-remover-item', function() {
        if ($('.item-row').length > 1) {
            $(this).closest('.item-row').remove();
            calcularTotal();
        }
    });

    // Calcular ao digitar
    $(document).on('input', '.item-qtd', function() {
        var row = $(this).closest('.item-row');
        calcularTotalItem(row);
        calcularTotal();
    });

    $(document).on('keyup', '.item-valor', function() {
        var row = $(this).closest('.item-row');
        calcularTotalItem(row);
        calcularTotal();
    });

    // Envio do formulário
    $('#formOrcamento').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var btn = form.find('button[type="submit"]');
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Salvando...');
        
        $.ajax({
            url: '<?php echo site_url("orcamentos/cadastrar"); ?>',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                $('[name="csrf_test_name"]').val(response.token);
                
                if (response.erro) {
                    alert(response.erro);
                    btn.prop('disabled', false).html('<i class="bx bx-save me-2"></i>Salvar Orçamento');
                    return;
                }
                
                if (response.redirect) {
                    window.location.href = response.redirect;
                }
            },
            error: function() {
                alert('Erro ao processar a requisição');
                btn.prop('disabled', false).html('<i class="bx bx-save me-2"></i>Salvar Orçamento');
            }
        });
    });

    // Inicializar
    aplicarMascaras();
    calcularTotal();
});
</script>

<?php echo $this->endSection() ?>
