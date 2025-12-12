<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>
<style>
    /* Hero Section */
    .hero-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 1.5rem;
        padding: 2.5rem;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }
    
    .hero-section::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 60%;
        height: 200%;
        background: rgba(255, 255, 255, 0.05);
        transform: rotate(25deg);
        pointer-events: none;
    }
    
    .hero-section::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -10%;
        width: 40%;
        height: 150%;
        background: rgba(255, 255, 255, 0.03);
        transform: rotate(-15deg);
        pointer-events: none;
    }
    
    .hero-content {
        position: relative;
        z-index: 1;
    }
    
    .hero-avatar {
        width: 80px;
        height: 80px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(10px);
        border: 3px solid rgba(255, 255, 255, 0.3);
    }
    
    .hero-avatar i {
        font-size: 2.5rem;
    }
    
    .hero-date {
        background: rgba(255, 255, 255, 0.15);
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        backdrop-filter: blur(5px);
    }
    
    /* Stats Cards */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: #fff;
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .stat-icon.primary { background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; }
    .stat-icon.success { background: linear-gradient(135deg, #11998e, #38ef7d); color: #fff; }
    .stat-icon.warning { background: linear-gradient(135deg, #f093fb, #f5576c); color: #fff; }
    .stat-icon.info { background: linear-gradient(135deg, #4facfe, #00f2fe); color: #fff; }
    
    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 0.25rem;
    }
    
    .stat-label {
        color: #6c757d;
        font-size: 0.875rem;
    }
    
    /* Section Header */
    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
    }
    
    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1a1a2e;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .section-title i {
        color: #667eea;
    }
    
    /* Evento Cards - Modernizados */
    .evento-card {
        background: #fff;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        cursor: pointer;
        border: 2px solid transparent;
        height: 100%;
    }
    
    .evento-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 40px rgba(102, 126, 234, 0.25);
        border-color: #667eea;
    }
    
    .evento-card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
    }
    
    .evento-card-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -30%;
        width: 100%;
        height: 200%;
        background: rgba(255, 255, 255, 0.1);
        transform: rotate(30deg);
    }
    
    .evento-card-header .badge {
        position: relative;
        z-index: 1;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        font-weight: 500;
        padding: 0.5rem 1rem;
        border-radius: 2rem;
    }
    
    .evento-card-body {
        padding: 1.5rem;
    }
    
    .evento-nome {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 0.75rem;
    }
    
    .evento-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #6c757d;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }
    
    .evento-info i {
        color: #667eea;
    }
    
    .evento-card-footer {
        padding: 1rem 1.5rem;
        background: #f8f9fa;
        border-top: 1px solid #eee;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .evento-action {
        color: #667eea;
        font-weight: 600;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
    }
    
    .evento-card:hover .evento-action {
        gap: 0.75rem;
    }
    
    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.375rem 0.75rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .status-badge.active {
        background: rgba(40, 167, 69, 0.15);
        color: #198754;
    }
    
    .status-badge.inactive {
        background: rgba(220, 53, 69, 0.15);
        color: #dc3545;
    }
    
    /* Quick Actions */
    .quick-actions {
        background: #fff;
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        margin-bottom: 2rem;
    }
    
    .quick-action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
        padding: 1.25rem;
        border-radius: 1rem;
        background: #f8f9fa;
        text-decoration: none;
        color: #1a1a2e;
        transition: all 0.3s;
        text-align: center;
    }
    
    .quick-action-btn:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        transform: translateY(-3px);
    }
    
    .quick-action-btn i {
        font-size: 1.75rem;
        color: #667eea;
        transition: all 0.3s;
    }
    
    .quick-action-btn:hover i {
        color: #fff;
    }
    
    .quick-action-btn span {
        font-weight: 500;
        font-size: 0.875rem;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: #fff;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }
    
    .empty-state-icon {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #667eea20, #764ba220);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
    }
    
    .empty-state-icon i {
        font-size: 3rem;
        color: #667eea;
    }
    
    .empty-state h4 {
        color: #1a1a2e;
        margin-bottom: 0.5rem;
    }
    
    .empty-state p {
        color: #6c757d;
        margin-bottom: 1.5rem;
    }
    
    /* Tip Card */
    .tip-card {
        background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
        border-radius: 1rem;
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-top: 2rem;
        border: none;
    }
    
    .tip-card i {
        font-size: 1.5rem;
        color: #0097a7;
    }
    
    .tip-card-content {
        flex: 1;
    }
    
    .tip-card-content strong {
        color: #00796b;
    }
    
    .tip-card-content p {
        margin: 0;
        color: #00796b;
        font-size: 0.875rem;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .hero-section {
            padding: 1.5rem;
        }
        
        .hero-avatar {
            width: 60px;
            height: 60px;
        }
        
        .hero-avatar i {
            font-size: 2rem;
        }
        
        .stats-row {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        
        .stat-card {
            padding: 1rem;
        }
        
        .stat-value {
            font-size: 1.5rem;
        }
    }
</style>
<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="hero-content">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="hero-avatar">
                    <i class="bx bx-user"></i>
                </div>
                <div>
                    <h2 class="mb-1">Olá, <strong><?php echo esc(usuario_logado()->nome); ?>!</strong></h2>
                    <p class="mb-0 opacity-75" style="font-size: 1.1rem;">Bem-vindo ao seu painel administrativo</p>
                </div>
            </div>
            <div class="hero-date">
                <i class="bx bx-calendar"></i>
                <?php echo date('d/m/Y'); ?> • <?php echo strftime('%A'); ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<?php 
$totalEventos = count($eventos ?? []);
$eventosAtivos = 0;
foreach ($eventos ?? [] as $e) {
    if ($e->ativo == 1) $eventosAtivos++;
}
?>
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="bx bx-calendar-event"></i>
        </div>
        <div class="stat-value"><?php echo $totalEventos; ?></div>
        <div class="stat-label">Total de Eventos</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="bx bx-check-circle"></i>
        </div>
        <div class="stat-value"><?php echo $eventosAtivos; ?></div>
        <div class="stat-label">Eventos Ativos</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="bx bx-time-five"></i>
        </div>
        <div class="stat-value"><?php echo $totalEventos - $eventosAtivos; ?></div>
        <div class="stat-label">Eventos Inativos</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon info">
            <i class="bx bx-trending-up"></i>
        </div>
        <div class="stat-value"><?php echo $eventosAtivos > 0 ? round(($eventosAtivos / max($totalEventos, 1)) * 100) : 0; ?>%</div>
        <div class="stat-label">Taxa de Atividade</div>
    </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions">
    <div class="section-header mb-3">
        <h5 class="section-title mb-0">
            <i class="bx bx-zap"></i>
            Ações Rápidas
        </h5>
    </div>
    <div class="row g-3">
        <div class="col-6 col-md-3">
            <a href="<?php echo site_url('contratos'); ?>" class="quick-action-btn">
                <i class="bx bx-file"></i>
                <span>Contratos</span>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="<?php echo site_url('expositores'); ?>" class="quick-action-btn">
                <i class="bx bx-store-alt"></i>
                <span>Expositores</span>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="<?php echo site_url('eventos'); ?>" class="quick-action-btn">
                <i class="bx bx-calendar-plus"></i>
                <span>Eventos</span>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="<?php echo site_url('usuarios'); ?>" class="quick-action-btn">
                <i class="bx bx-group"></i>
                <span>Usuários</span>
            </a>
        </div>
    </div>
</div>

<!-- Events Section -->
<div class="section-header">
    <h5 class="section-title">
        <i class="bx bx-calendar-event"></i>
        Selecione um Evento
    </h5>
    <a href="<?php echo site_url('eventos'); ?>" class="btn btn-sm btn-outline-primary">
        <i class="bx bx-cog me-1"></i>Gerenciar Eventos
    </a>
</div>

<?php if (!empty($eventos)) : ?>
    <div class="row g-4">
        <?php foreach ($eventos as $evento) : ?>
            <?php if ($evento->ativo == 1) : ?>
                <div class="col-md-6 col-lg-4">
                    <div class="evento-card" onclick="window.location.href='<?= site_url('home/selecionarEvento/' . $evento->id) ?>'">
                        <div class="evento-card-header">
                            <span class="badge">
                                <i class="bx bx-calendar-check me-1"></i>
                                Evento Ativo
                            </span>
                        </div>
                        <div class="evento-card-body">
                            <h5 class="evento-nome"><?= esc($evento->nome) ?></h5>
                            <?php if (!empty($evento->local)) : ?>
                            <div class="evento-info">
                                <i class="bx bx-map"></i>
                                <?= esc($evento->local) ?>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($evento->data_inicio)) : ?>
                            <div class="evento-info">
                                <i class="bx bx-calendar"></i>
                                <?= date('d/m/Y', strtotime($evento->data_inicio)) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="evento-card-footer">
                            <span class="status-badge active">
                                <i class="bx bx-check-circle"></i>
                                Ativo
                            </span>
                            <span class="evento-action">
                                Acessar <i class="bx bx-right-arrow-alt"></i>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
        
        <?php // Eventos Inativos ?>
        <?php foreach ($eventos as $evento) : ?>
            <?php if ($evento->ativo != 1) : ?>
                <div class="col-md-6 col-lg-4">
                    <div class="evento-card" style="opacity: 0.7;" onclick="window.location.href='<?= site_url('home/selecionarEvento/' . $evento->id) ?>'">
                        <div class="evento-card-header" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%);">
                            <span class="badge">
                                <i class="bx bx-calendar-x me-1"></i>
                                Evento Inativo
                            </span>
                        </div>
                        <div class="evento-card-body">
                            <h5 class="evento-nome"><?= esc($evento->nome) ?></h5>
                            <?php if (!empty($evento->local)) : ?>
                            <div class="evento-info">
                                <i class="bx bx-map"></i>
                                <?= esc($evento->local) ?>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($evento->data_inicio)) : ?>
                            <div class="evento-info">
                                <i class="bx bx-calendar"></i>
                                <?= date('d/m/Y', strtotime($evento->data_inicio)) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="evento-card-footer">
                            <span class="status-badge inactive">
                                <i class="bx bx-x-circle"></i>
                                Inativo
                            </span>
                            <span class="evento-action">
                                Acessar <i class="bx bx-right-arrow-alt"></i>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    
    <!-- Tip Card -->
    <div class="tip-card">
        <i class="bx bx-bulb"></i>
        <div class="tip-card-content">
            <strong>Dica:</strong>
            <p>Após selecionar um evento, você terá acesso ao dashboard específico com estatísticas de ingressos, pedidos e muito mais!</p>
        </div>
    </div>
    
