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

<!-- Agentes Vinculados -->
<div class="row mt-4">
    <div class="col-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="text-primary mb-0"><i class="bx bx-group me-2"></i>Agentes Vinculados</h6>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAdicionarAgente">
                <i class="bx bx-plus me-1"></i>Vincular Agente
            </button>
        </div>
        <hr class="mt-2">
    </div>
</div>

<div id="container-agentes">
    <?php if (!empty($agentesVinculados)): foreach ($agentesVinculados as $idx => $ag): ?>
    <div class="row agente-row mb-2 align-items-center" data-agente-id="<?php echo $ag->agente_id; ?>">
        <div class="col-md-4">
            <input type="hidden" name="agentes[<?php echo $idx; ?>][agente_id]" value="<?php echo $ag->agente_id; ?>">
            <strong><?php echo esc($ag->nome_fantasia ?: $ag->nome); ?></strong>
            <small class="text-muted d-block"><?php echo esc($ag->email ?? ''); ?></small>
        </div>
        <div class="col-md-3">
            <select name="agentes[<?php echo $idx; ?>][funcao]" class="form-select form-select-sm">
                <option value="agente" <?php echo ($ag->funcao ?? '') === 'agente' ? 'selected' : ''; ?>>Agente</option>
                <option value="empresario" <?php echo ($ag->funcao ?? '') === 'empresario' ? 'selected' : ''; ?>>Empresário</option>
                <option value="assessoria" <?php echo ($ag->funcao ?? '') === 'assessoria' ? 'selected' : ''; ?>>Assessoria</option>
                <option value="produtor" <?php echo ($ag->funcao ?? '') === 'produtor' ? 'selected' : ''; ?>>Produtor</option>
                <option value="tecnico" <?php echo ($ag->funcao ?? '') === 'tecnico' ? 'selected' : ''; ?>>Técnico</option>
                <option value="outro" <?php echo ($ag->funcao ?? '') === 'outro' ? 'selected' : ''; ?>>Outro</option>
            </select>
        </div>
        <div class="col-md-2">
            <div class="form-check">
                <input type="checkbox" name="agentes[<?php echo $idx; ?>][principal]" value="1" class="form-check-input" <?php echo ($ag->principal ?? 0) ? 'checked' : ''; ?>>
                <label class="form-check-label small">Principal</label>
            </div>
        </div>
        <div class="col-md-2">
            <a href="<?php echo site_url("agentes/exibir/{$ag->agente_id}"); ?>" class="btn btn-sm btn-outline-secondary" target="_blank" title="Ver Agente">
                <i class="bx bx-link-external"></i>
            </a>
            <button type="button" class="btn btn-sm btn-outline-danger btn-remover-agente"><i class="bx bx-trash"></i></button>
        </div>
    </div>
    <?php endforeach; endif; ?>
</div>

<p class="text-muted small mt-2">
    <i class="bx bx-info-circle me-1"></i>
    Não encontrou o agente? <a href="<?php echo site_url('agentes/criar'); ?>" target="_blank">Cadastre um novo agente</a>
</p>

<!-- Modal Adicionar Agente -->
<div class="modal fade" id="modalAdicionarAgente" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bx bx-user-plus me-2"></i>Vincular Agente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Selecione o Agente</label>
                    <select id="selectAgente" class="form-select" style="width: 100%"></select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Função</label>
                    <select id="selectFuncao" class="form-select">
                        <option value="agente">Agente</option>
                        <option value="empresario">Empresário</option>
                        <option value="assessoria">Assessoria</option>
                        <option value="produtor">Produtor</option>
                        <option value="tecnico">Técnico</option>
                        <option value="outro">Outro</option>
                    </select>
                </div>
                <div class="form-check">
                    <input type="checkbox" id="checkPrincipal" class="form-check-input">
                    <label class="form-check-label" for="checkPrincipal">Contato Principal</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarAgente">
                    <i class="bx bx-check me-1"></i>Vincular
                </button>
            </div>
        </div>
    </div>
</div>
