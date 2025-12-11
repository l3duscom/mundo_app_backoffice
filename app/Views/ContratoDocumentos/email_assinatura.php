<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #6f42c1; margin-bottom: 5px;">📝 Contrato Pronto para Assinatura</h1>
    </div>

    <p>Olá <strong><?php echo esc($expositor->nome_fantasia ?? $expositor->razao_social ?? 'Expositor'); ?></strong>,</p>

    <p>Temos uma ótima notícia! O contrato referente à sua participação no evento está pronto para assinatura.</p>

    <div style="background-color: #f8f9fa; border-radius: 10px; padding: 20px; margin: 25px 0;">
        <h3 style="margin-top: 0; color: #333;">📋 Detalhes do Contrato</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><strong>Código do Contrato:</strong></td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><?php echo esc($contrato->codigo ?? '#' . $contrato->id); ?></td>
            </tr>
            <?php if (isset($evento) && $evento): ?>
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><strong>Evento:</strong></td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><?php echo esc($evento->nome); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><strong>Valor do Contrato:</strong></td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;">R$ <?php echo number_format($contrato->valor_final ?? 0, 2, ',', '.'); ?></td>
            </tr>
        </table>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <p><strong>Para assinar o contrato, clique no botão abaixo:</strong></p>
        <a href="<?php echo esc($url_assinatura); ?>" 
           style="display: inline-block; background-color: #6f42c1; color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;">
            ✍️ Assinar Contrato
        </a>
    </div>

    <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;">
        <strong>⚠️ Importante:</strong><br>
        Este link é pessoal e intransferível. Ao assinar, você confirma ter lido e concordado com todos os termos do contrato.
    </div>

    <p>Se você não conseguir clicar no botão, copie e cole o link abaixo no seu navegador:</p>
    <p style="word-break: break-all; background-color: #e9ecef; padding: 10px; border-radius: 5px; font-size: 12px;">
        <?php echo esc($url_assinatura); ?>
    </p>

    <hr style="border: none; border-top: 1px solid #e9ecef; margin: 30px 0;">

    <p style="font-size: 12px; color: #6c757d;">
        Este é um email automático. Se você não reconhece esta solicitação ou tem dúvidas, por favor entre em contato conosco.
    </p>

    <p>Atenciosamente,<br>
    <strong>Equipe de Eventos</strong></p>

</body>
</html>
