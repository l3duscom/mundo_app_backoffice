# 📊 Dashboard de Vendas Administrativo

## 📝 Visão Geral

Dashboard exclusivo para administradores que permite comparar vendas entre dois eventos de forma visual e detalhada, com gráficos interativos e exportação de dados.

## 🎯 Características

### ✅ Recursos Implementados

1. **Comparação Entre Dois Eventos**
   - Seleção de eventos via dropdown
   - Comparação lado a lado de métricas

2. **Visualizações Gráficas (Chart.js)**
   - 📈 Gráfico de Ingressos Acumulados (linha)
   - 💰 Gráfico de Receita Acumulada (linha)
   - 📊 Gráfico de Ingressos por Dia (barras)
   - 💵 Gráfico de Receita por Dia (barras)
   - 📉 Comparação por Períodos (barras agrupadas)

3. **KPIs Principais**
   - Total de ingressos por evento
   - Receita total por evento
   - Diferença absoluta e percentual
   - Indicadores visuais (positivo/negativo)

4. **Exportação de Dados**
   - Download em CSV (UTF-8 com BOM)
   - Dados completos da evolução diária

5. **Segurança**
   - Acesso restrito a administradores
   - Validação de sessão
   - Proteção contra acesso não autorizado

6. **Design Responsivo**
   - Funciona em desktop, tablet e mobile
   - Gráficos adaptáveis
   - Interface moderna com gradientes

## 📂 Arquivos Criados

### Backend

1. **`app/Models/VendasComparativasModel.php`**
   - Model exclusivo para consultas de vendas
   - Métodos para buscar dados comparativos
   - Query otimizadas com CTEs e Window Functions

2. **`app/Controllers/AdminDashboardVendas.php`**
   - Controller exclusivo para o dashboard
   - Validação de acesso admin
   - API para dados AJAX
   - Exportação CSV

### Frontend

3. **`app/Views/admin/dashboard_vendas.php`**
   - Interface completa do dashboard
   - Integração com Chart.js 4.4.0
   - CSS personalizado inline
   - JavaScript para interatividade

### Configuração

4. **`app/Config/Routes.php`** (atualizado)
   - Grupo de rotas: `/admin-dashboard-vendas`
   - 3 rotas: index, dados-comparativos, exportar-csv

5. **`DASHBOARD_VENDAS_ADMIN.md`** (este arquivo)
   - Documentação completa

## 🚀 Como Usar

### 1. Acesso

Acesse via URL:
```
https://seu-dominio.com/admin-dashboard-vendas
```

### 2. Autenticação

O sistema verifica se o usuário é admin através de:
- `session('is_admin')` = true/1
- `session('user_type')` = 'ADMIN' ou 'ADMINISTRATOR'
- `session('user_data')['is_admin']` = true

**Se não for admin:** Redirecionado para home com mensagem de erro.

### 3. Seleção de Eventos

1. Selecione o **Evento 1** (principal) no primeiro dropdown
2. Selecione o **Evento 2** (comparação) no segundo dropdown
3. Clique em **"Comparar"**

### 4. Visualização

Após carregar, você verá:

#### KPIs no topo:
- 📊 Total Ingressos Evento 1
- 📊 Total Ingressos Evento 2
- 📈 Diferença de Ingressos (com %)
- 💰 Receita Total Evento 1
- 💰 Receita Total Evento 2
- 💸 Diferença de Receita (com %)

#### 5 Gráficos:
1. **Ingressos Acumulados**: Compara evolução total
2. **Receita Acumulada**: Compara evolução financeira
3. **Ingressos por Dia**: Vendas diárias comparadas
4. **Receita por Dia**: Faturamento diário comparado
5. **Períodos**: Comparação por semanas/meses

### 5. Exportação

Clique no botão **"Exportar CSV"** para baixar:
- Arquivo CSV com dados diários
- Formato: `comparacao_vendas_17_vs_18_2025-11-25_143022.csv`
- Encoding: UTF-8 com BOM (abre corretamente no Excel)

## 🛠️ Tecnologias Utilizadas

### Frontend
- **Chart.js 4.4.0**: Biblioteca de gráficos
- **Bootstrap 5**: Framework CSS (herdado do tema)
- **Font Awesome**: Ícones
- **CSS Custom**: Gradientes, cards, animações
- **JavaScript Vanilla**: Sem dependências extras

### Backend
- **CodeIgniter 4**: Framework PHP
- **MySQL 8.0+**: Banco de dados
- **CTEs e Window Functions**: Para queries complexas

## 🔒 Segurança

### Implementada

1. **Validação de Admin**
   - Método `isAdmin()` no controller
   - 3 formas de validação (flexível)
   - Redirecionamento automático

2. **Validação de Inputs**
   - Verificação de IDs de eventos
   - Casting para inteiros
   - Mensagens de erro claras

3. **Tratamento de Erros**
   - Try-catch em todas as operações
   - Logs de erros
   - Mensagens genéricas para usuário

4. **SQL Injection**
   - Uso de prepared statements via CodeIgniter
   - Sanitização de inputs

### Recomendações Adicionais

