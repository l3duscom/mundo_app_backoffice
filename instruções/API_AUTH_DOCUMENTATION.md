# API de Autenticação - Documentação

## Visão Geral

Esta API de autenticação foi criada para permitir login via API sem interferir no sistema de login atual baseado em sessões. A API usa **JWT (JSON Web Tokens)** para autenticação stateless e mantém toda a lógica de **permissões e grupos** do sistema.

## Configuração

### 1. Adicionar Chave Secreta no `.env`

Adicione a seguinte linha no seu arquivo `.env` na raiz do projeto:

```env
JWT_SECRET_KEY=sua_chave_secreta_super_segura_aqui_min_32_caracteres
```

**IMPORTANTE:** Use uma chave forte e única. Você pode gerar uma usando:
```bash
php -r "echo bin2hex(random_bytes(32));"
```

Se `JWT_SECRET_KEY` não estiver configurada, o sistema usará `CHAVE_RECUPERACAO_SENHA` como fallback.

## Endpoints Disponíveis

### 1. Login (Autenticação)

Realiza o login e retorna um token JWT para autenticação.

**Endpoint:** `POST /api/auth/login`

**Headers:**
```
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "email": "usuario@exemplo.com",
  "password": "senha123"
}
```

**Resposta de Sucesso (200):**
```json
{
  "success": true,
  "message": "Login realizado com sucesso",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer",
    "expires_in": 86400,
    "user": {
      "id": 1,
      "nome": "João Silva",
      "email": "usuario@exemplo.com",
      "codigo": "ABC1234",
      "ativo": true,
      "is_admin": true,
      "is_cliente": false,
      "is_membro": false,
      "is_parceiro": false,
      "is_influencer": false,
      "permissoes": []
    }
  }
}
```

**Respostas de Erro:**
- `400` - Campos obrigatórios não fornecidos
- `401` - Credenciais inválidas (email ou senha incorretos)
- `403` - Usuário inativo

### 2. Refresh Token (Renovar Token)

Renova o token de acesso usando o refresh token.

**Endpoint:** `POST /api/auth/refresh`

**Headers:**
```
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

**Resposta de Sucesso (200):**
```json
{
  "success": true,
  "message": "Token renovado com sucesso",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer",
    "expires_in": 86400
  }
}
```

**Respostas de Erro:**
- `400` - Refresh token não fornecido
- `401` - Refresh token inválido ou expirado

### 3. Perfil do Usuário (Me)

Retorna os dados atualizados do usuário autenticado.

**Endpoint:** `GET /api/auth/me`

**Headers:**
```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

**Resposta de Sucesso (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "nome": "João Silva",
    "email": "usuario@exemplo.com",
    "codigo": "ABC1234",
    "ativo": true,
    "imagem": null,
    "is_admin": true,
    "is_cliente": false,
    "is_membro": false,
    "is_parceiro": false,
    "is_influencer": false,
    "permissoes": [],
    "created_at": "2024-01-15 10:30:00",
    "updated_at": "2024-01-20 14:45:00"
  }
}
```

**Respostas de Erro:**
- `401` - Token não fornecido, inválido ou expirado

## Como Usar em Outras Rotas

### Proteger Rotas com JWT

Para proteger qualquer rota da API com autenticação JWT, use o filtro `jwtAuth`:

**No arquivo `app/Config/Routes.php`:**

```php
// Rota única protegida
$routes->get('api/produtos', 'Api\Produtos::index', ['filter' => 'jwtAuth']);

// Grupo de rotas protegidas
$routes->group('api/admin', ['filter' => 'jwtAuth'], function ($routes) {
    $routes->get('usuarios', 'Api\Admin::usuarios');
    $routes->post('usuarios', 'Api\Admin::criarUsuario');
});

// Rota protegida com permissão específica
$routes->get('api/relatorios', 'Api\Relatorios::index', ['filter' => 'jwtAuth:listar-relatorios']);
```

### Acessar Dados do Usuário Autenticado no Controller

```php
<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class Produtos extends BaseController
{
    public function index()
    {
        // Acessa os dados do usuário autenticado via JWT
        $usuarioAutenticado = $this->request->usuarioAutenticado;
        
        $userId = $usuarioAutenticado['user_id'];
        $isAdmin = $usuarioAutenticado['is_admin'];
        $permissoes = $usuarioAutenticado['permissoes'] ?? [];
        
        // Sua lógica aqui...
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $produtos
        ]);
    }
}
```

## Sistema de Permissões e Grupos

A API mantém **exatamente a mesma lógica** do sistema atual:

### Grupos de Usuários

1. **Admin (Grupo ID: 1)**
   - Tem acesso total a todas as funcionalidades
   - `is_admin = true`
   - Não precisa de permissões específicas

2. **Cliente (Grupo ID: 2)**
   - Usuários clientes/consumidores
   - `is_cliente = true`

3. **Membro (Grupo ID: 3)**
   - `is_membro = true`

4. **Parceiro (Grupo ID: 4)**
   - `is_parceiro = true`

5. **Influencer (Grupo ID: 5)**
   - `is_influencer = true`

### Verificação de Permissões

O token JWT contém todas as permissões do usuário. Você pode verificar permissões:

```php
// No controller
$usuarioAutenticado = $this->request->usuarioAutenticado;

