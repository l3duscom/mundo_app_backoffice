<?php
$tiposLabel = [
    'desfile_cosplay'       => 'Desfile Cosplay',
    'apresentacao_cosplay'  => 'Apresentação Cosplay',
    'cosplay_kids'          => 'Cosplay Kids',
    'kpop'                  => 'K-Pop Cover',
];
$tipoLabel = $tiposLabel[$concurso->tipo] ?? ucfirst($concurso->tipo);
$categoriaLabel = ($categoria !== 'todos') ? ucfirst($categoria) : 'Todas categorias';

$notasKeys = array_keys($categoriasNotas); // ex: ['nota_1','nota_2','nota_3','nota_4']
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Auditoria — <?= esc($concurso->nome) ?></title>
    <style>
        @page { margin: 100px 25px 90px 25px; }
        body  { font-family: DejaVu Sans, sans-serif; font-size: 9.5px; color: #222; }

        header {
            position: fixed; top: -80px; left: 0; right: 0; height: 75px;
            border-bottom: 2px solid #6C038F; padding-bottom: 6px;
        }
        header .titulo   { font-size: 15px; font-weight: bold; color: #6C038F; }
        header .sub      { font-size: 9.5px; color: #444; margin-top: 3px; line-height: 1.4; }
        header .marca    { position: absolute; right: 0; top: 4px; text-align: right; }
        header .marca .a { font-size: 11px; font-weight: bold; color: #6C038F; }
        header .marca .b { font-size: 8.5px; color: #666; }

        footer {
            position: fixed; bottom: -80px; left: 0; right: 0; height: 75px;
            border-top: 1px solid #999; font-size: 8px; color: #444; padding-top: 5px;
            line-height: 1.4;
        }
        footer .col      { display: inline-block; vertical-align: top; }
        footer .esq      { width: 62%; padding-right: 10px; }
        footer .dir      { width: 36%; text-align: right; }
        footer .hash     { font-family: DejaVu Sans Mono, monospace; font-size: 7.5px; word-break: break-all; color: #333; }
        footer .rot      { font-size: 7px; color: #777; }

        .juradosBox {
            border: 1px solid #dcd0e6; background: #faf6fd; padding: 6px 10px;
            margin: 8px 0 8px; font-size: 9px;
        }
        .juradosBox b { color: #6C038F; }

        .avisoBox {
            border: 1px solid #f0d68a; background: #fff8e1; padding: 7px 10px;
            margin: 0 0 12px; font-size: 8.8px; line-height: 1.45; color: #5c4400;
        }
        .avisoBox .tit {
            display: block; color: #7a5a00; font-weight: bold; font-size: 9px;
            margin-bottom: 3px; text-transform: uppercase; letter-spacing: 0.4px;
        }

        .stats { width: 100%; margin: 8px 0 10px; border-collapse: collapse; }
        .stats td {
            width: 33.33%; background: #f4eef7; border: 1px solid #dcd0e6;
            padding: 6px 8px; text-align: center;
        }
        .stats .n { font-size: 14px; font-weight: bold; color: #6C038F; display: block; }
        .stats .l { font-size: 8.5px; color: #666; text-transform: uppercase; letter-spacing: 0.6px; }

        table.rk { width: 100%; border-collapse: collapse; }
        table.rk th {
            background: #6C038F; color: #fff; font-weight: bold;
            padding: 5px 4px; text-align: left; font-size: 8.5px; text-transform: uppercase;
        }
        table.rk td {
            border-bottom: 1px solid #d8d8d8; padding: 4px 4px; vertical-align: top;
            font-size: 8.8px;
        }
        table.rk tr.head-inscricao td {
            background: #f5eefa; border-top: 2px solid #6C038F;
            padding-top: 6px; padding-bottom: 6px;
        }
        table.rk td.pos {
            width: 40px; text-align: center;
        }
        .pos-badge {
            display: inline-block; width: 26px; padding: 2px 0;
            border-radius: 4px; font-weight: bold; background: #eee;
        }
        .pos-badge.p1 { background: #FFD700; color: #6b4500; }
        .pos-badge.p2 { background: #C0C0C0; color: #333; }
        .pos-badge.p3 { background: #CD7F32; color: #fff; }
        .media { font-weight: bold; color: #6C038F; font-size: 10px; }
        .muted { color: #888; font-size: 8.5px; }

        table.jurados {
            width: 100%; border-collapse: collapse; margin-top: 4px; background: #fff;
        }
        table.jurados th, table.jurados td {
            border: 1px solid #d5c5e2; padding: 3px 5px; font-size: 8.2px;
        }
        table.jurados th {
            background: #efe4f5; color: #4a0d63; font-weight: bold; text-align: center;
        }
        table.jurados td.jur   { font-weight: bold; text-align: left; }
        table.jurados td.note  { text-align: center; }
        table.jurados td.tot   { text-align: center; background: #f9f2fd; font-weight: bold; color: #6C038F; }
        table.jurados tr.dup td {
            background: #fff8e6; color: #8a6d1c; font-style: italic; font-size: 7.8px;
        }
        table.jurados tr.dup td.jur { color: #8a6d1c; }
        table.jurados td.dup-msg { text-align: left; }

        .empty { text-align: center; padding: 40px 20px; color: #888; }
    </style>
</head>
<body>

    <header>
        <div class="marca">
            <div class="a">Relatório de Auditoria</div>
            <div class="b">Documento oficial · Verificação de resultados</div>
        </div>
        <div class="titulo"><?= esc($concurso->nome) ?></div>
        <div class="sub">
            <?= esc($tipoLabel) ?>
            <?php if (!$isCosplay): ?> · Categoria: <strong><?= esc($categoriaLabel) ?></strong><?php endif; ?>
            <?php if ($evento): ?> · Evento: <strong><?= esc($evento->nome) ?></strong><?php endif; ?>
        </div>
    </header>

    <footer>
        <div class="col esq">
            <div class="rot">DADOS AUDITÁVEIS</div>
            <b>Gerado em:</b> <?= esc($geradoEm) ?> (timestamp <?= (int) $timestampUnix ?>)
            &nbsp;·&nbsp; <b>Por:</b> <?= esc($geradoPor) ?><br>
            <b>IP:</b> <?= esc($ip) ?>
            &nbsp;·&nbsp; <b>User-Agent:</b> <?= esc(substr($userAgent ?? '', 0, 90)) ?><br>
            <b>Hash SHA-256:</b> <span class="hash"><?= esc($hashDocumento) ?></span>
        </div>
        <div class="col dir">
            <div class="rot">PAGINAÇÃO</div>
            Página <span></span><br>
            Concurso ID <?= (int) $concurso->id ?><?php if (!empty($concurso->evento_id)): ?> · Evento ID <?= (int) $concurso->evento_id ?><?php endif; ?>
        </div>
    </footer>

    <div class="juradosBox">
        <b>Corpo de jurados oficiais:</b>
        <?php $i = 0; foreach ($juradosOficiaisIds as $jid):
            $jm = $juradosOficiaisMap[$jid];
            if ($i++ > 0) echo ' · ';
            ?>
            <?= esc($jm['nome']) ?>
        <?php endforeach; ?>
    </div>

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
        <table class="rk">
            <?php foreach ($ranking as $idx => $item): $pos = $idx + 1; ?>
                <tr class="head-inscricao">
                    <td class="pos">
                        <span class="pos-badge <?= $pos <= 3 ? 'p' . $pos : '' ?>"><?= $pos ?>º</span>
                    </td>
                    <td colspan="4">
                        <strong><?= esc($item['participante'] ?? '-') ?></strong>
                        <?php if (!empty($item['nome_social']) && ($item['participante'] ?? '') !== $item['nome_social']): ?>
                            <span class="muted"> · <?= esc($item['nome_social']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($item['nome']) && ($item['participante'] ?? '') !== $item['nome']): ?>
                            <span class="muted"> · RG: <?= esc($item['nome']) ?></span>
                        <?php endif; ?>
                        <br>
                        <span class="muted">
                            <?php if ($isCosplay): ?>
                                Personagem: <?= esc($item['personagem'] ?? '-') ?> · Obra: <?= esc($item['obra'] ?? '-') ?>
                            <?php else: ?>
                                <?php
                                $cat = $item['categoria'] ?? 'solo';
                                if ($cat === 'grupo' && (int) ($item['integrantes'] ?? 0) === 2) $cat = 'dupla';
                                ?>
                                Categoria: <?= esc(ucfirst($cat)) ?><?php if ($cat !== 'solo' && !empty($item['integrantes'])): ?> (<?= (int) $item['integrantes'] ?> integrantes)<?php endif; ?>
                                · Música: <?= esc($item['nome_musica'] ?? '-') ?>
                            <?php endif; ?>
                            · Inscrição ID <?= (int) ($item['inscricao_id'] ?? 0) ?>
                        </span>
                    </td>
                    <td style="text-align: right; width: 90px;">
                        <span class="muted"><?= (int) ($item['total_avaliacoes'] ?? 0) ?>/<?= (int) ($item['jurados_necessarios'] ?? 0) ?> avaliações</span><br>
                        <span class="media">Média: <?= number_format((float) ($item['media_nota_total'] ?? 0), 2, ',', '.') ?></span>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="5">
                        <table class="jurados">
                            <thead>
                                <tr>
                                    <th style="width: 26%;">Jurado</th>
                                    <?php foreach ($categoriasNotas as $ck => $cl): ?>
                                        <th><?= esc($cl) ?></th>
                                    <?php endforeach; ?>
                                    <th style="width: 12%;">Total</th>
                                    <th style="width: 15%;">Data/Hora</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $notasSomadas = array_fill_keys($notasKeys, 0);
                                $totalSomado  = 0;
                                $countJurados = 0;

                                // Só entra na tabela jurado que efetivamente avaliou.
                                // Jurados sem avaliação são omitidos (fica registrado no
                                // "X/Y avaliações" do cabeçalho da inscrição).
                                foreach ($juradosOficiaisIds as $jid):
                                    $rec = $item['avaliacao_por_jurado'][$jid] ?? null;
                                    if ($rec === null) continue;
                                    $jm  = $juradosOficiaisMap[$jid];
                                    $av  = $rec['picked'];
                                    $dup = $rec['duplicates'];
                                    $countJurados++;
                                ?>
                                    <tr>
                                        <td class="jur"><?= esc($jm['nome']) ?></td>
                                        <?php foreach ($notasKeys as $nk):
                                            $v = $av[$nk] ?? null;
                                            if ($v !== null) $notasSomadas[$nk] += (float) $v;
                                        ?>
                                            <td class="note"><?= $v !== null ? number_format((float) $v, 2, ',', '.') : '-' ?></td>
                                        <?php endforeach; ?>
                                        <?php $totalSomado += (float) ($av['nota_total'] ?? 0); ?>
                                        <td class="tot"><?= number_format((float) ($av['nota_total'] ?? 0), 2, ',', '.') ?></td>
                                        <td class="note"><?= !empty($av['created_at']) ? esc(date('d/m/Y H:i', strtotime($av['created_at']))) : '-' ?></td>
                                    </tr>
                                    <?php foreach ($dup as $d): ?>
                                        <tr class="dup">
                                            <td class="jur" colspan="<?= count($notasKeys) + 3 ?>" class="dup-msg">
                                                ⚠ Nota duplicada do mesmo jurado <strong>desconsiderada</strong>
                                                (aval. ID <?= (int) ($d['id'] ?? 0) ?><?php if (!empty($d['created_at'])): ?>, registrada em <?= esc(date('d/m/Y H:i', strtotime($d['created_at']))) ?><?php endif; ?>) —
                                                <?php
                                                    $bits = [];
                                                    foreach ($notasKeys as $nk) {
                                                        $lbl = $categoriasNotas[$nk];
                                                        $vd  = $d[$nk] ?? null;
                                                        $bits[] = $lbl . ': ' . ($vd !== null ? number_format((float) $vd, 2, ',', '.') : '-');
                                                    }
                                                    echo esc(implode(', ', $bits));
                                                ?>, total: <?= number_format((float) ($d['nota_total'] ?? 0), 2, ',', '.') ?>.
                                                Foi mantida somente a avaliação de maior nota válida (critério do ranking).
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>

                                <?php
                                // Avaliações fora do corpo oficial (jurados extras) — mesma lógica de dedup
                                $extras = $item['avaliacao_por_jurado'] ?? [];
                                foreach ($extras as $jid => $rec) {
                                    if (in_array((int) $jid, $juradosOficiaisIds, true)) continue;
                                    $av  = $rec['picked'];
                                    $dup = $rec['duplicates'];
                                    $countJurados++;
                                    $totalSomado += (float) ($av['nota_total'] ?? 0);
                                    foreach ($notasKeys as $nk) {
                                        if (isset($av[$nk])) $notasSomadas[$nk] += (float) $av[$nk];
                                    }
                                    ?>
                                    <tr>
                                        <td class="jur"><?= esc($av['jurado_nome']) ?></td>
                                        <?php foreach ($notasKeys as $nk): ?>
                                            <td class="note"><?= isset($av[$nk]) ? number_format((float) $av[$nk], 2, ',', '.') : '-' ?></td>
                                        <?php endforeach; ?>
                                        <td class="tot"><?= number_format((float) ($av['nota_total'] ?? 0), 2, ',', '.') ?></td>
                                        <td class="note"><?= !empty($av['created_at']) ? esc(date('d/m/Y H:i', strtotime($av['created_at']))) : '-' ?></td>
                                    </tr>
                                    <?php foreach ($dup as $d): ?>
                                        <tr class="dup">
                                            <td class="jur" colspan="<?= count($notasKeys) + 3 ?>" class="dup-msg">
                                                ⚠ Nota duplicada do mesmo jurado <strong>desconsiderada</strong>
                                                (aval. ID <?= (int) ($d['id'] ?? 0) ?><?php if (!empty($d['created_at'])): ?>, registrada em <?= esc(date('d/m/Y H:i', strtotime($d['created_at']))) ?><?php endif; ?>) —
                                                total: <?= number_format((float) ($d['nota_total'] ?? 0), 2, ',', '.') ?>.
                                                Foi mantida somente a avaliação de maior nota válida.
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php
                                }
                                ?>

                                <?php if ($countJurados > 0): ?>
                                    <tr>
                                        <td class="jur" style="background: #efe4f5;">Média por categoria</td>
                                        <?php foreach ($notasKeys as $nk): ?>
                                            <td class="note" style="background: #efe4f5; font-weight: bold;">
                                                <?= number_format($notasSomadas[$nk] / $countJurados, 2, ',', '.') ?>
                                            </td>
                                        <?php endforeach; ?>
                                        <td class="tot" style="background: #efe4f5;">
                                            <?= number_format($totalSomado / $countJurados, 2, ',', '.') ?>
                                        </td>
                                        <td class="note" style="background: #efe4f5;">— </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <div class="avisoBox">
        <span class="tit">Protocolo em caso de falha de inserção de nota</span>
        Caso ocorra falha na inserção da nota diretamente no sistema, o jurado transmite as notas — critério a critério
        (<?php $lbls = array_values($categoriasNotas); echo esc(implode(', ', $lbls)); ?>) — à coordenação do concurso,
        que registra o valor por escrito e, em seguida, faz a inserção no sistema em nome do jurado.
        Caso essa inserção ocorra <strong>após a divulgação do pódio</strong>, a premiação é entregue
        <strong>tanto ao vencedor anunciado quanto ao vencedor ajustado</strong> pelo recalculo, e a coordenação realiza
        <strong>pronunciamento oficial</strong> comunicando o ajuste. Esta política garante que nenhum participante seja
        prejudicado por falha técnica alheia à sua performance e preserva a validade jurídica do resultado.
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font("DejaVu Sans", "normal");
            $size = 7.5;
            $w = $pdf->get_width();
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $tw = $fontMetrics->get_text_width($text, $font, $size);
            $pdf->page_text(
                $w - $tw - 30, $pdf->get_height() - 22,
                $text, $font, $size, [0.3, 0.3, 0.3]
            );
        }
    </script>

</body>
</html>
