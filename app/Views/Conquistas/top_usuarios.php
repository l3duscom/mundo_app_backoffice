<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
    .user-rank {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 0.5rem;
        background: #f8f9fa;
        transition: all 0.2s;
    }
    .user-rank:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }
    .user-rank.top-1 {
        background: linear-gradient(135deg, #FFD700, #FFA500);
        color: #333;
    }
    .user-rank.top-2 {
        background: linear-gradient(135deg, #C0C0C0, #A0A0A0);
        color: #333;
    }
    .user-rank.top-3 {
        background: linear-gradient(135deg, #CD7F32, #A0522D);
        color: white;
    }
    .rank-number {
        font-size: 1.5rem;
        font-weight: bold;
        min-width: 50px;
        text-align: center;
    }
    .user-info {
        flex: 1;
    }
    .user-info h6 {
        margin: 0;
    }
    .user-info small {
        color: inherit;
        opacity: 0.8;
    }
    .conquista-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
    }
    .conquista-header h2 {
        margin: 0;
    }
    .conquista-header .badge {
        font-size: 1rem;
    }
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Conquistas</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('conquistas-admin'); ?>">Conquistas</a></li>
                <li class="breadcrumb-item active" aria-current="page">Usuários</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="<?php echo site_url('conquistas-admin'); ?>" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-2"></i>Voltar
        </a>
    </div>
</div>
<!--end breadcrumb-->

<!-- Header da conquista -->
<div class="conquista-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="bx bx-trophy me-2"></i><?php echo esc($conquista->nome_conquista); ?></h2>
            <p class="mb-0 mt-2"><?php echo esc($conquista->descricao ?? 'Sem descrição'); ?></p>
        </div>
        <div class="text-end">
            <div class="mb-2">
                <?php
                $cores = [
                    'BRONZE' => 'background: linear-gradient(135deg, #CD7F32, #8B4513); color: white;',
                    'PRATA' => 'background: linear-gradient(135deg, #C0C0C0, #808080); color: white;',
                    'OURO' => 'background: linear-gradient(135deg, #FFD700, #DAA520); color: #333;',
                    'PLATINA' => 'background: linear-gradient(135deg, #E5E4E2, #BCC6CC); color: #333;',
                    'DIAMANTE' => 'background: linear-gradient(135deg, #B9F2FF, #7DF9FF); color: #333;',
                ];
                $estilo = $cores[$conquista->nivel] ?? 'background: #6c757d; color: white;';
                ?>
                <span class="badge" style="<?php echo $estilo; ?>"><?php echo esc($conquista->nivel); ?></span>
            </div>
            <h3 class="mb-0"><?php echo number_format($conquista->pontos, 0, ',', '.'); ?> pts</h3>
        </div>
    </div>
</div>

<!-- Lista de usuários -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bx bx-user me-2"></i>Usuários com esta Conquista</h5>
        <span class="badge bg-primary"><?php echo count($usuarios); ?> usuário(s)</span>
    </div>
    <div class="card-body">
        <?php if (empty($usuarios)): ?>
            <div class="text-center text-muted py-5">
                <i class="bx bx-user-x" style="font-size: 4rem;"></i>
                <p class="mt-3">Nenhum usuário possui esta conquista ainda.</p>
            </div>
        <?php else: ?>
            <?php foreach ($usuarios as $index => $usuario): ?>
                <?php
                $rankClass = '';
                if ($index === 0) $rankClass = 'top-1';
                elseif ($index === 1) $rankClass = 'top-2';
                elseif ($index === 2) $rankClass = 'top-3';
                ?>
                <div class="user-rank <?php echo $rankClass; ?>">
                    <div class="rank-number">
                        <?php if ($index < 3): ?>
                            <i class="bx bx-medal"></i>
                        <?php else: ?>
                            <?php echo ($index + 1); ?>º
                        <?php endif; ?>
                    </div>
                    <div class="user-info">
                        <h6><?php echo esc($usuario->nome); ?></h6>
                        <small><?php echo esc($usuario->email); ?></small>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-success"><?php echo number_format($usuario->pontos, 0, ',', '.'); ?> pts</span>
                        <br>
                        <small><?php echo date('d/m/Y H:i', strtotime($usuario->created_at)); ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<?php echo $this->endSection() ?>
