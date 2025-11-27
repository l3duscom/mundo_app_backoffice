# 🧪 Teste Rápido - Problema de Ingressos Misturados

## ✅ Correções Aplicadas

### **1. Model (`IngressoModel.php`):**
- ✅ `resetQuery()` adicionado para limpar estado
- ✅ Filtro duplo de `user_id` (segurança extra)
- ✅ Logs detalhados de cada consulta
- ✅ Campos `email` e `cpf` adicionados

### **2. Controller (`Api/Ingressos.php`):**
- ✅ Validação crítica: verifica se todos os ingressos pertencem ao usuário
- ✅ Logs de cada requisição (user_id, IP, timestamp)
- ✅ Headers `no-cache` adicionados
- ✅ Alerta 🚨 se detectar vazamento de dados

---

## 🔥 TESTE AGORA

### **Passo 1: Reiniciar Servidor**
```bash
# Windows (PowerShell como admin)
Restart-Service php-cgi

# Ou reinicie manualmente o servidor web
```

### **Passo 2: Testar API com 2 Usuários**

**Usuário A:**
```bash
curl -X GET https://seu-dominio.com/api/ingressos/atuais \
  -H "Authorization: Bearer TOKEN_USUARIO_A" \
  | jq '.data.total'
```

**Usuário B:**
```bash
curl -X GET https://seu-dominio.com/api/ingressos/atuais \
  -H "Authorization: Bearer TOKEN_USUARIO_B" \
  | jq '.data.total'
```

**Repetir 5-10 vezes** e verificar se os números permanecem consistentes.

### **Passo 3: Verificar Logs**

```bash
# Ver últimos logs
tail -50 writable/logs/log-*.log

# Filtrar por ingressos
tail -100 writable/logs/log-*.log | grep "Ingressos::"

# Procurar por vazamentos
tail -1000 writable/logs/log-*.log | grep "VAZAMENTO"
```

**O que procurar:**
```
✅ BOM:
INFO - API Ingressos::atuais - Usuario 6 requisitou ingressos. IP: 192.168.1.10
DEBUG - IngressoModel::recuperaIngressosPorUsuario - Usuario 6 possui 3 ingressos
INFO - API Ingressos::atuais - Usuario 6 - Retornando 3 ingressos atuais

❌ RUIM (vazamento):
CRITICAL - 🚨 VAZAMENTO DE DADOS DETECTADO! Usuario 6 recebeu ingresso 123 que pertence ao usuario 7
```

### **Passo 4: Verificar Banco de Dados**

Execute o script SQL:
```bash
# Ver arquivo completo
sql/debug_ingressos_por_usuario.sql
```

Principais queries:
```sql
-- Definir usuários para testar
SET @usuario_a = 6;  -- Troque
SET @usuario_b = 7;  -- Troque

-- Ver ingressos de cada um
SELECT i.id, i.user_id, i.codigo 
FROM ingressos i
INNER JOIN pedidos p ON p.id = i.pedido_id
WHERE i.user_id = @usuario_a
  AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH');

-- Verificar inconsistências
SELECT * FROM ingressos i
INNER JOIN pedidos p ON p.id = i.pedido_id
WHERE i.user_id != p.user_id;
```

---

## 🎯 Checklist de Testes

- [ ] Servidor reiniciado
- [ ] API testada com Usuário A (5x) - resultado consistente?
- [ ] API testada com Usuário B (5x) - resultado consistente?
- [ ] Logs verificados - sem "VAZAMENTO"?
- [ ] SQL executado - sem inconsistências?
- [ ] Teste com 2 usuários **simultâneos** (diferentes abas/terminais)

---

## 📊 Resultados Esperados

### **SUCESSO ✅:**
```
Usuário A sempre recebe: 3 ingressos (IDs: 100, 101, 102)
Usuário B sempre recebe: 5 ingressos (IDs: 200, 201, 202, 203, 204)

Logs:
✅ Sem mensagem de "VAZAMENTO"
✅ user_id correto em cada log
✅ Total de ingressos consistente
```

### **AINDA COM PROBLEMA ❌:**
```
Usuário A às vezes recebe: 3 ingressos, às vezes 5 ingressos
Usuário B às vezes recebe: 5 ingressos, às vezes 3 ingressos

Logs:
🚨 VAZAMENTO DE DADOS DETECTADO!
```

Se ainda houver problema, verificar:
1. **Cache do servidor** (nginx, apache, CDN)
2. **Sessões PHP** mal configuradas
3. **Proxy reverso** cachando respostas
4. **Cliente** armazenando token errado

---

## 🆘 Se o Problema Persistir

### **Opção 1: Desabilitar OPcache Temporariamente**
```php
// php.ini ou .user.ini
opcache.enable=0
```

### **Opção 2: Verificar Configuração PHP-FPM**
```ini
; /etc/php/8.1/fpm/pool.d/www.conf
pm = dynamic
pm.max_children = 10
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3
pm.max_requests = 500  ; Recicla processos a cada 500 requests
```

### **Opção 3: Limpar Todo Cache**
```bash
# CodeIgniter cache
rm -rf writable/cache/*

# OPcache
sudo systemctl restart php-fpm

# Navegador
Ctrl + Shift + Delete
```

---

## 📱 Teste Prático com App

Se estiver usando app mobile:

1. **Fazer logout completo** em ambos os dispositivos
2. **Limpar dados do app** (cache, storage)
3. **Fazer login novamente** em cada dispositivo
4. **Testar simultaneamente**:
   - Dispositivo A: Ver ingressos
   - Dispositivo B: Ver ingressos
   - Repetir 5 vezes

---

## 📚 Documentação Completa

- 📄 `PROBLEMA_INGRESSOS_MISTURADOS.md` - Análise detalhada
- 📄 `sql/debug_ingressos_por_usuario.sql` - Queries de debug
- 📄 Logs: `writable/logs/log-*.log`

---

## ✅ Arquivos Modificados

| Arquivo | Status |
|---------|--------|
| `app/Models/IngressoModel.php` | ✅ Corrigido |
| `app/Controllers/Api/Ingressos.php` | ✅ Corrigido |
| `sql/debug_ingressos_por_usuario.sql` | ✅ Criado |
| `PROBLEMA_INGRESSOS_MISTURADOS.md` | ✅ Criado |

---

🚀 **Agora teste e me informe o resultado!**

**Especialmente importante:**
1. Se os logs mostram algum "VAZAMENTO"
2. Se os números de ingressos ficam consistentes
3. Qual user_id está tendo problema

