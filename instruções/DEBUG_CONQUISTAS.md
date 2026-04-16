# 🐛 Debug - Sistema de Conquistas

## Problema Atual

Você está recebendo um erro genérico ao tentar atribuir conquistas:

```json
{
  "success": false,
  "message": "Erro ao atribuir conquista",
  "error": "Erro interno"
}
```

## Passos para Diagnosticar

### 1. Verificar Logs da Aplicação

O erro está sendo logado. Verifique o arquivo de logs do CodeIgniter:

```bash
tail -f writable/logs/log-YYYY-MM-DD.php
```

Procure por mensagens como:
- `Iniciando atribuição de conquista`
- `Exceção ao atribuir conquista`
- `Erro ao salvar usuario_conquista`
- `Erro ao salvar extrato_pontos`

### 2. Verificar Estrutura do Banco de Dados

Execute o script de verificação:

```bash
mysql -u seu_usuario -p seu_banco < sql/verificar_estrutura_conquistas.sql
```

Este script verificará se:
- ✅ Tabela `conquistas` existe
- ✅ Tabela `usuario_conquistas` existe
- ✅ Tabela `extrato_pontos` existe
- ✅ Coluna `pontos` existe na tabela `usuarios`
- ✅ Todas as foreign keys estão criadas

### 3. Executar Migrations (se necessário)

Se alguma tabela estiver faltando:

```bash
php spark migrate
```

### 4. Adicionar Coluna de Pontos (se necessário)

Se a coluna `pontos` não existir na tabela `usuarios`:

```bash
mysql -u seu_usuario -p seu_banco < sql/add_pontos_column_usuarios.sql
```

### 5. Testar em Modo Development

Para ver o erro real, altere temporariamente o ambiente para `development` em `.env`:

```env
CI_ENVIRONMENT = development
```

Depois teste novamente a API e você verá a mensagem de erro completa.

**⚠️ IMPORTANTE:** Lembre-se de voltar para `production` depois!

## Erros Comuns e Soluções

### Erro: "Unknown column 'pontos' in field list"

**Causa:** A coluna `pontos` não existe na tabela `usuarios`

**Solução:** Execute o script `sql/add_pontos_column_usuarios.sql`

### Erro: "Table 'conquistas' doesn't exist"

**Causa:** Migrations não foram executadas

**Solução:** Execute `php spark migrate`

### Erro: "Duplicate entry for key 'unique_user_conquista'"

**Causa:** Tentando atribuir uma conquista que o usuário já possui

**Solução:** Verifique se o usuário já tem a conquista antes de atribuir

### Erro: "Cannot add or update a child row: a foreign key constraint fails"

**Causa:** ID inválido (event_id, user_id, conquista_id ou atribuido_por não existe)

**Solução:** Verifique se todos os IDs existem no banco:
- O evento existe?
- O usuário existe?
- A conquista existe?
- O usuário que está atribuindo (atribuido_por) existe?

## Melhorias Implementadas

Para facilitar o debug, foram adicionados logs detalhados em:

1. **Início do processo** - Log de todos os parâmetros recebidos
2. **Erro de validação** - Log dos erros de validação e dados enviados
3. **Exceções** - Log completo da exceção com stack trace

## Teste Manual Rápido

### 1. Criar uma Conquista

```bash
POST /api/conquistas
Content-Type: application/json

{
  "event_id": 17,
  "nome_conquista": "Teste Debug",
  "descricao": "Conquista para teste",
  "pontos": 10,
  "nivel": "BRONZE",
  "status": "ATIVA"
}
```

### 2. Atribuir a Conquista

```bash
POST /api/usuario-conquistas/atribuir
Content-Type: application/json

{
  "user_id": 1,
  "conquista_id": [ID_DA_CONQUISTA_CRIADA],
  "event_id": 17
}
```

## Próximos Passos

Após verificar os logs, você poderá identificar exatamente qual é o erro:

1. **Problema no banco de dados** → Executar migrations/scripts SQL
2. **Problema de validação** → Ajustar dados enviados
3. **Problema de lógica** → Verificar regras de negócio

## Contato/Suporte

Se o erro persistir após seguir estes passos, envie:
1. Os logs da aplicação (últimas 50 linhas)
2. Resultado do script `verificar_estrutura_conquistas.sql`
3. Os dados que você está enviando na requisição

