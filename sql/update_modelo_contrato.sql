-- ========================================
-- SQL para atualizar o modelo de contrato existente
-- com as informações completas do Manual do Expositor
-- ========================================

UPDATE `contrato_documento_modelos` 
SET `conteudo_html` = '<div style="font-family: ''Times New Roman'', serif; font-size: 12pt; line-height: 1.6; color: #333;">

<div style="text-align: center; margin-bottom: 30px;">
    <h1 style="font-size: 18pt; margin-bottom: 5px; text-transform: uppercase;">CONTRATO DE LOCAÇÃO DE ESPAÇO COMERCIAL</h1>
    <h2 style="font-size: 14pt; color: #666; font-weight: normal;">{{evento_nome}}</h2>
    <p style="margin-top: 10px;"><strong>Contrato Nº:</strong> {{contrato_codigo}}</p>
</div>

<hr style="border: 1px solid #ccc; margin: 20px 0;">

<h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px;">1. DAS PARTES</h3>

<p><strong>LOCADORA:</strong><br>
<strong>Nome/Razão Social:</strong> MUNDO DREAM EVENTOS E PRODUÇÕES LTDA<br>
<strong>CNPJ:</strong> 21.812.142/0001-23<br>
<strong>Endereço:</strong> Av. Carlos Gomes, nº 222, Salas 801 e 802, Bairro Boa Vista, Porto Alegre – RS, CEP 90480-000<br>
Doravante denominada simplesmente "LOCADORA" ou "EVENTO".</p>

<p><strong>LOCATÁRIA:</strong><br>
<strong>Nome/Razão Social:</strong> {{expositor_nome}}<br>
<strong>Nome Fantasia:</strong> {{expositor_nome_fantasia}}<br>
<strong>CPF/CNPJ:</strong> {{expositor_documento}}<br>
<strong>Endereço:</strong> {{expositor_endereco}}<br>
<strong>E-mail:</strong> {{expositor_email}}<br>
<strong>Telefone:</strong> {{expositor_telefone}}<br>
Doravante denominada simplesmente "LOCATÁRIA" ou "EXPOSITORA".</p>

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

<p>A LOCATÁRIA deverá realizar obrigatoriamente o credenciamento até <strong>5 dias antes do evento</strong>, através do site ou planilha enviada com antecedência.</p>

<ul>
    <li><strong>Retirada das credenciais:</strong> No dia anterior ao evento, das 17h às 21h;</li>
    <li>O acesso da equipe da expositora ao evento só será permitido mediante o uso das credenciais e pulseiras de serviço;</li>
    <li>O documento para solicitação das credenciais estará em anexo e deve ser preenchido e assinado via gov.br;</li>
    <li>Após envio, o documento não poderá ser modificado;</li>
    <li>As cortesias que acompanham a reserva serão enviadas para o e-mail de cadastro em até 48h antes da abertura dos portões.</li>
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

<h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px; margin-top: 30px;">6. CREDENCIAMENTO DE VEÍCULOS</h3>

<p>6.1. Os expositores que tiverem materiais para carregar ou descarregar, deverão fazê-lo pelo portão de serviço indicado pela organização. Não é permitido o acesso de veículos no interior do pavilhão. Toda carga e descarga deverá ser feita através da doca de serviço.</p>

<p>6.2. O portão de serviço dará acesso durante o período de montagem e desmontagem do evento. O local do evento, a promotora e a montadora não dispõem de carrinhos para empréstimo/locação, cada expositor/montadora deverá providenciar o seu.</p>

<p>6.3. Não é permitido o acesso de caminhões, carretas e outros veículos no interior do Pavilhão para carga e descarga de materiais de montagens e mercadorias em qualquer etapa do evento (montagem, realização e desmontagem).</p>

<p>6.4. A LOCATÁRIA terá direito a <strong>UM TICKET</strong> de isenção de estacionamento para a sexta-feira que antecede o evento (direito a uma entrada e saída), e <strong>UM TICKET</strong> de isenção de estacionamento para os dias de realização do evento, com entradas e saídas ilimitadas.</p>

<p>6.4.1. Os tickets devem, obrigatoriamente, ser retirados pelo responsável no momento do credenciamento, sendo vetada a retirada após o prazo de credenciamento.</p>

<h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px; margin-top: 30px;">7. ACESSOS</h3>

