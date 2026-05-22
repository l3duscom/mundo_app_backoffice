<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
    .cronograma-card .card-header { cursor: pointer; }
    .cronograma-card .toggle-icon { transition: transform 0.2s; }
    .cronograma-card.collapsed .toggle-icon { transform: rotate(-90deg); }
    .item-row td { vertical-align: middle; }
    .status-select { min-width: 140px; }
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<!-- breadcrumb -->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Cronogramas</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Cronogramas</li>
            </ol>
        </nav>
    </div>
</div>

<div id="mensagens"></div>

<!-- Card filtro / contexto evento -->
<div class="card shadow radius-10 mb-3">
    <div class="card-body">
        <form method="get" action="<?= site_url('cronograma') ?>" class="row g-3 align-items-end">
            <div class="col-md-8">
                <label class="form-label mb-1"><i class="bx bx-calendar-event me-1"></i>Evento</label>
                <select name="evento_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Selecione um evento --</option>
                    <?php foreach ($eventos as $ev) : ?>
                        <option value="<?= $ev->id ?>" <?= ($eventIdSelecionado == $ev->id) ? 'selected' : '' ?>>
                            <?= esc($ev->nome) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 text-end">
                <?php if (!empty($eventIdSelecionado)) : ?>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCronograma" onclick="novoCronograma()">
                        <i class="bx bx-plus me-1"></i>Novo Cronograma
                    </button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php if (empty($eventIdSelecionado)) : ?>
    <div class="alert alert-info">
        <i class="bx bx-info-circle me-1"></i>Selecione um evento para gerenciar seus cronogramas.
    </div>
<?php elseif (empty($cronogramas)) : ?>
    <div class="alert alert-warning">
        <i class="bx bx-info-circle me-1"></i>Nenhum cronograma cadastrado para o evento <strong><?= esc($evento->nome ?? '') ?></strong>.
        Clique em <em>Novo Cronograma</em> para criar.
    </div>
<?php else : ?>
    <?php foreach ($cronogramas as $cron) : ?>
        <div class="card shadow radius-10 mb-3 cronograma-card" data-cronograma-id="<?= $cron->id ?>">
            <div class="card-header d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#cron-body-<?= $cron->id ?>" aria-expanded="true">
                <div class="d-flex align-items-center gap-2">
                    <i class="bx bx-chevron-down toggle-icon"></i>
                    <h6 class="mb-0"><i class="bx bx-calendar me-2 text-primary"></i><?= esc($cron->name) ?></h6>
                    <?= $cron->getBadgeStatus() ?>
                </div>
                <div class="btn-group btn-group-sm" onclick="event.stopPropagation();">
                    <button type="button" class="btn btn-outline-success" onclick="novoItem(<?= $cron->id ?>)" title="Adicionar item">
                        <i class="bx bx-plus"></i> Item
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick='editarCronograma(<?= json_encode(["id" => $cron->id, "name" => $cron->name, "ativo" => (int) $cron->ativo]) ?>)' title="Editar cronograma">
                        <i class="bx bx-edit"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger" onclick="excluirCronograma(<?= $cron->id ?>)" title="Excluir cronograma">
                        <i class="bx bx-trash"></i>
                    </button>
                </div>
            </div>
            <div id="cron-body-<?= $cron->id ?>" class="collapse show">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0" id="tabela-itens-<?= $cron->id ?>">
                            <thead class="table-light">
                                <tr>
                                    <th>Nome do Item</th>
                                    <th>Início</th>
                                    <th>Fim</th>
                                    <th>Duração</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center" style="width: 140px;">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td colspan="6" class="text-center text-muted py-3">Carregando itens...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- ===================== MODAL CRONOGRAMA ===================== -->
<div class="modal fade" id="modalCronograma" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formCronograma" onsubmit="return salvarCronograma(event)">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCronogramaTitulo"><i class="bx bx-calendar me-2"></i>Novo Cronograma</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="cron_id">
                    <input type="hidden" name="event_id" value="<?= esc($eventIdSelecionado ?? '') ?>">
                    <div class="mb-3">
                        <label class="form-label">Nome <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="cron_name" required minlength="3" maxlength="255">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="ativo" id="cron_ativo" value="1" checked>
                        <label class="form-check-label" for="cron_ativo">Ativo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===================== MODAL ITEM ===================== -->
