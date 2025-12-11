<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
    .doc-card {
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    
    .doc-card:hover {
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }
    
    .doc-status-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    
    .timeline-assinatura {
        position: relative;
        padding-left: 30px;
    }
    
    .timeline-assinatura::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e0e0e0;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 15px;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -24px;
        top: 5px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #6c757d;
    }
    
    .timeline-item.completed::before {
        background: #198754;
    }
    
    .timeline-item.active::before {
        background: #0d6efd;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.25);
    }
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">
        <a href="<?php echo site_url('/contratos'); ?>">Contratos</a>
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('/contratos'); ?>">Contratos</a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url("contratos/exibir/{$contrato->id}"); ?>"><?= esc($contrato->codigo ?? '#' . $contrato->id); ?></a></li>
                <li class="breadcrumb-item active">Documentos</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="<?php echo site_url("contratos/exibir/{$contrato->id}") ?>" class="btn btn-secondary">
            <i class="bx bx-arrow-back me-2"></i>Voltar ao Contrato
        </a>
    </div>
</div>
<!--end breadcrumb-->

<div class="row">
    <!-- Coluna Esquerda - Info do Contrato -->
    <div class="col-lg-4">
        <div class="card shadow radius-10">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #6f42c1;">
                            <i class="bx bx-file text-white fs-3"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-0"><?php echo esc($contrato->codigo ?? '#' . $contrato->id); ?></h5>
                        <p class="text-muted mb-0"><?php echo $contrato->getBadgeSituacao(); ?></p>
                    </div>
                </div>
                
                <hr>
                
                <div class="mb-3">
                    <small class="text-muted">Expositor:</small>
                    <p class="mb-0 fw-bold"><?php echo esc($expositor->getNomeExibicao()); ?></p>
                    <small class="text-muted"><?php echo esc($expositor->getDocumentoFormatado()); ?></small>
                </div>
                
                <div class="mb-3">
                    <small class="text-muted">Evento:</small>
                    <p class="mb-0"><?php echo esc($evento->nome ?? 'N/A'); ?></p>
                </div>
                
                <div class="mb-3">
                    <small class="text-muted">Valor do Contrato:</small>
                    <p class="mb-0 fw-bold text-success fs-5"><?php echo $contrato->getValorFinalFormatado(); ?></p>
                </div>
            </div>
        </div>

        <!-- Card para gerar novo documento -->
        <div class="card shadow radius-10 mt-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bx bx-plus-circle me-2"></i>Gerar Novo Documento</h6>
            </div>
            <div class="card-body">
                <form id="formGerarDocumento">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <label class="form-label">Selecione o Modelo:</label>
                        <select class="form-select" name="modelo_id" id="modelo_id">
                            <option value="">Automático (baseado no tipo de item)</option>
                            <?php foreach ($modelos as $modelo): ?>
                            <option value="<?php echo $modelo->id; ?>">
                                <?php echo esc($modelo->nome); ?> (<?php echo esc($modelo->tipo_item); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">O sistema escolherá o modelo adequado se não selecionar.</small>
                    </div>
                    <input type="hidden" name="contrato_id" value="<?php echo $contrato->id; ?>">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bx bx-file-blank me-2"></i>Gerar Documento
                    </button>
                </form>
                
                <?php if (empty($modelos)): ?>
                <div class="alert alert-warning mt-3 mb-0">
                    <i class="bx bx-error me-2"></i>
                    Nenhum modelo de documento cadastrado.
                    <a href="<?php echo site_url('contratodocumentos/criarmodelo'); ?>" class="alert-link">Criar modelo</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Coluna Direita - Lista de Documentos -->
    <div class="col-lg-8">
        <div class="card shadow radius-10">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bx bx-folder-open me-2"></i>Documentos do Contrato</h6>
                <a href="<?php echo site_url('contratodocumentos/modelos'); ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bx bx-cog me-1"></i>Gerenciar Modelos
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($documentos)): ?>
                <div class="text-center py-5">
                    <i class="bx bx-folder-open text-muted" style="font-size: 4rem;"></i>
                    <p class="text-muted mt-3">Nenhum documento gerado para este contrato.</p>
                    <p class="text-muted">Clique em "Gerar Documento" para criar um novo.</p>
                </div>
                <?php else: ?>
                
                <?php foreach ($documentos as $doc): ?>
                <div class="doc-card p-3 mb-3">
                    <div class="d-flex align-items-start">
                        <div class="doc-status-icon me-3 <?php 
                            echo match($doc->status) {
                                'rascunho' => 'bg-secondary text-white',
                                'pendente_assinatura' => 'bg-warning text-dark',
                                'assinado' => 'bg-info text-white',
                                'confirmado' => 'bg-success text-white',
                                'cancelado' => 'bg-danger text-white',
                                default => 'bg-secondary text-white'
                            };
                        ?>">
                            <i class="bx <?php 
                                echo match($doc->status) {
                                    'rascunho' => 'bx-file',
                                    'pendente_assinatura' => 'bx-time',
                                    'assinado' => 'bx-pen',
                                    'confirmado' => 'bx-check-circle',
                                    'cancelado' => 'bx-x-circle',
                                    default => 'bx-file'
                                };
                            ?>"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1"><?php echo esc($doc->titulo); ?></h6>
                                    <?php echo $doc->getBadgeStatus(); ?>
                                </div>
                                <small class="text-muted"><?php echo $doc->created_at->format('d/m/Y H:i'); ?></small>
                            </div>
                            
                            <!-- Timeline de status -->
                            <div class="timeline-assinatura mt-3">
                                <div class="timeline-item completed">
                                    <small><strong>Criado:</strong> <?php echo $doc->created_at->format('d/m/Y H:i'); ?></small>
                                </div>
                                
                                <?php if ($doc->data_envio): ?>
                                <div class="timeline-item completed">
                                    <small><strong>Enviado para assinatura:</strong> <?php echo $doc->getDataEnvioFormatada(); ?></small>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($doc->data_assinatura): ?>
                                <div class="timeline-item completed">
                                    <small><strong>Assinado por:</strong> <?php echo esc($doc->assinado_por); ?> (<?php echo esc($doc->documento_assinante); ?>)</small>
                                    <br><small><?php echo $doc->getDataAssinaturaFormatada(); ?> - IP: <?php echo esc($doc->ip_assinatura); ?></small>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($doc->data_confirmacao): ?>
                                <div class="timeline-item completed">
                                    <small><strong>Confirmado:</strong> <?php echo $doc->getDataConfirmacaoFormatada(); ?></small>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Botões de ação -->
                            <div class="mt-3">
                                <a href="<?php echo site_url("contratodocumentos/visualizar/{$doc->id}"); ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="bx bx-show me-1"></i>Visualizar
                                </a>
                                
                                <?php if ($doc->podeEditar()): ?>
                                <a href="<?php echo site_url("contratodocumentos/editar/{$doc->id}"); ?>" class="btn btn-sm btn-outline-secondary">
                                    <i class="bx bx-edit me-1"></i>Editar
                                </a>
                                <?php endif; ?>
                                
                                <?php if ($doc->status === 'rascunho'): ?>
                                <button type="button" class="btn btn-sm btn-success btn-enviar-assinatura" data-id="<?php echo $doc->id; ?>">
                                    <i class="bx bx-send me-1"></i>Enviar para Assinatura
                                </button>
                                <?php endif; ?>
                                
                                <?php if ($doc->status === 'pendente_assinatura'): ?>
                                <button type="button" class="btn btn-sm btn-info btn-copiar-link" data-url="<?php echo $doc->getUrlAssinatura(); ?>">
                                    <i class="bx bx-link me-1"></i>Copiar Link
                                </button>
                                <?php endif; ?>
                                
                                <?php if ($doc->podeConfirmar()): ?>
                                <button type="button" class="btn btn-sm btn-success btn-confirmar-doc" data-id="<?php echo $doc->id; ?>">
                                    <i class="bx bx-check-circle me-1"></i>Confirmar Assinatura
                                </button>
                                <?php endif; ?>
                                
                                <?php if ($doc->status !== 'confirmado' && $doc->status !== 'cancelado'): ?>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-cancelar-doc" data-id="<?php echo $doc->id; ?>">
                                    <i class="bx bx-x me-1"></i>Cancelar
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Link de Assinatura -->
<div class="modal fade" id="modalLink" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-link me-2"></i>Link de Assinatura</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p>Envie este link para o expositor assinar o documento:</p>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="linkAssinatura" readonly>
                    <button class="btn btn-outline-primary" type="button" id="btnCopiarLink">
                        <i class="bx bx-copy"></i>
                    </button>
                </div>
                <small class="text-muted">O expositor poderá visualizar e assinar o documento através deste link.</small>
            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script>
