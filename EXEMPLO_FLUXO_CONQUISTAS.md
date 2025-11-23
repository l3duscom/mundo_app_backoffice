# Exemplo de Fluxo Completo - Conquistas

## 📋 Visão Geral

Este documento demonstra o fluxo completo de criação e atribuição de conquistas, mostrando como o campo `codigo` é gerado automaticamente e como usar o `conquista_id` para atribuir conquistas.

---

## 🎯 Fluxo Completo

### Passo 1: Criar uma Conquista

**Requisição:**
```bash
POST /api/conquistas
Content-Type: application/json
Authorization: Bearer {seu_token_jwt}

{
  "event_id": 17,
  "nome_conquista": "Comprou Ingresso",
  "descricao": "Adquiriu ingresso para o evento",
  "pontos": 15,
  "nivel": "BRONZE",
  "status": "ATIVA"
}
```

**⚠️ IMPORTANTE:** 
- **NÃO envie** o campo `codigo`
- Ele será gerado automaticamente

**Resposta (201):**
```json
{
  "success": true,
  "message": "Conquista criada com sucesso",
  "data": {
    "id": 5,
    "event_id": 17,
    "codigo": "K9L0M1N2",
    "nome_conquista": "Comprou Ingresso",
    "descricao": "Adquiriu ingresso para o evento",
    "pontos": 15,
    "nivel": "BRONZE",
    "status": "ATIVA",
    "created_at": "2024-11-23 10:00:00"
  }
}
```

**✅ Resultado:**
- Conquista criada com ID = **5**
- Código gerado automaticamente = **K9L0M1N2**
- Guarde o **ID** (5) para atribuir aos usuários

---

### Passo 2: Atribuir Conquista ao Usuário

**Requisição:**
```bash
POST /api/usuario-conquistas/atribuir
Content-Type: application/json
Authorization: Bearer {seu_token_jwt}

{
  "user_id": 123,
  "conquista_id": 5,
  "event_id": 17,
  "admin": false
}
```

**⚠️ IMPORTANTE:** 
- Use o campo `conquista_id` com o **ID numérico** (5)
- **NÃO use** o campo `codigo` (K9L0M1N2)

**Resposta (201):**
```json
{
  "success": true,
  "message": "Conquista atribuída com sucesso",
  "data": {
    "usuario_conquista": {
      "id": 42,
      "user_id": 123,
      "conquista_id": 5,
      "event_id": 17,
      "pontos": 15,
      "admin": 0,
      "status": "ATIVA",
      "created_at": "2024-11-23 10:05:00"
    },
    "extrato": {
      "id": 84,
      "user_id": 123,
      "event_id": 17,
      "tipo": "CONQUISTA",
      "pontos": 15,
      "saldo_anterior": 50,
      "saldo_atual": 65,
      "descricao": "Conquista: Comprou Ingresso"
    },
    "pontos_atualizados": 65
  }
}
```

**✅ Resultado:**
- Conquista atribuída ao usuário 123
- Pontos do usuário atualizados: 50 → **65** (+15)
- Extrato criado com histórico da transação

---

### Passo 3: Listar Conquistas do Usuário

**Requisição:**
```bash
GET /api/usuario-conquistas/usuario/123?event_id=17
Authorization: Bearer {seu_token_jwt}
```

**Resposta (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 42,
      "conquista_id": 5,
      "nome_conquista": "Comprou Ingresso",
      "nivel": "BRONZE",
      "event_id": 17,
      "pontos": 15,
      "admin": 0,
      "status": "ATIVA",
      "created_at": "2024-11-23 10:05:00"
    }
  ],
  "total": 1,
  "total_pontos": 15
}
```

---

## 🔄 Fluxo em Massa (SQL Script)

Se você precisar atribuir uma conquista a múltiplos usuários de uma vez, pode usar um SQL script:

```sql
-- 1. Primeiro, crie a conquista via API ou diretamente no banco
INSERT INTO conquistas (event_id, codigo, nome_conquista, descricao, pontos, nivel, status, created_at, updated_at)
VALUES (17, 'ABC12345', 'Participante VIP', 'Adquiriu ingresso VIP', 50, 'OURO', 'ATIVA', NOW(), NOW());

-- Pegue o ID da conquista criada
SET @conquista_id = LAST_INSERT_ID();

-- 2. Atribua para todos os usuários que compraram ingresso
INSERT INTO usuario_conquistas (user_id, conquista_id, event_id, pontos, admin, status, created_at, updated_at)
SELECT DISTINCT 
    p.user_id,
    @conquista_id,
    17,
    50,
    0,
    'ATIVA',
    NOW(),
    NOW()
FROM pedidos p
WHERE p.event_id = 17 
  AND p.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH')
  AND p.deleted_at IS NULL
  AND NOT EXISTS (
    SELECT 1 FROM usuario_conquistas uc 
    WHERE uc.user_id = p.user_id 
      AND uc.conquista_id = @conquista_id 
      AND uc.event_id = 17
  );

