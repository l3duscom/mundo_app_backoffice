# 🧪 Casos de Teste - API Retirar Pontos

## ✅ Casos de Sucesso

### Teste 1: Retirada Simples
**Cenário:** Usuário com saldo suficiente

**Pré-condição:**
- Usuário existe (ID: 123)
- Saldo atual: 1000 pontos
- Usuário autenticado

**Request:**
```json
POST /api/usuarios/retirar-pontos
{
  "usuario_id": 123,
  "pontos": 100,
  "motivo": "Resgate de chaveiro"
}
```

**Resultado Esperado:**
- ✅ Status: 200
- ✅ `success: true`
- ✅ `saldo_anterior: 1000`
- ✅ `saldo_atual: 900`
- ✅ Registro criado em `extrato_pontos`
- ✅ Saldo do usuário atualizado

**SQL Verificação:**
```sql
-- Saldo deve ser 900
SELECT pontos FROM usuarios WHERE id = 123;

-- Deve existir registro de DEBITO
SELECT * FROM extrato_pontos 
WHERE usuario_id = 123 
AND tipo_transacao = 'DEBITO' 
ORDER BY created_at DESC 
LIMIT 1;
```

---

### Teste 2: Retirada com Evento
**Cenário:** Retirada vinculada a um evento específico

**Request:**
```json
POST /api/usuarios/retirar-pontos
{
  "usuario_id": 123,
  "pontos": 250,
  "motivo": "Resgate: Ingresso VIP Dreamfest 2026",
  "event_id": 18
}
```

**Verificação:**
```sql
-- event_id deve ser 18
SELECT event_id, descricao 
FROM extrato_pontos 
WHERE usuario_id = 123 
ORDER BY created_at DESC 
LIMIT 1;
```

---

### Teste 3: Consultar Saldo
**Cenário:** Verificar saldo antes de retirar

**Request:**
```
GET /api/usuarios/saldo/123
```

**Resultado Esperado:**
```json
{
  "success": true,
  "data": {
    "usuario_id": 123,
    "nome": "João Silva",
    "email": "joao@example.com",
    "pontos": 650
  }
}
```

---

## ❌ Casos de Erro

### Teste 4: Saldo Insuficiente
**Cenário:** Tentar retirar mais pontos do que o usuário tem

**Pré-condição:**
- Saldo atual: 50 pontos
- Tentando retirar: 100 pontos

**Request:**
```json
POST /api/usuarios/retirar-pontos
{
  "usuario_id": 123,
  "pontos": 100,
  "motivo": "Tentativa de resgate"
}
```

**Resultado Esperado:**
- ✅ Status: 400
- ✅ `success: false`
- ✅ `message: "Saldo insuficiente. O usuário possui apenas 50 pontos."`
- ✅ `saldo_atual: 50`
- ✅ `pontos_solicitados: 100`
- ✅ Nenhuma alteração no banco

**Verificação:**
```sql
-- Saldo deve permanecer 50
SELECT pontos FROM usuarios WHERE id = 123;

-- Não deve ter novo registro no extrato
SELECT COUNT(*) FROM extrato_pontos 
WHERE usuario_id = 123 
AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE);
```

---

### Teste 5: Usuário Não Encontrado
**Request:**
```json
POST /api/usuarios/retirar-pontos
{
  "usuario_id": 99999,
  "pontos": 100,
  "motivo": "Teste"
}
```

**Resultado Esperado:**
- ✅ Status: 404
- ✅ `success: false`
- ✅ `message: "Usuário não encontrado"`

---

### Teste 6: Token Inválido
**Request:**
```bash
curl -X POST /api/usuarios/retirar-pontos \
  -H "Authorization: Bearer TOKEN_INVALIDO" \
  -d '{"usuario_id": 123, "pontos": 100, "motivo": "Teste"}'
```

**Resultado Esperado:**
- ✅ Status: 401
- ✅ `message: "Usuário não autenticado"`

---

### Teste 7: Dados Inválidos - Pontos Zero
**Request:**
```json
POST /api/usuarios/retirar-pontos
{
  "usuario_id": 123,
  "pontos": 0,
  "motivo": "Teste"
}
```

**Resultado Esperado:**
- ✅ Status: 400
- ✅ `message: "O campo pontos é obrigatório e deve ser maior que zero"`

---

### Teste 8: Dados Inválidos - Pontos Negativos
**Request:**
```json
POST /api/usuarios/retirar-pontos
{
  "usuario_id": 123,
  "pontos": -50,
  "motivo": "Teste"
}
```

**Resultado Esperado:**
- ✅ Status: 400
- ✅ `message: "O campo pontos é obrigatório e deve ser maior que zero"`

---

