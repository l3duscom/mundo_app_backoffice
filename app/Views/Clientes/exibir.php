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
    
    .user-avatar {
        transition: all 0.3s ease;
    }
    
    .user-avatar:hover {
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
        <a href="<?php echo site_url('/clientes'); ?>">Clientes</a>
        <?php if ($cliente->pj == 1) : ?>
            / <a href="<?php echo site_url('/clientes/parceiros'); ?>">Parceiros</a>
        <?php endif; ?>
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <?php if ($cliente->pj == 1) : ?>
                    <li class="breadcrumb-item"><a href="<?php echo site_url('/clientes/parceiros'); ?>">Parceiros</a></li>
                <?php endif; ?>
                <li class="breadcrumb-item active" aria-current="page"><?= esc($cliente->nome); ?></li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <div class="btn-group">
            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                Ações
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="<?php echo site_url("clientes/editar/$cliente->id"); ?>">
                    <i class="bx bx-edit-alt me-2"></i>Editar cliente</a></li>
                <li><a class="dropdown-item" href="<?php echo site_url("clientes/historico/$cliente->id"); ?>">
                    <i class="bx bx-history me-2"></i>Histórico de atendimentos</a></li>
                <li><hr class="dropdown-divider"></li>
                
                <?php if ($cliente->deletado_em == null) : ?>
                    <li><a class="dropdown-item text-danger" href="<?php echo site_url("clientes/excluir/$cliente->id"); ?>">
                        <i class="bx bx-trash me-2"></i>Excluir cliente</a></li>
                <?php else : ?>
                    <li><a class="dropdown-item text-success" href="<?php echo site_url("clientes/desfazerexclusao/$cliente->id"); ?>">
                        <i class="bx bx-undo me-2"></i>Restaurar cliente</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <a href="<?php echo $cliente->pj == 1 ? site_url("clientes/parceiros") : site_url("clientes") ?>" class="btn btn-secondary ms-2">
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
                        <div class="user-avatar rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #6c757d;">
                            <i class="bx bx-user text-white fs-3"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <?php if ($cliente->grupo_id == 3) : ?>
                            <h5 class="card-title mb-0">
                                <i class="lni lni-crown text-warning me-1"></i>
                                <?php echo esc($cliente->nome); ?>
                            </h5>
                            <p class="text-muted mb-0">Cliente Premium</p>
                        <?php elseif ($cliente->grupo_id == 4) : ?>
                            <h5 class="card-title mb-0">
                                <i class="bx bx-group text-info me-1"></i>
                                <?php echo esc($cliente->nome); ?>
                            </h5>
                            <p class="text-muted mb-0">Parceiro</p>
                        <?php elseif ($cliente->grupo_id == 5) : ?>
                            <h5 class="card-title mb-0">
                                <i class="bx bx-star text-warning me-1"></i>
                                <?php echo esc($cliente->nome); ?>
                            </h5>
                            <p class="text-muted mb-0">Influencer</p>
                        <?php else : ?>
                            <h5 class="card-title mb-0"><?php echo esc($cliente->nome); ?></h5>
                            <p class="text-muted mb-0">Cliente</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <hr>
                
                <div class="row g-3">
                    <div class="col-12">
                        <div class="d-flex align-items-center info-item">
                            <i class="bx bx-id-card text-primary me-2"></i>
                            <div>
                                <small class="text-muted">Documento:</small>
                                <p class="mb-0"><?php echo $cliente->pj == 1 ? 'CNPJ: ' : 'CPF: '; ?><?php echo esc($cliente->cpf); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="d-flex align-items-center info-item">
                            <i class="bx bx-envelope text-primary me-2"></i>
                            <div>
                                <small class="text-muted">Email:</small>
                                <p class="mb-0"><?php echo esc($cliente->email); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="d-flex align-items-center info-item">
                            <i class="bx bx-phone text-primary me-2"></i>
                            <div>
                                <small class="text-muted">Telefone:</small>
                                <p class="mb-0"><?php echo esc($cliente->telefone); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="d-flex align-items-center info-item">
                            <i class="bx bx-time text-primary me-2"></i>
                            <div>
                                <small class="text-muted">Status:</small>
                                <p class="mb-0"><?php echo $cliente->exibeSituacao(); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row g-2">
                    <div class="col-6">
                        <small class="text-muted">Criado:</small>
                        <p class="mb-0"><?php echo $cliente->created_at->humanize(); ?></p>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Atualizado:</small>
                        <p class="mb-0"><?php echo $cliente->updated_at->humanize(); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow radius-10">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6">
                        <h6 class="card-title mb-3">
                            <i class="bx bx-map me-2"></i>Dados de Endereço
                        </h6>
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <small class="text-muted">CEP:</small>
                                <p class="mb-0"><?php echo esc($cliente->cep ?? 'Não informado'); ?></p>
                            </div>
                            <div class="col-9">
                                <small class="text-muted">Endereço:</small>
                                <p class="mb-0"><?php echo esc($cliente->endereco ?? 'Não informado'); ?></p>
                            </div>
                            <div class="col-3">
                                <small class="text-muted">Número:</small>
                                <p class="mb-0"><?php echo esc($cliente->numero ?? 'S/N'); ?></p>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Bairro:</small>
                                <p class="mb-0"><?php echo esc($cliente->bairro ?? 'Não informado'); ?></p>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">Cidade:</small>
                                <p class="mb-0"><?php echo esc($cliente->cidade ?? 'Não informado'); ?></p>
                            </div>
                            <div class="col-2">
                                <small class="text-muted">Estado:</small>
                                <p class="mb-0"><?php echo esc($cliente->estado ?? 'N/A'); ?></p>
                            </div>
                        </div>
                    </div>

                    <?php if ($cliente->grupo_id == 4) : ?>
                        <!-- Informações específicas para parceiros -->
                        <div class="col-lg-6">
                            <h6 class="card-title mb-3">
                                <i class="bx bx-group me-2"></i>Dados de Parceiro
                            </h6>
                            
                            <div class="row g-3">
                                <div class="col-12">
                                    <small class="text-muted">Tipo de Pessoa:</small>
                                    <p class="mb-0">
                                        <?php if ($cliente->pj == 1) : ?>
                                            <span class="badge bg-info">
                                                <i class="bx bx-buildings me-1"></i>Pessoa Jurídica (PJ)
                                            </span>
                                        <?php else : ?>
                                            <span class="badge bg-secondary">
                                                <i class="bx bx-user me-1"></i>Pessoa Física (PF)
                                            </span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">Tipo de Parceria:</small>
                                    <p class="mb-0">
                                        <?php 
                                            $tipos = [
                                                'comercial' => ['nome' => 'Comercial', 'class' => 'bg-primary'],
                                                'artista' => ['nome' => 'Artista', 'class' => 'bg-warning'],
                                                'indie' => ['nome' => 'Indie', 'class' => 'bg-info'],
                                                'estrategica' => ['nome' => 'Estratégica', 'class' => 'bg-success'],
                                                'fornecedor' => ['nome' => 'Fornecedor', 'class' => 'bg-secondary'],
                                                'patrocinador' => ['nome' => 'Patrocinador', 'class' => 'bg-danger'],
                                                'outro' => ['nome' => 'Outro', 'class' => 'bg-dark']
                                            ];
                                            if (isset($tipos[$cliente->tipo_parceria])) {
                                                echo '<span class="badge ' . $tipos[$cliente->tipo_parceria]['class'] . '">' . $tipos[$cliente->tipo_parceria]['nome'] . '</span>';
                                            } else {
                                                echo '<span class="badge bg-light text-dark">' . ($cliente->tipo_parceria ?? 'Não informado') . '</span>';
                                            }
                                        ?>
                                    </p>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">Área de Atuação:</small>
                                    <p class="mb-0"><?php echo esc($cliente->area_atuacao ?? 'Não informado'); ?></p>
                                </div>
                                <?php if (!empty($cliente->observacoes)) : ?>
                                    <div class="col-12">
                                        <small class="text-muted">Observações:</small>
                                        <p class="mb-0"><?php echo nl2br(esc($cliente->observacoes)); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else : ?>
                        <!-- Informações adicionais para outros tipos de cliente -->
                        <div class="col-lg-6">
                            <h6 class="card-title mb-3">
                                <i class="bx bx-info-circle me-2"></i>Informações Adicionais
                            </h6>
                            
                            <div class="row g-3">
                                <div class="col-12">
                                    <small class="text-muted">Grupo:</small>
                                    <p class="mb-0">
                                        <?php 
                                            $grupos = [
                                                2 => 'Cliente',
                                                3 => 'Membro Premium',
                                                4 => 'Parceiro',
                                                5 => 'Influencer'
                                            ];
                                            echo $grupos[$cliente->grupo_id] ?? 'Não identificado';
                                        ?>
                                    </p>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">ID do Cliente:</small>
                                    <p class="mb-0">#<?php echo $cliente->id; ?></p>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">ID do Usuário:</small>
                                    <p class="mb-0">#<?php echo $cliente->usuario_id; ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>


<?php echo $this->endSection() ?>




<?php echo $this->section('scripts') ?>

<!-- Aqui coloco os scripts da view-->

<?php echo $this->endSection() ?>