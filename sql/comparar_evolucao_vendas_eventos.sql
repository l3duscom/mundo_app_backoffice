-- ============================================================
-- SQL PARA COMPARAR EVOLUÇÃO DE VENDAS ENTRE 2 EVENTOS
-- ============================================================
-- Este script compara a evolução das vendas de dois eventos
-- desde a primeira venda até a última, mostrando:
-- - Vendas acumuladas ao longo do tempo
-- - Receita acumulada
-- - Velocidade de vendas
-- - Comparação dia a dia
-- ============================================================
-- REQUISITOS: MySQL 8.0+ (usa CTEs e Window Functions)
-- Para MySQL 5.7 ou anterior, use o arquivo sem CTEs
-- ============================================================

-- CONFIGURAÇÃO: Altere os IDs dos eventos que deseja comparar
SET @evento1_id = 17; -- ID do primeiro evento
SET @evento2_id = 18; -- ID do segundo evento

-- CONFIGURAÇÃO: Altere os status conforme seu banco de dados
-- Opção 1: 'CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH'
-- Opção 2: 'APROVADO', 'CONFIRMADO', 'PAGO'
SET @status1 = 'CONFIRMED';
SET @status2 = 'RECEIVED';
SET @status3 = 'RECEIVED_IN_CASH';

-- CONFIGURAÇÃO: ID do ticket de cortesia a ser ignorado
SET @ticket_cortesia = 608;

-- ============================================================
-- 1. VISÃO GERAL DOS EVENTOS
-- ============================================================
SELECT 
    e.id AS event_id,
    e.nome AS evento_nome,
    DATE_FORMAT(e.data_inicio, '%d/%m/%Y') AS data_evento,
    COUNT(DISTINCT p.id) AS total_pedidos,
    COUNT(i.id) AS total_ingressos,
    SUM(CASE WHEN p.status IN (@status1, @status2, @status3) THEN p.total ELSE 0 END) AS receita_total,
    MIN(p.created_at) AS primeira_venda,
    MAX(p.created_at) AS ultima_venda,
    DATEDIFF(MAX(p.created_at), MIN(p.created_at)) + 1 AS dias_vendas,
    COUNT(DISTINCT DATE(p.created_at)) AS dias_com_vendas
FROM eventos e
LEFT JOIN pedidos p ON e.id = p.event_id 
    AND p.status IN (@status1, @status2, @status3)
LEFT JOIN ingressos i ON p.id = i.pedido_id 
    AND i.ticket_id <> @ticket_cortesia
WHERE e.id IN (@evento1_id, @evento2_id)
GROUP BY e.id, e.nome, e.data_inicio
ORDER BY e.id;

