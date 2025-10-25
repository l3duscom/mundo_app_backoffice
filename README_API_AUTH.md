# 🔐 API de Autenticação JWT

API de autenticação completa para o sistema, mantendo toda a lógica de permissões e grupos existente.

## 🚀 Quick Start

### 1. Configuração (obrigatório)

Adicione no arquivo `.env`:

```env
JWT_SECRET_KEY=sua_chave_secreta_aqui_minimo_32_caracteres
```

💡 **Dica:** Gere uma chave forte com:
```bash
php -r "echo bin2hex(random_bytes(32));"
```

### 2. Fazer Login

```bash
curl -X POST http://seu-dominio.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "usuario@exemplo.com",
    "password": "senha123"
  }'
```

**Resposta:**
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "expires_in": 86400,
    "user": { ... }
  }
}
```

### 3. Usar o Token

```bash
curl -X GET http://seu-dominio.com/api/auth/me \
  -H "Authorization: Bearer SEU_TOKEN_AQUI"
```

## 📋 Endpoints

| Método | Endpoint | Descrição | Auth |
|--------|----------|-----------|------|
| `POST` | `/api/auth/login` | Fazer login | ❌ |
| `POST` | `/api/auth/refresh` | Renovar token | ❌ |
| `GET` | `/api/auth/me` | Perfil do usuário | ✅ |

## 🔒 Proteger suas Rotas

### Opção 1: No arquivo de rotas

```php
// app/Config/Routes.php

// Rota simples protegida
$routes->get('api/produtos', 'Api\Produtos::index', ['filter' => 'jwtAuth']);

// Grupo de rotas protegidas
$routes->group('api/admin', ['filter' => 'jwtAuth'], function ($routes) {
    $routes->get('usuarios', 'Api\Admin::usuarios');
    $routes->post('usuarios', 'Api\Admin::criar');
});

// Com permissão específica
$routes->get('api/relatorios', 'Api\Relatorios::index', ['filter' => 'jwtAuth:listar-relatorios']);
```

### Opção 2: No controller

```php
<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class Produtos extends BaseController
{
    public function index()
    {
        // Acessa dados do usuário autenticado
        $user = $this->request->usuarioAutenticado;
        
        // Verifica se é admin
        if ($user['is_admin']) {
            // Admin tem acesso total
        }
        
        // Verifica permissão específica
        if (in_array('listar-produtos', $user['permissoes'] ?? [])) {
            // Usuário tem permissão
        }
        
        // Sua lógica aqui...
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $produtos
        ]);
    }
}
```

## 👥 Sistema de Permissões

A API mantém **exatamente a mesma lógica** do sistema atual:

### Grupos

- **Admin (ID: 1)** → Acesso total (`is_admin = true`)
- **Cliente (ID: 2)** → Cliente/consumidor (`is_cliente = true`)
- **Membro (ID: 3)** → Membro (`is_membro = true`)
- **Parceiro (ID: 4)** → Parceiro (`is_parceiro = true`)
- **Influencer (ID: 5)** → Influencer (`is_influencer = true`)

### Token JWT contém:

```json
{
  "user_id": 1,
  "nome": "João Silva",
  "email": "joao@exemplo.com",
  "is_admin": true,
  "is_cliente": false,
  "is_membro": false,
  "is_parceiro": false,
  "is_influencer": false,
  "permissoes": ["listar-produtos", "editar-produtos"]
}
```

## 🧪 Testar a API

Execute o script de teste incluído:

```bash
php test_api_auth.php
```

Ou teste manualmente com Postman, Insomnia, ou qualquer cliente HTTP.

## 📚 Documentação Completa

- **[API_AUTH_DOCUMENTATION.md](./API_AUTH_DOCUMENTATION.md)** - Documentação completa e detalhada
- **[API_AUTH_EXAMPLES.md](./API_AUTH_EXAMPLES.md)** - Exemplos práticos de uso
- **[test_api_auth.php](./test_api_auth.php)** - Script de teste automatizado

## ⚠️ Importante

### O que NÃO mudou

✅ Login web atual continua funcionando normalmente  
✅ Sistema de sessões não foi alterado  
✅ Lógica de permissões e grupos está idêntica  
✅ Nenhuma rota existente foi modificada  

### O que foi adicionado

✅ API de login via JWT (`/api/auth/login`)  
✅ Refresh token para renovar sessão  
✅ Filtro `jwtAuth` para proteger rotas de API  
✅ Biblioteca JWT para geração e validação de tokens  

## 🔐 Segurança

- ✅ Tokens expiram em **24 horas** (ajustável)
- ✅ Refresh tokens expiram em **30 dias** (ajustável)
- ✅ Validação de assinatura com chave secreta
- ✅ Verificação de usuário ativo em cada requisição
- ✅ Suporte a permissões granulares
- ⚠️ Use **HTTPS em produção**
- ⚠️ Armazene tokens de forma segura no cliente

## 💡 Casos de Uso

### ✅ Aplicativos Mobile
Use JWT para autenticar apps iOS/Android

### ✅ SPAs (Single Page Applications)
Integre com React, Vue, Angular

### ✅ Integrações entre Sistemas
Permita que outros sistemas se conectem à sua API

### ✅ APIs de Terceiros
Forneça acesso programático ao seu sistema

## 🆘 Suporte

**Problemas comuns:**

1. **Token inválido** → Verifique se `JWT_SECRET_KEY` está configurada
2. **Permissão negada** → Verifique grupos e permissões do usuário
3. **CSRF error** → Rotas `/api/auth/*` estão isentas de CSRF

**Logs:**
```bash
tail -f writable/logs/log-*.log
```

## 📝 Licença

Este código faz parte do projeto Mundo App e segue a mesma licença.

---

**Desenvolvido com ❤️ mantendo compatibilidade total com o sistema existente**

