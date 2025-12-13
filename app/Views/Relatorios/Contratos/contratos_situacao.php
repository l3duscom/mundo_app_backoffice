<?= $this->extend('Layout/principal') ?>

<?= $this->section('titulo') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('estilos') ?>
<style>
    .stats-card {
        border: none;
        border-radius: 16px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 25px;
    }
    
    .stats-card h3 {
        font-size: 1.5rem;
        margin: 0;
    }
    
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    
    .table-report {
        border-collapse: collapse;
        width: 100%;
    }
    
    .table-report thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .table-report thead th {
        border: none;
        padding: 15px 12px;
        font-weight: 600;
        font-size: 13px;
        white-space: nowrap;
        color: white !important;
    }
    
    .table-report tbody td {
        padding: 15px 12px;
        vertical-align: middle;
        border-bottom: 1px solid #e9ecef;
        color: #333 !important;
    }
    
    .table-report tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }
    
    .table-report tbody tr:hover {
        background-color: #e9ecef;
    }
    
    .badge-situacao {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 13px;
        background: #667eea;
        color: white !important;
    }
    
    .text-success-dark {
        color: #198754 !important;
    }
    
    .text-danger-dark {
        color: #dc3545 !important;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('conteudo') ?>

<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= site_url('relatorios/contratos') ?>">Relatórios de Contratos</a></li>
        <li class="breadcrumb-item active">Por Situação</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bx bx-list-check me-2"></i><?= $titulo ?></h4>
    <div>
        <a href="<?= site_url('relatorios/contratos/exportar-excel/situacao?evento_id=' . $event_id . '&situacao=' . $situacao) ?>" class="btn btn-success me-2">
            <i class="bx bx-file me-1"></i>Exportar Excel
        </a>
        <a href="<?= site_url('relatorios/contratos/exportar-pdf/situacao?evento_id=' . $event_id . '&situacao=' . $situacao) ?>" target="_blank" class="btn btn-danger">
            <i class="bx bx-file-blank me-1"></i>Exportar PDF
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="filter-card">
    <form action="<?= site_url('relatorios/contratos/situacao') ?>" method="get" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label">Evento</label>
            <select name="evento_id" class="form-select">
                <?php foreach ($eventos as $evt): ?>
                    <option value="<?= $evt->id ?>" <?= ($event_id == $evt->id) ? 'selected' : '' ?>>
                        <?= esc($evt->nome) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Situação</label>
            <select name="situacao" class="form-select">
                <option value="">Todas</option>
                <?php foreach ($situacoes as $key => $label): ?>
                    <option value="<?= $key ?>" <?= ($situacao == $key) ? 'selected' : '' ?>>
                        <?= esc($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
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
            <p class="mb-1 opacity-75">Total de Contratos</p>
            <h3><?= $totais['quantidade'] ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
            <p class="mb-1 opacity-75">Valor Total</p>
            <h3><?= $totais['valor_total_formatado'] ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card" style="background: linear-gradient(135deg, #4CAF50 0%, #8BC34A 100%);">
            <p class="mb-1 opacity-75">Valor Pago (Bruto)</p>
            <h3><?= $totais['valor_pago_formatado'] ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <p class="mb-1 opacity-75">Em Aberto</p>
            <h3><?= $totais['valor_em_aberto_formatado'] ?></h3>
        </div>
    </div>
</div>

<!-- Card de Valor Líquido -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-dark text-white">
        <h6 class="mb-0"><i class="bx bx-money me-2"></i>Valores Líquidos (após taxas)</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="border rounded p-3 text-center bg-success text-white">
                    <p class="opacity-75 mb-1 small">Valor Pago Líquido</p>
                    <h4 class="mb-0"><?= $totais['valor_pago_liquido_formatado'] ?? 'R$ 0,00' ?></h4>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded p-3 text-center">
                    <p class="text-muted mb-1 small">Em Aberto Líquido</p>
                    <h4 class="text-warning mb-0"><?= $totais['valor_aberto_liquido_formatado'] ?? 'R$ 0,00' ?></h4>
                </div>
            </div>
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
                        <th>Situação</th>
                        <th class="text-center">Qtd</th>
                        <th class="text-end">Valor Total</th>
                        <th class="text-end">Pago (Bruto)</th>
                        <th class="text-end">Pago (Líq.)</th>
                        <th class="text-end">Em Aberto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($contratos_por_situacao)): ?>
                        <?php foreach ($contratos_por_situacao as $item): ?>
                        <tr>
                            <td>
                                <span class="badge-situacao bg-primary bg-opacity-10 text-primary">
                                    <?= esc($item['situacao_label']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary"><?= $item['quantidade'] ?></span>
                            </td>
                            <td class="text-end fw-bold"><?= $item['valor_total_formatado'] ?></td>
                            <td class="text-end text-success"><?= $item['valor_pago_formatado'] ?></td>
                            <td class="text-end text-success fw-bold"><?= $item['valor_pago_liquido_formatado'] ?? 'R$ 0,00' ?></td>
                            <td class="text-end text-danger"><?= $item['valor_em_aberto_formatado'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
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
