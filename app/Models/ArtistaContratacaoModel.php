<?php

namespace App\Models;

use CodeIgniter\Model;

class ArtistaContratacaoModel extends Model
{
    protected $table                = 'artista_contratacoes';
    protected $returnType           = 'App\Entities\ArtistaContratacao';
    protected $useSoftDeletes       = false;
    protected $allowedFields        = [
        'artista_id',
        'event_id',
        'codigo',
        'situacao',
        'data_apresentacao',
        'horario_inicio',
        'horario_fim',
        'palco',
        'valor_cache',
        'forma_pagamento',
        'quantidade_parcelas',
        'observacoes',
    ];

    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';

    /**
     * Gera código único
     */
    public function gerarCodigo(): string
    {
        $ano = date('Y');
        $tentativas = 0;
        
        do {
            $codigo = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            $codigo = sprintf('ART-%s-%s', $ano, $codigo);
            $existe = $this->where('codigo', $codigo)->countAllResults() > 0;
            $tentativas++;
        } while ($existe && $tentativas < 50);
        
        return $codigo;
    }

    /**
     * Busca contratação completa
     */
    public function buscaCompleta(int $id)
    {
        return $this->select('artista_contratacoes.*, artistas.nome_artistico, eventos.nome as evento_nome')
            ->join('artistas', 'artistas.id = artista_contratacoes.artista_id', 'left')
            ->join('eventos', 'eventos.id = artista_contratacoes.event_id', 'left')
            ->find($id);
    }

    /**
     * Busca por evento
     */
    public function buscaPorEvento(int $eventId): array
    {
        return $this->select('artista_contratacoes.*, artistas.nome_artistico')
            ->join('artistas', 'artistas.id = artista_contratacoes.artista_id', 'left')
            ->where('event_id', $eventId)
            ->orderBy('data_apresentacao', 'ASC')
            ->findAll();
    }

    /**
     * Busca por artista
     */
    public function buscaPorArtista(int $artistaId): array
    {
        return $this->select('artista_contratacoes.*, eventos.nome as evento_nome')
            ->join('eventos', 'eventos.id = artista_contratacoes.event_id', 'left')
            ->where('artista_id', $artistaId)
            ->orderBy('data_apresentacao', 'DESC')
            ->findAll();
    }

    /**
     * Calcula totais da contratação
     */
    public function calculaTotais(int $contratacaoId): array
    {
        $totais = [
            'cache' => 0,
            'voos' => 0,
            'hospedagens' => 0,
            'translados' => 0,
            'alimentacoes' => 0,
            'extras' => 0,
            'total' => 0,
            'pago' => 0,
            'pendente' => 0,
        ];

        // Cachê
        $contratacao = $this->find($contratacaoId);
        if ($contratacao) {
            $totais['cache'] = $contratacao->valor_cache;
        }

        // Voos
        $vooModel = new ArtistaVooModel();
        $voos = $vooModel->where('contratacao_id', $contratacaoId)->findAll();
        foreach ($voos as $v) {
            $totais['voos'] += $v->valor;
            if ($v->status === 'pago') $totais['pago'] += $v->valor;
            else $totais['pendente'] += $v->valor;
        }

        // Hospedagens
        $hospModel = new ArtistaHospedagemModel();
        $hosps = $hospModel->where('contratacao_id', $contratacaoId)->findAll();
        foreach ($hosps as $h) {
            $totais['hospedagens'] += $h->valor_total;
            if ($h->status === 'pago') $totais['pago'] += $h->valor_total;
            else $totais['pendente'] += $h->valor_total;
        }

        // Translados
        $transModel = new ArtistaTransladoModel();
        $trans = $transModel->where('contratacao_id', $contratacaoId)->findAll();
        foreach ($trans as $t) {
            $totais['translados'] += $t->valor;
            if ($t->status === 'pago') $totais['pago'] += $t->valor;
            else $totais['pendente'] += $t->valor;
        }

        // Alimentações
        $alimModel = new ArtistaAlimentacaoModel();
        $alims = $alimModel->where('contratacao_id', $contratacaoId)->findAll();
        foreach ($alims as $a) {
            $totais['alimentacoes'] += $a->valor_total;
            if ($a->status === 'pago') $totais['pago'] += $a->valor_total;
            else $totais['pendente'] += $a->valor_total;
        }

        // Extras
        $extraModel = new ArtistaCustoExtraModel();
        $extras = $extraModel->where('contratacao_id', $contratacaoId)->findAll();
        foreach ($extras as $e) {
            $totais['extras'] += $e->valor;
            if ($e->status === 'pago') $totais['pago'] += $e->valor;
            else $totais['pendente'] += $e->valor;
        }

        // Parcelas do cachê
        $parcelaModel = new ArtistaParcelaModel();
        $parcelas = $parcelaModel->where('contratacao_id', $contratacaoId)->findAll();
        foreach ($parcelas as $p) {
            if ($p->status === 'pago') $totais['pago'] += $p->valor;
            else $totais['pendente'] += $p->valor;
        }

        $totais['total'] = $totais['cache'] + $totais['voos'] + $totais['hospedagens'] + 
                          $totais['translados'] + $totais['alimentacoes'] + $totais['extras'];

        return $totais;
    }
}
