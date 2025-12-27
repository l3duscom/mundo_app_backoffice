<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<!-- DataTables -->
<link href="<?php echo site_url('recursos/theme/'); ?>plugins/datatable/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
<style>
    .stat-card {
        padding: 1.5rem;
        border-radius: 12px;
        color: white;
        text-align: center;
        height: 100%;
    }
    .stat-card.purple { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .stat-card.green { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
    .stat-card.orange { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    .stat-card.blue { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .stat-card h3 { font-size: 2.2rem; font-weight: bold; margin: 0; }
    .stat-card p { margin: 0.5rem 0 0; opacity: 0.9; font-size: 0.9rem; }
    .distribuicao-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 12px;
        border-bottom: 1px solid #eee;
    }
    .distribuicao-item:last-child { border-bottom: none; }
    .whatsapp-btn {
        background: #25D366;
        color: white;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }
    .whatsapp-btn:hover { background: #128C7E; color: white; }
    .evento-ranking-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 12px;
        border-bottom: 1px solid #eee;
    }
    .evento-ranking-item:last-child { border-bottom: none; }
    .tipo-badge {
        font-size: 0.75rem;
        padding: 2px 8px;
        border-radius: 10px;
        background: #e9ecef;
    }
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Relatórios</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Participantes de Concursos</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto d-flex gap-2 align-items-center">
        <label class="form-label mb-0 text-muted">Tipo:</label>
        <select class="form-select form-select-sm" id="filtroTipo" style="width: 180px;" onchange="filtrarPorTipo()">
            <option value="">Todos os tipos</option>
            <?php foreach ($tiposDisponiveis as $tipo): ?>
                <option value="<?php echo esc($tipo['tipo']); ?>" <?php echo ($tipoFiltro == $tipo['tipo']) ? 'selected' : ''; ?>>
                    <?php echo esc($tipo['tipo']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($tipoFiltro)): ?>
            <a href="<?php echo site_url('relatorios/participantes-concursos'); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bx bx-x"></i> Limpar
            </a>
        <?php endif; ?>
    </div>
</div>
<!--end breadcrumb-->

<!-- Cards estatísticos -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card purple">
            <h3><?php echo number_format($estatisticas['total_participantes_recorrentes'], 0, ',', '.'); ?></h3>
            <p><i class="bx bx-group"></i> Participantes Recorrentes</p>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card green">
            <h3><?php echo $estatisticas['media_eventos_por_participante']; ?></h3>
            <p><i class="bx bx-calendar-event"></i> Média Eventos/Participante</p>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card orange">
            <h3><?php echo number_format($estatisticas['total_inscricoes_recorrentes'], 0, ',', '.'); ?></h3>
            <p><i class="bx bx-notepad"></i> Total Inscrições</p>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card blue">
            <h3><?php echo $estatisticas['max_eventos']; ?></h3>
            <p><i class="bx bx-trophy"></i> Máx. Eventos</p>
        </div>
    </div>
</div>

<div class="row">
    <!-- Tabela Principal -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bx bx-trophy me-2 text-warning"></i>Ranking de Participantes - Múltiplos Eventos</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tabelaParticipantes" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">#</th>
                                <th>Participante</th>
                                <th class="text-center" style="width: 50px;">WhatsApp</th>
                                <th class="text-center">Eventos</th>
                                <th class="text-center">Inscrições</th>
                                <th class="text-center">Concursos</th>
                                <th class="text-center">Primeira</th>
                                <th class="text-center">Última</th>
                                <th class="text-center" style="width: 60px;">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $posicao = 1; ?>
                            <?php foreach ($participantes as $participante): ?>
                                <?php 
                                // Limpar telefone para WhatsApp
                                $telefone = preg_replace('/[^0-9]/', '', $participante['telefone'] ?? '');
                                if (!empty($telefone) && strlen($telefone) >= 10 && substr($telefone, 0, 2) !== '55') {
                                    $telefone = '55' . $telefone;
                                }
                                ?>
                                <tr class="<?php echo $posicao <= 3 ? 'table-warning' : ''; ?>">
                                    <td class="text-center fw-bold"><?php echo $posicao; ?>º</td>
                                    <td>
                                        <?php if ($posicao <= 3): ?>
                                            <i class="bx bx-medal text-warning"></i>
                                        <?php endif; ?>
                                        <strong><?php echo esc($participante['nome']); ?></strong><br>
                                        <small class="text-muted"><?php echo esc($participante['email']); ?></small>
                                        <?php if (!empty($participante['tipos_concurso'])): ?>
                                            <br><small class="tipo-badge"><?php echo esc($participante['tipos_concurso']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($telefone)): ?>
                                            <a href="https://wa.me/<?php echo $telefone; ?>" target="_blank" class="whatsapp-btn" title="<?php echo esc($participante['telefone']); ?>">
                                                <i class="bx bxl-whatsapp"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="#" class="badge bg-primary fs-6 text-decoration-none" 
                                           data-bs-toggle="popover" 
                                           data-bs-trigger="click"
                                           data-bs-html="true"
                                           data-bs-placement="left"
                                           title="Eventos participados"
                                           data-bs-content="<?php echo esc(str_replace(', ', '<br>', $participante['eventos_participados'])); ?>">
                                            <?php echo $participante['total_eventos']; ?> <i class="bx bx-search-alt-2" style="font-size:10px;"></i>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success"><?php echo $participante['total_inscricoes']; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info"><?php echo $participante['total_concursos']; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <small><?php echo date('d/m/Y', strtotime($participante['primeira_participacao'])); ?></small>
                                    </td>
                                    <td class="text-center">
                                        <small><?php echo date('d/m/Y', strtotime($participante['ultima_participacao'])); ?></small>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($participante['user_id'])): ?>
                                            <button type="button" class="btn btn-warning btn-sm" 
                                                    onclick="abrirModalConquista(<?php echo $participante['user_id']; ?>, '<?php echo esc($participante['nome']); ?>')" 
                                                    title="Atribuir Conquista">
                                                <i class="bx bx-trophy"></i>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php $posicao++; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Distribuição por eventos -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bx bx-bar-chart-alt-2 me-2"></i>Distribuição por Qtd. Eventos</h6>
            </div>
            <div class="card-body p-0">
                <?php foreach ($distribuicao as $qtd => $total): ?>
                    <div class="distribuicao-item">
                        <span><strong><?php echo $qtd; ?></strong> eventos</span>
                        <span class="badge bg-primary"><?php echo $total; ?> participantes</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Tipos de Concurso -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bx bx-category me-2"></i>Por Tipo de Concurso</h6>
            </div>
            <div class="card-body p-0">
                <?php foreach ($tiposConcurso as $tipo): ?>
                    <div class="evento-ranking-item">
                        <span><strong><?php echo esc($tipo['tipo']); ?></strong></span>
                        <div>
                            <span class="badge bg-success me-1"><?php echo $tipo['total_participantes']; ?> participantes</span>
                            <span class="badge bg-info"><?php echo $tipo['total_inscricoes']; ?> inscrições</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Eventos com mais participantes recorrentes -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bx bx-calendar-star me-2"></i>Eventos com Mais Recorrentes</h6>
            </div>
            <div class="card-body p-0">
                <?php foreach ($eventosRecorrentes as $evento): ?>
                    <div class="evento-ranking-item">
                        <span><?php echo esc($evento['nome']); ?></span>
                        <span class="badge bg-success"><?php echo $evento['total_participantes_recorrentes']; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Atribuir Conquista -->
<div class="modal fade" id="modalConquista" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bx bx-trophy me-2"></i>Atribuir Conquista</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="conquista_user_id">
                <p class="mb-3">Atribuindo conquista para: <strong id="conquista_user_nome"></strong></p>
                
                <div class="mb-3">
                    <label class="form-label">Selecione a Conquista</label>
                    <select class="form-select" id="conquista_id" required>
                        <option value="">Carregando conquistas...</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Pontos (opcional - deixe em branco para usar os pontos padrão da conquista)</label>
                    <input type="number" class="form-control" id="conquista_pontos" placeholder="Pontos">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="atribuirConquista()">
                    <i class="bx bx-trophy me-1"></i>Atribuir
                </button>
            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<!-- DataTables -->
<script src="<?php echo site_url('recursos/theme/'); ?>plugins/datatable/js/jquery.dataTables.min.js"></script>
<script src="<?php echo site_url('recursos/theme/'); ?>plugins/datatable/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#tabelaParticipantes').DataTable({
        ordering: false,
        language: {
            url: '<?php echo site_url("recursos/theme/plugins/datatable/js/pt-BR.json"); ?>'
        },
        pageLength: 25
    });
    
    // Inicializar popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl, {
            container: 'body',
            sanitize: false
        });
    });
    
    // Fechar popover ao clicar fora
    document.addEventListener('click', function(e) {
        if (!e.target.hasAttribute('data-bs-toggle')) {
            popoverList.forEach(function(popover) {
                popover.hide();
            });
        }
    });
    
    // Carregar conquistas ao abrir modal
    carregarConquistas();
});

