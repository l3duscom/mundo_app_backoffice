<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>


<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.css') ?>" />



<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>

<?php if ($evento) : ?>
    <div class="card rounded-4 mb-3">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-0">Concursos do Evento: <strong><?= esc($evento->nome) ?></strong></h4>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= site_url("concursos/criar/{$evento->id}") ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg"></i> Novo Concurso
                    </a>
                    <a href="<?= site_url('/') ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Trocar Evento
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (session()->has('sucesso')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bx bx-check-circle me-2"></i>
            <?= session('sucesso') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (session()->has('atencao')): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bx bx-error-circle me-2"></i>
            <?= session('atencao') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (session()->has('erro')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bx bx-x-circle me-2"></i>
            <?= session('erro') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="row">



    <!--end breadcrumb-->
    <div class="ms-auto">

    </div>


    <div class="row">
        <?php foreach ($concursos as $concurso) : ?>
            <div class="col-lg-12">

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card shadow radius-10">
                            <div class="card-body">

                                <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                                    <div class="breadcrumb-title pe-3"><strong class="font-20"><?= $concurso->nome ?></strong></div>
                                    <div class="ps-3">
                                        <nav aria-label="breadcrumb">
                                            <ol class="breadcrumb mb-0 p-0">
                                                <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-calendar-star"></i></a>
                                                </li>
                                                <li class="breadcrumb-item active" aria-current="page">
                                                    <?= $concurso->nome_evento ?>

                                                </li>
                                            </ol>
                                        </nav>
                                    </div>

                                </div>
                                <div class="row">


                                    <hr class="mt-2">
                                    <div class="col-lg-4">
                                        <p class="mb-0 text-muted" style="font-size: 10px;">Código</p>

                                        <strong><?= $concurso->codigo ?></strong>
                                    </div>
                                    <div class="col-lg-4">
                                        <p class="mb-0 text-muted" style="font-size: 10px;">Júri</p>
                                        <strong><?= $concurso->juri ?> jurados</strong>
                                    </div>
                                    <div class="col-lg-4">
                                        <p class="mb-0 text-muted" style="font-size: 10px;">Status</p>
                                        <?php if ($concurso->ativo == 1) : ?>
                                            <strong><i class="fadeIn animated bx bx-circle" style="color: green; font-size: 18px;"></i> Ativo</strong>
                                        <?php else : ?>
                                            <strong><i class="fadeIn animated bx bx-circle" style="color: red;"></i> Inativo</strong>
                                        <?php endif ?>

                                    </div>


                                </div>
                                <hr class="mt-2">



                                <div class="col-lg-12">
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="<?= site_url('/concursos/gerenciar/' . $concurso->id) ?>" class="btn btn-sm btn-primary mt-0 shadow">GERENCIAR / VOTAR</a>
                                        <a href="<?= site_url('/concursos/editar/' . $concurso->id) ?>" class="btn btn-sm btn-outline-secondary mt-0"><i class="bx bx-edit"></i> Editar</a>
                                        <a href="<?= site_url('/concursos/duplicar/' . $concurso->id) ?>" class="btn btn-sm btn-outline-info mt-0" onclick="return confirm('Deseja duplicar este concurso?');"><i class="bx bx-copy"></i> Duplicar</a>
                                        <a href="<?= site_url('/concursos/excluir/' . $concurso->id) ?>" class="btn btn-sm btn-outline-danger mt-0" onclick="return confirm('Tem certeza que deseja excluir este concurso? Esta ação não pode ser desfeita.');"><i class="bx bx-trash"></i> Excluir</a>
                                    </div>
                                    
                                    <?php 
                                    // Gerar link de inscrição baseado no tipo
                                    if ($concurso->tipo == 'kpop') {
                                        $linkInscricao = site_url("concursos/inscricao_kpop/{$concurso->id}");
                                    } elseif ($concurso->tipo == 'apresentacao_cosplay') {
                                        $linkInscricao = site_url("concursos/inscricao_cosplay_apresentacao/{$concurso->id}");
                                    } else {
                                        $linkInscricao = site_url("concursos/inscricao_cosplay/{$concurso->id}");
                                    }
                                    ?>
                                    <div class="mt-2">
                                        <small class="text-muted">Link de inscrição:</small>
                                        <div class="input-group input-group-sm mt-1">
                                            <input type="text" class="form-control form-control-sm bg-light" value="<?= $linkInscricao ?>" readonly id="link-<?= $concurso->id ?>">
                                            <button class="btn btn-outline-secondary" type="button" onclick="copiarLink('link-<?= $concurso->id ?>')" title="Copiar link">
                                                <i class="bx bx-copy"></i>
                                            </button>
                                            <a href="<?= $linkInscricao ?>" target="_blank" class="btn btn-outline-primary" title="Abrir link">
                                                <i class="bx bx-link-external"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
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

<script>
function copiarLink(inputId) {
    const input = document.getElementById(inputId);
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(function() {
        // Feedback visual
        const btn = input.nextElementSibling;
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="bx bx-check"></i>';
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-success');
        setTimeout(function() {
            btn.innerHTML = originalHtml;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-secondary');
        }, 2000);
    });
}
</script>


<?php echo $this->endSection() ?>