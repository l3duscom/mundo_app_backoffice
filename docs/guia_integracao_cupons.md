# Guia de Integração: Validação de Cupons de Desconto

## Visão Geral

Este documento descreve como integrar a validação de cupons de desconto em qualquer sistema de checkout.

---

## 1. Endpoint de Validação

### URL
```
POST /cupons/validar
```

### Parâmetros de Entrada

| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `codigo` | string | ✅ | Código do cupom digitado pelo usuário |
| `evento_id` | int | ❌ | ID do evento (para validar cupons específicos) |
| `user_id` | int | ❌ | ID do usuário (para verificar limite de uso por usuário) |
| `valor_pedido` | float | ❌ | Valor total do pedido (para verificar valor mínimo) |

### Resposta de Sucesso

```json
{
    "sucesso": "Cupom válido!",
    "token": "novo_csrf_token",
    "cupom": {
        "id": 1,
        "codigo": "NATAL10",
        "nome": "Desconto de Natal",
        "desconto_formatado": "10%",
        "valor_desconto": 15.00,
        "valor_desconto_formatado": "R$ 15,00",
        "valor_final": 135.00,
        "valor_final_formatado": "R$ 135,00"
    }
}
```

### Resposta de Erro

```json
{
    "erro": "Este cupom expirou.",
    "token": "novo_csrf_token"
}
```

**Mensagens de erro possíveis:**
- "Cupom não encontrado."
- "Este cupom está desativado."
- "Este cupom não é válido para este evento."
- "Este cupom ainda não está válido."
- "Este cupom expirou."
- "Este cupom já atingiu o limite de uso."
- "Valor mínimo do pedido para usar este cupom: R$ XX,XX"
- "Você já utilizou este cupom o número máximo de vezes."

---

## 2. Exemplo de Implementação (JavaScript)

### HTML do Campo de Cupom

```html
<div class="cupom-container">
    <input type="text" id="codigo-cupom" placeholder="Digite seu cupom">
    <button type="button" id="btn-validar-cupom">Aplicar</button>
    <div id="cupom-resultado"></div>
</div>

<!-- Campo hidden para armazenar o cupom validado -->
<input type="hidden" name="cupom_id" id="cupom_id" value="">
<input type="hidden" name="valor_desconto" id="valor_desconto" value="0">
```

### JavaScript

```javascript
$(document).ready(function() {
    var valorPedidoOriginal = 150.00; // Valor total do pedido
    var cupomAplicado = null;

    $('#btn-validar-cupom').on('click', function() {
        var codigo = $('#codigo-cupom').val().trim().toUpperCase();
        
        if (!codigo) {
            alert('Digite um código de cupom');
            return;
        }

        $.ajax({
            url: '/cupons/validar',
            type: 'POST',
            data: {
                codigo: codigo,
                evento_id: $('#evento_id').val(),      // Se aplicável
                user_id: $('#user_id').val(),          // Se aplicável
                valor_pedido: valorPedidoOriginal,
                csrf_test_name: $('input[name="csrf_test_name"]').val()
            },
            dataType: 'json',
            success: function(response) {
                // Atualiza token CSRF
                if (response.token) {
                    $('input[name="csrf_test_name"]').val(response.token);
                }

                if (response.erro) {
                    // Cupom inválido
                    $('#cupom-resultado').html(
                        '<span class="text-danger">' + response.erro + '</span>'
                    );
                    removerCupom();
                } else {
                    // Cupom válido
                    cupomAplicado = response.cupom;
                    
                    $('#cupom-resultado').html(
                        '<span class="text-success">✓ ' + cupomAplicado.codigo + 
                        ' - Desconto de ' + cupomAplicado.desconto_formatado + 
                        ' (' + cupomAplicado.valor_desconto_formatado + ')</span>'
                    );

                    // Atualiza campos hidden
                    $('#cupom_id').val(cupomAplicado.id);
                    $('#valor_desconto').val(cupomAplicado.valor_desconto);

                    // Atualiza exibição do valor total
                    atualizarValorTotal(cupomAplicado.valor_final);
                }
            },
            error: function() {
                alert('Erro ao validar cupom. Tente novamente.');
            }
        });
    });

    function removerCupom() {
        cupomAplicado = null;
        $('#cupom_id').val('');
        $('#valor_desconto').val('0');
        atualizarValorTotal(valorPedidoOriginal);
    }

    function atualizarValorTotal(novoValor) {
        $('#valor-total-display').text(
            'R$ ' + novoValor.toFixed(2).replace('.', ',')
        );
    }
});
```

