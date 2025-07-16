<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<!-- Aqui coloco os estilos da view-->
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>
<div class="row">
    <div class="col-lg-8">
        <div class="block">
            <div class="block-body">
                <div id="response"></div>
                <?php echo form_open('/', ['id' => 'form-perfil']); ?>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label class="form-control-label">Nome completo</label>
                        <input type="text" name="nome" class="form-control" value="<?php echo esc($cliente->nome); ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-control-label">E-mail</label>
                        <input type="email" name="email" class="form-control" value="<?php echo esc($cliente->email); ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label class="form-control-label">CPF</label>
                        <input type="text" name="cpf" class="form-control cpf" value="<?php echo esc($cliente->cpf); ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label class="form-control-label">Telefone</label>
                        <input type="text" name="telefone" class="form-control sp_celphones" value="<?php echo esc($cliente->telefone); ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label class="form-control-label">CEP</label>
                        <input type="text" name="cep" class="form-control cep" value="<?php echo esc($cliente->cep); ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-control-label">Endereço</label>
                        <input type="text" name="endereco" class="form-control" value="<?php echo esc($cliente->endereco); ?>" readonly>
                    </div>
                    <div class="form-group col-md-2">
                        <label class="form-control-label">Nº</label>
                        <input type="text" name="numero" class="form-control" value="<?php echo esc($cliente->numero); ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label class="form-control-label">Bairro</label>
                        <input type="text" name="bairro" class="form-control" value="<?php echo esc($cliente->bairro); ?>" readonly>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-control-label">Cidade</label>
                        <input type="text" name="cidade" class="form-control" value="<?php echo esc($cliente->cidade); ?>" readonly>
                    </div>
                    <div class="form-group col-md-2">
                        <label class="form-control-label">Estado</label>
                        <input type="text" name="estado" class="form-control" value="<?php echo esc($cliente->estado); ?>" readonly>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label class="form-control-label">Nova senha</label>
                        <input type="password" name="password" class="form-control" placeholder="Deixe em branco para não alterar">
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-control-label">Confirmação de senha</label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Confirme a nova senha">
                    </div>
                </div>
                <div class="form-group mt-4">
                    <input id="btn-salvar" type="submit" value="Salvar" class="btn btn-primary btn-sm mr-2">
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script src="<?php echo site_url('recursos/vendor/loadingoverlay/loadingoverlay.min.js') ?>"></script>
<script src="<?php echo site_url('recursos/vendor/mask/jquery.mask.min.js') ?>"></script>
<script src="<?php echo site_url('recursos/vendor/mask/app.js') ?>"></script>
<script>
$(document).ready(function(){
    // Aplica máscara ao campo CEP
    $('[name=cep]').mask('00000-000');
    $("#form-perfil").on('submit', function(e){
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: '<?php echo site_url('usuarios/atualizarperfil'); ?>',
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function(){
                $("#response").html('');
                $("#btn-salvar").val('Por favor aguarde...');
            },
            success: function(response){
                $("#btn-salvar").val('Salvar');
                $("#btn-salvar").removeAttr("disabled");
                if(response.sucesso){
                    $("#response").html('<div class="alert alert-success">' + response.sucesso + '</div>');
                }
                if(response.erro){
                    $("#response").html('<div class="alert alert-danger">' + response.erro + '</div>');
                    if(response.erros_model){
                        $.each(response.erros_model, function(key, value) {
                            $("#response").append('<ul class="list-unstyled"><li class="text-danger">'+ value +'</li></ul>');
                        });
                    }
                }
            },
            error: function(){
                alert('Não foi possível procesar a solicitação. Por favor entre em contato com o suporte técnico.');
                $("#btn-salvar").val('Salvar');
                $("#btn-salvar").removeAttr("disabled");
            }
        });
    });
    $("#form-perfil").submit(function () {
        $(this).find(":submit").attr('disabled', 'disabled');
    });
});
// Script de busca automática de endereço pelo CEP (igual Clientes)
$('[name=cep]').on('keyup', function() {
    var cep = $(this).val();
    if (cep.length === 9) {
        $.ajax({
            type: 'GET',
            url: '<?php echo site_url('clientes/consultacep'); ?>',
            data: { cep: cep },
            dataType: 'json',
            beforeSend: function() {
                $("#form-perfil").LoadingOverlay("show");
                $("#cep").html('');
            },
            success: function(response) {
                $("#form-perfil").LoadingOverlay("hide", true);
                if (!response.erro) {
                    if (!response.endereco) {
                        $('[name=endereco]').prop('readonly', false);
                        $('[name=endereco]').focus();
                    }
                    if (!response.bairro) {
                        $('[name=bairro]').prop('readonly', false);
                    }
                    $('[name=endereco]').val(response.endereco);
                    $('[name=bairro]').val(response.bairro);
                    $('[name=cidade]').val(response.cidade);
                    $('[name=estado]').val(response.estado);
                }
                if (response.erro) {
                    $("#cep").html(response.erro);
                }
            },
            error: function() {
                $("#form-perfil").LoadingOverlay("hide", true);
                alert('Não foi possível procesar a solicitação. Por favor entre em contato com o suporte técnico.');
            }
        });
    }
});
</script>
<?php echo $this->endSection() ?> 