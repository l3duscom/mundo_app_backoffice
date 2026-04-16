# Plano de Integração UTMify - mundo_app

## Contexto

O projeto é uma plataforma de venda de ingressos para eventos (CodeIgniter 4 + PHP) que já possui integrações com Meta Pixel, Google Analytics, TikTok Pixel e Google Ads. **Não há nenhum tratamento de parâmetros UTM atualmente.** O UTMify (utmify.com.br) é uma plataforma brasileira de rastreamento de vendas que conecta cliques em anúncios a compras efetivadas.

Como o checkout é **próprio** (não Hotmart/Kiwify), a integração precisa de 3 componentes:

1. **Script UTM** (frontend) - captura e persiste UTM params
2. **Pixel UTMify** (frontend) - rastreia eventos no navegador
3. **Webhook server-side** - envia dados de venda confirmada ao UTMify via API

---

## Etapa 1: Captura de UTM params (Frontend)

### 1.1 Adicionar script UTMify no layout

**Arquivo:** `app/Views/Layout/externo.php` (linha ~5, no `<head>`)

Adicionar o script de captura de UTMs do UTMify:

```html
<!-- UTMify UTM Script -->
<script
  src="https://cdn.utmify.com.br/scripts/utms/latest.js"
  async
  defer
></script>
```

### 1.2 Adicionar Pixel UTMify no layout

**Arquivo:** `app/Views/Layout/externo.php` (no `<head>`, após os outros pixels)

Adicionar o pixel de rastreamento:

```html
<!-- UTMify Pixel -->
<script
  src="https://cdn.utmify.com.br/scripts/pixel/pixel.js"
  data-pixel-id="SEU_PIXEL_ID"
  async
  defer
></script>
```

> **Nota:** O `PIXEL_ID` precisa ser obtido no painel do UTMify.

### 1.3 Criar Helper para captura de UTM params via PHP

**Arquivo novo:** `app/Helpers/utmify_helper.php`

Funções:

- `capture_utm_params()` - Lê `utm_source`, `utm_medium`, `utm_campaign`, `utm_content`, `utm_term` da URL e salva na sessão
- `get_utm_params()` - Retorna os UTM params da sessão
- `get_utmify_purchase_script($order_id, $valor)` - Gera script de evento Purchase para o pixel

### 1.4 Capturar UTMs no Controller de entrada

**Arquivo:** `app/Controllers/Checkout.php` (e/ou `Carrinho.php`)

Chamar `capture_utm_params()` nos métodos que recebem tráfego externo (páginas de evento, carrinho) para persistir os UTMs na sessão PHP.

---

## Etapa 2: Persistir UTMs no banco de dados (tabela separada)

> **Decisão:** Não alterar a tabela `pedidos` (sensível). Criar tabela de ligação `pedido_utms`.

### 2.1 Migration: criar tabela `pedido_utms`

**Arquivo novo:** `app/Database/Migrations/XXXX_CreatePedidoUtmsTable.php`

```sql
CREATE TABLE pedido_utms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    utm_source VARCHAR(255) NULL,
    utm_medium VARCHAR(255) NULL,
    utm_campaign VARCHAR(255) NULL,
    utm_content VARCHAR(255) NULL,
    utm_term VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE
);
```

### 2.2 Criar PedidoUtmModel

**Arquivo novo:** `app/Models/PedidoUtmModel.php`

Model simples para a tabela `pedido_utms` com `$allowedFields` para os campos UTM.

### 2.3 Salvar UTMs após criar pedido

**Arquivo:** `app/Controllers/Checkout.php`

Nos métodos `criaPedido()` (linha 1627) e `criaPedidoCartao()` (linha 1446), após o insert do pedido, inserir na tabela `pedido_utms`:

```php
$pedidoUtmModel->insert([
    'pedido_id'    => $pedido_id,
    'utm_source'   => session()->get('utm_source') ?? '',
    'utm_medium'   => session()->get('utm_medium') ?? '',
    'utm_campaign' => session()->get('utm_campaign') ?? '',
    'utm_content'  => session()->get('utm_content') ?? '',
    'utm_term'     => session()->get('utm_term') ?? '',
]);
```

---

