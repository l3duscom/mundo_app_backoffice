<?php echo $this->extend('Layout/principal'); ?>
<?php echo $this->section('titulo') ?> <?= $titulo ?> <?php echo $this->endSection() ?>
<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Metas de Vendas</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?= site_url('/') ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?= site_url('metas-vendas?event_id=' . ($meta->event_id ?? '')) ?>">Metas de Vendas</a></li>
                <li class="breadcrumb-item active">Acompanhamento</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="<?= site_url('metas-vendas/editar/' . $meta->id) ?>" class="btn btn-sm btn-outline-primary me-2">
            <i class="bx bx-edit me-1"></i>Editar Meta
        </a>
        <a href="<?= site_url('metas-vendas?event_id=' . ($meta->event_id ?? '')) ?>" class="btn btn-sm btn-secondary">
            <i class="bx bx-arrow-back me-1"></i>Voltar
        </a>
    </div>
</div>

<!-- Título da meta -->
<div class="card shadow-sm mb-3" style="background: linear-gradient(135deg,#6C038F,#9b3bc9); color:#fff;">
    <div class="card-body py-3">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0"><?= esc($meta->nome ?: $meta->getTipoLabel()) ?></h5>
                <small class="opacity-75"><?= esc($evento->nome ?? '') ?> &mdash; <?= $meta->getBadgeTipo() ?></small>
            </div>
            <div class="col-auto text-end">
                <div style="font-size:1.6rem;font-weight:700;" id="pct-atingido">—%</div>
                <small class="opacity-75">da meta atingido</small>
            </div>
        </div>
    </div>
</div>

<!-- Cards de indicadores -->
<div class="row g-3 mb-3" id="cards-indicadores">
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small mb-1">Meta Total</div>
                <div class="fs-5 fw-bold text-primary" id="ind-meta-total">—</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small mb-1">Realizado</div>
                <div class="fs-5 fw-bold text-success" id="ind-realizado">—</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small mb-1">Fase Atual</div>
                <div class="fs-6 fw-bold" id="ind-fase-atual">—</div>
                <small class="text-muted" id="ind-fase-periodo"></small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small mb-1">Média Diária Necessária</div>
                <div class="fs-5 fw-bold text-warning" id="ind-media-nec">—</div>
                <small class="text-muted">para bater a meta</small>
            </div>
        </div>
    </div>
</div>

<!-- Gráfico -->
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <h6 class="mb-3"><i class="bx bx-line-chart me-1"></i>Evolução — Realizado vs. Planejado</h6>
        <div id="grafico-loading" class="text-center py-4"><div class="spinner-border text-primary"></div></div>
        <canvas id="grafico-meta" height="100" style="display:none;"></canvas>
    </div>
</div>

<!-- Fases -->
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <h6 class="mb-3"><i class="bx bx-calendar-alt me-1"></i>Fases</h6>
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="tabela-fases">
                <thead class="table-light">
                    <tr>
                        <th>Período</th>
                        <th class="text-end">Meta da Fase</th>
                        <th class="text-end">Média/dia</th>
                        <th class="text-end">Média/semana</th>
                        <th class="text-end">Realizado</th>
                        <th class="text-center">%</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody id="tbody-fases"><tr><td colspan="7" class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></td></tr></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Marcos -->
<div class="card shadow-sm mb-3" id="card-marcos" style="display:none;">
    <div class="card-body">
        <h6 class="mb-3"><i class="bx bx-flag me-1"></i>Marcos de Acompanhamento</h6>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Data</th>
                        <th class="text-end">Esperado Acumulado</th>
                        <th class="text-end">Realizado Acumulado</th>
                        <th class="text-end">Diferença</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody id="tbody-marcos"></tbody>
            </table>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
