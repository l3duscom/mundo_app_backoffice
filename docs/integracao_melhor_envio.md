# Integração com Melhor Envio — Plano de Implementação

> **Objetivo:** automatizar todo o processo de envio dos pedidos físicos (atualmente manual), desde a cotação até a geração da etiqueta e o e-mail de rastreio para o cliente, usando a API do Melhor Envio.

---

## 1. Situação atual (manual)

| Etapa | Onde |
| --- | --- |
| Modal de envio | [Pedidos/ingressos.php:537-582](../app/Views/Pedidos/ingressos.php#L537-L582) — input livre `rastreio` + form `POST pedidos/rastreio/{id}` |
| Handler | [Pedidos::rastreio()](../app/Controllers/Pedidos.php#L793-L833) — só grava `pedidos.rastreio` e chama `enviaEmailRastreio()` |
| E-mail | [email_rastreio.php](../app/Views/Pedidos/email_rastreio.php) via [ResendService](../app/Services/ResendService.php) |
| Endereço destino | `enderecos.pedido_id` → fallback `clientes` por `usuario_id` |
| Itens enviados | `ingressos` do pedido com `tipo NOT IN ('cinemark','adicional','produto','')` (ex: pulseiras RFID) |

Hoje o operador faz manualmente: copia endereço → cadastra no painel ME → gera etiqueta → cola código de rastreio no input → submit → cliente recebe o e-mail.

---

## 2. Premissas (definidas pelo cliente)

- **Saldo da carteira ME**: pré-carregado externamente — **não há UI de saldo** nem fluxo de recarga
- **Dimensões e peso do volume**: **fixos** para todo envio
  - Altura **2 cm** · Largura **12 cm** · Comprimento **17 cm** · Peso **0,5 kg**
- **Endereço de postagem (remetente)**: **fixo** no `.env` (não muda por pedido)
- **Declaração de conteúdo**: gerada automaticamente a partir dos `ingressos` do pedido (ex: `"2x Pulseira RFID Dreamfest 26"`)
- **Logging exaustivo**: todo request/response da API ME deve ser logado (URL, método, body sanitizado, status, response, tempo) — usar `log_message('info', …)` com canal próprio

---

## 3. API Melhor Envio — resumo operacional

### Bases
- Sandbox: `https://sandbox.melhorenvio.com.br`
- Produção: `https://melhorenvio.com.br`

### Headers obrigatórios em todo request
```
Authorization: Bearer {access_token}
Accept: application/json
Content-Type: application/json
User-Agent: MundoDream Backoffice (contato@mundodream.com.br)
```

### Endpoints usados
| Função | Método | Path |
| --- | --- | --- |
| Cotação | `POST` | `/api/v2/me/shipment/calculate` |
| Inserir no carrinho | `POST` | `/api/v2/me/cart` |
| Checkout (paga com saldo) | `POST` | `/api/v2/me/shipment/checkout` |
| Gerar etiqueta | `POST` | `/api/v2/me/shipment/generate` |
| Imprimir (URL PDF) | `POST` | `/api/v2/me/shipment/print` |
| Rastreio | `POST` | `/api/v2/me/shipment/tracking` |

### Autenticação
- **OAuth 2.0 com authorization code** (não há client-credentials)
- Access token JWT — **30 dias**
- Refresh token — **45 dias**
- Autorização única feita pelo admin no setup; tokens persistem no banco e renovam automaticamente

### Webhooks
- `X-ME-Signature`: HMAC-SHA256 do raw body com o `webhook_secret`
- Eventos: `order.generated`, `order.posted`, `order.delivered`, `order.undelivered`, `order.cancelled`
- Timeout de resposta: **6 segundos**

---

## 4. Modelo de dados

### 4.1 Nova tabela `melhor_envio_credentials`

Uma única linha contendo os tokens OAuth do backoffice.

```sql
CREATE TABLE melhor_envio_credentials (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  access_token  TEXT NOT NULL,
  refresh_token TEXT NOT NULL,
  scope         VARCHAR(255) NULL,
  expires_at    DATETIME NOT NULL,
  created_at    DATETIME NULL,
  updated_at    DATETIME NULL
);
```

### 4.2 Nova tabela `melhor_envio_logs`

Trilha completa de toda chamada à API (atende o requisito de "logar tudo").

```sql
CREATE TABLE melhor_envio_logs (
  id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pedido_id       INT UNSIGNED NULL,
  endpoint        VARCHAR(100) NOT NULL,
  http_method     VARCHAR(8)   NOT NULL,
  request_body    JSON NULL,
  response_body   JSON NULL,
  http_status     SMALLINT NULL,
  duracao_ms      INT NULL,
  erro            TEXT NULL,
  created_at      DATETIME NOT NULL,
  INDEX (pedido_id),
  INDEX (endpoint),
  INDEX (created_at)
);
```

### 4.3 Colunas adicionais em `pedidos`

```sql
ALTER TABLE pedidos
  ADD COLUMN me_order_id      VARCHAR(40)  NULL COMMENT 'UUID do envio no ME',
  ADD COLUMN me_protocol      VARCHAR(40)  NULL COMMENT 'ex: ORD-202304125603',
  ADD COLUMN me_servico_id    INT          NULL COMMENT 'ID do serviço (PAC, SEDEX, etc.)',
  ADD COLUMN me_servico_nome  VARCHAR(80)  NULL,
  ADD COLUMN me_etiqueta_url  VARCHAR(255) NULL,
  ADD COLUMN me_status        VARCHAR(30)  NULL COMMENT 'pending|paid|generated|posted|delivered|cancelled',
  ADD COLUMN me_valor_frete   DECIMAL(8,2) NULL,
  ADD COLUMN me_postado_em    DATETIME     NULL,
  ADD COLUMN me_entregue_em   DATETIME     NULL;
```

Manter `pedidos.rastreio` — passa a ser **preenchido automaticamente** pelo webhook `order.posted` com o código da transportadora.

---

## 5. Configurações (`.env`)

```dotenv
# Credenciais OAuth da aplicação registrada no painel ME
MELHOR_ENVIO_CLIENT_ID     = ''
MELHOR_ENVIO_CLIENT_SECRET = ''
MELHOR_ENVIO_REDIRECT_URI  = 'https://backoffice.mundodream.com.br/melhor-envio/callback'
MELHOR_ENVIO_CONTATO       = 'ti@mundodream.com.br'
MELHOR_ENVIO_WEBHOOK_SECRET= ''

# Remetente padrão (não muda por pedido)
ME_FROM_NOME       = 'MundoDream Eventos'
ME_FROM_EMAIL      = 'logistica@mundodream.com.br'
ME_FROM_TELEFONE   = '5151999999999'
ME_FROM_DOCUMENTO  = '00000000000000'     # CNPJ
ME_FROM_ENDERECO   = 'Rua Exemplo'
ME_FROM_NUMERO     = '123'
ME_FROM_COMPLEMENTO= 'Sala 1'
ME_FROM_BAIRRO     = 'Centro'
ME_FROM_CIDADE     = 'Porto Alegre'
ME_FROM_ESTADO     = 'RS'
ME_FROM_CEP        = '91110000'
ME_FROM_PAIS       = 'BR'

# Volume padrão (fixo)
ME_VOL_ALTURA      = 2
ME_VOL_LARGURA     = 12
ME_VOL_COMPRIMENTO = 17
ME_VOL_PESO        = 0.5
```

---

## 6. Arquivos a criar

```
app/Config/MelhorEnvio.php
app/Services/MelhorEnvioService.php
app/Models/MelhorEnvioCredentialModel.php
app/Models/MelhorEnvioLogModel.php
app/Entities/MelhorEnvioCredential.php
app/Controllers/MelhorEnvio.php           # OAuth callback + webhook
app/Controllers/PedidosEnvio.php          # endpoints AJAX da modal
app/Database/Migrations/2026-06-19-100000_CreateMelhorEnvioCredentialsTable.php
app/Database/Migrations/2026-06-19-100100_CreateMelhorEnvioLogsTable.php
app/Database/Migrations/2026-06-19-100200_AddMelhorEnvioColumnsToPedidos.php
```

E modificar:
- `app/Config/Routes.php` — novas rotas
- `app/Views/Pedidos/ingressos.php` — substituir input livre por wizard
- `app/Views/Pedidos/email_rastreio.php` — incluir código de rastreio e link
- `app/Controllers/Pedidos.php` — remover método `rastreio()` antigo (ou marcar como fallback)

---

## 7. `MelhorEnvioService` — esqueleto

```php
namespace App\Services;

class MelhorEnvioService
{
    private string $baseUrl;
    private string $userAgent;
    private \App\Models\MelhorEnvioCredentialModel $credModel;
    private \App\Models\MelhorEnvioLogModel $logModel;

    public function __construct()
    {
        $this->baseUrl = env('CI_ENVIRONMENT') === 'production'
            ? 'https://melhorenvio.com.br'
            : 'https://sandbox.melhorenvio.com.br';
        $this->userAgent = 'MundoDream Backoffice (' . env('MELHOR_ENVIO_CONTATO') . ')';
        $this->credModel = new \App\Models\MelhorEnvioCredentialModel();
        $this->logModel  = new \App\Models\MelhorEnvioLogModel();
    }

    // ------- OAuth -------
    public function authorizeUrl(string $state): string;
    public function exchangeCode(string $code): void;        // grava em melhor_envio_credentials
    public function refreshIfNeeded(): void;                 // refresh quando faltar < 24h

    // ------- Operações -------
    public function cotar(array $to, ?array $options = []): array;
    public function adicionarAoCarrinho(int $pedidoId, int $servicoId, array $to): string; // → cart_item_id
    public function checkout(array $orderIds): array;
    public function gerarEtiqueta(array $orderIds): array;
    public function imprimirEtiqueta(array $orderIds, string $mode = 'private'): string;
    public function rastrear(array $orderIds): array;

    // ------- Helpers -------
    private function payloadVolumePadrao(): array
    {
        return [
            'height' => (float) env('ME_VOL_ALTURA'),
            'width'  => (float) env('ME_VOL_LARGURA'),
            'length' => (float) env('ME_VOL_COMPRIMENTO'),
            'weight' => (float) env('ME_VOL_PESO'),
        ];
    }

    private function payloadRemetente(): array
    {
        return [
            'name'          => env('ME_FROM_NOME'),
            'email'         => env('ME_FROM_EMAIL'),
            'phone'         => env('ME_FROM_TELEFONE'),
            'document'      => env('ME_FROM_DOCUMENTO'),
            'address'       => env('ME_FROM_ENDERECO'),
            'number'        => env('ME_FROM_NUMERO'),
            'complement'    => env('ME_FROM_COMPLEMENTO'),
            'district'      => env('ME_FROM_BAIRRO'),
            'city'          => env('ME_FROM_CIDADE'),
            'state_abbr'    => env('ME_FROM_ESTADO'),
            'postal_code'   => env('ME_FROM_CEP'),
            'country_id'    => env('ME_FROM_PAIS'),
        ];
    }

    private function declaracaoConteudo(int $pedidoId): array
    {
        // Agrupa ingressos físicos do pedido (exclui cinemark/adicional/produto/'')
        $sql = "SELECT i.nome, COUNT(*) AS qtd, AVG(i.valor) AS valor_unit
                FROM ingressos i
                WHERE i.pedido_id = ?
                  AND i.tipo NOT IN ('cinemark','adicional','produto','')
                  AND i.ticket_id != 608
                GROUP BY i.nome";
        $rows = $this->credModel->db->query($sql, [$pedidoId])->getResultArray();

        return array_map(fn($r) => [
            'name'         => $r['nome'],
            'quantity'     => (int) $r['qtd'],
            'unitary_value'=> (float) $r['valor_unit'],
        ], $rows);
    }

    private function request(string $method, string $path, array $body = [], ?int $pedidoId = null): array
    {
        $this->refreshIfNeeded();
        $cred = $this->credModel->first();

        $url = $this->baseUrl . $path;
        $headers = [
            'Authorization: Bearer ' . $cred->access_token,
            'Accept: application/json',
            'Content-Type: application/json',
            'User-Agent: ' . $this->userAgent,
        ];

        $t0 = microtime(true);
        // … cURL exec …
        $duracao = (int) round((microtime(true) - $t0) * 1000);

        // LOG OBRIGATÓRIO
        $this->logModel->insert([
            'pedido_id'     => $pedidoId,
            'endpoint'      => $path,
            'http_method'   => $method,
            'request_body'  => json_encode($body, JSON_UNESCAPED_UNICODE),
            'response_body' => $respBody,
            'http_status'   => $httpCode,
            'duracao_ms'    => $duracao,
            'erro'          => $erro ?: null,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        log_message('info', '[ME] ' . $method . ' ' . $path . ' → ' . $httpCode . ' (' . $duracao . 'ms)');

        if ($httpCode >= 400) {
            log_message('error', '[ME] erro ' . $httpCode . ' em ' . $path . ' — ' . $respBody);
            throw new \RuntimeException('Falha Melhor Envio: HTTP ' . $httpCode);
        }
        return json_decode($respBody, true);
    }
}
```

> **Regra de ouro:** nenhum chamado à API ME pode passar fora de `request()`. Isso garante log uniforme.

---

## 8. Rotas

```php
// app/Config/Routes.php

$routes->group('melhor-envio', function ($routes) {
    $routes->get('conectar',  'MelhorEnvio::conectar');   // admin clica 1x
    $routes->get('callback',  'MelhorEnvio::callback');   // OAuth redirect
    $routes->get('status',    'MelhorEnvio::status');     // diag: token + saldo
    $routes->post('webhook',  'MelhorEnvio::webhook');    // recebe eventos
});

$routes->group('pedidos-envio', function ($routes) {
    $routes->post('cotar/(:num)',          'PedidosEnvio::cotar/$1');
    $routes->post('comprar/(:num)',        'PedidosEnvio::comprar/$1');
    $routes->get('etiqueta/(:num)',        'PedidosEnvio::etiqueta/$1');
    $routes->post('marcar-postado/(:num)', 'PedidosEnvio::marcarPostado/$1');
    $routes->post('cancelar/(:num)',       'PedidosEnvio::cancelar/$1');
});
```

> **Importante:** a rota `melhor-envio/webhook` precisa estar **fora do filtro CSRF**.

---

## 9. Fluxo na modal `/pedidos/ingressos/{id}`

Substituir o input livre `rastreio` por um wizard:

### Passo 1 — Cotar
- Botão **"Cotar Frete"** → `POST pedidos-envio/cotar/{id}`
- Backend monta `to` a partir do `enderecos`/`clientes` do pedido, `from` do `.env`, volume padrão, e chama `cotar()`
- Frontend mostra `<select>` com `{servico, transportadora, prazo, preço}` ordenado por preço

### Passo 2 — Comprar + Gerar
- Admin escolhe serviço → botão **"Comprar Frete e Gerar Etiqueta"**
- `POST pedidos-envio/comprar/{id}` faz, em sequência atômica:
  1. `adicionarAoCarrinho(servico)` → `cart_item_id`
  2. `checkout([cart_item_id])` → `me_order_id`
  3. `gerarEtiqueta([me_order_id])`
  4. `imprimirEtiqueta([me_order_id])` → URL do PDF
  5. Persiste `me_order_id`, `me_protocol`, `me_servico_*`, `me_etiqueta_url`, `me_valor_frete`, `me_status = 'generated'`
- Em caso de erro, abortar e mostrar mensagem (mas os logs ficam)

### Passo 3 — Imprimir
- Botão **"Baixar Etiqueta"** abre `me_etiqueta_url` em nova aba
- Botão **"Marcar como Postado"** (fallback caso o webhook não chegue): `POST pedidos-envio/marcar-postado/{id}` força o status e dispara o e-mail

### Card resumo
Mostrar na página do pedido um card "Envio Melhor Envio" com: serviço, valor, status atual (badge), data de postagem, código de rastreio (com link `melhorrastreio.com.br/rastreio/{tracking}`).

---

## 10. Webhook (atualização passiva)

`MelhorEnvio::webhook()`:

```php
public function webhook()
{
    $raw   = $this->request->getBody();
    $sig   = $this->request->getHeaderLine('X-ME-Signature');
    $calc  = hash_hmac('sha256', $raw, env('MELHOR_ENVIO_WEBHOOK_SECRET'));

    if (! hash_equals($calc, $sig)) {
        log_message('warning', '[ME-WEBHOOK] assinatura inválida');
        return $this->response->setStatusCode(401);
    }

    $payload = json_decode($raw, true);
    log_message('info', '[ME-WEBHOOK] ' . $payload['event']);

    $pedidoModel = new \App\Models\PedidoModel();
    $pedido = $pedidoModel->where('me_order_id', $payload['data']['id'])->first();
    if (! $pedido) {
        return $this->response->setStatusCode(200);
    }

    $update = [];
    switch ($payload['event']) {
        case 'order.generated':
            $update['me_status']       = 'generated';
            $update['me_etiqueta_url'] = $payload['data']['tag_url'] ?? null;
            break;
        case 'order.posted':
            $update['me_status']    = 'posted';
            $update['rastreio']     = $payload['data']['tracking'] ?? null;
            $update['me_postado_em']= date('Y-m-d H:i:s');
            // Dispara o e-mail (existente) só agora
            (new \App\Controllers\Pedidos())->enviaEmailRastreio($pedido);
            break;
        case 'order.delivered':
            $update['me_status']     = 'delivered';
            $update['me_entregue_em']= date('Y-m-d H:i:s');
            break;
        case 'order.cancelled':
            $update['me_status'] = 'cancelled';
            break;
    }

    if ($update) {
        $pedidoModel->update($pedido->id, $update);
    }

    return $this->response->setStatusCode(200); // < 6s
}
```

---

## 11. E-mail enriquecido

Atualizar [email_rastreio.php](../app/Views/Pedidos/email_rastreio.php) para receber `$pedido` e renderizar:
- Código de rastreio em destaque
- Botão "Acompanhar Entrega" → `https://www.melhorrastreio.com.br/rastreio/{rastreio}`
- Transportadora (`me_servico_nome`) e prazo estimado

E-mail só é disparado a partir do webhook `order.posted` (não mais no momento de "salvar rastreio" — esse momento deixou de existir).

---

## 12. Logging — política

Camada | Como | Onde
--- | --- | ---
Toda chamada API (request+response) | `MelhorEnvioLogModel::insert()` no `request()` do Service | Tabela `melhor_envio_logs`
Resumo curto (status, latência) | `log_message('info', '[ME] …')` | `writable/logs/log-YYYY-MM-DD.log`
Erros HTTP ≥ 400 | `log_message('error', '[ME] …')` | idem
Webhooks recebidos | `log_message('info', '[ME-WEBHOOK] event=…')` + linha em `melhor_envio_logs` com `endpoint = 'webhook:{event}'` | ambos
Assinatura inválida | `log_message('warning', …)` | `writable/logs/…`
OAuth (refresh, callback) | `log_message('info', '[ME-OAUTH] …')` | `writable/logs/…`

Tela admin `/melhor-envio/status` lista as últimas N linhas da tabela `melhor_envio_logs` com filtro por pedido — facilita diagnóstico.

---

## 13. Fases de entrega

| Fase | Entregável | Critério de aceite |
| --- | --- | --- |
| 1. Fundação | Migrations + Service esqueleto + OAuth callback + tela `/melhor-envio/conectar` | Admin conecta a conta uma vez; tokens persistem; refresh agendado |
| 2. Cotação | Botão "Cotar Frete" exibindo lista de serviços | Cotação real funcionando em produção |
| 3. Compra + etiqueta | Wizard completo gerando label + persistindo `me_order_id`, etiqueta_url | Etiqueta PDF baixável; pedido marcado `generated` |
| 4. Webhook + e-mail | Endpoint webhook validando assinatura; e-mail só dispara no `order.posted` | Status do pedido evolui sozinho; cliente recebe e-mail com rastreio real |
| 5. Painel de envios | Listagem `/envios` filtrável por status, link de reimpressão de etiqueta | Operador consegue ver todos os envios em andamento sem abrir cada pedido |
| 6. Polimento | Cancelamento (`order.cancel`), logística reversa, badges no card | — |

---

## 14. Pontos de atenção

- **Token expirado em produção:** o `refreshIfNeeded()` previne, mas se o refresh token também expirar (>45 dias sem uso), o admin precisa reconectar. Alerta no `/melhor-envio/status` quando faltarem < 7 dias.
- **Webhook idempotente:** mesmo evento pode chegar mais de uma vez. Atualizar `update` só se mudou o status (`me_status != $novo`).
- **Endereço incompleto:** validar antes de cotar — número, CEP e estado obrigatórios. Se faltar, abortar com mensagem clara em vez de mandar request inútil pra API.
- **Dimensões customizadas:** apesar de fixas hoje, deixar o cálculo do volume isolado em `payloadVolumePadrao()` para futuro override por tipo de produto.
- **Cancelamento:** se o envio foi pago mas ainda não postado, dá pra cancelar e creditar de volta na carteira. Expor isso no painel só na fase 6.
- **Soft delete do pedido:** ao cancelar um pedido com envio ativo, alertar o operador para cancelar o envio antes.

---

## 15. Variáveis fixas — referência rápida

| Item | Valor | Origem |
| --- | --- | --- |
| Altura | 2 cm | `ME_VOL_ALTURA` |
| Largura | 12 cm | `ME_VOL_LARGURA` |
| Comprimento | 17 cm | `ME_VOL_COMPRIMENTO` |
| Peso | 0,5 kg | `ME_VOL_PESO` |
| Remetente | Endereço do escritório | `ME_FROM_*` |
| Saldo carteira | Pré-carregado externamente | painel ME |
| Itens declarados | `SELECT i.nome, COUNT(*), AVG(i.valor) FROM ingressos i WHERE pedido_id=? AND tipo NOT IN ('cinemark','adicional','produto','') GROUP BY nome` | tabela `ingressos` |
