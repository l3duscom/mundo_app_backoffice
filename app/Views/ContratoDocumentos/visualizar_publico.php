<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($titulo) ?> - <?= esc($documento->titulo ?? 'Contrato') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #f5f5f5;
            padding: 20px 0;
        }
        
        .documento-preview {
            background: white;
            padding: 40px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 900px;
            margin: 0 auto;
        }
        
        .documento-preview h1, 
        .documento-preview h2, 
        .documento-preview h3 {
            color: #333;
        }
        
        .documento-preview table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .documento-preview table th,
        .documento-preview table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        
        .documento-preview table th {
            background-color: #f5f5f5;
        }
        
        .header-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
        }
        
        @media print {
            .no-print { display: none !important; }
            body { background: white; padding: 0; }
            .documento-preview { 
                border: none; 
                box-shadow: none; 
                padding: 0;
            }
            .header-info { background: #333; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card shadow-sm mb-4">
        <div class="header-info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1"><i class="bi bi-file-earmark-text me-2"></i><?= esc($documento->titulo) ?></h4>
                    <p class="mb-0 opacity-75">
                        <?= esc($evento->titulo ?? 'Evento') ?> - 
                        <?= esc($expositor->nome_fantasia ?? $expositor->nome ?? 'Expositor') ?>
                    </p>
                </div>
                <div class="text-end">
                    <?php if ($documento->status === 'confirmado'): ?>
                    <span class="badge bg-success fs-6"><i class="bi bi-check-circle me-1"></i>Confirmado</span>
                    <?php elseif ($documento->status === 'assinado'): ?>
                    <span class="badge bg-primary fs-6"><i class="bi bi-pen me-1"></i>Assinado</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="documento-preview">
                <?= $documento->conteudo_html ?>
            </div>
        </div>
    </div>

    <?php if ($documento->isAssinado()): ?>
    <div class="card shadow-sm no-print">
        <div class="card-body">
            <h6 class="mb-3"><i class="bi bi-shield-check text-success me-2"></i>Informações de Assinatura</h6>
            <div class="row">
                <div class="col-md-4">
                    <small class="text-muted">Assinado por:</small>
                    <p class="mb-0 fw-bold"><?= esc($documento->assinado_por) ?></p>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Documento:</small>
                    <p class="mb-0"><?= esc($documento->documento_assinante) ?></p>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Data/Hora:</small>
                    <p class="mb-0"><?= $documento->getDataAssinaturaFormatada() ?></p>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <small class="text-muted">IP:</small>
                    <p class="mb-0 font-monospace"><?= esc($documento->ip_assinatura) ?></p>
                </div>
                <div class="col-md-6">
                    <small class="text-muted">Hash de Verificação:</small>
                    <p class="mb-0 font-monospace small"><?= esc($documento->hash_assinatura) ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="text-center mt-4 no-print">
        <button type="button" class="btn btn-primary" onclick="window.print()">
            <i class="bi bi-printer me-2"></i>Imprimir
        </button>
    </div>
</div>

</body>
</html>