-- ============================================================
-- 2. EVOLUÇÃO DIÁRIA COMPARATIVA
-- ============================================================
WITH vendas_diarias AS (
    SELECT 
        p.event_id,
        DATE(p.created_at) AS data_venda,
        COUNT(DISTINCT p.id) AS pedidos_dia,
        COUNT(i.id) AS ingressos_dia,
        SUM(p.total) AS receita_dia
    FROM pedidos p
    LEFT JOIN ingressos i ON p.id = i.pedido_id 
        AND i.ticket_id <> @ticket_cortesia
    WHERE p.event_id IN (@evento1_id, @evento2_id)
        AND p.status IN (@status1, @status2, @status3)
    GROUP BY p.event_id, DATE(p.created_at)
),
vendas_acumuladas AS (
    SELECT 
        vd.event_id,
        vd.data_venda,
        vd.pedidos_dia,
        vd.ingressos_dia,
        vd.receita_dia,
        SUM(vd.pedidos_dia) OVER (PARTITION BY vd.event_id ORDER BY vd.data_venda) AS pedidos_acumulados,
        SUM(vd.ingressos_dia) OVER (PARTITION BY vd.event_id ORDER BY vd.data_venda) AS ingressos_acumulados,
        SUM(vd.receita_dia) OVER (PARTITION BY vd.event_id ORDER BY vd.data_venda) AS receita_acumulada,
        ROW_NUMBER() OVER (PARTITION BY vd.event_id ORDER BY vd.data_venda) AS dia_venda
    FROM vendas_diarias vd
)
SELECT 
    va1.dia_venda,
    DATE_FORMAT(va1.data_venda, '%d/%m/%Y') AS data_evento1,
    va1.pedidos_dia AS pedidos_dia_ev1,
    va1.ingressos_dia AS ingressos_dia_ev1,
    va1.receita_dia AS receita_dia_ev1,
    va1.pedidos_acumulados AS pedidos_acum_ev1,
    va1.ingressos_acumulados AS ingressos_acum_ev1,
    va1.receita_acumulada AS receita_acum_ev1,
    DATE_FORMAT(va2.data_venda, '%d/%m/%Y') AS data_evento2,
    va2.pedidos_dia AS pedidos_dia_ev2,
    va2.ingressos_dia AS ingressos_dia_ev2,
    va2.receita_dia AS receita_dia_ev2,
    va2.pedidos_acumulados AS pedidos_acum_ev2,
    va2.ingressos_acumulados AS ingressos_acum_ev2,
    va2.receita_acumulada AS receita_acum_ev2,
    -- DIFERENÇAS
    (va1.ingressos_acumulados - COALESCE(va2.ingressos_acumulados, 0)) AS diff_ingressos,
    (va1.receita_acumulada - COALESCE(va2.receita_acumulada, 0)) AS diff_receita,
    ROUND((va1.ingressos_acumulados / NULLIF(va2.ingressos_acumulados, 0) * 100) - 100, 2) AS perc_evolucao_ingressos,
    ROUND((va1.receita_acumulada / NULLIF(va2.receita_acumulada, 0) * 100) - 100, 2) AS perc_evolucao_receita
FROM vendas_acumuladas va1
LEFT JOIN vendas_acumuladas va2 ON va1.dia_venda = va2.dia_venda 
    AND va2.event_id = @evento2_id
WHERE va1.event_id = @evento1_id
ORDER BY va1.dia_venda;

-- ============================================================
-- 3. COMPARAÇÃO POR PERÍODOS (PRIMEIRA SEMANA, PRIMEIRO MÊS, ETC)
-- ============================================================
WITH primeira_venda AS (
    SELECT 
        event_id,
        MIN(created_at) AS inicio
    FROM pedidos
    WHERE event_id IN (@evento1_id, @evento2_id)
        AND status IN (@status1, @status2, @status3)
    GROUP BY event_id
),
vendas_periodo AS (
    SELECT 
        p.event_id,
        CASE 
            WHEN DATEDIFF(p.created_at, pv.inicio) <= 7 THEN '1. Primeira Semana'
            WHEN DATEDIFF(p.created_at, pv.inicio) <= 14 THEN '2. Segunda Semana'
            WHEN DATEDIFF(p.created_at, pv.inicio) <= 21 THEN '3. Terceira Semana'
            WHEN DATEDIFF(p.created_at, pv.inicio) <= 30 THEN '4. Primeiro Mês'
            WHEN DATEDIFF(p.created_at, pv.inicio) <= 60 THEN '5. Segundo Mês'
            ELSE '6. Demais Períodos'
        END AS periodo,
        COUNT(DISTINCT p.id) AS pedidos,
        COUNT(i.id) AS ingressos,
        SUM(p.total) AS receita
    FROM pedidos p
    INNER JOIN primeira_venda pv ON p.event_id = pv.event_id
    LEFT JOIN ingressos i ON p.id = i.pedido_id 
        AND i.ticket_id <> @ticket_cortesia
    WHERE p.event_id IN (@evento1_id, @evento2_id)
        AND p.status IN (@status1, @status2, @status3)
    GROUP BY p.event_id, periodo
)
SELECT 
    vp1.periodo,
    vp1.pedidos AS pedidos_ev1,
    vp1.ingressos AS ingressos_ev1,
    vp1.receita AS receita_ev1,
    COALESCE(vp2.pedidos, 0) AS pedidos_ev2,
    COALESCE(vp2.ingressos, 0) AS ingressos_ev2,
    COALESCE(vp2.receita, 0) AS receita_ev2,
    (vp1.ingressos - COALESCE(vp2.ingressos, 0)) AS diff_ingressos,
    (vp1.receita - COALESCE(vp2.receita, 0)) AS diff_receita,
    ROUND((vp1.ingressos / NULLIF(vp2.ingressos, 0) * 100) - 100, 2) AS perc_evolucao_ingressos,
    ROUND((vp1.receita / NULLIF(vp2.receita, 0) * 100) - 100, 2) AS perc_evolucao_receita
