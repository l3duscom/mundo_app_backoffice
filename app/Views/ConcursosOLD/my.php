<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>


<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.css') ?>" />



<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>


<div class="row">



    <div class="ms-auto">

    </div>


    <div class="row">
        <?php foreach ($inscricoes as $inscricao) : ?>
            <div class="col-lg-12">

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card shadow radius-10">
                            <div class="card-body">

                                <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                                    <div class="breadcrumb-title pe-3"><strong class="font-20"><?= $inscricao->nome_concurso ?></strong></div><span style="padding-left: 10px;"><?= $inscricao->categoria ?></span>


                                    <div class="ps-3">
                                        <nav aria-label="breadcrumb">
                                            <ol class="breadcrumb mb-0 p-0">
                                                <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-star"></i></a>
                                                </li>
                                                <li class="breadcrumb-item active" aria-current="page">
                                                    <?= $inscricao->codigo ?>

                                                </li>
                                            </ol>
                                        </nav>
                                    </div>
                                    <div class="ms-auto">
                                        <div class="btn-group mt-2">
                                            <?php if ($inscricao->status == 'INICIADA') : ?>
                                                <a href="#" class="btn btn-dark mt-0 shadow disabled">Em avaliação</a>
                                            <?php elseif ($inscricao->status == 'APROVADA') : ?>
                                                <a href="<?= site_url('/concursos/checkin/' . $inscricao->id) ?>" class="btn btn-success mt-0 shadow disabled">Realizar Checkin</a>
                                            <?php elseif ($inscricao->status == 'CHECKIN') : ?>
                                                <a href="<?= site_url('/concursos/avaliacao_kpop/' . $inscricao->id) ?>" class="btn btn-dark mt-0 shadow disabled">Checkin realizado em <?= date('d/m/Y H:i:s', strtotime($inscricao->updated_at)) ?></a>
                                            <?php endif; ?>

                                        </div>
                                    </div>

                                </div>
                                <div class="row">


                                    <hr class="mt-2">
                                    <div class="col-lg-3">
                                        <p class="mb-0 text-muted" style="font-size: 10px;">Status</p>
                                        <?php if ($inscricao->status == 'INICIADA') : ?>
                                            <storng class="text-secondary" style="font-weight: 700;">INSCRIÇÃO RECEBIDA</storng>
                                        <?php elseif ($inscricao->status == 'CANCELADA') : ?>
                                            <storng class="text-danger" style="font-weight: 700;">INSCRIÇÃO CANCELADA</storng>
                                        <?php elseif ($inscricao->status == 'APROVADA') : ?>
                                            <storng class="text-warning" style="font-weight: 700;">APROVADA</storng>
                                        <?php elseif ($inscricao->status == 'CHECKIN') : ?>
                                            <storng class="text-success" style="font-weight: 700;">APRESENTAÇÃO LIBERADA</storng>
                                        <?php elseif ($inscricao->status == 'REJEITADA') : ?>
                                            <storng class="text-danger" style="font-weight: 700;">INSCRIÇÃO REJEITADA</storng>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-lg-3">
                                        <p class="mb-0 text-muted" style="font-size: 10px;">Ordem de apresentação</p>
                                        <?php if ($inscricao->status == 'CHECKIN') : ?>
                                            <strong>Você é o <?= $inscricao->ordem ?>º da fila</strong>
                                        <?php else : ?>
                                            <strong class="text-muted">Checkin de palco pendente</strong>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-lg-3">
                                        <p class="mb-0 text-muted" style="font-size: 10px;">Inscrição realziada em:</p>

                                        <strong><?= date('d/m/Y H:i:s', strtotime($inscricao->created_at)) ?></strong>

                                    </div>
                                    <div class="col-lg-3">
                                        <p class="mb-0 text-muted" style="font-size: 10px;">Última atualização:</p>

                                        <strong><?= date('d/m/Y H:i:s', strtotime($inscricao->updated_at)) ?></strong>

                                    </div>

                                    <div class="mt-2"></div>
                                    <?php if ($inscricao->tipo == 'desfile_cosplay' || $inscricao->tipo == 'apresentacao_cosplay' || $inscricao->tipo == 'cosplay_kids') : ?>
                                        <div class="col-lg-3">
                                            <p class="mb-0 text-muted" style="font-size: 10px;">Personagem</p>

                                            <strong><?= $inscricao->personagem ?> </strong>
                                        </div>
                                        <div class="col-lg-3">
                                            <p class="mb-0 text-muted" style="font-size: 10px;">Obra</p>

                                            <strong><?= $inscricao->obra ?> </strong>
                                        </div>
                                        <div class="col-lg-6">
                                            <p class="mb-0 text-muted" style="font-size: 10px;">Gênero</p>

                                            <strong><?= $inscricao->genero ?> </strong>
                                        </div>
                                        <div class="mt-2"></div>
                                        <div class="col-lg-4">
                                            <p class="mb-0 text-muted" style="font-size: 10px;">Referência</p>
                                            <span><a href="<?= site_url("concursos/imagem/$inscricao->referencia"); ?>" target="_blank"> Visualizar imagem de referência</a></span>
                                        </div>
                                    <?php else : ?>
                                        <div class="col-lg-12">
                                            <p class="mb-0 text-muted" style="font-size: 10px;">Video classificatórias</p>

                                            <span><a href="<?= $inscricao->video_apresentacao ?>" target="_blank"> <?= $inscricao->video_apresentacao ?></a></span>
                                        </div>
                                        <div class="mt-2"></div>
                                        <div class="col-lg-4">
                                            <p class="mb-0 text-muted" style="font-size: 10px;">Referência</p>
                                            <span><a href="<?= site_url("concursos/imagem/$inscricao->referencia"); ?>" target="_blank"> Visualizar imagem de referência</a></span>
                                        </div>
                                        <div class="col-lg-4">
                                            <p class="mb-0 text-muted" style="font-size: 10px;">Áudio Apresentação</p>
                                            <span><a href="<?= site_url("concursos/imagem/$inscricao->musica"); ?>" target="_blank"> Tocar música</a></span>
                                        </div>
                                        <div class="col-lg-4">
                                            <p class="mb-0 text-muted" style="font-size: 10px;">Vídeo LED</p>
                                            <span><a href="<?= site_url("concursos/imagem/$inscricao->video_led"); ?>" target="_blank"> Visualizar video</a></span>
                                        </div>
                                    <?php endif; ?>





                                </div>
                                <hr class="mt-2">




                            </div>
                        </div>
                    </div>




                </div>




            <?php endforeach; ?>

            <hr>



            </div>


    </div>
</div>






<?php echo $this->endSection() ?>


<?php echo $this->section('scripts') ?>


<script type="text/javascript" src="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.js') ?>"></script>






<?php echo $this->endSection() ?>