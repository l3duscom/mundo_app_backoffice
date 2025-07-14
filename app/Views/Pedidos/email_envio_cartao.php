<h3>Saudações <?php echo esc($cliente->nome); ?></h3>

<p>Sua solicitação de envio do cartão foi realizada com sucesso!</p>
<p>Acesse <a href="<?php echo site_url("console/dashboard"); ?>"><strong>sua área de membros</strong></a> para acompanhar a entrega!

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