# API de Acessos - Documentação

## Visão Geral

API usada pelo app **mobile de credenciamento/portaria** (e por qualquer front) para:

1. Ler o código de um ingresso e registrar a entrada no evento.
2. Exibir na UI as informações do titular, do ingresso e dos produtos adicionais (order bumps) atrelados ao pedido.
3. Marcar/desmarcar order bumps como **usados** (entrega de brinde, kit, produto vinculado ao ingresso).

Base URL de produção: `https://backoffice.mundodream.com.br`

Autenticação: **não requer JWT** para os endpoints atuais (validação vai por `codigo` do ingresso + `evento_id`). Se o front rodar em ambiente público, use HTTPS e o filtro `secureApi` que a rota já expõe.

---

## Endpoints

### 1. Ler ingresso e registrar acesso

```
POST /api/acessos/check
```

Registra um novo lançamento em `acessos`, independente de já haver acessos anteriores para o mesmo ingresso. Se já houver, o campo `code` da resposta vem `ALREADY_USED` e o array `warnings` traz um aviso — cabe ao front decidir se libera ou não (o padrão é permitir mas alertar).

**Content-Type:** `application/json` (também aceita `form-data`).

**Body:**

| Campo       | Tipo    | Obrigatório | Descrição                                                             |
| ----------- | ------- | ----------- | --------------------------------------------------------------------- |
| `evento_id` | integer | Sim         | ID do evento onde a validação está sendo feita.                       |
| `codigo`    | string  | Sim         | Código do ingresso lido (QR code / código de barras / digitado).      |
| `operador`  | integer | Sim         | ID do usuário do backoffice que está registrando o acesso.            |
| `tipo`      | string  | Não         | Tipo de lançamento. Padrão: `"ACESSO"`. Outros aceitos: `"SAIDA"`.    |

**Exemplo de body:**

```json
{
  "evento_id": 77,
  "codigo": "ABC123XYZ",
  "operador": 9,
  "tipo": "ACESSO"
}
```

#### 200 OK — sucesso

```json
{
  "success": true,
  "code": "ALLOWED",
  "access_count_before": 0,
  "ingresso": {
    "id": 4321,
    "codigo": "ABC123XYZ",
    "nome": "VIP FULL - Sábado",
    "participante": "João da Silva",
    "ticket": {
      "tipo": "vip",
      "dia": "sab",
      "data_inicio": "2026-07-11",
      "data_fim": "2026-07-11"
    },
    "pedido": {
      "id": 8891,
      "evento_id": 77,
      "status": "CONFIRMED",
      "user_id": 512,
      "frete": 1,
      "rastreio": "OS123456789BR"
    }
  },
  "order_bumps": [
    {
      "id": 10,
      "order_bump_id": 3,
      "nome": "Copo colecionável",
      "descricao": "Copo Dreamfest edição limitada",
      "imagem": "copo-df26.jpg",
      "tipo": "produto",
      "quantidade": 1,
      "preco_unitario": 45.00,
      "usado": false,
      "usado_em": null
    },
    {
      "id": 11,
      "order_bump_id": 5,
      "nome": "Pôster oficial",
      "descricao": null,
      "imagem": null,
      "tipo": "produto",
      "quantidade": 2,
      "preco_unitario": 30.00,
      "usado": true,
      "usado_em": "2026-07-11 10:23:41"
    }
  ],
  "display": {
    "titulo": "Acesso VIP FULL",
    "liberado_a_partir": "10:00",
    "material": [],
    "observacao": "MATERIAL ENTREGUE VIA SEDEX"
  },
  "warnings": []
}
```

Campos importantes:

- **`code`**: sempre um dos valores abaixo — use para o "semáforo" da UI:
  - `"ALLOWED"` — primeira leitura, entrada normal.
  - `"ALREADY_USED"` — o ingresso já tinha registros de `ACESSO` antes desta leitura. O acesso foi registrado do mesmo jeito; use `warnings[0]` para exibir o alerta.
