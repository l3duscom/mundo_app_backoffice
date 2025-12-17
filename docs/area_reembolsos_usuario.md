# Área de Reembolsos do Usuário - Documentação

Este documento descreve a implementação da funcionalidade de visualização de reembolsos para usuários finais.

## Visão Geral

Permite que usuários visualizem suas solicitações de reembolso (refunds) e upgrades na área de membros.

## Estrutura da Tabela

```sql
CREATE TABLE `refounds` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `pedido_id` INT,
    `cliente_id` INT,
    `tipo_solicitacao` VARCHAR(50),
    `aceito` TINYINT(1),
    `pedido_codigo` VARCHAR(50),
    `pedido_valor_total` DECIMAL(10,2),
    `pedido_data_compra` DATETIME,
    `cliente_nome` VARCHAR(255),
    `cliente_email` VARCHAR(255),
    `evento_id` INT,
    `evento_nome` VARCHAR(255),
    `evento_data_inicio` DATE,
    `ingressos_originais` JSON,
    `tipo_upgrade` VARCHAR(100),
    `oferta_titulo` VARCHAR(255),
    `oferta_vantagem_valor` DECIMAL(10,2),
    `opcao_selecionada` VARCHAR(100),
    `oferta_detalhes` JSON,
    `beneficios_apresentados` JSON,
    `ingressos_para_upgrade` JSON,
    `observacoes` TEXT,
    `status` ENUM('pendente','processando','concluido','cancelado','erro'),
    `processado_em` DATETIME,
    `created_at` DATETIME,
    `updated_at` DATETIME
);
```

## Estrutura dos JSONs

### ingressos_originais
```json
[{
    "id": "27252",
    "nome": "CORTESIA - CRIANÇA GRÁTIS",
    "codigo": "1836TXU7HW",
    "ticket_id": "608",
    "valor": "0.00"
}]
```

### ingressos_para_upgrade
```json
[{
    "ingresso_id": "27252",
    "ingresso_nome": "CORTESIA - CRIANÇA GRÁTIS",
    "ingresso_codigo": "1836TXU7HW",
    "ticket_id": "608",
    "valor_original": "0.00",
    "oferta": {
        "titulo": "1x EPIC PASS",
        "subtitulo": "Acesso especial ao próximo evento",
        "ganho": 99,
        "quantidade": 1,
        "tipo": "EPIC PASS"
    }
}]
```

## Arquivos

### 1. Model (RefoundModel.php)

```php
public function listaRefoundsPorCliente($cliente_id)
{
    return $this->select(['refounds.*'])
        ->where('cliente_id', $cliente_id)
        ->orderBy('created_at', 'DESC')
        ->findAll();
}

public function contaRefoundsPendentesPorCliente($cliente_id)
{
    return $this->where('cliente_id', $cliente_id)
                ->where('status', 'pendente')
                ->countAllResults();
}
```

### 2. Controller (Pedidos.php)

```php
public function meusRefounds()
{
    $id = $this->usuarioLogado()->id;
    $cli = $this->clienteModel->withDeleted(true)->where('usuario_id', $id)->first();

    if (!$cli) {
        return redirect()->back()->with('erro', 'Cliente não encontrado.');
    }

    $refoundModel = new \App\Models\RefoundModel();
    $refounds = $refoundModel->listaRefoundsPorCliente($cli->id);

    return view('Pedidos/meus_refounds', [
        'titulo' => 'Minhas Solicitações de Reembolso',
        'refounds' => $refounds,
    ]);
}

public function meuRefoundDetalhe(int $id = null)
{
    if (!$id) {
        return redirect()->to(site_url('pedidos/meus-refounds'))->with('erro', 'ID inválido.');
    }

    $userId = $this->usuarioLogado()->id;
    $cli = $this->clienteModel->withDeleted(true)->where('usuario_id', $userId)->first();

    $refoundModel = new \App\Models\RefoundModel();
    $refound = $refoundModel->find($id);

    // SEGURANÇA: Verifica se pertence ao cliente logado
    if (!$refound || $refound->cliente_id != $cli->id) {
        return redirect()->to(site_url('pedidos/meus-refounds'))->with('erro', 'Solicitação não encontrada.');
    }

    return view('Pedidos/meu_refound_detalhe', [
        'titulo' => 'Detalhes da Solicitação #' . $id,
        'refound' => $refound,
    ]);
}
```

### 3. Rotas

