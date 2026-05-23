-- ========================================
-- Corrige "Stand" -> "Estande" no bloco de multa
-- inserido anteriormente em modelos e documentos.
-- ========================================

SET SQL_SAFE_UPDATES = 0;

UPDATE contrato_documento_modelos
SET conteudo_html = REPLACE(
    conteudo_html,
    'O Stand só poderá ser montado',
    'O Estande só poderá ser montado'
)
WHERE conteudo_html LIKE '%O Stand só poderá ser montado%';

UPDATE contrato_documentos
SET conteudo_html = REPLACE(
    conteudo_html,
    'O Stand só poderá ser montado',
    'O Estande só poderá ser montado'
)
WHERE conteudo_html LIKE '%O Stand só poderá ser montado%';

SET SQL_SAFE_UPDATES = 1;
