<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>


<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.css') ?>" />



<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>


<div class="row">


    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3"> <?= $concurso->nome ?></div>

    </div>

    <!--end breadcrumb-->
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
                                    <?php if ($inscricao->integrantes > 1) : ?>
                                        <div class="breadcrumb-title pe-3"><strong class="font-20"><?= $inscricao->grupo ?></strong></div><span style="padding-left: 10px;">Grupos / Duplas</span>
                                    <?php else : ?>
                                        <div class="breadcrumb-title pe-3"><strong class="font-20"><?= $inscricao->nome_social ?></strong></div><span style="padding-left: 10px;">Solo</span>
                                    <?php endif; ?>

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
                                            <?php if ($inscricao->status == 'INICIADA' || $inscricao->status == 'CANCELADA') : ?>
                                                <a href="<?= site_url('/concursos/aprovaInscricao/' . $inscricao->id) ?>" class="btn btn-success mt-0 shadow">Aprovar inscrição</a>
                                                <a href="<?= site_url('/concursos/rejeitaInscricao/' . $inscricao->id) ?>" class="btn btn-danger mt-0 shadow">Rejeitar inscrição</a>
                                            <?php elseif ($inscricao->status == 'APROVADA') : ?>
                                                <a href="<?= site_url('/concursos/checkinonline/' . $inscricao->id) ?>" class="btn btn-info mt-0 shadow">Realizar Checkin Online</a>
                                                <a href="<?= site_url('/concursos/cancelaInscricao/' . $inscricao->id) ?>" class="btn btn-white mt-0 shadow">Cancelar / Desclassificar</a>
                                            <?php elseif ($inscricao->status == 'CHECKIN-ONLINE') : ?>
                                                <a href="<?= site_url('/concursos/checkin/' . $inscricao->id) ?>" class="btn btn-warning mt-0 shadow">Realizar Checkin</a>
                                                <a href="<?= site_url('/concursos/cancelaInscricao/' . $inscricao->id) ?>" class="btn btn-white mt-0 shadow">Cancelar / Desclassificar</a>
                                            <?php elseif ($inscricao->status == 'CHECKIN') : ?>
                                                <a href="<?= site_url('/concursos/avaliacao/' . $inscricao->id) ?>" class="btn btn-dark mt-0 shadow disabled">Checkin realizado em <?= date('d/m/Y H:i:s', strtotime($inscricao->updated_at)) ?></a>
                                                <a href="<?= site_url('/concursos/avaliacao/' . $inscricao->id) ?>" class="btn btn-primary mt-0 shadow">Iniciar avaliação</a>
                                            <?php elseif ($inscricao->status == 'REJEITADA') : ?>
                                                <a href="<?= site_url('/concursos/aprovaInscricao/' . $inscricao->id) ?>" class="btn btn-success mt-0 shadow ">Desfazer e aprovar</a>
                                            <?php endif; ?>

                                        </div>
                                    </div>

                                </div>
                                <div class="row">


                                    <hr class="mt-2">
                                    <div class="col-lg-3">
                                        <p class="mb-0 text-muted" style="font-size: 10px;">Status</p>

                                        <strong><?= $inscricao->status ?> </strong>
                                    </div>
                                    <div class="col-lg-3">
                                        <p class="mb-0 text-muted" style="font-size: 10px;">Whatsapp</p>

                                        <strong><a href="https://wa.me/55<?= str_replace(array("(", ")", " ", "-"), "", $inscricao->telefone) ?>" target="_blank"><?= $inscricao->telefone ?></a> </strong>
                                    </div>
                                    <div class="col-lg-3">
                                        <p class="mb-0 text-muted" style="font-size: 10px;">E-mail</p>

                                        <strong><a href="mailto:<?= $inscricao->email ?>" target="_blank"><?= $inscricao->email ?></a> </strong>
                                    </div>
                                    <div class="col-lg-3">
                                        <p class="mb-0 text-muted" style="font-size: 10px;">Identificação</p>

                                        <strong><?= $inscricao->cpf ?> </strong>
                                    </div>
                                    <div class="mt-2"></div>
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
                                    <div class="col-lg-12">
                                        <p class="mb-0 text-muted" style="font-size: 10px;">Referência</p>
                                        <span><a href="<?= site_url("concursos/imagem/$inscricao->referencia"); ?>" target="_blank"> Visualizar imagem de referência</a></span>
                                    </div>

                                    <div class="col-lg-12">
                                        <p class="mb-0 text-muted" style="font-size: 10px;">Vídeo Led</p>
                                        <span><a href="<?= site_url("concursos/imagem/$inscricao->video_led"); ?>" target="_blank"> Visualizar Video LED</a></span>
                                    </div>





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