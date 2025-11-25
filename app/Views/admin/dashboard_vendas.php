<?= $this->extend('Layout/principal') ?>

<?= $this->section('titulo') ?>Dashboard de Vendas<?= $this->endSection() ?>

<?= $this->section('conteudo') ?>

<style>
/* ========================================
   DASHBOARD VENDAS - ESTILOS EXCLUSIVOS
   ======================================== */

.dashboard-vendas-container {
    background: #f8f9fa;
    min-height: 100vh;
    padding: 20px 0;
}

.dashboard-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.dashboard-header h1 {
    margin: 0;
    font-size: 2rem;
    font-weight: 700;
}

.dashboard-header p {
    margin: 10px 0 0 0;
    opacity: 0.9;
    font-size: 1rem;
}

.filtros-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
}

.filtros-card h3 {
    color: #667eea;
    font-size: 1.3rem;
    margin-bottom: 20px;
    font-weight: 600;
}

.form-select-custom {
    border: 2px solid #e0e7ff;
    border-radius: 10px;
    padding: 12px 15px;
    font-size: 1rem;
    transition: all 0.3s;
}

.form-select-custom:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
}

.btn-comparar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    padding: 12px 40px;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 10px;
    transition: all 0.3s;
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

.btn-comparar:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

.btn-exportar {
    background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
    border: none;
    color: white;
    padding: 12px 30px;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 10px;
    transition: all 0.3s;
    box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
}

.btn-exportar:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(76, 175, 80, 0.4);
}

.loading-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

.loading-overlay.active {
    display: flex;
}

.loading-spinner {
    text-align: center;
    color: white;
}

.loading-spinner .spinner-border {
    width: 4rem;
    height: 4rem;
    border-width: 0.4rem;
}

.resultados-section {
    display: none;
}

.resultados-section.active {
    display: block;
}

.kpi-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s;
}

.kpi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
}

.kpi-card.positive {
    border-left: 5px solid #4CAF50;
}

.kpi-card.negative {
    border-left: 5px solid #f44336;
}

.kpi-card.neutral {
    border-left: 5px solid #667eea;
}

.kpi-label {
    font-size: 0.9rem;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 10px;
}

.kpi-value {
    font-size: 2rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 5px;
}

.kpi-change {
    font-size: 1rem;
    font-weight: 600;
}

.kpi-change.positive {
    color: #4CAF50;
}

.kpi-change.negative {
    color: #f44336;
}

.chart-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
}

.chart-card h4 {
    color: #333;
    font-size: 1.3rem;
    margin-bottom: 20px;
    font-weight: 600;
}

.chart-container {
    position: relative;
    height: 400px;
}

.evento-label {
    display: inline-block;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    margin-right: 10px;
}

.evento-label.ev1 {
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
}

.evento-label.ev2 {
    background: rgba(118, 75, 162, 0.1);
    color: #764ba2;
}

