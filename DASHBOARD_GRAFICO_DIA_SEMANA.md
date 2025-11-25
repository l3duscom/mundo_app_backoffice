# 📊 Novo Gráfico: Vendas por Dia da Semana

## 🎯 Objetivo
Visualizar o padrão de vendas ao longo da semana para identificar:
- Dias com maior volume de vendas
- Dias com maior receita
- Padrões de comportamento do público

## 🎨 Layout

### Posicionamento
```
┌─────────────────────┬─────────────────────┐
│ Evolução de Vendas  │ Dia da Semana       │
│    (col-lg-6)       │    (col-lg-6)       │
│   Gráfico de Linha  │  Gráfico de Barras  │
└─────────────────────┴─────────────────────┘
```

### Tipo de Gráfico
**Bar Chart (Barras Agrupadas)** com 2 datasets:
- 🔵 **Ingressos** (eixo Y esquerdo)
- 🟢 **Receita** (eixo Y direito)

## 📊 Dados Exibidos

### Eixo X (Labels)
- Domingo
- Segunda
- Terça
- Quarta
- Quinta
- Sexta
- Sábado

### Eixo Y Esquerdo (Ingressos)
- Contagem de ingressos vendidos
- Combos contam como 2
- Sem cortesias (ticket_id != 608)

### Eixo Y Direito (Receita)
- Receita total em R$
- Formatado: R$ X.XXX,XX

## 🔧 Implementação

### 1. Model: `VendasRealtimeModel.php`

#### Novo Método: `getVendasPorDiaSemana()`

```php
public function getVendasPorDiaSemana(int $evento_id): array
{
    $sql = "
    SELECT 
        DAYOFWEEK(p.created_at) as dia_numero,
        CASE DAYOFWEEK(p.created_at)
            WHEN 1 THEN 'Domingo'
            WHEN 2 THEN 'Segunda'
            WHEN 3 THEN 'Terça'
            WHEN 4 THEN 'Quarta'
            WHEN 5 THEN 'Quinta'
            WHEN 6 THEN 'Sexta'
            WHEN 7 THEN 'Sábado'
        END as dia_semana,
        SUM(CASE WHEN i.tipo = 'combo' THEN 2 ELSE 1 END) as ingressos,
        SUM(i.valor) as receita,
        COUNT(DISTINCT p.id) as pedidos
    FROM pedidos p
    INNER JOIN ingressos i ON i.pedido_id = p.id
    WHERE p.evento_id = ?
    AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
    AND i.tipo NOT IN ('cinemark', 'adicional', '', 'produto')
    AND i.ticket_id != 608
    GROUP BY DAYOFWEEK(p.created_at), dia_semana
    ORDER BY dia_numero
    ";
    
    $query = $this->db->query($sql, [$evento_id]);
    return $query ? $query->getResultArray() : [];
}
```

#### Regras de Negócio
- ✅ **DAYOFWEEK()**: Função MySQL que retorna 1-7 (1=Domingo, 7=Sábado)
- ✅ **CASE WHEN**: Converte número para nome em português
- ✅ **Filtros**: Mesmos do dashboard (sem cortesias, tipos válidos)
- ✅ **Agrupamento**: Por dia da semana (soma todos os domingos, todas as segundas, etc.)
- ✅ **Ordenação**: Por número do dia (1-7)

### 2. Controller: `DashboardVendas.php`

```php
try {
    log_message('info', 'Buscando vendas por dia da semana...');
    $dados['vendas_dia_semana'] = $this->vendasModel->getVendasPorDiaSemana($event_id);
} catch (\Exception $e) {
    log_message('error', 'Erro em getVendasPorDiaSemana: ' . $e->getMessage());
    $dados['vendas_dia_semana'] = [];
}
```

### 3. View: `vendas_realtime.php`

#### HTML
```html
<div class="col-lg-6 mb-4">
    <div class="chart-card">
        <div class="chart-card-title">
            <span>📅 Vendas por Dia da Semana</span>
        </div>
        <div style="position: relative; height: 350px;">
            <canvas id="chartDiaSemana"></canvas>
        </div>
    </div>
</div>
```

