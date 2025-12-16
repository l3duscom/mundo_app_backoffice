<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
    .step-indicator {
        display: flex;
        justify-content: center;
        margin-bottom: 30px;
    }
    .step {
        display: flex;
        align-items: center;
        color: #6c757d;
    }
    .step.active { color: #667eea; }
    .step.completed { color: #28a745; }
    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-right: 10px;
    }
    .step.active .step-number { background: #667eea; color: white; }
    .step.completed .step-number { background: #28a745; color: white; }
    .step-line {
        width: 60px;
        height: 2px;
        background: #e9ecef;
        margin: 0 15px;
    }
    .card-section { margin-bottom: 30px; }
    .pessoa-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        border: 1px solid #e9ecef;
    }
    .pessoa-card.responsavel { border-left: 4px solid #667eea; }
    .pessoa-card.funcionario { border-left: 4px solid #6c757d; }
    .pessoa-card.suplente { border-left: 4px solid #ffc107; }
    .veiculo-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        border: 1px solid #e9ecef;
        border-left: 4px solid #17a2b8;
    }
    .limite-info { font-size: 0.85rem; color: #6c757d; }
    .bloqueado-overlay {
        position: relative;
    }
    .bloqueado-overlay::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(255,255,255,0.7);
        border-radius: 8px;
    }
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3 text-muted">
            <a href="<?php echo site_url('/'); ?>">Dashboard</a>
        </div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active">Credenciamento</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px;">
        <div class="card-body py-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="text-white mb-1">Credenciamento</h4>
                    <p class="text-white-50 mb-0">Contrato: <?= esc($contrato->codigo) ?></p>
                </div>
                <?= $credenciamento->getBadgeStatus() ?>
            </div>
        </div>
    </div>

    <?php if (!$liberado) : ?>
    <!-- Bloqueado: Contrato não assinado -->
    <div class="alert alert-warning">
        <i class="bi bi-lock-fill me-2"></i>
        <strong>Credenciamento bloqueado.</strong> O contrato precisa estar assinado para liberar o credenciamento.
    </div>
    <?php elseif (!$dentroDoPrazo) : ?>
    <!-- Bloqueado: Prazo expirado -->
    <div class="alert alert-danger">
        <i class="bi bi-clock-fill me-2"></i>
        <strong>Prazo de edição encerrado.</strong> O credenciamento só pode ser editado até 5 dias antes do evento.
        <?php if ($evento) : ?>
        <br><small>Data do evento: <?= date('d/m/Y', strtotime($evento->data_inicio)) ?></small>
        <?php endif; ?>
    </div>
    <?php else : ?>
    <!-- Prazo -->
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>
        <?php if ($evento && $evento->data_inicio) : ?>
        <?php $prazoLimite = date('d/m/Y', strtotime('-5 days', strtotime($evento->data_inicio))); ?>
        Você pode editar o credenciamento até <strong><?= $prazoLimite ?></strong>.
        <?php else : ?>
        Preencha todos os dados do credenciamento.
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Indicador de Etapas -->
    <div class="step-indicator">
        <div class="step <?= !empty($veiculos) ? 'completed' : 'active' ?>">
            <div class="step-number">1</div>
            <span>Veículo</span>
        </div>
        <div class="step-line"></div>
        <div class="step <?= $responsavel ? 'completed' : (!empty($veiculos) ? 'active' : '') ?>">
            <div class="step-number">2</div>
            <span>Funcionários</span>
        </div>
        <div class="step-line"></div>
        <div class="step <?= !empty($suplentes) ? 'completed' : ($responsavel ? 'active' : '') ?>">
            <div class="step-number">3</div>
            <span>Suplentes</span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- ETAPA 1: VEÍCULO -->
            <div class="card shadow-sm card-section <?= !$podeEditar ? 'bloqueado-overlay' : '' ?>">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-truck text-info me-2"></i>Veículo</h5>
                    <span class="limite-info">Limite: 1 veículo</span>
                </div>
                <div class="card-body">
                    <?php if (!empty($veiculos)) : ?>
                    <?php foreach ($veiculos as $veiculo) : ?>
                    <div class="veiculo-card d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?= esc($veiculo->marca) ?> <?= esc($veiculo->modelo) ?></strong>
                            <span class="text-muted">- <?= esc($veiculo->cor) ?></span>
                            <span class="badge bg-secondary ms-2"><?= $veiculo->getPlacaFormatada() ?></span>
                        </div>
                        <?php if ($podeEditar) : ?>
                        <div>
                            <button class="btn btn-sm btn-outline-primary btn-editar-veiculo" data-id="<?= $veiculo->id ?>" data-marca="<?= esc($veiculo->marca) ?>" data-modelo="<?= esc($veiculo->modelo) ?>" data-cor="<?= esc($veiculo->cor) ?>" data-placa="<?= esc($veiculo->placa) ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-excluir-veiculo" data-id="<?= $veiculo->id ?>">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php else : ?>
                    <p class="text-muted mb-3">Nenhum veículo cadastrado.</p>
                    <?php endif; ?>

                    <?php if ($podeEditar && empty($veiculos)) : ?>
                    <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#modalVeiculo">
                        <i class="bi bi-plus-circle me-1"></i>Adicionar Veículo
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ETAPA 2: FUNCIONÁRIOS -->
            <div class="card shadow-sm card-section <?= !$podeEditar ? 'bloqueado-overlay' : '' ?>">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-people text-primary me-2"></i>Responsável e Funcionários</h5>
                    <?php if (!empty($limites['auto_responsavel_funcionario'])) : ?>
                    <span class="limite-info">Limite: <?= $limites['funcionarios'] ?> funcionário + responsável (conta como 2º funcionário)</span>
                    <?php else : ?>
                    <span class="limite-info">Limite: <?= $limites['funcionarios'] ?> funcionário(s)</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <!-- Responsável -->
                    <h6 class="text-muted mb-3">Responsável <span class="text-danger">*</span></h6>
                    <?php if ($responsavel) : ?>
                    <div class="pessoa-card responsavel d-flex justify-content-between align-items-center">
                        <div>
                            <?= $responsavel->getBadgeTipo() ?>
                            <strong class="ms-2"><?= esc($responsavel->nome) ?></strong>
                            <small class="text-muted d-block mt-1">CPF: <?= $responsavel->getCpfFormatado() ?> | RG: <?= esc($responsavel->rg) ?> | WhatsApp: <?= $responsavel->getWhatsappFormatado() ?></small>
                        </div>
                        <?php if ($podeEditar) : ?>
                        <div>
                            <button class="btn btn-sm btn-outline-primary btn-editar-pessoa" data-id="<?= $responsavel->id ?>" data-tipo="responsavel" data-nome="<?= esc($responsavel->nome) ?>" data-rg="<?= esc($responsavel->rg) ?>" data-cpf="<?= esc($responsavel->cpf) ?>" data-whatsapp="<?= esc($responsavel->whatsapp) ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php elseif ($podeEditar) : ?>
                    <div class="alert alert-warning py-2">
                        <i class="bi bi-exclamation-triangle me-1"></i> É obrigatório cadastrar um responsável.
                    </div>
                    <button class="btn btn-primary btn-adicionar-pessoa" data-tipo="responsavel">
                        <i class="bi bi-plus-circle me-1"></i>Cadastrar Responsável
                    </button>
                    <?php endif; ?>

                    <hr>

                    <!-- Funcionários -->
                    <h6 class="text-muted mb-3">Funcionários</h6>
                    <?php if (!empty($funcionarios)) : ?>
                    <?php foreach ($funcionarios as $func) : ?>
                    <div class="pessoa-card funcionario d-flex justify-content-between align-items-center">
                        <div>
                            <?= $func->getBadgeTipo() ?>
                            <strong class="ms-2"><?= esc($func->nome) ?></strong>
                            <small class="text-muted d-block mt-1">CPF: <?= $func->getCpfFormatado() ?> | RG: <?= esc($func->rg) ?> | WhatsApp: <?= $func->getWhatsappFormatado() ?></small>
                        </div>
                        <?php if ($podeEditar) : ?>
                        <div>
                            <button class="btn btn-sm btn-outline-primary btn-editar-pessoa" data-id="<?= $func->id ?>" data-tipo="funcionario" data-nome="<?= esc($func->nome) ?>" data-rg="<?= esc($func->rg) ?>" data-cpf="<?= esc($func->cpf) ?>" data-whatsapp="<?= esc($func->whatsapp) ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-excluir-pessoa" data-id="<?= $func->id ?>">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php else : ?>
                    <p class="text-muted">Nenhum funcionário cadastrado.</p>
                    <?php endif; ?>

                    <?php if ($podeEditar && count($funcionarios) < $limites['funcionarios']) : ?>
                    <button class="btn btn-outline-primary mt-2 btn-adicionar-pessoa" data-tipo="funcionario">
                        <i class="bi bi-plus-circle me-1"></i>Adicionar Funcionário
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ETAPA 3: SUPLENTES -->
            <div class="card shadow-sm card-section <?= !$podeEditar ? 'bloqueado-overlay' : '' ?>">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-person-plus text-warning me-2"></i>Suplentes</h5>
                    <span class="limite-info">Limite: <?= $limites['suplentes'] ?> suplente(s)</span>
                </div>
                <div class="card-body">
                    <?php if (!empty($suplentes)) : ?>
                    <?php foreach ($suplentes as $sup) : ?>
                    <div class="pessoa-card suplente d-flex justify-content-between align-items-center">
                        <div>
                            <?= $sup->getBadgeTipo() ?>
                            <strong class="ms-2"><?= esc($sup->nome) ?></strong>
                            <small class="text-muted d-block mt-1">CPF: <?= $sup->getCpfFormatado() ?> | RG: <?= esc($sup->rg) ?> | WhatsApp: <?= $sup->getWhatsappFormatado() ?></small>
                        </div>
                        <?php if ($podeEditar) : ?>
                        <div>
                            <button class="btn btn-sm btn-outline-primary btn-editar-pessoa" data-id="<?= $sup->id ?>" data-tipo="suplente" data-nome="<?= esc($sup->nome) ?>" data-rg="<?= esc($sup->rg) ?>" data-cpf="<?= esc($sup->cpf) ?>" data-whatsapp="<?= esc($sup->whatsapp) ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-excluir-pessoa" data-id="<?= $sup->id ?>">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php else : ?>
                    <p class="text-muted">Nenhum suplente cadastrado.</p>
                    <?php endif; ?>

                    <?php if ($podeEditar && count($suplentes) < $limites['suplentes']) : ?>
                    <button class="btn btn-outline-warning mt-2 btn-adicionar-pessoa" data-tipo="suplente">
                        <i class="bi bi-plus-circle me-1"></i>Adicionar Suplente
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Resumo Lateral -->
        <div class="col-lg-4">
            <div class="card shadow-sm" style="position: sticky; top: 20px;">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Resumo</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Veículo:</span>
                        <span class="<?= !empty($veiculos) ? 'text-success' : 'text-danger' ?>">
                            <?= count($veiculos) ?>/1
                            <?= !empty($veiculos) ? '<i class="bi bi-check-circle-fill"></i>' : '<i class="bi bi-x-circle-fill"></i>' ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Responsável:</span>
                        <span class="<?= $responsavel ? 'text-success' : 'text-danger' ?>">
                            <?= $responsavel ? '1/1' : '0/1' ?>
                            <?= $responsavel ? '<i class="bi bi-check-circle-fill"></i>' : '<i class="bi bi-x-circle-fill"></i>' ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Funcionários:</span>
                        <span class="text-muted"><?= count($funcionarios) ?>/<?= $limites['funcionarios'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Suplentes:</span>
                        <span class="text-muted"><?= count($suplentes) ?>/<?= $limites['suplentes'] ?></span>
                    </div>
                    <hr>
                    <div class="text-center">
                        <?= $credenciamento->getBadgeStatus() ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Veículo -->
<div class="modal fade" id="modalVeiculo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Veículo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formVeiculo">
                <div class="modal-body">
                    <input type="hidden" name="id" id="veiculo_id">
                    <input type="hidden" name="credenciamento_id" value="<?= $credenciamento->id ?>">
                    <input type="hidden" name="contrato_id" value="<?= $contrato->id ?>">
                    <div class="mb-3">
                        <label class="form-label">Marca <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="marca" id="veiculo_marca" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Modelo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="modelo" id="veiculo_modelo" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cor <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="cor" id="veiculo_cor" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Placa <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="placa" id="veiculo_placa" required maxlength="8" style="text-transform: uppercase;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Pessoa -->
<div class="modal fade" id="modalPessoa" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPessoaTitle">Pessoa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPessoa">
                <div class="modal-body">
                    <input type="hidden" name="id" id="pessoa_id">
                    <input type="hidden" name="tipo" id="pessoa_tipo">
                    <input type="hidden" name="credenciamento_id" value="<?= $credenciamento->id ?>">
                    <input type="hidden" name="contrato_id" value="<?= $contrato->id ?>">
                    <div class="mb-3">
                        <label class="form-label">Nome Completo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nome" id="pessoa_nome" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">RG <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="rg" id="pessoa_rg" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">CPF <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="cpf" id="pessoa_cpf" required maxlength="14">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">WhatsApp <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="whatsapp" id="pessoa_whatsapp" required maxlength="15">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script>
const URLS = {
    salvarVeiculo: '<?= site_url("credenciamento/salvarVeiculo") ?>',
    excluirVeiculo: '<?= site_url("credenciamento/excluirVeiculo") ?>',
    salvarPessoa: '<?= site_url("credenciamento/salvarPessoa") ?>',
    excluirPessoa: '<?= site_url("credenciamento/excluirPessoa") ?>',
};

// CSRF Token
const csrfName = '<?= csrf_token() ?>';
let csrfToken = '<?= csrf_hash() ?>';

// Veículo
document.getElementById('formVeiculo')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append(csrfName, csrfToken);
    
    try {
        const response = await fetch(URLS.salvarVeiculo, { method: 'POST', body: formData });
        const result = await response.json();
        
        if (result.success) {
            location.reload();
        } else {
            alert(result.message || 'Erro ao salvar veículo');
        }
    } catch (error) {
        console.error(error);
        alert('Erro ao salvar veículo');
    }
});

document.querySelectorAll('.btn-editar-veiculo').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('veiculo_id').value = this.dataset.id;
        document.getElementById('veiculo_marca').value = this.dataset.marca;
        document.getElementById('veiculo_modelo').value = this.dataset.modelo;
        document.getElementById('veiculo_cor').value = this.dataset.cor;
        document.getElementById('veiculo_placa').value = this.dataset.placa;
        new bootstrap.Modal(document.getElementById('modalVeiculo')).show();
    });
});

