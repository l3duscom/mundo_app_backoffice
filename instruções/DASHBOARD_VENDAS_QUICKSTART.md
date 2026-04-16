# 🚀 Dashboard de Vendas - Guia Rápido

## ⚡ Início Rápido (5 minutos)

### 1. Acesso Imediato

```
URL: https://seu-dominio.com/admin-dashboard-vendas
Requisito: Estar logado como ADMIN
```

### 2. Menu Lateral

O link **"Dashboard de Vendas"** foi adicionado automaticamente ao menu lateral administrativo, logo abaixo do "Dashboard" principal.

### 3. Primeiro Uso

1. **Selecione Evento 1**: Escolha o evento principal (mais recente)
2. **Selecione Evento 2**: Escolha o evento de comparação (anterior)
3. **Clique em "Comparar"**: Aguarde 2-3 segundos
4. **Visualize**: Gráficos e KPIs aparecerão automaticamente

---

## 📊 O que você verá

### KPIs (Cards no topo)
- ✅ **Total de Ingressos** de cada evento
- ✅ **Receita Total** de cada evento  
- ✅ **Diferenças** em números absolutos e percentuais
- ✅ **Indicadores visuais**: Verde (positivo) | Vermelho (negativo)

### Gráficos (5 visualizações)

1. **Ingressos Acumulados** 📈
   - Linha do tempo de vendas
   - Comparação dia a dia
   - Ideal para: Ver se está vendendo mais rápido

2. **Receita Acumulada** 💰
   - Evolução financeira
   - Comparação de faturamento
   - Ideal para: Análise de revenue

3. **Ingressos por Dia** 📊
   - Barras lado a lado
   - Vendas diárias
   - Ideal para: Identificar picos

4. **Receita por Dia** 💵
   - Faturamento diário
   - Comparação de dias
   - Ideal para: Análise de performance

5. **Períodos** 📉
   - Primeira semana, segundo mês, etc.
   - Comparação por fases
   - Ideal para: Estratégia de campanha

---

## 💾 Exportação

### Botão "Exportar CSV"

- **Quando aparece**: Após a primeira comparação
- **O que contém**: Todos os dados diários em formato planilha
- **Formato**: UTF-8 com BOM (compatível Excel)
- **Nome do arquivo**: `comparacao_vendas_17_vs_18_2025-11-25_143022.csv`

### Como usar o CSV

1. Abra no Excel ou Google Sheets
2. Use para criar suas próprias análises
3. Compartilhe com equipe
4. Importe em outras ferramentas (Power BI, Tableau, etc.)

---

## ⚙️ Configurações Rápidas

### Mudar status de pedidos considerados

**Arquivo**: `app/Models/VendasComparativasModel.php`

```php
// Linha ~20-30 (aproximadamente)
$status = ['CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH'];

// Adicione ou remova status conforme necessário
$status = ['CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH', 'PENDING'];
```

### Mudar ID da cortesia

**Arquivo**: `app/Models/VendasComparativasModel.php`

```php
// Linha ~15 (aproximadamente)
int $ticketCortesia = 608

// Altere para outro ID
int $ticketCortesia = 999
```

### Adicionar mais períodos

**Arquivo**: `app/Models/VendasComparativasModel.php`

Método: `getComparacaoPorPeriodos()`

```sql
CASE 
    WHEN DATEDIFF(p.created_at, pv.inicio) <= 7 THEN '1. Primeira Semana'
    WHEN DATEDIFF(p.created_at, pv.inicio) <= 14 THEN '2. Segunda Semana'
    WHEN DATEDIFF(p.created_at, pv.inicio) <= 21 THEN '3. Terceira Semana'
    WHEN DATEDIFF(p.created_at, pv.inicio) <= 30 THEN '4. Primeiro Mês'
    -- ADICIONE AQUI
    WHEN DATEDIFF(p.created_at, pv.inicio) <= 90 THEN '5. Três Meses'
    ELSE '6. Demais Períodos'
END AS periodo
```

---

## 🔧 Troubleshooting Rápido

### Problema: "Acesso negado"
**Solução**: Verificar se usuário está marcado como admin no banco de dados

```sql
-- Verificar
SELECT id, nome, email, is_admin FROM usuarios WHERE id = SEU_ID;

-- Corrigir se necessário
UPDATE usuarios SET is_admin = 1 WHERE id = SEU_ID;
```

### Problema: Nenhum evento aparece no dropdown
**Solução**: Eventos precisam ter pelo menos 1 pedido

```sql
-- Verificar eventos com pedidos
SELECT e.id, e.nome, COUNT(p.id) as total_pedidos 
FROM eventos e 
LEFT JOIN pedidos p ON e.id = p.event_id 
GROUP BY e.id, e.nome
HAVING total_pedidos > 0;
```

### Problema: Gráficos não aparecem
**Solução 1**: Verificar console do navegador (F12)
**Solução 2**: Verificar se Chart.js carregou (aba Network)
**Solução 3**: Testar CDN alternativo

