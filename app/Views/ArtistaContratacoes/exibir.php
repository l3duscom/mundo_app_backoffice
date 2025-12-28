<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">
        <a href="<?php echo site_url('/artista-contratacoes'); ?>">Contratações</a>
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('/artistas'); ?>">Artistas</a></li>
                <li class="breadcrumb-item active"><?php echo esc($contratacao->codigo); ?></li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <?php if ($contratacao->situacao === 'rascunho'): ?>
        <button type="button" class="btn btn-success me-2" id="btnConfirmar">
            <i class="bx bx-check me-1"></i>Confirmar
        </button>
        <?php endif; ?>
        <a href="<?php echo site_url('artista-contratacoes'); ?>" class="btn btn-secondary">
            <i class="bx bx-arrow-back me-1"></i>Voltar
        </a>
    </div>
</div>

<div class="row">
    <!-- Coluna Esquerda - Info da Contratação -->
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-body text-center">
                <i class="bx bx-microphone" style="font-size: 3rem; color: var(--bs-primary);"></i>
                <h5 class="mt-2 mb-1"><?php echo esc($artista->nome_artistico ?? '-'); ?></h5>
                <p class="text-muted mb-2"><?php echo esc($artista->genero_musical ?? ''); ?></p>
                <?php echo $contratacao->exibeSituacao(); ?>
                <p class="mt-2 mb-0"><code><?php echo esc($contratacao->codigo); ?></code></p>
                
                <?php if ($artista->telefone || $artista->email): ?>
                <hr class="my-3">
                <div class="text-start">
                    <?php if ($artista->telefone): ?>
                    <p class="mb-1 small">
                        <i class="bx bx-phone me-1"></i><?php echo esc($artista->telefone); ?>
                        <?php 
                        $artistaWhatsapp = preg_replace('/\D/', '', $artista->telefone);
                        if ($artistaWhatsapp): 
                        ?>
                        <a href="https://wa.me/55<?php echo $artistaWhatsapp; ?>" target="_blank" class="btn btn-sm btn-success ms-2" title="WhatsApp do Artista">
                            <i class="bx bxl-whatsapp"></i>
                        </a>
                        <?php endif; ?>
                    </p>
                    <?php endif; ?>
                    <?php if ($artista->email): ?>
                    <p class="mb-0 small"><i class="bx bx-envelope me-1"></i><?php echo esc($artista->email); ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h6><i class="bx bx-calendar me-2"></i>Apresentação</h6>
                <ul class="list-unstyled mb-0">
                    <li><strong>Data:</strong> <?php echo $contratacao->getDataApresentacaoFormatada(); ?></li>
                    <li><strong>Horário:</strong> <?php echo $contratacao->getHorarioFormatado(); ?></li>
                    <li><strong>Palco:</strong> <?php echo esc($contratacao->palco ?? '-'); ?></li>
                </ul>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h6><i class="bx bx-wallet me-2"></i>Resumo Financeiro</h6>
                <table class="table table-sm mb-0">
                    <tr><td>Cachê</td><td class="text-end">R$ <?php echo number_format($totais['cache'], 2, ',', '.'); ?></td></tr>
                    <tr><td>Voos</td><td class="text-end">R$ <?php echo number_format($totais['voos'], 2, ',', '.'); ?></td></tr>
                    <tr><td>Hospedagens</td><td class="text-end">R$ <?php echo number_format($totais['hospedagens'], 2, ',', '.'); ?></td></tr>
                    <tr><td>Translados</td><td class="text-end">R$ <?php echo number_format($totais['translados'], 2, ',', '.'); ?></td></tr>
                    <tr><td>Alimentação</td><td class="text-end">R$ <?php echo number_format($totais['alimentacoes'], 2, ',', '.'); ?></td></tr>
                    <tr><td>Extras</td><td class="text-end">R$ <?php echo number_format($totais['extras'], 2, ',', '.'); ?></td></tr>
                    <tr class="table-primary"><th>TOTAL</th><th class="text-end">R$ <?php echo number_format($totais['total'], 2, ',', '.'); ?></th></tr>
                    <tr class="table-success"><td>Pago</td><td class="text-end">R$ <?php echo number_format($totais['pago'], 2, ',', '.'); ?></td></tr>
                    <tr class="table-warning"><td>Pendente</td><td class="text-end">R$ <?php echo number_format($totais['pendente'], 2, ',', '.'); ?></td></tr>
                </table>
            </div>
        </div>

        <!-- Agentes Vinculados -->
        <div class="card mb-3">
            <div class="card-body">
                <h6><i class="bx bx-user-voice me-2"></i>Agentes / Contatos</h6>
                <?php if (!empty($agentesVinculados)): ?>
                <ul class="list-unstyled mb-0">
                    <?php foreach ($agentesVinculados as $ag): ?>
                    <li class="mb-2 pb-2 border-bottom">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong><?php echo esc($ag->nome_fantasia ?: $ag->nome); ?></strong>
                                <span class="badge bg-secondary ms-1"><?php echo \App\Models\ArtistaAgenteModel::FUNCOES[$ag->funcao] ?? $ag->funcao; ?></span>
                                <?php if ($ag->principal): ?><span class="badge bg-primary ms-1">Principal</span><?php endif; ?>
                                <?php if ($ag->telefone): ?><br><small class="text-muted"><i class="bx bx-phone me-1"></i><?php echo esc($ag->telefone); ?></small><?php endif; ?>
                                <?php if ($ag->email): ?><br><small class="text-muted"><i class="bx bx-envelope me-1"></i><?php echo esc($ag->email); ?></small><?php endif; ?>
                            </div>
                            <div>
                                <?php 
                                $whatsapp = $ag->whatsapp ?: $ag->telefone;
                                if ($whatsapp): 
                                    $whatsappNumero = preg_replace('/\D/', '', $whatsapp);
                                ?>
                                <a href="https://wa.me/55<?php echo $whatsappNumero; ?>" target="_blank" class="btn btn-sm btn-success" title="WhatsApp">
                                    <i class="bx bxl-whatsapp"></i>
                                </a>
                                <?php endif; ?>
                                <a href="<?php echo site_url("agentes/exibir/{$ag->agente_id}"); ?>" class="btn btn-sm btn-outline-secondary" title="Ver Agente" target="_blank">
                                    <i class="bx bx-link-external"></i>
                                </a>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p class="text-muted text-center mb-0 small">Nenhum agente vinculado</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Coluna Direita - Custos -->
    <div class="col-lg-8">
        <!-- Parcelas do Cachê -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bx bx-money text-success me-2"></i>Parcelas do Cachê</h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($parcelas)): ?>
                <p class="text-muted text-center p-3 mb-0">Confirme a contratação para gerar as parcelas</p>
                <?php else: ?>
                <table class="table table-sm mb-0">
                    <thead><tr><th>#</th><th>Vencimento</th><th class="text-end">Valor</th><th>Status</th><th>Ações</th></tr></thead>
                    <tbody>
                        <?php foreach ($parcelas as $p): ?>
                        <tr>
                            <td><?php echo $p->numero_parcela; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($p->data_vencimento)); ?></td>
                            <td class="text-end">R$ <?php echo number_format($p->valor, 2, ',', '.'); ?></td>
                            <td><span class="badge bg-<?php echo $p->status === 'pago' ? 'success' : 'warning'; ?>"><?php echo ucfirst($p->status); ?></span></td>
                            <td>
                                <?php if ($p->status !== 'pago'): ?>
                                <button class="btn btn-sm btn-success btn-pagar-parcela" data-id="<?php echo $p->id; ?>"><i class="bx bx-check"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Voos -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bx bx-plane text-info me-2"></i>Voos</h6>
                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#modalVoo"><i class="bx bx-plus"></i></button>
            </div>
            <div class="card-body p-0">
                <?php if (empty($voos)): ?>
                <p class="text-muted text-center p-3 mb-0">Nenhum voo cadastrado</p>
                <?php else: ?>
                <table class="table table-sm mb-0">
                    <thead><tr><th>Tipo</th><th>Voo</th><th>Rota</th><th>Data</th><th class="text-end">Valor</th><th>Status</th><th>Ações</th></tr></thead>
                    <tbody>
                        <?php foreach ($voos as $v): ?>
                        <tr>
                            <td><span class="badge bg-<?php echo $v->tipo === 'ida' ? 'primary' : 'secondary'; ?>"><?php echo ucfirst($v->tipo); ?></span></td>
                            <td><?php echo esc($v->numero_voo ?? '-'); ?></td>
                            <td><?php echo esc($v->origem ?? ''); ?> → <?php echo esc($v->destino ?? ''); ?></td>
                            <td><?php echo $v->data_embarque ? date('d/m/Y', strtotime($v->data_embarque)) : '-'; ?></td>
                            <td class="text-end">R$ <?php echo number_format($v->valor, 2, ',', '.'); ?></td>
                            <td><span class="badge bg-<?php echo $v->status === 'pago' ? 'success' : 'warning'; ?>"><?php echo ucfirst($v->status); ?></span></td>
                            <td>
                                <?php if ($v->status !== 'pago'): ?>
                                <button class="btn btn-sm btn-success btn-pagar-custo" data-tipo="voo" data-id="<?php echo $v->id; ?>"><i class="bx bx-check"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Hospedagens -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bx bx-building-house text-warning me-2"></i>Hospedagens</h6>
                <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalHospedagem"><i class="bx bx-plus"></i></button>
            </div>
            <div class="card-body p-0">
                <?php if (empty($hospedagens)): ?>
                <p class="text-muted text-center p-3 mb-0">Nenhuma hospedagem cadastrada</p>
                <?php else: ?>
                <table class="table table-sm mb-0">
                    <thead><tr><th>Hotel</th><th>Check-in</th><th>Check-out</th><th class="text-end">Valor</th><th>Status</th><th>Ações</th></tr></thead>
                    <tbody>
                        <?php foreach ($hospedagens as $h): ?>
                        <tr>
                            <td><?php echo esc($h->hotel); ?></td>
                            <td><?php echo $h->data_checkin ? date('d/m/Y H:i', strtotime($h->data_checkin)) : '-'; ?></td>
                            <td><?php echo $h->data_checkout ? date('d/m/Y H:i', strtotime($h->data_checkout)) : '-'; ?></td>
                            <td class="text-end">R$ <?php echo number_format($h->valor_total, 2, ',', '.'); ?></td>
                            <td><span class="badge bg-<?php echo $h->status === 'pago' ? 'success' : 'warning'; ?>"><?php echo ucfirst($h->status); ?></span></td>
                            <td>
                                <?php if ($h->status !== 'pago'): ?>
                                <button class="btn btn-sm btn-success btn-pagar-custo" data-tipo="hospedagem" data-id="<?php echo $h->id; ?>"><i class="bx bx-check"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Translados -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bx bx-car text-primary me-2"></i>Translados</h6>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalTranslado"><i class="bx bx-plus"></i></button>
            </div>
            <div class="card-body p-0">
                <?php if (empty($translados)): ?>
                <p class="text-muted text-center p-3 mb-0">Nenhum translado cadastrado</p>
                <?php else: ?>
                <table class="table table-sm mb-0">
                    <thead><tr><th>Tipo</th><th>Rota</th><th>Data</th><th class="text-end">Valor</th><th>Status</th><th>Ações</th></tr></thead>
                    <tbody>
                        <?php foreach ($translados as $t): ?>
                        <tr>
                            <td><?php echo $tiposTranslado[$t->tipo] ?? $t->tipo; ?></td>
                            <td><?php echo esc($t->origem ?? ''); ?> → <?php echo esc($t->destino ?? ''); ?></td>
                            <td><?php echo $t->data_translado ? date('d/m/Y H:i', strtotime($t->data_translado)) : '-'; ?></td>
                            <td class="text-end">R$ <?php echo number_format($t->valor, 2, ',', '.'); ?></td>
                            <td><span class="badge bg-<?php echo $t->status === 'pago' ? 'success' : 'warning'; ?>"><?php echo ucfirst($t->status); ?></span></td>
                            <td>
                                <?php if ($t->status !== 'pago'): ?>
                                <button class="btn btn-sm btn-success btn-pagar-custo" data-tipo="translado" data-id="<?php echo $t->id; ?>"><i class="bx bx-check"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Alimentação -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bx bx-restaurant text-secondary me-2"></i>Alimentação</h6>
                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalAlimentacao"><i class="bx bx-plus"></i></button>
            </div>
            <div class="card-body p-0">
                <?php if (empty($alimentacoes)): ?>
                <p class="text-muted text-center p-3 mb-0">Nenhuma alimentação cadastrada</p>
                <?php else: ?>
                <table class="table table-sm mb-0">
                    <thead><tr><th>Tipo</th><th>Local</th><th>Data</th><th class="text-end">Valor</th><th>Status</th><th>Ações</th></tr></thead>
                    <tbody>
                        <?php foreach ($alimentacoes as $a): ?>
                        <tr>
                            <td><?php echo $tiposAlimentacao[$a->tipo] ?? $a->tipo; ?></td>
                            <td><?php echo esc($a->local ?? '-'); ?></td>
                            <td><?php echo $a->data ? date('d/m/Y H:i', strtotime($a->data)) : '-'; ?></td>
                            <td class="text-end">R$ <?php echo number_format($a->valor_total, 2, ',', '.'); ?></td>
                            <td><span class="badge bg-<?php echo $a->status === 'pago' ? 'success' : 'warning'; ?>"><?php echo ucfirst($a->status); ?></span></td>
                            <td>
                                <?php if ($a->status !== 'pago'): ?>
                                <button class="btn btn-sm btn-success btn-pagar-custo" data-tipo="alimentacao" data-id="<?php echo $a->id; ?>"><i class="bx bx-check"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Custos Extras -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bx bx-plus-circle text-dark me-2"></i>Custos Extras</h6>
                <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#modalExtra"><i class="bx bx-plus"></i></button>
            </div>
            <div class="card-body p-0">
                <?php if (empty($extras)): ?>
                <p class="text-muted text-center p-3 mb-0">Nenhum custo extra cadastrado</p>
                <?php else: ?>
                <table class="table table-sm mb-0">
                    <thead><tr><th>Descrição</th><th>Data</th><th class="text-end">Valor</th><th>Status</th><th>Ações</th></tr></thead>
                    <tbody>
                        <?php foreach ($extras as $e): ?>
                        <tr>
                            <td><?php echo esc($e->descricao); ?></td>
                            <td><?php echo $e->data ? date('d/m/Y', strtotime($e->data)) : '-'; ?></td>
                            <td class="text-end">R$ <?php echo number_format($e->valor, 2, ',', '.'); ?></td>
                            <td><span class="badge bg-<?php echo $e->status === 'pago' ? 'success' : 'warning'; ?>"><?php echo ucfirst($e->status); ?></span></td>
                            <td>
                                <?php if ($e->status !== 'pago'): ?>
                                <button class="btn btn-sm btn-success btn-pagar-custo" data-tipo="extra" data-id="<?php echo $e->id; ?>"><i class="bx bx-check"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Anexos -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bx bx-paperclip text-secondary me-2"></i>Documentos / Anexos</h6>
                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalAnexo"><i class="bx bx-upload me-1"></i>Enviar</button>
            </div>
            <div class="card-body">
                <?php if (empty($anexos)): ?>
                <p class="text-muted text-center mb-0">Nenhum anexo enviado</p>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($anexos as $anexo): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center" id="anexo-<?php echo $anexo->id; ?>">
                        <div class="d-flex align-items-center">
                            <i class="<?php echo $anexo->getIcone(); ?> fs-4 me-3"></i>
                            <div>
                                <p class="mb-0"><?php echo esc($anexo->nome_arquivo); ?></p>
                                <small class="text-muted"><?php echo $anexo->getTamanhoFormatado(); ?></small>
                            </div>
                        </div>
                        <div>
                            <a href="<?php echo site_url("artista-contratacoes/visualizarAnexo/{$anexo->id}"); ?>" class="btn btn-sm btn-outline-secondary" target="_blank" title="Abrir">
                                <i class="bx bx-link-external"></i>
                            </a>
                            <a href="<?php echo site_url("artista-contratacoes/downloadAnexo/{$anexo->id}"); ?>" class="btn btn-sm btn-outline-primary" title="Baixar">
                                <i class="bx bx-download"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-danger btn-remover-anexo" data-id="<?php echo $anexo->id; ?>" title="Remover">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Anexo -->
