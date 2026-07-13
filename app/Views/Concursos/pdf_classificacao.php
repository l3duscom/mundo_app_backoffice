<?php
$tiposLabel = [
    'desfile_cosplay'       => 'Desfile Cosplay',
    'apresentacao_cosplay'  => 'Apresentação Cosplay',
    'cosplay_kids'          => 'Cosplay Kids',
    'kpop'                  => 'K-Pop Cover',
];
$tipoLabel = $tiposLabel[$concurso->tipo] ?? ucfirst($concurso->tipo);
$categoriaLabel = ($categoria !== 'todos') ? ucfirst($categoria) : 'Todas categorias';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Classificação — <?= esc($concurso->nome) ?></title>
    <style>
        @page { margin: 90px 30px 70px 30px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #222; }

        header {
            position: fixed; top: -70px; left: 0; right: 0; height: 60px;
            border-bottom: 1px solid #6C038F; padding-bottom: 6px;
        }
        header .titulo { font-size: 16px; font-weight: bold; color: #6C038F; }
        header .sub    { font-size: 10px; color: #555; margin-top: 2px; }

        footer {
            position: fixed; bottom: -50px; left: 0; right: 0; height: 50px;
            border-top: 1px solid #ccc; font-size: 8.5px; color: #555; padding-top: 6px;
        }
        footer .esq  { float: left;  width: 60%; }
        footer .dir  { float: right; width: 40%; text-align: right; }
        footer .hash { font-family: DejaVu Sans Mono, monospace; font-size: 8px; word-break: break-all; }

        .stats { width: 100%; margin: 10px 0 14px; border-collapse: collapse; }
        .stats td {
            width: 33.33%; background: #f4eef7; border: 1px solid #dcd0e6;
            padding: 8px 10px; text-align: center;
        }
        .stats .n { font-size: 15px; font-weight: bold; color: #6C038F; display: block; }
        .stats .l { font-size: 9px; color: #666; text-transform: uppercase; letter-spacing: 0.6px; }

        table.ranking { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.ranking th {
            background: #6C038F; color: #fff; font-weight: bold;
            padding: 6px 6px; text-align: left; font-size: 10px; text-transform: uppercase;
        }
        table.ranking td {
            border-bottom: 1px solid #e0e0e0; padding: 6px; vertical-align: top;
        }
        table.ranking tr.top1 td { background: #fff7d6; }
        table.ranking tr.top2 td { background: #f0f0f0; }
        table.ranking tr.top3 td { background: #f7ebd6; }

        .pos {
            display: inline-block; width: 26px; text-align: center; padding: 2px 0;
            border-radius: 4px; background: #eee; font-weight: bold; font-size: 10.5px;
        }
        .pos.p1 { background: #FFD700; color: #6b4500; }
        .pos.p2 { background: #C0C0C0; color: #333; }
        .pos.p3 { background: #CD7F32; color: #fff; }
        .media  { font-weight: bold; color: #6C038F; }
        .muted  { color: #888; font-size: 9.5px; }

        .empty { text-align: center; padding: 40px 20px; color: #888; }
    </style>
</head>
<body>

    <header>
        <div class="titulo">Classificação — <?= esc($concurso->nome) ?></div>
        <div class="sub">
            <?= esc($tipoLabel) ?>
            <?php if (!$isCosplay): ?> · Categoria: <strong><?= esc($categoriaLabel) ?></strong><?php endif; ?>
            <?php if ($evento): ?> · Evento: <strong><?= esc($evento->nome) ?></strong><?php endif; ?>
        </div>
    </header>

    <footer>
        <div class="esq">
            Gerado em <?= esc($geradoEm) ?> por <?= esc($geradoPor) ?> (ID <?= (int) $geradoPorId ?>)<br>
            Hash de referência: <span class="hash"><?= esc($hashDocumento) ?></span>
        </div>
        <div class="dir">
            Página <span class="pagenum"></span>
        </div>
    </footer>

    <table class="stats">
        <tr>
            <td><span class="n"><?= (int) $totalParticipantes ?></span><span class="l">Participantes classificados</span></td>
            <td><span class="n"><?= number_format($mediaGeral, 2, ',', '.') ?></span><span class="l">Média geral</span></td>
            <td><span class="n"><?= number_format($maiorNota, 2, ',', '.') ?></span><span class="l">Maior nota</span></td>
        </tr>
    </table>

    <?php if (empty($ranking)): ?>
        <div class="empty">Nenhuma inscrição avaliada encontrada.</div>
    <?php else: ?>
        <table class="ranking">
            <thead>
                <tr>
                    <th style="width: 45px;">Pos.</th>
                    <th>Participante</th>
                    <?php if ($isCosplay): ?>
                        <th>Personagem</th>
                        <th>Obra</th>
                    <?php else: ?>
                        <th>Categoria</th>
                        <th>Música</th>
                    <?php endif; ?>
                    <th style="width: 70px; text-align: center;">Avaliações</th>
                    <th style="width: 70px; text-align: right;">Média</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ranking as $i => $item): $pos = $i + 1; $cls = $pos === 1 ? 'top1' : ($pos === 2 ? 'top2' : ($pos === 3 ? 'top3' : '')); ?>
                    <tr class="<?= $cls ?>">
                        <td>
                            <span class="pos <?= $pos <= 3 ? 'p' . $pos : '' ?>"><?= $pos ?>º</span>
                        </td>
                        <td>
                            <strong><?= esc($item['participante'] ?? '-') ?></strong>
                            <?php if (!$isCosplay && ($item['categoria'] ?? '') === 'solo' && !empty($item['nome']) && ($item['participante'] ?? '') !== $item['nome']): ?>
                                <br><span class="muted"><?= esc($item['nome']) ?></span>
                            <?php elseif (!empty($item['nome_social']) && ($item['participante'] ?? '') !== $item['nome_social']): ?>
                                <br><span class="muted"><?= esc($item['nome_social']) ?></span>
                            <?php endif; ?>
                        </td>
                        <?php if ($isCosplay): ?>
                            <td><?= esc($item['personagem'] ?? '-') ?></td>
                            <td><?= esc($item['obra'] ?? '-') ?></td>
                        <?php else: ?>
                            <td>
                                <?php
                                $cat = $item['categoria'] ?? 'solo';
                                if ($cat === 'grupo' && (int) ($item['integrantes'] ?? 0) === 2) $cat = 'dupla';
                                echo esc(ucfirst($cat));
                                if ($cat !== 'solo' && !empty($item['integrantes'])) {
                                    echo ' (' . (int) $item['integrantes'] . ')';
                                }
                                ?>
                            </td>
                            <td><?= esc($item['nome_musica'] ?? '-') ?></td>
                        <?php endif; ?>
                        <td style="text-align: center;">
                            <?= (int) ($item['total_avaliacoes'] ?? 0) ?> / <?= (int) ($item['jurados_necessarios'] ?? 0) ?>
                        </td>
                        <td style="text-align: right;" class="media">
                            <?= number_format((float) ($item['media_nota_total'] ?? 0), 2, ',', '.') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font("DejaVu Sans", "normal");
            $size = 8;
            $pdf->page_text(
                420, 815,
                "Página {PAGE_NUM} de {PAGE_COUNT}",
                $font, $size, [0.35, 0.35, 0.35]
            );
        }
    </script>

</body>
</html>
