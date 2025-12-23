<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('estilos') ?>
<style>
    /* Layout PDV estilo SumUp */
    .pdv-container {
        display: flex;
        height: calc(100vh - 120px);
        gap: 20px;
    }
    
    /* Painel Esquerdo - Produtos */
    .pdv-produtos {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    
    /* Tabs de Categorias */
    .categoria-tabs {
        display: flex;
        gap: 8px;
        padding: 16px;
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        overflow-x: auto;
        flex-shrink: 0;
    }
    
    .categoria-tab {
        padding: 12px 24px;
        border: none;
        background: #fff;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        color: #333;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
        text-transform: uppercase;
    }
    
    .categoria-tab:hover {
        background: #e9ecef;
    }
    
    .categoria-tab.active {
        background: #28a745;
        color: white;
    }
    
    /* Grid de Ingressos */
    .ingressos-grid {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 16px;
        align-content: start;
    }
    
    .ingresso-card {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        border: 2px solid transparent;
        position: relative;
        min-height: 140px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    
    .ingresso-card:hover {
        background: #e9ecef;
        transform: scale(1.02);
    }
    
    .ingresso-card.no-carrinho {
        border-color: #28a745;
        background: #e8f5e9;
    }
    
    .ingresso-card .badge-qtd {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #28a745;
        color: white;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
    }
    
    .ingresso-card .nome {
        font-weight: 600;
        font-size: 14px;
        color: #333;
        margin-bottom: 8px;
        line-height: 1.3;
    }
    
    .ingresso-card .preco {
        font-size: 18px;
        font-weight: 700;
        color: #28a745;
    }
    
    .ingresso-card .lote {
        font-size: 11px;
        color: #6c757d;
        margin-top: 4px;
    }
    
    /* Painel Direito - Carrinho */
    .pdv-carrinho {
        width: 380px;
        background: #fff;
        border-radius: 16px;
        display: flex;
        flex-direction: column;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        flex-shrink: 0;
    }
    
    .carrinho-header {
        padding: 20px;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .carrinho-header h5 {
        margin: 0;
        font-weight: 700;
    }
    
    .carrinho-items {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
    }
    
    .carrinho-item {
        display: flex;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .carrinho-item:last-child {
        border-bottom: none;
    }
    
    .carrinho-item .qtd {
        width: 30px;
        font-weight: 700;
        color: #333;
    }
    
    .carrinho-item .nome {
        flex: 1;
        font-size: 14px;
        color: #333;
    }
    
    .carrinho-item .preco {
        font-weight: 600;
        color: #333;
    }
    
    .carrinho-item .remover {
        margin-left: 12px;
        color: #dc3545;
        cursor: pointer;
        padding: 4px;
    }
    
    .carrinho-empty {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #adb5bd;
        padding: 40px;
    }
    
    .carrinho-empty i {
        font-size: 48px;
        margin-bottom: 16px;
    }
    
    /* Footer do Carrinho */
    .carrinho-footer {
        padding: 20px;
        border-top: 1px solid #e9ecef;
        background: #f8f9fa;
    }
    
    .carrinho-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }
    
    .carrinho-total .label {
        font-size: 16px;
        color: #6c757d;
    }
    
    .carrinho-total .valor {
        font-size: 28px;
        font-weight: 700;
        color: #333;
    }
    
    .btn-cobrar {
        width: 100%;
        padding: 16px;
        font-size: 18px;
        font-weight: 700;
        border-radius: 12px;
        background: #28a745;
        border: none;
        color: white;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-cobrar:hover {
        background: #218838;
    }
    
    .btn-cobrar:disabled {
        background: #adb5bd;
        cursor: not-allowed;
    }
    
    /* Header do PDV */
    .pdv-header-bar {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        padding: 16px 24px;
        border-radius: 12px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .pdv-header-bar h4 {
        color: white;
        margin: 0;
        font-weight: 600;
    }
    
    .pdv-header-bar .evento-info {
        color: rgba(255,255,255,0.8);
        font-size: 14px;
    }
    
    /* Responsivo */
    @media (max-width: 992px) {
        .pdv-container {
            flex-direction: column;
            height: auto;
        }
        
        .pdv-carrinho {
            width: 100%;
            min-height: 300px;
        }
        
        .ingressos-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        }
    }
</style>
<?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="container-fluid py-3">
    
    <!-- Header -->
    <div class="pdv-header-bar">
        <div>
            <h4><i class="bi bi-shop me-2"></i><?= esc($evento->nome) ?></h4>
            <span class="evento-info">
                <i class="bi bi-calendar3 me-1"></i>
                <?= date('d/m/Y', strtotime($evento->data_inicio)) ?>
                <?php if ($evento->data_inicio != $evento->data_fim) : ?>
                    - <?= date('d/m/Y', strtotime($evento->data_fim)) ?>
                <?php endif; ?>
            </span>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-light" onclick="limparCarrinho()">
                <i class="bi bi-trash me-1"></i>Limpar
            </button>
            <a href="<?= site_url('pdv/dashboard') ?>" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i>Trocar Evento
            </a>
        </div>
    </div>

    <!-- Container PDV -->
    <div class="pdv-container">
        
        <!-- Painel de Produtos -->
        <div class="pdv-produtos">
            
            <!-- Tabs de Categorias -->
            <div class="categoria-tabs">
                <button class="categoria-tab active" data-categoria="todos" onclick="filtrarCategoria('todos', this)">
                    <i class="bi bi-grid me-1"></i>Todos
                </button>
                <?php 
                $categoriasUnicas = array_keys($categorias);
                foreach ($categoriasUnicas as $cat) : 
                    $catLabel = ucfirst($cat);
                    if ($cat == 'comum') $catLabel = 'Comum';
                    if ($cat == 'vip') $catLabel = 'VIP Full';
                    if ($cat == 'epic') $catLabel = 'Epic Pass';
                ?>
                    <button class="categoria-tab" data-categoria="<?= $cat ?>" onclick="filtrarCategoria('<?= $cat ?>', this)">
                        <?= $catLabel ?>
                    </button>
                <?php endforeach; ?>
            </div>
            
            <!-- Grid de Ingressos -->
            <div class="ingressos-grid" id="ingressosGrid">
                <?php foreach ($tickets as $ticket) : 
                    $qtdCarrinho = isset($carrinho[$ticket->id]) ? $carrinho[$ticket->id]['quantidade'] : 0;
                ?>
                    <div class="ingresso-card <?= $qtdCarrinho > 0 ? 'no-carrinho' : '' ?>" 
                         data-categoria="<?= strtolower($ticket->categoria ?? 'comum') ?>"
                         data-ticket-id="<?= $ticket->id ?>"
                         onclick="adicionarItem(<?= $ticket->id ?>)">
                        
                        <?php if ($qtdCarrinho > 0) : ?>
                            <span class="badge-qtd" id="badge-<?= $ticket->id ?>"><?= $qtdCarrinho ?></span>
                        <?php endif; ?>
                        
                        <div class="nome"><?= esc($ticket->nome) ?></div>
                        <div class="preco">R$ <?= number_format($ticket->preco, 2, ',', '.') ?></div>
                        <div class="lote"><?= $ticket->lote ?>º Lote</div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Painel do Carrinho -->
        <div class="pdv-carrinho">
            <div class="carrinho-header">
                <h5><i class="bi bi-cart3 me-2"></i>Carrinho</h5>
                <span class="text-muted" id="carrinhoQtd"><?= count($carrinho) ?> itens</span>
            </div>
            
            <div class="carrinho-items" id="carrinhoItems">
                <?php if (empty($carrinho)) : ?>
                    <div class="carrinho-empty">
                        <i class="bi bi-cart-x"></i>
                        <span>Carrinho vazio</span>
                        <small>Clique nos ingressos para adicionar</small>
                    </div>
                <?php else : ?>
                    <?php foreach ($carrinho as $item) : ?>
                        <div class="carrinho-item" data-ticket-id="<?= $item['ticket_id'] ?>">
                            <span class="qtd"><?= $item['quantidade'] ?>x</span>
                            <span class="nome"><?= esc($item['nome']) ?></span>
                            <span class="preco">R$ <?= number_format($item['total'] * $item['quantidade'], 2, ',', '.') ?></span>
                            <span class="remover" onclick="removerItem(<?= $item['ticket_id'] ?>)">
                                <i class="bi bi-x-lg"></i>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="carrinho-footer">
                <div class="carrinho-total">
                    <span class="label">Total</span>
                    <span class="valor" id="carrinhoTotal">R$ <?= number_format(array_sum(array_map(function($i) { return $i['total'] * $i['quantidade']; }, $carrinho)), 2, ',', '.') ?></span>
                </div>
                <button class="btn-cobrar" id="btnCobrar" onclick="irParaCheckout()" <?= empty($carrinho) ? 'disabled' : '' ?>>
                    <i class="bi bi-credit-card me-2"></i>Cobrar
                </button>
            </div>
        </div>
    </div>

</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script>
const eventId = <?= $evento->id ?>;
let carrinho = <?= json_encode($carrinho) ?>;
let csrfToken = '<?= csrf_hash() ?>';
const csrfName = '<?= csrf_token() ?>';

function filtrarCategoria(categoria, btn) {
    // Atualiza tabs
    document.querySelectorAll('.categoria-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    
    // Filtra cards
    document.querySelectorAll('.ingresso-card').forEach(card => {
        if (categoria === 'todos' || card.dataset.categoria === categoria) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}

function adicionarItem(ticketId) {
    let data = { ticket_id: ticketId };
    data[csrfName] = csrfToken;
    
    $.ajax({
        url: '<?= site_url('pdv/adicionarItem') ?>',
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            if (response.token) {
                csrfToken = response.token;
            }
            if (response.sucesso) {
                carrinho = response.carrinho;
                atualizarCarrinhoUI(response.carrinho, response.total);
                atualizarBadgeIngresso(ticketId);
            } else if (response.erro) {
                alert(response.erro);
            }
        },
        error: function(xhr, status, error) {
            console.error('Erro AJAX:', error);
            console.log('Response:', xhr.responseText);
        }
    });
}

function removerItem(ticketId) {
    event.stopPropagation();
    let data = { ticket_id: ticketId };
    data[csrfName] = csrfToken;
    
    $.ajax({
        url: '<?= site_url('pdv/removerItem') ?>',
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            if (response.token) {
                csrfToken = response.token;
            }
            if (response.sucesso) {
                carrinho = response.carrinho;
                atualizarCarrinhoUI(response.carrinho, response.total);
                atualizarBadgeIngresso(ticketId);
            }
        }
    });
}

function atualizarCarrinhoUI(carrinhoData, total) {
    const container = $('#carrinhoItems');
    const items = Object.values(carrinhoData);
    
    if (items.length === 0) {
        container.html(`
            <div class="carrinho-empty">
                <i class="bi bi-cart-x"></i>
                <span>Carrinho vazio</span>
                <small>Clique nos ingressos para adicionar</small>
            </div>
        `);
        $('#btnCobrar').prop('disabled', true);
    } else {
        let html = '';
        items.forEach(item => {
            html += `
                <div class="carrinho-item" data-ticket-id="${item.ticket_id}">
                    <span class="qtd">${item.quantidade}x</span>
                    <span class="nome">${item.nome}</span>
                    <span class="preco">R$ ${(item.total * item.quantidade).toFixed(2).replace('.', ',')}</span>
                    <span class="remover" onclick="removerItem(${item.ticket_id})">
                        <i class="bi bi-x-lg"></i>
                    </span>
                </div>
            `;
        });
        container.html(html);
        $('#btnCobrar').prop('disabled', false);
    }
    
    $('#carrinhoTotal').text('R$ ' + total.toFixed(2).replace('.', ','));
    $('#carrinhoQtd').text(items.length + ' itens');
}

function atualizarBadgeIngresso(ticketId) {
    const card = $(`.ingresso-card[data-ticket-id="${ticketId}"]`);
    const item = carrinho[ticketId];
    
    card.find('.badge-qtd').remove();
    
    if (item && item.quantidade > 0) {
        card.addClass('no-carrinho');
        card.prepend(`<span class="badge-qtd" id="badge-${ticketId}">${item.quantidade}</span>`);
    } else {
        card.removeClass('no-carrinho');
    }
}

function limparCarrinho() {
    if (!confirm('Limpar todo o carrinho?')) return;
    
    let data = {};
    data[csrfName] = csrfToken;
    
    $.ajax({
        url: '<?= site_url('pdv/limparCarrinho') ?>',
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            if (response.sucesso) {
                location.reload();
            }
        }
    });
}

function irParaCheckout() {
    if (Object.keys(carrinho).length === 0) {
        alert('Adicione itens ao carrinho');
        return;
    }
    window.location.href = '<?= site_url("pdv/checkout") ?>/' + eventId;
}
</script>
<?php echo $this->endSection() ?>