<div class="modal fade" id="modalAnexo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="bx bx-upload me-2"></i>Enviar Anexo</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="formAnexo" enctype="multipart/form-data">
                    <input type="hidden" name="<?php echo csrf_token(); ?>" value="<?php echo csrf_hash(); ?>">
                    <input type="hidden" name="contratacao_id" value="<?php echo $contratacao->id; ?>">
                    <div class="mb-3">
                        <label class="form-label">Arquivo (PDF ou Imagem)</label>
                        <input type="file" name="arquivo" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.gif,.webp" required>
                        <small class="text-muted">Máximo: 10MB</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarAnexo"><i class="bx bx-upload me-1"></i>Enviar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Voo -->
<div class="modal fade" id="modalVoo" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formVoo">
                <input type="hidden" name="<?php echo csrf_token(); ?>" value="<?php echo csrf_hash(); ?>">
                <input type="hidden" name="contratacao_id" value="<?php echo $contratacao->id; ?>">
                <div class="modal-header"><h5 class="modal-title">Adicionar Voo</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3 mb-3"><label>Tipo</label><select name="tipo" class="form-select"><option value="ida">Ida</option><option value="volta">Volta</option></select></div>
                        <div class="col-md-3 mb-3"><label>Companhia</label><input type="text" name="companhia" class="form-control"></div>
                        <div class="col-md-3 mb-3"><label>Nº Voo</label><input type="text" name="numero_voo" class="form-control"></div>
                        <div class="col-md-3 mb-3"><label>Localizador</label><input type="text" name="localizador" class="form-control"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Origem</label><input type="text" name="origem" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label>Destino</label><input type="text" name="destino" class="form-control"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3"><label>Data</label><input type="date" name="data_embarque" class="form-control"></div>
                        <div class="col-md-3 mb-3"><label>Embarque</label><input type="time" name="horario_embarque" class="form-control"></div>
                        <div class="col-md-3 mb-3"><label>Chegada</label><input type="time" name="horario_chegada" class="form-control"></div>
                        <div class="col-md-3 mb-3"><label>Valor</label><input type="text" name="valor" class="form-control money" placeholder="0,00"></div>
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

