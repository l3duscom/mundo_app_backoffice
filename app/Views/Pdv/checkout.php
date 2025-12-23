<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
    .checkout-container {
        max-width: 900px;
        margin: 0 auto;
    }
    
    .checkout-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        margin-bottom: 24px;
        overflow: hidden;
    }
    
    .checkout-card-header {
        background: #f8f9fa;
        padding: 20px 24px;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .checkout-card-header .step {
        width: 32px;
        height: 32px;
        background: #28a745;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
    }
    
    .checkout-card-header h5 {
        margin: 0;
        font-weight: 600;
    }
    
    .checkout-card-body {
        padding: 24px;
    }
    
    /* Campos de formulário grandes para touch */
    .form-control-lg {
        padding: 16px 20px;
        font-size: 16px;
        border-radius: 12px;
    }
    
    .form-label {
        font-weight: 600;
        margin-bottom: 8px;
    }
    
    /* Opções de entrega */
    .entrega-option {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 20px;
        cursor: pointer;
        transition: all 0.2s;
        margin-bottom: 12px;
    }
    
    .entrega-option:hover {
        border-color: #28a745;
        background: #f8fff9;
    }
    
    .entrega-option.selected {
        border-color: #28a745;
        background: #e8f5e9;
    }
    
    .entrega-option .form-check-input {
        width: 24px;
        height: 24px;
    }
    
    .entrega-option .entrega-label {
        font-weight: 600;
        font-size: 16px;
    }
    
    .entrega-option .entrega-preco {
        color: #28a745;
        font-weight: 700;
    }
    
    /* Opções de pagamento */
    .pagamento-options {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }
    
    .pagamento-option {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 24px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .pagamento-option:hover {
        border-color: #28a745;
        background: #f8fff9;
    }
    
    .pagamento-option.selected {
        border-color: #28a745;
        background: #e8f5e9;
    }
    
    .pagamento-option i {
        font-size: 36px;
        margin-bottom: 12px;
        display: block;
    }
    
    .pagamento-option .label {
        font-weight: 600;
        font-size: 16px;
    }
    
    /* Resumo do pedido */
    .resumo-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .resumo-item:last-child {
        border-bottom: none;
    }
    
    .resumo-total {
        display: flex;
        justify-content: space-between;
        padding: 16px 0;
        font-size: 20px;
        font-weight: 700;
        border-top: 2px solid #333;
        margin-top: 16px;
    }
    
    /* Botão grande */
    .btn-finalizar {
        width: 100%;
        padding: 20px;
        font-size: 20px;
        font-weight: 700;
        border-radius: 12px;
        background: #28a745;
        border: none;
        color: white;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-finalizar:hover {
        background: #218838;
    }
    
    /* Modal PIX */
    .pix-container {
        text-align: center;
        padding: 30px;
    }
    
    .pix-qrcode {
        max-width: 300px;
        margin: 20px auto;
        padding: 20px;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    
    .pix-qrcode img {
        width: 100%;
    }
    
    .pix-valor {
        font-size: 32px;
        font-weight: 700;
        color: #28a745;
        margin: 20px 0;
    }
    
    .pix-aguardando {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        color: #ffc107;
        font-weight: 600;
        margin-top: 20px;
    }
    
    @media (max-width: 768px) {
        .pagamento-options {
            grid-template-columns: 1fr;
        }
    }
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="container-fluid py-4">
    <div class="checkout-container">
        
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1"><i class="bi bi-cart-check me-2 text-success"></i>Finalizar Venda</h3>
                <span class="text-muted"><?= esc($evento->nome ?? 'Evento') ?></span>
            </div>
            <a href="<?= site_url("pdv/vender/{$evento->id}") ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>
        </div>

        <form id="formCheckout">
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" id="csrfToken">
            <input type="hidden" name="event_id" value="<?= $evento->id ?>">
            
            <!-- 1. Dados do Cliente -->
            <div class="checkout-card">
                <div class="checkout-card-header">
                    <span class="step">1</span>
                    <h5>Dados do Cliente</h5>
                </div>
                <div class="checkout-card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nome Completo</label>
                            <input type="text" class="form-control form-control-lg" name="nome" required 
                                   placeholder="Digite o nome do cliente">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">E-mail</label>
                            <input type="email" class="form-control form-control-lg" name="email" required
                                   placeholder="email@exemplo.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Telefone/WhatsApp</label>
                            <input type="tel" class="form-control form-control-lg" name="telefone" required
                                   placeholder="(51) 99999-9999">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CPF</label>
                            <input type="text" class="form-control form-control-lg" name="cpf" required
                                   placeholder="000.000.000-00">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Forma de Entrega -->
            <div class="checkout-card">
                <div class="checkout-card-header">
                    <span class="step">2</span>
                    <h5>Forma de Entrega do Kit</h5>
                </div>
                <div class="checkout-card-body">
                    <div class="entrega-option selected" onclick="selecionarEntrega('local', this)">
                        <div class="form-check d-flex align-items-center gap-3">
                            <input class="form-check-input" type="radio" name="entrega" value="local" id="entregaLocal" checked>
                            <label class="form-check-label flex-grow-1" for="entregaLocal">
                                <span class="entrega-label"><i class="bi bi-geo-alt me-2"></i>Retira no Local</span>
                                <p class="text-muted mb-0 mt-1">O cliente retira o kit na entrada do evento</p>
                            </label>
                            <span class="entrega-preco">Grátis</span>
                        </div>
                    </div>
                    
                    <div class="entrega-option" onclick="selecionarEntrega('casa', this)">
                        <div class="form-check d-flex align-items-center gap-3">
                            <input class="form-check-input" type="radio" name="entrega" value="casa" id="entregaCasa">
                            <label class="form-check-label flex-grow-1" for="entregaCasa">
                                <span class="entrega-label"><i class="bi bi-truck me-2"></i>Recebe em Casa</span>
                                <p class="text-muted mb-0 mt-1">Enviamos o kit para o endereço do cliente</p>
                            </label>
                            <span class="entrega-preco">+ R$ 25,00</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Forma de Pagamento -->
            <div class="checkout-card">
                <div class="checkout-card-header">
                    <span class="step">3</span>
                    <h5>Forma de Pagamento</h5>
                </div>
                <div class="checkout-card-body">
                    <div class="pagamento-options">
                        <div class="pagamento-option selected" onclick="selecionarPagamento('pix', this)">
                            <i class="bi bi-qr-code text-success"></i>
                            <span class="label">PIX</span>
                            <input type="radio" name="forma_pagamento" value="pix" checked hidden>
                        </div>
                        <div class="pagamento-option" onclick="selecionarPagamento('dinheiro', this)">
                            <i class="bi bi-cash-coin text-warning"></i>
                            <span class="label">Dinheiro</span>
                            <input type="radio" name="forma_pagamento" value="dinheiro" hidden>
                        </div>
                        <div class="pagamento-option" onclick="selecionarPagamento('cartao', this)">
                            <i class="bi bi-credit-card text-primary"></i>
                            <span class="label">Cartão</span>
                            <input type="radio" name="forma_pagamento" value="cartao" hidden>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Resumo -->
            <div class="checkout-card">
                <div class="checkout-card-header">
                    <span class="step">4</span>
                    <h5>Resumo do Pedido</h5>
                </div>
                <div class="checkout-card-body">
                    <?php foreach ($carrinho as $item) : ?>
                        <div class="resumo-item">
                            <span><?= $item['quantidade'] ?>x <?= esc($item['nome']) ?></span>
                            <span>R$ <?= number_format($item['total'] * $item['quantidade'], 2, ',', '.') ?></span>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="resumo-item" id="taxaEntregaRow" style="display: none;">
                        <span>Taxa de Entrega</span>
                        <span>R$ 25,00</span>
                    </div>
                    
                    <div class="resumo-total">
                        <span>Total</span>
                        <span id="totalFinal">R$ <?= number_format($total, 2, ',', '.') ?></span>
                    </div>
                </div>
            </div>

            <!-- Botão Finalizar -->
            <button type="submit" class="btn-finalizar">
                <i class="bi bi-check-circle me-2"></i>Finalizar Venda
            </button>

        </form>
    </div>
</div>

<!-- Modal PIX -->
<div class="modal fade" id="modalPix" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body pix-container">
                <h4><i class="bi bi-qr-code me-2"></i>Pagamento PIX</h4>
                
                <div class="pix-qrcode">
                    <img id="pixQrcode" src="" alt="QR Code PIX">
                </div>
                
                <div class="pix-valor">
                    <span id="pixValor">R$ 0,00</span>
                </div>
                
                <div class="pix-aguardando">
                    <div class="spinner-border spinner-border-sm" role="status"></div>
                    <span>Aguardando pagamento...</span>
                </div>
                
                <div class="mt-4">
                    <button class="btn btn-outline-secondary" onclick="copiarPix()">
                        <i class="bi bi-clipboard me-2"></i>Copiar código PIX
                    </button>
                </div>
                
                <input type="hidden" id="pixCopiaCola" value="">
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmação Manual -->
<div class="modal fade" id="modalConfirmar" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-5">
                <i class="bi bi-question-circle text-warning" style="font-size: 64px;"></i>
                <h4 class="mt-3">Confirmar Pagamento?</h4>
                <p class="text-muted">O pagamento foi recebido em <strong id="tipoPagamento">dinheiro</strong>?</p>
                <div class="d-flex gap-3 justify-content-center mt-4">
                    <button class="btn btn-outline-secondary btn-lg" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-success btn-lg" onclick="confirmarPagamentoManual()">
                        <i class="bi bi-check-lg me-2"></i>Sim, Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script>
let totalBase = <?= $total ?>;
let taxaEntrega = 0;
let pedidoId = null;
let csrfToken = '<?= csrf_hash() ?>';
const csrfName = '<?= csrf_token() ?>';

function selecionarEntrega(tipo, element) {
    document.querySelectorAll('.entrega-option').forEach(e => e.classList.remove('selected'));
    element.classList.add('selected');
    element.querySelector('input[type="radio"]').checked = true;
    
    if (tipo === 'casa') {
        taxaEntrega = 25;
        $('#taxaEntregaRow').show();
    } else {
        taxaEntrega = 0;
        $('#taxaEntregaRow').hide();
    }
    
    atualizarTotal();
}

function selecionarPagamento(tipo, element) {
    document.querySelectorAll('.pagamento-option').forEach(e => e.classList.remove('selected'));
    element.classList.add('selected');
    element.querySelector('input[type="radio"]').checked = true;
}

function atualizarTotal() {
    const total = totalBase + taxaEntrega;
    $('#totalFinal').text('R$ ' + total.toFixed(2).replace('.', ','));
}

$('#formCheckout').on('submit', function(e) {
    e.preventDefault();
    
    const formData = $(this).serialize();
    const formaPagamento = $('input[name="forma_pagamento"]:checked').val();
    
    $.ajax({
        url: '<?= site_url('pdv/processarVenda') ?>',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.erro) {
                alert(response.erro);
                return;
            }
            
            pedidoId = response.pedido_id;
            
            if (response.forma_pagamento === 'pix') {
                // Mostra modal PIX
                $('#pixQrcode').attr('src', response.pix.qrcode);
                $('#pixValor').text('R$ ' + response.pix.valor.toFixed(2).replace('.', ','));
                $('#pixCopiaCola').val(response.pix.qrcode_text);
                new bootstrap.Modal($('#modalPix')).show();
                
                // Inicia polling para verificar pagamento
                verificarPagamentoPix();
                
            } else {
                // Mostra modal de confirmação manual
                $('#tipoPagamento').text(formaPagamento === 'dinheiro' ? 'dinheiro' : 'cartão');
                new bootstrap.Modal($('#modalConfirmar')).show();
            }
        },
        error: function() {
            alert('Erro ao processar venda. Tente novamente.');
        }
    });
});

function confirmarPagamentoManual() {
    let data = { pedido_id: pedidoId };
    data[csrfName] = csrfToken;
    
    $.ajax({
        url: '<?= site_url('pdv/confirmarPagamento') ?>',
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            if (response.token) {
                csrfToken = response.token;
            }
            if (response.sucesso) {
                window.location.href = response.redirect;
            }
        }
    });
}

function verificarPagamentoPix() {
    // Polling a cada 3 segundos
    setInterval(function() {
        let data = { pedido_id: pedidoId };
        data[csrfName] = csrfToken;
        
        $.ajax({
            url: '<?= site_url('pdv/verificarPagamento') ?>',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.token) {
                    csrfToken = response.token;
                }
                if (response.pago) {
                    window.location.href = '<?= site_url('pdv/obrigado') ?>/' + pedidoId;
                }
            }
        });
    }, 3000);
}

function copiarPix() {
    const texto = $('#pixCopiaCola').val();
    navigator.clipboard.writeText(texto).then(() => {
        alert('Código PIX copiado!');
    });
}
</script>
<?php echo $this->endSection() ?>
