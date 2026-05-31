<?php echo $this->extend('Layout/principal'); ?>
<?php echo $this->section('titulo') ?> <?= $titulo ?> <?php echo $this->endSection() ?>
<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted"><?= $meta ? 'Editar Meta' : 'Nova Meta' ?></div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?= site_url('/') ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?= site_url('metas-vendas?event_id=' . (int) $eventIdSelecionado) ?>">Metas de Vendas</a></li>
                <li class="breadcrumb-item active"><?= $meta ? 'Editar' : 'Nova' ?></li>
            </ol>
        </nav>
    </div>
</div>

<div id="mensagens"></div>

<form id="form-meta">
    <input type="hidden" name="id" value="<?= $meta->id ?? '' ?>">

    <!-- Card 1: Cabeçalho -->
    <div class="card shadow radius-10 mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Evento <span class="text-danger">*</span></label>
                    <select name="event_id" class="form-select" required>
                        <option value="">-- Selecione --</option>
                        <?php foreach ($eventos as $ev) : ?>
                            <option value="<?= $ev->id ?>" <?= (($meta->event_id ?? $eventIdSelecionado) == $ev->id) ? 'selected' : '' ?>>
                                <?= esc($ev->nome) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tipo <span class="text-danger">*</span></label>
                    <div class="d-flex gap-3 mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipo" id="tipoIngressos" value="ingressos"
                                <?= (($meta->tipo ?? $tipoSelecionado) === 'ingressos') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="tipoIngressos">Ingressos</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipo" id="tipoComercial" value="comercial"
                                <?= (($meta->tipo ?? $tipoSelecionado) === 'comercial') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="tipoComercial">Comercial</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nome (opcional)</label>
                    <input type="text" name="nome" class="form-control" placeholder="Ex: Meta Dreamfest 25" value="<?= esc($meta->nome ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Meta Total <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="text" name="meta_total" id="meta_total" class="form-control money" required placeholder="0,00"
                            value="<?= isset($meta->meta_total) ? number_format($meta->meta_total, 2, ',', '.') : '' ?>">
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check form-switch mb-1">
                        <input class="form-check-input" type="checkbox" name="ativo" id="ativo" <?= (!isset($meta) || ($meta->ativo ?? true)) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="ativo">Meta ativa</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Fases -->
    <div class="card shadow radius-10 mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="bx bx-calendar-alt me-1"></i>Fases</h5>
                <button type="button" class="btn btn-sm btn-primary" id="btn-add-fase">
                    <i class="bx bx-plus me-1"></i>Adicionar fase
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="tabela-fases">
                    <thead class="table-light">
                        <tr>
                            <th style="width:130px;">Nome</th>
                            <th style="width:135px;">Data Início</th>
                            <th style="width:135px;">Data Fim</th>
                            <th style="width:150px;">Meta da Fase</th>
                            <th style="width:130px;" class="text-center">Média/dia</th>
                            <th style="width:130px;" class="text-center">Média/semana</th>
                            <th style="width:40px;"></th>
                        </tr>
                    </thead>
                    <tbody id="fases-body">
                        <?php foreach ($fases as $i => $f) : ?>
                        <tr class="fase-row">
                            <td><input type="text" name="fases[<?= $i ?>][nome]" class="form-control form-control-sm" placeholder="Fase <?= $i+1 ?>" value="<?= esc($f->nome ?? '') ?>"></td>
                            <td><input type="date" name="fases[<?= $i ?>][data_inicio]" class="form-control form-control-sm fase-inicio" value="<?= esc($f->data_inicio ?? '') ?>"></td>
                            <td><input type="date" name="fases[<?= $i ?>][data_fim]" class="form-control form-control-sm fase-fim" value="<?= esc($f->data_fim ?? '') ?>"></td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">R$</span>
                                    <input type="text" name="fases[<?= $i ?>][meta_fase]" class="form-control money fase-meta" value="<?= number_format($f->meta_fase, 2, ',', '.') ?>">
                                </div>
                            </td>
                            <td class="text-center fase-media-dia text-muted">
                                <?= $f->data_inicio && $f->data_fim ? 'R$ ' . number_format($f->getMediaDia(), 2, ',', '.') : '—' ?>
                            </td>
                            <td class="text-center fase-media-semana text-muted">
                                <?= $f->data_inicio && $f->data_fim ? 'R$ ' . number_format($f->getMediaSemana(), 2, ',', '.') : '—' ?>
                            </td>
                            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-fase"><i class="bx bx-trash"></i></button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div id="aviso-fases" class="alert alert-info <?= !empty($fases) ? 'd-none' : '' ?>">
                <i class="bx bx-info-circle me-1"></i>Adicione ao menos uma fase.
            </div>
        </div>
    </div>

    <!-- Card 3: Marcos -->
    <div class="card shadow radius-10 mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="bx bx-flag me-1"></i>Marcos de Acompanhamento</h5>
                <button type="button" class="btn btn-sm btn-primary" id="btn-add-marco">
                    <i class="bx bx-plus me-1"></i>Adicionar marco
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="tabela-marcos">
                    <thead class="table-light">
                        <tr>
                            <th style="width:160px;">Data</th>
                            <th>Faturamento Acumulado Esperado</th>
                            <th style="width:40px;"></th>
                        </tr>
                    </thead>
                    <tbody id="marcos-body">
                        <?php foreach ($marcos as $i => $m) : ?>
                        <tr class="marco-row">
                            <td><input type="date" name="marcos[<?= $i ?>][data]" class="form-control form-control-sm" value="<?= esc($m->data) ?>"></td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">R$</span>
                                    <input type="text" name="marcos[<?= $i ?>][faturamento_acumulado_esperado]" class="form-control money" value="<?= number_format($m->faturamento_acumulado_esperado, 2, ',', '.') ?>">
                                </div>
                            </td>
                            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-marco"><i class="bx bx-trash"></i></button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div id="aviso-marcos" class="alert alert-info <?= !empty($marcos) ? 'd-none' : '' ?>">
                <i class="bx bx-info-circle me-1"></i>Opcional — adicione marcos de faturamento acumulado esperado.
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between">
        <a href="<?= site_url('metas-vendas?event_id=' . (int) $eventIdSelecionado) ?>" class="btn btn-secondary">
            <i class="bx bx-arrow-back me-1"></i>Voltar
        </a>
        <button type="submit" class="btn btn-success" id="btn-salvar">
            <i class="bx bx-save me-1"></i>Salvar
        </button>
    </div>