<!-- Modal Hospedagem -->
<div class="modal fade" id="modalHospedagem" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formHospedagem">
                <input type="hidden" name="<?php echo csrf_token(); ?>" value="<?php echo csrf_hash(); ?>">
                <input type="hidden" name="contratacao_id" value="<?php echo $contratacao->id; ?>">
                <div class="modal-header"><h5 class="modal-title">Adicionar Hospedagem</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Hotel</label><input type="text" name="hotel" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label>Código Reserva</label><input type="text" name="codigo_reserva" class="form-control"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Check-in</label><input type="datetime-local" name="data_checkin" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label>Check-out</label><input type="datetime-local" name="data_checkout" class="form-control"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3"><label>Tipo Quarto</label><input type="text" name="tipo_quarto" class="form-control"></div>
                        <div class="col-md-4 mb-3"><label>Valor Diária</label><input type="text" name="valor_diaria" class="form-control money" placeholder="0,00"></div>
                        <div class="col-md-4 mb-3"><label>Valor Total</label><input type="text" name="valor_total" class="form-control money" placeholder="0,00"></div>
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

<!-- Modal Translado -->
<div class="modal fade" id="modalTranslado" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formTranslado">
                <input type="hidden" name="<?php echo csrf_token(); ?>" value="<?php echo csrf_hash(); ?>">
                <input type="hidden" name="contratacao_id" value="<?php echo $contratacao->id; ?>">
                <div class="modal-header"><h5 class="modal-title">Adicionar Translado</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label>Tipo</label><select name="tipo" class="form-select">
                        <?php foreach ($tiposTranslado as $k => $v): ?><option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php endforeach; ?>
                    </select></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Origem</label><input type="text" name="origem" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label>Destino</label><input type="text" name="destino" class="form-control"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Data/Hora</label><input type="datetime-local" name="data_translado" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label>Valor</label><input type="text" name="valor" class="form-control money" placeholder="0,00"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Motorista</label><input type="text" name="motorista" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label>Tel Motorista</label><input type="text" name="telefone_motorista" class="form-control"></div>
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