function filtrarPorTipo() {
    var tipo = $('#filtroTipo').val();
    var url = '<?php echo site_url("relatorios/participantes-concursos"); ?>';
    if (tipo) {
        url += '?tipo=' + encodeURIComponent(tipo);
    }
    window.location.href = url;
}
var conquistasCarregadas = false;

function carregarConquistas() {
    if (conquistasCarregadas) return;
    
    $.ajax({
        url: '<?php echo site_url("conquistas/buscarConquistas"); ?>',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            var select = $('#conquista_id');
            select.empty();
            select.append('<option value="">Selecione uma conquista...</option>');
            
            if (data && data.length > 0) {
                data.forEach(function(conquista) {
                    select.append('<option value="' + conquista.id + '" data-pontos="' + conquista.pontos + '">' + 
                        conquista.nome_conquista + ' (' + conquista.pontos + ' pts)</option>');
                });
                conquistasCarregadas = true;
            } else {
                select.append('<option value="">Nenhuma conquista disponível</option>');
            }
        },
        error: function() {
            $('#conquista_id').html('<option value="">Erro ao carregar conquistas</option>');
        }
    });
}

function abrirModalConquista(userId, userName) {
    $('#conquista_user_id').val(userId);
    $('#conquista_user_nome').text(userName);
    $('#conquista_pontos').val('');
    $('#conquista_id').val('');
    
    var modal = new bootstrap.Modal(document.getElementById('modalConquista'));
    modal.show();
}