.alert-info-custom {
    background: linear-gradient(135deg, #e0e7ff 0%, #f5f3ff 100%);
    border: 2px solid #667eea;
    border-radius: 10px;
    padding: 20px;
    color: #333;
    margin-bottom: 30px;
}

@media (max-width: 768px) {
    .dashboard-header h1 {
        font-size: 1.5rem;
    }
    
    .kpi-value {
        font-size: 1.5rem;
    }
    
    .chart-container {
        height: 300px;
    }
}
</style>

<div class="dashboard-vendas-container">
    <div class="container-fluid">
        
        <!-- Header -->
        <div class="dashboard-header">
            <h1><i class="fas fa-chart-line me-2"></i>Dashboard de Vendas Comparativas</h1>
            <p>Análise detalhada e comparação entre eventos</p>
        </div>
        
        <!-- Filtros -->
        <div class="filtros-card">
            <h3><i class="fas fa-filter me-2"></i>Selecione os Eventos para Comparar</h3>
            
            <div class="row align-items-end">
                <div class="col-md-5 mb-3">
                    <label for="evento1" class="form-label fw-bold">Evento 1 (Principal)</label>
                    <select id="evento1" class="form-select form-select-custom">
                        <option value="">Selecione um evento...</option>
                        <?php foreach ($eventos as $evento): ?>
                            <option value="<?= $evento['id'] ?>">
                                <?= esc($evento['nome']) ?> - <?= esc($evento['data_inicio']) ?>
                                (<?= $evento['total_pedidos'] ?> pedidos)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-5 mb-3">
                    <label for="evento2" class="form-label fw-bold">Evento 2 (Comparação)</label>
                    <select id="evento2" class="form-select form-select-custom">
                        <option value="">Selecione um evento...</option>
                        <?php foreach ($eventos as $evento): ?>
                            <option value="<?= $evento['id'] ?>">
                                <?= esc($evento['nome']) ?> - <?= esc($evento['data_inicio']) ?>
                                (<?= $evento['total_pedidos'] ?> pedidos)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2 mb-3">
                    <button id="btnComparar" class="btn btn-comparar w-100">
                        <i class="fas fa-chart-bar me-2"></i>Comparar
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Loading Overlay -->
        <div class="loading-overlay" id="loadingOverlay">
            <div class="loading-spinner">
                <div class="spinner-border text-light" role="status"></div>
                <p class="mt-3">Carregando dados...</p>
            </div>
        </div>
        
        <!-- Resultados -->
        <div class="resultados-section" id="resultadosSection">
            
            <!-- Info dos Eventos -->
            <div class="alert-info-custom" id="infoEventos">
                <div class="row">
                    <div class="col-md-6">
                        <span class="evento-label ev1">Evento 1</span>
                        <strong id="nomeEvento1">-</strong>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <span class="evento-label ev2">Evento 2</span>
                        <strong id="nomeEvento2">-</strong>
                    </div>
                </div>
            </div>
            
            <!-- KPIs Principais -->
            <div class="row" id="kpisContainer">
                <!-- KPIs serão inseridos aqui via JavaScript -->
            </div>
            
            <!-- Botão Exportar -->
            <div class="text-end mb-3">
                <button id="btnExportar" class="btn btn-exportar" style="display: none;">
                    <i class="fas fa-file-excel me-2"></i>Exportar CSV
                </button>
            </div>
            
            <!-- Gráficos -->
            <div class="row">
                <!-- Gráfico: Evolução de Ingressos Acumulados -->
                <div class="col-lg-6">
                    <div class="chart-card">
                        <h4><i class="fas fa-ticket-alt me-2"></i>Ingressos Vendidos (Acumulado)</h4>
                        <div class="chart-container">
                            <canvas id="chartIngressosAcumulados"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Gráfico: Evolução de Receita Acumulada -->
                <div class="col-lg-6">
                    <div class="chart-card">
                        <h4><i class="fas fa-dollar-sign me-2"></i>Receita (Acumulada)</h4>
                        <div class="chart-container">
                            <canvas id="chartReceitaAcumulada"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Gráfico: Ingressos por Dia -->
                <div class="col-lg-6">
                    <div class="chart-card">
                        <h4><i class="fas fa-calendar-day me-2"></i>Ingressos Vendidos por Dia</h4>
                        <div class="chart-container">
                            <canvas id="chartIngressosDia"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Gráfico: Receita por Dia -->
                <div class="col-lg-6">
                    <div class="chart-card">
                        <h4><i class="fas fa-money-bill-wave me-2"></i>Receita por Dia</h4>
                        <div class="chart-container">
                            <canvas id="chartReceitaDia"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Gráfico: Comparação por Períodos -->
                <div class="col-12">
                    <div class="chart-card">
                        <h4><i class="fas fa-chart-bar me-2"></i>Comparação por Períodos (Ingressos)</h4>
                        <div class="chart-container">
                            <canvas id="chartPeriodos"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// ========================================
// DASHBOARD VENDAS - JAVASCRIPT
// ========================================

let chartsInstances = {};
let dadosAtuais = null;

document.addEventListener('DOMContentLoaded', function() {
    
    // Botão Comparar
    document.getElementById('btnComparar').addEventListener('click', function() {
        const evento1Id = document.getElementById('evento1').value;
        const evento2Id = document.getElementById('evento2').value;
        
        if (!evento1Id || !evento2Id) {
            alert('Por favor, selecione os dois eventos para comparar.');
            return;
        }
        
        if (evento1Id === evento2Id) {
            alert('Por favor, selecione eventos diferentes.');
            return;
        }
        
        carregarDadosComparativos(evento1Id, evento2Id);
    });
    
    // Botão Exportar
    document.getElementById('btnExportar').addEventListener('click', function() {
        const evento1Id = document.getElementById('evento1').value;
        const evento2Id = document.getElementById('evento2').value;
        
        if (evento1Id && evento2Id) {
            window.location.href = `<?= base_url('admin-dashboard-vendas/exportar-csv') ?>?evento1_id=${evento1Id}&evento2_id=${evento2Id}`;
        }
    });
    
});

/**
 * Carrega dados comparativos via AJAX
 */
function carregarDadosComparativos(evento1Id, evento2Id) {
    // Mostrar loading
    document.getElementById('loadingOverlay').classList.add('active');
    
    fetch(`<?= base_url('admin-dashboard-vendas/dados-comparativos') ?>?evento1_id=${evento1Id}&evento2_id=${evento2Id}`)
        .then(response => {
            // Debug: Log do status
            console.log('Response Status:', response.status);
            console.log('Response OK:', response.ok);
            
            // Se não for 200, tenta ler como texto para ver o erro
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Response Text:', text);
                    throw new Error(`HTTP ${response.status}: ${text.substring(0, 200)}`);
                });
            }
            
            // Tenta ler como texto primeiro para debug
            return response.text().then(text => {
                console.log('Response Text:', text.substring(0, 500));
                
                // Verifica se é vazio
                if (!text || text.trim() === '') {
                    throw new Error('Resposta vazia do servidor');
                }
                
                // Tenta fazer parse do JSON
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('JSON Parse Error:', e);
                    console.error('Text received:', text);
                    throw new Error('Resposta não é um JSON válido: ' + e.message);
                }
            });
        })
        .then(result => {
            console.log('Result:', result);
            
            if (result.success) {
                dadosAtuais = result.data;
                renderizarDashboard(result.data);
                document.getElementById('resultadosSection').classList.add('active');
                document.getElementById('btnExportar').style.display = 'inline-block';
            } else {
                alert('Erro ao carregar dados: ' + (result.message || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Erro completo:', error);
            alert('Erro ao carregar dados:\n\n' + error.message + '\n\nVerifique o console (F12) para mais detalhes.');
        })
        .finally(() => {
            document.getElementById('loadingOverlay').classList.remove('active');
        });
}

/**
 * Renderiza todo o dashboard com os dados recebidos
 */
function renderizarDashboard(data) {
    // Atualizar info dos eventos
    const visaoGeral = data.visao_geral;
    if (visaoGeral && visaoGeral.length >= 2) {
        document.getElementById('nomeEvento1').textContent = visaoGeral[0].evento_nome + ' (' + visaoGeral[0].data_evento + ')';
        document.getElementById('nomeEvento2').textContent = visaoGeral[1].evento_nome + ' (' + visaoGeral[1].data_evento + ')';
    }
    
    // Renderizar KPIs
    renderizarKPIs(data.resumo_executivo);
    
    // Renderizar gráficos
    renderizarGraficos(data);
}

/**
 * Renderiza os KPIs principais
 */
function renderizarKPIs(resumo) {
    const container = document.getElementById('kpisContainer');
    
    const diffIngressos = parseInt(resumo.diff_ingressos || 0);
    const percIngressos = parseFloat(resumo.perc_evolucao_ingressos || 0);
    const diffReceita = parseFloat(resumo.diff_receita || 0);
    const percReceita = parseFloat(resumo.perc_evolucao_receita || 0);
    
    const kpis = [
        {
            label: 'Total Ingressos - Evento 1',
            value: formatNumber(resumo.total_ingressos_ev1),
            change: null,
            type: 'neutral'
        },
        {
            label: 'Total Ingressos - Evento 2',
            value: formatNumber(resumo.total_ingressos_ev2),
            change: null,
            type: 'neutral'
        },
        {
            label: 'Diferença de Ingressos',
            value: (diffIngressos >= 0 ? '+' : '') + formatNumber(diffIngressos),
            change: percIngressos ? (percIngressos >= 0 ? '+' : '') + percIngressos.toFixed(2) + '%' : null,
            type: diffIngressos >= 0 ? 'positive' : 'negative'
        },
        {
            label: 'Receita Total - Evento 1',
            value: 'R$ ' + formatMoney(resumo.receita_ev1),
            change: null,
            type: 'neutral'
        },
        {
            label: 'Receita Total - Evento 2',
            value: 'R$ ' + formatMoney(resumo.receita_ev2),
            change: null,
            type: 'neutral'
        },
        {
            label: 'Diferença de Receita',
            value: 'R$ ' + (diffReceita >= 0 ? '+' : '') + formatMoney(Math.abs(diffReceita)),
            change: percReceita ? (percReceita >= 0 ? '+' : '') + percReceita.toFixed(2) + '%' : null,
            type: diffReceita >= 0 ? 'positive' : 'negative'
        }
    ];
    
    let html = '';
    kpis.forEach(kpi => {
        html += `
            <div class="col-lg-4 col-md-6">
                <div class="kpi-card ${kpi.type}">
                    <div class="kpi-label">${kpi.label}</div>
                    <div class="kpi-value">${kpi.value}</div>
                    ${kpi.change ? `<div class="kpi-change ${kpi.type}">${kpi.change}</div>` : ''}
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

/**
 * Renderiza todos os gráficos
 */
function renderizarGraficos(data) {
    const evolucao = data.evolucao_diaria;
    const periodos = data.comparacao_periodos;
    
    // Preparar dados
    const labels = evolucao.map(item => 'Dia ' + item.dia_venda);
    const ingressosAcumEv1 = evolucao.map(item => item.ingressos_acum_ev1);
    const ingressosAcumEv2 = evolucao.map(item => item.ingressos_acum_ev2);
    const receitaAcumEv1 = evolucao.map(item => parseFloat(item.receita_acum_ev1));
    const receitaAcumEv2 = evolucao.map(item => parseFloat(item.receita_acum_ev2));
    const ingressosDiaEv1 = evolucao.map(item => item.ingressos_dia_ev1);
    const ingressosDiaEv2 = evolucao.map(item => item.ingressos_dia_ev2);
    const receitaDiaEv1 = evolucao.map(item => parseFloat(item.receita_dia_ev1));
    const receitaDiaEv2 = evolucao.map(item => parseFloat(item.receita_dia_ev2));
    
    const labelsPeriodos = periodos.map(item => item.periodo.replace(/^\d+\.\s*/, ''));
    const ingressosPeriodosEv1 = periodos.map(item => item.ingressos_ev1);
    const ingressosPeriodosEv2 = periodos.map(item => item.ingressos_ev2);
    
    // Destruir gráficos anteriores
    Object.values(chartsInstances).forEach(chart => chart.destroy());
    chartsInstances = {};
    
    // Gráfico: Ingressos Acumulados
    chartsInstances.ingressosAcum = new Chart(
        document.getElementById('chartIngressosAcumulados'),
        {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Evento 1',
                        data: ingressosAcumEv1,
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Evento 2',
                        data: ingressosAcumEv2,
                        borderColor: '#764ba2',
                        backgroundColor: 'rgba(118, 75, 162, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { size: 14, weight: 'bold' },
                            padding: 15
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: { size: 12 }
                        }
                    },
                    x: {
                        ticks: {
                            font: { size: 11 }
                        }
                    }
                }
            }
        }
    );
    
    // Gráfico: Receita Acumulada
    chartsInstances.receitaAcum = new Chart(
        document.getElementById('chartReceitaAcumulada'),
        {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Evento 1',
                        data: receitaAcumEv1,
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Evento 2',
                        data: receitaAcumEv2,
                        borderColor: '#764ba2',
                        backgroundColor: 'rgba(118, 75, 162, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { size: 14, weight: 'bold' },
                            padding: 15
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': R$ ' + context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR');
                            },
                            font: { size: 12 }
                        }
                    },
                    x: {
                        ticks: {
                            font: { size: 11 }
                        }
                    }
                }
            }
        }
    );
    
    // Gráfico: Ingressos por Dia
    chartsInstances.ingressosDia = new Chart(
        document.getElementById('chartIngressosDia'),
        {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Evento 1',
                        data: ingressosDiaEv1,
                        backgroundColor: 'rgba(102, 126, 234, 0.8)',
                        borderColor: '#667eea',
                        borderWidth: 2
                    },
                    {
                        label: 'Evento 2',
                        data: ingressosDiaEv2,
                        backgroundColor: 'rgba(118, 75, 162, 0.8)',
                        borderColor: '#764ba2',
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { size: 14, weight: 'bold' },
                            padding: 15
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: { size: 12 }
                        }
                    },
                    x: {
                        ticks: {
                            font: { size: 11 }
                        }
                    }
                }
            }
        }
    );
    
    // Gráfico: Receita por Dia
    chartsInstances.receitaDia = new Chart(
        document.getElementById('chartReceitaDia'),
        {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Evento 1',
                        data: receitaDiaEv1,
                        backgroundColor: 'rgba(102, 126, 234, 0.8)',
                        borderColor: '#667eea',
                        borderWidth: 2
                    },
                    {
                        label: 'Evento 2',
                        data: receitaDiaEv2,
                        backgroundColor: 'rgba(118, 75, 162, 0.8)',
                        borderColor: '#764ba2',
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { size: 14, weight: 'bold' },
                            padding: 15
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': R$ ' + context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR');
                            },
                            font: { size: 12 }
                        }
                    },
                    x: {
                        ticks: {
                            font: { size: 11 }
                        }
                    }
                }
            }
        }
    );
    
    // Gráfico: Comparação por Períodos
    chartsInstances.periodos = new Chart(
        document.getElementById('chartPeriodos'),
        {
            type: 'bar',
            data: {
                labels: labelsPeriodos,
                datasets: [
                    {
                        label: 'Evento 1',
                        data: ingressosPeriodosEv1,
                        backgroundColor: 'rgba(102, 126, 234, 0.8)',
                        borderColor: '#667eea',
                        borderWidth: 2
                    },
                    {
                        label: 'Evento 2',
                        data: ingressosPeriodosEv2,
                        backgroundColor: 'rgba(118, 75, 162, 0.8)',
                        borderColor: '#764ba2',
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { size: 14, weight: 'bold' },
                            padding: 15
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: { size: 12 }
                        }
                    },
                    x: {
                        ticks: {
                            font: { size: 12 }
                        }
                    }
                }
            }
        }
    );
}

/**
 * Formata número com separador de milhares
 */
function formatNumber(num) {
    return parseInt(num || 0).toLocaleString('pt-BR');
}

/**
 * Formata valor monetário
 */
function formatMoney(num) {
    return parseFloat(num || 0).toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

</script>

<?= $this->endSection() ?>