```html
<!-- Em app/Views/admin/dashboard_vendas.php, trocar: -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!-- Por: -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.js"></script>
```

### Problema: CSV com caracteres estranhos
**Solução**: Abrir no Excel usando "Dados" > "De Texto/CSV" e selecionar UTF-8

---

## 📱 Responsividade

✅ **Desktop**: Gráficos lado a lado (2 colunas)
✅ **Tablet**: Gráficos empilhados (1 coluna)  
✅ **Mobile**: Interface otimizada, scroll suave

---

## 🎨 Personalização Visual

### Cores dos Gráficos

**Arquivo**: `app/Views/admin/dashboard_vendas.php`

**Evento 1** (Roxo):
```javascript
borderColor: '#667eea',
backgroundColor: 'rgba(102, 126, 234, 0.1)',
```

**Evento 2** (Roxo Escuro):
```javascript
borderColor: '#764ba2',
backgroundColor: 'rgba(118, 75, 162, 0.1)',
```

### Cores Sugeridas

```javascript
// Azul
borderColor: '#3b82f6',
backgroundColor: 'rgba(59, 130, 246, 0.1)',

// Verde
borderColor: '#10b981',
backgroundColor: 'rgba(16, 185, 129, 0.1)',

// Vermelho
borderColor: '#ef4444',
backgroundColor: 'rgba(239, 68, 68, 0.1)',

// Laranja
borderColor: '#f97316',
backgroundColor: 'rgba(249, 115, 22, 0.1)',
```

---

## 📈 Métricas Calculadas

### Diferença Absoluta
```
Diferença = Evento1 - Evento2
Exemplo: 1000 - 800 = +200 ingressos
```

### Diferença Percentual
```
% = ((Evento1 / Evento2) * 100) - 100
Exemplo: ((1000 / 800) * 100) - 100 = +25%
```

### Acumulado
```
Dia 1: 100 ingressos (acumulado = 100)
Dia 2: 150 ingressos (acumulado = 250)
Dia 3: 80 ingressos (acumulado = 330)
```

---

## 🔐 Segurança

### Níveis de Proteção

1. ✅ **Verificação de Admin** no controller
2. ✅ **Validação de Sessão** em cada request
3. ✅ **Sanitização de Inputs** via CodeIgniter
4. ✅ **Prepared Statements** nas queries
5. ✅ **Try-Catch** em todas as operações

### Como verificar acesso

```php
// app/Controllers/AdminDashboardVendas.php
private function isAdmin(): bool
{
    $session = session();
    
    // Verifica 3 formas diferentes
    if ($session->has('is_admin') && $session->get('is_admin') === true) {
        return true;
    }
    
    // ... outras verificações
    
    return false;
}
```

---

## 📚 Recursos Adicionais

### Documentação Completa
- `DASHBOARD_VENDAS_ADMIN.md`: Documentação técnica completa
- Comentários no código: Explicações inline

### Scripts SQL Base
- `sql/comparar_evolucao_vendas_eventos.sql`: MySQL 8.0+
- `sql/comparar_evolucao_vendas_eventos_mysql57.sql`: MySQL 5.7

### Bibliotecas
- [Chart.js Documentation](https://www.chartjs.org/docs/latest/)
- [CodeIgniter 4 Guide](https://codeigniter.com/user_guide/)

---

## ✨ Dicas de Uso

### 1. Análise de Performance
Compare evento atual com edição anterior para identificar:
- 🎯 Se vendas estão mais rápidas
- 📊 Picos de vendas (dias/horários)
- 💰 Crescimento de receita
- 📉 Períodos de baixa

### 2. Planejamento de Campanha
Use os gráficos para:
- 🚀 Identificar quando impulsionar vendas
- 📱 Programar campanhas em redes sociais
- 💌 Enviar e-mails marketing no timing certo
- 🎉 Criar urgência nos últimos dias

### 3. Relatórios Executivos
Export CSV e:
- 📧 Compartilhe com equipe
- 📊 Crie apresentações
- 💼 Envie para stakeholders
- 📈 Integre com outras ferramentas

---

## 🎯 Casos de Uso

### Exemplo 1: Evento anual repetido
```
Evento 1: MundoOtaku 2025 (atual)
Evento 2: MundoOtaku 2024 (anterior)
Objetivo: Ver se vendas estão melhores que ano passado
```

### Exemplo 2: Dois eventos simultâneos
```
Evento 1: Show KPOP - VIP
Evento 2: Show KPOP - Comum
Objetivo: Comparar performance de setores
```

### Exemplo 3: Teste de estratégia
```
Evento 1: Com campanha de influenciadores
Evento 2: Sem campanha de influenciadores
Objetivo: Medir impacto da estratégia
```

---

**Dashboard criado em:** Novembro 2025  
**Versão:** 1.0  
**Compatibilidade:** CodeIgniter 4 + MySQL 8.0+

---

**🚀 Comece agora: [/admin-dashboard-vendas](./admin-dashboard-vendas)**

