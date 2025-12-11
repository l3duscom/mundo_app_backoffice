<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #198754; margin-bottom: 5px;">✅ Contrato Assinado!</h1>
        <p style="color: #6c757d; font-size: 14px;">Notificação automática do sistema</p>
    </div>

    <div style="background-color: #d1e7dd; border: 1px solid #badbcc; border-radius: 10px; padding: 20px; margin-bottom: 25px;">
        <p style="margin: 0; color: #0f5132;">
            <strong>📝 Um novo contrato foi assinado pelo expositor!</strong>
        </p>
    </div>

    <div style="background-color: #f8f9fa; border-radius: 10px; padding: 20px; margin: 25px 0;">
        <h3 style="margin-top: 0; color: #333;">📋 Dados do Contrato</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><strong>Código do Contrato:</strong></td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><?php echo esc($contrato->codigo ?? '#' . $contrato->id); ?></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><strong>Valor do Contrato:</strong></td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;">R$ <?php echo number_format($contrato->valor_final ?? 0, 2, ',', '.'); ?></td>
            </tr>
        </table>
    </div>

    <div style="background-color: #f8f9fa; border-radius: 10px; padding: 20px; margin: 25px 0;">
        <h3 style="margin-top: 0; color: #333;">🏢 Dados do Expositor</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><strong>Nome:</strong></td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><?php echo esc($expositor->getNomeExibicao()); ?></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><strong><?php echo $expositor->getLabelDocumento(); ?>:</strong></td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><?php echo $expositor->getDocumentoFormatado(); ?></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><strong>Email:</strong></td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><?php echo esc($expositor->email ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><strong>Telefone:</strong></td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><?php echo esc($expositor->telefone ?? 'N/A'); ?></td>
            </tr>
        </table>
    </div>

    <div style="background-color: #f8f9fa; border-radius: 10px; padding: 20px; margin: 25px 0;">
        <h3 style="margin-top: 0; color: #333;">✍️ Dados da Assinatura</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><strong>Assinado por:</strong></td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><?php echo esc($documento->assinado_por ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><strong>Documento:</strong></td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><?php echo esc($documento->documento_assinante ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><strong>Data/Hora:</strong></td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><?php echo $documento->data_assinatura ? date('d/m/Y H:i:s', strtotime($documento->data_assinatura)) : 'N/A'; ?></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><strong>IP:</strong></td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><?php echo esc($documento->ip_assinatura ?? 'N/A'); ?></td>
            </tr>
        </table>
    </div>

    <?php if (isset($evento) && $evento): ?>
    <div style="background-color: #f8f9fa; border-radius: 10px; padding: 20px; margin: 25px 0;">
        <h3 style="margin-top: 0; color: #333;">📅 Evento</h3>
        <p style="margin: 0;"><strong><?php echo esc($evento->nome); ?></strong></p>
    </div>
    <?php endif; ?>

    <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;">
        <strong>⚠️ Próximo passo:</strong><br>
        Acesse o backoffice para <strong>confirmar</strong> o documento assinado e finalizar o processo do contrato.
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="<?php echo site_url("contratodocumentos/gerenciar/{$contrato->id}"); ?>" 
           style="display: inline-block; background-color: #0d6efd; color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px; font-weight: bold;">
            📂 Acessar Gerenciamento do Documento
        </a>
    </div>

    <hr style="border: none; border-top: 1px solid #e9ecef; margin: 30px 0;">

    <p style="font-size: 12px; color: #6c757d; text-align: center;">
        Este é um email automático do sistema de gestão de contratos.<br>
        Enviado em <?php echo date('d/m/Y H:i:s'); ?>
    </p>

</body>
</html>