- **`access_count_before`**: nº de acessos que existiam *antes* deste. Sirva "1ª entrada", "2ª entrada" etc. calculando `access_count_before + 1` se quiser mostrar a ordinal desta leitura.
- **`ingresso.participante`**: nome escrito no ingresso; se estiver vazio, cai automaticamente no nome do cliente vinculado ao `pedido.user_id`; se não houver cliente, usa `nome_fantasia`/`nome` do **expositor** vinculado. Se nem cliente nem expositor existirem, vem `null`.
- **`ingresso.pedido.id`**: use este `pedido_id` na chamada do `orderbump/{id}/usar` (parâmetro `pedido_id` no body — protege contra id de outro pedido).
- **`order_bumps`**: array com todos os produtos adicionais comprados no pedido. Cada item tem `usado` (bool) e `usado_em` (datetime ou `null`). Use isso para renderizar checkboxes/toggles.
- **`display`**: bloco pronto para a UI de "acesso liberado" (título, materiais que devem ser entregues, etc.). Consulte a seção [Bloco `display`](#bloco-display) abaixo.
- **`warnings`**: alertas em texto simples. Renderize como banner amarelo/vermelho.

#### 404 Not Found — ingresso inválido / fora da janela

```json
{
  "success": false,
  "code": "NOT_FOUND",
  "message": "O ingresso não foi localizado ou não está válido para hoje."
}
```

Cenários:

- Código não existe para este `evento_id`.
- Pedido do ingresso está com status diferente de `CONFIRMED`, `RECEIVED`, `paid` ou `RECEIVED_IN_CASH`.
- Ticket **não combo** com `data_inicio` diferente da data de hoje (fuso `America/Sao_Paulo`).
- Ticket **combo** cuja janela `data_inicio` ↔ `data_fim` não abrange o dia de hoje.

O front deve exibir "Acesso negado" (semáforo vermelho) sem opção de bypass.

#### 400 Bad Request / 422 Unprocessable Entity — payload inválido

```json
{
  "status": 400,
  "error": 400,
  "messages": {
    "evento_id": "The evento_id field is required.",
    "codigo": "The codigo field is required."
  }
}
```

Validação:

- `evento_id`: `required|is_natural_no_zero`
- `codigo`: `required|string`
- `operador`: `required|is_natural_no_zero`
- `tipo`: `permit_empty|string`

#### 500 Internal Server Error — falha ao salvar

```json
{
  "status": 500,
  "error": 500,
  "messages": {
    "error": "Não foi possível registrar o acesso."
  }
}
```

#### 405 Method Not Allowed — método errado

Só aceita `POST`. Qualquer outro método retorna 405.

---

### 2. Marcar / desmarcar order bump como usado

```
POST /api/acessos/orderbump/{id}/usar
```

Onde `{id}` é o `order_bumps[].id` retornado pelo endpoint `check`.

**Uso:** quando o operador entrega o produto ao participante (copo, pôster, credencial extra etc.), o front chama este endpoint para persistir a entrega.

**Body (JSON, opcional):**

| Campo       | Tipo    | Obrigatório | Descrição                                                                                       |
| ----------- | ------- | ----------- | ----------------------------------------------------------------------------------------------- |
| `usado`     | boolean | Não         | `true` marca como usado, `false` desmarca. Se **não** for enviado, o endpoint **alterna** o estado atual. |
| `pedido_id` | integer | Recomendado | ID do pedido dono do order bump. Se enviado, o servidor valida que o OB pertence a esse pedido. |

**Exemplo — marcar explicitamente:**

```json
{
  "usado": true,
  "pedido_id": 8891
}
```

**Exemplo — apenas alternar (toggle):**

```json
{
  "pedido_id": 8891
}
```

#### 200 OK

```json
{
  "success": true,
  "code": "MARKED_USED",
  "order_bump": {
    "id": 10,
    "pedido_id": 8891,
    "order_bump_id": 3,
    "nome": "Copo colecionável",
    "descricao": "Copo Dreamfest edição limitada",
    "imagem": "copo-df26.jpg",
    "tipo": "produto",
    "quantidade": 1,
    "preco_unitario": 45.00,
    "usado": true,
    "usado_em": "2026-07-11 10:23:41"
  }
}
```

Campos:

- **`code`**: `"MARKED_USED"` quando ficou marcado, `"UNMARKED_USED"` quando ficou desmarcado.
- **`order_bump`**: o registro atualizado (mesma estrutura do array `order_bumps` do endpoint `check`). Substitua no state do front pelo item de mesmo `id`.

#### 404 Not Found — order bump não existe ou não pertence ao pedido

```json
{
  "success": false,
  "code": "NOT_FOUND",
  "message": "Order bump não encontrado para o pedido informado."
}
```

Acontece quando:

- O `id` não existe na tabela `pedido_order_bumps`.
- Foi enviado `pedido_id` no body e ele **não bate** com o `pedido_id` do OB (proteção contra request cruzado).

#### 422 Unprocessable Entity — id inválido

```json
{
  "status": 422,
  "messages": { "id": "ID do order bump inválido." }
}
```

#### 500 Internal Server Error — falha ao gravar

```json
{
  "status": 500,
  "messages": { "error": "Não foi possível atualizar o order bump." }
}
```

---

## Bloco `display`

O endpoint `check` monta um bloco `display` que traduz o `ingresso.nome` em orientações práticas para o operador. O front pode usar direto sem lógica adicional.

Estrutura:

```json
{
  "titulo": "Acesso VIP",
  "liberado_a_partir": "10:00",
  "material": [
    "Credencial + Cordão colecionável",
    "Pôster Colecionável",
    "Ingresso Holográfico",
    "Copo Colecionável",
    "Pulseira RFID (favor vincular)"
  ],
  "observacao": null
}
```

Regras aplicadas:

| Nome do ingresso contém | `titulo`             | `liberado_a_partir` |
| ----------------------- | -------------------- | ------------------- |
| `VIP` **e** tem rastreio | `Acesso VIP FULL`    | `10:00`             |
| `VIP`                    | `Acesso VIP`         | `10:00`             |
| `PREMIUM`                | `Acesso PREMIUM`     | `10:00`             |
| `EPIC`                   | `Acesso EPIC PASS`   | `10:00`             |
| `COSPLAY`                | `Acesso COSPLAY`     | `11:00`             |
| _default_ (BASIC)        | `Acesso BASIC`       | `11:00`             |

- Se o pedido tem `rastreio` preenchido (material enviado via SEDEX), `material` vem `[]` e `observacao` = `"MATERIAL ENTREGUE VIA SEDEX"`.
- Se `rastreio` está vazio, `material` vem preenchido com a lista de itens que o operador deve entregar naquele momento e `observacao` = `null`.

O front pode ignorar `display` e derivar o que quiser a partir de `ingresso.nome` + `pedido.rastreio`, mas geralmente é mais simples usar direto.

---

## Fluxo recomendado no app

```
┌────────────────────────────────────────────────────┐
│ 1. Operador loga no app → obtém user_id (operador) │
│    e event_id atual (evento onde vai trabalhar).   │
└────────────────────────────────────────────────────┘
                    │
                    ▼
┌────────────────────────────────────────────────────┐
│ 2. Operador escaneia QR / digita código.           │
└────────────────────────────────────────────────────┘
                    │
                    ▼
┌────────────────────────────────────────────────────┐
│ 3. POST /api/acessos/check                         │
│    { evento_id, codigo, operador, tipo: "ACESSO" } │
└────────────────────────────────────────────────────┘
                    │
       ┌────────────┼─────────────┐
       ▼            ▼             ▼
   200 ALLOWED  200 ALREADY   404 NOT_FOUND
   (verde)      _USED         (vermelho)
                (amarelo)
                    │
                    ▼
┌────────────────────────────────────────────────────┐
│ 4. Renderiza:                                      │
│    • ingresso.participante (nome grande)           │
│    • display.titulo + display.liberado_a_partir    │
│    • display.material (checklist)                  │
│    • display.observacao (se houver)                │
│    • warnings (se houver)                          │
│    • order_bumps (produtos adicionais)             │
└────────────────────────────────────────────────────┘
                    │
                    ▼
┌────────────────────────────────────────────────────┐
│ 5. Ao entregar um order bump:                      │
│    POST /api/acessos/orderbump/{id}/usar           │
│    { usado: true, pedido_id }                      │
│    → atualiza o item no state do app               │
└────────────────────────────────────────────────────┘
```

---

## Detalhes técnicos importantes

### Idempotência

- O endpoint `check` **sempre** registra um novo lançamento em `acessos`, mesmo em `ALREADY_USED`. Se o operador escanear o mesmo código 3 vezes, ficam 3 linhas em `acessos`.
- Se o front quiser evitar duplicatas por engano (dupla leitura em segundos), implemente um **debounce local** (ex.: bloquear leitura do mesmo `codigo` por 3s).

### Order bumps — estado partilhado

- `usado` é um flag em `pedido_order_bumps`. Ele **persiste entre leituras**: se o operador A marcou como usado às 10h e o operador B lê o mesmo ingresso às 15h, o response já vem com `usado: true, usado_em: "2026-07-11 10:23:41"`.
- Como o toggle sem `usado` explícito alterna o estado, permite desfazer entregas erradas com facilidade.
- **Recomendação de UX:** ao mostrar um OB já usado, deixe visualmente diferente (opacidade menor, badge "Já entregue") e peça confirmação antes de desmarcar.

### Fuso horário

Toda a validação de data usa `America/Sao_Paulo` (definido em `Time::today('America/Sao_Paulo')`). Não é preciso enviar timezone no request.

### Códigos considerados válidos para o pedido

Um ingresso só passa na validação se o `pedido.status` estiver em:

- `CONFIRMED`
- `RECEIVED`
- `paid`
- `RECEIVED_IN_CASH`

Qualquer outro status (`PENDING`, `REFUNDED`, `CHARGEBACK`, etc.) → 404 `NOT_FOUND`.

### Fallback de participante

O front **não precisa** se preocupar em buscar dados do cliente ou expositor separadamente para exibir o nome. O backend já resolve a cadeia:

```
ingressos.participante  →  clientes.nome  →  expositores.nome_fantasia  →  expositores.nome  →  null
```

Isso significa que ingressos de parceiros/expositores (que não têm cliente vinculado) mostram o nome do expositor automaticamente.

---

## Exemplos rápidos por linguagem

### JavaScript (fetch)

```js
// Ler ingresso
const res = await fetch('https://backoffice.mundodream.com.br/api/acessos/check', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    evento_id: 77,
    codigo: 'ABC123XYZ',
    operador: 9,
    tipo: 'ACESSO',
  }),
});
const data = await res.json();

if (data.success) {
  if (data.code === 'ALLOWED') showGreen(data);
  else showYellow(data); // ALREADY_USED
} else {
  showRed(data.message);
}

// Marcar order bump como usado
await fetch(
  `https://backoffice.mundodream.com.br/api/acessos/orderbump/${orderBump.id}/usar`,
  {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ usado: true, pedido_id: data.ingresso.pedido.id }),
  }
);
```

### Dart / Flutter (http)

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

Future<Map<String, dynamic>> checkAcesso({
  required int eventoId,
  required String codigo,
  required int operador,
  String tipo = 'ACESSO',
}) async {
  final res = await http.post(
    Uri.parse('https://backoffice.mundodream.com.br/api/acessos/check'),
    headers: {'Content-Type': 'application/json'},
    body: jsonEncode({
      'evento_id': eventoId,
      'codigo': codigo,
      'operador': operador,
      'tipo': tipo,
    }),
  );
  return jsonDecode(res.body) as Map<String, dynamic>;
}

Future<Map<String, dynamic>> marcarOrderBump({
  required int obId,
  required int pedidoId,
  bool? usado,
}) async {
  final res = await http.post(
    Uri.parse(
      'https://backoffice.mundodream.com.br/api/acessos/orderbump/$obId/usar',
    ),
    headers: {'Content-Type': 'application/json'},
    body: jsonEncode({
      if (usado != null) 'usado': usado,
      'pedido_id': pedidoId,
    }),
  );
  return jsonDecode(res.body) as Map<String, dynamic>;
}
```

