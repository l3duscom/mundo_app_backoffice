<div class="row">
    <input type="hidden" name="user_id" value="<?php echo esc($cliente->usuario_id); ?>">
    <input type="hidden" name="pedido_id" value="<?php echo esc($pedido_id); ?>">
    <div class="form-group col-md-4">
        <label class="form-control-label">CEP</label>
        <input type="text" name="cep" placeholder="Insira o CEP" class="form-control cep" value="<?php if ($endereco) echo esc($endereco->cep); ?>">
        <div id="cep"></div>
    </div>

    <div class="form-group col-md-6">
        <label class="form-control-label">Endereço</label>
        <input type="text" name="endereco" placeholder="Insira o endereço" class="form-control" value="<?php if ($endereco) echo esc($endereco->endereco); ?>">
    </div>

    <div class="form-group col-md-2">
        <label class="form-control-label">Nº</label>
        <input type="text" name="numero" placeholder="Insira o Nº" class="form-control" value="<?php if ($endereco) echo esc($endereco->numero); ?>">
    </div>

    <div class="form-group col-md-4">
        <label class="form-control-label">Bairro</label>
        <input type="text" name="bairro" placeholder="Insira o Bairro" class="form-control" value="<?php if ($endereco) echo esc($endereco->bairro); ?>">
    </div>

    <div class="form-group col-md-6">
        <label class="form-control-label">Cidade</label>
        <input type="text" name="cidade" placeholder="Insira a Cidade" class="form-control" value="<?php if ($endereco) echo esc($endereco->cidade); ?>">
    </div>

    <div class="form-group col-md-2">
        <label class="form-control-label">Estado</label>
        <input type="text" name="estado" placeholder="U.F" class="form-control" value="<?php if ($endereco) echo esc($endereco->estado); ?>">
    </div>

</div>