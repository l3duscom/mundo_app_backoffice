-- =====================================================
-- SCRIPT DE TESTE: Dados de Envio
-- =====================================================
-- Este script testa a query usada na exportação de envios
-- Para usar: substitua @evento_id pelo ID do evento desejado
-- =====================================================

SET @evento_id = 17; -- ALTERE PARA O ID DO SEU EVENTO

-- =====================================================
-- 1. QUERY PRINCIPAL (igual ao Model)
-- =====================================================

SELECT 
    c.nome,
    '' as empresa,
    c.cpf,
    COALESCE(e.cep, c.cep) as cep,
    COALESCE(e.endereco, c.endereco) as endereco,
    
    -- Extrai o número (pega de enderecos ou clientes)
    SUBSTRING_INDEX(COALESCE(e.numero, c.numero), ' ', 1) as numero,
    
    -- Extrai o complemento
    CASE 
        WHEN SUBSTRING_INDEX(COALESCE(e.numero, c.numero), ' ', 1) <> 
             SUBSTRING(COALESCE(e.numero, c.numero), LOCATE(' ', COALESCE(e.numero, c.numero)) + 1) 
        THEN SUBSTRING(COALESCE(e.numero, c.numero), LOCATE(' ', COALESCE(e.numero, c.numero)) + 1) 
        ELSE NULL 
    END AS complemento,
    
    COALESCE(e.bairro, c.bairro) as bairro,
    COALESCE(e.cidade, c.cidade) as cidade,
    COALESCE(e.estado, c.estado) as uf,
    
    '' as aos_cuidados,
    'N' as nota_fiscal,
    '' as servico,
    '' as serv_adicionais,
    p.total as valor_declarado,
    '' as observacoes,
    'Ingressos Dreamfest 25' as conteudo,
    '' as ddd,
    '' as telefone,
    c.email,
    '' as chave,
    '0,1' as peso,
    '1' as altura,
    '10' as largura,
    '15' as comprimento,
    '' as entrega_vizinho,
    '' as rfid,
    p.id as pedido_id,
    p.cod_pedido,
    p.rastreio,
    
    -- CAMPOS DE DEBUG (remover na exportação final)
    CASE 
        WHEN e.cep IS NOT NULL THEN 'Endereços' 
        ELSE 'Clientes' 
    END as origem_endereco

FROM pedidos p

INNER JOIN clientes c ON c.usuario_id = p.user_id

-- LEFT JOIN: permite que pedidos sem endereço em 'enderecos' apareçam
LEFT JOIN (
    SELECT e1.pedido_id, e1.cep, e1.endereco, e1.numero, e1.bairro, e1.cidade, e1.estado, e1.created_at
    FROM enderecos e1
    INNER JOIN (
        SELECT pedido_id, MAX(created_at) AS max_updated
        FROM enderecos
        GROUP BY pedido_id
    ) sub ON e1.pedido_id = sub.pedido_id AND e1.created_at = sub.max_updated
) e ON e.pedido_id = p.id

WHERE 
    p.frete = 1 
    AND (p.rastreio IS NULL OR p.rastreio = '') 
    AND p.evento_id = @evento_id
    AND p.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH')
    
ORDER BY c.nome ASC;

-- =====================================================
-- 2. ESTATÍSTICAS
-- =====================================================

SELECT 
    '===== ESTATÍSTICAS DE ENVIO =====' as info;

SELECT 
    COUNT(*) as total_pedidos_para_envio
FROM pedidos p
WHERE p.frete = 1 
    AND (p.rastreio IS NULL OR p.rastreio = '') 
    AND p.evento_id = @evento_id
    AND p.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH');

-- =====================================================
-- 3. ORIGEM DOS ENDEREÇOS (Debug)
-- =====================================================

SELECT 
    '===== ORIGEM DOS ENDEREÇOS =====' as info;

SELECT 
    CASE 
        WHEN e.cep IS NOT NULL THEN 'Tabela: enderecos' 
        ELSE 'Tabela: clientes (FALLBACK)' 
    END as origem,
    COUNT(*) as quantidade
