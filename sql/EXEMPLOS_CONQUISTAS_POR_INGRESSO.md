# Exemplos de Conquistas por Tipo de Ingresso

## 📋 Visão Geral

Este documento contém exemplos práticos de como usar o script `atribuir_conquista_por_tipo_ingresso.sql` para criar conquistas baseadas em tipos específicos de ingressos.

---

## 🎯 Passo a Passo

### 1. Descobrir os IDs dos Tipos de Ingresso

Primeiro, execute esta query para ver todos os tipos de ingresso do evento:

```sql
-- Configure o ID do evento
SET @event_id = 17;

-- Lista todos os tipos de ingresso
SELECT 
    t.id as ticket_id,
    t.nome as tipo_ingresso,
    t.descricao,
    t.valor,
    COUNT(DISTINCT i.id) as total_vendidos,
    COUNT(DISTINCT p.user_id) as usuarios_unicos
FROM tickets t
LEFT JOIN ingressos i ON t.id = i.ticket_id AND i.event_id = @event_id
LEFT JOIN pedidos p ON i.pedido_id = p.id AND p.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH')
WHERE t.event_id = @event_id
GROUP BY t.id, t.nome, t.descricao, t.valor
ORDER BY total_vendidos DESC;
```

**Resultado exemplo:**
```
+------------+------------------------+------------------+---------+----------------+-----------------+
| ticket_id  | tipo_ingresso          | descricao        | valor   | total_vendidos | usuarios_unicos |
+------------+------------------------+------------------+---------+----------------+-----------------+
| 5          | VIP - 2 Dias           | Ingresso VIP     | 250.00  | 45             | 42              |
| 3          | Inteira - 2 Dias       | Ingresso inteira | 150.00  | 180            | 175             |
| 4          | Meia - 2 Dias          | Meia-entrada     | 75.00   | 95             | 93              |
| 8          | Combo Família          | 2 adultos + kids | 300.00  | 12             | 12              |
| 12         | Ingresso Solidário     | Com doação       | 100.00  | 8              | 8               |
+------------+------------------------+------------------+---------+----------------+-----------------+
```

---

## 💎 Exemplo 1: Conquista VIP

**Objetivo:** Recompensar usuários que compraram ingresso VIP

```sql
-- ============================================
-- CONFIGURAÇÕES
-- ============================================
SET @event_id = 17;
SET @ticket_id = 5;                           -- ID do ingresso VIP
SET @conquista_nome = 'VIP no Mundo Dream';
SET @conquista_desc = 'Adquiriu ingresso VIP e tem acesso a áreas exclusivas';
SET @conquista_pontos = 100;                  -- Muitos pontos pois é VIP
SET @conquista_nivel = 'OURO';

-- Execute o restante do script atribuir_conquista_por_tipo_ingresso.sql
```

**Resultado esperado:**
- 42 usuários receberão a conquista
- Cada um ganhará 100 pontos
- Conquista de nível OURO
- Código gerado automaticamente (ex: `A1B2C3D4`)

---

## 🎓 Exemplo 2: Conquista Meia-Entrada

**Objetivo:** Gamificar estudantes e pessoas com direito a meia

```sql
-- ============================================
-- CONFIGURAÇÕES
-- ============================================
SET @event_id = 17;
SET @ticket_id = 4;                           -- ID da meia-entrada
SET @conquista_nome = 'Estudante/Meia';
SET @conquista_desc = 'Aproveitou o desconto de meia-entrada';
SET @conquista_pontos = 20;
SET @conquista_nivel = 'BRONZE';

-- Execute o restante do script
```

**Uso:** Incentivar estudantes a participarem do programa de pontos.

---

## 👨‍👩‍👧‍👦 Exemplo 3: Combo Família

**Objetivo:** Recompensar famílias que vieram juntas

