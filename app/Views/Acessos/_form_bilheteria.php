<div class="row">
    <input type="hidden" name="operador" value="<?= (int) usuario_logado()->id ?>">
    <input type="hidden" name="evento_id" value="<?= (int) ($event_id ?? evento_selecionado()) ?>">
    <input type="hidden" name="tipo" value="ACESSO">

    <div class="form-group">
        <input type="text" name="codigo" autofocus placeholder="Aguardando leitura do QR CODE" class="form-control mb-2 shadow font-20" style="padding:13px;" required>

    </div>


</div>