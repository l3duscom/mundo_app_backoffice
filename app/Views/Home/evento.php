<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>
<style>
    .evento-header {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .stats-card {
        transition: all 0.3s ease;
        border: 2px solid transparent;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .stats-card:hover {
        transform: translateY(-2px);
        border-color: #007bff;
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.2);
    }
    
    .stats-card .card-body {
        padding: 1.5rem;
    }
    
    .stats-value {
        font-size: 2rem;
        font-weight: 700;
        color: #007bff;
    }
    
    .stats-label {
        font-size: 0.9rem;
        color: #6c757d;
        font-weight: 500;
    }
    
    .stats-amount {
        font-size: 1.2rem;
        font-weight: 600;
        color: #28a745;
    }
    
    .type-card {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        padding: 1rem;
        text-align: center;
    }
    
    .type-card .type-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #495057;
    }
    
    .type-card .type-label {
        font-size: 0.8rem;
        color: #6c757d;
        text-transform: uppercase;
        font-weight: 600;
    }
    
    .toggle-section {
        background: #f8f9fa;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 2rem;
    }
</style>
<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>

<!-- Header do Evento Selecionado -->
<div class="evento-header">
    <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <div class="me-3">
                <i class="bi bi-calendar-check" style="font-size: 2rem;"></i>
            </div>
            <div>
                <h6 class="mb-0"><?= esc($evento->nome) ?></h6>
            </div>
        </div>
        <div>
            <a href="<?= site_url('/') ?>" class="btn btn-light btn-sm">
                <i class="bi bi-arrow-left me-1"></i>
                Trocar Evento
            </a>
        </div>
    </div>
</div>

<!-- Seção de Estatísticas -->
<div class="toggle-section">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-graph-up me-2"></i>
                Estatísticas do Evento
            </h5>
            <p class="mb-0 text-muted">Visualize os dados de ingressos e vendas</p>
        </div>
        <button class="btn btn-outline-primary" data-bs-toggle="collapse" href="#collapseStats" role="button" aria-expanded="false" aria-controls="collapseStats">
            <i class="bi bi-eye-fill me-2"></i>
            Mostrar/Ocultar
        </button>
    </div>
</div>

<div class="collapse" id="collapseStats">
    <!-- Estatísticas Principais -->
    <div class="row row-cols-1 row-cols-lg-4 mb-4">
        <div class="col">
            <div class="card stats-card h-100">
                <div class="card-body text-center">
                    <div class="stats-value"><?= $total_ingressos; ?></div>
                    <div class="stats-label">Total de Ingressos</div>
                    <div class="stats-amount mt-2">
                        R$ <?= number_format($valor_total[0]->total, 2, ',', '.') ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card stats-card h-100">
                <div class="card-body text-center">
                    <div class="stats-value"><?= $total_ingressos_hoje; ?></div>
                    <div class="stats-label">Ingressos Hoje</div>
                    <div class="stats-amount mt-2">
                        R$ <?= number_format($valor_hoje[0]->total, 2, ',', '.') ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card stats-card h-100">
                <div class="card-body text-center">
                    <div class="stats-value"><?= $total_ingressos_pendentes; ?></div>
                    <div class="stats-label">Pendentes</div>
                    <div class="mt-2">
                        <i class="bi bi-clock text-warning" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card stats-card h-100">
                <div class="card-body text-center">
                    <div class="stats-value"><?= $total_ingressos_cortesias; ?></div>
                    <div class="stats-label">Cortesias</div>
                    <div class="mt-2">
                        <i class="bi bi-cup-hot text-info" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estatísticas por Tipo -->
    <div class="row">
        <div class="col-12">
            <h6 class="mb-3">
                <i class="bi bi-list-ul me-2"></i>
                Ingressos por Tipo
            </h6>
        </div>
    </div>
    
    <div class="row row-cols-2 row-cols-lg-4">
        <div class="col mb-3">
            <div class="type-card">
                <div class="type-value"><?= $total_sabado; ?></div>
                <div class="type-label">Sábado</div>
            </div>
        </div>
        <div class="col mb-3">
            <div class="type-card">
                <div class="type-value"><?= $total_domingo; ?></div>
                <div class="type-label">Domingo</div>
            </div>
        </div>
        <div class="col mb-3">
            <div class="type-card">
                <div class="type-value"><?= $total_epic; ?></div>
                <div class="type-label">EPIC PASS</div>
            </div>
        </div>
        <div class="col mb-3">
            <div class="type-card">
                <div class="type-value"><?= $total_vip; ?></div>
                <div class="type-label">VIP FULL</div>
            </div>
        </div>
        <div class="col mb-3">
            <div class="type-card">
                <div class="type-value"><?= $total_elite; ?></div>
                <div class="type-label">ELITE</div>
            </div>
        </div>
         <div class="col mb-3">
            <div class="type-card">
                <div class="type-value"><?= $total_exp; ?></div>
                <div class="type-label">EXPERIENCE</div>
            </div>
        </div>
    </div>
</div>


<?php echo $this->endSection() ?>




<?php echo $this->section('scripts') ?>
<script>
    // Adicionar animações suaves
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.stats-card');
        
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    });
</script>
<?php echo $this->endSection() ?>