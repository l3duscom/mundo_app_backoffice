<input type="hidden" name="id" value="<?php echo $upsell->id ?? ''; ?>">

<div class="row">
    <div class="col-md-5 mb-3">
        <label class="form-control-label">Ingresso Atual <span class="text-danger">*</span></label>
        <select name="ticket_origem_id" id="ticketOrigem" class="form-select" required>
            <option value="">Selecione o ingresso atual...</option>
            <?php foreach ($tickets as $t): ?>
            <option value="<?php echo $t->id; ?>" 
                data-preco="<?php echo $t->preco; ?>"
                <?php echo ($upsell->ticket_origem_id ?? 0) == $t->id ? 'selected' : ''; ?>>
                <?php echo esc($t->nome); ?> - R$ <?php echo number_format($t->preco, 2, ',', '.'); ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-2 mb-3 d-flex align-items-center justify-content-center">
        <i class="bx bx-right-arrow-alt fs-1 text-primary mt-4"></i>
    </div>

    <div class="col-md-5 mb-3">
        <label class="form-control-label">Upgrade Para <span class="text-danger">*</span></label>
        <select name="ticket_destino_id" id="ticketDestino" class="form-select" required>
            <option value="">Selecione o ingresso de upgrade...</option>
            <?php foreach ($tickets as $t): ?>
            <option value="<?php echo $t->id; ?>" 
                data-preco="<?php echo $t->preco; ?>"
                <?php echo ($upsell->ticket_destino_id ?? 0) == $t->id ? 'selected' : ''; ?>>
                <?php echo esc($t->nome); ?> - R$ <?php echo number_format($t->preco, 2, ',', '.'); ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<!-- Cálculo da Diferença -->
<div class="row">
    <div class="col-12">
        <div class="alert alert-info" id="alertDiferenca">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <strong><i class="bx bx-calculator me-2"></i>Diferença Calculada:</strong>
                    <span id="valorDiferenca" class="fs-4 ms-2">R$ 0,00</span>
                </div>
                <div class="col-md-6 text-end">
                    <small class="text-muted">Este valor é calculado automaticamente</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-4 mb-3">
        <label class="form-control-label">Valor Customizado (opcional)</label>
        <input type="text" name="valor_customizado" id="valorCustomizado" class="form-control money" 
            placeholder="Deixe em branco para usar a diferença"
            value="<?php echo $upsell->valor_customizado ? number_format($upsell->valor_customizado, 2, ',', '.') : ''; ?>">
        <small class="text-muted">Se preenchido, substitui o valor da diferença</small>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-control-label">Desconto sobre diferença (%)</label>
        <input type="number" name="desconto_percentual" class="form-control" min="0" max="100" step="0.01"
            placeholder="Ex: 10"
            value="<?php echo $upsell->desconto_percentual ?? ''; ?>">
        <small class="text-muted">Aplica desconto sobre o valor da diferença</small>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-control-label">Ordem de exibição</label>
        <input type="number" name="ordem" class="form-control" min="0"
            value="<?php echo $upsell->ordem ?? 0; ?>">
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-control-label">Título (opcional)</label>
        <input type="text" name="titulo" class="form-control" 
            placeholder="Ex: Upgrade para VIP"
            value="<?php echo esc($upsell->titulo ?? ''); ?>">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-control-label">Status</label>
        <select name="ativo" class="form-select">
            <option value="1" <?php echo ($upsell->ativo ?? 1) == 1 ? 'selected' : ''; ?>>Ativo</option>
            <option value="0" <?php echo ($upsell->ativo ?? 1) == 0 ? 'selected' : ''; ?>>Inativo</option>
        </select>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-3">
        <label class="form-control-label">Descrição do benefício (opcional)</label>
        <textarea name="descricao" class="form-control" rows="2" placeholder="Descreva os benefícios do upgrade..."><?php echo esc($upsell->descricao ?? ''); ?></textarea>
    </div>
</div>