```sql
-- ============================================
-- CONFIGURAÇÕES
-- ============================================
SET @event_id = 17;
SET @ticket_id = 8;                           -- ID do combo família
SET @conquista_nome = 'Diversão em Família';
SET @conquista_desc = 'Trouxe a família toda para o evento';
SET @conquista_pontos = 75;
SET @conquista_nivel = 'PRATA';

-- Execute o restante do script
```

**Benefício:** Incentiva compra de combos e aumenta público familiar.

---

## 💝 Exemplo 4: Ingresso Solidário

**Objetivo:** Reconhecer quem contribuiu com causa social

```sql
-- ============================================
-- CONFIGURAÇÕES
-- ============================================
SET @event_id = 17;
SET @ticket_id = 12;                          -- ID do ingresso solidário
SET @conquista_nome = 'Coração Solidário';
SET @conquista_desc = 'Contribuiu com doação ao adquirir ingresso solidário';
SET @conquista_pontos = 150;                  -- Muitos pontos para incentivar
SET @conquista_nivel = 'OURO';

-- Execute o restante do script
```

**Impacto:** Incentiva doações e engajamento social.

---

## 🌟 Exemplo 5: Múltiplas Conquistas (Tiers de Ingresso)

**Cenário:** Sistema de tiers progressivo

### Tier 1: Bronze (Ingresso Básico)

```sql
SET @event_id = 17;
SET @ticket_id = 3;
SET @conquista_nome = 'Participante Mundo Dream';
SET @conquista_desc = 'Adquiriu ingresso para o evento';
SET @conquista_pontos = 10;
SET @conquista_nivel = 'BRONZE';
```

### Tier 2: Prata (Ingresso Premium)

```sql
SET @event_id = 17;
SET @ticket_id = 6;
SET @conquista_nome = 'Participante Premium';
SET @conquista_desc = 'Adquiriu ingresso premium com benefícios extras';
SET @conquista_pontos = 50;
SET @conquista_nivel = 'PRATA';
```

### Tier 3: Ouro (VIP)

```sql
SET @event_id = 17;
SET @ticket_id = 5;
SET @conquista_nome = 'Participante VIP';
SET @conquista_desc = 'Adquiriu ingresso VIP com acesso total';
SET @conquista_pontos = 100;
SET @conquista_nivel = 'OURO';
```

**Execute cada um separadamente para criar as 3 conquistas.**

---

## 🎪 Exemplo 6: Ingresso para Workshop Específico

**Objetivo:** Recompensar quem participou de workshop técnico

```sql
-- ============================================
-- CONFIGURAÇÕES
-- ============================================
SET @event_id = 17;
SET @ticket_id = 15;                          -- ID do ingresso workshop
SET @conquista_nome = 'Participou do Workshop de Cosplay';
SET @conquista_desc = 'Participou do workshop técnico de confecção de cosplay';
SET @conquista_pontos = 40;
SET @conquista_nivel = 'PRATA';

-- Execute o restante do script
```

**Benefício:** Incentiva participação em workshops pagos.

---

## 📊 Verificações Após Execução

### 1. Verificar se a conquista foi criada

```sql
SELECT * FROM conquistas WHERE event_id = 17 ORDER BY id DESC LIMIT 5;
```

### 2. Ver quantos usuários receberam

```sql
SELECT 
    c.nome_conquista,
    c.codigo,
    COUNT(uc.id) as total_usuarios,
    SUM(uc.pontos) as pontos_distribuidos
FROM conquistas c
LEFT JOIN usuario_conquistas uc ON c.id = uc.conquista_id
WHERE c.event_id = 17
GROUP BY c.id
ORDER BY c.created_at DESC;
```

### 3. Listar usuários com suas conquistas