### cURL

```bash
# Ler ingresso
curl -X POST https://backoffice.mundodream.com.br/api/acessos/check \
  -H "Content-Type: application/json" \
  -d '{
    "evento_id": 77,
    "codigo": "ABC123XYZ",
    "operador": 9,
    "tipo": "ACESSO"
  }'

# Marcar order bump como usado
curl -X POST https://backoffice.mundodream.com.br/api/acessos/orderbump/10/usar \
  -H "Content-Type: application/json" \
  -d '{
    "usado": true,
    "pedido_id": 8891
  }'
```

---

## Endpoint auxiliar — Acesso à Sala VIP

```
POST /api/acessos/salavip
```

Valida a entrada de um convidado na **sala VIP** durante o evento, usando a **pulseira RFID** (`credenciais.codigo`) em vez do QR do ingresso. Regra específica: só libera se o ingresso vinculado tiver `"VIP"` no nome.

Não valida janela de data do ticket (a sala VIP fica ativa durante todo o evento).

**Body:**

| Campo       | Tipo    | Obrigatório | Descrição                                             |
| ----------- | ------- | ----------- | ----------------------------------------------------- |
| `evento_id` | integer | Sim         | ID do evento.                                         |
| `codigo`    | string  | Sim         | Código da **pulseira** lida (`credenciais.codigo`).   |
| `operador`  | integer | Sim         | ID do operador da portaria da sala VIP.               |
| `tipo`      | string  | Não         | Tipo do lançamento. Padrão `"ACESSO"`.                |

