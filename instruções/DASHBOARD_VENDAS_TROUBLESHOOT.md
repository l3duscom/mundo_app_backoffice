# 🔧 Solução de Problemas - Dashboard de Vendas

## ❌ Erro: "Acesso negado. Esta área é exclusiva para administradores."

### 🎯 Solução Rápida (3 passos)

#### 1. Verifique se você está no grupo admin

Acesse esta URL para ver seu status:
```
https://seu-dominio.com/admin-dashboard-vendas/debug-usuario
```

Esta página irá mostrar:
- ✅ Se você está logado
- ✅ Seus dados de usuário
- ✅ Se você está no grupo admin (grupo_id = 1)
- ✅ O que fazer para corrigir

---

#### 2. Se não estiver no grupo admin, execute este SQL:

```sql
-- MÉTODO 1: Por email
SET @seu_email = 'seu-email@exemplo.com';

INSERT INTO grupos_usuarios (grupo_id, usuario_id, created_at, updated_at)
SELECT 1, u.id, NOW(), NOW()
FROM usuarios u
WHERE u.email = @seu_email
AND NOT EXISTS (
    SELECT 1 FROM grupos_usuarios 
    WHERE grupo_id = 1 AND usuario_id = u.id
);
```

**OU**

```sql
-- MÉTODO 2: Por ID (se você souber seu ID)
SET @seu_usuario_id = 999; -- SUBSTITUA 999 PELO SEU ID

INSERT INTO grupos_usuarios (grupo_id, usuario_id, created_at, updated_at)
VALUES (1, @seu_usuario_id, NOW(), NOW());
```

---

#### 3. Faça LOGOUT e LOGIN novamente

**IMPORTANTE:** Após adicionar ao grupo admin, você PRECISA:
1. Fazer logout: `https://seu-dominio.com/logout`
2. Fazer login novamente
3. Tentar acessar o dashboard

A sessão só é atualizada após novo login!

---

## 🔍 Diagnóstico Completo

### Passo 1: Verificar se está logado

```sql
-- Verifique se há sessão ativa
SELECT * FROM usuarios WHERE email = 'seu-email@exemplo.com';
```

### Passo 2: Verificar grupos disponíveis

```sql
-- Veja todos os grupos
SELECT * FROM grupos ORDER BY id;

-- O grupo_id = 1 deve ser 'Administrador' ou similar
```

### Passo 3: Verificar seus grupos

```sql
-- Veja em quais grupos você está
SELECT 
    u.id AS usuario_id,
    u.nome,
    u.email,
    g.id AS grupo_id,
    g.nome AS grupo_nome
FROM usuarios u
INNER JOIN grupos_usuarios gu ON u.id = gu.usuario_id
INNER JOIN grupos g ON gu.grupo_id = g.id
WHERE u.email = 'seu-email@exemplo.com';
```

**Resultado esperado:**
- Deve aparecer uma linha com `grupo_id = 1`
- Se não aparecer, você NÃO é admin

### Passo 4: Adicionar ao grupo admin

```sql
-- Pegue seu ID primeiro
SELECT id FROM usuarios WHERE email = 'seu-email@exemplo.com';

-- Digamos que seu ID é 123
INSERT INTO grupos_usuarios (grupo_id, usuario_id, created_at, updated_at)
VALUES (1, 123, NOW(), NOW());
```

### Passo 5: Verificar novamente

```sql
-- Confirme que foi adicionado
SELECT * FROM grupos_usuarios WHERE usuario_id = 123 AND grupo_id = 1;
```

---

## 🚨 Problemas Comuns

### Problema 1: "Duplicate entry"
**Mensagem:** `Duplicate entry '1-123' for key 'PRIMARY'`
**Causa:** Você já está no grupo admin
**Solução:** Apenas faça logout e login novamente

---

### Problema 2: Tabela grupos_usuarios não existe
**Causa:** Migração não foi executada
**Solução:** Execute as migrations:
```bash
php spark migrate
```

---

### Problema 3: Continua dando erro após adicionar
**Causa:** Sessão não foi atualizada
**Solução:**
1. Limpe o cache do navegador (Ctrl+Shift+Del)
2. Faça logout: `https://seu-dominio.com/logout`
3. Feche o navegador
4. Abra novamente e faça login
5. Tente acessar o dashboard

---

