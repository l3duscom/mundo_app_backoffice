<input type="hidden" name="id" value="<?php echo $artista->id ?? ''; ?>">

<div class="row">
    <!-- Dados Artísticos -->
    <div class="col-12 mb-3">
        <h6 class="text-primary"><i class="bx bx-microphone me-2"></i>Dados Artísticos</h6>
        <hr class="mt-0">
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-control-label">Nome Artístico <span class="text-danger">*</span></label>
        <input type="text" name="nome_artistico" class="form-control" value="<?php echo esc($artista->nome_artistico ?? ''); ?>" required>
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-control-label">Gênero Musical</label>
        <input type="text" name="genero_musical" class="form-control" placeholder="Ex: Pop, Rock, Sertanejo" value="<?php echo esc($artista->genero_musical ?? ''); ?>">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-control-label">Status</label>
        <select name="ativo" class="form-select">
            <option value="1" <?php echo ($artista->ativo ?? 1) == 1 ? 'selected' : ''; ?>>Ativo</option>
            <option value="0" <?php echo ($artista->ativo ?? 1) == 0 ? 'selected' : ''; ?>>Inativo</option>
        </select>
    </div>

    <div class="col-12 mb-3">
        <label class="form-control-label">Biografia</label>
        <textarea name="biografia" class="form-control" rows="3" placeholder="Breve biografia do artista/banda..."><?php echo esc($artista->biografia ?? ''); ?></textarea>
    </div>

    <div class="col-12 mb-3">
        <label class="form-control-label">Rider Técnico</label>
        <textarea name="rider_tecnico" class="form-control" rows="3" placeholder="Requisitos técnicos do show..."><?php echo esc($artista->rider_tecnico ?? ''); ?></textarea>
    </div>
</div>

<div class="row mt-4">
    <!-- Dados Pessoais -->
    <div class="col-12 mb-3">
        <h6 class="text-primary"><i class="bx bx-user me-2"></i>Dados Pessoais</h6>
        <hr class="mt-0">
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-control-label">Nome Completo (Civil)</label>
        <input type="text" name="nome_completo" class="form-control" value="<?php echo esc($artista->nome_completo ?? ''); ?>">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-control-label">CPF</label>
        <input type="text" name="cpf" class="form-control cpf" placeholder="000.000.000-00" value="<?php echo esc($artista->cpf ?? ''); ?>">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-control-label">RG</label>
        <input type="text" name="rg" class="form-control" value="<?php echo esc($artista->rg ?? ''); ?>">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-control-label">Data de Nascimento</label>
        <input type="date" name="data_nascimento" class="form-control" value="<?php echo $artista->data_nascimento ?? ''; ?>">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-control-label">Nacionalidade</label>
        <input type="text" name="nacionalidade" class="form-control" value="<?php echo esc($artista->nacionalidade ?? 'Brasileira'); ?>">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-control-label">Passaporte</label>
        <input type="text" name="passaporte" class="form-control" placeholder="Para estrangeiros" value="<?php echo esc($artista->passaporte ?? ''); ?>">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-control-label">Validade Passaporte</label>
        <input type="date" name="passaporte_validade" class="form-control" value="<?php echo $artista->passaporte_validade ?? ''; ?>">
    </div>
</div>

<div class="row mt-4">
    <!-- Contato -->
    <div class="col-12 mb-3">
        <h6 class="text-primary"><i class="bx bx-phone me-2"></i>Contato do Artista</h6>
        <hr class="mt-0">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-control-label">E-mail</label>
        <input type="email" name="email" class="form-control" value="<?php echo esc($artista->email ?? ''); ?>">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-control-label">Telefone</label>
        <input type="text" name="telefone" class="form-control telefone" value="<?php echo esc($artista->telefone ?? ''); ?>">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-control-label">Observações</label>
        <input type="text" name="observacoes" class="form-control" value="<?php echo esc($artista->observacoes ?? ''); ?>">
    </div>
</div>

<!-- Contatos (Agentes, Empresários, etc.) -->
<div class="row mt-4">
    <div class="col-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="text-primary mb-0"><i class="bx bx-group me-2"></i>Agentes e Outros Contatos</h6>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAdicionarContato">
                <i class="bx bx-plus me-1"></i>Adicionar Contato
            </button>
        </div>
        <hr class="mt-2">
    </div>
</div>

<div id="container-contatos">
    <?php if (!empty($contatos)): foreach ($contatos as $idx => $c): ?>
    <div class="row contato-row mb-2" data-index="<?php echo $idx; ?>">
        <div class="col-md-2">
            <select name="contatos[<?php echo $idx; ?>][tipo]" class="form-select form-select-sm">
                <option value="agente" <?php echo ($c->tipo ?? '') === 'agente' ? 'selected' : ''; ?>>Agente</option>
                <option value="empresario" <?php echo ($c->tipo ?? '') === 'empresario' ? 'selected' : ''; ?>>Empresário</option>
                <option value="assessoria" <?php echo ($c->tipo ?? '') === 'assessoria' ? 'selected' : ''; ?>>Assessoria</option>
                <option value="tecnico" <?php echo ($c->tipo ?? '') === 'tecnico' ? 'selected' : ''; ?>>Técnico</option>
                <option value="outro" <?php echo ($c->tipo ?? '') === 'outro' ? 'selected' : ''; ?>>Outro</option>
            </select>
        </div>
        <div class="col-md-3">
            <input type="text" name="contatos[<?php echo $idx; ?>][nome]" class="form-control form-control-sm" placeholder="Nome" value="<?php echo esc($c->nome ?? ''); ?>">
        </div>
        <div class="col-md-2">
            <input type="text" name="contatos[<?php echo $idx; ?>][telefone]" class="form-control form-control-sm telefone" placeholder="Telefone" value="<?php echo esc($c->telefone ?? ''); ?>">
        </div>
        <div class="col-md-3">
            <input type="email" name="contatos[<?php echo $idx; ?>][email]" class="form-control form-control-sm" placeholder="E-mail" value="<?php echo esc($c->email ?? ''); ?>">
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-sm btn-outline-danger btn-remover-contato"><i class="bx bx-trash"></i></button>
        </div>
    </div>
    <?php endforeach; endif; ?>
</div>
