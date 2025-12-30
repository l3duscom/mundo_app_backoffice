<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
    /* Estilos do Pódio */
    .podio-container {
        display: flex;
        justify-content: center;
        align-items: flex-end;
        gap: 20px;
        padding: 30px 0;
        min-height: 350px;
    }
    
    .podio-item {
        text-align: center;
        transition: transform 0.3s ease;
    }
    
    .podio-item:hover {
        transform: translateY(-10px);
    }
    
    .podio-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-size: 1.8rem;
        font-weight: bold;
        color: white;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    }
    
    .podio-nome {
        font-weight: 600;
        font-size: 1rem;
        margin-bottom: 5px;
        max-width: 150px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .podio-nota {
        font-size: 1.2rem;
        font-weight: bold;
    }
    
    .podio-base {
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px 8px 0 0;
        padding: 15px 25px;
        font-size: 2rem;
        font-weight: bold;
        color: rgba(255,255,255,0.9);
    }
    
    /* 1º Lugar - Ouro */
    .podio-1 .podio-base {
        background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
        height: 150px;
        width: 140px;
    }
    .podio-1 .podio-avatar {
        background: linear-gradient(135deg, #FFD700 0%, #DAA520 100%);
        border: 4px solid #FFA500;
        box-shadow: 0 0 20px rgba(255, 215, 0, 0.5);
    }
    .podio-1 .podio-nota { color: #DAA520; }
    
    /* 2º Lugar - Prata */
    .podio-2 .podio-base {
        background: linear-gradient(135deg, #C0C0C0 0%, #808080 100%);
        height: 120px;
        width: 120px;
    }
    .podio-2 .podio-avatar {
        background: linear-gradient(135deg, #C0C0C0 0%, #A9A9A9 100%);
        border: 4px solid #808080;
        box-shadow: 0 0 15px rgba(192, 192, 192, 0.5);
    }
    .podio-2 .podio-nota { color: #808080; }
    
    /* 3º Lugar - Bronze */
    .podio-3 .podio-base {
        background: linear-gradient(135deg, #CD7F32 0%, #8B4513 100%);
        height: 90px;
        width: 120px;
    }
    .podio-3 .podio-avatar {
        background: linear-gradient(135deg, #CD7F32 0%, #B8860B 100%);
        border: 4px solid #8B4513;
        box-shadow: 0 0 15px rgba(205, 127, 50, 0.5);
    }
    .podio-3 .podio-nota { color: #CD7F32; }
    
    /* Cards estatísticos */
    .stat-card {
        padding: 1.5rem;
        border-radius: 12px;
        color: white;
        text-align: center;
    }
    .stat-card.purple {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .stat-card.green {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    .stat-card.orange {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    .stat-card h3 {
        font-size: 2.5rem;
        font-weight: bold;
        margin: 0;
    }
    .stat-card p {
        margin: 0.5rem 0 0;
        opacity: 0.9;
    }
    
    /* Badge de posição */
    .posicao-badge {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: white;
    }
    .posicao-1 { background: linear-gradient(135deg, #FFD700, #DAA520); }
    .posicao-2 { background: linear-gradient(135deg, #C0C0C0, #808080); }
    .posicao-3 { background: linear-gradient(135deg, #CD7F32, #8B4513); }
    .posicao-default { background: #6c757d; }
    
    /* Tabela */
    .ranking-table tbody tr:first-child { background-color: rgba(255, 215, 0, 0.1); }
    .ranking-table tbody tr:nth-child(2) { background-color: rgba(192, 192, 192, 0.1); }
    .ranking-table tbody tr:nth-child(3) { background-color: rgba(205, 127, 50, 0.1); }
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Concursos</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url("concursos/{$concurso->evento_id}"); ?>">Concursos</a></li>
                <li class="breadcrumb-item active" aria-current="page">Ranking</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto d-flex gap-2 align-items-center">
        <?php if (!$isCosplay): ?>
            <!-- Filtro por categoria (apenas K-Pop) -->
            <select class="form-select form-select-sm" id="filtroCategoria" style="width: 180px;" onchange="filtrarCategoria()">
                <option value="todos" <?= $categoria === 'todos' ? 'selected' : '' ?>>Todas Categorias</option>
                <option value="solo" <?= $categoria === 'solo' ? 'selected' : '' ?>>Solo</option>
                <option value="dupla" <?= $categoria === 'dupla' ? 'selected' : '' ?>>Dupla</option>
                <option value="grupo" <?= $categoria === 'grupo' ? 'selected' : '' ?>>Grupo (3+)</option>
            </select>
        <?php endif; ?>
        <a href="<?php echo site_url("concursos/gerenciar/{$concurso->id}"); ?>" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-2"></i>Voltar
        </a>
    </div>
</div>
<!--end breadcrumb-->

<!-- Título do concurso -->
<div class="card mb-4">
    <div class="card-body text-center py-4">
        <h2 class="mb-1"><i class="bx bx-trophy text-warning me-2"></i><?= esc($concurso->nome) ?></h2>
        <p class="text-muted mb-0">
            <?php 
            $tiposLabel = [
                'desfile_cosplay' => 'Desfile Cosplay',
                'apresentacao_cosplay' => 'Apresentação Cosplay', 
                'cosplay_kids' => 'Cosplay Kids',
                'kpop' => 'K-Pop Cover',
            ];
            echo $tiposLabel[$concurso->tipo] ?? ucfirst($concurso->tipo);
            ?>
            <?php if (!$isCosplay && $categoria !== 'todos'): ?>
                <span class="badge bg-primary ms-2"><?= ucfirst($categoria) ?></span>
            <?php endif; ?>
        </p>
    </div>
</div>

<!-- Cards estatísticos -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card purple">
            <h3><?= $totalParticipantes ?></h3>
            <p><i class="bx bx-user-check"></i> Participantes Classificados</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card green">
            <h3><?= number_format($mediaGeral, 2, ',', '.') ?></h3>
            <p><i class="bx bx-bar-chart-alt-2"></i> Média Geral</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card orange">
            <h3><?= number_format($maiorNota, 2, ',', '.') ?></h3>
            <p><i class="bx bx-star"></i> Maior Nota</p>
        </div>
    </div>
</div>

<?php if (count($ranking) >= 3): ?>
<!-- Pódio Visual -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bx bx-medal me-2 text-warning"></i>Pódio</h5>
    </div>
    <div class="card-body">
        <div class="podio-container">
            <!-- 2º Lugar (esquerda) -->
            <div class="podio-item podio-2">
                <div class="podio-avatar">
                    <?= strtoupper(substr($ranking[1]['participante'] ?? '?', 0, 1)) ?>
                </div>
                <div class="podio-nome" title="<?= esc($ranking[1]['participante'] ?? '') ?>">
                    <?= esc($ranking[1]['participante'] ?? '-') ?>
                </div>
                <?php if ($isCosplay && !empty($ranking[1]['personagem'])): ?>
                    <small class="text-muted d-block"><?= esc($ranking[1]['personagem']) ?></small>
                <?php endif; ?>
                <div class="podio-nota"><?= number_format($ranking[1]['media_nota_total'] ?? 0, 2, ',', '.') ?></div>
                <div class="podio-base">2º</div>
            </div>
            
            <!-- 1º Lugar (centro) -->
            <div class="podio-item podio-1">
                <div class="podio-avatar">
                    <?= strtoupper(substr($ranking[0]['participante'] ?? '?', 0, 1)) ?>
                </div>
                <div class="podio-nome" title="<?= esc($ranking[0]['participante'] ?? '') ?>">
                    <?= esc($ranking[0]['participante'] ?? '-') ?>
                </div>
                <?php if ($isCosplay && !empty($ranking[0]['personagem'])): ?>
                    <small class="text-muted d-block"><?= esc($ranking[0]['personagem']) ?></small>
                <?php endif; ?>
                <div class="podio-nota"><?= number_format($ranking[0]['media_nota_total'] ?? 0, 2, ',', '.') ?></div>
                <div class="podio-base">1º</div>
            </div>
            
            <!-- 3º Lugar (direita) -->
            <div class="podio-item podio-3">
                <div class="podio-avatar">
                    <?= strtoupper(substr($ranking[2]['participante'] ?? '?', 0, 1)) ?>
                </div>
                <div class="podio-nome" title="<?= esc($ranking[2]['participante'] ?? '') ?>">
                    <?= esc($ranking[2]['participante'] ?? '-') ?>
                </div>
                <?php if ($isCosplay && !empty($ranking[2]['personagem'])): ?>
                    <small class="text-muted d-block"><?= esc($ranking[2]['personagem']) ?></small>
                <?php endif; ?>
                <div class="podio-nota"><?= number_format($ranking[2]['media_nota_total'] ?? 0, 2, ',', '.') ?></div>
                <div class="podio-base">3º</div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Tabela de Ranking Completo -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bx bx-list-ol me-2"></i>Classificação Completa</h5>
        <span class="badge bg-primary"><?= count($ranking) ?> participantes</span>
    </div>
    <div class="card-body">
        <?php if (count($ranking) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover ranking-table">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 60px;">Posição</th>
                        <th>Participante</th>
                        <?php if ($isCosplay): ?>
                            <th>Personagem</th>
                            <th>Obra</th>
                        <?php else: ?>
                            <th>Categoria</th>
                            <th>Artista/Música</th>
                        <?php endif; ?>
                        <th class="text-center">Avaliações</th>
                        <th class="text-end">Média</th>
                        <th class="text-center" style="width: 80px;">Detalhes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ranking as $index => $item): ?>
                    <tr>
                        <td class="text-center">
                            <?php 
                            $posicao = $index + 1;
                            $badgeClass = $posicao <= 3 ? "posicao-{$posicao}" : 'posicao-default';
                            ?>
                            <span class="posicao-badge <?= $badgeClass ?>"><?= $posicao ?>º</span>
                        </td>
                        <td>
                            <strong><?= esc($item['participante']) ?></strong>
                            <?php 
                            // Para K-Pop solo: mostrar nome do RG abaixo do nome social
                            if (!$isCosplay && ($item['categoria'] ?? '') === 'solo' && !empty($item['nome']) && $item['participante'] !== $item['nome']): 
                            ?>
                                <br><small class="text-muted"><?= esc($item['nome']) ?></small>
                            <?php elseif (!empty($item['nome_social']) && $item['participante'] !== $item['nome_social']): ?>
                                <br><small class="text-muted"><?= esc($item['nome_social']) ?></small>
                            <?php endif; ?>
                        </td>
                        <?php if ($isCosplay): ?>
                            <td><?= esc($item['personagem'] ?? '-') ?></td>
                            <td><?= esc($item['obra'] ?? '-') ?></td>
                        <?php else: ?>
                            <td>
                                <?php 
                                $categoriaLabel = $item['categoria'] ?? 'solo';
                                if ($categoriaLabel === 'solo') {
                                    echo '<span class="badge bg-info">Solo</span>';
                                } elseif (($item['integrantes'] ?? 1) == 2) {
                                    echo '<span class="badge bg-secondary">Dupla</span>';
                                } else {
                                    echo '<span class="badge bg-dark">Grupo</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php if (!empty($item['marca'])): ?>
                                    <small class="text-muted"><?= esc($item['marca']) ?></small>
                                <?php endif; ?>
                                <?php if (!empty($item['nome_musica'])): ?>
                                    <br><em><?= esc($item['nome_musica']) ?></em>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                        <td class="text-center">
                            <span class="badge bg-secondary"><?= $item['total_avaliacoes'] ?>/<?= $item['jurados_necessarios'] ?></span>
                        </td>
                        <td class="text-end">
                            <span class="fw-bold <?= $posicao <= 3 ? 'text-success' : '' ?>" style="font-size: 1.1rem;">
                                <?= number_format($item['media_nota_total'], 2, ',', '.') ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="verDetalhes(<?= $item['inscricao_id'] ?>, '<?= esc($item['participante']) ?>')" title="Ver notas detalhadas">
                                <i class="bx bx-show"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="bx bx-trophy text-muted" style="font-size: 4rem;"></i>
            <h5 class="mt-3 text-muted">Nenhum participante classificado ainda</h5>
            <p class="text-muted">Os participantes aparecerão aqui após receberem todas as avaliações dos jurados.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de Detalhes das Avaliações -->
<div class="modal fade" id="modalDetalhes" tabindex="-1" aria-labelledby="modalDetalhesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="modalDetalhesLabel">
                    <i class="bx bx-bar-chart-alt-2 me-2"></i>Detalhes das Avaliações
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div id="modalDetalhesConteudo">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                        <p class="mt-2 text-muted">Carregando avaliações...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script>
function filtrarCategoria() {
    var categoria = document.getElementById('filtroCategoria').value;
    var url = new URL(window.location.href);
    
    if (categoria === 'todos') {
        url.searchParams.delete('categoria');
    } else {
        url.searchParams.set('categoria', categoria);
    }
    
    window.location.href = url.toString();
}

function verDetalhes(inscricaoId, participante) {
    // Atualizar título do modal
    document.getElementById('modalDetalhesLabel').innerHTML = '<i class="bx bx-bar-chart-alt-2 me-2"></i>Avaliações - ' + participante;
    
    // Mostrar loading
    document.getElementById('modalDetalhesConteudo').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <p class="mt-2 text-muted">Carregando avaliações...</p>
        </div>
    `;
    
    // Abrir modal
    var modal = new bootstrap.Modal(document.getElementById('modalDetalhes'));
    modal.show();
    
    // Buscar dados via AJAX
    fetch('<?= site_url('concursos/detalhesAvaliacao') ?>/' + inscricaoId, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            renderizarDetalhes(data.avaliacoes, data.categorias);
        } else {
            document.getElementById('modalDetalhesConteudo').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bx bx-error me-2"></i>${data.mensagem || 'Erro ao carregar avaliações.'}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        document.getElementById('modalDetalhesConteudo').innerHTML = `
            <div class="alert alert-danger">
                <i class="bx bx-error me-2"></i>Erro ao conectar com o servidor.
            </div>
        `;
    });
}

function renderizarDetalhes(avaliacoes, categorias) {
    if (avaliacoes.length === 0) {
        document.getElementById('modalDetalhesConteudo').innerHTML = `
            <div class="alert alert-info">
                <i class="bx bx-info-circle me-2"></i>Nenhuma avaliação encontrada.
            </div>
        `;
        return;
    }
    
    var html = `
        <div class="table-responsive">
            <table class="table table-bordered table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Jurado</th>
                        <th class="text-center">${categorias.nota_1}</th>
                        <th class="text-center">${categorias.nota_2}</th>
                        <th class="text-center">${categorias.nota_3}</th>
                        <th class="text-center">${categorias.nota_4}</th>
                        <th class="text-center bg-primary">Total</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    var somaTotal = 0;
    avaliacoes.forEach(function(av) {
        var total = parseFloat(av.nota_total) || 0;
        somaTotal += total;
        
        html += `
            <tr>
                <td><strong>${av.jurado_nome}</strong></td>
                <td class="text-center">${formatarNota(av.nota_1)}</td>
                <td class="text-center">${formatarNota(av.nota_2)}</td>
                <td class="text-center">${formatarNota(av.nota_3)}</td>
                <td class="text-center">${formatarNota(av.nota_4)}</td>
                <td class="text-center fw-bold text-primary">${formatarNota(av.nota_total)}</td>
            </tr>
        `;
    });
    
    // Linha de média
    var media = somaTotal / avaliacoes.length;
    html += `
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="5" class="text-end"><strong>Média do Participante:</strong></td>
                        <td class="text-center"><strong class="text-success fs-5">${formatarNota(media)}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="mt-3 text-muted small">
            <i class="bx bx-info-circle me-1"></i>
            Total de ${avaliacoes.length} avaliação(ões) registrada(s).
        </div>
    `;
    
    document.getElementById('modalDetalhesConteudo').innerHTML = html;
}

function formatarNota(valor) {
    if (valor === null || valor === undefined || valor === '') return '-';
    return parseFloat(valor).toFixed(2).replace('.', ',');
}
</script>
<?php echo $this->endSection() ?>
