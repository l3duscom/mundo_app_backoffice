<?php
$tipoLabel = $checklist->getTipoLabel();
$corTopo   = $checklist->tipo === 'saida' ? '#fd7e14' : '#198754';
$codigo    = $contrato->codigo ?? ('#' . $contrato->id);
$nomeExp   = method_exists($expositor, 'getNomeExibicao') ? $expositor->getNomeExibicao() : ($expositor->nome ?? 'Expositor');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 640px; margin: 0 auto; padding: 20px;">

    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: <?= $corTopo ?>; margin-bottom: 5px;">✅ Checklist de <?= esc($tipoLabel) ?> concluído</h1>
        <p style="color: #6c757d; font-size: 15px;"><?= esc($evento->nome ?? 'Evento') ?></p>
    </div>

    <p>Olá <strong><?= esc($nomeExp) ?></strong>,</p>

    <p>
        Informamos que o <strong>checklist de <?= esc(strtolower($tipoLabel)) ?></strong> referente ao seu credenciamento
        no evento <strong><?= esc($evento->nome ?? '') ?></strong> foi <strong>concluído</strong>.
    </p>

    <div style="background-color: #f8f9fa; border-radius: 10px; padding: 18px; margin: 22px 0;">
        <h3 style="margin-top: 0; color: #333;">📋 Resumo</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 6px 0; border-bottom: 1px solid #e9ecef;"><strong>Contrato:</strong></td>
                <td style="padding: 6px 0; border-bottom: 1px solid #e9ecef;"><?= esc($codigo) ?></td>
            </tr>
            <tr>
                <td style="padding: 6px 0; border-bottom: 1px solid #e9ecef;"><strong>Tipo:</strong></td>
                <td style="padding: 6px 0; border-bottom: 1px solid #e9ecef;"><?= esc($tipoLabel) ?></td>
            </tr>
            <tr>
                <td style="padding: 6px 0; border-bottom: 1px solid #e9ecef;"><strong>Concluído em:</strong></td>
                <td style="padding: 6px 0; border-bottom: 1px solid #e9ecef;">
                    <?= $checklist->conferido_em ? date('d/m/Y H:i', strtotime($checklist->conferido_em)) : date('d/m/Y H:i') ?>
                </td>
            </tr>
            <?php if (!empty($conferidoPorNome)) : ?>
            <tr>
                <td style="padding: 6px 0; border-bottom: 1px solid #e9ecef;"><strong>Conferido por:</strong></td>
                <td style="padding: 6px 0; border-bottom: 1px solid #e9ecef;"><?= esc($conferidoPorNome) ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <?php if (!empty($itens)) : ?>
    <div style="margin: 22px 0;">
        <h3 style="margin-bottom: 10px; color: #333;">📝 Itens conferidos</h3>
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <thead>
                <tr style="background-color: #f1f3f5;">
                    <th style="padding: 8px; border: 1px solid #dee2e6; text-align: left;">Item</th>
                    <th style="padding: 8px; border: 1px solid #dee2e6; text-align: center; width: 60px;">Qtd.</th>
                    <th style="padding: 8px; border: 1px solid #dee2e6; text-align: center; width: 60px;">OK</th>
                    <th style="padding: 8px; border: 1px solid #dee2e6; text-align: left;">Observação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($itens as $it) : ?>
                <tr>
                    <td style="padding: 8px; border: 1px solid #dee2e6;"><?= esc($it->titulo) ?></td>
                    <td style="padding: 8px; border: 1px solid #dee2e6; text-align: center;"><?= (int) $it->quantidade ?></td>
                    <td style="padding: 8px; border: 1px solid #dee2e6; text-align: center;">
                        <?= $it->checked ? '✅' : '—' ?>
                    </td>
                    <td style="padding: 8px; border: 1px solid #dee2e6;"><?= esc($it->observacao ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if (!empty($checklist->observacoes)) : ?>
    <div style="background-color: #fff3cd; border: 1px solid #ffe69c; border-radius: 10px; padding: 16px; margin: 22px 0;">
        <h4 style="margin-top: 0; color: #664d03;">Observações da conferência</h4>
        <p style="margin-bottom: 0; color: #664d03; white-space: pre-line;"><?= esc($checklist->observacoes) ?></p>
    </div>
    <?php endif; ?>

    <div style="background-color: #f8f9fa; border-radius: 10px; padding: 16px; margin: 22px 0;">
        <p style="margin-bottom: 0;">
            Em caso de dúvidas, fale com a gente em
            <a href="mailto:relacionamento@mundodream.com.br" style="color: #0d6efd;">relacionamento@mundodream.com.br</a>.
        </p>
    </div>

    <hr style="border: none; border-top: 1px solid #e9ecef; margin: 28px 0;">

    <p style="font-size: 12px; color: #6c757d; text-align: center;">
        Este é um email automático do sistema de credenciamento.<br>
        Por favor, não responda diretamente a este email.
    </p>

</body>
</html>
