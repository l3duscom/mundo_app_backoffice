# 🔐 Onde o Token JWT é Armazenado?

## ❓ Pergunta
> "Ao fazer login via API `/api/auth/login`, onde fica salvo o token do usuário?"

---

## ✅ Resposta Rápida

**O token JWT NÃO é salvo no servidor nem no banco de dados.**

O token é:
1. ✅ **Gerado** no momento do login
2. ✅ **Retornado** na resposta da API
3. ✅ **Armazenado pelo cliente** (app, frontend, etc.)
4. ✅ **Enviado de volta** em cada requisição protegida

---

## 🔍 Como Funciona (Passo a Passo)

### **1️⃣ Usuário Faz Login**
```bash
POST /api/auth/login
Content-Type: application/json

{
  "email": "usuario@exemplo.com",
  "password": "senha123"
}
```

### **2️⃣ Servidor Gera o Token**

No controller `app/Controllers/Api/Auth.php`:

```php
// Linha 196-197
$token = Jwt::encode($payload, 86400); // Token de acesso (24h)

// Linha 199-203
$refreshToken = Jwt::encode([
    'user_id' => $usuario->id,
    'type' => 'refresh'
], 2592000); // Refresh token (30 dias)
```

### **3️⃣ Servidor Retorna o Token (NÃO salva)**

```json
{
  "success": true,
  "message": "Login realizado com sucesso",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",           // ← Token de acesso
    "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",  // ← Refresh token
    "token_type": "Bearer",
    "expires_in": 86400,                             // ← 24 horas
    "user": {
      "id": 1,
      "nome": "João Silva",
      "email": "usuario@exemplo.com"
    }
  }
}
```

### **4️⃣ Cliente Armazena o Token**

O **cliente** (app mobile, frontend web, etc.) deve armazenar o token:

#### **🌐 Web (JavaScript/Frontend):**
```javascript
// localStorage (simples, mas menos seguro)
localStorage.setItem('token', response.data.token);
localStorage.setItem('refresh_token', response.data.refresh_token);

// sessionStorage (mais seguro, expira ao fechar aba)
sessionStorage.setItem('token', response.data.token);

// Cookie HttpOnly (mais seguro, requer backend)
// Configurar no servidor, não acessível via JS
```

#### **📱 Mobile (React Native / Flutter):**
```javascript
// AsyncStorage (React Native)
await AsyncStorage.setItem('@token', response.data.token);
await AsyncStorage.setItem('@refresh_token', response.data.refresh_token);

// SharedPreferences (Flutter)
final prefs = await SharedPreferences.getInstance();
await prefs.setString('token', response.data['token']);
```

### **5️⃣ Cliente Usa o Token em Requisições**

```bash
GET /api/usuarios/retirar-pontos
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Content-Type: application/json
```

---

## 🎯 Por Que o Token NÃO é Salvo no Servidor?

### **1. JWT é Stateless (Sem Estado)**
- ✅ O token contém todas as informações necessárias **dentro dele mesmo**
- ✅ O servidor **valida o token** usando a chave secreta (`JWT_SECRET_KEY`)
- ✅ Não precisa consultar banco de dados para cada requisição

### **2. Estrutura do Token JWT**

Um JWT tem 3 partes (separadas por `.`):

```
eyJ0eXAiOiJKV1QiLCJhbGc.eyJ1c2VyX2lkIjoxLC.SflKxwRJSMeKKF2Q
│                     │                     │
│                     │                     └─ Assinatura (garante autenticidade)
│                     └─────────────────────── Payload (dados do usuário)
└───────────────────────────────────────────── Header (tipo e algoritmo)
```

**Exemplo de Payload Decodificado:**
```json
{
  "user_id": 6,
  "email": "usuario@exemplo.com",
  "nome": "João Silva",
  "is_admin": true,
  "is_cliente": false,
  "iat": 1732589458,  // Issued At (quando foi criado)
  "exp": 1732675858   // Expiration (quando expira)
}
```

### **3. Validação Automática**

O `JwtAuthFilter` valida o token **automaticamente**:

```php
// app/Filters/JwtAuthFilter.php

// Extrai o token do header Authorization
$token = $this->extractTokenFromHeader($authHeader);

// Decodifica e valida usando a chave secreta
$payload = Jwt::decode($token);

// Se válido, armazena os dados no request
$request->usuarioAutenticado = $payload;
```

---

## 🔄 Fluxo Completo de Autenticação

```
┌─────────────┐                    ┌─────────────┐
│   Cliente   │                    │   Servidor  │
│ (App/Web)   │                    │ (API)       │
└─────────────┘                    └─────────────┘
      │                                    │
      │  1. POST /api/auth/login           │
      │    { email, password }             │
      ├───────────────────────────────────>│
      │                                    │
      │                            2. Valida credenciais
      │                            3. Gera JWT (não salva)
      │                                    │
      │  4. Retorna { token, user }        │
      │<───────────────────────────────────┤
      │                                    │
5. Armazena token                          │
   (localStorage/AsyncStorage)             │
      │                                    │
      │  6. GET /api/usuarios/saldo/6      │
      │     Authorization: Bearer token    │
      ├───────────────────────────────────>│
      │                                    │
      │                            7. Valida token (JwtAuthFilter)
      │                            8. Executa ação
      │                                    │
      │  9. Retorna resposta               │
      │<───────────────────────────────────┤
      │                                    │
```

---

## 🗄️ O Que É Salvo no Banco de Dados?

### ✅ **É Salvo:**
- Dados do usuário (`usuarios`)
- Senha **hasheada** (nunca em texto plano)
- Permissões e grupos
- Logs de segurança (tentativas de login, IPs)

### ❌ **NÃO É Salvo:**
- Token JWT de acesso
- Refresh token JWT
- Sessões (sistema stateless)