<p>7.1. Nos dias do evento, o acesso da LOCATÁRIA será feito pelo portão principal, das 8h30 até as 09h50.</p>

<p>7.2. Todos devem estar portando sua credencial e pulseira de serviço.</p>

<p>7.3. Após o horário de acesso, não será permitida a entrada com material de reposição e/ou montagem. O acesso deverá ser solicitado diretamente no CAEX e aguardar liberação, que só será feita em horários de menor fluxo e não é garantido.</p>

<h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px; margin-top: 30px;">8. DO VALOR E PAGAMENTO</h3>

<p><strong>Valor Original:</strong> {{contrato_valor_original}}<br>
<strong>Desconto Concedido:</strong> {{contrato_valor_desconto}}<br>
<strong>Valor Final:</strong> {{contrato_valor_final}}</p>

<p><strong>Forma de Pagamento:</strong> {{contrato_forma_pagamento}}<br>
<strong>Parcelamento:</strong> {{contrato_parcelas}}x de {{contrato_valor_parcela}}</p>

<h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px; margin-top: 30px;">9. DISPOSIÇÕES GERAIS</h3>

<p>9.1. A LOCATÁRIA arcará com o pagamento proporcional de todos os impostos e taxas, seja de que natureza forem, que incidam ou venham a incidir sobre o Estande e eventuais multas decorrentes do inadimplemento ou atraso nos respectivos pagamentos.</p>

<p>9.2. Como forma de propagação de sua atividade comercial, é permitido à LOCATÁRIA fixar letreiros ou faixas e instalar luminosos nas áreas externas do Estande, desde que não o danifique.</p>

<p>9.3. Os produtos devem ser expostos apenas no espaço interno do estande locado, e não deve ultrapassar a altura do seu espaço.</p>

<p>9.4. A LOCADORA disponibilizará à LOCATÁRIA um número pré-definido de mesas e cadeiras, porém, é liberado o uso de mobiliário próprio e/ou locação de material extra.</p>

<p>9.5. A LOCATÁRIA se obriga a respeitar os horários da feira durante todo o período de montagem, realização e desmontagem, mantendo seu estande em perfeitas condições de funcionamento, desde a inauguração até o horário oficial de encerramento, ficando terminantemente proibido o fechamento parcial ou total do estande.</p>

<p>9.6. A LOCATÁRIA se obriga, durante todo o período em que permanecer no imóvel, a zelar pela perfeita conservação e limpeza do mesmo, efetuando os reparos necessários e arcando com os custos.</p>

<p>9.7. Quando findo o presente contrato de locação, caberá à LOCATÁRIA restituir o Estande em condições adequadas de uso, conservação, higiene e manutenção.</p>

<p>9.8. Findo o prazo do Evento, em <strong>3 (três) horas</strong>, o Estande deverá ser desocupado.</p>

<p>9.9. A presente locação destina-se exclusivamente para ocupação de estabelecimento provisório expositora da LOCATÁRIA, vedada qualquer alteração desta destinação. À LOCATÁRIA também não será permitido emprestar, ceder ou sublocar o Estande objeto da presente locação, sem prévia e expressa autorização por parte da LOCADORA.</p>

<p>9.10. À LOCADORA fica facultado vistoriar e examinar o Estande em seu interior, sempre que lhe aprouver, em horário comercial e mediante prévio aviso.</p>

<p>9.11. <strong>A LOCATÁRIA deverá conceder descontos entre 5% e 30% aos visitantes com ingressos VIP FULL e EPIC PASS.</strong></p>

<h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px; margin-top: 30px;">10. DA CONFIDENCIALIDADE</h3>

<p>10.1. As PARTES, por si e por seus sócios, associados, empregados, prepostos, procuradores, terceiros subcontratados ou pessoas ligadas a este ("Partes Relacionadas"), obrigam-se a manter o mais absoluto sigilo relativamente a toda e qualquer informação referente a este termo, em especial quanto ao valor aqui firmado.</p>

<p>10.2. Além de constituir infração contratual, a violação do dever de confidencialidade, inclusive aquela cometida pelas Partes Relacionadas, obriga a Parte infratora ao pagamento de indenização pelos prejuízos comprovadamente causados à Parte proprietária da informação e, eventualmente, às outras Partes que tenham sido prejudicadas.</p>

