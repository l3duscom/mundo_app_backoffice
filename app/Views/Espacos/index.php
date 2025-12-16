<?php echo $this->extend('Layout/principal') ?>

<?php echo $this->section('estilos') ?>
<link href="<?php echo site_url('recursos/theme/'); ?>plugins/datatable/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
<?php echo $this->endSection() ?>

<?php echo $this->section('titulo') ?>
<?php echo $titulo; ?>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Espaços</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active">Espaços Disponíveis</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Filtro e Cards de Resumo -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card shadow radius-10">
            <div class="card-body">
                <label class="form-label fw-bold">Selecione o Evento</label>
                <select class="form-select" id="filtroEvento">
                    <option value="">Selecione...</option>
                    <?php foreach ($eventos as $evt): ?>
                    <option value="<?= $evt->id ?>"<?= ($eventIdSelecionado == $evt->id) ? ' selected' : '' ?>><?= esc($evt->nome) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <?php if ($eventIdSelecionado): ?>
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-4">
                <div class="card shadow radius-10 border-start border-success border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Livres</p>
                                <h4 class="my-1 text-success"><?= $contagem['livre'] ?></h4>
                            </div>
                            <div class="ms-auto fs-1 text-success"><i class="bx bx-check-circle"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow radius-10 border-start border-warning border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Reservados</p>
                                <h4 class="my-1 text-warning"><?= $contagem['reservado'] ?></h4>
                            </div>
                            <div class="ms-auto fs-1 text-warning"><i class="bx bx-bookmark"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow radius-10 border-start border-secondary border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Bloqueados</p>
                                <h4 class="my-1 text-secondary"><?= $contagem['bloqueado'] ?></h4>
                            </div>
                            <div class="ms-auto fs-1 text-secondary"><i class="bx bx-block"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if ($eventIdSelecionado): ?>
<!-- Card Principal -->
<div class="card shadow radius-10">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bx bx-map-pin me-2"></i>Espaços</h6>
        <div class="btn-group">
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalEspaco">
                <i class="bx bx-plus me-1"></i>Novo Espaço
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalLote">
                <i class="bx bx-layer me-1"></i>Criar em Lote
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabelaEspacos" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Status</th>
                        <th>Contrato</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Carregado via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php else: ?>
<div class="alert alert-info">
    <i class="bx bx-info-circle me-2"></i>Selecione um evento para gerenciar os espaços disponíveis.
</div>
<?php endif; ?>

<!-- Modal Novo/Editar Espaço -->
<div class="modal fade" id="modalEspaco" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEspacoTitulo">Novo Espaço</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEspaco">
                <div class="modal-body">
                    <input type="hidden" name="id" id="espaco_id">
                    <input type="hidden" name="event_id" value="<?= $eventIdSelecionado ?>">

                    <div class="mb-3">
                        <label class="form-label">Tipo <span class="text-danger">*</span></label>
                        <select class="form-select" name="tipo_item" id="tipo_item" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($tiposItem as $tipo): ?>
                            <option value="<?= $tipo ?>"><?= $tipo ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nome/Código <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nome" id="nome" placeholder="Ex: A1, B2, Stand 05" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea class="form-control" name="descricao" id="descricao" rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="status">
                            <option value="livre">Livre</option>
                            <option value="bloqueado">Bloqueado</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Criar em Lote -->
