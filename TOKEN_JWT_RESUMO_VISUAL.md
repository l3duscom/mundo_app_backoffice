# 🔐 Token JWT - Resumo Visual

## 🎯 Onde o Token Fica Salvo?

```
┌─────────────────────────────────────────────────────────────────┐
│                    SERVIDOR (API)                               │
│                                                                 │
│  ❌ Token JWT NÃO é salvo aqui                                 │
│  ✅ Apenas VALIDA o token quando recebe                        │
│                                                                 │
│  Salvo no servidor:                                            │
│  • Usuário (id, email, senha hasheada)                         │
│  • Permissões e grupos                                         │
│  • Logs de segurança                                           │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
                              ↓
                    Gera token no login
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                    RESPOSTA DO LOGIN                            │
│                                                                 │
│  {                                                              │
│    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",      ← Validade: 24h │
│    "refresh_token": "eyJ0eXAiOiJKV1Q...",      ← Validade: 30d │
│    "user": { ... }                                              │
│  }                                                              │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
                              ↓
              Cliente armazena o token
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                    CLIENTE (App/Web)                            │
│                                                                 │
│  ✅ Token JWT É SALVO AQUI                                     │
│                                                                 │
│  Web:                                                           │
│  • localStorage.setItem('token', token)                        │
│  • sessionStorage.setItem('token', token)                      │
│  • Cookie HttpOnly (mais seguro)                               │
│                                                                 │
│  Mobile:                                                        │
│  • AsyncStorage (React Native)                                 │
│  • SecureStore (Expo)                                          │
│  • SharedPreferences (Flutter/Android)                         │
│  • Keychain (iOS)                                              │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 📱 Fluxo Completo

```
1️⃣ LOGIN
┌─────────┐                          ┌─────────┐
│ Cliente │  POST /api/auth/login    │ Servidor│
│         │  { email, password }     │         │
│         ├─────────────────────────>│         │
│         │                          │ Valida  │
│         │                          │ Gera JWT│
│         │  { token, user }         │         │
│         │<─────────────────────────┤         │
└─────────┘                          └─────────┘
    │
    └─> Armazena token no localStorage/AsyncStorage
    
    
2️⃣ USO DO TOKEN
┌─────────┐                          ┌─────────┐
│ Cliente │  GET /api/usuarios/saldo │ Servidor│
│         │  Authorization: Bearer   │         │
│         │  eyJ0eXAiOiJKV1Q...      │         │
│         ├─────────────────────────>│         │
│         │                          │ Valida  │
│         │                          │ JWT     │
│         │  { saldo: 1000 }         │         │
│         │<─────────────────────────┤         │
└─────────┘                          └─────────┘
    │
    └─> Token continua salvo no cliente
    
    
3️⃣ RENOVAR TOKEN (quando expirar)
┌─────────┐                          ┌─────────┐
│ Cliente │  POST /api/auth/refresh  │ Servidor│
│         │  { refresh_token }       │         │
│         ├─────────────────────────>│         │
│         │                          │ Valida  │
│         │                          │ Gera    │
│         │                          │ Novo JWT│
│         │  { token, refresh }      │         │
│         │<─────────────────────────┤         │
└─────────┘                          └─────────┘
    │
    └─> Atualiza token no localStorage/AsyncStorage
```

---

## 🔍 Anatomia do Token JWT

```
eyJ0eXAiOiJKV1QiLCJhbGc.eyJ1c2VyX2lkIjoxLC.SflKxwRJSMeKKF2Q
│─────────────────────│─────────────────│───────────────────│
│     HEADER          │    PAYLOAD      │    SIGNATURE      │
│                     │                 │                   │
│  {                  │  {              │  HMAC-SHA256(     │
│   "typ": "JWT",     │   "user_id": 6, │    header +       │
│   "alg": "HS256"    │   "email": "...",│   payload,       │
│  }                  │   "exp": 123456 │    JWT_SECRET_KEY │
│                     │  }              │  )                │
└─────────────────────┴─────────────────┴───────────────────┘
```

---

## ✅ Checklist: O Que Fazer com o Token?

### **Após Login:**
```javascript
// ✅ FAZER: Armazenar no cliente
localStorage.setItem('token', response.data.token);
localStorage.setItem('refresh_token', response.data.refresh_token);

// ❌ NÃO FAZER: Enviar para armazenar no servidor
// (servidor não precisa/não deve armazenar)
```

### **Em Cada Requisição:**
```javascript
// ✅ FAZER: Enviar no header Authorization
fetch('/api/usuarios/saldo/6', {
  headers: {
    'Authorization': `Bearer ${localStorage.getItem('token')}`,
    'Content-Type': 'application/json'
  }
});

// ❌ NÃO FAZER: Enviar na URL
// fetch('/api/usuarios/saldo?token=...')  // INSEGURO!
```

### **Ao Fazer Logout:**
```javascript
// ✅ FAZER: Remover do cliente
localStorage.removeItem('token');
localStorage.removeItem('refresh_token');

