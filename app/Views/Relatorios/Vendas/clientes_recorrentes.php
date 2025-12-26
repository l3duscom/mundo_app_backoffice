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
                <li class="breadcrumb-item active" aria-current="page">Clientes Recorrentes</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<!-- Cards estatísticos -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card purple">
            <h3><?php echo number_format($estatisticas['total_clientes_recorrentes'], 0, ',', '.'); ?></h3>
            <p><i class="bx bx-group"></i> Clientes Recorrentes</p>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card green">
            <h3><?php echo $estatisticas['media_eventos_por_cliente']; ?></h3>
            <p><i class="bx bx-calendar-event"></i> Média Eventos/Cliente</p>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card orange">
            <h3><?php echo number_format($estatisticas['total_ingressos_recorrentes'], 0, ',', '.'); ?></h3>
            <p><i class="bx bx-ticket"></i> Total Ingressos</p>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card blue">
            <h3>R$ <?php echo number_format($estatisticas['valor_total_recorrentes'], 0, ',', '.'); ?></h3>
            <p><i class="bx bx-money"></i> Valor Total</p>
        </div>
    </div>
</div>

<div class="row">
    <!-- Tabela Principal -->
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bx bx-trophy me-2 text-warning"></i>Ranking de Clientes - Múltiplos Eventos</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tabelaClientes" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">#</th>
                                <th>Cliente</th>
                                <th class="text-center" style="width: 50px;">WhatsApp</th>
                                <th class="text-center">Eventos</th>
                                <th class="text-center">Ingressos</th>
                                <th class="text-end">Valor Total</th>
                                <th class="text-center">Primeira</th>
                                <th class="text-center">Última</th>
                                <th class="text-center" style="width: 60px;">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $posicao = 1; ?>
                            <?php foreach ($clientes as $cliente): ?>
                                <?php 
                                // Limpar telefone para WhatsApp (só números, com código do país)
                                $telefone = preg_replace('/[^0-9]/', '', $cliente['telefone'] ?? '');
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
                                        <strong><?php echo esc($cliente['nome']); ?></strong><br>
                                        <small class="text-muted"><?php echo esc($cliente['email']); ?></small>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($telefone)): ?>
                                            <a href="https://wa.me/<?php echo $telefone; ?>" target="_blank" class="whatsapp-btn" title="<?php echo esc($cliente['telefone']); ?>">
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
                                           data-bs-content="<?php echo esc(str_replace(', ', '<br>', $cliente['eventos_participados'])); ?>">
                                            <?php echo $cliente['total_eventos']; ?> <i class="bx bx-search-alt-2" style="font-size:10px;"></i>
                                        </a>
                                    </td>
                                    <td class="text-center"><?php echo number_format($cliente['total_ingressos'], 0, ',', '.'); ?></td>
                                    <td class="text-end text-success fw-bold">R$ <?php echo number_format($cliente['valor_total'], 2, ',', '.'); ?></td>
                                    <td class="text-center"><?php echo date('d/m/Y', strtotime($cliente['primeira_compra'])); ?></td>
                                    <td class="text-center"><?php echo date('d/m/Y', strtotime($cliente['ultima_compra'])); ?></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-warning btn-sm" 
                                                onclick="abrirModalConquista(<?php echo $cliente['user_id']; ?>, '<?php echo esc($cliente['nome']); ?>')" 
                                                title="Atribuir Conquista">
                                            <i class="bx bx-trophy"></i>
                                        </button>
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
    <div class="col-lg-3">
        <!-- Ranking de Eventos -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bx bx-calendar-star me-2"></i>Top Eventos (Recorrência)</h6>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($eventosRecorrentes)): ?>
                    <?php $pos = 1; foreach ($eventosRecorrentes as $evento): ?>
                        <div class="evento-ranking-item">
                            <span>
                                <?php if ($pos <= 3): ?><i class="bx bx-medal text-warning"></i><?php endif; ?>
                                <strong><?php echo $pos; ?>º</strong> 
                                <?php echo esc(mb_substr($evento['nome'], 0, 20)); ?><?php echo strlen($evento['nome']) > 20 ? '...' : ''; ?>
                            </span>
                            <span class="badge bg-success"><?php echo $evento['total_clientes_recorrentes']; ?></span>
                        </div>
                    <?php $pos++; endforeach; ?>
                <?php else: ?>
                    <div class="p-3 text-center text-muted">Nenhum dado</div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Distribuição -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bx bx-pie-chart-alt me-2"></i>Distribuição por Eventos</h6>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($distribuicao)): ?>
                    <?php foreach ($distribuicao as $qtdEventos => $qtdClientes): ?>
                        <div class="distribuicao-item">
                            <span>
                                <span class="badge bg-primary"><?php echo $qtdEventos; ?></span> 
                                eventos
                            </span>
                            <span class="fw-bold"><?php echo $qtdClientes; ?> clientes</span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-3 text-center text-muted">Nenhum dado</div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recordista -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bx bx-star me-2"></i>Recordista</h6>
            </div>
            <div class="card-body text-center">
                <?php if (!empty($clientes)): ?>
                    <?php $recordista = $clientes[0]; ?>
                    <i class="bx bx-trophy text-warning" style="font-size: 3rem;"></i>
                    <h5 class="mt-2 mb-1"><?php echo esc($recordista['nome']); ?></h5>
                    <p class="text-muted mb-2"><?php echo esc($recordista['email']); ?></p>
                    <span class="badge bg-primary fs-5"><?php echo $recordista['total_eventos']; ?> eventos</span>
                    <p class="mt-2 mb-0 text-success fw-bold">R$ <?php echo number_format($recordista['valor_total'], 2, ',', '.'); ?></p>
                    <?php 
                    $telefoneRecordista = preg_replace('/[^0-9]/', '', $recordista['telefone'] ?? '');
                    if (!empty($telefoneRecordista) && strlen($telefoneRecordista) >= 10 && substr($telefoneRecordista, 0, 2) !== '55') {
                        $telefoneRecordista = '55' . $telefoneRecordista;
                    }
                    if (!empty($telefoneRecordista)): 
                    ?>
                        <a href="https://wa.me/<?php echo $telefoneRecordista; ?>" target="_blank" class="btn btn-success btn-sm mt-2">
                            <i class="bx bxl-whatsapp me-1"></i>WhatsApp
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted">Nenhum cliente recorrente</p>
                <?php endif; ?>
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
    $('#tabelaClientes').DataTable({
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
    
    $.ajax({
        url: '<?php echo site_url("conquistas/atribuirConquista"); ?>',
        type: 'POST',
        data: {
            user_id: userId,
            conquista_id: conquistaId,
            pontos: pontos,
            '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>'
        },
        dataType: 'json',
        success: function(response) {
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
