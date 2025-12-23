<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
    .obrigado-container {
        max-width: 600px;
        margin: 0 auto;
        text-align: center;
        padding: 40px 20px;
    }
    
    .sucesso-icon {
        width: 120px;
        height: 120px;
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 30px;
        animation: pulse 2s infinite;
    }
    
    .sucesso-icon i {
        font-size: 60px;
        color: white;
    }
    
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.4); }
        70% { box-shadow: 0 0 0 20px rgba(40, 167, 69, 0); }
        100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
    }
    
    .obrigado-titulo {
        font-size: 32px;
        font-weight: 700;
        color: #28a745;
        margin-bottom: 16px;
    }
    
    .obrigado-subtitulo {
        font-size: 18px;
        color: #6c757d;
        margin-bottom: 40px;
    }
    
    .pedido-info {
        background: #f8f9fa;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 30px;
        text-align: left;
    }
    
    .pedido-info .info-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #e9ecef;
    }
    
    .pedido-info .info-row:last-child {
        border-bottom: none;
    }
    
    .pedido-info .label {
        color: #6c757d;
    }
    
    .pedido-info .valor {
        font-weight: 600;
        color: #333;
    }
    
    .pedido-codigo {
        background: #28a745;
        color: white;
        padding: 16px 24px;
        border-radius: 12px;
        font-size: 24px;
        font-weight: 700;
        letter-spacing: 2px;
        margin-bottom: 30px;
        display: inline-block;
    }
    
    .btn-whatsapp {
        background: #25d366;
        color: white;
        padding: 16px 32px;
        font-size: 18px;
        font-weight: 600;
        border-radius: 12px;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 12px;
    }
    
    .btn-whatsapp:hover {
        background: #128c7e;
        color: white;
    }
    
    .btn-whatsapp i {
        font-size: 24px;
    }
    
    .acoes-container {
        display: flex;
        flex-direction: column;
        gap: 16px;
        margin-top: 30px;
    }
    
    .btn-nova-venda {
        background: #28a745;
        color: white;
        padding: 16px 32px;
        font-size: 18px;
        font-weight: 600;
        border-radius: 12px;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-nova-venda:hover {
        background: #218838;
        color: white;
    }
    
    .ingressos-lista {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 20px;
        text-align: left;
    }
    
    .ingresso-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .ingresso-item:last-child {
        border-bottom: none;
    }
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="container-fluid py-4">
    <div class="obrigado-container">
        
        <!-- Ícone de Sucesso -->
        <div class="sucesso-icon">
            <i class="bi bi-check-lg"></i>
        </div>
        
        <!-- Título -->
        <h1 class="obrigado-titulo">Venda Concluída!</h1>
        <p class="obrigado-subtitulo">Os ingressos foram gerados com sucesso</p>
        
        <!-- Código do Pedido -->
        <div class="pedido-codigo">
            <?= esc($pedido->codigo ?? 'PDV000000') ?>
        </div>
        
        <!-- Informações do Pedido -->
        <div class="pedido-info">
            <div class="info-row">
                <span class="label">Cliente</span>
                <span class="valor"><?= esc($cliente->nome ?? 'N/A') ?></span>
            </div>
            <div class="info-row">
                <span class="label">Email</span>
                <span class="valor"><?= esc($cliente->email ?? 'N/A') ?></span>
            </div>
            <div class="info-row">
                <span class="label">Evento</span>
                <span class="valor"><?= esc($evento->nome ?? 'N/A') ?></span>
            </div>
            <div class="info-row">
                <span class="label">Forma de Pagamento</span>
                <span class="valor"><?= ucfirst($pedido->forma_pagamento ?? 'N/A') ?></span>
            </div>
            <div class="info-row">
                <span class="label">Total Pago</span>
                <span class="valor" style="font-size: 20px; color: #28a745;">
                    R$ <?= number_format($pedido->total ?? 0, 2, ',', '.') ?>
                </span>
            </div>
        </div>
        
        <!-- Lista de Ingressos -->
        <div class="ingressos-lista">
            <h6 class="mb-3"><i class="bi bi-ticket-perforated me-2"></i>Ingressos Gerados</h6>
            <?php if (!empty($ingressos)) : ?>
                <?php foreach ($ingressos as $ingresso) : ?>
                    <div class="ingresso-item">
                        <span><?= esc($ingresso->codigo) ?></span>
                        <span class="text-success"><i class="bi bi-check-circle"></i></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Ações -->
        <div class="acoes-container">
            
            <!-- Botão WhatsApp -->
            <?php 
            $telefone = preg_replace('/[^0-9]/', '', $cliente->telefone ?? '');
            $mensagem = urlencode("Olá {$cliente->nome}! 🎉\n\nSua compra foi confirmada!\n\n📋 *Pedido:* {$pedido->codigo}\n🎪 *Evento:* {$evento->nome}\n💰 *Valor:* R$ " . number_format($pedido->total ?? 0, 2, ',', '.') . "\n\nAcesse seu ingresso em: https://mundodream.com.br/console/dashboard\n\nNos vemos no evento! 🚀");
            ?>
            <a href="https://wa.me/55<?= $telefone ?>?text=<?= $mensagem ?>" target="_blank" class="btn-whatsapp">
                <i class="bi bi-whatsapp"></i>
                Enviar Confirmação via WhatsApp
            </a>
            
            <!-- Nova Venda -->
            <a href="<?= site_url('pdv/dashboard') ?>" class="btn-nova-venda">
                <i class="bi bi-plus-circle me-2"></i>Nova Venda
            </a>
            
        </div>
        
        <!-- Mensagem de Email -->
        <p class="text-muted mt-4">
            <i class="bi bi-envelope-check me-1"></i>
            Um email de confirmação foi enviado para <strong><?= esc($cliente->email ?? '') ?></strong>
        </p>
        
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script>
// Limpa sessão do carrinho PDV ao carregar esta página
$(document).ready(function() {
    // Carrinho já foi limpo no controller
});
</script>
<?php echo $this->endSection() ?>
