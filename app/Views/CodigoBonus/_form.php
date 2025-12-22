<div class="row">
    <div class="form-group col-md-6 mb-3">
        <label class="form-label">Código <span class="text-danger">*</span></label>
        <input type="text" name="codigo" class="form-control" value="<?= isset($codigo) ? esc($codigo->codigo) : '' ?>" required>
    </div>

    <div class="form-group col-md-3 mb-3">
        <label class="form-label">Validade</label>
        <input type="date" name="validade" class="form-control" value="<?= isset($codigo) && $codigo->validade ? date('Y-m-d', strtotime($codigo->validade)) : '' ?>">
    </div>

    <div class="form-group col-md-3 mb-3">
        <label class="form-label">Validade do Lote</label>
        <input type="date" name="validade_lote" class="form-control" value="<?= isset($codigo) && $codigo->validade_lote ? date('Y-m-d', strtotime($codigo->validade_lote)) : '' ?>">
    </div>
</div>
