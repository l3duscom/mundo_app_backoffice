<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">
        <a href="<?php echo site_url('/agentes'); ?>">Agentes</a>
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('/agentes'); ?>">Agentes</a></li>
                <li class="breadcrumb-item active"><?php echo esc($agente->getNomeExibicao()); ?></li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="<?php echo site_url("agentes/editar/{$agente->id}"); ?>" class="btn btn-outline-primary me-2">
            <i class="bx bx-edit me-1"></i>Editar
        </a>
        <a href="<?php echo site_url('/agentes'); ?>" class="btn btn-secondary">
            <i class="bx bx-arrow-back me-1"></i>Voltar
        </a>
    </div>
</div>

<div class="row">
    <!-- Coluna Principal -->
    <div class="col-lg-8">
        <!-- Dados do Agente -->
        <div class="card shadow radius-10 mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <span class="badge bg-secondary me-2"><?php echo $tipos[$agente->tipo] ?? $agente->tipo; ?></span>
                    <?php echo esc($agente->getNomeExibicao()); ?>
                    <?php echo $agente->exibeStatus(); ?>
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if ($agente->nome_fantasia && $agente->nome_fantasia !== $agente->nome): ?>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Razão Social</small>
                        <strong><?php echo esc($agente->nome); ?></strong>
                    </div>
                    <?php endif; ?>
                    
                    <div class="col-md-3 mb-3">
                        <small class="text-muted d-block">Documento</small>
                        <strong><?php echo $agente->getDocumento(); ?></strong>
                    </div>

                    <?php if ($agente->site): ?>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Site</small>
                        <a href="<?php echo esc($agente->site); ?>" target="_blank">
                            <i class="bx bx-link-external me-1"></i><?php echo esc($agente->site); ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Contato -->
                <h6 class="mt-4 text-muted"><i class="bx bx-phone me-1"></i>Contato</h6>
                <hr class="mt-1">
                <div class="row">
                    <?php if ($agente->email): ?>
                    <div class="col-md-4 mb-3">
                        <small class="text-muted d-block">E-mail</small>
                        <a href="mailto:<?php echo esc($agente->email); ?>"><?php echo esc($agente->email); ?></a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($agente->telefone): ?>
                    <div class="col-md-4 mb-3">
                        <small class="text-muted d-block">Telefone</small>
                        <strong><?php echo esc($agente->telefone); ?></strong>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($agente->whatsapp): ?>
                    <div class="col-md-4 mb-3">
                        <small class="text-muted d-block">WhatsApp</small>
                        <a href="https://wa.me/55<?php echo preg_replace('/\D/', '', $agente->whatsapp); ?>" target="_blank">
                            <i class="bx bxl-whatsapp text-success me-1"></i><?php echo esc($agente->whatsapp); ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Endereço -->
                <?php if ($agente->endereco): ?>
                <h6 class="mt-4 text-muted"><i class="bx bx-map me-1"></i>Endereço</h6>
                <hr class="mt-1">
                <p><?php echo esc($agente->getEnderecoCompleto()); ?></p>
                <?php endif; ?>

                <!-- Dados Bancários -->
                <?php if ($agente->banco || $agente->pix): ?>
                <h6 class="mt-4 text-muted"><i class="bx bx-wallet me-1"></i>Dados Bancários</h6>
                <hr class="mt-1">
                <div class="row">
                    <?php if ($agente->banco): ?>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Banco</small>
                        <strong><?php echo esc($agente->banco); ?></strong>
                        <?php if ($agente->agencia && $agente->conta): ?>
                        - Ag: <?php echo esc($agente->agencia); ?> / Conta: <?php echo esc($agente->conta); ?>
                        <?php if ($agente->tipo_conta): ?>(<?php echo ucfirst($agente->tipo_conta); ?>)<?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($agente->pix): ?>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Chave PIX</small>
                        <strong><?php echo esc($agente->pix); ?></strong>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Observações -->
                <?php if ($agente->observacoes): ?>
                <h6 class="mt-4 text-muted"><i class="bx bx-note me-1"></i>Observações</h6>
                <hr class="mt-1">
                <p><?php echo nl2br(esc($agente->observacoes)); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Artistas Representados -->
        <div class="card shadow radius-10">
            <div class="card-header">
                <h6 class="mb-0"><i class="bx bx-microphone me-2"></i>Artistas Representados</h6>
            </div>
            <div class="card-body">
                <?php if (empty($artistas)): ?>
                <p class="text-muted text-center mb-0">Nenhum artista vinculado</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr><th>Artista</th><th>Função</th><th></th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($artistas as $art): ?>
                            <tr>
                                <td>
                                    <?php echo esc($art->nome_artistico); ?>
                                    <?php if ($art->principal): ?><span class="badge bg-primary ms-1">Principal</span><?php endif; ?>
                                </td>
                                <td><span class="badge bg-secondary"><?php echo \App\Models\ArtistaAgenteModel::FUNCOES[$art->funcao] ?? $art->funcao; ?></span></td>
                                <td><a href="<?php echo site_url("artistas/exibir/{$art->id}"); ?>" class="btn btn-sm btn-outline-primary"><i class="bx bx-show"></i></a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Coluna Lateral - Anexos -->
    <div class="col-lg-4">
        <div class="card shadow radius-10">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bx bx-paperclip me-2"></i>Documentos / Media Kit</h6>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAnexo">
                    <i class="bx bx-upload me-1"></i>Enviar
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($anexos)): ?>
                <p class="text-muted text-center mb-0">Nenhum anexo enviado</p>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($anexos as $anexo): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0" id="anexo-<?php echo $anexo->id; ?>">
                        <div class="d-flex align-items-center">
                            <i class="<?php echo $anexo->getIcone(); ?> fs-4 me-2"></i>
                            <div>
                                <p class="mb-0 small"><?php echo esc($anexo->nome_arquivo); ?></p>
                                <small class="text-muted">
                                    <?php echo $anexo->getTamanhoFormatado(); ?>
                                    <?php if ($anexo->descricao): ?> - <?php echo esc($anexo->descricao); ?><?php endif; ?>
                                </small>
                            </div>
                        </div>
                        <div>
                            <a href="<?php echo site_url("agentes/visualizarAnexo/{$anexo->id}"); ?>" class="btn btn-sm btn-outline-secondary" target="_blank" title="Abrir">
                                <i class="bx bx-link-external"></i>
                            </a>
                            <a href="<?php echo site_url("agentes/downloadAnexo/{$anexo->id}"); ?>" class="btn btn-sm btn-outline-primary" title="Baixar">
                                <i class="bx bx-download"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-danger btn-remover-anexo" data-id="<?php echo $anexo->id; ?>" title="Remover">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Anexo -->
