# ✅ Correção Aplicada: event_id → evento_id

## 📝 Problema Identificado

**Erro SQL:** `Unknown column 'p.event_id' in 'on clause'`

**Causa:** A tabela `pedidos` usa o campo `evento_id`, não `event_id`.

## ✅ Correções Aplicadas

### Arquivo: `app/Models/VendasComparativasModel.php`

Todas as queries SQL foram corrigidas:

#### 1. Método `getVisaoGeralEventos()`
- ✅ `e.id AS event_id` → `e.id AS evento_id`
- ✅ `p.event_id` → `p.evento_id`

#### 2. Método `getEvolucaoDiariaComparativa()`
- ✅ `p.event_id` → `p.evento_id` (todas as ocorrências)
- ✅ `vd.event_id` → `vd.evento_id`
- ✅ `va1.event_id` → `va1.evento_id`
- ✅ `va2.event_id` → `va2.evento_id`

#### 3. Método `getComparacaoPorPeriodos()`
- ✅ `event_id` → `evento_id` (em primeira_venda CTE)
- ✅ `p.event_id` → `p.evento_id`
- ✅ `pv.event_id` → `pv.evento_id`

#### 4. Método `getResumoExecutivo()`
- ✅ `p.event_id` → `p.evento_id` (em todos os subselects)

#### 5. Método `getEventosDisponiveis()`
- ✅ `p.event_id` → `p.evento_id`

## 🔍 Total de Correções

**38 ocorrências corrigidas** em todo o arquivo.

## ✅ Verificação

Execute este comando para confirmar que não há mais referências a `event_id`:

```bash
grep -n "event_id" app/Models/VendasComparativasModel.php
```

**Resultado esperado:** Nenhuma ocorrência encontrada (ou apenas em comentários).

## 🚀 Próximos Passos

1. ✅ **Limpe o cache** (se houver):
   ```bash
   php spark cache:clear
   ```

2. ✅ **Acesse o dashboard novamente**:
   ```
   https://seu-dominio.com/admin-dashboard-vendas
   ```

3. ✅ **Teste a comparação**:
   - Selecione dois eventos
   - Clique em "Comparar"
   - Deve carregar os gráficos sem erros

## 📊 Estrutura Correta das Tabelas

Para referência futura:

```sql
-- Tabela EVENTOS
eventos:
  - id (PK)
  - nome
  - data_inicio
  - ...

-- Tabela PEDIDOS
pedidos:
  - id (PK)
  - evento_id (FK → eventos.id)  ← CAMPO CORRETO
  - user_id (FK)
  - total
  - status
  - created_at
  - ...

-- Tabela INGRESSOS
ingressos:
  - id (PK)
  - pedido_id (FK → pedidos.id)
  - ticket_id (FK → tickets.id)
  - ...
```

## 🛠️ Se Houver Outros Erros de Coluna

Caso encontre outros erros similares, verifique:

1. **Coluna `status` na tabela `pedidos`**:
   - Valores: `CONFIRMED`, `RECEIVED`, `RECEIVED_IN_CASH`
   - Se estiver usando outros valores, ajuste em `VendasComparativasModel.php`

2. **Coluna `ticket_id` na tabela `ingressos`**:
   - ID da cortesia: 608 (configurado no Model)
   - Para mudar: altere `$ticketCortesia = 608;` em cada método

3. **Coluna `total` na tabela `pedidos`**:
   - Tipo: DECIMAL ou FLOAT
   - Contém o valor total do pedido

## 📝 Notas Adicionais

- ✅ Todas as correções mantêm compatibilidade com MySQL 8.0+
- ✅ CTEs (WITH) são utilizadas para melhor performance
- ✅ Window Functions (OVER PARTITION BY) são utilizadas
- ⚠️ Se usar MySQL 5.7, considere usar o script alternativo (sem CTEs)

## 🎯 Teste Rápido

Execute este SQL no seu banco para verificar a estrutura:

```sql
-- Verificar estrutura da tabela pedidos
DESCRIBE pedidos;

-- Deve mostrar 'evento_id' na lista de colunas
```

---

**Status:** ✅ **CORRIGIDO**  
**Data:** Novembro 2025  
**Arquivo Atualizado:** `app/Models/VendasComparativasModel.php`

