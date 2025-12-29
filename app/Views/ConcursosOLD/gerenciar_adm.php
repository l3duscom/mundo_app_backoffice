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
        <a href="<?= site_url('/concursos/add_kpop/' . $concurso->id) ?>" class="btn btn-sm btn-primary mt-0 shadow">Realizar inscrição</a>

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
                                    <div class="breadcrumb-title pe-3"><strong class="font-20"><?= $inscricao->nome_social ?></strong></div>
                                    <div class="ps-3">
                                        <nav aria-label="breadcrumb">
                                            <ol class="breadcrumb mb-0 p-0">
                                                <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-star"></i></a>
                                                </li>
                                                <li class="breadcrumb-item active" aria-current="page">
                                                    <?= $inscricao->nota_total ?>

                                                </li>
                                            </ol>
                                        </nav>
                                    </div>
                                    <div class="ms-auto">
                                        <div class="btn-group mt-2">
                                            <a href="<?= site_url('/concursos/avaliacao/' . $inscricao->id) ?>" class="btn btn-primary mt-0 shadow">Iniciar avaliação</a>

                                        </div>
                                    </div>

                                </div>
                                <div class="row">


                                    <hr class="mt-2">
                                    <div class="col-lg-4">
                                        <p class="mb-0 text-muted" style="font-size: 10px;">Personagem</p>

                                        <strong><?= $inscricao->personagem ?></strong>
                                    </div>
                                    <div class="col-lg-4">
                                        <p class="mb-0 text-muted" style="font-size: 10px;">Obra</p>
                                        <strong><?= $inscricao->obra ?></strong>
                                    </div>
                                    <div class="col-lg-4">
                                        <p class="mb-0 text-muted" style="font-size: 10px;">Gênero</p>
                                        <strong><?= $inscricao->genero ?></strong>
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