<div class="modal fade" id="modalAnexo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bx bx-upload me-2"></i>Enviar Anexo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formAnexo" enctype="multipart/form-data">
                    <input type="hidden" name="<?php echo csrf_token(); ?>" value="<?php echo csrf_hash(); ?>">
                    <input type="hidden" name="agente_id" value="<?php echo $agente->id; ?>">
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <input type="text" name="descricao" class="form-control" placeholder="Ex: Media Kit, Contrato...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Arquivo (PDF ou Imagem)</label>
                        <input type="file" name="arquivo" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.gif,.webp" required>
                        <small class="text-muted">Máximo: 10MB</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarAnexo">
                    <i class="bx bx-upload me-1"></i>Enviar
                </button>
            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script>
var csrfName = '<?php echo csrf_token(); ?>';
var csrfHash = '<?php echo csrf_hash(); ?>';

$(document).ready(function() {
    // Upload anexo
    $('#btnSalvarAnexo').click(function() {
        var form = $('#formAnexo')[0];
        var formData = new FormData(form);
        var btn = $(this);
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        
        $.ajax({
            url: '<?php echo site_url("agentes/uploadAnexo"); ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                csrfHash = response.token;
                if (response.erro) {
                    alert(response.erro);
                    btn.prop('disabled', false).html('<i class="bx bx-upload me-1"></i>Enviar');
                    return;
                }
                location.reload();
            },
            error: function() {
                alert('Erro ao enviar arquivo');
                btn.prop('disabled', false).html('<i class="bx bx-upload me-1"></i>Enviar');
            }
        });
    });

    // Remover anexo
    $('.btn-remover-anexo').click(function() {
        var anexoId = $(this).data('id');
        
        if (!confirm('Remover este anexo?')) return;
        
        $.post('<?php echo site_url("agentes/removerAnexo"); ?>', {
            [csrfName]: csrfHash, anexo_id: anexoId
        }, function(r) {
            csrfHash = r.token;
            if (r.erro) { alert(r.erro); return; }
            $('#anexo-' + anexoId).fadeOut(300, function() { $(this).remove(); });
        }, 'json');
    });
});
</script>
<?php echo $this->endSection() ?>
