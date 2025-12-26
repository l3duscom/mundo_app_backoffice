<form id="formLancamento">
    <input type="hidden" id="lancamento_id" value="<?php echo $lancamento->id ?? ''; ?>">
    
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Tipo <span class="text-danger">*</span></label>
            <div class="d-flex gap-3">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="tipo" id="tipo_entrada" value="ENTRADA" 
                        <?php echo (!$lancamento->id || $lancamento->tipo === 'ENTRADA') ? 'checked' : ''; ?>>
                    <label class="form-check-label text-success fw-bold" for="tipo_entrada">
                        <i class="bx bx-trending-up"></i> Entrada
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="tipo" id="tipo_saida" value="SAIDA"
                        <?php echo ($lancamento->tipo === 'SAIDA') ? 'checked' : ''; ?>>
                    <label class="form-check-label text-danger fw-bold" for="tipo_saida">
                        <i class="bx bx-trending-down"></i> Saída
                    </label>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Status <span class="text-danger">*</span></label>
            <select class="form-select" name="status" id="status" required>
                <option value="pendente" <?php echo ($lancamento->status ?? 'pendente') === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                <option value="pago" <?php echo ($lancamento->status ?? '') === 'pago' ? 'selected' : ''; ?>>Pago</option>
                <option value="cancelado" <?php echo ($lancamento->status ?? '') === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
            </select>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Descrição <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="descricao" id="descricao" required 
            value="<?php echo esc($lancamento->descricao ?? ''); ?>" placeholder="Ex: Pagamento de aluguel">
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Valor <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text">R$</span>
                <input type="text" class="form-control" name="valor" id="valor" required 
                    value="<?php echo $lancamento->valor ? number_format($lancamento->valor, 2, ',', '.') : ''; ?>" 
                    placeholder="0,00">
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Evento (opcional)</label>
            <select class="form-select" name="event_id" id="event_id">
                <option value="">Nenhum evento específico</option>
                <?php foreach ($eventos as $evento): ?>
                    <option value="<?php echo $evento->id; ?>" 
                        <?php echo ($lancamento->event_id ?? '') == $evento->id ? 'selected' : ''; ?>>
                        <?php echo esc($evento->nome); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Data do Lançamento <span class="text-danger">*</span></label>
            <input type="date" class="form-control" name="data_lancamento" id="data_lancamento" required 
                value="<?php echo $lancamento->data_lancamento ? $lancamento->data_lancamento->format('Y-m-d') : date('Y-m-d'); ?>">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Data de Pagamento</label>
            <input type="date" class="form-control" name="data_pagamento" id="data_pagamento" 
                value="<?php echo $lancamento->data_pagamento ? $lancamento->data_pagamento->format('Y-m-d') : ''; ?>">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Forma de Pagamento</label>
            <select class="form-select" name="forma_pagamento" id="forma_pagamento">
                <option value="">Selecione...</option>
                <option value="PIX" <?php echo ($lancamento->forma_pagamento ?? '') === 'PIX' ? 'selected' : ''; ?>>PIX</option>
                <option value="CARTAO_CREDITO" <?php echo ($lancamento->forma_pagamento ?? '') === 'CARTAO_CREDITO' ? 'selected' : ''; ?>>Cartão de Crédito</option>
                <option value="CARTAO_DEBITO" <?php echo ($lancamento->forma_pagamento ?? '') === 'CARTAO_DEBITO' ? 'selected' : ''; ?>>Cartão de Débito</option>
                <option value="BOLETO" <?php echo ($lancamento->forma_pagamento ?? '') === 'BOLETO' ? 'selected' : ''; ?>>Boleto</option>
                <option value="DINHEIRO" <?php echo ($lancamento->forma_pagamento ?? '') === 'DINHEIRO' ? 'selected' : ''; ?>>Dinheiro</option>
                <option value="TRANSFERENCIA" <?php echo ($lancamento->forma_pagamento ?? '') === 'TRANSFERENCIA' ? 'selected' : ''; ?>>Transferência</option>
            </select>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Categoria</label>
        <input type="text" class="form-control" name="categoria" id="categoria" 
            value="<?php echo esc($lancamento->categoria ?? ''); ?>" placeholder="Ex: Fornecedores, Marketing, etc.">
    </div>

    <div class="mb-3">
        <label class="form-label">Observações</label>
        <textarea class="form-control" name="observacoes" id="observacoes" rows="3" 
            placeholder="Observações adicionais..."><?php echo esc($lancamento->observacoes ?? ''); ?></textarea>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i>Salvar
        </button>
        <a href="<?php echo site_url('financeiro'); ?>" class="btn btn-secondary">
            <i class="bx bx-arrow-back me-1"></i>Voltar
        </a>
    </div>
</form>
