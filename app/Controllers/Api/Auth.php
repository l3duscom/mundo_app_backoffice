<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\Jwt;
use App\Libraries\RateLimiter;

/**
 * Controller de autenticação via API
 * Implementa login, refresh token e perfil do usuário autenticado
 * Mantém toda a lógica de permissões e grupos do sistema
 * COM SEGURANÇA APRIMORADA: Rate limiting, IP blocking, logs de auditoria
 */
class Auth extends BaseController
{
    private $usuarioModel;
    private $grupoUsuarioModel;
    private $rateLimiter;

    public function __construct()
    {
        $this->usuarioModel = new \App\Models\UsuarioModel();
        $this->grupoUsuarioModel = new \App\Models\GrupoUsuarioModel();
        $this->rateLimiter = new RateLimiter();
    }

    /**
     * Login via API
     * POST /api/auth/login
     * 
     * @return \CodeIgniter\HTTP\Response JSON response com token e dados do usuário
     */
    public function login()
    {
        $clientIp = RateLimiter::getClientIp();
        $userAgent = $this->request->getUserAgent()->getAgentString();

        // Valida se é uma requisição POST
        if ($this->request->getMethod() !== 'post') {
            $this->logSecurityEvent('invalid_method', null, $clientIp, 'Método inválido: ' . $this->request->getMethod());
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Método não permitido'
                ])
                ->setStatusCode(405);
        }

        // SEGURANÇA: Verifica se IP está bloqueado
        $blockStatus = $this->rateLimiter->isBlocked($clientIp, 'login');
        if ($blockStatus['blocked']) {
            $this->logSecurityEvent('blocked_ip_attempt', null, $clientIp, $blockStatus['reason']);
            
            $response = $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Acesso temporariamente bloqueado',
                    'error' => 'Muitas tentativas de login falhadas. Tente novamente em ' . 
                               gmdate('i:s', $blockStatus['retry_after']) . ' minutos.',
                    'retry_after' => $blockStatus['retry_after']
                ])
                ->setStatusCode(429);
            
            $response->setHeader('Retry-After', (string)$blockStatus['retry_after']);
            
            return $response;
        }

        // SEGURANÇA: Rate limiting para tentativas de login (5 por 5 minutos)
        $rateLimit = $this->rateLimiter->attempt($clientIp, 'login', 5, 300);
        if (!$rateLimit['allowed']) {
            $this->logSecurityEvent('rate_limit_exceeded', null, $clientIp, 'Limite de tentativas excedido');
            
            $response = $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Muitas tentativas de login',
                    'error' => 'Você excedeu o limite de tentativas. Aguarde 15 minutos.',
                    'retry_after' => $rateLimit['retry_after']
                ])
                ->setStatusCode(429);
            
            $response->setHeader('Retry-After', (string)$rateLimit['retry_after']);
            
            return $response;
        }

        // Recupera credenciais
        $email = $this->request->getPost('email') ?? $this->request->getJSON()->email ?? null;
        $password = $this->request->getPost('password') ?? $this->request->getJSON()->password ?? null;

        // Valida se as credenciais foram fornecidas
        if (empty($email) || empty($password)) {
            $this->logSecurityEvent('missing_credentials', $email, $clientIp, 'Credenciais não fornecidas');
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Email e senha são obrigatórios',
                    'errors' => [
                        'email' => empty($email) ? 'Email é obrigatório' : null,
                        'password' => empty($password) ? 'Senha é obrigatória' : null
                    ],
                    'remaining_attempts' => $rateLimit['remaining']
                ])
                ->setStatusCode(400);
        }

        // Validação de formato de email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->logSecurityEvent('invalid_email_format', $email, $clientIp, 'Formato de email inválido');
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Formato de email inválido',
                    'remaining_attempts' => $rateLimit['remaining']
                ])
                ->setStatusCode(400);
        }

        // Busca o usuário
        $usuario = $this->usuarioModel->buscaUsuarioPorEmail($email);

        if ($usuario === null) {
            $this->logSecurityEvent('invalid_credentials', $email, $clientIp, 'Usuário não encontrado');
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Credenciais inválidas',
                    'errors' => [
                        'credenciais' => 'Email ou senha incorretos'
                    ],
                    'remaining_attempts' => $rateLimit['remaining']
                ])
                ->setStatusCode(401);
        }

        // Verifica a senha
        if ($usuario->verificaPassword($password) === false) {
            $this->logSecurityEvent('invalid_password', $email, $clientIp, 'Senha incorreta', $usuario->id);
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Credenciais inválidas',
                    'errors' => [
                        'credenciais' => 'Email ou senha incorretos'
                    ],
                    'remaining_attempts' => $rateLimit['remaining']
                ])
                ->setStatusCode(401);
        }

        // Verifica se o usuário está ativo
        if ($usuario->ativo == false) {
            $this->logSecurityEvent('inactive_user_attempt', $email, $clientIp, 'Tentativa de login com usuário inativo', $usuario->id);
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Usuário inativo',
                    'errors' => [
                        'usuario' => 'Sua conta está inativa. Entre em contato com o administrador.'
                    ]
                ])
                ->setStatusCode(403);
        }

        // SUCESSO: Limpa tentativas falhas
        $this->rateLimiter->clear($clientIp, 'login');

        // Define as permissões e grupos do usuário
        $usuario = $this->definePermissoesDoUsuario($usuario);

        // Cria o payload do token JWT
        $payload = [
            'user_id' => $usuario->id,
            'email' => $usuario->email,
            'nome' => $usuario->nome,
            'is_admin' => $usuario->is_admin,
            'is_cliente' => $usuario->is_cliente,
            'is_membro' => $usuario->is_membro,
            'is_parceiro' => $usuario->is_parceiro,
            'is_influencer' => $usuario->is_influencer,
        ];

        // Adiciona permissões se o usuário não for admin nem cliente
        if (!$usuario->is_admin && isset($usuario->permissoes)) {
            $payload['permissoes'] = $usuario->permissoes;
        }

        // Gera o token JWT (expira em 24 horas)
        $token = Jwt::encode($payload, 86400);

        // Gera refresh token (expira em 30 dias)
        $refreshToken = Jwt::encode([
            'user_id' => $usuario->id,
            'type' => 'refresh'
        ], 2592000);

        // Prepara dados do usuário para resposta (sem dados sensíveis)
        $userData = [
            'id' => $usuario->id,
            'nome' => $usuario->nome,
            'email' => $usuario->email,
            'codigo' => $usuario->codigo ?? null,
            'ativo' => $usuario->ativo,
            'is_admin' => $usuario->is_admin,
            'is_cliente' => $usuario->is_cliente,
            'is_membro' => $usuario->is_membro,
            'is_parceiro' => $usuario->is_parceiro,
            'is_influencer' => $usuario->is_influencer,
        ];

        // Adiciona permissões na resposta se existirem
        if (isset($usuario->permissoes) && !empty($usuario->permissoes)) {
            $userData['permissoes'] = $usuario->permissoes;
        }

        // SUCESSO: Log de auditoria
        $this->logSecurityEvent('login_success', $email, $clientIp, 'Login bem-sucedido via API', $usuario->id);

        // Retorna sucesso com token e dados do usuário
        return $this->response
            ->setJSON([
                'success' => true,
                'message' => 'Login realizado com sucesso',
                'data' => [
                    'token' => $token,
                    'refresh_token' => $refreshToken,
                    'token_type' => 'Bearer',
                    'expires_in' => 86400, // 24 horas em segundos
                    'user' => $userData
                ]
            ])
            ->setStatusCode(200);
    }

    /**
     * Refresh token - gera um novo token a partir de um refresh token válido
     * POST /api/auth/refresh
     * 
     * @return \CodeIgniter\HTTP\Response JSON response com novo token
     */
    public function refresh()
    {
        // Valida se é uma requisição POST
        if ($this->request->getMethod() !== 'post') {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Método não permitido'
                ])
                ->setStatusCode(405);
        }

        // Recupera o refresh token
        $refreshToken = $this->request->getPost('refresh_token') 
            ?? $this->request->getJSON()->refresh_token 
            ?? null;

        if (empty($refreshToken)) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Refresh token é obrigatório'
                ])
                ->setStatusCode(400);
        }

        // Decodifica o refresh token
        $payload = Jwt::decode($refreshToken);

        if ($payload === null || !isset($payload['user_id']) || ($payload['type'] ?? null) !== 'refresh') {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Refresh token inválido ou expirado'
                ])
                ->setStatusCode(401);
        }

        // Busca o usuário
        $usuario = $this->usuarioModel->find($payload['user_id']);

        if ($usuario === null || $usuario->ativo == false) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Usuário não encontrado ou inativo'
                ])
                ->setStatusCode(401);
        }

        // Define as permissões e grupos do usuário
        $usuario = $this->definePermissoesDoUsuario($usuario);

        // Cria o payload do novo token JWT
        $newPayload = [
            'user_id' => $usuario->id,
            'email' => $usuario->email,
            'nome' => $usuario->nome,
            'is_admin' => $usuario->is_admin,
            'is_cliente' => $usuario->is_cliente,
            'is_membro' => $usuario->is_membro,
            'is_parceiro' => $usuario->is_parceiro,
            'is_influencer' => $usuario->is_influencer,
        ];

        // Adiciona permissões se o usuário não for admin nem cliente
        if (!$usuario->is_admin && isset($usuario->permissoes)) {
            $newPayload['permissoes'] = $usuario->permissoes;
        }

        // Gera novo token JWT
        $newToken = Jwt::encode($newPayload, 86400);

        // Retorna novo token
        return $this->response
            ->setJSON([
                'success' => true,
                'message' => 'Token renovado com sucesso',
                'data' => [
                    'token' => $newToken,
                    'token_type' => 'Bearer',
                    'expires_in' => 86400
                ]
            ])
            ->setStatusCode(200);
    }

    /**
     * Retorna o perfil do usuário autenticado
     * GET /api/auth/me
     * Requer autenticação JWT
     * 
     * @return \CodeIgniter\HTTP\Response JSON response com dados do usuário
     */
    public function me()
    {
        // Recupera o token do header
        $authHeader = $this->request->getServer('HTTP_AUTHORIZATION');
        $token = Jwt::extractFromHeader($authHeader);

        if (empty($token)) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Token de autenticação não fornecido'
                ])
                ->setStatusCode(401);
        }

        // Decodifica o token
        $payload = Jwt::decode($token);

        if ($payload === null || !isset($payload['user_id'])) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Token inválido ou expirado'
                ])
                ->setStatusCode(401);
        }

        // Busca o usuário atualizado
        $usuario = $this->usuarioModel->find($payload['user_id']);

        if ($usuario === null || $usuario->ativo == false) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Usuário não encontrado ou inativo'
                ])
                ->setStatusCode(401);
        }

        // Define as permissões e grupos do usuário
        $usuario = $this->definePermissoesDoUsuario($usuario);

        // Prepara dados do usuário para resposta
        $userData = [
            'id' => $usuario->id,
            'nome' => $usuario->nome,
            'email' => $usuario->email,
            'codigo' => $usuario->codigo ?? null,
            'ativo' => $usuario->ativo,
            'imagem' => $usuario->imagem ?? null,
            'is_admin' => $usuario->is_admin,
            'is_cliente' => $usuario->is_cliente,
            'is_membro' => $usuario->is_membro,
            'is_parceiro' => $usuario->is_parceiro,
            'is_influencer' => $usuario->is_influencer,
            'created_at' => $usuario->created_at,
            'updated_at' => $usuario->updated_at,
        ];

        // Adiciona permissões na resposta se existirem
        if (isset($usuario->permissoes) && !empty($usuario->permissoes)) {
            $userData['permissoes'] = $usuario->permissoes;
        }

        return $this->response
            ->setJSON([
                'success' => true,
                'data' => $userData
            ])
            ->setStatusCode(200);
    }

    /**
     * Define as permissões do usuário de acordo com seus grupos
     * Usa a mesma lógica da classe Autenticacao
     * 
     * @param object $usuario
     * @return object Usuário com permissões definidas
     */
    private function definePermissoesDoUsuario(object $usuario): object
    {
        // Verifica se é admin (grupo 1)
        $usuario->is_admin = $this->verificaGrupo(1, $usuario->id);

        // Se for admin, não é dos outros grupos
        if ($usuario->is_admin) {
            $usuario->is_cliente = false;
            $usuario->is_membro = false;
            $usuario->is_parceiro = false;
            $usuario->is_influencer = false;
        } else {
            // Verifica os outros grupos
            $usuario->is_cliente = $this->verificaGrupo(2, $usuario->id);
            $usuario->is_membro = $this->verificaGrupo(3, $usuario->id);
            $usuario->is_parceiro = $this->verificaGrupo(4, $usuario->id);
            $usuario->is_influencer = $this->verificaGrupo(5, $usuario->id);
        }

        // Recupera permissões específicas se não for admin
        if (!$usuario->is_admin) {
            $permissoes = $this->usuarioModel->recuperaPermissoesDoUsuarioLogado($usuario->id);
            $usuario->permissoes = array_column($permissoes, 'permissao');
        }

        return $usuario;
    }

    /**
     * Verifica se o usuário pertence a um grupo específico
     * 
     * @param int $grupo_id ID do grupo
     * @param int $usuario_id ID do usuário
     * @return bool
     */
    private function verificaGrupo(int $grupo_id, int $usuario_id): bool
    {
        $resultado = $this->grupoUsuarioModel->usuarioEstaNoGrupo($grupo_id, $usuario_id);
        return $resultado !== null;
    }

    /**
     * Registra eventos de segurança para auditoria
     * 
     * @param string $event_type Tipo de evento (login_success, invalid_password, etc)
     * @param string|null $email Email do usuário (se disponível)
     * @param string $ip_address IP de origem
     * @param string $description Descrição do evento
     * @param int|null $user_id ID do usuário (se disponível)
     * @return void
     */
    private function logSecurityEvent(
        string $event_type,
        ?string $email,
        string $ip_address,
        string $description,
        ?int $user_id = null
    ): void {
        $userAgent = $this->request->getUserAgent()->getAgentString();
        
        // Log estruturado para análise
        $logData = [
            'event_type' => $event_type,
            'email' => $email,
            'user_id' => $user_id,
            'ip_address' => $ip_address,
            'user_agent' => substr($userAgent, 0, 255), // Limita tamanho
            'description' => $description,
            'timestamp' => date('Y-m-d H:i:s'),
            'uri' => $this->request->getUri()->getPath()
        ];

        // Nível de log baseado no tipo de evento
        $logLevel = 'info';
        if (in_array($event_type, ['blocked_ip_attempt', 'rate_limit_exceeded', 'invalid_password'])) {
            $logLevel = 'warning';
        } elseif (in_array($event_type, ['invalid_method', 'inactive_user_attempt'])) {
            $logLevel = 'error';
        }

        // Log no arquivo
        log_message($logLevel, 'Security Event: ' . json_encode($logData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        // Registra no banco de dados para auditoria (async para não impactar performance)
        $this->saveSecurityLogToDB($logData);
    }

    /**
     * Salva log de segurança no banco de dados
     * 
     * @param array $logData
     * @return void
     */
    private function saveSecurityLogToDB(array $logData): void
    {
        try {
            $db = \Config\Database::connect();
            
            // Cria tabela se não existir
            $this->createSecurityLogsTableIfNotExists($db);

            // Insere registro
            $db->table('security_logs')->insert([
                'event_type' => $logData['event_type'],
                'email' => $logData['email'],
                'user_id' => $logData['user_id'],
                'ip_address' => $logData['ip_address'],
                'user_agent' => $logData['user_agent'],
                'description' => $logData['description'],
                'uri' => $logData['uri'],
                'created_at' => $logData['timestamp']
            ]);
        } catch (\Exception $e) {
            // Não lança exceção para não interromper o fluxo
            log_message('error', 'Erro ao salvar log de segurança: ' . $e->getMessage());
        }
    }

    /**
     * Cria tabela de logs de segurança se não existir
     * 
     * @param \CodeIgniter\Database\BaseConnection $db
     * @return void
     */
    private function createSecurityLogsTableIfNotExists($db): void
    {
        $tableExists = $db->tableExists('security_logs');
        
        if (!$tableExists) {
            $forge = \Config\Database::forge();
            
            $forge->addField([
                'id' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'event_type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                ],
                'email' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'user_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                ],
                'ip_address' => [
                    'type' => 'VARCHAR',
                    'constraint' => 45,
                ],
                'user_agent' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ],
                'description' => [
                    'type' => 'TEXT',
                ],
                'uri' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                ],
            ]);

            $forge->addKey('id', true);
            $forge->addKey('event_type');
            $forge->addKey('user_id');
            $forge->addKey('ip_address');
            $forge->addKey('created_at');
            
            try {
                $forge->createTable('security_logs', true);
                log_message('info', 'Tabela security_logs criada com sucesso');
            } catch (\Exception $e) {
                log_message('error', 'Erro ao criar tabela security_logs: ' . $e->getMessage());
            }
        }
    }
}

