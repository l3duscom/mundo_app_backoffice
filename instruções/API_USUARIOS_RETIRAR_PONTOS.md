# 📘 API de Usuários - Retirar Pontos

## 🎯 Objetivo
API para retirar pontos de um usuário, gerando extrato da transação e atualizando o saldo automaticamente.

## 🔐 Autenticação
Todas as rotas requerem:
- Token JWT válido
- Usuário autenticado

## 📍 Endpoints

### 1. Retirar Pontos
**POST** `/api/usuarios/retirar-pontos`

Retira pontos de um usuário e registra a transação no extrato.

#### Headers
```
Authorization: Bearer {JWT_TOKEN}
Content-Type: application/json
```

#### Body
```json
{
  "usuario_id": 123,
  "pontos": 100,
  "motivo": "Resgate de prêmio XYZ",
  "event_id": 17
}
```

#### Parâmetros

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `usuario_id` | integer | Sim | ID do usuário |
| `pontos` | integer | Sim | Quantidade de pontos a retirar (> 0) |
| `motivo` | string | Sim | Descrição do motivo da retirada |
| `event_id` | integer | Não | ID do evento (opcional) |

#### Resposta de Sucesso (200)
```json
{
  "success": true,
  "message": "Pontos retirados com sucesso",
  "data": {
    "usuario_id": 123,
    "pontos_retirados": 100,
    "saldo_anterior": 500,
    "saldo_atual": 400,
    "extrato_id": 456,
    "motivo": "Resgate de prêmio XYZ"
  }
}
```

#### Erros Possíveis

##### 400 - Bad Request (Dados Inválidos)
```json
{
  "success": false,
  "message": "O campo pontos é obrigatório e deve ser maior que zero"
}
```

##### 400 - Bad Request (Saldo Insuficiente)
```json
{
  "success": false,
  "message": "Saldo insuficiente. O usuário possui apenas 50 pontos.",
  "saldo_atual": 50,
  "pontos_solicitados": 100
}
```

##### 401 - Unauthorized
```json
{
  "success": false,
  "message": "Usuário não autenticado"
}
```

##### 404 - Not Found
```json
{
  "success": false,
  "message": "Usuário não encontrado"
}
```

##### 500 - Internal Server Error
```json
{
  "success": false,
  "message": "Erro ao retirar pontos",
  "error": "Erro interno no servidor"
}
```

---

### 2. Consultar Saldo
**GET** `/api/usuarios/saldo/{usuario_id}`

Consulta o saldo de pontos de um usuário.

#### Headers
```
Authorization: Bearer {JWT_TOKEN}
```

#### Parâmetros de URL

| Parâmetro | Tipo | Descrição |
|-----------|------|-----------|
| `usuario_id` | integer | ID do usuário |

#### Resposta de Sucesso (200)
```json
{
  "success": true,
  "data": {
    "usuario_id": 123,
    "nome": "João Silva",
    "email": "joao@example.com",
    "pontos": 500
  }
}
```

#### Erros Possíveis

##### 400 - Bad Request
```json
{
  "success": false,
  "message": "ID do usuário é obrigatório"
}
```

##### 401 - Unauthorized
```json
{
  "success": false,
  "message": "Usuário não autenticado"
}
```

##### 404 - Not Found
```json
{
  "success": false,
  "message": "Usuário não encontrado"
}
```

---

## 🔄 Fluxo de Retirada de Pontos

```
1. Validação
   ├─> Token JWT válido?
   ├─> Dados obrigatórios presentes?
   └─> Pontos > 0?

2. Verificação
   ├─> Usuário existe?
   └─> Saldo suficiente?

3. Transação DB
   ├─> INÍCIO DA TRANSAÇÃO
   ├─> Calcular novo saldo
   ├─> Atualizar pontos do usuário
   ├─> Criar registro no extrato
   └─> COMMIT ou ROLLBACK

4. Resposta
   └─> Retornar sucesso com dados
```

## 📊 Registro no Extrato

Quando pontos são retirados, um registro é criado na tabela `extrato_pontos`:

```sql
INSERT INTO extrato_pontos (
    usuario_id,
    event_id,
    tipo_transacao,
    pontos,
    saldo_anterior,
    saldo_atual,
    descricao,
    admin,
    created_at
) VALUES (
    123,                           -- usuario_id
    17,                            -- event_id (ou NULL)
    'DEBITO',                      -- tipo_transacao
    100,                           -- pontos retirados
    500,                           -- saldo_anterior
    400,                           -- saldo_atual (500 - 100)
    'Resgate de prêmio XYZ',      -- descricao
    1,                             -- admin (ID do admin)
    '2025-11-26 10:30:00'         -- created_at
);
```

## 🔒 Segurança

