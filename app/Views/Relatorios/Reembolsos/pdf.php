<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= esc($titulo) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #222; }
        .header {
            background: #fd7e14;
            color: #fff;
            padding: 12px 16px;
            margin-bottom: 12px;
        }
        .header h1 { font-size: 14px; margin-bottom: 4px; }
        .header p { font-size: 9px; opacity: 0.95; }
        .info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 10px;
            margin-bottom: 12px;
            border-radius: 4px;
        }
        .info table { width: 100%; }
        .info td { padding: 4px 8px; vertical-align: top; }
        .label { color: #6c757d; font-size: 7px; text-transform: uppercase; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th {
            background: #343a40;
            color: #fff;
            padding: 6px 4px;
            text-align: left;
            font-size: 7px;
        }
        table.data td { padding: 5px 4px; border-bottom: 1px solid #dee2e6; vertical-align: top; }
        table.data tr:nth-child(even) { background: #f8f9fa; }
        .text-end { text-align: right; }
        .footer { margin-top: 12px; font-size: 7px; color: #6c757d; }
    </style>
</head>
<body>
    <div class="header">
        <h1><?= esc($titulo) ?></h1>
        <p>
            Evento: <?= esc($evento->nome ?? 'Todos os eventos') ?>
            &nbsp;|&nbsp;
            Período (solicitação): <?= date('d/m/Y', strtotime($filtros['data_inicio'])) ?> a <?= date('d/m/Y', strtotime($filtros['data_fim'])) ?>
        </p>
    </div>

    <div class="info">
        <table>
            <tr>
                <td>
                    <div class="label">Quantidade</div>
                    <strong><?= (int) ($totais->total_registros ?? 0) ?></strong>
                </td>
                <td>
                    <div class="label">Valor total</div>
                    <strong>R$ <?= number_format((float) ($totais->valor_total ?? 0), 2, ',', '.') ?></strong>
                </td>
            </tr>
        </table>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Pedido</th>
                <th class="text-end">Valor</th>
                <th>Evento</th>
                <th>Situação</th>
                <th>Pagamento</th>
                <th>Data sol.</th>
                <th>Proc. em</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($linhas as $l) : ?>
                <tr>
                    <td><?= (int) $l['id'] ?></td>
                    <td><?= esc($l['cliente_nome']) ?></td>
                    <td><?= esc($l['pedido_codigo']) ?></td>
                    <td class="text-end">R$ <?= number_format($l['valor'], 2, ',', '.') ?></td>
                    <td><?= esc($l['evento_nome']) ?></td>
                    <td><?= esc($l['situacao']) ?></td>
                    <td><?= esc($l['pagamento']) ?></td>
                    <td><?= esc($l['data_solicitacao']) ?></td>
                    <td><?= esc($l['processado_em']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">Gerado em <?= date('d/m/Y H:i:s') ?></div>
</body>
</html>
