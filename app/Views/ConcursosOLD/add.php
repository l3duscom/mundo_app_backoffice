<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>

<!-- Aqui coloco os estilos da view-->

<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>


<div class="row">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3"><?= $concurso->nome ?> </div> &nbsp;&nbsp;&nbsp; Inscrições
    </div>
    <!--end breadcrumb-->

    <div class="col-lg-10">

        <div class="block">

            <div class="block-body">

                <!-- Exibirá os retornos do backend -->
                <div id="response">


                </div>

                <div class="card shadow bg-dark radius-10">
                    <div class="card-body">
                        <?php echo form_open_multipart('Concursos/registrar_inscricao') ?>

                        <?= csrf_field() ?>

                        <input type="hidden" name="concurso_id" value="<?= $concurso->id ?>">

                        <div class="row">
                            <div class="form-group col-md-6">

                                <input type="email" name="email" placeholder="Informe seu email" class="form-control form-control-lg mb-2 shadow" style="padding:13px;">
                            </div>
                            <div class="form-group col-md-6">

                                <input type="text" name="nome_social" placeholder="Informe seu nome social" class="form-control form-control-lg mb-2 shadow " style="padding:13px;">

                            </div>
                            <div class="form-group col-md-10">

                                <input type="text" name="motivacao" placeholder="Qual sua motivação?" class="form-control form-control-lg mb-2 shadow " style="padding:13px;">

                            </div>
                            <div class="form-group col-md-2">

                                <input type="number" name="tempo" placeholder="Tempo" class="form-control form-control-lg mb-2 shadow " style="padding:13px;">

                            </div>
                            <div class="form-group col-md-4">

                                <input type="text" name="personagem" placeholder="Informe o personagem" class="form-control form-control-lg mb-2 shadow " style="padding:13px;">

                            </div>
                            <div class="form-group col-md-4">

                                <input type="text" name="obra" placeholder="Informe a obra" class="form-control form-control-lg mb-2 shadow " style="padding:13px;">

                            </div>
                            <div class="form-group col-md-4">

                                <input type="text" name="genero" placeholder="Informe o genero" class="form-control form-control-lg mb-2 shadow " style="padding:13px;">

                            </div>

                            <div class="form-group col-md-6">
                                <label class="form-control-label">Escolha uma imagem</label>
                                <input type="file" name="referencia" class="form-control">
                            </div>
                            <div class="form-group col-md-6">
                                <label class="form-control-label">Material de Apoio</label>
                                <input type="file" name="apoio" class="form-control">
                            </div>
                            <hr>
                            <div class="form-group col-md-12">

                                <input type="text" name="observacoes" placeholder="Observações" class="form-control form-control-lg mb-2 shadow " style="padding:13px;">

                            </div>



                            <div class="d-grid gap-2 mb-0">
                                <input id="btn-salvar" type="submit" value="Finalizar inscrição" class="btn btn-primary btn-lg mt-0">

                            </div>
                        </div>





                        <?php echo form_close(); ?>
                    </div>
                </div>

            </div>



        </div> <!-- ./ block -->

    </div>


</div>


<?php echo $this->endSection() ?>




<?php echo $this->section('scripts') ?>






<?php echo $this->endSection() ?>