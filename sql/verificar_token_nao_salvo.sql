-- ===============================================
-- VERIFICAR: Token JWT NÃO é Salvo no Banco
-- ===============================================

-- 1. Verificar se há alguma tabela de tokens/sessões JWT
-- ===============================================
SHOW TABLES LIKE '%token%';
-- Resultado esperado: Vazio ou apenas tabelas de outros sistemas (não JWT)

SHOW TABLES LIKE '%jwt%';
-- Resultado esperado: Vazio

SHOW TABLES LIKE '%session%';
-- Resultado esperado: Vazio (não usa sessões para API)


-- 2. Verificar colunas em tabelas de usuários
-- ===============================================
DESCRIBE usuarios;
-- Campos esperados:
-- id, nome, email, senha (hasheada), cpf, etc.
-- ❌ NÃO deve ter: token, jwt, access_token, session_id


-- 3. Buscar por qualquer coluna com "token" no nome
-- ===============================================
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    COLUMN_TYPE,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND COLUMN_NAME LIKE '%token%'
ORDER BY TABLE_NAME, COLUMN_NAME;

-- Resultado esperado:
-- Pode aparecer: password_reset_token, device_token (push notifications), etc.
-- ❌ NÃO deve aparecer: jwt_token, access_token, auth_token (para autenticação API)


-- 4. Verificar se há logs de segurança (estes SIM existem)
-- ===============================================
SELECT 
    event_type,
    identifier AS email_tentativa,
    ip_address,
    user_id,
    details,
    created_at
FROM security_logs
WHERE user_id = 6  -- Troque pelo ID do usuário testado
ORDER BY created_at DESC
LIMIT 10;

-- Resultado esperado:
-- ✅ Mostra eventos: login_success, login_attempt, etc.
-- ❌ NÃO mostra: o token JWT em si


-- 5. Verificar extrato de pontos (este também existe)
-- ===============================================
SELECT 
    id,
    user_id,
    tipo,
    pontos,
    descricao,
    atribuido_por,
    created_at
FROM extrato_pontos
WHERE user_id = 6
ORDER BY created_at DESC
LIMIT 5;

-- Resultado esperado:
-- ✅ Mostra transações de pontos (DEBITO, CREDITO)
-- ❌ NÃO mostra: token JWT


-- ===============================================
-- CONCLUSÃO
-- ===============================================

/*
❌ Token JWT NÃO é armazenado no banco de dados
❌ Token JWT NÃO é armazenado em sessões no servidor
✅ Token JWT é retornado na resposta do login
✅ Token JWT deve ser armazenado pelo CLIENTE (app/web)
✅ Token JWT é enviado de volta em cada requisição

Por quê?
JWT é STATELESS (sem estado):
• O token contém todas as informações dentro dele
• O servidor valida usando a assinatura (JWT_SECRET_KEY)
• Não precisa consultar banco de dados
• Melhor performance e escalabilidade

O que É salvo no banco:
✅ Dados do usuário (id, nome, email, senha hasheada)
✅ Logs de segurança (eventos de login/logout)
✅ Extrato de pontos (transações)
✅ Permissões e grupos

O que NÃO é salvo:
❌ Token JWT de acesso (expira em 24h)
❌ Refresh token (expira em 30 dias)
❌ Sessões de autenticação
*/


-- ===============================================
-- TESTE PRÁTICO: Fazer Login e Verificar
-- ===============================================

/*
PASSO 1: Fazer login via API

POST /api/auth/login
Content-Type: application/json

{
  "email": "usuario@exemplo.com",
  "password": "senha123"
}

RESPOSTA:
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",  ← Token gerado
    "refresh_token": "eyJ0eXAiOiJKV1Q...",
    "user": { ... }
  }
}

PASSO 2: Copiar o token da resposta
Exemplo: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjo2...

PASSO 3: Tentar buscar esse token no banco
*/

-- Buscar em TODAS as tabelas por esse token (substitua pelo seu token real)
SELECT 'usuarios' as tabela, id, nome, email 
FROM usuarios 
WHERE CONCAT_WS('|', id, nome, email, senha, cpf) LIKE '%eyJ0eXAiOiJKV1QiLCJhbGc%'
LIMIT 1;

-- Resultado esperado: Vazio (não encontrado)

