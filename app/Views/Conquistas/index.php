<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<!-- DataTables -->
<link href="<?php echo site_url('recursos/theme/'); ?>plugins/datatable/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<!-- Evento do contexto -->
<input type="hidden" id="eventoContexto" value="<?php echo esc($evento_id ?? ''); ?>">
<!-- CSRF Token -->
<input type="hidden" id="csrfToken" name="<?php echo csrf_token(); ?>" value="<?php echo csrf_hash(); ?>">

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Conquistas</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Conquistas</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto d-flex gap-2">
        <button type="button" class="btn btn-outline-success" onclick="abrirModalDuplicarMassa()" id="btnDuplicarMassa" disabled>
            <i class="bx bx-copy-alt me-2"></i>Duplicar Selecionados
        </button>
        <a href="<?php echo site_url('conquistas-admin/extrato'); ?>" class="btn btn-outline-info">
            <i class="bx bx-list-ul me-2"></i>Extrato de Pontos
        </a>
        <a href="<?php echo site_url('conquistas-admin/ranking'); ?>" class="btn btn-outline-success">
            <i class="bx bx-bar-chart-alt-2 me-2"></i>Ranking
        </a>
        <a href="<?php echo site_url('conquistas-admin/criar'); ?>" class="btn btn-primary">
            <i class="bx bx-plus me-2"></i>Nova Conquista
        </a>
    </div>
</div>
<!--end breadcrumb-->

<!-- Filtro por Evento -->
<div class="card mb-3">
    <div class="card-body py-2">
        <div class="row align-items-center">
            <div class="col-md-4">
                <label class="form-label mb-1">Filtrar por Evento</label>
                <select class="form-select form-select-sm" id="filtroEvento">
                    <option value="todos">🌐 Todos os Eventos + Globais</option>
                    <option value="">🌐 Apenas Conquistas Globais</option>
                    <?php foreach ($eventos as $evento): ?>
                        <option value="<?php echo $evento->id; ?>" <?php echo $evento_id == $evento->id ? 'selected' : ''; ?>>
                            <?php echo esc($evento->nome); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-sm btn-primary mt-3" onclick="aplicarFiltro()">
                    <i class="bx bx-filter-alt"></i> Filtrar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabelaConquistas" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 30px;"><input type="checkbox" id="checkTodos" title="Selecionar Todos"></th>
                        <th>Código</th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Pontos</th>
                        <th>Nível</th>
                        <th>Evento</th>
                        <th>Usuários</th>
                        <th>Status</th>
                        <th class="text-center" style="width: 150px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Duplicar Individual -->
<div class="modal fade" id="modalDuplicar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bx bx-copy me-2"></i>Duplicar Conquista</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="duplicarConquistaId">
                <div class="mb-3">
                    <label class="form-label">Duplicar para qual evento?</label>
                    <select class="form-select" id="duplicarEventoId">
                        <option value="">Mesmo evento (criar cópia)</option>
                        <?php foreach ($eventos as $evento): ?>
                            <option value="<?php echo $evento->id; ?>"><?php echo esc($evento->nome); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="confirmarDuplicar()">
                    <i class="bx bx-copy me-2"></i>Duplicar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Duplicar em Massa -->
<div class="modal fade" id="modalDuplicarMassa" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bx bx-copy-alt me-2"></i>Duplicar em Massa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong id="qtdSelecionados">0</strong> conquista(s) selecionada(s)</p>
                <div class="mb-3">
                    <label class="form-label">Duplicar para qual evento?</label>
                    <select class="form-select" id="duplicarMassaEventoId" required>
                        <option value="">Selecione o evento de destino</option>
                        <?php foreach ($eventos as $evento): ?>
                            <option value="<?php echo $evento->id; ?>"><?php echo esc($evento->nome); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="confirmarDuplicarMassa()">
                    <i class="bx bx-copy-alt me-2"></i>Duplicar Todos
                </button>
            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>

<!-- DataTables -->
<script src="<?php echo site_url('recursos/theme/'); ?>plugins/datatable/js/jquery.dataTables.min.js"></script>
<script src="<?php echo site_url('recursos/theme/'); ?>plugins/datatable/js/dataTables.bootstrap5.min.js"></script>

<script>
var csrfToken = $('#csrfToken').val();
var tabela;
var conquistasSelecionadas = [];