-- 3. Atualiza pontos dos usuários
UPDATE usuarios u
INNER JOIN usuario_conquistas uc ON u.id = uc.user_id
SET u.pontos = COALESCE(u.pontos, 0) + uc.pontos
WHERE uc.conquista_id = @conquista_id 
  AND uc.event_id = 17;

-- 4. Cria extrato para cada usuário
INSERT INTO extrato_pontos (user_id, event_id, tipo, pontos, saldo_anterior, saldo_atual, descricao, referencia_tipo, referencia_id, created_at, updated_at)
SELECT 
    uc.user_id,
    uc.event_id,
    'CONQUISTA',
    uc.pontos,
    COALESCE(u.pontos, 0) - uc.pontos,
    COALESCE(u.pontos, 0),
    CONCAT('Conquista: ', c.nome_conquista),
    'usuario_conquistas',
    uc.id,
    NOW(),
    NOW()
FROM usuario_conquistas uc
INNER JOIN usuarios u ON uc.user_id = u.id
INNER JOIN conquistas c ON uc.conquista_id = c.id
WHERE uc.conquista_id = @conquista_id 
  AND uc.event_id = 17;
```

---

## ❌ Erros Comuns

### Erro 1: Enviar o campo `codigo` ao criar conquista

**❌ Errado:**
```json
{
  "event_id": 17,
  "codigo": "MEUCODIGO",
  "nome_conquista": "Teste"
}
```

**✅ Correto:**
```json
{
  "event_id": 17,
  "nome_conquista": "Teste"
}
```

O campo `codigo` será **ignorado** se enviado e um novo código será gerado automaticamente.

---

### Erro 2: Tentar atribuir conquista usando o `codigo`

**❌ Errado:**
```json
{
  "user_id": 123,
  "codigo": "K9L0M1N2",
  "event_id": 17
}
```

**✅ Correto:**
```json
{
  "user_id": 123,
  "conquista_id": 5,
  "event_id": 17
}
```

Use sempre o `conquista_id` (ID numérico), **NÃO** o `codigo`.

---

### Erro 3: Tentar atribuir conquista duplicada

**Requisição:**
```json
{
  "user_id": 123,
  "conquista_id": 5,
  "event_id": 17
}
```

**Resposta (400):**
```json
{
  "success": false,
  "message": "Usuário já possui esta conquista neste evento"
}
```

**Solução:** Cada conquista pode ser atribuída apenas uma vez por usuário/evento.

---

## 📊 Verificação de Dados

### Verificar conquista criada

```sql
SELECT id, event_id, codigo, nome_conquista, pontos, nivel, status
FROM conquistas
WHERE id = 5;
```

### Verificar conquistas do usuário

```sql
SELECT 
    uc.id,
    uc.user_id,
    c.nome_conquista,
    c.codigo,
    uc.pontos,
    uc.status,
    uc.created_at
FROM usuario_conquistas uc
INNER JOIN conquistas c ON uc.conquista_id = c.id
WHERE uc.user_id = 123
  AND uc.event_id = 17;
```

### Verificar pontos do usuário

```sql
SELECT 
    u.id,
    u.name,
    u.pontos as pontos_totais,
    (SELECT SUM(uc.pontos) 
     FROM usuario_conquistas uc 
     WHERE uc.user_id = u.id 
       AND uc.event_id = 17 
       AND uc.status = 'ATIVA') as pontos_evento_17
FROM usuarios u
WHERE u.id = 123;
```

### Verificar extrato

```sql
SELECT 
    ep.id,
    ep.tipo,
    ep.pontos,
    ep.saldo_anterior,
    ep.saldo_atual,
    ep.descricao,
    ep.created_at
FROM extrato_pontos ep
WHERE ep.user_id = 123
  AND ep.event_id = 17
ORDER BY ep.created_at DESC;
```

---

## 🎯 Resumo das Regras

| Campo | Criação de Conquista | Atribuição ao Usuário |
|-------|---------------------|----------------------|
| `codigo` | ❌ NÃO enviar (auto-gerado) | ❌ NÃO usar |
| `conquista_id` | ✅ Retornado na resposta | ✅ OBRIGATÓRIO |
| `event_id` | ✅ Obrigatório | ✅ Obrigatório |
| `user_id` | ❌ Não aplicável | ✅ Obrigatório |

---

## 💡 Dicas

1. **Guarde o ID retornado**: Ao criar uma conquista, sempre guarde o `id` retornado para uso posterior.

2. **Código é para compartilhamento**: O `codigo` é útil para:
   - Compartilhar conquistas em redes sociais
   - QR Codes em eventos físicos
   - Links de compartilhamento
   - Códigos promocionais

3. **Use sempre o ID numérico**: Para operações via API, sempre use `conquista_id` (ID numérico), nunca o `codigo`.

4. **Uma conquista por usuário/evento**: O sistema previne duplicação automaticamente através de índice único.

5. **Transações garantem integridade**: O sistema usa transações para garantir que pontos e extratos sejam sempre consistentes.

