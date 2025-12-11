<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ContratoDocumentoModeloSeeder extends Seeder
{
    public function run()
    {
        $modelo = [
            'nome' => 'Contrato Padrão de Exposição',
            'tipo_item' => 'Geral',
            'descricao' => 'Modelo padrão para contratos de espaço comercial e expositores',
            'ativo' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'conteudo_html' => $this->getModeloPadrao(),
        ];

        $this->db->table('contrato_documento_modelos')->insert($modelo);
    }

    private function getModeloPadrao(): string
    {
        return <<<HTML
<div style="font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.6; color: #333;">

<div style="text-align: center; margin-bottom: 30px;">
    <h1 style="font-size: 18pt; margin-bottom: 5px; text-transform: uppercase;">CONTRATO DE LOCAÇÃO DE ESPAÇO COMERCIAL</h1>
    <h2 style="font-size: 14pt; color: #666; font-weight: normal;">{{evento_nome}}</h2>
    <p style="margin-top: 10px;"><strong>Contrato Nº:</strong> {{contrato_codigo}}</p>
</div>

<hr style="border: 1px solid #ccc; margin: 20px 0;">

<h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px;">1. DAS PARTES</h3>

<p><strong>LOCADOR:</strong><br>
Empresa organizadora do evento {{evento_nome}}, doravante denominada simplesmente "LOCADOR".</p>

<p><strong>LOCATÁRIO:</strong><br>
<strong>Nome/Razão Social:</strong> {{expositor_nome}}<br>
<strong>Nome Fantasia:</strong> {{expositor_nome_fantasia}}<br>
<strong>CPF/CNPJ:</strong> {{expositor_documento}}<br>
<strong>Endereço:</strong> {{expositor_endereco}}<br>
<strong>E-mail:</strong> {{expositor_email}}<br>
<strong>Telefone:</strong> {{expositor_telefone}}<br>
Doravante denominado simplesmente "LOCATÁRIO" ou "EXPOSITOR".</p>

<h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px; margin-top: 30px;">2. DO OBJETO</h3>

<p>O presente contrato tem por objeto a locação de espaço comercial para participação no evento <strong>{{evento_nome}}</strong>, conforme especificações abaixo:</p>

{{itens_lista}}

<h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px; margin-top: 30px;">3. DO PERÍODO</h3>

<p>O evento será realizado nas seguintes datas:</p>
<ul>
    <li><strong>Data de Início:</strong> {{evento_data_inicio}}</li>
    <li><strong>Data de Término:</strong> {{evento_data_fim}}</li>
</ul>

<p>A montagem deverá ser realizada no dia anterior ao início do evento, e a desmontagem deverá ser concluída até 4 horas após o encerramento do último dia do evento.</p>

<h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px; margin-top: 30px;">4. DO VALOR E PAGAMENTO</h3>

<p><strong>Valor Original:</strong> {{contrato_valor_original}}<br>
<strong>Desconto Concedido:</strong> {{contrato_valor_desconto}}<br>
<strong>Valor Final:</strong> {{contrato_valor_final}}</p>

<p><strong>Forma de Pagamento:</strong> {{contrato_forma_pagamento}}<br>
<strong>Parcelamento:</strong> {{contrato_parcelas}}x de {{contrato_valor_parcela}}</p>

<h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px; margin-top: 30px;">5. DAS OBRIGAÇÕES DO LOCATÁRIO</h3>

<ol type="a">
    <li>Efetuar o pagamento nas datas acordadas;</li>
    <li>Respeitar os horários de montagem, funcionamento e desmontagem;</li>
    <li>Manter o espaço limpo e organizado durante todo o evento;</li>
    <li>Não ultrapassar os limites do espaço locado;</li>
    <li>Não comercializar produtos diferentes dos informados no cadastro;</li>
    <li>Cumprir todas as normas de segurança e legislação vigente;</li>
    <li>Não ceder, transferir ou sublocar o espaço a terceiros;</li>
    <li>Apresentar todos os documentos necessários para fiscalização;</li>
    <li>Zelar pela segurança de seus produtos e equipamentos.</li>
</ol>

<h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px; margin-top: 30px;">6. DAS OBRIGAÇÕES DO LOCADOR</h3>

<ol type="a">
    <li>Disponibilizar o espaço conforme especificado neste contrato;</li>
    <li>Providenciar a estrutura básica do evento;</li>
    <li>Realizar a divulgação geral do evento;</li>
    <li>Fornecer credenciamento ao expositor e seus colaboradores;</li>
    <li>Prestar suporte durante a realização do evento.</li>
</ol>

<h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px; margin-top: 30px;">7. DO CANCELAMENTO</h3>

<p>Em caso de cancelamento por parte do LOCATÁRIO:</p>
<ul>
    <li>Até 30 dias antes do evento: devolução de 50% do valor pago;</li>
    <li>Menos de 30 dias antes do evento: sem direito a reembolso.</li>
</ul>

<p>Em caso de cancelamento do evento por força maior, será realizada a devolução integral dos valores pagos ou crédito para edição futura.</p>

<h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px; margin-top: 30px;">8. DAS DISPOSIÇÕES GERAIS</h3>

<p>As partes elegem o foro da comarca onde o evento será realizado para dirimir quaisquer dúvidas ou litígios decorrentes deste contrato.</p>

<p>E por estarem assim justos e contratados, as partes assinam o presente instrumento em formato digital, concordando com todos os termos aqui estabelecidos.</p>

<div style="margin-top: 40px; text-align: center;">
    <p><strong>{{data_atual_extenso}}</strong></p>
</div>

<div style="margin-top: 50px; display: flex; justify-content: space-around;">
    <div style="text-align: center; width: 45%;">
        <div style="border-top: 1px solid #333; padding-top: 10px;">
            <strong>LOCADOR</strong><br>
            Organizador do Evento
        </div>
    </div>
    <div style="text-align: center; width: 45%;">
        <div style="border-top: 1px solid #333; padding-top: 10px;">
            <strong>LOCATÁRIO</strong><br>
            {{expositor_nome}}<br>
            {{expositor_documento}}
        </div>
    </div>
</div>

</div>
HTML;
    }
}