<div class="modal fade" id="modalLote" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Criar Espaços em Lote</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formLote">
                <div class="modal-body">
                    <input type="hidden" name="event_id" value="<?= $eventIdSelecionado ?>">

                    <div class="mb-3">
                        <label class="form-label">Tipos <span class="text-danger">*</span></label>
                        <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                            <?php foreach ($tiposItem as $tipo): ?>
                            <div class="form-check">
                                <input class="form-check-input tipo-lote-check" type="checkbox" name="tipos_item[]" value="<?= $tipo ?>" id="tipo_<?= preg_replace('/[^a-zA-Z0-9]/', '', $tipo) ?>">
                                <label class="form-check-label" for="tipo_<?= preg_replace('/[^a-zA-Z0-9]/', '', $tipo) ?>"><?= $tipo ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <small class="text-muted">Selecione um ou mais tipos para criar os espaços.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Prefixo</label>
                        <input type="text" class="form-control" name="prefixo" placeholder="Ex: A, B, Stand ">
                        <small class="text-muted">Opcional. Será concatenado com os números.</small>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">Início <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="inicio" value="1" min="1" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">Fim <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="fim" value="20" min="1" max="100" required>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="bx bx-info-circle me-1"></i>
                        Exemplo: Prefixo "A" + Início 1 + Fim 5 = A1, A2, A3, A4, A5
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Criar Espaços</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script src="<?php echo site_url('recursos/theme/'); ?>plugins/datatable/js/jquery.dataTables.min.js"></script>
<script src="<?php echo site_url('recursos/theme/'); ?>plugins/datatable/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    var csrfToken = '<?php echo csrf_hash(); ?>';
    var csrfName = '<?php echo csrf_token(); ?>';
    var eventId = '<?= $eventIdSelecionado ?>';

    // Filtro por evento
    $('#filtroEvento').on('change', function() {
        var evtId = $(this).val();
        if (evtId) {
            window.location.href = '<?= site_url('espacos') ?>?event_id=' + evtId;
        }
    });

    // DataTable
    <?php if ($eventIdSelecionado): ?>
    var table = $('#tabelaEspacos').DataTable({
        ajax: {
            url: '<?= site_url('espacos/recuperaEspacos') ?>?event_id=' + eventId,
            dataSrc: 'data'
        },
        columns: [
            { data: 'tipo_item' },
            { data: 'nome' },
            { data: 'descricao' },
            { data: 'status' },
            { data: 'contrato' },
            { data: 'acoes', className: 'text-center' }
        ],
        order: [[0, 'asc'], [1, 'asc']],
        language: { url: '<?= site_url('recursos/vendor/datatables/pt-BR.json') ?>' }
    });
    <?php endif; ?>

    // Salvar espaço
    $('#formEspaco').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            type: 'POST',
            url: '<?= site_url('espacos/salvar') ?>',
            data: $(this).serialize() + '&' + csrfName + '=' + csrfToken,
            dataType: 'json',
            success: function(response) {
                if (response.token) csrfToken = response.token;
                if (response.sucesso) {
                    $('#modalEspaco').modal('hide');
                    table.ajax.reload();
                    alert(response.sucesso);
                } else {
                    alert(response.erro || 'Erro ao salvar');
                }
            },
            error: function() { alert('Erro ao processar solicitação'); }
        });
    });

    // Criar em lote
    $('#formLote').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            type: 'POST',
            url: '<?= site_url('espacos/salvarLote') ?>',
            data: $(this).serialize() + '&' + csrfName + '=' + csrfToken,
            dataType: 'json',
            success: function(response) {
                if (response.token) csrfToken = response.token;
                if (response.sucesso) {
                    $('#modalLote').modal('hide');
                    table.ajax.reload();
                    alert(response.sucesso);
                    location.reload();
                } else {
                    alert(response.erro || 'Erro ao criar');
                }
            },
            error: function() { alert('Erro ao processar solicitação'); }
        });
    });

    // Editar
    $(document).on('click', '.btn-editar', function() {
        $('#espaco_id').val($(this).data('id'));
        $('#tipo_item').val($(this).data('tipo-item'));
        $('#nome').val($(this).data('nome'));
        $('#descricao').val($(this).data('descricao'));
        $('#status').val($(this).data('status'));
        $('#modalEspacoTitulo').text('Editar Espaço');
        $('#modalEspaco').modal('show');
    });

    // Limpar modal ao abrir novo
    $('#modalEspaco').on('show.bs.modal', function(e) {
        if (!$(e.relatedTarget).hasClass('btn-editar')) {
            $('#formEspaco')[0].reset();
            $('#espaco_id').val('');
            $('#modalEspacoTitulo').text('Novo Espaço');
        }
    });

    // Bloquear
    $(document).on('click', '.btn-bloquear', function() {
        if (!confirm('Bloquear este espaço?')) return;
        alterarStatus($(this).data('id'), 'bloqueado');
    });

    // Liberar
    $(document).on('click', '.btn-liberar', function() {
        if (!confirm('Liberar este espaço?')) return;
        alterarStatus($(this).data('id'), 'livre');
    });

    function alterarStatus(id, status) {
        $.ajax({
            type: 'POST',
            url: '<?= site_url('espacos/alterarStatus') ?>',
            data: { id: id, status: status, [csrfName]: csrfToken },
            dataType: 'json',
            success: function(response) {
                if (response.token) csrfToken = response.token;
                if (response.sucesso) {
                    table.ajax.reload();
                } else {
                    alert(response.erro);
                }
            }
        });
    }

    // Excluir
    $(document).on('click', '.btn-excluir', function() {
        if (!confirm('Excluir este espaço?')) return;
        
        $.ajax({
            type: 'POST',
            url: '<?= site_url('espacos/excluir') ?>',
            data: { id: $(this).data('id'), [csrfName]: csrfToken },
            dataType: 'json',
            success: function(response) {
                if (response.token) csrfToken = response.token;
                if (response.sucesso) {
                    table.ajax.reload();
                    location.reload();
                } else {
                    alert(response.erro);
                }
            }
        });
    });
});
</script>
<?php echo $this->endSection() ?>