document.querySelectorAll('.btn-excluir-veiculo').forEach(btn => {
    btn.addEventListener('click', async function() {
        if (!confirm('Deseja excluir este veículo?')) return;
        
        try {
            const formData = new FormData();
            formData.append(csrfName, csrfToken);
            const response = await fetch(URLS.excluirVeiculo + '/' + this.dataset.id, { method: 'POST', body: formData });
            const result = await response.json();
            
            if (result.success) {
                location.reload();
            } else {
                alert(result.message || 'Erro ao excluir veículo');
            }
        } catch (error) {
            alert('Erro ao excluir veículo');
        }
    });
});

// Pessoas
const tipoLabels = { responsavel: 'Responsável', funcionario: 'Funcionário', suplente: 'Suplente' };

document.querySelectorAll('.btn-adicionar-pessoa').forEach(btn => {
    btn.addEventListener('click', function() {
        const tipo = this.dataset.tipo;
        document.getElementById('pessoa_id').value = '';
        document.getElementById('pessoa_tipo').value = tipo;
        document.getElementById('pessoa_nome').value = '';
        document.getElementById('pessoa_rg').value = '';
        document.getElementById('pessoa_cpf').value = '';
        document.getElementById('pessoa_whatsapp').value = '';
        document.getElementById('modalPessoaTitle').textContent = 'Cadastrar ' + tipoLabels[tipo];
        new bootstrap.Modal(document.getElementById('modalPessoa')).show();
    });
});

