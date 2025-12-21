<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>


<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.css') ?>" />



<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>


<div class="row">

    <?php $id = usuario_logado()->id ?>
    <!--breadcrumb-->
    <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3 text-muted"><a href="<?php echo site_url('/eventos'); ?>">Eventos</a></div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <?php if (usuario_logado()->is_membro) : ?>
                        <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="fadeIn animated bx bx-crown" style="color: #ffd700;" title="Membro Premium"></i> </a></li>
                    <?php else : ?>
                        <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active" aria-current="page"><?= $titulo; ?></li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <div class="btn-group">
                <a href="<?php echo site_url("eventos/editar/$evento->id"); ?>" class="btn btn-primary">Editar evento</a>
                <a href="<?php echo site_url("eventos/excluir/$evento->id"); ?>" class="btn btn-danger">Excluir evento</a>
            </div>
        </div>

    </div>
    <!--end breadcrumb-->
    <div class="col-lg-12">

        <div class="card shadow radius-10">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-12">
                        <h5 class="card-title">Informações do Evento</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Nome:</strong> <?= esc($evento->nome) ?></p>
                                <p><strong>Slug:</strong> <?= esc($evento->slug) ?></p>
                                <p><strong>Categoria:</strong> <?= esc($evento->categoria ?? 'Não informado') ?></p>
                                <p><strong>Assunto:</strong> <?= esc($evento->assunto ?? 'Não informado') ?></p>
                                <p><strong>Produtora:</strong> <?= esc($evento->produtora ?? 'Não informado') ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Data Início:</strong> <?= $evento->data_inicio ? date('d/m/Y', strtotime($evento->data_inicio)) : 'Não informado' ?></p>
                                <p><strong>Hora Início:</strong> <?= $evento->hora_inicio ?? 'Não informado' ?></p>
                                <p><strong>Data Fim:</strong> <?= $evento->data_fim ? date('d/m/Y', strtotime($evento->data_fim)) : 'Não informado' ?></p>
                                <p><strong>Hora Fim:</strong> <?= $evento->hora_fim ?? 'Não informado' ?></p>
                                <p><strong>Nomenclatura:</strong> <?= esc($evento->nomenclatura ?? 'Não informado') ?></p>
                            </div>
                        </div>

                        <h5 class="card-title mt-4">Local do Evento</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <p><strong>Local:</strong> <?= esc($evento->local ?? 'Não informado') ?></p>
                                <p><strong>Endereço:</strong> <?= esc($evento->endereco ?? 'Não informado') ?>, <?= esc($evento->numero ?? '') ?></p>
                                <p><strong>Bairro:</strong> <?= esc($evento->bairro ?? 'Não informado') ?></p>
                                <p><strong>Cidade:</strong> <?= esc($evento->cidade ?? 'Não informado') ?> - <?= esc($evento->estado ?? '') ?></p>
                                <p><strong>CEP:</strong> <?= esc($evento->cep ?? 'Não informado') ?></p>
                            </div>
                        </div>

                        <h5 class="card-title mt-4">Descrição</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <p><?= nl2br(esc($evento->descricao ?? 'Descrição não informada')) ?></p>
                            </div>
                        </div>

                        <h5 class="card-title mt-4">Configurações</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Taxa:</strong> <?= $evento->taxa ? 'Sim' : 'Não' ?></p>
                                <p><strong>Integração:</strong> <?= $evento->integracao ? 'Sim' : 'Não' ?></p>
                                <p><strong>Próprio:</strong> <?= $evento->proprio ? 'Sim' : 'Não' ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Ativo:</strong> <?= $evento->ativo ? 'Sim' : 'Não' ?></p>
                                <p><strong>Free:</strong> <?= $evento->free ? 'Sim' : 'Não' ?></p>
                                <p><strong>Visibilidade:</strong> <?= esc($evento->visibilidade ?? 'Não informado') ?></p>
                            </div>
                        </div>

                        <h5 class="card-title mt-4">Meta Pixel</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <p><strong>Meta Pixel ID:</strong> <?= esc($evento->meta_pixel_id ?? 'Não configurado') ?></p>
                            </div>
                        </div>

                        <h5 class="card-title mt-4">Imagens do Evento</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong><i class="bx bx-user-circle me-1"></i>Avatar</strong></p>
                                <?php if (!empty($evento->avatar)): ?>
                                    <img src="<?= site_url('eventos/imagem/' . $evento->avatar) ?>" 
                                         class="img-thumbnail" style="max-height: 200px;">
                                <?php else: ?>
                                    <p class="text-muted">Nenhum avatar cadastrado</p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <p><strong><i class="bx bx-image me-1"></i>Capa (Cover)</strong></p>
                                <?php if (!empty($evento->cover)): ?>
                                    <img src="<?= site_url('eventos/imagem/' . $evento->cover) ?>" 
                                         class="img-thumbnail" style="max-width: 100%;">
                                <?php else: ?>
                                    <p class="text-muted">Nenhuma capa cadastrada</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <h5 class="card-title mt-4">Datas</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Criado em:</strong> <?= $evento->created_at ? date('d/m/Y H:i:s', strtotime($evento->created_at)) : 'Não informado' ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Atualizado em:</strong> <?= $evento->updated_at ? date('d/m/Y H:i:s', strtotime($evento->updated_at)) : 'Não informado' ?></p>
                            </div>
                        </div>

                        <?php if ($evento->deleted_at) : ?>
                            <h5 class="card-title mt-4 text-danger">Exclusão</h5>
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <p><strong>Excluído em:</strong> <?= date('d/m/Y H:i:s', strtotime($evento->deleted_at)) ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>




</div>





<?php echo $this->endSection() ?>


<?php echo $this->section('scripts') ?>


<script type="text/javascript" src="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.js') ?>"></script>



<?php echo $this->endSection() ?> 