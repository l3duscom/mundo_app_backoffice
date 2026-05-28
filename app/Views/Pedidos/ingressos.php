<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>


<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.css') ?>" />

<style>
@keyframes pulse {
    0% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.7; transform: scale(1.05); }
    100% { opacity: 1; transform: scale(1); }
}

.badge.bg-danger[style*="pulse"] {
    box-shadow: 0 0 10px rgba(220, 53, 69, 0.5);
}
</style>

<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>


<div class="row">


    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">
            Ingressos do Pedido <strong><?= $pedido->cod_pedido ?></strong>
            <?php if (!empty($pedido->cupom_codigo)) : ?>
                <span class="badge bg-success ms-2" title="<?= esc($pedido->cupom_nome ?? '') ?>">
                    <i class="bx bx-purchase-tag"></i>
                    Cupom: <?= esc($pedido->cupom_codigo) ?>
                    <?php if (!empty($pedido->valor_desconto) && (float) $pedido->valor_desconto > 0) : ?>
                        &mdash; R$ <?= number_format((float) $pedido->valor_desconto, 2, ',', '.') ?>
                    <?php endif; ?>
                </span>
            <?php endif; ?>
        </div>
        <div class="ms-auto">
            <div class="btn-group" style="padding-right: 30px;">
                <?php if ($pedido->frete == 1) : ?>
                    <a href="#" class="btn btn-primary mt-0 shadow" data-bs-toggle="modal" data-bs-target="#participanteModal"><i class="bx bx-user" style="padding-right: 5px;"></i> Enviar pedido</a>
                <?php endif ?>
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Imprimir
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= site_url('/ingressos/gerarEtiqueta/' . $pedido->id) ?>" target="_blank"><strong>Etiqueta</strong></a></li>
                        <li><a class="dropdown-item" href="<?= site_url('/ingressos/gerarEticket/' . $pedido->id) ?>" target="_blank">E-ticket</a></li>
                        <li><a class="dropdown-item" href="<?= site_url('/ingressos/gerarEticketGratis/' . $pedido->id) ?>" target="_blank">Cortesia</a></li>
                        <li><a class="dropdown-item" href="<?= site_url('/ingressos/gerarEticketPromo/' . $pedido->id) ?>" target="_blank">Promocional</a></li>
                    </ul>
                </div>
            </div>

        </div>
    </div>

    <!--end breadcrumb-->
    <div class="ms-auto">

    </div>
    <?php $ingresso_id = null ?>

    <div class="row">

        <?php if ($ingressos != null) : ?>
            <?php foreach ($ingressos as $i) : ?>
                <div class="col-lg-12">

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card shadow radius-10">
                                <div class="card-body">

                                    <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                                        <div class="breadcrumb-title pe-3"><strong class="font-20"><?= $i->nome_evento ?></strong></div>
                                        <div class="ps-3">
                                            <nav aria-label="breadcrumb">
                                                <ol class="breadcrumb mb-0 p-0">
                                                    <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-calendar-star"></i></a>
                                                    </li>
                                                    <li class="breadcrumb-item active" aria-current="page">
                                                        <?= date('d/m/Y', strtotime($i->data_inicio)) ?> - <?= date('d/m/Y', strtotime($i->data_fim)) ?>

                                                    </li>
                                                </ol>
                                            </nav>
                                        </div>

                                    </div>
                                    <div class="row">


                                        <hr class="mt-2">
                                        <div class="col-lg-8">
                                            <p class="mb-0 text-muted" style="font-size: 10px;">Ingresso</p>

                                            <strong><?= $i->nome ?></strong>
                                        </div>
                                        <div class="col-lg-2">
                                            <p class="mb-0 text-muted" style="font-size: 10px;">Código do ingresso</p>
                                            <strong><?= $i->codigo ?></strong>
                                        </div>
                                        <div class="col-lg-2">
                                            <p class="mb-0 text-muted" style="font-size: 10px;">Último Acesso</p>
                                            <?php if (isset($ultimoAcessoPorIngresso[$i->id]) && $ultimoAcessoPorIngresso[$i->id]): ?>
                                                <?php $ultimoAcesso = $ultimoAcessoPorIngresso[$i->id]; ?>
                                                <span class="badge bg-success">
                                                    <i class="bx bx-check-circle me-1"></i>
                                                    <?= date('d/m/Y H:i', strtotime($ultimoAcesso->created_at)) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger fs-6" style="animation: pulse 1.5s infinite;">
                                                    <i class="bx bx-error-circle me-1"></i>Não utilizado
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-lg-12 mt-3"></div>
                                        <div class="col-lg-3">
                                            <p class="mb-0 text-muted" style="font-size: 10px;">Nome</p>
                                            <?php if ($i->participante == null) : ?>
                                                <strong><?= $i->nome_cliente ?></strong>
                                            <?php else : ?>
                                                <strong><?= $i->participante ?></strong>
                                            <?php endif ?>

                                        </div>

                                        <div class="col-lg-3">
                                            <p class="mb-0 text-muted" style="font-size: 10px;">Acesso</p>
                                            <strong><?= $i->frete == null || $i->frete == 0 ? "Retirar no local" : "Receber em casa" ?></strong>
                                        </div>


                                        <div class="col-lg-3">
                                            <p class="mb-0 text-muted" style="font-size: 10px;">Comprovante</p>
                                            <strong>
                                                <?php if ($i->comprovante != null) : ?>
                                                    <strong><a href="<?= $i->comprovante ?>" target="_blank">Baixar comprovante</a></strong>
                                                <?php else : ?>
                                                    <strong>Indiponível</strong>
                                                <?php endif ?>
                                            </strong>

                                        </div>

                                    </div>
                                    <hr class="mt-2">

                                    <?php 
                                    $bonus_cinemark = null;
                                    if (isset($bonus_por_ingresso[$i->id])) {
                                        foreach ($bonus_por_ingresso[$i->id] as $bonus) {
                                            if ($bonus->tipo_bonus === 'cinemark') {
                                                $bonus_cinemark = $bonus;
                                                break;
                                            }
                                        }
                                    }
                                    ?>
                                    <?php if ($bonus_cinemark != null) : ?>
                                        <strong style="color: #ffcc00">Seu ingresso CINEMARK já está disponível!</strong><br>
                                        <small class="text-muted">Como usar o Cinemark Voucher:</small><br>
                                        <?= nl2br(esc($bonus_cinemark->instrucoes)) ?><br>
                                        - Após isso, digite o código <strong style="font-size: 16px; color: #ffcc00"><?= esc($bonus_cinemark->codigo) ?></strong> do voucher que irá utilizar.<br>
                                        <span class="badge bg-success mt-2" style="font-size: 13px;"><i class="bi bi-check-circle-fill me-1"></i>Validade de 20 dias</span>
                                        <hr class="mt-2">
                                    <?php endif ?>


                                    <div class="col-lg-12">

                                        <a href="<?= site_url('/ingressos/vincular/' . $i->id) ?>" class="btn  btn-success mt-0 shadow">Vincular pulsiera RFID</a>
                                        <a href="<?= site_url('/ingressos/cinemark/' . $i->id) ?>" class="btn <?= $bonus_cinemark ? 'btn-warning' : 'btn-primary' ?> mt-0 shadow"><?= $bonus_cinemark ? 'Editar Cinemark' : 'Adicionar Cinemark' ?></a>
                                        <button type="button" class="btn btn-outline-info mt-0 shadow btn-trocar-dia"
                                                data-ingresso-id="<?= $i->id ?>"
                                                data-ingresso-nome="<?= esc($i->nome) ?>">
                                            <i class="bx bx-transfer-alt"></i> Trocar dia
                                        </button>


                                    </div>

                                    <?php $trocasDoIngresso = $trocasPorIngresso[$i->id] ?? []; ?>
                                    <?php if (!empty($trocasDoIngresso)) : ?>
                                        <hr class="mt-3">
                                        <div class="mt-3">
                                            <h6><i class="bx bx-transfer-alt me-2"></i>Histórico de Trocas de Dia</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>Data/Hora</th>
                                                            <th>De</th>
                                                            <th>Para</th>
                                                            <th>Operador</th>
                                                            <th>Motivo</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($trocasDoIngresso as $tr) : ?>
                                                            <tr>
                                                                <td><i class="bx bx-calendar me-1"></i><?= date('d/m/Y H:i', strtotime($tr->created_at)) ?></td>
                                                                <td><?= esc($tr->nome_anterior ?? '-') ?></td>
                                                                <td><strong><?= esc($tr->nome_novo) ?></strong></td>
                                                                <td><?= esc($tr->operador_nome ?? 'Sistema') ?></td>
                                                                <td><?= esc($tr->motivo ?? '-') ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Histórico de Acessos -->
                                    <?php if (isset($acessosPorIngresso[$i->id]) && !empty($acessosPorIngresso[$i->id])): ?>
                                    <hr class="mt-3">
                                    <div class="mt-3">
                                        <h6><i class="bx bx-log-in-circle me-2"></i>Histórico de Acessos</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Data/Hora</th>
                                                        <th>Tipo</th>
                                                        <th>Operador</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($acessosPorIngresso[$i->id] as $acesso): ?>
                                                    <tr>
                                                        <td>
                                                            <i class="bx bx-calendar me-1"></i>
                                                            <?= date('d/m/Y H:i:s', strtotime($acesso->created_at)) ?>
                                                        </td>
                                                        <td>
                                                            <?php 
                                                            $tipoClass = 'bg-info';
                                                            if ($acesso->tipo_acesso == 'ACESSO') $tipoClass = 'bg-success';
                                                            if ($acesso->tipo_acesso == 'SAIDA') $tipoClass = 'bg-warning';
                                                            ?>
                                                            <span class="badge <?= $tipoClass ?>"><?= esc($acesso->tipo_acesso ?? 'ACESSO') ?></span>
                                                        </td>
                                                        <td><?= esc($acesso->operador_nome ?? 'Sistema') ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>




                    </div>




                <?php endforeach; ?>

            <!-- Seção de Order Bumps -->
            <?php if (!empty($orderBumps)) : ?>
                <div class="col-lg-12 mt-4">
                    <div class="card shadow radius-10 border-start border-3 border-primary">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div>
                                    <h5 class="mb-0">
                                        <i class="bx bx-gift me-2 text-primary"></i>Order Bumps do Pedido
                                    </h5>
                                    <small class="text-muted">Produtos/serviços adicionais adquiridos</small>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Produto</th>
                                            <th class="text-center">Qtd</th>
                                            <th class="text-end">Preço Unit.</th>
                                            <th class="text-end">Total</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orderBumps as $ob) : ?>
                                            <tr id="orderbump-row-<?= $ob->id ?>">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if (!empty($ob->imagem)) : ?>
                                                            <img src="<?= site_url('uploads/order_bumps/' . $ob->imagem) ?>" 
                                                                 alt="<?= esc($ob->nome) ?>" 
                                                                 class="rounded me-2" 
                                                                 style="width: 40px; height: 40px; object-fit: cover;">
                                                        <?php else : ?>
                                                            <div class="rounded me-2 d-flex align-items-center justify-content-center bg-light" 
                                                                 style="width: 40px; height: 40px;">
                                                                <i class="bx bx-package text-muted"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div>
                                                            <strong><?= esc($ob->nome) ?></strong>
                                                            <?php if (!empty($ob->descricao)) : ?>
                                                                <br><small class="text-muted"><?= esc(substr($ob->descricao, 0, 50)) ?><?= strlen($ob->descricao) > 50 ? '...' : '' ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <span class="badge bg-secondary"><?= $ob->quantidade ?></span>
                                                </td>
                                                <td class="text-end align-middle"><?= $ob->getPrecoFormatado() ?></td>
                                                <td class="text-end align-middle fw-bold"><?= $ob->getTotalFormatado() ?></td>
                                                <td class="text-center align-middle" id="orderbump-status-<?= $ob->id ?>">
                                                    <?= $ob->exibeStatusUsado() ?>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <?php if (!$ob->usado) : ?>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-success btn-marcar-usado" 
                                                                data-id="<?= $ob->id ?>"
                                                                data-nome="<?= esc($ob->nome) ?>"
                                                                title="Marcar como usado">
                                                            <i class="bx bx-check-circle"></i> Marcar usado
                                                        </button>
                                                    <?php else : ?>
                                                        <span class="text-success">
                                                            <i class="bx bx-check-double"></i> Utilizado
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

                <hr>
                <h5>Todos os ingressos e adicionais deste usuário</h5>
                <?php foreach ($todos as $t) : ?>

                    <div class="col-lg-12">

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card shadow radius-10">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-3">
                                                <p class="mb-0 text-muted" style="font-size: 10px;">Pedido</p>

                                                <strong><?= $t->cod_pedido ?></strong>

                                            </div>
                                            <div class="col-lg-3">
                                                <p class="mb-0 text-muted" style="font-size: 10px;">Status Entrega</p>

                                                <strong><?= $t->status_entrega ?></strong>

                                            </div>
                                            <div class="col-lg-3">
                                                <p class="mb-0 text-muted" style="font-size: 10px;">Rastreio</p>

                                                <strong><?= $t->rastreio ?></strong>

                                            </div>
                                            <div class="col-lg-3">
                                                <p class="mb-0 text-muted" style="font-size: 10px;">Ingresso</p>

                                                <strong><?= $t->nome ?></strong>

                                            </div>
                                            <div class="col-lg-3">
                                                <p class="mb-0 text-muted" style="font-size: 10px;">Codigo</p>

                                                <strong><?= $t->codigo ?></strong>

                                            </div>
                                            <div class="col-lg-3">
                                                <p class="mb-0 text-muted" style="font-size: 10px;">Status</p>

                                                <strong><?= $t->status ?></strong>

                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>

                <center>
                    <hr>
                    <p>Nenhum ingresso encontrado</p>

                    <a href="<?= site_url('/carrinho') ?>" target="_blank" class="btn btn-primary">Comprar ingresso!</a>
                    <hr>
                </center>

            <?php endif; ?>

                </div>


    </div>