**Exemplo:**

```json
{
  "evento_id": 77,
  "codigo": "RFID-A1B2C3",
  "operador": 9,
  "tipo": "ACESSO"
}
```

#### 200 OK — liberado

```json
{
  "success": true,
  "code": "ALLOWED",
  "access_count_before": 0,
  "ingresso": {
    "id": 4321,
    "codigo": "ABC123XYZ",
    "nome": "VIP FULL - Sábado",
    "participante": "João da Silva",
    "credencial": "RFID-A1B2C3",
    "credencial_id": 987,
    "pedido": {
      "id": 8891,
      "evento_id": 77,
      "status": "CONFIRMED",
      "user_id": 512,
      "frete": 1,
      "rastreio": "OS123456789BR"
    }
  },
  "ticket_alimentacao": {
    "retirado": false,
    "retirado_em": null,
    "mostrar_checkbox": true,
    "credencial_id": 987
  },
  "display": {
    "titulo": "Acesso Sala VIP",
    "atencao": "Entrada permitida apenas com pulseira inviolada."
  },
  "warnings": []
}
```

- `code` = `"ALREADY_USED"` quando `access_count_before > 0` — mesma semântica do `/api/acessos/check`.
- **`ticket_alimentacao`**: estado do ticket de alimentação vinculado à credencial (persistido em `credenciais.ticket_alimentacao`).
  - `retirado` (bool): se o ticket já foi entregue.
  - `retirado_em` (datetime|null): quando foi marcado.
  - `mostrar_checkbox` (bool): **dica de UX** — é `true` apenas quando é a 1ª leitura desta pulseira **e** o ticket ainda não foi retirado. Use este flag para decidir se exibe o checkbox no app. Se `false`, o app pode omitir o checkbox (mas ainda pode mostrar "já retirado em ..." se `retirado === true`).
  - `credencial_id` (int): ID da credencial — usar no endpoint de marcação abaixo.

