<?php echo $this->extend('Layout/principal') ?>

<?php echo $this->section('estilos') ?>
<link href="<?php echo site_url('recursos/theme/'); ?>plugins/datatable/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
<?php echo $this->endSection() ?>

<?php echo $this->section('titulo') ?>
<?php echo $titulo; ?>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Ingressos</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('/eventos'); ?>">Eventos</a></li>
                <li class="breadcrumb-item active"><?= esc($evento->nome) ?></li>
            </ol>
        </nav>
    </div>
</div>

<!-- Evento selecionado via URL -->
<input type="hidden" id="filtroEvento" value="<?= esc($eventIdSelecionado) ?>">

<?php if ($eventIdSelecionado): ?>
<!-- Cards de Resumo -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card shadow radius-10 border-start border-primary border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Total</p>
                        <h4 class="my-1 text-primary"><?= $contagem['total'] ?></h4>
                    </div>
                    <div class="ms-auto fs-1 text-primary"><i class="bx bx-ticket"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow radius-10 border-start border-success border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Ativos</p>
                        <h4 class="my-1 text-success"><?= $contagem['ativos'] ?></h4>
                    </div>
                    <div class="ms-auto fs-1 text-success"><i class="bx bx-check-circle"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow radius-10 border-start border-secondary border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Inativos</p>
                        <h4 class="my-1 text-secondary"><?= $contagem['inativos'] ?></h4>
                    </div>
                    <div class="ms-auto fs-1 text-secondary"><i class="bx bx-pause-circle"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow radius-10 border-start border-info border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Evento</p>
                        <h6 class="my-1 text-info text-truncate" style="max-width: 150px;"><?= esc($evento->nome) ?></h6>
                    </div>
                    <div class="ms-auto fs-1 text-info"><i class="bx bx-calendar-event"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Card Principal -->
<div class="card shadow radius-10">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bx bx-ticket me-2"></i>Ingressos do Evento</h6>
        <div>
            <button type="button" class="btn btn-warning btn-sm me-2" id="btnEditarLote" style="display: none;">
                <i class="bx bx-edit-alt me-1"></i>Editar Lote
            </button>
            <button type="button" class="btn btn-info btn-sm me-2" id="btnDuplicarLote" style="display: none;">
                <i class="bx bx-copy-alt me-1"></i>Duplicar Lote
            </button>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTicket">
                <i class="bx bx-plus me-1"></i>Novo Ingresso
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Abas de Lotes -->
        <ul class="nav nav-pills mb-3" id="tabLotes" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" type="button" data-lote="">
                    <i class="bx bx-list-ul me-1"></i>Todos
                </button>
            </li>
            <?php foreach ($lotes as $lote): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link" type="button" data-lote="<?= esc($lote) ?>">
                    <?= esc($lote) ?>
                </button>
            </li>
            <?php endforeach; ?>
        </ul>
        
        <!-- Conteúdo das abas (tabela única filtrada) -->
        <div class="tab-content pt-3" id="tabLotesContent">
            <div class="table-responsive">
                <table id="tabelaTickets" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Código</th>
                            <th>Tipo</th>
                            <th>Categoria</th>
                            <th>Preço</th>
                            <th class="text-center">Qtd</th>
                            <th class="text-center">Estoque</th>
                            <th class="text-center">Vendidos</th>
                            <th>Lote</th>
                            <th>Validade</th>
                            <th>Status</th>
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
</div>
<?php else: ?>
<div class="alert alert-info">
    <i class="bx bx-info-circle me-2"></i>Selecione um evento para gerenciar os ingressos.
</div>
<?php endif; ?>

