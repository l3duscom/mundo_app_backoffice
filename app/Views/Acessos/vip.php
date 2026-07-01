<?php echo $this->extend('Layout/acessos'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>


<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/datatable/datatables-combinado.min.css') ?>" />


<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>

<div class="row">


    <div class="col-lg-12">

        <div class="block">

            <div class="block-body">

                <!-- Exibirá os retornos do backend -->
                <div id="response">


                </div>

                <div class="card shadow radius-10 " style="background-color: #fff;">
                    <div class="card-body" style="padding:20%">
                        <div class="form-group mb-2">
                            <?php echo form_open('/', ['id' => 'form']) ?>


                            <?php echo $this->include('Acessos/_form_sala_vip'); ?>




                            <input id="btn-salvar" type="submit" value="Validar acesso" class="btn btn-white w-100 text-muted">


                            <?php echo form_close(); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Modal Ticket Alimentação (1ª entrada VIP) -->
    <div class="modal fade" id="ticketAlimentacaoModal" tabindex="-1" aria-labelledby="ticketAlimentacaoModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning bg-opacity-25">
                    <h5 class="modal-title" id="ticketAlimentacaoModalLabel">
                        <i class="bx bx-restaurant me-2"></i>Ticket de Alimentação
                    </h5>
                </div>
                <div class="modal-body text-center">
                    <p class="mb-2">Primeira entrada da pulseira na sala VIP:</p>
                    <p class="mb-3"><strong class="ingresso-nome"></strong></p>
                    <div class="form-check form-check-lg d-inline-flex align-items-center">
                        <input class="form-check-input me-2" type="checkbox" id="chkTicketAlimentacao" name="retirado" value="1" style="width:22px;height:22px;">
                        <label class="form-check-label fs-5" for="chkTicketAlimentacao">
                            Ticket de alimentação entregue
                        </label>
                    </div>
                    <input type="hidden" name="credencial_id" value="">
                    <p class="text-muted small mt-3">Marque se o ticket de alimentação foi entregue agora. Caso pule, poderá ser marcado posteriormente.</p>
                    <div id="responseTicketAlimentacao"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="btnPularTicket" class="btn btn-outline-secondary">Pular</button>
                    <button type="button" id="btnConfirmarTicket" class="btn btn-primary">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <?php echo $this->endSection() ?>


    <?php echo $this->section('scripts') ?>
    <script src="<?php echo site_url('recursos/vendor/loadingoverlay/loadingoverlay.min.js') ?>"></script>


    <script src="<?php echo site_url('recursos/vendor/mask/jquery.mask.min.js') ?>"></script>
    <script src="<?php echo site_url('recursos/vendor/mask/app.js') ?>"></script>


    <script>
        $(document).ready(function() {

            //$("#form").LoadingOverlay("show");

            <?php echo $this->include('Clientes/_checkmail'); ?>

            <?php echo $this->include('Clientes/_viacep'); ?>


            $("#form").on('submit', function(e) {


                e.preventDefault();


                $.ajax({

                    type: 'POST',
                    url: '<?php echo site_url('acessos/check'); ?>',
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

                            } else if (response.ticket_alimentacao && response.ticket_alimentacao.mostrar_checkbox) {

                                // Primeira leitura + ticket ainda não retirado → abre modal
                                $('#ticketAlimentacaoModal .ingresso-nome').text(response.nome || '');
                                $('#ticketAlimentacaoModal input[name=credencial_id]').val(response.credencial_id);
                                $('#ticketAlimentacaoModal input[name=retirado]').prop('checked', false);
                                $('#ticketAlimentacaoModal').modal('show');

                            } else {

                                // Já lido antes ou já retirado → redireciona normal
                                window.location.href =
                                    "<?php echo site_url("acessos/salavip/"); ?>";

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

            // ---- Modal Ticket de Alimentação (sala VIP - 1ª entrada) ----
            function fecharModalERedirecionar() {
                $('#ticketAlimentacaoModal').modal('hide');
                setTimeout(function() {
                    window.location.href = "<?php echo site_url('acessos/salavip/'); ?>";
                }, 400);
            }

            $("#btnPularTicket").on('click', function() {
                // Não marca nada — só fecha e volta.
                fecharModalERedirecionar();
            });

            $("#btnConfirmarTicket").on('click', function() {
                var $modal        = $('#ticketAlimentacaoModal');
                var credencialId  = $modal.find('input[name=credencial_id]').val();
                var retirado      = $modal.find('input[name=retirado]').is(':checked') ? 1 : 0;

                $("#btnConfirmarTicket").prop('disabled', true).text('Salvando...');
                $("#responseTicketAlimentacao").html('');

                $.ajax({
                    type: 'POST',
                    url: '<?php echo site_url('acessos/marcarTicketAlimentacao'); ?>',
                    data: {
                        credencial_id: credencialId,
                        retirado: retirado,
                        '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.sucesso) {
                            fecharModalERedirecionar();
                        } else {
                            $("#responseTicketAlimentacao").html(
                                '<div class="alert alert-danger mt-2">' + (response.erro || 'Erro ao salvar.') + '</div>'
                            );
                            $("#btnConfirmarTicket").prop('disabled', false).text('Confirmar');
                        }
                    },
                    error: function() {
                        $("#responseTicketAlimentacao").html(
                            '<div class="alert alert-danger mt-2">Erro de rede ao salvar.</div>'
                        );
                        $("#btnConfirmarTicket").prop('disabled', false).text('Confirmar');
                    }
                });
            });

        });
    </script>
    <?php echo $this->endSection() ?>