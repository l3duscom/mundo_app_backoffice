<h3>Saudações <?php echo esc($cliente->nome); ?></h3>

<p>Seu pedido foi realizado com sucesso!</p>
<p>Acesse <a href="<?php echo site_url("console/dashboard"); ?>"><strong>sua área de membros</strong></a> para visualizar seus ingressos!

<p>Estamos muito felizes em contar com você no evento geek mais mágico do sul do Brasil!</p>

<hr>
<h3>Detalhes do evento:</h3>
<p>
    <?php if (isset($evento) && $evento): ?>
        <strong><?= esc($evento->nome) ?></strong>
        <?php if ($evento->data_inicio && $evento->data_fim): ?>
            <br><?= date('d/m/Y', strtotime($evento->data_inicio)) ?> e <?= date('d/m/Y', strtotime($evento->data_fim)) ?>
        <?php elseif ($evento->data_inicio): ?>
            <br><?= date('d/m/Y', strtotime($evento->data_inicio)) ?>
        <?php endif; ?>
        <?php if ($evento->hora_inicio && $evento->hora_fim): ?>
            das <?= $evento->hora_inicio ?> às <?= $evento->hora_fim ?>
        <?php elseif ($evento->hora_inicio): ?>
            às <?= $evento->hora_inicio ?>
        <?php endif; ?>
        <?php if ($evento->local): ?>
            <br><?= esc($evento->local) ?>
        <?php endif; ?>
        <?php if ($evento->endereco): ?>
            <br><?= esc($evento->endereco) ?><?= $evento->numero ? ', ' . esc($evento->numero) : '' ?>
            <?php if ($evento->bairro): ?>
                <br><?= esc($evento->bairro) ?>
            <?php endif; ?>
            <?php if ($evento->cidade && $evento->estado): ?>
                <br><?= esc($evento->cidade) ?> - <?= esc($evento->estado) ?>
            <?php endif; ?>
        <?php endif; ?>
    <?php else: ?>
        <strong>Dreamfest 23 - Mega Festival Geek</strong>
        <br>10 e 11 de junho das 10h às 20h
        <br>Centro de eventos da PUCRS - Porto Alegre RS
    <?php endif; ?>
    <hr>
    Geek que é geek não 😴 no ponto!
</p>