<?php echo $this->extend('Layout/externo'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>

<style>
/* Wizard Steps */
.wizard-steps {
    display: flex;
    justify-content: space-between;
    margin-bottom: 2rem;
    position: relative;
}

.wizard-steps::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 0;
    right: 0;
    height: 3px;
    background: #e9ecef;
    z-index: 0;
}

.wizard-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 1;
    flex: 1;
}

.step-number {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
    transition: all 0.3s ease;
    border: 3px solid #e9ecef;
}

.step-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-align: center;
    font-weight: 500;
    transition: all 0.3s ease;
}

.wizard-step.active .step-number {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: #667eea;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.wizard-step.active .step-label {
    color: #667eea;
    font-weight: 600;
}

.wizard-step.completed .step-number {
    background: #28a745;
    color: white;
    border-color: #28a745;
}

.wizard-step.completed .step-label {
    color: #28a745;
}

/* Form Steps */
.form-step {
    display: none;
    animation: fadeIn 0.3s ease;
}

.form-step.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Step Header */
.step-header {
    text-align: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f0f0f0;
}

.step-header h4 {
    color: #333;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.step-header p {
    color: #6c757d;
    margin-bottom: 0;
    font-size: 0.9rem;
}

/* Form Fields */
.form-floating-custom {
    position: relative;
    margin-bottom: 1.5rem;
}

.form-floating-custom label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
    display: block;
}

.form-floating-custom .form-control,
.form-floating-custom .form-select {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-floating-custom .form-control:focus,
.form-floating-custom .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.form-floating-custom .help-text {
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 0.25rem;
    display: flex;
    align-items: flex-start;
    gap: 0.25rem;
}

.form-floating-custom .help-text i {
    margin-top: 2px;
}

/* File Upload */
.file-upload-wrapper {
    border: 2px dashed #e9ecef;
    border-radius: 10px;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.3s ease;
    background: #fafbfc;
}

.file-upload-wrapper:hover {
    border-color: #667eea;
    background: #f8f9ff;
}

.file-upload-wrapper input[type="file"] {
    display: none;
}

.file-upload-wrapper label {
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.file-upload-wrapper .upload-icon {
    font-size: 2.5rem;
    color: #667eea;
}

.file-upload-wrapper .file-name {
    color: #28a745;
    font-weight: 600;
    display: none;
}

.file-upload-wrapper.has-file .file-name {
    display: block;
}

/* Navigation Buttons */
.wizard-nav {
    display: flex;
    justify-content: space-between;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 2px solid #f0f0f0;
}

.btn-wizard {
    padding: 0.75rem 2rem;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-wizard-prev {
    background: #f8f9fa;
    color: #495057;
    border: 2px solid #e9ecef;
}

.btn-wizard-prev:hover {
    background: #e9ecef;
    color: #333;
}

.btn-wizard-next {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
}

.btn-wizard-next:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    color: white;
}

.btn-wizard-submit {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border: none;
}

.btn-wizard-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
    color: white;
}

/* Review Section */
.review-section {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1rem;
}

.review-section h6 {
    color: #667eea;
    font-weight: 600;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e9ecef;
}

.review-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}

.review-item:last-child {
    border-bottom: none;
}

.review-item .label {
    color: #6c757d;
    font-size: 0.85rem;
}

.review-item .value {
    color: #333;
    font-weight: 600;
    font-size: 0.9rem;
    text-align: right;
}

/* Contest Header */
.contest-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 15px 15px 0 0;
    text-align: center;
    margin: -1.25rem -1.25rem 2rem -1.25rem;
}

.contest-header h3 {
    margin-bottom: 0.5rem;
    font-weight: 700;
}

