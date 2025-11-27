# 🐛 Problema: Ingressos de Usuários Diferentes se Confundem

## ❓ Problema Relatado

> "Quando uso a API de ingressos atuais, por vezes, o resultado se confunde com outro usuário."

**Gravidade:** 🔴 **CRÍTICA** - Vazamento de dados entre usuários!

---

## 🔍 Análise do Código

### **Endpoint Afetado:**
```
GET /api/ingressos/atuais
```

### **Fluxo Atual:**

```php
// app/Controllers/Api/Ingressos.php (linha 308-375)
public function atuais()
{
    // 1. Obtém user_id do JWT
    $usuarioAutenticado = $this->request->usuarioAutenticado ?? null;
    $userId = $usuarioAutenticado['user_id'];  // ✅ Correto
    
    // 2. Busca ingressos do usuário
    $ingressos = $this->ingressoModel->recuperaIngressosPorUsuario($userId);
    
    // 3. Filtra e retorna
    ...
}
```

```php
// app/Models/IngressoModel.php (linha 131-173)
public function recuperaIngressosPorUsuario(int $usuario_id)
{
    $retorno = $this->select($atributos)
        ->join('pedidos', 'pedidos.id = ingressos.pedido_id')
        ->join('usuarios', 'usuarios.id = ingressos.user_id')
        ->join('eventos', 'eventos.id = pedidos.evento_id')
        ->where('usuarios.id', $usuario_id)  // ✅ Filtro está correto
        ->whereIn('pedidos.status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
        ->orderBy('pedidos.id', 'DESC')
        ->findAll();
    
    return $retorno;
}
```

**⚠️ O código PARECE correto, mas há possíveis causas ocultas:**

---

## 🕵️ Possíveis Causas

### **1. Cache do Query Builder (MAIS PROVÁVEL)**
CodeIgniter pode estar reutilizando estado do Query Builder entre requisições.

**Sintoma:** Mesma instância do modelo é reutilizada sem resetar o estado.

**Solução:** Resetar o query builder antes de cada consulta.

### **2. Cache de Resultado**
Cache no nível de OPcache, FastCGI ou servidor.

**Sintoma:** Resultado de uma requisição é retornado para outra.

**Solução:** Adicionar headers no-cache e verificar configurações do servidor.

### **3. Propriedades Estáticas Compartilhadas**
Variáveis estáticas no Model ou Controller sendo compartilhadas entre requisições.

**Sintoma:** Estado persistente entre chamadas de API.

**Solução:** Verificar e eliminar propriedades estáticas.

### **4. Token JWT Incorreto**
Cliente está enviando token de outro usuário.

**Sintoma:** `user_id` no JWT é de outro usuário.

**Solução:** Adicionar logs para verificar qual `user_id` está sendo processado.

### **5. Race Condition no FastCGI/FPM**
Processos PHP compartilhando estado incorretamente.

**Sintoma:** Problema intermitente, especialmente sob carga.

**Solução:** Verificar configuração do PHP-FPM e reiniciar serviço.

---

## ✅ Soluções Implementadas

### **Solução 1: Resetar Query Builder + Logs Detalhados**

```php
public function recuperaIngressosPorUsuario(int $usuario_id)
{
    // IMPORTANTE: Resetar query builder para prevenir state leaking
    $this->builder()->resetQuery();
    
    // Log para debug
    log_message('debug', "IngressoModel::recuperaIngressosPorUsuario - Usuario ID: {$usuario_id}");
    
    $atributos = [
        'ingressos.id',
        'ingressos.user_id',  // ← ADICIONADO para debug
        'ingressos.ticket_id',
        'ingressos.created_at',
        'ingressos.nome',
        'ingressos.valor_unitario',
        'ingressos.valor',
        'ingressos.quantidade',
        'ingressos.codigo',
        'ingressos.pedido_id',
        'ingressos.participante',
        'ingressos.tipo',
        'ingressos.cinemark',
        'ingressos.email',
        'ingressos.cpf',
        'pedidos.codigo as cod_pedido',
        'pedidos.rastreio',
        'pedidos.status',
        'pedidos.status_entrega',
        'pedidos.frete',
        'pedidos.evento_id',
        'pedidos.comprovante',
        'eventos.nome as nome_evento',
        'eventos.slug',
        'eventos.data_inicio',
        'eventos.data_fim',
        'eventos.hora_inicio',
        'eventos.hora_fim',
        'eventos.local'
    ];

    $retorno = $this->select($atributos)
        ->join('pedidos', 'pedidos.id = ingressos.pedido_id')
        ->join('usuarios', 'usuarios.id = ingressos.user_id')
        ->join('eventos', 'eventos.id = pedidos.evento_id')
        ->where('usuarios.id', $usuario_id)
        ->whereIn('pedidos.status', ['CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH'])
        ->orderBy('pedidos.id', 'DESC')
        ->findAll();
    
    // Log do resultado
    log_message('debug', sprintf(
        "IngressoModel::recuperaIngressosPorUsuario - Usuario %d retornou %d ingressos",
        $usuario_id,
        count($retorno)
    ));
    
    // Log dos IDs dos ingressos para debug
    if (!empty($retorno)) {
        $ids = array_map(fn($i) => $i->id ?? 'null', $retorno);
        log_message('debug', "IngressoModel - IDs retornados: " . implode(', ', $ids));
    }

    return $retorno;
}
```

