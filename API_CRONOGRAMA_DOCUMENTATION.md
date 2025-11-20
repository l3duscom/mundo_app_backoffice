# API de Cronograma - Documentação

API RESTful para gerenciamento de cronogramas de eventos.

## 🔒 Autenticação

Todas as rotas requerem autenticação JWT. Inclua o token no header:

```
Authorization: Bearer {seu_token_jwt}
```

## 📋 Endpoints

### 1. Listar Cronogramas

Lista todos os cronogramas ou filtra por parâmetros.

**Endpoint:** `GET /api/cronograma`

**Query Parameters (opcionais):**
- `event_id` - Filtra por ID do evento
- `ativo` - Filtra por status (0 ou 1)

**Exemplos:**

```bash
# Listar todos os cronogramas
curl -X GET "https://seudominio.com/api/cronograma" \
  -H "Authorization: Bearer {token}"

# Filtrar por evento
curl -X GET "https://seudominio.com/api/cronograma?event_id=1" \
  -H "Authorization: Bearer {token}"

# Filtrar apenas ativos
curl -X GET "https://seudominio.com/api/cronograma?ativo=1" \
  -H "Authorization: Bearer {token}"
```

**Resposta de Sucesso (200):**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Abertura do Evento",
      "ativo": true,
      "evento": {
        "id": 1,
        "nome": "Festival de Música 2024"
      },
      "created_at": "2024-11-20 10:30:00",
      "updated_at": "2024-11-20 10:30:00"
    }
  ],
  "total": 1
}
```

---

### 2. Buscar Cronograma por ID

Retorna detalhes completos de um cronograma específico.

**Endpoint:** `GET /api/cronograma/{id}`

**Exemplo:**

```bash
curl -X GET "https://seudominio.com/api/cronograma/1" \
  -H "Authorization: Bearer {token}"
```

**Resposta de Sucesso (200):**

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Abertura do Evento",
    "ativo": true,
    "evento": {
      "id": 1,
      "nome": "Festival de Música 2024",
      "slug": "festival-de-musica-2024",
      "data_inicio": "2024-12-01",
      "data_fim": "2024-12-03"
    },
    "created_at": "2024-11-20 10:30:00",
    "updated_at": "2024-11-20 10:30:00"
  }
}
```

**Resposta de Erro (404):**

```json
{
  "success": false,
  "message": "Cronograma não encontrado"
}
```

---

### 3. Criar Cronograma

Cria um novo cronograma vinculado a um evento.

**Endpoint:** `POST /api/cronograma`

**Body (JSON):**

```json
{
  "event_id": 1,
  "name": "Palestra de Abertura",
  "ativo": true
}
```

**Campos:**
- `event_id` (obrigatório) - ID do evento
- `name` (obrigatório) - Nome do cronograma (min: 3, max: 255)
- `ativo` (opcional) - Status ativo (padrão: true)

**Exemplo:**

```bash
curl -X POST "https://seudominio.com/api/cronograma" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "event_id": 1,
    "name": "Palestra de Abertura",
    "ativo": true
  }'
```

**Resposta de Sucesso (201):**

```json
{
  "success": true,
  "message": "Cronograma criado com sucesso",
  "data": {
    "id": 2,
    "event_id": 1,
    "name": "Palestra de Abertura",
    "ativo": true,
    "created_at": "2024-11-20 14:30:00"
  }
}
```

**Resposta de Erro de Validação (422):**

```json
{
  "success": false,
  "message": "Erro ao criar cronograma",
  "errors": {
    "name": "O campo Nome precisa ter pelo menos 3 caracteres."
  }
}
```

---

### 4. Atualizar Cronograma

Atualiza um cronograma existente.

**Endpoint:** 
- `PUT /api/cronograma/{id}` (atualização completa)
- `PATCH /api/cronograma/{id}` (atualização parcial)

**Body (JSON):**

```json
{
  "name": "Nome Atualizado",
  "ativo": false
}
```

**Campos (todos opcionais):**
- `event_id` - ID do evento
- `name` - Nome do cronograma
- `ativo` - Status ativo

**Exemplo:**

```bash
# PUT - Atualização completa
curl -X PUT "https://seudominio.com/api/cronograma/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Palestra de Abertura - Atualizado",
    "ativo": true
  }'

# PATCH - Atualização parcial (apenas um campo)
curl -X PATCH "https://seudominio.com/api/cronograma/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "ativo": false
  }'
```

**Resposta de Sucesso (200):**

```json
{
  "success": true,
  "message": "Cronograma atualizado com sucesso",
  "data": {
    "id": 1,
    "event_id": 1,
    "name": "Palestra de Abertura - Atualizado",
    "ativo": true,
    "updated_at": "2024-11-20 15:00:00"
  }
}
```

**Resposta quando não há alterações:**