---

## 3. Fluxo de Processamento do Pedido

Após a confirmação do pedido, você precisa:

### 3.1 Salvar o Cupom no Pedido

```php
// No controller de pedidos, ao criar o pedido:
$pedidoData = [
    'user_id' => $userId,
    'evento_id' => $eventoId,
    'total' => $valorTotal,
    'valor_liquido' => $valorLiquido,
    'cupom_id' => $request->getPost('cupom_id') ?: null,
    'valor_desconto' => $request->getPost('valor_desconto') ?: 0,
    // ... outros campos
];

$pedidoModel->insert($pedidoData);
```

### 3.2 Incrementar o Uso do Cupom

```php
// Após confirmar o pagamento do pedido:
if ($pedido->cupom_id) {
    $cupomModel = new CupomModel();
    $cupomModel->incrementarUso($pedido->cupom_id);
}
```

### 3.3 Decrementar em Caso de Cancelamento

```php
// Se o pedido for cancelado:
if ($pedido->cupom_id) {
    $cupomModel = new CupomModel();
    $cupomModel->decrementarUso($pedido->cupom_id);
}
```

---

## 4. Tipos de Desconto

| Tipo | Descrição | Cálculo |
|------|-----------|---------|
| `percentual` | Desconto em porcentagem | `valor_pedido * (desconto / 100)` |
| `fixo` | Valor fixo em reais | `min(desconto, valor_pedido)` |

---

## 5. Campos do Cupom

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `codigo` | VARCHAR(50) | Código único do cupom |
| `nome` | VARCHAR(100) | Nome interno para identificação |
| `tipo` | ENUM | 'percentual' ou 'fixo' |
| `desconto` | DECIMAL | Valor do desconto (% ou R$) |
| `valor_minimo` | DECIMAL | Valor mínimo do pedido |
| `quantidade_total` | INT | Limite de usos (NULL = ilimitado) |
| `quantidade_usada` | INT | Contador de usos |
| `uso_por_usuario` | INT | Limite de uso por usuário |
| `data_inicio` | DATE | Início da validade |
| `data_fim` | DATE | Fim da validade |
| `ativo` | TINYINT | 1 = ativo, 0 = inativo |
| `evento_id` | INT | ID do evento (NULL = todos) |

---

## 6. Exemplo de Uso Completo

```php
// 1. Usuário digita o cupom no checkout
$codigo = 'NATAL10';
$eventoId = 18;
$userId = 123;
$valorPedido = 150.00;

// 2. Valida o cupom
$cupomModel = new CupomModel();
$resultado = $cupomModel->validarCupom($codigo, $eventoId, $userId, $valorPedido);

if (!$resultado['valido']) {
    // Cupom inválido
    echo "Erro: " . $resultado['erro'];
    return;
}

// 3. Calcula o desconto
$cupom = $resultado['cupom'];
$valorDesconto = $cupomModel->calcularDesconto($cupom, $valorPedido);
$valorFinal = $valorPedido - $valorDesconto;

// 4. Cria o pedido com o cupom
$pedido = [
    'total' => $valorPedido,
    'cupom_id' => $cupom->id,
    'valor_desconto' => $valorDesconto,
    'valor_liquido' => $valorFinal,
    // ...
];

// 5. Após confirmação do pagamento, incrementa o uso
$cupomModel->incrementarUso($cupom->id);
```
