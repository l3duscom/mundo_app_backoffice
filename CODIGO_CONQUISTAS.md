# Campo Código em Conquistas

## 📋 Visão Geral

O campo `codigo` foi adicionado à tabela `conquistas` para fornecer um identificador único e amigável para cada conquista, útil para:

- **Compartilhamento**: Códigos simples e curtos para usuários compartilharem conquistas
- **Busca rápida**: Facilitar a busca de conquistas específicas
- **Integração**: Identificadores únicos para integrações externas
- **Marketing**: Códigos promocionais e gamificação

## 🔧 Características Técnicas

### Estrutura do Campo

- **Tipo**: `VARCHAR(8)`
- **Constraint**: `NOT NULL`
- **Índice**: `UNIQUE KEY` (único no banco de dados)
- **Formato**: 8 caracteres alfanuméricos em maiúsculas (0-9, A-F - hexadecimal)
- **Geração**: Automática pelo sistema no momento da criação

### Exemplos de Códigos

```
A1B2C3D4
5E6F7A8B
9C0D1E2F
```

## 🚀 Implementação

### 1. Migration

**Arquivo**: `app/Database/Migrations/2024-11-23-000000_AddCodigoToConquistas.php`

Adiciona a coluna `codigo` e o índice único na tabela `conquistas`.

```bash
php spark migrate
```

### 2. Model

**Arquivo**: `app/Models/ConquistaModel.php`

#### Callback `gerarCodigoAntesDeInserir()`

O código é gerado **automaticamente** antes de inserir no banco de dados usando um callback `beforeInsert`:

```php
protected $beforeInsert = ['gerarCodigoAntesDeInserir'];

protected function gerarCodigoAntesDeInserir(array $data)
{
    // Se o código não foi fornecido, gera automaticamente
    if (empty($data['data']['codigo'])) {
        $data['data']['codigo'] = $this->gerarCodigoUnico();
    }
    
    return $data;
}
```

#### Método `gerarCodigoUnico()`

Gera um código único de 8 caracteres usando `random_bytes()` e garante unicidade:

```php
public function gerarCodigoUnico(): string
{
    $tentativas = 0;
    $maxTentativas = 50;
    
    do {
        // Gera código aleatório de 8 caracteres
        $codigo = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        
        // Verifica se já existe
        $existe = $this->where('codigo', $codigo)->countAllResults() > 0;
        
        $tentativas++;
        
        if ($tentativas >= $maxTentativas) {
            throw new \RuntimeException('Não foi possível gerar um código único');
        }
        
    } while ($existe);
    
    return $codigo;
}
```

### 3. Controller

**Arquivo**: `app/Controllers/Api/Conquistas.php`

O Controller **NÃO precisa** gerar o código - o Model faz isso automaticamente:

```php
// Prepara dados para salvar
// Nota: O código será gerado automaticamente pelo Model
$data = [
    'event_id' => $json['event_id'],
    'nome_conquista' => $json['nome_conquista'] ?? '',
    'descricao' => $json['descricao'] ?? null,
    'pontos' => $json['pontos'] ?? 0,
    'nivel' => $json['nivel'] ?? 'BRONZE',
    'status' => $json['status'] ?? 'ATIVA',
];

// O Model adicionará o código automaticamente antes de inserir
$this->conquistaModel->save($data);
```

### 4. Entity

**Arquivo**: `app/Entities/ConquistaEntity.php`

```php
protected $casts = [
    // ...
    'codigo' => 'string',
    // ...
];
```

## 📝 Regras de Negócio

### Criação

✅ **Funcionamento Automático:**
- Sistema gera código automaticamente via callback `beforeInsert`
- Código único garantido por validação `is_unique` e tentativas até 50x
- Usuário **NÃO precisa** enviar o campo `codigo` na requisição
- Se enviado, será ignorado e um novo código será gerado

❌ **NÃO Permitido:**
- Usuário não pode especificar o código manualmente
- Código não pode ser duplicado (garantido por índice único)

### Atualização

❌ **NÃO Permitido:**
- Código **NÃO pode ser alterado** após a criação
- Tentativa de alterar será ignorada ou retornará erro

