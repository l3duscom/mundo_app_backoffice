<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow radius-10">
            <div class="card-body">
                <div id="response">
                    <?php if (session()->has('sucesso')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bx bx-check-circle me-2"></i>
                            <strong>Sucesso!</strong> <?= session('sucesso') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (session()->has('atencao')): ?>
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="bx bx-error-circle me-2"></i>
                            <strong>Atenção!</strong> <?= session('atencao') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (session()->has('erro')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bx bx-x-circle me-2"></i>
                            <strong>Erro!</strong> <?= session('erro') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                </div>

                <?php echo form_open_multipart('Concursos/atualizar_inscricao_cosplay_apresentacao', ['id' => 'form-edicao']) ?>

                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" id="csrf_token_field">
                <input type="hidden" name="inscricao_id" value="<?= $inscricao->id ?>">
                <input type="hidden" name="concurso_id" value="<?= $concurso->id ?>">
                
                <center>
                    <h4>Editar Inscrição - <?= $concurso->nome ?></h4>
                    <p class="text-muted">Código: <?= $inscricao->codigo ?></p>
                    <hr>
                </center>

                <div class="alert alert-info fade show" role="alert">
                    <i class="bx bx-info-circle me-2"></i>
                    <strong>Importante:</strong> Ao salvar as alterações, o status da sua inscrição será alterado para "Inscrição Editada" e passará por uma nova avaliação.
                </div>

                <div class="row">
                    <div class="form-group col-md-6">
                        <label class="form-control-label text-muted">Nome Social</label>
                        <input type="text" name="nome_social" value="<?= esc($inscricao->nome_social) ?>" class="form-control mb-2 shadow" style="padding:13px;" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-control-label text-muted">Nome Completo (RG)</label>
                        <input type="text" name="nome" value="<?= esc($inscricao->nome) ?>" class="form-control mb-2 shadow" style="padding:13px;" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-control-label">Celular/Whatsapp</label>
                        <input type="text" name="telefone" value="<?= esc($inscricao->telefone) ?>" class="form-control sp_celphones mb-2 shadow" style="padding:13px" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-control-label text-muted">Motivação</label>
                        <input type="text" name="motivacao" value="<?= esc($inscricao->motivacao) ?>" class="form-control mb-2 shadow" style="padding:13px;" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="form-control-label">Personagem</label>
                        <input type="text" name="personagem" value="<?= esc($inscricao->personagem) ?>" class="form-control mb-2 shadow" style="padding:13px;" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="form-control-label">Obra/Mídia</label>
                        <input type="text" name="obra" value="<?= esc($inscricao->obra) ?>" class="form-control mb-2 shadow" style="padding:13px;" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="form-control-label">Gênero da Obra</label>
                        <select name="genero" class="form-control mb-2 shadow" required>
                            <option value="Animação(Anime, Filme de animação, Cartoon, entre outros)" <?= strpos($inscricao->genero, 'Animação') !== false ? 'selected' : '' ?>>Animação</option>
                            <option value="Game" <?= $inscricao->genero == 'Game' ? 'selected' : '' ?>>Game</option>
                            <option value="Filme" <?= $inscricao->genero == 'Filme' ? 'selected' : '' ?>>Filme</option>
                            <option value="Série" <?= $inscricao->genero == 'Série' ? 'selected' : '' ?>>Série</option>
                            <option value="Mangá/Manwa" <?= $inscricao->genero == 'Mangá/Manwa' ? 'selected' : '' ?>>Mangá/Manwa</option>
                            <option value="HQ" <?= $inscricao->genero == 'HQ' ? 'selected' : '' ?>>HQ</option>
                            <option value="Livro" <?= $inscricao->genero == 'Livro' ? 'selected' : '' ?>>Livro</option>
                        </select>
                    </div>
                    <div class="form-group col-md-12">
                        <label class="form-control-label text-muted">Observações</label>
                        <input type="text" name="observacoes" value="<?= esc($inscricao->observacoes) ?>" class="form-control mb-2 shadow" style="padding:13px;" required>
                    </div>

                    <div class="col-12 mt-3">
                        <div class="card shadow radius-10">
                            <div class="card-body">
                                <span style="font-weight: 600; font-size:16px">Arquivos (opcional)</span>
                                <hr>
                                
                                <div id="file-validation-alert" class="alert alert-danger d-none" role="alert"></div>
                                
                                <div class="form-group col-md-12 mb-3">
                                    <label class="form-control-label">Imagem de referência atual:</label>
                                    <a href="<?= site_url("concursos/imagem/{$inscricao->referencia}") ?>" target="_blank" class="btn btn-sm btn-outline-primary">Visualizar</a>
                                    <input type="file" name="referencia" class="form-control mt-2" accept=".jpg,.jpeg,.png">
                                    <small class="text-muted">Formatos: JPG, PNG. Máx. 50MB</small>
                                </div>
                                <div class="form-group col-md-12 mb-3">
                                    <label class="form-control-label">Vídeo LED atual:</label>
                                    <a href="<?= site_url("concursos/imagem/{$inscricao->video_led}") ?>" target="_blank" class="btn btn-sm btn-outline-primary">Visualizar</a>
                                    <input type="file" name="video_led" class="form-control mt-2" accept=".mp4">
                                    <small class="text-muted">Formato: MP4. Máx. 100MB</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mb-0 mt-3">
                        <button id="btn-salvar" type="submit" class="btn btn-primary btn-lg mt-0">
                            <span id="btn-text">Salvar Alterações</span>
                            <span id="btn-spinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                        </button>
                        <a href="<?= site_url('concursos/my') ?>" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </div>

                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script src="<?php echo site_url('recursos/vendor/mask/jquery.mask.min.js') ?>"></script>
<script src="<?php echo site_url('recursos/vendor/mask/app.js') ?>"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-edicao');
    const btn = document.getElementById('btn-salvar');
    const btnText = document.getElementById('btn-text');
    const btnSpinner = document.getElementById('btn-spinner');
    const alertDiv = document.getElementById('file-validation-alert');
    
    const MAX_TOTAL_SIZE = 95 * 1024 * 1024;
    const ALLOWED_IMAGE = ['image/jpeg', 'image/png'];
    const ALLOWED_VIDEO = ['video/mp4'];
    
    function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    function validateFiles() {
        let totalSize = 0;
        const errors = [];
        
        const fileInputs = form.querySelectorAll('input[type="file"]');
        fileInputs.forEach(input => {
            if (input.files.length > 0) {
                const file = input.files[0];
                totalSize += file.size;
                
                if (input.name === 'referencia' && !ALLOWED_IMAGE.includes(file.type)) {
                    errors.push('A imagem de referência deve estar no formato JPG ou PNG.');
                }
                if (input.name === 'video_led' && !ALLOWED_VIDEO.includes(file.type)) {
                    errors.push('O vídeo LED deve estar no formato MP4.');
                }
            }
        });
        
        if (totalSize > MAX_TOTAL_SIZE) {
            errors.push(`O tamanho total dos arquivos (${formatBytes(totalSize)}) excede o limite de 95MB.`);
        }
        
        return errors;
    }
    
    if (form && btn) {
        form.addEventListener('submit', function(e) {
            const errors = validateFiles();
            
            if (errors.length > 0) {
                e.preventDefault();
                alertDiv.innerHTML = '<strong>Erro nos arquivos:</strong><ul>' + errors.map(err => '<li>' + err + '</li>').join('') + '</ul>';
                alertDiv.classList.remove('d-none');
                alertDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
                return false;
            }
            
            alertDiv.classList.add('d-none');
            btn.disabled = true;
            btnText.textContent = 'Processando...';
            btnSpinner.classList.remove('d-none');
            
            return true;
        });
    }
});
</script>

<?php echo $this->endSection() ?>