FROM vendas_periodo vp1
LEFT JOIN vendas_periodo vp2 ON vp1.periodo = vp2.periodo 
    AND vp2.event_id = @evento2_id
WHERE vp1.event_id = @evento1_id
ORDER BY vp1.periodo;

-- ============================================================
-- 4. VELOCIDADE DE VENDAS (TEMPO PARA ATINGIR MARCOS)
-- ============================================================
WITH primeira_venda AS (
    SELECT 
        event_id,
        MIN(created_at) AS inicio
    FROM pedidos
    WHERE event_id IN (@evento1_id, @evento2_id)
        AND status IN (@status1, @status2, @status3)
    GROUP BY event_id
),
vendas_acumuladas AS (
    SELECT 
        p.event_id,
        p.created_at,
        SUM(1) OVER (PARTITION BY p.event_id ORDER BY p.created_at) AS ingressos_acum,
        SUM(p.total) OVER (PARTITION BY p.event_id ORDER BY p.created_at) AS receita_acum
    FROM pedidos p
    INNER JOIN ingressos i ON p.id = i.pedido_id 
        AND i.ticket_id <> @ticket_cortesia
    WHERE p.event_id IN (@evento1_id, @evento2_id)
        AND p.status IN (@status1, @status2, @status3)
),
marcos AS (
    SELECT 
        va.event_id,
        '100 Ingressos' AS marco,
        MIN(DATEDIFF(va.created_at, pv.inicio)) AS dias_para_atingir
    FROM vendas_acumuladas va
    INNER JOIN primeira_venda pv ON va.event_id = pv.event_id
    WHERE va.ingressos_acum >= 100
    GROUP BY va.event_id
    
    UNION ALL
    
    SELECT 
        va.event_id,
        '500 Ingressos' AS marco,
        MIN(DATEDIFF(va.created_at, pv.inicio)) AS dias_para_atingir
    FROM vendas_acumuladas va
    INNER JOIN primeira_venda pv ON va.event_id = pv.event_id
    WHERE va.ingressos_acum >= 500
    GROUP BY va.event_id
    
    UNION ALL
    
    SELECT 
        va.event_id,
        '1000 Ingressos' AS marco,
        MIN(DATEDIFF(va.created_at, pv.inicio)) AS dias_para_atingir
    FROM vendas_acumuladas va
    INNER JOIN primeira_venda pv ON va.event_id = pv.event_id
    WHERE va.ingressos_acum >= 1000
    GROUP BY va.event_id
    
    UNION ALL
    
    SELECT 
        va.event_id,
        'R$ 10.000' AS marco,
        MIN(DATEDIFF(va.created_at, pv.inicio)) AS dias_para_atingir
    FROM vendas_acumuladas va
    INNER JOIN primeira_venda pv ON va.event_id = pv.event_id
    WHERE va.receita_acum >= 10000
    GROUP BY va.event_id
    
    UNION ALL
    
    SELECT 
        va.event_id,
        'R$ 50.000' AS marco,
        MIN(DATEDIFF(va.created_at, pv.inicio)) AS dias_para_atingir
    FROM vendas_acumuladas va
    INNER JOIN primeira_venda pv ON va.event_id = pv.event_id
    WHERE va.receita_acum >= 50000
    GROUP BY va.event_id
    
    UNION ALL
    
    SELECT 
        va.event_id,
        'R$ 100.000' AS marco,
        MIN(DATEDIFF(va.created_at, pv.inicio)) AS dias_para_atingir
    FROM vendas_acumuladas va
    INNER JOIN primeira_venda pv ON va.event_id = pv.event_id
    WHERE va.receita_acum >= 100000
    GROUP BY va.event_id
)
SELECT 
    m1.marco,
    m1.dias_para_atingir AS dias_evento1,
    COALESCE(m2.dias_para_atingir, 'Não atingido') AS dias_evento2,
    CASE 
        WHEN m2.dias_para_atingir IS NULL THEN 'Evento 1 atingiu, Evento 2 não'
        WHEN m1.dias_para_atingir < m2.dias_para_atingir THEN CONCAT('Evento 1 mais rápido (', m2.dias_para_atingir - m1.dias_para_atingir, ' dias)')
        WHEN m1.dias_para_atingir > m2.dias_para_atingir THEN CONCAT('Evento 2 mais rápido (', m1.dias_para_atingir - m2.dias_para_atingir, ' dias)')
        ELSE 'Mesma velocidade'
    END AS comparacao