<!-- Modal Alimentação -->
<div class="modal fade" id="modalAlimentacao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formAlimentacao">
                <input type="hidden" name="<?php echo csrf_token(); ?>" value="<?php echo csrf_hash(); ?>">
                <input type="hidden" name="contratacao_id" value="<?php echo $contratacao->id; ?>">
                <div class="modal-header"><h5 class="modal-title">Adicionar Alimentação</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label>Tipo</label><select name="tipo" class="form-select">
                        <?php foreach ($tiposAlimentacao as $k => $v): ?><option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php endforeach; ?>
                    </select></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Local</label><input type="text" name="local" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label>Data/Hora</label><input type="datetime-local" name="data" class="form-control"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3"><label>Pessoas</label><input type="number" name="quantidade_pessoas" class="form-control" value="1"></div>
                        <div class="col-md-4 mb-3"><label>Valor/Pessoa</label><input type="text" name="valor_pessoa" class="form-control money" placeholder="0,00"></div>
                        <div class="col-md-4 mb-3"><label>Valor Total</label><input type="text" name="valor_total" class="form-control money" placeholder="0,00" readonly></div>
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

<!-- Modal Extra -->
<div class="modal fade" id="modalExtra" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formExtra">
                <input type="hidden" name="<?php echo csrf_token(); ?>" value="<?php echo csrf_hash(); ?>">
                <input type="hidden" name="contratacao_id" value="<?php echo $contratacao->id; ?>">
                <div class="modal-header"><h5 class="modal-title">Adicionar Custo Extra</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label>Descrição</label><input type="text" name="descricao" class="form-control" required></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Data</label><input type="date" name="data" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label>Valor</label><input type="text" name="valor" class="form-control money" placeholder="0,00" required></div>
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
var contratacaoId = <?php echo $contratacao->id; ?>;
var csrfName = '<?php echo csrf_token(); ?>';
var csrfHash = '<?php echo csrf_hash(); ?>';