```php
$routes->get('pedidos/meus-refounds', 'Pedidos::meusRefounds');
$routes->get('pedidos/meus-refounds/(:num)', 'Pedidos::meuRefoundDetalhe/$1');
```

## Funções de Formatação de Ingressos

> **IMPORTANTE**: Usar funções diferentes para ingressos originais e para upgrade!

### formatarIngressosCliente (para ingressos originais)
```php
function formatarIngressosCliente($jsonString) {
    $ingressos = json_decode($jsonString, true);
    foreach ($ingressos as $ingresso) {
        $nome = $ingresso['nome'] ?? $ingresso['ingresso_nome'] ?? 'Ingresso';
        $codigo = $ingresso['codigo'] ?? $ingresso['ingresso_codigo'] ?? '';
        $valor = $ingresso['valor'] ?? $ingresso['valor_original'] ?? null;
        // ... renderizar
    }
}
```

### formatarIngressosUpgradeCliente (para ingressos de upgrade)
```php
function formatarIngressosUpgradeCliente($jsonString) {
    $ingressos = json_decode($jsonString, true);
    foreach ($ingressos as $ingresso) {
        // Usar o tipo da OFERTA como nome principal
        $oferta = $ingresso['oferta'] ?? [];
        $nome = $oferta['tipo'] ?? $oferta['titulo'] ?? $ingresso['nome'] ?? 'Ingresso';
        $codigo = $ingresso['ingresso_codigo'] ?? $ingresso['codigo'] ?? '';
        $valor = $oferta['ganho'] ?? $ingresso['preco'] ?? null;
        
        // IMPORTANTE: Mostrar "Ganho de R$ xx,xx" para deixar claro que é um benefício
        $valorFormatado = $valor !== null ? 'Ganho de R$ ' . number_format(floatval($valor), 2, ',', '.') : '';
        
        // Renderizar com ícone de upgrade (seta verde) e texto em verde
        $html .= '<span class="ingresso-valor text-success">' . $valorFormatado . '</span>';
    }
}
```

## Views

### meus_refounds.php (Listagem)
- Cards resumo com totais
- Lista de solicitações com status, evento, valor, data
- Botão "Ver Detalhes"

### meu_refound_detalhe.php (Detalhes)
- Breadcrumb de navegação
- Timeline visual de progresso (Enviada → Em Análise → Concluída/Cancelada)
- Cards de evento e pedido
- Seção de upgrade com:
  - Ingressos originais (função `formatarIngressosCliente`)
  - Ingressos para upgrade (função `formatarIngressosUpgradeCliente`)
  - Benefícios apresentados

## Card na Dashboard

```php
<?php if (isset($refoundsTotal) && $refoundsTotal > 0): ?>
<div class="card shadow-sm border-0 mb-4" style="border-left: 4px solid #6f42c1 !important;">
    <div class="card-body py-3">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="bg-purple rounded-circle" style="width:50px; height:50px;">
                    <i class="bx bx-refresh text-white"></i>
                </div>
            </div>
            <div class="col">
                <h6 class="mb-1 fw-bold">Minhas Solicitações</h6>
                <p class="mb-0 text-muted small">
                    <?php if ($refoundsPendentes > 0): ?>
                        <span class="badge bg-warning"><?= $refoundsPendentes ?> pendente(s)</span>
                    <?php endif; ?>
                    <?= $refoundsTotal ?> solicitação(ões) no total
                </p>
            </div>
            <div class="col-auto">
                <a href="<?= site_url('pedidos/meus-refounds') ?>" class="btn btn-outline-primary btn-sm">
                    Ver Solicitações
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
```

## Status ENUM

| Valor | Descrição |
|-------|-----------|
| `pendente` | Aguardando análise |
| `processando` | Em processamento |
| `concluido` | Aprovado/Finalizado |
| `cancelado` | Rejeitado/Cancelado |
| `erro` | Erro no processamento |

## Segurança

O método `meuRefoundDetalhe` **DEVE** verificar se o refund pertence ao cliente logado:

```php
if (!$refound || $refound->cliente_id != $cli->id) {
    return redirect()->to(site_url('pedidos/meus-refounds'))->with('erro', 'Solicitação não encontrada.');
}
```

## URLs

- **Listagem:** `/pedidos/meus-refounds`
- **Detalhes:** `/pedidos/meus-refounds/{id}`

---

*Documentação atualizada em 17/12/2024*
