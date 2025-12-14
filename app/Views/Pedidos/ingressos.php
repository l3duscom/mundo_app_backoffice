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
        <div class="breadcrumb-title pe-3">Ingressos do Pedido <strong><?= $pedido->cod_pedido ?></strong></div>
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

                                    <?php if ($i->cinemark != null) : ?>
                                        <strong>Seu ingresso CINEMARK já está disponível!</strong><br>
                                        Como usar o Cinemark Voucher:<br>
                                        1 - Atualize ou baixe o APP Cinemark no Google Play ou APP Store.<br>
                                        2 - Faça seu login, selecione o cinema, filme de sua preferência.<br>
                                        3 - Selecione o horário da sessão e os assentos;<br>
                                        4 - Selecione o tipo de ingresso como Voucher e quantidade de ingressos que irá utilizar;<br>
                                        5 - Após isso, digite o código <strong style="font-size: 16px;"> <?= $i->cinemark ?></strong> do voucher que irá utilizar.<br>
                                        6 - Apresente seu voucher online no celular diretamente na entrada da sala do cinema.<br>
                                        <hr class="mt-2">
                                    <?php endif ?>


                                    <div class="col-lg-12">

                                        <a href="<?= site_url('/ingressos/vincular/' . $i->id) ?>" class="btn  btn-success mt-0 shadow">Vincular pulsiera RFID</a>
                                        <a href="<?= site_url('/ingressos/cinemark/' . $i->id) ?>" class="btn  btn-primary mt-0 shadow">Adicionar Cinemark</a>


                                    </div>

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


    });
</script>

<?php echo $this->endSection() ?>