#### 403 Forbidden — ingresso não é VIP

```json
{
  "success": false,
  "code": "NOT_VIP",
  "message": "O ingresso não possui privilégios VIP.",
  "ingresso": {
    "id": 4321,
    "nome": "Ingresso BASIC - Sábado"
  }
}
```

O front deve mostrar semáforo **vermelho** e o motivo — a pulseira até é válida, mas o ingresso vinculado não é VIP.

#### 404 Not Found — credencial inexistente

```json
{
  "success": false,
  "code": "NOT_FOUND",
  "message": "Credencial não localizada ou pedido inválido."
}
```

Também retorna 404 quando a credencial existe mas o `pedido.status` não está entre `CONFIRMED / RECEIVED / paid / RECEIVED_IN_CASH`.

#### 400/422/500

Mesmo padrão dos demais endpoints (validação de payload, falha ao gravar).

### Endpoint auxiliar — Marcar ticket de alimentação

```
POST /api/acessos/salavip/ticket-alimentacao/{credencial_id}
```

Marca (ou desmarca) a retirada do ticket de alimentação vinculado à credencial. Usar depois que o operador entregar (ou desfazer entrega errada).

**Body (JSON, opcional):**

| Campo      | Tipo    | Obrigatório | Descrição                                                                                       |
| ---------- | ------- | ----------- | ----------------------------------------------------------------------------------------------- |
| `retirado` | boolean | Não         | `true` marca como retirado, `false` desmarca. Se **não** for enviado, alterna o estado atual.   |