Se desejar aumentar ainda mais a segurança:

1. **Adicionar CSRF Token**
   - Para requests AJAX
   - Configurar no BaseController

2. **Rate Limiting**
   - Limitar número de requests
   - Usar filtro do CodeIgniter

3. **Logging de Acessos**
   - Registrar quem acessa o dashboard
   - Auditoria de exportações

## 🎨 Personalização

### Cores do Gráfico

Edite em `app/Views/admin/dashboard_vendas.php`:

```javascript
// Evento 1
borderColor: '#667eea',
backgroundColor: 'rgba(102, 126, 234, 0.1)',

// Evento 2
borderColor: '#764ba2',
backgroundColor: 'rgba(118, 75, 162, 0.1)',
```

### Gradientes do Header

```css
.dashboard-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
```

### Períodos de Comparação

Edite em `app/Models/VendasComparativasModel.php`:

```sql
CASE 
    WHEN DATEDIFF(p.created_at, pv.inicio) <= 7 THEN '1. Primeira Semana'
    WHEN DATEDIFF(p.created_at, pv.inicio) <= 14 THEN '2. Segunda Semana'
    -- Adicione mais períodos aqui
END AS periodo
```

## 📊 Dados Considerados

### Incluídos
- ✅ Pedidos com status: `CONFIRMED`, `RECEIVED`, `RECEIVED_IN_CASH`
- ✅ Ingressos pagos

### Excluídos
- ❌ Cortesias (ticket_id = 608)
- ❌ Pedidos cancelados/pendentes
- ❌ Pedidos em outros status

### Para Alterar

Edite as constantes em `VendasComparativasModel.php`:

```php
// Status aceitos
$status = ['CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH'];

// ID da cortesia
$ticketCortesia = 608;
```

## 🐛 Troubleshooting

### Erro: "Acesso negado"
**Causa:** Usuário não é admin
**Solução:** Verificar sessão ou configurar campo `is_admin` corretamente

### Erro: "CTE não suportado"
**Causa:** MySQL < 8.0
**Solução:** Usar script SQL alternativo em `sql/comparar_evolucao_vendas_eventos_mysql57.sql`

### Gráficos não aparecem
**Causa:** Chart.js não carregou
**Solução:** Verificar CDN ou baixar Chart.js localmente

### CSV com caracteres estranhos
**Causa:** Encoding incorreto
**Solução:** Abrir com Excel usando "Importar Dados" e selecionar UTF-8

### Erro 404
**Causa:** Rota não registrada
**Solução:** Verificar `app/Config/Routes.php` e limpar cache de rotas

## 🔄 Atualizações Futuras (Sugestões)

### Recursos Extras (não implementados)

1. **Filtros Avançados**
   - Período personalizado
   - Tipo de ingresso específico
   - Status de pedido customizável

2. **Mais Visualizações**
   - Gráfico de pizza (distribuição)
   - Mapa de calor (horários de pico)
   - Funil de conversão

3. **Comparação Múltipla**
   - 3+ eventos simultaneamente
   - Benchmarking automático

4. **Alertas e Notificações**
   - Meta de vendas
   - Alertas de queda

5. **Cache de Dados**
   - Redis para queries pesadas
   - Atualização incremental

6. **API REST**
   - Endpoints JSON públicos
   - Integração com BI externo

## 📚 Dependências

### Instaladas Automaticamente
- CodeIgniter 4 (já presente)
- MySQL (já presente)

### CDN (online)
- Chart.js 4.4.0: https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js

### Opcional (para uso offline)
Baixe Chart.js e coloque em `public/assets/js/chart.min.js`, depois altere na view:
```html
<script src="<?= base_url('assets/js/chart.min.js') ?>"></script>
```

## 🧪 Testes

### Teste Manual

1. **Acesso Negado**
   - Fazer logout ou usar usuário não-admin
   - Tentar acessar `/admin-dashboard-vendas`
   - Deve redirecionar com erro

2. **Comparação Válida**
   - Login como admin
   - Selecionar dois eventos diferentes
   - Clicar em "Comparar"
   - Deve exibir gráficos

3. **Mesmo Evento**
   - Selecionar mesmo evento nos dois dropdowns
   - Deve exibir alerta

4. **Exportação CSV**
   - Após comparação
   - Clicar em "Exportar CSV"
   - Deve baixar arquivo

### Performance

- Queries otimizadas com índices
- Uso de CTEs para subqueries
- Limite de dados carregados (apenas eventos selecionados)

## 📖 Referências

- [Chart.js Docs](https://www.chartjs.org/docs/latest/)
- [CodeIgniter 4 Docs](https://codeigniter.com/user_guide/)
- [MySQL Window Functions](https://dev.mysql.com/doc/refman/8.0/en/window-functions.html)

## 📞 Suporte

Para dúvidas ou problemas:
1. Verificar logs em `writable/logs/`
2. Habilitar debug: `CI_ENVIRONMENT = development` em `.env`
3. Verificar console do navegador (F12)

---

**Dashboard desenvolvido exclusivamente para este projeto. NÃO reutilizar componentes em outras partes do sistema.**