</div>

<!-- Modal -->
<div class="modal fade bd-example-modal" id="participanteModal" tabindex="-1" role="dialog" aria-labelledby="participanteModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="participante>Modal">Gerenciamento de pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">


                <?php echo form_open("pedidos/rastreio/$pedido->id") ?>
                <?= csrf_field() ?>
                <input type="hidden" name="pedido_id" value="<?php echo $pedido->id ?>">

                <div class="form-group col-md-12">
                    <label class="form-control-label">Informe o Código de rastreio</label>
                    <input type="text" name="rastreio" class="form-control form-control-lg" placeholder="Código de rastreio" value="<?php if ($pedido->rastreio) echo esc($pedido->rastreio); ?>">
                </div>
                <p class="text-muted font-13">Ao atualizar o código, o cliente receberá uma notificação, o status do pedido mudará para enviado e o botão de acompanhar entrega será habilitado.</p>
                <hr>
                <?php if ($endereco != null) : ?>
                    Nome: <strong><?= $cliente->nome ?></strong><br>
                    CPF: <strong><?= $cliente->cpf ?></strong><br>
                    Rua: <strong><?= $endereco->endereco ?></strong><br>
                    Nº/comp: <strong><?= $endereco->numero ?></strong><br>
                    Cidade: <strong><?= $endereco->cidade ?> </strong><br>
                    Estado: <strong><?= $endereco->estado ?> </strong><br>
                    CEP: <strong><?= $endereco->cep ?></strong>
                <?php else : ?>
                    Nome: <strong><?= $cliente->nome ?></strong><br>
                    CPF: <strong><?= $cliente->cpf ?></strong><br>
                    Rua: <strong><?= $cliente->endereco ?></strong><br>
                    Nº/comp: <strong><?= $cliente->numero ?></strong><br>
                    Cidade: <strong><?= $cliente->cidade ?> </strong><br>
                    Estado: <strong><?= $cliente->estado ?> </strong><br>
                    CEP: <strong><?= $cliente->cep ?></strong>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <input id="btn-salvar" type="submit" value="Enviar pedido" class="btn btn-primary btn-sm">
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>




