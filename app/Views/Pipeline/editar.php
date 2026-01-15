<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
.form-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    padding: 24px;
    margin-bottom: 20px;
}

.form-card h5 {
    color: #0d6efd;
    font-weight: 600;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e9ecef;
}

.temperatura-selector {
    display: flex;
    gap: 12px;
}

.temperatura-selector .btn-check:checked + .btn {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.temperatura-selector .btn {
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
}

.btn-temperatura-frio { 
    background: #e3f2fd; 
    color: #0277bd; 
    border-color: #90caf9;
}
.btn-temperatura-frio:hover,
.btn-check:checked + .btn-temperatura-frio { 
    background: #0277bd; 
    color: white; 
}

.btn-temperatura-morno { 
    background: #fff8e1; 
    color: #f57c00; 
    border-color: #ffcc80;
}
.btn-temperatura-morno:hover,
.btn-check:checked + .btn-temperatura-morno { 
    background: #f57c00; 
    color: white; 
}

.btn-temperatura-quente { 
    background: #ffebee; 
    color: #c62828; 
    border-color: #ef9a9a;
}
.btn-temperatura-quente:hover,
.btn-check:checked + .btn-temperatura-quente { 
    background: #c62828; 
    color: white; 
}
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-dark fw-bold"><?php echo $titulo; ?></div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('pipeline'); ?>">Pipeline</a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('pipeline/exibir/' . $lead->id); ?>"><?php echo esc($lead->codigo); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<?php if (session()->has('errors')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bx bx-error-circle me-2"></i>
        <?php foreach (session('errors') as $error): ?>
            <?php echo $error; ?><br>
        <?php endforeach; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<form action="<?php echo site_url('pipeline/atualizar'); ?>" method="post" id="formLead">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="id" value="<?php echo $lead->id; ?>">
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Dados Principais -->
            <div class="form-card">
                <h5><i class="bx bx-user me-2"></i>Dados do Lead</h5>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Nome / Razão Social <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="nome" 
                               class="form-control" 
                               value="<?php echo old('nome', $lead->nome); ?>" 
                               required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nome Fantasia</label>
                        <input type="text" 
                               name="nome_fantasia" 
                               class="form-control" 
                               value="<?php echo old('nome_fantasia', $lead->nome_fantasia); ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Tipo de Pessoa</label>
                        <select name="tipo_pessoa" class="form-select" id="tipoPessoa">
                            <option value="juridica" <?php echo old('tipo_pessoa', $lead->tipo_pessoa) === 'juridica' ? 'selected' : ''; ?>>Pessoa Jurídica</option>
                            <option value="fisica" <?php echo old('tipo_pessoa', $lead->tipo_pessoa) === 'fisica' ? 'selected' : ''; ?>>Pessoa Física</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" id="labelDocumento">CNPJ</label>
                        <input type="text" 
                               name="documento" 
                               id="documento"
                               class="form-control" 
                               value="<?php echo old('documento', $lead->documento); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Segmento</label>
                        <input type="text" 
                               name="segmento" 
                               class="form-control" 
                               value="<?php echo old('segmento', $lead->segmento); ?>"
                               placeholder="Ex: Loja de Games, Artista, etc">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">E-mail</label>
                        <input type="email" 
                               name="email" 
                               class="form-control" 
                               value="<?php echo old('email', $lead->email); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Telefone</label>
                        <input type="text" 
                               name="telefone" 
                               class="form-control telefone" 
                               value="<?php echo old('telefone', $lead->telefone); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Celular / WhatsApp</label>
                        <input type="text" 
                               name="celular" 
                               class="form-control celular" 
                               value="<?php echo old('celular', $lead->celular); ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Instagram</label>
                        <div class="input-group">
                            <span class="input-group-text">@</span>
                            <input type="text" 
                                   name="instagram" 
                                   class="form-control" 
                                   value="<?php echo old('instagram', $lead->instagram); ?>"
                                   placeholder="usuario">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Origem</label>
                        <select name="origem" class="form-select">
                            <option value="">Selecione...</option>
                            <?php 
                            $origens = ['Indicação', 'Site', 'Instagram', 'Evento anterior', 'WhatsApp', 'E-mail', 'Prospecção'];
                            foreach ($origens as $origem): 
                            ?>
                                <option value="<?php echo $origem; ?>" <?php echo old('origem', $lead->origem) === $origem ? 'selected' : ''; ?>><?php echo $origem; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Observações -->
            <div class="form-card">
                <h5><i class="bx bx-note me-2"></i>Observações</h5>
                <textarea name="observacoes" class="form-control" rows="4" placeholder="Anotações sobre o lead..."><?php echo old('observacoes', $lead->observacoes); ?></textarea>
            </div>

            <!-- Motivo da Perda (se aplicável) -->
            <?php if ($lead->etapa === 'perdido'): ?>
            <div class="form-card border border-danger">
                <h5 class="text-danger"><i class="bx bx-x-circle me-2"></i>Motivo da Perda</h5>
                <input type="text" 
                       name="motivo_perda" 
                       class="form-control" 
                       value="<?php echo old('motivo_perda', $lead->motivo_perda); ?>"
                       placeholder="Descreva o motivo...">
            </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <!-- Qualificação -->
            <div class="form-card">
                <h5><i class="bx bx-target-lock me-2"></i>Qualificação</h5>

                <div class="mb-4">
                    <label class="form-label">Temperatura do Lead</label>
                    <div class="temperatura-selector">
                        <input type="radio" class="btn-check" name="temperatura" id="tempFrio" value="frio" <?php echo old('temperatura', $lead->temperatura) === 'frio' ? 'checked' : ''; ?>>
                        <label class="btn btn-temperatura-frio" for="tempFrio">
                            <i class="bx bx-wind"></i> Frio
                        </label>
                        
                        <input type="radio" class="btn-check" name="temperatura" id="tempMorno" value="morno" <?php echo old('temperatura', $lead->temperatura) === 'morno' ? 'checked' : ''; ?>>
                        <label class="btn btn-temperatura-morno" for="tempMorno">
                            <i class="bx bx-sun"></i> Morno
                        </label>
                        
                        <input type="radio" class="btn-check" name="temperatura" id="tempQuente" value="quente" <?php echo old('temperatura', $lead->temperatura) === 'quente' ? 'checked' : ''; ?>>
                        <label class="btn btn-temperatura-quente" for="tempQuente">
                            <i class="bx bxs-flame"></i> Quente
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Valor Estimado</label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="text" 
                               name="valor_estimado" 
                               class="form-control money" 
                               value="<?php echo old('valor_estimado', number_format($lead->valor_estimado, 2, ',', '.')); ?>"
                               placeholder="0,00">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Próxima Ação</label>
                    <?php 
                    $proxAcao = old('proxima_acao', $lead->proxima_acao);
                    if ($proxAcao instanceof \CodeIgniter\I18n\Time) {
                        $proxAcao = $proxAcao->format('Y-m-d');
                    }
                    ?>
                    <input type="date" 
                           name="proxima_acao" 
                           class="form-control" 
                           value="<?php echo $proxAcao; ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Descrição da Ação</label>
                    <input type="text" 
                           name="proxima_acao_descricao" 
                           class="form-control" 
                           value="<?php echo old('proxima_acao_descricao', $lead->proxima_acao_descricao); ?>"
                           placeholder="Ex: Ligar para apresentar proposta">
                </div>
            </div>

            <!-- Atribuição -->
            <div class="form-card">
                <h5><i class="bx bx-user-check me-2"></i>Atribuição</h5>

                <div class="mb-3">
                    <label class="form-label">Vendedor Responsável</label>
                    <select name="vendedor_id" class="form-select">
                        <option value="">Selecione...</option>
                        <?php foreach ($vendedores as $vendedor): ?>
                            <option value="<?php echo $vendedor['id']; ?>" <?php echo old('vendedor_id', $lead->vendedor_id) == $vendedor['id'] ? 'selected' : ''; ?>>
                                <?php echo esc($vendedor['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Evento</label>
                    <select name="evento_id" class="form-select">
                        <option value="">Selecione...</option>
                        <?php foreach ($eventos as $evento): ?>
                            <option value="<?php echo $evento['id']; ?>" <?php echo old('evento_id', $lead->evento_id) == $evento['id'] ? 'selected' : ''; ?>>
                                <?php echo esc($evento['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Ações -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bx bx-save me-2"></i>Salvar Alterações
                </button>
                <a href="<?php echo site_url('pipeline/exibir/' . $lead->id); ?>" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-2"></i>Voltar
                </a>
            </div>
        </div>
    </div>
</form>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/jquery-mask-plugin@1.14.16/dist/jquery.mask.min.js"></script>
<script>
$(document).ready(function() {
    // Máscaras
    $('.telefone').mask('(00) 0000-0000');
    $('.celular').mask('(00) 00000-0000');
    $('.money').mask('#.##0,00', {reverse: true});

    // Alterna máscara CPF/CNPJ
    function atualizarMascaraDocumento() {
        var tipo = $('#tipoPessoa').val();
        var $doc = $('#documento');
        $doc.unmask();
        
        if (tipo === 'fisica') {
            $doc.mask('000.000.000-00');
            $('#labelDocumento').text('CPF');
        } else {
            $doc.mask('00.000.000/0000-00');
            $('#labelDocumento').text('CNPJ');
        }
    }

    $('#tipoPessoa').on('change', atualizarMascaraDocumento);
    atualizarMascaraDocumento();
});
</script>
<?php echo $this->endSection() ?>