$(document).ready(function() {
    var csrfToken = '<?php echo csrf_hash(); ?>';
    var csrfName = '<?php echo csrf_token(); ?>';

    // Gerar documento
    $('#formGerarDocumento').on('submit', function(e) {
        e.preventDefault();
        
        var btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Gerando...');
        
        var formData = $(this).serialize();
        
        $.ajax({
            type: 'POST',
            url: '<?php echo site_url('contratodocumentos/gerar'); ?>',
            data: formData,
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.token) {
                    csrfToken = response.token;
                    // Atualiza o token no formulário
                    $('input[name="' + csrfName + '"]').val(csrfToken);
                }
                
                if (response.sucesso) {
                    alert(response.sucesso);
                    location.reload();
                } else {
                    alert(response.erro || 'Erro ao gerar documento');
                    btn.prop('disabled', false).html('<i class="bx bx-file-blank me-2"></i>Gerar Documento');
                }
            },
            error: function(xhr, status, error) {
                console.log('Erro AJAX:', xhr.responseText);
                alert('Erro ao processar solicitação: ' + (xhr.responseJSON?.erro || error || 'Verifique o console'));
                btn.prop('disabled', false).html('<i class="bx bx-file-blank me-2"></i>Gerar Documento');
            }
        });
    });

    // Enviar para assinatura
    $('.btn-enviar-assinatura').on('click', function() {
        if (!confirm('Enviar documento para assinatura? O expositor receberá um link para assinar.')) return;
        
        var btn = $(this);
        var id = btn.data('id');
        
        btn.prop('disabled', true);
        
        $.ajax({
            type: 'POST',
            url: '<?php echo site_url('contratodocumentos/enviarparaassinatura'); ?>',
            data: { id: id, [csrfName]: csrfToken },
            dataType: 'json',
            success: function(response) {
                if (response.token) csrfToken = response.token;
                
                if (response.sucesso) {
                    $('#linkAssinatura').val(response.url_assinatura);
                    $('#modalLink').modal('show');
                    setTimeout(function() { location.reload(); }, 2000);
                } else {
                    alert(response.erro || 'Erro ao enviar');
                    btn.prop('disabled', false);
                }
            },
            error: function() {
                alert('Erro ao processar');
                btn.prop('disabled', false);
            }
        });
    });

    // Copiar link
    $('.btn-copiar-link').on('click', function() {
        var url = $(this).data('url');
        navigator.clipboard.writeText(url).then(function() {
            alert('Link copiado!');
        });
    });

    $('#btnCopiarLink').on('click', function() {
        var input = $('#linkAssinatura');
        input.select();
        navigator.clipboard.writeText(input.val()).then(function() {
            alert('Link copiado!');
        });
    });

    // Confirmar documento
    $('.btn-confirmar-doc').on('click', function() {
        if (!confirm('Confirmar este documento? O contrato será finalizado.')) return;
        
        var btn = $(this);
        var id = btn.data('id');
        
        btn.prop('disabled', true);
        
        $.ajax({
            type: 'POST',
            url: '<?php echo site_url('contratodocumentos/confirmar'); ?>',
            data: { id: id, [csrfName]: csrfToken },
            dataType: 'json',
            success: function(response) {
                if (response.token) csrfToken = response.token;
                
                if (response.sucesso) {
                    alert(response.sucesso);
                    location.reload();
                } else {
                    alert(response.erro || 'Erro ao confirmar');
                    btn.prop('disabled', false);
                }
            },
            error: function() {
                alert('Erro ao processar');
                btn.prop('disabled', false);
            }
        });
    });

    // Cancelar documento
    $('.btn-cancelar-doc').on('click', function() {
        if (!confirm('Cancelar este documento?')) return;
        
        var btn = $(this);
        var id = btn.data('id');
        
        $.ajax({
            type: 'POST',
            url: '<?php echo site_url('contratodocumentos/cancelar'); ?>',
            data: { id: id, [csrfName]: csrfToken },
            dataType: 'json',
            success: function(response) {
                if (response.token) csrfToken = response.token;
                
                if (response.sucesso) {
                    location.reload();
                } else {
                    alert(response.erro || 'Erro ao cancelar');
                }
            }
        });
    });
});
</script>
<?php echo $this->endSection() ?>

