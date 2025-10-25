<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\Jwt;

/**
 * Controller de autenticação via API
 * Implementa login, refresh token e perfil do usuário autenticado
 * Mantém toda a lógica de permissões e grupos do sistema
 */
class Auth extends BaseController
{
    private $usuarioModel;
    private $grupoUsuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new \App\Models\UsuarioModel();
        $this->grupoUsuarioModel = new \App\Models\GrupoUsuarioModel();
    }

    /**
     * Login via API
     * POST /api/auth/login
     * 
     * @return \CodeIgniter\HTTP\Response JSON response com token e dados do usuário
     */
    public function login()
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

        // Recupera credenciais
        $email = $this->request->getPost('email') ?? $this->request->getJSON()->email ?? null;
        $password = $this->request->getPost('password') ?? $this->request->getJSON()->password ?? null;

        // Valida se as credenciais foram fornecidas
        if (empty($email) || empty($password)) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Email e senha são obrigatórios',
                    'errors' => [
                        'email' => empty($email) ? 'Email é obrigatório' : null,
                        'password' => empty($password) ? 'Senha é obrigatória' : null
                    ]
                ])
                ->setStatusCode(400);
        }

        // Busca o usuário
        $usuario = $this->usuarioModel->buscaUsuarioPorEmail($email);

        if ($usuario === null) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Credenciais inválidas',
                    'errors' => [
                        'credenciais' => 'Email ou senha incorretos'
                    ]
                ])
                ->setStatusCode(401);
        }

        // Verifica a senha
        if ($usuario->verificaPassword($password) === false) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Credenciais inválidas',
                    'errors' => [
                        'credenciais' => 'Email ou senha incorretos'
                    ]
                ])
                ->setStatusCode(401);
        }

        // Verifica se o usuário está ativo
        if ($usuario->ativo == false) {
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
}

