<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3"><?= $titulo ?></div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="<?= site_url('/') ?>"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item"><a href="<?= site_url("concursos/{$evento->id}") ?>">Concursos</a></li>
                        <li class="breadcrumb-item active"><?= $concurso ? 'Editar' : 'Novo' ?></li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card shadow radius-10">
            <div class="card-header">
                <h6 class="mb-0">
                    <?= $concurso ? 'Editar Concurso' : 'Novo Concurso' ?>
                    <span class="badge bg-primary ms-2"><?= esc($evento->nome) ?></span>
                </h6>
            </div>
            <div class="card-body">
                <?php if (session()->has('erro')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= session('erro') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php 
                $action = $concurso ? site_url('concursos/atualizar') : site_url('concursos/salvar');
                echo form_open($action, ['id' => 'form-concurso']); 
                ?>
                
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                <input type="hidden" name="evento_id" value="<?= $evento->id ?>">
                <?php if ($concurso): ?>
                    <input type="hidden" name="id" value="<?= $concurso->id ?>">
                <?php endif; ?>

                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Nome do Concurso <span class="text-danger">*</span></label>
                        <input type="text" name="nome" class="form-control" 
                               value="<?= old('nome', $concurso->nome ?? '') ?>" 
                               placeholder="Ex: Concurso K-Pop Dream Festival" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tipo <span class="text-danger">*</span></label>
                        <select name="tipo" class="form-select" required>
                            <option value="">Selecione...</option>
                            <option value="kpop" <?= old('tipo', $concurso->tipo ?? '') == 'kpop' ? 'selected' : '' ?>>K-Pop</option>
                            <option value="cosplay" <?= old('tipo', $concurso->tipo ?? '') == 'cosplay' ? 'selected' : '' ?>>Cosplay (Desfile)</option>
                            <option value="apresentacao_cosplay" <?= old('tipo', $concurso->tipo ?? '') == 'apresentacao_cosplay' ? 'selected' : '' ?>>Cosplay (Apresentação)</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Quantidade de Jurados <span class="text-danger">*</span></label>
                        <input type="number" name="juri" class="form-control" 
                               value="<?= old('juri', $concurso->juri ?? 3) ?>" 
                               min="1" max="20" required>
                    </div>

                    <div class="col-md-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="ativo" id="ativo" 
                                   <?= old('ativo', $concurso->ativo ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="ativo">Concurso ativo (inscrições abertas)</label>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save"></i> <?= $concurso ? 'Salvar Alterações' : 'Criar Concurso' ?>
                    </button>
                    <a href="<?= site_url("concursos/{$evento->id}") ?>" class="btn btn-outline-secondary">
                        <i class="bx bx-x"></i> Cancelar
                    </a>
                </div>

                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<?php echo $this->endSection() ?>