<?php else : ?>
    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="bx bx-calendar-x"></i>
        </div>
        <h4>Nenhum evento cadastrado</h4>
        <p>Comece criando seu primeiro evento para gerenciar ingressos e pedidos.</p>
        <a href="<?php echo site_url('eventos/criar'); ?>" class="btn btn-primary">
            <i class="bx bx-plus me-2"></i>Criar Novo Evento
        </a>
    </div>
<?php endif; ?>

<?php echo $this->endSection() ?>




<?php echo $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animate stats on scroll
        const stats = document.querySelectorAll('.stat-value');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeInUp 0.5s ease forwards';
                }
            });
        }, { threshold: 0.5 });
        
        stats.forEach(stat => observer.observe(stat));
        
        // Add ripple effect to cards
        const cards = document.querySelectorAll('.evento-card');
        cards.forEach(card => {
            card.addEventListener('click', function(e) {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const ripple = document.createElement('span');
                ripple.style.cssText = `
                    position: absolute;
                    background: rgba(102, 126, 234, 0.3);
                    border-radius: 50%;
                    transform: scale(0);
                    animation: ripple 0.6s linear;
                    pointer-events: none;
                    width: 100px;
                    height: 100px;
                    left: ${x - 50}px;
                    top: ${y - 50}px;
                `;
                
                card.style.position = 'relative';
                card.style.overflow = 'hidden';
                card.appendChild(ripple);
                
                setTimeout(() => ripple.remove(), 600);
            });
        });
    });
    
    // Add CSS animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
</script>
<?php echo $this->endSection() ?>