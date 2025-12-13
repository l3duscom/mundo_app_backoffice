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
    
    .method-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-right: 12px;
    }
    
    .method-icon.pix { background: #32BCAD20; color: #32BCAD; }
    .method-icon.card { background: #5C6BC020; color: #5C6BC0; }
    .method-icon.boleto { background: #FF980020; color: #FF9800; }
    .method-icon.money { background: #4CAF5020; color: #4CAF50; }
</style>
<?= $this->endSection() ?>

<?= $this->section('conteudo') ?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= site_url('relatorios/vendas') ?>">Relatórios de Vendas</a></li>
        <li class="breadcrumb-item active">Por Método de Pagamento</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bx bx-credit-card me-2"></i><?= $titulo ?></h4>
    <div>
        <a href="<?= site_url('relatorios/vendas/exportar-excel/metodo?evento_id=' . $event_id . '&data_inicio=' . $data_inicio . '&data_fim=' . $data_fim) ?>" class="btn btn-success me-2">
            <i class="bx bx-file me-1"></i>Exportar Excel
        </a>
        <a href="<?= site_url('relatorios/vendas/exportar-pdf/metodo?evento_id=' . $event_id . '&data_inicio=' . $data_inicio . '&data_fim=' . $data_fim) ?>" target="_blank" class="btn btn-danger">
            <i class="bx bx-file-blank me-1"></i>Exportar PDF
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="filter-card">
    <form action="<?= site_url('relatorios/vendas/metodo') ?>" method="get" class="row g-3 align-items-end">
        <div class="col-md-3">
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
            <label class="form-label">Data Início</label>
            <input type="date" name="data_inicio" class="form-control" value="<?= $data_inicio ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Data Fim</label>
            <input type="date" name="data_fim" class="form-control" value="<?= $data_fim ?>">
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
    <div class="col-md-4">
        <div class="stats-card">
            <p class="mb-1 opacity-75">Métodos Utilizados</p>
            <h3><?= count($vendas_por_metodo) ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <p class="mb-1 opacity-75">Total de Vendas</p>
            <h3><?= $totais['quantidade'] ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
            <p class="mb-1 opacity-75">Receita Total</p>
            <h3><?= $totais['valor_formatado'] ?></h3>
        </div>
    </div>
</div>

<!-- Tabela de Resultados -->
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-report table-hover mb-0">
                <thead>
                    <tr>
                        <th>Método de Pagamento</th>
                        <th class="text-center">Quantidade</th>
                        <th class="text-end">Valor Total</th>
                        <th class="text-center">Participação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($vendas_por_metodo)): ?>
                        <?php foreach ($vendas_por_metodo as $venda): ?>
                        <?php 
                        $iconClass = 'money';
                        $icon = 'bx-money';
                        $metodo = strtoupper($venda['metodo']);
                        if (strpos($metodo, 'PIX') !== false) { $iconClass = 'pix'; $icon = 'bx-qr'; }
                        elseif (strpos($metodo, 'CART') !== false || strpos($metodo, 'CREDIT') !== false) { $iconClass = 'card'; $icon = 'bx-credit-card'; }
                        elseif (strpos($metodo, 'BOLETO') !== false) { $iconClass = 'boleto'; $icon = 'bx-barcode'; }
                        ?>
                        <tr>
                            <td>
                                <span class="method-icon <?= $iconClass ?>">
                                    <i class="bx <?= $icon ?>"></i>
                                </span>
                                <strong><?= esc($venda['metodo_label']) ?></strong>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger"><?= $venda['quantidade'] ?></span>
                            </td>
                            <td class="text-end fw-bold text-success">
                                <?= $venda['valor_formatado'] ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary fs-6"><?= $venda['percentual'] ?>%</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
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
