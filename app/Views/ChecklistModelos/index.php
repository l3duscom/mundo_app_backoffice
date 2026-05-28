<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Modelos de Checklist</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?= site_url('/') ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active">Modelos de Checklist</li>
            </ol>
        </nav>
    </div>
</div>

<div id="mensagens"></div>

<div class="card shadow radius-10 mb-3">
    <div class="card-body">
        <form method="get" action="<?= site_url('checklist-modelos') ?>" class="row g-3 align-items-end">
            <div class="col-md-8">
                <label class="form-label mb-1"><i class="bx bx-calendar-event me-1"></i>Evento</label>
                <select name="event_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Selecione um evento --</option>
                    <?php foreach ($eventos as $ev) : ?>
                        <option value="<?= $ev->id ?>" <?= ($eventoIdSelecionado == $ev->id) ? 'selected' : '' ?>>
                            <?= esc($ev->nome) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 text-end">
                <?php if (!empty($eventoIdSelecionado)) : ?>
                    <a href="<?= site_url('checklist-modelos/criar?event_id=' . (int) $eventoIdSelecionado) ?>" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i>Novo Modelo
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php if (empty($eventoIdSelecionado)) : ?>
    <div class="alert alert-info">
        <i class="bx bx-info-circle me-1"></i>Selecione um evento para visualizar seus modelos de checklist.
    </div>
<?php elseif (empty($modelos)) : ?>
    <div class="alert alert-warning">
        <i class="bx bx-info-circle me-1"></i>Nenhum modelo de checklist cadastrado para este evento.
    </div>
<?php else : ?>
    <div class="card shadow radius-10">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Tipo</th>
                            <th class="text-center">Itens</th>
                            <th class="text-center">Ativo</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($modelos as $m) : ?>
                            <tr>
                                <td>
                                    <strong><?= esc($m->nome ?: '(sem nome)') ?></strong>
                                </td>
                                <td><?= $m->getBadgeTipo() ?></td>
                                <td class="text-center"><span class="badge bg-secondary"><?= (int) $m->total_itens ?></span></td>
                                <td class="text-center">
                                    <?php if ($m->ativo) : ?>
                                        <span class="badge bg-success">Ativo</span>
                                    <?php else : ?>
                                        <span class="badge bg-secondary">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= site_url('checklist-modelos/editar/' . $m->id) ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bx bx-edit"></i></a>
                                    <button type="button" class="btn btn-sm btn-outline-info btn-copiar" data-id="<?= $m->id ?>" data-nome="<?= esc($m->nome ?: 'modelo') ?>" title="Copiar para outro evento"><i class="bx bx-copy"></i></button>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-excluir" data-id="<?= $m->id ?>" data-nome="<?= esc($m->nome ?: 'modelo') ?>" title="Excluir"><i class="bx bx-trash"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Modal Copiar -->
<div class="modal fade" id="modalCopiar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Copiar modelo para outro evento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Copiando o modelo <strong id="copiar-nome"></strong>.</p>
                <input type="hidden" id="copiar-modelo-id">
                <div class="mb-3">
                    <label class="form-label">Evento destino</label>
                    <select id="copiar-evento" class="form-select">
                        <option value="">-- Selecione --</option>
                        <?php foreach ($eventos as $ev) : ?>
                            <option value="<?= $ev->id ?>"><?= esc($ev->nome) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-copiar-confirmar">
                    <i class="bx bx-copy me-1"></i>Copiar
                </button>
            </div>
        </div>
    </div>
</div>

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

    // Excluir
    document.querySelectorAll('.btn-excluir').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.dataset.id;
            const nome = this.dataset.nome;
            if (!confirm('Excluir o modelo "' + nome + '" e todos os seus itens?')) return;

            const fd = new FormData();
            fd.append('id', id);
            fd.append(CSRF_NAME, CSRF_HASH);

            try {
                const r = await fetch('<?= site_url('checklist-modelos/excluir') ?>', {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const j = await r.json();
                if (j.token) CSRF_HASH = j.token;
                if (j.erro) { alerta(j.erro, 'danger'); return; }
                location.reload();
            } catch (e) { alerta('Erro ao excluir.', 'danger'); }
        });
    });

    // Copiar
    const modalCopiar = new bootstrap.Modal(document.getElementById('modalCopiar'));
    document.querySelectorAll('.btn-copiar').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('copiar-modelo-id').value = this.dataset.id;
            document.getElementById('copiar-nome').textContent = this.dataset.nome;
            document.getElementById('copiar-evento').value = '';
            modalCopiar.show();
        });
    });

    document.getElementById('btn-copiar-confirmar').addEventListener('click', async function() {
        const modeloId = document.getElementById('copiar-modelo-id').value;
        const eventoDestino = document.getElementById('copiar-evento').value;
        if (!eventoDestino) { alert('Selecione o evento destino.'); return; }

        const fd = new FormData();
        fd.append('modelo_id', modeloId);
        fd.append('event_id_destino', eventoDestino);
        fd.append(CSRF_NAME, CSRF_HASH);

        this.disabled = true;
        try {
            const r = await fetch('<?= site_url('checklist-modelos/copiar') ?>', {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const j = await r.json();
            if (j.token) CSRF_HASH = j.token;
            if (j.erro) { alerta(j.erro, 'danger'); this.disabled = false; return; }
            window.location.href = j.redirect;
        } catch (e) {
            alerta('Erro ao copiar.', 'danger');
            this.disabled = false;
        }
    });
})();
</script>
<?php echo $this->endSection() ?>
