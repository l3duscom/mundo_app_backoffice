# API de Atribuição de Conquistas - Documentação

## 📋 Visão Geral

API RESTful para gerenciar a atribuição de conquistas aos usuários, extrato de pontos e ranking. Sistema com controle transacional que garante integridade dos dados.

**Base URL:** `/api/usuario-conquistas`

---

## 🔐 Autenticação

Todas as requisições requerem um token JWT válido no header:

```
Authorization: Bearer {seu_token_jwt}
```

---

## ⚡ Características Principais

- ✅ **Atribuição atômica** - Usa transações para garantir consistência
- ✅ **Previne duplicação** - Uma conquista por usuário/evento
- ✅ **Atualização automática de pontos** - Soma pontos na tabela usuarios
- ✅ **Extrato completo** - Histórico imutável de transações
- ✅ **Sistema de revogação** - Com ajuste automático de pontos
- ✅ **Ranking dinâmico** - Por evento com total de pontos e conquistas

---

## 📡 Endpoints

### 1. Listar Conquistas do Usuário

**GET** `/api/usuario-conquistas/usuario/{user_id}`

Lista todas as conquistas de um usuário específico.

#### Query Parameters (opcionais):
- `event_id` (int): Filtrar por evento específico

#### Exemplo de Requisição:
```bash
GET /api/usuario-conquistas/usuario/1
GET /api/usuario-conquistas/usuario/1?event_id=1
```

#### Resposta de Sucesso (200):
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "conquista_id": 1,
      "nome_conquista": "Primeira Participação",
      "nivel": "BRONZE",
      "event_id": 1,
      "pontos": 10,
      "admin": 0,
      "status": "ATIVA",
      "created_at": "2024-11-21 10:00:00"
    },
    {
      "id": 2,
      "conquista_id": 2,
      "nome_conquista": "Participou de 3 Painéis",
      "nivel": "PRATA",
      "event_id": 1,
      "pontos": 25,
      "admin": 1,
      "status": "ATIVA",
      "created_at": "2024-11-21 11:00:00"
    }
  ],
  "total": 2,
  "total_pontos": 35
}
```

---

### 2. Atribuir Conquista ao Usuário

**POST** `/api/usuario-conquistas/atribuir`

Atribui uma conquista a um usuário. O sistema automaticamente:
- Verifica se a conquista já foi atribuída
- Soma os pontos na tabela `usuarios`
- Cria entrada no extrato de pontos

#### Body JSON (obrigatório):
```json
{
  "user_id": 1,
  "conquista_id": 1,
  "event_id": 1,
  "admin": false,
  "atribuido_por": 2
}
```

#### Campos:
- `user_id` (int, obrigatório): ID do usuário
- `conquista_id` (int, obrigatório): ID da conquista (use o **ID numérico**, não o código)
- `event_id` (int, obrigatório): ID do evento
- `admin` (bool, opcional): Se foi atribuído manualmente por admin (default: false)
- `atribuido_por` (int, opcional): ID do admin que atribuiu

**⚠️ IMPORTANTE:** 
- Use o campo `conquista_id` (ID numérico) para atribuir conquistas.
- **NÃO use** o campo `codigo` - ele é apenas informativo e para compartilhamento.

#### Exemplo de Requisição:
```bash
POST /api/usuario-conquistas/atribuir
Content-Type: application/json

