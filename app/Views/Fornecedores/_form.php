<div class="row">

    <!-- Seção: Dados da Empresa -->
    <div class="col-12 mb-3">
        <h6 class="text-primary"><i class="bx bx-building me-2"></i>Dados da Empresa</h6>
        <hr class="mt-0">
    </div>

    <div class="form-group col-md-8 mb-3">
        <label class="form-control-label">Razão social <span class="text-danger">*</span></label>
        <input type="text" name="razao" placeholder="Insira a razão social" class="form-control" value="<?php echo esc($fornecedor->razao ?? ''); ?>">
    </div>

    <div class="form-group col-md-4 mb-3">
        <label class="form-control-label">Categoria</label>
        <div class="input-group">
            <select name="categoria_id" id="categoria_id" class="form-select">
                <option value="">Selecione uma categoria</option>
                <?php if (isset($categorias)): foreach ($categorias as $cat): ?>
                    <option value="<?php echo $cat->id; ?>" <?php echo (isset($fornecedor->categoria_id) && $fornecedor->categoria_id == $cat->id) ? 'selected' : ''; ?>>
                        <?php echo esc($cat->nome); ?>
                    </option>
                <?php endforeach; endif; ?>
            </select>
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalNovaCategoria" title="Adicionar nova categoria">
                <i class="bx bx-plus"></i>
            </button>
        </div>
    </div>

    <div class="form-group col-md-4 mb-3">
        <label class="form-control-label">CNPJ <span class="text-danger">*</span></label>
        <input type="text" name="cnpj" placeholder="Insira o CNPJ" class="form-control cnpj" value="<?php echo esc($fornecedor->cnpj ?? ''); ?>">
    </div>

    <div class="form-group col-md-4 mb-3">
        <label class="form-control-label">Inscrição estadual</label>
        <input type="text" name="ie" placeholder="Insira a I.E." class="form-control" value="<?php echo esc($fornecedor->ie ?? ''); ?>">
    </div>

    <div class="form-group col-md-4 mb-3">
        <label class="form-control-label">E-mail</label>
        <input type="email" name="email" placeholder="email@empresa.com" class="form-control" value="<?php echo esc($fornecedor->email ?? ''); ?>">
    </div>

    <!-- Seção: Contato Responsável -->
    <div class="col-12 mb-3 mt-4">
        <h6 class="text-primary"><i class="bx bx-user me-2"></i>Contato Responsável</h6>
        <hr class="mt-0">
    </div>

    <div class="form-group col-md-4 mb-3">
        <label class="form-control-label">Telefone Principal <span class="text-danger">*</span></label>
        <input type="text" name="telefone" placeholder="(00) 00000-0000" class="form-control phone_with_ddd" value="<?php echo esc($fornecedor->telefone ?? ''); ?>">
    </div>

    <div class="form-group col-md-4 mb-3">
        <label class="form-control-label">Nome do Contato</label>
        <input type="text" name="nome_contato" placeholder="Nome do responsável" class="form-control" value="<?php echo esc($fornecedor->nome_contato ?? ''); ?>">
    </div>

    <div class="form-group col-md-4 mb-3">
        <label class="form-control-label">Telefone do Contato</label>
        <input type="text" name="telefone_contato" placeholder="(00) 00000-0000" class="form-control phone_with_ddd" value="<?php echo esc($fornecedor->telefone_contato ?? ''); ?>">
    </div>

    <!-- Seção: Endereço -->
    <div class="col-12 mb-3 mt-4">
        <h6 class="text-primary"><i class="bx bx-map me-2"></i>Endereço</h6>
        <hr class="mt-0">
    </div>

    <div class="form-group col-md-3 mb-3">
        <label class="form-control-label">CEP</label>
        <input type="text" name="cep" placeholder="00000-000" class="form-control cep" value="<?php echo esc($fornecedor->cep ?? ''); ?>">
        <div id="cep"></div>
    </div>

    <div class="form-group col-md-6 mb-3">
        <label class="form-control-label">Endereço</label>
        <input type="text" name="endereco" placeholder="Insira o endereço" class="form-control" value="<?php echo esc($fornecedor->endereco ?? ''); ?>">
    </div>

    <div class="form-group col-md-3 mb-3">
        <label class="form-control-label">Nº</label>
        <input type="text" name="numero" placeholder="Nº" class="form-control" value="<?php echo esc($fornecedor->numero ?? ''); ?>">
    </div>

    <div class="form-group col-md-4 mb-3">
        <label class="form-control-label">Bairro</label>
        <input type="text" name="bairro" placeholder="Insira o Bairro" class="form-control" value="<?php echo esc($fornecedor->bairro ?? ''); ?>">
    </div>

    <div class="form-group col-md-6 mb-3">
        <label class="form-control-label">Cidade</label>
        <input type="text" name="cidade" placeholder="Insira a Cidade" class="form-control" value="<?php echo esc($fornecedor->cidade ?? ''); ?>">
    </div>

    <div class="form-group col-md-2 mb-3">
        <label class="form-control-label">Estado</label>
        <input type="text" name="estado" placeholder="UF" class="form-control" value="<?php echo esc($fornecedor->estado ?? ''); ?>">
    </div>

    <!-- Seção: Dados Bancários -->
    <div class="col-12 mb-3 mt-4">
        <h6 class="text-primary"><i class="bx bx-credit-card me-2"></i>Dados Bancários</h6>
        <hr class="mt-0">
    </div>

    <div class="form-group col-md-3 mb-3">
        <label class="form-control-label">Banco</label>
        <input type="text" name="banco" placeholder="Nome do banco" class="form-control" value="<?php echo esc($fornecedor->banco ?? ''); ?>">
    </div>

    <div class="form-group col-md-2 mb-3">
        <label class="form-control-label">Agência</label>
        <input type="text" name="agencia" placeholder="0000" class="form-control" value="<?php echo esc($fornecedor->agencia ?? ''); ?>">
    </div>

    <div class="form-group col-md-3 mb-3">
        <label class="form-control-label">Conta</label>
        <input type="text" name="conta" placeholder="00000-0" class="form-control" value="<?php echo esc($fornecedor->conta ?? ''); ?>">
    </div>

    <div class="form-group col-md-4 mb-3">
        <label class="form-control-label">Chave PIX</label>
        <input type="text" name="pix" placeholder="CPF, E-mail, Telefone ou Chave Aleatória" class="form-control" value="<?php echo esc($fornecedor->pix ?? ''); ?>">
    </div>

    <!-- Seção: Observações -->
    <div class="col-12 mb-3 mt-4">
        <h6 class="text-primary"><i class="bx bx-note me-2"></i>Observações</h6>
        <hr class="mt-0">
    </div>

    <div class="form-group col-md-12 mb-3">
        <label class="form-control-label">Observações</label>
        <textarea name="observacoes" class="form-control" rows="3" placeholder="Informações adicionais sobre o fornecedor..."><?php echo esc($fornecedor->observacoes ?? ''); ?></textarea>
    </div>

