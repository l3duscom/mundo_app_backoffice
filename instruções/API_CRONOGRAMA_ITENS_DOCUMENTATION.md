# API de Itens do Cronograma - Documentação

API RESTful para gerenciamento de itens de cronogramas de eventos.

## 🔒 Autenticação

Todas as rotas requerem autenticação JWT. Inclua o token no header:

```
Authorization: Bearer {seu_token_jwt}
```

## 📋 Status Disponíveis

Os itens podem ter os seguintes status:
- `AGUARDANDO` - Item aguardando início (padrão)
- `EM_ANDAMENTO` - Item acontecendo no momento
- `CONCLUIDO` - Item finalizado
- `CANCELADO` - Item cancelado

## 📋 Endpoints

### 1. Listar Itens

Lista todos os itens ou filtra por parâmetros.

**Endpoint:** `GET /api/cronograma-item`

**Query Parameters (opcionais):**
- `cronograma_id` - Filtra por ID do cronograma
- `status` - Filtra por status
- `ativo` - Filtra por status ativo (0 ou 1)

**Exemplos:**

```bash
# Listar todos os itens
curl -X GET "https://seudominio.com/api/cronograma-item" \
  -H "Authorization: Bearer {token}"

# Filtrar por cronograma
curl -X GET "https://seudominio.com/api/cronograma-item?cronograma_id=1" \
  -H "Authorization: Bearer {token}"

# Filtrar por status
curl -X GET "https://seudominio.com/api/cronograma-item?status=EM_ANDAMENTO" \
  -H "Authorization: Bearer {token}"
```

**Resposta de Sucesso (200):**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "cronograma_id": 1,
      "nome_item": "Abertura do Evento",
      "data_hora_inicio": "2024-12-01 10:00:00",
      "data_hora_fim": "2024-12-01 11:00:00",
      "duracao_minutos": 60,
      "duracao_formatada": "1h",
      "ativo": true,
      "status": "AGUARDANDO",
      "is_passado": false,
      "is_agora": false,
      "cronograma": {
        "id": 1,
        "nome": "Programação Principal"
      },
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

### 2. Buscar Item por ID

Retorna detalhes completos de um item específico.

**Endpoint:** `GET /api/cronograma-item/{id}`

**Exemplo:**

```bash
curl -X GET "https://seudominio.com/api/cronograma-item/1" \
  -H "Authorization: Bearer {token}"
```

**Resposta de Sucesso (200):**

```json
{
  "success": true,
  "data": {
    "id": 1,
    "cronograma_id": 1,
    "nome_item": "Abertura do Evento",
    "data_hora_inicio": "2024-12-01 10:00:00",
    "data_hora_fim": "2024-12-01 11:00:00",
    "duracao_minutos": 60,
    "duracao_formatada": "1h",
    "ativo": true,
    "status": "AGUARDANDO",
    "is_passado": false,
    "is_agora": false,
    "cronograma": {
      "id": 1,
      "nome": "Programação Principal"
    },
    "evento": {
      "id": 1,
      "nome": "Festival de Música 2024",
      "slug": "festival-de-musica-2024"
    },
    "created_at": "2024-11-20 10:30:00",
    "updated_at": "2024-11-20 10:30:00"
  }
}
```

---

### 3. Criar Item

Cria um novo item vinculado a um cronograma.

**Endpoint:** `POST /api/cronograma-item`

**Body (JSON):**

```json
{
  "cronograma_id": 1,
  "nome_item": "Palestra de Abertura",
  "data_hora_inicio": "2024-12-01 10:00:00",
  "data_hora_fim": "2024-12-01 11:30:00",
  "ativo": true,
  "status": "AGUARDANDO"
}
```

**Campos:**
- `cronograma_id` (obrigatório) - ID do cronograma
- `nome_item` (obrigatório) - Nome do item (min: 3, max: 255)
- `data_hora_inicio` (opcional) - Data/hora de início (formato: Y-m-d H:i:s)
- `data_hora_fim` (opcional) - Data/hora de fim (formato: Y-m-d H:i:s)
- `ativo` (opcional) - Status ativo (padrão: true)
- `status` (opcional) - Status do item (padrão: AGUARDANDO)

**Exemplo:**

```bash
curl -X POST "https://seudominio.com/api/cronograma-item" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "cronograma_id": 1,
    "nome_item": "Palestra de Abertura",
    "data_hora_inicio": "2024-12-01 10:00:00",
    "data_hora_fim": "2024-12-01 11:30:00",
    "ativo": true,
    "status": "AGUARDANDO"
  }'
```

**Resposta de Sucesso (201):**

