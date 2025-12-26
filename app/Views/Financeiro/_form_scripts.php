<script>
var csrfToken = '<?php echo csrf_hash(); ?>';

$(document).ready(function() {
    // Máscara de valor
    $('#valor').on('input', function() {
        var value = $(this).val().replace(/\D/g, '');
        value = (parseInt(value) / 100).toFixed(2);
        value = value.replace('.', ',');
        value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        $(this).val(value);
    });

    // Auto preencher data de pagamento quando status for pago
    $('#status').on('change', function() {
        if ($(this).val() === 'pago' && !$('#data_pagamento').val()) {
            $('#data_pagamento').val($('#data_lancamento').val() || '<?php echo date("Y-m-d"); ?>');
        }
    });

    // Submit do formulário
    $('#formLancamento').on('submit', function(e) {
        e.preventDefault();
        
        var lancamentoId = $('#lancamento_id').val();
        var url = lancamentoId 
            ? '<?php echo site_url("financeiro/atualizar/"); ?>' + lancamentoId
            : '<?php echo site_url("financeiro/cadastrar"); ?>';
        
        var formData = {
            event_id: $('#event_id').val(),
            tipo: $('input[name="tipo"]:checked').val(),
            descricao: $('#descricao').val(),
            valor: $('#valor').val(),
            data_lancamento: $('#data_lancamento').val(),
            data_pagamento: $('#data_pagamento').val(),
            status: $('#status').val(),
            forma_pagamento: $('#forma_pagamento').val(),
            categoria: $('#categoria').val(),
            observacoes: $('#observacoes').val()
        };
        formData['<?php echo csrf_token(); ?>'] = csrfToken;
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.token) csrfToken = response.token;
                
                if (response.sucesso) {
                    alert(response.sucesso);
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    }
                } else if (response.erro) {
                    alert('Erro: ' + response.erro);
                }
            },
            error: function() {
                alert('Erro ao processar requisição.');
            }
        });
    });
});
</script>