</div>

<div class="form-check form-switch mb-3">
    <input type="hidden" name="ativo" value="0">
    <input type="checkbox" name="ativo" value="1" class="form-check-input" id="ativo" <?php if (!isset($fornecedor->id) || $fornecedor->ativo == true): ?> checked <?php endif; ?>>
    <label class="form-check-label" for="ativo">Fornecedor ativo</label>
</div>

<!-- Modal Nova Categoria -->
<div class="modal fade" id="modalNovaCategoria" tabindex="-1" aria-labelledby="modalNovaCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNovaCategoriaLabel"><i class="bx bx-folder-plus me-2"></i>Nova Categoria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="novaCategoriaNome" class="form-label">Nome da Categoria <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="novaCategoriaNome" placeholder="Ex: Alimentação, Áudio e Vídeo, Decoração...">
                </div>
                <div id="erroNovaCategoria" class="alert alert-danger d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarCategoria">
                    <i class="bx bx-save me-1"></i>Salvar Categoria
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('btnSalvarCategoria').addEventListener('click', function() {
        var nome = document.getElementById('novaCategoriaNome').value.trim();
        var erroDiv = document.getElementById('erroNovaCategoria');
        
        if (!nome) {
            erroDiv.textContent = 'Informe o nome da categoria';
            erroDiv.classList.remove('d-none');
            return;
        }
        
        erroDiv.classList.add('d-none');
        
        fetch('<?php echo site_url("fornecedores/cadastrarCategoria"); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'nome=' + encodeURIComponent(nome) + '&<?php echo csrf_token(); ?>=<?php echo csrf_hash(); ?>'
        })
        .then(response => response.json())
        .then(data => {
            if (data.erro) {
                erroDiv.textContent = data.erro;
                erroDiv.classList.remove('d-none');
            } else if (data.categoria) {
                // Adiciona a nova categoria no select
                var select = document.getElementById('categoria_id');
                var option = new Option(data.categoria.nome, data.categoria.id, true, true);
                select.add(option);
                
                // Fecha o modal
                var modal = bootstrap.Modal.getInstance(document.getElementById('modalNovaCategoria'));
                modal.hide();
                
                // Limpa o campo
                document.getElementById('novaCategoriaNome').value = '';
            }
        })
        .catch(error => {
            erroDiv.textContent = 'Erro ao cadastrar categoria';
            erroDiv.classList.remove('d-none');
        });
    });
});
</script>