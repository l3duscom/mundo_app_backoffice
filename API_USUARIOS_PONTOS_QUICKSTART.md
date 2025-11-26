# ⚡ Quick Start - API de Retirada de Pontos

## 🎯 Resumo Rápido

Nova API para **retirar pontos** de usuários com registro no extrato e validações completas.

## 📍 Endpoints Criados

### 1️⃣ **Retirar Pontos** (Admin)
```
POST /api/usuarios/retirar-pontos
```

### 2️⃣ **Consultar Saldo** (Autenticado)
```
GET /api/usuarios/saldo/{usuario_id}
```

---

## 🚀 Uso Rápido

### JavaScript (Fetch)
```javascript
// 1. Consultar saldo
const saldo = await fetch('/api/usuarios/saldo/123', {
    headers: { 'Authorization': 'Bearer ' + token }
}).then(r => r.json());

console.log('Saldo:', saldo.data.pontos);

// 2. Retirar pontos
const resultado = await fetch('/api/usuarios/retirar-pontos', {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer ' + token,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        usuario_id: 123,
        pontos: 100,
        motivo: 'Resgate de prêmio'
    })
}).then(r => r.json());

if (resultado.success) {
    alert('Novo saldo: ' + resultado.data.saldo_atual);
}
```

### cURL
```bash
# Retirar 100 pontos
curl -X POST https://mundodream.com.br/api/usuarios/retirar-pontos \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "usuario_id": 123,
    "pontos": 100,
    "motivo": "Resgate de camiseta"
  }'
```

---

## ✅ O Que Foi Implementado

### **Arquivo Criado:**
- ✅ `app/Controllers/Api/Usuarios.php`

### **Rotas Adicionadas** (Routes.php):
- ✅ `POST /api/usuarios/retirar-pontos`
- ✅ `GET /api/usuarios/saldo/{usuario_id}`

### **Validações:**
1. ✅ Token JWT válido
2. ✅ Usuário autenticado
3. ✅ Permissão de admin (apenas para retirar)
4. ✅ Dados obrigatórios presentes
5. ✅ Pontos > 0
6. ✅ Usuário existe
7. ✅ Saldo suficiente

### **Funcionalidades:**
- ✅ Retirada de pontos com transação DB
- ✅ Criação automática de extrato
- ✅ Cálculo de saldos (anterior e atual)
- ✅ Registro do admin responsável
- ✅ Logs detalhados
- ✅ Rollback automático em caso de erro
- ✅ Consulta de saldo

---

## 📊 Fluxo da Operação

```
┌─────────────────────┐
│ POST /retirar-pontos│
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Validar Autenticação│ → 401 se falhar
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Validar Admin       │ → 403 se não for admin
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Validar Dados       │ → 400 se dados inválidos
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Buscar Usuário      │ → 404 se não existir
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Verificar Saldo     │ → 400 se insuficiente
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ INICIAR TRANSAÇÃO   │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Calcular Novo Saldo │
│ (atual - retirada)  │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Atualizar Usuário   │
│ SET pontos = novo   │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Criar Extrato       │
│ - tipo: DEBITO      │
│ - saldos            │
│ - admin             │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ COMMIT TRANSAÇÃO    │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Log de Sucesso      │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Retornar 200 OK     │
└─────────────────────┘
```

---

## 📝 Body Exemplo

### Mínimo Necessário
```json
{
  "usuario_id": 123,
  "pontos": 100,
  "motivo": "Resgate de prêmio"
}
```

### Completo (com evento)
```json
{
  "usuario_id": 123,
  "pontos": 500,
  "motivo": "Resgate: Camiseta Oficial Dreamfest 2025",
  "event_id": 17
}
```

---

## 🔍 Consultar Saldo

### Request
```bash
GET /api/usuarios/saldo/123
```

### Response
```json
{
  "success": true,
  "data": {
    "usuario_id": 123,
    "nome": "João Silva",
    "email": "joao@example.com",
    "pontos": 2500
  }
}
```

---

## 📁 Arquivos

| Arquivo | Descrição |
|---------|-----------|
| `app/Controllers/Api/Usuarios.php` | Controller com 2 métodos |
| `app/Config/Routes.php` | Rotas da API |
| `API_USUARIOS_RETIRAR_PONTOS.md` | Documentação completa |
| `EXEMPLOS_API_RETIRAR_PONTOS.md` | Exemplos práticos |
| `sql/test_retirar_pontos.sql` | Scripts de teste |
| `API_USUARIOS_PONTOS_QUICKSTART.md` | Este arquivo |

---

## ✅ Pronto para Usar!

**URL Base:** `https://mundodream.com.br/api/usuarios`

**Rotas:**
- `POST /retirar-pontos` - Retirar pontos (admin)
- `GET /saldo/{id}` - Consultar saldo (autenticado)

**Status:** ✅ Implementado, testado e documentado

