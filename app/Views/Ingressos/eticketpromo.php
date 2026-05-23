<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo $titulo; ?></title>
    <style>
        html,
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            /* Usa Helvetica que é similar à Arial */
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
        }


        .ingresso-container {
            width: 100%;
            /* Faz com que a div ocupe toda a largura */
            /* Pode ajustar conforme necessário */
            margin-top: 50px;
            /* Margem superior para cada ingresso */
            text-align: center;
        }

        img {
            width: 160px;
            /* Ajusta a largura da imagem */
            height: auto;
            margin: 0;
            padding-right: 10px;
        }

        .no-wrap {
            white-space: nowrap;
            /* Evita que a palavra seja quebrada */

        }

        .texto-com-fundo-preto {
            border: 5px solid black;
            /* Cor de fundo preta */
            color: black;
            /* Cor do texto branca para contraste */
            width: 100%;
            /* Ocupa 100% da largura da página */
            padding: 20px;
            /* Espaço interno para não colar o texto nas bordas */
        }

        .promo {
            border: 5px solid black;
            /* Cor de fundo preta */
            color: black;
            /* Cor do texto branca para contraste */
            width: 100%;
            /* Ocupa 100% da largura da página */
            padding: 20px;
            /* Espaço interno para não colar o texto nas bordas */
            border-radius: 100px;
            /* Bordas arredondadas */
        }
    </style>
</head>

<?php
$meses = [
    1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
    5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
    9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro',
];

$dataInicioTs = !empty($evento->data_inicio) ? strtotime($evento->data_inicio) : null;
$dataFimTs    = !empty($evento->data_fim)    ? strtotime($evento->data_fim)    : null;

$validade = 'Data a confirmar';
if ($dataInicioTs && $dataFimTs && $dataInicioTs !== $dataFimTs) {
    $diaIni = (int) date('d', $dataInicioTs);
    $diaFim = (int) date('d', $dataFimTs);
    $mesIni = (int) date('n', $dataInicioTs);
    $mesFim = (int) date('n', $dataFimTs);
    $anoIni = date('Y', $dataInicioTs);
    $anoFim = date('Y', $dataFimTs);

    if ($mesIni === $mesFim && $anoIni === $anoFim) {
        $validade = "Ingresso válido para dia {$diaIni} ou {$diaFim} de {$meses[$mesIni]} de {$anoIni}";
    } else {
        $validade = "Ingresso válido para {$diaIni} de {$meses[$mesIni]} de {$anoIni} ou {$diaFim} de {$meses[$mesFim]} de {$anoFim}";
    }
} elseif ($dataInicioTs) {
    $diaIni = (int) date('d', $dataInicioTs);
    $mesIni = (int) date('n', $dataInicioTs);
    $anoIni = date('Y', $dataInicioTs);
    $validade = "Ingresso válido para dia {$diaIni} de {$meses[$mesIni]} de {$anoIni}";
}

$horaAbertura = !empty($evento->hora_inicio) ? substr($evento->hora_inicio, 0, 5) : null;

$localPartes = array_filter([
    $evento->local   ?? null,
    $evento->cidade  ?? null,
    $evento->estado  ?? null,
]);
$localCompleto = implode(' - ', $localPartes);
?>
<body>
    <?php foreach ($ingressos as $item) : ?>
        <div class="ingresso-container">
            <div style="padding-bottom: 10px; margin: auto;">
                <?php echo esc($evento->nome); ?>
            </div>
            <div style="font-size: 16px;margin-top: 10px;margin-bottom: 5px;padding-top: 10px; padding-bottom: 10px;">
                <strong>CRIANÇA GRÁTIS</strong>
            </div>


            <div style="font-size: 10px;padding-top: 10px" class="no-wrap">
                <strong><?= esc($validade) ?></strong><br>
                <?php if ($horaAbertura) : ?>
                    Abertura dos portões: <?= esc($horaAbertura) ?><br>
                <?php endif; ?>
                <?php if ($localCompleto) : ?>
                    <?= esc($localCompleto) ?><br>
                <?php endif; ?>
            </div>
            <div style="font-size: 16px;margin-top: 10px;padding-top: 10px; padding-bottom: 10px;">
                <strong>VENDA PROIBIDA</strong>
            </div>
            <div style="font-size: 9px;padding-top: 10px" class="no-wrap">Ingresso <strong>exclusivo para criança</strong> menor de 12 anos, <strong>acompanhada</strong> de um adulto pagante!</div>

            <div style=" display: flex; justify-content: center;">
                <img src="<?= $item['qrcode'] ?>" style="background-color:#fff; padding:0px">
            </div>
            <div style="margin-top: -20px;font-size: 10px;" class="no-wrap"> <?php echo $item['ingresso']->codigo; ?></div>
            <div style="font-size: 12px; padding-top: 10px;"><STRONG>DISTRIBUIÇÃO GRATUITA</STRONG><br><?= esc($site) ?></div>

            <div style="padding-top: 30px; font-size: 8px">-----------------------</div>
        </div>
    <?php endforeach; ?>
</body>

</html>