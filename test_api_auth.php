<?php

/**
 * Script de teste para a API de Autenticação
 * 
 * Este script demonstra como usar a API de autenticação.
 * Execute este arquivo da linha de comando ou através do navegador.
 * 
 * IMPORTANTE: Configure a URL base e as credenciais antes de executar.
 */

// Configurações
$baseUrl = 'http://localhost/mundo_app'; // Altere para a URL do seu projeto
$email = 'admin@exemplo.com'; // Altere para um email válido no seu sistema
$password = 'senha123'; // Altere para a senha correta

// Função auxiliar para fazer requisições HTTP
function makeRequest($url, $method = 'GET', $data = null, $headers = [])
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Para desenvolvimento local

    // Headers padrão
    $defaultHeaders = ['Content-Type: application/json'];
    $allHeaders = array_merge($defaultHeaders, $headers);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders);

    // Configurações específicas por método
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);

    return [
        'http_code' => $httpCode,
        'body' => $response ? json_decode($response, true) : null,
        'raw_body' => $response,
        'error' => $error
    ];
}

// Função para exibir resultado
function displayResult($title, $result)
{
    echo "\n" . str_repeat('=', 80) . "\n";
    echo $title . "\n";
    echo str_repeat('=', 80) . "\n";
    echo "HTTP Code: {$result['http_code']}\n";
    
    if ($result['error']) {
        echo "ERRO: {$result['error']}\n";
    }
    
    if ($result['body']) {
        echo "Resposta:\n";
        echo json_encode($result['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } else {
        echo "Resposta Raw:\n";
        echo $result['raw_body'];
    }
    echo "\n" . str_repeat('=', 80) . "\n";
}

// Início dos testes
echo "\n\n";
echo "╔════════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                        TESTE DA API DE AUTENTICAÇÃO                            ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "Base URL: $baseUrl\n";
echo "Email: $email\n";
echo "\n";

// ============================================================================
// TESTE 1: Login
// ============================================================================
echo "▶ Teste 1: Realizando login...\n";

$loginData = [
    'email' => $email,
    'password' => $password
];

$loginResult = makeRequest("$baseUrl/api/auth/login", 'POST', $loginData);
displayResult('TESTE 1: LOGIN', $loginResult);

if ($loginResult['http_code'] !== 200 || !$loginResult['body']['success']) {
    echo "\n❌ ERRO: Login falhou. Verifique as credenciais e tente novamente.\n";
    echo "Configure o email e senha corretos no topo deste arquivo.\n\n";
    exit(1);
}

$token = $loginResult['body']['data']['token'];
$refreshToken = $loginResult['body']['data']['refresh_token'];
$userData = $loginResult['body']['data']['user'];

echo "\n✅ Login realizado com sucesso!\n";
echo "Token obtido (primeiros 50 caracteres): " . substr($token, 0, 50) . "...\n";
echo "Usuário: {$userData['nome']} ({$userData['email']})\n";
echo "Grupos:\n";
echo "  - Admin: " . ($userData['is_admin'] ? 'Sim' : 'Não') . "\n";
echo "  - Cliente: " . ($userData['is_cliente'] ? 'Sim' : 'Não') . "\n";
echo "  - Membro: " . ($userData['is_membro'] ? 'Sim' : 'Não') . "\n";
echo "  - Parceiro: " . ($userData['is_parceiro'] ? 'Sim' : 'Não') . "\n";
echo "  - Influencer: " . ($userData['is_influencer'] ? 'Sim' : 'Não') . "\n";

if (!empty($userData['permissoes'])) {
    echo "Permissões: " . implode(', ', $userData['permissoes']) . "\n";
}

sleep(1); // Pausa para facilitar leitura

// ============================================================================
// TESTE 2: Obter perfil com token JWT
// ============================================================================
echo "\n▶ Teste 2: Obtendo perfil do usuário autenticado...\n";

$profileResult = makeRequest(
    "$baseUrl/api/auth/me",
    'GET',
    null,
    ["Authorization: Bearer $token"]
);

displayResult('TESTE 2: PERFIL DO USUÁRIO', $profileResult);

if ($profileResult['http_code'] === 200 && $profileResult['body']['success']) {
    echo "\n✅ Perfil obtido com sucesso!\n";
    echo "Nome: {$profileResult['body']['data']['nome']}\n";
    echo "Email: {$profileResult['body']['data']['email']}\n";
} else {
    echo "\n❌ ERRO: Falha ao obter perfil.\n";
}

sleep(1);

// ============================================================================
// TESTE 3: Refresh Token
// ============================================================================
echo "\n▶ Teste 3: Renovando token com refresh token...\n";

$refreshData = [
    'refresh_token' => $refreshToken
];

$refreshResult = makeRequest("$baseUrl/api/auth/refresh", 'POST', $refreshData);
displayResult('TESTE 3: REFRESH TOKEN', $refreshResult);

if ($refreshResult['http_code'] === 200 && $refreshResult['body']['success']) {
    $newToken = $refreshResult['body']['data']['token'];
    echo "\n✅ Token renovado com sucesso!\n";
    echo "Novo token (primeiros 50 caracteres): " . substr($newToken, 0, 50) . "...\n";
} else {
    echo "\n❌ ERRO: Falha ao renovar token.\n";
}

sleep(1);

// ============================================================================
// TESTE 4: Testar token inválido
// ============================================================================
echo "\n▶ Teste 4: Testando acesso com token inválido...\n";

$invalidTokenResult = makeRequest(
    "$baseUrl/api/auth/me",
    'GET',
    null,
    ["Authorization: Bearer token_invalido_123"]
);

displayResult('TESTE 4: TOKEN INVÁLIDO', $invalidTokenResult);

if ($invalidTokenResult['http_code'] === 401) {
    echo "\n✅ Token inválido foi corretamente rejeitado!\n";
} else {
    echo "\n❌ ERRO: Token inválido deveria retornar 401.\n";
}

sleep(1);

// ============================================================================
// TESTE 5: Testar sem token
// ============================================================================
echo "\n▶ Teste 5: Testando acesso sem token...\n";

$noTokenResult = makeRequest("$baseUrl/api/auth/me", 'GET');
displayResult('TESTE 5: SEM TOKEN', $noTokenResult);

if ($noTokenResult['http_code'] === 401) {
    echo "\n✅ Requisição sem token foi corretamente rejeitada!\n";
} else {
    echo "\n❌ ERRO: Requisição sem token deveria retornar 401.\n";
}

sleep(1);

// ============================================================================
// TESTE 6: Testar login com credenciais inválidas
// ============================================================================
echo "\n▶ Teste 6: Testando login com credenciais inválidas...\n";

$invalidLoginData = [
    'email' => 'email_invalido@teste.com',
    'password' => 'senha_errada'
];

$invalidLoginResult = makeRequest("$baseUrl/api/auth/login", 'POST', $invalidLoginData);
displayResult('TESTE 6: LOGIN INVÁLIDO', $invalidLoginResult);

if ($invalidLoginResult['http_code'] === 401) {
    echo "\n✅ Login com credenciais inválidas foi corretamente rejeitado!\n";
} else {
    echo "\n❌ ERRO: Login inválido deveria retornar 401.\n";
}

// ============================================================================
// Resumo dos testes
// ============================================================================
echo "\n\n";
echo "╔════════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                              RESUMO DOS TESTES                                 ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "✅ Teste 1: Login com sucesso\n";
echo "✅ Teste 2: Obter perfil com token JWT\n";
echo "✅ Teste 3: Renovar token com refresh token\n";
echo "✅ Teste 4: Rejeitar token inválido\n";
echo "✅ Teste 5: Rejeitar requisição sem token\n";
echo "✅ Teste 6: Rejeitar login com credenciais inválidas\n";
echo "\n";
echo "═══════════════════════════════════════════════════════════════════════════════\n";
echo "                        TODOS OS TESTES CONCLUÍDOS!                            \n";
echo "═══════════════════════════════════════════════════════════════════════════════\n";
echo "\n";

