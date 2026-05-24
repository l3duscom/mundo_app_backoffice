<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<link href="<?php echo site_url('recursos/theme/'); ?>plugins/datatable/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
<style>
    .stat-card {
        padding: 1rem;
        border-radius: 12px;
        color: white;
        text-align: center;
        height: 100%;
    }
    .stat-card.purple { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .stat-card.gray   { background: linear-gradient(135deg, #6c757d 0%, #495057 100%); }
    .stat-card.blue   { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .stat-card.red    { background: linear-gradient(135deg, #f5576c 0%, #d62b3f 100%); }
    .stat-card.green  { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
    .stat-card h3 { font-size: 1.8rem; font-weight: bold; margin: 0; }
    .stat-card p { margin: 0.3rem 0 0; opacity: 0.9; font-size: 0.85rem; }
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
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Marketing</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Recuperação de Leads</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="get" action="<?= site_url('/recuperacao-leads') ?>" class="row g-3 align-items-end">
            <div class="col-md-8">
                <label class="form-label">Evento de origem (clientes que compraram nele)</label>
                <select name="evento_origem_id" class="form-select" required>
                    <option value="">Selecione um evento...</option>
                    <?php foreach ($eventos as $ev) : ?>
                        <option value="<?= $ev->id ?>" <?= $eventoOrigemId === (int) $ev->id ? 'selected' : '' ?>>
                            <?= esc($ev->nome) ?>
                            <?php if (! empty($ev->data_inicio)) : ?>
                                — <?= date('d/m/Y', strtotime($ev->data_inicio)) ?>
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bx bx-search"></i> Buscar leads
                </button>
            </div>
        </form>

        <div class="mt-3 text-muted small">
            Evento atual (destino): <strong><?= esc($eventoDestino->nome) ?></strong> &mdash;
            mostraremos quem comprou no evento de origem mas <strong>ainda não comprou</strong> aqui.
        </div>
    </div>
</div>

<?php if ($eventoOrigem) : ?>
    <div class="row mt-3">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card purple">
                <h3><?= number_format($estatisticas['total'], 0, ',', '.') ?></h3>
                <p><i class="bx bx-user"></i> Leads a recuperar</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card gray">
                <h3><?= number_format($estatisticas['sem_status'], 0, ',', '.') ?></h3>
                <p><i class="bx bx-time"></i> Sem ação</p>
            </div>
        </div>
        <div class="col-lg-2 col-md-6 mb-3">
            <div class="stat-card blue">
                <h3><?= number_format($estatisticas['contato_feito'], 0, ',', '.') ?></h3>
                <p><i class="bx bx-message-detail"></i> Contato feito</p>
            </div>
        </div>
        <div class="col-lg-2 col-md-6 mb-3">
            <div class="stat-card red">
                <h3><?= number_format($estatisticas['rejeitado'], 0, ',', '.') ?></h3>
                <p><i class="bx bx-x-circle"></i> Rejeitados</p>
            </div>
        </div>
        <div class="col-lg-2 col-md-6 mb-3">
            <div class="stat-card green">
                <h3><?= number_format($estatisticas['revertido'], 0, ',', '.') ?></h3>
                <p><i class="bx bx-check-circle"></i> Revertidos</p>
            </div>
        </div>
    </div>

    <div class="card mt-2">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bx bx-target-lock me-2"></i>
                Leads de <strong><?= esc($eventoOrigem->nome) ?></strong>
                que não compraram em <strong><?= esc($eventoDestino->nome) ?></strong>
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($leads)) : ?>
                <div class="alert alert-info mb-0">
                    Nenhum lead a recuperar — todos os clientes do evento de origem já compraram aqui.
                </div>
            <?php else : ?>
                <div class="table-responsive">
                    <table id="tabelaLeads" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th class="text-center" style="width: 50px;">WA</th>
                                <th class="text-center">Ingressos</th>
                                <th class="text-end">Valor origem</th>
                                <th class="text-center">Última compra</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" style="width: 80px;">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leads as $lead) : ?>
                                <?php
                                $telefone = preg_replace('/[^0-9]/', '', $lead['telefone'] ?? '');
                                if (! empty($telefone) && strlen($telefone) >= 10 && substr($telefone, 0, 2) !== '55') {
                                    $telefone = '55' . $telefone;
                                }
                                $status = $lead['recuperacao_status'] ?? null;
                                $badgeClass = 'bg-secondary';
                                $badgeLabel = 'Sem ação';
                                if ($status === 'contato_feito') { $badgeClass = 'bg-info'; $badgeLabel = 'Contato feito'; }
                                if ($status === 'rejeitado')     { $badgeClass = 'bg-danger'; $badgeLabel = 'Rejeitado'; }
                                if ($status === 'revertido')     { $badgeClass = 'bg-success'; $badgeLabel = 'Revertido'; }
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($lead['nome']) ?></strong><br>
                                        <small class="text-muted"><?= esc($lead['email']) ?></small>
                                    </td>
                                    <td class="text-center">
                                        <?php if (! empty($telefone)) : ?>
                                            <a href="https://wa.me/<?= $telefone ?>" target="_blank" class="whatsapp-btn" title="<?= esc($lead['telefone']) ?>">
                                                <i class="bx bxl-whatsapp"></i>
                                            </a>
                                        <?php else : ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?= number_format($lead['total_ingressos_origem'], 0, ',', '.') ?></td>
                                    <td class="text-end text-success fw-bold">R$ <?= number_format($lead['valor_total_origem'], 2, ',', '.') ?></td>
                                    <td class="text-center"><?= date('d/m/Y', strtotime($lead['ultima_compra_origem'])) ?></td>
                                    <td class="text-center">
                                        <span class="badge <?= $badgeClass ?>"><?= $badgeLabel ?></span>
                                        <?php if (! empty($lead['recuperacao_observacao'])) : ?>
                                            <i class="bx bx-note text-muted" title="<?= esc($lead['recuperacao_observacao']) ?>"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-primary"
                                                onclick="abrirModalStatus(<?= (int) $lead['user_id'] ?>, '<?= esc(addslashes($lead['nome'])) ?>', '<?= esc($status ?? '') ?>', '<?= esc(addslashes($lead['recuperacao_observacao'] ?? '')) ?>')">
                                            <i class="bx bx-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div class="modal fade" id="modalStatus" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Atualizar status do lead</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal_user_id">
                <p class="mb-3">Lead: <strong id="modal_user_nome"></strong></p>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" id="modal_status">
                        <option value="">Sem ação</option>
                        <option value="contato_feito">Contato feito</option>
                        <option value="rejeitado">Rejeitado</option>
                        <option value="revertido">Revertido</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Observação (opcional)</label>
                    <textarea class="form-control" id="modal_observacao" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarStatus()">
                    <i class="bx bx-save me-1"></i>Salvar
                </button>
            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script src="<?php echo site_url('recursos/theme/'); ?>plugins/datatable/js/jquery.dataTables.min.js"></script>
<script src="<?php echo site_url('recursos/theme/'); ?>plugins/datatable/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    if ($('#tabelaLeads').length) {
        $('#tabelaLeads').DataTable({
            ordering: true,
            language: {
                url: '<?php echo site_url("recursos/theme/plugins/datatable/js/pt-BR.json"); ?>'
            },
            pageLength: 25,
            order: [[3, 'desc']]
        });
    }
});

var csrfToken = '<?php echo csrf_hash(); ?>';
var eventoOrigemIdAtual = <?= (int) $eventoOrigemId ?>;

function abrirModalStatus(userId, nome, status, observacao) {
    $('#modal_user_id').val(userId);
    $('#modal_user_nome').text(nome);
    $('#modal_status').val(status || '');
    $('#modal_observacao').val(observacao || '');

    var modal = new bootstrap.Modal(document.getElementById('modalStatus'));
    modal.show();
}

function salvarStatus() {
    var postData = {
        user_id: $('#modal_user_id').val(),
        evento_origem_id: eventoOrigemIdAtual,
        status: $('#modal_status').val(),
        observacao: $('#modal_observacao').val()
    };
    postData['<?php echo csrf_token(); ?>'] = csrfToken;

    $.ajax({
        url: '<?php echo site_url("recuperacao-leads/salvar"); ?>',
        type: 'POST',
        data: postData,
        dataType: 'json',
        success: function(response) {
            if (response.token) {
                csrfToken = response.token;
            }
            if (response.sucesso) {
                bootstrap.Modal.getInstance(document.getElementById('modalStatus')).hide();
                location.reload();
            } else if (response.erro) {
                alert('Erro: ' + response.erro);
            }
        },
        error: function() {
            alert('Erro ao salvar status.');
        }
    });
}
</script>
<?php echo $this->endSection() ?>
