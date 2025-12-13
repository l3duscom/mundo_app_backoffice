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
    
    .report-card.situacao .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .report-card.financeiro .card-header {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    
    .report-card.expositor .card-header {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .report-card .card-header i {
        font-size: 3rem;
        opacity: 0.9;
    }
    
    .report-card .card-body {
        padding: 25px;
    }
    
    .hero-section {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        border-radius: 20px;
        padding: 40px;
        color: white;
        margin-bottom: 30px;
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
            <h1><i class="bx bx-file me-3"></i>Relatórios de Contratos</h1>
            <p>Gere relatórios detalhados sobre os contratos do seu evento. Exporte para PDF ou Excel.</p>
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
    <form action="<?= site_url('relatorios/contratos') ?>" method="get" class="row g-3 align-items-end">
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
    <!-- Relatório por Situação -->
    <div class="col-md-4">
        <div class="card report-card situacao h-100" onclick="window.location.href='<?= site_url('relatorios/contratos/situacao?evento_id=' . $event_id) ?>'">
            <div class="card-header text-center">
                <i class="bx bx-list-check"></i>
            </div>
            <div class="card-body text-center">
                <h5>Contratos por Situação</h5>
                <p>Veja a distribuição dos contratos por status (rascunho, assinado, finalizado, etc).</p>
            </div>
            <div class="card-footer bg-transparent border-0 text-center pb-4">
                <span class="btn btn-outline-primary">
                    <i class="bx bx-right-arrow-alt me-1"></i>Acessar
                </span>
            </div>
        </div>
    </div>
    
    <!-- Relatório Financeiro -->
    <div class="col-md-4">
        <div class="card report-card financeiro h-100" onclick="window.location.href='<?= site_url('relatorios/contratos/financeiro?evento_id=' . $event_id) ?>'">
            <div class="card-header text-center">
                <i class="bx bx-dollar-circle"></i>
            </div>
            <div class="card-body text-center">
                <h5>Resumo Financeiro</h5>
                <p>Acompanhe valores totais, pagos, em aberto e evolução de recebimentos.</p>
            </div>
            <div class="card-footer bg-transparent border-0 text-center pb-4">
                <span class="btn btn-outline-success">
                    <i class="bx bx-right-arrow-alt me-1"></i>Acessar
                </span>
            </div>
        </div>
    </div>
    
    <!-- Relatório por Expositor -->
    <div class="col-md-4">
        <div class="card report-card expositor h-100" onclick="window.location.href='<?= site_url('relatorios/contratos/expositor?evento_id=' . $event_id) ?>'">
            <div class="card-header text-center">
                <i class="bx bx-store-alt"></i>
            </div>
            <div class="card-body text-center">
                <h5>Contratos por Expositor</h5>
                <p>Veja os contratos agrupados por expositor com valores e status.</p>
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
<div class="alert alert-info d-flex align-items-center" role="alert">
    <i class="bx bx-info-circle fs-4 me-3"></i>
    <div>
        <strong>Selecione um evento acima</strong> para visualizar os relatórios disponíveis.
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
