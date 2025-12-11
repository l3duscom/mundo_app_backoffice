<div class="row">

    <!-- Tipo de Pessoa -->
    <div class="form-group col-md-12 mb-4">
        <label class="form-control-label fw-bold">Tipo de Pessoa</label>
        <div class="d-flex gap-4 mt-2">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="tipo_pessoa" id="tipo_pf" value="pf" <?php echo ($expositor->tipo_pessoa ?? 'pf') === 'pf' ? 'checked' : ''; ?>>
                <label class="form-check-label" for="tipo_pf">
                    <i class="bx bx-user me-1"></i>Pessoa Física (CPF)
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="tipo_pessoa" id="tipo_pj" value="pj" <?php echo ($expositor->tipo_pessoa ?? 'pf') === 'pj' ? 'checked' : ''; ?>>
                <label class="form-check-label" for="tipo_pj">
                    <i class="bx bx-buildings me-1"></i>Pessoa Jurídica (CNPJ)
                </label>
            </div>
        </div>
    </div>

    <hr class="mb-4">

    <!-- Dados Principais -->
    <div class="col-12 mb-3">
        <h6 class="text-primary"><i class="bx bx-id-card me-2"></i>Dados Principais</h6>
    </div>

    <div class="form-group col-md-8">
        <label class="form-control-label" id="nome-label"><?php echo ($expositor->tipo_pessoa ?? 'pf') === 'pj' ? 'Razão Social' : 'Nome Completo'; ?> <span class="text-danger">*</span></label>
        <input type="text" name="nome" id="nome" placeholder="<?php echo ($expositor->tipo_pessoa ?? 'pf') === 'pj' ? 'Insira a razão social' : 'Insira o nome completo'; ?>" class="form-control" value="<?php echo esc($expositor->nome); ?>">
    </div>

    <div class="form-group col-md-4">
        <label class="form-control-label" id="documento-label"><?php echo ($expositor->tipo_pessoa ?? 'pf') === 'pj' ? 'CNPJ' : 'CPF'; ?> <span class="text-danger">*</span></label>
        <input type="text" name="documento" id="documento" placeholder="<?php echo ($expositor->tipo_pessoa ?? 'pf') === 'pj' ? 'Insira o CNPJ' : 'Insira o CPF'; ?>" class="form-control <?php echo ($expositor->tipo_pessoa ?? 'pf') === 'pj' ? 'cnpj' : 'cpf'; ?>" value="<?php echo esc($expositor->documento); ?>">
    </div>

    <!-- Campos específicos para PJ -->
    <div class="form-group col-md-6 campos-pj" <?php echo ($expositor->tipo_pessoa ?? 'pf') === 'pf' ? 'style="display: none;"' : ''; ?>>
        <label class="form-control-label">Nome Fantasia</label>
        <input type="text" name="nome_fantasia" placeholder="Insira o nome fantasia" class="form-control" value="<?php echo esc($expositor->nome_fantasia); ?>">
    </div>

    <div class="form-group col-md-6 campos-pj" <?php echo ($expositor->tipo_pessoa ?? 'pf') === 'pf' ? 'style="display: none;"' : ''; ?>>
        <label class="form-control-label">Inscrição Estadual</label>
        <input type="text" name="ie" placeholder="Insira a I.E. (ou ISENTO)" class="form-control" value="<?php echo esc($expositor->ie); ?>">
    </div>

    <hr class="my-4">

    <!-- Contato -->
    <div class="col-12 mb-3">
        <h6 class="text-primary"><i class="bx bx-phone me-2"></i>Contato</h6>
    </div>

    <div class="form-group col-md-4">
        <label class="form-control-label">E-mail <span class="text-danger">*</span></label>
        <input type="email" name="email" placeholder="Insira o e-mail" class="form-control" value="<?php echo esc($expositor->email); ?>">
    </div>

    <div class="form-group col-md-4">
        <label class="form-control-label">Telefone <span class="text-danger">*</span></label>
        <input type="text" name="telefone" placeholder="Insira o telefone" class="form-control phone_with_ddd" value="<?php echo esc($expositor->telefone); ?>">
    </div>

    <div class="form-group col-md-4">
        <label class="form-control-label">Celular</label>
        <input type="text" name="celular" placeholder="Insira o celular" class="form-control sp_celphones" value="<?php echo esc($expositor->celular); ?>">
    </div>

    <!-- Responsável (apenas PJ) -->
    <div class="form-group col-md-6 campos-pj" <?php echo ($expositor->tipo_pessoa ?? 'pf') === 'pf' ? 'style="display: none;"' : ''; ?>>
        <label class="form-control-label">Responsável pela Empresa</label>
        <input type="text" name="responsavel" placeholder="Nome do responsável" class="form-control" value="<?php echo esc($expositor->responsavel); ?>">
    </div>

    <div class="form-group col-md-6 campos-pj" <?php echo ($expositor->tipo_pessoa ?? 'pf') === 'pf' ? 'style="display: none;"' : ''; ?>>
        <label class="form-control-label">Telefone do Responsável</label>
        <input type="text" name="responsavel_telefone" placeholder="Telefone do responsável" class="form-control sp_celphones" value="<?php echo esc($expositor->responsavel_telefone); ?>">
    </div>

    <hr class="my-4">

    <!-- Endereço -->
    <div class="col-12 mb-3">
        <h6 class="text-primary"><i class="bx bx-map me-2"></i>Endereço</h6>
    </div>

    <div class="form-group col-md-3">
        <label class="form-control-label">CEP</label>
        <input type="text" name="cep" placeholder="Insira o CEP" class="form-control cep" value="<?php echo esc($expositor->cep); ?>">
        <div id="cep"></div>
    </div>

    <div class="form-group col-md-7">
        <label class="form-control-label">Endereço</label>
        <input type="text" name="endereco" placeholder="Insira o endereço" class="form-control" value="<?php echo esc($expositor->endereco); ?>" readonly>
    </div>

    <div class="form-group col-md-2">
        <label class="form-control-label">Nº</label>
        <input type="text" name="numero" placeholder="Nº" class="form-control" value="<?php echo esc($expositor->numero); ?>">
    </div>

    <div class="form-group col-md-4">
        <label class="form-control-label">Complemento</label>
        <input type="text" name="complemento" placeholder="Apto, Sala, etc." class="form-control" value="<?php echo esc($expositor->complemento); ?>">
    </div>

    <div class="form-group col-md-4">
        <label class="form-control-label">Bairro</label>
        <input type="text" name="bairro" placeholder="Insira o Bairro" class="form-control" value="<?php echo esc($expositor->bairro); ?>" readonly>
    </div>

    <div class="form-group col-md-3">
        <label class="form-control-label">Cidade</label>
        <input type="text" name="cidade" placeholder="Cidade" class="form-control" value="<?php echo esc($expositor->cidade); ?>" readonly>
    </div>

    <div class="form-group col-md-1">
        <label class="form-control-label">UF</label>
        <input type="text" name="estado" placeholder="UF" class="form-control" value="<?php echo esc($expositor->estado); ?>" readonly>
    </div>

    <hr class="my-4">

    <!-- Informações Adicionais -->
    <div class="col-12 mb-3">
        <h6 class="text-primary"><i class="bx bx-info-circle me-2"></i>Informações Adicionais</h6>
    </div>

    <div class="form-group col-md-6">
        <label class="form-control-label">Tipo de Expositor <span class="text-danger">*</span></label>
        <select name="tipo_expositor" class="form-control">
            <option value="">Selecione o tipo</option>
            <option value="Stand Comercial" <?php echo ($expositor->tipo_expositor ?? '') === 'Stand Comercial' ? 'selected' : ''; ?>>Stand Comercial</option>
            <option value="Artist Alley" <?php echo ($expositor->tipo_expositor ?? '') === 'Artist Alley' ? 'selected' : ''; ?>>Artist Alley</option>
            <option value="Vila dos Artesãos" <?php echo ($expositor->tipo_expositor ?? '') === 'Vila dos Artesãos' ? 'selected' : ''; ?>>Vila dos Artesãos</option>
            <option value="Espaço Medieval" <?php echo ($expositor->tipo_expositor ?? '') === 'Espaço Medieval' ? 'selected' : ''; ?>>Espaço Medieval</option>
            <option value="Indie" <?php echo ($expositor->tipo_expositor ?? '') === 'Indie' ? 'selected' : ''; ?>>Indie</option>
            <option value="Games" <?php echo ($expositor->tipo_expositor ?? '') === 'Games' ? 'selected' : ''; ?>>Games</option>
            <option value="Espaço Temático" <?php echo ($expositor->tipo_expositor ?? '') === 'Espaço Temático' ? 'selected' : ''; ?>>Espaço Temático</option>
            <option value="Estúdio Tattoo" <?php echo ($expositor->tipo_expositor ?? '') === 'Estúdio Tattoo' ? 'selected' : ''; ?>>Estúdio Tattoo</option>
            <option value="Parceiros" <?php echo ($expositor->tipo_expositor ?? '') === 'Parceiros' ? 'selected' : ''; ?>>Parceiros</option>
            <option value="Food Park" <?php echo ($expositor->tipo_expositor ?? '') === 'Food Park' ? 'selected' : ''; ?>>Food Park</option>
            <option value="Patrocinadores" <?php echo ($expositor->tipo_expositor ?? '') === 'Patrocinadores' ? 'selected' : ''; ?>>Patrocinadores</option>
            <option value="Outros" <?php echo ($expositor->tipo_expositor ?? '') === 'Outros' ? 'selected' : ''; ?>>Outros</option>
        </select>
    </div>

    <div class="form-group col-md-6">
        <label class="form-control-label">Segmento de Atuação</label>
        <select name="segmento" class="form-control">
            <option value="">Selecione o segmento</option>
            <option value="Alimentação" <?php echo ($expositor->segmento ?? '') === 'Alimentação' ? 'selected' : ''; ?>>Alimentação</option>
            <option value="Artesanato" <?php echo ($expositor->segmento ?? '') === 'Artesanato' ? 'selected' : ''; ?>>Artesanato</option>
            <option value="Brinquedos" <?php echo ($expositor->segmento ?? '') === 'Brinquedos' ? 'selected' : ''; ?>>Brinquedos</option>
            <option value="Colecionáveis" <?php echo ($expositor->segmento ?? '') === 'Colecionáveis' ? 'selected' : ''; ?>>Colecionáveis</option>
            <option value="Cosplay" <?php echo ($expositor->segmento ?? '') === 'Cosplay' ? 'selected' : ''; ?>>Cosplay</option>
            <option value="Decoração" <?php echo ($expositor->segmento ?? '') === 'Decoração' ? 'selected' : ''; ?>>Decoração</option>
            <option value="Eletrônicos" <?php echo ($expositor->segmento ?? '') === 'Eletrônicos' ? 'selected' : ''; ?>>Eletrônicos</option>
            <option value="Games" <?php echo ($expositor->segmento ?? '') === 'Games' ? 'selected' : ''; ?>>Games</option>
            <option value="K-Pop" <?php echo ($expositor->segmento ?? '') === 'K-Pop' ? 'selected' : ''; ?>>K-Pop</option>
            <option value="Livros e HQs" <?php echo ($expositor->segmento ?? '') === 'Livros e HQs' ? 'selected' : ''; ?>>Livros e HQs</option>
            <option value="Mangás e Animes" <?php echo ($expositor->segmento ?? '') === 'Mangás e Animes' ? 'selected' : ''; ?>>Mangás e Animes</option>
            <option value="Moda e Acessórios" <?php echo ($expositor->segmento ?? '') === 'Moda e Acessórios' ? 'selected' : ''; ?>>Moda e Acessórios</option>
            <option value="Papelaria" <?php echo ($expositor->segmento ?? '') === 'Papelaria' ? 'selected' : ''; ?>>Papelaria</option>
            <option value="Pelúcias" <?php echo ($expositor->segmento ?? '') === 'Pelúcias' ? 'selected' : ''; ?>>Pelúcias</option>
            <option value="Serviços" <?php echo ($expositor->segmento ?? '') === 'Serviços' ? 'selected' : ''; ?>>Serviços</option>
            <option value="Vestuário" <?php echo ($expositor->segmento ?? '') === 'Vestuário' ? 'selected' : ''; ?>>Vestuário</option>
            <option value="Outro" <?php echo ($expositor->segmento ?? '') === 'Outro' ? 'selected' : ''; ?>>Outro</option>
        </select>
    </div>

    <div class="form-group col-md-6">
        <div class="custom-control custom-checkbox mt-4">
            <input type="hidden" name="ativo" value="0">
            <input type="checkbox" name="ativo" value="1" class="custom-control-input" id="ativo" <?php if (($expositor->ativo ?? 1) == true): ?> checked <?php endif; ?>>
            <label class="custom-control-label" for="ativo">Expositor ativo</label>
        </div>
    </div>

    <div class="form-group col-md-12">
        <label class="form-control-label">Observações</label>
        <textarea name="observacoes" rows="3" placeholder="Informações adicionais sobre o expositor..." class="form-control"><?php echo esc($expositor->observacoes); ?></textarea>
    </div>

</div>