<!-- Modal -->
<div class="modal fade bd-example-modal" id="credencialModal" tabindex="-1" role="dialog" aria-labelledby="credencialModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="credencialModal>Modal">Vinculo de credencial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <?= $pedido->id ?>
                <?php echo form_open("pedidos/rastreio/$pedido->id") ?>
                <?= csrf_field() ?>

                <div class="form-group col-md-12">
                    <label class="form-control-label">Informe o Código de rastreio</label>
                    <input type="text" name="participante" class="form-control form-control-lg" placeholder="Código de rastreio">
                </div>
                <p class="text-muted font-13">Ao atualizar o código, o cliente receberá uma notificação, o status do pedido mudará para enviado e o botão de acompanhar entrega será habilitado.</p>
                <hr>
                <?php if ($endereco != null) : ?>
                    Rua <?= $endereco->endereco ?> Nº <?= $endereco->numero ?>, <?= $endereco->cidade ?> - <?= $endereco->estado ?> | <strong><?= $endereco->cep ?></strong>
                <?php else : ?>
                    Rua <?= $cliente->endereco ?> Nº <?= $cliente->numero ?>, <?= $cliente->cidade ?> - <?= $cliente->estado ?> | <strong><?= $cliente->cep ?></strong>

                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <input id="btn-salvar" type="submit" value="Enviar pedido" class="btn btn-primary btn-sm">
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>