### Validações Implementadas

1. **Autenticação:** Token JWT obrigatório
2. **Saldo:** Verifica se o usuário tem pontos suficientes
3. **Transação:** Uso de DB transaction para garantir atomicidade
4. **Log:** Todas as operações são registradas
5. **Sanitização:** Dados são validados e sanitizados

### Rollback Automático

Se qualquer etapa falhar durante a retirada:
- A transação é revertida (ROLLBACK)
- Nenhuma alteração é feita no banco
- Erro é registrado no log
- Resposta de erro é retornada

## 📝 Exemplos de Uso

### Exemplo 1: Resgate de Prêmio

**Request:**
```bash
curl -X POST https://mundodream.com.br/api/usuarios/retirar-pontos \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..." \
  -H "Content-Type: application/json" \
  -d '{
    "usuario_id": 123,
    "pontos": 500,
    "motivo": "Resgate de camiseta oficial",
    "event_id": 17
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Pontos retirados com sucesso",
  "data": {
    "usuario_id": 123,
    "pontos_retirados": 500,
    "saldo_anterior": 2500,
    "saldo_atual": 2000,
    "extrato_id": 789,
    "motivo": "Resgate de camiseta oficial"
  }
}
```

### Exemplo 2: Consultar Saldo

**Request:**
```bash
curl -X GET https://mundodream.com.br/api/usuarios/saldo/123 \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
```

**Response:**
```json
{
  "success": true,
  "data": {
    "usuario_id": 123,
    "nome": "João Silva",
    "email": "joao@example.com",
    "pontos": 2000
  }
}
```

### Exemplo 3: Erro - Saldo Insuficiente

**Request:**
```bash
curl -X POST https://mundodream.com.br/api/usuarios/retirar-pontos \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..." \
  -H "Content-Type: application/json" \
  -d '{
    "usuario_id": 123,
    "pontos": 5000,
    "motivo": "Resgate impossível"
  }'
```

**Response:**
```json
{
  "success": false,
  "message": "Saldo insuficiente. O usuário possui apenas 2000 pontos.",
  "saldo_atual": 2000,
  "pontos_solicitados": 5000
}
```

## 🧪 Testes

### SQL para Verificar Operação

```sql
-- Verificar saldo do usuário
SELECT id, nome, email, pontos 
FROM usuarios 
WHERE id = 123;

-- Verificar extrato
SELECT * 
FROM extrato_pontos 
WHERE usuario_id = 123 
ORDER BY created_at DESC 
LIMIT 10;

-- Verificar última retirada
SELECT 
    ep.id,
    ep.tipo_transacao,
    ep.pontos,
    ep.saldo_anterior,
    ep.saldo_atual,
    ep.descricao,
    ep.created_at,
    u_admin.nome as admin_nome
FROM extrato_pontos ep
LEFT JOIN usuarios u_admin ON u_admin.id = ep.admin
WHERE ep.usuario_id = 123 
AND ep.tipo_transacao = 'DEBITO'
ORDER BY ep.created_at DESC 
LIMIT 1;
```

## 📊 Status Codes

| Código | Significado | Quando Ocorre |
|--------|-------------|---------------|
| 200 | OK | Operação bem-sucedida |
| 400 | Bad Request | Dados inválidos ou saldo insuficiente |
| 401 | Unauthorized | Token inválido ou ausente |
| 404 | Not Found | Usuário não encontrado |
| 500 | Internal Server Error | Erro no servidor |

## 🔍 Logs

Todas as operações são registradas:

```php
// Sucesso
log_message('info', 'Pontos retirados: Usuario 123 teve 100 pontos retirados por admin 1. Saldo: 500 -> 400. Motivo: Resgate de prêmio');

// Erro
log_message('error', 'Erro ao retirar pontos: Usuario 123, Pontos 100. Erro: Saldo insuficiente');
```

## ⚠️ Notas Importantes

1. **Transações Atômicas:** A retirada de pontos e criação do extrato são atômicas
2. **Autenticação Obrigatória:** Usuário precisa estar autenticado
3. **Sem Saldo Negativo:** Sistema impede retirada se saldo < pontos
4. **Auditoria:** Todas as operações registram o usuário responsável
5. **Event ID Opcional:** Pode ser null se não relacionado a um evento
6. **Tipo de Transação:** Sempre será 'DEBITO' para retiradas

## 🚀 Implementação
- **Data:** 26/11/2025
- **Endpoint:** `/api/usuarios/retirar-pontos`
- **Método:** POST
- **Autenticação:** JWT (qualquer usuário autenticado)
- **Controller:** `App\Controllers\Api\Usuarios`
- **Transação:** Sim (DB Transaction)
- **Status:** ✅ Implementado e documentado

