<?php

namespace App\Models;

use CodeIgniter\Model;

class LeadModel extends Model
{
    protected $table            = 'leads';
    protected $returnType       = 'App\Entities\Lead';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'codigo',
        'evento_id',
        'vendedor_id',
        'etapa',
        'nome',
        'nome_fantasia',
        'tipo_pessoa',
        'documento',
        'email',
        'telefone',
        'celular',
        'segmento',
        'instagram',
        'origem',
        'valor_estimado',
        'temperatura',
        'proxima_acao',
        'proxima_acao_descricao',
        'motivo_perda',
        'observacoes',
        'expositor_id',
        'contrato_id',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'nome' => 'required|min_length[2]|max_length[255]',
    ];

    protected $validationMessages = [
        'nome' => [
            'required'   => 'O nome é obrigatório.',
            'min_length' => 'O nome deve ter pelo menos 2 caracteres.',
        ],
    ];

    /**
     * Gera código único para o lead
     */
    public function gerarCodigo(): string
    {
        $ano = date('Y');
        $ultimoLead = $this->select('codigo')
            ->like('codigo', "LEAD-{$ano}-", 'after')
            ->orderBy('id', 'DESC')
            ->first();

        $numero = 1;
        if ($ultimoLead && preg_match('/LEAD-\d{4}-(\d+)/', $ultimoLead->codigo, $matches)) {
            $numero = (int) $matches[1] + 1;
        }

        return sprintf("LEAD-%s-%05d", $ano, $numero);
    }

    /**
     * Busca leads agrupados por etapa para o Kanban
     */
    public function buscaLeadsPorEtapa(?int $eventoId = null, ?int $vendedorId = null): array
    {
        $etapas = ['novo', 'primeiro_contato', 'qualificado', 'proposta', 'negociacao', 'ganho', 'perdido'];
        $resultado = [];

        foreach ($etapas as $etapa) {
            $builder = $this->select('leads.*, usuarios.nome as vendedor_nome')
                ->join('usuarios', 'usuarios.id = leads.vendedor_id', 'left')
                ->where('leads.etapa', $etapa);

            if ($eventoId) {
                $builder->where('leads.evento_id', $eventoId);
            }

            if ($vendedorId) {
                $builder->where('leads.vendedor_id', $vendedorId);
            }

            $leads = $builder->orderBy('leads.updated_at', 'DESC')->findAll();

            $totalValor = 0;
            $cards = [];

            foreach ($leads as $lead) {
                $totalValor += $lead->valor_estimado;
                
                $cards[] = [
                    'id'                => $lead->id,
                    'codigo'            => $lead->codigo,
                    'nome'              => $lead->getNomeExibicao(),
                    'nome_completo'     => $lead->nome,
                    'email'             => $lead->email,
                    'celular'           => $lead->celular,
                    'valor_estimado'    => $lead->getValorEstimadoFormatado(),
                    'valor_numero'      => $lead->valor_estimado,
                    'temperatura'       => $lead->temperatura,
                    'temperatura_badge' => $lead->getBadgeTemperatura(),
                    'segmento'          => $lead->segmento,
                    'proxima_acao'      => $lead->getProximaAcaoFormatada(),
                    'proxima_acao_atrasada' => $lead->isProximaAcaoAtrasada(),
                    'vendedor'          => $lead->vendedor_nome ?? '-',
                    'etapa'             => $lead->etapa,
                    'convertido'        => $lead->isConvertido(),
                ];
            }

            $resultado[$etapa] = [
                'cards'                 => $cards,
                'count'                 => count($leads),
                'total_valor'           => $totalValor,
                'total_valor_formatado' => 'R$ ' . number_format($totalValor, 2, ',', '.'),
            ];
        }

        return $resultado;
    }

    /**
     * Busca leads por vendedor
     */
    public function buscaLeadsPorVendedor(int $vendedorId): array
    {
        return $this->where('vendedor_id', $vendedorId)
            ->orderBy('updated_at', 'DESC')
            ->findAll();
    }

    /**
     * Busca lead por documento
     */
    public function buscaPorDocumento(string $documento): ?object
    {
        $documento = preg_replace('/[^0-9]/', '', $documento);
        return $this->where('documento', $documento)->first();
    }

    /**
     * Busca lead por email
     */
    public function buscaPorEmail(string $email): ?object
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Retorna estatísticas do pipeline
     */
    public function getEstatisticas(?int $eventoId = null): array
    {
        $db = \Config\Database::connect();
        
        // Total de leads
        $builderTotal = $db->table('leads')->where('deleted_at IS NULL');
        if ($eventoId) {
            $builderTotal->where('evento_id', $eventoId);
        }
        $total = $builderTotal->countAllResults();

        // Valor total estimado
        $builderValor = $db->table('leads')->selectSum('valor_estimado')->where('deleted_at IS NULL');
        if ($eventoId) {
            $builderValor->where('evento_id', $eventoId);
        }
        $valorResult = $builderValor->get()->getRow();
        $valor = $valorResult ? ($valorResult->valor_estimado ?? 0) : 0;

        // Total ganhos
        $builderGanhos = $db->table('leads')->where('etapa', 'ganho')->where('deleted_at IS NULL');
        if ($eventoId) {
            $builderGanhos->where('evento_id', $eventoId);
        }
        $totalGanhos = $builderGanhos->countAllResults();

        // Total perdidos
        $builderPerdidos = $db->table('leads')->where('etapa', 'perdido')->where('deleted_at IS NULL');
        if ($eventoId) {
            $builderPerdidos->where('evento_id', $eventoId);
        }
        $totalPerdidos = $builderPerdidos->countAllResults();

        return [
            'total'         => $total,
            'valor_total'   => $valor,
            'ganhos'        => $totalGanhos,
            'perdidos'      => $totalPerdidos,
            'taxa_conversao' => $total > 0 ? round(($totalGanhos / $total) * 100, 1) : 0,
        ];
    }
}