```sql
SELECT 
    u.id,
    u.nome,
    u.email,
    u.pontos as saldo_total,
    GROUP_CONCAT(c.nome_conquista SEPARATOR ', ') as conquistas,
    COUNT(uc.id) as total_conquistas
FROM usuarios u
LEFT JOIN usuario_conquistas uc ON u.id = uc.user_id AND uc.event_id = 17
LEFT JOIN conquistas c ON uc.conquista_id = c.id
WHERE u.id IN (
    SELECT DISTINCT user_id FROM usuario_conquistas WHERE event_id = 17
)
GROUP BY u.id
ORDER BY u.pontos DESC
LIMIT 20;
```

---

## 🔄 Script Completo para Múltiplas Conquistas

Se você precisa criar várias conquistas de uma vez:

```sql
-- ============================================
-- Cria múltiplas conquistas por tipo de ingresso
-- ============================================

-- VIP
SET @event_id = 17; SET @ticket_id = 5;
SET @conquista_nome = 'VIP no Mundo Dream';
SET @conquista_desc = 'Adquiriu ingresso VIP';
SET @conquista_pontos = 100; SET @conquista_nivel = 'OURO';
-- ... execute script principal

-- Premium  
SET @event_id = 17; SET @ticket_id = 6;
SET @conquista_nome = 'Participante Premium';
SET @conquista_desc = 'Adquiriu ingresso premium';
SET @conquista_pontos = 50; SET @conquista_nivel = 'PRATA';
-- ... execute script principal

-- Meia
SET @event_id = 17; SET @ticket_id = 4;
SET @conquista_nome = 'Estudante Mundo Dream';
SET @conquista_desc = 'Aproveitou desconto de estudante';
SET @conquista_pontos = 20; SET @conquista_nivel = 'BRONZE';
-- ... execute script principal

-- Solidário
SET @event_id = 17; SET @ticket_id = 12;
SET @conquista_nome = 'Coração Solidário';
SET @conquista_desc = 'Contribuiu com doação';
SET @conquista_pontos = 150; SET @conquista_nivel = 'OURO';
-- ... execute script principal
```

---

## 💡 Dicas Importantes

### 1. Pontuação Sugerida

| Tipo de Ingresso | Pontos Sugeridos | Nível |
|------------------|------------------|-------|
| Básico/Inteira   | 10-15 pontos    | BRONZE |
| Meia-entrada     | 15-25 pontos    | BRONZE |
| Premium          | 40-60 pontos    | PRATA |
| VIP              | 80-100 pontos   | OURO |
| Combo Família    | 60-80 pontos    | PRATA |
| Solidário        | 100-150 pontos  | OURO |
| Workshop Extra   | 30-50 pontos    | PRATA |

### 2. Nomenclatura de Conquistas

**Boas práticas:**
- ✅ "VIP no Mundo Dream" - específico e claro
- ✅ "Coração Solidário" - emotivo e memorável
- ✅ "Diversão em Família" - relacionado ao benefício

**Evite:**
- ❌ "Conquista 1" - genérico
- ❌ "Ticket ID 5" - técnico demais
- ❌ "asdasd" - sem significado

### 3. Descrições Atrativas

**Boas:**
- "Trouxe a família toda para o evento"
- "Contribuiu com doação ao adquirir ingresso"
- "Tem acesso a áreas VIP exclusivas"

**Evite:**
- "Ingresso comprado"
- "Usuário com ticket"
- Descrições vazias

### 4. Ordem de Execução

1. **Primeiro**: Execute query de descoberta de ticket_ids
2. **Segundo**: Configure variáveis
3. **Terceiro**: Execute o script completo
4. **Quarto**: Verifique os resultados
5. **Quinto**: Teste via API se necessário

---

## ⚠️ Troubleshooting

### Problema 1: "0 usuários receberam"

**Possíveis causas:**
- `ticket_id` incorreto
- `event_id` incorreto
- Status de pedido não incluído (`CONFIRMED`, etc)
- Todos já possuem a conquista

