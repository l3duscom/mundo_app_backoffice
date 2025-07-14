<h3>Saudações <?php echo esc($cliente->nome); ?></h3>

<p>O checkin da sua inscrição <?= $inscricao->codigo ?> para o <?= $concurso->nome ?> foi realziado com sucesso e sua apresentação está liberada!</p>

<p>Sua ordem de apresentação no Palco Mundo é:<br><strong><?= $inscricao->ordem ?>º</strong></p>
<hr>
<h3>Resumo da inscrição:</h3>
<p>
    <strong><?= $inscricao->status ?></strong>
    <?php if ($inscricao->categoria == null) : ?>
        <br>Personagem: <?= $inscricao->personagem ?>
        <br>Obra: <?= $inscricao->obra ?>
        <br>Gênero: <?= $inscricao->genero ?>
        <br>Imagem de referência: <a href="<?= site_url("concursos/imagem/$inscricao->referencia"); ?>" target="_blank"> Visualizar imagem de referência</a>

    <?php else : ?>
        <br>Video classificatórias: <a href="<?= $inscricao->video_apresentacao ?>" target="_blank"> <?= $inscricao->video_apresentacao ?></a>
        <br>Imagem de referência: <a href="<?= site_url("concursos/imagem/$inscricao->referencia"); ?>" target="_blank"> Visualizar imagem de referência</a>
        <br>Áudio da apresentação: <a href="<?= site_url("concursos/imagem/$inscricao->musica"); ?>" target="_blank"> Tocar música</a>
        <br>Video classificatórias: <a href="<?= site_url("concursos/imagem/$inscricao->video_led"); ?>" target="_blank"> Visualizar video</a>
    <?php endif; ?>

    <hr>
</p>
<hr>
<p><strong>Acesse:</strong><a href="https://mundodream.com.br"> sua área de membros</a> com o email <?= $cliente->email ?> para fazer acompanhar sua inscrição.</p>
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
        <strong>Dreamfest 25 Parte 2 - Mega Convenção Geek</strong>
        <br>6 e 7 de dezembro das 11h às 19h
        <br>Centro de eventos da FENAC - NH
    <?php endif; ?>
    <hr>
    Geek que é geek não 😴 no ponto!
</p>