### Problema 4: Página debug não funciona
**Causa:** Rota não está carregada
**Solução:**
```bash
# Limpe o cache de rotas
php spark cache:clear

# OU tente a URL alternativa
https://seu-dominio.com/admindashboardvendas/debugUsuario
```

---

## 📋 Checklist Completo

Siga esta ordem:

- [ ] 1. Acesse `/admin-dashboard-vendas/debug-usuario`
- [ ] 2. Verifique se seu `is_admin` é `true`
- [ ] 3. Se não for, verifique no banco se está no grupo_id = 1
- [ ] 4. Execute o SQL para adicionar ao grupo admin
- [ ] 5. Faça LOGOUT
- [ ] 6. Faça LOGIN novamente
- [ ] 7. Acesse o dashboard: `/admin-dashboard-vendas`
- [ ] 8. Deve funcionar! ✅

---

## 🔐 Script SQL Completo (Copiar e Colar)

```sql
-- ============================================================
-- SCRIPT COMPLETO PARA ADICIONAR ADMIN
-- ============================================================

-- 1. SUBSTITUA ESTE EMAIL PELO SEU
SET @seu_email = 'seu-email@exemplo.com';

-- 2. VERIFICAR SEU USUÁRIO
SELECT 
    id AS usuario_id,
    nome,
    email,
    ativo
FROM usuarios 
WHERE email = @seu_email;

-- 3. VERIFICAR SEUS GRUPOS ATUAIS
SELECT 
    u.nome AS usuario,
    g.nome AS grupo,
    g.id AS grupo_id
FROM usuarios u
LEFT JOIN grupos_usuarios gu ON u.id = gu.usuario_id
LEFT JOIN grupos g ON gu.grupo_id = g.id
WHERE u.email = @seu_email;

-- 4. ADICIONAR AO GRUPO ADMIN (APENAS SE NÃO ESTIVER)
INSERT INTO grupos_usuarios (grupo_id, usuario_id, created_at, updated_at)
SELECT 1, u.id, NOW(), NOW()
FROM usuarios u
WHERE u.email = @seu_email
AND NOT EXISTS (
    SELECT 1 FROM grupos_usuarios 
    WHERE grupo_id = 1 AND usuario_id = u.id
);

-- 5. CONFIRMAR QUE FOI ADICIONADO
SELECT 
    u.nome AS usuario,
    g.nome AS grupo,
    'ADMIN' AS status
FROM usuarios u
INNER JOIN grupos_usuarios gu ON u.id = gu.usuario_id
INNER JOIN grupos g ON gu.grupo_id = g.id
WHERE u.email = @seu_email
AND gu.grupo_id = 1;

-- ============================================================
-- SE RETORNAR 1 LINHA, VOCÊ É ADMIN! 
-- AGORA FAÇA LOGOUT E LOGIN NOVAMENTE!
-- ============================================================
```

---

## 🎯 Teste Final

Após seguir todos os passos:

1. Acesse: `https://seu-dominio.com/logout`
2. Faça login novamente
3. Acesse: `https://seu-dominio.com/admin-dashboard-vendas`
4. Deve carregar a página com os dropdowns de eventos! ✅

---

## 📞 Ainda não funcionou?

### Verifique os logs

```bash
# No terminal
tail -f writable/logs/log-$(date +%Y-%m-%d).log
```

Procure por linhas como:
```
INFO - Dashboard de Vendas: Usuário ID 123 - is_admin: false
WARNING - Dashboard de Vendas: Acesso negado para usuário ID 123
```

Se aparecer `is_admin: false`, o problema é no banco de dados (grupo não está correto).

### Habilite o modo debug

No arquivo `.env`:
```
CI_ENVIRONMENT = development
```

Isso mostrará erros mais detalhados.

---

## ✅ Sucesso!

Quando funcionar, você verá:
- 🎨 Página bonita com gradiente roxo
- 📊 Dois dropdowns para selecionar eventos
- 🔵 Botão "Comparar"

---

## 🗑️ Após Resolver (IMPORTANTE)

**Remova o método de debug em produção:**

1. Remova a rota em `app/Config/Routes.php`:
```php
// REMOVA ESTA LINHA:
$routes->get('debug-usuario', 'AdminDashboardVendas::debugUsuario');
```

2. Remova o método em `app/Controllers/AdminDashboardVendas.php`:
```php
// REMOVA TODO O MÉTODO debugUsuario()
```

Ou simplesmente comente as linhas para uso futuro.

---

**Última atualização:** Novembro 2025