### Teste 9: Campo Obrigatório Faltando
**Request:**
```json
POST /api/usuarios/retirar-pontos
{
  "usuario_id": 123,
  "pontos": 100
}
```

**Resultado Esperado:**
- ✅ Status: 400
- ✅ `message: "O campo motivo é obrigatório"`

---

## 🔄 Teste de Transação (Atomicidade)

### Teste 10: Rollback em Caso de Erro

**Objetivo:** Garantir que se houver erro, nenhuma alteração é feita

**Simulação:**
1. Forçar erro após atualizar usuário (ex: erro no extrato)
2. Verificar que os pontos do usuário **não foram alterados**
3. Verificar que **não foi criado registro** no extrato

**SQL para Simular:**
```sql
START TRANSACTION;

-- Atualizar usuário
UPDATE usuarios SET pontos = pontos - 100 WHERE id = 123;

-- Simular erro (syntax error proposital)
INSERT INTO tabela_que_nao_existe VALUES (1);

-- ROLLBACK automático ocorrerá
```

**Verificação:**
```sql
-- Pontos devem estar inalterados
SELECT pontos FROM usuarios WHERE id = 123;
```

---

## 📊 Testes de Performance

### Teste 11: Múltiplas Retiradas Simultâneas

**JavaScript:**
```javascript
const retiradas = Array(10).fill(null).map((_, i) => 
    fetch('/api/usuarios/retirar-pontos', {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            usuario_id: 123 + i,
            pontos: 10,
            motivo: `Resgate lote #${i}`
        })
    })
);

const resultados = await Promise.all(retiradas);
console.log('Processadas:', resultados.length);
```

---

## 🎯 Checklist de Testes

### Validações
- [ ] Token JWT ausente → 401
- [ ] Token JWT inválido → 401
- [ ] usuario_id ausente → 400
- [ ] pontos ausente → 400
- [ ] pontos = 0 → 400
- [ ] pontos < 0 → 400
- [ ] motivo ausente → 400
- [ ] Usuário não existe → 404
- [ ] Saldo insuficiente → 400

### Operações
- [ ] Retirada bem-sucedida → 200
- [ ] Saldo do usuário atualizado corretamente
- [ ] Extrato criado com tipo DEBITO
- [ ] saldo_anterior correto
- [ ] saldo_atual correto (anterior - retirada)
- [ ] admin_id registrado
- [ ] motivo registrado
- [ ] event_id registrado (se fornecido)
- [ ] Log criado

### Transações
- [ ] Rollback em caso de erro
- [ ] Atomicidade garantida
- [ ] Sem estado inconsistente

### Consulta de Saldo
- [ ] Retorna dados corretos
- [ ] Funciona com token válido
- [ ] Erro com usuário inexistente

---

## 🛠️ Ferramentas de Teste

### Postman
1. Importar collection `EXEMPLOS_API_RETIRAR_PONTOS.md`
2. Configurar variável `{{jwt_token}}`
3. Executar testes

### SQL Scripts
```bash
# Executar testes SQL
mysql -u usuario -p database < sql/test_retirar_pontos.sql
```

### Browser Console
```javascript
// Copie e cole no console do navegador
// (após obter token JWT)
const token = 'SEU_TOKEN_AQUI';

fetch('/api/usuarios/saldo/123', {
    headers: { 'Authorization': 'Bearer ' + token }
})
.then(r => r.json())
.then(d => console.log('Saldo:', d));
```

---

## 📈 Métricas de Teste

### Performance Aceitável
- ⚡ Tempo de resposta: < 500ms
- 🔄 Transação DB: < 100ms
- 📝 Log: < 10ms

### Carga
- 🎯 Suportar 10 requisições simultâneas
- 🎯 Sem race conditions
- 🎯 Transações isoladas

---

## 🚀 Próximos Passos

1. [ ] Testar em desenvolvimento
2. [ ] Validar todos os cenários de erro
3. [ ] Verificar logs
4. [ ] Testar transações
5. [ ] Validar em produção (com cautela)
6. [ ] Monitorar primeiras operações
7. [ ] Coletar métricas de uso

---

## 📞 Suporte

**Logs:**
```bash
tail -f writable/logs/log-$(date +%Y-%m-%d).log | grep "retirar pontos"
```

**Debug SQL:**
```sql
-- Ver últimas retiradas
SELECT * FROM extrato_pontos 
WHERE tipo_transacao = 'DEBITO' 
ORDER BY created_at DESC 
LIMIT 10;
```

---

## ✅ Status
- **Implementação:** ✅ Completa
- **Documentação:** ✅ Completa
- **Testes:** 🧪 Prontos para execução
- **Deploy:** 🚀 Pronto

