<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\Jwt;

/**
 * Filtro de autenticação JWT
 * Valida tokens JWT nas requisições da API
 * Verifica permissões e grupos do usuário
 */
class JwtAuthFilter implements FilterInterface
{
    /**
     * Verifica se o token JWT é válido antes de permitir acesso à rota
     *
     * @param RequestInterface $request
     * @param array|null $arguments Pode conter permissões requeridas
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Recupera o token do header Authorization
        $authHeader = $request->getServer('HTTP_AUTHORIZATION');
        $token = Jwt::extractFromHeader($authHeader);

        // Verifica se o token foi fornecido
        if (empty($token)) {
            return service('response')
                ->setJSON([
                    'success' => false,
                    'message' => 'Token de autenticação não fornecido',
                    'error' => 'Use o header: Authorization: Bearer YOUR_JWT_TOKEN'
                ])
                ->setStatusCode(401);
        }

        // Decodifica e valida o token
        $payload = Jwt::decode($token);

        if ($payload === null) {
            return service('response')
                ->setJSON([
                    'success' => false,
                    'message' => 'Token inválido ou expirado',
                    'error' => 'Faça login novamente para obter um novo token'
                ])
                ->setStatusCode(401);
        }

        // Verifica se o payload contém o user_id
        if (!isset($payload['user_id'])) {
            return service('response')
                ->setJSON([
                    'success' => false,
                    'message' => 'Token inválido',
                    'error' => 'Formato de token inválido'
                ])
                ->setStatusCode(401);
        }

        // Valida se o usuário ainda existe e está ativo
        $usuarioModel = new \App\Models\UsuarioModel();
        $usuario = $usuarioModel->find($payload['user_id']);

        if ($usuario === null || $usuario->ativo == false) {
            return service('response')
                ->setJSON([
                    'success' => false,
                    'message' => 'Usuário não encontrado ou inativo',
                    'error' => 'Seu acesso foi revogado ou sua conta está inativa'
                ])
                ->setStatusCode(401);
        }

        // Armazena os dados do usuário autenticado no request
        // Isso permite que os controllers acessem os dados do usuário autenticado
        $request->usuarioAutenticado = $payload;

        // Verifica permissões específicas se fornecidas nos argumentos
        if (!empty($arguments)) {
            $permissaoRequerida = $arguments[0] ?? null;

            if ($permissaoRequerida !== null) {
                // Admin tem acesso a tudo
                if ($payload['is_admin'] ?? false) {
                    return $request;
                }

                // Verifica se o usuário tem a permissão requerida
                $permissoes = $payload['permissoes'] ?? [];
                
                if (!in_array($permissaoRequerida, $permissoes)) {
                    return service('response')
                        ->setJSON([
                            'success' => false,
                            'message' => 'Acesso negado',
                            'error' => 'Você não tem permissão para acessar este recurso'
                        ])
                        ->setStatusCode(403);
                }
            }
        }

        return $request;
    }

    /**
     * Executado após a requisição
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array|null $arguments
     * @return ResponseInterface
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Não precisa fazer nada no after
        return $response;
    }
}

