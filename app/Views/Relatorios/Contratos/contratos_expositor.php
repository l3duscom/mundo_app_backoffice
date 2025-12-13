<?= $this->extend('Layout/principal') ?>

<?= $this->section('titulo') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('estilos') ?>
<style>
    .stats-card {
        border: none;
        border-radius: 16px;
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 25px;
    }
    
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    
    .table-report thead {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }
    
    .table-report thead th {
        border: none;
        padding: 15px;
        font-weight: 600;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('conteudo') ?>

<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= site_url('relatorios/contratos') ?>">Relatórios de Contratos</a></li>
        <li class="breadcrumb-item active">Por Expositor</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bx bx-store-alt me-2"></i><?= $titulo ?></h4>
    <div>
        <a href="<?= site_url('relatorios/contratos/exportar-excel/expositor?evento_id=' . $event_id) ?>" class="btn btn-success me-2">
            <i class="bx bx-file me-1"></i>Exportar Excel
        </a>
        <a href="<?= site_url('relatorios/contratos/exportar-pdf/expositor?evento_id=' . $event_id) ?>" target="_blank" class="btn btn-danger">
            <i class="bx bx-file-blank me-1"></i>Exportar PDF
        </a>
    </div>
</div>

<!-- Filtro -->
<div class="filter-card">
    <form action="<?= site_url('relatorios/contratos/expositor') ?>" method="get" class="row g-3 align-items-end">
        <div class="col-md-9">
            <label class="form-label">Evento</label>
            <select name="evento_id" class="form-select">
                <?php foreach ($eventos as $evt): ?>
                    <option value="<?= $evt->id ?>" <?= ($event_id == $evt->id) ? 'selected' : '' ?>>
                        <?= esc($evt->nome) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bx bx-filter-alt me-1"></i>Filtrar
            </button>
        </div>
    </form>
</div>

<!-- Cards de Estatísticas -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <p class="mb-1 opacity-75">Total de Expositores</p>
            <h3><?= count($contratos_por_expositor) ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <p class="mb-1 opacity-75">Total de Contratos</p>
            <h3><?= $totais['quantidade'] ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
            <p class="mb-1 opacity-75">Valor Pago</p>
            <h3><?= $totais['valor_pago_formatado'] ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card" style="background: linear-gradient(135deg, #FF6B6B 0%, #ee5a24 100%);">
            <p class="mb-1 opacity-75">Em Aberto</p>
            <h3><?= $totais['valor_em_aberto_formatado'] ?></h3>
        </div>
    </div>
</div>

<!-- Tabela -->
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-report table-hover mb-0">
                <thead>
                    <tr>
                        <th>Expositor</th>
                        <th class="text-center">Contratos</th>
                        <th class="text-end">Valor Total</th>
                        <th class="text-end">Valor Pago</th>
                        <th class="text-end">Em Aberto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($contratos_por_expositor)): ?>
                        <?php foreach ($contratos_por_expositor as $item): ?>
                        <tr>
                            <td>
                                <i class="bx bx-store-alt me-2 text-danger"></i>
                                <strong><?= esc($item['expositor'] ?? 'Sem nome') ?></strong>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger"><?= $item['quantidade'] ?></span>
                            </td>
                            <td class="text-end fw-bold"><?= $item['valor_total_formatado'] ?></td>
                            <td class="text-end text-success"><?= $item['valor_pago_formatado'] ?></td>
                            <td class="text-end text-danger"><?= $item['valor_em_aberto_formatado'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bx bx-search-alt fs-1 mb-3 d-block"></i>
                                Nenhum registro encontrado.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
