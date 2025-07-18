<div class="row">

    <!-- Campo CSRF token -->
    <input type="hidden" name="csrf_ordem" value="<?= csrf_hash() ?>">

    <!-- Checkbox para definir tipo de pessoa -->
    <div class="form-group col-md-12">
        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="pj" name="pj" value="1" <?= $cliente->pj == 1 ? 'checked' : '' ?>>
            <label class="custom-control-label" for="pj">Pessoa Jurídica (PJ)</label>
            <small class="form-text text-muted">Marque esta opção se for uma empresa/CNPJ</small>
        </div>
        
        <?php if ($cliente->grupo_id == 4) : ?>
            <div class="alert alert-info mt-2">
                <i class="bx bx-info-circle me-2"></i>
                <strong>Parceiro Identificado:</strong> Este cliente é um parceiro registrado no sistema.
            </div>
        <?php endif; ?>
    </div>

    <div class="form-group col-md-12">
        <label class="form-control-label" id="nome-label"><?= $cliente->pj == 1 ? 'Razão Social' : 'Nome completo' ?></label>
        <input type="text" name="nome" id="nome" placeholder="<?= $cliente->pj == 1 ? 'Insira a razão social' : 'Insira o nome completo' ?>" class="form-control" value="<?php echo esc($cliente->nome); ?>">
    </div>

    <div class="form-group col-md-4">
        <label class="form-control-label" id="cpf-label"><?= $cliente->pj == 1 ? 'CNPJ' : 'CPF' ?></label>
        <input type="text" name="cpf" id="cpf" placeholder="<?= $cliente->pj == 1 ? 'Insira o CNPJ' : 'Insira o CPF' ?>" class="form-control <?= $cliente->pj == 1 ? 'cnpj' : 'cpf' ?>" value="<?php echo esc($cliente->cpf); ?>">
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

    <!-- Campos específicos para parceiro/PJ -->
    <div class="form-group col-md-12 partner-fields" <?= $cliente->pj == 1 || $cliente->grupo_id == 4 ? '' : 'style="display: none;"' ?>>
        <hr>
        <div class="partner-info">
            <h6>
                <i class="bx bx-buildings me-2"></i>Informações Adicionais
                <small class="text-muted">(Campos específicos para PJ/Parceiros)</small>
            </h6>
        </div>
    </div>

    <div class="form-group col-md-6 partner-fields" <?= $cliente->pj == 1 || $cliente->grupo_id == 4 ? '' : 'style="display: none;"' ?>>
        <label class="form-control-label">Tipo de Parceria</label>
        <select name="tipo_parceria" class="form-control">
            <option value="">Selecione o tipo de parceria</option>
            <option value="comercial" <?= $cliente->tipo_parceria == 'comercial' ? 'selected' : '' ?>>Comercial</option>
            <option value="artista" <?= $cliente->tipo_parceria == 'artista' ? 'selected' : '' ?>>Artista</option>
            <option value="indie" <?= $cliente->tipo_parceria == 'indie' ? 'selected' : '' ?>>Indie</option>
            <option value="estrategica" <?= $cliente->tipo_parceria == 'estrategica' ? 'selected' : '' ?>>Estratégica</option>
            <option value="fornecedor" <?= $cliente->tipo_parceria == 'fornecedor' ? 'selected' : '' ?>>Fornecedor</option>
            <option value="patrocinador" <?= $cliente->tipo_parceria == 'patrocinador' ? 'selected' : '' ?>>Patrocinador</option>
            <option value="outro" <?= $cliente->tipo_parceria == 'outro' ? 'selected' : '' ?>>Outro</option>
        </select>
    </div>

    <div class="form-group col-md-6 partner-fields" <?= $cliente->pj == 1 || $cliente->grupo_id == 4 ? '' : 'style="display: none;"' ?>>
        <label class="form-control-label">Área de Atuação</label>
        <input type="text" name="area_atuacao" placeholder="Ex: Tecnologia, Marketing, Alimentação..." class="form-control" value="<?php echo esc($cliente->area_atuacao); ?>">
    </div>

    <div class="form-group col-md-12 partner-fields" <?= $cliente->pj == 1 || $cliente->grupo_id == 4 ? '' : 'style="display: none;"' ?>>
        <label class="form-control-label">Observações</label>
        <textarea name="observacoes" class="form-control" rows="3" placeholder="Informações adicionais sobre a parceria..."><?php echo esc($cliente->observacoes); ?></textarea>
    </div>

</div> 