</form>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script>
(function () {
    const CSRF_NAME = '<?= csrf_token() ?>';
    let CSRF_HASH = '<?= csrf_hash() ?>';

    function alerta(msg, tipo) {
        const html = '<div class="alert alert-' + tipo + ' alert-dismissible fade show">'
            + msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        document.getElementById('mensagens').innerHTML = html;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function fmtBrl(val) {
        return 'R$ ' + val.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function parseMoney(str) {
        return parseFloat(String(str).replace(/\./g, '').replace(',', '.')) || 0;
    }

    // ---- Máscara monetária simples ----
    function applyMoney(el) {
        el.addEventListener('input', function () {
            let v = this.value.replace(/\D/g, '');
            if (!v) { this.value = ''; return; }
            v = (parseInt(v) / 100).toFixed(2);
            this.value = parseFloat(v).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
        });
    }
    document.querySelectorAll('.money').forEach(applyMoney);

    // ---- Cálculo de médias das fases ----
    function calcMedias(row) {
        const ini  = row.querySelector('.fase-inicio')?.value;
        const fim  = row.querySelector('.fase-fim')?.value;
        const meta = parseMoney(row.querySelector('.fase-meta')?.value);
        const diaEl  = row.querySelector('.fase-media-dia');
        const semEl  = row.querySelector('.fase-media-semana');
        if (!diaEl || !semEl) return;
        if (!ini || !fim || !meta) { diaEl.textContent = '—'; semEl.textContent = '—'; return; }
        const dias = Math.round((new Date(fim) - new Date(ini)) / 86400000) + 1;
        if (dias <= 0) { diaEl.textContent = '—'; semEl.textContent = '—'; return; }
        diaEl.textContent  = fmtBrl(meta / dias);
        semEl.textContent  = fmtBrl((meta / dias) * 7);
    }

    function bindFaseRow(row) {
        row.querySelectorAll('.fase-inicio, .fase-fim, .fase-meta').forEach(el => {
            el.addEventListener('change', () => calcMedias(row));
            el.addEventListener('input', () => calcMedias(row));
        });
        row.querySelector('.btn-remove-fase')?.addEventListener('click', function () {
            row.remove(); reindexarFases();
        });
        calcMedias(row);
    }

    document.querySelectorAll('.fase-row').forEach(bindFaseRow);

    function reindexarFases() {
        const aviso = document.getElementById('aviso-fases');
        const rows  = document.querySelectorAll('.fase-row');
        rows.forEach((tr, i) => {
            tr.querySelectorAll('[name]').forEach(inp => {
                inp.name = inp.name.replace(/fases\[\d+\]/, 'fases[' + i + ']');
            });
        });
        aviso.classList.toggle('d-none', rows.length > 0);
    }

    document.getElementById('btn-add-fase').addEventListener('click', function () {
        const i = document.querySelectorAll('.fase-row').length;
        const tr = document.createElement('tr');
        tr.className = 'fase-row';
        tr.innerHTML = `
            <td><input type="text" name="fases[${i}][nome]" class="form-control form-control-sm" placeholder="Fase ${i+1}"></td>
            <td><input type="date" name="fases[${i}][data_inicio]" class="form-control form-control-sm fase-inicio"></td>
            <td><input type="date" name="fases[${i}][data_fim]" class="form-control form-control-sm fase-fim"></td>
            <td><div class="input-group input-group-sm"><span class="input-group-text">R$</span>
                <input type="text" name="fases[${i}][meta_fase]" class="form-control money fase-meta"></div></td>
            <td class="text-center fase-media-dia text-muted">—</td>
            <td class="text-center fase-media-semana text-muted">—</td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-fase"><i class="bx bx-trash"></i></button></td>`;
        document.getElementById('fases-body').appendChild(tr);
        applyMoney(tr.querySelector('.money'));
        bindFaseRow(tr);
        document.getElementById('aviso-fases').classList.add('d-none');
    });

    // ---- Marcos ----
    function bindMarcoRow(row) {
        row.querySelector('.btn-remove-marco')?.addEventListener('click', function () {
            row.remove(); reindexarMarcos();
        });
    }
    document.querySelectorAll('.marco-row').forEach(bindMarcoRow);

    function reindexarMarcos() {
        const aviso = document.getElementById('aviso-marcos');
        const rows  = document.querySelectorAll('.marco-row');
        rows.forEach((tr, i) => {
            tr.querySelectorAll('[name]').forEach(inp => {
                inp.name = inp.name.replace(/marcos\[\d+\]/, 'marcos[' + i + ']');
            });
        });
        aviso.classList.toggle('d-none', rows.length > 0);
    }

    document.getElementById('btn-add-marco').addEventListener('click', function () {
        const i = document.querySelectorAll('.marco-row').length;
        const tr = document.createElement('tr');
        tr.className = 'marco-row';
        tr.innerHTML = `
            <td><input type="date" name="marcos[${i}][data]" class="form-control form-control-sm"></td>
            <td><div class="input-group input-group-sm"><span class="input-group-text">R$</span>
                <input type="text" name="marcos[${i}][faturamento_acumulado_esperado]" class="form-control money"></div></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-marco"><i class="bx bx-trash"></i></button></td>`;
        document.getElementById('marcos-body').appendChild(tr);
        applyMoney(tr.querySelector('.money'));
        bindMarcoRow(tr);
        document.getElementById('aviso-marcos').classList.add('d-none');
    });

    // ---- Submit ----
    document.getElementById('form-meta').addEventListener('submit', async function (ev) {
        ev.preventDefault();
        if (!document.querySelectorAll('.fase-row').length) {
            alerta('Adicione ao menos uma fase.', 'warning'); return;
        }
        const btn = document.getElementById('btn-salvar');
        btn.disabled = true;
        const fd = new FormData(this);
        fd.append(CSRF_NAME, CSRF_HASH);
        try {
            const r = await fetch('<?= site_url('metas-vendas/salvar') ?>', {
                method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const j = await r.json();
            if (j.token) CSRF_HASH = j.token;
            if (j.erro) { alerta(j.erro, 'danger'); btn.disabled = false; return; }
            window.location.href = j.redirect;
        } catch (e) { alerta('Erro ao salvar.', 'danger'); btn.disabled = false; }
    });
})();
</script>
<?php echo $this->endSection() ?>
