# 🔍 Debug da API do Dashboard de Vendas

## ❌ Erro: "Unexpected end of JSON input"

Este erro indica que a API não está retornando JSON válido ou está retornando vazio.

## 🎯 Passos para Diagnosticar

### 1. Verificar os Logs do Sistema

Abra o arquivo de log em tempo real:

```bash
# Linux/Mac
tail -f writable/logs/log-$(date +%Y-%m-%d).log

# Windows PowerShell
Get-Content writable/logs/log-$(Get-Date -Format yyyy-MM-dd).log -Wait -Tail 50
```

Procure por mensagens como:
```
INFO - getDadosComparativos: Início da requisição
ERROR - getDadosComparativos: Erro - [mensagem do erro]
```

---

### 2. Teste a API Diretamente

Acesse a API diretamente no navegador (substitua os IDs):

```
https://seu-dominio.com/admin-dashboard-vendas/dados-comparativos?evento1_id=17&evento2_id=18
```

**Resultado esperado:** JSON com estrutura:
```json
{
  "success": true,
  "data": {
    "visao_geral": [...],
    "evolucao_diaria": [...],
    "comparacao_periodos": [...],
    "resumo_executivo": {...}
  }
}
```

**Se retornar erro 403 (Forbidden):**
- Você não é admin
- Siga os passos em `DASHBOARD_VENDAS_TROUBLESHOOT.md`

**Se retornar erro 500 (Internal Server Error):**
- Há um erro no SQL ou no código
- Verifique os logs

**Se retornar HTML em vez de JSON:**
- Pode estar caindo em outra rota
- Verifique `app/Config/Routes.php`

---

### 3. Verificar no Console do Navegador (F12)

Com o console aberto, tente comparar os eventos e procure por:

**Logs de Debug:**
```javascript
Response Status: 200
Response OK: true
Response Text: {"success":true,...}
```

**Se aparecer:**
- `Response Status: 403` → Não é admin
- `Response Status: 500` → Erro no servidor
- `Response Text: <html>` → Não está retornando JSON

---

### 4. Testar SQL Diretamente

Execute manualmente as queries no banco de dados:

```sql
-- 1. Verificar se os eventos existem
SELECT * FROM eventos WHERE id IN (17, 18);

-- 2. Verificar se há pedidos para esses eventos
SELECT COUNT(*) as total_pedidos, evento_id 
FROM pedidos 
WHERE evento_id IN (17, 18)
GROUP BY evento_id;

-- 3. Testar query de visão geral
SELECT 
    e.id AS evento_id,
    e.nome AS evento_nome,
    DATE_FORMAT(e.data_inicio, '%d/%m/%Y') AS data_evento,
    COUNT(DISTINCT p.id) AS total_pedidos,
    COUNT(i.id) AS total_ingressos,
    SUM(CASE WHEN p.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH') THEN p.total ELSE 0 END) AS receita_total
FROM eventos e
LEFT JOIN pedidos p ON e.id = p.evento_id 
    AND p.status IN ('CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH')
LEFT JOIN ingressos i ON p.id = i.pedido_id 
    AND i.ticket_id <> 608
WHERE e.id IN (17, 18)
GROUP BY e.id, e.nome, e.data_inicio
ORDER BY e.id;
```

**Se retornar erro SQL:**
- Corrija o campo ou tabela que está errado
- Verifique se as tabelas `eventos`, `pedidos`, `ingressos` existem
- Verifique se os campos estão corretos (evento_id vs event_id)

---

## 🔧 Soluções Comuns

### Problema 1: Erro de Permissão (403)

**Sintoma:** 
```json
{"success": false, "message": "Acesso negado"}
```

**Solução:**
1. Acesse: `/admin-dashboard-vendas/debug-usuario`
2. Verifique se `is_admin` é `true`
3. Se não for, execute SQL:

```sql
INSERT INTO grupos_usuarios (grupo_id, usuario_id, created_at, updated_at)
VALUES (1, SEU_USUARIO_ID, NOW(), NOW());
```

4. Faça **LOGOUT** e **LOGIN** novamente

---

### Problema 2: Erro SQL (500)