**Exemplo — marcar:**
```json
{ "retirado": true }
```

**Exemplo — apenas alternar:** body vazio ou `{}`.

#### 200 OK

```json
{
  "success": true,
  "code": "MARKED_RETIRADO",
  "ticket_alimentacao": {
    "credencial_id": 987,
    "ingresso_id": 4321,
    "retirado": true,
    "retirado_em": "2026-07-01 15:23:41"
  }
}
```

- `code` = `"UNMARKED_RETIRADO"` quando desmarca.

#### 404 Not Found — credencial inexistente

```json
{
  "success": false,
  "code": "NOT_FOUND",
  "message": "Credencial não encontrada."
}
```

#### 422 / 405 / 500

Padrão dos demais endpoints.

### Fluxo recomendado no app (sala VIP + ticket alimentação)

```
1. Operador lê a pulseira → POST /api/acessos/salavip
2. Backend retorna response.ticket_alimentacao
3. Front decide:
   • Se mostrar_checkbox === true:
       └─ exibe checkbox "Ticket de alimentação entregue"
       └─ ao marcar + confirmar → POST /api/acessos/salavip/ticket-alimentacao/{credencial_id}
                                   body: { retirado: true }
   • Se retirado === true:
       └─ exibe badge "Ticket já retirado em <retirado_em>"
   • Se retirado === false && mostrar_checkbox === false (ex: 2ª+ leitura):
       └─ exibe botão discreto "Marcar ticket retirado" que abre o mesmo POST
```

### Diferenças entre os 2 endpoints de check

| Aspecto              | `POST /api/acessos/check`            | `POST /api/acessos/salavip`             |
| -------------------- | ------------------------------------ | --------------------------------------- |
| Uso típico           | Entrada geral no evento (bilheteria) | Entrada na sala VIP durante o evento    |
| Campo lido           | `ingressos.codigo` (QR do ingresso)  | `credenciais.codigo` (pulseira RFID)    |
| Filtro por categoria | Aceita qualquer ingresso             | **Só libera se `nome` contém "VIP"**    |
| Janela de data       | Valida `tickets.data_inicio/data_fim`| Ignora — sala fica ativa todo o evento  |
| Order bumps          | Sim (`order_bumps`)                  | Não                                     |
| Bloco `display`      | Rico (título/material/observação)    | Simples (título + atenção fixa)         |
| Códigos possíveis    | `ALLOWED` / `ALREADY_USED` / `NOT_FOUND` | `ALLOWED` / `ALREADY_USED` / `NOT_VIP` / `NOT_FOUND` |

O `usuario_id` gravado em `acessos` é o mesmo em ambos os casos (`ingressos.user_id`) — permite que o histórico de acessos do participante junte entrada geral + entradas na sala VIP.

