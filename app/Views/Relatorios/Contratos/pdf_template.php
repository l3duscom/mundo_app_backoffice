<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= $titulo ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 20px;
            margin-bottom: 20px;
        }
        .header h1 { font-size: 18px; margin-bottom: 5px; }
        .header p { font-size: 11px; opacity: 0.9; }
        .info-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            margin-bottom: 20px;
        }
        table { width: 100%; border-collapse: collapse; }
        th {
            background: #343a40;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-size: 11px;
        }
        td { padding: 10px 8px; border-bottom: 1px solid #dee2e6; }
        tr:nth-child(even) { background: #f8f9fa; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-success { color: #28a745; }
        .text-danger { color: #dc3545; }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #f8f9fa;
            padding: 10px 20px;
            font-size: 10px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><?= $titulo ?></h1>
        <p>Evento: <?= esc($evento->nome ?? 'N/A') ?></p>
    </div>
    
    <div class="info-box">
        <strong>Total de Contratos:</strong> <?= $totais['quantidade'] ?> |
        <strong>Valor Total:</strong> <?= $totais['valor_total_formatado'] ?> |
        <strong>Pago:</strong> <span class="text-success"><?= $totais['valor_pago_formatado'] ?></span> |
        <strong>Em Aberto:</strong> <span class="text-danger"><?= $totais['valor_em_aberto_formatado'] ?></span>
    </div>
    
    <table>
        <thead>
            <tr>
                <?php foreach ($colunas as $coluna): ?>
                <th><?= $coluna ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dados as $linha): ?>
            <tr>
                <?php foreach ($linha as $key => $valor): ?>
                    <?php if (!str_contains($key, 'formatado') && !str_contains($key, 'label')): ?>
                        <?php if (str_contains($key, 'valor') || str_contains($key, 'aberto')): ?>
                            <td class="text-right">R$ <?= number_format($valor ?? 0, 2, ',', '.') ?></td>
                        <?php elseif ($key === 'quantidade'): ?>
                            <td class="text-center"><?= $valor ?></td>
                        <?php elseif ($key === 'situacao'): ?>
                            <td><?= ucfirst(str_replace('_', ' ', $valor)) ?></td>
                        <?php else: ?>
                            <td><?= esc($valor) ?></td>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="footer">
        Gerado em: <?= date('d/m/Y H:i:s') ?> | Sistema de Gestão
    </div>
</body>
</html>
