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
    
    .fornecedor-avatar {
        transition: all 0.3s ease;
    }
    
    .fornecedor-avatar:hover {
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
        <a href="<?php echo site_url('/fornecedores'); ?>">Fornecedores</a>
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('/fornecedores'); ?>">Fornecedores</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= esc($fornecedor->razao); ?></li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <div class="btn-group">
            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                Ações
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="<?php echo site_url("fornecedores/editar/$fornecedor->id"); ?>">
                    <i class="bx bx-edit-alt me-2"></i>Editar fornecedor</a></li>
                <li><a class="dropdown-item" href="<?php echo site_url("fornecedores/notas/$fornecedor->id"); ?>">
                    <i class="bx bx-file me-2"></i>Gerenciar notas fiscais</a></li>
                
                <li><hr class="dropdown-divider"></li>
                
                <?php if ($fornecedor->deleted_at == null) : ?>
                    <li><a class="dropdown-item text-danger" href="<?php echo site_url("fornecedores/excluir/$fornecedor->id"); ?>">
                        <i class="bx bx-trash me-2"></i>Excluir fornecedor</a></li>
                <?php else : ?>
                    <li><a class="dropdown-item text-success" href="<?php echo site_url("fornecedores/desfazerexclusao/$fornecedor->id"); ?>">
                        <i class="bx bx-undo me-2"></i>Restaurar fornecedor</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <a href="<?php echo site_url("fornecedores") ?>" class="btn btn-secondary ms-2">
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
                        <div class="fornecedor-avatar rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #0dcaf0;">
                            <i class="bx bx-store text-white fs-3"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-0"><?php echo esc($fornecedor->razao); ?></h5>
                        <?php if (isset($fornecedor->categoria_nome) && $fornecedor->categoria_nome): ?>
                            <span class="badge bg-primary"><?php echo esc($fornecedor->categoria_nome); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <hr>
                
                <div class="row g-3">
                    <div class="col-12">
                        <div class="d-flex align-items-center info-item">
                            <i class="bx bx-id-card text-primary me-2"></i>
                            <div>
                                <small class="text-muted">CNPJ:</small>
                                <p class="mb-0"><?php echo esc($fornecedor->cnpj); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($fornecedor->ie)) : ?>
                    <div class="col-12">
                        <div class="d-flex align-items-center info-item">
                            <i class="bx bx-receipt text-primary me-2"></i>
                            <div>
                                <small class="text-muted">Inscrição Estadual:</small>
                                <p class="mb-0"><?php echo esc($fornecedor->ie); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="col-12">
                        <div class="d-flex align-items-center info-item">
                            <i class="bx bx-phone text-primary me-2"></i>
                            <div>
                                <small class="text-muted">Telefone:</small>
                                <p class="mb-0"><?php echo esc($fornecedor->telefone); ?></p>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($fornecedor->email)) : ?>
                    <div class="col-12">
                        <div class="d-flex align-items-center info-item">
                            <i class="bx bx-envelope text-primary me-2"></i>
                            <div>
                                <small class="text-muted">Email:</small>
                                <p class="mb-0"><?php echo esc($fornecedor->email); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="col-12">
                        <div class="d-flex align-items-center info-item">
                            <i class="bx bx-check-circle text-primary me-2"></i>
                            <div>
                                <small class="text-muted">Status:</small>
                                <p class="mb-0"><?php echo $fornecedor->exibeSituacao(); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row g-2">
                    <div class="col-6">
                        <small class="text-muted">Criado:</small>
                        <p class="mb-0"><?php echo $fornecedor->criado_em ? $fornecedor->criado_em->humanize() : '-'; ?></p>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Atualizado:</small>
                        <p class="mb-0"><?php echo $fornecedor->atualizado_em ? $fornecedor->atualizado_em->humanize() : '-'; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow radius-10">
            <div class="card-body">
                <div class="row">
                    <!-- Contato Responsável -->
                    <div class="col-lg-6">
                        <h6 class="card-title mb-3">
                            <i class="bx bx-user me-2"></i>Contato Responsável
                        </h6>
                        
                        <div class="row g-3">
                            <?php if (!empty($fornecedor->nome_contato)) : ?>
                            <div class="col-12">
                                <small class="text-muted">Nome:</small>
                                <p class="mb-0"><?php echo esc($fornecedor->nome_contato); ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($fornecedor->telefone_contato)) : ?>
                            <div class="col-12">
                                <small class="text-muted">Telefone:</small>
                                <p class="mb-0"><?php echo esc($fornecedor->telefone_contato); ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if (empty($fornecedor->nome_contato) && empty($fornecedor->telefone_contato)) : ?>
                            <div class="col-12">
                                <p class="text-muted mb-0">Não informado</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Dados de Endereço -->
                        <h6 class="card-title mb-3 mt-4">
                            <i class="bx bx-map me-2"></i>Dados de Endereço
                        </h6>
                        
                        <div class="row g-3">
                            <?php if (!empty($fornecedor->cep)) : ?>
                            <div class="col-12">
                                <small class="text-muted">CEP:</small>
                                <p class="mb-0"><?php echo esc($fornecedor->cep); ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($fornecedor->endereco)) : ?>
                            <div class="col-9">
                                <small class="text-muted">Endereço:</small>
                                <p class="mb-0"><?php echo esc($fornecedor->endereco); ?></p>
                            </div>
                            <div class="col-3">
                                <small class="text-muted">Número:</small>
                                <p class="mb-0"><?php echo esc($fornecedor->numero ?? 'S/N'); ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($fornecedor->bairro)) : ?>
                            <div class="col-6">
                                <small class="text-muted">Bairro:</small>
                                <p class="mb-0"><?php echo esc($fornecedor->bairro); ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($fornecedor->cidade)) : ?>
                            <div class="col-4">
                                <small class="text-muted">Cidade:</small>
                                <p class="mb-0"><?php echo esc($fornecedor->cidade); ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($fornecedor->estado)) : ?>
                            <div class="col-2">
                                <small class="text-muted">Estado:</small>
                                <p class="mb-0"><?php echo esc($fornecedor->estado); ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if (empty($fornecedor->cep) && empty($fornecedor->endereco)) : ?>
                            <div class="col-12">
                                <p class="text-muted mb-0">Não informado</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Dados Bancários -->
                    <div class="col-lg-6">
                        <h6 class="card-title mb-3">
                            <i class="bx bx-credit-card me-2"></i>Dados Bancários
                        </h6>
                        
                        <div class="row g-3">
                            <?php if (!empty($fornecedor->banco)) : ?>
                            <div class="col-12">
                                <small class="text-muted">Banco:</small>
                                <p class="mb-0"><?php echo esc($fornecedor->banco); ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($fornecedor->agencia)) : ?>
                            <div class="col-6">
                                <small class="text-muted">Agência:</small>
                                <p class="mb-0"><?php echo esc($fornecedor->agencia); ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($fornecedor->conta)) : ?>
                            <div class="col-6">
                                <small class="text-muted">Conta:</small>
                                <p class="mb-0"><?php echo esc($fornecedor->conta); ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($fornecedor->pix)) : ?>
                            <div class="col-12">
                                <small class="text-muted">Chave PIX:</small>
                                <p class="mb-0">
                                    <code class="bg-light p-1 rounded"><?php echo esc($fornecedor->pix); ?></code>
                                </p>
                            </div>
                            <?php endif; ?>
                            <?php if (empty($fornecedor->banco) && empty($fornecedor->pix)) : ?>
                            <div class="col-12">
                                <p class="text-muted mb-0">Não informado</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Observações -->
                        <?php if (!empty($fornecedor->observacoes)) : ?>
                        <div class="mt-4">
                            <h6 class="card-title mb-3">
                                <i class="bx bx-note me-2"></i>Observações
                            </h6>
                            <p class="mb-0"><?php echo nl2br(esc($fornecedor->observacoes)); ?></p>
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