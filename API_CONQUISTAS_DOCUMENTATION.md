# API de Conquistas - Documentação

## 📋 Visão Geral

API RESTful para gerenciar conquistas (achievements) de eventos. Todas as rotas requerem autenticação JWT.

**Base URL:** `/api/conquistas`

---

## 🔐 Autenticação

Todas as requisições requerem um token JWT válido no header:

```
Authorization: Bearer {seu_token_jwt}
```

---

## 📡 Endpoints

### 1. Listar Todas as Conquistas

**GET** `/api/conquistas`

Lista todas as conquistas com filtros opcionais.

#### Query Parameters (opcionais):
- `event_id` (int): Filtrar por evento específico
- `nivel` (string): Filtrar por nível (BRONZE, PRATA, OURO, etc)
- `status` (string): Filtrar por status (ATIVA, INATIVA, BLOQUEADA)

#### Exemplo de Requisição:
```bash
GET /api/conquistas
GET /api/conquistas?event_id=1
GET /api/conquistas?nivel=OURO
GET /api/conquistas?event_id=1&status=ATIVA
```

#### Resposta de Sucesso (200):
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "event_id": 1,
      "nome_conquista": "Primeira Participação",
      "descricao": "Participou do evento pela primeira vez",
      "pontos": 10,
      "nivel": "BRONZE",
      "status": "ATIVA",
      "created_at": "2024-11-21 10:00:00"
    },
    {
      "id": 2,
      "event_id": 1,
      "codigo": "E5F6G7H8",
      "nome_conquista": "Participou de 3 Painéis",
      "descricao": "Assistiu 3 painéis durante o evento",
      "pontos": 25,
      "nivel": "PRATA",
      "status": "ATIVA",
      "created_at": "2024-11-21 10:05:00"
    }
  ],
  "total": 2
}
```

---

### 2. Listar Conquistas por Evento

**GET** `/api/conquistas/evento/{event_id}`

Lista todas as conquistas ativas de um evento específico.

#### Parâmetros:
- `event_id` (int, obrigatório): ID do evento

#### Exemplo de Requisição:
```bash
GET /api/conquistas/evento/1
```

#### Resposta de Sucesso (200):
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "event_id": 1,
      "codigo": "A1B2C3D4",
      "nome_conquista": "Primeira Participação",
      "descricao": "Participou pela primeira vez do evento",
      "pontos": 10,
      "nivel": "BRONZE",
      "status": "ATIVA",
      "created_at": "2024-11-21 10:00:00"
    }
  ],
  "total": 1
}
```

#### Resposta de Erro (404):
```json
{
  "success": false,
  "message": "Evento não encontrado"
}
```

---

### 3. Buscar Conquista Específica

**GET** `/api/conquistas/{id}`

Retorna os detalhes de uma conquista específica.

#### Parâmetros:
- `id` (int, obrigatório): ID da conquista

#### Exemplo de Requisição:
```bash
GET /api/conquistas/1
```

#### Resposta de Sucesso (200):
```json
{
  "success": true,
  "data": {
    "id": 1,
    "event_id": 1,
    "codigo": "A1B2C3D4",
    "nome_conquista": "Primeira Participação",
    "descricao": "Participou pela primeira vez do evento",
    "pontos": 10,
    "nivel": "BRONZE",
    "status": "ATIVA",
    "created_at": "2024-11-21 10:00:00",
    "updated_at": "2024-11-21 10:00:00"
  }
}
```

#### Resposta de Erro (404):
```json
{
  "success": false,
  "message": "Conquista não encontrada"
}
```

---

### 4. Criar Nova Conquista

**POST** `/api/conquistas`

Cria uma nova conquista.

#### Body JSON (obrigatório):
```json
{
  "event_id": 1,
  "nome_conquista": "Primeira Participação",
  "descricao": "Participou do evento pela primeira vez",
  "pontos": 10,
  "nivel": "BRONZE",
  "status": "ATIVA"
}
```

#### Campos:
- `event_id` (int, obrigatório): ID do evento
- `nome_conquista` (string, obrigatório): Nome da conquista (max 255 caracteres)
- `descricao` (string, opcional): Descrição detalhada da conquista
- `pontos` (int, obrigatório): Pontos da conquista
- `nivel` (string, obrigatório): Nível da conquista (max 50 caracteres)
- `status` (string, opcional): Status da conquista. Valores permitidos: ATIVA, INATIVA, BLOQUEADA (padrão: ATIVA)

**⚠️ IMPORTANTE:** 
- O campo `codigo` é gerado **AUTOMATICAMENTE** pelo sistema com 8 caracteres únicos.
- **NÃO envie** o campo `codigo` no body da requisição - ele será ignorado se enviado.
- O código gerado será retornado na resposta após a criação.

#### Exemplo de Requisição:
```bash
POST /api/conquistas
Content-Type: application/json

{
  "event_id": 1,
  "nome_conquista": "Conheceu 5 Convidados",
  "descricao": "Participou de Meet & Greet com 5 convidados",
  "pontos": 50,
  "nivel": "OURO",
  "status": "ATIVA"
}
```

#### Resposta de Sucesso (201):
```json
{
  "success": true,
  "message": "Conquista criada com sucesso",
  "data": {
    "id": 5,
    "event_id": 1,
    "codigo": "K9L0M1N2",
    "nome_conquista": "Conheceu 5 Convidados",
    "descricao": "Participou de Meet & Greet com 5 convidados",
    "pontos": 50,
    "nivel": "OURO",
    "status": "ATIVA",
    "created_at": "2024-11-21 14:30:00"
  }
}
```

