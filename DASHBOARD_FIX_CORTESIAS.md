# 🎫 Correção: Exclusão de Ingressos Cortesia do Dashboard

## 📋 Problema Identificado
O dashboard de vendas estava contando **ingressos cortesia** (gratuitos) junto com os ingressos pagos, causando divergência nos números.

**Exemplo:**
- Dashboard mostrava: **453 ingressos**
- Real (sem cortesias): **40 ingressos**
- Diferença: **413 cortesias** estavam sendo contadas indevidamente

## 🔍 Identificação
Ingressos cortesia são identificados por:
- `ticket_id = 608` na tabela `ingressos`

## ✅ Correção Aplicada

### Arquivo: `app/Models/VendasRealtimeModel.php`

Adicionado filtro `AND i.ticket_id != 608` em **TODAS** as queries que contam ingressos:

#### 1. **getMetricasGerais()** - Total de Ingressos
```sql
AND i2.ticket_id != 608
```

#### 2. **getEvolucaoDiaria()** - Evolução de Vendas
```sql
AND i.ticket_id != 608
```

#### 3. **getVendasPorHora()** - Vendas por Hora
```sql
AND i.ticket_id != 608
```

#### 4. **getTopIngressos()** - Top Ingressos Vendidos
```sql
AND i.ticket_id != 608
```

#### 5. **getVendasRecentes()** - Vendas Recentes
```sql
AND i.ticket_id != 608
```

#### 6. **getComparacaoPeriodo()** - Comparação de Período
```sql
AND i.ticket_id != 608
```
(Aplicado em ambos os períodos: atual e anterior)

## 📊 Impacto

### Antes ❌
- Ingressos pagos + cortesias = **números inflados**
- Métricas não representavam vendas reais
- Relatórios imprecisos

### Depois ✅
- Apenas ingressos **pagos** são contabilizados
- Números refletem **vendas reais**
- Métricas precisas para análise de negócio

## 🧪 Testes
Arquivos SQL atualizados para validação:
- `sql/debug_contagem_ingressos.sql` - Comparação com/sem cortesias
- `sql/test_evolucao_ingressos.sql` - Validação de evolução

## 📝 Regras de Contagem (mantidas)
1. ✅ Ingressos tipo `'combo'` contam como **2**
2. ✅ Tipos ignorados: `'cinemark'`, `'adicional'`, `''`, `'produto'`
3. ✅ Apenas pedidos confirmados: `CONFIRMED`, `RECEIVED`, `paid`, `RECEIVED_IN_CASH`
4. ✅ **NOVO:** Cortesias excluídas: `ticket_id != 608`

## 🚀 Implementação
- **Data:** 25/11/2025
- **Arquivos alterados:** 1 (VendasRealtimeModel.php)
- **Métodos corrigidos:** 6
- **Scripts SQL atualizados:** 2
- **Status:** ✅ Implementado e testado

