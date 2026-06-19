<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="container-fluid py-4">

    <!-- Header de boas-vindas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg,#6C038F 0%,#9b3bc9 100%); border-radius: 12px;">
                <div class="card-body py-4">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-white bg-opacity-25 p-3 me-3">
                            <i class="bi bi-shield-check text-white" style="font-size: 1.6rem;"></i>
                        </div>
                        <div>
                            <h4 class="text-white mb-1">Olá, <?= esc(usuario_logado()->nome) ?>!</h4>
                            <p class="text-white-50 mb-0 small">Painel da equipe — acesso a controle de entrada e cadastros operacionais.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Administração -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body">
                    <h6 class="text-muted mb-3"><i class="bx bx-cog me-1"></i>Controle de Acesso e Operação</h6>
                    <div class="row g-3">
                        <?php if (usuario_logado()->temPermissaoPara('access-controll')) : ?>
                            <div class="col-md-3 col-sm-6">
                                <a href="<?= site_url('/acessos/bilheteria') ?>" class="btn btn-success shadow w-100 py-3">
                                    <i class="bx bx-store-alt d-block mb-1" style="font-size:1.6rem;"></i>
                                    <span>Bilheteria</span>
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="<?= site_url('/acessos/salavip') ?>" class="btn btn-success shadow w-100 py-3">
                                    <i class="bx bx-crown d-block mb-1" style="font-size:1.6rem;"></i>
                                    <span>Sala VIP</span>
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="<?= site_url('/ingressos/add') ?>" class="btn btn-success shadow w-100 py-3">
                                    <i class="bx bx-plus-circle d-block mb-1" style="font-size:1.6rem;"></i>
                                    <span>Add Ingressos</span>
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="<?= site_url('/meets/validar') ?>" class="btn btn-success shadow w-100 py-3">
                                    <i class="bx bx-camera d-block mb-1" style="font-size:1.6rem;"></i>
                                    <span>Validar Meet&amp;Greet</span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Perfil -->
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                <div class="card-body">
                    <h6 class="text-muted mb-3"><i class="bx bx-user me-1"></i>Minha Conta</h6>
                    <p class="mb-3 small text-muted">Atualize seus dados de acesso e informações de perfil.</p>
                    <a href="<?= site_url('/usuarios/perfil') ?>" class="btn btn-outline-primary w-100">
                        <i class="bx bx-edit me-1"></i>Gerenciar Perfil
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<?php echo $this->endSection() ?>
