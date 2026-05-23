-- ========================================
-- Insere bloco de multa por atraso ANTES da Cláusula 3
-- (3. DO PERÍODO E REALIZAÇÃO)
-- Atualiza modelos e todos os documentos já gerados.
-- Idempotente: a cláusula WHERE evita duplicação se rodar de novo.
-- ========================================

-- Desativa safe update mode da sessão (MySQL Workbench)
SET SQL_SAFE_UPDATES = 0;

-- Modelos de documento
UPDATE contrato_documento_modelos
SET conteudo_html = REPLACE(
    conteudo_html,
    '<h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px; margin-top: 30px;">3. DO PERÍODO E REALIZAÇÃO</h3>',
    '<div style="margin: 20px 0;"><p><strong>Multa de 10% do valor devido sobre atraso de cada parcela.</strong></p><p><strong>Parágrafo Primeiro</strong> &ndash; Após o sétimo dia de atraso, sua vaga será cancelada sem reembolso do sinal.</p><p><strong>Parágrafo Segundo</strong> &ndash; O Estande só poderá ser montado e devidamente organizado após o pagamento de 100% do valor deste contrato.</p></div><h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px; margin-top: 30px;">3. DO PERÍODO E REALIZAÇÃO</h3>'
)
WHERE conteudo_html LIKE '%3. DO PERÍODO E REALIZAÇÃO%'
  AND conteudo_html NOT LIKE '%Multa de 10%% do valor devido%';

-- Documentos já gerados (mesma lógica)

UPDATE contrato_documentos
SET conteudo_html = REPLACE(
    conteudo_html,
    '<h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px; margin-top: 30px;">3. DO PERÍODO E REALIZAÇÃO</h3>',
    '<div style="margin: 20px 0;"><p><strong>Multa de 10% do valor devido sobre atraso de cada parcela.</strong></p><p><strong>Parágrafo Primeiro</strong> &ndash; Após o sétimo dia de atraso, sua vaga será cancelada sem reembolso do sinal.</p><p><strong>Parágrafo Segundo</strong> &ndash; O Estande só poderá ser montado e devidamente organizado após o pagamento de 100% do valor deste contrato.</p></div><h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px; margin-top: 30px;">3. DO PERÍODO E REALIZAÇÃO</h3>'
)
WHERE conteudo_html LIKE '%3. DO PERÍODO E REALIZAÇÃO%'
  AND conteudo_html NOT LIKE '%Multa de 10%% do valor devido%';

-- Reativa safe update mode
SET SQL_SAFE_UPDATES = 1;