### **Solução 2: Logs no Controller**

```php
public function atuais()
{
    $usuarioAutenticado = $this->request->usuarioAutenticado ?? null;
    
    if (!$usuarioAutenticado) {
        return $this->response
            ->setJSON([
                'success' => false,
                'message' => 'Usuário não autenticado'
            ])
            ->setStatusCode(401);
    }
    
    $userId = $usuarioAutenticado['user_id'];
    
    // Log de quem está fazendo a requisição
    log_message('info', sprintf(
        "API Ingressos::atuais - Usuario %d (%s) requisitou ingressos. IP: %s",
        $userId,
        $usuarioAutenticado['email'] ?? 'sem-email',
        $this->request->getIPAddress()
    ));

    try {
        $ingressos = $this->ingressoModel->recuperaIngressosPorUsuario($userId);
        
        // VALIDAÇÃO DE SEGURANÇA: Verificar se todos os ingressos pertencem ao usuário
        foreach ($ingressos as $ingresso) {
            if (isset($ingresso->user_id) && $ingresso->user_id != $userId) {
                log_message('critical', sprintf(
                    "VAZAMENTO DE DADOS! Usuario %d recebeu ingresso %d que pertence ao usuario %d",
                    $userId,
                    $ingresso->id,
                    $ingresso->user_id
                ));
                
                // Retornar erro em vez de dados de outro usuário
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Erro de segurança detectado'
                    ])
                    ->setStatusCode(500);
            }
        }
        
        $ingressos_atuais = [];
        $hoje = date('Y-m-d');

        foreach ($ingressos as $ingresso) {
            $ticket = $this->ticketModel->find($ingresso->ticket_id ?? null);
            
            $data_fim = $ticket->data_fim ?? null;
            $limite = date('Y-m-d', strtotime('-2 days', strtotime($hoje)));
            
            // Só adiciona se for atual (não expirado há mais de 2 dias)
            if (!$data_fim || $data_fim >= $limite) {
                // Gera QR Code
                $qrCodeBase64 = null;
                if ($ingresso->codigo) {
                    try {
                        $qrCodeBase64 = (new QRCode)->render($ingresso->codigo);
                    } catch (\Exception $e) {
                        log_message('warning', 'Erro ao gerar QR Code: ' . $e->getMessage());
                    }
                }
                
                $ingressoData = [
                    'id' => $ingresso->id,
                    'codigo' => $ingresso->codigo,
                    'nome' => $ingresso->nome ?? null,
                    'email' => $ingresso->email ?? null,
                    'cpf' => $ingresso->cpf ?? null,
                    'status' => $ingresso->status ?? null,
                    'qr_code' => $qrCodeBase64,
                ];

                if ($ticket) {
                    $ingressoData['ticket'] = [
                        'id' => $ticket->id,
                        'nome' => $ticket->nome ?? null,
                        'data_inicio' => $ticket->data_inicio ?? null,
                        'data_fim' => $ticket->data_fim ?? null,
                    ];
                }

                $ingressos_atuais[] = $ingressoData;
            }
        }
        
        log_message('info', sprintf(
            "API Ingressos::atuais - Usuario %d - Retornando %d ingressos atuais",
            $userId,
            count($ingressos_atuais)
        ));

        return $this->response
            ->setJSON([
                'success' => true,
                'data' => [
                    'ingressos' => $ingressos_atuais,
                    'total' => count($ingressos_atuais),
                ]
            ])
            ->setStatusCode(200)
            // Headers para prevenir cache
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0');

    } catch (\Exception $e) {
        log_message('error', sprintf(
            "Erro ao buscar ingressos atuais API - Usuario %d: %s",
            $userId,
            $e->getMessage()
        ));
        
        return $this->response
            ->setJSON([
                'success' => false,
                'message' => 'Erro ao buscar ingressos',
                'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
            ])
            ->setStatusCode(500);
    }
}
```

### **Solução 3: Verificar Token JWT**

Adicionar endpoint de debug para verificar o token:

