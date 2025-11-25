-- ============================================================
-- SQL PARA COMPARAR EVOLUÇÃO DE VENDAS ENTRE 2 EVENTOS
-- VERSÃO COMPATÍVEL COM MYSQL 5.7 (SEM CTEs)
-- ============================================================
-- Esta versão NÃO usa CTEs (WITH) nem Window Functions (OVER)
-- Funciona em MySQL 5.7 e versões anteriores
-- ============================================================

-- CONFIGURAÇÃO: Altere os IDs dos eventos que deseja comparar
SET @evento1_id = 17;
SET @evento2_id = 18;

-- CONFIGURAÇÃO: Altere os status conforme seu banco de dados
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
LEFT JOIN pedidos p ON e.id = p.evento_id 
    AND p.status IN (@status1, @status2, @status3)
LEFT JOIN ingressos i ON p.id = i.pedido_id 
    AND i.ticket_id <> @ticket_cortesia
WHERE e.id IN (@evento1_id, @evento2_id)
GROUP BY e.id, e.nome, e.data_inicio
ORDER BY e.id;

-- ============================================================
-- 2. EVOLUÇÃO DIÁRIA - EVENTO 1
-- ============================================================
CREATE TEMPORARY TABLE IF NOT EXISTS vendas_diarias_ev1 AS
SELECT 
    DATE(p.created_at) AS data_venda,
    COUNT(DISTINCT p.id) AS pedidos_dia,
    COUNT(i.id) AS ingressos_dia,
    SUM(p.total) AS receita_dia
FROM pedidos p
LEFT JOIN ingressos i ON p.id = i.pedido_id 
    AND i.ticket_id <> @ticket_cortesia
WHERE p.evento_id = @evento1_id
    AND p.status IN (@status1, @status2, @status3)
GROUP BY DATE(p.created_at)
ORDER BY DATE(p.created_at);

-- ============================================================
-- 3. EVOLUÇÃO DIÁRIA - EVENTO 2
-- ============================================================
CREATE TEMPORARY TABLE IF NOT EXISTS vendas_diarias_ev2 AS
SELECT 
    DATE(p.created_at) AS data_venda,
    COUNT(DISTINCT p.id) AS pedidos_dia,
    COUNT(i.id) AS ingressos_dia,
    SUM(p.total) AS receita_dia
FROM pedidos p
LEFT JOIN ingressos i ON p.id = i.pedido_id 
    AND i.ticket_id <> @ticket_cortesia
WHERE p.evento_id = @evento2_id
    AND p.status IN (@status1, @status2, @status3)
GROUP BY DATE(p.created_at)
ORDER BY DATE(p.created_at);

-- ============================================================
-- 4. COMPARAÇÃO DIÁRIA COM ACUMULADOS (SIMULANDO WINDOW FUNCTIONS)
-- ============================================================
-- Adiciona números sequenciais
SET @row_ev1 = 0;
SET @row_ev2 = 0;

CREATE TEMPORARY TABLE IF NOT EXISTS vendas_ev1_numeradas AS
SELECT 
    (@row_ev1 := @row_ev1 + 1) AS dia_venda,
    data_venda,
    pedidos_dia,
    ingressos_dia,
    receita_dia
FROM vendas_diarias_ev1;

CREATE TEMPORARY TABLE IF NOT EXISTS vendas_ev2_numeradas AS
SELECT 
    (@row_ev2 := @row_ev2 + 1) AS dia_venda,
    data_venda,
    pedidos_dia,
    ingressos_dia,
    receita_dia
FROM vendas_diarias_ev2;

-- Calcula acumulados para Evento 1
CREATE TEMPORARY TABLE IF NOT EXISTS vendas_ev1_acumuladas AS
SELECT 
    v1.dia_venda,
    v1.data_venda,
    v1.pedidos_dia,
    v1.ingressos_dia,
    v1.receita_dia,
    (SELECT SUM(v2.pedidos_dia) 
     FROM vendas_ev1_numeradas v2 
     WHERE v2.dia_venda <= v1.dia_venda) AS pedidos_acumulados,
    (SELECT SUM(v2.ingressos_dia) 
     FROM vendas_ev1_numeradas v2 
     WHERE v2.dia_venda <= v1.dia_venda) AS ingressos_acumulados,
    (SELECT SUM(v2.receita_dia) 
     FROM vendas_ev1_numeradas v2 
     WHERE v2.dia_venda <= v1.dia_venda) AS receita_acumulada
FROM vendas_ev1_numeradas v1;

