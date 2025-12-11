<div class="row">

    <!-- Dados do Contrato -->
    <div class="col-12 mb-3">
        <h6 class="text-primary"><i class="bx bx-file me-2"></i>Dados do Contrato</h6>
    </div>

    <div class="form-group col-md-3">
        <label class="form-control-label">Código do Contrato</label>
        <input type="text" name="codigo" class="form-control" value="<?php echo esc($contrato->codigo); ?>" readonly>
    </div>

    <div class="form-group col-md-5">
        <label class="form-control-label">Expositor <span class="text-danger">*</span></label>
        <select name="expositor_id" class="form-select" required>
            <option value="">Selecione o expositor</option>
            <?php foreach ($expositores as $exp): ?>
                <option value="<?php echo $exp->id; ?>" <?php echo ($contrato->expositor_id ?? '') == $exp->id ? 'selected' : ''; ?>>
                    <?php echo esc($exp->nome_fantasia ?: $exp->nome); ?> - <?php echo esc($exp->getDocumentoFormatado()); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group col-md-4">
        <label class="form-control-label">Evento <span class="text-danger">*</span></label>
        <select name="event_id" class="form-select" required>
            <option value="">Selecione o evento</option>
            <?php foreach ($eventos as $ev): ?>
                <option value="<?php echo $ev->id; ?>" <?php echo ($contrato->event_id ?? '') == $ev->id ? 'selected' : ''; ?>>
                    <?php echo esc($ev->nome); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group col-md-12">
        <label class="form-control-label">Descrição</label>
        <input type="text" name="descricao" placeholder="Descrição geral do contrato" class="form-control" value="<?php echo esc($contrato->descricao); ?>">
    </div>

    <hr class="my-4">

    <!-- Situação -->
    <div class="col-12 mb-3">
        <h6 class="text-primary"><i class="bx bx-check-circle me-2"></i>Situação do Contrato</h6>
    </div>

    <div class="form-group col-md-4">
        <label class="form-control-label">Situação</label>
        <select name="situacao" class="form-select">
            <option value="proposta" <?php echo ($contrato->situacao ?? 'proposta') === 'proposta' ? 'selected' : ''; ?>>Proposta</option>
            <option value="proposta_aceita" <?php echo ($contrato->situacao ?? '') === 'proposta_aceita' ? 'selected' : ''; ?>>Proposta Aceita</option>
            <option value="contrato_assinado" <?php echo ($contrato->situacao ?? '') === 'contrato_assinado' ? 'selected' : ''; ?>>Contrato Assinado</option>
            <option value="pagamento_aberto" <?php echo ($contrato->situacao ?? '') === 'pagamento_aberto' ? 'selected' : ''; ?>>Pagamento em Aberto</option>
            <option value="pagamento_andamento" <?php echo ($contrato->situacao ?? '') === 'pagamento_andamento' ? 'selected' : ''; ?>>Pagamento em Andamento</option>
            <option value="pagamento_confirmado" <?php echo ($contrato->situacao ?? '') === 'pagamento_confirmado' ? 'selected' : ''; ?>>Pagamento Confirmado</option>
            <option value="cancelado" <?php echo ($contrato->situacao ?? '') === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
            <option value="banido" <?php echo ($contrato->situacao ?? '') === 'banido' ? 'selected' : ''; ?>>Banido</option>
        </select>
    </div>

    <div class="form-group col-md-4">
        <label class="form-control-label">Data da Proposta</label>
        <input type="date" name="data_proposta" class="form-control" value="<?php echo esc($contrato->data_proposta); ?>">
    </div>

    <div class="form-group col-md-4">
        <label class="form-control-label">Data de Vencimento</label>
        <input type="date" name="data_vencimento" class="form-control" value="<?php echo esc($contrato->data_vencimento); ?>">
    </div>

    <hr class="my-4">

    <!-- Valores -->
    <div class="col-12 mb-3">
        <h6 class="text-primary"><i class="bx bx-money me-2"></i>Valores e Pagamento</h6>
        <small class="text-muted">Os valores originais e descontos são calculados automaticamente baseados nos itens do contrato. Após criar o contrato, adicione os itens para atualizar os valores.</small>
    </div>

    <div class="form-group col-md-3">
        <label class="form-control-label">Quantidade de Parcelas</label>
        <input type="number" name="quantidade_parcelas" min="1" max="24" class="form-control" value="<?php echo esc($contrato->quantidade_parcelas ?? 1); ?>">
    </div>

    <div class="form-group col-md-3">
        <label class="form-control-label">Forma de Pagamento</label>
        <select name="forma_pagamento" class="form-select">
            <option value="">Selecione</option>
            <option value="PIX" <?php echo ($contrato->forma_pagamento ?? '') === 'PIX' ? 'selected' : ''; ?>>PIX</option>
            <option value="Cartão de Crédito" <?php echo ($contrato->forma_pagamento ?? '') === 'Cartão de Crédito' ? 'selected' : ''; ?>>Cartão de Crédito</option>
            <option value="Cartão de Débito" <?php echo ($contrato->forma_pagamento ?? '') === 'Cartão de Débito' ? 'selected' : ''; ?>>Cartão de Débito</option>
            <option value="Boleto" <?php echo ($contrato->forma_pagamento ?? '') === 'Boleto' ? 'selected' : ''; ?>>Boleto</option>
            <option value="Transferência" <?php echo ($contrato->forma_pagamento ?? '') === 'Transferência' ? 'selected' : ''; ?>>Transferência</option>
            <option value="Dinheiro" <?php echo ($contrato->forma_pagamento ?? '') === 'Dinheiro' ? 'selected' : ''; ?>>Dinheiro</option>
            <option value="Permuta" <?php echo ($contrato->forma_pagamento ?? '') === 'Permuta' ? 'selected' : ''; ?>>Permuta</option>
            <option value="Cortesia" <?php echo ($contrato->forma_pagamento ?? '') === 'Cortesia' ? 'selected' : ''; ?>>Cortesia</option>
        </select>
    </div>

    <div class="form-group col-md-3">
        <label class="form-control-label">Valor Já Pago</label>
        <input type="text" name="valor_pago" placeholder="R$ 0,00" class="form-control money" value="<?php echo number_format($contrato->valor_pago ?? 0, 2, ',', '.'); ?>">
    </div>

    <div class="form-group col-md-3">
        <label class="form-control-label">Desconto Adicional</label>
        <input type="text" name="desconto_adicional" placeholder="R$ 0,00" class="form-control money" value="<?php echo number_format($contrato->desconto_adicional ?? 0, 2, ',', '.'); ?>">
        <small class="text-muted">Desconto extra além dos itens</small>
    </div>

    <hr class="my-4">

    <!-- Observações -->
    <div class="col-12 mb-3">
        <h6 class="text-primary"><i class="bx bx-note me-2"></i>Observações</h6>
    </div>

    <div class="form-group col-md-12">
        <label class="form-control-label">Observações</label>
        <textarea name="observacoes" rows="3" placeholder="Informações adicionais sobre o contrato..." class="form-control"><?php echo esc($contrato->observacoes); ?></textarea>
    </div>

</div>
