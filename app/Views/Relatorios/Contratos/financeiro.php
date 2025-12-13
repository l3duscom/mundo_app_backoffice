<?= $this->extend('Layout/principal') ?>

<?= $this->section('titulo') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('estilos') ?>
<style>
    .stats-card {
        border: none;
        border-radius: 16px;
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
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
    
    .finance-card {
        border: none;
        border-radius: 16px;
        padding: 25px;
        text-align: center;
    }
    
    .finance-card h2 {
        font-size: 2rem;
        font-weight: 700;
    }
    
    .progress-custom {
        height: 30px;
        border-radius: 15px;
        overflow: hidden;
        background: #e9ecef;
    }
    
    .progress-bar-custom {
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('conteudo') ?>

<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= site_url('relatorios/contratos') ?>">Relatórios de Contratos</a></li>
        <li class="breadcrumb-item active">Financeiro</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bx bx-dollar-circle me-2"></i><?= $titulo ?></h4>
    <a href="<?= site_url('relatorios/contratos/exportar-excel/financeiro?evento_id=' . $event_id) ?>" class="btn btn-success">
        <i class="bx bx-file me-1"></i>Exportar Excel
    </a>
</div>

<!-- Filtro -->
<div class="filter-card">
    <form action="<?= site_url('relatorios/contratos/financeiro') ?>" method="get" class="row g-3 align-items-end">
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

<!-- Cards de Resumo -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="finance-card bg-light">
            <p class="text-muted mb-1">Total de Contratos</p>
            <h2 class="text-primary"><?= $resumo['total_contratos'] ?></h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="finance-card bg-light">
            <p class="text-muted mb-1">Valor Original</p>
            <h2 class="text-secondary text-decoration-line-through"><?= $resumo['valor_original_formatado'] ?></h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="finance-card bg-light">
            <p class="text-muted mb-1">Descontos</p>
            <h2 class="text-danger">-<?= $resumo['valor_desconto_formatado'] ?></h2>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="finance-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <p class="opacity-75 mb-1">Valor Final</p>
            <h2><?= $resumo['valor_final_formatado'] ?></h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="finance-card" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white;">
            <p class="opacity-75 mb-1">Valor Pago (Bruto)</p>
            <h2><?= $resumo['valor_pago_formatado'] ?></h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="finance-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
            <p class="opacity-75 mb-1">Em Aberto</p>
            <h2><?= $resumo['valor_em_aberto_formatado'] ?></h2>
        </div>
    </div>
</div>

<!-- Valores Líquidos -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-dark text-white">
        <h6 class="mb-0"><i class="bx bx-money me-2"></i>Valores Líquidos (após taxas)</h6>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="border rounded p-3 text-center">
                    <p class="text-muted mb-1 small">Taxa Total Asaas</p>
                    <h4 class="text-danger mb-0">-<?= $resumo['taxa_total_formatado'] ?? 'R$ 0,00' ?></h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 text-center bg-success text-white">
                    <p class="opacity-75 mb-1 small">Valor Pago Líquido</p>
                    <h4 class="mb-0"><?= $resumo['valor_pago_liquido_formatado'] ?? 'R$ 0,00' ?></h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 text-center">
                    <p class="text-muted mb-1 small">Em Aberto Líquido</p>
                    <h4 class="text-warning mb-0"><?= $resumo['valor_aberto_liquido_formatado'] ?? 'R$ 0,00' ?></h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Progresso -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h6 class="mb-0">Progresso de Recebimento</h6>
    </div>
    <div class="card-body">
        <div class="progress-custom">
            <div class="progress-bar-custom bg-success" style="width: <?= min($resumo['percentual_pago'], 100) ?>%;">
                <?= $resumo['percentual_pago'] ?>% Pago
            </div>
        </div>
        <div class="d-flex justify-content-between mt-3">
            <div>
                <span class="text-success fw-bold">Pago: <?= $resumo['valor_pago_formatado'] ?></span>
                <span class="text-muted ms-2">(Líq: <?= $resumo['valor_pago_liquido_formatado'] ?? 'R$ 0,00' ?>)</span>
            </div>
            <span class="text-danger fw-bold">Restante: <?= $resumo['valor_em_aberto_formatado'] ?></span>
        </div>
    </div>
</div>

<!-- Recebimentos Mensais -->
<?php if (!empty($recebimentos_mensais)): ?>
<div class="card shadow-sm">
    <div class="card-header">
        <h6 class="mb-0"><i class="bx bx-calendar me-2"></i>Recebimentos Mensais</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Mês</th>
                        <th class="text-center">Pagamentos</th>
                        <th class="text-end">Valor Recebido</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recebimentos_mensais as $mes): ?>
                    <tr>
                        <td>
                            <i class="bx bx-calendar-check me-2 text-success"></i>
                            <?= $mes['mes_formatado'] ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success"><?= $mes['quantidade'] ?></span>
                        </td>
                        <td class="text-end fw-bold text-success"><?= $mes['valor_pago_formatado'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