-- Calcula acumulados para Evento 2
CREATE TEMPORARY TABLE IF NOT EXISTS vendas_ev2_acumuladas AS
SELECT 
    v1.dia_venda,
    v1.data_venda,
    v1.pedidos_dia,
    v1.ingressos_dia,
    v1.receita_dia,
    (SELECT SUM(v2.pedidos_dia) 
     FROM vendas_ev2_numeradas v2 
     WHERE v2.dia_venda <= v1.dia_venda) AS pedidos_acumulados,
    (SELECT SUM(v2.ingressos_dia) 
     FROM vendas_ev2_numeradas v2 
     WHERE v2.dia_venda <= v1.dia_venda) AS ingressos_acumulados,
    (SELECT SUM(v2.receita_dia) 
     FROM vendas_ev2_numeradas v2 
     WHERE v2.dia_venda <= v1.dia_venda) AS receita_acumulada
FROM vendas_ev2_numeradas v1;

-- Resultado final comparativo
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
    COALESCE(va2.pedidos_dia, 0) AS pedidos_dia_ev2,
    COALESCE(va2.ingressos_dia, 0) AS ingressos_dia_ev2,
    COALESCE(va2.receita_dia, 0) AS receita_dia_ev2,
    COALESCE(va2.pedidos_acumulados, 0) AS pedidos_acum_ev2,
    COALESCE(va2.ingressos_acumulados, 0) AS ingressos_acum_ev2,
    COALESCE(va2.receita_acumulada, 0) AS receita_acum_ev2,
    -- DIFERENÇAS
    (va1.ingressos_acumulados - COALESCE(va2.ingressos_acumulados, 0)) AS diff_ingressos,
    (va1.receita_acumulada - COALESCE(va2.receita_acumulada, 0)) AS diff_receita,
    ROUND((va1.ingressos_acumulados / NULLIF(va2.ingressos_acumulados, 0) * 100) - 100, 2) AS perc_evolucao_ingressos,
    ROUND((va1.receita_acumulada / NULLIF(va2.receita_acumulada, 0) * 100) - 100, 2) AS perc_evolucao_receita
FROM vendas_ev1_acumuladas va1
LEFT JOIN vendas_ev2_acumuladas va2 ON va1.dia_venda = va2.dia_venda
ORDER BY va1.dia_venda;

-- ============================================================
-- 5. COMPARAÇÃO POR PERÍODOS
-- ============================================================
SELECT 
    periodo,
    SUM(CASE WHEN event_id = @evento1_id THEN pedidos ELSE 0 END) AS pedidos_ev1,
    SUM(CASE WHEN event_id = @evento1_id THEN ingressos ELSE 0 END) AS ingressos_ev1,
    SUM(CASE WHEN event_id = @evento1_id THEN receita ELSE 0 END) AS receita_ev1,
    SUM(CASE WHEN event_id = @evento2_id THEN pedidos ELSE 0 END) AS pedidos_ev2,
    SUM(CASE WHEN event_id = @evento2_id THEN ingressos ELSE 0 END) AS ingressos_ev2,
    SUM(CASE WHEN event_id = @evento2_id THEN receita ELSE 0 END) AS receita_ev2,
    (SUM(CASE WHEN event_id = @evento1_id THEN ingressos ELSE 0 END) - 
     SUM(CASE WHEN event_id = @evento2_id THEN ingressos ELSE 0 END)) AS diff_ingressos,
    (SUM(CASE WHEN event_id = @evento1_id THEN receita ELSE 0 END) - 
     SUM(CASE WHEN event_id = @evento2_id THEN receita ELSE 0 END)) AS diff_receita
FROM (
    SELECT 
        p.event_id,
        CASE 
            WHEN DATEDIFF(p.created_at, primeira_venda.inicio) <= 7 THEN '1. Primeira Semana'
            WHEN DATEDIFF(p.created_at, primeira_venda.inicio) <= 14 THEN '2. Segunda Semana'
            WHEN DATEDIFF(p.created_at, primeira_venda.inicio) <= 21 THEN '3. Terceira Semana'
            WHEN DATEDIFF(p.created_at, primeira_venda.inicio) <= 30 THEN '4. Primeiro Mês'
            WHEN DATEDIFF(p.created_at, primeira_venda.inicio) <= 60 THEN '5. Segundo Mês'
            ELSE '6. Demais Períodos'
        END AS periodo,
        COUNT(DISTINCT p.id) AS pedidos,
        COUNT(i.id) AS ingressos,
        SUM(p.total) AS receita
    FROM pedidos p
    INNER JOIN (
        SELECT 
            event_id,
            MIN(created_at) AS inicio
        FROM pedidos
        WHERE event_id IN (@evento1_id, @evento2_id)
            AND status IN (@status1, @status2, @status3)
        GROUP BY event_id
    ) primeira_venda ON p.evento_id = primeira_venda.event_id
    LEFT JOIN ingressos i ON p.id = i.pedido_id 
        AND i.ticket_id <> @ticket_cortesia
    WHERE p.evento_id IN (@evento1_id, @evento2_id)
        AND p.status IN (@status1, @status2, @status3)
    GROUP BY p.event_id, periodo
) periodos
GROUP BY periodo
ORDER BY periodo;

