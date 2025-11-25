<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
:root {
    --ga-blue: #1a73e8;
    --ga-green: #0d652d;
    --ga-orange: #e37400;
    --ga-red: #d93025;
    --ga-purple: #9334e6;
    --ga-bg: #f8f9fa;
    --ga-card: #ffffff;
    --ga-border: #dadce0;
}

.dashboard-container {
    background: var(--ga-bg);
    min-height: 100vh;
    padding: 2rem 1rem;
}

.dashboard-header {
    background: var(--ga-card);
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    border: 1px solid var(--ga-border);
}

.metric-card {
    background: var(--ga-card);
    border-radius: 8px;
    padding: 1rem;
    border: 1px solid var(--ga-border);
    transition: box-shadow 0.3s ease;
}

.metric-card-compact {
    height: 140px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.metric-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.metric-label {
    font-size: 0.875rem;
    color: #5f6368;
    font-weight: 500;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.metric-card-compact .metric-label {
    margin-bottom: 0.25rem;
}

.metric-value {
    font-size: 2rem;
    font-weight: 400;
    color: #202124;
    margin-bottom: 0.5rem;
    line-height: 1.2;
}

.metric-card-compact .metric-value {
    font-size: 2.5rem;
    margin-bottom: 0;
    line-height: 1;
}

/* Valores menores para cards com números grandes */
#receitaTotal,
#receitaHoje {
    font-size: 1.75rem !important;
}

#ticketMedio {
    font-size: 2rem !important;
}

.metric-card-ingressos {
    height: 303px;
    display: flex;
    flex-direction: column;
}

@media (max-width: 991px) {
    .metric-card-ingressos {
        height: auto;
    }
}

.metric-change {
    font-size: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
    margin-top: 0.25rem;
}

.metric-change.positive {
    color: var(--ga-green);
}

.metric-change.negative {
    color: var(--ga-red);
}

.sub-metric-card-main {
    background: var(--ga-card);
    border: 2px solid var(--ga-blue);
    border-radius: 8px;
    padding: 0.75rem;
    color: #202124;
    margin-bottom: 0.5rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-height: 100px;
}

.sub-metric-card {
    background: var(--ga-bg);
    border-radius: 6px;
    padding: 0.4rem 0.5rem;
    text-align: center;
    border: 1px solid var(--ga-border);
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-height: 70px;
}

.sub-metric-label {
    font-size: 0.7rem;
    font-weight: 500;
    color: #5f6368;
    margin-bottom: 0.35rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.sub-metric-value {
    font-size: 1.75rem;
    font-weight: 600;
    line-height: 1;
    margin-bottom: 0.15rem;
}

.sub-metric-label-small {
    font-size: 0.65rem;
    color: #5f6368;
    font-weight: 500;
    margin-bottom: 0.35rem;
    text-transform: uppercase;
}

.sub-metric-value-small {
    font-size: 1.25rem;
    font-weight: 600;
    color: #202124;
}

.metric-card-today {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    border: 2px solid #2196f3;
}

.metric-card-today .metric-label {
    color: #1565c0;
}

.metric-card-today .metric-value {
    color: #0d47a1;
}

.metric-card-revenue {
    background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
    border: 2px solid #4caf50;
}

.metric-card-revenue .metric-label {
    color: #2e7d32;
}

.metric-card-revenue .metric-value {
    color: #1b5e20;
}

.metric-card-pending {
    background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
    border: 2px solid #ff9800;
}

.metric-card-pending .metric-label {
    color: #e65100;
}

.metric-card-pending .metric-value {
    color: #e65100;
}

.chart-card {
    background: var(--ga-card);
    border-radius: 8px;
    padding: 1.5rem;
    border: 1px solid var(--ga-border);
    margin-bottom: 2rem;
}

.chart-card-title {
    font-size: 1rem;
    font-weight: 500;
    color: #202124;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.period-selector {
    display: flex;
    gap: 0.5rem;
}

.period-btn {
    padding: 0.375rem 0.75rem;
    border: 1px solid var(--ga-border);
    background: var(--ga-card);
    border-radius: 4px;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
}

.period-btn:hover {
    background: var(--ga-bg);
}

.period-btn.active {
    background: var(--ga-blue);
    color: white;
    border-color: var(--ga-blue);
}

.table-card {
    background: var(--ga-card);
    border-radius: 8px;
    border: 1px solid var(--ga-border);
    overflow: hidden;
}

.table-card-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--ga-border);
    font-weight: 500;
    color: #202124;
}

