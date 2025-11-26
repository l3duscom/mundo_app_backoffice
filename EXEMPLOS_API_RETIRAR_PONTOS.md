# 💡 Exemplos Práticos - API Retirar Pontos

## 🔑 Pré-requisitos
- Token JWT válido
- Usuário admin autenticado
- Usuário alvo existe no sistema

## 📝 Cenários de Uso

### Cenário 1: Resgate de Prêmio Físico
**Situação:** Cliente quer trocar 500 pontos por uma camiseta

#### cURL
```bash
curl -X POST https://mundodream.com.br/api/usuarios/retirar-pontos \
  -H "Authorization: Bearer SEU_TOKEN_JWT_AQUI" \
  -H "Content-Type: application/json" \
  -d '{
    "usuario_id": 123,
    "pontos": 500,
    "motivo": "Resgate: Camiseta Oficial Dreamfest 2025",
    "event_id": 17
  }'
```

#### JavaScript (Fetch)
```javascript
const retirarPontos = async (usuarioId, pontos, motivo, eventId) => {
    try {
        const response = await fetch('https://mundodream.com.br/api/usuarios/retirar-pontos', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('jwt_token')}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                usuario_id: usuarioId,
                pontos: pontos,
                motivo: motivo,
                event_id: eventId
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            console.log('Pontos retirados:', result.data);
            alert(`Sucesso! Novo saldo: ${result.data.saldo_atual} pontos`);
            return result.data;
        } else {
            console.error('Erro:', result.message);
            alert('Erro: ' + result.message);
            return null;
        }
    } catch (error) {
        console.error('Erro na requisição:', error);
        alert('Erro ao retirar pontos');
        return null;
    }
};

// Usar a função
retirarPontos(123, 500, 'Resgate: Camiseta Oficial', 17);
```

#### jQuery
```javascript
$.ajax({
    url: '<?= site_url("api/usuarios/retirar-pontos") ?>',
    type: 'POST',
    headers: {
        'Authorization': 'Bearer ' + localStorage.getItem('jwt_token')
    },
    contentType: 'application/json',
    data: JSON.stringify({
        usuario_id: 123,
        pontos: 500,
        motivo: 'Resgate: Camiseta Oficial Dreamfest 2025',
        event_id: 17
    }),
    success: function(response) {
        if (response.success) {
            console.log('Pontos retirados com sucesso!');
            console.log('Novo saldo:', response.data.saldo_atual);
            alert('Pontos retirados! Novo saldo: ' + response.data.saldo_atual);
        } else {
            alert('Erro: ' + response.message);
        }
    },
    error: function(xhr, status, error) {
        console.error('Erro:', error);
        alert('Erro ao retirar pontos');
    }
});
```

---

### Cenário 2: Correção de Pontos
**Situação:** Pontos foram atribuídos incorretamente e precisam ser removidos

#### cURL
```bash
curl -X POST https://mundodream.com.br/api/usuarios/retirar-pontos \
  -H "Authorization: Bearer SEU_TOKEN_JWT_AQUI" \
  -H "Content-Type: application/json" \
  -d '{
    "usuario_id": 456,
    "pontos": 250,
    "motivo": "Correção: Pontos atribuídos por engano em 25/11/2025"
  }'
```

**Resposta Esperada:**
```json
{
  "success": true,
  "message": "Pontos retirados com sucesso",
  "data": {
    "usuario_id": 456,
    "pontos_retirados": 250,
    "saldo_anterior": 750,
    "saldo_atual": 500,
    "extrato_id": 789,
    "motivo": "Correção: Pontos atribuídos por engano em 25/11/2025"
  }
}
```

---

### Cenário 3: Consultar Saldo Antes de Retirar

