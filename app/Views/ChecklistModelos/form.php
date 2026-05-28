<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">
        <?= $modelo ? 'Editar Modelo' : 'Novo Modelo' ?>
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?= site_url('/') ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?= site_url('checklist-modelos?event_id=' . (int) $eventoIdSelecionado) ?>">Modelos de Checklist</a></li>
                <li class="breadcrumb-item active"><?= $modelo ? 'Editar' : 'Novo' ?></li>
            </ol>
        </nav>
    </div>
</div>

<div id="mensagens"></div>

<form id="form-modelo">
    <input type="hidden" name="id" value="<?= $modelo->id ?? '' ?>">

    <div class="card shadow radius-10 mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Evento <span class="text-danger">*</span></label>
                    <select name="event_id" class="form-select" required>
                        <option value="">-- Selecione --</option>
                        <?php foreach ($eventos as $ev) : ?>
                            <option value="<?= $ev->id ?>" <?= (($modelo->event_id ?? $eventoIdSelecionado) == $ev->id) ? 'selected' : '' ?>>
                                <?= esc($ev->nome) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tipo <span class="text-danger">*</span></label>
                    <select name="tipo" class="form-select" required>
                        <option value="entrada" <?= (($modelo->tipo ?? 'entrada') === 'entrada') ? 'selected' : '' ?>>Entrada</option>
                        <option value="saida" <?= (($modelo->tipo ?? '') === 'saida') ? 'selected' : '' ?>>Saída</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nome (opcional)</label>
                    <input type="text" name="nome" class="form-control" placeholder="Ex: Entrada padrão expositor" value="<?= esc($modelo->nome ?? '') ?>">
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="ativo" id="ativo" <?= (!isset($modelo) || $modelo->ativo) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="ativo">Modelo ativo</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow radius-10 mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="bx bx-list-check me-1"></i>Itens do Checklist</h5>
                <button type="button" class="btn btn-sm btn-primary" id="btn-add-item">
                    <i class="bx bx-plus me-1"></i>Adicionar item
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="tabela-itens">
                    <thead class="table-light">
                        <tr>
                            <th style="width:35px;">#</th>
                            <th>Título <span class="text-danger">*</span></th>
                            <th style="width:100px;">Qtd.</th>
                            <th style="width:160px;">Tipo</th>
                            <th style="width:160px;">Categoria</th>
                            <th style="width:50px;"></th>
                        </tr>
                    </thead>
                    <tbody id="itens-body">
                        <?php if (!empty($itens)) : ?>
                            <?php foreach ($itens as $i => $it) : ?>
                                <tr class="item-row">
                                    <td class="text-center item-num"><?= $i + 1 ?></td>
                                    <td><input type="text" name="itens[<?= $i ?>][titulo]" class="form-control form-control-sm" value="<?= esc($it->titulo) ?>" required></td>
                                    <td><input type="number" name="itens[<?= $i ?>][quantidade]" class="form-control form-control-sm" min="1" value="<?= (int) $it->quantidade ?>"></td>
                                    <td><input type="text" name="itens[<?= $i ?>][tipo]" class="form-control form-control-sm" value="<?= esc($it->tipo) ?>" placeholder="livre"></td>
                                    <td><input type="text" name="itens[<?= $i ?>][categoria]" class="form-control form-control-sm" value="<?= esc($it->categoria) ?>" placeholder="livre"></td>
                                    <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-item"><i class="bx bx-trash"></i></button></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div id="aviso-vazio" class="alert alert-info <?= !empty($itens) ? 'd-none' : '' ?>">
                <i class="bx bx-info-circle me-1"></i>Adicione ao menos um item ao checklist.
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between">
        <a href="<?= site_url('checklist-modelos?event_id=' . (int) $eventoIdSelecionado) ?>" class="btn btn-secondary">
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
(function() {
    const CSRF_NAME = '<?= csrf_token() ?>';
    let CSRF_HASH = '<?= csrf_hash() ?>';

    const tbody = document.getElementById('itens-body');
    const aviso = document.getElementById('aviso-vazio');

    function alerta(msg, tipo) {
        const html = '<div class="alert alert-' + tipo + ' alert-dismissible fade show">'
            + msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        document.getElementById('mensagens').innerHTML = html;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function reindexar() {
        const rows = tbody.querySelectorAll('tr.item-row');
        rows.forEach((tr, i) => {
            tr.querySelector('.item-num').textContent = i + 1;
            tr.querySelectorAll('input').forEach(inp => {
                const name = inp.getAttribute('name');
                if (!name) return;
                inp.setAttribute('name', name.replace(/itens\[\d+\]/, 'itens[' + i + ']'));
            });
        });
        aviso.classList.toggle('d-none', rows.length > 0);
    }

    function adicionarLinha(dados) {
        dados = dados || {};
        const i = tbody.querySelectorAll('tr.item-row').length;
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML = ''
            + '<td class="text-center item-num">' + (i + 1) + '</td>'
            + '<td><input type="text" name="itens[' + i + '][titulo]" class="form-control form-control-sm" value="' + (dados.titulo || '') + '" required></td>'
            + '<td><input type="number" name="itens[' + i + '][quantidade]" class="form-control form-control-sm" min="1" value="' + (dados.quantidade || 1) + '"></td>'
            + '<td><input type="text" name="itens[' + i + '][tipo]" class="form-control form-control-sm" value="' + (dados.tipo || '') + '" placeholder="livre"></td>'
            + '<td><input type="text" name="itens[' + i + '][categoria]" class="form-control form-control-sm" value="' + (dados.categoria || '') + '" placeholder="livre"></td>'
            + '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-item"><i class="bx bx-trash"></i></button></td>';
        tbody.appendChild(tr);
        aviso.classList.add('d-none');
    }

    document.getElementById('btn-add-item').addEventListener('click', () => adicionarLinha());

    tbody.addEventListener('click', function(ev) {
        const btn = ev.target.closest('.btn-remove-item');
        if (!btn) return;
        btn.closest('tr.item-row').remove();
        reindexar();
    });

    // Garante pelo menos uma linha em modo criação
    if (tbody.querySelectorAll('tr.item-row').length === 0) {
        adicionarLinha();
    }

    document.getElementById('form-modelo').addEventListener('submit', async function(ev) {
        ev.preventDefault();

        const linhas = tbody.querySelectorAll('tr.item-row');
        if (linhas.length === 0) {
            alerta('Adicione ao menos um item ao checklist.', 'warning');
            return;
        }

        const fd = new FormData(this);
        fd.append(CSRF_NAME, CSRF_HASH);

        const btn = document.getElementById('btn-salvar');
        btn.disabled = true;

        try {
            const r = await fetch('<?= site_url('checklist-modelos/salvar') ?>', {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const j = await r.json();
            if (j.token) CSRF_HASH = j.token;
            if (j.erro) {
                alerta(j.erro, 'danger');
                btn.disabled = false;
                return;
            }
            window.location.href = j.redirect;
        } catch (e) {
            alerta('Erro ao salvar modelo.', 'danger');
            btn.disabled = false;
        }
    });
})();
</script>
<?php echo $this->endSection() ?>