<!-- Modal Novo/Editar Ticket -->
<div class="modal fade" id="modalTicket" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTicketTitulo">Novo Ingresso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTicket">
                <div class="modal-body">
                    <input type="hidden" name="id" id="ticket_id">
                    <input type="hidden" name="event_id" id="event_id_hidden" value="<?= $eventIdSelecionado ?>">
                    
                    <!-- Seletor de evento (visível apenas na duplicação) -->
                    <div class="row mb-3" id="seletorEvento" style="display: none;">
                        <div class="col-md-12">
                            <label class="form-label"><strong>Evento de Destino</strong></label>
                            <select class="form-select" id="event_id_select">
                                <?php foreach ($eventosAtivos as $ev): ?>
                                <option value="<?= $ev->id ?>" <?= $ev->id == $eventIdSelecionado ? 'selected' : '' ?>>
                                    <?= esc($ev->nome) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Selecione o evento onde o ingresso será criado</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Nome <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nome" id="nome" placeholder="Ex: Ingresso VIP Dia 1" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Código</label>
                            <input type="text" class="form-control" name="codigo" id="codigo" placeholder="Auto-gerado">
                            <small class="text-muted">Deixe vazio para gerar automaticamente</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tipo <span class="text-danger">*</span></label>
                            <select class="form-select" name="tipo" id="tipo" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($tipos as $tipo): ?>
                                <option value="<?= $tipo ?>"><?= $tipo ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Categoria</label>
                            <select class="form-select" name="categoria" id="categoria">
                                <option value="">Selecione...</option>
                                <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat ?>"><?= $cat ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Dia</label>
                            <select class="form-select" name="dia" id="dia">
                                <option value="">Selecione...</option>
                                <?php foreach ($dias as $d): ?>
                                <option value="<?= $d ?>"><?= $d ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Preço (R$) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control money" name="preco" id="preco" placeholder="0,00" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Valor Unitário (R$)</label>
                            <input type="text" class="form-control money" name="valor_unitario" id="valor_unitario" placeholder="0,00">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Lote</label>
                            <input type="text" class="form-control" name="lote" id="lote" placeholder="Ex: 1º Lote">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Quantidade <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="quantidade" id="quantidade" min="0" value="0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Estoque <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="estoque" id="estoque" min="0" value="0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Promo</label>
                            <input type="text" class="form-control" name="promo" id="promo" placeholder="Ex: girafinhas">
                            <small class="text-muted">Identificador promocional</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Data Início</label>
                            <input type="date" class="form-control" name="data_inicio" id="data_inicio">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Data Fim</label>
                            <input type="date" class="form-control" name="data_fim" id="data_fim">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Validade do Lote <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="data_lote" id="data_lote" required>
                            <small class="text-muted">Data limite de venda</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea class="form-control" name="descricao" id="descricao" rows="2" placeholder="Descrição do ingresso..."></textarea>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="ativo" id="ativo" value="1" checked>
                        <label class="form-check-label" for="ativo">Ingresso Ativo</label>
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

