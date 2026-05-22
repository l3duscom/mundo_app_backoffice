<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
    .lineup-card { transition: transform .15s; }
    .lineup-card:hover { transform: translateY(-2px); }
    .lineup-img {
        width: 100%; height: 180px; object-fit: cover; border-top-left-radius: .5rem; border-top-right-radius: .5rem;
        background: #2a2a2a;
    }
    .lineup-img-placeholder {
        width: 100%; height: 180px; display:flex; align-items:center; justify-content:center;
        background: #2a2a2a; color:#666; font-size: 3rem;
        border-top-left-radius: .5rem; border-top-right-radius: .5rem;
    }
    .lineup-tipo { font-size: 11px; letter-spacing:.5px; text-transform: uppercase; }
    .preview-img { max-height: 200px; max-width: 100%; border-radius: .25rem; }
    .dia-header { border-left: 4px solid var(--bs-primary); padding-left: 10px; }
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Line-up</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Line-up</li>
            </ol>
        </nav>
    </div>
</div>

<div id="mensagens"></div>

<!-- Filtro evento -->
<div class="card shadow radius-10 mb-3">
    <div class="card-body">
        <form method="get" action="<?= site_url('lineup') ?>" class="row g-3 align-items-end">
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
                    <button type="button" class="btn btn-primary" onclick="novoLineup()">
                        <i class="bx bx-plus me-1"></i>Nova Atração
                    </button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php if (empty($eventIdSelecionado)) : ?>
    <div class="alert alert-info"><i class="bx bx-info-circle me-1"></i>Selecione um evento para gerenciar o line-up.</div>
<?php elseif (empty($lineup)) : ?>
    <div class="alert alert-warning">
        <i class="bx bx-info-circle me-1"></i>Nenhuma atração cadastrada para o evento <strong><?= esc($evento->nome ?? '') ?></strong>.
        Clique em <em>Nova Atração</em> para começar.
    </div>
<?php else : ?>

    <?php
    $diasOrdenados = array_keys($porDia);
    sort($diasOrdenados);
    ?>
    <?php foreach ($diasOrdenados as $diaKey) : ?>
        <h5 class="dia-header mb-3">
            <?php if ($diaKey === 'sem_dia') : ?>
                <i class="bx bx-question-mark me-1"></i> Sem dia definido
            <?php else : ?>
                <i class="bx bx-calendar me-1"></i> <?= date('d/m/Y', strtotime($diaKey)) ?>
            <?php endif; ?>
            <span class="badge bg-secondary ms-2"><?= count($porDia[$diaKey]) ?></span>
        </h5>
        <div class="row mb-4">
            <?php foreach ($porDia[$diaKey] as $item) : ?>
                <div class="col-md-4 col-lg-3 mb-3">
                    <div class="card lineup-card shadow-sm h-100">
                        <?php if (!empty($item->imagem)) : ?>
                            <img src="<?= site_url('lineup/imagem/' . $item->imagem) ?>" class="lineup-img" alt="<?= esc($item->nome) ?>">
                        <?php else : ?>
                            <div class="lineup-img-placeholder"><i class="bx bx-image"></i></div>
                        <?php endif; ?>
                        <div class="card-body">
                            <?php if (!empty($item->tipo)) : ?>
                                <span class="badge bg-primary lineup-tipo mb-1"><?= esc($item->tipo) ?></span>
                            <?php endif; ?>
                            <h6 class="mb-1"><?= esc($item->nome) ?></h6>
                            <small class="text-muted d-block mb-2"><?= esc(character_limiter($item->descricao ?? '', 80)) ?></small>
                            <div class="d-flex justify-content-between align-items-center">
                                <?= $item->getBadgeStatus() ?>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary"
                                        onclick='editarLineup(<?= json_encode([
                                            "id"        => $item->id,
                                            "nome"      => $item->nome,
                                            "dia"       => $item->dia ? date("Y-m-d", strtotime((string) $item->dia)) : "",
                                            "tipo"      => $item->tipo,
                                            "descricao" => $item->descricao,
                                            "ordem"     => (int) $item->ordem,
                                            "ativo"     => (int) $item->ativo,
                                            "imagem"    => $item->imagem,
                                        ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' title="Editar">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" onclick="excluirLineup(<?= $item->id ?>)" title="Excluir">
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