<p>10.3. As obrigações de confidencialidade subsistirão pelo período de <strong>02 (dois) anos</strong> a partir do encerramento do presente instrumento.</p>

<h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px; margin-top: 30px;">11. DAS PENALIDADES</h3>

<p>A parte que infringir qualquer cláusula deste contrato estará sujeita ao pagamento de multa de <strong>R$ 2.000,00 (dois mil reais)</strong>, corrigidos monetariamente conforme índice do IGPM ou índice que venha substituí-lo e juros de mora na forma da lei, independente de interpelação judicial ou extrajudicial, além de sofrer a competente ação de perdas e danos.</p>

<h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px; margin-top: 30px;">12. DO CANCELAMENTO</h3>

<p><strong>12.1. Cancelamento por parte da LOCADORA:</strong></p>
<p>Em caso de cancelamento do evento, a LOCADORA fica obrigada a devolver integralmente o valor do investimento feito pela LOCATÁRIA em prazo não maior a 60 dias. A organização isenta-se de qualquer responsabilidade sobre demais despesas, como gastos com transporte, estadia e alimentação.</p>

<p>12.1.1. Não se incluem nas infrações fatos decorrentes de força maior como calamidade pública, convulsão social, adiamento do evento ou liberação do local sede do evento.</p>

<p><strong>12.2. Cancelamento por parte da LOCATÁRIA:</strong></p>
<p>Caso a LOCATÁRIA opte pelo cancelamento da sua reserva, deverá solicitar obrigatoriamente através do e-mail comercial@mundodream.com.br</p>

<p>12.3. Em caso de desistência <strong>antes da escolha do espaço</strong> na planta oficial, o valor de ressarcimento se limitará a 50% do valor total do contrato.</p>

<p>12.4. Em caso de desistência <strong>após a escolha do espaço</strong>, vale observar os seguintes critérios:</p>
<ul>
    <li>Pedidos que ocorram <strong>até 60 dias</strong> antes da realização do evento: multa rescisória de <strong>75%</strong> sobre o valor total do contrato;</li>
    <li>Pedidos que ocorram de <strong>60 a 45 dias</strong> antes da realização do evento: multa rescisória de <strong>85%</strong> sobre o valor total do contrato;</li>
    <li>Pedidos que ocorram com <strong>menos de 30 dias</strong> antes da realização do evento: multa rescisória de <strong>95%</strong> sobre o valor total do contrato.</li>
</ul>

<p>12.4.1. O valor da multa será deduzido da quantia já paga pela LOCATÁRIA. Caso esse valor não seja suficiente para quitação total da multa, a LOCATÁRIA terá cinco dias úteis para realizar o pagamento do valor faltante. Caso o valor da multa seja superior ao valor já pago, a LOCADORA terá cinco dias úteis para devolução dos valores.</p>

<h3 style="font-size: 14pt; border-bottom: 2px solid #333; padding-bottom: 5px; margin-top: 30px;">13. DO FORO</h3>

<p>As partes elegem o foro da comarca onde o evento será realizado para dirimir quaisquer dúvidas ou litígios decorrentes deste contrato.</p>

<p>E por estarem assim justas e contratadas, as partes assinam o presente instrumento em formato digital, concordando com todos os termos aqui estabelecidos.</p>

<div style="margin-top: 40px; text-align: center;">
    <p><strong>{{data_atual_extenso}}</strong></p>
</div>

<div style="margin-top: 50px; display: flex; justify-content: space-around;">
    <div style="text-align: center; width: 45%;">
        <div style="border-top: 1px solid #333; padding-top: 10px;">
            <strong>LOCADORA</strong><br>
            MUNDO DREAM EVENTOS E PRODUÇÕES LTDA<br>
            CNPJ: 21.812.142/0001-23
        </div>
    </div>
    <div style="text-align: center; width: 45%;">
        <div style="border-top: 1px solid #333; padding-top: 10px;">
            <strong>LOCATÁRIA</strong><br>
            {{expositor_nome}}<br>
            {{expositor_documento}}
        </div>
    </div>
</div>

</div>',
`updated_at` = NOW()
WHERE `id` = 1;

-- Se quiser atualizar por nome ao invés de ID:
-- WHERE `nome` = 'Contrato Padrão de Exposição';
