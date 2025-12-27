<?php

namespace App\Services;

use App\Models\UsuarioConquistaModel;
use App\Models\ExtratoPontosModel;
use App\Models\ConquistaModel;
use App\Models\UsuarioModel;

class ConquistaService
{
    protected $usuarioConquistaModel;
    protected $extratoPontosModel;
    protected $conquistaModel;
    protected $usuarioModel;
    protected $db;

    public function __construct()
    {
        $this->usuarioConquistaModel = new UsuarioConquistaModel();
        $this->extratoPontosModel = new ExtratoPontosModel();
        $this->conquistaModel = new ConquistaModel();
        $this->usuarioModel = new UsuarioModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * Atribui uma conquista a um usuário
     * 
     * @param int $userId
     * @param int $conquistaId
     * @param int $eventId
     * @param bool $isAdmin Se foi atribuído manualmente por admin
     * @param int|null $atribuidoPor ID do admin que atribuiu
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public function atribuirConquista(
        int $userId, 
        int $conquistaId, 
        int $eventId, 
        bool $isAdmin = false, 
        ?int $atribuidoPor = null
    ): array {
        log_message('info', "Iniciando atribuição de conquista - userId: {$userId}, conquistaId: {$conquistaId}, eventId: {$eventId}, isAdmin: " . ($isAdmin ? 'true' : 'false'));
        
        // Inicia transação
        $this->db->transStart();

        try {
            // 1. Verifica se o usuário existe
            $usuario = $this->usuarioModel->find($userId);
            if (!$usuario) {
                $this->db->transRollback();
                return [
                    'success' => false,
                    'message' => 'Usuário não encontrado',
                ];
            }

            // 2. Verifica se a conquista existe
            $conquista = $this->conquistaModel->find($conquistaId);
            if (!$conquista) {
                $this->db->transRollback();
                return [
                    'success' => false,
                    'message' => 'Conquista não encontrada',
                ];
            }

            // 3. Verifica se conquista está ativa
            if ($conquista->status !== 'ATIVA') {
                $this->db->transRollback();
                return [
                    'success' => false,
                    'message' => 'Conquista não está ativa',
                ];
            }

            // 4. Verifica se usuário já possui a conquista
            if ($this->usuarioConquistaModel->usuarioPossuiConquista($userId, $conquistaId, $eventId)) {
                $this->db->transRollback();
                return [
                    'success' => false,
                    'message' => 'Usuário já possui esta conquista',
                ];
            }

            // 5. Busca saldo anterior do usuário
            $saldoAnterior = (int) ($usuario->pontos ?? 0);
            $pontos = (int) $conquista->pontos;
            $saldoAtual = $saldoAnterior + $pontos;

            // 6. Cria registro de usuário conquista
            $usuarioConquistaData = [
                'conquista_id'  => $conquistaId,
                'event_id'      => $eventId,
                'user_id'       => $userId,
                'pontos'        => $pontos,
                'admin'         => $isAdmin ? 1 : 0,
                'status'        => 'ATIVA',
                'atribuido_por' => $atribuidoPor,
            ];

            if (!$this->usuarioConquistaModel->save($usuarioConquistaData)) {
                $this->db->transRollback();
                $errors = $this->usuarioConquistaModel->errors();
                log_message('error', 'Erro ao salvar usuario_conquista: ' . json_encode($errors));
                log_message('error', 'Dados enviados: ' . json_encode($usuarioConquistaData));
                return [
                    'success' => false,
                    'message' => 'Erro ao atribuir conquista',
                    'errors' => $errors,
                ];
            }

            $usuarioConquistaId = $this->usuarioConquistaModel->getInsertID();

            // 7. Atualiza pontos do usuário
            $this->usuarioModel->update($userId, ['pontos' => $saldoAtual]);

            // 8. Cria entrada no extrato
            $extratoData = [
                'user_id'         => $userId,
                'event_id'        => $eventId,
                'tipo'            => 'CONQUISTA',
                'pontos'          => $pontos,
                'saldo_anterior'  => $saldoAnterior,
                'saldo_atual'     => $saldoAtual,
                'descricao'       => "Conquista: {$conquista->nome_conquista}",
                'referencia_tipo' => 'usuario_conquista',
                'referencia_id'   => $usuarioConquistaId,
                'atribuido_por'   => $atribuidoPor,
            ];

            if (!$this->extratoPontosModel->save($extratoData)) {
                $this->db->transRollback();
                $errors = $this->extratoPontosModel->errors();
                log_message('error', 'Erro ao salvar extrato_pontos: ' . json_encode($errors));
                log_message('error', 'Dados do extrato: ' . json_encode($extratoData));
                return [
                    'success' => false,
                    'message' => 'Erro ao criar extrato de pontos',
                    'errors' => $errors,
                ];
            }

            // Completa transação
            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return [
                    'success' => false,
                    'message' => 'Erro ao processar transação',
                ];
            }

            // Busca dados completos da conquista atribuída
            $conquistaAtribuida = $this->usuarioConquistaModel->find($usuarioConquistaId);

            // 9. Envia email de notificação ao usuário
            $this->enviarEmailConquista($usuario, $conquista, $pontos);

            return [
                'success' => true,
                'message' => 'Conquista atribuída com sucesso',
                'data' => [
                    'id' => $conquistaAtribuida->id,
                    'conquista_id' => $conquistaAtribuida->conquista_id,
                    'conquista_nome' => $conquista->nome_conquista,
                    'event_id' => $conquistaAtribuida->event_id,
                    'user_id' => $conquistaAtribuida->user_id,
                    'pontos' => $conquistaAtribuida->pontos,
                    'saldo_anterior' => $saldoAnterior,
                    'saldo_atual' => $saldoAtual,
                    'admin' => $conquistaAtribuida->admin,
                    'status' => $conquistaAtribuida->status,
                    'created_at' => $conquistaAtribuida->created_at,
                ],
            ];

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Exceção ao atribuir conquista: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            log_message('error', 'Dados: userId=' . $userId . ', conquistaId=' . $conquistaId . ', eventId=' . $eventId);
            
            return [
                'success' => false,
                'message' => 'Erro ao atribuir conquista',
                'error' => $e->getMessage(), // Sempre retorna a mensagem em caso de exceção
            ];
        }
    }