<div class="modal fade" id="modalItem" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formItem" onsubmit="return salvarItem(event)">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalItemTitulo"><i class="bx bx-list-ul me-2"></i>Novo Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="item_id">
                    <input type="hidden" name="cronograma_id" id="item_cronograma_id">
                    <div class="mb-3">
                        <label class="form-label">Nome do Item <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nome_item" id="item_nome" required minlength="3" maxlength="255">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Data/Hora Início</label>
                            <input type="datetime-local" class="form-control" name="data_hora_inicio" id="item_inicio">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Data/Hora Fim</label>
                            <input type="datetime-local" class="form-control" name="data_hora_fim" id="item_fim">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="item_status" class="form-select">
                            <option value="AGUARDANDO">Aguardando</option>
                            <option value="EM_ANDAMENTO">Em Andamento</option>
                            <option value="CONCLUIDO">Concluído</option>
                            <option value="CANCELADO">Cancelado</option>
                        </select>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="ativo" id="item_ativo" value="1" checked>
                        <label class="form-check-label" for="item_ativo">Ativo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script>
let csrfToken = '<?= csrf_hash() ?>';
const csrfName = '<?= csrf_token() ?>';

const URL_SALVAR_CRON      = '<?= site_url('cronograma/salvar') ?>';
const URL_EXCLUIR_CRON     = '<?= site_url('cronograma/excluir') ?>';
const URL_ITENS            = '<?= site_url('cronograma/itens') ?>';
const URL_SALVAR_ITEM      = '<?= site_url('cronograma/salvarItem') ?>';
const URL_EXCLUIR_ITEM     = '<?= site_url('cronograma/excluirItem') ?>';
const URL_ALT_STATUS_ITEM  = '<?= site_url('cronograma/alterarStatusItem') ?>';

function alerta(msg, tipo = 'success') {
    const id = 'alert-' + Date.now();
    const html = `
        <div id="${id}" class="alert alert-${tipo} alert-dismissible fade show" role="alert">
            <i class="bx ${tipo === 'success' ? 'bx-check-circle' : 'bx-error-circle'} me-2"></i>${msg}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
    document.getElementById('mensagens').insertAdjacentHTML('beforeend', html);
    setTimeout(() => {
        const el = document.getElementById(id);
        if (el) el.remove();
    }, 4000);
}

function postAjax(url, formData) {
    formData.append(csrfName, csrfToken);
    return fetch(url, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.token) csrfToken = data.token;
            return data;
        });
}

// ============ CRONOGRAMA ============
function novoCronograma() {
    document.getElementById('formCronograma').reset();
    document.getElementById('cron_id').value = '';
    document.getElementById('cron_ativo').checked = true;
    document.getElementById('modalCronogramaTitulo').innerHTML = '<i class="bx bx-calendar me-2"></i>Novo Cronograma';
}

function editarCronograma(cron) {
    document.getElementById('cron_id').value = cron.id;
    document.getElementById('cron_name').value = cron.name;
    document.getElementById('cron_ativo').checked = cron.ativo == 1;
    document.getElementById('modalCronogramaTitulo').innerHTML = '<i class="bx bx-edit me-2"></i>Editar Cronograma';
    new bootstrap.Modal(document.getElementById('modalCronograma')).show();
}

function salvarCronograma(e) {
    e.preventDefault();
    const fd = new FormData(document.getElementById('formCronograma'));
    postAjax(URL_SALVAR_CRON, fd).then(data => {
        if (data.sucesso) {
            alerta(data.sucesso);
            bootstrap.Modal.getInstance(document.getElementById('modalCronograma')).hide();
            setTimeout(() => location.reload(), 600);
        } else {
            let msg = data.erro || 'Erro ao salvar.';
            if (data.erros_model) {
                msg += '<br>' + Object.values(data.erros_model).join('<br>');
            }
            alerta(msg, 'danger');
        }
    });
    return false;
}

function excluirCronograma(id) {
    if (!confirm('Excluir este cronograma? Todos os itens serão removidos.')) return;
    const fd = new FormData();
    fd.append('id', id);
    postAjax(URL_EXCLUIR_CRON, fd).then(data => {
        if (data.sucesso) {
            alerta(data.sucesso);
            setTimeout(() => location.reload(), 600);
        } else {
            alerta(data.erro || 'Erro ao excluir.', 'danger');
        }
    });
}

// ============ ITENS ============
function carregarItens(cronogramaId) {
    fetch(URL_ITENS + '/' + cronogramaId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(resp => {
            const tbody = document.querySelector('#tabela-itens-' + cronogramaId + ' tbody');
            if (!resp.data || resp.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">Nenhum item cadastrado.</td></tr>';
                return;
            }
            tbody.innerHTML = resp.data.map(item => `
                <tr class="item-row">
                    <td><strong>${item.nome_item}</strong></td>
                    <td>${item.data_hora_inicio}</td>
                    <td>${item.data_hora_fim}</td>
                    <td>${item.duracao}</td>
                    <td class="text-center">
                        <select class="form-select form-select-sm status-select" onchange="alterarStatus(${item.id}, this.value, ${cronogramaId})">
                            <option value="AGUARDANDO" ${item.status_raw === 'AGUARDANDO' ? 'selected' : ''}>Aguardando</option>
                            <option value="EM_ANDAMENTO" ${item.status_raw === 'EM_ANDAMENTO' ? 'selected' : ''}>Em Andamento</option>
                            <option value="CONCLUIDO" ${item.status_raw === 'CONCLUIDO' ? 'selected' : ''}>Concluído</option>
                            <option value="CANCELADO" ${item.status_raw === 'CANCELADO' ? 'selected' : ''}>Cancelado</option>
                        </select>
                    </td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary" onclick='editarItem(${JSON.stringify(item)}, ${cronogramaId})' title="Editar">
                                <i class="bx bx-edit"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger" onclick="excluirItem(${item.id}, ${cronogramaId})" title="Excluir">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        });
}