---

## 📊 Exemplo de Log de Segurança

**Tabela:** `security_logs`

```sql
SELECT * FROM security_logs WHERE user_id = 6 ORDER BY created_at DESC LIMIT 5;
```

| id  | event_type    | identifier           | ip_address    | user_id | created_at          |
|-----|---------------|----------------------|---------------|---------|---------------------|
| 123 | login_success | usuario@exemplo.com  | 192.168.1.10  | 6       | 2025-11-26 01:50:00 |
| 122 | login_attempt | usuario@exemplo.com  | 192.168.1.10  | NULL    | 2025-11-26 01:49:55 |

**Note:** O token em si NÃO aparece aqui, apenas eventos de login/logout.

---

## 🔒 Segurança: Onde Armazenar o Token no Cliente?

### **Opções Comuns:**

| Método | Segurança | Persistência | Uso Recomendado |
|--------|-----------|--------------|-----------------|
| **localStorage** | ⚠️ Média | ✅ Sim | Apps web simples |
| **sessionStorage** | ✅ Boa | ❌ Não (expira) | Apps web temporários |
| **Cookie HttpOnly** | ✅✅ Melhor | ✅ Sim | Apps web (requer backend) |
| **AsyncStorage** | ✅ Boa | ✅ Sim | Apps mobile (React Native) |
| **SecureStore** | ✅✅ Melhor | ✅ Sim | Apps mobile (Expo) |
| **Keychain/Keystore** | ✅✅✅ Máxima | ✅ Sim | Apps mobile nativos |

### **⚠️ Vulnerabilidades a Evitar:**

❌ **Nunca armazenar em:**
- Parâmetros de URL (`?token=...`)
- Variáveis globais JavaScript
- LocalStorage sem criptografia (para dados sensíveis)
- Cookies sem flags `HttpOnly` e `Secure`

---

## 🔄 Refresh Token: Para Que Serve?

### **Token de Acesso** (24 horas):
- ✅ Usado para autenticar requisições
- ✅ Curta duração (mais seguro)
- ✅ Se roubado, expira rápido

### **Refresh Token** (30 dias):
- ✅ Usado para gerar **novo token de acesso** sem login
- ✅ Longa duração (conveniência)
- ✅ Endpoint: `POST /api/auth/refresh`

**Exemplo de uso:**
```bash
POST /api/auth/refresh
Content-Type: application/json

{
  "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

**Resposta:**
```json
{
  "success": true,
  "data": {
    "token": "novo_token_aqui...",        // ← Token novo (24h)
    "refresh_token": "novo_refresh...",   // ← Refresh novo (30d)
    "expires_in": 86400
  }
}
```

---

## 🧪 Como Verificar o Conteúdo do Token?

### **1. Decodificar no Site (NÃO envie tokens reais!):**
👉 https://jwt.io/

Cole seu token e veja o payload decodificado.

### **2. Decodificar via PHP:**
```php
use App\Libraries\Jwt;

$token = "eyJ0eXAiOiJKV1QiLCJhbGc...";
$payload = Jwt::decode($token);

print_r($payload);
// Array (
//     [user_id] => 6
//     [email] => usuario@exemplo.com
//     [nome] => João Silva
//     [exp] => 1732675858
// )
```

### **3. Decodificar via JavaScript (Frontend):**
```javascript
function parseJwt(token) {
    const base64Url = token.split('.')[1];
    const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
    const jsonPayload = decodeURIComponent(
        atob(base64).split('').map(c => 
            '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)
        ).join('')
    );
    return JSON.parse(jsonPayload);
}

const token = localStorage.getItem('token');
const payload = parseJwt(token);
console.log(payload.user_id); // 6
```

---

## 📝 Resumo Final

| Pergunta | Resposta |
|----------|----------|
| **Onde o token é salvo?** | ❌ **Não é salvo no servidor** |
| **Quem armazena o token?** | ✅ **O cliente** (app, navegador) |
| **Como o servidor valida?** | ✅ Usando a **assinatura JWT** com `JWT_SECRET_KEY` |
| **Por quanto tempo é válido?** | ✅ Token: **24 horas** / Refresh: **30 dias** |
| **Posso invalidar um token?** | ⚠️ **Não diretamente** (JWT é stateless). Alternativa: blacklist ou trocar `JWT_SECRET_KEY` |

---

## 🎯 Arquivos Importantes

| Arquivo | Função |
|---------|--------|
| `app/Controllers/Api/Auth.php` | Gera e retorna o token JWT |
| `app/Libraries/Jwt.php` | Encode/decode do JWT |
| `app/Filters/JwtAuthFilter.php` | Valida token em rotas protegidas |
| `app/Config/Routes.php` | Define rotas protegidas com `['filter' => 'jwtAuth']` |
| `.env` | Contém `JWT_SECRET_KEY` |

---

## ✅ Checklist de Segurança

- [x] Token **não é salvo** no banco de dados
- [x] Token tem **tempo de expiração** (24h)
- [x] Refresh token permite **renovação sem login** (30d)
- [x] Validação automática via **JwtAuthFilter**
- [x] Logs de segurança para **auditoria**
- [x] Rate limiting para **prevenir força bruta**
- [x] Chave secreta em **variável de ambiente** (`.env`)

---

## 🚀 Próximos Passos

1. ✅ **Cliente armazena o token** após login
2. ✅ **Cliente envia token** no header `Authorization: Bearer ...`
3. ✅ **Servidor valida automaticamente** via filter
4. ✅ **Cliente renova token** quando expirar (usando refresh token)
5. ✅ **Cliente deleta token** ao fazer logout

---

🎉 **Token JWT é stateless: o servidor não precisa "lembrar" dele!**

