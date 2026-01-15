<input type="hidden" name="id" value="<?php echo $orderBump->id ?? ''; ?>">

<div class="row">
    <div class="col-md-8 mb-3">
        <label class="form-label">Nome <span class="text-danger">*</span></label>
        <input type="text" name="nome" class="form-control" required
            placeholder="Ex: Camiseta Oficial do Evento"
            value="<?php echo esc($orderBump->nome ?? ''); ?>">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Tipo <span class="text-danger">*</span></label>
        <select name="tipo" class="form-select" required>
            <option value="produto" <?php echo ($orderBump->tipo ?? 'produto') == 'produto' ? 'selected' : ''; ?>>Produto</option>
            <option value="servico" <?php echo ($orderBump->tipo ?? '') == 'servico' ? 'selected' : ''; ?>>Serviço</option>
            <option value="ingresso_adicional" <?php echo ($orderBump->tipo ?? '') == 'ingresso_adicional' ? 'selected' : ''; ?>>Ingresso Adicional</option>
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Preço <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text">R$</span>
            <input type="text" name="preco" id="preco" class="form-control money" required
                placeholder="0,00"
                value="<?php echo isset($orderBump->preco) ? number_format($orderBump->preco, 2, ',', '.') : ''; ?>">
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Estoque</label>
        <input type="number" name="estoque" class="form-control" min="0"
            placeholder="Deixe vazio para ilimitado"
            value="<?php echo $orderBump->estoque ?? ''; ?>">
        <small class="text-muted">Vazio = estoque ilimitado</small>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Máx. por Pedido</label>
        <input type="number" name="max_por_pedido" class="form-control" min="1"
            value="<?php echo $orderBump->max_por_pedido ?? 1; ?>">
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Vincular a Ingresso (opcional)</label>
        <select name="ticket_id" id="ticketId" class="form-select">
            <option value="">Todos os ingressos do evento</option>
            <?php foreach ($tickets as $t): ?>
            <option value="<?php echo $t->id; ?>" 
                <?php echo ($orderBump->ticket_id ?? 0) == $t->id ? 'selected' : ''; ?>>
                <?php echo esc($t->nome); ?> - R$ <?php echo number_format($t->preco, 2, ',', '.'); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <small class="text-muted">Se não selecionar, aparecerá para todos os ingressos</small>
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Ordem de Exibição</label>
        <input type="number" name="ordem" class="form-control" min="0"
            value="<?php echo $orderBump->ordem ?? 0; ?>">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Status</label>
        <select name="ativo" class="form-select">
            <option value="1" <?php echo ($orderBump->ativo ?? 1) == 1 ? 'selected' : ''; ?>>Ativo</option>
            <option value="0" <?php echo ($orderBump->ativo ?? 1) == 0 ? 'selected' : ''; ?>>Inativo</option>
        </select>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-3">
        <label class="form-label">Descrição (opcional)</label>
        <textarea name="descricao" class="form-control" rows="3" 
            placeholder="Descreva os detalhes do produto/serviço..."><?php echo esc($orderBump->descricao ?? ''); ?></textarea>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-3">
        <label class="form-label">Imagem (opcional)</label>
        <div class="row align-items-center">
            <div class="col-md-3">
                <div id="imagemPreview" class="border rounded d-flex align-items-center justify-content-center bg-light" 
                    style="width: 150px; height: 150px; overflow: hidden;">
                    <?php if ($orderBump->temImagem()): ?>
                    <img src="<?php echo $orderBump->getImagemUrl(); ?>" alt="" style="max-width: 100%; max-height: 100%; object-fit: cover;">
                    <?php else: ?>
                    <i class="bx bx-image text-muted" style="font-size: 3rem;"></i>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-9">
                <input type="file" name="imagem" id="inputImagem" class="form-control" accept="image/*">
                <small class="text-muted d-block mt-1">Formatos: JPG, PNG, GIF, WebP. Tamanho máximo: 5MB</small>
                <?php if ($orderBump->temImagem()): ?>
                <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="btnRemoverImagem">
                    <i class="bx bx-trash me-1"></i>Remover imagem
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
