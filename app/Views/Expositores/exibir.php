<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>

<style>
    .card {
        transition: all 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 25px rgba(0, 0, 0, 0.1) !important;
    }
    
    .info-item {
        transition: all 0.2s ease;
        padding: 0.5rem;
        border-radius: 8px;
    }
    
    .info-item:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }
    
    .badge {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }
    
    .expositor-avatar {
        transition: all 0.3s ease;
    }
    
    .expositor-avatar:hover {
        transform: scale(1.1);
    }
    
    .dropdown-item {
        transition: all 0.2s ease;
    }
    
    .dropdown-item:hover {
        background-color: rgba(0, 123, 255, 0.1);
        color: #007bff;
    }
</style>

<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">
        <a href="<?php echo site_url('/expositores'); ?>">Expositores</a>
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('/expositores'); ?>">Expositores</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= esc($expositor->getNomeExibicao()); ?></li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <div class="btn-group">
            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                Ações
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="<?php echo site_url("expositores/editar/$expositor->id"); ?>">
                    <i class="bx bx-edit-alt me-2"></i>Editar expositor</a></li>
                <li><a class="dropdown-item" href="<?php echo site_url("expositores/reenviarEmail/$expositor->id"); ?>">
                    <i class="bx bx-envelope me-2"></i>Reenviar email de boas-vindas</a></li>
                <li><hr class="dropdown-divider"></li>
                
                <?php if ($expositor->deleted_at == null) : ?>
                    <li><a class="dropdown-item text-danger" href="<?php echo site_url("expositores/excluir/$expositor->id"); ?>">
                        <i class="bx bx-trash me-2"></i>Excluir expositor</a></li>
                <?php else : ?>
                    <li><a class="dropdown-item text-success" href="<?php echo site_url("expositores/desfazerexclusao/$expositor->id"); ?>">
                        <i class="bx bx-undo me-2"></i>Restaurar expositor</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <a href="<?php echo site_url("expositores") ?>" class="btn btn-secondary ms-2">
            <i class="bx bx-arrow-back me-2"></i>Voltar
        </a>
    </div>
</div>
<!--end breadcrumb-->