// Admin tem acesso a tudo
if ($usuarioAutenticado['is_admin']) {
    // Permitir acesso
}

// Verificar permissão específica
if (in_array('listar-usuarios', $usuarioAutenticado['permissoes'] ?? [])) {
    // Usuário tem permissão para listar usuários
}

// Verificar grupo
if ($usuarioAutenticado['is_cliente']) {
    // É um cliente
}
```

### Proteger Rota com Permissão Específica

Use o filtro com argumento para exigir permissão específica:

```php
// Em Routes.php
$routes->get('api/usuarios', 'Api\Usuarios::index', ['filter' => 'jwtAuth:listar-usuarios']);
```

Isso garantirá que apenas usuários **admin** ou com a permissão **"listar-usuarios"** possam acessar a rota.

## Exemplos de Uso

### Exemplo 1: Login com cURL

```bash
curl -X POST http://seu-dominio.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "usuario@exemplo.com",
    "password": "senha123"
  }'
```

### Exemplo 2: Acessar Rota Protegida

```bash
curl -X GET http://seu-dominio.com/api/auth/me \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
```

### Exemplo 3: JavaScript/Fetch

```javascript
// Login
const login = async (email, password) => {
  const response = await fetch('http://seu-dominio.com/api/auth/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ email, password })
  });
  
  const data = await response.json();
  
  if (data.success) {
    // Armazena o token (localStorage, sessionStorage, etc)
    localStorage.setItem('token', data.data.token);
    localStorage.setItem('refresh_token', data.data.refresh_token);
    return data.data.user;
  }
  
  throw new Error(data.message);
};

// Fazer requisição autenticada
const getProfile = async () => {
  const token = localStorage.getItem('token');
  
  const response = await fetch('http://seu-dominio.com/api/auth/me', {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  
  return await response.json();
};
```

## Segurança

### Boas Práticas

1. **Chave Secreta:** Use uma chave forte e única no `JWT_SECRET_KEY`
2. **HTTPS:** Sempre use HTTPS em produção para proteger os tokens
3. **Tempo de Expiração:** Tokens expiram em 24 horas (pode ser ajustado)
4. **Refresh Token:** Use refresh token para renovar tokens sem fazer login novamente
5. **Armazenamento:** No frontend, armazene tokens de forma segura (evite localStorage se possível, prefira httpOnly cookies)

### Tempo de Expiração

- **Access Token:** 24 horas (86400 segundos)
- **Refresh Token:** 30 dias (2592000 segundos)

Para alterar, edite os valores em `app/Controllers/Api/Auth.php`:

```php
// Access token - 1 hora
$token = Jwt::encode($payload, 3600);

// Refresh token - 7 dias
$refreshToken = Jwt::encode([...], 604800);
```

## Diferenças entre Login Web e API

| Aspecto | Login Web | Login API |
|---------|-----------|-----------|
| **Autenticação** | Session-based | JWT Token |
| **Estado** | Stateful (sessão no servidor) | Stateless (token no cliente) |
| **Rota** | `/login` → `Login::criar()` | `/api/auth/login` → `Api\Auth::login()` |
| **Filtro** | `LoginFilter` | `JwtAuthFilter` |
| **Resposta** | Redirect ou JSON | Sempre JSON |
| **Logout** | Destrói sessão | Cliente descarta token |
| **Permissões** | ✅ Mesma lógica | ✅ Mesma lógica |

**IMPORTANTE:** Ambos os sistemas coexistem e não interferem um com o outro. O login web continua funcionando exatamente como antes.

## Solução de Problemas

### Token Inválido
- Verifique se o token não expirou
- Verifique se está enviando o header correto: `Authorization: Bearer TOKEN`
- Verifique se `JWT_SECRET_KEY` está configurada no `.env`

### Permissões Negadas
- Verifique se o usuário pertence ao grupo correto
- Verifique se o usuário tem a permissão necessária na tabela `permissoes`
- Admin sempre tem acesso total

### CSRF Token
- A API está isenta de CSRF, não precisa enviar `csrf_token`
- Se estiver recebendo erro CSRF, verifique se a rota está em `app/Config/Filters.php` na exceção

## Suporte

Para dúvidas ou problemas:
1. Verifique os logs em `writable/logs/`
2. Ative o debug em `.env`: `CI_ENVIRONMENT = development`
3. Consulte a documentação do CodeIgniter 4

