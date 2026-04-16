# API de Produtos - Documentação

## 📋 Visão Geral

API RESTful para gerenciar produtos de eventos. Todas as rotas requerem autenticação JWT.

**Base URL:** `/api/produtos`

---

## 🔐 Autenticação

Todas as requisições requerem um token JWT válido no header:

```
Authorization: Bearer {seu_token_jwt}
```

---

## 📡 Endpoints

### 1. Listar Todos os Produtos

**GET** `/api/produtos`

Lista todos os produtos com filtros opcionais.

#### Query Parameters (opcionais):
- `event_id` (int): Filtrar por evento específico
- `categoria` (string): Filtrar por categoria

#### Exemplo de Requisição:
```bash
GET /api/produtos
GET /api/produtos?event_id=1
GET /api/produtos?categoria=Vestuário
GET /api/produtos?event_id=1&categoria=Acessórios
```

#### Resposta de Sucesso (200):
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "event_id": 1,
      "imagem": "/uploads/produtos/camiseta.png",
      "categoria": "Vestuário",
      "nome": "Camiseta Oficial do Evento",
      "preco": 79.90,
      "pontos": 100,
      "created_at": "2024-12-04 10:00:00"
    },
    {
      "id": 2,
      "event_id": 1,
      "imagem": "/uploads/produtos/caneca.png",
      "categoria": "Acessórios",
      "nome": "Caneca Personalizada",
      "preco": 29.90,
      "pontos": 50,
      "created_at": "2024-12-04 10:05:00"
    }
  ],
  "total": 2
}
```

---

### 2. Listar Produtos por Evento

**GET** `/api/produtos/evento/{event_id}`

Lista todos os produtos de um evento específico.

#### Parâmetros:
- `event_id` (int, obrigatório): ID do evento

#### Exemplo de Requisição:
```bash
GET /api/produtos/evento/1
```

#### Resposta de Sucesso (200):
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "event_id": 1,
      "imagem": "/uploads/produtos/camiseta.png",
      "categoria": "Vestuário",
      "nome": "Camiseta Oficial do Evento",
      "preco": 79.90,
      "pontos": 100,
      "created_at": "2024-12-04 10:00:00"
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

### 3. Listar Categorias por Evento

**GET** `/api/produtos/categorias/{event_id}`

Lista todas as categorias de produtos disponíveis em um evento.

#### Parâmetros:
- `event_id` (int, obrigatório): ID do evento

#### Exemplo de Requisição:
```bash
GET /api/produtos/categorias/1
```

#### Resposta de Sucesso (200):
```json
{
  "success": true,
  "data": [
    "Acessórios",
    "Decoração",
    "Vestuário"
  ],
  "total": 3
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

### 4. Buscar Produto Específico

**GET** `/api/produtos/{id}`

Retorna os detalhes de um produto específico.

#### Parâmetros:
- `id` (int, obrigatório): ID do produto

#### Exemplo de Requisição:
```bash
GET /api/produtos/1
```

#### Resposta de Sucesso (200):
```json
{
  "success": true,
  "data": {
    "id": 1,
    "event_id": 1,
    "imagem": "/uploads/produtos/camiseta.png",
    "categoria": "Vestuário",
    "nome": "Camiseta Oficial do Evento",
    "preco": 79.90,
    "pontos": 100,
    "created_at": "2024-12-04 10:00:00",
    "updated_at": "2024-12-04 10:00:00"
  }
}
```

#### Resposta de Erro (404):
```json
{
  "success": false,
  "message": "Produto não encontrado"
}
```

---

### 5. Criar Novo Produto

**POST** `/api/produtos`

Cria um novo produto.

#### Body JSON (obrigatório):
```json
{
  "event_id": 1,
  "imagem": "/uploads/produtos/camiseta.png",
  "categoria": "Vestuário",
  "nome": "Camiseta Oficial do Evento",
  "preco": 79.90,
  "pontos": 100
}
```

#### Campos:
| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `event_id` | int | ✅ Sim | ID do evento |
| `imagem` | string | ❌ Não | URL ou caminho da imagem (max 500 caracteres) |
| `categoria` | string | ✅ Sim | Categoria do produto (max 100 caracteres) |
| `nome` | string | ✅ Sim | Nome do produto (max 255 caracteres) |
| `preco` | decimal | ✅ Sim | Preço do produto (ex: 79.90) |
| `pontos` | int | ✅ Sim | Pontos necessários para resgatar o produto |

#### Exemplo de Requisição:
```bash
POST /api/produtos
Content-Type: application/json

{
  "event_id": 1,
  "imagem": "/uploads/produtos/poster.png",
  "categoria": "Decoração",
  "nome": "Poster Autografado",
  "preco": 49.90,
  "pontos": 75
}
```

#### Resposta de Sucesso (201):
```json
{
  "success": true,
  "message": "Produto criado com sucesso",
  "data": {
    "id": 3,
    "event_id": 1,
    "imagem": "/uploads/produtos/poster.png",
    "categoria": "Decoração",
    "nome": "Poster Autografado",
    "preco": 49.90,
    "pontos": 75,
    "created_at": "2024-12-04 14:30:00"
  }
}
```

#### Resposta de Erro (422):
```json
{
  "success": false,
  "message": "Erro ao criar produto",
  "errors": {
    "nome": "O nome do produto é obrigatório",
    "preco": "O preço deve ser um valor decimal válido"
  }
}
```

---

### 6. Atualizar Produto

**PUT** `/api/produtos/{id}` ou **PATCH** `/api/produtos/{id}`

Atualiza um produto existente.

#### Parâmetros:
- `id` (int, obrigatório): ID do produto

#### Body JSON:
```json
{
  "nome": "Camiseta Oficial - Edição Limitada",
  "preco": 89.90,
  "pontos": 120
}
```

Todos os campos são opcionais. Apenas os campos fornecidos serão atualizados.

#### Exemplo de Requisição:
```bash
PUT /api/produtos/1
Content-Type: application/json

{
  "preco": 89.90,
  "pontos": 120
}
```

#### Resposta de Sucesso (200):
```json
{
  "success": true,
  "message": "Produto atualizado com sucesso",
  "data": {
    "id": 1,
    "event_id": 1,
    "imagem": "/uploads/produtos/camiseta.png",
    "categoria": "Vestuário",
    "nome": "Camiseta Oficial do Evento",
    "preco": 89.90,
    "pontos": 120,
    "updated_at": "2024-12-04 15:00:00"
  }
}
```

#### Resposta de Erro (404):
```json
{
  "success": false,
  "message": "Produto não encontrado"
}
```

---

### 7. Deletar Produto

**DELETE** `/api/produtos/{id}`

Deleta um produto (soft delete).

#### Parâmetros:
- `id` (int, obrigatório): ID do produto

#### Exemplo de Requisição:
```bash
DELETE /api/produtos/1
```

#### Resposta de Sucesso (200):
```json
{
  "success": true,
  "message": "Produto deletado com sucesso"
}
```

#### Resposta de Erro (404):
```json
{
  "success": false,
  "message": "Produto não encontrado"
}
```

---

## 📦 Categorias Sugeridas

As categorias são flexíveis e podem ser customizadas por evento. Sugestões:

- **Vestuário** - Camisetas, bonés, moletons, etc.
- **Acessórios** - Canecas, chaveiros, bottons, etc.
- **Decoração** - Posters, quadros, adesivos, etc.
- **Colecionáveis** - Action figures, cards, mangás, etc.
- **Alimentação** - Combos de comida, bebidas, etc.
- **Experiências** - Meet & Greet, fotos, autógrafos, etc.

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

## 🎯 Exemplos de Produtos

```json
[
  {
    "imagem": "/uploads/produtos/camiseta.png",
    "categoria": "Vestuário",
    "nome": "Camiseta Oficial do Evento",
    "preco": 79.90,
    "pontos": 100
  },
  {
    "imagem": "/uploads/produtos/caneca.png",
    "categoria": "Acessórios",
    "nome": "Caneca Personalizada",
    "preco": 29.90,
    "pontos": 50
  },
  {
    "imagem": "/uploads/produtos/poster.png",
    "categoria": "Decoração",
    "nome": "Poster Autografado",
    "preco": 49.90,
    "pontos": 75
  },
  {
    "imagem": "/uploads/produtos/action-figure.png",
    "categoria": "Colecionáveis",
    "nome": "Action Figure Exclusiva",
    "preco": 199.90,
    "pontos": 250
  },
  {
    "imagem": "/uploads/produtos/meet-greet.png",
    "categoria": "Experiências",
    "nome": "Meet & Greet VIP",
    "preco": 299.90,
    "pontos": 500
  }
]
```

---

## 🔧 Executar Migration

Para criar a tabela de produtos no banco de dados:

```bash
php spark migrate
```

Ou execute o SQL diretamente:

```sql
CREATE TABLE `produtos` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_id` INT(5) UNSIGNED NOT NULL,
    `imagem` VARCHAR(500) NULL,
    `categoria` VARCHAR(100) NOT NULL,
    `nome` VARCHAR(255) NOT NULL,
    `preco` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `pontos` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    `deleted_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    INDEX `produtos_event_id` (`event_id`),
    INDEX `produtos_categoria` (`categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
```

---

## 📝 Notas

- Todas as datas são retornadas no formato `Y-m-d H:i:s`
- O soft delete é usado - registros deletados não são removidos fisicamente
- A validação do `event_id` verifica se o evento existe antes de criar/atualizar
- O campo `preco` aceita valores decimais com 2 casas (ex: 79.90)
- O campo `pontos` é usado para sistema de resgate por pontos de fidelidade

---

## ⚠️ Erros Comuns e Soluções

### Erro 400: Dados não fornecidos
- **Causa**: JSON vazio ou mal formatado
- **Solução**: Verifique se o Content-Type é `application/json` e o body está válido

### Erro 404: Evento não encontrado
- **Causa**: O `event_id` fornecido não existe no banco
- **Solução**: Verifique se o evento existe antes de criar o produto

### Erro 422: Erro de validação
- **Causa**: Dados não atendem às regras de validação
- **Solução**: Verifique a mensagem de erro no campo `errors` da resposta
- **Campos obrigatórios**: `event_id`, `categoria`, `nome`, `preco`, `pontos`

### Erro 405: Método não permitido
- **Causa**: Método HTTP incorreto
- **Solução**: Verifique se está usando o método correto (GET, POST, PUT, PATCH, DELETE)

---

## 🔗 Rotas Disponíveis

| Método | Rota | Descrição |
|--------|------|-----------|
| `GET` | `/api/produtos` | Lista todos os produtos |
| `GET` | `/api/produtos/{id}` | Detalhes de um produto |
| `GET` | `/api/produtos/evento/{event_id}` | Produtos por evento |
| `GET` | `/api/produtos/categorias/{event_id}` | Categorias por evento |
| `POST` | `/api/produtos` | Cria novo produto |
| `PUT` | `/api/produtos/{id}` | Atualiza produto |
| `PATCH` | `/api/produtos/{id}` | Atualiza produto parcialmente |
| `DELETE` | `/api/produtos/{id}` | Remove produto (soft delete) |