---

## Endpoint auxiliar — Validação de Meet & Greet

Não é parte do controller `Api\Acessos`, mas segue o mesmo padrão (sem JWT, operador identifica-se no body) e costuma ser usado pelo mesmo app de portaria.

```
POST /api/meets/validar
```

Valida uma reserva de Meet & Greet a partir do código lido. **Uso único** — se a reserva já foi validada antes, retorna `ALREADY_VALIDATED` e não altera nada.

**Body:**

| Campo      | Tipo    | Obrigatório | Descrição                                                       |
| ---------- | ------- | ----------- | --------------------------------------------------------------- |
| `code`     | string  | Sim         | Código da reserva (`queue_meet.code`, lido do QR do usuário).   |
| `operador` | integer | Não         | ID do operador (retornado como eco na resposta para auditoria). |

**Exemplo:**

```json
{ "code": "9K4H2Z7QP1", "operador": 9 }
```

#### 200 OK — validada agora

```json
{
  "success": true,
  "code": "VALIDATED",
  "operador": 9,
  "message": "Reserva validada com sucesso.",
  "reserva": {
    "id": 4210,
    "code": "9K4H2Z7QP1",
    "ordem": 12,
    "status": "VALIDADO",
    "artista": "Nome do Convidado",
    "dia": "sab",
    "tipo": "vip",
    "data_meet": "2026-07-11",
    "hora_inicial": "14:00:00",
    "usuario_id": 512,
    "usuario_nome": "João da Silva",
    "usuario_email": "joao@example.com",
    "ingresso_id": 4321,
    "ingresso_nome": "VIP FULL - Sábado",
    "ingresso_codigo": "ABC123XYZ",
    "event_id": 77
  }
}
```

- **`ordem`**: posição atribuída na fila do meet (max + 1). Use como número de senha visível pro operador ("Você é o 12º").

#### 200 OK — já validada antes

```json
{
  "success": true,
  "code": "ALREADY_VALIDATED",
  "ja_validado": true,
  "message": "Esta reserva já foi validada.",
  "reserva": { ... mesma estrutura acima, com a ordem original preservada ... }
}
```

- Note que `success` continua `true` — do ponto de vista do app, "a reserva existe e é conhecida". O front deve tratar visualmente diferente (amarelo em vez de verde) usando o `code`.

#### 404 Not Found — código inexistente

```json
{
  "success": false,
  "code": "NOT_FOUND",
  "message": "Código não encontrado."
}
```

#### 422 — payload inválido

```json
{
  "success": false,
  "code": "INVALID_PAYLOAD",
  "message": "Código não informado.",
  "errors": { "code": "O campo code é obrigatório." }
}
```

#### 405/500 — método errado ou falha

Mesmo padrão dos outros endpoints.

### Comparação — Ingresso vs Meet & Greet

| Aspecto              | `POST /api/acessos/check`                | `POST /api/meets/validar`                |
| -------------------- | ---------------------------------------- | ---------------------------------------- |
| Fonte do código      | `ingressos.codigo` (QR do ingresso)      | `queue_meet.code` (QR da reserva)        |
| Filtro por evento    | Obrigatório (`evento_id` no body)        | Não — busca global pelo `code`           |
| Reuso                | Aceita múltiplas leituras (log em `acessos`) | **Única** — segunda leitura devolve `ALREADY_VALIDATED` |
| Efeito colateral     | Insere linha em `acessos`                | Atualiza `queue_meet.ordem` + `status`   |
| Retorno principal    | `code = ALLOWED` / `ALREADY_USED`        | `code = VALIDATED` / `ALREADY_VALIDATED` |
| Order bumps          | Sim (`order_bumps`)                      | Não                                      |

Fluxo típico do app: se o operador lê um código e ele não bate com ingresso (`NOT_FOUND` em `acessos/check`), tentar `meets/validar` — se voltar `NOT_FOUND` também, aí sim é código inválido de verdade.

---

## Tabela-resumo dos códigos de resposta

