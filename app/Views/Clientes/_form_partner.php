<div class="row">

    <!-- Campo CSRF token -->
    <input type="hidden" name="csrf_ordem" value="<?= csrf_hash() ?>">

    <!-- Checkbox para definir tipo de pessoa -->
    <div class="form-group col-md-12">
        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="pj" name="pj" value="1" checked>
            <label class="custom-control-label" for="pj">Pessoa Jurídica (PJ)</label>
            <small class="form-text text-muted">Marque esta opção se for uma empresa/CNPJ</small>
        </div>
    </div>

    <div class="form-group col-md-12">
        <label class="form-control-label" id="nome-label">Razão Social</label>
        <input type="text" name="nome" id="nome" placeholder="Insira a razão social" class="form-control" value="<?php echo esc($cliente->nome); ?>">
    </div>

    <div class="form-group col-md-4">
        <label class="form-control-label" id="cpf-label">CNPJ</label>
        <input type="text" name="cpf" id="cpf" placeholder="Insira o CNPJ" class="form-control cnpj" value="<?php echo esc($cliente->cpf); ?>">
    </div>

    <div class="form-group col-md-4">
        <label class="form-control-label">Telefone</label>
        <input type="text" name="telefone" placeholder="Insira o telefone" class="form-control sp_celphones" value="<?php echo esc($cliente->telefone); ?>">
    </div>

    <div class="form-group col-md-4">
        <label class="form-control-label">E-mail (para acesso ao sistema)</label>
        <input type="text" name="email" placeholder="Insira o email" class="form-control" value="<?php echo esc($cliente->email); ?>">
        <div id="email"></div>
    </div>

    <div class="form-group col-md-4">
        <label class="form-control-label">CEP</label>
        <input type="text" name="cep" placeholder="Insira o CEP" class="form-control cep" value="<?php echo esc($cliente->cep); ?>">
        <div id="cep"></div>
    </div>

    <div class="form-group col-md-6">
        <label class="form-control-label">Endereço</label>
        <input type="text" name="endereco" placeholder="Insira o endereço" class="form-control" value="<?php echo esc($cliente->endereco); ?>" readonly>
    </div>

    <div class="form-group col-md-2">
        <label class="form-control-label">Nº</label>
        <input type="text" name="numero" placeholder="Insira o Nº" class="form-control" value="<?php echo esc($cliente->numero); ?>">
    </div>

    <div class="form-group col-md-4">
        <label class="form-control-label">Bairro</label>
        <input type="text" name="bairro" placeholder="Insira o Bairro" class="form-control" value="<?php echo esc($cliente->bairro); ?>" readonly>
    </div>

    <div class="form-group col-md-6">
        <label class="form-control-label">Cidade</label>
        <input type="text" name="cidade" placeholder="Insira a Cidade" class="form-control" value="<?php echo esc($cliente->cidade); ?>" readonly>
    </div>

    <div class="form-group col-md-2">
        <label class="form-control-label">Estado</label>
        <input type="text" name="estado" placeholder="U.F" class="form-control" value="<?php echo esc($cliente->estado); ?>" readonly>
    </div>

    <!-- Campos específicos para parceiro -->
    <div class="form-group col-md-12">
        <hr>
        <h6>Informações do Parceiro</h6>
    </div>

    <div class="form-group col-md-6">
        <label class="form-control-label">Tipo de Parceria</label>
        <select name="tipo_parceria" class="form-control">
            <option value="">Selecione o tipo de parceria</option>
            <option value="comercial">Comercial</option>
            <option value="artista">Artista</option>
            <option value="indie">Indie</option>
            <option value="estrategica">Estratégica</option>
            <option value="fornecedor">Fornecedor</option>
            <option value="patrocinador">Patrocinador</option>
            <option value="outro">Outro</option>
        </select>
    </div>

    <div class="form-group col-md-6">
        <label class="form-control-label">Área de Atuação</label>
        <input type="text" name="area_atuacao" placeholder="Ex: Tecnologia, Marketing, Alimentação..." class="form-control" value="">
    </div>

    <div class="form-group col-md-12">
        <label class="form-control-label">Observações</label>
        <textarea name="observacoes" class="form-control" rows="3" placeholder="Informações adicionais sobre a parceria..."></textarea>
    </div>

</div> 