<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Contrato extends Entity
{

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // Mapeamento das situações
    protected $situacoes = [
        'proposta'              => 'Proposta',
        'proposta_aceita'       => 'Proposta Aceita',
        'contrato_assinado'     => 'Contrato Assinado',
        'pagamento_aberto'      => 'Pagamento em Aberto',
        'pagamento_andamento'   => 'Pagamento em Andamento',
        'aguardando_contrato'   => 'Aguardando Contrato',
        'pagamento_confirmado'  => 'Pagamento Confirmado',
        'cancelado'             => 'Cancelado',
        'banido'                => 'Banido',
    ];

    /**
     * Retorna a situação formatada
     *
     * @return string
     */
    public function getSituacaoFormatada(): string
    {
        return $this->situacoes[$this->situacao] ?? $this->situacao;
    }

    /**
     * Retorna o badge colorido da situação
     *
     * @return string
     */
    public function getBadgeSituacao(): string
    {
        $cores = [
            'proposta'              => 'bg-secondary',
            'proposta_aceita'       => 'bg-info',
            'contrato_assinado'     => 'bg-primary',
            'pagamento_aberto'      => 'bg-warning text-dark',
            'pagamento_andamento'   => 'bg-orange',
            'aguardando_contrato'   => 'bg-purple',
            'pagamento_confirmado'  => 'bg-success',
            'cancelado'             => 'bg-danger',
            'banido'                => 'bg-dark',
        ];

        $classe = $cores[$this->situacao] ?? 'bg-secondary';
        $texto = $this->getSituacaoFormatada();

        // Cores customizadas
        $style = '';
        if ($classe === 'bg-orange') {
            $classe = '';
            $style = 'style="background-color: #fd7e14; color: white;"';
        } elseif ($classe === 'bg-purple') {
            $classe = '';
            $style = 'style="background-color: #6f42c1; color: white;"';
        }

        return '<span class="badge ' . $classe . '" ' . $style . '>' . esc($texto) . '</span>';
    }

    /**
     * Retorna o ícone da situação
     *
     * @return string
     */
    public function getIconeSituacao(): string
    {
        $icones = [
            'proposta'              => 'bx bx-file',
            'proposta_aceita'       => 'bx bx-check',
            'contrato_assinado'     => 'bx bx-edit',
            'pagamento_aberto'      => 'bx bx-time',
            'pagamento_andamento'   => 'bx bx-loader',
            'aguardando_contrato'   => 'bx bx-file-find',
            'pagamento_confirmado'  => 'bx bx-check-circle',
            'cancelado'             => 'bx bx-x-circle',
            'banido'                => 'bx bx-block',
        ];

        return $icones[$this->situacao] ?? 'bx bx-help-circle';
    }

    /**
     * Retorna o valor original formatado em BRL
     *
     * @return string
     */
    public function getValorOriginalFormatado(): string
    {
        return 'R$ ' . number_format($this->valor_original, 2, ',', '.');
    }

    /**
     * Retorna o valor do desconto formatado em BRL
     *
     * @return string
     */
    public function getValorDescontoFormatado(): string
    {
        return 'R$ ' . number_format($this->valor_desconto, 2, ',', '.');
    }

    /**
     * Retorna o valor final formatado em BRL
     *
     * @return string
     */
    public function getValorFinalFormatado(): string
    {
        return 'R$ ' . number_format($this->valor_final, 2, ',', '.');
    }

    /**
     * Retorna o valor pago formatado em BRL
     *
     * @return string
     */
    public function getValorPagoFormatado(): string
    {
        return 'R$ ' . number_format($this->valor_pago, 2, ',', '.');
    }

    /**
     * Retorna o valor em aberto formatado em BRL
     *
     * @return string
     */
    public function getValorEmAbertoFormatado(): string
    {
        return 'R$ ' . number_format($this->valor_em_aberto, 2, ',', '.');
    }

    /**
     * Retorna o valor da parcela formatado em BRL
     *
     * @return string
     */
    public function getValorParcelaFormatado(): string
    {
        return 'R$ ' . number_format($this->valor_parcela, 2, ',', '.');
    }

    /**
     * Retorna a porcentagem paga
     *
     * @return float
     */
    public function getPorcentagemPaga(): float
    {
        if ($this->valor_final <= 0) {
            return 0;
        }

        return round(($this->valor_pago / $this->valor_final) * 100, 1);
    }

    /**
     * Retorna a porcentagem de desconto
     *
     * @return float
     */
    public function getPorcentagemDesconto(): float
    {
        if ($this->valor_original <= 0) {
            return 0;
        }

        return round(($this->valor_desconto / $this->valor_original) * 100, 1);
    }

    /**
     * Verifica se o contrato está pago
     *
     * @return bool
     */
    public function isPago(): bool
    {
        return $this->situacao === 'pagamento_confirmado';
    }

    /**
     * Verifica se o contrato está cancelado ou banido
     *
     * @return bool
     */
    public function isCanceladoOuBanido(): bool
    {
        return in_array($this->situacao, ['cancelado', 'banido']);
    }

    /**
     * Verifica se o contrato está ativo (não cancelado/banido)
     *
     * @return bool
     */
    public function isAtivo(): bool
    {
        return !$this->isCanceladoOuBanido() && $this->deleted_at === null;
    }

    /**
     * Calcula os valores automaticamente
     *
     * @return void
     */
    public function calcularValores(): void
    {
        $this->valor_final = $this->valor_original - $this->valor_desconto;
        $this->valor_em_aberto = $this->valor_final - $this->valor_pago;
        
        if ($this->quantidade_parcelas > 0) {
            $this->valor_parcela = $this->valor_final / $this->quantidade_parcelas;
        }
    }

    /**
     * Exibe a situação do contrato (para listagem)
     *
     * @return string
     */
    public function exibeSituacao(): string
    {
        if ($this->deleted_at != null) {
            $icone = '<span class="text-white">Excluído</span>&nbsp;<i class="fa fa-undo"></i>&nbsp;Desfazer';
            return anchor("contratos/desfazerexclusao/$this->id", $icone, ['class' => 'btn btn-outline-success btn-sm']);
        }

        return $this->getBadgeSituacao();
    }
}

