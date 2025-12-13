<?= $this->extend('Layout/principal') ?>

<?= $this->section('titulo') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('estilos') ?>
<style>
    .report-card {
        border: none;
        border-radius: 16px;
        transition: all 0.3s ease;
        cursor: pointer;
        background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
        overflow: hidden;
    }
    
    .report-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 40px rgba(0,0,0,0.15);
    }
    
    .report-card .card-header {
        border: none;
        padding: 25px;
        color: white;
    }
    
    .report-card.periodo .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .report-card.ingresso .card-header {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    
    .report-card.metodo .card-header {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .report-card .card-header i {
        font-size: 3rem;
        opacity: 0.9;
    }
    
    .report-card .card-body {
        padding: 25px;
    }
    
    .report-card h5 {
        font-weight: 600;
        color: #333;
        margin-bottom: 10px;
    }
    
    .report-card p {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 0;
    }
    
    .hero-section {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        border-radius: 20px;
        padding: 40px;
        color: white;
        margin-bottom: 30px;
    }
    
    .hero-section h1 {
        font-weight: 700;
        margin-bottom: 10px;
    }
    
    .hero-section p {
        opacity: 0.9;
        margin-bottom: 0;
    }
    
    .event-selector {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('conteudo') ?>

<div class="hero-section">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1><i class="bx bx-bar-chart-alt-2 me-3"></i>Relatórios de Vendas</h1>
            <p>Gere relatórios detalhados sobre as vendas do seu evento. Exporte para PDF ou Excel.</p>
        </div>
        <div class="col-md-4 text-end">
            <?php if ($evento): ?>
                <span class="badge bg-light text-dark fs-6 px-4 py-2">
                    <i class="bx bx-calendar-event me-2"></i><?= esc($evento->nome) ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Seletor de Evento -->
<div class="event-selector">
    <form action="<?= site_url('relatorios/vendas') ?>" method="get" class="row g-3 align-items-end">
        <div class="col-md-8">
            <label class="form-label fw-bold">Selecione o Evento</label>
            <select name="evento_id" class="form-select form-select-lg" onchange="this.form.submit()">
                <option value="">-- Selecione um evento --</option>
                <?php foreach ($eventos as $evt): ?>
                    <option value="<?= $evt->id ?>" <?= ($event_id == $evt->id) ? 'selected' : '' ?>>
                        <?= esc($evt->nome) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary btn-lg w-100">
                <i class="bx bx-search me-2"></i>Selecionar
            </button>
        </div>
    </form>
</div>

<?php if ($event_id): ?>
<!-- Cards de Relatórios -->
<div class="row g-4">
    <!-- Relatório por Período -->
    <div class="col-md-4">
        <div class="card report-card periodo h-100" onclick="window.location.href='<?= site_url('relatorios/vendas/periodo?evento_id=' . $event_id) ?>'">
            <div class="card-header text-center">
                <i class="bx bx-calendar"></i>
            </div>
            <div class="card-body text-center">
                <h5>Vendas por Período</h5>
                <p>Acompanhe a evolução diária, semanal ou mensal das vendas do evento.</p>
            </div>
            <div class="card-footer bg-transparent border-0 text-center pb-4">
                <span class="btn btn-outline-primary">
                    <i class="bx bx-right-arrow-alt me-1"></i>Acessar
                </span>
            </div>
        </div>
    </div>
    
    <!-- Relatório por Ingresso -->
    <div class="col-md-4">
        <div class="card report-card ingresso h-100" onclick="window.location.href='<?= site_url('relatorios/vendas/ingresso?evento_id=' . $event_id) ?>'">
            <div class="card-header text-center">
                <i class="bx bx-ticket"></i>
            </div>
            <div class="card-body text-center">
                <h5>Vendas por Ingresso</h5>
                <p>Veja quais tipos de ingresso são mais vendidos e sua participação na receita.</p>
            </div>
            <div class="card-footer bg-transparent border-0 text-center pb-4">
                <span class="btn btn-outline-success">
                    <i class="bx bx-right-arrow-alt me-1"></i>Acessar
                </span>
            </div>
        </div>
    </div>
    
    <!-- Relatório por Método de Pagamento -->
    <div class="col-md-4">
        <div class="card report-card metodo h-100" onclick="window.location.href='<?= site_url('relatorios/vendas/metodo?evento_id=' . $event_id) ?>'">
            <div class="card-header text-center">
                <i class="bx bx-credit-card"></i>
            </div>
            <div class="card-body text-center">
                <h5>Vendas por Método</h5>
                <p>Analise a distribuição de vendas por forma de pagamento (PIX, Cartão, etc).</p>
            </div>
            <div class="card-footer bg-transparent border-0 text-center pb-4">
                <span class="btn btn-outline-danger">
                    <i class="bx bx-right-arrow-alt me-1"></i>Acessar
                </span>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- Mensagem para selecionar evento -->
<div class="alert alert-info d-flex align-items-center" role="alert">
    <i class="bx bx-info-circle fs-4 me-3"></i>
    <div>
        <strong>Selecione um evento acima</strong> para visualizar os relatórios disponíveis.
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
