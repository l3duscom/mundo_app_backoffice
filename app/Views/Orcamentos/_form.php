<input type="hidden" name="event_id" value="<?php echo $orcamento->event_id ?? $evento_id ?? ''; ?>">

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-control-label">Fornecedor <span class="text-danger">*</span></label>
        <select name="fornecedor_id" id="fornecedor_id" class="form-select" required>
            <option value="">Selecione o fornecedor</option>
            <?php if (isset($fornecedores)): foreach ($fornecedores as $f): ?>
                <option value="<?php echo $f->id; ?>" <?php echo (isset($orcamento->fornecedor_id) && $orcamento->fornecedor_id == $f->id) ? 'selected' : ''; ?>>
                    <?php echo esc($f->razao); ?>
                </option>
            <?php endforeach; endif; ?>
        </select>
    </div>

    <div class="col-md-8 mb-3">
        <label class="form-control-label">Título <span class="text-danger">*</span></label>
        <input type="text" name="titulo" class="form-control" placeholder="Ex: Serviço de decoração para evento" value="<?php echo esc($orcamento->titulo ?? ''); ?>" required>
    </div>

    <div class="col-12 mb-3">
        <label class="form-control-label">Descrição</label>
        <textarea name="descricao" class="form-control" rows="3" placeholder="Descrição geral do orçamento..."><?php echo esc($orcamento->descricao ?? ''); ?></textarea>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-control-label">Data de Validade</label>
        <input type="date" name="data_validade" class="form-control" value="<?php echo $orcamento->data_validade ?? ''; ?>">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-control-label">Desconto (R$)</label>
        <input type="text" name="valor_desconto" class="form-control money" placeholder="0,00" value="<?php echo isset($orcamento->valor_desconto) ? number_format($orcamento->valor_desconto, 2, ',', '.') : ''; ?>">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-control-label">Observações Internas</label>
        <input type="text" name="observacoes" class="form-control" placeholder="Obs..." value="<?php echo esc($orcamento->observacoes ?? ''); ?>">
    </div>
</div>

<!-- Pagamento -->
<div class="row mt-4">
    <div class="col-12 mb-3">
        <h6 class="text-primary"><i class="bx bx-credit-card me-2"></i>Pagamento</h6>
        <hr class="mt-0">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-control-label">Forma de Pagamento</label>
        <select name="forma_pagamento" class="form-select">
            <option value="">Selecione</option>
            <option value="PIX" <?php echo ($orcamento->forma_pagamento ?? '') === 'PIX' ? 'selected' : ''; ?>>PIX</option>
            <option value="Cartão de Crédito" <?php echo ($orcamento->forma_pagamento ?? '') === 'Cartão de Crédito' ? 'selected' : ''; ?>>Cartão de Crédito</option>
            <option value="Cartão de Débito" <?php echo ($orcamento->forma_pagamento ?? '') === 'Cartão de Débito' ? 'selected' : ''; ?>>Cartão de Débito</option>
            <option value="Boleto" <?php echo ($orcamento->forma_pagamento ?? '') === 'Boleto' ? 'selected' : ''; ?>>Boleto</option>
            <option value="Transferência" <?php echo ($orcamento->forma_pagamento ?? '') === 'Transferência' ? 'selected' : ''; ?>>Transferência</option>
            <option value="Dinheiro" <?php echo ($orcamento->forma_pagamento ?? '') === 'Dinheiro' ? 'selected' : ''; ?>>Dinheiro</option>
        </select>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-control-label">Quantidade de Parcelas</label>
        <input type="number" name="quantidade_parcelas" class="form-control" min="1" max="24" value="<?php echo $orcamento->quantidade_parcelas ?? 1; ?>">
        <small class="text-muted">Parcelas serão geradas ao aprovar o orçamento</small>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-control-label">Data 1ª Parcela</label>
        <input type="date" name="data_primeira_parcela" class="form-control" value="<?php echo date('Y-m-d'); ?>">
    </div>
</div>

<!-- Itens do Orçamento -->
<div class="col-12 mb-3 mt-4">
    <h6 class="text-primary"><i class="bx bx-list-ul me-2"></i>Itens do Orçamento</h6>
    <hr class="mt-0">
</div>

<div id="container-itens">
    <?php if (isset($itens) && count($itens) > 0): ?>
        <?php foreach ($itens as $index => $item): ?>
        <div class="row item-row mb-2" data-index="<?php echo $index; ?>">
            <div class="col-md-5">
                <input type="text" name="itens[<?php echo $index; ?>][descricao]" class="form-control form-control-sm" placeholder="Descrição do item" value="<?php echo esc($item->descricao); ?>">
            </div>
            <div class="col-md-2">
                <input type="number" name="itens[<?php echo $index; ?>][quantidade]" class="form-control form-control-sm item-qtd" placeholder="Qtd" step="0.01" value="<?php echo $item->quantidade; ?>">
            </div>
            <div class="col-md-2">
                <input type="text" name="itens[<?php echo $index; ?>][valor_unitario]" class="form-control form-control-sm money item-valor" placeholder="Valor Unit." value="<?php echo number_format($item->valor_unitario, 2, ',', '.'); ?>">
            </div>
            <div class="col-md-2">
                <input type="text" class="form-control form-control-sm item-total" readonly value="<?php echo number_format($item->valor_total, 2, ',', '.'); ?>">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-sm btn-danger btn-remover-item"><i class="bx bx-trash"></i></button>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="row item-row mb-2" data-index="0">
            <div class="col-md-5">
                <input type="text" name="itens[0][descricao]" class="form-control form-control-sm" placeholder="Descrição do item">
            </div>
            <div class="col-md-2">
                <input type="number" name="itens[0][quantidade]" class="form-control form-control-sm item-qtd" placeholder="Qtd" step="0.01" value="1">
            </div>
            <div class="col-md-2">
                <input type="text" name="itens[0][valor_unitario]" class="form-control form-control-sm money item-valor" placeholder="Valor Unit.">
            </div>
            <div class="col-md-2">
                <input type="text" class="form-control form-control-sm item-total" readonly placeholder="Total">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-sm btn-danger btn-remover-item"><i class="bx bx-trash"></i></button>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="row mb-3">
    <div class="col-12">
        <button type="button" class="btn btn-outline-primary btn-sm" id="btnAdicionarItem">
            <i class="bx bx-plus me-1"></i>Adicionar Item
        </button>
    </div>
</div>

<div class="row">
    <div class="col-md-8"></div>
    <div class="col-md-4">
        <table class="table table-sm">
            <tr>
                <td><strong>Total:</strong></td>
                <td class="text-end"><strong id="valorTotal">R$ 0,00</strong></td>
            </tr>
        </table>
    </div>
</div>
