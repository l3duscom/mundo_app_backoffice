<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">
        <a href="<?php echo site_url('/artistas'); ?>">Artistas</a>
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('/artistas'); ?>">Artistas</a></li>
                <li class="breadcrumb-item active"><?php echo esc($artista->nome_artistico); ?></li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="<?php echo site_url("artistas/editar/{$artista->id}"); ?>" class="btn btn-primary me-2">
            <i class="bx bx-edit me-1"></i>Editar
        </a>
        <a href="<?php echo site_url('artistas'); ?>" class="btn btn-secondary">
            <i class="bx bx-arrow-back me-1"></i>Voltar
        </a>
    </div>
</div>

<div class="row">
    <!-- Coluna Esquerda - Dados do Artista -->
    <div class="col-lg-4">
        <div class="card shadow radius-10 mb-3">
            <div class="card-body text-center">
                <div class="avatar avatar-xl mb-3">
                    <i class="bx bx-microphone" style="font-size: 4rem; color: var(--bs-primary);"></i>
                </div>
                <h4 class="mb-1"><?php echo esc($artista->nome_artistico); ?></h4>
                <p class="text-muted mb-2"><?php echo esc($artista->genero_musical ?? 'Gênero não informado'); ?></p>
                <?php echo $artista->exibeStatus(); ?>
            </div>
        </div>

        <div class="card shadow radius-10 mb-3">
            <div class="card-body">
                <h6 class="card-title"><i class="bx bx-user me-2"></i>Dados Pessoais</h6>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><strong>Nome Civil:</strong><br><?php echo esc($artista->nome_completo ?? '-'); ?></li>
                    <li class="mb-2"><strong>CPF:</strong> <?php echo $artista->getCpfFormatado() ?: '-'; ?></li>
                    <li class="mb-2"><strong>RG:</strong> <?php echo esc($artista->rg ?? '-'); ?></li>
                    <li class="mb-2"><strong>Nascimento:</strong> <?php echo $artista->getDataNascimentoFormatada(); ?></li>
                    <li class="mb-2"><strong>Nacionalidade:</strong> <?php echo esc($artista->nacionalidade ?? '-'); ?></li>
                    <?php if ($artista->isEstrangeiro()): ?>
                    <li class="mb-2"><strong>Passaporte:</strong> <?php echo $artista->getPassaporteInfo(); ?></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="card shadow radius-10">
            <div class="card-body">
                <h6 class="card-title"><i class="bx bx-phone me-2"></i>Contato</h6>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><i class="bx bx-envelope me-2"></i><?php echo esc($artista->email ?? '-'); ?></li>
                    <li class="mb-2"><i class="bx bx-phone me-2"></i><?php echo esc($artista->telefone ?? '-'); ?></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Coluna Direita - Contatos e Contratações -->
    <div class="col-lg-8">
        <!-- Contatos (Agentes, etc.) -->
        <div class="card shadow radius-10 mb-3">
            <div class="card-body">
                <h6 class="card-title"><i class="bx bx-group me-2"></i>Agentes e Contatos</h6>
                
                <?php if (!empty($contatos)): ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Nome</th>
                                <th>Telefone</th>
                                <th>E-mail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contatos as $c): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?php echo ucfirst($c->tipo); ?></span></td>
                                <td><?php echo esc($c->nome); ?></td>
                                <td><?php echo esc($c->telefone ?? '-'); ?></td>
                                <td><?php echo esc($c->email ?? '-'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted text-center mb-0">Nenhum contato cadastrado</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Biografia e Rider -->
        <?php if (!empty($artista->biografia) || !empty($artista->rider_tecnico)): ?>
        <div class="card shadow radius-10 mb-3">
            <div class="card-body">
                <?php if (!empty($artista->biografia)): ?>
                <h6 class="card-title"><i class="bx bx-book-open me-2"></i>Biografia</h6>
                <p><?php echo nl2br(esc($artista->biografia)); ?></p>
                <?php endif; ?>
                
                <?php if (!empty($artista->rider_tecnico)): ?>
                <h6 class="card-title mt-4"><i class="bx bx-cog me-2"></i>Rider Técnico</h6>
                <p class="mb-0"><?php echo nl2br(esc($artista->rider_tecnico)); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Contratações -->
        <div class="card shadow radius-10">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="card-title mb-0"><i class="bx bx-calendar-event me-2"></i>Contratações</h6>
                </div>
                
                <?php if (!empty($contratacoes)): ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Evento</th>
                                <th>Data</th>
                                <th class="text-end">Cachê</th>
                                <th class="text-center">Situação</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contratacoes as $c): ?>
                            <tr>
                                <td><code><?php echo esc($c->codigo); ?></code></td>
                                <td><?php echo esc($c->evento_nome ?? '-'); ?></td>
                                <td><?php echo $c->getDataApresentacaoFormatada(); ?></td>
                                <td class="text-end"><?php echo $c->getValorCacheFormatado(); ?></td>
                                <td class="text-center"><?php echo $c->exibeSituacao(); ?></td>
                                <td class="text-center">
                                    <a href="<?php echo site_url("artista-contratacoes/exibir/{$c->id}"); ?>" class="btn btn-sm btn-outline-info" title="Ver">
                                        <i class="bx bx-show"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted text-center mb-0">Nenhuma contratação registrada</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>
