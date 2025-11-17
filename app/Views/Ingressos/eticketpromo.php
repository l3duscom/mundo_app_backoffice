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

<body>
    <?php foreach ($ingressos as $item) : ?>
        <div class="ingresso-container">
            <div style="padding-bottom: 10px; margin: auto;">
                <?php echo $evento->nome; ?>
            </div>
            <div style="font-size: 16px;margin-top: 10px;margin-bottom: 5px;padding-top: 10px; padding-bottom: 10px;">
                <strong>CRIANÇA GRÁTIS</strong>
            </div>


            <div style="font-size: 10px;padding-top: 10px" class="no-wrap"><strong>Ingresso válido para dia 6 ou 7 de dezembro de 2025 </strong><br>Abertura dos portões: 11h<br>Centro de Eventos da FENAC - Novo Hamburgo RS<br></div>
            <div style="font-size: 16px;margin-top: 10px;padding-top: 10px; padding-bottom: 10px;">
                <strong>VENDA PROIBIDA</strong>
            </div>
            <div style="font-size: 9px;padding-top: 10px" class="no-wrap">Ingresso <strong>exclusivo para criança</strong> menor de 12 anos, <strong>acompanhada</strong> de um adulto pagante!</div>

            <div style=" display: flex; justify-content: center;">
                <img src="<?= $item['qrcode'] ?>" style="background-color:#fff; padding:0px">
            </div>
            <div style="margin-top: -20px;font-size: 10px;" class="no-wrap"> <?php echo $item['ingresso']->codigo; ?></div>
            <div style="font-size: 12px; padding-top: 10px;"><STRONG>DISTRIBUIÇÃO GRATUITA</STRONG><br>www.dreamfest.com.br</div>

            <div style="padding-top: 30px; font-size: 8px">-----------------------</div>
        </div>
    <?php endforeach; ?>
</body>

</html>