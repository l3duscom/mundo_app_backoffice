<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-dark">
        <a href="<?php echo site_url('/email-templates'); ?>">Templates de Email</a>
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('/email-templates'); ?>">Templates</a></li>
                <li class="breadcrumb-item active"><?php echo esc($templateNome); ?></li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="<?php echo site_url('email-templates'); ?>" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i>Voltar
        </a>
    </div>
</div>

<!-- Informações do Template -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow radius-10">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="widget-icon bg-<?php echo $categoriaInfo['cor']; ?> text-white rounded-circle">
                            <i class="bx <?php echo $categoriaInfo['icone']; ?>"></i>
                        </div>
                    </div>
                    <div class="col">
                        <h5 class="mb-1"><?php echo esc($templateNome); ?></h5>
                        <p class="text-muted mb-0">
                            <span class="badge bg-<?php echo $categoriaInfo['cor']; ?> me-2"><?php echo esc($categoria); ?></span>
                            <code class="text-muted"><?php echo esc($caminhoArquivo); ?></code>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs de Visualização -->
<div class="card shadow radius-10">
    <div class="card-header bg-transparent">
        <ul class="nav nav-tabs card-header-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="preview-tab" data-bs-toggle="tab" href="#preview" role="tab">
                    <i class="bx bx-show me-1"></i>Preview
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="codigo-tab" data-bs-toggle="tab" href="#codigo" role="tab">
                    <i class="bx bx-code-alt me-1"></i>Código Fonte
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            <!-- Preview Tab -->
            <div class="tab-pane fade show active" id="preview" role="tabpanel">
                <div class="email-preview-container border rounded p-4 bg-light">
                    <div class="email-preview bg-white p-4 shadow-sm rounded">
                        <!-- Header do Email -->
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-secondary me-2">De:</span>
                                <span>sistema@mundodream.com.br</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-secondary me-2">Para:</span>
                                <span>cliente@exemplo.com</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary me-2">Assunto:</span>
                                <strong><?php echo esc($templateNome); ?></strong>
                            </div>
                        </div>
                        
                        <!-- Conteúdo do Email -->
                        <div class="email-body">
                            <?php echo $conteudoHtml; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Código Tab -->
            <div class="tab-pane fade" id="codigo" role="tabpanel">
                <div class="code-container">
                    <div class="d-flex justify-content-end mb-2">
                        <button class="btn btn-sm btn-outline-primary" onclick="copiarCodigo()">
                            <i class="bx bx-copy me-1"></i>Copiar Código
                        </button>
                    </div>
                    <pre class="bg-dark text-light p-3 rounded" style="max-height: 500px; overflow: auto;"><code id="codigo-fonte"><?php echo esc($conteudoRaw); ?></code></pre>
                </div>
            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
.widget-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}
.email-preview-container {
    background: repeating-linear-gradient(
        45deg,
        #f8f9fa,
        #f8f9fa 10px,
        #fff 10px,
        #fff 20px
    );
}
.email-preview {
    max-width: 700px;
    margin: 0 auto;
}
.email-body img {
    max-width: 100%;
    height: auto;
}
pre {
    white-space: pre-wrap;
    word-wrap: break-word;
}
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script>
function copiarCodigo() {
    const codigo = document.getElementById('codigo-fonte').textContent;
    navigator.clipboard.writeText(codigo).then(() => {
        alert('Código copiado para a área de transferência!');
    }).catch(err => {
        console.error('Erro ao copiar:', err);
    });
}
</script>
<?php echo $this->endSection() ?>
