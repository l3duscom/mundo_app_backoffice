<?php echo $this->extend('Layout/externo'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
.upsell-card {
    background: linear-gradient(135deg, #6c038f 0%, #9b4dca 100%);
    border-radius: 20px;
    color: white;
    box-shadow: 0 10px 40px rgba(108,3,143,0.3);
}
.upgrade-badge {
    background: #ffd700;
    color: #333;
    padding: 5px 15px;
    border-radius: 20px;
    font-weight: bold;
    display: inline-block;
}
.price-highlight {
    font-size: 48px;
    font-weight: bold;
    color: #ffd700;
}
.payment-option {
    background: rgba(255,255,255,0.1);
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 15px;
    padding: 20px;
    cursor: pointer;
    transition: all 0.3s;
}
.payment-option:hover, .payment-option.selected {
    border-color: #ffd700;
    background: rgba(255,255,255,0.2);
}
.payment-option i {
    font-size: 32px;
    margin-bottom: 10px;
}
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card upsell-card">
            <div class="card-body p-4 text-center">
                
                <div class="mb-3">
                    <span class="upgrade-badge">
                        <i class="bx bx-rocket me-1"></i>UPGRADE
                    </span>
                </div>
                
                <h3 class="mb-3">
                    <?php echo esc($upsell->titulo ?: 'Faça o upgrade do seu ingresso!'); ?>
                </h3>
                
                <div style="background: rgba(255,255,255,0.15); border-radius: 15px; padding: 20px; margin-bottom: 25px;">
                    <p class="mb-2">Seu novo ingresso:</p>
                    <h4 class="mb-2"><?php echo esc($ticket->nome); ?></h4>
                    <?php if ($ticket->descricao): ?>
                    <p class="small opacity-75 mb-0"><?php echo esc($ticket->descricao); ?></p>
                    <?php endif; ?>
                </div>
                
                <p class="opacity-75 mb-2">Valor a pagar:</p>
                <div class="price-highlight mb-3">
                    R$ <?php echo number_format($valor, 2, ',', '.'); ?>
                </div>
                
                <?php if ($upsell->temDesconto()): ?>
                <p class="mb-3">
                    <span style="background: #ff6b6b; padding: 5px 15px; border-radius: 15px; font-size: 12px;">
                        <?php echo $upsell->desconto_percentual; ?>% de desconto aplicado!
                    </span>
                </p>
                <?php endif; ?>
                
                <hr style="border-color: rgba(255,255,255,0.2);">
                
                <p class="mb-3">Escolha a forma de pagamento:</p>
                
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="payment-option" id="opt-pix" onclick="selectPayment('PIX')">
                            <i class="bx bx-qr"></i>
                            <div>PIX</div>
                            <small style="opacity: 0.7;">Pagamento instantâneo</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="payment-option" id="opt-cartao" onclick="selectPayment('CREDIT_CARD')">
                            <i class="bx bx-credit-card"></i>
                            <div>Cartão</div>
                            <small style="opacity: 0.7;">Crédito</small>
                        </div>
                    </div>
                </div>
                
                <form id="formUpsell" action="<?php echo site_url('checkout/processarUpsellPagamento'); ?>" method="post">
                    <input type="hidden" name="<?php echo csrf_token(); ?>" value="<?php echo csrf_hash(); ?>">
                    <input type="hidden" name="upsell_id" value="<?php echo $upsell->id; ?>">
                    <input type="hidden" name="forma_pagamento" id="forma_pagamento" value="PIX">
                    
                    <button type="submit" class="btn btn-lg" style="background: #ffd700; color: #333; font-weight: bold; padding: 15px 50px; border-radius: 30px;">
                        <i class="bx bx-check-circle me-2"></i>PAGAR UPGRADE
                    </button>
                </form>
                
                <div class="mt-4">
                    <a href="<?php echo site_url('checkout/obrigado'); ?>" class="text-white opacity-50" style="text-decoration: underline;">
                        Voltar sem fazer upgrade
                    </a>
                </div>
                
            </div>
        </div>
        
        <div class="text-center mt-3">
            <small class="text-muted">Pagamento seguro processado por Asaas / Pagar.me</small>
        </div>
    </div>
</div>

<script>
function selectPayment(type) {
    document.getElementById('forma_pagamento').value = type;
    document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('selected'));
    document.getElementById('opt-' + (type === 'PIX' ? 'pix' : 'cartao')).classList.add('selected');
}
// Seleciona PIX por padrão
document.getElementById('opt-pix').classList.add('selected');
</script>

<?php echo $this->endSection() ?>