<!-- Modal Duplicar Lote em Massa -->
<div class="modal fade" id="modalDuplicarLote" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bx bx-copy-alt me-2"></i>Duplicar Lote - <span id="loteOrigem"></span> → <span id="loteDestino"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formDuplicarLote">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label"><strong>Evento de Destino</strong></label>
                            <select class="form-select" name="event_id" id="eventoDuplicarLote" required>
                                <?php foreach ($eventosAtivos as $ev): ?>
                                <option value="<?= $ev->id ?>" <?= $ev->id == $eventIdSelecionado ? 'selected' : '' ?>>
                                    <?= esc($ev->nome) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Selecione o evento onde os ingressos serão criados</small>
                        </div>
                    </div>
                    <div class="alert alert-info mb-3">
                        <i class="bx bx-info-circle me-2"></i>
                        Edite os campos abaixo. Tipo, Categoria, Dia, Datas e Descrição serão mantidos do original.
                        O código será gerado automaticamente.
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="tabelaDuplicar">
                            <thead class="table-light">
                                <tr>
                                    <th>Nome</th>
                                    <th style="width: 100px;">Preço</th>
                                    <th style="width: 80px;">Qtd</th>
                                    <th style="width: 80px;">Estoque</th>
                                    <th style="width: 80px;">Lote</th>
                                    <th style="width: 130px;">Val. Lote</th>
                                    <th style="width: 100px;">Promo</th>
                                    <th style="width: 60px;">Ativo</th>
                                </tr>
                            </thead>
                            <tbody id="linhasDuplicar">
                                <!-- Linhas geradas via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info">
                        <i class="bx bx-save me-1"></i>Criar Ingressos
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Lote em Massa -->
<div class="modal fade" id="modalEditarLote" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="bx bx-edit-alt me-2"></i>Editar Lote <span id="loteEditando"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditarLote">
                <div class="modal-body">
                    <div class="alert alert-warning mb-3">
                        <i class="bx bx-info-circle me-2"></i>
                        Edite os campos abaixo. As alterações serão aplicadas aos ingressos existentes.
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="tabelaEditar">
                            <thead class="table-light">
                                <tr>
                                    <th>Nome</th>
                                    <th style="width: 100px;">Preço</th>
                                    <th style="width: 80px;">Qtd</th>
                                    <th style="width: 80px;">Estoque</th>
                                    <th style="width: 80px;">Lote</th>
                                    <th style="width: 130px;">Val. Lote</th>
                                    <th style="width: 100px;">Promo</th>
                                    <th style="width: 60px;">Ativo</th>
                                </tr>
                            </thead>
                            <tbody id="linhasEditar">
                                <!-- Linhas geradas via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bx bx-save me-1"></i>Salvar Alterações
                    </button>
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
    
    // Função para decodificar base64 preservando UTF-8
    function decodeBase64(str) {
        if (!str) return '';
        try {
            return decodeURIComponent(atob(str).split('').map(function(c) {
                return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
            }).join(''));
        } catch(e) {
            return atob(str);
        }
    }

    // DataTable
    <?php if ($eventIdSelecionado): ?>
    var table = $('#tabelaTickets').DataTable({
        ajax: {
            url: '<?= site_url('tickets/recuperaTickets') ?>?event_id=' + eventId,
            dataSrc: 'data'
        },
        columns: [
            { data: 'nome' },
            { data: 'codigo' },
            { data: 'tipo' },
            { data: 'categoria' },
            { data: 'preco' },
            { data: 'quantidade', className: 'text-center' },
            { data: 'estoque', className: 'text-center' },
            { data: 'vendidos', className: 'text-center' },
            { data: 'lote' },
            { data: 'data_lote' },
            { data: 'status' },
            { data: 'acoes', className: 'text-center' }
        ],
        order: [], // Manter ordem do servidor (mais novos primeiro)
        language: { url: '<?= site_url('recursos/vendor/datatables/pt-BR.json') ?>' },
        pageLength: 25
    });

    // Filtro por aba de lote
    var loteAtual = '';
    
    // Quando clicar em uma aba de lote
    $('#tabLotes button').on('click', function() {
        // Remove active de todas as abas e adiciona na clicada
        $('#tabLotes button').removeClass('active');
        $(this).addClass('active');
        
        loteAtual = String($(this).data('lote') ?? '');
        table.draw();
        
        // Mostrar/ocultar botões de Lote
        if (loteAtual !== '') {
            $('#btnDuplicarLote, #btnEditarLote').show();
        } else {
            $('#btnDuplicarLote, #btnEditarLote').hide();
        }
    });

    // Filtro customizado do DataTable
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (loteAtual === '') {
            return true; // Mostra todos
        }
        var loteNaLinha = String(data[8] || '').trim(); // Coluna do lote (índice 8)
        return loteNaLinha === loteAtual;
    });
    <?php endif; ?>

    // Máscara para valores monetários
    $('.money').on('keyup', function() {
        var val = $(this).val().replace(/\D/g, '');
        val = (parseInt(val) / 100).toFixed(2).replace('.', ',');
        $(this).val(val == 'NaN' ? '0,00' : val);
    });

    // Salvar ticket
    $('#formTicket').on('submit', function(e) {
        e.preventDefault();
        
        // Habilitar campos disabled antes de serializar (para enviar os valores)
        var disabledFields = $('#tipo, #categoria, #dia, #data_inicio, #data_fim, #descricao');
        disabledFields.prop('disabled', false);
        
        var isDuplicating = $('#modalTicket').data('duplicating');
        
        // Se estiver duplicando, usar o evento selecionado no combo
        if (isDuplicating) {
            $('#event_id_hidden').val($('#event_id_select').val());
        }
        
        var formData = $(this).serialize();
        formData += '&' + csrfName + '=' + csrfToken;
        
        $.ajax({
            type: 'POST',
            url: '<?= site_url('tickets/salvar') ?>',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.token) csrfToken = response.token;
                if (response.sucesso) {
                    $('#modalTicket').modal('hide');
                    table.ajax.reload();
                    alert(response.sucesso);
                    if (isDuplicating) {
                        location.reload(); // Atualizar abas de lotes
                    }
                } else {
                    alert(response.erro || 'Erro ao salvar');
                }
            },
            error: function() { alert('Erro ao processar solicitação'); }
        });
    });

    // Editar
    $(document).on('click', '.btn-editar', function(e) {
        e.preventDefault();
        $('#ticket_id').val($(this).data('id'));
        $('#nome').val($(this).data('nome'));
        $('#codigo').val($(this).data('codigo'));
        $('#tipo').val($(this).data('tipo'));
        $('#categoria').val($(this).data('categoria'));
        $('#dia').val(($(this).data('dia') || '').toString().toLowerCase());
        $('#preco').val($(this).data('preco'));
        $('#valor_unitario').val($(this).data('valor-unitario'));
        $('#quantidade').val($(this).data('quantidade'));
        $('#estoque').val($(this).data('estoque'));
        $('#lote').val($(this).data('lote'));
        $('#data_inicio').val($(this).data('data-inicio'));
        $('#data_fim').val($(this).data('data-fim'));
        $('#data_lote').val($(this).data('data-lote'));
        $('#descricao').val(decodeBase64($(this).data('descricao')));
        $('#promo').val($(this).data('promo'));
        $('#ativo').prop('checked', $(this).data('ativo') == 1);
        $('#modalTicketTitulo').text('Editar Ingresso');
        $('#modalTicket').data('editing', true);
        $('#modalTicket').data('duplicating', false);
        // Desbloquear campos
        $('#tipo, #categoria, #dia, #data_inicio, #data_fim, #descricao').prop('disabled', false);
        // Ocultar seletor de evento
        $('#seletorEvento').hide();
        $('#modalTicket').modal('show');
    });

    // Duplicar
    $(document).on('click', '.btn-duplicar', function(e) {
        e.preventDefault();
        $('#ticket_id').val(''); // Novo ticket
        $('#nome').val($(this).data('nome'));
        $('#codigo').val(''); // Gerar automaticamente
        $('#tipo').val($(this).data('tipo'));
        $('#categoria').val($(this).data('categoria'));
        $('#dia').val($(this).data('dia'));
        
        // Calcular preço com 15% a mais
        var precoOriginal = $(this).data('preco').toString().replace('.', '').replace(',', '.');
        var precoNovo = (parseFloat(precoOriginal) * 1.15).toFixed(2).replace('.', ',');
        $('#preco').val(precoNovo);
        
        $('#valor_unitario').val($(this).data('valor-unitario'));
        $('#quantidade').val($(this).data('quantidade'));
        $('#estoque').val(''); // Em branco
        $('#lote').val($(this).data('lote')); // +1 já calculado no PHP
        $('#data_inicio').val($(this).data('data-inicio'));
        $('#data_fim').val($(this).data('data-fim'));
        $('#data_lote').val(''); // Limpar validade do lote
        $('#descricao').val(decodeBase64($(this).data('descricao')));
        $('#promo').val($(this).data('promo'));
        $('#ativo').prop('checked', true);
        
        // Bloquear campos que não podem ser alterados
        $('#tipo, #categoria, #dia, #data_inicio, #data_fim, #descricao').prop('disabled', true);
        
        // Mostrar seletor de evento
        $('#seletorEvento').show();
        $('#event_id_select').val(eventId); // Default: evento atual
        
        $('#modalTicketTitulo').text('Duplicar Ingresso');
        $('#modalTicket').data('editing', true);
        $('#modalTicket').data('duplicating', true);
        $('#modalTicket').modal('show');
    });

    // Limpar modal ao abrir novo
    $('#modalTicket').on('show.bs.modal', function(e) {
        if (!$(this).data('editing')) {
            $('#formTicket')[0].reset();
            $('#ticket_id').val('');
            $('#ativo').prop('checked', true);
            $('#modalTicketTitulo').text('Novo Ingresso');
            // Desbloquear campos
            $('#tipo, #categoria, #dia, #data_inicio, #data_fim, #descricao').prop('disabled', false);
            // Ocultar seletor de evento
            $('#seletorEvento').hide();
            // Resetar evento para o atual
            $('#event_id_hidden').val(eventId);
        }
    });
    
    $('#modalTicket').on('hidden.bs.modal', function() {
        $(this).data('editing', false);
        $(this).data('duplicating', false);
        // Desbloquear campos ao fechar
        $('#tipo, #categoria, #dia, #data_inicio, #data_fim, #descricao').prop('disabled', false);
        // Ocultar seletor de evento e resetar
        $('#seletorEvento').hide();
        $('#event_id_hidden').val(eventId);
    });

    // Ativar
    $(document).on('click', '.btn-ativar', function() {
        if (!confirm('Ativar este ingresso?')) return;
        alterarStatus($(this).data('id'), 1);
    });

    // Desativar
    $(document).on('click', '.btn-desativar', function() {
        if (!confirm('Desativar este ingresso?')) return;
        alterarStatus($(this).data('id'), 0);
    });

    function alterarStatus(id, ativo) {
        $.ajax({
            type: 'POST',
            url: '<?= site_url('tickets/alterarStatus') ?>',
            data: { id: id, ativo: ativo, [csrfName]: csrfToken },
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
        if (!confirm('Excluir este ingresso?')) return;
        
        $.ajax({
            type: 'POST',
            url: '<?= site_url('tickets/excluir') ?>',
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

    // =========================================
    // DUPLICAR LOTE EM MASSA
    // =========================================
    
    // Abrir modal de duplicação em massa
    $('#btnDuplicarLote').on('click', function() {
        var novoLote = (parseInt(loteAtual) + 1);
        $('#loteOrigem').text(loteAtual);
        $('#loteDestino').text(novoLote);
        
        // Coletar dados dos tickets visíveis (filtrados)
        var linhas = '';
        var idx = 0;
        
        // Pegar dados dos botões de duplicar visíveis
        $('#tabelaTickets tbody tr:visible .btn-duplicar').each(function() {
            var btn = $(this);
            var precoOriginal = btn.data('preco').toString().replace('.', '').replace(',', '.');
            var precoNovo = (parseFloat(precoOriginal) * 1.15).toFixed(2).replace('.', ',');
            
            linhas += '<tr>';
            linhas += '<td><input type="text" class="form-control form-control-sm" name="tickets[' + idx + '][nome]" value="' + btn.data('nome') + '" required></td>';
            linhas += '<td><input type="text" class="form-control form-control-sm money-massa" name="tickets[' + idx + '][preco]" value="' + precoNovo + '" required></td>';
            linhas += '<td><input type="number" class="form-control form-control-sm" name="tickets[' + idx + '][quantidade]" value="' + btn.data('quantidade') + '" min="0" required></td>';
            linhas += '<td><input type="number" class="form-control form-control-sm" name="tickets[' + idx + '][estoque]" value="" min="0" placeholder="0" required></td>';
            linhas += '<td><input type="text" class="form-control form-control-sm" name="tickets[' + idx + '][lote]" value="' + novoLote + '" required></td>';
            linhas += '<td><input type="date" class="form-control form-control-sm" name="tickets[' + idx + '][data_lote]" value="" required></td>';
            linhas += '<td><input type="text" class="form-control form-control-sm" name="tickets[' + idx + '][promo]" value="' + (btn.data('promo') || '') + '"></td>';
            linhas += '<td class="text-center"><input type="checkbox" class="form-check-input" name="tickets[' + idx + '][ativo]" value="1" checked></td>';
            
            // Campos ocultos (bloqueados)
            linhas += '<input type="hidden" name="tickets[' + idx + '][tipo]" value="' + btn.data('tipo') + '">';
            linhas += '<input type="hidden" name="tickets[' + idx + '][categoria]" value="' + btn.data('categoria') + '">';
            linhas += '<input type="hidden" name="tickets[' + idx + '][dia]" value="' + btn.data('dia') + '">';
            linhas += '<input type="hidden" name="tickets[' + idx + '][data_inicio]" value="' + (btn.data('data-inicio') || '') + '">';
            linhas += '<input type="hidden" name="tickets[' + idx + '][data_fim]" value="' + (btn.data('data-fim') || '') + '">';
            linhas += '<input type="hidden" name="tickets[' + idx + '][descricao_base64]" value="' + (btn.data('descricao') || '') + '">';
            linhas += '<input type="hidden" name="tickets[' + idx + '][valor_unitario]" value="' + btn.data('valor-unitario') + '">';
            linhas += '</tr>';
            idx++;
        });
        
        if (idx === 0) {
            alert('Nenhum ingresso encontrado no lote selecionado.');
            return;
        }
        
        $('#linhasDuplicar').html(linhas);
        $('#modalDuplicarLote').modal('show');
        
        // Aplicar máscara de dinheiro
        $('.money-massa').on('keyup', function() {
            var val = $(this).val().replace(/\D/g, '');
            val = (parseInt(val) / 100).toFixed(2).replace('.', ',');
            $(this).val(val == 'NaN' ? '0,00' : val);
        });
    });
    
    // Submit duplicação em massa
    $('#formDuplicarLote').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        formData += '&' + csrfName + '=' + csrfToken;
        
        $.ajax({
            type: 'POST',
            url: '<?= site_url('tickets/salvarLote') ?>',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.token) csrfToken = response.token;
                if (response.sucesso) {
                    $('#modalDuplicarLote').modal('hide');
                    alert(response.sucesso);
                    location.reload();
                } else {
                    alert(response.erro || 'Erro ao salvar');
                }
            },
            error: function() { alert('Erro ao processar solicitação'); }
        });
    });
    
    // =========================================
    // EDITAR LOTE EM MASSA
    // =========================================
    
    // Abrir modal de edição em massa
    $('#btnEditarLote').on('click', function() {
        $('#loteEditando').text(loteAtual);
        
        // Coletar dados dos tickets visíveis (filtrados)
        var linhas = '';
        var idx = 0;
        
        // Pegar dados dos botões de editar visíveis
        $('#tabelaTickets tbody tr:visible .btn-editar').each(function() {
            var btn = $(this);
            var dataLote = btn.data('data-lote') || '';
            
            linhas += '<tr>';
            linhas += '<input type="hidden" name="tickets[' + idx + '][id]" value="' + btn.data('id') + '">';
            linhas += '<td><input type="text" class="form-control form-control-sm" name="tickets[' + idx + '][nome]" value="' + btn.data('nome') + '" required></td>';
            linhas += '<td><input type="text" class="form-control form-control-sm money-editar" name="tickets[' + idx + '][preco]" value="' + btn.data('preco') + '" required></td>';
            linhas += '<td><input type="number" class="form-control form-control-sm" name="tickets[' + idx + '][quantidade]" value="' + btn.data('quantidade') + '" min="0" required></td>';
            linhas += '<td><input type="number" class="form-control form-control-sm" name="tickets[' + idx + '][estoque]" value="' + btn.data('estoque') + '" min="0" required></td>';
            linhas += '<td><input type="text" class="form-control form-control-sm" name="tickets[' + idx + '][lote]" value="' + (btn.data('lote') || '') + '" required></td>';
            linhas += '<td><input type="date" class="form-control form-control-sm" name="tickets[' + idx + '][data_lote]" value="' + dataLote + '" required></td>';
            linhas += '<td><input type="text" class="form-control form-control-sm" name="tickets[' + idx + '][promo]" value="' + (btn.data('promo') || '') + '"></td>';
            linhas += '<td class="text-center"><input type="checkbox" class="form-check-input" name="tickets[' + idx + '][ativo]" value="1"' + (btn.data('ativo') == 1 ? ' checked' : '') + '></td>';
            linhas += '</tr>';
            idx++;
        });
        
        if (idx === 0) {
            alert('Nenhum ingresso encontrado no lote selecionado.');
            return;
        }
        
        $('#linhasEditar').html(linhas);
        $('#modalEditarLote').modal('show');
        
        // Aplicar máscara de dinheiro
        $('.money-editar').on('keyup', function() {
            var val = $(this).val().replace(/\D/g, '');
            val = (parseInt(val) / 100).toFixed(2).replace('.', ',');
            $(this).val(val == 'NaN' ? '0,00' : val);
        });
    });
    
    // Submit edição em massa
    $('#formEditarLote').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        formData += '&event_id=' + eventId;
        formData += '&' + csrfName + '=' + csrfToken;
        
        $.ajax({
            type: 'POST',
            url: '<?= site_url('tickets/atualizarLote') ?>',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.token) csrfToken = response.token;
                if (response.sucesso) {
                    $('#modalEditarLote').modal('hide');
                    alert(response.sucesso);
                    location.reload();
                } else {
                    alert(response.erro || 'Erro ao salvar');
                }
            },
            error: function() { alert('Erro ao processar solicitação'); }
        });
    });
});
</script>
<?php echo $this->endSection() ?>
