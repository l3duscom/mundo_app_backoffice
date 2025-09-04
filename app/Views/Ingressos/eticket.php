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
    </style>
</head>

<body>
    <?php foreach ($ingressos as $item) : ?>
        <div class="ingresso-container">
            <div style="padding-bottom: 0px; margin: auto;">
                <?php echo $evento->nome; ?>
            </div>

            <div style="font-weight: 900; padding: 10px;" class="no-wrap"><strong><?php echo $item['ingresso']->nome; ?></strong></div>
            <div style="font-weight: 900;" class="no-wrap">Participante:<br> <?php echo $item['participante'] ?></div>
            <div style="font-weight: 900;" class="no-wrap"><strong>R$ <?php echo $item['ingresso']->valor; ?></strong></div>

            <div style="font-size: 11px;padding-top: 15px" class="no-wrap"><strong>Ingresso válido para os <br>dias escolhidos </strong> <br>Abertura dos portões: 10h<br>Centro de eventos PUCRS<br>Av. Ipiranga 6681, Porto Alegre, <br>Rio Grande do Sul</div>

            <div style="display: flex; justify-content: center;">
                <img src="<?= $item['qrcode'] ?>" style="background-color:#fff; padding:0px">
            </div>
            <div style="margin-top: -20px;font-size: 10px;" class="no-wrap"> <?php echo $item['ingresso']->codigo; ?></div>
            <div style="font-size: 12px; padding-top: 10px;"><STRONG>O Universo Geek ao Extremo</STRONG><br>www.mundodream.com.br</div>

            <div style="padding-top: 30px; font-size: 8px">-----------------------</div>
        </div>
    <?php endforeach; ?>
</body>

</html>