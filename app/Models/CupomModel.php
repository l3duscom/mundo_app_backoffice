<?php

namespace App\Models;

use CodeIgniter\Model;

class CupomModel extends Model
{
    protected $table = 'cupons';
    protected $returnType = 'App\Entities\Cupom';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'evento_id',
        'nome',
        'codigo',
        'desconto',
        'tipo',
        'valor_minimo',
        'quantidade_total',
        'quantidade_usada',
        'uso_por_usuario',
        'data_inicio',
        'data_fim',
        'ativo',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'nome' => 'required|min_length[3]',
        'codigo' => 'required|min_length[3]|alpha_numeric_punct',
        'desconto' => 'required|numeric|greater_than[0]',
    ];

    protected $validationMessages = [
        'codigo' => [
            'alpha_numeric_punct' => 'O código deve conter apenas letras, números e pontuação.',
        ],
    ];

    /**
     * Busca cupom pelo código
     */
    public function buscaPorCodigo(string $codigo)
    {
        return $this->where('codigo', strtoupper($codigo))->first();
    }

    /**
     * Valida se um cupom pode ser usado
     * @param string $codigo Código do cupom
     * @param int|null $eventId ID do evento
     * @param int|null $userId ID do usuário
     * @param float $valorPedido Valor do pedido
     * @return array ['valido' => bool, 'erro' => string|null, 'cupom' => Cupom|null]
     */
    public function validarCupom(string $codigo, ?int $eventId = null, ?int $userId = null, float $valorPedido = 0): array
    {
        $cupom = $this->buscaPorCodigo($codigo);

        if (!$cupom) {
            return ['valido' => false, 'erro' => 'Cupom não encontrado.', 'cupom' => null];
        }

        // Verifica se está ativo
        if (!$cupom->ativo) {
            return ['valido' => false, 'erro' => 'Este cupom está desativado.', 'cupom' => null];
        }

        // Verifica evento (se cupom é específico para um evento)
        if ($cupom->evento_id && $eventId && $cupom->evento_id != $eventId) {
            return ['valido' => false, 'erro' => 'Este cupom não é válido para este evento.', 'cupom' => null];
        }

        // Verifica data de início
        if ($cupom->data_inicio && date('Y-m-d') < $cupom->data_inicio) {
            return ['valido' => false, 'erro' => 'Este cupom ainda não está válido.', 'cupom' => null];
        }

        // Verifica data de fim
        if ($cupom->data_fim && date('Y-m-d') > $cupom->data_fim) {
            return ['valido' => false, 'erro' => 'Este cupom expirou.', 'cupom' => null];
        }

        // Verifica quantidade disponível
        if ($cupom->quantidade_total !== null && $cupom->quantidade_usada >= $cupom->quantidade_total) {
            return ['valido' => false, 'erro' => 'Este cupom já atingiu o limite de uso.', 'cupom' => null];
        }

        // Verifica valor mínimo
        if ($cupom->valor_minimo > 0 && $valorPedido < $cupom->valor_minimo) {
            return [
                'valido' => false,
                'erro' => 'Valor mínimo do pedido para usar este cupom: R$ ' . number_format($cupom->valor_minimo, 2, ',', '.'),
                'cupom' => null
            ];
        }

        // Verifica uso por usuário
        if ($userId && $cupom->uso_por_usuario > 0) {
            $pedidoModel = new PedidoModel();
            $usosUsuario = $pedidoModel
                ->where('cupom_id', $cupom->id)
                ->where('user_id', $userId)
                ->whereIn('status', ['CONFIRMED', 'RECEIVED', 'PAID', 'RECEIVED_IN_CASH'])
                ->countAllResults();

            if ($usosUsuario >= $cupom->uso_por_usuario) {
                return ['valido' => false, 'erro' => 'Você já utilizou este cupom o número máximo de vezes.', 'cupom' => null];
            }
        }

        return ['valido' => true, 'erro' => null, 'cupom' => $cupom];
    }

    /**
     * Calcula o valor do desconto
     */
    public function calcularDesconto($cupom, float $valorPedido): float
    {
        if ($cupom->tipo === 'fixo') {
            return min($cupom->desconto, $valorPedido); // Não pode ser maior que o pedido
        }

        // Percentual
        return round(($valorPedido * $cupom->desconto) / 100, 2);
    }

    /**
     * Incrementa o uso do cupom
     */
    public function incrementarUso(int $cupomId): bool
    {
        return $this->set('quantidade_usada', 'quantidade_usada + 1', false)
            ->where('id', $cupomId)
            ->update();
    }

    /**
     * Decrementa o uso do cupom (cancelamento)
     */
    public function decrementarUso(int $cupomId): bool
    {
        return $this->set('quantidade_usada', 'GREATEST(quantidade_usada - 1, 0)', false)
            ->where('id', $cupomId)
            ->update();
    }

    /**
     * Totais por evento
     */
    public function totaisPorEvento(?int $eventId = null): array
    {
        $builder = $this->select('
            COUNT(*) as total_cupons,
            SUM(CASE WHEN ativo = 1 THEN 1 ELSE 0 END) as cupons_ativos,
            SUM(quantidade_usada) as total_usos
        ');

        if ($eventId) {
            $builder->where('evento_id', $eventId);
        }

        return $builder->first() ?? ['total_cupons' => 0, 'cupons_ativos' => 0, 'total_usos' => 0];
    }

    /**
     * Lista cupons por evento
     */
    public function listaPorEvento(?int $eventId = null)
    {
        $builder = $this->orderBy('created_at', 'DESC');

        if ($eventId) {
            $builder->where('evento_id', $eventId);
        }

        return $builder->findAll();
    }
}