```json
{
  "success": true,
  "message": "Item criado com sucesso",
  "data": {
    "id": 2,
    "cronograma_id": 1,
    "nome_item": "Palestra de Abertura",
    "data_hora_inicio": "2024-12-01 10:00:00",
    "data_hora_fim": "2024-12-01 11:30:00",
    "ativo": true,
    "status": "AGUARDANDO",
    "created_at": "2024-11-20 14:30:00"
  }
}
```

---

### 4. Atualizar Item

Atualiza um item existente.

**Endpoint:** 
- `PUT /api/cronograma-item/{id}` (atualização completa)
- `PATCH /api/cronograma-item/{id}` (atualização parcial)

**Body (JSON):**

```json
{
  "nome_item": "Nome Atualizado",
  "data_hora_inicio": "2024-12-01 11:00:00",
  "status": "EM_ANDAMENTO"
}
```

**Exemplo:**

```bash
curl -X PATCH "https://seudominio.com/api/cronograma-item/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "EM_ANDAMENTO"
  }'
```

**Resposta de Sucesso (200):**

```json
{
  "success": true,
  "message": "Item atualizado com sucesso",
  "data": {
    "id": 1,
    "cronograma_id": 1,
    "nome_item": "Abertura do Evento",
    "data_hora_inicio": "2024-12-01 10:00:00",
    "data_hora_fim": "2024-12-01 11:00:00",
    "ativo": true,
    "status": "EM_ANDAMENTO",
    "updated_at": "2024-11-20 15:00:00"
  }
}
```

---

### 5. Atualizar Status do Item

Atalho para atualizar apenas o status de um item.

**Endpoint:** `PATCH /api/cronograma-item/{id}/status`

**Body (JSON):**

```json
{
  "status": "CONCLUIDO"
}
```

**Exemplo:**

```bash
curl -X PATCH "https://seudominio.com/api/cronograma-item/1/status" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "CONCLUIDO"
  }'
```

**Resposta de Sucesso (200):**

```json
{
  "success": true,
  "message": "Status atualizado com sucesso",
  "data": {
    "id": 1,
    "status": "CONCLUIDO"
  }
}
```

---

### 6. Excluir Item

Exclui (soft delete) um item.

**Endpoint:** `DELETE /api/cronograma-item/{id}`

**Exemplo:**

```bash
curl -X DELETE "https://seudominio.com/api/cronograma-item/1" \
  -H "Authorization: Bearer {token}"
```

**Resposta de Sucesso (200):**

```json
{
  "success": true,
  "message": "Item excluído com sucesso"
}
```

---

### 7. Listar Itens por Cronograma

Lista todos os itens de um cronograma específico, ordenados por data/hora de início.

**Endpoint:** `GET /api/cronograma-item/cronograma/{cronograma_id}`

**Exemplo:**

```bash
curl -X GET "https://seudominio.com/api/cronograma-item/cronograma/1" \
  -H "Authorization: Bearer {token}"
```

**Resposta de Sucesso (200):**

```json
{
  "success": true,
  "data": {
    "cronograma": {
      "id": 1,
      "nome": "Programação Principal"
    },
    "itens": [
      {
        "id": 1,
        "nome_item": "Abertura",
        "data_hora_inicio": "2024-12-01 10:00:00",
        "data_hora_fim": "2024-12-01 11:00:00",
        "duracao_minutos": 60,
        "duracao_formatada": "1h",
        "ativo": true,
        "status": "AGUARDANDO",
        "is_passado": false,
        "is_agora": false,
        "created_at": "2024-11-20 10:00:00",
        "updated_at": "2024-11-20 10:00:00"
      },
      {
        "id": 2,
        "nome_item": "Palestra Principal",
        "data_hora_inicio": "2024-12-01 11:00:00",
        "data_hora_fim": "2024-12-01 12:30:00",
        "duracao_minutos": 90,
        "duracao_formatada": "1h 30min",
        "ativo": true,
        "status": "AGUARDANDO",
        "is_passado": false,
        "is_agora": false,
        "created_at": "2024-11-20 11:00:00",
        "updated_at": "2024-11-20 11:00:00"
      }
    ],
    "total": 2
  }
}
```

---

### 8. Listar Próximos Itens

Lista os próximos itens de um cronograma (a partir da data/hora atual).

**Endpoint:** `GET /api/cronograma-item/cronograma/{cronograma_id}/proximos`

**Query Parameters (opcionais):**
- `limit` - Limite de resultados (padrão: 10)

**Exemplo:**

```bash
# Listar próximos 5 itens
curl -X GET "https://seudominio.com/api/cronograma-item/cronograma/1/proximos?limit=5" \
  -H "Authorization: Bearer {token}"
```

**Resposta de Sucesso (200):**

