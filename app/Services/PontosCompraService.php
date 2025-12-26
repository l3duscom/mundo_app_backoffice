<?php

namespace App\Services;

use App\Models\ExtratoPontosModel;
use App\Models\UsuarioModel;
use App\Models\PedidoModel;
use App\Models\IngressoModel;
use App\Models\TicketModel;

/**
 * Serviço para atribuir pontos por compra de ingresso
 * 
 * Regras de cálculo:
 * - A cada R$ 2,00 gastos = 1 ponto (arredondado para baixo)
 * - Bonificação por lote inicial: 7% de redução por lote adicional
 * - Lote 1 = 100%, Lote 2 = 93%, Lote 3 = 86%, ..., Lote 8+ = 50% (mínimo)
 */
class PontosCompraService
{
    protected $extratoPontosModel;
    protected $usuarioModel;
    protected $pedidoModel;
    protected $ingressoModel;
    protected $ticketModel;
    protected $db;

    // Constantes de configuração
    const VALOR_POR_PONTO = 2.0;  // R$ 2,00 = 1 ponto
    const REDUCAO_POR_LOTE = 0.07; // 7% de redução por lote
    const REDUCAO_MAXIMA = 0.50;   // Máximo 50% de redução

    public function __construct()
    {
        $this->extratoPontosModel = new ExtratoPontosModel();
        $this->usuarioModel = new UsuarioModel();
        $this->pedidoModel = new PedidoModel();
        $this->ingressoModel = new IngressoModel();
        $this->ticketModel = new TicketModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * Atribui pontos pela compra de ingresso
     * 
     * @param int $userId ID do usuário
     * @param int $eventId ID do evento
     * @param int $pedidoId ID do pedido
     * @param float $valorTotal Valor total pago
     * @param int|null $lote Número do lote (se null, busca do pedido)
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public function atribuirPontosPorCompra(
        int $userId, 
        int $eventId, 
        int $pedidoId, 
        float $valorTotal, 
        ?int $lote = null
    ): array {
        log_message('info', "PontosCompra: Iniciando - userId: {$userId}, pedidoId: {$pedidoId}, valor: {$valorTotal}");

        // Inicia transação
        $this->db->transStart();

        try {
            // Se o valor for zero ou negativo, não atribui pontos
            if ($valorTotal <= 0) {
                return [
                    'success' => true,
                    'message' => 'Nenhum ponto atribuído (valor zero)',
                    'data' => ['pontos' => 0]
                ];
            }

            // Verifica se o usuário existe
            $usuario = $this->usuarioModel->find($userId);
            if (!$usuario) {
                $this->db->transRollback();
                return [
                    'success' => false,
                    'message' => 'Usuário não encontrado',
                ];
            }

            // Verifica se já existe pontos para esse pedido (evita duplicata)
            $existente = $this->extratoPontosModel
                ->where('referencia_tipo', 'pedido')
                ->where('referencia_id', $pedidoId)
                ->where('tipo', 'COMPRA')
                ->first();
            
            if ($existente) {
                $this->db->transRollback();
                return [
                    'success' => false,
                    'message' => 'Pontos já atribuídos para este pedido',
                    'data' => ['pontos' => $existente->pontos]
                ];
            }

            // Se lote não foi informado, busca o lote do primeiro ingresso do pedido
            if ($lote === null) {
                $lote = $this->buscarLoteDoPedido($pedidoId);
            }

            // Calcula os pontos
            $pontos = $this->calcularPontos($valorTotal, $lote);

            // Se não há pontos a atribuir
            if ($pontos <= 0) {
                $this->db->transComplete();
                return [
                    'success' => true,
                    'message' => 'Nenhum ponto atribuído (cálculo resultou em zero)',
                    'data' => ['pontos' => 0]
                ];
            }

            // Busca saldo anterior
            $saldoAnterior = (int) ($usuario->pontos ?? 0);
            $saldoAtual = $saldoAnterior + $pontos;

            // Atualiza pontos do usuário
            $this->usuarioModel->update($userId, ['pontos' => $saldoAtual]);

            // Cria entrada no extrato
            $multiplicador = $this->calcularMultiplicadorLote($lote);
            $descricao = "Compra de ingresso (Lote {$lote}, {$this->formatarMultiplicador($multiplicador)})";

            $extratoData = [
                'user_id'         => $userId,
                'event_id'        => $eventId,
                'tipo'            => 'COMPRA',
                'pontos'          => $pontos,
                'saldo_anterior'  => $saldoAnterior,
                'saldo_atual'     => $saldoAtual,
                'descricao'       => $descricao,
                'referencia_tipo' => 'pedido',
                'referencia_id'   => $pedidoId,
                'atribuido_por'   => null,
            ];

            if (!$this->extratoPontosModel->save($extratoData)) {
                $this->db->transRollback();
                $errors = $this->extratoPontosModel->errors();
                log_message('error', 'PontosCompra: Erro ao salvar extrato: ' . json_encode($errors));
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

            log_message('info', "PontosCompra: Sucesso - userId: {$userId}, pontos: {$pontos}, lote: {$lote}");

            return [
                'success' => true,
                'message' => 'Pontos atribuídos com sucesso',
                'data' => [
                    'pontos' => $pontos,
                    'saldo_anterior' => $saldoAnterior,
                    'saldo_atual' => $saldoAtual,
                    'lote' => $lote,
                    'multiplicador' => $multiplicador,
                    'valor_base' => $valorTotal,
                ],
            ];

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'PontosCompra: Exceção - ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Erro ao atribuir pontos',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Atribui pontos a partir do ID do pedido (busca os dados automaticamente)
     * 
     * @param int $pedidoId ID do pedido
     * @return array
     */
    public function atribuirPontosDoPedido(int $pedidoId): array
    {
        $pedido = $this->pedidoModel->find($pedidoId);
        
        if (!$pedido) {
            return [
                'success' => false,
                'message' => 'Pedido não encontrado',
            ];
        }

        return $this->atribuirPontosPorCompra(
            (int) $pedido->user_id,
            (int) $pedido->evento_id,
            $pedidoId,
            (float) $pedido->total,
            null // Busca lote automaticamente
        );
    }

    /**
     * Calcula o multiplicador baseado no lote
     * 
     * Lote 1 = 1.0 (100%)
     * Lote 2 = 0.93 (93%)
     * Lote 3 = 0.86 (86%)
     * ...
     * Lote 8+ = 0.50 (50% - mínimo)
     * 
     * @param int $lote Número do lote (1-8+)
     * @return float Multiplicador (0.50 a 1.00)
     */
    public function calcularMultiplicadorLote(int $lote): float
    {
        // Garante lote mínimo de 1
        $lote = max(1, $lote);
        
        // Calcula redução: (lote - 1) * 7%
        $reducao = ($lote - 1) * self::REDUCAO_POR_LOTE;
        
        // Limita redução ao máximo de 50%
        $reducao = min($reducao, self::REDUCAO_MAXIMA);
        
        // Multiplicador = 1 - redução
        return round(1 - $reducao, 2);
    }

    /**
     * Calcula pontos finais
     * 
     * Fórmula: floor(valor / VALOR_POR_PONTO) * multiplicador_lote
     * 
     * @param float $valor Valor em reais
     * @param int $lote Número do lote
     * @return int Pontos calculados (arredondado para baixo)
     */
    public function calcularPontos(float $valor, int $lote = 1): int
    {
        // Pontos base: valor / 2
        $pontosBase = $valor / self::VALOR_POR_PONTO;
        
        // Aplica multiplicador do lote
        $multiplicador = $this->calcularMultiplicadorLote($lote);
        $pontosFinal = $pontosBase * $multiplicador;
        
        // Arredonda para baixo
        return (int) floor($pontosFinal);
    }

    /**
     * Busca o lote do pedido baseado nos ingressos
     * 
     * @param int $pedidoId
     * @return int Lote (default 1 se não encontrar)
     */
    protected function buscarLoteDoPedido(int $pedidoId): int
    {
        // Busca o primeiro ingresso do pedido
        $ingresso = $this->ingressoModel
            ->where('pedido_id', $pedidoId)
            ->first();

        if (!$ingresso || !$ingresso->ticket_id) {
            return 1; // Default lote 1
        }

        // Busca o ticket para obter o lote
        $ticket = $this->ticketModel->find($ingresso->ticket_id);

        if (!$ticket || !$ticket->lote) {
            return 1; // Default lote 1
        }

        return (int) $ticket->lote;
    }

    /**
     * Formata o multiplicador para exibição
     * 
     * @param float $multiplicador
     * @return string Ex: "100%", "93%"
     */
    protected function formatarMultiplicador(float $multiplicador): string
    {
        return round($multiplicador * 100) . '%';
    }
}
