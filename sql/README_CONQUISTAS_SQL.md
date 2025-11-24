# Scripts SQL de Conquistas - Guia Completo

## 📋 Visão Geral

Este documento explica os diferentes scripts SQL disponíveis para atribuir conquistas e quando usar cada um.

---

## 📁 Scripts Disponíveis

### 1. `atribuir_conquista_pedidos_evento_17.sql`
**Uso:** Atribuir conquista para QUALQUER pessoa que comprou ingresso

**Características:**
- ✅ Não diferencia tipo de ingresso
- ✅ **CRIA** a conquista automaticamente
- ✅ Pontos fixos para todos
- ✅ Conta apenas se tem pedido aprovado

**Quando usar:**
- Conquista geral "Comprou Ingresso"
- Mesma recompensa para todos
- Primeira vez criando a conquista

**Exemplo:**
```sql
-- Todos que compraram ingresso ganham 15 pontos
SET @event_id = 17;
SET @conquista_nome = 'Comprou Ingresso';
SET @pontos = 15;
```

---

### 2. `atribuir_conquista_por_tipo_ingresso.sql`
**Uso:** Atribuir conquista para tipo ESPECÍFICO de ingresso

**Características:**
- ✅ Filtra por `ticket_id` específico
- ✅ **CRIA** a conquista automaticamente
- ✅ Pontos fixos independente da quantidade
- ✅ Uma conquista por usuário

**Quando usar:**
- Conquista específica "VIP", "Premium", "Meia"
- Recompensa diferente por categoria
- Não importa quantos ingressos do tipo tem

**Exemplo:**
```sql
-- Quem tem ingresso VIP (ticket_id=5) ganha 100 pontos
SET @event_id = 17;
SET @ticket_id = 5;
SET @conquista_nome = 'Ingresso VIP';
SET @pontos = 100;
```

**Resultado:**
- Usuário com 1 VIP: **100 pontos**
- Usuário com 3 VIP: **100 pontos** (mesmo valor)

---

### 3. `atribuir_conquista_por_quantidade_ingressos.sql` ⭐ **NOVO**
**Uso:** Pontos multiplicados pela quantidade de ingressos

**Características:**
- ✅ Filtra por `ticket_id` específico
- ⚠️ **NÃO CRIA** - conquista deve existir
- ✅ Pontos multiplicados pela quantidade
- ✅ Uma conquista por usuário (com pontos somados)

**Quando usar:**
- Recompensar proporcionalmente
- Incentivar compra de múltiplos ingressos
- Sistema de pontos por volume

**Exemplo:**
```sql
-- Conquista ID 5 já existe e vale 50 pontos base
SET @event_id = 17;
SET @ticket_id = 5;
SET @conquista_id = 5;  -- Conquista JÁ CRIADA
```

**Resultado:**
- Usuário com 1 VIP: **50 pontos** (1 × 50)
- Usuário com 3 VIP: **150 pontos** (3 × 50)
- Usuário com 5 VIP: **250 pontos** (5 × 50)

---

## 🆚 Comparação Rápida

| Característica | Script 1 (Geral) | Script 2 (Por Tipo) | Script 3 (Quantidade) |
|----------------|------------------|---------------------|-----------------------|
| **Filtro** | Qualquer ingresso | Tipo específico | Tipo específico |
| **Cria conquista?** | ✅ Sim | ✅ Sim | ❌ Não (deve existir) |
| **Pontos** | Fixos | Fixos | Multiplicados por qtd |
| **Exemplo prático** | "Participante" | "VIP" | "Colecionador VIP" |
| **Pontos se tem 3 ingressos** | 15 pts | 100 pts | 150 pts (3×50) |

---

## 🎯 Cenários de Uso

### Cenário 1: Sistema Simples (1 conquista básica)

**Objetivo:** Todo mundo que comprou ganha pontos iguais

**Script:** `atribuir_conquista_pedidos_evento_17.sql`

