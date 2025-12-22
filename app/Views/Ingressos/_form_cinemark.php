<div class="row">
    <input type="hidden" name="ingresso_id" value="<?php echo $id ?>">
    <input type="hidden" name="pedido_id" value="<?php echo $pedido ?>">

    <div class="form-group col-md-6">
        <label class="form-control-label">Código do ingresso</label>
        <input type="text" name="cinemark" autofocus class="form-control " value="<?php if (isset($bonus_cinemark) && $bonus_cinemark) echo esc($bonus_cinemark->codigo); ?>">
    </div>


</div>