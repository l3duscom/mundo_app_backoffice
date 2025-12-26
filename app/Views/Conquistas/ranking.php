<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
    .stat-card {
        padding: 1.5rem;
        border-radius: 12px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-align: center;
    }
    .stat-card.green {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    .stat-card.orange {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    .stat-card h3 {
        font-size: 2.5rem;
        font-weight: bold;
        margin: 0;
    }
    .stat-card p {
        margin: 0.5rem 0 0;
        opacity: 0.9;
    }
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<!-- Evento do contexto -->
<input type="hidden" id="eventoContexto" value="<?php echo esc($evento_id ?? ''); ?>">

<!--breadcrumb-->
<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">Conquistas</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('conquistas-admin'); ?>">Conquistas</a></li>
                <li class="breadcrumb-item active" aria-current="page">Ranking</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="<?php echo site_url('conquistas-admin'); ?>" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-2"></i>Voltar
        </a>
    </div>
</div>
<!--end breadcrumb-->

<!-- Cards estatísticos -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <h3 id="totalConquistas">-</h3>
            <p><i class="bx bx-trophy"></i> Conquistas Cadastradas</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card green">
            <h3 id="totalAtribuicoes">-</h3>
            <p><i class="bx bx-user-check"></i> Total de Atribuições</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card orange">
            <h3 id="totalPontos">-</h3>
            <p><i class="bx bx-coin-stack"></i> Total de Pontos</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <h3 id="mediaAtribuicoes">-</h3>
            <p><i class="bx bx-stats"></i> Média por Conquista</p>
        </div>
    </div>
</div>

<div class="row">
    <!-- Gráfico de barras - Top conquistas -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bx bx-bar-chart-alt-2 me-2"></i>Top 10 Conquistas Mais Atribuídas</h5>
            </div>
            <div class="card-body">
                <div id="chartTop" style="min-height: 400px;"></div>
            </div>
        </div>
    </div>
    
    <!-- Gráfico de pizza - Por nível -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bx bx-pie-chart-alt me-2"></i>Distribuição por Nível</h5>
            </div>
            <div class="card-body">
                <div id="chartNivel" style="min-height: 400px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Tabela detalhada -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bx bx-list-ul me-2"></i>Detalhamento por Conquista</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped" id="tabelaDetalhada">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Conquista</th>
                        <th>Nível</th>
                        <th class="text-center">Usuários</th>
                        <th class="text-end">Total de Pontos</th>
                    </tr>
                </thead>
                <tbody id="tabelaBody">
                    <tr>
                        <td colspan="5" class="text-center">
                            <span class="spinner-border spinner-border-sm"></span> Carregando...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<!-- ApexCharts -->
<script src="<?php echo site_url('recursos/theme/'); ?>plugins/apexcharts-bundle/js/apexcharts.min.js"></script>

<script>
$(document).ready(function() {
    var eventoContexto = $('#eventoContexto').val();
    
    // Função para formatar números
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
    
    // Carregar dados
    $.ajax({
        url: '<?php echo site_url("conquistas-admin/dadosRanking"); ?>',
        type: 'GET',
        data: { event_id: eventoContexto },
        dataType: 'json',
        success: function(response) {
            // Atualizar cards
            $('#totalConquistas').text(response.totalConquistas || 0);
            $('#totalAtribuicoes').text(response.totalAtribuicoes || 0);
            $('#totalPontos').text(formatNumber(response.totalPontos || 0));
            
            var media = response.totalConquistas > 0 
                ? Math.round(response.totalAtribuicoes / response.totalConquistas) 
                : 0;
            $('#mediaAtribuicoes').text(media);
            
            // Gráfico de barras
            renderChartTop(response.topConquistas || []);
            
            // Gráfico de pizza
            renderChartNivel(response.porNivel || []);
            
            // Tabela
            renderTabela(response.topConquistas || []);
        },
        error: function() {
            alert('Erro ao carregar dados do ranking');
        }
    });
    
    function renderChartTop(data) {
        var labels = data.map(function(item) { return item.nome_conquista; });
        var values = data.map(function(item) { return parseInt(item.total); });
        
        var options = {
            series: [{
                name: 'Usuários',
                data: values
            }],
            chart: {
                type: 'bar',
                height: 400,
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: true,
                    distributed: true
                }
            },
            colors: ['#667eea', '#764ba2', '#f093fb', '#f5576c', '#11998e', '#38ef7d', '#FFD700', '#C0C0C0', '#CD7F32', '#B9F2FF'],
            dataLabels: {
                enabled: true
            },
            xaxis: {
                categories: labels
            },
            legend: { show: false }
        };
        
        new ApexCharts(document.querySelector("#chartTop"), options).render();
    }
    
    function renderChartNivel(data) {
        var labels = data.map(function(item) { return item.nivel; });
        var values = data.map(function(item) { return parseInt(item.total); });
        
        var options = {
            series: values,
            chart: {
                type: 'donut',
                height: 400
            },
            labels: labels,
            colors: ['#CD7F32', '#C0C0C0', '#FFD700', '#E5E4E2', '#B9F2FF'],
            legend: {
                position: 'bottom'
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: { width: 200 },
                    legend: { position: 'bottom' }
                }
            }]
        };
        
        new ApexCharts(document.querySelector("#chartNivel"), options).render();
    }
    
    function renderTabela(data) {
        var html = '';
        data.forEach(function(item, index) {
            html += '<tr>';
            html += '<td>' + (index + 1) + 'º</td>';
            html += '<td><strong>' + item.nome_conquista + '</strong></td>';
            html += '<td>' + getNivelBadge(item.nivel) + '</td>';
            html += '<td class="text-center"><span class="badge bg-primary">' + item.total + '</span></td>';
            html += '<td class="text-end"><span class="text-success fw-bold">' + formatNumber(parseInt(item.total_pontos || 0)) + ' pts</span></td>';
            html += '</tr>';
        });
        
        if (html === '') {
            html = '<tr><td colspan="5" class="text-center text-muted">Nenhuma conquista atribuída ainda</td></tr>';
        }
        
        $('#tabelaBody').html(html);
    }
    
    function getNivelBadge(nivel) {
        var cores = {
            'BRONZE': 'background: linear-gradient(135deg, #CD7F32, #8B4513); color: white;',
            'PRATA': 'background: linear-gradient(135deg, #C0C0C0, #808080); color: white;',
            'OURO': 'background: linear-gradient(135deg, #FFD700, #DAA520); color: #333;',
            'PLATINA': 'background: linear-gradient(135deg, #E5E4E2, #BCC6CC); color: #333;',
            'DIAMANTE': 'background: linear-gradient(135deg, #B9F2FF, #7DF9FF); color: #333;'
        };
        var estilo = cores[nivel] || 'background: #6c757d; color: white;';
        return '<span class="badge" style="' + estilo + '">' + nivel + '</span>';
    }
});
</script>
<?php echo $this->endSection() ?>