FROM marcos m1
LEFT JOIN marcos m2 ON m1.marco = m2.marco 
    AND m2.event_id = @evento2_id
WHERE m1.event_id = @evento1_id
ORDER BY m1.marco;

-- ============================================================
-- 5. RESUMO EXECUTIVO
-- ============================================================
SELECT 
    'RESUMO COMPARATIVO' AS tipo,
    @evento1_id AS evento1,
    @evento2_id AS evento2,
    CONCAT(
        'Evento ', @evento1_id, ' teve ',
        ROUND((
            (SELECT COUNT(*) FROM ingressos i 
             INNER JOIN pedidos p ON i.pedido_id = p.id 
             WHERE p.event_id = @evento1_id AND p.status IN (@status1, @status2, @status3)
                 AND i.ticket_id <> @ticket_cortesia) /
            NULLIF((SELECT COUNT(*) FROM ingressos i 
                    INNER JOIN pedidos p ON i.pedido_id = p.id 
                    WHERE p.event_id = @evento2_id AND p.status IN (@status1, @status2, @status3)
                        AND i.ticket_id <> @ticket_cortesia), 0)
            * 100
        ) - 100, 2),
        '% ',
        IF((SELECT COUNT(*) FROM ingressos i 
            INNER JOIN pedidos p ON i.pedido_id = p.id 
            WHERE p.event_id = @evento1_id AND p.status IN (@status1, @status2, @status3)
                AND i.ticket_id <> @ticket_cortesia) >
           (SELECT COUNT(*) FROM ingressos i 
            INNER JOIN pedidos p ON i.pedido_id = p.id 
            WHERE p.event_id = @evento2_id AND p.status IN (@status1, @status2, @status3)
                AND i.ticket_id <> @ticket_cortesia),
           'a mais', 'a menos'),
        ' ingressos que Evento ', @evento2_id
    ) AS analise_ingressos,
    CONCAT(
        'Evento ', @evento1_id, ' teve ',
        ROUND((
            (SELECT SUM(p.total) FROM pedidos p 
             WHERE p.event_id = @evento1_id AND p.status IN (@status1, @status2, @status3)) /
            NULLIF((SELECT SUM(p.total) FROM pedidos p 
                    WHERE p.event_id = @evento2_id AND p.status IN (@status1, @status2, @status3)), 0)
            * 100
        ) - 100, 2),
        '% ',
        IF((SELECT SUM(p.total) FROM pedidos p 
            WHERE p.event_id = @evento1_id AND p.status IN (@status1, @status2, @status3)) >
           (SELECT SUM(p.total) FROM pedidos p 
            WHERE p.event_id = @evento2_id AND p.status IN (@status1, @status2, @status3)),
           'a mais', 'a menos'),
        ' receita que Evento ', @evento2_id
    ) AS analise_receita;