```sql
SET @event_id = 17;
SET @conquista_nome = 'Participante Mundo Dream 2024';
SET @pontos = 10;
```

**Resultado:** Todos ganham 10 pontos, independente do tipo ou quantidade.

---

### Cenário 2: Sistema de Tiers (3 níveis)

**Objetivo:** Pontos diferentes por categoria de ingresso

**Script:** `atribuir_conquista_por_tipo_ingresso.sql` (rodar 3 vezes)

```sql
-- Bronze: Básico
SET @ticket_id = 3;
SET @conquista_nome = 'Participante Bronze';
SET @pontos = 10;

-- Prata: Premium
SET @ticket_id = 6;
SET @conquista_nome = 'Participante Prata';
SET @pontos = 50;

-- Ouro: VIP
SET @ticket_id = 5;
SET @conquista_nome = 'Participante Ouro';
SET @pontos = 100;
```

**Resultado:** 
- Quem tem básico: 10 pts
- Quem tem premium: 50 pts
- Quem tem VIP: 100 pts

---

### Cenário 3: Sistema Proporcional (incentivo de volume)

**Objetivo:** Quanto mais ingressos, mais pontos

**Passo 1:** Criar conquista via API ou SQL
```sql
INSERT INTO conquistas (event_id, codigo, nome_conquista, pontos, nivel, status, created_at, updated_at)
VALUES (17, 'VIPVOL01', 'Colecionador VIP', 50, 'OURO', 'ATIVA', NOW(), NOW());
-- Anote o ID gerado (ex: 25)
```

**Passo 2:** Atribuir com multiplicação
```sql
SET @event_id = 17;
SET @ticket_id = 5;
SET @conquista_id = 25;  -- ID da conquista criada acima
```

**Resultado:**
- 1 VIP: 50 pts
- 2 VIP: 100 pts
- 5 VIP: 250 pts
- 10 VIP: 500 pts

---

### Cenário 4: Sistema Híbrido (melhor de todos)

**Objetivo:** Tiers + Volume

1. **Conquista Base** (Script 2):
   ```sql
   -- "Tem VIP" - 100 pts fixos
   SET @ticket_id = 5;
   SET @conquista_nome = 'Participante VIP';
   SET @pontos = 100;
   ```

2. **Conquista Volume** (Script 3):
   ```sql
   -- "Colecionador VIP" - 50 pts por ingresso
   SET @conquista_id = 26;
   SET @ticket_id = 5;
   -- Pontos base: 50
   ```

**Resultado para alguém com 3 VIP:**
- Conquista base: 100 pts
- Conquista volume: 150 pts (3×50)
- **Total: 250 pts**

---

## 💡 Decisão: Qual Script Usar?

### Use Script 1 se:
- [ ] Quer conquista geral para todos
- [ ] Não importa o tipo de ingresso
- [ ] Pontos iguais para todos
- [ ] Primeira conquista do evento

### Use Script 2 se:
- [ ] Quer diferenciar por categoria (VIP, Premium, etc)
- [ ] Pontos fixos por categoria
- [ ] Não importa quantos ingressos tem
- [ ] Quer criar múltiplas conquistas (uma por tipo)

### Use Script 3 se:
- [ ] Quer recompensar volume
- [ ] Pontos proporcionais à quantidade
- [ ] Conquista já está criada
- [ ] Quer incentivar compra de múltiplos

---

## 📊 Exemplos Práticos

### Exemplo A: Evento Básico

**Situação:** Primeiro evento, sistema simples

**Solução:**
```sql
-- Script 1: Conquista geral
SET @conquista_nome = 'Participante Mundo Dream';
SET @pontos = 15;
```

**Motivo:** Simples, rápido, todos iguais.

---

### Exemplo B: Evento com VIP

**Situação:** Tem ingressos normal e VIP

**Solução:**
```sql
-- Script 2: Duas conquistas
-- Conquista 1: Normal
SET @ticket_id = 3;
SET @conquista_nome = 'Participante';
SET @pontos = 10;

-- Conquista 2: VIP
SET @ticket_id = 5;
SET @conquista_nome = 'Participante VIP';
SET @pontos = 100;
```

