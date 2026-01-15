<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
/* Main Container with Modern Aesthetic */
.lead-detail-page {
    max-width: 1400px;
    margin: 0 auto;
}

/* Top Hero Section with Gradient */
.lead-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 0;
    color: white;
    margin-bottom: 24px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
}

.lead-hero-content {
    padding: 32px;
}

.lead-hero-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
}

.lead-codigo-badge {
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.lead-hero h1 {
    font-size: 2rem;
    font-weight: 700;
    margin: 0 0 4px 0;
}

.lead-hero .lead-empresa {
    opacity: 0.85;
    font-size: 1rem;
}

.lead-hero-value {
    text-align: right;
}

.lead-hero-value .valor {
    font-size: 2.5rem;
    font-weight: 800;
    line-height: 1;
}

.lead-hero-value .valor-label {
    font-size: 0.85rem;
    opacity: 0.8;
    margin-top: 4px;
}

.lead-hero-badges {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.lead-hero-badges .badge {
    padding: 8px 16px;
    font-size: 0.85rem;
    font-weight: 600;
    border-radius: 8px;
}

/* Action Bar */
.action-bar {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    padding: 16px 32px;
    margin-top: 20px;
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.action-bar .btn {
    border-radius: 10px;
    padding: 10px 20px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.action-bar .btn-light {
    background: white;
    border: none;
    color: #495057;
}

.action-bar .btn-light:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.action-bar .btn-success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    border: none;
}

.action-bar .btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(17, 153, 142, 0.4);
}

/* Converted Banner */
.converted-banner {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
    padding: 20px 24px;
    border-radius: 16px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 8px 30px rgba(17, 153, 142, 0.3);
}

.converted-banner .icon-circle {
    width: 56px;
    height: 56px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
}

.converted-banner a {
    color: white;
    font-weight: 600;
    text-decoration: underline;
}

/* Info Cards with Modern Style */
.info-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    padding: 24px;
    margin-bottom: 24px;
    border: 1px solid rgba(0,0,0,0.04);
    transition: all 0.2s;
}

.info-card:hover {
    box-shadow: 0 8px 30px rgba(0,0,0,0.08);
}

.info-card h5 {
    color: #1a1a2e;
    font-weight: 700;
    font-size: 1.1rem;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-card h5 i {
    font-size: 1.3rem;
    color: #667eea;
}

/* Contact Info Grid */
.contact-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    background: #f8f9fa;
    border-radius: 12px;
    transition: all 0.2s;
}

.contact-item:hover {
    background: #e9ecef;
}

.contact-item .icon-box {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.contact-item .icon-box.email { background: rgba(234, 88, 12, 0.1); color: #ea580c; }
.contact-item .icon-box.phone { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.contact-item .icon-box.whatsapp { background: rgba(37, 211, 102, 0.1); color: #25d366; }
.contact-item .icon-box.instagram { background: rgba(236, 72, 153, 0.1); color: #ec4899; }
.contact-item .icon-box.document { background: rgba(99, 102, 241, 0.1); color: #6366f1; }

.contact-item .info {
    flex: 1;
}

.contact-item .info .label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.contact-item .info .value {
    font-weight: 600;
    color: #1a1a2e;
    font-size: 0.95rem;
}

.contact-item .info .value a {
    color: #667eea;
    text-decoration: none;
}

.contact-item .info .value a:hover {
    text-decoration: underline;
}

/* Quick Stats Row */
.stats-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid rgba(0,0,0,0.04);
}

.stat-card .icon {
    width: 50px;
    height: 50px;
    margin: 0 auto 12px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
}

.stat-card .stat-value {
    font-size: 1rem;
    font-weight: 700;
    color: #1a1a2e;
}

.stat-card .stat-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Timeline Modern */
.timeline-modern {
    position: relative;
    padding-left: 0;
}

.timeline-modern::before {
    display: none;
}

.timeline-item-modern {
    display: flex;
    gap: 16px;
    padding: 16px;
    margin-bottom: 12px;
    background: #f8f9fa;
    border-radius: 14px;
    transition: all 0.2s;
    border: 1px solid transparent;
}

.timeline-item-modern:hover {
    background: #fff;
    border-color: #e9ecef;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.timeline-icon-modern {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.timeline-content-modern {
    flex: 1;
}

.timeline-content-modern .time {
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 4px;
}

.timeline-content-modern .tipo-badge {
    font-size: 0.7rem;
    padding: 3px 10px;
    border-radius: 20px;
    margin-left: 8px;
}

.timeline-content-modern .text {
    font-size: 0.95rem;
    color: #1a1a2e;
    line-height: 1.5;
}

.timeline-content-modern .user {
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 6px;
}

/* Next Action Card */
.next-action-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
}

.next-action-card.overdue {
    background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
}

.next-action-card h5 {
    color: white;
    margin-bottom: 16px;
}

.next-action-card h5 i {
    color: white;
}

.next-action-card .date {
    font-size: 2rem;
    font-weight: 800;
}

.next-action-card .description {
    opacity: 0.9;
    margin-top: 8px;
}

/* Observations Card */
.observations-card {
    background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
    border-radius: 16px;
    padding: 24px;
}

.observations-card h5 {
    color: #1a1a2e;
}

.observations-card h5 i {
    color: #ea580c;
}

.observations-card p {
    color: #495057;
    line-height: 1.6;
}

/* Lost Reason Card */
.lost-reason-card {
    background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
    color: white;
    border-radius: 16px;
    padding: 24px;
}

.lost-reason-card h5 {
    color: white;
}

.lost-reason-card h5 i {
    color: white;
}

/* Empty State */
.empty-timeline {
    text-align: center;
    padding: 40px 20px;
    color: #adb5bd;
}

.empty-timeline i {
    font-size: 3rem;
    margin-bottom: 16px;
}

/* Modal Improvements */
.modal-nova-atividade .modal-content {
    border-radius: 20px;
    border: none;
}

.modal-nova-atividade .modal-header {
    border-bottom: none;
    padding: 24px 24px 0;
}

.modal-nova-atividade .modal-body {
    padding: 24px;
}

.modal-nova-atividade .modal-footer {
    border-top: none;
    padding: 0 24px 24px;
}

.activity-type-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 16px;
}

.activity-type-buttons .btn-check:checked + label {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.activity-type-buttons label {
    padding: 10px 16px;
    border-radius: 10px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 6px;
}

@media (max-width: 768px) {
    .lead-hero-top {
        flex-direction: column;
        gap: 20px;
    }
    .lead-hero-value {
        text-align: left;
    }
    .contact-grid {
        grid-template-columns: 1fr;
    }
    .stats-row {
        grid-template-columns: 1fr;
    }
}
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="lead-detail-page">
    
    <!-- Breadcrumb -->
    <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?php echo site_url('pipeline'); ?>">Pipeline</a></li>
                    <li class="breadcrumb-item active"><?php echo esc($lead->codigo); ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <?php if (session()->has('sucesso')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bx bx-check-circle me-2"></i><?php echo session('sucesso'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->has('erro')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bx bx-error-circle me-2"></i><?php echo session('erro'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Converted Banner -->
    <?php if ($lead->isConvertido()): ?>
    <div class="converted-banner">
        <div class="icon-circle">
            <i class="bx bx-check-double"></i>
        </div>
        <div>
            <h5 class="mb-1 fw-bold">Lead Convertido com Sucesso!</h5>
            <div>
                <?php if ($expositor): ?>
                    Expositor: <a href="<?php echo site_url('expositores/exibir/' . $expositor->id); ?>"><?php echo esc($expositor->nome); ?></a>
                <?php endif; ?>
                <?php if ($contrato): ?>
                    &nbsp;•&nbsp; Contrato: <a href="<?php echo site_url('contratos/exibir/' . $contrato->id); ?>"><?php echo esc($contrato->codigo); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Hero Section -->
    <div class="lead-hero">
        <div class="lead-hero-content">
            <div class="lead-hero-top">
                <div>
                    <span class="lead-codigo-badge">
                        <i class="bx bx-hash"></i> <?php echo esc($lead->codigo); ?>
                    </span>
                    <h1><?php echo esc($lead->getNomeExibicao()); ?></h1>
                    <?php if ($lead->nome_fantasia && $lead->nome_fantasia !== $lead->nome): ?>
                        <div class="lead-empresa"><?php echo esc($lead->nome); ?></div>
                    <?php endif; ?>
                </div>
                <div class="lead-hero-value">
                    <div class="valor"><?php echo $lead->getValorEstimadoFormatado(); ?></div>
                    <div class="valor-label">Valor Estimado</div>
                </div>
            </div>
            <div class="lead-hero-badges">
                <?php echo $lead->getBadgeEtapa(); ?>
                <?php echo $lead->getBadgeTemperatura(); ?>
                <?php if ($lead->segmento): ?>
                    <span class="badge bg-white text-dark"><?php echo esc($lead->segmento); ?></span>
                <?php endif; ?>
                <?php if ($lead->origem): ?>
                    <span class="badge bg-white bg-opacity-25"><?php echo esc($lead->origem); ?></span>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Action Bar inside Hero -->
        <div class="action-bar">
            <a href="<?php echo site_url('pipeline/editar/' . $lead->id); ?>" class="btn btn-light">
                <i class="bx bx-edit"></i> Editar
            </a>
            <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#modalAtividade">
                <i class="bx bx-plus-circle"></i> Nova Atividade
            </button>
            <?php if ($lead->celular): ?>
            <a href="https://wa.me/55<?php echo preg_replace('/[^0-9]/', '', $lead->celular); ?>" target="_blank" class="btn btn-light" style="color: #25d366;">
                <i class="bx bxl-whatsapp"></i> WhatsApp
            </a>
            <?php endif; ?>
            <?php if ($lead->email): ?>
            <a href="mailto:<?php echo esc($lead->email); ?>" class="btn btn-light" style="color: #ea580c;">
                <i class="bx bx-envelope"></i> E-mail
            </a>
            <?php endif; ?>
            <?php if ($lead->podeConverter()): ?>
            <form action="<?php echo site_url('pipeline/converterEmExpositor'); ?>" method="post" class="d-inline">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="lead_id" value="<?php echo $lead->id; ?>">
                <button type="submit" class="btn btn-success" onclick="return confirm('Confirma a conversão deste lead em expositor e criação de contrato?')">
                    <i class="bx bx-rocket"></i> Converter em Expositor
                </button>
            </form>
            <?php endif; ?>
            <a href="<?php echo site_url('pipeline'); ?>" class="btn btn-light ms-auto">
                <i class="bx bx-arrow-back"></i> Voltar
            </a>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="icon" style="background: rgba(102, 126, 234, 0.1); color: #667eea;">
                <i class="bx bx-user"></i>
            </div>
            <div class="stat-value"><?php echo $vendedor ? esc($vendedor->nome) : 'Não atribuído'; ?></div>
            <div class="stat-label">Vendedor</div>
        </div>
        <div class="stat-card">
            <div class="icon" style="background: rgba(236, 72, 153, 0.1); color: #ec4899;">
                <i class="bx bx-calendar-event"></i>
            </div>
            <div class="stat-value"><?php echo $evento ? esc($evento->nome) : 'Não definido'; ?></div>
            <div class="stat-label">Evento</div>
        </div>
        <div class="stat-card">
            <div class="icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                <i class="bx bx-time-five"></i>
            </div>
            <div class="stat-value"><?php echo date('d/m/Y', strtotime($lead->created_at)); ?></div>
            <div class="stat-label">Criado em</div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Contact Information -->
            <div class="info-card">
                <h5><i class="bx bx-id-card"></i> Informações de Contato</h5>
                
                <div class="contact-grid">
                    <?php if ($lead->email): ?>
                    <div class="contact-item">
                        <div class="icon-box email">
                            <i class="bx bx-envelope"></i>
                        </div>
                        <div class="info">
                            <div class="label">E-mail</div>
                            <div class="value"><a href="mailto:<?php echo esc($lead->email); ?>"><?php echo esc($lead->email); ?></a></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($lead->celular): ?>
                    <div class="contact-item">
                        <div class="icon-box whatsapp">
                            <i class="bx bxl-whatsapp"></i>
                        </div>
                        <div class="info">
                            <div class="label">WhatsApp</div>
                            <div class="value"><a href="https://wa.me/55<?php echo preg_replace('/[^0-9]/', '', $lead->celular); ?>" target="_blank"><?php echo esc($lead->celular); ?></a></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($lead->telefone): ?>
                    <div class="contact-item">
                        <div class="icon-box phone">
                            <i class="bx bx-phone"></i>
                        </div>
                        <div class="info">
                            <div class="label">Telefone</div>
                            <div class="value"><?php echo esc($lead->telefone); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($lead->instagram): ?>
                    <div class="contact-item">
                        <div class="icon-box instagram">
                            <i class="bx bxl-instagram"></i>
                        </div>
                        <div class="info">
                            <div class="label">Instagram</div>
                            <div class="value"><a href="https://instagram.com/<?php echo esc($lead->instagram); ?>" target="_blank">@<?php echo esc($lead->instagram); ?></a></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($lead->documento): ?>
                    <div class="contact-item">
                        <div class="icon-box document">
                            <i class="bx bx-file"></i>
                        </div>
                        <div class="info">
                            <div class="label"><?php echo $lead->tipo_pessoa === 'fisica' ? 'CPF' : 'CNPJ'; ?></div>
                            <div class="value"><?php echo esc($lead->documento); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="contact-item">
                        <div class="icon-box document">
                            <i class="bx bx-buildings"></i>
                        </div>
                        <div class="info">
                            <div class="label">Tipo</div>
                            <div class="value"><?php echo $lead->tipo_pessoa === 'fisica' ? 'Pessoa Física' : 'Pessoa Jurídica'; ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="info-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="bx bx-history"></i> Histórico de Atividades</h5>
                </div>
                
                <?php if (empty($atividades)): ?>
                    <div class="empty-timeline">
                        <i class="bx bx-message-square-detail"></i>
                        <p class="mb-0">Nenhuma atividade registrada ainda</p>
                        <button type="button" class="btn btn-primary btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#modalAtividade">
                            <i class="bx bx-plus me-1"></i>Adicionar primeira atividade
                        </button>
                    </div>
                <?php else: ?>
                    <div class="timeline-modern">
                        <?php foreach ($atividades as $atividade): ?>
                            <?php $config = \App\Models\LeadAtividadeModel::getConfigTipo($atividade->tipo); ?>
                            <div class="timeline-item-modern">
                                <div class="timeline-icon-modern bg-<?php echo $config['cor']; ?> text-white">
                                    <i class="<?php echo $config['icone']; ?>"></i>
                                </div>
                                <div class="timeline-content-modern">
                                    <div class="time">
                                        <?php echo date('d/m/Y', strtotime($atividade->created_at)); ?> às <?php echo date('H:i', strtotime($atividade->created_at)); ?>
                                        <span class="tipo-badge badge bg-<?php echo $config['cor']; ?>"><?php echo $config['nome']; ?></span>
                                    </div>
                                    <div class="text">
                                        <?php if ($atividade->tipo === 'mudanca_etapa'): ?>
                                            Movido de <strong><?php echo \App\Models\LeadAtividadeModel::getNomeEtapa($atividade->etapa_anterior); ?></strong>
                                            para <strong><?php echo \App\Models\LeadAtividadeModel::getNomeEtapa($atividade->etapa_nova); ?></strong>
                                        <?php else: ?>
                                            <?php echo nl2br(esc($atividade->descricao)); ?>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($atividade->usuario_nome): ?>
                                        <div class="user"><i class="bx bx-user-circle"></i> <?php echo esc($atividade->usuario_nome); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Next Action -->
            <?php if ($lead->proxima_acao): ?>
            <div class="next-action-card <?php echo $lead->isProximaAcaoAtrasada() ? 'overdue' : ''; ?>">
                <h5>
                    <i class="bx bx-calendar-check me-2"></i>Próxima Ação
                    <?php if ($lead->isProximaAcaoAtrasada()): ?>
                        <span class="badge bg-white text-danger ms-2">ATRASADA</span>
                    <?php endif; ?>
                </h5>
                <div class="date"><?php echo $lead->getProximaAcaoFormatada(); ?></div>
                <?php if ($lead->proxima_acao_descricao): ?>
                    <div class="description"><?php echo esc($lead->proxima_acao_descricao); ?></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Observations -->
            <?php if ($lead->observacoes): ?>
            <div class="observations-card info-card">
                <h5><i class="bx bx-notepad"></i> Observações</h5>
                <p class="mb-0"><?php echo nl2br(esc($lead->observacoes)); ?></p>
            </div>
            <?php endif; ?>

            <!-- Lost Reason -->
            <?php if ($lead->etapa === 'perdido' && $lead->motivo_perda): ?>
            <div class="lost-reason-card">
                <h5><i class="bx bx-x-circle me-2"></i> Motivo da Perda</h5>
                <p class="mb-0"><?php echo esc($lead->motivo_perda); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Nova Atividade -->
<div class="modal fade modal-nova-atividade" id="modalAtividade" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Nova Atividade</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="activity-type-buttons mb-3">
                    <input type="radio" class="btn-check" name="tipoAtividade" id="tipoNota" value="nota" checked>
                    <label class="btn btn-outline-secondary" for="tipoNota"><i class="bx bx-note"></i> Nota</label>
                    
                    <input type="radio" class="btn-check" name="tipoAtividade" id="tipoLigacao" value="ligacao">
                    <label class="btn btn-outline-secondary" for="tipoLigacao"><i class="bx bx-phone"></i> Ligação</label>
                    
                    <input type="radio" class="btn-check" name="tipoAtividade" id="tipoEmail" value="email">
                    <label class="btn btn-outline-secondary" for="tipoEmail"><i class="bx bx-envelope"></i> E-mail</label>
                    
                    <input type="radio" class="btn-check" name="tipoAtividade" id="tipoWhatsapp" value="whatsapp">
                    <label class="btn btn-outline-secondary" for="tipoWhatsapp"><i class="bx bxl-whatsapp"></i> WhatsApp</label>
                    
                    <input type="radio" class="btn-check" name="tipoAtividade" id="tipoReuniao" value="reuniao">
                    <label class="btn btn-outline-secondary" for="tipoReuniao"><i class="bx bx-video"></i> Reunião</label>
                </div>
                <textarea class="form-control" id="descricaoAtividade" rows="5" placeholder="Descreva a atividade..." style="border-radius: 12px;"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 10px;">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarAtividade" style="border-radius: 10px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                    <i class="bx bx-save me-1"></i>Salvar
                </button>
            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Salvar atividade
    $('#btnSalvarAtividade').on('click', function() {
        var tipo = $('input[name="tipoAtividade"]:checked').val();
        var descricao = $('#descricaoAtividade').val();
        
        if (!descricao.trim()) {
            alert('Por favor, descreva a atividade.');
            return;
        }
        
        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Salvando...');
        
        $.ajax({
            url: '<?php echo site_url("pipeline/registrarAtividade"); ?>',
            type: 'POST',
            data: {
                lead_id: <?php echo $lead->id; ?>,
                tipo: tipo,
                descricao: descricao,
                '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.sucesso) {
                    location.reload();
                } else {
                    $('#btnSalvarAtividade').prop('disabled', false).html('<i class="bx bx-save me-1"></i>Salvar');
                    alert(response.erro || 'Erro ao salvar atividade.');
                }
            },
            error: function() {
                $('#btnSalvarAtividade').prop('disabled', false).html('<i class="bx bx-save me-1"></i>Salvar');
                alert('Erro ao salvar atividade.');
            }
        });
    });
});
</script>
<?php echo $this->endSection() ?>
