<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.css') ?>" />
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="row">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3"><?= $titulo ?></div>
        <div class="ms-auto">
            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovo">
                <i class="bi bi-plus-lg"></i> Novo Código
            </a>
            <a href="#" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalImportar">
                <i class="bi bi-upload"></i> Importar em Lote
            </a>
            <button type="button" class="btn btn-info" id="btnEditarMassa" disabled>
                <i class="bi bi-pencil-square"></i> Editar Selecionados (<span id="qtdSelecionados">0</span>)
            </button>
            <?php if ($total_expirados > 0): ?>
            <a href="<?= site_url('codigo-bonus/liberar-expirados') ?>" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja liberar todos os <?= $total_expirados ?> códigos expirados?\n\nOs códigos vinculados serão marcados como EXPIRADO.')">
                <i class="bi bi-arrow-clockwise"></i> Liberar Expirados (<?= $total_expirados ?>)
            </a>
            <?php endif; ?>
            <a href="<?= site_url('codigo-bonus/migrar-cinemark') ?>" class="btn btn-warning" onclick="return confirm('Migrar códigos Cinemark do evento atual para as novas tabelas?\n\nEsta ação irá:\n- Criar registros na tabela bonus\n- Criar registros na tabela codigo_bonus\n- Marcar os códigos como usados')">
                <i class="bi bi-arrow-right-circle"></i> Migrar Cinemark
            </a>
        </div>
    </div>

    <!-- Cards de resumo -->
    <div class="col-md-3 mb-3">
        <div class="card radius-10 bg-success">
            <div class="card-body text-center">
                <h4 class="mb-0 text-white"><?= $total_disponiveis ?></h4>
                <p class="mb-0 text-white">Disponíveis</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card radius-10 bg-secondary">
            <div class="card-body text-center">
                <h4 class="mb-0 text-white"><?= $total_usados ?></h4>
                <p class="mb-0 text-white">Usados</p>
            </div>
        </div>
    </div>
    <?php if ($total_expirados > 0): ?>
    <div class="col-md-3 mb-3">
        <div class="card radius-10 bg-danger">
            <div class="card-body text-center">
                <h4 class="mb-0 text-white"><?= $total_expirados ?></h4>
                <p class="mb-0 text-white">Expirados</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="col-12">
        <div class="card radius-10">
            <div class="card-body">
                <div id="response"></div>
                <div class="table-responsive">
                    <table id="tabela" class="table table-striped table-bordered align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th><input type="checkbox" id="selecionarTodos" title="Selecionar todos"></th>
                                <th>ID</th>
                                <th>Código</th>
                                <th>Status</th>
                                <th>Validade</th>
                                <th>Validade Lote</th>
                                <th>Criado em</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Novo -->
<div class="modal fade" id="modalNovo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Novo Código</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <?php echo form_open('/', ['id' => 'formNovo']) ?>
            <div class="modal-body">
                <div id="responseNovo"></div>
                <?php echo $this->include('CodigoBonus/_form'); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="btnSalvar">Salvar</button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<!-- Modal Importar -->
<div class="modal fade" id="modalImportar" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Importar Códigos em Lote</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <?php echo form_open('/', ['id' => 'formImportar']) ?>
            <div class="modal-body">
                <div id="responseImportar"></div>
                <div class="mb-3">
                    <label class="form-label">Códigos (um por linha)</label>
                    <textarea name="codigos" class="form-control" rows="10" placeholder="ABC123&#10;DEF456&#10;GHI789"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Validade</label>
                        <input type="date" name="validade" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Validade do Lote</label>
                        <input type="date" name="validade_lote" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success" id="btnImportar">Importar</button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<!-- Modal Edição em Massa -->
<div class="modal fade" id="modalEditarMassa" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Editar em Massa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <?php echo form_open('/', ['id' => 'formEditarMassa']) ?>
            <div class="modal-body">
                <div id="responseEditarMassa"></div>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> <strong><span id="qtdEditando">0</span> código(s)</strong> selecionado(s) para edição.
                    <br><small>Deixe em branco os campos que não deseja alterar.</small>
                </div>
                <input type="hidden" name="ids" id="idsSelecionados" value="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nova Validade</label>
                        <input type="date" name="validade" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nova Validade do Lote</label>
                        <input type="date" name="validade_lote" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-info" id="btnAtualizarMassa">Atualizar Selecionados</button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script src="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.js') ?>"></script>