```php
/**
 * Debug: Retorna o payload do JWT (apenas em desenvolvimento)
 * GET /api/ingressos/debug-token
 */
public function debugToken()
{
    if (ENVIRONMENT !== 'development') {
        return $this->response
            ->setJSON(['error' => 'Endpoint disponível apenas em desenvolvimento'])
            ->setStatusCode(403);
    }
    
    $usuarioAutenticado = $this->request->usuarioAutenticado ?? null;
    
    return $this->response
        ->setJSON([
            'jwt_payload' => $usuarioAutenticado,
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString()
        ])
        ->setStatusCode(200);
}
```

---

## 🧪 Como Testar

### **Teste 1: Verificar Logs**

```bash
# Fazer requisição
curl -X GET https://seu-dominio.com/api/ingressos/atuais \
  -H "Authorization: Bearer SEU_TOKEN"

# Verificar logs
tail -f writable/logs/log-*.log | grep "Ingressos::atuais"
```

**Verificar:**
- ✅ `user_id` correto no log
- ✅ Quantidade de ingressos retornados
- ❌ Mensagem de `VAZAMENTO DE DADOS!`

### **Teste 2: Múltiplos Usuários Simultâneos**

```bash
# Terminal 1 - Usuário A
while true; do
  curl -X GET https://seu-dominio.com/api/ingressos/atuais \
    -H "Authorization: Bearer TOKEN_USUARIO_A" \
    -s | jq '.data.total'
  sleep 1
done

# Terminal 2 - Usuário B
while true; do
  curl -X GET https://seu-dominio.com/api/ingressos/atuais \
    -H "Authorization: Bearer TOKEN_USUARIO_B" \
    -s | jq '.data.total'
  sleep 1
done
```

**Verificar:**
- ✅ Cada usuário recebe sempre a mesma quantidade
- ❌ Quantidade oscilando (indicaria mistura)

### **Teste 3: Verificar user_id Retornado**

```sql
-- Verificar ingressos de um usuário específico
SELECT 
    i.id,
    i.user_id,
    i.codigo,
    i.nome,
    p.codigo as cod_pedido,
    p.status
FROM ingressos i
INNER JOIN pedidos p ON p.id = i.pedido_id
WHERE i.user_id = 6  -- Troque pelo user_id testado
  AND p.status IN ('CONFIRMED', 'RECEIVED', 'paid', 'RECEIVED_IN_CASH')
ORDER BY i.id DESC;
```

Comparar IDs retornados com os da API.

---

## 🔧 Configurações do Servidor

### **Verificar PHP-FPM:**

```bash
# Verificar se há processos PHP compartilhando estado
ps aux | grep php-fpm

# Reiniciar PHP-FPM
sudo systemctl restart php-fpm
# ou
sudo service php8.1-fpm restart
```

### **Verificar OPcache:**

```php
// public/phpinfo.php (criar temporariamente, depois deletar)
<?php
phpinfo();
?>
```

Verificar:
- `opcache.enable` = 1 (OK)
- `opcache.validate_timestamps` = 1 (OK para dev)
- `opcache.revalidate_freq` = 0 (OK para dev)

### **Desabilitar Cache Temporariamente (teste):**

```php
// php.ini ou .user.ini
opcache.enable=0
opcache.enable_cli=0
```

---

## 📊 Checklist de Verificação

- [ ] Logs implementados no Model (`IngressoModel.php`)
- [ ] Logs implementados no Controller (`Api/Ingressos.php`)
- [ ] `resetQuery()` adicionado no Model
- [ ] Headers `no-cache` adicionados na resposta
- [ ] Validação de `user_id` no Controller
- [ ] Endpoint de debug criado (apenas dev)
- [ ] Teste com múltiplos usuários simultâneos
- [ ] Verificação de logs após requisições
- [ ] PHP-FPM reiniciado
- [ ] OPcache verificado

---

## 🚨 Ação Imediata

**Se o problema persistir:**

1. ✅ **Implementar as correções acima**
2. ✅ **Verificar logs** após cada requisição
3. ✅ **Identificar padrão**: quando ocorre a mistura?
4. ✅ **Testar com cache desabilitado**
5. ✅ **Verificar se JWT está correto**

---

## 📚 Arquivos Afetados

| Arquivo | Modificação |
|---------|-------------|
| `app/Models/IngressoModel.php` | Reset query + logs |
| `app/Controllers/Api/Ingressos.php` | Validação + logs + no-cache |
| `app/Config/Routes.php` | Adicionar rota debug (opcional) |

---

## 🎯 Próximos Passos

1. **Implementar correções** (abaixo)
2. **Reiniciar servidor PHP**
3. **Testar com 2 usuários** diferentes simultaneamente
4. **Verificar logs** para confirmar que cada user_id recebe apenas seus ingressos
5. **Reportar resultado** com logs

---

🔴 **Este é um problema crítico de segurança. Não pode ir para produção até ser resolvido!**