#### Resposta de Erro (422):
```json
{
  "success": false,
  "message": "Erro ao criar conquista",
  "errors": {
    "pontos": "Os pontos devem ser um número inteiro"
  }
}
```

---

### 5. Atualizar Conquista

**PUT** `/api/conquistas/{id}` ou **PATCH** `/api/conquistas/{id}`

Atualiza uma conquista existente.

#### Parâmetros:
- `id` (int, obrigatório): ID da conquista

#### Body JSON:
```json
{
  "nome_conquista": "Nova Primeira Participação",
  "pontos": 15,
  "nivel": "PRATA",
  "status": "ATIVA"
}
```

Todos os campos são opcionais. Apenas os campos fornecidos serão atualizados.

**Nota:** O campo `codigo` não pode ser alterado pois é único e gerado automaticamente.

#### Exemplo de Requisição:
```bash
PUT /api/conquistas/1
Content-Type: application/json

{
  "pontos": 20,
  "nivel": "PRATA"
}
```

#### Resposta de Sucesso (200):
```json
{
  "success": true,
  "message": "Conquista atualizada com sucesso",
  "data": {
    "id": 1,
    "event_id": 1,
    "codigo": "A1B2C3D4",
    "nome_conquista": "Primeira Participação",
    "descricao": "Participou do evento pela primeira vez",
    "pontos": 20,
    "nivel": "PRATA",
    "status": "ATIVA",
    "updated_at": "2024-11-21 15:00:00"
  }
}
```

#### Resposta Sem Alterações (200):
```json
{
  "success": true,
  "message": "Nenhuma alteração detectada",
  "data": {
    "id": 1,
    "event_id": 1,
    "codigo": "A1B2C3D4",
    "nome_conquista": "Primeira Participação",
    "descricao": "Participou do evento pela primeira vez",
    "pontos": 20,
    "nivel": "PRATA",
    "status": "ATIVA"
  }
}
```

---

### 6. Deletar Conquista

**DELETE** `/api/conquistas/{id}`

Deleta uma conquista (soft delete).

#### Parâmetros:
- `id` (int, obrigatório): ID da conquista

#### Exemplo de Requisição:
```bash
DELETE /api/conquistas/1
```

#### Resposta de Sucesso (200):
```json
{
  "success": true,
  "message": "Conquista deletada com sucesso"
}
```

#### Resposta de Erro (404):
```json
{
  "success": false,
  "message": "Conquista não encontrada"
}
```

---

## 🏆 Níveis Sugeridos

Os níveis são flexíveis e podem ser customizados por evento. Sugestões:

- **BRONZE** - Conquistas fáceis (10-20 pontos)
- **PRATA** - Conquistas médias (25-40 pontos)
- **OURO** - Conquistas difíceis (50-75 pontos)
- **PLATINA** - Conquistas muito difíceis (100-150 pontos)
- **DIAMANTE** - Conquistas épicas (200+ pontos)

---

## 📊 Status Disponíveis

- **ATIVA** - Conquista disponível para ser obtida
- **INATIVA** - Conquista temporariamente desabilitada
- **BLOQUEADA** - Conquista que requer pré-requisitos

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

## 🎯 Exemplos de Conquistas

```json
[
  {
    "nome_conquista": "Primeira Participação",
    "descricao": "Participou do evento pela primeira vez",
    "pontos": 10,
    "nivel": "BRONZE"
  },
  {
    "nome_conquista": "Participou de 3 Painéis",
    "descricao": "Assistiu 3 painéis durante o evento",
    "pontos": 25,
    "nivel": "PRATA"
  },
  {
    "nome_conquista": "Conheceu 5 Convidados",
    "descricao": "Participou de Meet & Greet com 5 convidados",
    "pontos": 50,
    "nivel": "OURO"
  },
  {
    "nome_conquista": "Mestre Cosplayer",
    "descricao": "Participou do desfile cosplay e ganhou premiação",
    "pontos": 100,
    "nivel": "PLATINA"
  },
  {
    "nome_conquista": "Completou Todo o Cronograma",
    "descricao": "Participou de todos os itens do cronograma",
    "pontos": 200,
    "nivel": "DIAMANTE"
  }
]
```

---

## 🔧 Executar Migration

Para criar a tabela de conquistas no banco de dados:

```bash
php spark migrate
```

---

## 📝 Notas

- Todas as datas são retornadas no formato `Y-m-d H:i:s`
- O soft delete é usado - registros deletados não são removidos fisicamente
- A validação do `event_id` verifica se o evento existe antes de criar/atualizar
- Os pontos podem ser negativos se necessário (penalidades)

---

## ⚠️ Erros Comuns e Soluções

### Erro 400: Dados não fornecidos
- **Causa**: JSON vazio ou mal formatado
- **Solução**: Verifique se o Content-Type é `application/json` e o body está válido

### Erro 404: Evento não encontrado
- **Causa**: O `event_id` fornecido não existe no banco
- **Solução**: Verifique se o evento existe antes de criar a conquista

### Erro 422: Erro de validação
- **Causa**: Dados não atendem às regras de validação
- **Solução**: Verifique a mensagem de erro no campo `errors` da resposta
- **Campos obrigatórios**: `event_id`, `nome_conquista`, `pontos`, `nivel`, `status`
- **Valores válidos para status**: `ATIVA`, `INATIVA`, `BLOQUEADA`
- **Valores sugeridos para nivel**: `BRONZE`, `PRATA`, `OURO`, `PLATINA`, `DIAMANTE`

### Erro 405: Método não permitido
- **Causa**: Método HTTP incorreto
- **Solução**: Verifique se está usando o método correto (GET, POST, PUT, PATCH, DELETE)