| Endpoint                                | Status | `code`             | Significado                                       |
| --------------------------------------- | ------ | ------------------ | ------------------------------------------------- |
| `POST /api/acessos/check`               | 200    | `ALLOWED`          | Primeira leitura — libera entrada.                |
| `POST /api/acessos/check`               | 200    | `ALREADY_USED`     | Já lido antes — libera mas alerta.                |
| `POST /api/acessos/check`               | 404    | `NOT_FOUND`        | Código inválido / fora da janela / status errado. |
| `POST /api/acessos/check`               | 400/422| —                  | Payload incompleto ou tipo errado.                |
| `POST /api/acessos/check`               | 405    | —                  | Método diferente de POST.                         |
| `POST /api/acessos/check`               | 500    | —                  | Falha ao gravar em `acessos`.                     |
| `POST /api/acessos/salavip`             | 200    | `ALLOWED`          | Pulseira liberada na sala VIP (primeira leitura). |
| `POST /api/acessos/salavip`             | 200    | `ALREADY_USED`     | Pulseira já usada antes — libera com aviso.       |
| `POST /api/acessos/salavip`             | 403    | `NOT_VIP`          | Pulseira válida mas ingresso não é VIP.           |
| `POST /api/acessos/salavip`             | 404    | `NOT_FOUND`        | Credencial não encontrada ou pedido inválido.     |
| `POST /api/acessos/salavip`             | 400/422| —                  | Payload incompleto.                               |
| `POST /api/acessos/salavip`             | 500    | —                  | Falha ao gravar.                                  |
| `POST /api/acessos/salavip/ticket-alimentacao/{id}` | 200 | `MARKED_RETIRADO`   | Ticket marcado como retirado.                   |
| `POST /api/acessos/salavip/ticket-alimentacao/{id}` | 200 | `UNMARKED_RETIRADO` | Ticket desmarcado.                              |
| `POST /api/acessos/salavip/ticket-alimentacao/{id}` | 404 | `NOT_FOUND`         | Credencial inexistente.                         |
| `POST /api/acessos/salavip/ticket-alimentacao/{id}` | 422 | —                   | ID malformado.                                  |
| `POST /api/acessos/salavip/ticket-alimentacao/{id}` | 500 | —                   | Falha ao gravar.                                |
| `POST /api/acessos/orderbump/{id}/usar` | 200    | `MARKED_USED`      | Order bump ficou marcado como usado.              |
| `POST /api/acessos/orderbump/{id}/usar` | 200    | `UNMARKED_USED`    | Order bump ficou desmarcado.                      |
| `POST /api/acessos/orderbump/{id}/usar` | 404    | `NOT_FOUND`        | ID inválido ou não pertence ao `pedido_id`.       |
| `POST /api/acessos/orderbump/{id}/usar` | 422    | —                  | ID malformado.                                    |
| `POST /api/acessos/orderbump/{id}/usar` | 500    | —                  | Falha ao gravar.                                  |
| `POST /api/meets/validar`               | 200    | `VALIDATED`        | Reserva de M&G validada agora.                    |
| `POST /api/meets/validar`               | 200    | `ALREADY_VALIDATED`| Reserva já tinha sido validada antes.             |
| `POST /api/meets/validar`               | 404    | `NOT_FOUND`        | Código de reserva não encontrado.                 |
| `POST /api/meets/validar`               | 422    | `INVALID_PAYLOAD`  | Campo `code` vazio ou não enviado.                |
| `POST /api/meets/validar`               | 500    | —                  | Falha ao gravar.                                  |

---

## Referências no código

- Controller: `app/Controllers/Api/Acessos.php`
- Model do check: `app/Models/CheckModel.php` (tabela `acessos`)
- Model do order bump: `app/Models/PedidoOrderBumpModel.php` (métodos `marcarComoUsado`, `desmarcarUsado`, `getOrderBumpDoPedido`)
- Rotas: `app/Config/Routes.php` (linhas dos routes `api/acessos/check` e `api/acessos/orderbump/(:num)/usar`)
