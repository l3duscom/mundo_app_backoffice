<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= esc($titulo) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 7px; color: #222; }
        .header {
            background: #fd7e14;
            color: #fff;
            padding: 10px 12px;
            margin-bottom: 10px;
        }
        .header h1 { font-size: 12px; margin-bottom: 3px; }
        .header p { font-size: 8px; opacity: 0.95; }
        .info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 8px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        .info table { width: 100%; }
        .info td { padding: 3px 6px; vertical-align: top; }
        .label { color: #6c757d; font-size: 6px; text-transform: uppercase; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th {
            background: #343a40;
            color: #fff;
            padding: 4px 3px;
            text-align: left;
            font-size: 6px;
        }
        table.data td { padding: 4px 3px; border-bottom: 1px solid #dee2e6; vertical-align: top; }
        table.data tr:nth-child(even) { background: #f8f9fa; }
        .text-end { text-align: right; }
        .footer { margin-top: 10px; font-size: 6px; color: #6c757d; }
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
                    <div class="label">Linhas (ingressos)</div>
                    <strong><?= (int) ($totais_detalhe->total_linhas ?? 0) ?></strong>
                </td>
                <td>
                    <div class="label">Soma valores ingressos</div>
                    <strong>R$ <?= number_format((float) ($totais_detalhe->valor_ingressos ?? 0), 2, ',', '.') ?></strong>
                </td>
            </tr>
        </table>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>Sol.</th>
                <th>Cliente</th>
                <th>Pedido</th>
                <th>Evento</th>
                <th>Tipo</th>
                <th>Sit.</th>
                <th>Pag.</th>
                <th>Data sol.</th>
                <th>Ingresso</th>
                <th>Cód.</th>
                <th>Part.</th>
                <th>T.</th>
                <th class="text-end">R$</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($linhas as $l) : ?>
                <tr>
                    <td><?= (int) $l['refound_id'] ?></td>
                    <td><?= esc($l['cliente_nome']) ?></td>
                    <td><?= esc($l['pedido_codigo']) ?></td>
                    <td><?= esc($l['evento_nome']) ?></td>
                    <td><?= esc($l['tipo_solicitacao']) ?></td>
                    <td><?= esc($l['situacao']) ?></td>
                    <td><?= esc($l['pagamento']) ?></td>
                    <td><?= esc($l['data_solicitacao']) ?></td>
                    <td><?= esc($l['ingresso_nome']) ?></td>
                    <td><?= esc($l['ingresso_codigo']) ?></td>
                    <td><?= esc($l['ingresso_participante']) ?></td>
                    <td><?= esc($l['ingresso_tipo']) ?></td>
                    <td class="text-end"><?= number_format($l['ingresso_valor'], 2, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">Gerado em <?= date('d/m/Y H:i:s') ?></div>
</body>
</html>
