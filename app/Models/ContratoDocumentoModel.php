<?php

namespace App\Models;

use CodeIgniter\Model;

class ContratoDocumentoModel extends Model
{
    protected $table                = 'contrato_documentos';
    protected $returnType           = 'App\Entities\ContratoDocumento';
    protected $useSoftDeletes       = true;
    protected $allowedFields        = [
        'contrato_id',
        'modelo_id',
        'titulo',
        'conteudo_html',
        'status',
        'hash_assinatura',
        'ip_assinatura',
        'user_agent_assinatura',
        'data_envio',
        'data_assinatura',
        'data_confirmacao',
        'assinado_por',
        'documento_assinante',
        'confirmado_por',
        'observacoes',
    ];

    protected $useTimestamps        = true;
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';
    protected $deletedField         = 'deleted_at';

    protected $validationRules    = [
        'contrato_id'   => 'required|integer',
        'titulo'        => 'required|max_length[200]',
        'conteudo_html' => 'required',
    ];

    protected $validationMessages = [
        'contrato_id' => [
            'required' => 'O contrato é obrigatório.',
        ],
        'titulo' => [
            'required' => 'O título do documento é obrigatório.',
        ],
        'conteudo_html' => [
            'required' => 'O conteúdo do documento é obrigatório.',
        ],
    ];

