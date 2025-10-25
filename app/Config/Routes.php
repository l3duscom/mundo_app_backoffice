<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override(); // CRIAR PÁGINA CUSTOMIZADA
$routes->setAutoRoute(true);

//$routes->get('geraCsvDiciScm', 'DeclarationsController::geraCsvDiciScm');

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');


$routes->get('login', 'Login::novo');
$routes->get('logout', 'Login::logout');

$routes->get('esqueci', 'Password::esqueci');

$routes->get('notifica', 'Declarations::notifica');
$routes->get('notificaT', 'Declarations::notificaT');
$routes->post('import-csv', 'DeclarationsController::importCsv');
$routes->post('cron', 'Cron::index');

$routes->group(
    'cron',
    function ($routes) {
        $routes->post('paynotify', 'PayNotify::index');
    }
);



// Grupo de rotas para o controller de Formas de pagamentos
$routes->group('formas', function ($routes) {
    $routes->add('/', 'FormasPagamentos::index');
    $routes->add('recuperaformas', 'FormasPagamentos::recuperaFormas');

    $routes->add('exibir/(:segment)', 'FormasPagamentos::exibir/$1');
    $routes->add('editar/(:segment)', 'FormasPagamentos::editar/$1');
    $routes->add('criar/', 'FormasPagamentos::criar');

    // Aqui é POST
    $routes->post('cadastrar', 'FormasPagamentos::cadastrar');
    $routes->post('atualizar', 'FormasPagamentos::atualizar');

    // Aqui é GET e POST
    $routes->match(['get', 'post'], 'excluir/(:segment)', 'FormasPagamentos::excluir/$1');
});

$routes->group('assinaturas', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('/', 'Assinaturas::index');
    $routes->get('contratar/(:num)', 'Assinaturas::contratar/$1');
    $routes->post('processar', 'Assinaturas::processar');
    $routes->get('confirmacao/(:num)', 'Assinaturas::confirmacao/$1');
    $routes->get('minhas', 'Assinaturas::minhasAssinaturas');
    $routes->get('detalhes/(:num)', 'Assinaturas::detalhes/$1');
    $routes->post('cancelar', 'Assinaturas::cancelar');
    $routes->post('webhook', 'Assinaturas::webhook');
    
    // Área administrativa
    $routes->get('admin', 'Assinaturas::admin');
    $routes->get('admin/planos', 'Assinaturas::adminPlanos');
});


// Grupo de rotas para o controller de Ordens Itens para não dar o erro de 404 - Not found
// quando estiver hospedado
$routes->group('ordensitens', function ($routes) {
    $routes->add('itens/(:segment)', 'OrdensItens::itens/$1');
    $routes->add('pesquisaitens', 'OrdensItens::pesquisaItens');
    $routes->add('adicionaritem', 'OrdensItens::adicionarItem');
    $routes->add('atualizarquantidade/(:segment)', 'OrdensItens::atualizarQuantidade/$1');
    $routes->add('removeritem/(:segment)', 'OrdensItens::removerItem/$1');
});


// Grupo de rotas para o controller de Ordens Evidências para não dar o erro de 404 - Not found
// quando estiver hospedado
$routes->group('ordensevidencias', function ($routes) {
    $routes->add('evidencias/(:segment)', 'OrdensEvidencias::evidencias/$1');
    $routes->add('upload', 'OrdensEvidencias::upload');
    $routes->add('arquivo/(:segment)', 'OrdensEvidencias::arquivo/$1');
    $routes->add('removerevidencia/(:segment)', 'OrdensEvidencias::removerEvidencia/$1');
});