<!-- Modal: Trocar dia do ingresso -->
<div class="modal fade" id="trocaDiaModal" tabindex="-1" role="dialog" aria-labelledby="trocaDiaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="trocaDiaModalLabel">Trocar dia do ingresso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="troca-dia-loading" class="text-center py-4" style="display:none;">
                    <i class="bx bx-loader-alt bx-spin font-22"></i> Carregando opções...
                </div>
                <div id="troca-dia-erro" class="alert alert-danger" style="display:none;"></div>
                <div id="troca-dia-conteudo" style="display:none;">
                    <p class="mb-2">
                        Ingresso atual: <strong id="troca-dia-nome-atual"></strong>
                        <span class="badge bg-secondary ms-1" id="troca-dia-dia-atual"></span>
                    </p>
                    <p class="text-muted small mb-3">
                        A troca só é permitida entre ingressos do mesmo evento, tipo e categoria.
                    </p>
                    <input type="hidden" id="troca-dia-ingresso-id">
                    <div class="mb-3">
                        <label class="form-label">Selecione o novo dia</label>
                        <div id="troca-dia-opcoes" class="list-group"></div>
                        <div id="troca-dia-vazio" class="alert alert-warning mt-2" style="display:none;">
                            Nenhum ticket disponível para troca com os mesmos filtros.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Motivo (opcional)</label>
                        <textarea id="troca-dia-motivo" class="form-control" rows="2" placeholder="Ex: cliente solicitou alteração via WhatsApp"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-sm" id="btn-troca-dia-confirmar" disabled>
                    <i class="bx bx-check"></i> Confirmar troca
                </button>
            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>