var csrfToken = '<?php echo csrf_hash(); ?>';

function atribuirConquista() {
    var userId = $('#conquista_user_id').val();
    var conquistaId = $('#conquista_id').val();
    var pontos = $('#conquista_pontos').val();
    
    if (!conquistaId) {
        alert('Por favor, selecione uma conquista!');
        return;
    }
    
    // Se não informou pontos, pegar os pontos padrão da conquista
    if (!pontos) {
        pontos = $('#conquista_id option:selected').data('pontos');
    }
    
    var postData = {
        user_id: userId,
        conquista_id: conquistaId,
        pontos: pontos
    };
    postData['<?php echo csrf_token(); ?>'] = csrfToken;
    
    $.ajax({
        url: '<?php echo site_url("conquistas/atribuirConquista"); ?>',
        type: 'POST',
        data: postData,
        dataType: 'json',
        success: function(response) {
            // Renovar token
            if (response.token) {
                csrfToken = response.token;
            }
            
            if (response.sucesso) {
                alert(response.sucesso);
                bootstrap.Modal.getInstance(document.getElementById('modalConquista')).hide();
            } else if (response.erro) {
                alert('Erro: ' + response.erro);
            }
        },
        error: function() {
            alert('Erro ao atribuir conquista. Tente novamente.');
        }
    });
}
</script>
<?php echo $this->endSection() ?>