.ga-table {
    width: 100%;
}

.ga-table thead th {
    background: var(--ga-bg);
    padding: 0.75rem 1.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #5f6368;
    text-align: left;
    border-bottom: 1px solid var(--ga-border);
}

.ga-table tbody td {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--ga-border);
    font-size: 0.875rem;
    color: #202124;
}

.ga-table tbody tr:hover {
    background: var(--ga-bg);
}

.loading-spinner {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 3rem;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid var(--ga-border);
    border-top-color: var(--ga-blue);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.badge-status {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-pix {
    background: #32bcad;
    color: white;
}

.badge-credit {
    background: var(--ga-purple);
    color: white;
}

.refresh-btn {
    padding: 0.5rem 1rem;
    background: var(--ga-blue);
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 0.875rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: background 0.2s;
}

.refresh-btn:hover {
    background: #1557b0;
}

.refresh-btn i {
    transition: transform 0.5s;
}

.refresh-btn.refreshing i {
    animation: spin 0.5s linear infinite;
}

#refreshBtnText {
    font-weight: 500;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.auto-refresh-toggle {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #5f6368;
}

@media (max-width: 768px) {
    .metric-value {
        font-size: 1.5rem;
    }
    
    .period-selector {
        flex-wrap: wrap;
    }
}
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="dashboard-container">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h3 class="mb-1">📊 Dashboard de Vendas</h3>
                <p class="mb-0" style="color: #5f6368;">
                    <strong><?= esc($evento->nome) ?></strong> • Atualizado agora
                </p>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <div class="auto-refresh-toggle">
                    <input type="checkbox" id="autoRefreshToggle" class="form-check-input" checked>
                    <label for="autoRefreshToggle">Auto-atualizar</label>
                </div>
                <button class="refresh-btn" id="refreshBtn">
                    <i class="bi bi-arrow-clockwise"></i>
                    <span id="refreshBtnText">Atualizar</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="loading-spinner">
        <div class="spinner"></div>
    </div>

    <!-- Dashboard Content -->
    <div id="dashboardContent" style="display: none;">
        <!-- Métricas Principais -->
        <div class="row g-3 mb-4">
            <!-- Card de Ingressos com 3 subcards -->
            <div class="col-lg-6">
                <div class="metric-card metric-card-ingressos">
                    <div class="metric-label mb-2">📊 Ingressos</div>
                    <div class="row g-2">
                        <div class="col-12">
                            <div class="sub-metric-card-main">
                                <div class="sub-metric-label">Ingressos Vendidos</div>
                                <div class="sub-metric-value" id="totalIngressos">-</div>
                                <div class="metric-change" id="changeIngressos">-</div>
                            </div>
                        </div>
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
            <div class="col-md-6 col-lg-2">
                <div class="metric-card metric-card-today metric-card-compact mb-3">
                    <div class="metric-label">📅 Ingressos Hoje</div>
                    <div class="metric-value" id="ingressosHoje">-</div>
                </div>
                <div class="metric-card metric-card-compact">
                    <div class="metric-label">Ticket Médio</div>
                    <div class="metric-value" id="ticketMedio">-</div>
                    <div class="metric-change" id="changeTicket">-</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-2">
                <div class="metric-card metric-card-revenue metric-card-compact mb-3">
                    <div class="metric-label">💰 Receita Hoje</div>
                    <div class="metric-value" id="receitaHoje">-</div>
                </div>
                <div class="metric-card metric-card-compact">
                    <div class="metric-label">Receita Total</div>
                    <div class="metric-value" id="receitaTotal">-</div>
                    <div class="metric-change" id="changeReceita">-</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-2">
                <div class="metric-card metric-card-pending metric-card-compact mb-3">
                    <div class="metric-label">⏳ Pendentes</div>
                    <div class="metric-value" id="pedidosPendentes">-</div>
                </div>
                <div class="metric-card metric-card-compact">
                    <div class="metric-label">Taxa de Conversão</div>
                    <div class="metric-value" id="taxaConversao">-</div>
                    <div class="metric-change" id="changeConversao">-</div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row">
            <!-- Evolução de Vendas -->
            <div class="col-lg-8 mb-4">
                <div class="chart-card">
                    <div class="chart-card-title">
                        <span>Evolução de Vendas</span>
                        <div class="period-selector">
                            <button class="period-btn" data-period="7">7 dias</button>
                            <button class="period-btn active" data-period="30">30 dias</button>
                            <button class="period-btn" data-period="90">90 dias</button>
                        </div>
                    </div>
                    <div style="position: relative; height: 350px;">
                        <canvas id="chartEvolucao"></canvas>
                    </div>
                </div>
            </div>

            <!-- Vendas por Método -->
            <div class="col-lg-4 mb-4">
                <div class="chart-card">
                    <div class="chart-card-title">
                        <span>Métodos de Pagamento</span>
                    </div>
                    <div style="position: relative; height: 300px;">
                        <canvas id="chartMetodos"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Top Ingressos -->
            <div class="col-lg-6 mb-4">
                <div class="table-card">
                    <div class="table-card-header">🎫 Ingressos Mais Vendidos</div>
                    <div style="max-height: 400px; overflow-y: auto;">
                        <table class="ga-table">
                            <thead>
                                <tr>
                                    <th>Ingresso</th>
                                    <th style="text-align: right;">Qtd.</th>
                                    <th style="text-align: right;">Receita</th>
                                </tr>
                            </thead>
                            <tbody id="topIngressosBody">
                                <tr><td colspan="3" style="text-align: center; padding: 2rem;">Carregando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Vendas Recentes -->
            <div class="col-lg-6 mb-4">
                <div class="table-card">
                    <div class="table-card-header">⚡ Vendas Recentes</div>
                    <div style="max-height: 400px; overflow-y: auto;">
                        <table class="ga-table">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Valor</th>
                                    <th>Método</th>
                                    <th>Hora</th>
                                </tr>
                            </thead>
                            <tbody id="vendasRecentesBody">
                                <tr><td colspan="4" style="text-align: center; padding: 2rem;">Carregando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
const EVENTO_ID = <?= $evento_id ?>;
let currentPeriod = 30;
let autoRefreshInterval = null;
let charts = {};

// Carregar dados
async function loadDashboard() {
    const refreshBtn = document.getElementById('refreshBtn');
    refreshBtn.classList.add('refreshing');
    
    try {
        const response = await fetch(`<?= site_url('dashboard-vendas/get-dados') ?>?evento_id=${EVENTO_ID}&periodo=${currentPeriod}`);
        const result = await response.json();
        
        console.log('Dados recebidos:', result);
        
        if (result.success) {
            updateMetrics(result.data);
            updateCharts(result.data);
            updateTables(result.data);
            
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('dashboardContent').style.display = 'block';
        } else {
            console.error('Erro na resposta:', result.error);
            alert('Erro ao carregar dados: ' + (result.error || 'Desconhecido'));
        }
    } catch (error) {
        console.error('Erro ao carregar dashboard:', error);
        alert('Erro ao carregar dashboard. Veja o console para mais detalhes.');
    } finally {
        refreshBtn.classList.remove('refreshing');
        // Reinicia o countdown após carregar
        const autoRefreshToggle = document.getElementById('autoRefreshToggle');
        if (autoRefreshToggle.checked) {
            startCountdown();
        }
    }
}

// Atualizar métricas
function updateMetrics(data) {
    const metricas = data.metricas_gerais;
    const conversao = data.taxa_conversao;
    
    document.getElementById('totalIngressos').textContent = formatNumber(metricas.total_ingressos || 0);
    document.getElementById('totalCortesias').textContent = formatNumber(metricas.total_cortesias || 0);
    document.getElementById('totalComCortesias').textContent = formatNumber(metricas.total_com_cortesias || 0);
    document.getElementById('ingressosHoje').textContent = formatNumber(metricas.ingressos_hoje || 0);
    document.getElementById('receitaHoje').textContent = formatCurrency(metricas.receita_hoje || 0);
    document.getElementById('pedidosPendentes').textContent = formatNumber(metricas.pedidos_pendentes || 0);
    document.getElementById('receitaTotal').textContent = formatCurrency(metricas.receita_total || 0);
    document.getElementById('ticketMedio').textContent = formatCurrency(metricas.ticket_medio || 0);
    document.getElementById('taxaConversao').textContent = (conversao.taxa_conversao || 0) + '%';
    
    // Comparação com período anterior
    const comparacao = data.comparacao_periodo;
    if (comparacao.length === 2) {
        const atual = comparacao.find(p => p.periodo === 'periodo_atual');
        const anterior = comparacao.find(p => p.periodo === 'periodo_anterior');
        
        if (atual && anterior) {
            const diffIngressos = calcPercentDiff(atual.ingressos, anterior.ingressos);
            const diffReceita = calcPercentDiff(atual.receita, anterior.receita);
            
            updateChange('changeIngressos', diffIngressos);
            updateChange('changeReceita', diffReceita);
        }
    }
}

function updateChange(elementId, percent) {
    const element = document.getElementById(elementId);
    const isPositive = percent >= 0;
    element.className = `metric-change ${isPositive ? 'positive' : 'negative'}`;
    element.innerHTML = `
        <i class="bi bi-arrow-${isPositive ? 'up' : 'down'}"></i>
        ${Math.abs(percent).toFixed(1)}% vs período anterior
    `;
}

function calcPercentDiff(current, previous) {
    if (!previous || previous === 0) return 0;
    return ((current - previous) / previous) * 100;
}

// Atualizar gráficos
function updateCharts(data) {
    // Gráfico de Evolução
    const evolucaoData = data.evolucao_diaria || [];
    
    // Validar se há dados
    if (evolucaoData.length === 0) {
        console.warn('Sem dados de evolução diária');
        return;
    }
    
    const labels = evolucaoData.map(d => formatDate(d.data));
    const receitas = evolucaoData.map(d => parseFloat(d.receita || 0));
    const ingressos = evolucaoData.map(d => parseInt(d.ingressos || 0));
    
    console.log('Dados do gráfico:', { labels, receitas, ingressos });
    
    // Destruir gráfico anterior se existir
    if (charts.evolucao) {
        try {
            charts.evolucao.destroy();
        } catch (e) {
            console.warn('Erro ao destruir gráfico:', e);
        }
    }
    
    const canvas = document.getElementById('chartEvolucao');
    if (!canvas) {
        console.error('Canvas chartEvolucao não encontrado!');
        return;
    }
    
    const ctxEvolucao = canvas.getContext('2d');
    charts.evolucao = new Chart(ctxEvolucao, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Receita (R$)',
                    data: receitas,
                    borderColor: '#1a73e8',
                    backgroundColor: 'rgba(26, 115, 232, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y'
                },
                {
                    label: 'Ingressos',
                    data: ingressos,
                    borderColor: '#0d652d',
                    backgroundColor: 'rgba(13, 101, 45, 0.1)',
                    tension: 0.4,
                    fill: true,
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
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });
    
    // Gráfico de Métodos
    const metodosData = data.vendas_por_metodo || [];
    
    console.log('Métodos de pagamento recebidos:', metodosData);
    console.log('Tipo de vendas_por_metodo:', typeof data.vendas_por_metodo);
    console.log('É array?', Array.isArray(data.vendas_por_metodo));
    
    if (!metodosData || metodosData.length === 0) {
        console.warn('Sem dados de métodos de pagamento');
        // Mostrar mensagem no card
        const canvasMetodos = document.getElementById('chartMetodos');
        if (canvasMetodos) {
            const parent = canvasMetodos.parentElement;
            parent.innerHTML = '<div style="text-align: center; padding: 3rem; color: #999;">Sem dados de métodos de pagamento</div>';
        }
        return;
    }
    
    const metodosLabels = metodosData.map(m => {
        console.log('Processando método:', m.metodo);
        const metodo = m.metodo ? m.metodo.toLowerCase() : '';
        if (metodo === 'pix') return 'PIX';
        if (metodo === 'credit_card' || metodo === 'credito' || metodo === 'cartão') return 'Cartão';
        if (metodo === 'boleto') return 'Boleto';
        return m.metodo || 'Outro';
    });
    const metodosValues = metodosData.map(m => parseFloat(m.receita || 0));
    
    console.log('Labels processados:', metodosLabels);
    console.log('Values processados:', metodosValues);
    
    if (charts.metodos) {
        try {
            charts.metodos.destroy();
        } catch (e) {
            console.warn('Erro ao destruir gráfico métodos:', e);
        }
    }
    
    const canvasMetodos = document.getElementById('chartMetodos');
    if (!canvasMetodos) {
        console.error('Canvas chartMetodos não encontrado!');
        return;
    }
    
    const ctxMetodos = canvasMetodos.getContext('2d');
    charts.metodos = new Chart(ctxMetodos, {
        type: 'doughnut',
        data: {
            labels: metodosLabels,
            datasets: [{
                data: metodosValues,
                backgroundColor: ['#32bcad', '#9334e6', '#e37400', '#1a73e8']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 1,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Atualizar tabelas
function updateTables(data) {
    // Top Ingressos
    const topIngressos = data.top_ingressos;
    const topBody = document.getElementById('topIngressosBody');
    
    if (topIngressos.length === 0) {
        topBody.innerHTML = '<tr><td colspan="3" style="text-align: center; padding: 2rem;">Nenhum dado disponível</td></tr>';
    } else {
        topBody.innerHTML = topIngressos.map(item => `
            <tr>
                <td>${item.ingresso}</td>
                <td style="text-align: right;"><strong>${item.quantidade}</strong></td>
                <td style="text-align: right;">${formatCurrency(item.receita_total)}</td>
            </tr>
        `).join('');
    }
    
    // Vendas Recentes
    const vendasRecentes = data.vendas_recentes || [];
    const vendasBody = document.getElementById('vendasRecentesBody');
    
    console.log('Vendas recentes recebidas:', vendasRecentes);
    
    if (!vendasBody) {
        console.error('Elemento vendasRecentesBody não encontrado!');
        return;
    }
    
    if (vendasRecentes.length === 0) {
        vendasBody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 2rem; color: #999;">Nenhuma venda recente</td></tr>';
    } else {
        vendasBody.innerHTML = vendasRecentes.map(venda => {
            let metodo = '<span class="badge-status" style="background: #666; color: white;">Outro</span>';
            
            if (venda.forma_pagamento === 'pix' || venda.forma_pagamento === 'PIX') {
                metodo = '<span class="badge-status badge-pix">PIX</span>';
            } else if (venda.forma_pagamento === 'credit_card' || venda.forma_pagamento === 'CREDIT_CARD' || venda.forma_pagamento === 'credito') {
                metodo = '<span class="badge-status badge-credit">Cartão</span>';
            }
            
            return `
                <tr>
                    <td>${venda.cliente_nome || 'Cliente'}</td>
                    <td><strong>${formatCurrency(venda.total)}</strong></td>
                    <td>${metodo}</td>
                    <td>${formatTime(venda.created_at)}</td>
                </tr>
            `;
        }).join('');
    }
}

// Seletores de período
document.querySelectorAll('.period-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        currentPeriod = parseInt(this.dataset.period);
        loadDashboard();
    });
});

// Timer de countdown
let countdownInterval = null;
let secondsRemaining = 30;

function updateRefreshButton() {
    const refreshBtnText = document.getElementById('refreshBtnText');
    const autoRefreshToggle = document.getElementById('autoRefreshToggle');
    
    if (autoRefreshToggle.checked) {
        refreshBtnText.textContent = `Atualizar (${secondsRemaining}s)`;
    } else {
        refreshBtnText.textContent = 'Atualizar';
    }
}

function startCountdown() {
    secondsRemaining = 30;
    updateRefreshButton();
    
    if (countdownInterval) {
        clearInterval(countdownInterval);
    }
    
    const autoRefreshToggle = document.getElementById('autoRefreshToggle');
    if (autoRefreshToggle.checked) {
        countdownInterval = setInterval(() => {
            secondsRemaining--;
            updateRefreshButton();
            
            if (secondsRemaining <= 0) {
                secondsRemaining = 30;
            }
        }, 1000);
    }
}

// Botão de refresh
document.getElementById('refreshBtn').addEventListener('click', function() {
    loadDashboard();
    startCountdown();
});

// Auto-refresh
document.getElementById('autoRefreshToggle').addEventListener('change', function() {
    if (this.checked) {
        autoRefreshInterval = setInterval(loadDashboard, 30000); // 30 segundos
        startCountdown();
    } else {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
            autoRefreshInterval = null;
        }
        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }
        updateRefreshButton();
    }
});

// Formatação
function formatCurrency(value) {
    return 'R$ ' + parseFloat(value || 0).toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function formatNumber(value) {
    return parseInt(value || 0).toLocaleString('pt-BR');
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
}

function formatTime(dateTimeStr) {
    const date = new Date(dateTimeStr);
    return date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
}

// Carregar ao iniciar
loadDashboard();

// Iniciar auto-refresh automaticamente
const autoRefreshToggle = document.getElementById('autoRefreshToggle');
if (autoRefreshToggle.checked) {
    autoRefreshInterval = setInterval(loadDashboard, 30000); // 30 segundos
    startCountdown();
}
</script>

<?php echo $this->endSection() ?>

