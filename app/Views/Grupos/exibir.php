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
    .permission-badge {
        display: inline-block;
        padding: 5px 12px;
        margin: 3px;
        background-color: #f5f5f5;
        border-radius: 20px;
        font-size: 0.85rem;
        color: #555;
        border: 1px solid #e0e0e0;
    }
    .grupo-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 20px;
    }
    .grupo-header h3 {
        margin: 0;
        font-weight: 600;
    }
    .grupo-header .badge {
        font-size: 0.9rem;
        padding: 8px 15px;
    }
</style>

<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>


<div class="row">

    <?php if ($grupo->id < 3) : ?>

        <div class="col-md-12">

            <div class="alert alert-info" role="alert">
                <h4 class="alert-heading"><i class="fa fa-info-circle"></i> Importante!</h4>
                <p>O grupo <b><?php echo esc($grupo->nome); ?></b> não pode ser editado ou excluído, pois os mesmos não
                    podem ter suas permissões revogadas. </p>
                <hr>
                <p class="mb-0">Não se preocupe, pois os demais grupos podem ser editados ou removidos conforme se fizer
                    necessário.</p>
            </div>
        </div>

    <?php endif; ?>

    <!-- Header do Grupo -->
    <div class="col-12">
        <div class="grupo-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h3><i class="fa fa-users mr-2"></i><?php echo esc($grupo->nome); ?></h3>
                    <p class="mb-0 mt-2 opacity-75"><?php echo esc($grupo->descricao); ?></p>
                </div>
                <div class="mt-2 mt-md-0">
                    <?php if ($grupo->deletado_em != null) : ?>
                        <span class="badge badge-danger"><i class="fa fa-trash"></i> Excluído</span>
                    <?php elseif ($grupo->exibir) : ?>
                        <span class="badge badge-success"><i class="fa fa-eye"></i> Visível</span>
                    <?php else : ?>
                        <span class="badge badge-warning"><i class="fa fa-eye-slash"></i> Oculto</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards de Informação -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="block info-card primary h-100">
            <div class="d-flex align-items-center">
                <div class="icon primary mr-3">
                    <i class="fa fa-user fa-lg"></i>
                </div>
                <div>
                    <h4 class="mb-0"><?php echo $totalUsuarios; ?></h4>
                    <small class="text-muted">Usuários no grupo</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="block info-card success h-100">
            <div class="d-flex align-items-center">
                <div class="icon success mr-3">
                    <i class="fa fa-key fa-lg"></i>
                </div>
                <div>
                    <?php if ($grupo->id == 1) : ?>
                        <h4 class="mb-0">Todas</h4>
                        <small class="text-muted">Permissões (Admin)</small>
                    <?php elseif ($grupo->id == 2) : ?>
                        <h4 class="mb-0">Limitadas</h4>
                        <small class="text-muted">Permissões (Cliente)</small>
                    <?php else : ?>
                        <h4 class="mb-0"><?php echo $totalPermissoes; ?></h4>
                        <small class="text-muted">Permissões atribuídas</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="block info-card warning h-100">
            <div class="d-flex align-items-center">
                <div class="icon warning mr-3">
                    <i class="fa fa-calendar fa-lg"></i>
                </div>
                <div>
                    <h6 class="mb-0"><?php echo $grupo->created_at->format('d/m/Y'); ?></h6>
                    <small class="text-muted">Data de criação</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="block info-card danger h-100">
            <div class="d-flex align-items-center">
                <div class="icon danger mr-3">
                    <i class="fa fa-clock fa-lg"></i>
                </div>
                <div>
                    <h6 class="mb-0"><?php echo $grupo->updated_at->humanize(); ?></h6>
                    <small class="text-muted">Última atualização</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalhes e Ações -->
    <div class="col-lg-6 mb-4">
        <div class="block h-100">
            <div class="title">
                <strong><i class="fa fa-info-circle text-primary"></i> Detalhes do Grupo</strong>
            </div>

            <table class="table table-borderless mb-0">
                <tbody>
                    <tr>
                        <td class="text-muted" style="width: 40%;">ID do Grupo:</td>
                        <td><strong>#<?php echo $grupo->id; ?></strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nome:</td>
                        <td><strong><?php echo esc($grupo->nome); ?></strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Descrição:</td>
                        <td><?php echo esc($grupo->descricao); ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Exibir nas opções:</td>
                        <td>
                            <?php if ($grupo->exibir) : ?>
                                <span class="badge badge-success"><i class="fa fa-check"></i> Sim</span>
                            <?php else : ?>
                                <span class="badge badge-secondary"><i class="fa fa-times"></i> Não</span>
                            <?php endif; ?>
                            <a tabindex="0" style="text-decoration: none;" role="button" data-bs-toggle="popover" data-bs-trigger="focus" title="Importante" data-bs-content="Esse grupo <?php echo ($grupo->exibir == true ? 'será' : 'não será'); ?> exibido como opção na hora de definir um <b>Responsável técnico</b> pela ordem de serviço." data-bs-html="true">&nbsp;&nbsp;<i class="fa fa-question-circle text-info"></i></a>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status:</td>
                        <td>
                            <?php if ($grupo->deletado_em != null) : ?>
                                <span class="badge badge-danger"><i class="fa fa-trash"></i> Excluído</span>
                            <?php else : ?>
                                <span class="badge badge-success"><i class="fa fa-check-circle"></i> Ativo</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Criado em:</td>
                        <td><?php echo $grupo->created_at->format('d/m/Y H:i'); ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Atualizado:</td>
                        <td><?php echo $grupo->updated_at->format('d/m/Y H:i'); ?> <small class="text-muted">(<?php echo $grupo->updated_at->humanize(); ?>)</small></td>
                    </tr>
                </tbody>
            </table>

            <hr>

            <!-- Botões de Ação -->
            <div class="d-flex flex-wrap">
                <?php if ($grupo->id > 2) : ?>

                <div class="btn-group mr-2 mb-2">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-cog"></i> Ações
                    </button>
                    <div class="dropdown-menu">
                            <a class="dropdown-item" href="<?php echo site_url("grupos/editar/$grupo->id"); ?>">
                                <i class="fa fa-edit text-primary"></i> Editar grupo
                            </a>

                            <a class="dropdown-item" href="<?php echo site_url("grupos/permissoes/$grupo->id"); ?>">
                                <i class="fa fa-key text-success"></i> Gerenciar permissões
                            </a>

                            <div class="dropdown-divider"></div>

                            <?php if ($grupo->deletado_em == null) : ?>

                                <a class="dropdown-item text-danger" href="<?php echo site_url("grupos/excluir/$grupo->id"); ?>">
                                    <i class="fa fa-trash"></i> Excluir grupo
                                </a>

                            <?php else : ?>

                                <a class="dropdown-item text-success" href="<?php echo site_url("grupos/desfazerexclusao/$grupo->id"); ?>">
                                    <i class="fa fa-undo"></i> Restaurar grupo
                                </a>

                            <?php endif; ?>

                        </div>

                    </div>

                <?php endif; ?>

                <a href="<?php echo site_url("grupos") ?>" class="btn btn-secondary mb-2">
                    <i class="fa fa-arrow-left"></i> Voltar
                </a>
            </div>

        </div>
    </div>

    <!-- Permissões do Grupo -->
    <div class="col-lg-6 mb-4">
        <div class="block h-100">
            <div class="title d-flex justify-content-between align-items-center">
                <strong><i class="fa fa-key text-success"></i> Permissões do Grupo</strong>
                <?php if ($grupo->id > 2) : ?>
                    <a href="<?php echo site_url("grupos/permissoes/$grupo->id"); ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fa fa-cog"></i> Gerenciar
                    </a>
                <?php endif; ?>
            </div>

            <?php if ($grupo->id == 1) : ?>
                <div class="alert alert-success mb-0">
                    <i class="fa fa-shield-alt"></i> <strong>Acesso Total</strong>
                    <p class="mb-0 mt-2">O grupo Administrador possui acesso completo a todas as funcionalidades do sistema sem restrições.</p>
                </div>
            <?php elseif ($grupo->id == 2) : ?>
                <div class="alert alert-info mb-0">
                    <i class="fa fa-user"></i> <strong>Área do Cliente</strong>
                    <p class="mb-0 mt-2">O grupo Clientes possui acesso limitado às funcionalidades de consumidor do sistema (compras, perfil, ingressos, etc).</p>
                </div>
            <?php elseif (!empty($permissoes)) : ?>
                <div class="permissoes-list">
                    <?php foreach ($permissoes as $permissao) : ?>
                        <span class="permission-badge">
                            <i class="fa fa-check-circle text-success"></i> <?php echo esc($permissao->nome); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
                <?php if ($totalPermissoes > 10) : ?>
                    <div class="mt-3">
                        <a href="<?php echo site_url("grupos/permissoes/$grupo->id"); ?>" class="btn btn-sm btn-link">
                            Ver todas as <?php echo $totalPermissoes; ?> permissões <i class="fa fa-arrow-right"></i>
                        </a>
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <div class="alert alert-warning mb-0">
                    <i class="fa fa-exclamation-triangle"></i> <strong>Nenhuma permissão atribuída</strong>
                    <p class="mb-0 mt-2">Este grupo ainda não possui permissões. Clique em "Gerenciar" para adicionar permissões.</p>
                </div>
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