<?php echo $this->section('scripts') ?>


<script type="text/javascript" src="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.js') ?>"></script>



<script>
    $(document).ready(function() {


        const DATATABLE_PTBR = {
            "sEmptyTable": "Nenhum registro encontrado",
            "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
            "sInfoFiltered": "(Filtrados de _MAX_ registros)",
            "sInfoPostFix": "",
            "sInfoThousands": ".",
            "sLengthMenu": "_MENU_ resultados por página",
            "sLoadingRecords": "Carregando...",
            "sProcessing": "Processando...",
            "sZeroRecords": "Nenhum registro encontrado",
            "sSearch": "Pesquisar",
            "oPaginate": {
                "sNext": "Próximo",
                "sPrevious": "Anterior",
                "sFirst": "Primeiro",
                "sLast": "Último"
            },
            "oAria": {
                "sSortAscending": ": Ordenar colunas de forma ascendente",
                "sSortDescending": ": Ordenar colunas de forma descendente"
            },
            "select": {
                "rows": {
                    "_": "Selecionado %d linhas",
                    "0": "Nenhuma linha selecionada",
                    "1": "Selecionado 1 linha"
                }
            }
        }


        $(' #ajaxTable').DataTable({
            "oLanguage": DATATABLE_PTBR,
            "ajax": "<?php echo site_url('declarations/recuperaDeclaracoesPorUsuario'); ?>",
            "columns": [{
                "data": "nome"
            }, {
                "data": "month"
            }, {
                "data": "type"
            }, {
                "data": "status"
            }, {
                "data": "created_at"
            }, ],
            "order": [],
            "deferRender": true,
            "processing": true,
            "language": {
                processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',
            },
            "responsive": true,
            "pagingType": $(window).width() < 768 ? "simple" : "simple_numbers",
        });
    });