<script>
$(document).ready(function() {
    var idsSelecionados = [];

    // DataTable
    var table = $('#tabela').DataTable({
        "ajax": {
            "url": "<?php echo site_url('codigo-bonus/recupera'); ?>",
            "type": "GET"
        },
        "columns": [
            {
                "data": "id",
                "render": function(data) {
                    return '<input type="checkbox" class="checkbox-codigo" value="' + data + '">';
                },
                "orderable": false
            },
            {"data": "id"},
            {"data": "codigo"},
            {"data": "status"},
            {"data": "validade"},
            {"data": "validade_lote"},
            {"data": "created_at"},
            {"data": "acoes"}
        ],
        "order": [[1, "desc"]],
        "language": {
            "url": "<?php echo site_url('recursos/vendor/datatable/pt-BR.json') ?>"
        },
        "responsive": true
    });

    // Selecionar todos
    $('#selecionarTodos').on('change', function() {
        var checked = $(this).is(':checked');
        $('.checkbox-codigo').prop('checked', checked);
        atualizarSelecionados();
    });

    // Ao clicar em um checkbox individual
    $(document).on('change', '.checkbox-codigo', function() {
        atualizarSelecionados();
    });

    // Atualizar contagem e habilitar/desabilitar botão
    function atualizarSelecionados() {
        idsSelecionados = [];
        $('.checkbox-codigo:checked').each(function() {
            idsSelecionados.push($(this).val());
        });
        $('#qtdSelecionados').text(idsSelecionados.length);
        $('#btnEditarMassa').prop('disabled', idsSelecionados.length === 0);
    }

    // Abrir modal de edição em massa
    $('#btnEditarMassa').on('click', function() {
        if (idsSelecionados.length === 0) return;
        $('#qtdEditando').text(idsSelecionados.length);
        $('#idsSelecionados').val(idsSelecionados.join(','));
        $('#formEditarMassa')[0].reset();
        $('#idsSelecionados').val(idsSelecionados.join(','));
        $('#responseEditarMassa').html('');
        $('#modalEditarMassa').modal('show');
    });

    // Form Edição em Massa
    $("#formEditarMassa").on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: '<?php echo site_url('codigo-bonus/atualizar-massa'); ?>',
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {
                $("#responseEditarMassa").html('');
                $("#btnAtualizarMassa").text('Aguarde...').attr('disabled', true);
            },
            success: function(response) {
                $("#btnAtualizarMassa").text('Atualizar Selecionados').removeAttr('disabled');
                if (response.sucesso) {
                    $("#responseEditarMassa").html('<div class="alert alert-success">' + response.sucesso + '</div>');
                    table.ajax.reload();
                    setTimeout(function() {
                        $('#modalEditarMassa').modal('hide');
                        // Limpar seleção
                        $('.checkbox-codigo').prop('checked', false);
                        $('#selecionarTodos').prop('checked', false);
                        atualizarSelecionados();
                    }, 1500);
                }
                if (response.erro) {
                    $("#responseEditarMassa").html('<div class="alert alert-danger">' + response.erro + '</div>');
                }
            }
        });
    });

    // Form Novo
    $("#formNovo").on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: '<?php echo site_url('codigo-bonus/cadastrar'); ?>',
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {
                $("#responseNovo").html('');
                $("#btnSalvar").val('Aguarde...').attr('disabled', true);
            },
            success: function(response) {
                $("#btnSalvar").val('Salvar').removeAttr('disabled');
                if (response.sucesso) {
                    $("#responseNovo").html('<div class="alert alert-success">' + response.sucesso + '</div>');
                    table.ajax.reload();
                    setTimeout(function() {
                        $('#modalNovo').modal('hide');
                        $('#formNovo')[0].reset();
                        $("#responseNovo").html('');
                    }, 1000);
                }
                if (response.erro) {
                    $("#responseNovo").html('<div class="alert alert-danger">' + response.erro + '</div>');
                }
            }
        });
    });

    // Form Importar
    $("#formImportar").on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: '<?php echo site_url('codigo-bonus/importar'); ?>',
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {
                $("#responseImportar").html('');
                $("#btnImportar").val('Aguarde...').attr('disabled', true);
            },
            success: function(response) {
                $("#btnImportar").val('Importar').removeAttr('disabled');
                if (response.sucesso) {
                    $("#responseImportar").html('<div class="alert alert-success">' + response.sucesso + '</div>');
                    table.ajax.reload();
                    // Recarrega a página para atualizar os cards
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                }
                if (response.erro) {
                    $("#responseImportar").html('<div class="alert alert-danger">' + response.erro + '</div>');
                }
            }
        });
    });
});
</script>

<?php echo $this->endSection() ?>
