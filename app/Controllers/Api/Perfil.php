<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

/**
 * Controller API de Perfil
 * Gestão completa do perfil do usuário autenticado via JWT.
 * Quando o usuário não tiver cliente vinculado, usa o expositor.
 */
class Perfil extends BaseController
{
    private $usuarioModel;
    private $clienteModel;
    private $expositorModel;

    private const CAMPOS_CLIENTE = [
        'nome',
        'cpf',
        'telefone',
        'email',
        'cep',
        'endereco',
        'numero',
        'bairro',
        'cidade',
        'estado',
    ];

    private const CAMPOS_EXPOSITOR = [
        'nome',
        'nome_fantasia',
        'documento',
        'telefone',
        'celular',
        'email',
        'cep',
        'endereco',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
    ];

    public function __construct()
    {
        $this->usuarioModel   = new \App\Models\UsuarioModel();
        $this->clienteModel   = new \App\Models\ClienteModel();
        $this->expositorModel = new \App\Models\ExpositorModel();
    }

    /**
     * Retorna os dados do perfil do usuário autenticado
     * GET /api/perfil
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function index()
    {
        $userId = $this->getUsuarioId();
        if ($userId === null) {
            return $this->unauthorized();
        }

        try {
            $usuario = $this->usuarioModel->find($userId);
            if (!$usuario) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Usuário não encontrado'
                    ])
                    ->setStatusCode(404);
            }

            $cliente   = $this->clienteModel->where('usuario_id', $userId)->first();
            $expositor = $cliente ? null : $this->expositorModel->where('usuario_id', $userId)->first();

            return $this->response
                ->setJSON([
                    'success' => true,
                    'data' => $this->montaPerfil($usuario, $cliente, $expositor),
                ])
                ->setStatusCode(200);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao buscar perfil API: ' . $e->getMessage());
            return $this->erroInterno('Erro ao buscar perfil', $e);
        }
    }

    /**
     * Atualiza os dados do perfil (nome, email e dados do cliente OU expositor)
     * PUT   /api/perfil
     * PATCH /api/perfil
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function update()
    {
        if (!in_array($this->request->getMethod(), ['put', 'patch'])) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Método não permitido'
                ])
                ->setStatusCode(405);
        }

        $userId = $this->getUsuarioId();
        if ($userId === null) {
            return $this->unauthorized();
        }

        $json = $this->request->getJSON(true);
        if (!is_array($json) || empty($json)) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Dados não fornecidos ou JSON inválido'
                ])
                ->setStatusCode(400);
        }

        try {
            $usuario = $this->usuarioModel->find($userId);
            if (!$usuario) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Usuário não encontrado'
                    ])
                    ->setStatusCode(404);
            }

            $cliente   = $this->clienteModel->where('usuario_id', $userId)->first();
            $expositor = $cliente ? null : $this->expositorModel->where('usuario_id', $userId)->first();

            // Atualiza cliente (quando existir)
            if ($cliente) {
                $dadosCliente = array_intersect_key($json, array_flip(self::CAMPOS_CLIENTE));
                if (!empty($dadosCliente)) {
                    $cliente->fill($dadosCliente);
                    if ($cliente->hasChanged() && !$this->clienteModel->save($cliente)) {
                        return $this->response
                            ->setJSON([
                                'success' => false,
                                'message' => 'Erro ao atualizar dados do cliente',
                                'errors'  => $this->clienteModel->errors(),
                            ])
                            ->setStatusCode(422);
                    }
                }
            } elseif ($expositor) {
                // Mapeia "cpf" → "documento" para compatibilidade com o app
                if (isset($json['cpf']) && !isset($json['documento'])) {
                    $json['documento'] = $json['cpf'];
                }

                $dadosExpositor = array_intersect_key($json, array_flip(self::CAMPOS_EXPOSITOR));
                if (!empty($dadosExpositor)) {
                    $expositor->fill($dadosExpositor);
                    if ($expositor->hasChanged() && !$this->expositorModel->save($expositor)) {
                        return $this->response
                            ->setJSON([
                                'success' => false,
                                'message' => 'Erro ao atualizar dados do expositor',
                                'errors'  => $this->expositorModel->errors(),
                            ])
                            ->setStatusCode(422);
                    }
                }
            }

            // Atualiza usuário (nome e email)
            if (isset($json['nome']) && $json['nome'] !== $usuario->nome) {
                $usuario->nome = $json['nome'];
            }
            if (isset($json['email']) && $json['email'] !== $usuario->email) {
                $usuario->email = $json['email'];
            }

            if ($usuario->hasChanged()) {
                if (!$this->usuarioModel->save($usuario)) {
                    return $this->response
                        ->setJSON([
                            'success' => false,
                            'message' => 'Erro ao atualizar usuário',
                            'errors'  => $this->usuarioModel->errors(),
                        ])
                        ->setStatusCode(422);
                }
            }

            // Recarrega dados atualizados
            $usuario   = $this->usuarioModel->find($userId);
            $cliente   = $this->clienteModel->where('usuario_id', $userId)->first();
            $expositor = $cliente ? null : $this->expositorModel->where('usuario_id', $userId)->first();

            return $this->response
                ->setJSON([
                    'success' => true,
                    'message' => 'Perfil atualizado com sucesso',
                    'data'    => $this->montaPerfil($usuario, $cliente, $expositor),
                ])
                ->setStatusCode(200);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao atualizar perfil API: ' . $e->getMessage());
            return $this->erroInterno('Erro ao atualizar perfil', $e);
        }
    }

    /**
     * Altera a senha do usuário autenticado
     * POST /api/perfil/senha
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function senha()
    {
        if ($this->request->getMethod() !== 'post') {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Método não permitido'
                ])
                ->setStatusCode(405);
        }

        $userId = $this->getUsuarioId();
        if ($userId === null) {
            return $this->unauthorized();
        }

        $json                  = $this->request->getJSON(true);
        $senhaAtual            = $json['senha_atual'] ?? null;
        $novaSenha             = $json['nova_senha'] ?? null;
        $novaSenhaConfirmacao  = $json['nova_senha_confirmacao'] ?? null;

        if (empty($senhaAtual) || empty($novaSenha) || empty($novaSenhaConfirmacao)) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Campos senha_atual, nova_senha e nova_senha_confirmacao são obrigatórios'
                ])
                ->setStatusCode(400);
        }

        if ($novaSenha !== $novaSenhaConfirmacao) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'A confirmação da nova senha não confere'
                ])
                ->setStatusCode(422);
        }

        try {
            $usuario = $this->usuarioModel->find($userId);
            if (!$usuario) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Usuário não encontrado'
                    ])
                    ->setStatusCode(404);
            }

            if (!$usuario->verificaPassword($senhaAtual)) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Senha atual incorreta'
                    ])
                    ->setStatusCode(401);
            }

            $usuario->password              = $novaSenha;
            $usuario->password_confirmation = $novaSenhaConfirmacao;

            if (!$this->usuarioModel->save($usuario)) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Erro ao alterar senha',
                        'errors'  => $this->usuarioModel->errors(),
                    ])
                    ->setStatusCode(422);
            }

            return $this->response
                ->setJSON([
                    'success' => true,
                    'message' => 'Senha alterada com sucesso',
                ])
                ->setStatusCode(200);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao alterar senha API: ' . $e->getMessage());
            return $this->erroInterno('Erro ao alterar senha', $e);
        }
    }

    /**
     * Faz upload da imagem de perfil
     * POST /api/perfil/imagem (multipart/form-data, campo "imagem")
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function imagem()
    {
        if ($this->request->getMethod() !== 'post') {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Método não permitido'
                ])
                ->setStatusCode(405);
        }

        $userId = $this->getUsuarioId();
        if ($userId === null) {
            return $this->unauthorized();
        }

        try {
            $usuario = $this->usuarioModel->find($userId);
            if (!$usuario) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Usuário não encontrado'
                    ])
                    ->setStatusCode(404);
            }

            $arquivo = $this->request->getFile('imagem');
            if (!$arquivo || !$arquivo->isValid()) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Arquivo de imagem inválido ou não enviado'
                    ])
                    ->setStatusCode(400);
            }

            if (!in_array($arquivo->getMimeType(), ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'Tipo de imagem não suportado (use jpg, png, webp ou gif)'
                    ])
                    ->setStatusCode(422);
            }

            $dimensoes = @getimagesize($arquivo->getPathName());
            if (!$dimensoes || $dimensoes[0] < 300 || $dimensoes[1] < 300) {
                return $this->response
                    ->setJSON([
                        'success' => false,
                        'message' => 'A imagem precisa ter no mínimo 300x300 pixels'
                    ])
                    ->setStatusCode(422);
            }

            $caminhoRelativo = $arquivo->store('usuarios');
            $caminhoCompleto = WRITEPATH . "uploads/$caminhoRelativo";
            $nomeArquivo     = basename($caminhoRelativo);

            try {
                service('image')
                    ->withFile($caminhoCompleto)
                    ->fit(300, 300, 'center')
                    ->save($caminhoCompleto);
            } catch (\Throwable $imgEx) {
                log_message('warning', 'Falha ao redimensionar imagem de perfil API: ' . $imgEx->getMessage());
            }

            $imagemAntiga    = $usuario->imagem;
            $usuario->imagem = $nomeArquivo;
            $this->usuarioModel->save($usuario);

            if (!empty($imagemAntiga) && $imagemAntiga !== $nomeArquivo) {
                $antigaPath = WRITEPATH . "uploads/usuarios/$imagemAntiga";
                if (is_file($antigaPath)) {
                    @unlink($antigaPath);
                }
            }

            return $this->response
                ->setJSON([
                    'success' => true,
                    'message' => 'Imagem atualizada com sucesso',
                    'data'    => [
                        'imagem'     => $usuario->imagem,
                        'imagem_url' => site_url('usuarios/imagem/' . $usuario->imagem),
                    ],
                ])
                ->setStatusCode(200);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao fazer upload da imagem de perfil API: ' . $e->getMessage());
            return $this->erroInterno('Erro ao fazer upload da imagem', $e);
        }
    }

    // ----------------- helpers -----------------

    private function getUsuarioId(): ?int
    {
        $payload = $this->request->usuarioAutenticado ?? null;
        return isset($payload['user_id']) ? (int) $payload['user_id'] : null;
    }

    private function unauthorized()
    {
        return $this->response
            ->setJSON([
                'success' => false,
                'message' => 'Usuário não autenticado'
            ])
            ->setStatusCode(401);
    }

    private function erroInterno(string $msg, \Exception $e)
    {
        return $this->response
            ->setJSON([
                'success' => false,
                'message' => $msg,
                'error'   => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno'
            ])
            ->setStatusCode(500);
    }

    private function montaPerfil($usuario, $cliente, $expositor = null): array
    {
        // O bloco "cliente" mantém a chave do payload original e usa expositor como fonte
        // quando o usuário não tem cliente — assim o app não precisa mudar.
        $clientePayload = null;

        if ($cliente) {
            $clientePayload = [
                'id'           => (int) $cliente->id,
                'nome'         => $cliente->nome ?? null,
                'cpf'          => $cliente->cpf ?? null,
                'telefone'     => $cliente->telefone ?? null,
                'email'        => $cliente->email ?? null,
                'cep'          => $cliente->cep ?? null,
                'endereco'     => $cliente->endereco ?? null,
                'numero'       => $cliente->numero ?? null,
                'bairro'       => $cliente->bairro ?? null,
                'cidade'       => $cliente->cidade ?? null,
                'estado'       => $cliente->estado ?? null,
                'is_expositor' => false,
            ];
        } elseif ($expositor) {
            $clientePayload = [
                'id'           => (int) $expositor->id,
                'nome'         => $expositor->nome_fantasia ?? $expositor->nome ?? null,
                'cpf'          => $expositor->documento ?? null,
                'telefone'     => $expositor->telefone ?? null,
                'email'        => $expositor->email ?? null,
                'cep'          => $expositor->cep ?? null,
                'endereco'     => $expositor->endereco ?? null,
                'numero'       => $expositor->numero ?? null,
                'bairro'       => $expositor->bairro ?? null,
                'cidade'       => $expositor->cidade ?? null,
                'estado'       => $expositor->estado ?? null,
                'is_expositor' => true,
            ];
        }

        return [
            'usuario' => [
                'id'         => (int) $usuario->id,
                'nome'       => $usuario->nome,
                'email'      => $usuario->email,
                'codigo'     => $usuario->codigo ?? null,
                'imagem'     => $usuario->imagem ?? null,
                'imagem_url' => !empty($usuario->imagem)
                    ? site_url('usuarios/imagem/' . $usuario->imagem)
                    : null,
                'created_at' => $usuario->created_at,
                'updated_at' => $usuario->updated_at,
            ],
            'cliente' => $clientePayload,
        ];
    }
}