document.querySelectorAll('.btn-editar-pessoa').forEach(btn => {
    btn.addEventListener('click', function() {
        const tipo = this.dataset.tipo;
        document.getElementById('pessoa_id').value = this.dataset.id;
        document.getElementById('pessoa_tipo').value = tipo;
        document.getElementById('pessoa_nome').value = this.dataset.nome;
        document.getElementById('pessoa_rg').value = this.dataset.rg;
        document.getElementById('pessoa_cpf').value = this.dataset.cpf;
        document.getElementById('pessoa_whatsapp').value = this.dataset.whatsapp;
        document.getElementById('modalPessoaTitle').textContent = 'Editar ' + tipoLabels[tipo];
        new bootstrap.Modal(document.getElementById('modalPessoa')).show();
    });
});

document.getElementById('formPessoa')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append(csrfName, csrfToken);
    
    try {
        const response = await fetch(URLS.salvarPessoa, { method: 'POST', body: formData });
        const result = await response.json();
        
        if (result.success) {
            location.reload();
        } else {
            alert(result.message || 'Erro ao salvar dados');
        }
    } catch (error) {
        alert('Erro ao salvar dados');
    }
});

document.querySelectorAll('.btn-excluir-pessoa').forEach(btn => {
    btn.addEventListener('click', async function() {
        if (!confirm('Deseja excluir esta pessoa?')) return;
        
        try {
            const formData = new FormData();
            formData.append(csrfName, csrfToken);
            const response = await fetch(URLS.excluirPessoa + '/' + this.dataset.id, { method: 'POST', body: formData });
            const result = await response.json();
            
            if (result.success) {
                location.reload();
            } else {
                alert(result.message || 'Erro ao excluir pessoa');
            }
        } catch (error) {
            alert('Erro ao excluir pessoa');
        }
    });
});

// Máscara CPF
document.getElementById('pessoa_cpf')?.addEventListener('input', function(e) {
    let v = e.target.value.replace(/\D/g, '');
    if (v.length > 11) v = v.slice(0, 11);
    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    e.target.value = v;
});

// Máscara WhatsApp
document.getElementById('pessoa_whatsapp')?.addEventListener('input', function(e) {
    let v = e.target.value.replace(/\D/g, '');
    if (v.length > 11) v = v.slice(0, 11);
    v = v.replace(/(\d{2})(\d)/, '($1) $2');
    v = v.replace(/(\d{5})(\d)/, '$1-$2');
    e.target.value = v;
});
</script>
<?php echo $this->endSection() ?>