function novoItem(cronogramaId) {
    document.getElementById('formItem').reset();
    document.getElementById('item_id').value = '';
    document.getElementById('item_cronograma_id').value = cronogramaId;
    document.getElementById('item_ativo').checked = true;
    document.getElementById('item_status').value = 'AGUARDANDO';
    document.getElementById('modalItemTitulo').innerHTML = '<i class="bx bx-list-ul me-2"></i>Novo Item';
    new bootstrap.Modal(document.getElementById('modalItem')).show();
}

function editarItem(item, cronogramaId) {
    document.getElementById('item_id').value = item.id;
    document.getElementById('item_cronograma_id').value = cronogramaId;
    document.getElementById('item_nome').value = item.nome_item;
    document.getElementById('item_inicio').value = item.inicio_raw || '';
    document.getElementById('item_fim').value = item.fim_raw || '';
    document.getElementById('item_status').value = item.status_raw;
    document.getElementById('item_ativo').checked = item.ativo == 1;
    document.getElementById('modalItemTitulo').innerHTML = '<i class="bx bx-edit me-2"></i>Editar Item';
    new bootstrap.Modal(document.getElementById('modalItem')).show();
}

function salvarItem(e) {
    e.preventDefault();
    const fd = new FormData(document.getElementById('formItem'));
    const cronogramaId = document.getElementById('item_cronograma_id').value;
    postAjax(URL_SALVAR_ITEM, fd).then(data => {
        if (data.sucesso) {
            alerta(data.sucesso);
            bootstrap.Modal.getInstance(document.getElementById('modalItem')).hide();
            carregarItens(cronogramaId);
        } else {
            let msg = data.erro || 'Erro ao salvar.';
            if (data.erros_model) {
                msg += '<br>' + Object.values(data.erros_model).join('<br>');
            }
            alerta(msg, 'danger');
        }
    });
    return false;
}

function excluirItem(id, cronogramaId) {
    if (!confirm('Excluir este item?')) return;
    const fd = new FormData();
    fd.append('id', id);
    postAjax(URL_EXCLUIR_ITEM, fd).then(data => {
        if (data.sucesso) {
            alerta(data.sucesso);
            carregarItens(cronogramaId);
        } else {
            alerta(data.erro || 'Erro ao excluir.', 'danger');
        }
    });
}

function alterarStatus(id, status, cronogramaId) {
    const fd = new FormData();
    fd.append('id', id);
    fd.append('status', status);
    postAjax(URL_ALT_STATUS_ITEM, fd).then(data => {
        if (data.sucesso) {
            alerta(data.sucesso);
            carregarItens(cronogramaId);
        } else {
            alerta(data.erro || 'Erro ao atualizar status.', 'danger');
        }
    });
}

// ============ INIT ============
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.cronograma-card').forEach(card => {
        const id = card.dataset.cronogramaId;
        carregarItens(id);
    });
});
</script>
<?php echo $this->endSection() ?>
