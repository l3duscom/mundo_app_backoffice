# 🔐 Autenticação JWT - Guia Completo

## 🎯 Problema Resolvido

**Erro:** "Usuário não autenticado" mesmo enviando token

**Causa:** O método `usuarioLogado()` usa sessão web, mas APIs JWT armazenam dados em `$request->usuarioAutenticado`

**Solução:** Controller agora usa corretamente o JWT payload

---

## 🔑 Como Obter o Token JWT

### 1️⃣ **Fazer Login**

**Endpoint de Login:** (você precisa ter uma rota de login na API)

```bash
POST /api/auth/login
Content-Type: application/json

{
  "email": "usuario@example.com",
  "senha": "sua_senha"
}
```

**Resposta Esperada:**
```json
{
  "success": true,
  "message": "Login realizado com sucesso",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 123,
    "nome": "João Silva",
    "email": "usuario@example.com"
  }
}
```

---

## 🚀 Como Usar o Token

### **Header Obrigatório:**
```
Authorization: Bearer SEU_TOKEN_JWT_AQUI
```

### **Exemplo cURL:**
```bash
curl -X POST https://mundodream.com.br/api/usuarios/retirar-pontos \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..." \
  -H "Content-Type: application/json" \
  -d '{
    "usuario_id": 123,
    "pontos": 100,
    "motivo": "Resgate de prêmio"
  }'
```

### **Exemplo JavaScript:**
```javascript
const token = 'eyJ0eXAiOiJKV1QiLCJhbGc...'; // Token obtido no login

const response = await fetch('https://mundodream.com.br/api/usuarios/retirar-pontos', {
    method: 'POST',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        usuario_id: 123,
        pontos: 100,
        motivo: 'Resgate de prêmio'
    })
});

const result = await response.json();
console.log(result);
```

### **Exemplo jQuery:**
```javascript
$.ajax({
    url: 'https://mundodream.com.br/api/usuarios/retirar-pontos',
    type: 'POST',
    headers: {
        'Authorization': 'Bearer ' + token
    },
    contentType: 'application/json',
    data: JSON.stringify({
        usuario_id: 123,
        pontos: 100,
        motivo: 'Resgate de prêmio'
    }),
    success: function(response) {
        console.log('Sucesso:', response);
    },
    error: function(xhr, status, error) {
        console.error('Erro:', xhr.responseJSON);
    }
});
```

### **Exemplo Postman:**

1. **Headers Tab:**
   - Key: `Authorization`
   - Value: `Bearer eyJ0eXAiOiJKV1QiLCJhbGc...`

2. **Body Tab:**
   - Selecione: `raw` + `JSON`
   - Cole:
   ```json
   {
     "usuario_id": 123,
     "pontos": 100,
     "motivo": "Resgate de prêmio"
   }
   ```

---

## 🔍 Estrutura do Token JWT

### **Formato:**
```
eyJ0eXAiOiJKV1QiLCJhbGc.eyJ1c2VyX2lkIjoxMjM.SflKxwRJSMeKKF2QT4fwpM
│                         │                      │
│                         │                      └─ Signature
│                         └─ Payload (dados do usuário)
└─ Header (tipo e algoritmo)
```

### **Payload Decodificado:**
```json
{
  "user_id": 123,
  "email": "usuario@example.com",
  "is_admin": false,
  "permissoes": ["ver-conquistas", "resgatar-pontos"],
  "iat": 1701014400,
  "exp": 1701100800
}
```

---

## ⚠️ Erros Comuns

### **Erro 1: Token não fornecido**
```json
{
  "success": false,
  "message": "Token de autenticação não fornecido",
  "error": "Use o header: Authorization: Bearer YOUR_JWT_TOKEN"
}
```

**Solução:** Adicione o header `Authorization: Bearer {token}`

---

### **Erro 2: Token inválido ou expirado**
```json
{
  "success": false,
  "message": "Token inválido ou expirado",
  "error": "Faça login novamente para obter um novo token"
}
```

**Solução:** Faça login novamente para obter um novo token

---

### **Erro 3: Formato incorreto**
```json
{
  "success": false,
  "message": "Token inválido",
  "error": "Formato de token inválido"
}
```

**Solução:** Verifique se está usando `Bearer` antes do token

---

### **Erro 4: Usuário inativo**
```json
{
  "success": false,
  "message": "Usuário não encontrado ou inativo",
  "error": "Seu acesso foi revogado ou sua conta está inativa"
}
```

**Solução:** Entre em contato com o administrador

---

## 🧪 Testar no Navegador

### **Console do Navegador:**
```javascript
// 1. Fazer login (ajuste a URL)
const login = await fetch('https://mundodream.com.br/api/auth/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        email: 'seu@email.com',
        senha: 'sua_senha'
    })
}).then(r => r.json());

console.log('Token:', login.token);

// 2. Salvar token
const token = login.token;

// 3. Testar retirada de pontos
const resultado = await fetch('https://mundodream.com.br/api/usuarios/retirar-pontos', {
    method: 'POST',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        usuario_id: 123,
        pontos: 10,
        motivo: 'Teste via console'
    })
}).then(r => r.json());

console.log('Resultado:', resultado);
```

