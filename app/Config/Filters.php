<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;

class Filters extends BaseConfig
{
    /**
     * Configures aliases for Filter classes to
     * make reading things nicer and simpler.
     *
     * @var array
     */
    public $aliases = [
        'csrf' => CSRF::class,
        'toolbar' => DebugToolbar::class,
        'honeypot' => Honeypot::class,
        'login' => \App\Filters\LoginFilter::class, // Filtro de login
        'visitante' => \App\Filters\VisitanteFilter::class, // Filtro visitante
        'cliente' => \App\Filters\ClienteFilter::class, // Filtro cliente
        'webhook' => \App\Filters\WebhookFilter::class, // Filtro webhook
        'eventoContext' => \App\Filters\EventoContextFilter::class, // Filtro contexto de evento
        'apiKey' => \App\Filters\ApiKeyFilter::class, // Filtro API Key
        'jwtAuth' => \App\Filters\JwtAuthFilter::class, // Filtro JWT para autenticação de API
        'secureApi' => \App\Filters\SecureApiFilter::class, // Filtro de segurança para API (HTTPS, rate limiting)
    ];

    /**
     * List of filter aliases that are always
     * applied before and after every request.
     *
     * @var array
     */
    public $globals = [
        'before' => [
            'honeypot',
            'csrf' => [
                'except' => [
                    'cron/paynotify',
                    'cidades/getcidades',
                    'api/checkout/notify',
                    'webhook/backoffice',
                    'api/acessos/check',   // OK: CSRF liberado aqui
                    'api/*',   // Todas as rotas da API não usam CSRF
                ],
            ],
        ],
        'after' => [
            'toolbar',
            // 'honeypot',
        ],
    ];

    /**
     * List of filter aliases that works on a
     * particular HTTP method (GET, POST, etc.).
     *
     * Example:
     * 'post' => ['csrf', 'throttle']
     *
     * @var array
     */
    public $methods = [];

    /**
     * List of filter aliases that should run on any
     * before or after URI patterns.
     *
     * Example:
     * 'isLoggedIn' => ['before' => ['account/*', 'profiles/*']]
     *
     * @var array
     */
    public $filters = [
        'webhook' => [
            'before' => [
                'api/checkout/notify',
                'webhook/backoffice',
            ],
        ],
        'login' => [
            'before' => [
                '/',
                'home(/*)?',
                'usuarios(/*)?',
                'grupos(/*)?',
                'fornecedores(/*)?',
                'itens(/*)?',
                'itenscatalogo(/*)?',
                'formasPagamentos(/*)?',
                'eventos(/*)?',
                'ordens(/*)?',
                'contas(/*)?',
                'formas(/*)?',
                'ordensitens(/*)?',
                'ordensevidencias(/*)?',
                'transacoes/editar',
                'transacoes/atualizar',
                'transacoes/cancelar',
                'transacoes/consultar',
                'transacoes/pagar',
                'clientes(/*)?',
                'declarations(/*)?',
                'declarations(/*)?',
                'ingressos(/*)?',
                'pedidos(/*)?',
                'dashboard(/*)?',
                'concursos/gerenciar(/*)?',
                'concursos/my(/*)?',
                'concursos/criar(/*)?',
                'concursos/editar(/*)?',
                'concursos/excluir(/*)?',
                'concursos/duplicar(/*)?',
                'concursos/atualizar',
                'concursos/salvar',
                'concursos/aprovaInscricao(/*)?',
                'concursos/rejeitaInscricao(/*)?',
                'concursos/cancelaInscricao(/*)?',
                'concursos/checkin(/*)?',
                'concursos/checkinonline(/*)?',
                'concursos/avaliacao(/*)?',
                'concursos/avaliacao_kpop(/*)?',
                'concursos/historico_edicoes(/*)?',
                'contratos(/*)?',
                'contratoitens(/*)?',
                'contratodocumentos/gerenciar(/*)?',
                'contratodocumentos/gerar',
                'contratodocumentos/visualizar(/*)?',
                'contratodocumentos/editar(/*)?',
                'contratodocumentos/salvar',
                'contratodocumentos/enviarparaassinatura',
                'contratodocumentos/confirmar',
                'contratodocumentos/cancelar',
                'contratodocumentos/modelos',
                'contratodocumentos/criarmodelo',
                'contratodocumentos/salvarmodelo',
                'contratodocumentos/editarmodelo(/*)?',
                'contratodocumentos/excluirmodelo(/*)?',
                'expositores(/*)?',
                'relatorios(/*)?',
                'pdv(/*)?',
            ],
            'except' => [
                'api/*', // Exclui todas as rotas da API do filtro de login web
                'eventos/imagem/*', // Imagens de eventos são públicas
            ],
        ],
        'visitante' => [
            'before' => [
                'login(/*)?',
                'password(/*)?',
                'paynotify(/*)?',
                'cron(/paynotify)?',

            ],
        ],
        'cliente' => [
            'before' => [
                'console/dashboard',
                'console(/*)',
                'ingressos(/*)?',
                'pedidos(/*)?',

                'usuarios(/editar)?',
                'usuarios(/editarsenha)?',
            ],
            'except' => [
                'api/*', // Exclui todas as rotas da API do filtro cliente web
            ],
        ],
        'eventoContext' => [
            'before' => [
                'concursos(/*)?',
                'pedidos/gerenciar_evento(/*)?',
                'ingressos/add(/*)?',
            ],
        ],
    ];
}