#### JavaScript Completo
```javascript
async function processarResgate(usuarioId, pontosNecessarios, motivo, eventId) {
    try {
        const token = localStorage.getItem('jwt_token');
        
        // 1. Consultar saldo atual
        const saldoResponse = await fetch(`https://mundodream.com.br/api/usuarios/saldo/${usuarioId}`, {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });
        
        const saldoData = await saldoResponse.json();
        
        if (!saldoData.success) {
            alert('Erro ao consultar saldo: ' + saldoData.message);
            return false;
        }
        
        const saldoAtual = saldoData.data.pontos;
        
        // 2. Verificar se tem saldo suficiente
        if (saldoAtual < pontosNecessarios) {
            alert(`Saldo insuficiente! O usuário tem ${saldoAtual} pontos, mas precisa de ${pontosNecessarios}.`);
            return false;
        }
        
        // 3. Confirmar com o usuário
        const confirmar = confirm(
            `Confirma retirada de ${pontosNecessarios} pontos?\n` +
            `Saldo atual: ${saldoAtual}\n` +
            `Novo saldo: ${saldoAtual - pontosNecessarios}\n` +
            `Motivo: ${motivo}`
        );
        
        if (!confirmar) {
            return false;
        }
        
        // 4. Retirar pontos
        const retirarResponse = await fetch('https://mundodream.com.br/api/usuarios/retirar-pontos', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                usuario_id: usuarioId,
                pontos: pontosNecessarios,
                motivo: motivo,
                event_id: eventId
            })
        });
        
        const retirarData = await retirarResponse.json();
        
        if (retirarData.success) {
            alert('Pontos retirados com sucesso!\n' +
                  `Novo saldo: ${retirarData.data.saldo_atual} pontos`);
            
            // Atualizar interface
            atualizarSaldoNaTela(retirarData.data.saldo_atual);
            adicionarTransacaoNoExtrato(retirarData.data);
            
            return true;
        } else {
            alert('Erro ao retirar pontos: ' + retirarData.message);
            return false;
        }
        
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao processar resgate');
        return false;
    }
}

// Funções auxiliares (implemente conforme sua interface)
function atualizarSaldoNaTela(novoSaldo) {
    document.getElementById('saldoPontos').textContent = novoSaldo;
}

function adicionarTransacaoNoExtrato(transacao) {
    // Adicionar na tabela de extrato, etc.
    console.log('Nova transação:', transacao);
}

// Exemplo de uso
processarResgate(123, 500, 'Resgate: Camiseta Oficial', 17);
```

---

### Cenário 4: Retirada em Lote
**Situação:** Vários usuários resgataram o mesmo prêmio

#### JavaScript
```javascript
async function retirarPontosEmLote(resgates, token) {
    const resultados = {
        sucesso: [],
        erro: []
    };
    
    for (const resgate of resgates) {
        try {
            const response = await fetch('https://mundodream.com.br/api/usuarios/retirar-pontos', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    usuario_id: resgate.usuario_id,
                    pontos: resgate.pontos,
                    motivo: resgate.motivo,
                    event_id: resgate.event_id
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                resultados.sucesso.push({
                    usuario_id: resgate.usuario_id,
                    ...result.data
                });
            } else {
                resultados.erro.push({
                    usuario_id: resgate.usuario_id,
                    erro: result.message
                });
            }
            
            // Aguardar 100ms entre requisições para não sobrecarregar
            await new Promise(resolve => setTimeout(resolve, 100));
            
        } catch (error) {
            resultados.erro.push({
                usuario_id: resgate.usuario_id,
                erro: error.message
            });
        }
    }
    
    console.log('Resultados:', resultados);
    console.log(`Sucesso: ${resultados.sucesso.length}, Erros: ${resultados.erro.length}`);
    
    return resultados;
}

// Exemplo de uso
const resgates = [
    { usuario_id: 123, pontos: 500, motivo: 'Resgate: Camiseta', event_id: 17 },
    { usuario_id: 456, pontos: 500, motivo: 'Resgate: Camiseta', event_id: 17 },
    { usuario_id: 789, pontos: 500, motivo: 'Resgate: Camiseta', event_id: 17 }
];