```json
{
  "success": true,
  "message": "Nenhuma alteração detectada",
  "data": {
    "id": 1,
    "event_id": 1,
    "name": "Palestra de Abertura",
    "ativo": true
  }
}
```

---

### 5. Excluir Cronograma

Exclui (soft delete) um cronograma.

**Endpoint:** `DELETE /api/cronograma/{id}`

**Exemplo:**

```bash
curl -X DELETE "https://seudominio.com/api/cronograma/1" \
  -H "Authorization: Bearer {token}"
```

**Resposta de Sucesso (200):**

```json
{
  "success": true,
  "message": "Cronograma excluído com sucesso"
}
```

**Resposta de Erro (404):**

```json
{
  "success": false,
  "message": "Cronograma não encontrado"
}
```

---

### 6. Restaurar Cronograma

Restaura um cronograma excluído (desfaz soft delete).

**Endpoint:** `POST /api/cronograma/{id}/restore`

**Exemplo:**

```bash
curl -X POST "https://seudominio.com/api/cronograma/1/restore" \
  -H "Authorization: Bearer {token}"
```

**Resposta de Sucesso (200):**

```json
{
  "success": true,
  "message": "Cronograma restaurado com sucesso",
  "data": {
    "id": 1,
    "event_id": 1,
    "name": "Palestra de Abertura",
    "ativo": true
  }
}
```

**Resposta de Erro (400):**

```json
{
  "success": false,
  "message": "Cronograma não está excluído"
}
```

---

### 7. Listar Cronogramas por Evento

Atalho para listar todos os cronogramas de um evento específico.

**Endpoint:** `GET /api/cronograma/evento/{event_id}`

**Exemplo:**

```bash
curl -X GET "https://seudominio.com/api/cronograma/evento/1" \
  -H "Authorization: Bearer {token}"
```

**Resposta de Sucesso (200):**

```json
{
  "success": true,
  "data": {
    "evento": {
      "id": 1,
      "nome": "Festival de Música 2024"
    },
    "cronogramas": [
      {
        "id": 1,
        "name": "Abertura",
        "ativo": true,
        "created_at": "2024-11-20 10:00:00",
        "updated_at": "2024-11-20 10:00:00"
      },
      {
        "id": 2,
        "name": "Show Principal",
        "ativo": true,
        "created_at": "2024-11-20 11:00:00",
        "updated_at": "2024-11-20 11:00:00"
      }
    ],
    "total": 2
  }
}
```

---

## 🔐 Códigos de Status HTTP

| Código | Significado |
|--------|-------------|
| 200 | Sucesso |
| 201 | Criado com sucesso |
| 400 | Requisição inválida |
| 401 | Não autenticado |
| 404 | Recurso não encontrado |
| 405 | Método não permitido |
| 422 | Erro de validação |
| 429 | Muitas requisições (rate limit) |
| 500 | Erro interno do servidor |

---

## 🚨 Tratamento de Erros

Todas as respostas de erro seguem o padrão:

```json
{
  "success": false,
  "message": "Descrição do erro",
  "error": "Detalhes técnicos (apenas em ambiente de desenvolvimento)"
}
```

Para erros de validação:

```json
{
  "success": false,
  "message": "Erro ao criar cronograma",
  "errors": {
    "campo1": "Mensagem de erro do campo 1",
    "campo2": "Mensagem de erro do campo 2"
  }
}
```

---

## 📝 Notas Importantes

1. **Autenticação obrigatória:** Todas as rotas requerem token JWT válido
2. **Soft Delete:** Cronogramas excluídos são mantidos no banco com `deleted_at` preenchido
3. **Validações:** 
   - `name`: mínimo 3, máximo 255 caracteres
   - `event_id`: deve existir na tabela `eventos`
   - `ativo`: aceita apenas 0 ou 1 (false ou true)
4. **Rate Limiting:** A API possui limite de requisições por IP
5. **HTTPS:** Recomendado usar HTTPS em produção

---

## 🧪 Testando a API

### Exemplo com JavaScript (Fetch API):

```javascript
// Login primeiro
const loginResponse = await fetch('https://seudominio.com/api/auth/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    email: 'seu@email.com',
    password: 'suasenha'
  })
});

const { data } = await loginResponse.json();
const token = data.token;

// Listar cronogramas
const response = await fetch('https://seudominio.com/api/cronograma', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});

const cronogramas = await response.json();
console.log(cronogramas);

// Criar cronograma
const createResponse = await fetch('https://seudominio.com/api/cronograma', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    event_id: 1,
    name: 'Novo Cronograma',
    ativo: true
  })
});

const novoCronograma = await createResponse.json();
console.log(novoCronograma);
```

---

## 📞 Suporte

Para dúvidas ou problemas, verifique os logs do sistema ou entre em contato com o administrador.