**Motivo:** Diferencia e valoriza VIP.

---

### Exemplo C: Evento com Grupos

**Situação:** Quer incentivar grupos/famílias

**Solução:**
```sql
-- Script 3: Pontos por quantidade
-- Criar conquista: "Grupo Mundo Dream" (50 pts base)
SET @conquista_id = 30;
SET @ticket_id = 3;  -- Qualquer tipo

-- Resultados automáticos:
-- 2 pessoas: 100 pts (2×50)
-- 4 pessoas: 200 pts (4×50)
-- 10 pessoas: 500 pts (10×50)
```

**Motivo:** Incentiva compra em grupo.

---

## ⚠️ Atenções Importantes

### Script 1 e 2: CRIAM conquista
```sql
-- Se rodar 2x, não duplica (tem verificação)
WHERE NOT EXISTS (
    SELECT 1 FROM conquistas 
    WHERE event_id = @event_id 
    AND nome_conquista = @conquista_nome
)
```

### Script 3: REQUER conquista existente
```sql
-- Se conquista não existir, FALHA!
-- Crie primeiro via API ou INSERT manual
```

### Todos previnem duplicação de atribuição
```sql
-- Usuário só ganha conquista 1x
WHERE NOT EXISTS (
    SELECT 1 FROM usuario_conquistas 
    WHERE user_id = X AND conquista_id = Y
)
```

---

## 🔍 Como Verificar Resultados

### Após Script 1 ou 2:
```sql
-- Ver conquista criada
SELECT * FROM conquistas WHERE event_id = 17 ORDER BY id DESC LIMIT 1;

-- Ver atribuições
SELECT COUNT(*) FROM usuario_conquistas WHERE conquista_id = @conquista_id;
```

### Após Script 3:
```sql
-- Ver distribuição de pontos
SELECT 
    (pontos / @pontos_base) as qtd_ingressos,
    COUNT(*) as usuarios,
    SUM(pontos) as pontos_totais
FROM usuario_conquistas
WHERE conquista_id = @conquista_id
GROUP BY (pontos / @pontos_base);
```

---

## 🚀 Workflow Recomendado

### Para Evento Novo:

1. **Descubra os tipos de ingresso**
   ```sql
   SELECT id, nome, valor 
   FROM tickets 
   WHERE event_id = 17;
   ```

2. **Decida a estratégia** (Simples / Tiers / Volume)

3. **Execute os scripts apropriados**

4. **Verifique os resultados**

5. **Divulgue para os usuários** (email, notificação, etc)

---

## 📚 Arquivos Relacionados

- `atribuir_conquista_pedidos_evento_17.sql` - Script base geral
- `atribuir_conquista_por_tipo_ingresso.sql` - Script por categoria
- `atribuir_conquista_por_quantidade_ingressos.sql` - Script proporcional
- `EXEMPLOS_CONQUISTAS_POR_INGRESSO.md` - Exemplos detalhados
- `add_codigo_conquistas.sql` - Adiciona coluna código (se necessário)

---

## ❓ FAQ

**P: Posso rodar o mesmo script 2x?**
R: Sim! Todos têm proteção contra duplicação.

**P: Posso ter múltiplas conquistas no mesmo evento?**
R: Sim! Crie quantas quiser (VIP, Premium, Básico, etc).

**P: E se o usuário comprar mais ingressos depois?**
R: Com Script 3, rode novamente que ele NÃO vai duplicar (já tem a conquista).

**P: Posso mudar os pontos de uma conquista existente?**
R: Sim, mas não afeta quem já recebeu. Use UPDATE manual se necessário.

**P: Como desfazer uma atribuição?**
R: Use o bloco ROLLBACK no final de cada script (com cuidado!).

---

**Pronto para criar um sistema de conquistas incrível! 🎉**