const token = localStorage.getItem('jwt_token');
retirarPontosEmLote(resgates, token).then(resultados => {
    alert(`Processados: ${resultados.sucesso.length} sucesso, ${resultados.erro.length} erros`);
});
```

---

### Cenário 5: Interface de Administração

#### HTML + JavaScript Completo
```html
<div class="card">
    <div class="card-body">
        <h5>Retirar Pontos de Usuário</h5>
        
        <form id="formRetirarPontos">
            <div class="mb-3">
                <label>Usuário ID</label>
                <input type="number" class="form-control" id="inputUsuarioId" required>
            </div>
            
            <div class="mb-3">
                <label>Pontos a Retirar</label>
                <input type="number" class="form-control" id="inputPontos" min="1" required>
            </div>
            
            <div class="mb-3">
                <label>Motivo</label>
                <textarea class="form-control" id="inputMotivo" rows="3" required></textarea>
            </div>
            
            <div class="mb-3">
                <label>Evento (opcional)</label>
                <select class="form-control" id="inputEvento">
                    <option value="">Nenhum</option>
                    <option value="17">Dreamfest 2025</option>
                    <!-- Adicione mais eventos conforme necessário -->
                </select>
            </div>
            
            <div id="saldoInfo" class="alert alert-info" style="display:none;">
                <strong>Saldo Atual:</strong> <span id="saldoAtual">-</span> pontos<br>
                <strong>Novo Saldo:</strong> <span id="novoSaldo">-</span> pontos
            </div>
            
            <button type="button" class="btn btn-primary" onclick="verificarSaldo()">
                Verificar Saldo
            </button>
            
            <button type="submit" class="btn btn-danger" id="btnRetirar" disabled>
                Retirar Pontos
            </button>
        </form>
        
        <div id="resultado" class="mt-3"></div>
    </div>
</div>

<script>
const API_BASE_URL = '<?= site_url("api/usuarios") ?>';
const TOKEN = localStorage.getItem('jwt_token'); // ou de onde você armazena o token

async function verificarSaldo() {
    const usuarioId = document.getElementById('inputUsuarioId').value;
    const pontos = parseInt(document.getElementById('inputPontos').value);
    
    if (!usuarioId || !pontos) {
        alert('Preencha o ID do usuário e a quantidade de pontos');
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE_URL}/saldo/${usuarioId}`, {
            headers: {
                'Authorization': `Bearer ${TOKEN}`
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            const saldoAtual = result.data.pontos;
            const novoSaldo = saldoAtual - pontos;
            
            document.getElementById('saldoAtual').textContent = saldoAtual;
            document.getElementById('novoSaldo').textContent = novoSaldo;
            document.getElementById('saldoInfo').style.display = 'block';
            
            // Habilitar botão de retirar se tiver saldo
            if (novoSaldo >= 0) {
                document.getElementById('btnRetirar').disabled = false;
                document.getElementById('novoSaldo').style.color = 'green';
            } else {
                document.getElementById('btnRetirar').disabled = true;
                document.getElementById('novoSaldo').style.color = 'red';
                alert('Saldo insuficiente!');
            }
        } else {
            alert('Erro: ' + result.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao consultar saldo');
    }
}

document.getElementById('formRetirarPontos').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const usuarioId = document.getElementById('inputUsuarioId').value;
    const pontos = parseInt(document.getElementById('inputPontos').value);
    const motivo = document.getElementById('inputMotivo').value;
    const eventId = document.getElementById('inputEvento').value || null;
    
    const confirmar = confirm(
        `Confirma a retirada de ${pontos} pontos?\n` +
        `Usuário: ${usuarioId}\n` +
        `Motivo: ${motivo}`
    );
    
    if (!confirmar) return;
    
    try {
        const response = await fetch(`${API_BASE_URL}/retirar-pontos`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${TOKEN}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                usuario_id: parseInt(usuarioId),
                pontos: pontos,
                motivo: motivo,
                event_id: eventId ? parseInt(eventId) : null
            })
        });
        
        const result = await response.json();
        
        const resultadoDiv = document.getElementById('resultado');
        
        if (result.success) {
            resultadoDiv.innerHTML = `
                <div class="alert alert-success">
                    <h6>✅ Pontos retirados com sucesso!</h6>
                    <p class="mb-0">
                        <strong>Pontos retirados:</strong> ${result.data.pontos_retirados}<br>
                        <strong>Saldo anterior:</strong> ${result.data.saldo_anterior}<br>
                        <strong>Saldo atual:</strong> ${result.data.saldo_atual}<br>
                        <strong>ID do extrato:</strong> ${result.data.extrato_id}
                    </p>
                </div>
            `;
            
            // Limpar formulário
            this.reset();
            document.getElementById('saldoInfo').style.display = 'none';
            document.getElementById('btnRetirar').disabled = true;
            
        } else {
            resultadoDiv.innerHTML = `
                <div class="alert alert-danger">
                    <h6>❌ Erro ao retirar pontos</h6>
                    <p class="mb-0">${result.message}</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Erro:', error);
        document.getElementById('resultado').innerHTML = `
            <div class="alert alert-danger">
                <h6>❌ Erro na requisição</h6>
                <p class="mb-0">${error.message}</p>
            </div>
        `;
    }
});
</script>
```

---

### Cenário 6: PHP (CodeIgniter Service)
**Situação:** Chamar a API internamente (server-side)

#### Service Class
```php
<?php

