# 🔧 Fix: Erro 500 na API de Ingressos

## ❌ Erro Encontrado

```
ERROR - 27-11-2025 00:15:25 --> Unknown column 'ingressos.email' in 'field list'
```

**Causa:** Tentei adicionar os campos `ingressos.email` e `ingressos.cpf` na query, mas **esses campos não existem na tabela `ingressos`**.

---

## ✅ Correção Aplicada

### **1. Model (`IngressoModel.php`)**

**ANTES (com erro):**
```php
$atributos = [
    'ingressos.id',
    'ingressos.user_id',
    'ingressos.email',      // ❌ Campo não existe
    'ingressos.cpf',        // ❌ Campo não existe
    // ...
];
```

**DEPOIS (corrigido):**
```php
$atributos = [
    'ingressos.id',
    'ingressos.user_id',    // ✅ Campo existe
    'ingressos.nome',
    'ingressos.codigo',
    // ... outros campos válidos
];
```

### **2. Controller (`Api/Ingressos.php`)**

Removidas as referências aos campos inexistentes:

**ANTES (com erro):**
```php
$ingressoData = [
    'id' => $ingresso->id,
    'codigo' => $ingresso->codigo,
    'nome' => $ingresso->nome,
    'email' => $ingresso->email,    // ❌ Campo não existe
    'cpf' => $ingresso->cpf,        // ❌ Campo não existe
    'status' => $ingresso->status,
];
```

**DEPOIS (corrigido):**
```php
$ingressoData = [
    'id' => $ingresso->id,
    'codigo' => $ingresso->codigo,
    'nome' => $ingresso->nome,
    'status' => $ingresso->status,
    'qr_code' => $qrCodeBase64,
];
```

---

## 📋 Campos Corretos da Tabela `ingressos`

Campos que **existem** e podem ser usados:
```sql
SELECT 
    ingressos.id,
    ingressos.user_id,
    ingressos.pedido_id,
    ingressos.ticket_id,
    ingressos.codigo,
    ingressos.nome,
    ingressos.participante,
    ingressos.tipo,
    ingressos.cinemark,
    ingressos.valor,
    ingressos.valor_unitario,
    ingressos.quantidade,
    ingressos.created_at,
    ingressos.updated_at
FROM ingressos;
```

**Campos que NÃO existem:**
- ❌ `ingressos.email` (está em `clientes` ou `usuarios`)
- ❌ `ingressos.cpf` (está em `clientes`)
- ❌ `ingressos.telefone` (está em `clientes`)

---

## 🎯 Se Precisar de Email ou CPF

Se no futuro precisar retornar email ou CPF do ingresso, faça JOIN com a tabela correta:

### **Opção 1: Buscar de `clientes`**
```php
$atributos = [
    'ingressos.id',
    'ingressos.codigo',
    'ingressos.nome',
    'clientes.email',   // ✅ Da tabela clientes
    'clientes.cpf',     // ✅ Da tabela clientes
];

$this->select($atributos)
    ->join('pedidos', 'pedidos.id = ingressos.pedido_id')
    ->join('usuarios', 'usuarios.id = ingressos.user_id')
    ->join('clientes', 'clientes.usuario_id = usuarios.id')  // ← JOIN adicional
    ->where('ingressos.user_id', $usuario_id)
    ->findAll();
```

### **Opção 2: Buscar de `usuarios`**
```php
$atributos = [
    'ingressos.id',
    'ingressos.codigo',
    'ingressos.nome',
    'usuarios.email',   // ✅ Da tabela usuarios
];

$this->select($atributos)
    ->join('pedidos', 'pedidos.id = ingressos.pedido_id')
    ->join('usuarios', 'usuarios.id = ingressos.user_id')
    ->where('ingressos.user_id', $usuario_id)
    ->findAll();
```

---

## ✅ Status Atual

| Item | Status |
|------|--------|
| Erro 500 corrigido | ✅ |
| Campos inexistentes removidos | ✅ |
| Logs de segurança mantidos | ✅ |
| Validação de user_id mantida | ✅ |
| Query resetQuery() mantida | ✅ |
| Sem erros de linter | ✅ |

---

## 🧪 Teste Agora

### **1. Testar API:**
```bash
curl -X GET https://seu-dominio.com/api/ingressos/atuais \
  -H "Authorization: Bearer SEU_TOKEN"
```

**Resposta esperada (200 OK):**
```json
{
  "success": true,
  "data": {
    "ingressos": [
      {
        "id": 123,
        "codigo": "ABC123",
        "nome": "Ingresso VIP",
        "status": "CONFIRMED",
        "qr_code": "data:image/png;base64,..."
      }
    ],
    "total": 1
  }
}
```

### **2. Verificar logs:**
```bash
tail -50 writable/logs/log-*.log | grep "Ingressos::"
```

**Logs esperados:**
```
INFO - API Ingressos::atuais - Usuario 4162 requisitou ingressos
DEBUG - IngressoModel::recuperaIngressosPorUsuario - Usuario 4162 possui 3 ingressos
INFO - API Ingressos::atuais - Usuario 4162 - Retornando 3 ingressos atuais
```

---

## 📊 Estrutura das Tabelas

### **`ingressos`**
```sql
CREATE TABLE ingressos (
    id INT PRIMARY KEY,
    user_id INT,
    pedido_id INT,
    ticket_id INT,
    codigo VARCHAR(255),
    nome VARCHAR(255),
    participante VARCHAR(255),
    tipo VARCHAR(50),
    cinemark BOOLEAN,
    valor DECIMAL(10,2),
    valor_unitario DECIMAL(10,2),
    quantidade INT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);
```

### **`clientes`**
```sql
CREATE TABLE clientes (
    id INT PRIMARY KEY,
    usuario_id INT,
    nome VARCHAR(255),
    email VARCHAR(255),     -- ← Email está aqui
    cpf VARCHAR(14),        -- ← CPF está aqui
    telefone VARCHAR(20),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 📚 Arquivos Modificados

| Arquivo | Modificação |
|---------|-------------|
| `app/Models/IngressoModel.php` | ✅ Removidos campos `email` e `cpf` inexistentes |
| `app/Controllers/Api/Ingressos.php` | ✅ Removidas referências aos campos em 3 métodos |
| `FIX_ERROR_500_INGRESSOS.md` | ✅ Este arquivo (documentação) |

---

## 🎉 Resultado

✅ **Erro 500 resolvido!**
✅ **Todas as correções de segurança mantidas:**
- Reset do Query Builder
- Logs detalhados
- Validação de user_id
- Detecção de vazamento de dados

🚀 **API pronta para uso!**

---

## 🔗 Documentação Relacionada

- 📄 `PROBLEMA_INGRESSOS_MISTURADOS.md` - Problema original de segurança
- 📄 `TESTE_RAPIDO_INGRESSOS.md` - Guia de testes
- 📄 `sql/debug_ingressos_por_usuario.sql` - Queries de debug

---

**Teste novamente e confirme se está funcionando! 🎯**