</script>

<script>
    $(document).ready(function() {




        $("#form_participante").on('submit', function(e) {


            e.preventDefault();


            $.ajax({

                type: 'POST',
                url: '<?php echo site_url('ingressos/atualizar'); ?>',
                data: new FormData(this),
                dataType: 'json',
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function() {

                    $("#response").html('');
                    $("#btn-salvar").val('Por favor aguarde...');

                },
                success: function(response) {

                    $("#btn-salvar").val('Salvar');
                    $("#btn-salvar").removeAttr("disabled");

                    $('[name=csrf_ordem]').val(response.token);


                    if (!response.erro) {


                        if (response.info) {

                            $("#response").html('<div class="alert alert-info">' + response
                                .info + '</div>');

                        } else {

                            // Tudo certo com a atualização do usuário
                            // Podemos agora redirecioná-lo tranquilamente

                            window.location.href =
                                "<?php echo site_url("ingressos/"); ?>";

                        }

                    }

                    if (response.erro) {

                        // Exitem erros de validação


                        $("#response").html('<div class="alert alert-danger">' + response.erro +
                            '</div>');


                        if (response.erros_model) {


                            $.each(response.erros_model, function(key, value) {

                                $("#response").append(
                                    '<ul class="list-unstyled"><li class="text-danger">' +
                                    value + '</li></ul>');

                            });

                        }

                    }

                },
                error: function() {

                    alert(
                        'Não foi possível procesar a solicitação. Por favor entre em contato com o suporte técnico.'
                    );
                    $("#btn-salvar").val('Salvar');
                    $("#btn-salvar").removeAttr("disabled");

                }



            });


        });


        $("#form").submit(function() {

            $(this).find(":submit").attr('disabled', 'disabled');

        });

        // ===== Troca de dia do ingresso =====
        const trocaDiaModalEl = document.getElementById('trocaDiaModal');
        const trocaDiaModal = trocaDiaModalEl ? new bootstrap.Modal(trocaDiaModalEl) : null;
        let trocaDiaTicketSelecionado = null;

        function trocaDiaResetModal() {
            $('#troca-dia-erro').hide().text('');
            $('#troca-dia-loading').hide();
            $('#troca-dia-conteudo').hide();
            $('#troca-dia-vazio').hide();
            $('#troca-dia-opcoes').empty();
            $('#troca-dia-motivo').val('');
            $('#btn-troca-dia-confirmar').prop('disabled', true);
            trocaDiaTicketSelecionado = null;
        }

        $('.btn-trocar-dia').on('click', function() {
            const ingressoId = $(this).data('ingresso-id');
            const ingressoNome = $(this).data('ingresso-nome');

            trocaDiaResetModal();
            $('#troca-dia-ingresso-id').val(ingressoId);
            $('#troca-dia-nome-atual').text(ingressoNome);
            $('#troca-dia-loading').show();
            trocaDiaModal.show();

            $.ajax({
                type: 'GET',
                url: '<?= site_url('pedidos/trocaListar/') ?>' + ingressoId,
                dataType: 'json',
                success: function(response) {
                    $('#troca-dia-loading').hide();

                    if (response.erro) {
                        $('#troca-dia-erro').text(response.erro).show();
                        return;
                    }

                    if (response.ticket_atual && response.ticket_atual.dia) {
                        $('#troca-dia-dia-atual').text('Dia atual: ' + response.ticket_atual.dia).show();
                    } else {
                        $('#troca-dia-dia-atual').hide();
                    }

                    const $opcoes = $('#troca-dia-opcoes').empty();
                    if (!response.disponiveis || response.disponiveis.length === 0) {
                        $('#troca-dia-vazio').show();
                    } else {
                        response.disponiveis.forEach(function(t) {
                            const diaTxt = t.dia ? ' <span class="badge bg-info ms-2">' + t.dia + '</span>' : '';
                            const loteTxt = t.lote ? ' <small class="text-muted ms-2">(lote ' + t.lote + ')</small>' : '';
                            const $btn = $('<button type="button" class="list-group-item list-group-item-action troca-dia-opcao" data-ticket-id="' + t.id + '">'
                                + '<strong>' + t.nome + '</strong>' + diaTxt + loteTxt
                                + '</button>');
                            $opcoes.append($btn);
                        });
                    }

                    $('#troca-dia-conteudo').show();
                },
                error: function() {
                    $('#troca-dia-loading').hide();
                    $('#troca-dia-erro').text('Erro ao carregar tickets disponíveis.').show();
                }
            });
        });

        $('#troca-dia-opcoes').on('click', '.troca-dia-opcao', function() {
            $('.troca-dia-opcao').removeClass('active');
            $(this).addClass('active');
            trocaDiaTicketSelecionado = $(this).data('ticket-id');
            $('#btn-troca-dia-confirmar').prop('disabled', false);
        });

        $('#btn-troca-dia-confirmar').on('click', function() {
            const ingressoId = $('#troca-dia-ingresso-id').val();
            const motivo = $('#troca-dia-motivo').val();
            const $btn = $(this);

            if (!trocaDiaTicketSelecionado) {
                return;
            }

            if (!confirm('Confirmar a troca do ingresso para o ticket selecionado?')) {
                return;
            }

            $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Processando...');

            $.ajax({
                type: 'POST',
                url: '<?= site_url('pedidos/trocaExecutar') ?>',
                data: {
                    ingresso_id: ingressoId,
                    ticket_id_novo: trocaDiaTicketSelecionado,
                    motivo: motivo,
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.erro) {
                        $('#troca-dia-erro').text(response.erro).show();
                        $btn.prop('disabled', false).html('<i class="bx bx-check"></i> Confirmar troca');
                        return;
                    }
                    location.reload();
                },
                error: function() {
                    $('#troca-dia-erro').text('Erro ao processar a troca. Tente novamente.').show();
                    $btn.prop('disabled', false).html('<i class="bx bx-check"></i> Confirmar troca');
                }
            });
        });

        // Marcar Order Bump como usado
        $(".btn-marcar-usado").on('click', function() {
            var btn = $(this);
            var id = btn.data('id');
            var nome = btn.data('nome');
            
            // Primeira confirmação
            if (!confirm('Deseja marcar "' + nome + '" como usado?')) {
                return;
            }
            
            // Segunda confirmação (ação irreversível)
            if (!confirm('ATENÇÃO: Esta ação NÃO pode ser desfeita. Confirma?')) {
                return;
            }
            
            btn.prop('disabled', true);
            btn.html('<i class="bx bx-loader-alt bx-spin"></i> Processando...');
            
            $.ajax({
                type: 'POST',
                url: '<?= site_url("pedidos/marcarOrderBumpUsado/") ?>' + id,
                data: {
                    pedido_id: <?= $pedido->id ?>,
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                },
                dataType: 'json',
                success: function(response) {
                    if (!response.erro) {
                        // Atualizar status
                        $('#orderbump-status-' + id).html(
                            '<span class="badge bg-success"><i class="bx bx-check-circle me-1"></i>Usado em ' + response.usado_em + '</span>'
                        );
                        
                        // Substituir botão por texto
                        btn.closest('td').html(
                            '<span class="text-success"><i class="bx bx-check-double"></i> Utilizado</span>'
                        );
                        
                        // Feedback visual
                        $('#orderbump-row-' + id).addClass('table-success');
                        setTimeout(function() {
                            $('#orderbump-row-' + id).removeClass('table-success');
                        }, 3000);
                    } else {
                        alert('Erro: ' + response.mensagem);
                        btn.prop('disabled', false);
                        btn.html('<i class="bx bx-check-circle"></i> Marcar usado');
                    }
                },
                error: function() {
                    alert('Erro ao processar a solicitação. Tente novamente.');
                    btn.prop('disabled', false);
                    btn.html('<i class="bx bx-check-circle"></i> Marcar usado');
                }
            });
        });

    });
</script>

<?php echo $this->endSection() ?>