{
  "user_id": 1,
  "conquista_id": 1,
  "event_id": 1
}
```

#### Resposta de Sucesso (201):
```json
{
  "success": true,
  "message": "Conquista atribuída com sucesso",
  "data": {
    "id": 5,
    "conquista_id": 1,
    "conquista_nome": "Primeira Participação",
    "event_id": 1,
    "user_id": 1,
    "pontos": 10,
    "saldo_anterior": 25,
    "saldo_atual": 35,
    "admin": 0,
    "status": "ATIVA",
    "created_at": "2024-11-21 14:30:00"
  }
}
```

#### Resposta de Erro (400):
```json
{
  "success": false,
  "message": "Usuário já possui esta conquista"
}
```

#### Outros Erros Possíveis:
- Usuário não encontrado
- Conquista não encontrada
- Conquista não está ativa

---

### 3. Atribuir Conquista por Código

**POST** `/api/usuario-conquistas/atribuir-por-codigo`

Atribui uma conquista a um usuário usando o **código** da conquista (não o ID). Útil para:
- 📱 QR Codes em eventos
- 🎁 Códigos promocionais
- 🔗 Compartilhamento entre usuários
- ✨ Links de ativação/gamificação

O sistema automaticamente:
- Busca a conquista pelo código
- Valida se está ativa e pertence ao evento
- Verifica se já foi atribuída
- Soma os pontos na tabela `usuarios`
- Cria entrada no extrato de pontos

#### Body JSON (obrigatório):
```json
{
  "user_id": 1,
  "codigo": "A1B2C3D4",
  "event_id": 1,
  "admin": false,
  "atribuido_por": 2
}
```

#### Campos:
- `user_id` (int, obrigatório): ID do usuário
- `codigo` (string, obrigatório): Código da conquista (8 caracteres)
- `event_id` (int, obrigatório): ID do evento
- `admin` (bool, opcional): Se foi atribuído manualmente por admin (default: false)
- `atribuido_por` (int, opcional): ID do admin que atribuiu

#### Exemplo de Requisição:
```bash
POST /api/usuario-conquistas/atribuir-por-codigo
Content-Type: application/json

