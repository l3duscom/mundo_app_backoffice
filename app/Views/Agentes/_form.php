<input type="hidden" name="id" value="<?php echo $agente->id ?? ''; ?>">

<div class="row">
    <!-- Tipo e Dados Principais -->
    <div class="col-12 mb-3">
        <h6 class="text-primary"><i class="bx bx-user-circle me-2"></i>Dados Principais</h6>
        <hr class="mt-0">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-control-label">Tipo <span class="text-danger">*</span></label>
        <select name="tipo" class="form-select" required>
            <?php foreach ($tipos as $k => $v): ?>
            <option value="<?php echo $k; ?>" <?php echo ($agente->tipo ?? 'agente') === $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-5 mb-3">
        <label class="form-control-label">Nome / Razão Social <span class="text-danger">*</span></label>
        <input type="text" name="nome" class="form-control" value="<?php echo esc($agente->nome ?? ''); ?>" required>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-control-label">Nome Fantasia</label>
        <input type="text" name="nome_fantasia" class="form-control" value="<?php echo esc($agente->nome_fantasia ?? ''); ?>">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-control-label">CPF</label>
        <input type="text" name="cpf" class="form-control cpf" placeholder="000.000.000-00" value="<?php echo esc($agente->cpf ?? ''); ?>">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-control-label">CNPJ</label>
        <input type="text" name="cnpj" class="form-control cnpj" placeholder="00.000.000/0000-00" value="<?php echo esc($agente->cnpj ?? ''); ?>">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-control-label">Status</label>
        <select name="ativo" class="form-select">
            <option value="1" <?php echo ($agente->ativo ?? 1) == 1 ? 'selected' : ''; ?>>Ativo</option>
            <option value="0" <?php echo ($agente->ativo ?? 1) == 0 ? 'selected' : ''; ?>>Inativo</option>
        </select>
    </div>
</div>

<div class="row mt-4">
    <!-- Contato -->
    <div class="col-12 mb-3">
        <h6 class="text-primary"><i class="bx bx-phone me-2"></i>Contato</h6>
        <hr class="mt-0">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-control-label">E-mail</label>
        <input type="email" name="email" class="form-control" value="<?php echo esc($agente->email ?? ''); ?>">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-control-label">Telefone</label>
        <input type="text" name="telefone" class="form-control telefone" value="<?php echo esc($agente->telefone ?? ''); ?>">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-control-label">WhatsApp</label>
        <input type="text" name="whatsapp" class="form-control telefone" value="<?php echo esc($agente->whatsapp ?? ''); ?>">
    </div>

    <div class="col-md-5 mb-3">
        <label class="form-control-label">Site</label>
        <input type="url" name="site" class="form-control" placeholder="https://..." value="<?php echo esc($agente->site ?? ''); ?>">
    </div>
</div>

<div class="row mt-4">
    <!-- Endereço -->
    <div class="col-12 mb-3">
        <h6 class="text-primary"><i class="bx bx-map me-2"></i>Endereço</h6>
        <hr class="mt-0">
    </div>

    <div class="col-md-2 mb-3">
        <label class="form-control-label">CEP</label>
        <input type="text" name="cep" class="form-control cep" placeholder="00000-000" value="<?php echo esc($agente->cep ?? ''); ?>">
    </div>

    <div class="col-md-5 mb-3">
        <label class="form-control-label">Endereço</label>
        <input type="text" name="endereco" class="form-control" value="<?php echo esc($agente->endereco ?? ''); ?>">
    </div>

    <div class="col-md-2 mb-3">
        <label class="form-control-label">Número</label>
        <input type="text" name="numero" class="form-control" value="<?php echo esc($agente->numero ?? ''); ?>">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-control-label">Complemento</label>
        <input type="text" name="complemento" class="form-control" value="<?php echo esc($agente->complemento ?? ''); ?>">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-control-label">Bairro</label>
        <input type="text" name="bairro" class="form-control" value="<?php echo esc($agente->bairro ?? ''); ?>">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-control-label">Cidade</label>
        <input type="text" name="cidade" class="form-control" value="<?php echo esc($agente->cidade ?? ''); ?>">
    </div>

    <div class="col-md-2 mb-3">
        <label class="form-control-label">Estado</label>
        <input type="text" name="estado" class="form-control" maxlength="2" placeholder="UF" value="<?php echo esc($agente->estado ?? ''); ?>">
    </div>
</div>

<div class="row mt-4">
    <!-- Dados Bancários -->
    <div class="col-12 mb-3">
        <h6 class="text-primary"><i class="bx bx-wallet me-2"></i>Dados Bancários</h6>
        <hr class="mt-0">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-control-label">Banco</label>
        <input type="text" name="banco" class="form-control" value="<?php echo esc($agente->banco ?? ''); ?>">
    </div>

    <div class="col-md-2 mb-3">
        <label class="form-control-label">Agência</label>
        <input type="text" name="agencia" class="form-control" value="<?php echo esc($agente->agencia ?? ''); ?>">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-control-label">Conta</label>
        <input type="text" name="conta" class="form-control" value="<?php echo esc($agente->conta ?? ''); ?>">
    </div>

    <div class="col-md-2 mb-3">
        <label class="form-control-label">Tipo Conta</label>
        <select name="tipo_conta" class="form-select">
            <option value="">Selecione...</option>
            <option value="corrente" <?php echo ($agente->tipo_conta ?? '') === 'corrente' ? 'selected' : ''; ?>>Corrente</option>
            <option value="poupanca" <?php echo ($agente->tipo_conta ?? '') === 'poupanca' ? 'selected' : ''; ?>>Poupança</option>
        </select>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-control-label">Chave PIX</label>
        <input type="text" name="pix" class="form-control" value="<?php echo esc($agente->pix ?? ''); ?>">
    </div>
</div>

<div class="row mt-4">
    <!-- Observações -->
    <div class="col-12 mb-3">
        <label class="form-control-label">Observações</label>
        <textarea name="observacoes" class="form-control" rows="3"><?php echo esc($agente->observacoes ?? ''); ?></textarea>
    </div>
</div>
