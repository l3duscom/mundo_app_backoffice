<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Usuarios extends BaseController
{
    private $usuarioModel;
    private $extratoPontosModel;
    private $db;
    
    public function __construct()
    {
        $this->usuarioModel = new \App\Models\UsuarioModel();
        $this->extratoPontosModel = new \App\Models\ExtratoPontosModel();
        $this->db = \Config\Database::connect();
    }
    
    /**
     * Retirar pontos de um usuário
     * POST /api/usuarios/retirar-pontos
     * 
     * Body:
     * {
     *   "usuario_id": 123,
     *   "pontos": 100,
     *   "motivo": "Resgate de prêmio",
     *   "event_id": 17
     * }
     */
    public function retirarPontos()
    {
        // Obter usuário autenticado via JWT (definido pelo JwtAuthFilter)
        $usuarioAutenticado = $this->request->usuarioAutenticado ?? null;
        
        if (!$usuarioAutenticado) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        
        // Obter dados do POST
        $json = $this->request->getJSON(true);
        
        // Validar dados obrigatórios
        if (empty($json['usuario_id'])) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'O campo usuario_id é obrigatório'
                ])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }
        
        if (empty($json['pontos']) || $json['pontos'] <= 0) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'O campo pontos é obrigatório e deve ser maior que zero'
                ])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }
        
        if (empty($json['motivo'])) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'O campo motivo é obrigatório'
                ])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }
        
        $usuario_id = (int) $json['usuario_id'];
        $pontos = (int) $json['pontos'];
        $motivo = trim($json['motivo']);
        $event_id = !empty($json['event_id']) ? (int) $json['event_id'] : null;
        $admin_id = $usuarioAutenticado['user_id']; // ID do usuário que está fazendo a operação
        
        // Buscar usuário
        $usuario = $this->usuarioModel->find($usuario_id);
        
        if (!$usuario) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Usuário não encontrado'
                ])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        
        // Verificar se o usuário tem pontos suficientes
        $saldoAtual = (int) $usuario->pontos;
        
        if ($saldoAtual < $pontos) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => "Saldo insuficiente. O usuário possui apenas {$saldoAtual} pontos.",
                    'saldo_atual' => $saldoAtual,
                    'pontos_solicitados' => $pontos
                ])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }
        
        // Iniciar transação
        $this->db->transStart();
        
        try {
            // Calcular novo saldo
            $novoSaldo = $saldoAtual - $pontos;
            
            // Atualizar pontos do usuário
            $updated = $this->usuarioModel->update($usuario_id, [
                'pontos' => $novoSaldo
            ]);
            
            if (!$updated) {
                throw new \Exception('Erro ao atualizar pontos do usuário');
            }
            
            // Criar registro no extrato
            $extratoData = [
                'usuario_id' => $usuario_id,
                'event_id' => $event_id,
                'tipo_transacao' => 'DEBITO',
                'pontos' => $pontos,
                'saldo_anterior' => $saldoAtual,
                'saldo_atual' => $novoSaldo,
                'descricao' => $motivo,
                'admin' => $admin_id,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $extratoId = $this->extratoPontosModel->insert($extratoData);
            
            if (!$extratoId) {
                throw new \Exception('Erro ao criar registro no extrato de pontos');
            }
            
            // Completar transação
            $this->db->transComplete();
            
            if ($this->db->transStatus() === false) {
                throw new \Exception('Erro na transação do banco de dados');
            }
            
            // Log da operação
            log_message('info', sprintf(
                'Pontos retirados: Usuario %d teve %d pontos retirados por admin %d. Saldo: %d -> %d. Motivo: %s',
                $usuario_id,
                $pontos,
                $admin_id,
                $saldoAtual,
                $novoSaldo,
                $motivo
            ));
            
            // Retornar sucesso
            return $this->response
                ->setJSON([
                    'success' => true,
                    'message' => 'Pontos retirados com sucesso',
                    'data' => [
                        'usuario_id' => $usuario_id,
                        'pontos_retirados' => $pontos,
                        'saldo_anterior' => $saldoAtual,
                        'saldo_atual' => $novoSaldo,
                        'extrato_id' => $extratoId,
                        'motivo' => $motivo
                    ]
                ])
                ->setStatusCode(ResponseInterface::HTTP_OK);
                
        } catch (\Exception $e) {
            // Rollback em caso de erro
            $this->db->transRollback();
            
            log_message('error', sprintf(
                'Erro ao retirar pontos: Usuario %d, Pontos %d. Erro: %s',
                $usuario_id,
                $pontos,
                $e->getMessage()
            ));
            
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Erro ao retirar pontos',
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno no servidor'
                ])
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Consultar saldo de pontos de um usuário
     * GET /api/usuarios/saldo/{usuario_id}
     */
    public function consultarSaldo($usuario_id = null)
    {
        // Obter usuário autenticado via JWT (definido pelo JwtAuthFilter)
        $usuarioAutenticado = $this->request->usuarioAutenticado ?? null;
        
        if (!$usuarioAutenticado) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        
        if (empty($usuario_id)) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'ID do usuário é obrigatório'
                ])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }
        
        $usuario = $this->usuarioModel->find($usuario_id);
        
        if (!$usuario) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Usuário não encontrado'
                ])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        
        return $this->response
            ->setJSON([
                'success' => true,
                'data' => [
                    'usuario_id' => $usuario->id,
                    'nome' => $usuario->nome,
                    'email' => $usuario->email,
                    'pontos' => (int) $usuario->pontos
                ]
            ])
            ->setStatusCode(ResponseInterface::HTTP_OK);
    }
}

