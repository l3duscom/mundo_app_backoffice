<h3>Saudações <?php echo esc($cliente->nome); ?>!</h3>

<p>Temos uma ótima notícia para você! Seu pagamento foi confirmado e os ingressos já estão disponíveis na opção "Meus Ingressos" na plataforma.</p>
<p>Acesse <a href="https://mundodream.com.br/console/dashboard"><strong>sua área de membros</strong></a> para visualizar seus ingressos!

<p>Estamos muito felizes em contar com você no evento geek mais mágico do sul do Brasil!</p>

<?php if (!empty($orderBumps)): ?>
<hr>
<h3>Produtos adicionais:</h3>
<table cellpadding="6" cellspacing="0" border="0" style="border-collapse:collapse; font-family: Arial, sans-serif; font-size: 14px;">
    <?php
        $totalOrderBumps = 0;
        foreach ($orderBumps as $ob):
            $obQtd = max(1, (int) ($ob->quantidade ?? 1));
            $obSubtotal = (float) $ob->preco_unitario * $obQtd;
            $totalOrderBumps += $obSubtotal;
    ?>
    <tr>
        <td style="padding:4px 8px;">•</td>
        <td style="padding:4px 8px;">
            <strong><?= esc($ob->nome) ?></strong>
            <?php if ($obQtd > 1): ?>
                <span style="color:#6d28d9; font-weight:bold;"> &nbsp;×&nbsp;<?= $obQtd ?></span>
            <?php endif; ?>
        </td>
        <td style="padding:4px 8px; text-align:right;">
            R$ <?= number_format($obSubtotal, 2, ',', '.') ?>
        </td>
    </tr>
    <?php endforeach; ?>
    <tr>
        <td colspan="3" style="border-top:1px solid #e5e7eb; padding-top:6px; text-align:right;">
            <strong>Total produtos: R$ <?= number_format($totalOrderBumps, 2, ',', '.') ?></strong>
        </td>
    </tr>
</table>
<p style="font-size: 12px; color: #6b7280;">Seus produtos ficam disponíveis em <a href="https://mundodream.com.br/console/dashboard">sua área de membros</a>, na aba "Meus Produtos".</p>
<?php endif; ?>

<hr>
<h3>Detalhes do evento:</h3>
<p>
    <?php if (isset($evento) && $evento): ?>
        <div>
            <strong><?= esc($evento->nome) ?></strong><br>
            <?php
                $data_inicio = date_create($evento->data_inicio);
                $data_fim = date_create($evento->data_fim);
                $meses = [
                    '01' => 'janeiro', '02' => 'fevereiro', '03' => 'março', '04' => 'abril',
                    '05' => 'maio', '06' => 'junho', '07' => 'julho', '08' => 'agosto',
                    '09' => 'setembro', '10' => 'outubro', '11' => 'novembro', '12' => 'dezembro'
                ];
                $dia_inicio = date_format($data_inicio, 'd');
                $dia_fim = date_format($data_fim, 'd');
                $mes = $meses[date_format($data_inicio, 'm')];
                $hora_inicio = substr($evento->hora_inicio, 0, 5);
                $hora_fim = substr($evento->hora_fim, 0, 5);
                echo "{$dia_inicio} e {$dia_fim} de {$mes} das {$hora_inicio} às {$hora_fim}<br>";
            ?>
            <?= esc($evento->local) ?>
        </div>
    <?php else: ?>
        <strong>Dreamfest 25 Parte 2 - Mega Convenção Geek</strong>
        <br>6 e 7 de dezembro das 11h às 19h
        <br>Centro de eventos da FENAC - NH
    <?php endif; ?>
    <hr>
    Geek que é geek não 😴 no ponto!
</p>