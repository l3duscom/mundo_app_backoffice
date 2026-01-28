<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>

<style>
    .info-card {
        border-left: 4px solid;
        transition: transform 0.2s ease;
    }
    .info-card:hover {
        transform: translateY(-2px);
    }
    .info-card.primary {
        border-left-color: #5c6bc0;
    }
    .info-card.success {
        border-left-color: #66bb6a;
    }
    .info-card.warning {
        border-left-color: #ffa726;
    }
    .info-card.danger {
        border-left-color: #ef5350;
    }
    .info-card.info {
        border-left-color: #29b6f6;
    }
    .info-card.purple {
        border-left-color: #ab47bc;
    }
    .info-card .icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    .info-card .icon.primary {
        background-color: rgba(92, 107, 192, 0.1);
        color: #5c6bc0;
    }
    .info-card .icon.success {
        background-color: rgba(102, 187, 106, 0.1);
        color: #66bb6a;
    }
    .info-card .icon.warning {
        background-color: rgba(255, 167, 38, 0.1);
        color: #ffa726;
    }
    .info-card .icon.danger {
        background-color: rgba(239, 83, 80, 0.1);
        color: #ef5350;
    }
    .info-card .icon.info {
        background-color: rgba(41, 182, 246, 0.1);
        color: #29b6f6;
    }
    .info-card .icon.purple {
        background-color: rgba(171, 71, 188, 0.1);
        color: #ab47bc;
    }
    .grupo-badge {
        display: inline-block;
        padding: 5px 12px;
        margin: 3px;
        background-color: #f5f5f5;
        border-radius: 20px;
        font-size: 0.85rem;
        color: #555;
        border: 1px solid #e0e0e0;
    }
    .usuario-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 20px;
    }
    .usuario-header h3 {
        margin: 0;
        font-weight: 600;
    }
    .usuario-header .badge {
        font-size: 0.9rem;
        padding: 8px 15px;
    }
    .avatar-container {
        position: relative;
        display: inline-block;
    }
    .avatar-container img {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 50%;
        border: 4px solid rgba(255,255,255,0.3);
    }
    .avatar-edit-btn {
        position: absolute;
        bottom: 5px;
        right: 5px;
        background: white;
        border-radius: 50%;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        color: #667eea;
        text-decoration: none;
    }
    .avatar-edit-btn:hover {
        color: #764ba2;
        text-decoration: none;
    }
    .premium-badge {
        background: linear-gradient(135deg, #f5af19 0%, #f12711 100%);
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-weight: bold;
        display: inline-flex;
        align-items: center;
    }
    .premium-badge i {
        margin-right: 5px;
    }
</style>

<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>


<div class="row">

    <!-- Header do Usuário -->
    <div class="col-12">
        <div class="usuario-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="d-flex align-items-center">
                    <div class="avatar-container mr-4">
                        <?php if ($usuario->imagem == null) : ?>
                            <img src="<?php echo site_url('recursos/img/usuario_sem_imagem.png'); ?>" alt="Usuário sem imagem">
                        <?php else : ?>
                            <img src="<?php echo site_url("usuarios/imagem/$usuario->imagem"); ?>" alt="<?php echo esc($usuario->nome); ?>">
                        <?php endif; ?>
                        <a href="<?php echo site_url("usuarios/editarimagem/$usuario->id") ?>" class="avatar-edit-btn" title="Alterar imagem">
                            <i class="fa fa-camera"></i>
                        </a>
                    </div>
                    <div>
                        <h3><?php echo esc($usuario->nome); ?></h3>
                        <p class="mb-1 opacity-75"><i class="fa fa-envelope mr-2"></i><?php echo esc($usuario->email); ?></p>
                        <p class="mb-0 opacity-75"><i class="fa fa-hashtag mr-2"></i>ID: <?php echo $usuario->id; ?></p>
                    </div>
                </div>
                <div class="mt-3 mt-md-0 text-right">
                    <?php if ($usuario->deleted_at != null) : ?>
                        <span class="badge badge-danger"><i class="fa fa-trash"></i> Excluído</span>
                    <?php elseif ($usuario->ativo) : ?>
                        <span class="badge badge-success"><i class="fa fa-check-circle"></i> Ativo</span>
                    <?php else : ?>
                        <span class="badge badge-warning"><i class="fa fa-lock"></i> Inativo</span>
                    <?php endif; ?>

                    <?php if (isset($usuario->is_premium) && $usuario->is_premium) : ?>
                        <br><span class="premium-badge mt-2"><i class="fa fa-crown"></i> PREMIUM</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="col-lg-2 col-md-4 col-6 mb-4">
        <div class="block info-card primary h-100">
            <div class="d-flex align-items-center">
                <div class="icon primary mr-3">
                    <i class="fa fa-star fa-lg"></i>
                </div>
                <div>
                    <h4 class="mb-0"><?php echo number_format($usuario->pontos ?? 0, 0, ',', '.'); ?></h4>
                    <small class="text-muted">Pontos</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-6 mb-4">
        <div class="block info-card success h-100">
            <div class="d-flex align-items-center">
                <div class="icon success mr-3">
                    <i class="fa fa-shopping-cart fa-lg"></i>
                </div>
                <div>
                    <h4 class="mb-0"><?php echo $totalPedidos; ?></h4>
                    <small class="text-muted">Pedidos</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-6 mb-4">
        <div class="block info-card warning h-100">
            <div class="d-flex align-items-center">
                <div class="icon warning mr-3">
                    <i class="fa fa-money-bill-wave fa-lg"></i>
                </div>
                <div>
                    <h5 class="mb-0">R$ <?php echo number_format($totalGasto, 2, ',', '.'); ?></h5>
                    <small class="text-muted">Total Gasto</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-6 mb-4">
        <div class="block info-card purple h-100">
            <div class="d-flex align-items-center">
                <div class="icon purple mr-3">
                    <i class="fa fa-trophy fa-lg"></i>
                </div>
                <div>
                    <h4 class="mb-0"><?php echo $totalConquistas; ?></h4>
                    <small class="text-muted">Conquistas</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-6 mb-4">
        <div class="block info-card info h-100">
            <div class="d-flex align-items-center">
                <div class="icon info mr-3">
                    <i class="fa fa-users fa-lg"></i>
                </div>
                <div>
                    <h4 class="mb-0"><?php echo count($grupos); ?></h4>
                    <small class="text-muted">Grupos</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-6 mb-4">
        <div class="block info-card danger h-100">
            <div class="d-flex align-items-center">
                <div class="icon danger mr-3">
                    <i class="fa fa-calendar fa-lg"></i>
                </div>
                <div>
                    <h6 class="mb-0"><?php echo $usuario->created_at->format('d/m/Y'); ?></h6>
                    <small class="text-muted">Cadastro</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalhes do Usuário -->
    <div class="col-lg-6 mb-4">
        <div class="block h-100">
            <div class="title">
                <strong><i class="fa fa-user text-primary"></i> Informações do Usuário</strong>
            </div>

            <table class="table table-borderless mb-0">
                <tbody>
                    <tr>
                        <td class="text-muted" style="width: 40%;">ID:</td>
                        <td><strong>#<?php echo $usuario->id; ?></strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nome:</td>
                        <td><strong><?php echo esc($usuario->nome); ?></strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">E-mail:</td>
                        <td>
                            <a href="mailto:<?php echo esc($usuario->email); ?>"><?php echo esc($usuario->email); ?></a>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status:</td>
                        <td>
                            <?php if ($usuario->deleted_at != null) : ?>
                                <span class="badge badge-danger"><i class="fa fa-trash"></i> Excluído</span>
                            <?php elseif ($usuario->ativo) : ?>
                                <span class="badge badge-success"><i class="fa fa-check-circle"></i> Ativo</span>
                            <?php else : ?>
                                <span class="badge badge-warning"><i class="fa fa-lock"></i> Inativo</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Pontos:</td>
                        <td><strong><?php echo number_format($usuario->pontos ?? 0, 0, ',', '.'); ?></strong> pontos</td>
                    </tr>
                    <?php if (isset($usuario->is_premium)) : ?>
                    <tr>
                        <td class="text-muted">Premium:</td>
                        <td>
                            <?php if ($usuario->is_premium) : ?>
                                <span class="badge badge-warning"><i class="fa fa-crown"></i> SIM</span>
                                <?php if (isset($usuario->premium_ate) && $usuario->premium_ate) : ?>
                                    <small class="text-muted ml-2">até <?php echo date('d/m/Y', strtotime($usuario->premium_ate)); ?></small>
                                <?php endif; ?>
                            <?php else : ?>
                                <span class="badge badge-secondary">Não</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="text-muted">Criado em:</td>
                        <td><?php echo $usuario->created_at->format('d/m/Y H:i'); ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Atualizado:</td>
                        <td>
                            <?php echo $usuario->updated_at->format('d/m/Y H:i'); ?>
                            <small class="text-muted">(<?php echo $usuario->updated_at->humanize(); ?>)</small>
                        </td>
                    </tr>
                </tbody>
            </table>

            <hr>

            <!-- Botões de Ação -->
            <div class="d-flex flex-wrap">
                <div class="btn-group mr-2 mb-2">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-cog"></i> Ações
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="<?php echo site_url("usuarios/editar/$usuario->id"); ?>">
                            <i class="fa fa-edit text-primary"></i> Editar usuário
                        </a>
                        <a class="dropdown-item" href="<?php echo site_url("usuarios/editarimagem/$usuario->id"); ?>">
                            <i class="fa fa-image text-info"></i> Alterar imagem
                        </a>
                        <a class="dropdown-item" href="<?php echo site_url("usuarios/grupos/$usuario->id"); ?>">
                            <i class="fa fa-users text-success"></i> Gerenciar grupos
                        </a>

                        <div class="dropdown-divider"></div>

                        <?php if ($usuario->deleted_at == null) : ?>
                            <a class="dropdown-item text-danger" href="<?php echo site_url("usuarios/excluir/$usuario->id"); ?>">
                                <i class="fa fa-trash"></i> Excluir usuário
                            </a>
                        <?php else : ?>
                            <a class="dropdown-item text-success" href="<?php echo site_url("usuarios/desfazerexclusao/$usuario->id"); ?>">
                                <i class="fa fa-undo"></i> Restaurar usuário
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <a href="<?php echo site_url("usuarios") ?>" class="btn btn-secondary mb-2">
                    <i class="fa fa-arrow-left"></i> Voltar
                </a>
            </div>

        </div>
    </div>

    <!-- Grupos do Usuário -->
    <div class="col-lg-6 mb-4">
        <div class="block h-100">
            <div class="title d-flex justify-content-between align-items-center">
                <strong><i class="fa fa-users text-success"></i> Grupos de Acesso</strong>
                <a href="<?php echo site_url("usuarios/grupos/$usuario->id"); ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fa fa-cog"></i> Gerenciar
                </a>
            </div>

            <?php if (!empty($grupos)) : ?>
                <div class="grupos-list">
                    <?php foreach ($grupos as $grupo) : ?>
                        <span class="grupo-badge">
                            <?php if ($grupo->grupo_id == 1) : ?>
                                <i class="fa fa-shield-alt text-danger"></i>
                            <?php elseif ($grupo->grupo_id == 2) : ?>
                                <i class="fa fa-user text-primary"></i>
                            <?php else : ?>
                                <i class="fa fa-users text-success"></i>
                            <?php endif; ?>
                            <?php echo esc($grupo->nome); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="alert alert-warning mb-0">
                    <i class="fa fa-exclamation-triangle"></i> <strong>Nenhum grupo atribuído</strong>
                    <p class="mb-0 mt-2">Este usuário não está associado a nenhum grupo de acesso.</p>
                </div>
            <?php endif; ?>

            <?php if ($cliente) : ?>
                <hr>
                <div class="title">
                    <strong><i class="fa fa-address-card text-info"></i> Dados do Cliente</strong>
                </div>
                <table class="table table-borderless table-sm mb-0">
                    <tbody>
                        <?php if (isset($cliente->cpf) && $cliente->cpf) : ?>
                        <tr>
                            <td class="text-muted" style="width: 40%;">CPF:</td>
                            <td><?php echo esc($cliente->cpf); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if (isset($cliente->telefone) && $cliente->telefone) : ?>
                        <tr>
                            <td class="text-muted">Telefone:</td>
                            <td>
                                <a href="https://wa.me/55<?php echo preg_replace('/[^0-9]/', '', $cliente->telefone); ?>" target="_blank" class="text-success">
                                    <i class="fab fa-whatsapp"></i> <?php echo esc($cliente->telefone); ?>
                                </a>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php if (isset($cliente->cidade) && $cliente->cidade) : ?>
                        <tr>
                            <td class="text-muted">Cidade:</td>
                            <td><?php echo esc($cliente->cidade); ?><?php echo isset($cliente->estado) ? ' - ' . esc($cliente->estado) : ''; ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if (isset($cliente->endereco) && $cliente->endereco) : ?>
                        <tr>
                            <td class="text-muted">Endereço:</td>
                            <td><?php echo esc($cliente->endereco); ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php endif; ?>

        </div>
    </div>

</div>


<?php echo $this->endSection() ?>




<?php echo $this->section('scripts') ?>

<script>
$(document).ready(function(){
    // Inicializa popovers (Bootstrap 5)
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl, { html: true });
    });
});
</script>

<?php echo $this->endSection() ?>
