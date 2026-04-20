<?= $this->extend('Layout/principal') ?>

<?= $this->section('titulo') ?>
<?= esc($titulo) ?>
<?= $this->endSection() ?>

<?= $this->section('estilos') ?>
<style>
    .hero-reemb {
        background: linear-gradient(135deg, #fd7e14 0%, #e85d04 100%);
        border-radius: 16px;
        padding: 32px;
        color: #fff;
        margin-bottom: 24px;
    }
    .filtro-card {
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.06);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('conteudo') ?>

<div class="hero-reemb">
    <h1 class="h3 mb-2"><i class="bx bx-receipt me-2"></i><?= esc($titulo) ?></h1>
    <p class="mb-0 opacity-90">Filtre por evento, período (data da solicitação), tipo e situação de pagamento. Exporte para CSV ou PDF.</p>
</div>

<div class="card filtro-card border-0">
    <div class="card-body p-4">
        <form action="<?= site_url('relatorios/reembolsos/lista') ?>" method="get" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-bold">Evento</label>
                <select name="evento_id" class="form-select">
                    <option value="">Todos os eventos</option>
                    <?php foreach ($eventos as $evt) : ?>
                        <option value="<?= (int) $evt->id ?>" <?= ($event_id == $evt->id) ? 'selected' : '' ?>>
                            <?= esc($evt->nome) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">Data inicial</label>
                <input type="date" name="data_inicio" class="form-control" value="<?= esc($data_inicio) ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">Data final</label>
                <input type="date" name="data_fim" class="form-control" value="<?= esc($data_fim) ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">Tipo</label>
                <select name="tipo_solicitacao" class="form-select">
                    <option value="">Todos</option>
                    <option value="reembolso">Reembolso</option>
                    <option value="upgrade">Upgrade</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">Pagamento</label>
                <select name="status" class="form-select">
                    <option value="">Todos</option>
                    <option value="pendente">Pendente</option>
                    <option value="processando">Processando</option>
                    <option value="concluido">Concluído</option>
                    <option value="cancelado">Cancelado</option>
                    <option value="erro">Erro</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bx bx-search-alt me-1"></i>Gerar relatório
                </button>
                <a href="<?= site_url('relatorios/reembolsos') ?>" class="btn btn-outline-secondary">Limpar</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
