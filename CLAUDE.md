# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

**IMPORTANTE**: Sempre forneça explicações e comentários em português para este projeto.

## Visão Geral do Projeto

Esta é uma aplicação **CodeIgniter 4** de gerenciamento de eventos e e-commerce para venda de ingressos. O sistema gerencia:

- Venda de ingressos com diferentes categorias de preços (VIP, Epic, pacotes combo)
- Carrinho de compras e checkout com PIX e cartão de crédito
- Gerenciamento de clientes e usuários com permissões baseadas egeminim papéis
- Gerenciamento de concursos (cosplay, competições de K-pop)
- Controle de pedidos e rastreamento de entrega
- Dashboard de relatórios e analytics

## Comandos de Desenvolvimento

### Comandos PHP/Composer

- `composer install` - Instalar dependências
- `composer update` - Atualizar dependências
- `php spark` - Acessar ferramentas CLI do CodeIgniter
- `php spark migrate` - Executar migrações do banco de dados
- `php spark db:seed [NomeSeeder]` - Executar seeders do banco

### Testes

- `composer test` ou `vendor/bin/phpunit` - Executar testes PHPUnit
- Testes localizados no diretório `tests/` com configuração PHPUnit em `phpunit.xml.dist`

### Servidor de Desenvolvimento

- `php spark serve` - Iniciar servidor de desenvolvimento (built-in do CodeIgniter)
- Aplicação roda do diretório `public/` (document root)

## Arquitetura e Estrutura do Código

### Padrão MVC (CodeIgniter 4)

- **Controllers** (`app/Controllers/`): Manipulam requisições HTTP e lógica de negócio
- **Models** (`app/Models/`): Interação com banco de dados usando classe Model do CodeIgniter
- **Views** (`app/Views/`): Arquivos de template para saída HTML
- **Entities** (`app/Entities/`): Objetos de dados representando registros do banco

### Componentes Principais

#### Autenticação e Autorização

- Biblioteca de autenticação customizada em `app/Libraries/Autenticacao.php`
- Sistema de permissões baseado em papéis com tabelas `usuarios`, `grupos`, e `permissoes`
- Controller base fornece método `usuarioLogado()` e verificação de permissões

#### Integração de Pagamentos

- **Services** (`app/Services/`): Integrações com gateways de pagamento
  - `AsaasService.php` - Processador de pagamentos Asaas
  - `MercadoPagoService.php` - Integração Mercado Pago
  - `GerencianetService.php` - Gateway de pagamento Gerencianet
  - `PagarmeService.php` - Serviço de pagamento Pagar.me

#### Banco de Dados

- Banco MySQL com migrações em `app/Database/Migrations/`
- Usa Query Builder e classes Model built-in do CodeIgniter
- Soft deletes habilitado na maioria dos models

### Roteamento

- Rotas definidas em `app/Config/Routes.php`
- Usa grupos de rotas para organização (formas, api/carrinho, api/checkout, etc.)
- Auto-routing habilitado para métodos de controller

### Models Principais e Lógica de Negócio

- `EventoModel` - Gerenciamento de eventos
- `IngressoModel` - Gerenciamento de ingressos com lógica complexa de preços
- `PedidoModel` - Processamento de pedidos e rastreamento de pagamentos
- `ClienteModel` / `UsuarioModel` - Gerenciamento de usuários
- `TransacaoModel` - Rastreamento de transações de pagamento

### Integração Frontend

- Endpoints de API para processo de checkout (`api/checkout/`, `api/carrinho/`)
- Funcionalidade de carrinho e checkout baseada em AJAX
- Usa Bootstrap para componentes de UI

### Upload de Arquivos

- Imagens de usuários armazenadas em `writable/uploads/usuarios/`
- Imagens de eventos/produtos em `writable/uploads/itens/`
- Arquivos de recibos em `writable/uploads/recibos/`

## Configuração do Ambiente

1. Copie `env` para `.env` e configure:

   - Credenciais do banco de dados
   - URL base
   - Chaves de API dos gateways de pagamento
   - Configurações de email

2. Certifique-se de que o diretório `writable/` tenha permissões adequadas para uploads e cache

## Notas Importantes

- Esta é uma aplicação de produção que processa pagamentos reais e dados de clientes
- Sempre teste integrações de pagamento em modo sandbox antes do deploy em produção
- A aplicação usa contexto de evento baseado em sessão (`event_id` armazenado na sessão)
- Verificação extensiva de permissões em toda a aplicação
- Regras de validação customizadas e mensagens de erro em português (pt-BR)