## Etapa 3: Webhook de notificação ao UTMify (Server-Side)

API Token UTMFY: rTHVfZ0W4FaTkSz4cgcMw0eGXst1kq7NJCfT

### 3.1 Criar Service UTMify

**Arquivo novo:** `app/Services/UtmifyService.php`

Responsabilidades:

- Enviar postback de venda confirmada para o UTMify quando o pagamento for aprovado
- Payload inclui: order_id, valor, email do cliente, UTM params do pedido
- Usar a URL de webhook/postback fornecida pelo painel do UTMify

### 3.2 Disparar notificação no Webhook ASAAS

**Arquivo:** `app/Controllers/Webhook.php` (linha ~78)

Após a atualização do status do pedido para `CONFIRMED`/`PAID`/`RECEIVED`, chamar `UtmifyService::notifyPurchase()` passando os dados do pedido com os UTMs salvos.

---

## Etapa 4: Eventos do Pixel no Frontend

### 4.1 Evento InitiateCheckout

**Arquivo:** `app/Views/Checkout/pix.php` e `app/Views/Checkout/cartao.php`

Adicionar hidden fields com UTM params e disparar evento InitiateCheckout do pixel UTMify via JavaScript.

### 4.2 Evento Purchase na página de obrigado

**Arquivo:** `app/Views/Checkout/obrigado.php` (seção scripts, linha ~366)

Adicionar evento de compra do pixel UTMify, similar ao que já é feito para o Meta Pixel.

---

## Resumo dos arquivos a modificar/criar

| Arquivo                                                  | Ação                                                       |
| -------------------------------------------------------- | ---------------------------------------------------------- |
| `app/Views/Layout/externo.php`                           | Adicionar scripts UTMify                                   |
| `app/Helpers/utmify_helper.php`                          | **CRIAR** - funções de captura UTM                         |
| `app/Controllers/Checkout.php`                           | Capturar UTMs + inserir na `pedido_utms` após criar pedido |
| `app/Models/PedidoUtmModel.php`                          | **CRIAR** - model da tabela `pedido_utms`                  |
| `app/Database/Migrations/XXXX_CreatePedidoUtmsTable.php` | **CRIAR** - migration da tabela de ligação                 |
| `app/Services/UtmifyService.php`                         | **CRIAR** - service para postback                          |
| `app/Controllers/Webhook.php`                            | Disparar notificação UTMify (buscar UTMs via JOIN)         |
| `app/Views/Checkout/pix.php`                             | Hidden fields UTM + evento InitiateCheckout                |
| `app/Views/Checkout/cartao.php`                          | Hidden fields UTM + evento InitiateCheckout                |
| `app/Views/Checkout/obrigado.php`                        | Evento Purchase UTMify                                     |

---

## Verificação / Teste

1. Acessar uma página de evento com UTMs na URL: `?utm_source=facebook&utm_medium=cpc&utm_campaign=test`
2. Verificar se os UTMs foram salvos na sessão (via debug/log)
3. Completar uma compra e verificar no banco se os campos `utm_*` foram preenchidos na tabela `pedido_utms`
4. Simular webhook ASAAS com status `CONFIRMED` e verificar se o postback foi enviado ao UTMify
5. Verificar no painel do UTMify se a venda apareceu com os UTMs corretos

---

## Pré-requisitos (do usuário)

- [ ] Criar conta no UTMify (utmify.com.br)
- [ ] Obter o **Pixel ID** no painel UTMify
- [ ] Obter a **URL de webhook/postback** para envio de vendas
- [ ] Obter o **Token de API** (se necessário para autenticação do postback)

UTM facebook: utm_source=FB&utm_campaign={{campaign.name}}|{{campaign.id}}&utm_medium={{adset.name}}|{{adset.id}}&utm_content={{ad.name}}|{{ad.id}}&utm_term={{placement}}

Pixel UTMFY: <script>
window.pixelId = "69e0fc350d119a03b6f17a74";
var a = document.createElement("script");
a.setAttribute("async", "");
a.setAttribute("defer", "");
a.setAttribute("src", "https://cdn.utmify.com.br/scripts/pixel/pixel.js");
document.head.appendChild(a);
</script>