// Rotas para os relatórios
$routes->group('relatorios', function ($routes) {
    $routes->add('produtos-com-estoque-zerado-negativo', 'Relatorios::gerarRelatorioProdutosEstoqueZerado');
    $routes->add('itens-mais-vendidos', 'Relatorios::gerarRelatorioItensMaisVendidos');

    // Rotas das ordens
    $routes->add('ordens-abertas', 'Relatorios::exibeRelatorioOrdens');
    $routes->add('ordens-encerradas', 'Relatorios::exibeRelatorioOrdens');
    $routes->add('ordens-excluidas', 'Relatorios::exibeRelatorioOrdens');
    $routes->add('ordens-canceladas', 'Relatorios::exibeRelatorioOrdens');
    $routes->add('ordens-aguardando-pagamento', 'Relatorios::exibeRelatorioOrdens');
    $routes->add('ordens-nao-pagas', 'Relatorios::exibeRelatorioOrdens');
    $routes->add('ordens-com-boleto', 'Relatorios::exibeRelatorioOrdens');


    // Contas
    $routes->add('contas-abertas', 'Relatorios::exibeRelatorioContas');
    $routes->add('contas-pagas', 'Relatorios::exibeRelatorioContas');
    $routes->add('contas-vencidas', 'Relatorios::exibeRelatorioContas');


    // Equipe
    $routes->add('desempenho-atendentes', 'Relatorios::exibeRelatorioEquipe');
    $routes->add('desempenho-responsaveis', 'Relatorios::exibeRelatorioEquipe');
});

$routes->get('api/checkout/obrigado', 'Api\Checkout::obrigado');
$routes->post('api/checkout/notify', 'Api\Checkout::notify');
$routes->post('notify', 'Api\Checkout::notify'); // Rota alternativa para o ASAAS
$routes->post('webhook/asaas', 'Webhook::asaas'); // Rota webhook específica
$routes->post('api/acessos/check', 'Api\Acessos::check'); 

// Rotas do Checkout
$routes->get('checkout/pix/(:num)', 'Checkout::pix/$1');
$routes->get('checkout/cartao/(:num)', 'Checkout::cartao/$1');
$routes->post('checkout/cartao_step_2/(:num)', 'Checkout::cartao_step_2/$1');
$routes->post('checkout/finalizarpix/(:num)', 'Checkout::finalizarpix/$1');
$routes->post('checkout/finalizarcartao/(:num)', 'Checkout::finalizarcartao/$1');
$routes->get('checkout/qrcode/(:any)', 'Checkout::qrcode/$1');
$routes->get('checkout/check-status/(:any)', 'Checkout::checkTransactionStatus/$1');

// Rotas para o controller de API do carrinho
$routes->group('api/carrinho', ['filter' => 'apiKey'], function ($routes) {
    $routes->get('evento/(:num)', 'ApiCarrinho::evento/$1');
    $routes->get('adicional/(:num)', 'ApiCarrinho::adicional/$1');
    $routes->get('girafinhas/(:num)', 'ApiCarrinho::girafinhas/$1');
    $routes->get('otakada/(:num)', 'ApiCarrinho::otakada/$1');
    $routes->get('loja', 'ApiCarrinho::loja');
    $routes->get('clube', 'ApiCarrinho::clube');
    $routes->get('pucrs/(:num)', 'ApiCarrinho::pucrs/$1');
    $routes->get('marista/(:num)', 'ApiCarrinho::marista/$1');
    $routes->post('adicionar', 'ApiCarrinho::adicionar');
});

// Rotas para o controller de API do checkout
$routes->group('api/checkout', ['filter' => 'apiKey'], function ($routes) {
    $routes->get('pix/(:num)', 'ApiCheckout::pix/$1');
    $routes->get('cartao/(:num)', 'ApiCheckout::cartao/$1');
    $routes->get('loja', 'ApiCheckout::loja');
    $routes->get('obrigado', 'ApiCheckout::obrigado');
    $routes->get('qrcode/(:num)/(:any)', 'ApiCheckout::qrcode/$1/$2');
    $routes->post('finalizarpix/(:num)', 'ApiCheckout::finalizarpix/$1');
    $routes->post('finalizarcartao/(:num)', 'ApiCheckout::finalizarcartao/$1');
});

$routes->get('concursos/(:num)', 'Concursos::index/$1');
$routes->get('concursos', 'Concursos::index');

$routes->get('usuarios/perfil', 'Usuarios::perfil');
$routes->post('usuarios/atualizarperfil', 'Usuarios::atualizarPerfil');

// ========================================
// Rotas da API de Autenticação
// ========================================
$routes->group('api/auth', function ($routes) {
    // Rotas públicas (sem autenticação)
    $routes->post('login', 'Api\Auth::login'); // Login via API - retorna JWT token
    $routes->post('refresh', 'Api\Auth::refresh'); // Renova o token usando refresh token
    
    // Rotas protegidas (requer JWT token)
    $routes->get('me', 'Api\Auth::me', ['filter' => 'jwtAuth']); // Perfil do usuário autenticado
});


/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