$(document).ready(function() {
    
    tabela = $('#tabelaConquistas').DataTable({
        ajax: {
            url: '<?php echo site_url("conquistas-admin/recupera"); ?>',
            type: 'GET',
            data: function(d) {
                // Usa o filtro selecionado (todos, vazio para global, ou id do evento)
                d.event_id = $('#filtroEvento').val();
            }
        },
        columns: [
            { 
                data: 'id',
                render: function(data) {
                    return '<input type="checkbox" class="checkConquista" value="' + data + '">';
                },
                orderable: false,
                className: 'text-center'
            },
            { data: 'codigo' },
            { data: 'nome' },
            { data: 'descricao' },
            { data: 'pontos', className: 'text-center' },
            { data: 'nivel', className: 'text-center' },
            { data: 'evento', className: 'text-center' },
            { data: 'usuarios', className: 'text-center' },
            { data: 'status', className: 'text-center' },
            { data: 'acoes', className: 'text-center', orderable: false }
        ],
        order: [[1, 'asc']],
        language: {
            url: '<?php echo site_url("recursos/theme/plugins/datatable/js/pt-BR.json"); ?>'
        }
    });
    
    // Selecionar todos
    $('#checkTodos').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('.checkConquista').prop('checked', isChecked);
        atualizarSelecionados();
    });
    
    // Atualizar selecionados ao clicar em checkbox individual
    $(document).on('change', '.checkConquista', function() {
        atualizarSelecionados();
    });
});

function aplicarFiltro() {
    tabela.ajax.reload();
}

function atualizarSelecionados() {
    conquistasSelecionadas = [];
    $('.checkConquista:checked').each(function() {
        conquistasSelecionadas.push($(this).val());
    });
    
    if (conquistasSelecionadas.length > 0) {
        $('#btnDuplicarMassa').prop('disabled', false).text('Duplicar ' + conquistasSelecionadas.length + ' Selecionados');
    } else {
        $('#btnDuplicarMassa').prop('disabled', true).html('<i class="bx bx-copy-alt me-2"></i>Duplicar Selecionados');
    }
}

function duplicarConquista(id) {
    $('#duplicarConquistaId').val(id);
    $('#duplicarEventoId').val('');
    new bootstrap.Modal($('#modalDuplicar')).show();
}

function confirmarDuplicar() {
    var id = $('#duplicarConquistaId').val();
    var eventoId = $('#duplicarEventoId').val();
    
    $.ajax({
        url: '<?php echo site_url("conquistas-admin/duplicar"); ?>',
        type: 'POST',
        data: {
            id: id,
            event_id: eventoId,
            '<?php echo csrf_token(); ?>': csrfToken
        },
        dataType: 'json',
        success: function(response) {
            if (response.token) csrfToken = response.token;
            
            if (response.sucesso) {
                alert(response.sucesso);
                bootstrap.Modal.getInstance($('#modalDuplicar')).hide();
                tabela.ajax.reload();
            } else if (response.erro) {
                alert('Erro: ' + response.erro);
            }
        }
    });
}

function abrirModalDuplicarMassa() {
    $('#qtdSelecionados').text(conquistasSelecionadas.length);
    $('#duplicarMassaEventoId').val('');
    new bootstrap.Modal($('#modalDuplicarMassa')).show();
}

function confirmarDuplicarMassa() {
    var eventoId = $('#duplicarMassaEventoId').val();
    
    if (!eventoId) {
        alert('Selecione o evento de destino.');
        return;
    }
    
    $.ajax({
        url: '<?php echo site_url("conquistas-admin/duplicar-massa"); ?>',
        type: 'POST',
        data: {
            ids: conquistasSelecionadas,
            event_id: eventoId,
            '<?php echo csrf_token(); ?>': csrfToken
        },
        dataType: 'json',
        success: function(response) {
            if (response.token) csrfToken = response.token;
            
            if (response.sucesso) {
                alert(response.sucesso);
                bootstrap.Modal.getInstance($('#modalDuplicarMassa')).hide();
                tabela.ajax.reload();
                // Limpar seleção
                $('#checkTodos').prop('checked', false);
                conquistasSelecionadas = [];
                atualizarSelecionados();
            } else if (response.erro) {
                alert('Erro: ' + response.erro);
            }
        }
    });
}
</script>

<?php echo $this->endSection() ?>
