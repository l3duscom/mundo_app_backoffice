<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
    .meet-card { transition: transform .15s; }
    .meet-card:hover { transform: translateY(-2px); box-shadow: 0 0 10px rgba(108,3,143,.12); }
    .meet-badge-tipo { font-size: 11px; letter-spacing:.5px; text-transform: uppercase; }
    .dia-header { border-left: 4px solid #6C038F; padding-left: 10px; color: #6C038F; }
    .meet-horario { font-weight: 600; color: #6C038F; }
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Meet &amp; Greet</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Meet &amp; Greet</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <button type="button" class="btn btn-primary" onclick="novoMeet()">
            <i class="bx bx-plus me-1"></i>Novo Meet &amp; Greet
        </button>
    </div>
</div>

<div id="mensagens"></div>

<div class="card shadow radius-10 mb-3">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div class="me-3"><i class="bx bx-calendar-event fs-3 text-primary"></i></div>
            <div>
                <small class="text-muted d-block">Evento atual</small>
                <h5 class="mb-0"><?= esc($evento->nome) ?></h5>
            </div>
        </div>
    </div>
</div>

<?php if (empty($meets)) : ?>
    <div class="alert alert-warning">
        <i class="bx bx-info-circle me-1"></i>Nenhum Meet &amp; Greet cadastrado para o evento <strong><?= esc($evento->nome) ?></strong>.
        Clique em <em>Novo Meet &amp; Greet</em> para começar.
    </div>
<?php else : ?>
    <?php
    $diasOrdenados = array_keys($porDia);
    sort($diasOrdenados);
    ?>
    <?php foreach ($diasOrdenados as $diaKey) : ?>
        <h5 class="dia-header mb-3">
            <?php if ($diaKey === 'sem_dia') : ?>
                <i class="bx bx-question-mark me-1"></i> Sem data definida
            <?php else : ?>
                <i class="bx bx-calendar me-1"></i> <?= date('d/m/Y', strtotime($diaKey)) ?>
            <?php endif; ?>
            <span class="badge bg-secondary ms-2"><?= count($porDia[$diaKey]) ?></span>
        </h5>
        <div class="row mb-4">
            <?php foreach ($porDia[$diaKey] as $item) : ?>
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card meet-card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0"><?= esc($item->artista) ?></h6>
                                <?php if (!empty($item->tipo)) : ?>
                                    <span class="badge bg-primary meet-badge-tipo"><?= esc($item->tipo) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="meet-horario mb-2">
                                <i class="bx bx-time-five"></i> <?= $item->getHorarioFormatado() ?>
                            </div>
                            <div class="text-muted small mb-2">
                                <?php if (!empty($item->dia)) : ?>
                                    <div><i class="bx bx-calendar-event"></i> <?= esc($item->dia) ?></div>
                                <?php endif; ?>
                                <div><i class="bx bx-group"></i> Quantidade: <strong><?= (int) $item->quantidade ?></strong></div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary"
                                        onclick='editarMeet(<?= json_encode([
                                            "id"           => $item->id,
                                            "artista"      => $item->artista,
                                            "dia"          => $item->dia,
                                            "tipo"         => $item->tipo,
                                            "quantidade"   => (int) $item->quantidade,
                                            "data_meet"    => $item->data_meet ? date("Y-m-d", strtotime((string) $item->data_meet)) : "",
                                            "hora_inicial" => $item->hora_inicial ? substr((string) $item->hora_inicial, 0, 5) : "",
                                            "hora_final"   => $item->hora_final ? substr((string) $item->hora_final, 0, 5) : "",
                                        ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' title="Editar">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" onclick="excluirMeet(<?= $item->id ?>)" title="Excluir">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- MODAL -->
<div class="modal fade" id="modalMeet" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="formMeet">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalMeetTitulo"><i class="bx bx-star me-2"></i>Novo Meet &amp; Greet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="mt_id">

                    <div class="mb-3">
                        <label class="form-label">Artista <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="artista" id="mt_artista" required minlength="2" maxlength="255">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Data</label>
                            <select name="data_meet" id="mt_data_meet" class="form-select">
                                <option value="">-- Selecione --</option>
                                <?php foreach ($dias as $d) : ?>
                                    <option value="<?= $d['date'] ?>"><?= $d['formatado'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Dia (rótulo livre)</label>
                            <input type="text" class="form-control" name="dia" id="mt_dia" maxlength="50" placeholder="Ex.: Sábado, Dia 1...">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Hora inicial</label>
                            <input type="time" class="form-control" name="hora_inicial" id="mt_hora_inicial">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Hora final</label>
                            <input type="time" class="form-control" name="hora_final" id="mt_hora_final">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Quantidade</label>
                            <input type="number" class="form-control" name="quantidade" id="mt_quantidade" value="0" min="0">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipo</label>
                        <input list="mt_tipos_list" name="tipo" id="mt_tipo" class="form-control" maxlength="50" placeholder="Comum, VIP, Epic...">
                        <datalist id="mt_tipos_list">
                            <?php foreach ($tipos as $t) : ?>
                                <option value="<?= esc($t) ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
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

const URL_SALVAR  = '<?= site_url('meet/salvar') ?>';
const URL_EXCLUIR = '<?= site_url('meet/excluir') ?>';

function alerta(msg, tipo = 'success') {
    const id = 'al-' + Date.now();
    document.getElementById('mensagens').insertAdjacentHTML('beforeend', `
        <div id="${id}" class="alert alert-${tipo} alert-dismissible fade show">
            <i class="bx ${tipo === 'success' ? 'bx-check-circle' : 'bx-error-circle'} me-2"></i>${msg}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`);
    setTimeout(() => { const el = document.getElementById(id); if (el) el.remove(); }, 4000);
}

function postAjax(url, formData) {
    formData.append(csrfName, csrfToken);
    return fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'same-origin'
    }).then(r => r.json()).then(data => {
        if (data.token) csrfToken = data.token;
        return data;
    });
}

function novoMeet() {
    document.getElementById('formMeet').reset();
    document.getElementById('mt_id').value = '';
    document.getElementById('modalMeetTitulo').innerHTML = '<i class="bx bx-star me-2"></i>Novo Meet &amp; Greet';
    new bootstrap.Modal(document.getElementById('modalMeet')).show();
}

function editarMeet(item) {
    document.getElementById('formMeet').reset();
    document.getElementById('mt_id').value           = item.id;
    document.getElementById('mt_artista').value      = item.artista || '';
    document.getElementById('mt_dia').value          = item.dia || '';
    document.getElementById('mt_tipo').value         = item.tipo || '';
    document.getElementById('mt_quantidade').value   = item.quantidade || 0;
    document.getElementById('mt_data_meet').value    = item.data_meet || '';
    document.getElementById('mt_hora_inicial').value = item.hora_inicial || '';
    document.getElementById('mt_hora_final').value   = item.hora_final || '';
    document.getElementById('modalMeetTitulo').innerHTML = '<i class="bx bx-edit me-2"></i>Editar Meet &amp; Greet';
    new bootstrap.Modal(document.getElementById('modalMeet')).show();
}

document.getElementById('formMeet').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    postAjax(URL_SALVAR, fd).then(data => {
        if (data.sucesso) {
            alerta(data.sucesso);
            bootstrap.Modal.getInstance(document.getElementById('modalMeet')).hide();
            setTimeout(() => location.reload(), 600);
        } else {
            let msg = data.erro || 'Erro ao salvar.';
            if (data.erros_model) {
                msg += '<br>' + Object.values(data.erros_model).join('<br>');
            }
            if (data.debug) {
                console.error('Debug meet/salvar:', data.debug);
                msg += '<br><small class="text-muted">' + data.debug.arquivo + '</small>';
            }
            alerta(msg, 'danger');
        }
    }).catch(err => alerta(err.message || 'Erro de comunicação com o servidor.', 'danger'));
});

function excluirMeet(id) {
    if (!confirm('Remover este Meet & Greet?')) return;
    const fd = new FormData();
    fd.append('id', id);
    postAjax(URL_EXCLUIR, fd).then(data => {
        if (data.sucesso) {
            alerta(data.sucesso);
            setTimeout(() => location.reload(), 600);
        } else {
            alerta(data.erro || 'Erro ao remover.', 'danger');
        }
    });
}
</script>
<?php echo $this->endSection() ?>