// ❌ NÃO FAZER: Tentar "invalidar" no servidor
// (JWT é stateless, não há como invalidar individualmente)
```

---

## 🆚 JWT vs Sessão Tradicional

| Aspecto | JWT (Stateless) | Sessão (Stateful) |
|---------|-----------------|-------------------|
| **Armazenamento no servidor** | ❌ Não | ✅ Sim (banco/Redis) |
| **Escalabilidade** | ✅✅ Ótima | ⚠️ Requer Redis/memcached |
| **Invalidação** | ❌ Difícil | ✅ Fácil |
| **Tamanho** | ⚠️ ~200-500 bytes | ✅ ~32 bytes (ID) |
| **Segurança** | ✅ Boa (assinado) | ✅ Boa |
| **Uso ideal** | APIs, microserviços | Apps monolíticos |

---

## 🔒 Onde NÃO Armazenar o Token

```
❌ Parâmetros de URL
   GET /api/usuarios?token=eyJ0eXAiOiJKV1Q...
   ↳ Fica no histórico do navegador
   ↳ Fica nos logs do servidor
   ↳ Pode vazar via Referer header

❌ Variáveis Globais JavaScript
   window.token = "eyJ0eXAiOiJKV1Q...";
   ↳ Acessível por qualquer script
   ↳ Vulnerável a XSS

❌ LocalStorage Sem Cuidados
   ↳ Acessível por scripts maliciosos (XSS)
   ↳ Compartilhado entre abas
   ↳ Não expira automaticamente

✅ MELHOR: Cookie HttpOnly + Secure + SameSite
   ↳ Não acessível via JavaScript
   ↳ Enviado automaticamente
   ↳ Proteção contra XSS e CSRF
```

---

## 🎓 Exemplo Prático (JavaScript)

### **1. Fazer Login e Salvar Token**
```javascript
async function login(email, password) {
  const response = await fetch('/api/auth/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  });
  
  const data = await response.json();
  
  if (data.success) {
    // ✅ Armazenar no cliente
    localStorage.setItem('token', data.data.token);
    localStorage.setItem('refresh_token', data.data.refresh_token);
    localStorage.setItem('user', JSON.stringify(data.data.user));
    
    console.log('Login bem-sucedido!');
    return data.data.user;
  }
  
  throw new Error(data.message);
}
```

### **2. Usar Token em Requisições**
```javascript
async function retirarPontos(usuarioId, pontos, motivo) {
  const token = localStorage.getItem('token');
  
  const response = await fetch('/api/usuarios/retirar-pontos', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,  // ← Token aqui
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      usuario_id: usuarioId,
      pontos: pontos,
      motivo: motivo
    })
  });
  
  return await response.json();
}
```

### **3. Renovar Token Expirado**
```javascript
async function refreshToken() {
  const refreshToken = localStorage.getItem('refresh_token');
  
  const response = await fetch('/api/auth/refresh', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ refresh_token: refreshToken })
  });
  
  const data = await response.json();
  
  if (data.success) {
    // ✅ Atualizar tokens
    localStorage.setItem('token', data.data.token);
    localStorage.setItem('refresh_token', data.data.refresh_token);
    
    return data.data.token;
  }
  
  // Token refresh expirou, fazer login novamente
  throw new Error('Sessão expirada. Faça login novamente.');
}
```

### **4. Interceptor Automático (Axios)**
```javascript
import axios from 'axios';

// Adicionar token automaticamente em todas as requisições
axios.interceptors.request.use(config => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Renovar token automaticamente se expirar
axios.interceptors.response.use(
  response => response,
  async error => {
    if (error.response?.status === 401) {
      try {
        const newToken = await refreshToken();
        error.config.headers.Authorization = `Bearer ${newToken}`;
        return axios(error.config); // Tentar novamente
      } catch {
        // Redirecionar para login
        window.location.href = '/login';
      }
    }
    return Promise.reject(error);
  }
);
```

---

## 📊 Verificar o Token no Banco de Dados

### **❓ Há alguma tabela com tokens?**

**Resposta:** ❌ Não, mas há logs de segurança.

### **✅ Tabela `security_logs`**
```sql
-- Logs de tentativas de login (não armazena token)
SELECT 
    event_type,
    identifier AS email,
    ip_address,
    details,
    created_at
FROM security_logs
WHERE user_id = 6
ORDER BY created_at DESC
LIMIT 10;
```

**Resultado Exemplo:**
| event_type | email | ip_address | created_at |
|------------|-------|------------|------------|
| login_success | user@email.com | 192.168.1.10 | 2025-11-26 02:00:00 |
| login_attempt | user@email.com | 192.168.1.10 | 2025-11-26 01:59:55 |

**Note:** O token JWT **não aparece** aqui. Apenas eventos de login.

---

## 🎯 Conclusão

```
┌───────────────────────────────────────────────────────┐
│                                                       │
│  🔑 TOKEN JWT É ARMAZENADO NO CLIENTE                │
│                                                       │
│  ✅ Cliente: localStorage/AsyncStorage/SecureStore   │
│  ❌ Servidor: NÃO armazena (apenas valida)           │
│                                                       │
│  Por quê? JWT é STATELESS (sem estado)               │
│  • Token contém todas as informações                 │
│  • Servidor valida pela assinatura                   │
│  • Não precisa consultar banco a cada requisição     │
│                                                       │
└───────────────────────────────────────────────────────┘
```

---

## 📚 Documentação Completa

- 📄 `ONDE_FICA_SALVO_TOKEN_JWT.md` - Explicação detalhada
- 📄 `API_AUTH_DOCUMENTATION.md` - Documentação da API
- 📄 `README_API_AUTH.md` - Quick start
- 📄 `API_AUTH_EXAMPLES.md` - Exemplos práticos

---

🎉 **Token JWT = Cliente armazena, Servidor valida!**

