# 🏆 Sistema de Conquistas (Achievements) - Mundo App

Sistema completo de gamificação com conquistas, pontos e ranking para eventos.

---

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Arquitetura](#arquitetura)
3. [Instalação](#instalação)
4. [Estrutura de Tabelas](#estrutura-de-tabelas)
5. [APIs Disponíveis](#apis-disponíveis)
6. [Fluxo de Funcionamento](#fluxo-de-funcionamento)
7. [Exemplos de Uso](#exemplos-de-uso)

---

## 🎯 Visão Geral

O Sistema de Conquistas permite:

- ✅ Criar conquistas personalizadas por evento
- ✅ Atribuir conquistas automaticamente ou manualmente
- ✅ Sistema de pontos com extrato completo
- ✅ Ranking de usuários por evento
- ✅ Controle de níveis (Bronze, Prata, Ouro, etc)
- ✅ Auditoria completa de todas as transações
- ✅ Prevenção de duplicação de conquistas
- ✅ Sistema de revogação com ajuste automático

---

## 🏗️ Arquitetura

### Componentes Criados

```
app/
├── Controllers/
│   └── Api/
│       ├── Conquistas.php              # CRUD de conquistas
│       └── UsuarioConquistas.php       # Atribuição e gestão
├── Models/
│   ├── ConquistaModel.php              # Model de conquistas
│   ├── UsuarioConquistaModel.php       # Model de atribuições
│   └── ExtratoPontosModel.php          # Model de extrato
├── Entities/
│   ├── ConquistaEntity.php
│   ├── UsuarioConquistaEntity.php
│   └── ExtratoPontosEntity.php
├── Services/
│   └── ConquistaService.php            # Lógica de negócio (transações)
└── Database/
    └── Migrations/
        ├── 2024-11-21-000000_CreateConquistasTable.php
        ├── 2024-11-21-010000_CreateUsuarioConquistasTable.php
        └── 2024-11-21-020000_CreateExtratoPontosTable.php

sql/
└── add_pontos_column_usuarios.sql      # Adiciona coluna pontos

Documentação:
├── API_CONQUISTAS_DOCUMENTATION.md
└── API_USUARIO_CONQUISTAS_DOCUMENTATION.md
```

---

## 🚀 Instalação

### 1. Adicionar coluna de pontos na tabela usuarios

```bash
# Execute o SQL
mysql -u usuario -p database < sql/add_pontos_column_usuarios.sql
```

Ou manualmente:
```sql
ALTER TABLE `usuarios` 
ADD COLUMN `pontos` INT(11) NOT NULL DEFAULT 0 COMMENT 'Total de pontos acumulados' 
AFTER `ativo`;
```

### 2. Executar Migrations

```bash
php spark migrate
```

Isso criará as tabelas:
- `conquistas` - Catálogo de conquistas
- `usuario_conquistas` - Conquistas atribuídas aos usuários
- `extrato_pontos` - Histórico de transações de pontos

### 3. Verificar Rotas

As rotas são automaticamente carregadas em `app/Config/Routes.php`:

- `/api/conquistas/*` - Gerenciar conquistas
- `/api/usuario-conquistas/*` - Atribuir e consultar

---

## 📊 Estrutura de Tabelas

### 1. conquistas
Catálogo de conquistas disponíveis

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT | ID único |
| event_id | INT | ID do evento |
| nome_conquista | VARCHAR(255) | Nome da conquista |
| pontos | INT | Pontos que vale |
| nivel | VARCHAR(50) | BRONZE, PRATA, OURO, etc |
| status | VARCHAR(50) | ATIVA, INATIVA, BLOQUEADA |

### 2. usuario_conquistas
Conquistas atribuídas aos usuários

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT | ID único |
| conquista_id | INT | ID da conquista |
| event_id | INT | ID do evento |
| user_id | INT | ID do usuário |
| pontos | INT | Pontos ganhos |
| admin | TINYINT | 0=automático, 1=manual |
| status | VARCHAR(50) | ATIVA, REVOGADA |
| atribuido_por | INT | ID do admin (se manual) |

**UNIQUE KEY**: (`user_id`, `conquista_id`, `event_id`) - Previne duplicação

### 3. extrato_pontos
Histórico imutável de transações

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT | ID único |
| user_id | INT | ID do usuário |
| event_id | INT | ID do evento |
| tipo | VARCHAR(50) | CONQUISTA, BONUS, REVOGACAO, etc |
| pontos | INT | Pontos (+/-) |
| saldo_anterior | INT | Saldo antes |
| saldo_atual | INT | Saldo depois |
| descricao | TEXT | Descrição da transação |
| referencia_tipo | VARCHAR(50) | Tipo da entidade relacionada |
| referencia_id | INT | ID da entidade relacionada |

### 4. usuarios (coluna adicionada)

| Campo | Tipo | Descrição |
|-------|------|-----------|
| pontos | INT | Total de pontos acumulados |

---

## 🔌 APIs Disponíveis

### API de Conquistas (`/api/conquistas`)

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/conquistas` | Lista todas as conquistas |
| GET | `/api/conquistas/{id}` | Detalhes de uma conquista |
| GET | `/api/conquistas/evento/{event_id}` | Conquistas por evento |
| POST | `/api/conquistas` | Cria nova conquista |
| PUT/PATCH | `/api/conquistas/{id}` | Atualiza conquista |
| DELETE | `/api/conquistas/{id}` | Deleta conquista |

### API de Atribuição (`/api/usuario-conquistas`)

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/usuario-conquistas/usuario/{user_id}` | Conquistas do usuário |
| GET | `/api/usuario-conquistas/extrato/{user_id}` | Extrato de pontos |
| GET | `/api/usuario-conquistas/ranking/{event_id}` | Ranking por evento |
| POST | `/api/usuario-conquistas/atribuir` | Atribui conquista |
| POST | `/api/usuario-conquistas/{id}/revogar` | Revoga conquista |

**Todas as rotas requerem autenticação JWT.**

---

## 🔄 Fluxo de Funcionamento

### 1. Criar Conquistas para o Evento

```bash
POST /api/conquistas
{
  "event_id": 1,
  "nome_conquista": "Primeira Participação",
  "pontos": 10,
  "nivel": "BRONZE",
  "status": "ATIVA"
}
```

### 2. Atribuir Conquista ao Usuário

```bash
POST /api/usuario-conquistas/atribuir
{
  "user_id": 1,
  "conquista_id": 1,
  "event_id": 1
}
```

**O que acontece internamente:**

1. ✅ Verifica se usuário e conquista existem
2. ✅ Verifica se usuário já possui a conquista
3. ✅ Busca saldo atual do usuário
4. ✅ Cria registro em `usuario_conquistas`
5. ✅ **Soma pontos** na tabela `usuarios`
6. ✅ Cria entrada no `extrato_pontos`
7. ✅ Commit da transação (tudo ou nada)

### 3. Ver Conquistas do Usuário

```bash
GET /api/usuario-conquistas/usuario/1?event_id=1
```

### 4. Ver Ranking

```bash
GET /api/usuario-conquistas/ranking/1?limit=10
```

---

## 💡 Exemplos de Uso

### Exemplo 1: Conquistas de um Evento

```sql
-- Conquistas para o evento MundoDream 2025
INSERT INTO `conquistas` (`event_id`, `nome_conquista`, `pontos`, `nivel`, `status`) VALUES
(1, 'Primeira Participação', 10, 'BRONZE', 'ATIVA'),
(1, 'Participou de 3 Painéis', 25, 'PRATA', 'ATIVA'),
(1, 'Conheceu 5 Convidados', 50, 'OURO', 'ATIVA'),
(1, 'Mestre Cosplayer', 100, 'PLATINA', 'ATIVA'),
(1, 'Completou Todo o Cronograma', 200, 'DIAMANTE', 'ATIVA'),
(1, 'Comprou no Meet & Greet', 15, 'BRONZE', 'ATIVA'),
(1, 'Tirou Foto com Convidado', 20, 'BRONZE', 'ATIVA'),
(1, 'Participou do Quiz', 30, 'PRATA', 'ATIVA');
```

### Exemplo 2: Atribuição Automática via Sistema

```php
// No seu código quando usuário completa uma ação
use App\Services\ConquistaService;

$conquistaService = new ConquistaService();

$result = $conquistaService->atribuirConquista(
    userId: $userId,
    conquistaId: 1, // "Primeira Participação"
    eventId: 1,
    isAdmin: false, // Automático
    atribuidoPor: null
);

if ($result['success']) {
    // Notificar usuário sobre a conquista
    notify($userId, "Você desbloqueou: " . $result['data']['conquista_nome']);
}
```

### Exemplo 3: Atribuição Manual por Admin

```javascript
// No painel admin
const atribuir = await fetch('/api/usuario-conquistas/atribuir', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + adminToken,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    user_id: 5,
    conquista_id: 10, // Conquista especial
    event_id: 1,
    admin: true,
    atribuido_por: adminId
  })
});
```

### Exemplo 4: Exibir Ranking no Site

```javascript
// Buscar top 10
const response = await fetch('/api/usuario-conquistas/ranking/1?limit=10', {
  headers: { 'Authorization': 'Bearer ' + token }
});

const { data } = await response.json();

data.forEach(user => {
  console.log(`${user.posicao}º ${user.nome} - ${user.total_pontos} pontos`);
});
```

---

## 🔐 Regras de Segurança

1. **Uma conquista por usuário** - Garantido por UNIQUE KEY
2. **Conquistas imutáveis** - Não podem ser editadas após atribuição
3. **Extrato imutável** - Histórico completo preservado
4. **Transações atômicas** - Rollback automático em caso de erro
5. **Autenticação obrigatória** - JWT em todas as rotas
6. **Auditoria completa** - Registra quem fez cada ação

---

## 📈 Performance

- Índices otimizados em todas as tabelas
- Unique key previne duplicação
- Queries otimizadas para ranking e extrato
- Soft delete preserva histórico

---

## 🎨 Sugestões de Níveis

| Nível | Pontos | Cor Sugerida |
|-------|--------|--------------|
| BRONZE | 10-20 | #CD7F32 |
| PRATA | 25-40 | #C0C0C0 |
| OURO | 50-75 | #FFD700 |
| PLATINA | 100-150 | #E5E4E2 |
| DIAMANTE | 200+ | #B9F2FF |

---

## 🐛 Troubleshooting

### Erro: "Usuário já possui esta conquista"
- Uma conquista só pode ser atribuída uma vez por usuário/evento
- Verifique se já foi atribuída antes

### Erro ao criar conquista
- Verifique se o `event_id` existe
- Valide os campos obrigatórios
- Check se `status` é ATIVA, INATIVA ou BLOQUEADA

### Pontos não foram somados
- Verifique se a transação foi completada com sucesso
- Consulte o extrato de pontos para ver o histórico
- Verifique logs de erro

---

## 📚 Documentação Completa

- **API de Conquistas**: `API_CONQUISTAS_DOCUMENTATION.md`
- **API de Atribuição**: `API_USUARIO_CONQUISTAS_DOCUMENTATION.md`

---

## 🎯 Próximos Passos

1. Implementar notificações quando conquista é desbloqueada
2. Criar dashboard visual de conquistas
3. Sistema de badges personalizados
4. Conquistas com pré-requisitos
5. Conquistas secretas/ocultas
6. Compartilhamento social

---

## ✅ Checklist de Implementação

- [x] Migrations criadas
- [x] Models e Entities criados
- [x] Service de conquistas implementado
- [x] Controllers da API criados
- [x] Rotas configuradas
- [x] Documentação completa
- [x] Sistema de transações
- [x] Prevenção de duplicatas
- [x] Extrato de pontos
- [x] Sistema de ranking
- [x] Sistema de revogação

---

**Desenvolvido para Mundo App** 🏆

