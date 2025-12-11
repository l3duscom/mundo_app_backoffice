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
<strong>Nome/Razão Social:</strong> MUNDO DREAM EVENTOS E PRODUÇÕES LTDA<br>
<strong>CNPJ:</strong> 21.812.142/0001-23<br>
<strong>Endereço:</strong> Av. Carlos Gomes, nº 222, Salas 801 e 802, Bairro Boa Vista, Porto Alegre – RS, CEP 90480-000<br>
Doravante denominado simplesmente "LOCADOR" ou "EVENTO".</p>

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

<h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px; margin-top: 30px;">3. DO PERÍODO E REALIZAÇÃO</h3>

<p>O evento será realizado nas seguintes datas:</p>
<ul>
    <li><strong>Data de Início:</strong> {{evento_data_inicio}}</li>
    <li><strong>Data de Término:</strong> {{evento_data_fim}}</li>
    <li><strong>Horário de Realização:</strong> 10h às 20h</li>
    <li><strong>Local:</strong> {{evento_local}}</li>
</ul>

<h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px; margin-top: 30px;">4. DO CREDENCIAMENTO</h3>

<p>O LOCATÁRIO deverá realizar obrigatoriamente o credenciamento até <strong>5 dias antes do evento</strong>, através do site ou planilha enviada com antecedência.</p>

<ul>
    <li><strong>Retirada das credenciais:</strong> No dia anterior ao evento, das 17h às 21h;</li>
    <li>O acesso da equipe do expositor ao evento só será permitido mediante o uso das credenciais e pulseiras de serviço;</li>
    <li>O documento para solicitação das credenciais estará em anexo e deve ser preenchido e assinado via gov.br;</li>
    <li>Após envio, o documento não poderá ser modificado;</li>
    <li>As cortesias que acompanham a reserva serão enviadas para o email de cadastro em até 48h antes da abertura dos portões.</li>
</ul>

<p><strong>Contatos:</strong><br>
E-mail Comercial: comercial@mundodream.com.br<br>
Site de Credenciamento/Cortesias: https://mundodream.com.br</p>

<h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px; margin-top: 30px;">5. MONTAGEM E DESMONTAGEM</h3>

<ul>
    <li><strong>Montagem:</strong> No dia anterior ao evento, das 18h às 23h59;</li>
    <li><strong>Desmontagem:</strong> No último dia de evento, das 20h15 às 23h59;</li>
    <li>É recomendado o uso de fechamento frontal e proteção com lona durante a noite, mesmo que o evento conte com segurança noturna.</li>
</ul>

<h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px; margin-top: 30px;">6. ACESSO DE VEÍCULOS E ESTACIONAMENTO</h3>

<ul>
    <li>Entrada e saída de materiais pelo portão indicado pela staff no momento da chegada;</li>
    <li>Carga e descarga devem ser feitas pelo portão indicado pela organização;</li>
    <li>A organização oferece carrinhos de transporte, consulte disponibilidade.</li>
</ul>

<p><strong>Estacionamento - Cada expositor tem direito a:</strong></p>
<ul>
    <li>01 ticket de isenção de estacionamento para o dia de montagem (uma entrada e saída);</li>
    <li>01 ticket de isenção para os dias do evento (entradas e saídas ilimitadas);</li>
    <li>Os tickets devem ser retirados no credenciamento – não haverá segunda via.</li>
</ul>

<h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px; margin-top: 30px;">7. ACESSO NOS DIAS DE EVENTO</h3>

<ul>
    <li><strong>Entrada da equipe:</strong> 08h00 às 09h59 pelo portão de staff;</li>
    <li>Após este horário, só será permitida a entrada mediante solicitação no CAEX e aprovação em horários de menor fluxo.</li>
</ul>

<h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px; margin-top: 30px;">8. DO VALOR E PAGAMENTO</h3>

<p><strong>Valor Original:</strong> {{contrato_valor_original}}<br>
<strong>Desconto Concedido:</strong> {{contrato_valor_desconto}}<br>
<strong>Valor Final:</strong> {{contrato_valor_final}}</p>

<p><strong>Forma de Pagamento:</strong> {{contrato_forma_pagamento}}<br>
<strong>Parcelamento:</strong> {{contrato_parcelas}}x de {{contrato_valor_parcela}}</p>

<h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px; margin-top: 30px;">9. DAS OBRIGAÇÕES DO LOCATÁRIO</h3>

<ol type="a">
    <li>Efetuar o pagamento nas datas acordadas;</li>
    <li>Respeitar os horários de montagem, funcionamento e desmontagem;</li>
    <li>Manter o espaço limpo e organizado durante todo o evento;</li>
    <li>Não ultrapassar os limites do espaço locado;</li>
    <li>Não comercializar produtos diferentes dos informados no cadastro;</li>
    <li>Cumprir todas as normas de segurança e legislação vigente;</li>
    <li>Não ceder, transferir ou sublocar o espaço a terceiros;</li>
    <li>Apresentar todos os documentos necessários para fiscalização;</li>
    <li>Zelar pela segurança de seus produtos e equipamentos;</li>
    <li>Não fechar o estande antes do horário de encerramento do evento;</li>
    <li>Manter produtos dentro da área do stand e respeitar a altura da estrutura contratada;</li>
    <li>Ao fim do evento, desocupar o estande em até 3 horas;</li>
    <li><strong>Conceder descontos entre 5% e 30% aos visitantes com ingressos VIP FULL e EPIC PASS.</strong></li>
</ol>

<h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px; margin-top: 30px;">10. DAS OBRIGAÇÕES DO LOCADOR</h3>

<ol type="a">
    <li>Disponibilizar o espaço conforme especificado neste contrato;</li>
    <li>Providenciar a estrutura básica do evento;</li>
    <li>Realizar a divulgação geral do evento;</li>
    <li>Fornecer credenciamento ao expositor e seus colaboradores;</li>
    <li>Prestar suporte durante a realização do evento.</li>
</ol>

<h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px; margin-top: 30px;">11. DO CANCELAMENTO</h3>

<p>Em caso de cancelamento por parte do LOCATÁRIO:</p>
<ul>
    <li>Até 30 dias antes do evento: devolução de 50% do valor pago;</li>
    <li>Menos de 30 dias antes do evento: sem direito a reembolso.</li>
</ul>

<p>Em caso de cancelamento do evento por força maior, será realizada a devolução integral dos valores pagos ou crédito para edição futura.</p>

<h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px; margin-top: 30px;">12. DA CONFIDENCIALIDADE</h3>

<p>As partes devem manter sigilo das condições contratuais por 2 (dois) anos.</p>

<h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px; margin-top: 30px;">13. DAS DISPOSIÇÕES GERAIS</h3>

<p>As partes elegem o foro da comarca onde o evento será realizado para dirimir quaisquer dúvidas ou litígios decorrentes deste contrato.</p>

<p>E por estarem assim justos e contratados, as partes assinam o presente instrumento em formato digital, concordando com todos os termos aqui estabelecidos.</p>

<div style="margin-top: 40px; text-align: center;">
    <p><strong>{{data_atual_extenso}}</strong></p>
</div>

<div style="margin-top: 50px; display: flex; justify-content: space-around;">
    <div style="text-align: center; width: 45%;">
        <div style="border-top: 1px solid #333; padding-top: 10px;">
            <strong>LOCADOR</strong><br>
            MUNDO DREAM EVENTOS E PRODUÇÕES LTDA<br>
            CNPJ: 21.812.142/0001-23
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

