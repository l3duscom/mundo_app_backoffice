<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .assinatura-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header-assinatura {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .documento-preview {
            max-height: 500px;
            overflow-y: auto;
            padding: 30px;
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin: 20px;
        }
        
        .documento-preview table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .documento-preview table th,
        .documento-preview table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        
        .assinatura-form {
            padding: 30px;
            background: white;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 30px;
            font-weight: 600;
        }
        
        .btn-assinar {
            padding: 15px 40px;
            font-size: 1.1rem;
            border-radius: 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-assinar:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .info-contrato {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .sucesso-assinatura {
            text-align: center;
            padding: 50px 30px;
        }
        
        .sucesso-assinatura i {
            font-size: 5rem;
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="assinatura-container">
                    <div class="header-assinatura">
                        <h2 class="mb-1"><i class="bx bx-file-blank me-2"></i>Assinatura de Contrato</h2>
                        <p class="mb-0 opacity-75"><?php echo esc($documento->titulo); ?></p>
                    </div>

                    <?php if ($documento->status === 'pendente_assinatura'): ?>
                    
                    <!-- Informações do Contrato -->
                    <div class="p-4">
                        <div class="info-contrato">
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">Contrato:</small>
                                    <p class="mb-2 fw-bold"><?php echo esc($contrato->codigo ?? '#' . $contrato->id); ?></p>
                                    
                                    <small class="text-muted">Expositor:</small>
                                    <p class="mb-2"><?php echo esc($expositor->getNomeExibicao()); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">Evento:</small>
                                    <p class="mb-2"><?php echo esc($evento->nome ?? 'N/A'); ?></p>
                                    
                                    <small class="text-muted">Valor:</small>
                                    <p class="mb-0 fw-bold text-success fs-5"><?php echo $contrato->getValorFinalFormatado(); ?></p>
                                </div>
                            </div>
                        </div>

                        <h5 class="mb-3"><i class="bx bx-file me-2"></i>Documento do Contrato</h5>
                        <p class="text-muted">Leia atentamente o documento abaixo antes de assinar:</p>
                        
                        <div class="documento-preview">
                            <?php echo $documento->conteudo_html; ?>
                        </div>
                    </div>

                    <!-- Formulário de Assinatura -->
                    <div class="assinatura-form border-top">
                        <h5 class="mb-4"><i class="bx bx-pen me-2"></i>Dados para Assinatura</h5>
                        
                        <form id="formAssinatura">
                            <input type="hidden" name="hash" value="<?php echo esc($documento->hash_assinatura); ?>">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nome Completo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-lg" name="nome_assinante" 
                                           value="<?php echo esc($expositor->nome); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">CPF/CNPJ <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-lg" name="documento_assinante" 
                                           value="<?php echo esc($expositor->documento); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" name="concordo" id="concordo" required>
                                <label class="form-check-label" for="concordo">
                                    Li e concordo com todos os termos e condições do documento acima.
                                    Declaro que as informações fornecidas são verdadeiras e assumo total responsabilidade 
                                    por esta assinatura digital.
                                </label>
                            </div>
                            
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary btn-assinar" id="btnAssinar">
                                    <i class="bx bx-check-circle me-2"></i>Assinar Documento
                                </button>
                            </div>
                            
                            <p class="text-muted text-center mt-3 small">
                                <i class="bx bx-shield-quarter me-1"></i>
                                Sua assinatura será registrada com data, hora e IP para fins de validação.
                            </p>
                        </form>
                    </div>

                    <?php elseif ($documento->status === 'assinado'): ?>
                    
                    <div class="sucesso-assinatura">
                        <i class="bx bx-check-circle"></i>
                        <h3 class="mt-4">Documento Assinado!</h3>
                        <p class="text-muted">Este documento já foi assinado e está aguardando confirmação.</p>
                        
                        <div class="info-contrato mt-4 text-start">
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">Assinado por:</small>
                                    <p class="mb-2 fw-bold"><?php echo esc($documento->assinado_por); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">Data/Hora:</small>
                                    <p class="mb-2"><?php echo $documento->getDataAssinaturaFormatada(); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php elseif ($documento->status === 'confirmado'): ?>
                    
                    <div class="sucesso-assinatura">
                        <i class="bx bx-badge-check text-success"></i>
                        <h3 class="mt-4 text-success">Documento Confirmado!</h3>
                        <p class="text-muted">Este contrato foi assinado e confirmado com sucesso.</p>
                    </div>

                    <?php else: ?>
                    
                    <div class="text-center p-5">
                        <i class="bx bx-error-circle text-warning" style="font-size: 4rem;"></i>
                        <h4 class="mt-3">Documento Indisponível</h4>
                        <p class="text-muted">Este documento não está disponível para assinatura no momento.</p>
                    </div>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php if ($documento->status === 'pendente_assinatura'): ?>
    <script>
    $(document).ready(function() {
        var csrfToken = '<?php echo csrf_hash(); ?>';
        var csrfName = '<?php echo csrf_token(); ?>';

        $('#formAssinatura').on('submit', function(e) {
            e.preventDefault();
            
            var btn = $('#btnAssinar');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processando...');
            
            $.ajax({
                type: 'POST',
                url: '<?php echo site_url('contratodocumentos/processarassinatura'); ?>',
                data: $(this).serialize() + '&' + csrfName + '=' + csrfToken,
                dataType: 'json',
                success: function(response) {
                    if (response.sucesso) {
                        alert(response.sucesso);
                        location.reload();
                    } else {
                        alert(response.erro || 'Erro ao processar assinatura');
                        btn.prop('disabled', false).html('<i class="bx bx-check-circle me-2"></i>Assinar Documento');
                    }
                },
                error: function() {
                    alert('Erro ao processar solicitação');
                    btn.prop('disabled', false).html('<i class="bx bx-check-circle me-2"></i>Assinar Documento');
                }
            });
        });
    });
    </script>
    <?php endif; ?>
</body>
</html>