<div class="row">
    <div class="col-lg-4">
        <div class="card shadow radius-10">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <div class="expositor-avatar rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: <?php echo $expositor->isPessoaJuridica() ? '#0dcaf0' : '#6c757d'; ?>;">
                            <i class="bx <?php echo $expositor->isPessoaJuridica() ? 'bx-buildings' : 'bx-user'; ?> text-white fs-3"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-0"><?php echo esc($expositor->getNomeExibicao()); ?></h5>
                        <p class="text-muted mb-0"><?php echo $expositor->getTipoPessoaFormatado(); ?></p>
                    </div>
                </div>
                
                <hr>
                
                <div class="row g-3">
                    <div class="col-12">
                        <div class="d-flex align-items-center info-item">
                            <i class="bx bx-id-card text-primary me-2"></i>
                            <div>
                                <small class="text-muted"><?php echo $expositor->getLabelDocumento(); ?>:</small>
                                <p class="mb-0"><?php echo esc($expositor->getDocumentoFormatado()); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="d-flex align-items-center info-item">
                            <i class="bx bx-envelope text-primary me-2"></i>
                            <div>
                                <small class="text-muted">Email:</small>
                                <p class="mb-0"><?php echo esc($expositor->email); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="d-flex align-items-center info-item">
                            <i class="bx bx-phone text-primary me-2"></i>
                            <div>
                                <small class="text-muted">Telefone:</small>
                                <p class="mb-0"><?php echo esc($expositor->telefone); ?></p>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($expositor->celular)) : ?>
                    <div class="col-12">
                        <div class="d-flex align-items-center info-item">
                            <i class="bx bx-mobile text-primary me-2"></i>
                            <div>
                                <small class="text-muted">Celular:</small>
                                <p class="mb-0"><?php echo esc($expositor->celular); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="col-12">
                        <div class="d-flex align-items-center info-item">
                            <i class="bx bx-check-circle text-primary me-2"></i>
                            <div>
                                <small class="text-muted">Status:</small>
                                <p class="mb-0"><?php echo $expositor->exibeSituacao(); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row g-2">
                    <div class="col-6">
                        <small class="text-muted">Criado:</small>
                        <p class="mb-0"><?php echo $expositor->created_at->humanize(); ?></p>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Atualizado:</small>
                        <p class="mb-0"><?php echo $expositor->updated_at->humanize(); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow radius-10">
            <div class="card-body">
                <div class="row">
                    <!-- Dados do Endereço -->
                    <div class="col-lg-6">
                        <h6 class="card-title mb-3">
                            <i class="bx bx-map me-2"></i>Dados de Endereço
                        </h6>
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <small class="text-muted">CEP:</small>
                                <p class="mb-0"><?php echo esc($expositor->cep ?? 'Não informado'); ?></p>
                            </div>
                            <div class="col-9">
                                <small class="text-muted">Endereço:</small>
                                <p class="mb-0"><?php echo esc($expositor->endereco ?? 'Não informado'); ?></p>
                            </div>
                            <div class="col-3">
                                <small class="text-muted">Número:</small>
                                <p class="mb-0"><?php echo esc($expositor->numero ?? 'S/N'); ?></p>
                            </div>
                            <?php if (!empty($expositor->complemento)) : ?>
                            <div class="col-12">
                                <small class="text-muted">Complemento:</small>
                                <p class="mb-0"><?php echo esc($expositor->complemento); ?></p>
                            </div>
                            <?php endif; ?>
                            <div class="col-6">
                                <small class="text-muted">Bairro:</small>
                                <p class="mb-0"><?php echo esc($expositor->bairro ?? 'Não informado'); ?></p>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">Cidade:</small>
                                <p class="mb-0"><?php echo esc($expositor->cidade ?? 'Não informado'); ?></p>
                            </div>
                            <div class="col-2">
                                <small class="text-muted">Estado:</small>
                                <p class="mb-0"><?php echo esc($expositor->estado ?? 'N/A'); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Informações Adicionais -->
                    <div class="col-lg-6">
                        <?php if ($expositor->isPessoaJuridica()) : ?>
                            <!-- Dados PJ -->
                            <h6 class="card-title mb-3">
                                <i class="bx bx-buildings me-2"></i>Dados da Empresa
                            </h6>
                            
                            <div class="row g-3">
                                <?php if (!empty($expositor->nome_fantasia)) : ?>
                                <div class="col-12">
                                    <small class="text-muted">Nome Fantasia:</small>
                                    <p class="mb-0"><?php echo esc($expositor->nome_fantasia); ?></p>
                                </div>
                                <?php endif; ?>
                                <div class="col-12">
                                    <small class="text-muted">Razão Social:</small>
                                    <p class="mb-0"><?php echo esc($expositor->nome); ?></p>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">Inscrição Estadual:</small>
                                    <p class="mb-0"><?php echo esc($expositor->ie ?? 'Não informado'); ?></p>
                                </div>
                                <?php if (!empty($expositor->responsavel)) : ?>
                                <div class="col-12">
                                    <small class="text-muted">Responsável:</small>
                                    <p class="mb-0"><?php echo esc($expositor->responsavel); ?></p>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($expositor->responsavel_telefone)) : ?>
                                <div class="col-12">
                                    <small class="text-muted">Telefone do Responsável:</small>
                                    <p class="mb-0"><?php echo esc($expositor->responsavel_telefone); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php else : ?>
                            <!-- Dados PF -->
                            <h6 class="card-title mb-3">
                                <i class="bx bx-info-circle me-2"></i>Informações Adicionais
                            </h6>
                            
                            <div class="row g-3">
                                <div class="col-12">
                                    <small class="text-muted">Nome Completo:</small>
                                    <p class="mb-0"><?php echo esc($expositor->nome); ?></p>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">ID do Expositor:</small>
                                    <p class="mb-0">#<?php echo $expositor->id; ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Tipo de Expositor, Segmento e Observações -->
                        <?php if (!empty($expositor->tipo_expositor)) : ?>
                        <div class="mt-4">
                            <small class="text-muted">Tipo de Expositor:</small>
                            <p class="mb-0"><span class="badge bg-success"><?php echo esc($expositor->tipo_expositor); ?></span></p>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($expositor->segmento)) : ?>
                        <div class="mt-3">
                            <small class="text-muted">Segmento de Atuação:</small>
                            <p class="mb-0"><span class="badge bg-primary"><?php echo esc($expositor->segmento); ?></span></p>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($expositor->observacoes)) : ?>
                        <div class="mt-4">
                            <small class="text-muted">Observações:</small>
                            <p class="mb-0"><?php echo nl2br(esc($expositor->observacoes)); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php echo $this->endSection() ?>




<?php echo $this->section('scripts') ?>

<!-- Aqui coloco os scripts da view-->

<?php echo $this->endSection() ?>