    /**
     * Revoga uma conquista de um usuário
     * 
     * @param int $usuarioConquistaId
     * @param int $atribuidoPor ID do admin que revogou
     * @param string|null $motivo
     * @return array
     */
    public function revogarConquista(int $usuarioConquistaId, int $atribuidoPor, ?string $motivo = null): array
    {
        // Inicia transação
        $this->db->transStart();

        try {
            // 1. Busca a conquista do usuário
            $usuarioConquista = $this->usuarioConquistaModel->find($usuarioConquistaId);
            if (!$usuarioConquista) {
                $this->db->transRollback();
                return [
                    'success' => false,
                    'message' => 'Conquista do usuário não encontrada',
                ];
            }

            // 2. Verifica se já está revogada
            if ($usuarioConquista->status === 'REVOGADA') {
                $this->db->transRollback();
                return [
                    'success' => false,
                    'message' => 'Conquista já está revogada',
                ];
            }

            // 3. Busca usuário
            $usuario = $this->usuarioModel->find($usuarioConquista->user_id);
            if (!$usuario) {
                $this->db->transRollback();
                return [
                    'success' => false,
                    'message' => 'Usuário não encontrado',
                ];
            }

            $saldoAnterior = (int) ($usuario->pontos ?? 0);
            $pontos = -((int) $usuarioConquista->pontos); // Negativo para revogar
            $saldoAtual = $saldoAnterior + $pontos;

            // Não permite saldo negativo
            if ($saldoAtual < 0) {
                $saldoAtual = 0;
            }

            // 4. Atualiza status da conquista
            $this->usuarioConquistaModel->update($usuarioConquistaId, ['status' => 'REVOGADA']);

            // 5. Atualiza pontos do usuário
            $this->usuarioModel->update($usuarioConquista->user_id, ['pontos' => $saldoAtual]);

            // 6. Cria entrada no extrato
            $conquista = $this->conquistaModel->find($usuarioConquista->conquista_id);
            $descricao = "Revogação: {$conquista->nome_conquista}";
            if ($motivo) {
                $descricao .= " - Motivo: {$motivo}";
            }

            $extratoData = [
                'user_id'         => $usuarioConquista->user_id,
                'event_id'        => $usuarioConquista->event_id,
                'tipo'            => 'REVOGACAO',
                'pontos'          => $pontos,
                'saldo_anterior'  => $saldoAnterior,
                'saldo_atual'     => $saldoAtual,
                'descricao'       => $descricao,
                'referencia_tipo' => 'usuario_conquista',
                'referencia_id'   => $usuarioConquistaId,
                'atribuido_por'   => $atribuidoPor,
            ];

            if (!$this->extratoPontosModel->save($extratoData)) {
                $this->db->transRollback();
                return [
                    'success' => false,
                    'message' => 'Erro ao criar extrato de pontos',
                    'errors' => $this->extratoPontosModel->errors(),
                ];
            }

            // Completa transação
            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return [
                    'success' => false,
                    'message' => 'Erro ao processar transação',
                ];
            }

            return [
                'success' => true,
                'message' => 'Conquista revogada com sucesso',
                'data' => [
                    'id' => $usuarioConquistaId,
                    'pontos_removidos' => abs($pontos),
                    'saldo_anterior' => $saldoAnterior,
                    'saldo_atual' => $saldoAtual,
                ],
            ];

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Erro ao revogar conquista: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Erro ao revogar conquista',
                'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Erro interno',
            ];
        }
    }

    /**
     * Envia email de notificação de conquista ao usuário
     * 
     * @param object $usuario
     * @param object $conquista
     * @param int $pontos
     * @return void
     */
    private function enviarEmailConquista($usuario, $conquista, int $pontos): void
    {
        try {
            $resendService = new ResendService();
            
            $nivelEmoji = [
                'BRONZE' => '🥉',
                'PRATA' => '🥈',
                'OURO' => '🥇',
                'PLATINA' => '💎',
                'DIAMANTE' => '💎',
            ];
            
            $emoji = $nivelEmoji[$conquista->nivel] ?? '🏆';
            
            $html = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                <div style='text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 15px 15px 0 0;'>
                    <h1 style='color: white; margin: 0; font-size: 28px;'>{$emoji} Nova Conquista!</h1>
                </div>
                
                <div style='background: #f8f9fa; padding: 30px; border-radius: 0 0 15px 15px;'>
                    <p style='font-size: 16px; color: #333;'>Olá <strong>" . esc($usuario->nome) . "</strong>,</p>
                    
                    <p style='font-size: 16px; color: #333;'>Parabéns! Você conquistou uma nova medalha:</p>
                    
                    <div style='background: white; border-radius: 10px; padding: 20px; text-align: center; margin: 20px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                        <h2 style='color: #764ba2; margin: 0 0 10px 0;'>" . esc($conquista->nome_conquista) . "</h2>
                        <p style='color: #666; margin: 0 0 15px 0;'>" . esc($conquista->descricao ?? '') . "</p>
                        <div style='display: inline-block; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding: 10px 25px; border-radius: 25px; font-size: 18px; font-weight: bold;'>
                            +{$pontos} pontos
                        </div>
                    </div>
                    
                    <p style='font-size: 14px; color: #666; text-align: center;'>Continue participando dos nossos eventos para desbloquear mais conquistas!</p>
                </div>
                
                <div style='text-align: center; padding: 20px; color: #999; font-size: 12px;'>
                    <p>Mundo Dream - Conquistas e Recompensas</p>
                </div>
            </div>
            ";
            
            $resendService->enviarEmail(
                $usuario->email,
                "{$emoji} Você conquistou: {$conquista->nome_conquista}!",
                $html
            );
            
            log_message('info', "Email de conquista enviado para {$usuario->email} - Conquista: {$conquista->nome_conquista}");
            
        } catch (\Exception $e) {
            // Não falha a atribuição se o email der erro, apenas loga
            log_message('error', 'Erro ao enviar email de conquista: ' . $e->getMessage());
        }
    }
}

