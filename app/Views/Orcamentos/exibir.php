<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
    .card { transition: all 0.3s ease; }
    .card:hover { transform: translateY(-2px); box-shadow: 0 4px 25px rgba(0, 0, 0, 0.1) !important; }
    .info-item { transition: all 0.2s ease; padding: 0.5rem; border-radius: 8px; }
    .info-item:hover { background-color: rgba(0, 0, 0, 0.05); }
    .anexo-card { border: 1px solid #dee2e6; border-radius: 8px; padding: 1rem; margin-bottom: 0.5rem; }
    .anexo-card:hover { background-color: #f8f9fa; }
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">
        <a href="<?php echo site_url('/orcamentos'); ?>">Orçamentos</a>
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('/orcamentos'); ?>">Orçamentos</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo esc($orcamento->codigo); ?></li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <div class="btn-group">
            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">Ações</button>
            <ul class="dropdown-menu">
                <?php if ($orcamento->podeEditar()): ?>
                <li><a class="dropdown-item" href="<?php echo site_url("orcamentos/editar/{$orcamento->id}"); ?>">
                    <i class="bx bx-edit me-2"></i>Editar</a></li>
                <?php endif; ?>
                
                <?php if ($orcamento->situacao == 'rascunho'): ?>
                <li><a class="dropdown-item btn-alterar-situacao" href="#" data-situacao="enviado">
                    <i class="bx bx-send me-2"></i>Marcar como Enviado</a></li>
                <?php endif; ?>
                
                <?php if ($orcamento->situacao == 'enviado'): ?>
                <li><a class="dropdown-item btn-alterar-situacao text-success" href="#" data-situacao="aprovado">
                    <i class="bx bx-check-circle me-2"></i>Aprovar Orçamento</a></li>
                <?php endif; ?>
                
                <?php if ($orcamento->situacao == 'aprovado'): ?>
                <li><a class="dropdown-item btn-alterar-situacao" href="#" data-situacao="em_andamento">
                    <i class="bx bx-loader me-2"></i>Iniciar Execução</a></li>
                <?php endif; ?>
                
                <?php if ($orcamento->situacao == 'em_andamento'): ?>
                <li><a class="dropdown-item btn-alterar-situacao text-success" href="#" data-situacao="concluido">
                    <i class="bx bx-check-double me-2"></i>Concluir</a></li>
                <?php endif; ?>
                
                <?php if ($orcamento->podeCancelar()): ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item btn-alterar-situacao text-danger" href="#" data-situacao="cancelado">
                    <i class="bx bx-x-circle me-2"></i>Cancelar</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <a href="<?php echo site_url('orcamentos'); ?>" class="btn btn-secondary ms-2">
            <i class="bx bx-arrow-back me-2"></i>Voltar
        </a>
    </div>
</div>
<!--end breadcrumb-->

<div class="row">
    <!-- Coluna Esquerda -->
    <div class="col-lg-4">
        <div class="card shadow radius-10">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #0dcaf0;">
                            <i class="bx bx-file text-white fs-3"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-0"><?php echo esc($orcamento->codigo); ?></h5>
                        <p class="mb-0"><?php echo $orcamento->exibeSituacao(); ?></p>
                    </div>
                </div>
                
                <hr>
                
                <div class="row g-3">
                    <div class="col-12">
                        <div class="d-flex align-items-center info-item">
                            <i class="bx bx-store text-primary me-2"></i>
                            <div>
                                <small class="text-muted">Fornecedor:</small>
                                <p class="mb-0"><?php echo esc($orcamento->fornecedor_nome ?? '-'); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($orcamento->evento_nome)): ?>
                    <div class="col-12">
                        <div class="d-flex align-items-center info-item">
                            <i class="bx bx-calendar-event text-primary me-2"></i>
                            <div>
                                <small class="text-muted">Evento:</small>
                                <p class="mb-0"><?php echo esc($orcamento->evento_nome); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="col-12">
                        <div class="d-flex align-items-center info-item">
                            <i class="bx bx-money text-success me-2"></i>
                            <div>
                                <small class="text-muted">Valor Final:</small>
                                <p class="mb-0 fs-5"><strong>R$ <?php echo number_format($orcamento->valor_final, 2, ',', '.'); ?></strong></p>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($orcamento->data_validade): ?>
                    <div class="col-12">
                        <div class="d-flex align-items-center info-item">
                            <i class="bx bx-calendar text-warning me-2"></i>
                            <div>
                                <small class="text-muted">Validade:</small>
                                <p class="mb-0"><?php echo $orcamento->data_validade->toLocalizedString('dd/MM/yyyy'); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <hr>

                <div class="row g-2">
                    <div class="col-6">
                        <small class="text-muted">Criado:</small>
                        <p class="mb-0"><?php echo $orcamento->created_at ? $orcamento->created_at->humanize() : '-'; ?></p>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Atualizado:</small>
                        <p class="mb-0"><?php echo $orcamento->updated_at ? $orcamento->updated_at->humanize() : '-'; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Coluna Direita -->
    <div class="col-lg-8">
        <!-- Itens -->
        <div class="card shadow radius-10 mb-3">
            <div class="card-body">
                <h6 class="card-title"><i class="bx bx-list-ul me-2"></i>Itens do Orçamento</h6>
                
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Descrição</th>
                                <th class="text-center" style="width: 80px;">Qtd</th>
                                <th class="text-end" style="width: 120px;">Unit.</th>
                                <th class="text-end" style="width: 120px;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($itens)): foreach ($itens as $item): ?>
                            <tr>
                                <td><?php echo esc($item->descricao); ?></td>
                                <td class="text-center"><?php echo number_format($item->quantidade, 2, ',', '.'); ?></td>
                                <td class="text-end">R$ <?php echo number_format($item->valor_unitario, 2, ',', '.'); ?></td>
                                <td class="text-end"><strong>R$ <?php echo number_format($item->valor_total, 2, ',', '.'); ?></strong></td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="4" class="text-muted text-center">Nenhum item cadastrado</td></tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                <td class="text-end"><strong>R$ <?php echo number_format($orcamento->valor_total, 2, ',', '.'); ?></strong></td>
                            </tr>
                            <?php if ($orcamento->valor_desconto > 0): ?>
                            <tr class="text-danger">
                                <td colspan="3" class="text-end">Desconto:</td>
                                <td class="text-end">- R$ <?php echo number_format($orcamento->valor_desconto, 2, ',', '.'); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr class="table-success">
                                <td colspan="3" class="text-end"><strong>TOTAL:</strong></td>
                                <td class="text-end"><strong class="fs-5">R$ <?php echo number_format($orcamento->valor_final, 2, ',', '.'); ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Anexos -->
        <div class="card shadow radius-10">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="card-title mb-0"><i class="bx bx-paperclip me-2"></i>Anexos</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAnexo">
                        <i class="bx bx-upload me-1"></i>Enviar Arquivo
                    </button>
                </div>
                
                <div id="listaAnexos">
                    <?php if (!empty($anexos)): foreach ($anexos as $anexo): ?>
                    <div class="anexo-card d-flex justify-content-between align-items-center" id="anexo-<?php echo $anexo->id; ?>">
                        <div class="d-flex align-items-center">
                            <i class="<?php echo $anexo->getIcone(); ?> fs-4 me-3"></i>
                            <div>
                                <p class="mb-0"><?php echo esc($anexo->nome_arquivo); ?></p>
                                <small class="text-muted"><?php echo $anexo->getTamanhoFormatado(); ?></small>
                            </div>
                        </div>
                        <div>
                            <a href="<?php echo site_url("orcamentos/visualizarAnexo/{$anexo->id}"); ?>" class="btn btn-sm btn-outline-secondary" target="_blank" title="Abrir">
                                <i class="bx bx-link-external"></i>
                            </a>
                            <a href="<?php echo site_url("orcamentos/downloadAnexo/{$anexo->id}"); ?>" class="btn btn-sm btn-outline-primary" title="Baixar">
                                <i class="bx bx-download"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-danger btn-remover-anexo" data-id="<?php echo $anexo->id; ?>" title="Remover">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; else: ?>
                    <p class="text-muted text-center mb-0">Nenhum anexo enviado</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Observações -->
        <?php if (!empty($orcamento->descricao) || !empty($orcamento->observacoes)): ?>
        <div class="card shadow radius-10 mt-3">
            <div class="card-body">
                <?php if (!empty($orcamento->descricao)): ?>
                <h6 class="card-title"><i class="bx bx-detail me-2"></i>Descrição</h6>
                <p><?php echo nl2br(esc($orcamento->descricao)); ?></p>
                <?php endif; ?>
                
                <?php if (!empty($orcamento->observacoes)): ?>
                <h6 class="card-title"><i class="bx bx-note me-2"></i>Observações</h6>
                <p class="mb-0"><?php echo nl2br(esc($orcamento->observacoes)); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Upload Anexo -->
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
                    <input type="hidden" name="orcamento_id" value="<?php echo $orcamento->id; ?>">
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
$(document).ready(function() {
    // Alterar situação
    $('.btn-alterar-situacao').click(function(e) {
        e.preventDefault();
        var situacao = $(this).data('situacao');
        
        if (!confirm('Confirma a alteração de situação?')) return;
        
        $.ajax({
            url: '<?php echo site_url("orcamentos/alterarSituacao"); ?>',
            type: 'POST',
            data: {
                '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>',
                id: '<?php echo $orcamento->id; ?>',
                situacao: situacao
            },
            dataType: 'json',
            success: function(response) {
                if (response.erro) {
                    alert(response.erro);
                    return;
                }
                location.reload();
            }
        });
    });

    // Upload anexo
    $('#btnSalvarAnexo').click(function() {
        var form = $('#formAnexo')[0];
        var formData = new FormData(form);
        var btn = $(this);
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        
        $.ajax({
            url: '<?php echo site_url("orcamentos/uploadAnexo"); ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
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
        
        $.ajax({
            url: '<?php echo site_url("orcamentos/removerAnexo"); ?>',
            type: 'POST',
            data: {
                '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>',
                anexo_id: anexoId
            },
            dataType: 'json',
            success: function(response) {
                if (response.erro) {
                    alert(response.erro);
                    return;
                }
                $('#anexo-' + anexoId).fadeOut();
            }
        });
    });
});
</script>
<?php echo $this->endSection() ?>