namespace App\Services;

class PontosService
{
    private $usuarioModel;
    private $extratoPontosModel;
    private $db;
    
    public function __construct()
    {
        $this->usuarioModel = new \App\Models\UsuarioModel();
        $this->extratoPontosModel = new \App\Models\ExtratoPontosModel();
        $this->db = \Config\Database::connect();
    }
    
    /**
     * Retirar pontos de um usuário
     * 
     * @param int $usuario_id
     * @param int $pontos
     * @param string $motivo
     * @param int|null $event_id
     * @param int $admin_id
     * @return array
     */
    public function retirarPontos(int $usuario_id, int $pontos, string $motivo, ?int $event_id = null, int $admin_id): array
    {
        // Buscar usuário
        $usuario = $this->usuarioModel->find($usuario_id);
        
        if (!$usuario) {
            return [
                'success' => false,
                'message' => 'Usuário não encontrado'
            ];
        }
        
        $saldoAtual = (int) $usuario->pontos;
        
        // Verificar saldo
        if ($saldoAtual < $pontos) {
            return [
                'success' => false,
                'message' => "Saldo insuficiente. Tem {$saldoAtual}, precisa de {$pontos}.",
                'saldo_atual' => $saldoAtual
            ];
        }
        
        $this->db->transStart();
        
        try {
            $novoSaldo = $saldoAtual - $pontos;
            
            // Atualizar pontos
            $this->usuarioModel->update($usuario_id, ['pontos' => $novoSaldo]);
            
            // Criar extrato
            $extratoId = $this->extratoPontosModel->insert([
                'usuario_id' => $usuario_id,
                'event_id' => $event_id,
                'tipo_transacao' => 'DEBITO',
                'pontos' => $pontos,
                'saldo_anterior' => $saldoAtual,
                'saldo_atual' => $novoSaldo,
                'descricao' => $motivo,
                'admin' => $admin_id,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->db->transComplete();
            
            if ($this->db->transStatus() === false) {
                throw new \Exception('Erro na transação');
            }
            
            return [
                'success' => true,
                'message' => 'Pontos retirados com sucesso',
                'data' => [
                    'pontos_retirados' => $pontos,
                    'saldo_anterior' => $saldoAtual,
                    'saldo_atual' => $novoSaldo,
                    'extrato_id' => $extratoId
                ]
            ];
            
        } catch (\Exception $e) {
            $this->db->transRollback();
            
            return [
                'success' => false,
                'message' => 'Erro ao retirar pontos',
                'error' => $e->getMessage()
            ];
        }
    }
}
```

**Uso:**
```php
$pontosService = new \App\Services\PontosService();

$resultado = $pontosService->retirarPontos(
    usuario_id: 123,
    pontos: 500,
    motivo: 'Resgate de prêmio',
    event_id: 17,
    admin_id: 1
);

if ($resultado['success']) {
    echo "Pontos retirados! Novo saldo: " . $resultado['data']['saldo_atual'];
} else {
    echo "Erro: " . $resultado['message'];
}
```

---

## 🧪 Testes

### Postman Collection
```json
{
  "info": {
    "name": "API Usuários - Retirar Pontos",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Retirar Pontos",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{jwt_token}}"
          },
          {
            "key": "Content-Type",
            "value": "application/json"
          }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"usuario_id\": 123,\n  \"pontos\": 100,\n  \"motivo\": \"Resgate de prêmio\",\n  \"event_id\": 17\n}"
        },
        "url": {
          "raw": "https://mundodream.com.br/api/usuarios/retirar-pontos",
          "protocol": "https",
          "host": ["mundodream", "com", "br"],
          "path": ["api", "usuarios", "retirar-pontos"]
        }
      }
    },
    {
      "name": "Consultar Saldo",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{jwt_token}}"
          }
        ],
        "url": {
          "raw": "https://mundodream.com.br/api/usuarios/saldo/123",
          "protocol": "https",
          "host": ["mundodream", "com", "br"],
          "path": ["api", "usuarios", "saldo", "123"]
        }
      }
    }
  ]
}
```

---

## ⚠️ Boas Práticas

### 1. Sempre Consultar Saldo Antes
```javascript
// ✅ BOM
const saldo = await consultarSaldo(usuarioId);
if (saldo >= pontos) {
    await retirarPontos(usuarioId, pontos, motivo);
}

