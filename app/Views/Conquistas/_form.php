<!-- Formulário de Conquista -->
<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="event_id" class="form-label">Evento <small class="text-muted">(opcional)</small></label>
            <select name="event_id" id="event_id" class="form-select">
                <option value="">🌐 Conquista Global (todos os eventos)</option>
                <?php foreach ($eventos as $evento): ?>
                    <option value="<?php echo $evento->id; ?>" <?php echo (isset($conquista) && $conquista->event_id == $evento->id) || (isset($evento_id) && $evento_id == $evento->id) ? 'selected' : ''; ?>>
                        <?php echo esc($evento->nome); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="nome_conquista" class="form-label">Nome da Conquista <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="nome_conquista" id="nome_conquista" 
                   value="<?php echo isset($conquista) ? esc($conquista->nome_conquista) : ''; ?>" 
                   placeholder="Ex: Primeira Participação" required>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição</label>
            <textarea class="form-control" name="descricao" id="descricao" rows="3" 
                      placeholder="Descrição detalhada da conquista"><?php echo isset($conquista) ? esc($conquista->descricao) : ''; ?></textarea>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="mb-3">
            <label for="pontos" class="form-label">Pontos <span class="text-danger">*</span></label>
            <input type="number" class="form-control" name="pontos" id="pontos" 
                   value="<?php echo isset($conquista) ? $conquista->pontos : '10'; ?>" 
                   min="0" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="nivel" class="form-label">Nível <span class="text-danger">*</span></label>
            <select name="nivel" id="nivel" class="form-select" required>
                <?php 
                $niveis = ['BRONZE', 'PRATA', 'OURO', 'PLATINA', 'DIAMANTE'];
                $nivelAtual = isset($conquista) ? $conquista->nivel : 'BRONZE';
                foreach ($niveis as $nivel): 
                ?>
                    <option value="<?php echo $nivel; ?>" <?php echo $nivelAtual == $nivel ? 'selected' : ''; ?>>
                        <?php echo $nivel; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
            <select name="status" id="status" class="form-select" required>
                <?php 
                $statusList = ['ATIVA' => 'Ativa', 'INATIVA' => 'Inativa', 'BLOQUEADA' => 'Bloqueada'];
                $statusAtual = isset($conquista) ? $conquista->status : 'ATIVA';
                foreach ($statusList as $key => $label): 
                ?>
                    <option value="<?php echo $key; ?>" <?php echo $statusAtual == $key ? 'selected' : ''; ?>>
                        <?php echo $label; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<!-- Legenda de níveis -->
<div class="row mt-3">
    <div class="col-12">
        <div class="alert alert-light border">
            <h6 class="mb-2"><i class="bx bx-info-circle"></i> Referência de Níveis</h6>
            <div class="d-flex flex-wrap gap-3">
                <span><span class="badge" style="background: linear-gradient(135deg, #CD7F32, #8B4513); color: white;">BRONZE</span> 10-20 pts</span>
                <span><span class="badge" style="background: linear-gradient(135deg, #C0C0C0, #808080); color: white;">PRATA</span> 25-40 pts</span>
                <span><span class="badge" style="background: linear-gradient(135deg, #FFD700, #DAA520); color: #333;">OURO</span> 50-75 pts</span>
                <span><span class="badge" style="background: linear-gradient(135deg, #E5E4E2, #BCC6CC); color: #333;">PLATINA</span> 100-150 pts</span>
                <span><span class="badge" style="background: linear-gradient(135deg, #B9F2FF, #7DF9FF); color: #333;">DIAMANTE</span> 200+ pts</span>
            </div>
        </div>
    </div>
</div>