---

## 📦 Como o Filtro JWT Funciona

### **Fluxo:**
```
1. Request chega com header Authorization
   ↓
2. JwtAuthFilter extrai o token
   ↓
3. Token é decodificado e validado
   ↓
4. Verifica se usuário existe e está ativo
   ↓
5. Armazena dados em $request->usuarioAutenticado
   ↓
6. Controller acessa os dados
```

### **O que o filtro armazena:**
```php
$request->usuarioAutenticado = [
    'user_id' => 123,
    'email' => 'usuario@example.com',
    'is_admin' => false,
    'permissoes' => ['...'],
    'iat' => 1701014400,
    'exp' => 1701100800
];
```

### **Como o controller acessa:**
```php
$usuarioAutenticado = $this->request->usuarioAutenticado;
$userId = $usuarioAutenticado['user_id'];
$isAdmin = $usuarioAutenticado['is_admin'] ?? false;
```

---

## 🔐 Segurança

### **Boas Práticas:**

1. **NUNCA** exponha o token no código frontend
2. Armazene em `localStorage` ou `sessionStorage` (com cautela)
3. Use HTTPS em produção
4. Implemente refresh tokens para tokens de longa duração
5. Defina tempo de expiração adequado

### **Exemplo de Armazenamento:**
```javascript
// Após login bem-sucedido
localStorage.setItem('jwt_token', token);

// Ao fazer requisições
const token = localStorage.getItem('jwt_token');

// Ao fazer logout
localStorage.removeItem('jwt_token');
```

---

## 🛠️ Debugging

### **Ver Payload do Token:**

**Online:** https://jwt.io/

**JavaScript:**
```javascript
function decodeJWT(token) {
    const parts = token.split('.');
    const payload = JSON.parse(atob(parts[1]));
    console.log('Payload:', payload);
    return payload;
}

const payload = decodeJWT(token);
console.log('User ID:', payload.user_id);
console.log('Expira em:', new Date(payload.exp * 1000));
```

**PHP:**
```php
$parts = explode('.', $token);
$payload = json_decode(base64_decode($parts[1]), true);
var_dump($payload);
```

---

## 📊 Verificar Token no Backend

### **SQL para verificar usuário:**
```sql
-- Usando o user_id do token
SELECT 
    id,
    nome,
    email,
    ativo,
    is_admin,
    pontos
FROM usuarios 
WHERE id = 123; -- user_id do token
```

### **Logs do Servidor:**
```bash
# Ver requisições com JWT
tail -f writable/logs/*.log | grep "JWT\|Token\|autenticação"
```

---

## 📝 Exemplo Completo de Fluxo

### **1. Login:**
```javascript
const loginResponse = await fetch('/api/auth/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        email: 'usuario@example.com',
        senha: 'senha123'
    })
});

const loginData = await loginResponse.json();
const token = loginData.token;
localStorage.setItem('jwt_token', token);
```

### **2. Consultar Saldo:**
```javascript
const token = localStorage.getItem('jwt_token');

const saldoResponse = await fetch('/api/usuarios/saldo/123', {
    headers: {
        'Authorization': `Bearer ${token}`
    }
});

const saldoData = await saldoResponse.json();
console.log('Saldo:', saldoData.data.pontos);
```

### **3. Retirar Pontos:**
```javascript
const retirarResponse = await fetch('/api/usuarios/retirar-pontos', {
    method: 'POST',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        usuario_id: 123,
        pontos: 100,
        motivo: 'Resgate de camiseta'
    })
});

const retirarData = await retirarResponse.json();
if (retirarData.success) {
    console.log('Novo saldo:', retirarData.data.saldo_atual);
}
```

---

## ✅ Checklist de Integração

- [ ] Implementar rota de login que retorna JWT
- [ ] Armazenar token após login
- [ ] Adicionar header `Authorization: Bearer {token}` em todas as requisições API
- [ ] Tratar erro 401 (redirecionar para login)
- [ ] Implementar refresh token (opcional)
- [ ] Limpar token no logout
- [ ] Testar expiração do token

---

## 🚀 Status da Correção

- ✅ Controller agora usa `$request->usuarioAutenticado`
- ✅ Compatível com filtro JWT
- ✅ Funciona para qualquer usuário autenticado
- ✅ Mantém auditoria (armazena user_id no extrato)
- ✅ Logs completos

---

## 📞 Suporte

**Se ainda assim não funcionar:**

1. Verifique se o header está correto: `Authorization: Bearer {token}`
2. Decodifique o token em jwt.io para ver se é válido
3. Verifique logs do servidor
4. Teste com Postman primeiro
5. Confirme que o usuário está ativo no banco

**Arquivos relacionados:**
- `app/Filters/JwtAuthFilter.php` - Filtro JWT
- `app/Controllers/Api/Usuarios.php` - Controller de pontos
- `app/Config/Routes.php` - Rotas da API