// ❌ RUIM
await retirarPontos(usuarioId, pontos, motivo); // Pode falhar
```

### 2. Confirmar com o Usuário
```javascript
const confirmar = confirm(`Confirma retirada de ${pontos} pontos?`);
if (confirmar) {
    await retirarPontos(...);
}
```

### 3. Tratar Erros
```javascript
try {
    const result = await retirarPontos(...);
    if (result.success) {
        // Sucesso
    } else {
        // Erro esperado (saldo insuficiente, etc)
        alert(result.message);
    }
} catch (error) {
    // Erro de rede/sistema
    console.error(error);
    alert('Erro ao processar requisição');
}
```

### 4. Usar Loading States
```javascript
const btn = document.getElementById('btnRetirar');
btn.disabled = true;
btn.textContent = 'Processando...';

try {
    await retirarPontos(...);
} finally {
    btn.disabled = false;
    btn.textContent = 'Retirar Pontos';
}
```

---

## 🔍 Debugging

### Verificar Requisição
```javascript
console.log('Enviando:', {
    usuario_id: usuarioId,
    pontos: pontos,
    motivo: motivo,
    event_id: eventId
});

const response = await fetch(...);
console.log('Status:', response.status);

const result = await response.json();
console.log('Resposta:', result);
```

### Ver Logs do Servidor
```bash
# CodeIgniter logs
tail -f writable/logs/log-2025-11-26.log | grep "retirar pontos"
```

---

## 📊 Métricas e Relatórios

### Total de Pontos Retirados Hoje
```sql
SELECT SUM(pontos) as total_retirado_hoje
FROM extrato_pontos
WHERE tipo_transacao = 'DEBITO'
AND DATE(created_at) = CURDATE();
```

### Retiradas por Motivo
```sql
SELECT 
    descricao as motivo,
    COUNT(*) as quantidade,
    SUM(pontos) as total_pontos
FROM extrato_pontos
WHERE tipo_transacao = 'DEBITO'
GROUP BY descricao
ORDER BY total_pontos DESC;
```

---

## 🚀 Implementação
- **Endpoint:** `/api/usuarios/retirar-pontos`
- **Método:** POST
- **Autenticação:** JWT + Admin
- **Transação:** Sim (DB Transaction)
- **Validações:** 6 níveis
- **Logs:** Completos
- **Status:** ✅ Pronto para uso

