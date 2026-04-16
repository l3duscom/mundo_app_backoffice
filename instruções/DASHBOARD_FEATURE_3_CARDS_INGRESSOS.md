# 🎟️ Feature: Card de Ingressos com 3 Métricas

## 📋 Descrição
Modificação do card "Total de Ingressos" no Dashboard de Vendas para exibir **3 métricas distintas**:
1. **Ingressos Vendidos** (destaque) - Sem cortesias
2. **Cortesias** - Apenas cortesias (ticket_id = 608)
3. **Total Geral** - Vendidos + Cortesias

## 🎨 Layout

### Antes ❌
```
┌─────────────────────┐
│ Total de Ingressos  │
│      2.705          │
│   ↓ 13.3%           │
└─────────────────────┘
```

### Depois ✅
```
┌──────────────────────────────────────────┐
│  📊 Ingressos                            │
├──────────────────────────────────────────┤
│  Ingressos Vendidos                      │
│         2.705                            │
│      ↓ 13.3%                             │
├──────────────┬───────────────────────────┤
│ 🎁 Cortesias │ 📝 Total Geral           │
│      413     │     3.118                │
└──────────────┴───────────────────────────┘
```

## 🔧 Implementação

### 1. Model: `VendasRealtimeModel.php`
Adicionadas 2 novas subqueries no método `getMetricasGerais()`:

```php
// Total de cortesias (ticket_id = 608)
(SELECT SUM(CASE WHEN i6.tipo = 'combo' THEN 2 ELSE 1 END)
 FROM ingressos i6
 INNER JOIN pedidos p6 ON p6.id = i6.pedido_id
 WHERE p6.evento_id = ?
 AND p6.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
 AND i6.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
 AND i6.ticket_id = 608
) as total_cortesias

// Total com cortesias
(SELECT SUM(CASE WHEN i7.tipo = 'combo' THEN 2 ELSE 1 END)
 FROM ingressos i7
 INNER JOIN pedidos p7 ON p7.id = i7.pedido_id
 WHERE p7.evento_id = ?
 AND p7.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
 AND i7.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
) as total_com_cortesias
```

### 2. View: `vendas_realtime.php`

#### HTML Structure
```html
<div class="col-lg-6">
    <div class="metric-card">
        <div class="metric-label mb-3">📊 Ingressos</div>
        <div class="row g-2">
            <!-- Card principal: Ingressos Vendidos -->
            <div class="col-12">
                <div class="sub-metric-card-main">
                    <div class="sub-metric-label">Ingressos Vendidos</div>
                    <div class="sub-metric-value" id="totalIngressos">-</div>
                    <div class="metric-change" id="changeIngressos">-</div>
                </div>
            </div>
            <!-- Sub-cards: Cortesias e Total -->
            <div class="col-6">
                <div class="sub-metric-card">
                    <div class="sub-metric-label-small">🎁 Cortesias</div>
                    <div class="sub-metric-value-small" id="totalCortesias">-</div>
                </div>
            </div>
            <div class="col-6">
                <div class="sub-metric-card">
                    <div class="sub-metric-label-small">📝 Total Geral</div>
                    <div class="sub-metric-value-small" id="totalComCortesias">-</div>
                </div>
            </div>
        </div>
    </div>
</div>
```

#### CSS Classes
```css
.sub-metric-card-main {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.25rem;
    border-radius: 8px;
}

.sub-metric-card {
    background: var(--ga-bg);
    padding: 0.75rem;
    border-radius: 6px;
    border: 1px solid var(--ga-border);
    text-align: center;
}
```

#### JavaScript
```javascript
document.getElementById('totalIngressos').textContent = formatNumber(metricas.total_ingressos || 0);
document.getElementById('totalCortesias').textContent = formatNumber(metricas.total_cortesias || 0);
document.getElementById('totalComCortesias').textContent = formatNumber(metricas.total_com_cortesias || 0);
```

## 🧪 Validação

### SQL de Teste
Arquivo: `sql/test_metricas_ingressos.sql`

**Validação:**
```sql
-- Deve ser TRUE
ingressos_vendidos + cortesias = total_geral
```

### Exemplo de Resultado
```
ingressos_vendidos: 2.705
cortesias: 413
total_geral: 3.118
validacao_ok: TRUE ✅
```

## 📊 Regras de Negócio

### Ingressos Vendidos (Principal)
- ✅ Tipos válidos: todos EXCETO `cinemark`, `adicional`, `''`, `produto`
- ✅ Status: `CONFIRMED`, `RECEIVED`, `paid`, `RECEIVED_IN_CASH`
- ✅ **EXCLUIR** cortesias: `ticket_id != 608`
- ✅ Combos contam como 2

### Cortesias
- ✅ Tipos válidos: todos EXCETO `cinemark`, `adicional`, `''`, `produto`
- ✅ Status: `CONFIRMED`, `RECEIVED`, `paid`, `RECEIVED_IN_CASH`
- ✅ **APENAS** cortesias: `ticket_id = 608`
- ✅ Combos contam como 2

### Total Geral
- ✅ Tipos válidos: todos EXCETO `cinemark`, `adicional`, `''`, `produto`
- ✅ Status: `CONFIRMED`, `RECEIVED`, `paid`, `RECEIVED_IN_CASH`
- ✅ **SEM FILTRO** de ticket_id (inclui tudo)
- ✅ Combos contam como 2

## 🎯 Benefícios
1. ✅ **Transparência**: Visualização clara de cortesias vs vendidos
2. ✅ **Análise**: Possibilita análise de impacto das cortesias
3. ✅ **Precisão**: Mantém números de vendas reais em destaque
4. ✅ **Contexto**: Total geral para referência completa

## 📱 Responsividade
- **Desktop (≥992px)**: Card ocupa 6 colunas (50% da largura)
- **Tablet (768-991px)**: Card ocupa 6 colunas
- **Mobile (<768px)**: Card ocupa 12 colunas (largura completa)

## 🚀 Implementação
- **Data:** 25/11/2025
- **Arquivos alterados:** 2
  - `app/Models/VendasRealtimeModel.php`
  - `app/Views/Dashboard/vendas_realtime.php`
- **Linhas adicionadas:** ~100
- **Status:** ✅ Implementado e testado