#### JavaScript (Chart.js)
```javascript
// Gráfico de Vendas por Dia da Semana
const diaSemanaData = data.vendas_dia_semana || [];

if (diaSemanaData.length > 0) {
    const diasLabels = diaSemanaData.map(d => d.dia_semana);
    const diasIngressos = diaSemanaData.map(d => parseInt(d.ingressos || 0));
    const diasReceita = diaSemanaData.map(d => parseFloat(d.receita || 0));
    
    if (charts.diaSemana) {
        charts.diaSemana.destroy();
    }
    
    const canvasDiaSemana = document.getElementById('chartDiaSemana');
    if (canvasDiaSemana) {
        const ctxDiaSemana = canvasDiaSemana.getContext('2d');
        charts.diaSemana = new Chart(ctxDiaSemana, {
            type: 'bar',
            data: {
                labels: diasLabels,
                datasets: [
                    {
                        label: 'Ingressos',
                        data: diasIngressos,
                        backgroundColor: 'rgba(26, 115, 232, 0.8)',
                        borderColor: '#1a73e8',
                        borderWidth: 2,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Receita (R$)',
                        data: diasReceita,
                        backgroundColor: 'rgba(13, 101, 45, 0.8)',
                        borderColor: '#0d652d',
                        borderWidth: 2,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Ingressos'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        grid: {
                            drawOnChartArea: false
                        },
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR');
                            }
                        },
                        title: {
                            display: true,
                            text: 'Receita'
                        }
                    }
                }
            }
        });
    }
}
```

## 📊 Configuração do Chart.js

### Tipo
```javascript
type: 'bar'
```

### Datasets
1. **Ingressos** (Azul)
   - `backgroundColor`: 'rgba(26, 115, 232, 0.8)'
   - `borderColor`: '#1a73e8'
   - `yAxisID`: 'y' (esquerdo)

2. **Receita** (Verde)
   - `backgroundColor`: 'rgba(13, 101, 45, 0.8)'
   - `borderColor`: '#0d652d'
   - `yAxisID`: 'y1' (direito)

### Scales (Eixos)

#### Y (Esquerdo) - Ingressos
```javascript
y: {
    type: 'linear',
    position: 'left',
    beginAtZero: true,
    title: {
        text: 'Ingressos'
    }
}
```

#### Y1 (Direito) - Receita
```javascript
y1: {
    type: 'linear',
    position: 'right',
    beginAtZero: true,
    grid: {
        drawOnChartArea: false
    },
    ticks: {
        callback: function(value) {
            return 'R$ ' + value.toLocaleString('pt-BR');
        }
    },
    title: {
        text: 'Receita'
    }
}
```

## 🎯 Casos de Uso

### 1. Identificar Melhor Dia
**Pergunta:** Qual dia da semana vende mais?

**Análise:**
- Comparar altura das barras azuis (ingressos)
- Identificar picos de venda
- Planejar ações promocionais

### 2. Receita por Dia
**Pergunta:** Qual dia gera mais receita?

**Análise:**
- Comparar altura das barras verdes (receita)
- Pode ser diferente do dia com mais ingressos
- Útil para precificação estratégica

### 3. Padrões de Comportamento
**Observações possíveis:**
- 📈 **Fins de semana:** Geralmente vendem mais
- 📉 **Meio de semana:** Vendas menores
- 🎯 **Segunda-feira:** Pode ter pico pós-divulgação

## 📱 Responsividade

### Desktop (≥992px)
- 2 gráficos lado a lado (col-lg-6 cada)
- Largura: 50% cada
- Altura: 350px

### Tablet e Mobile (<992px)
- Gráficos empilhados verticalmente
- Largura: 100% cada
- Altura: 350px mantida

## ✅ Benefícios

1. **📊 Insight Semanal:** Padrão de vendas por dia
2. **🎯 Planejamento:** Identificar melhores dias para ações
3. **💡 Estratégia:** Ajustar marketing conforme comportamento
4. **📈 Comparação:** Ingressos vs Receita lado a lado
5. **🔄 Complementar:** Funciona junto com evolução diária

## 🧪 Testes

### SQL de Validação
Arquivo: `sql/test_vendas_dia_semana.sql`

**Queries incluídas:**
1. Agregação por dia da semana (query do dashboard)
2. Distribuição por data com dia da semana
3. Últimos 30 dias detalhados

## 🚀 Implementação
- **Data:** 25/11/2025
- **Arquivos modificados:** 3
  - `app/Models/VendasRealtimeModel.php` (+33 linhas)
  - `app/Controllers/DashboardVendas.php` (+10 linhas)
  - `app/Views/Dashboard/vendas_realtime.php` (+100 linhas)
- **SQL de teste:** `sql/test_vendas_dia_semana.sql`
- **Tipo de gráfico:** Bar Chart (barras agrupadas)
- **Status:** ✅ Implementado e testado

## 📝 Notas Técnicas

### DAYOFWEEK() no MySQL
- Retorna 1-7
- 1 = Domingo (não Segunda!)
- Independente de locale

### Ordenação
- `ORDER BY dia_numero` garante ordem correta
- Domingo → Sábado

### Performance
- Query simples com agregação
- Índices em `created_at` e `status` ajudam
- Sem JOINs complexos

### Compatibilidade
- MySQL 5.7+
- Chart.js 3.x+
- Navegadores modernos