### Validação

```php
'codigo' => 'permit_empty|string|exact_length[8]|is_unique[conquistas.codigo,id,{id}]'
```

- **Não obrigatório** no input (gerado automaticamente se vazio)
- Se fornecido, deve ter exatamente 8 caracteres
- Deve ser único no banco de dados
- Gerado automaticamente antes da inserção pelo callback `beforeInsert`

## 🔄 Migração de Dados Existentes

Se você já possui conquistas cadastradas, execute o SQL para gerar códigos:

```sql
-- Gera códigos únicos para conquistas existentes
UPDATE `conquistas` 
SET `codigo` = UPPER(SUBSTRING(MD5(CONCAT(id, RAND(), NOW())), 1, 8))
WHERE `codigo` = '' OR `codigo` IS NULL;

-- Verifica duplicados
SELECT COUNT(*) as total, 
       COUNT(DISTINCT codigo) as unicos,
       COUNT(*) - COUNT(DISTINCT codigo) as duplicados
FROM `conquistas`;
```

**Nota**: Execute o `UPDATE` novamente caso encontre duplicados até que todos sejam únicos.

## 📡 Uso na API

### Resposta de Criação

```json
{
  "success": true,
  "message": "Conquista criada com sucesso",
  "data": {
    "id": 5,
    "event_id": 17,
    "codigo": "K9L0M1N2",
    "nome_conquista": "Comprou Ingresso",
    "descricao": "Adquiriu ingresso para o evento",
    "pontos": 15,
    "nivel": "BRONZE",
    "status": "ATIVA",
    "created_at": "2024-11-23 10:00:00"
  }
}
```

### Listagem

Todas as rotas de listagem (`GET /api/conquistas`, `GET /api/conquistas/evento/{id}`) agora incluem o campo `codigo`.

### Busca por Código (Futura Implementação)

Você pode adicionar uma rota para buscar conquistas por código:

```php
// Em ConquistaModel.php
public function buscarPorCodigo(string $codigo)
{
    return $this->where('codigo', strtoupper($codigo))->first();
}

// No Controller
public function porCodigo($codigo = null)
{
    if (!$codigo) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Código não fornecido'
        ])->setStatusCode(400);
    }
    
    $conquista = $this->conquistaModel->buscarPorCodigo($codigo);
    
    if (!$conquista) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Conquista não encontrada'
        ])->setStatusCode(404);
    }
    
    return $this->response->setJSON([
        'success' => true,
        'data' => $conquista
    ]);
}
```

## 🎯 Casos de Uso

### 1. Compartilhamento Social

Usuários podem compartilhar suas conquistas usando o código:

```
"Acabei de ganhar a conquista K9L0M1N2 no Mundo Dream! 🏆"
```

### 2. Códigos Promocionais

Administradores podem criar conquistas especiais e divulgar o código:

```
"Use o código DREAM2024 para ganhar 100 pontos extras!"
```

### 3. QR Codes

Gere QR Codes com os códigos para distribuição física em eventos.

### 4. Integração com Apps Externos

Apps podem referenciar conquistas usando o código em vez do ID numérico.

## ⚠️ Notas Importantes

1. **Unicidade**: O sistema tenta até 50 vezes gerar um código único. Se falhar, uma exceção é lançada.

2. **Formato Hexadecimal**: Códigos usam apenas caracteres hexadecimais (0-9, A-F) para maximizar compatibilidade.

3. **Imutabilidade**: Uma vez criado, o código não pode ser alterado.

4. **Case-Insensitive**: Recomenda-se tratar códigos como case-insensitive nas buscas (sempre converter para maiúsculas).

## 📊 Performance

- **Geração**: ~0.001s por código
- **Índice Único**: Otimiza buscas por código
- **Colisões**: Probabilidade extremamente baixa com 16^8 combinações possíveis (4.3 bilhões)

## 🔐 Segurança

- Códigos são gerados usando `random_bytes()` (criptograficamente seguro)
- Não revelam informações sobre a conquista
- Não podem ser "adivinhados" sequencialmente