(function () {
    const URL_DADOS = '<?= site_url('metas-vendas/dados-acompanhamento/' . $meta->id) ?>';

    function brl(val) {
        return 'R$ ' + Number(val).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
    }

    function badgeStatus(s) {
        const map = {
            futuro:    ['bg-secondary', 'Futuro'],
            atual:     ['bg-primary',   'Atual'],
            encerrado: ['bg-light text-dark border', 'Encerrado'],
            ok:        ['bg-success',   'Atingido'],
            atrasado:  ['bg-danger',    'Abaixo'],
        };
        const [cls, lbl] = map[s] || ['bg-secondary', s];
        return `<span class="badge ${cls}">${lbl}</span>`;
    }

    fetch(URL_DADOS, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(j => {
            // Indicadores
            document.getElementById('pct-atingido').textContent = j.percentual + '%';
            document.getElementById('ind-meta-total').textContent = brl(j.meta_total);
            document.getElementById('ind-realizado').textContent = brl(j.realizado_total);
            document.getElementById('ind-media-nec').textContent = brl(j.media_necessaria);
            if (j.fase_atual) {
                document.getElementById('ind-fase-atual').textContent = j.fase_atual.nome || j.fase_atual.periodo;
                document.getElementById('ind-fase-periodo').textContent = j.fase_atual.periodo;
            }

            // Tabela de fases
            const tbFases = document.getElementById('tbody-fases');
            tbFases.innerHTML = '';
            (j.fases || []).forEach(f => {
                const pct = f.percentual;
                const barColor = pct >= 100 ? '#198754' : (pct >= 60 ? '#0d6efd' : '#ffc107');
                tbFases.insertAdjacentHTML('beforeend', `
                    <tr>
                        <td>${f.periodo}</td>
                        <td class="text-end">${brl(f.meta_fase)}</td>
                        <td class="text-end text-muted">${brl(f.media_dia)}</td>
                        <td class="text-end text-muted">${brl(f.media_semana)}</td>
                        <td class="text-end fw-bold">${brl(f.realizado)}</td>
                        <td class="text-center" style="min-width:90px;">
                            <div class="progress" style="height:6px;"><div class="progress-bar" style="width:${Math.min(100,pct)}%;background:${barColor};"></div></div>
                            <small>${pct}%</small>
                        </td>
                        <td class="text-center">${badgeStatus(f.status)}</td>
                    </tr>`);
            });

            // Tabela de marcos
            if (j.marcos && j.marcos.length) {
                document.getElementById('card-marcos').style.display = '';
                const tbMarcos = document.getElementById('tbody-marcos');
                tbMarcos.innerHTML = '';
                j.marcos.forEach(m => {
                    const difClass = m.diferenca >= 0 ? 'text-success' : 'text-danger';
                    const difSinal = m.diferenca >= 0 ? '+' : '';
                    tbMarcos.insertAdjacentHTML('beforeend', `
                        <tr>
                            <td>${m.data}</td>
                            <td class="text-end">${brl(m.esperado)}</td>
                            <td class="text-end">${brl(m.realizado)}</td>
                            <td class="text-end ${difClass}">${difSinal}${brl(m.diferenca)}</td>
                            <td class="text-center">${badgeStatus(m.status)}</td>
                        </tr>`);
                });
            }

            // Gráfico
            document.getElementById('grafico-loading').style.display = 'none';
            const canvas = document.getElementById('grafico-meta');
            canvas.style.display = '';
            new Chart(canvas, {
                type: 'line',
                data: {
                    labels: j.grafico.labels,
                    datasets: [
                        {
                            label: 'Realizado acumulado',
                            data: j.grafico.realizado,
                            borderColor: '#0d6efd',
                            backgroundColor: 'rgba(13,110,253,.08)',
                            fill: true,
                            tension: .3,
                            pointRadius: 0,
                        },
                        {
                            label: 'Planejado acumulado',
                            data: j.grafico.planejado,
                            borderColor: '#198754',
                            borderDash: [6, 4],
                            backgroundColor: 'transparent',
                            tension: .3,
                            pointRadius: 0,
                        },
                    ]
                },
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { position: 'top' } },
                    scales: {
                        y: {
                            ticks: {
                                callback: v => 'R$ ' + Number(v).toLocaleString('pt-BR', { notation: 'compact' })
                            }
                        },
                        x: { ticks: { maxTicksLimit: 15 } }
                    }
                }
            });
        })
        .catch(() => {
            document.getElementById('grafico-loading').innerHTML = '<span class="text-danger">Erro ao carregar dados.</span>';
        });
})();
</script>
<?php echo $this->endSection() ?>
