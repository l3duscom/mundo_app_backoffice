<div class="row">

    <div class="form-group col-md-6">
        <label class="form-control-label">Evento <span class="text-danger">*</span></label>
        <select name="event_id" class="form-select" required>
            <option value="">Selecione o evento...</option>
            <?php foreach ($eventos as $ev): ?>
                <option value="<?php echo $ev->id; ?>" <?php echo ($item->event_id ?? '') == $ev->id ? 'selected' : ''; ?>>
                    <?php echo esc($ev->nome); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group col-md-6">
        <label class="form-control-label">Nome do Item <span class="text-danger">*</span></label>
        <input type="text" name="nome" placeholder="Ex: Stand Comercial 3x3" class="form-control" value="<?php echo esc($item->nome); ?>" required>
    </div>

    <div class="form-group col-md-3">
        <label class="form-control-label">Tipo <span class="text-danger">*</span></label>
        <select name="tipo" class="form-select" required>
            <option value="">Selecione...</option>
            <?php foreach (\App\Models\ItemCatalogoModel::getTipos() as $tipo): ?>
                <option value="<?php echo $tipo; ?>" <?php echo ($item->tipo ?? '') === $tipo ? 'selected' : ''; ?>>
                    <?php echo $tipo; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group col-md-3">
        <label class="form-control-label">Metragem</label>
        <input type="text" name="metragem" placeholder="Ex: 3x3m" class="form-control" value="<?php echo esc($item->metragem); ?>">
    </div>

    <div class="form-group col-md-4">
        <label class="form-control-label">Valor <span class="text-danger">*</span></label>
        <input type="text" name="valor" placeholder="R$ 0,00" class="form-control money" value="<?php echo number_format($item->valor ?? 0, 2, ',', '.'); ?>" required>
    </div>

    <div class="form-group col-md-4">
        <label class="form-control-label">Status</label>
        <div class="form-check form-switch mt-2">
            <input class="form-check-input" type="checkbox" name="ativo" id="ativo" <?php echo ($item->ativo ?? 1) ? 'checked' : ''; ?>>
            <label class="form-check-label" for="ativo">Item ativo</label>
        </div>
    </div>

    <div class="form-group col-md-12">
        <label class="form-control-label">Descrição</label>
        <textarea name="descricao" rows="3" placeholder="Descrição detalhada do item..." class="form-control"><?php echo esc($item->descricao); ?></textarea>
    </div>

</div>

