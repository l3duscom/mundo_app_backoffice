<!-- Formulário de Cupom -->
<input type="hidden" name="id" value="<?php echo isset($cupom) ? $cupom->id : ''; ?>">

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Código do Cupom <span class="text-danger">*</span></label>
        <input type="text" name="codigo" class="form-control text-uppercase" 
               value="<?php echo isset($cupom) ? esc($cupom->codigo) : ''; ?>" 
               placeholder="Ex: NATAL10, BLACKFRIDAY" maxlength="50" required>
        <div class="invalid-feedback"></div>
        <small class="text-muted">O código que o cliente irá digitar no checkout</small>
    </div>
    
    <div class="col-md-6 mb-3">
        <label class="form-label">Nome do Cupom <span class="text-danger">*</span></label>
        <input type="text" name="nome" class="form-control" 
               value="<?php echo isset($cupom) ? esc($cupom->nome) : ''; ?>" 
               placeholder="Nome interno para identificação" required>
        <div class="invalid-feedback"></div>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Tipo de Desconto <span class="text-danger">*</span></label>
        <select name="tipo" class="form-select" id="tipoDesconto" required>
            <option value="percentual" <?php echo (isset($cupom) && $cupom->tipo === 'percentual') ? 'selected' : ''; ?>>Percentual (%)</option>
            <option value="fixo" <?php echo (isset($cupom) && $cupom->tipo === 'fixo') ? 'selected' : ''; ?>>Valor Fixo (R$)</option>
        </select>
        <div class="invalid-feedback"></div>
    </div>
    
    <div class="col-md-4 mb-3">
        <label class="form-label">Valor do Desconto <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text" id="prefixoDesconto">%</span>
            <input type="number" name="desconto" class="form-control" 
                   value="<?php echo isset($cupom) ? $cupom->desconto : ''; ?>" 
                   placeholder="0" step="0.01" min="0.01" required>
        </div>
        <div class="invalid-feedback"></div>
    </div>
    
    <div class="col-md-4 mb-3">
        <label class="form-label">Valor Mínimo do Pedido</label>
        <div class="input-group">
            <span class="input-group-text">R$</span>
            <input type="number" name="valor_minimo" class="form-control" 
                   value="<?php echo isset($cupom) ? $cupom->valor_minimo : '0'; ?>" 
                   placeholder="0.00" step="0.01" min="0">
        </div>
        <small class="text-muted">Deixe 0 para não ter mínimo</small>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Quantidade Total</label>
        <input type="number" name="quantidade_total" class="form-control" 
               value="<?php echo isset($cupom) && $cupom->quantidade_total ? $cupom->quantidade_total : ''; ?>" 
               placeholder="Ilimitado" min="1">
        <small class="text-muted">Deixe vazio para quantidade ilimitada</small>
    </div>
    
    <div class="col-md-4 mb-3">
        <label class="form-label">Uso por Usuário</label>
        <input type="number" name="uso_por_usuario" class="form-control" 
               value="<?php echo isset($cupom) ? $cupom->uso_por_usuario : '1'; ?>" 
               placeholder="1" min="1">
        <small class="text-muted">Quantas vezes cada usuário pode usar</small>
    </div>
    
    <div class="col-md-4 mb-3">
        <label class="form-label">Evento</label>
        <?php 
        // Determina o evento a ser usado (do cupom existente ou do contexto)
        $eventoSelecionado = isset($cupom) ? $cupom->evento_id : ($evento_id ?? '');
        ?>
        <input type="hidden" name="evento_id" value="<?php echo $eventoSelecionado; ?>">
        <input type="text" class="form-control" readonly 
               value="<?php 
                   foreach ($eventos as $evento) {
                       if ($evento->id == $eventoSelecionado) {
                           echo esc($evento->nome);
                           break;
                       }
                   }
               ?>">
        <small class="text-muted">Evento do contexto atual</small>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Data de Início</label>
        <input type="date" name="data_inicio" class="form-control" 
               value="<?php echo isset($cupom) && $cupom->data_inicio ? date('Y-m-d', strtotime($cupom->data_inicio)) : ''; ?>">
        <small class="text-muted">Deixe vazio para começar imediatamente</small>
    </div>
    
    <div class="col-md-4 mb-3">
        <label class="form-label">Data de Término</label>
        <input type="date" name="data_fim" class="form-control" 
               value="<?php echo isset($cupom) && $cupom->data_fim ? date('Y-m-d', strtotime($cupom->data_fim)) : ''; ?>">
        <small class="text-muted">Deixe vazio para não expirar</small>
    </div>
    
    <div class="col-md-4 mb-3">
        <label class="form-label">Status</label>
        <div class="form-check form-switch mt-2">
            <input class="form-check-input" type="checkbox" name="ativo" id="ativo" value="1"
                   <?php echo (!isset($cupom) || $cupom->ativo) ? 'checked' : ''; ?>>
            <label class="form-check-label" for="ativo">Ativo</label>
        </div>
    </div>
</div>

<script>
// Alterar prefixo conforme tipo de desconto
document.getElementById('tipoDesconto').addEventListener('change', function() {
    var prefixo = document.getElementById('prefixoDesconto');
    prefixo.textContent = this.value === 'fixo' ? 'R$' : '%';
});

// Inicializar prefixo
document.addEventListener('DOMContentLoaded', function() {
    var tipo = document.getElementById('tipoDesconto').value;
    document.getElementById('prefixoDesconto').textContent = tipo === 'fixo' ? 'R$' : '%';
});
</script>