.contest-header p {
    opacity: 0.9;
    margin-bottom: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .wizard-steps {
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .wizard-step {
        flex: 0 0 45%;
    }
    
    .step-label {
        font-size: 0.7rem;
    }
    
    .step-number {
        width: 35px;
        height: 35px;
        font-size: 0.9rem;
    }
}
</style>

<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>


<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">
        <div class="card shadow-lg radius-10 border-0">
            <div class="card-body p-4">
                
                <!-- Contest Header -->
                <div class="contest-header">
                    <h3><i class="bx bx-music me-2"></i><?= $concurso->nome ?></h3>
                    <p>Preencha o formulário abaixo para realizar sua inscrição</p>
                </div>

                <!-- Alerts -->
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

                <!-- Wizard Steps Indicator -->
                <div class="wizard-steps">
                    <div class="wizard-step active" data-step="1">
                        <div class="step-number">1</div>
                        <span class="step-label">Dados Pessoais</span>
                    </div>
                    <div class="wizard-step" data-step="2">
                        <div class="step-number">2</div>
                        <span class="step-label">Apresentação</span>
                    </div>
                    <div class="wizard-step" data-step="3">
                        <div class="step-number">3</div>
                        <span class="step-label">Arquivos</span>
                    </div>
                    <div class="wizard-step" data-step="4">
                        <div class="step-number">4</div>
                        <span class="step-label">Revisão</span>
                    </div>
                </div>

                <!-- Form -->
                <?php echo form_open_multipart('Concursos/registrar_inscricao_kpop_open', ['id' => 'form-inscricao']) ?>
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" id="csrf_token_field">
                <input type="hidden" name="concurso_id" value="<?= $concurso->id ?>">

                <!-- Step 1: Dados Pessoais -->
                <div class="form-step active" data-step="1">
                    <div class="step-header">
                        <h4><i class="bx bx-user me-2"></i>Dados Pessoais</h4>
                        <p>Informe seus dados para identificação</p>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-floating-custom">
                                <label><i class="bx bx-envelope me-1"></i> E-mail</label>
                                <input type="email" name="email" id="email" placeholder="seu@email.com" class="form-control" required>
                                <span class="help-text"><i class="bx bx-info-circle"></i> Usado para confirmação e acesso ao sistema</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating-custom">
                                <label><i class="bx bx-user-circle me-1"></i> Nome Social</label>
                                <input type="text" name="nome_social" id="nome_social" placeholder="Como você quer ser chamado" class="form-control" required>
                                <span class="help-text"><i class="bx bx-info-circle"></i> É o nome que será divulgado</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating-custom">
                                <label><i class="bx bx-id-card me-1"></i> Nome Completo (RG)</label>
                                <input type="text" name="nome" id="nome" placeholder="Seu nome completo" class="form-control" required>
                                <span class="help-text"><i class="bx bx-info-circle"></i> Para conferência com documento oficial</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating-custom">
                                <label><i class="bx bx-phone me-1"></i> Celular/WhatsApp</label>
                                <input type="text" name="telefone" id="telefone" placeholder="(00) 00000-0000" class="form-control sp_celphones" required>
                                <span class="help-text"><i class="bx bx-info-circle"></i> Contato sobre sua participação</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating-custom">
                                <label><i class="bx bx-credit-card me-1"></i> CPF</label>
                                <input type="text" name="cpf" id="cpf" placeholder="000.000.000-00" class="form-control cpf" required>
                                <span class="help-text"><i class="bx bx-info-circle"></i> Para identificação (não divulgado)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Dados da Apresentação -->
                <div class="form-step" data-step="2">
                    <div class="step-header">
                        <h4><i class="bx bx-microphone me-2"></i>Dados da Apresentação</h4>
                        <p>Informações sobre sua apresentação</p>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-floating-custom">
                                <label><i class="bx bx-category me-1"></i> Categoria</label>
                                <select name="categoria" id="categoria" class="form-select" required>
                                    <option value="">Selecione...</option>
                                    <option value="solo">Solo</option>
                                    <option value="dupla">Dupla</option>
                                    <option value="grupo">Grupo</option>
                                </select>
                                <span class="help-text"><i class="bx bx-info-circle"></i> Qual modalidade você irá competir?</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating-custom" id="grupo-field" style="display: none;">
                                <label><i class="bx bx-group me-1"></i> Nome do Grupo/Dupla</label>
                                <input type="text" name="grupo" id="grupo" placeholder="Nome do grupo" class="form-control">
                                <span class="help-text"><i class="bx bx-info-circle"></i> Nome para divulgação</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating-custom" id="integrantes-field" style="display: none;">
                                <label><i class="bx bx-user-plus me-1"></i> Qtd. Integrantes</label>
                                <input type="number" name="integrantes" id="integrantes" placeholder="2" class="form-control" min="1" max="20">
                                <span class="help-text"><i class="bx bx-info-circle"></i> Quantos membros?</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating-custom">
                                <label><i class="bx bx-video me-1"></i> Vídeo de Apresentação (Link)</label>
                                <input type="url" name="video_apresentacao" id="video_apresentacao" placeholder="https://youtube.com/..." class="form-control" required>
                                <span class="help-text"><i class="bx bx-info-circle"></i> Link de vídeo anterior seu ou do grupo (YouTube, Drive, etc.) - Usado para triagem</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Arquivos -->
                <div class="form-step" data-step="3">
                    <div class="step-header">
                        <h4><i class="bx bx-folder-open me-2"></i>Arquivos</h4>
                        <p>Faça upload dos arquivos necessários</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating-custom">
                                <label><i class="bx bx-image me-1"></i> Imagem de Referência</label>
                                <div class="file-upload-wrapper" id="referencia-wrapper">
                                    <label for="referencia">
                                        <i class="bx bx-cloud-upload upload-icon"></i>
                                        <span class="upload-text">Clique para enviar imagem</span>
                                        <span class="file-name" id="referencia-name"></span>
                                        <small class="text-muted">JPG ou PNG (máx. 50MB)</small>
                                    </label>
                                    <input type="file" name="referencia" id="referencia" accept=".jpg,.jpeg,.png" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating-custom">
                                <label><i class="bx bx-music me-1"></i> Arquivo de Música</label>
                                <div class="file-upload-wrapper" id="musica-wrapper">
                                    <label for="musica">
                                        <i class="bx bx-cloud-upload upload-icon"></i>
                                        <span class="upload-text">Clique para enviar música</span>
                                        <span class="file-name" id="musica-name"></span>
                                        <small class="text-muted">MP3 (máx. 50MB)</small>
                                    </label>
                                    <input type="file" name="musica" id="musica" accept=".mp3" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating-custom">
                                <label><i class="bx bx-text me-1"></i> Nome da Música</label>
                                <input type="text" name="nome_musica" id="nome_musica" placeholder="Ex: Dynamite - BTS" class="form-control" required>
                                <span class="help-text"><i class="bx bx-info-circle"></i> Nome da música e artista que será usada</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating-custom">
                                <label><i class="bx bx-video-recording me-1"></i> Vídeo LED <a href="https://www.youtube.com/watch?v=gSoFw92w-zo" target="_blank" class="text-primary">(Ver Exemplo)</a></label>
                                <div class="file-upload-wrapper" id="video_led-wrapper">
                                    <label for="video_led">
                                        <i class="bx bx-cloud-upload upload-icon"></i>
                                        <span class="upload-text">Clique para enviar vídeo LED</span>
                                        <span class="file-name" id="video_led-name"></span>
                                        <small class="text-muted">MP4 (máx. 100MB)</small>
                                    </label>
                                    <input type="file" name="video_led" id="video_led" accept=".mp4" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Revisão -->
                <div class="form-step" data-step="4">
                    <div class="step-header">
                        <h4><i class="bx bx-check-double me-2"></i>Revisão Final</h4>
                        <p>Confira seus dados antes de enviar</p>
                    </div>

                    <div class="review-section">
                        <h6><i class="bx bx-user me-1"></i> Dados Pessoais</h6>
                        <div class="review-item">
                            <span class="label">E-mail</span>
                            <span class="value" id="review-email">-</span>
                        </div>
                        <div class="review-item">
                            <span class="label">Nome Social</span>
                            <span class="value" id="review-nome_social">-</span>
                        </div>
                        <div class="review-item">
                            <span class="label">Nome Completo</span>
                            <span class="value" id="review-nome">-</span>
                        </div>
                        <div class="review-item">
                            <span class="label">Telefone</span>
                            <span class="value" id="review-telefone">-</span>
                        </div>
                        <div class="review-item">
                            <span class="label">CPF</span>
                            <span class="value" id="review-cpf">-</span>
                        </div>
                    </div>

                    <div class="review-section">
                        <h6><i class="bx bx-microphone me-1"></i> Dados da Apresentação</h6>
                        <div class="review-item">
                            <span class="label">Categoria</span>
                            <span class="value" id="review-categoria">-</span>
                        </div>
                        <div class="review-item" id="review-grupo-row" style="display: none;">
                            <span class="label">Grupo/Dupla</span>
                            <span class="value" id="review-grupo">-</span>
                        </div>
                        <div class="review-item" id="review-integrantes-row" style="display: none;">
                            <span class="label">Integrantes</span>
                            <span class="value" id="review-integrantes">-</span>
                        </div>
                    </div>

                    <div class="review-section">
                        <h6><i class="bx bx-folder-open me-1"></i> Arquivos</h6>
                        <div class="review-item">
                            <span class="label">Imagem de Referência</span>
                            <span class="value" id="review-referencia">-</span>
                        </div>
                        <div class="review-item">
                            <span class="label">Nome da Música</span>
                            <span class="value" id="review-nome_musica">-</span>
                        </div>
                        <div class="review-item">
                            <span class="label">Arquivo de Música</span>
                            <span class="value" id="review-musica">-</span>
                        </div>
                        <div class="review-item">
                            <span class="label">Vídeo LED</span>
                            <span class="value" id="review-video_led">-</span>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3">
                        <i class="bx bx-info-circle me-2"></i>
                        Ao clicar em <strong>Finalizar Inscrição</strong>, você confirma que todas as informações estão corretas.
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="wizard-nav">
                    <button type="button" class="btn btn-wizard btn-wizard-prev" id="btn-prev" style="visibility: hidden;">
                        <i class="bx bx-chevron-left me-1"></i> Anterior
                    </button>
                    <button type="button" class="btn btn-wizard btn-wizard-next" id="btn-next">
                        Próximo <i class="bx bx-chevron-right ms-1"></i>
                    </button>
                    <button type="submit" class="btn btn-wizard btn-wizard-submit" id="btn-submit" style="display: none;">
                        <i class="bx bx-check me-1"></i> Finalizar Inscrição
                        <span id="btn-spinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                    </button>
                </div>

                <?php echo form_close(); ?>

            </div>
        </div>
    </div>
</div>

<!-- Modal de Processamento -->
<div class="modal fade" id="modalProcessando" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center">
            <div class="modal-body py-5">
                <div class="spinner-border text-primary mb-3" style="width: 4rem; height: 4rem;" role="status">
                    <span class="visually-hidden">Processando...</span>
                </div>
                <h5 class="mb-3">Processando sua inscrição...</h5>
                <p class="text-muted">Não feche ou atualize esta página.<br>Estamos enviando seus dados e arquivos.</p>
            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>

<script src="<?php echo site_url('recursos/vendor/loadingoverlay/loadingoverlay.min.js') ?>"></script>
<script src="<?php echo site_url('recursos/vendor/mask/jquery.mask.min.js') ?>"></script>
<script src="<?php echo site_url('recursos/vendor/mask/app.js') ?>"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 1;
    const totalSteps = 4;
    const form = document.getElementById('form-inscricao');
    const btnPrev = document.getElementById('btn-prev');
    const btnNext = document.getElementById('btn-next');
    const btnSubmit = document.getElementById('btn-submit');
    const wizardSteps = document.querySelectorAll('.wizard-step');
    const formSteps = document.querySelectorAll('.form-step');
    
    // Categoria change handler
    const categoriaSelect = document.getElementById('categoria');
    const grupoField = document.getElementById('grupo-field');
    const integrantesField = document.getElementById('integrantes-field');
    
    categoriaSelect.addEventListener('change', function() {
        if (this.value === 'grupo' || this.value === 'dupla') {
            grupoField.style.display = 'block';
            integrantesField.style.display = 'block';
            document.getElementById('grupo').required = true;
            if (this.value === 'dupla') {
                document.getElementById('integrantes').value = 2;
            }
        } else {
            grupoField.style.display = 'none';
            integrantesField.style.display = 'none';
            document.getElementById('grupo').required = false;
        }
    });
    
    // File input handlers
    ['referencia', 'musica', 'video_led'].forEach(function(fieldId) {
        const input = document.getElementById(fieldId);
        const wrapper = document.getElementById(fieldId + '-wrapper');
        const nameSpan = document.getElementById(fieldId + '-name');
        
        input.addEventListener('change', function() {
            if (this.files.length > 0) {
                wrapper.classList.add('has-file');
                nameSpan.textContent = '✓ ' + this.files[0].name;
            } else {
                wrapper.classList.remove('has-file');
                nameSpan.textContent = '';
            }
        });
    });
    
    // Navigate to step
    function goToStep(step) {
        // Update form steps
        formSteps.forEach(function(fs) {
            fs.classList.remove('active');
            if (parseInt(fs.dataset.step) === step) {
                fs.classList.add('active');
            }
        });
        
        // Update wizard indicators
        wizardSteps.forEach(function(ws) {
            const wsStep = parseInt(ws.dataset.step);
            ws.classList.remove('active', 'completed');
            if (wsStep < step) {
                ws.classList.add('completed');
            } else if (wsStep === step) {
                ws.classList.add('active');
            }
        });
        
        // Update buttons
        btnPrev.style.visibility = step === 1 ? 'hidden' : 'visible';
        
        if (step === totalSteps) {
            btnNext.style.display = 'none';
            btnSubmit.style.display = 'inline-flex';
            updateReview();
        } else {
            btnNext.style.display = 'inline-flex';
            btnSubmit.style.display = 'none';
        }
        
        currentStep = step;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    
    // Validate current step
    function validateStep(step) {
        const currentFormStep = document.querySelector(`.form-step[data-step="${step}"]`);
        const inputs = currentFormStep.querySelectorAll('input[required], select[required]');
        let isValid = true;
        
        inputs.forEach(function(input) {
            if (!input.value || input.value === '') {
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            const firstInvalid = currentFormStep.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.focus();
            }
        }
        
        return isValid;
    }
    
    // Update review section
    function updateReview() {
        const fields = ['email', 'nome_social', 'nome', 'telefone', 'cpf', 'nome_musica'];
        fields.forEach(function(field) {
            const input = document.getElementById(field);
            const review = document.getElementById('review-' + field);
            if (input && review) {
                review.textContent = input.value || '-';
            }
        });
        
        // Categoria
        const categoria = document.getElementById('categoria');
        document.getElementById('review-categoria').textContent = categoria.options[categoria.selectedIndex]?.text || '-';
        
        // Grupo/Integrantes
        if (categoria.value === 'grupo' || categoria.value === 'dupla') {
            document.getElementById('review-grupo-row').style.display = 'flex';
            document.getElementById('review-integrantes-row').style.display = 'flex';
            document.getElementById('review-grupo').textContent = document.getElementById('grupo').value || '-';
            document.getElementById('review-integrantes').textContent = document.getElementById('integrantes').value || '-';
        } else {
            document.getElementById('review-grupo-row').style.display = 'none';
            document.getElementById('review-integrantes-row').style.display = 'none';
        }
        
        // Files
        const fileFields = ['referencia', 'musica', 'video_led'];
        fileFields.forEach(function(field) {
            const input = document.getElementById(field);
            const review = document.getElementById('review-' + field);
            if (input && review) {
                review.textContent = input.files.length > 0 ? '✓ ' + input.files[0].name : 'Não selecionado';
            }
        });
    }
    
    // Button handlers
    btnNext.addEventListener('click', function() {
        if (validateStep(currentStep)) {
            goToStep(currentStep + 1);
        }
    });
    
    btnPrev.addEventListener('click', function() {
        goToStep(currentStep - 1);
    });
    
    // Form submit
    form.addEventListener('submit', function(e) {
        if (!validateStep(currentStep)) {
            e.preventDefault();
            return false;
        }
        
        btnSubmit.disabled = true;
        document.getElementById('btn-spinner').classList.remove('d-none');
        
        setTimeout(function() {
            var modal = new bootstrap.Modal(document.getElementById('modalProcessando'));
            modal.show();
        }, 100);
        
        return true;
    });
    
    // Remove invalid class on input
    form.querySelectorAll('input, select').forEach(function(input) {
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    });
});
</script>

<?php echo $this->endSection() ?>