```json
{
  "success": true,
  "data": {
    "cronograma": {
      "id": 1,
      "nome": "Programação Principal"
    },
    "proximos_itens": [
      {
        "id": 3,
        "nome_item": "Próxima Palestra",
        "data_hora_inicio": "2024-12-01 14:00:00",
        "data_hora_fim": "2024-12-01 15:00:00",
        "duracao_minutos": 60,
        "duracao_formatada": "1h",
        "status": "AGUARDANDO"
      }
    ],
    "total": 1
  }
}
```

---

## 📊 Campos Calculados

A API retorna campos calculados automaticamente:

### duracao_minutos
Duração do item em minutos (diferença entre data_hora_fim e data_hora_inicio).

### duracao_formatada
Duração formatada (Ex: "1h 30min", "45min").

### is_passado
Boolean indicando se o item já passou (data_hora_fim < agora).

### is_agora
Boolean indicando se o item está acontecendo no momento atual.

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
  "message": "Erro ao criar item",
  "errors": {
    "nome_item": "O campo Nome do Item precisa ter pelo menos 3 caracteres.",
    "status": "O campo Status deve ser AGUARDANDO, EM_ANDAMENTO, CONCLUIDO ou CANCELADO."
  }
}
```

---

## 📝 Fluxo de Status Recomendado

```
AGUARDANDO → EM_ANDAMENTO → CONCLUIDO
                    ↓
                CANCELADO
```

**Exemplo de uso:**

```javascript
// Marcar item como em andamento
await fetch(`https://seudominio.com/api/cronograma-item/1/status`, {
  method: 'PATCH',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({ status: 'EM_ANDAMENTO' })
});

// Após conclusão
await fetch(`https://seudominio.com/api/cronograma-item/1/status`, {
  method: 'PATCH',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({ status: 'CONCLUIDO' })
});
```

---

## 🧪 Testando a API

### Exemplo Completo com JavaScript:

```javascript
// Login
const loginResponse = await fetch('https://seudominio.com/api/auth/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'seu@email.com',
    password: 'suasenha'
  })
});

const { data } = await loginResponse.json();
const token = data.token;

// Criar item
const createResponse = await fetch('https://seudominio.com/api/cronograma-item', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    cronograma_id: 1,
    nome_item: 'Palestra de Tecnologia',
    data_hora_inicio: '2024-12-01 14:00:00',
    data_hora_fim: '2024-12-01 15:30:00',
    ativo: true,
    status: 'AGUARDANDO'
  })
});

const novoItem = await createResponse.json();
console.log(novoItem);

// Listar itens do cronograma
const itensResponse = await fetch('https://seudominio.com/api/cronograma-item/cronograma/1', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});

const itens = await itensResponse.json();
console.log(itens);

// Atualizar status
const statusResponse = await fetch(`https://seudominio.com/api/cronograma-item/${novoItem.data.id}/status`, {
  method: 'PATCH',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({ status: 'EM_ANDAMENTO' })
});

const statusAtualizado = await statusResponse.json();
console.log(statusAtualizado);

// Buscar próximos itens
const proximosResponse = await fetch('https://seudominio.com/api/cronograma-item/cronograma/1/proximos?limit=5', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});

const proximos = await proximosResponse.json();
console.log(proximos);
```

---

## 💡 Casos de Uso

### 1. App de Evento em Tempo Real

```javascript
// Atualizar a cada minuto
setInterval(async () => {
  const response = await fetch(`/api/cronograma-item/cronograma/1/proximos?limit=3`, {
    headers: { 'Authorization': `Bearer ${token}` }
  });
  
  const { data } = await response.json();
  updateUI(data.proximos_itens);
}, 60000);
```

### 2. Painel de Controle do Evento

```javascript
// Listar todos os itens com filtro
const response = await fetch(`/api/cronograma-item?cronograma_id=1&ativo=1`, {
  headers: { 'Authorization': `Bearer ${token}` }
});

const { data } = await response.json();

// Identificar item atual
const itemAtual = data.find(item => item.is_agora);
if (itemAtual) {
  console.log('Acontecendo agora:', itemAtual.nome_item);
}
```

### 3. Notificação de Início de Item

```javascript
// Verificar se algum item está para começar em 15 minutos
const proximos = await fetch(`/api/cronograma-item/cronograma/1/proximos`, {
  headers: { 'Authorization': `Bearer ${token}` }
});

const { data } = await proximos.json();

data.proximos_itens.forEach(item => {
  const inicio = new Date(item.data_hora_inicio);
  const agora = new Date();
  const diff = (inicio - agora) / 1000 / 60; // minutos
  
  if (diff <= 15 && diff > 0) {
    notifyUser(`${item.nome_item} começa em ${Math.round(diff)} minutos!`);
  }
});
```

---

## 📞 Suporte

Para dúvidas ou problemas, verifique os logs do sistema ou entre em contato com o administrador.

