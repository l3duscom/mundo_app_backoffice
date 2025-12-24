<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Cupons de Desconto</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('cupons'); ?>">Cupons</a></li>
                <li class="breadcrumb-item active" aria-current="page">Gerar em Massa</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<div class="card">
    <div class="card-header">
        <h6 class="mb-0"><i class="bx bx-duplicate me-2"></i>Gerar Cupons em Massa</h6>
    </div>
    <div class="card-body">
        <?php echo form_open('/', ['id' => 'formGerarMassa', 'class' => 'row g-3']); ?>
            
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-1"></i>
                    Esta ferramenta irá gerar múltiplos cupons com códigos aleatórios de 8 dígitos (letras e números, sem caracteres especiais).
                    <br>Cada cupom gerado terá <strong>uso único</strong> por padrão.
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Prefixo (opcional)</label>
                <input type="text" name="prefixo" class="form-control text-uppercase" 
                       placeholder="Ex: NATAL, VIP" maxlength="10">
                <small class="text-muted">Se preenchido, o código será: PREFIXO-XXXXXXXX</small>
            </div>
            
            <div class="col-md-4 mb-3">
                <label class="form-label">Quantidade de Cupons <span class="text-danger">*</span></label>
                <input type="number" name="quantidade" class="form-control" 
                       value="10" min="1" max="1000" required>
                <small class="text-muted">Máximo: 1000 cupons por vez</small>
            </div>
            
            <div class="col-md-4 mb-3">
                <label class="form-label">Nome Base <span class="text-danger">*</span></label>
                <input type="text" name="nome" class="form-control" 
                       placeholder="Ex: Cupom Natal 2024" required>
                <small class="text-muted">Nome interno para identificação</small>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Tipo de Desconto <span class="text-danger">*</span></label>
                <select name="tipo" class="form-select" id="tipoDesconto" required>
                    <option value="percentual">Percentual (%)</option>
                    <option value="fixo">Valor Fixo (R$)</option>
                </select>
            </div>
            
            <div class="col-md-4 mb-3">
                <label class="form-label">Valor do Desconto <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text" id="prefixoDesconto">%</span>
                    <input type="number" name="desconto" class="form-control" 
                           placeholder="0" step="0.01" min="0.01" required>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <label class="form-label">Valor Mínimo do Pedido</label>
                <div class="input-group">
                    <span class="input-group-text">R$</span>
                    <input type="number" name="valor_minimo" class="form-control" 
                           value="0" placeholder="0.00" step="0.01" min="0">
                </div>
                <small class="text-muted">Deixe 0 para não ter mínimo</small>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Data de Início</label>
                <input type="date" name="data_inicio" class="form-control">
                <small class="text-muted">Deixe vazio para começar imediatamente</small>
            </div>
            
            <div class="col-md-4 mb-3">
                <label class="form-label">Data de Término</label>
                <input type="date" name="data_fim" class="form-control">
                <small class="text-muted">Deixe vazio para não expirar</small>
            </div>
            
            <div class="col-md-4 mb-3">
                <label class="form-label">Evento</label>
                <input type="hidden" name="evento_id" value="<?php echo $evento_id ?? ''; ?>">
                <input type="text" class="form-control" readonly 
                       value="<?php 
                           foreach ($eventos as $evento) {
                               if ($evento->id == ($evento_id ?? '')) {
                                   echo esc($evento->nome);
                                   break;
                               }
                           }
                       ?>">
                <small class="text-muted">Evento do contexto atual</small>
            </div>
            
            <div class="col-12">
                <hr>
                <a href="<?php echo site_url('cupons'); ?>" class="btn btn-light">
                    <i class="bx bx-arrow-back me-1"></i>Voltar
                </a>
                <button type="submit" class="btn btn-primary" id="btn-gerar">
                    <i class="bx bx-duplicate me-1"></i>Gerar Cupons
                </button>
            </div>
            
        <?php echo form_close(); ?>
    </div>
</div>

<!-- Modal de Sucesso com opção de exportar -->
<div class="modal fade" id="modalSucesso" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-check-circle me-2"></i>Cupons Gerados!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong id="qtdGerada">0</strong> cupons foram gerados com sucesso!</p>
                <p>Deseja exportar os códigos para CSV?</p>
            </div>
            <div class="modal-footer">
                <a href="<?php echo site_url('cupons'); ?>" class="btn btn-secondary">
                    <i class="bx bx-list-ul me-1"></i>Ver Lista
                </a>
                <button type="button" class="btn btn-success" id="btn-exportar">
                    <i class="bx bx-download me-1"></i>Exportar CSV
                </button>
            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>

<script>
$(document).ready(function() {
    
    var cuponsGerados = [];
    
    // Alterar prefixo conforme tipo de desconto
    $('#tipoDesconto').on('change', function() {
        var prefixo = $('#prefixoDesconto');
        prefixo.text(this.value === 'fixo' ? 'R$' : '%');
    });

    $("#formGerarMassa").on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            type: "POST",
            url: "<?php echo site_url('cupons/cadastrarEmMassa'); ?>",
            data: new FormData(this),
            dataType: "json",
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {
                $("#btn-gerar").prop("disabled", true).html('<span class="spinner-border spinner-border-sm"></span> Gerando...');
            },
            success: function(response) {
                $("#btn-gerar").prop("disabled", false).html('<i class="bx bx-duplicate me-1"></i>Gerar Cupons');
                $("input[name=csrf_test_name]").val(response.token);

                if (response.erro) {
                    Lobibox.notify('error', {
                        pauseDelayOnHover: true,
                        continueDelayOnInactiveTab: false,
                        position: 'top right',
                        msg: response.erro
                    });
                } else {
                    cuponsGerados = response.cupons || [];
                    $('#qtdGerada').text(response.quantidade || 0);
                    $('#modalSucesso').modal('show');
                }
            },
            error: function() {
                $("#btn-gerar").prop("disabled", false).html('<i class="bx bx-duplicate me-1"></i>Gerar Cupons');
                Lobibox.notify('error', {
                    pauseDelayOnHover: true,
                    continueDelayOnInactiveTab: false,
                    position: 'top right',
                    msg: 'Erro ao processar requisição'
                });
            }
        });
    });

    // Exportar CSV
    $('#btn-exportar').on('click', function() {
        if (cuponsGerados.length === 0) {
            alert('Nenhum cupom para exportar.');
            return;
        }
        
        // Criar CSV
        var csv = 'Código,Nome,Desconto,Tipo,Valor Mínimo,Data Início,Data Fim\n';
        cuponsGerados.forEach(function(cupom) {
            csv += '"' + cupom.codigo + '",';
            csv += '"' + cupom.nome + '",';
            csv += cupom.desconto + ',';
            csv += '"' + cupom.tipo + '",';
            csv += cupom.valor_minimo + ',';
            csv += '"' + (cupom.data_inicio || '') + '",';
            csv += '"' + (cupom.data_fim || '') + '"\n';
        });
        
        // Download
        var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');
        var url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'cupons_gerados_' + new Date().toISOString().slice(0,10) + '.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    // Remover classe de erro ao digitar
    $('input, select').on('input change', function() {
        $(this).removeClass('is-invalid');
    });
});
</script>

<?php echo $this->endSection() ?>