**Solução:**
```sql
-- Verifica se existem ingressos com este ticket_id
SELECT COUNT(*) 
FROM ingressos i
INNER JOIN pedidos p ON i.pedido_id = p.id
WHERE i.ticket_id = @ticket_id 
AND i.event_id = @event_id
AND p.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH');
```

### Problema 2: "Código duplicado"

**Causa:** Conquista com mesmo nome já existe

**Solução:**
```sql
-- Verifica conquistas existentes
SELECT * FROM conquistas WHERE event_id = @event_id AND nome_conquista LIKE '%VIP%';

-- Se necessário, use nome diferente ou delete a antiga (com cuidado!)
```

### Problema 3: "Pontos não atualizaram"

**Causa:** Commit não executado ou erro na transação

**Solução:**
```sql
-- Verifica saldo dos usuários
SELECT u.id, u.nome, u.pontos, 
       (SELECT SUM(pontos) FROM usuario_conquistas WHERE user_id = u.id AND event_id = @event_id) as pontos_conquistas
FROM usuarios u
WHERE u.id IN (SELECT DISTINCT user_id FROM usuario_conquistas WHERE event_id = @event_id)
LIMIT 10;

-- Se inconsistente, recalcula:
UPDATE usuarios u
SET u.pontos = (
    SELECT COALESCE(SUM(uc.pontos), 0)
    FROM usuario_conquistas uc
    WHERE uc.user_id = u.id AND uc.status = 'ATIVA'
)
WHERE u.id IN (SELECT DISTINCT user_id FROM usuario_conquistas WHERE event_id = @event_id);
```

---

## 🎯 Estratégias de Gamificação

### 1. Sistema de Tiers

Crie 3 níveis progressivos de conquistas baseados no valor do ingresso:
- Bronze (básico) → 10 pts
- Prata (premium) → 50 pts  
- Ouro (VIP) → 100 pts

### 2. Conquistas Especiais

- **Early Bird**: Para quem comprou antecipado (adicionar filtro de data)
- **Solidário**: Para ingressos com doação
- **Combo**: Para múltiplos ingressos na mesma compra

### 3. Incentivo de Upgrade

Mostre no app: "Faltam 50 pontos para o próximo nível! Faça upgrade para VIP."

---

## 📈 Relatórios Úteis

### Distribuição de Conquistas por Tipo

```sql
SELECT 
    c.nome_conquista,
    c.nivel,
    c.pontos,
    COUNT(uc.id) as usuarios,
    SUM(uc.pontos) as pontos_totais
FROM conquistas c
LEFT JOIN usuario_conquistas uc ON c.id = uc.conquista_id
WHERE c.event_id = 17
GROUP BY c.id
ORDER BY usuarios DESC;
```

### Top 10 Usuários com Mais Pontos

```sql
SELECT 
    u.id,
    u.nome,
    u.email,
    u.pontos,
    COUNT(uc.id) as total_conquistas
FROM usuarios u
INNER JOIN usuario_conquistas uc ON u.id = uc.user_id
WHERE uc.event_id = 17
GROUP BY u.id
ORDER BY u.pontos DESC
LIMIT 10;
```

### Taxa de Conversão por Tipo de Ingresso

```sql
SELECT 
    t.nome as tipo_ingresso,
    COUNT(DISTINCT i.id) as ingressos_vendidos,
    COUNT(DISTINCT uc.user_id) as usuarios_com_conquista,
    ROUND((COUNT(DISTINCT uc.user_id) * 100.0 / COUNT(DISTINCT p.user_id)), 2) as taxa_conversao
FROM tickets t
INNER JOIN ingressos i ON t.id = i.ticket_id
INNER JOIN pedidos p ON i.pedido_id = p.id
LEFT JOIN usuario_conquistas uc ON p.user_id = uc.user_id AND uc.event_id = @event_id
WHERE i.event_id = @event_id
AND p.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH')
GROUP BY t.id
ORDER BY ingressos_vendidos DESC;
```

---

**Pronto para criar conquistas incríveis! 🎉**

