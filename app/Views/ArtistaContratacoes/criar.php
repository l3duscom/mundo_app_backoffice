<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">
        <a href="<?php echo site_url('/artista-contratacoes'); ?>">Contratações</a>
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('/artista-contratacoes'); ?>">Contratações</a></li>
                <li class="breadcrumb-item active">Nova</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="<?php echo site_url('artista-contratacoes'); ?>" class="btn btn-secondary">
            <i class="bx bx-arrow-back me-1"></i>Voltar
        </a>
    </div>
</div>

<div class="card shadow">
    <div class="card-header">
        <h5 class="mb-0"><i class="bx bx-calendar-plus me-2"></i><?php echo $titulo; ?></h5>
    </div>
    <div class="card-body">
        <form id="formContratacao" method="post" action="<?php echo site_url('artista-contratacoes/cadastrar'); ?>">
            <input type="hidden" name="<?php echo csrf_token(); ?>" value="<?php echo csrf_hash(); ?>">
            <input type="hidden" name="event_id" value="<?php echo $contratacao->event_id ?? evento_selecionado(); ?>">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Artista <span class="text-danger">*</span></label>
                    <select name="artista_id" id="selectArtista" class="form-select" required>
                        <option value="">Selecione o artista...</option>
                        <?php foreach ($artistas as $a): ?>
                        <option value="<?php echo $a->id; ?>"><?php echo esc($a->nome_artistico); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">
                        Não encontrou? <a href="<?php echo site_url('artistas/criar'); ?>" target="_blank">Cadastrar novo artista</a>
                    </small>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Situação</label>
                    <select name="situacao" class="form-select">
                        <option value="rascunho">Rascunho</option>
                        <option value="confirmado">Confirmado</option>
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Palco/Local</label>
                    <input type="text" name="palco" class="form-control" placeholder="Ex: Palco Principal">
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Data da Apresentação</label>
                    <input type="date" name="data_apresentacao" class="form-control">
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Horário Início</label>
                    <input type="time" name="horario_inicio" class="form-control">
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Horário Fim</label>
                    <input type="time" name="horario_fim" class="form-control">
                </div>
            </div>

            <hr>
            <h6 class="text-primary"><i class="bx bx-money me-2"></i>Cachê</h6>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Valor do Cachê</label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="text" name="valor_cache" class="form-control money" placeholder="0,00">
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Forma de Pagamento</label>
                    <select name="forma_pagamento" class="form-select">
                        <option value="">Selecione...</option>
                        <option value="PIX">PIX</option>
                        <option value="Transferência">Transferência</option>
                        <option value="Boleto">Boleto</option>
                        <option value="Cheque">Cheque</option>
                        <option value="Dinheiro">Dinheiro</option>
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Parcelas</label>
                    <select name="quantidade_parcelas" class="form-select">
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?>x</option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-12 mb-3">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="3" placeholder="Observações sobre a contratação..."></textarea>
                </div>
            </div>

            <hr>
            <div class="d-flex justify-content-end">
                <a href="<?php echo site_url('artista-contratacoes'); ?>" class="btn btn-light me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bx bx-save me-2"></i>Salvar Contratação
                </button>
            </div>
        </form>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Select2 para busca de artistas
    $('#selectArtista').select2({
        theme: 'bootstrap-5',
        placeholder: 'Digite para buscar o artista...',
        allowClear: true,
        width: '100%'
    });

    // Máscara de moeda simples
    $('.money').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        value = (parseInt(value) / 100).toFixed(2);
        $(this).val(value.replace('.', ','));
    });
});
</script>
<?php echo $this->endSection() ?>
