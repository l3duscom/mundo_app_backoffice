# 🔥 Debug do Dashboard em Produção

## ❌ Erro HTTP 500 em Produção

O erro só ocorre em produção porque o ambiente esconde os detalhes do erro.

## 🎯 Solução Rápida (3 URLs de Teste)

### 1️⃣ Teste se a API básica funciona

```
https://mundodream.com.br/admin-dashboard-vendas/test-api
```

**Deve retornar:**
```json
{
  "success": true,
  "message": "API funcionando corretamente!",
  "user": {
    "is_admin": true
  }
}
```

---

### 2️⃣ Teste cada query SQL separadamente

```
https://mundodream.com.br/admin-dashboard-vendas/test-queries?evento1_id=17&evento2_id=18
```

**Substitua 17 e 18 pelos IDs reais dos eventos que você quer comparar.**

**Resultado esperado:**
```json
{
  "success": true,
  "tests": {
    "visao_geral": {
      "status": "OK",
      "count": 2
    },
    "evolucao_diaria": {
      "status": "OK",
      "count": 30
    },
    "comparacao_periodos": {
      "status": "OK",
      "count": 6
    },
    "resumo_executivo": {
      "status": "OK"
    }
  }
}
```

**Se algum teste falhar:**
```json
{
  "tests": {
    "evolucao_diaria": {
      "status": "ERRO",
      "message": "SQLSTATE[42S22]: Column not found: 1054 Unknown column 'p.event_id'",
      "line": 68,
      "file": "VendasComparativasModel.php"
    }
  }
}
```

Isso mostrará **exatamente** qual query está falhando e por quê! 🎯

---

### 3️⃣ Depois que identificar o erro, teste a API completa

```
https://mundodream.com.br/admin-dashboard-vendas/dados-comparativos?evento1_id=17&evento2_id=18
```

---

## 🔍 Erros Comuns em Produção

### Erro 1: MySQL 5.7 (CTEs não suportados)

**Sintoma na URL test-queries:**
```json
{
  "tests": {
    "evolucao_diaria": {
      "status": "ERRO",
      "message": "You have an error in your SQL syntax... near 'WITH vendas_diarias'"
    }
  }
}
```

**Solução:**
Seu MySQL não suporta CTEs. Você precisa da versão para MySQL 5.7.

Execute no banco de dados:
```sql
SELECT VERSION();
```

Se for MySQL 5.7.x ou inferior, precisa modificar o Model para não usar `WITH`.

---

### Erro 2: Coluna não encontrada

**Sintoma:**
```json
{
  "message": "Unknown column 'p.event_id' in 'on clause'"
}
```

**Solução:**
Já corrigimos isso. Use `evento_id` em vez de `event_id`.

Se o erro persistir, execute:
```sql
SHOW COLUMNS FROM pedidos;
```

E verifique o nome real da coluna.

---

### Erro 3: Tabela não existe

**Sintoma:**
```json
{
  "message": "Table 'mundodream.eventos' doesn't exist"
}
```

**Solução:**
Verifique os nomes reais das tabelas:
```sql
SHOW TABLES;
```

---

### Erro 4: Status de pedido diferente

**Sintoma:**
Retorna dados mas todos zerados (0 ingressos, R$ 0,00).

**Solução:**
Verifique quais status são usados no seu sistema:
```sql
SELECT DISTINCT status FROM pedidos;
```

Se for diferente de `CONFIRMED`, `RECEIVED`, `RECEIVED_IN_CASH`, atualize em:
`app/Models/VendasComparativasModel.php`

Procure por:
```php
$status = ['CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH'];
```

E altere para os status corretos do seu sistema.

---

## 🛠️ Correções Aplicadas

### ✅ Output Buffer Limpo

Adicionado no início da API:
```php
if (ob_get_level() > 0) {
    ob_clean();
}
```

Isso remove qualquer output (echo, print, warnings) que possa estar quebrando o JSON.

### ✅ Erro Detalhado Sempre Mostrado

Mesmo em produção, agora o erro completo é retornado:
```json
{
  "success": false,
  "message": "Erro ao buscar dados: [erro exato]",
  "error_detail": {
    "message": "...",
    "file": "...",
    "line": 123
  }
}
```

---

## 📋 Checklist de Debug em Produção

Execute as URLs na ordem e anote os resultados:

- [ ] `/test-api` → **Resultado:** __________
- [ ] `/test-queries?evento1_id=17&evento2_id=18` → **Resultado:** __________
- [ ] `/dados-comparativos?evento1_id=17&evento2_id=18` → **Resultado:** __________

Se alguma falhar, copie o JSON de erro completo.

---

## 🎯 Próximos Passos

### Se `/test-queries` mostrou erro em uma query específica:

1. **Identifique qual teste falhou**
   - `visao_geral`
   - `evolucao_diaria`
   - `comparacao_periodos`
   - `resumo_executivo`

2. **Veja a mensagem de erro exata**

3. **Corrija o SQL correspondente em:**
   - `app/Models/VendasComparativasModel.php`
   - Método correspondente ao teste que falhou

4. **Execute o SQL manualmente no banco para testar:**
   ```sql
   -- Exemplo: testar visão geral
   SELECT 
       e.id AS evento_id,
       e.nome AS evento_nome
   FROM eventos e
   WHERE e.id IN (17, 18);
   ```

---

## 🚨 Se Nada Funcionar

### Última tentativa: Habilitar modo development temporariamente

**CUIDADO:** Isso mostrará todos os erros publicamente!

No arquivo `.env` ou `app/Config/Boot/production.php`:

```php
// TEMPORÁRIO - REMOVER APÓS DEBUG
ini_set('display_errors', '1');
error_reporting(E_ALL);
```

Depois **REVERTA IMEDIATAMENTE** após identificar o erro!

---

## 📞 Informações para Análise

Se precisar de ajuda, forneça:

1. **Resultado de `/test-api`**
2. **Resultado de `/test-queries?evento1_id=X&evento2_id=Y`**
3. **Console do navegador** (F12) ao tentar comparar
4. **Versão do MySQL**: Execute `SELECT VERSION();`
5. **Resultado de**: `SHOW COLUMNS FROM pedidos;`

---

## ✅ Após Resolver

### Remova os métodos de debug:

Em `app/Config/Routes.php`, **REMOVA**:
```php
$routes->get('test-api', 'AdminDashboardVendas::testApi');
$routes->get('test-queries', 'AdminDashboardVendas::testQueries');
$routes->get('debug-usuario', 'AdminDashboardVendas::debugUsuario');
```

Ou comente para uso futuro:
```php
// $routes->get('test-api', 'AdminDashboardVendas::testApi');
```

---

## 🎯 Exemplo Real de Debug

**Passo 1:** Acesse `/test-queries?evento1_id=17&evento2_id=18`

**Resultado:**
```json
{
  "tests": {
    "visao_geral": { "status": "OK" },
    "evolucao_diaria": {
      "status": "ERRO",
      "message": "Unknown column 'p.event_id'"
    }
  }
}
```

**Diagnóstico:** O erro está na query de "evolução diária"

**Solução:** Abra `app/Models/VendasComparativasModel.php` → método `getEvolucaoDiariaComparativa()` → troque `p.event_id` por `p.evento_id`

**Passo 2:** Teste novamente até todos ficarem "OK"

**Passo 3:** Acesse o dashboard normalmente

---

**Última atualização:** Novembro 2025  
**Arquivos modificados:** 
- `app/Controllers/AdminDashboardVendas.php` (output buffer + erro detalhado)
- `app/Config/Routes.php` (rotas de debug)

