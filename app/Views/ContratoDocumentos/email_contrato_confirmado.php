<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #198754; margin-bottom: 5px;">🎉 Contrato Confirmado!</h1>
        <p style="color: #6c757d; font-size: 16px;">Bem-vindo(a) ao <?php echo esc($evento->nome ?? 'nosso evento'); ?>!</p>
    </div>

    <p>Olá <strong><?php echo esc($expositor->getNomeExibicao()); ?></strong>,</p>

    <p>Temos o prazer de informar que seu <strong>contrato foi confirmado</strong> com sucesso! Agora você está oficialmente confirmado(a) como expositor(a) em nosso evento.</p>

    <div style="background-color: #d1e7dd; border: 1px solid #badbcc; border-radius: 10px; padding: 20px; margin: 25px 0; text-align: center;">
        <h2 style="margin: 0; color: #0f5132;">✅ Participação Confirmada!</h2>
    </div>

    <div style="background-color: #f8f9fa; border-radius: 10px; padding: 20px; margin: 25px 0;">
        <h3 style="margin-top: 0; color: #333;">📋 Resumo do Contrato</h3>
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
                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><strong>Data de Confirmação:</strong></td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><?php echo date('d/m/Y H:i'); ?></td>
            </tr>
        </table>
    </div>

    <div style="background-color: #cff4fc; border: 1px solid #9eeaf9; border-radius: 10px; padding: 20px; margin: 25px 0;">
        <h3 style="margin-top: 0; color: #055160;">📌 Próximos Passos</h3>
        <p style="margin-bottom: 0; color: #055160;">
            <strong>Em breve você receberá o link para realizar o seu CREDENCIAMENTO.</strong>
        </p>
        <p style="margin-top: 10px; color: #055160;">
            ⚠️ <strong>IMPORTANTE:</strong> O credenciamento é <u>obrigatório</u> para participação no evento. 
            Fique atento(a) ao seu email para realizar este procedimento assim que disponível.
        </p>
    </div>

    <div style="background-color: #f8f9fa; border-radius: 10px; padding: 20px; margin: 25px 0;">
        <h3 style="margin-top: 0; color: #333;">📧 Dúvidas?</h3>
        <p style="margin-bottom: 0;">
            Se tiver qualquer dúvida ou precisar de mais informações, entre em contato conosco pelo email 
            <a href="mailto:relacionamento@mundodream.com.br" style="color: #0d6efd;">relacionamento@mundodream.com.br</a>
        </p>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <p style="font-size: 18px; color: #198754;">
            <strong>Estamos ansiosos para recebê-lo(a) em nosso evento!</strong>
        </p>
    </div>

    <hr style="border: none; border-top: 1px solid #e9ecef; margin: 30px 0;">

    <p style="font-size: 12px; color: #6c757d; text-align: center;">
        Este é um email automático do sistema de gestão de contratos.<br>
        Por favor, não responda diretamente a este email.<br>
        Para contato, utilize: <a href="mailto:relacionamento@mundodream.com.br">relacionamento@mundodream.com.br</a>
    </p>

</body>
</html>
