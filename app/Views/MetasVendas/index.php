<?php echo $this->extend('Layout/principal'); ?>
<?php echo $this->section('titulo') ?> <?= $titulo ?> <?php echo $this->endSection() ?>
<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Metas de Vendas</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?= site_url('/') ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active">Metas de Vendas</li>
            </ol>
        </nav>
    </div>
</div>

<div id="mensagens"></div>

<!-- Filtro de evento -->
<div class="card shadow radius-10 mb-3">
    <div class="card-body">
        <form method="get" action="<?= site_url('metas-vendas') ?>" class="row g-3 align-items-end">
            <div class="col-md-8">
                <label class="form-label mb-1"><i class="bx bx-calendar-event me-1"></i>Evento</label>
                <select name="event_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Selecione um evento --</option>
                    <?php foreach ($eventos as $ev) : ?>
                        <option value="<?= $ev->id ?>" <?= ($eventIdSelecionado == $ev->id) ? 'selected' : '' ?>>
                            <?= esc($ev->nome) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<?php if (empty($eventIdSelecionado)) : ?>
    <div class="alert alert-info"><i class="bx bx-info-circle me-1"></i>Selecione um evento para visualizar as metas.</div>
<?php else : ?>

<!-- Tabs Comercial / Ingressos -->
<ul class="nav nav-tabs mb-3" id="tabsMeta">
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tabComercial">
            <i class="bx bx-briefcase me-1"></i>Comercial
            <span class="badge bg-primary ms-1"><?= count($metasComercial) ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#tabIngressos">
            <i class="bx bx-ticket me-1"></i>Ingressos
            <span class="badge bg-success ms-1"><?= count($metasIngressos) ?></span>
        </a>
    </li>
</ul>

<div class="tab-content">
    <?php foreach ([['comercial', $metasComercial], ['ingressos', $metasIngressos]] as [$tipo, $metas]) : ?>
    <div class="tab-pane fade <?= $tipo === 'ingressos' ? 'show active' : '' ?>" id="tab<?= ucfirst($tipo) ?>">
        <div class="card shadow radius-10">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 text-muted"><?= $tipo === 'comercial' ? 'Metas Comerciais' : 'Metas de Ingressos' ?></h6>
                    <a href="<?= site_url('metas-vendas/criar?event_id=' . $eventIdSelecionado . '&tipo=' . $tipo) ?>" class="btn btn-sm btn-primary">
                        <i class="bx bx-plus me-1"></i>Nova Meta
                    </a>
                </div>
                <?php if (empty($metas)) : ?>
                    <div class="alert alert-warning mb-0">
                        <i class="bx bx-info-circle me-1"></i>Nenhuma meta cadastrada para este evento.
                    </div>
                <?php else : ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th class="text-end">Meta Total</th>
                                    <th class="text-center">Fases</th>
                                    <th class="text-center">Ativo</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($metas as $m) : ?>
                                <tr>
                                    <td><strong><?= esc($m->nome ?: '(sem nome)') ?></strong></td>
                                    <td class="text-end">R$ <?= number_format($m->meta_total, 2, ',', '.') ?></td>
                                    <td class="text-center"><span class="badge bg-secondary"><?= (int) $m->total_fases ?></span></td>
                                    <td class="text-center">
                                        <?php if ($m->ativo) : ?>
                                            <span class="badge bg-success">Ativo</span>
                                        <?php else : ?>
                                            <span class="badge bg-secondary">Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= site_url('metas-vendas/acompanhar/' . $m->id) ?>" class="btn btn-sm btn-outline-success" title="Acompanhar"><i class="bx bx-line-chart"></i></a>
                                        <a href="<?= site_url('metas-vendas/editar/' . $m->id) ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bx bx-edit"></i></a>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-excluir" data-id="<?= $m->id ?>" data-nome="<?= esc($m->nome ?: 'esta meta') ?>" title="Excluir"><i class="bx bx-trash"></i></button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script>
(function() {
    const CSRF_NAME = '<?= csrf_token() ?>';
    let CSRF_HASH = '<?= csrf_hash() ?>';

    function alerta(msg, tipo) {
        const html = '<div class="alert alert-' + tipo + ' alert-dismissible fade show">'
            + msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        document.getElementById('mensagens').innerHTML = html;
    }

    document.querySelectorAll('.btn-excluir').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.dataset.id;
            const nome = this.dataset.nome;
            if (!confirm('Excluir a meta "' + nome + '"?')) return;
            const fd = new FormData();
            fd.append('id', id);
            fd.append(CSRF_NAME, CSRF_HASH);
            try {
                const r = await fetch('<?= site_url('metas-vendas/excluir') ?>', {
                    method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const j = await r.json();
                if (j.token) CSRF_HASH = j.token;
                if (j.erro) { alerta(j.erro, 'danger'); return; }
                location.reload();
            } catch (e) { alerta('Erro ao excluir.', 'danger'); }
        });
    });
})();
</script>
<?php echo $this->endSection() ?>