**Sintoma:** 
```json
{"success": false, "message": "Erro ao buscar dados: Unknown column..."}
```

**Solução:**
Verifique o erro específico e corrija:

- `Unknown column 'p.event_id'` → Use `p.evento_id`
- `Unknown column 'p.status'` → Verifique se a coluna existe na tabela pedidos
- `Table doesn't exist` → Execute migrations

---

### Problema 3: MySQL 5.7 (CTEs não suportados)

**Sintoma:**
```
You have an error in your SQL syntax... near 'WITH vendas_diarias AS'
```

**Solução:**
Seu MySQL não suporta CTEs. Opções:

1. **Opção A:** Atualize para MySQL 8.0+
2. **Opção B:** Use versão sem CTEs (será mais lento)

Para opção B, modifique o Model para não usar `WITH`:

```php
// Em vez de usar CTEs, use subqueries aninhadas
// Ou crie tabelas temporárias
```

---

### Problema 4: Timeout

**Sintoma:**
Loading infinito, sem erro

**Solução:**
1. Aumente timeout no PHP:
```ini
; php.ini
max_execution_time = 300
```

2. Ou adicione índices nas tabelas:
```sql
ALTER TABLE pedidos ADD INDEX idx_evento_status (evento_id, status);
ALTER TABLE ingressos ADD INDEX idx_pedido_ticket (pedido_id, ticket_id);
```

---

### Problema 5: JSON vazio

**Sintoma:**
```
Response Text: 
```

**Solução:**
1. Verifique se há `echo` ou `print` antes do JSON
2. Verifique se há espaços em branco no início dos arquivos PHP
3. Adicione no início do controller:

```php
ob_clean(); // Limpa qualquer output anterior
```

---

## 🧪 Script de Teste Rápido

Crie um arquivo temporário para testar:

**`public/test-api.php`:**

```php
<?php
// REMOVER APÓS TESTE!

// Simular requisição
$_GET['evento1_id'] = 17;
$_GET['evento2_id'] = 18;

// Incluir CodeIgniter
require __DIR__ . '/../vendor/autoload.php';

$app = \Config\Services::codeigniter();
$app->initialize();

// Testar controller
$controller = new \App\Controllers\AdminDashboardVendas();
$response = $controller->getDadosComparativos();

// Exibir resultado
header('Content-Type: application/json');
echo $response->getBody();
```

Acesse: `https://seu-dominio.com/test-api.php`

---

## 📊 Verificar Estrutura do Banco

Execute para confirmar estrutura:

```sql
-- Verificar campos da tabela pedidos
SHOW COLUMNS FROM pedidos;

-- Verificar campos da tabela eventos
SHOW COLUMNS FROM eventos;

-- Verificar campos da tabela ingressos
SHOW COLUMNS FROM ingressos;

-- Verificar índices
SHOW INDEX FROM pedidos;
SHOW INDEX FROM ingressos;
```

---

## ✅ Checklist de Debug

- [ ] Logs do sistema estão sendo gerados
- [ ] API retorna JSON quando acessada diretamente
- [ ] Usuário está no grupo admin (grupo_id = 1)
- [ ] Eventos existem no banco de dados
- [ ] Há pedidos para os eventos selecionados
- [ ] Campos das tabelas estão corretos (evento_id, não event_id)
- [ ] Status dos pedidos são: CONFIRMED, RECEIVED, RECEIVED_IN_CASH
- [ ] MySQL versão 8.0+ (ou usando versão sem CTEs)
- [ ] Console do navegador mostra logs de debug

---

## 🔄 Após Corrigir

1. Limpe o cache:
```bash
php spark cache:clear
```

2. Recarregue a página com **Ctrl+Shift+R** (hard reload)

3. Tente novamente

---

## 📞 Informações para Suporte

Se precisar de ajuda, forneça:

1. **Logs do sistema** (últimas 50 linhas)
2. **Console do navegador** (screenshot)
3. **Resultado da API diretamente** (JSON retornado)
4. **Versão do MySQL**: `SELECT VERSION();`
5. **IDs dos eventos** sendo comparados

---

**Última atualização:** Novembro 2025  
**Arquivo atualizado:** `app/Controllers/AdminDashboardVendas.php` com logs detalhados