<!-- ===================== MODAL ===================== -->
<div class="modal fade" id="modalLineup" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="formLineup" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLineupTitulo"><i class="bx bx-music me-2"></i>Nova Atração</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="lu_id">
                    <input type="hidden" name="event_id" value="<?= esc($eventIdSelecionado ?? '') ?>">

                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Nome <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nome" id="lu_nome" required minlength="2" maxlength="255">
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Dia</label>
                                    <select name="dia" id="lu_dia" class="form-select">
                                        <option value="">-- Selecione --</option>
                                        <?php foreach ($dias as $d) : ?>
                                            <option value="<?= $d['date'] ?>"><?= $d['formatado'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tipo</label>
                                    <input list="lu_tipos_list" name="tipo" id="lu_tipo" class="form-control" maxlength="60" placeholder="Show, DJ, Banda...">
                                    <datalist id="lu_tipos_list">
                                        <?php foreach ($tipos as $t) : ?>
                                            <option value="<?= esc($t) ?>"></option>
                                        <?php endforeach; ?>
                                    </datalist>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Descrição</label>
                                <textarea class="form-control" name="descricao" id="lu_descricao" rows="4" maxlength="2000"></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Ordem</label>
                                        <input type="number" class="form-control" name="ordem" id="lu_ordem" value="0" min="0">
                                    </div>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" name="ativo" id="lu_ativo" value="1" checked>
                                        <label class="form-check-label" for="lu_ativo">Ativo</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Imagem</label>
                            <input type="file" class="form-control mb-2" name="imagem" id="lu_imagem" accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted d-block mb-2">JPG, PNG ou WEBP — máx 2MB.</small>
                            <div class="text-center">
                                <img id="lu_preview" src="" alt="" class="preview-img" style="display:none;">
                                <div id="lu_sem_imagem" class="text-muted small fst-italic">Sem imagem</div>
                            </div>
                        </div>
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

const URL_SALVAR  = '<?= site_url('lineup/salvar') ?>';
const URL_EXCLUIR = '<?= site_url('lineup/excluir') ?>';
const URL_IMAGEM  = '<?= site_url('lineup/imagem') ?>';

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

function novoLineup() {
    document.getElementById('formLineup').reset();
    document.getElementById('lu_id').value = '';
    document.getElementById('lu_ativo').checked = true;
    document.getElementById('lu_preview').style.display = 'none';
    document.getElementById('lu_preview').src = '';
    document.getElementById('lu_sem_imagem').style.display = '';
    document.getElementById('modalLineupTitulo').innerHTML = '<i class="bx bx-music me-2"></i>Nova Atração';
    new bootstrap.Modal(document.getElementById('modalLineup')).show();
}

function editarLineup(item) {
    document.getElementById('formLineup').reset();
    document.getElementById('lu_id').value        = item.id;
    document.getElementById('lu_nome').value      = item.nome || '';
    document.getElementById('lu_dia').value       = item.dia || '';
    document.getElementById('lu_tipo').value      = item.tipo || '';
    document.getElementById('lu_descricao').value = item.descricao || '';
    document.getElementById('lu_ordem').value     = item.ordem || 0;
    document.getElementById('lu_ativo').checked   = item.ativo == 1;

    const preview = document.getElementById('lu_preview');
    const semImg  = document.getElementById('lu_sem_imagem');
    if (item.imagem) {
        preview.src = URL_IMAGEM + '/' + item.imagem;
        preview.style.display = '';
        semImg.style.display = 'none';
    } else {
        preview.src = '';
        preview.style.display = 'none';
        semImg.style.display = '';
    }

    document.getElementById('modalLineupTitulo').innerHTML = '<i class="bx bx-edit me-2"></i>Editar Atração';
    new bootstrap.Modal(document.getElementById('modalLineup')).show();
}

// Limite do arquivo (deve casar com php.ini do servidor — geralmente 2MB)
const MAX_UPLOAD_BYTES = 2 * 1024 * 1024;

// Preview ao escolher imagem
document.getElementById('lu_imagem').addEventListener('change', function() {
    const file = this.files[0];
    const preview = document.getElementById('lu_preview');
    const semImg  = document.getElementById('lu_sem_imagem');
    if (!file) return;

    if (file.size > MAX_UPLOAD_BYTES) {
        alerta('Imagem muito grande (' + (file.size / 1024 / 1024).toFixed(2) + 'MB). Máximo 2MB. Compresse a imagem antes de enviar.', 'danger');
        this.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = e => {
        preview.src = e.target.result;
        preview.style.display = '';
        semImg.style.display = 'none';
    };
    reader.readAsDataURL(file);
});

document.getElementById('formLineup').addEventListener('submit', function(e) {
    e.preventDefault();

    // Confere tamanho do arquivo antes de subir
    const fileInput = document.getElementById('lu_imagem');
    if (fileInput.files[0] && fileInput.files[0].size > MAX_UPLOAD_BYTES) {
        alerta('Imagem maior que 2MB. Compresse a imagem antes de enviar.', 'danger');
        return;
    }

    const fd = new FormData(this);
    fd.append(csrfName, csrfToken);

    fetch(URL_SALVAR, {
        method: 'POST',
        body: fd,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'same-origin'
    }).then(async r => {
        if (r.status === 403) {
            throw new Error('CSRF/upload bloqueado (403). Recarregue a página (F5) e tente novamente.');
        }
        const ct = r.headers.get('content-type') || '';
        if (!ct.includes('application/json')) {
            const txt = await r.text();
            console.error('Resposta não-JSON:', txt.substring(0, 300));
            throw new Error('Resposta inesperada do servidor. Veja o console.');
        }
        return r.json();
    }).then(data => {
        if (data.token) csrfToken = data.token;
        if (data.sucesso) {
            alerta(data.sucesso);
            bootstrap.Modal.getInstance(document.getElementById('modalLineup')).hide();
            setTimeout(() => location.reload(), 600);
        } else {
            let msg = data.erro || 'Erro ao salvar.';
            if (data.erros_model) {
                msg += '<br>' + Object.values(data.erros_model).join('<br>');
            }
            alerta(msg, 'danger');
        }
    }).catch(err => alerta(err.message || 'Erro de comunicação com o servidor.', 'danger'));
});

function excluirLineup(id) {
    if (!confirm('Remover esta atração?')) return;
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