$(document).ready(function() {
    // Máscara de moeda
    function formatMoney(value) {
        value = value.replace(/\D/g, '');
        if (!value) return '';
        value = (parseInt(value) / 100).toFixed(2);
        return value.replace('.', ',');
    }
    
    function parseMoney(value) {
        if (!value) return 0;
        return parseFloat(value.replace('.', '').replace(',', '.')) || 0;
    }
    
    // Aplicar máscara nos campos de valor
    $(document).on('input', '.money', function() {
        $(this).val(formatMoney($(this).val()));
    });
    
    // Cálculo automático de valor total na Alimentação
    $('#formAlimentacao input[name="quantidade_pessoas"], #formAlimentacao input[name="valor_pessoa"]').on('input', function() {
        var qtd = parseInt($('#formAlimentacao input[name="quantidade_pessoas"]').val()) || 0;
        var valorPessoa = parseMoney($('#formAlimentacao input[name="valor_pessoa"]').val());
        var total = (qtd * valorPessoa).toFixed(2).replace('.', ',');
        $('#formAlimentacao input[name="valor_total"]').val(total);
    });
    
    // Confirmar contratação
    $('#btnConfirmar').click(function() {
        if (!confirm('Confirmar esta contratação? As parcelas do cachê serão geradas.')) return;
        
        $.post('<?php echo site_url("artista-contratacoes/confirmar"); ?>', {
            [csrfName]: csrfHash, id: contratacaoId
        }, function(r) {
            csrfHash = r.token;
            if (r.erro) { alert(r.erro); return; }
            location.reload();
        }, 'json');
    });

    // Pagar parcela
    $('.btn-pagar-parcela').click(function() {
        if (!confirm('Marcar como paga?')) return;
        var id = $(this).data('id');
        
        $.post('<?php echo site_url("artista-contratacoes/pagarParcela"); ?>', {
            [csrfName]: csrfHash, parcela_id: id
        }, function(r) {
            csrfHash = r.token;
            if (r.erro) { alert(r.erro); return; }
            location.reload();
        }, 'json');
    });

    // Pagar custo
    $('.btn-pagar-custo').click(function() {
        if (!confirm('Marcar como pago?')) return;
        var tipo = $(this).data('tipo');
        var id = $(this).data('id');
        
        $.post('<?php echo site_url("artista-contratacoes/pagarCusto"); ?>', {
            [csrfName]: csrfHash, tipo: tipo, custo_id: id, contratacao_id: contratacaoId
        }, function(r) {
            csrfHash = r.token;
            if (r.erro) { alert(r.erro); return; }
            location.reload();
        }, 'json');
    });

    // Forms de custos - converter valores antes de enviar
    $('#formVoo, #formHospedagem, #formTranslado, #formAlimentacao, #formExtra').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var action = form.attr('id').replace('form', 'adicionar');
        
        // Converter valores monetários para formato numérico
        form.find('.money').each(function() {
            var val = parseMoney($(this).val());
            $(this).val(val);
        });
        
        $.post('<?php echo site_url("artista-contratacoes/"); ?>' + action, form.serialize(), function(r) {
            if (r.erro) { alert(r.erro); return; }
            location.reload();
        }, 'json');
    });

    // Upload anexo
    $('#btnSalvarAnexo').click(function() {
        var form = $('#formAnexo')[0];
        var formData = new FormData(form);
        var btn = $(this);
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        
        $.ajax({
            url: '<?php echo site_url("artista-contratacoes/uploadAnexo"); ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                csrfHash = response.token;
                if (response.erro) {
                    alert(response.erro);
                    btn.prop('disabled', false).html('<i class="bx bx-upload me-1"></i>Enviar');
                    return;
                }
                location.reload();
            },
            error: function() {
                alert('Erro ao enviar arquivo');
                btn.prop('disabled', false).html('<i class="bx bx-upload me-1"></i>Enviar');
            }
        });
    });

    // Remover anexo
    $('.btn-remover-anexo').click(function() {
        var anexoId = $(this).data('id');
        
        if (!confirm('Remover este anexo?')) return;
        
        $.post('<?php echo site_url("artista-contratacoes/removerAnexo"); ?>', {
            [csrfName]: csrfHash, anexo_id: anexoId
        }, function(r) {
            csrfHash = r.token;
            if (r.erro) { alert(r.erro); return; }
            $('#anexo-' + anexoId).fadeOut(300, function() { $(this).remove(); });
        }, 'json');
    });
});
</script>
<?php echo $this->endSection() ?>

