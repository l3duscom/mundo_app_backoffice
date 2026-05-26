<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
    .banner-card {
        transition: transform .15s, box-shadow .15s;
        cursor: grab;
    }
    .banner-card:hover { transform: translateY(-2px); box-shadow: 0 0 10px rgba(108,3,143,.15); }
    .banner-card.sortable-ghost { opacity: .4; }
    .banner-card.sortable-chosen { cursor: grabbing; }
    .banner-img {
        width: 100%; height: 160px; object-fit: cover;
        border-top-left-radius: .5rem; border-top-right-radius: .5rem;
        background: #2a2a2a;
    }
    .banner-handle {
        position: absolute; top: 8px; left: 8px;
        background: rgba(0,0,0,.55); color: #fff;
        border-radius: 50%; width: 28px; height: 28px;
        display:flex; align-items:center; justify-content:center;
        font-size: 16px;
    }
    .banner-ordem {
        position: absolute; top: 8px; right: 8px;
        background: #6C038F; color:#fff;
        border-radius: 50%; width: 28px; height: 28px;
        display:flex; align-items:center; justify-content:center;
        font-size: 13px; font-weight: bold;
    }
    .preview-img { max-height: 200px; max-width: 100%; border-radius: .25rem; }
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Banners</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Banners</li>
            </ol>
        </nav>
    </div>
</div>

<div id="mensagens"></div>

<div class="card shadow radius-10 mb-3">
    <div class="card-body">
        <form method="get" action="<?= site_url('banners') ?>" class="row g-3 align-items-end">
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
                    <button type="button" class="btn btn-primary" onclick="novoBanner()">
                        <i class="bx bx-plus me-1"></i>Novo Banner
                    </button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php if (empty($eventIdSelecionado)) : ?>
    <div class="alert alert-info"><i class="bx bx-info-circle me-1"></i>Selecione um evento para gerenciar os banners.</div>
<?php elseif (empty($banners)) : ?>
    <div class="alert alert-warning">
        <i class="bx bx-info-circle me-1"></i>Nenhum banner cadastrado para o evento <strong><?= esc($evento->nome ?? '') ?></strong>.
        Clique em <em>Novo Banner</em> para começar.
    </div>
<?php else : ?>
    <div class="alert alert-light border small">
        <i class="bx bx-move me-1"></i> Arraste os cards para reordenar. A ordem é salva automaticamente.
    </div>
    <div class="row" id="banners-grid">
        <?php foreach ($banners as $i => $item) : ?>
            <div class="col-md-4 col-lg-3 mb-3 banner-item" data-id="<?= $item->id ?>">
                <div class="card banner-card shadow-sm h-100 position-relative">
                    <div class="banner-handle"><i class="bx bx-move"></i></div>
                    <div class="banner-ordem"><?= $i + 1 ?></div>
                    <?php if (!empty($item->imagem)) : ?>
                        <img src="<?= site_url('banners/imagem/' . $item->imagem) ?>" class="banner-img" alt="Banner">
                    <?php endif; ?>
                    <div class="card-body">
                        <?php if (!empty($item->link)) : ?>
                            <small class="text-muted d-block mb-2 text-truncate" title="<?= esc($item->link) ?>">
                                <i class="bx bx-link"></i> <?= esc($item->link) ?>
                            </small>
                        <?php else : ?>
                            <small class="text-muted d-block mb-2 fst-italic">Sem link</small>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between align-items-center">
                            <?= $item->getBadgeStatus() ?>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary"
                                    onclick='editarBanner(<?= json_encode([
                                        "id"     => $item->id,
                                        "link"   => $item->link,
                                        "ordem"  => (int) $item->ordem,
                                        "ativo"  => (int) $item->ativo,
                                        "imagem" => $item->imagem,
                                    ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' title="Editar">
                                    <i class="bx bx-edit"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger" onclick="excluirBanner(<?= $item->id ?>)" title="Excluir">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- MODAL -->