    /**
     * Busca documentos por contrato
     */
    public function buscaPorContrato(int $contratoId): array
    {
        return $this->where('contrato_id', $contratoId)
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    /**
     * Busca documento por hash de assinatura
     */
    public function buscaPorHash(string $hash)
    {
        return $this->where('hash_assinatura', $hash)->first();
    }

    /**
     * Busca documento ativo do contrato (último não cancelado)
     */
    public function buscaDocumentoAtivo(int $contratoId)
    {
        return $this->where('contrato_id', $contratoId)
            ->where('status !=', 'cancelado')
            ->orderBy('id', 'DESC')
            ->first();
    }

    /**
     * Verifica se contrato tem documento confirmado
     */
    public function temDocumentoConfirmado(int $contratoId): bool
    {
        return $this->where('contrato_id', $contratoId)
            ->where('status', 'confirmado')
            ->countAllResults() > 0;
    }

    /**
     * Verifica se contrato tem documento pendente de assinatura
     */
    public function temDocumentoPendente(int $contratoId): bool
    {
        return $this->where('contrato_id', $contratoId)
            ->whereIn('status', ['pendente_assinatura', 'assinado'])
            ->countAllResults() > 0;
    }

    /**
     * Gerar documento a partir do modelo preenchido
     */
    public function gerarDocumento(int $contratoId, int $modeloId = null): ?int
    {
        try {
            $contratoModel = new \App\Models\ContratoModel();
            $expositorModel = new \App\Models\ExpositorModel();
            $eventoModel = new \App\Models\EventoModel();
            $itemModel = new \App\Models\ContratoItemModel();
            $modeloDocModel = new \App\Models\ContratoDocumentoModeloModel();

            $contrato = $contratoModel->find($contratoId);
            if (!$contrato) {
                log_message('error', 'gerarDocumento: Contrato não encontrado - ID: ' . $contratoId);
                return null;
            }

            $expositor = $expositorModel->find($contrato->expositor_id);
            if (!$expositor) {
                log_message('error', 'gerarDocumento: Expositor não encontrado - ID: ' . $contrato->expositor_id);
                return null;
            }
            
            $evento = $eventoModel->find($contrato->event_id);
            $itens = $itemModel->buscaPorContrato($contratoId);

            // Busca modelo (pelo ID informado ou automático)
            if ($modeloId) {
                $modelo = $modeloDocModel->find($modeloId);
            } else {
                $modelo = $modeloDocModel->buscaModeloParaContrato($contratoId);
            }

            if (!$modelo) {
                log_message('error', 'gerarDocumento: Modelo não encontrado para o contrato ' . $contratoId);
                return null;
            }
            
            log_message('info', 'gerarDocumento: Gerando documento para contrato ' . $contratoId . ' com modelo ' . $modelo->id);

        // Monta tabela de itens com desconto
        $tabelaItens = '<table class="tabela-itens" style="width:100%; border-collapse: collapse; margin: 10px 0;">
            <thead>
                <tr style="background-color: #f5f5f5;">
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Item</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Descrição</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Qtd</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Valor Unit.</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Desconto</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($itens as $item) {
            $desconto = $item->valor_desconto ?? 0;
            $descontoFormatado = 'R$ ' . number_format($desconto, 2, ',', '.');
            
            $tabelaItens .= '<tr>
                <td style="border: 1px solid #ddd; padding: 8px;">' . esc($item->tipo_item) . '</td>
                <td style="border: 1px solid #ddd; padding: 8px;">' . esc($item->descricao) . ' ' . esc($item->localizacao) . '</td>
                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . $item->quantidade . '</td>
                <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">' . $item->getValorUnitarioFormatado() . '</td>
                <td style="border: 1px solid #ddd; padding: 8px; text-align: right; color: #28a745;">' . ($desconto > 0 ? '-' . $descontoFormatado : '-') . '</td>
                <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">' . $item->getValorTotalFormatado() . '</td>
            </tr>';
        }

        $tabelaItens .= '</tbody>
            <tfoot>
                <tr style="background-color: #f9f9f9;">
                    <td colspan="5" style="border: 1px solid #ddd; padding: 8px; text-align: right;">Subtotal:</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">' . $contrato->getValorOriginalFormatado() . '</td>
                </tr>
                <tr style="background-color: #f9f9f9; color: #28a745;">
                    <td colspan="5" style="border: 1px solid #ddd; padding: 8px; text-align: right;">Desconto Total:</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">-' . $contrato->getValorDescontoFormatado() . '</td>
                </tr>
                <tr style="background-color: #f5f5f5; font-weight: bold;">
                    <td colspan="5" style="border: 1px solid #ddd; padding: 8px; text-align: right;">TOTAL:</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">' . $contrato->getValorFinalFormatado() . '</td>
                </tr>
                <tr style="background-color: #e3f2fd;">
                    <td colspan="5" style="border: 1px solid #ddd; padding: 8px; text-align: right;">Valor Pago:</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">R$ ' . number_format($contrato->valor_pago ?? 0, 2, ',', '.') . '</td>
                </tr>
            </tfoot>
        </table>';

        // Monta dados para substituição
        $dados = [
            // Contrato
            'contrato_codigo'           => $contrato->codigo ?? '#' . $contrato->id,
            'contrato_data_proposta'    => $contrato->data_proposta ? date('d/m/Y', strtotime($contrato->data_proposta)) : '-',
            'contrato_data_aceite'      => $contrato->data_aceite ? date('d/m/Y', strtotime($contrato->data_aceite)) : '-',
            'contrato_valor_original'   => $contrato->getValorOriginalFormatado(),
            'contrato_valor_desconto'   => $contrato->getValorDescontoFormatado(),
            'contrato_valor_final'      => $contrato->getValorFinalFormatado(),
            'contrato_valor_pago'       => 'R$ ' . number_format($contrato->valor_pago ?? 0, 2, ',', '.'),
            'contrato_valor_em_aberto'  => 'R$ ' . number_format($contrato->valor_em_aberto ?? 0, 2, ',', '.'),
            'contrato_parcelas'         => $contrato->quantidade_parcelas ?? 1,
            'contrato_valor_parcela'    => $contrato->getValorParcelaFormatado(),
            'contrato_forma_pagamento'  => $contrato->forma_pagamento ?? 'Não definida',

            // Expositor
            'expositor_nome'            => $expositor->nome ?? '',
            'expositor_nome_fantasia'   => $expositor->nome_fantasia ?? $expositor->nome ?? '',
            'expositor_documento'       => $expositor->getDocumentoFormatado() ?? '',
            'expositor_tipo_pessoa'     => $expositor->getTipoPessoaFormatado() ?? '',
            'expositor_endereco'        => $expositor->getEnderecoCompleto() ?? '',
            'expositor_email'           => $expositor->email ?? '',
            'expositor_telefone'        => $expositor->telefone ?? $expositor->celular ?? '',

            // Evento
            'evento_nome'               => $evento->nome ?? '',
            'evento_data_inicio'        => isset($evento->data_inicio) ? date('d/m/Y', strtotime($evento->data_inicio)) : '-',
            'evento_data_fim'           => isset($evento->data_fim) ? date('d/m/Y', strtotime($evento->data_fim)) : '-',
            'evento_local'              => $evento->local ?? '',

            // Itens
            'itens_lista'               => $tabelaItens,
            'itens_total'               => $contrato->getValorFinalFormatado(),

            // Data
            'data_atual'                => date('d/m/Y'),
            'data_atual_extenso'        => $this->dataExtenso(date('Y-m-d')),
        ];

        // Preenche o conteúdo
        $conteudoPreenchido = $modelo->preencherConteudo($dados);

        // Cria o documento
        $documento = new \App\Entities\ContratoDocumento([
            'contrato_id'   => $contratoId,
            'modelo_id'     => $modelo->id,
            'titulo'        => 'Contrato ' . ($contrato->codigo ?? '#' . $contrato->id),
            'conteudo_html' => $conteudoPreenchido,
            'status'        => 'rascunho',
        ]);

        if ($this->save($documento)) {
            log_message('info', 'gerarDocumento: Documento criado com sucesso - ID: ' . $this->getInsertID());
            return $this->getInsertID();
        }

        log_message('error', 'gerarDocumento: Erro ao salvar documento - Erros: ' . json_encode($this->errors()));
        return null;
        
        } catch (\Exception $e) {
            log_message('error', 'gerarDocumento: Exceção - ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Converte data para extenso em português
     */
    private function dataExtenso(string $data): string
    {
        $meses = [
            1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
            5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
            9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro'
        ];

        $timestamp = strtotime($data);
        $dia = date('d', $timestamp);
        $mes = $meses[(int)date('m', $timestamp)];
        $ano = date('Y', $timestamp);

        return "{$dia} de {$mes} de {$ano}";
    }
}