-- ============================================================
-- 6. RESUMO EXECUTIVO
-- ============================================================
SELECT 
    'RESUMO COMPARATIVO' AS tipo,
    @evento1_id AS evento1,
    @evento2_id AS evento2,
    -- Total Ingressos
     (SELECT COUNT(*) 
      FROM ingressos i 
      INNER JOIN pedidos p ON i.pedido_id = p.id 
      WHERE p.evento_id = @evento1_id 
          AND p.status IN (@status1, @status2, @status3)
          AND i.ticket_id <> @ticket_cortesia) AS total_ingressos_ev1,
     (SELECT COUNT(*) 
      FROM ingressos i 
      INNER JOIN pedidos p ON i.pedido_id = p.id 
      WHERE p.evento_id = @evento2_id 
          AND p.status IN (@status1, @status2, @status3)
          AND i.ticket_id <> @ticket_cortesia) AS total_ingressos_ev2,
    -- Diferença Ingressos
     ((SELECT COUNT(*) 
       FROM ingressos i 
       INNER JOIN pedidos p ON i.pedido_id = p.id 
       WHERE p.evento_id = @evento1_id 
           AND p.status IN (@status1, @status2, @status3)
           AND i.ticket_id <> @ticket_cortesia) -
      (SELECT COUNT(*) 
       FROM ingressos i 
       INNER JOIN pedidos p ON i.pedido_id = p.id 
       WHERE p.evento_id = @evento2_id 
           AND p.status IN (@status1, @status2, @status3)
           AND i.ticket_id <> @ticket_cortesia)) AS diff_ingressos,
    -- Percentual Evolução Ingressos
    ROUND((
         (SELECT COUNT(*) 
          FROM ingressos i 
          INNER JOIN pedidos p ON i.pedido_id = p.id 
          WHERE p.evento_id = @evento1_id 
              AND p.status IN (@status1, @status2, @status3)
              AND i.ticket_id <> @ticket_cortesia) /
         NULLIF((SELECT COUNT(*) 
                 FROM ingressos i 
                 INNER JOIN pedidos p ON i.pedido_id = p.id 
                 WHERE p.evento_id = @evento2_id 
                     AND p.status IN (@status1, @status2, @status3)
                     AND i.ticket_id <> @ticket_cortesia), 0)
        * 100
    ) - 100, 2) AS perc_evolucao_ingressos,
    -- Total Receita
    (SELECT SUM(p.total) 
     FROM pedidos p 
     WHERE p.evento_id = @evento1_id 
         AND p.status IN (@status1, @status2, @status3)) AS receita_ev1,
    (SELECT SUM(p.total) 
     FROM pedidos p 
     WHERE p.evento_id = @evento2_id 
         AND p.status IN (@status1, @status2, @status3)) AS receita_ev2,
    -- Diferença Receita
    ((SELECT SUM(p.total) 
      FROM pedidos p 
      WHERE p.evento_id = @evento1_id 
          AND p.status IN (@status1, @status2, @status3)) -
     (SELECT SUM(p.total) 
      FROM pedidos p 
      WHERE p.evento_id = @evento2_id 
          AND p.status IN (@status1, @status2, @status3))) AS diff_receita,
    -- Percentual Evolução Receita
    ROUND((
        (SELECT SUM(p.total) 
         FROM pedidos p 
         WHERE p.evento_id = @evento1_id 
             AND p.status IN (@status1, @status2, @status3)) /
        NULLIF((SELECT SUM(p.total) 
                FROM pedidos p 
                WHERE p.evento_id = @evento2_id 
                    AND p.status IN (@status1, @status2, @status3)), 0)
        * 100
    ) - 100, 2) AS perc_evolucao_receita;

-- ============================================================
-- LIMPEZA: Remove tabelas temporárias
-- ============================================================
DROP TEMPORARY TABLE IF EXISTS vendas_diarias_ev1;
DROP TEMPORARY TABLE IF EXISTS vendas_diarias_ev2;
DROP TEMPORARY TABLE IF EXISTS vendas_ev1_numeradas;
DROP TEMPORARY TABLE IF EXISTS vendas_ev2_numeradas;
DROP TEMPORARY TABLE IF EXISTS vendas_ev1_acumuladas;
DROP TEMPORARY TABLE IF EXISTS vendas_ev2_acumuladas;

