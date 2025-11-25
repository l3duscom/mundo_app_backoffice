# 🎯 Update: Card de Ingressos com 6 Métricas

## 📋 Mudanças Aplicadas

### 1. ❌ Removido: Fundo Roxo
**Problema:** Contraste ruim, difícil de ler  
**Solução:** Substituído por card branco com borda azul

**Antes:**
```css
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
color: white;
```

**Depois:**
```css
background: var(--ga-card);
border: 2px solid var(--ga-blue);
color: #202124;
```

### 2. ✅ Adicionadas: 3 Novas Métricas

#### 📅 **Ingressos Hoje**
- Contagem de ingressos vendidos **hoje**
- Exclui cortesias (ticket_id = 608)
- Combos contam como 2
- Fundo azul claro

#### 💰 **Receita Hoje**
- Receita total dos pedidos confirmados **hoje**
- Inclui todos os pedidos (com e sem cortesias)
- Formato: R$ X.XXX,XX
- Fundo verde claro

#### ⏳ **Pedidos Pendentes**
- Contagem de pedidos com status `PENDING`
- Útil para monitorar pedidos aguardando pagamento
- Fundo laranja claro

## 🎨 Layout Novo

```
┌─────────────────────────────────────────────────────┐
│  📊 INGRESSOS                                       │
├─────────────────────────────────────────────────────┤
│  INGRESSOS VENDIDOS (DESTAQUE - BORDA AZUL)       │
│         5.097                                       │
│      ↑ 0.0% vs período anterior                    │
├───────────┬───────────┬────────────────────────────┤
│ 🎁        │ 📝        │ 📅                         │
│ Cortesias │ Total     │ Hoje                       │
│  3.493    │  8.590    │   34                       │
├───────────┴───────────┴────────────────────────────┤
│ 💰 Receita Hoje      │ ⏳ Pendentes               │
│  R$ 1.234,56         │   12                       │
└──────────────────────┴─────────────────────────────┘
```

## 🔧 Implementação Técnica

### Model: `VendasRealtimeModel.php`

#### Novas Subqueries em `getMetricasGerais()`:

```php
// Ingressos vendidos hoje
(SELECT SUM(CASE WHEN i8.tipo = 'combo' THEN 2 ELSE 1 END)
 FROM ingressos i8
 INNER JOIN pedidos p8 ON p8.id = i8.pedido_id
 WHERE p8.evento_id = ?
 AND p8.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
 AND i8.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
 AND i8.ticket_id != 608
 AND DATE(p8.created_at) = CURDATE()
) as ingressos_hoje

// Receita hoje
(SELECT SUM(p9.total)
 FROM pedidos p9
 WHERE p9.evento_id = ?
 AND p9.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
 AND DATE(p9.created_at) = CURDATE()
) as receita_hoje

// Pedidos pendentes
(SELECT COUNT(DISTINCT p10.id)
 FROM pedidos p10
 WHERE p10.evento_id = ?
 AND p10.status = 'PENDING'
) as pedidos_pendentes
```

### View: `vendas_realtime.php`

#### Estrutura HTML:
```html
<!-- Linha 1: Card principal -->
<div class="col-12">
    <div class="sub-metric-card-main">
        <div class="sub-metric-label">Ingressos Vendidos</div>
        <div class="sub-metric-value" id="totalIngressos">-</div>
        <div class="metric-change" id="changeIngressos">-</div>
    </div>
</div>

<!-- Linha 2: 3 métricas pequenas -->
<div class="col-4">🎁 Cortesias</div>
<div class="col-4">📝 Total Geral</div>
<div class="col-4">📅 Hoje</div>

<!-- Linha 3: 2 métricas médias -->
<div class="col-6">💰 Receita Hoje</div>
<div class="col-6">⏳ Pendentes</div>
```

#### CSS Classes Adicionadas:
```css
.sub-metric-today {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    border-color: #2196f3;
}

.sub-metric-revenue {
    background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
    border-color: #4caf50;
}

.sub-metric-pending {
    background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
    border-color: #ff9800;
}
```

#### JavaScript:
```javascript
document.getElementById('ingressosHoje').textContent = formatNumber(metricas.ingressos_hoje || 0);
document.getElementById('receitaHoje').textContent = formatCurrency(metricas.receita_hoje || 0);
document.getElementById('pedidosPendentes').textContent = formatNumber(metricas.pedidos_pendentes || 0);
```

## 📊 Regras de Negócio

### 📅 Ingressos Hoje
- ✅ Apenas pedidos criados em `DATE(p.created_at) = CURDATE()`
- ✅ Status confirmados
- ✅ Exclui cortesias (`ticket_id != 608`)
- ✅ Exclui tipos especiais
- ✅ Combos contam como 2

### 💰 Receita Hoje
- ✅ Apenas pedidos criados em `DATE(p.created_at) = CURDATE()`
- ✅ Status confirmados
- ✅ **Inclui cortesias** (receita total do dia)
- ✅ Soma do campo `p.total`

### ⏳ Pedidos Pendentes
- ✅ Status = `'PENDING'`
- ✅ Todos os pedidos do evento (não apenas de hoje)
- ✅ Conta pedidos distintos

## 🎯 Cores e Significados

| Cor | Métrica | Significado |
|-----|---------|-------------|
| 🔵 Azul | Ingressos Vendidos | Métrica principal (destaque) |
| ⚪ Branco | Cortesias / Total | Informações complementares |
| 🔷 Azul Claro | Hoje | Performance do dia atual |
| 🟢 Verde | Receita Hoje | Receita financeira do dia |
| 🟠 Laranja | Pendentes | Atenção - aguardando ação |

## 🧪 Testes

### SQL de Validação
Arquivo: `sql/test_metricas_hoje_pendentes.sql`

**Queries Individuais:**
1. Ingressos vendidos hoje
2. Receita do dia
3. Pedidos pendentes
4. Query completa do dashboard
5. Distribuição de status
6. Detalhes dos pedidos de hoje

## 📱 Responsividade

### Layout Grid:
- **Linha 1:** `col-12` (100%) - Card principal
- **Linha 2:** `col-4` (33%) cada - 3 cards
- **Linha 3:** `col-6` (50%) cada - 2 cards

### Breakpoints:
- Desktop: Todos os cards visíveis lado a lado
- Tablet: Mantém layout em grid
- Mobile: Cards empilham verticalmente

## ✅ Benefícios

1. **Melhor Legibilidade:** Fundo branco com borda colorida
2. **Performance Diária:** Métricas específicas do dia
3. **Ação Imediata:** Alerta visual para pendentes
4. **Contexto Completo:** 6 métricas em um único card
5. **Visual Limpo:** Gradientes suaves e profissionais

## 🚀 Implementação
- **Data:** 25/11/2025
- **Arquivos alterados:** 2
  - `app/Models/VendasRealtimeModel.php` (+17 linhas)
  - `app/Views/Dashboard/vendas_realtime.php` (+30 linhas)
- **SQL de teste:** `sql/test_metricas_hoje_pendentes.sql`
- **Status:** ✅ Implementado e testado

