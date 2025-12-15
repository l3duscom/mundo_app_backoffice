<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="container-fluid py-4">
    
    <!-- Header de boas-vindas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body py-4">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-white bg-opacity-25 p-3 me-3">
                            <i class="bi bi-building text-white" style="font-size: 2rem;"></i>
                        </div>
                        <div>
                            <h3 class="text-white mb-1">Olá, <?= esc($expositor->nome_fantasia ?? $expositor->nome ?? 'Parceiro') ?>!</h3>
                            <p class="text-white-50 mb-0">Bem-vindo ao seu painel de parceiro</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!isset($expositor) || !$expositor) : ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Seu cadastro de expositor ainda não está vinculado à sua conta. Entre em contato com o suporte.
    </div>
    <?php else : ?>

    <!-- Contratos por Evento -->
    <?php if (empty($contratos_por_evento)) : ?>
    <div class="text-center py-5">
        <i class="bi bi-file-earmark-text text-muted" style="font-size: 4rem;"></i>
        <h5 class="mt-3 text-muted">Nenhum contrato encontrado</h5>
        <p class="text-muted">Seus contratos aparecerão aqui quando forem cadastrados.</p>
    </div>
    <?php else : ?>
    
    <?php foreach ($contratos_por_evento as $eventoData) : ?>
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header bg-dark border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1 text-white">
                        <i class="bi bi-calendar-event me-2"></i><?= esc($eventoData['evento']->nome) ?>
                    </h5>
                    <small class="text-muted">
                        <?= date('d/m/Y', strtotime($eventoData['evento']->data_inicio)) ?> 
                        - <?= date('d/m/Y', strtotime($eventoData['evento']->data_fim)) ?>
                    </small>
                </div>
                <span class="badge bg-primary"><?= count($eventoData['contratos']) ?> contrato(s)</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-dark table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Descrição</th>
                            <th>Valor</th>
                            <th>Situação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($eventoData['contratos'] as $contrato) : ?>
                        <tr>
                            <td><strong><?= esc($contrato->codigo) ?></strong></td>
                            <td><?= esc($contrato->descricao ?? '-') ?></td>
                            <td>R$ <?= number_format($contrato->valor_final, 2, ',', '.') ?></td>
                            <td>
                                <?php
                                $situacaoClass = match($contrato->situacao) {
                                    'pago' => 'bg-success',
                                    'ativo', 'assinado' => 'bg-primary',
                                    'pendente', 'proposta' => 'bg-warning text-dark',
                                    'cancelado', 'banido' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?= $situacaoClass ?>"><?= ucfirst(esc($contrato->situacao)) ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    
    <?php endif; ?>
    <?php endif; ?>

</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<?php echo $this->endSection() ?>
