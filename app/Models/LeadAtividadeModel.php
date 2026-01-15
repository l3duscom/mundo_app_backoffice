<?php

namespace App\Models;

use CodeIgniter\Model;

class LeadAtividadeModel extends Model
{
    protected $table          = 'lead_atividades';
    protected $returnType     = 'object';
    protected $allowedFields  = [
        'lead_id',
        'usuario_id',
        'tipo',
        'descricao',
        'etapa_anterior',
        'etapa_nova',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = false;

    /**
     * Registra uma nova atividade
     */
    public function registrarAtividade(int $leadId, string $tipo, ?string $descricao = null, ?string $etapaAnterior = null, ?string $etapaNova = null): int
    {
        $usuarioId = session()->get('usuario_id');

        $data = [
            'lead_id'        => $leadId,
            'usuario_id'     => $usuarioId,
            'tipo'           => $tipo,
            'descricao'      => $descricao,
            'etapa_anterior' => $etapaAnterior,
            'etapa_nova'     => $etapaNova,
        ];

        $this->insert($data);
        return $this->getInsertID();
    }

    /**
     * Busca atividades de um lead
     */
    public function buscarPorLead(int $leadId, int $limite = 50): array
    {
        return $this->select('lead_atividades.*, usuarios.nome as usuario_nome')
            ->join('usuarios', 'usuarios.id = lead_atividades.usuario_id', 'left')
            ->where('lead_atividades.lead_id', $leadId)
            ->orderBy('lead_atividades.created_at', 'DESC')
            ->limit($limite)
            ->findAll();
    }

    /**
     * Retorna ícone e cor para cada tipo de atividade
     */
    public static function getConfigTipo(string $tipo): array
    {
        $config = [
            'nota'          => ['icone' => 'bx bx-note', 'cor' => 'secondary', 'nome' => 'Nota'],
            'ligacao'       => ['icone' => 'bx bx-phone', 'cor' => 'info', 'nome' => 'Ligação'],
            'email'         => ['icone' => 'bx bx-envelope', 'cor' => 'primary', 'nome' => 'E-mail'],
            'reuniao'       => ['icone' => 'bx bx-calendar', 'cor' => 'success', 'nome' => 'Reunião'],
            'whatsapp'      => ['icone' => 'bx bxl-whatsapp', 'cor' => 'success', 'nome' => 'WhatsApp'],
            'mudanca_etapa' => ['icone' => 'bx bx-transfer', 'cor' => 'warning', 'nome' => 'Mudança de Etapa'],
            'criacao'       => ['icone' => 'bx bx-plus-circle', 'cor' => 'primary', 'nome' => 'Criação'],
            'conversao'     => ['icone' => 'bx bx-check-double', 'cor' => 'success', 'nome' => 'Conversão'],
        ];

        return $config[$tipo] ?? ['icone' => 'bx bx-history', 'cor' => 'secondary', 'nome' => $tipo];
    }

    /**
     * Retorna nome amigável da etapa
     */
    public static function getNomeEtapa(string $etapa): string
    {
        $nomes = [
            'novo'             => 'Novo',
            'primeiro_contato' => 'Primeiro Contato',
            'qualificado'      => 'Qualificado',
            'proposta'         => 'Proposta',
            'negociacao'       => 'Negociação',
            'ganho'            => 'Ganho',
            'perdido'          => 'Perdido',
        ];

        return $nomes[$etapa] ?? $etapa;
    }
}
