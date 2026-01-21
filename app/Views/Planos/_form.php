<div class="row">
    <div class="col-md-8">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome do Plano <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="nome" name="nome" 
                   value="<?php echo old('nome', $plano->nome ?? ''); ?>" 
                   placeholder="Ex: Premium, Gold, VIP" required>
        </div>

        <div class="mb-3">
            <label for="slug" class="form-label">Slug</label>
            <input type="text" class="form-control" id="slug" name="slug" 
                   value="<?php echo old('slug', $plano->slug ?? ''); ?>" 
                   placeholder="Gerado automaticamente se vazio">
            <small class="text-muted">Identificador único usado em URLs. Deixe vazio para gerar automaticamente.</small>
        </div>

        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição</label>
            <textarea class="form-control" id="descricao" name="descricao" rows="3" 
                      placeholder="Descrição do plano"><?php echo old('descricao', $plano->descricao ?? ''); ?></textarea>
        </div>

        <div class="mb-3">
            <label for="beneficios" class="form-label">Benefícios</label>
            <textarea class="form-control" id="beneficios" name="beneficios" rows="5" 
                      placeholder="Um benefício por linha"><?php 
                $beneficios = old('beneficios') ?: ($plano->getBeneficios() ?? []);
                if (is_array($beneficios)) {
                    echo implode("\n", $beneficios);
                } else {
                    echo $beneficios;
                }
            ?></textarea>
            <small class="text-muted">Digite um benefício por linha. Será exibido como lista na página de vendas.</small>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card bg-light border-0">
            <div class="card-body">
                <h6 class="card-title mb-3"><i class="bx bx-cog me-2"></i>Configurações</h6>

                <div class="mb-3">
                    <label for="preco" class="form-label">Preço <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="text" class="form-control money" id="preco" name="preco" 
                               value="<?php echo old('preco', isset($plano->preco) ? number_format($plano->preco, 2, ',', '.') : ''); ?>" 
                               placeholder="0,00" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="ciclo" class="form-label">Ciclo de Cobrança <span class="text-danger">*</span></label>
                    <select class="form-select" id="ciclo" name="ciclo" required>
                        <option value="MONTHLY" <?php echo old('ciclo', $plano->ciclo ?? '') === 'MONTHLY' ? 'selected' : ''; ?>>Mensal</option>
                        <option value="YEARLY" <?php echo old('ciclo', $plano->ciclo ?? '') === 'YEARLY' ? 'selected' : ''; ?>>Anual</option>
                    </select>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="1" 
                               <?php echo old('ativo', $plano->ativo ?? 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="ativo">Plano Ativo</label>
                    </div>
                    <small class="text-muted">Planos inativos não aparecem para novos assinantes</small>
                </div>
            </div>
        </div>

        <!-- Preview do Plano -->
        <div class="card mt-3 border-primary">
            <div class="card-header bg-primary text-white">
                <i class="bx bx-show me-2"></i>Preview
            </div>
            <div class="card-body text-center">
                <h5 id="preview-nome" class="card-title"><?php echo esc($plano->nome ?? 'Nome do Plano'); ?></h5>
                <h2 id="preview-preco" class="text-primary mb-0">
                    R$ <span><?php echo number_format($plano->preco ?? 0, 2, ',', '.'); ?></span>
                </h2>
                <small id="preview-ciclo" class="text-muted">/mês</small>
                <hr>
                <ul id="preview-beneficios" class="list-unstyled text-start small">
                    <?php foreach ($plano->getBeneficios() ?? [] as $beneficio): ?>
                    <li><i class="bx bx-check text-success me-1"></i><?php echo esc($beneficio); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