FROM pedidos p
INNER JOIN clientes c ON c.usuario_id = p.user_id
LEFT JOIN (
    SELECT e1.pedido_id, e1.cep
    FROM enderecos e1
    INNER JOIN (
        SELECT pedido_id, MAX(created_at) AS max_updated
        FROM enderecos
        GROUP BY pedido_id
    ) sub ON e1.pedido_id = sub.pedido_id AND e1.created_at = sub.max_updated
) e ON e.pedido_id = p.id
WHERE 
    p.frete = 1 
    AND (p.rastreio IS NULL OR p.rastreio = '') 
    AND p.evento_id = @evento_id
    AND p.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH')
GROUP BY origem;

-- =====================================================
-- 4. VERIFICAR ENDEREÇOS INCOMPLETOS
-- =====================================================

SELECT 
    '===== ENDEREÇOS INCOMPLETOS (ATENÇÃO!) =====' as info;

SELECT 
    p.id as pedido_id,
    p.cod_pedido,
    c.nome,
    c.email,
    COALESCE(e.cep, c.cep) as cep,
    COALESCE(e.cidade, c.cidade) as cidade,
    CASE 
        WHEN COALESCE(e.cep, c.cep) IS NULL THEN 'SEM CEP'
        WHEN COALESCE(e.endereco, c.endereco) IS NULL THEN 'SEM ENDEREÇO'
        WHEN COALESCE(e.cidade, c.cidade) IS NULL THEN 'SEM CIDADE'
        WHEN COALESCE(e.estado, c.estado) IS NULL THEN 'SEM ESTADO'
        ELSE 'OK'
    END as problema
FROM pedidos p
INNER JOIN clientes c ON c.usuario_id = p.user_id
LEFT JOIN (
    SELECT e1.pedido_id, e1.cep, e1.endereco, e1.cidade, e1.estado
    FROM enderecos e1
    INNER JOIN (
        SELECT pedido_id, MAX(created_at) AS max_updated
        FROM enderecos
        GROUP BY pedido_id
    ) sub ON e1.pedido_id = sub.pedido_id AND e1.created_at = sub.max_updated
) e ON e.pedido_id = p.id
WHERE 
    p.frete = 1 
    AND (p.rastreio IS NULL OR p.rastreio = '') 
    AND p.evento_id = @evento_id
    AND p.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH')
    AND (
        COALESCE(e.cep, c.cep) IS NULL OR
        COALESCE(e.endereco, c.endereco) IS NULL OR
        COALESCE(e.cidade, c.cidade) IS NULL OR
        COALESCE(e.estado, c.estado) IS NULL
    );

-- =====================================================
-- 5. DISTRIBUIÇÃO POR ESTADO
-- =====================================================

SELECT 
    '===== DISTRIBUIÇÃO POR ESTADO =====' as info;

SELECT 
    COALESCE(e.estado, c.estado) as uf,
    COUNT(*) as quantidade,
    CONCAT(ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) 
        FROM pedidos p2 
        WHERE p2.frete = 1 
        AND (p2.rastreio IS NULL OR p2.rastreio = '') 
        AND p2.evento_id = @evento_id
        AND p2.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH')
    )), 2), '%') as percentual
FROM pedidos p
INNER JOIN clientes c ON c.usuario_id = p.user_id
LEFT JOIN (
    SELECT e1.pedido_id, e1.estado
    FROM enderecos e1
    INNER JOIN (
        SELECT pedido_id, MAX(created_at) AS max_updated
        FROM enderecos
        GROUP BY pedido_id
    ) sub ON e1.pedido_id = sub.pedido_id AND e1.created_at = sub.max_updated
) e ON e.pedido_id = p.id
WHERE 
    p.frete = 1 
    AND (p.rastreio IS NULL OR p.rastreio = '') 
    AND p.evento_id = @evento_id
    AND p.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH')
GROUP BY uf
ORDER BY quantidade DESC;

-- =====================================================
-- 6. VALOR TOTAL A DECLARAR
-- =====================================================

SELECT 
    '===== VALOR TOTAL A DECLARAR =====' as info;

SELECT 
    COUNT(*) as total_pedidos,
    CONCAT('R$ ', FORMAT(SUM(p.total), 2, 'pt_BR')) as valor_total_declarado,
    CONCAT('R$ ', FORMAT(AVG(p.total), 2, 'pt_BR')) as valor_medio_por_pedido
FROM pedidos p
WHERE 
    p.frete = 1 
    AND (p.rastreio IS NULL OR p.rastreio = '') 
    AND p.evento_id = @evento_id
    AND p.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH');

-- =====================================================
-- FIM DO SCRIPT DE TESTE
-- =====================================================

