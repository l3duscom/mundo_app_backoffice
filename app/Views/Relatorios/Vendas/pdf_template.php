<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= $titulo ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 11px;
            opacity: 0.9;
        }
        
        .info-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .info-box h3 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #495057;
        }
        
        .info-grid {
            display: table;
            width: 100%;
        }
        
        .info-item {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 10px;
        }
        
        .info-item .label {
            font-size: 10px;
            color: #6c757d;
            text-transform: uppercase;
        }
        
        .info-item .value {
            font-size: 16px;
            font-weight: bold;
            color: #28a745;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th {
            background: #343a40;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }
        
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #dee2e6;
        }
        
        tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-success {
            color: #28a745;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
            padding: 10px 20px;
            font-size: 10px;
            color: #6c757d;
        }
        
        .totals-row {
            background: #e9ecef !important;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><?= $titulo ?></h1>
        <p>Evento: <?= esc($evento->nome ?? 'N/A') ?> | Período: <?= date('d/m/Y', strtotime($data_inicio)) ?> a <?= date('d/m/Y', strtotime($data_fim)) ?></p>
    </div>
    
    <div class="info-box">
        <div class="info-grid">
            <div class="info-item">
                <div class="label">Total de Vendas</div>
                <div class="value"><?= $totais['quantidade'] ?></div>
            </div>
            <div class="info-item">
                <div class="label">Receita Total</div>
                <div class="value"><?= $totais['valor_formatado'] ?></div>
            </div>
            <div class="info-item">
                <div class="label">Ticket Médio</div>
                <div class="value"><?= $totais['ticket_medio'] ?></div>
            </div>
        </div>
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
                <?php $i = 0; foreach ($linha as $key => $valor): ?>
                    <?php if (!in_array($key, ['data_formatada', 'valor_formatado', 'metodo_label'])): ?>
                        <?php if ($key === 'data'): ?>
                            <td><?= date('d/m/Y', strtotime($valor)) ?></td>
                        <?php elseif ($key === 'valor_total'): ?>
                            <td class="text-right text-success">R$ <?= number_format($valor, 2, ',', '.') ?></td>
                        <?php elseif ($key === 'quantidade'): ?>
                            <td class="text-center"><?= $valor ?></td>
                        <?php elseif ($key === 'percentual'): ?>
                            <td class="text-center"><?= $valor ?>%</td>
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