<div class="modal fade" id="modalBanner" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="formBanner" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalBannerTitulo"><i class="bx bx-image me-2"></i>Novo Banner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="bn_id">
                    <input type="hidden" name="event_id" value="<?= esc($eventIdSelecionado ?? '') ?>">

                    <div class="row">
                        <div class="col-md-7">
                            <div class="mb-3">
                                <label class="form-label">Link de destino</label>
                                <input type="text" class="form-control" name="link" id="bn_link" placeholder="https://... ou rota interna do app">
                                <small class="text-muted">Opcional — URL completa ou identificador de tela do app.</small>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ordem</label>
                                    <input type="number" class="form-control" name="ordem" id="bn_ordem" value="0" min="0">
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" name="ativo" id="bn_ativo" value="1" checked>
                                        <label class="form-check-label" for="bn_ativo">Ativo</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Imagem <span class="text-danger" id="bn_img_required">*</span></label>
                            <input type="file" class="form-control mb-2" name="imagem" id="bn_imagem" accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted d-block mb-2">JPG, PNG ou WEBP — máx 5MB.</small>
                            <div class="text-center">
                                <img id="bn_preview" src="" alt="" class="preview-img" style="display:none;">
                                <div id="bn_sem_imagem" class="text-muted small fst-italic">Sem imagem</div>
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
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
let csrfToken = '<?= csrf_hash() ?>';
const csrfName = '<?= csrf_token() ?>';

const URL_SALVAR    = '<?= site_url('banners/salvar') ?>';
const URL_EXCLUIR   = '<?= site_url('banners/excluir') ?>';
const URL_REORDENAR = '<?= site_url('banners/reordenar') ?>';
const URL_IMAGEM    = '<?= site_url('banners/imagem') ?>';
const EVENT_ID      = '<?= esc($eventIdSelecionado ?? '') ?>';

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

function novoBanner() {
    document.getElementById('formBanner').reset();
    document.getElementById('bn_id').value = '';
    document.getElementById('bn_ativo').checked = true;
    document.getElementById('bn_preview').style.display = 'none';
    document.getElementById('bn_preview').src = '';
    document.getElementById('bn_sem_imagem').style.display = '';
    document.getElementById('bn_img_required').style.display = '';
    document.getElementById('modalBannerTitulo').innerHTML = '<i class="bx bx-image me-2"></i>Novo Banner';
    new bootstrap.Modal(document.getElementById('modalBanner')).show();
}

function editarBanner(item) {
    document.getElementById('formBanner').reset();
    document.getElementById('bn_id').value    = item.id;
    document.getElementById('bn_link').value  = item.link || '';
    document.getElementById('bn_ordem').value = item.ordem || 0;
    document.getElementById('bn_ativo').checked = item.ativo == 1;

    const preview = document.getElementById('bn_preview');
    const semImg  = document.getElementById('bn_sem_imagem');
    if (item.imagem) {
        preview.src = URL_IMAGEM + '/' + item.imagem;
        preview.style.display = '';
        semImg.style.display = 'none';
    } else {
        preview.src = '';
        preview.style.display = 'none';
        semImg.style.display = '';
    }
    document.getElementById('bn_img_required').style.display = 'none';

    document.getElementById('modalBannerTitulo').innerHTML = '<i class="bx bx-edit me-2"></i>Editar Banner';
    new bootstrap.Modal(document.getElementById('modalBanner')).show();
}

const MAX_UPLOAD_BYTES = 5 * 1024 * 1024;

document.getElementById('bn_imagem').addEventListener('change', function() {
    const file = this.files[0];
    const preview = document.getElementById('bn_preview');
    const semImg  = document.getElementById('bn_sem_imagem');
    if (!file) return;

    if (file.size > MAX_UPLOAD_BYTES) {
        alerta('Imagem muito grande (' + (file.size / 1024 / 1024).toFixed(2) + 'MB). Máximo 5MB.', 'danger');
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

document.getElementById('formBanner').addEventListener('submit', function(e) {
    e.preventDefault();

    const fileInput = document.getElementById('bn_imagem');
    if (fileInput.files[0] && fileInput.files[0].size > MAX_UPLOAD_BYTES) {
        alerta('Imagem maior que 5MB.', 'danger');
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
            bootstrap.Modal.getInstance(document.getElementById('modalBanner')).hide();
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

function excluirBanner(id) {
    if (!confirm('Remover este banner?')) return;
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

const grid = document.getElementById('banners-grid');
if (grid) {
    Sortable.create(grid, {
        animation: 150,
        handle: '.banner-card',
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        onEnd: function() {
            const ids = Array.from(grid.querySelectorAll('.banner-item')).map(el => el.dataset.id);
            const fd  = new FormData();
            fd.append('event_id', EVENT_ID);
            ids.forEach(id => fd.append('ids[]', id));
            postAjax(URL_REORDENAR, fd).then(data => {
                if (data.sucesso) {
                    grid.querySelectorAll('.banner-ordem').forEach((badge, i) => badge.textContent = i + 1);
                    alerta('Ordem atualizada.');
                } else {
                    alerta(data.erro || 'Erro ao reordenar.', 'danger');
                }
            });
        }
    });
}
</script>
<?php echo $this->endSection() ?>