SELECT 'security_logs' as tabela, id, event_type, identifier
FROM security_logs
WHERE details LIKE '%eyJ0eXAiOiJKV1QiLCJhbGc%'
LIMIT 1;

-- Resultado esperado: Vazio (não encontrado)


-- ===============================================
-- DEMONSTRAÇÃO: Como o JWT é Validado
-- ===============================================

/*
Quando você faz uma requisição protegida:

┌─────────────────────────────────────────────┐
│ Cliente                                     │
│                                             │
│ GET /api/usuarios/saldo/6                   │
│ Authorization: Bearer eyJ0eXAi...           │
└─────────────────┬───────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────┐
│ Servidor: JwtAuthFilter                     │
│                                             │
│ 1. Extrai token do header                  │
│ 2. Decodifica usando JWT_SECRET_KEY         │
│ 3. Valida assinatura                        │
│ 4. Verifica expiração (exp)                 │
│ 5. Se válido, armazena em $request          │
│    $request->usuarioAutenticado = payload   │
│                                             │
│ ❌ NÃO consulta banco de dados!            │
│ ✅ Apenas valida assinatura matemática      │
└─────────────────┬───────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────┐
│ Controller: retirarPontos()                 │
│                                             │
│ $user = $request->usuarioAutenticado;       │
│ // Array com dados do token JWT            │
│ // ['user_id' => 6, 'email' => '...']      │
│                                             │
│ ✅ Agora sim consulta banco para ações     │
│    $usuario = find($user['user_id']);      │
└─────────────────────────────────────────────┘
*/


-- ===============================================
-- FAQ: Perguntas Frequentes
-- ===============================================

/*
Q: Se o token não é salvo, como o servidor "lembra" que estou logado?
A: O servidor NÃO lembra! JWT é stateless. O token CONTÉM todas as informações
   dentro dele, criptografadas e assinadas. O servidor apenas valida a assinatura.

Q: Posso invalidar um token antes de expirar?
A: Não diretamente com JWT puro. Alternativas:
   • Blacklist: Criar tabela de tokens revogados (perde vantagem stateless)
   • Trocar JWT_SECRET_KEY: Invalida TODOS os tokens (drástico)
   • Usar refresh token rotation: Mais seguro

Q: E se alguém roubar meu token?
A: O token funciona como uma "chave temporária":
   • Expira em 24 horas (reduz janela de ataque)
   • Use HTTPS para prevenir interceptação
   • Não armazene em lugares inseguros (URL, variáveis globais)
   • Implemente detecção de atividade suspeita (IP, user-agent)

Q: Por que usar JWT em vez de sessões?
A: Vantagens do JWT:
   ✅ Stateless: não precisa armazenar sessões
   ✅ Escalável: funciona em múltiplos servidores sem Redis/memcached
   ✅ Cross-domain: funciona entre diferentes domínios
   ✅ Mobile-friendly: não depende de cookies
   
   Desvantagens:
   ❌ Não pode ser invalidado facilmente
   ❌ Tamanho maior (200-500 bytes vs 32 bytes de session ID)
   ❌ Dados sensíveis ficam no token (use apenas dados não-críticos)

Q: Onde devo armazenar o token no cliente?
A: Depende da plataforma:
   • Web: Cookie HttpOnly (mais seguro) ou localStorage (mais simples)
   • Mobile: SecureStore/Keychain (criptografado no dispositivo)
   • Nunca em: URL, variáveis globais, LocalStorage sem criptografia adicional
*/


-- ===============================================
-- REFERÊNCIAS
-- ===============================================

/*
📄 Arquivos de Código:
• app/Controllers/Api/Auth.php - Gera o token no login
• app/Libraries/Jwt.php - Encode/decode do JWT
• app/Filters/JwtAuthFilter.php - Valida token em rotas protegidas
• app/Controllers/Api/Usuarios.php - Usa token validado

📚 Documentação:
• ONDE_FICA_SALVO_TOKEN_JWT.md - Explicação completa
• TOKEN_JWT_RESUMO_VISUAL.md - Diagramas visuais
• API_AUTH_DOCUMENTATION.md - Documentação da API
• README_API_AUTH.md - Quick start

🌐 Recursos Externos:
• https://jwt.io/ - Decodificar e debugar tokens
• https://auth0.com/docs/secure/tokens/json-web-tokens - Guia completo
*/