{
  "user_id": 123,
  "codigo": "K9L0M1N2",
  "event_id": 17
}
```

#### Resposta de Sucesso (201):
```json
{
  "success": true,
  "message": "Conquista atribuída com sucesso",
  "conquista": {
    "id": 5,
    "codigo": "K9L0M1N2",
    "nome_conquista": "Comprou Ingresso",
    "descricao": "Adquiriu ingresso para o evento",
    "pontos": 15,
    "nivel": "BRONZE"
  },
  "data": {
    "usuario_conquista": {
      "id": 42,
      "user_id": 123,
      "conquista_id": 5,
      "event_id": 17,
      "pontos": 15,
      "admin": 0,
      "status": "ATIVA",
      "created_at": "2024-11-23 10:00:00"
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

#### Resposta de Erro - Código Não Encontrado (404):
```json
{
  "success": false,
  "message": "Conquista não encontrada com o código fornecido"
}
```

#### Resposta de Erro - Conquista Inativa (400):
```json
{
  "success": false,
  "message": "Conquista não está ativa",
  "status_conquista": "INATIVA"
}
```

#### Resposta de Erro - Evento Incorreto (400):
```json
{
  "success": false,
  "message": "Conquista não pertence ao evento informado",
  "event_id_conquista": 15,
  "event_id_informado": 17
}
```

#### Resposta de Erro - Já Atribuída (400):
```json
{
  "success": false,
  "message": "Usuário já possui esta conquista neste evento"
}
```

---

### 4. Revogar Conquista do Usuário

**POST** `/api/usuario-conquistas/{id}/revogar`

Revoga uma conquista atribuída. O sistema automaticamente:
- Atualiza status para "REVOGADA"
- Remove os pontos da tabela `usuarios`
- Cria entrada negativa no extrato

#### Parâmetros:
- `id` (int, obrigatório): ID do registro em `usuario_conquistas`

#### Body JSON:
```json
{
  "atribuido_por": 2,
  "motivo": "Motivo da revogação"
}
```

#### Campos:
- `atribuido_por` (int, obrigatório): ID do admin que está revogando
- `motivo` (string, opcional): Motivo da revogação

#### Exemplo de Requisição:
```bash
POST /api/usuario-conquistas/5/revogar
Content-Type: application/json

{
  "atribuido_por": 2,
  "motivo": "Conquista atribuída por engano"
}
```

#### Resposta de Sucesso (200):
```json
{
  "success": true,
  "message": "Conquista revogada com sucesso",
  "data": {
    "id": 5,
    "pontos_removidos": 10,
    "saldo_anterior": 35,
    "saldo_atual": 25
  }
}
```

#### Resposta de Erro (400):
```json
{
  "success": false,
  "message": "Conquista já está revogada"
}
```

---

### 5. Extrato de Pontos do Usuário

**GET** `/api/usuario-conquistas/extrato/{user_id}`

Retorna o histórico completo de transações de pontos do usuário.

#### Query Parameters (opcionais):
- `event_id` (int): Filtrar por evento
- `limit` (int): Limitar quantidade de registros

#### Exemplo de Requisição:
```bash
GET /api/usuario-conquistas/extrato/1
GET /api/usuario-conquistas/extrato/1?event_id=1&limit=10
```

#### Resposta de Sucesso (200):
```json
{
  "success": true,
  "data": [
    {
      "id": 3,
      "event_id": 1,
      "tipo": "REVOGACAO",
      "pontos": -10,
      "saldo_anterior": 35,
      "saldo_atual": 25,
      "descricao": "Revogação: Primeira Participação - Motivo: Conquista atribuída por engano",
      "created_at": "2024-11-21 15:00:00"
    },
    {
      "id": 2,
      "event_id": 1,
      "tipo": "CONQUISTA",
      "pontos": 25,
      "saldo_anterior": 10,
      "saldo_atual": 35,
      "descricao": "Conquista: Participou de 3 Painéis",
      "created_at": "2024-11-21 11:00:00"
    },
    {
      "id": 1,
      "event_id": 1,
      "tipo": "CONQUISTA",
      "pontos": 10,
      "saldo_anterior": 0,
      "saldo_atual": 10,
      "descricao": "Conquista: Primeira Participação",
      "created_at": "2024-11-21 10:00:00"
    }
  ],
  "total": 3,
  "saldo_atual": 25
}
```

---

### 6. Ranking de Usuários por Evento

**GET** `/api/usuario-conquistas/ranking/{event_id}`

Retorna o ranking de usuários com mais pontos em um evento.

#### Parâmetros:
- `event_id` (int, obrigatório): ID do evento

#### Query Parameters (opcionais):
- `limit` (int): Limitar quantidade de usuários (default: 10, máximo: 100)

#### Exemplo de Requisição:
```bash
GET /api/usuario-conquistas/ranking/1
GET /api/usuario-conquistas/ranking/1?limit=20
```

#### Resposta de Sucesso (200):
```json
{
  "success": true,
  "data": [
    {
      "posicao": 1,
      "user_id": 5,
      "nome": "Maria Silva",
      "total_pontos": 150,
      "total_conquistas": 8
    },
    {
      "posicao": 2,
      "user_id": 3,
      "nome": "João Santos",
      "total_pontos": 120,
      "total_conquistas": 6
    },
    {
      "posicao": 3,
      "user_id": 1,
      "nome": "Ana Costa",
      "total_pontos": 85,
      "total_conquistas": 5
    }
  ],
  "total": 3
}
```

---

## 🔄 Tipos de Transação no Extrato

| Tipo | Descrição | Pontos |
|------|-----------|--------|
| CONQUISTA | Conquista atribuída ao usuário | Positivo |
| REVOGACAO | Conquista revogada | Negativo |
| BONUS | Bônus especial dado por admin | Positivo |
| AJUSTE | Ajuste manual de pontos | Positivo/Negativo |
| PENALIDADE | Penalidade aplicada | Negativo |

---

## 🔒 Regras de Negócio

### Atribuição de Conquistas

1. **Uma conquista por usuário/evento** - Garantido por índice UNIQUE na tabela
2. **Conquistas são imutáveis** - Não podem ser editadas após atribuição
3. **Pontos somados automaticamente** - Atualiza coluna `pontos` em `usuarios`
4. **Transações atômicas** - Rollback em caso de erro

### Revogação de Conquistas

1. **Não remove o registro** - Apenas muda status para "REVOGADA"
2. **Remove pontos** - Decrementa os pontos do usuário
3. **Previne saldo negativo** - Saldo mínimo é 0
4. **Histórico preservado** - Entrada no extrato com tipo "REVOGACAO"

### Extrato de Pontos

1. **Imutável** - Não pode ser editado ou deletado
2. **Sempre sequencial** - Registra saldo_anterior e saldo_atual
3. **Rastreável** - Vincula à entidade origem (conquista, pedido, etc)
4. **Auditável** - Registra quem fez a transação

---

## 📊 Estrutura das Tabelas

### usuario_conquistas
```sql
CREATE TABLE `usuario_conquistas` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `conquista_id` INT(11) UNSIGNED NOT NULL,
    `event_id` INT(11) UNSIGNED NOT NULL,
    `user_id` INT(11) UNSIGNED NOT NULL,
    `pontos` INT(11) NOT NULL DEFAULT 0,
    `admin` TINYINT(1) NOT NULL DEFAULT 0,
    `status` VARCHAR(50) NOT NULL DEFAULT 'ATIVA',
    `atribuido_por` INT(11) UNSIGNED NULL,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    `deleted_at` DATETIME NULL,
    UNIQUE KEY `unique_user_conquista` (`user_id`, `conquista_id`, `event_id`)
);
```

### extrato_pontos
```sql
CREATE TABLE `extrato_pontos` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT(11) UNSIGNED NOT NULL,
    `event_id` INT(11) UNSIGNED NULL,
    `tipo` VARCHAR(50) NOT NULL,
    `pontos` INT(11) NOT NULL,
    `saldo_anterior` INT(11) NOT NULL DEFAULT 0,
    `saldo_atual` INT(11) NOT NULL DEFAULT 0,
    `descricao` TEXT NULL,
    `referencia_tipo` VARCHAR(50) NULL,
    `referencia_id` INT(11) UNSIGNED NULL,
    `atribuido_por` INT(11) UNSIGNED NULL,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    `deleted_at` DATETIME NULL
);
```

---

## ⚠️ Códigos de Status HTTP

| Código | Descrição |
|--------|-----------|
| 200 | Sucesso |
| 201 | Criado com sucesso |
| 400 | Requisição inválida |
| 401 | Não autorizado (token inválido) |
| 404 | Recurso não encontrado |
| 405 | Método não permitido |
| 422 | Erro de validação |
| 500 | Erro interno do servidor |

---

## 🚀 Como Usar

### 1. Executar Migrations
```bash
php spark migrate
```

### 2. Exemplo de Fluxo Completo

```javascript
// 1. Atribuir conquista
const atribuir = await fetch('/api/usuario-conquistas/atribuir', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    user_id: 1,
    conquista_id: 1,
    event_id: 1
  })
});

// 2. Listar conquistas do usuário
const conquistas = await fetch('/api/usuario-conquistas/usuario/1?event_id=1', {
  headers: { 'Authorization': 'Bearer ' + token }
});

// 3. Ver extrato
const extrato = await fetch('/api/usuario-conquistas/extrato/1?event_id=1', {
  headers: { 'Authorization': 'Bearer ' + token }
});

// 4. Ver ranking
const ranking = await fetch('/api/usuario-conquistas/ranking/1?limit=10', {
  headers: { 'Authorization': 'Bearer ' + token }
});
```

---

## 💡 Casos de Uso

### Atribuição Automática
Quando um usuário completa uma ação (ex: participou de 3 painéis):
```json
POST /api/usuario-conquistas/atribuir
{
  "user_id": 1,
  "conquista_id": 2,
  "event_id": 1,
  "admin": false
}
```

### Atribuição Manual por Admin
Admin atribui conquista especial:
```json
POST /api/usuario-conquistas/atribuir
{
  "user_id": 1,
  "conquista_id": 10,
  "event_id": 1,
  "admin": true,
  "atribuido_por": 2
}
```

### Correção de Erro
Admin revoga conquista atribuída por engano:
```json
POST /api/usuario-conquistas/5/revogar
{
  "atribuido_por": 2,
  "motivo": "Atribuída ao usuário errado"
}
```

---

## 📝 Notas Importantes

1. **Transações Atômicas**: Todas as operações de atribuição/revogação usam transações para garantir consistência
2. **Prevenção de Duplicatas**: Índice UNIQUE impede atribuição duplicada
3. **Imutabilidade do Extrato**: Histórico completo e rastreável
4. **Segurança**: Todas as rotas exigem autenticação JWT
5. **Performance**: Índices otimizados para queries de ranking e extrato

---

## 🎯 Próximas Funcionalidades Sugeridas

- Sistema de badges visuais
- Conquistas com pré-requisitos
- Conquistas secretas/ocultas
- Sistema de níveis de usuário baseado em pontos
- Notificações push quando conquista é desbloqueada
- Compartilhamento social de conquistas

