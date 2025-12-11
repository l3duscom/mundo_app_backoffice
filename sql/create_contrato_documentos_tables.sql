-- ========================================
-- Script para criar as tabelas de Documentos de Contrato
-- MySQL 5.7+ / MariaDB 10.2+
-- ========================================

-- Tabela de Modelos de Documento
CREATE TABLE IF NOT EXISTS `contrato_documento_modelos` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nome` VARCHAR(100) NOT NULL COMMENT 'Nome do modelo (ex: Contrato Espaço Comercial)',
    `tipo_item` VARCHAR(50) NOT NULL COMMENT 'Tipo de item vinculado (ex: Espaço Comercial)',
    `descricao` TEXT NULL COMMENT 'Descrição do modelo',
    `conteudo_html` LONGTEXT NOT NULL COMMENT 'Conteúdo HTML do modelo com placeholders',
    `variaveis` TEXT NULL COMMENT 'JSON com lista de variáveis disponíveis',
    `ativo` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    `deleted_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    INDEX `tipo_item` (`tipo_item`),
    INDEX `ativo` (`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Documentos do Contrato
CREATE TABLE IF NOT EXISTS `contrato_documentos` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `contrato_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID do contrato',
    `modelo_id` INT(11) UNSIGNED NULL COMMENT 'ID do modelo utilizado',
    `titulo` VARCHAR(200) NOT NULL COMMENT 'Título do documento',
    `conteudo_html` LONGTEXT NOT NULL COMMENT 'Conteúdo HTML do documento preenchido',
    `status` ENUM('rascunho', 'pendente_assinatura', 'assinado', 'confirmado', 'cancelado') 
             NOT NULL DEFAULT 'rascunho' COMMENT 'Status do documento',
    `hash_assinatura` VARCHAR(100) NULL COMMENT 'Hash único para assinatura',
    `ip_assinatura` VARCHAR(45) NULL COMMENT 'IP do assinante',
    `user_agent_assinatura` VARCHAR(500) NULL COMMENT 'User agent do navegador na assinatura',
    `data_envio` DATETIME NULL COMMENT 'Data que foi enviado para assinatura',
    `data_assinatura` DATETIME NULL COMMENT 'Data que foi assinado',
    `data_confirmacao` DATETIME NULL COMMENT 'Data que foi confirmado pelo sistema',
    `assinado_por` VARCHAR(200) NULL COMMENT 'Nome de quem assinou',
    `documento_assinante` VARCHAR(20) NULL COMMENT 'CPF/CNPJ de quem assinou',
    `confirmado_por` INT(11) UNSIGNED NULL COMMENT 'ID do usuário que confirmou',
    `observacoes` TEXT NULL,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    `deleted_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    INDEX `contrato_id` (`contrato_id`),
    INDEX `modelo_id` (`modelo_id`),
    INDEX `status` (`status`),
    INDEX `hash_assinatura` (`hash_assinatura`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Atualizar ENUM da tabela contratos para incluir 'aguardando_contrato'
-- ========================================
ALTER TABLE `contratos` MODIFY `situacao` ENUM(
    'proposta',
    'proposta_aceita', 
    'contrato_assinado',
    'pagamento_aberto',
    'pagamento_andamento',
    'aguardando_contrato',
    'pagamento_confirmado',
    'cancelado',
    'banido'
) NOT NULL DEFAULT 'proposta' COMMENT 'Situação atual do contrato';

-- ========================================
-- Inserir modelo de exemplo
-- ========================================
INSERT INTO `contrato_documento_modelos` 
(`nome`, `tipo_item`, `descricao`, `ativo`, `created_at`, `updated_at`, `conteudo_html`) 
VALUES 
('Contrato Padrão de Exposição', 'Geral', 'Modelo padrão para contratos de espaço comercial e expositores', 1, NOW(), NOW(),
'<div style="font-family: ''Times New Roman'', serif; font-size: 12pt; line-height: 1.6; color: #333;">

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

</div>');

